<?php

namespace App\Models;

use App\Core\Model;

class Material extends Model
{
    public function findAll()
    {
        $stmt = $this->db->query("
            SELECT m.*, c.name as category_name 
            FROM materials m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.deleted_at IS NULL 
            ORDER BY m.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, c.name as category_name 
            FROM materials m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.id = ? AND m.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO materials (category_id, brand, model, serial_number, inventory_number, asset_tag, status, purchase_date, warranty_expiry, cost, notes) VALUES (?, ?, ?, ?, ?, ?, 'available', ?, ?, ?, ?)");
        $stmt->execute([
            $data['category_id'],
            $data['brand'],
            $data['model'],
            $data['serial_number'],
            $data['inventory_number'],
            $data['asset_tag'] ?? null,
            $data['purchase_date'] ?? null,
            $data['warranty_expiry'] ?? null,
            $data['cost'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE materials SET category_id = ?, brand = ?, model = ?, serial_number = ?, inventory_number = ?, asset_tag = ?, purchase_date = ?, warranty_expiry = ?, cost = ?, notes = ? WHERE id = ?");
        return $stmt->execute([
            $data['category_id'],
            $data['brand'],
            $data['model'],
            $data['serial_number'],
            $data['inventory_number'],
            $data['asset_tag'] ?? null,
            $data['purchase_date'] ?? null,
            $data['warranty_expiry'] ?? null,
            $data['cost'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("UPDATE materials SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findTrashed()
    {
        $stmt = $this->db->query("
            SELECT m.*, c.name as category_name 
            FROM materials m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.deleted_at IS NOT NULL 
            ORDER BY m.deleted_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function restore($id)
    {
        $stmt = $this->db->prepare("UPDATE materials SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function paginate($page = 1, $perPage = 10, $filters = [], $sortColumn = 'id', $sortOrder = 'desc')
    {
        $offset = ($page - 1) * $perPage;
        $params = [];

        // Base SQL
        $sql = "
            FROM materials m
            LEFT JOIN categories c ON m.category_id = c.id
            LEFT JOIN assignments a ON a.material_id = m.id AND a.returned_at IS NULL
            LEFT JOIN agents ag ON ag.id = a.agent_id
            WHERE m.deleted_at IS NULL
        ";

        // Filter Logic
        // 1. Search
        if (!empty($filters['search'])) {
            $sql .= " AND (m.asset_tag LIKE ? OR c.name LIKE ? OR m.brand LIKE ? OR m.model LIKE ? OR m.serial_number LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like, $like, $like]);
        }

        // 2. Category
        if (!empty($filters['category_id'])) {
            $sql .= " AND m.category_id = ?";
            $params[] = $filters['category_id'];
        }

        // 3. Status
        if (!empty($filters['status'])) {
            $sql .= " AND m.status = ?";
            $params[] = $filters['status'];
        }

        // 4. Purchase Date Range
        if (!empty($filters['date_min'])) {
            $sql .= " AND m.purchase_date >= ?";
            $params[] = $filters['date_min'];
        }
        if (!empty($filters['date_max'])) {
            $sql .= " AND m.purchase_date <= ?";
            $params[] = $filters['date_max'];
        }

        // Count Total
        $countStmt = $this->db->prepare("SELECT COUNT(m.id) " . $sql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Sorting Logic
        $allowedColumns = ['category_name', 'model', 'status', 'agent_name', 'id', 'purchase_date'];
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'id';
        }
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $orderBy = "";
        if ($sortColumn === 'agent_name') {
            $orderBy = "ag.last_name $sortOrder, ag.first_name $sortOrder";
        } elseif ($sortColumn === 'category_name') {
            $orderBy = "c.name $sortOrder";
        } elseif ($sortColumn === 'model') {
            $orderBy = "m.brand $sortOrder, m.model";
        } elseif ($sortColumn === 'status') {
            $orderBy = "m.status $sortOrder";
        } elseif ($sortColumn === 'purchase_date') {
            $orderBy = "m.purchase_date $sortOrder";
        } else {
            $orderBy = "m.id $sortOrder";
        }

        // Fetch Data
        $selectSql = "SELECT m.*, c.name as category_name, ag.id as agent_id, ag.first_name, ag.last_name ";
        $finalSql = $selectSql . $sql . " ORDER BY " . $orderBy . " LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($finalSql);

        $paramIndex = 1;
        foreach ($params as $value) {
            $stmt->bindValue($paramIndex, $value);
            $paramIndex++;
        }

        $stmt->bindValue($paramIndex, (int) $perPage, \PDO::PARAM_INT);
        $paramIndex++;
        $stmt->bindValue($paramIndex, (int) $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function search($query = '')
    {
        $sql = "
            SELECT m.id, m.asset_tag, c.name as type, m.brand, m.model, m.serial_number, m.status
            FROM materials m
            JOIN categories c ON m.category_id = c.id
            WHERE m.status = 'available' AND m.deleted_at IS NULL
        ";

        $params = [];

        if ($query !== '') {
            $exact_q = $query;
            $like_q = '%' . $query . '%';

            // PDO::ATTR_EMULATE_PREPARES is false, so we cannot reuse named parameters.
            // We must use unique names for every occurrence.
            $sql .= "
                AND (
                    m.serial_number LIKE :like_q_1 COLLATE utf8mb4_general_ci
                    OR m.asset_tag LIKE :like_q_2 COLLATE utf8mb4_general_ci
                    OR CONCAT_WS(' ', c.name, m.brand, m.model) LIKE :like_q_3 COLLATE utf8mb4_general_ci
                )
            ";

            $sql .= "
                ORDER BY
                    CASE
                        WHEN m.serial_number LIKE :exact_q_1 COLLATE utf8mb4_general_ci THEN 1
                        WHEN m.asset_tag LIKE :exact_q_2 COLLATE utf8mb4_general_ci THEN 2
                        WHEN CONCAT_WS(' ', c.name, m.brand, m.model) LIKE :like_q_4 COLLATE utf8mb4_general_ci THEN 3
                        ELSE 4
                    END, 
                    c.name, m.brand
            ";

            $params = [
                ':like_q_1' => $like_q,
                ':like_q_2' => $like_q,
                ':like_q_3' => $like_q,
                ':exact_q_1' => $exact_q,
                ':exact_q_2' => $exact_q,
                ':like_q_4' => $like_q
            ];
        } else {
            $sql .= " ORDER BY c.name, m.brand, m.model";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAssignmentHistory($materialId)
    {
        $stmt = $this->db->prepare("
            SELECT a.assigned_at, a.returned_at, a.note, ag.first_name, ag.last_name, ag.id as agent_id
            FROM assignments a
            JOIN agents ag ON a.agent_id = ag.id
            WHERE a.material_id = ?
            ORDER BY a.assigned_at DESC
        ");
        $stmt->execute([$materialId]);
        return $stmt->fetchAll();
    }

    public function getCurrentAssignment($materialId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                a.assigned_at, a.condition_on_assign, a.note AS assignment_note,
                ag.id AS agent_id, ag.first_name, ag.last_name
            FROM assignments a
            JOIN agents ag ON ag.id = a.agent_id
            WHERE a.material_id = ? AND a.returned_at IS NULL
        ");
        $stmt->execute([$materialId]);
        return $stmt->fetch();
    }
}
