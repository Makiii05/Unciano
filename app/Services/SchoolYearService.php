<?php

namespace App\Services;

use App\Core\Database;

class SchoolYearService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM school_years ORDER BY start_year DESC, code DESC');
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description, start_year, end_year FROM school_years ORDER BY start_year DESC');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM school_years WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM school_years WHERE code = ? AND id != ? LIMIT 1');
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM school_years WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO school_years (code, description, start_year, end_year, status, created_at, updated_at)
             VALUES (:code, :description, :start_year, :end_year, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'start_year' => $data['start_year'],
            'end_year' => $data['end_year'],
            'status' => $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE school_years SET code = :code, description = :description, start_year = :start_year, end_year = :end_year, status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'start_year' => $data['start_year'],
            'end_year' => $data['end_year'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM school_years WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $stmt = $this->db->prepare('SELECT 1 FROM academic_terms WHERE school_year_id = ? LIMIT 1');
        $stmt->execute([$id]);
        if ($stmt->fetch()) {
            return ['academic_terms'];
        }
        return [];
    }
}
