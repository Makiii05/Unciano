<?php

namespace App\Services;

use App\Core\Database;

class ProgramService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, d.code AS department_code, d.description AS department_description
             FROM programs p
             LEFT JOIN departments d ON d.id = p.department_id
             ORDER BY p.code'
        );
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description, department_id FROM programs ORDER BY code');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM programs WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare('SELECT id, code, description FROM programs WHERE department_id = ? ORDER BY code');
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }

    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM programs WHERE code = ? AND id != ? LIMIT 1');
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM programs WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO programs (code, description, status, department_id, created_at, updated_at)
             VALUES (:code, :description, :status, :department_id, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'status' => $data['status'],
            'department_id' => $data['department_id'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE programs SET code = :code, description = :description, status = :status, department_id = :department_id, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'status' => $data['status'],
            'department_id' => $data['department_id'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM programs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $checks = [
            'levels' => 'SELECT 1 FROM levels WHERE program_id = ? LIMIT 1',
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
