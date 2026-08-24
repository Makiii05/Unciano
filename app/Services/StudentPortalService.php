<?php

namespace App\Services;

use App\Core\Database;

class StudentPortalService
{
    private \PDO $db;
    public function __construct(){ $this->db = Database::connection(); }

    public function getStudent(): ?array
    {
        return auth('student');
    }

    public function getStudentId(): ?int
    {
        $s = $this->getStudent();
        if (!$s) return null;
        // student_accounts.id is sa.id, but student id is s.id -> from auth we have student_number etc plus student_id from students via join? Check functions.php selects s.* plus sa.*, so id is ambiguous? Let's fetch directly
        // auth('student') returns sa.* plus s.* ; student id is s.id but aliased? In query, sa.* includes id as student_account id, and s.student_number etc but s.id not selected as separate? Actually query selects sa.*, s.student_number, s.first_name... but not s.id. So we need to fetch student id via student_number lookup.
        // Fallback: query students by student_number
        if (isset($s['student_id'])) return (int) $s['student_id'];
        if (isset($s['student_number'])) {
            $stmt = $this->db->prepare('SELECT id FROM students WHERE student_number=? LIMIT 1');
            $stmt->execute([$s['student_number']]);
            $row = $stmt->fetch();
            return $row ? (int) $row['id'] : null;
        }
        return null;
    }

    public function getTerms(int $studentId): array
    {
        // Terms are by department of student
        $stmt = $this->db->prepare('SELECT department_id FROM students WHERE id=? LIMIT 1');
        $stmt->execute([$studentId]);
        $stu = $stmt->fetch();
        if (!$stu) return [];
        $deptId = (int) $stu['department_id'];
        $stmt2 = $this->db->prepare('SELECT id, code, description, type FROM academic_terms WHERE department_id=? ORDER BY id DESC');
        $stmt2->execute([$deptId]);
        return $stmt2->fetchAll();
    }

    public function getGrades(int $studentId): array
    {
        // Fetch enlistments with subject and term data, grouped by term
        $stmt = $this->db->prepare("
            SELECT e.id AS enlistment_id, e.student_id, e.academic_term_id, e.subject_offering_id, e.final_grade,
                   at.code AS term_code, at.description AS term_description, at.type AS term_type,
                   so.code AS offering_code, s.code AS subject_code, s.description AS subject_description, s.unit
            FROM enlistments e
            JOIN subject_offerings so ON so.id = e.subject_offering_id
            LEFT JOIN subjects s ON s.id = so.subject_id
            LEFT JOIN academic_terms at ON at.id = e.academic_term_id
            WHERE e.student_id = ?
            ORDER BY e.academic_term_id, so.code
        ");
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll();
        $enlistmentsByTerm = [];
        $termIds = [];
        foreach ($rows as $r) {
            $tid = (int) $r['academic_term_id'];
            if (!isset($enlistmentsByTerm[$tid])) $enlistmentsByTerm[$tid] = [];
            $enlistmentsByTerm[$tid][] = $r;
            $termIds[$tid] = true;
        }
        // Fetch terms for keyBy
        $terms = $this->getTerms($studentId);
        $termsById = [];
        foreach ($terms as $t) $termsById[(int)$t['id']] = $t;
        // Also ensure terms that appear in enlistments but not in department terms are added (fallback)
        foreach (array_keys($enlistmentsByTerm) as $tid) {
            if (!isset($termsById[$tid])) {
                $stmt2 = $this->db->prepare('SELECT id, code, description, type FROM academic_terms WHERE id=? LIMIT 1');
                $stmt2->execute([$tid]);
                $t = $stmt2->fetch();
                if ($t) $termsById[$tid] = $t;
            }
        }
        // Fetch student info
        $stmt3 = $this->db->prepare('SELECT s.*, d.code AS department_code, p.code AS program_code, l.description AS level_description FROM students s LEFT JOIN departments d ON d.id=s.department_id LEFT JOIN programs p ON p.id=s.program_id LEFT JOIN levels l ON l.id=s.level_id WHERE s.id=? LIMIT 1');
        $stmt3->execute([$studentId]);
        $student = $stmt3->fetch();
        return [
            'student' => $student,
            'enlistmentsByTerm' => $enlistmentsByTerm,
            'terms' => $terms,
            'termsById' => $termsById,
        ];
    }
}
