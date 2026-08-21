<?php

namespace App\Services;

use App\Core\Database;

class AuthService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function loginStaff(string $email, string $password, string $type): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = ? AND type = ? AND status = ? LIMIT 1'
        );
        $stmt->execute([$email, $type, 'active']);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    public function loginStudent(string $studentNumber, string $password): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, sa.id as account_id, sa.password, sa.account_status
             FROM students s
             JOIN student_accounts sa ON sa.student_id = s.id
             WHERE s.student_number = ? LIMIT 1'
        );
        $stmt->execute([$studentNumber]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        if (!password_verify($password, $row['password'])) {
            return null;
        }

        if ($row['account_status'] !== 'on') {
            return ['_disabled' => true];
        }

        return $row;
    }

    public function loginTeacher(string $code, string $password): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, ta.id as account_id, ta.password, ta.status as account_status
             FROM teachers t
             JOIN teacher_accounts ta ON ta.teacher_id = t.id
             WHERE t.code = ? LIMIT 1'
        );
        $stmt->execute([$code]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        if (!password_verify($password, $row['password'])) {
            return null;
        }

        if ($row['account_status'] !== 'open') {
            return ['_disabled' => true];
        }

        return $row;
    }

    public function logout(): void
    {
        session_regenerate_id(true);
        session_destroy();
    }
}
