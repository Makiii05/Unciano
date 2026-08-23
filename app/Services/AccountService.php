<?php

namespace App\Services;

use App\Core\Database;

class AccountService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // ---- Faculty (users) ----

    public function getUsers(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY name');
        return $stmt->fetchAll();
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
        }
        return (bool) $stmt->fetch();
    }

    public function createUser(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, type, role, department_id, status, created_at, updated_at)
             VALUES (:name, :email, :password, :type, :role, :department_id, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'type' => $data['type'],
            'role' => $data['role'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateUser(int $userId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = :name, email = :email, status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
            'id' => $userId,
        ]);
    }

    public function deleteUser(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    public function changeUserPassword(int $userId, string $newPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);
    }

    // ---- Teacher accounts ----

    public function getTeacherAccounts(): array
    {
        $stmt = $this->db->query(
            'SELECT ta.id, ta.teacher_id, ta.status, ta.password, ta.created_at, ta.updated_at,
                    t.code, t.first_name, t.middle_name, t.last_name, t.email AS teacher_email, t.status AS teacher_status
             FROM teacher_accounts ta
             LEFT JOIN teachers t ON t.id = ta.teacher_id
             ORDER BY ta.id'
        );
        return $stmt->fetchAll();
    }

    public function getTeacherAccountById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ta.*, t.first_name, t.last_name, t.email AS teacher_email
             FROM teacher_accounts ta
             LEFT JOIN teachers t ON t.id = ta.teacher_id
             WHERE ta.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateTeacherAccount(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE teacher_accounts SET status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function deleteTeacherAccount(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM teacher_accounts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function changeTeacherPassword(int $id, string $newPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE teacher_accounts SET password = :password, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    // ---- Student accounts ----

    public function getStudentAccounts(): array
    {
        $stmt = $this->db->query(
            'SELECT sa.id, sa.student_id, sa.account_status, sa.password, sa.examination_permit, sa.created_at, sa.updated_at,
                    s.student_number, s.first_name, s.middle_name, s.last_name, s.sex,
                    s.department_id, s.program_id, s.level_id,
                    d.code AS department_code, p.code AS program_code, l.description AS level_description
             FROM student_accounts sa
             JOIN students s ON s.id = sa.student_id
             LEFT JOIN departments d ON d.id = s.department_id
             LEFT JOIN programs p ON p.id = s.program_id
             LEFT JOIN levels l ON l.id = s.level_id
             ORDER BY sa.id'
        );
        return $stmt->fetchAll();
    }

    public function getStudentAccountById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT sa.*, s.first_name, s.last_name, s.student_number
             FROM student_accounts sa
             JOIN students s ON s.id = sa.student_id
             WHERE sa.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateStudentAccount(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE student_accounts SET account_status = :account_status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'account_status' => $data['account_status'],
            'id' => $id,
        ]);
    }

    public function deleteStudentAccount(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM student_accounts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function changeStudentPassword(int $id, string $newPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE student_accounts SET password = :password, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }
}
