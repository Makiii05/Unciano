<?php

namespace App\Services;

use App\Core\Database;

class TeacherOfferingService
{
    private \PDO $db;
    public function __construct(){ $this->db = Database::connection(); }

    public function getTeachersWithLoadings(int $deptId): array
    {
        $stmt = $this->db->prepare("SELECT t.*, t.code AS teacher_code, COUNT(to2.id) AS department_loadings_count
            FROM teachers t
            JOIN teacher_offerings to2 ON to2.teacher_id = t.id
            JOIN subject_offerings so ON so.id = to2.offering_id AND so.department_id = ?
            GROUP BY t.id ORDER BY t.code");
        $stmt->execute([$deptId]);
        $rows = $stmt->fetchAll();
        // Fallback if no loadings via join, also check distinct
        if (empty($rows)) {
            // Try alternative: teachers with any loadings in dept
            $stmt2 = $this->db->prepare("SELECT t.*, COUNT(to3.id) as department_loadings_count FROM teachers t JOIN teacher_offerings to3 ON to3.teacher_id=t.id JOIN subject_offerings so2 ON so2.id=to3.offering_id WHERE so2.department_id=? GROUP BY t.id ORDER BY t.code");
            $stmt2->execute([$deptId]);
            $rows = $stmt2->fetchAll();
        }
        return $rows;
    }

    public function getByTeacherAndTerm(int $teacherId, int $termId, int $deptId): array
    {
        $stmt = $this->db->prepare("SELECT to2.*, to2.id AS loading_id, so.code AS offering_code, so.description AS offering_description,
                s.description AS subject_description, s.code AS subject_code,
                p.code AS program_code, p.description AS program_description,
                l.description AS level_description
            FROM teacher_offerings to2
            JOIN subject_offerings so ON so.id = to2.offering_id AND so.department_id = ?
            LEFT JOIN subjects s ON s.id = so.subject_id
            LEFT JOIN programs p ON p.id = so.program_id
            LEFT JOIN levels l ON l.id = so.level_id
            WHERE to2.teacher_id = ? AND to2.academic_term_id = ? ORDER BY to2.id");
        $stmt->execute([$deptId, $teacherId, $termId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['offering'] = [
                'id' => $r['offering_id'],
                'code' => $r['offering_code'],
                'description' => $r['offering_description'],
                'subject' => ['code'=>$r['subject_code'],'description'=>$r['subject_description']],
                'program' => $r['program_code'] ? ['code'=>$r['program_code'],'description'=>$r['program_description']] : null,
                'level' => $r['level_description'] ? ['description'=>$r['level_description']] : null,
            ];
        }
        return $rows;
    }

    public function getOfferingsByTerm(int $termId, int $deptId, ?int $teacherId=null): array
    {
        $params = [$termId, $deptId];
        $exclude = '';
        if ($teacherId) {
            $exclude = " AND so.id NOT IN (SELECT offering_id FROM teacher_offerings WHERE teacher_id=? AND academic_term_id=?)";
            $params[] = $teacherId;
            $params[] = $termId;
        }
        $sql = "SELECT so.*, so.id AS offering_id, s.code AS subject_code, s.description AS subject_description,
                p.code AS program_code, p.description AS program_description,
                l.code AS level_code, l.description AS level_description
            FROM subject_offerings so
            JOIN subjects s ON s.id=so.subject_id
            LEFT JOIN programs p ON p.id=so.program_id
            LEFT JOIN levels l ON l.id=so.level_id
            WHERE so.academic_term_id=? AND so.department_id=? {$exclude} ORDER BY so.code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['subject'] = ['id'=>$r['subject_id'],'code'=>$r['subject_code'],'description'=>$r['subject_description']];
            $r['program'] = $r['program_code'] ? ['code'=>$r['program_code'],'description'=>$r['program_description']] : null;
            $r['level'] = $r['level_code'] ? ['code'=>$r['level_code'],'description'=>$r['level_description']] : null;
        }
        return $rows;
    }

    public function create(array $data): array
    {
        $this->db->beginTransaction();
        try {
            $chk = $this->db->prepare('SELECT id FROM teacher_offerings WHERE offering_id=? AND academic_term_id=? LIMIT 1');
            $chk->execute([$data['offering_id'], $data['academic_term_id']]);
            $existing = $chk->fetch();
            if ($existing) {
                $del = $this->db->prepare('DELETE FROM teacher_offerings WHERE id=?');
                $del->execute([$existing['id']]);
            }
            $ins = $this->db->prepare('INSERT INTO teacher_offerings (teacher_id, offering_id, academic_term_id, status, grade_input_status, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())');
            $ins->execute([
                $data['teacher_id'],
                $data['offering_id'],
                $data['academic_term_id'],
                $data['status'] ?? 'active',
                $data['grade_input_status'] ?? 'unlocked',
            ]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            $stmt = $this->db->prepare("SELECT to2.*, so.code AS offering_code, s.description AS subject_description FROM teacher_offerings to2 JOIN subject_offerings so ON so.id=to2.offering_id LEFT JOIN subjects s ON s.id=so.subject_id WHERE to2.id=? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            $row['offering'] = ['id'=>$row['offering_id'],'code'=>$row['offering_code'],'subject'=>['description'=>$row['subject_description']]];
            return $row;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM teacher_offerings WHERE id=?');
        return $stmt->execute([$id]);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teacher_offerings WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getEnrolledStudents(int $offeringId, int $termId): array
    {
        // For Class List under teacher loading
        $stmt = $this->db->prepare("SELECT s.id, s.student_number, s.last_name, s.first_name, s.middle_name, s.sex FROM students s JOIN enlistments e ON e.student_id=s.id WHERE e.subject_offering_id=? AND e.academic_term_id=? ORDER BY s.last_name, s.first_name");
        $stmt->execute([$offeringId, $termId]);
        return $stmt->fetchAll();
    }

    public function getGradeSheetData(int $teacherOfferingId): array
    {
        $stmt = $this->db->prepare("SELECT to2.*, so.code AS offering_code, so.description AS offering_description, so.subject_id, so.program_id, so.level_id, so.academic_term_id, t.code AS teacher_code, t.first_name, t.last_name, s.code AS subject_code, s.description AS subject_description FROM teacher_offerings to2 JOIN subject_offerings so ON so.id=to2.offering_id JOIN teachers t ON t.id=to2.teacher_id LEFT JOIN subjects s ON s.id=so.subject_id WHERE to2.id=? LIMIT 1");
        $stmt->execute([$teacherOfferingId]);
        $offering = $stmt->fetch();
        if (!$offering) throw new \RuntimeException('Teacher offering not found.');

        $students = $this->getEnrolledStudents((int)$offering['offering_id'], (int)$offering['academic_term_id']);
        // Hardcode 4 periods per user answer
        $periods = ['prelim','midterm','prefinal','final'];
        // Fetch grades per period if grade table exists
        $groups = [];
        foreach (['male','female'] as $sex) {
            $filtered = array_values(array_filter($students, fn($s)=>strtolower($s['sex']??'')=== $sex));
            $list = [];
            foreach ($filtered as $s) {
                $gradeVal = null;
                try {
                    $gstmt = $this->db->prepare("SELECT period_grade, initial_grade FROM grade WHERE teacher_offering_id=? AND student_id=? AND period=? AND status='approved' LIMIT 1");
                    // Try last period final first, fallback any approved
                    $gstmt->execute([$teacherOfferingId, $s['id'], end($periods)]);
                    $g = $gstmt->fetch();
                    if (!$g) {
                        $gstmt2 = $this->db->prepare("SELECT period_grade, initial_grade FROM grade WHERE teacher_offering_id=? AND student_id=? AND status='approved' ORDER BY FIELD(period,'prelim','midterm','prefinal','final') DESC LIMIT 1");
                        $gstmt2->execute([$teacherOfferingId, $s['id']]);
                        $g = $gstmt2->fetch();
                    }
                    $gradeVal = $g ? ($g['period_grade'] ?? $g['initial_grade']) : null;
                } catch (\Throwable $e) {}
                $list[] = [
                    'number' => $s['student_number'],
                    'name' => trim($s['last_name'].', '.$s['first_name'].' '.($s['middle_name'] ? substr($s['middle_name'],0,1).'.' : '')),
                    'grade' => $gradeVal,
                    'sex' => $s['sex'],
                ];
            }
            $groups[$sex] = ['label'=>ucfirst($sex), 'students'=>$list];
        }
        return ['teacherOffering'=>$offering, 'offering'=>$offering, 'period'=>end($periods), 'groups'=>$groups, 'periods'=>$periods];
    }
}
