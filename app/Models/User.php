<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY email");
        return $stmt->fetchAll();
    }
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($email, $password, $role, $firstName)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, role, first_name, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$email, $hashedPassword, $role, $firstName]);
        return $this->db->lastInsertId();
    }

    public function setResetToken($email, $token)
    {
        // Token expires in 1 hour
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?");
        return $stmt->execute([$token, $expiresAt, $email]);
    }

    public function findByResetToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires_at > ?");
        $stmt->execute([$token, date('Y-m-d H:i:s')]);
        return $stmt->fetch();
    }

    public function updatePasswordByToken($token, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE reset_token = ?");
        return $stmt->execute([$hashedPassword, $token]);
    }

    public function update($id, $email, $role, $firstName, $password = null)
    {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET email = ?, role = ?, first_name = ?, password = ? WHERE id = ?");
            return $stmt->execute([$email, $role, $firstName, $hashedPassword, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET email = ?, role = ?, first_name = ? WHERE id = ?");
            return $stmt->execute([$email, $role, $firstName, $id]);
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
