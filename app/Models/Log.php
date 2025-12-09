<?php

namespace App\Models;

use App\Core\Model;

class Log extends Model
{
    public function paginate($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        // Get total count
        $countStmt = $this->db->query("SELECT COUNT(*) FROM logs");
        $total = $countStmt->fetchColumn();

        // Get paginated data
        $stmt = $this->db->prepare("
            SELECT l.*, u.email as user_email
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total
        ];
    }

    public function create($userId, $action, $details = null)
    {
        $stmt = $this->db->prepare("INSERT INTO logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details]);
    }

    public function findAll()
    {
        $stmt = $this->db->query("
            SELECT l.*, u.email as user_email
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
