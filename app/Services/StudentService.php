<?php

namespace App\Services;

use App\Core\Database;

class StudentService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function searchInDepartment(int $departmentId, ?string $term): array
    {
        if ($term === null || trim($term) === '') {
            return [];
        }
        $term = trim($term);
        $like = "%{$term}%";
        $stmt = $this->db->prepare("SELECT s.id, s.student_number, s.last_name, s.first_name, s.middle_name, s.sex, s.status, s.student_type, s.department_id, s.program_id, s.level_id,
                d.code AS department_code, d.description AS department_description,
                p.code AS program_code, p.description AS program_description,
                l.code AS level_code, l.description AS level_description
            FROM students s
            LEFT JOIN departments d ON d.id=s.department_id
            LEFT JOIN programs p ON p.id=s.program_id
            LEFT JOIN levels l ON l.id=s.level_id
            WHERE s.department_id = ?
              AND (s.student_number LIKE ? OR s.last_name LIKE ? OR s.first_name LIKE ? OR s.middle_name LIKE ? OR d.code LIKE ? OR d.description LIKE ? OR p.code LIKE ? OR p.description LIKE ? OR l.code LIKE ? OR l.description LIKE ?)
            ORDER BY s.student_number LIMIT 50");
        $stmt->execute([$departmentId, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like]);
        $rows = $stmt->fetchAll();
        // Nest for JS compatibility (Laravel returns department/program/level objects)
        foreach ($rows as &$r) {
            $r['department'] = $r['department_code'] ? ['id' => $r['department_id'], 'code' => $r['department_code'], 'description' => $r['department_description']] : null;
            $r['program'] = $r['program_code'] ? ['id' => $r['program_id'], 'code' => $r['program_code'], 'description' => $r['program_description']] : null;
            $r['level'] = $r['level_code'] ? ['id' => $r['level_id'], 'code' => $r['level_code'], 'description' => $r['level_description']] : null;
        }
        return $rows;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM students WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateDetails(int $studentId, array $data): bool
    {
        $fields = [];
        $params = [];
        if (array_key_exists('level_id', $data)) {
            $fields[] = 'level_id = :level_id';
            $params['level_id'] = $data['level_id'] !== '' ? (int) $data['level_id'] : null;
        }
        if (array_key_exists('status', $data)) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
        }
        if (empty($fields)) return false;
        $params['id'] = $studentId;
        $sql = 'UPDATE students SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
