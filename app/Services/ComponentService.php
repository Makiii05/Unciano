<?php

namespace App\Services;

use App\Core\Database;

class ComponentService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, d.code AS department_code
             FROM components c
             LEFT JOIN departments d ON d.id = c.department_id
             ORDER BY d.code, c.code'
        );
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description, percentage, department_id FROM components ORDER BY code');
        return $stmt->fetchAll();
    }

    public function getByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare('SELECT id, code, description, percentage FROM components WHERE department_id = ? ORDER BY code');
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM components WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function codeExists(string $code, int $departmentId, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM components WHERE code = ? AND department_id = ? AND id != ? LIMIT 1');
            $stmt->execute([$code, $departmentId, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM components WHERE code = ? AND department_id = ? LIMIT 1');
            $stmt->execute([$code, $departmentId]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO components (code, description, percentage, department_id, created_at, updated_at)
             VALUES (:code, :description, :percentage, :department_id, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'percentage' => $data['percentage'],
            'department_id' => $data['department_id'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE components SET code = :code, description = :description, percentage = :percentage, department_id = :department_id, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'percentage' => $data['percentage'],
            'department_id' => $data['department_id'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM components WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $found = [];
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM grading_components WHERE component_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'grading_systems';
        } catch (\Throwable $e) {}
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM grade_column WHERE component_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'grade_columns';
        } catch (\Throwable $e) {}
        return $found;
    }
}
