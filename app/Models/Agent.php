<?php

namespace App\Models;

use App\Core\Model;

class Agent extends Model
{
    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM agents ORDER BY last_name, first_name");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM agents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO agents (first_name, last_name, email, phone, department, position, employee_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['department'] ?? null,
            $data['position'] ?? null,
            $data['employee_id'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE agents SET first_name = ?, last_name = ?, email = ?, phone = ?, department = ?, position = ?, employee_id = ?, notes = ? WHERE id = ?");
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['department'] ?? null,
            $data['position'] ?? null,
            $data['employee_id'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    public function search($query)
    {
        $stmt = $this->db->prepare("SELECT * FROM agents WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?");
        $term = "%$query%";
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }
    public function paginate($page = 1, $perPage = 10, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereSql = "";

        if ($search !== '') {
            $like = '%' . str_replace(' ', '%', $search) . '%';
            $whereSql = " WHERE CONCAT(first_name,' ',last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
            $params = [$like, $like, $like];
        }

        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM agents" . $whereSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get items
        // Get items
        $sql = "SELECT * FROM agents" . $whereSql . " ORDER BY last_name, first_name LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);

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
}
