<?php

namespace App\Services;

use App\Core\Database;

class LevelService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT l.*, p.code AS program_code, p.description AS program_description
             FROM levels l
             LEFT JOIN programs p ON p.id = l.program_id
             ORDER BY l.program_id, l.order, l.code'
        );
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description, program_id, `order` FROM levels ORDER BY program_id, `order`');
        return $stmt->fetchAll();
    }

    public function getByProgram(int $programId): array
    {
        $stmt = $this->db->prepare('SELECT id, code, description, program_id, `order` FROM levels WHERE program_id = ? ORDER BY `order`, code');
        $stmt->execute([$programId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM levels WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO levels (code, description, program_id, `order`, created_at, updated_at)
             VALUES (:code, :description, :program_id, :order_val, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'program_id' => $data['program_id'],
            'order_val' => $data['order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE levels SET code = :code, description = :description, program_id = :program_id, `order` = :order_val, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'program_id' => $data['program_id'],
            'order_val' => $data['order'] ?? 0,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM levels WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        // Levels may be referenced by prospectuses, students, subject_offerings etc.
        // For core academic block, check prospectuses (if table exists) but don't fail if missing.
        $found = [];
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM prospectuses WHERE level_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'prospectuses';
        } catch (\Throwable $e) {}
        return $found;
    }
}
