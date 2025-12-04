<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

class Assignment extends Model
{
    public function findAll()
    {
        $stmt = $this->db->query("
            SELECT ass.*, 
                   m.asset_tag, m.serial_number, m.brand, m.model, c.name as category_name,
                   a.first_name, a.last_name
            FROM assignments ass
            JOIN materials m ON ass.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            JOIN agents a ON ass.agent_id = a.id
            ORDER BY ass.assigned_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // Check if material is available
            $stmt = $this->db->prepare("SELECT status FROM materials WHERE id = ? FOR UPDATE");
            $stmt->execute([$data['material_id']]);
            $mat = $stmt->fetch();

            if (!$mat || $mat['status'] !== 'available') {
                throw new Exception('Ce matériel n\'est pas disponible pour une attribution.');
            }

            // Insert assignment
            $stmt = $this->db->prepare("INSERT INTO assignments (agent_id, material_id, assigned_at, condition_on_assign, note) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['agent_id'],
                $data['material_id'],
                $data['assigned_at'],
                $data['condition_on_assign'] ?? null,
                $data['note'] ?? null
            ]);

            $assignmentId = $this->db->lastInsertId();

            // Update material status
            $stmt = $this->db->prepare("UPDATE materials SET status = 'assigned' WHERE id = ?");
            $stmt->execute([$data['material_id']]);

            $this->db->commit();
            return $assignmentId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markAsReturned($assignmentId)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT material_id, agent_id FROM assignments WHERE id = ? AND returned_at IS NULL");
            $stmt->execute([$assignmentId]);
            $assignment = $stmt->fetch();

            if (!$assignment) {
                throw new Exception('Affectation introuvable ou matériel déjà retourné.');
            }

            // Update assignment
            $stmt = $this->db->prepare("UPDATE assignments SET returned_at = CURDATE() WHERE id = ?");
            $stmt->execute([$assignmentId]);

            // Update material status
            $stmt = $this->db->prepare("UPDATE materials SET status = 'available' WHERE id = ?");
            $stmt->execute([$assignment['material_id']]);

            $this->db->commit();
            return $assignment;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findCurrentByAgent($agentId)
    {
        $stmt = $this->db->prepare("
            SELECT ass.id as assign_id, m.*, c.name as category_name, ass.assigned_at, ass.condition_on_assign, ass.note
            FROM assignments ass 
            JOIN materials m ON ass.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            WHERE ass.agent_id = ? AND ass.returned_at IS NULL
        ");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    public function findHistoryByAgent($agentId)
    {
        $stmt = $this->db->prepare("
            SELECT ass.*, m.asset_tag, c.name as category_name, m.brand, m.model
            FROM assignments ass 
            JOIN materials m ON ass.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            WHERE ass.agent_id = ? 
            ORDER BY ass.assigned_at DESC
        ");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    public function findAvailableMaterials($query = '')
    {
        $sql = "
            SELECT m.id, m.asset_tag, c.name as type, m.brand, m.model, m.serial_number, m.status
            FROM materials m
            JOIN categories c ON m.category_id = c.id
            WHERE m.status = 'available' AND m.deleted_at IS NULL
        ";

        $params = [];

        if ($query !== '') {
            $term = '%' . strtolower($query) . '%';
            $sql .= " AND (LOWER(m.serial_number) LIKE ? 
                         OR LOWER(m.asset_tag) LIKE ? 
                         OR LOWER(CONCAT(c.name, ' ', m.brand, ' ', m.model)) LIKE ?)";
            $params = [$term, $term, $term];

            $sql .= " ORDER BY c.name, m.brand";
        } else {
            $sql .= " ORDER BY c.name, m.brand, m.model";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function findByIdWithDetails($id)
    {
        $stmt = $this->db->prepare("
            SELECT
                a.id, a.assigned_at, a.returned_at, a.condition_on_assign,
                ag.first_name, ag.last_name, ag.position, ag.department,
                m.asset_tag, m.serial_number, m.brand, m.model,
                c.name as category_name
            FROM assignments a
            JOIN agents ag ON a.agent_id = ag.id
            JOIN materials m ON a.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function paginate($page = 1, $perPage = 10, $search = '', $sortColumn = 'assigned_at', $sortOrder = 'desc')
    {
        $offset = ($page - 1) * $perPage;

        // Whitelist sort columns
        $allowedSortColumns = ['assigned_at', 'category_name', 'brand', 'model', 'first_name', 'last_name', 'condition_on_assign', 'returned_at'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'assigned_at';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        // Build query
        $sql = "
            FROM assignments ass
            JOIN materials m ON ass.material_id = m.id
            JOIN categories c ON m.category_id = c.id
            JOIN agents a ON ass.agent_id = a.id
        ";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE (
                m.asset_tag LIKE :search1 OR
                m.serial_number LIKE :search2 OR
                m.brand LIKE :search3 OR
                m.model LIKE :search4 OR
                c.name LIKE :search5 OR
                a.first_name LIKE :search6 OR
                a.last_name LIKE :search7
            )";
            $searchTerm = '%' . $search . '%';
            for ($i = 1; $i <= 7; $i++) {
                $params[":search$i"] = $searchTerm;
            }
        }

        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) " . $sql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Get items
        $query = "
            SELECT ass.*, 
                   m.asset_tag, m.serial_number, m.brand, m.model, c.name as category_name,
                   a.first_name, a.last_name
            " . $sql . "
            ORDER BY $sortColumn $sortOrder
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
