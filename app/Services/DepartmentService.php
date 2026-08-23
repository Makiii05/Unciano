<?php

namespace App\Services;

use App\Core\Database;

class DepartmentService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM departments ORDER BY code');
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description FROM departments ORDER BY code');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM departments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM departments WHERE code = ? AND id != ? LIMIT 1');
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM departments WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO departments (code, description, education_level, status, created_at, updated_at)
             VALUES (:code, :description, :education_level, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'education_level' => $data['education_level'] ?? 'college',
            'status' => $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE departments SET code = :code, description = :description, education_level = :education_level, status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'education_level' => $data['education_level'] ?? 'college',
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM departments WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $checks = [
            'programs' => 'SELECT 1 FROM programs WHERE department_id = ? LIMIT 1',
            'curricula' => 'SELECT 1 FROM curricula WHERE department_id = ? LIMIT 1',
            'academic_terms' => 'SELECT 1 FROM academic_terms WHERE department_id = ? LIMIT 1',
        ];
        $found = [];
        foreach ($checks as $label => $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            if ($stmt->fetch()) {
                $found[] = $label;
            }
        }
        return $found;
    }
}
