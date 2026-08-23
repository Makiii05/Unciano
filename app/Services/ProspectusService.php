<?php

namespace App\Services;

use App\Core\Database;

class ProspectusService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, 
                    c.curriculum AS curriculum_name, c.department_id AS curriculum_department_id,
                    d.code AS curriculum_department_code,
                    l.code AS level_code, l.description AS level_description, l.program_id,
                    pr.code AS program_code,
                    at.code AS term_code, at.description AS term_description,
                    sy.code AS school_year_code,
                    s.code AS subject_code, s.description AS subject_description, s.unit
             FROM prospectuses p
             LEFT JOIN curricula c ON c.id = p.curriculum_id
             LEFT JOIN departments d ON d.id = c.department_id
             LEFT JOIN levels l ON l.id = p.level_id
             LEFT JOIN programs pr ON pr.id = l.program_id
             LEFT JOIN academic_terms at ON at.id = p.term_id
             LEFT JOIN school_years sy ON sy.id = at.school_year_id
             LEFT JOIN subjects s ON s.id = p.subject_id
             ORDER BY p.curriculum_id, l.`order`, l.id, at.start_date, s.code'
        );
        return $stmt->fetchAll();
    }

    public function getByCurriculum(int $curriculumId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, 
                    c.curriculum AS curriculum_name,
                    l.code AS level_code, l.description AS level_description, l.program_id, l.`order` AS level_order,
                    pr.code AS program_code,
                    at.code AS term_code, at.description AS term_description, at.start_date,
                    sy.code AS school_year_code,
                    s.code AS subject_code, s.description AS subject_description, s.unit
             FROM prospectuses p
             LEFT JOIN curricula c ON c.id = p.curriculum_id
             LEFT JOIN levels l ON l.id = p.level_id
             LEFT JOIN programs pr ON pr.id = l.program_id
             LEFT JOIN academic_terms at ON at.id = p.term_id
             LEFT JOIN school_years sy ON sy.id = at.school_year_id
             LEFT JOIN subjects s ON s.id = p.subject_id
             WHERE p.curriculum_id = ?
             ORDER BY l.`order`, l.id, at.start_date, s.code'
        );
        $stmt->execute([$curriculumId]);
        $rows = $stmt->fetchAll();
        // Attach prerequisites for each subject for display
        foreach ($rows as &$row) {
            if (!empty($row['subject_id'])) {
                $stmt2 = $this->db->prepare(
                    'SELECT s.code FROM subject_prerequisites sp JOIN subjects s ON s.id = sp.prerequisite_subject_id WHERE sp.subject_id = ? ORDER BY s.code'
                );
                $stmt2->execute([$row['subject_id']]);
                $prereqs = $stmt2->fetchAll(\PDO::FETCH_COLUMN);
                $row['prerequisites'] = $prereqs;
                $row['prerequisites_display'] = empty($prereqs) ? '—' : implode(', ', $prereqs);
            } else {
                $row['prerequisites'] = [];
                $row['prerequisites_display'] = '—';
            }
        }
        return $rows;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM prospectuses WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existsComposite(int $curriculumId, int $levelId, int $termId, int $subjectId, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM prospectuses WHERE curriculum_id = ? AND level_id = ? AND term_id = ? AND subject_id = ? AND id != ? LIMIT 1');
            $stmt->execute([$curriculumId, $levelId, $termId, $subjectId, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM prospectuses WHERE curriculum_id = ? AND level_id = ? AND term_id = ? AND subject_id = ? LIMIT 1');
            $stmt->execute([$curriculumId, $levelId, $termId, $subjectId]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospectuses (curriculum_id, level_id, term_id, subject_id, status, created_at, updated_at)
             VALUES (:curriculum_id, :level_id, :term_id, :subject_id, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'curriculum_id' => $data['curriculum_id'],
            'level_id' => $data['level_id'],
            'term_id' => $data['term_id'],
            'subject_id' => $data['subject_id'],
            'status' => $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE prospectuses SET curriculum_id = :curriculum_id, level_id = :level_id, term_id = :term_id, subject_id = :subject_id, status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'curriculum_id' => $data['curriculum_id'],
            'level_id' => $data['level_id'],
            'term_id' => $data['term_id'],
            'subject_id' => $data['subject_id'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM prospectuses WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
