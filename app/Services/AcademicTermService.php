<?php

namespace App\Services;

use App\Core\Database;

class AcademicTermService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT at.*, 
                    sy.code AS school_year_code, sy.description AS school_year_description,
                    d.code AS department_code
             FROM academic_terms at
             LEFT JOIN school_years sy ON sy.id = at.school_year_id
             LEFT JOIN departments d ON d.id = at.department_id
             ORDER BY at.start_date DESC, at.code'
        );
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description FROM academic_terms ORDER BY start_date DESC');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM academic_terms WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO academic_terms (code, description, type, department_id, school_year_id, start_date, end_date, status, created_at, updated_at)
             VALUES (:code, :description, :type, :department_id, :school_year_id, :start_date, :end_date, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'type' => $data['type'] ?? 'semester',
            'department_id' => $data['department_id'] ?? null,
            'school_year_id' => $data['school_year_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE academic_terms SET code = :code, description = :description, type = :type, department_id = :department_id, school_year_id = :school_year_id, start_date = :start_date, end_date = :end_date, status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'type' => $data['type'] ?? 'semester',
            'department_id' => $data['department_id'] ?? null,
            'school_year_id' => $data['school_year_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM academic_terms WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $found = [];
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM prospectuses WHERE term_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'prospectuses';
        } catch (\Throwable $e) {}

        // Could also check subject_offerings, enlistments etc., but keep minimal for core
        return $found;
    }
}
