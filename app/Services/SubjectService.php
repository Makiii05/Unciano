<?php

namespace App\Services;

use App\Core\Database;

class SubjectService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM subjects ORDER BY code');
        return $stmt->fetchAll();
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, code, description FROM subjects ORDER BY code');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM subjects WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM subjects WHERE code = ? AND id != ? LIMIT 1');
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM subjects WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO subjects (code, description, unit, lech, lecu, labh, labu, type, education_level, status, created_at, updated_at)
             VALUES (:code, :description, :unit, :lech, :lecu, :labh, :labu, :type, :education_level, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'unit' => $data['unit'],
            'lech' => $data['lech'],
            'lecu' => $data['lecu'],
            'labh' => $data['labh'],
            'labu' => $data['labu'],
            'type' => $data['type'],
            'education_level' => $data['education_level'] ?? 'college',
            'status' => $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE subjects SET code = :code, description = :description, unit = :unit, lech = :lech, lecu = :lecu, labh = :labh, labu = :labu, type = :type, education_level = :education_level, status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'code' => $data['code'],
            'description' => $data['description'],
            'unit' => $data['unit'],
            'lech' => $data['lech'],
            'lecu' => $data['lecu'],
            'labh' => $data['labh'],
            'labu' => $data['labu'],
            'type' => $data['type'],
            'education_level' => $data['education_level'] ?? 'college',
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM subjects WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $found = [];
        $checks = [
            'prerequisites' => 'SELECT 1 FROM subject_prerequisites WHERE subject_id = ? LIMIT 1',
            'prerequisite_of' => 'SELECT 1 FROM subject_prerequisites WHERE prerequisite_subject_id = ? LIMIT 1',
        ];
        foreach ($checks as $label => $sql) {
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);
                if ($stmt->fetch()) $found[] = $label;
            } catch (\Throwable $e) {}
        }
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM prospectuses WHERE subject_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'prospectuses';
        } catch (\Throwable $e) {}
        return $found;
    }

    // ---- Prerequisites ----

    public function getPrerequisites(int $subjectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT sp.id, sp.subject_id, sp.prerequisite_subject_id, s.code AS prereq_code, s.description AS prereq_description
             FROM subject_prerequisites sp
             JOIN subjects s ON s.id = sp.prerequisite_subject_id
             WHERE sp.subject_id = ?
             ORDER BY sp.id'
        );
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll();
    }

    public function searchPrerequisites(int $subjectId, string $query): array
    {
        $query = trim($query);
        if ($query === '') return [];

        // Exclude self and already assigned prerequisites
        $stmt = $this->db->prepare('SELECT prerequisite_subject_id FROM subject_prerequisites WHERE subject_id = ?');
        $stmt->execute([$subjectId]);
        $excluded = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $excluded[] = $subjectId;

        // Build placeholders
        $placeholders = implode(',', array_fill(0, count($excluded), '?'));
        $sql = "SELECT id, code, description FROM subjects WHERE id NOT IN ($placeholders) AND (code LIKE ? OR description LIKE ?) ORDER BY code LIMIT 10";
        $like = "%$query%";
        $params = array_merge($excluded, [$like, $like]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addPrerequisite(int $subjectId, int $prereqId): void
    {
        if ($subjectId === $prereqId) {
            throw new \RuntimeException('A subject cannot be a prerequisite of itself.');
        }
        $stmt = $this->db->prepare('SELECT 1 FROM subject_prerequisites WHERE subject_id = ? AND prerequisite_subject_id = ? LIMIT 1');
        $stmt->execute([$subjectId, $prereqId]);
        if ($stmt->fetch()) {
            throw new \RuntimeException('Prerequisite already added.');
        }
        // Ensure prereq exists
        $stmt = $this->db->prepare('SELECT id FROM subjects WHERE id = ? LIMIT 1');
        $stmt->execute([$prereqId]);
        if (!$stmt->fetch()) {
            throw new \RuntimeException('Prerequisite subject not found.');
        }
        $stmt = $this->db->prepare('INSERT INTO subject_prerequisites (subject_id, prerequisite_subject_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmt->execute([$subjectId, $prereqId]);
    }

    public function removePrerequisite(int $subjectId, int $prereqRowId): void
    {
        // $prereqRowId is the id of subject_prerequisites row, ensure it belongs to subject
        $stmt = $this->db->prepare('SELECT id FROM subject_prerequisites WHERE id = ? AND subject_id = ? LIMIT 1');
        $stmt->execute([$prereqRowId, $subjectId]);
        if (!$stmt->fetch()) {
            throw new \RuntimeException('Prerequisite not found for this subject.');
        }
        $stmt = $this->db->prepare('DELETE FROM subject_prerequisites WHERE id = ?');
        $stmt->execute([$prereqRowId]);
    }
}
