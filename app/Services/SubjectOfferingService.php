<?php

namespace App\Services;

use App\Core\Database;

class SubjectOfferingService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getByTerm(int $academicTermId): array
    {
        $stmt = $this->db->prepare(
            'SELECT so.*, s.code AS subject_code, s.description AS subject_description, s.unit,
                    p.code AS program_code, l.code AS level_code, l.description AS level_description,
                    gs.description AS grading_description
             FROM subject_offerings so
             JOIN subjects s ON s.id = so.subject_id
             JOIN programs p ON p.id = so.program_id
             LEFT JOIN levels l ON l.id = so.level_id
             LEFT JOIN grading_systems gs ON gs.id = so.grading_id
             WHERE so.academic_term_id = ?
             ORDER BY so.code'
        );
        $stmt->execute([$academicTermId]);
        return $stmt->fetchAll();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT so.*, s.code AS subject_code, s.description AS subject_description, at.code AS term_code
             FROM subject_offerings so
             JOIN subjects s ON s.id = so.subject_id
             JOIN academic_terms at ON at.id = so.academic_term_id
             ORDER BY so.created_at DESC LIMIT 100'
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM subject_offerings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createFromProspectus(array $data): array
    {
        // data: academic_term_id, prospectus_id, grading_id, class_size, department_id
        $stmt = $this->db->prepare('SELECT * FROM prospectuses WHERE id = ? LIMIT 1');
        $stmt->execute([$data['prospectus_id']]);
        $prospectus = $stmt->fetch();
        if (!$prospectus) {
            throw new \RuntimeException('Prospectus not found.');
        }
        $subjectId = (int) $prospectus['subject_id'];
        $levelId = (int) $prospectus['level_id'];

        $stmt = $this->db->prepare('SELECT * FROM subjects WHERE id = ? LIMIT 1');
        $stmt->execute([$subjectId]);
        $subject = $stmt->fetch();
        if (!$subject) throw new \RuntimeException('Subject not found.');

        $stmt = $this->db->prepare('SELECT * FROM levels WHERE id = ? LIMIT 1');
        $stmt->execute([$levelId]);
        $level = $stmt->fetch();
        if (!$level) throw new \RuntimeException('Level not found.');

        $stmt = $this->db->prepare('SELECT * FROM programs WHERE id = ? LIMIT 1');
        $stmt->execute([$level['program_id']]);
        $program = $stmt->fetch();
        if (!$program) throw new \RuntimeException('Program not found.');

        $departmentId = (int) $data['department_id'];

        return $this->createOffering($data, $subject, $program, $level, (int) $data['academic_term_id'], $departmentId);
    }

    public function createFromSearch(array $data): array
    {
        $stmt = $this->db->prepare('SELECT * FROM subjects WHERE id = ? LIMIT 1');
        $stmt->execute([$data['subject_id']]);
        $subject = $stmt->fetch();
        if (!$subject) throw new \RuntimeException('Subject not found.');

        $stmt = $this->db->prepare('SELECT * FROM programs WHERE id = ? LIMIT 1');
        $stmt->execute([$data['program_id']]);
        $program = $stmt->fetch();
        if (!$program) throw new \RuntimeException('Program not found.');

        $stmt = $this->db->prepare('SELECT * FROM levels WHERE id = ? LIMIT 1');
        $stmt->execute([$data['level_id']]);
        $level = $stmt->fetch();
        if (!$level) throw new \RuntimeException('Level not found.');

        $departmentId = (int) $data['department_id'];

        return $this->createOffering($data, $subject, $program, $level, (int) $data['academic_term_id'], $departmentId);
    }

    private function createOffering(array $data, array $subject, array $program, array $level, int $academicTermId, int $departmentId): array
    {
        $code = $this->generateCode($program, $subject, $level, $academicTermId);
        $description = $subject['description'];
        $classSize = isset($data['class_size']) && $data['class_size'] !== '' ? (int) $data['class_size'] : 40;
        if ($classSize < 1) $classSize = 40;
        if ($classSize > 500) $classSize = 500;

        $stmt = $this->db->prepare(
            'INSERT INTO subject_offerings (academic_term_id, subject_id, department_id, program_id, level_id, grading_id, code, description, class_size, created_at, updated_at)
             VALUES (:academic_term_id, :subject_id, :department_id, :program_id, :level_id, :grading_id, :code, :description, :class_size, NOW(), NOW())'
        );
        $stmt->execute([
            'academic_term_id' => $academicTermId,
            'subject_id' => $subject['id'],
            'department_id' => $departmentId,
            'program_id' => $program['id'],
            'level_id' => $level['id'],
            'grading_id' => $data['grading_id'] ?? null,
            'code' => $code,
            'description' => $description,
            'class_size' => $classSize,
        ]);
        $id = (int) $this->db->lastInsertId();
        $stmt = $this->db->prepare('SELECT * FROM subject_offerings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        // Attach subject for response
        $row['subject_code'] = $subject['code'];
        $row['subject_description'] = $subject['description'];
        return $row;
    }

    private function generateCode(array $program, array $subject, array $level, int $academicTermId): string
    {
        $year = '1';
        if (!empty($level['code'])) {
            if (preg_match('/\d+/', $level['code'], $m)) {
                $year = $m[0];
            } elseif (isset($level['order'])) {
                $year = (string) ((int) $level['order'] + 1);
            }
        } elseif (isset($level['order'])) {
            $year = (string) ((int) $level['order'] + 1);
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM subject_offerings WHERE academic_term_id = ? AND program_id = ? AND level_id = ? AND subject_id = ?');
        $stmt->execute([$academicTermId, $program['id'], $level['id'], $subject['id']]);
        $count = (int) $stmt->fetchColumn();
        $letter = chr(65 + min($count, 25)); // A-Z

        return $program['code'] . '-' . $subject['code'] . '-' . $year . $letter;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM subject_offerings WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $found = [];
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM enlistments WHERE subject_offering_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'enlistments';
        } catch (\Throwable $e) {}
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM teacher_offerings WHERE offering_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'teacher_offerings';
        } catch (\Throwable $e) {}
        return $found;
    }
}
