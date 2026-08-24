<?php

namespace App\Services;

use App\Core\Database;

class EnlistmentService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getEnlistmentsByTerm(int $studentId, int $termId): array
    {
        $stmt = $this->db->prepare("SELECT e.*, e.id AS enlistment_id, e.final_grade, so.code AS offering_code, so.description AS offering_description,
                so.program_id, so.level_id, s.code AS subject_code, s.description AS subject_description, s.unit,
                p.code AS program_code, p.description AS program_description,
                l.code AS level_code, l.description AS level_description
            FROM enlistments e
            JOIN subject_offerings so ON so.id = e.subject_offering_id
            LEFT JOIN subjects s ON s.id = so.subject_id
            LEFT JOIN programs p ON p.id = so.program_id
            LEFT JOIN levels l ON l.id = so.level_id
            WHERE e.student_id = ? AND e.academic_term_id = ? ORDER BY e.id");
        $stmt->execute([$studentId, $termId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['subjectOffering'] = [
                'id' => $r['subject_offering_id'],
                'code' => $r['offering_code'],
                'description' => $r['offering_description'],
                'program' => $r['program_code'] ? ['code' => $r['program_code'], 'description' => $r['program_description']] : null,
                'level' => $r['level_code'] ? ['code' => $r['level_code'], 'description' => $r['level_description']] : null,
                'subject' => ['code' => $r['subject_code'], 'description' => $r['subject_description'], 'unit' => $r['unit']],
            ];
            $r['subject_offering'] = $r['subjectOffering'];
        }
        return $rows;
    }

    public function getOfferingsForTerm(int $termId, int $deptId, int $studentId): array
    {
        $enlistedStmt = $this->db->prepare('SELECT subject_offering_id FROM enlistments WHERE student_id = ? AND academic_term_id = ?');
        $enlistedStmt->execute([$studentId, $termId]);
        $enlistedIds = array_column($enlistedStmt->fetchAll(), 'subject_offering_id');
        $notIn = '';
        $params = [$termId, $deptId];
        if (!empty($enlistedIds)) {
            $placeholders = implode(',', array_fill(0, count($enlistedIds), '?'));
            $notIn = " AND so.id NOT IN ({$placeholders})";
            $params = array_merge($params, $enlistedIds);
        }
        $sql = "SELECT so.*, so.id AS offering_id, s.code AS subject_code, s.description AS subject_description, s.unit,
                p.code AS program_code, p.description AS program_description,
                l.code AS level_code, l.description AS level_description
            FROM subject_offerings so
            JOIN subjects s ON s.id = so.subject_id
            LEFT JOIN programs p ON p.id = so.program_id
            LEFT JOIN levels l ON l.id = so.level_id
            WHERE so.academic_term_id = ? AND so.department_id = ? {$notIn} ORDER BY so.code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['subject'] = ['id' => $r['subject_id'], 'code' => $r['subject_code'], 'description' => $r['subject_description'], 'unit' => $r['unit']];
            $r['program'] = $r['program_code'] ? ['id' => $r['program_id'], 'code' => $r['program_code']] : null;
            $r['level'] = $r['level_code'] ? ['id' => $r['level_id'], 'code' => $r['level_code'], 'description' => $r['level_description']] : null;
        }
        return $rows;
    }

    public function getSectionsByTerm(int $termId, int $deptId): array
    {
        $stmt = $this->db->prepare("SELECT so.code, p.code AS program_code FROM subject_offerings so LEFT JOIN programs p ON p.id=so.program_id WHERE so.academic_term_id=? AND so.department_id=? ORDER BY so.code");
        $stmt->execute([$termId, $deptId]);
        $rows = $stmt->fetchAll();
        $sections = [];
        foreach ($rows as $r) {
            $section = $this->sectionFromCode($r['code']);
            if ($section === null) continue;
            if (!isset($sections[$section])) {
                $sections[$section] = ['section' => $section, 'offerings_count' => 0, 'program_code' => $r['program_code']];
            }
            $sections[$section]['offerings_count']++;
        }
        ksort($sections);
        return array_values($sections);
    }

    public function enlist(array $data): array
    {
        $stmt = $this->db->prepare('SELECT * FROM subject_offerings WHERE id = ? LIMIT 1');
        $stmt->execute([$data['subject_offering_id']]);
        $offering = $stmt->fetch();
        if (!$offering || (int)$offering['academic_term_id'] !== (int)$data['academic_term_id']) {
            throw new \RuntimeException('The selected subject offering does not belong to the selected academic term.');
        }
        $chk = $this->db->prepare('SELECT id FROM enlistments WHERE student_id=? AND academic_term_id=? AND subject_offering_id=? LIMIT 1');
        $chk->execute([$data['student_id'], $data['academic_term_id'], $data['subject_offering_id']]);
        if ($chk->fetch()) {
            throw new \RuntimeException('This subject is already enlisted by the student for the selected academic term.');
        }
        $ins = $this->db->prepare('INSERT INTO enlistments (student_id, academic_term_id, subject_offering_id, created_at, updated_at) VALUES (?,?,?,NOW(),NOW())');
        $ins->execute([$data['student_id'], $data['academic_term_id'], $data['subject_offering_id']]);
        $id = (int)$this->db->lastInsertId();
        $stmt = $this->db->prepare("SELECT e.*, so.code AS offering_code, s.description AS subject_description, s.unit FROM enlistments e JOIN subject_offerings so ON so.id=e.subject_offering_id LEFT JOIN subjects s ON s.id=so.subject_id WHERE e.id=? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $row['subject_offering'] = ['id'=>$row['subject_offering_id'],'code'=>$row['offering_code'],'subject'=>['description'=>$row['subject_description'],'unit'=>$row['unit']]];
        $changed = $this->maybeSetOldStudentType((int)$data['student_id'], (int)$data['academic_term_id']);
        return ['enlistment'=>$row, 'student_type_changed'=>$changed, 'student_type'=>$this->getStudentType((int)$data['student_id'])];
    }

    public function enlistBySection(array $data): array
    {
        $deptId = (int)$data['department_id'];
        $termId = (int)$data['academic_term_id'];
        $studentId = (int)$data['student_id'];
        $section = $data['section'];
        $stmt = $this->db->prepare('SELECT so.* FROM subject_offerings so WHERE so.academic_term_id=? AND so.department_id=?');
        $stmt->execute([$termId, $deptId]);
        $offerings = $stmt->fetchAll();
        $enlistedStmt = $this->db->prepare('SELECT subject_offering_id FROM enlistments WHERE student_id=? AND academic_term_id=?');
        $enlistedStmt->execute([$studentId, $termId]);
        $enlistedIds = array_column($enlistedStmt->fetchAll(), 'subject_offering_id');
        $created = [];
        foreach ($offerings as $off) {
            if ($this->sectionFromCode($off['code']) !== $section) continue;
            if (in_array($off['id'], $enlistedIds, true)) continue;
            $ins = $this->db->prepare('INSERT INTO enlistments (student_id, academic_term_id, subject_offering_id, created_at, updated_at) VALUES (?,?,?,NOW(),NOW())');
            $ins->execute([$studentId, $termId, $off['id']]);
            $id = (int)$this->db->lastInsertId();
            $stmt2 = $this->db->prepare("SELECT e.*, so.code AS offering_code, s.description AS subject_description, s.unit FROM enlistments e JOIN subject_offerings so ON so.id=e.subject_offering_id LEFT JOIN subjects s ON s.id=so.subject_id WHERE e.id=? LIMIT 1");
            $stmt2->execute([$id]);
            $row = $stmt2->fetch();
            $row['subject_offering'] = ['id'=>$row['subject_offering_id'],'code'=>$row['offering_code'],'subject'=>['description'=>$row['subject_description'],'unit'=>$row['unit']]];
            $created[] = $row;
        }
        if (empty($created)) {
            throw new \RuntimeException('All subjects in this section are already enlisted by the student.');
        }
        $changed = $this->maybeSetOldStudentType($studentId, $termId);
        return ['enlistments'=>$created, 'student_type_changed'=>$changed, 'student_type'=>$this->getStudentType($studentId)];
    }

    public function remove(int $enlistmentId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM enlistments WHERE id=?');
        return $stmt->execute([$enlistmentId]);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM enlistments WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function maybeSetOldStudentType(int $studentId, int $termId): bool
    {
        $stmt = $this->db->prepare('SELECT student_type FROM students WHERE id=? LIMIT 1');
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        if (!$row || $row['student_type']==='old') return false;
        $chk = $this->db->prepare('SELECT 1 FROM enlistments WHERE student_id=? AND academic_term_id != ? LIMIT 1');
        $chk->execute([$studentId, $termId]);
        if (!$chk->fetch()) return false;
        $upd = $this->db->prepare("UPDATE students SET student_type='old', updated_at=NOW() WHERE id=?");
        $upd->execute([$studentId]);
        return true;
    }

    private function getStudentType(int $studentId): ?string
    {
        $stmt = $this->db->prepare('SELECT student_type FROM students WHERE id=? LIMIT 1');
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        return $row['student_type'] ?? null;
    }

    private function sectionFromCode(?string $code): ?string
    {
        if ($code===null || trim($code)==='') return null;
        $parts = explode('-', $code);
        if (count($parts)<3) return null;
        $yearLetter = end($parts);
        if (!preg_match('/^\d+[A-Z]$/', $yearLetter)) return null;
        return $parts[0] . '-' . $yearLetter;
    }
}
