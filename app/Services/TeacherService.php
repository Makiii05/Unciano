<?php

namespace App\Services;

use App\Core\Database;

class TeacherService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function searchTeachers(?string $term): array
    {
        $sql = "SELECT id, code, first_name, middle_name, last_name, email, status FROM teachers WHERE status = 'active'";
        $params = [];
        if ($term !== null && trim($term) !== '') {
            $term = trim($term);
            $like = "%{$term}%";
            $sql .= " AND (code LIKE ? OR first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?)";
            $params = [$like, $like, $like, $like];
        }
        $sql .= " ORDER BY code LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teachers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
