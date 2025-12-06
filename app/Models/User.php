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

    public function create($email, $password, $role, $firstName, $lastName)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, role, first_name, last_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$email, $hashedPassword, $role, $firstName, $lastName]);
        return $this->db->lastInsertId();
    }

    // ... setResetToken, findByResetToken, updatePasswordByToken methods remain unchanged ...

    public function update($id, $email, $role, $firstName, $lastName, $password = null)
    {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET email = ?, role = ?, first_name = ?, last_name = ?, password = ? WHERE id = ?");
            return $stmt->execute([$email, $role, $firstName, $lastName, $hashedPassword, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET email = ?, role = ?, first_name = ?, last_name = ? WHERE id = ?");
            return $stmt->execute([$email, $role, $firstName, $lastName, $id]);
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
