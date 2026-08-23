<?php

namespace App\Services;

use App\Core\Database;

class CurriculumService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, d.code AS department_code, d.description AS department_description
             FROM curricula c
             LEFT JOIN departments d ON d.id = c.department_id
             ORDER BY c.curriculum'
        );
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, curriculum, department_id FROM curricula ORDER BY curriculum');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM curricula WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO curricula (curriculum, status, department_id, created_at, updated_at)
             VALUES (:curriculum, :status, :department_id, NOW(), NOW())'
        );
        $stmt->execute([
            'curriculum' => $data['curriculum'],
            'status' => $data['status'],
            'department_id' => $data['department_id'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE curricula SET curriculum = :curriculum, status = :status, department_id = :department_id, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'curriculum' => $data['curriculum'],
            'status' => $data['status'],
            'department_id' => $data['department_id'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM curricula WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $found = [];
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM prospectuses WHERE curriculum_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'prospectuses';
        } catch (\Throwable $e) {}
        return $found;
    }
}
