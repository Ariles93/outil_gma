<?php

namespace App\Models;

use App\Core\Model;

class Log extends Model
{
    public function findAll()
    {
        // Assuming a logs table exists with user_id, action, details, created_at
        // If not, this will fail, but we'll cross that bridge if we get there.
        // We join with users to get the username if possible, or just display raw logs.
        // Let's assume a simple structure first.

        // Check if users table exists and has name? User model shows it uses 'users' table.
        // Let's try to join with users.

        $stmt = $this->db->query("
            SELECT l.*, u.email as user_email
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function create($userId, $action, $details = null)
    {
        $stmt = $this->db->prepare("INSERT INTO logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details]);
    }
}
