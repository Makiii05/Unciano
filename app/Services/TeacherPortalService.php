<?php

namespace App\Services;

use App\Core\Database;

class TeacherPortalService
{
    private \PDO $db;
    public function __construct(){ $this->db = Database::connection(); }

    public function getTeacher(): ?array
    {
        // auth('teacher') returns teacher_accounts + teachers joined
        $acc = auth('teacher');
        return $acc ?: null;
    }

    public function getTeacherId(): ?int
    {
        $t = $this->getTeacher();
        if (!$t) return null;
        // t.teacher_id is from teacher_accounts, t.id may be teacher_accounts id
        // functions.php selects ta.*, t.code,... so teacher_id is ta.teacher_id
        return isset($t['teacher_id']) ? (int) $t['teacher_id'] : (int) ($t['id'] ?? 0);
    }

    public function getTerms(int $teacherId): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT at.id, at.code, at.description, at.type FROM academic_terms at JOIN teacher_offerings to2 ON to2.academic_term_id=at.id WHERE to2.teacher_id=? ORDER BY at.id DESC");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public function resolveTerm(int $teacherId, ?int $termId=null): ?array
    {
        $terms = $this->getTerms($teacherId);
        if ($termId) {
            foreach ($terms as $t) if ((int)$t['id']===$termId) return $t;
        }
        // fallback to most recent
        return $terms[0] ?? null;
    }

    public function getSubjectLoad(int $teacherId, ?int $termId=null): array
    {
        $terms = $this->getTerms($teacherId);
        $term = $this->resolveTerm($teacherId, $termId);
        $loadings = [];
        if ($term) {
            $stmt = $this->db->prepare("SELECT to2.*, to2.id AS loading_id, so.code AS offering_code, so.description AS offering_description, s.code AS subject_code, s.description AS subject_description, p.code AS program_code, l.description AS level_description, at.code AS term_code FROM teacher_offerings to2 JOIN subject_offerings so ON so.id=to2.offering_id LEFT JOIN subjects s ON s.id=so.subject_id LEFT JOIN programs p ON p.id=so.program_id LEFT JOIN levels l ON l.id=so.level_id LEFT JOIN academic_terms at ON at.id=to2.academic_term_id WHERE to2.teacher_id=? AND to2.academic_term_id=? ORDER BY to2.id");
            $stmt->execute([$teacherId, $term['id']]);
            $loadings = $stmt->fetchAll();
            foreach ($loadings as &$ld) {
                $ld['offering'] = ['code'=>$ld['offering_code'],'description'=>$ld['offering_description'],'subject'=>['code'=>$ld['subject_code'],'description'=>$ld['subject_description']],'program'=> $ld['program_code']?['code'=>$ld['program_code']]:null,'level'=> $ld['level_description']?['description'=>$ld['level_description']]:null, 'academicTerm'=>['code'=>$ld['term_code']]];
            }
        }
        // get teacher row for display
        $teacher = null;
        if ($teacherId) {
            $stmt = $this->db->prepare('SELECT * FROM teachers WHERE id=? LIMIT 1');
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch();
        }
        return ['teacher'=>$teacher,'terms'=>$terms,'term'=>$term,'loadings'=>$loadings];
    }

    public function getClassList(int $teacherOfferingId, int $teacherId): array
    {
        // verify ownership
        $stmt = $this->db->prepare('SELECT * FROM teacher_offerings WHERE id=? AND teacher_id=? LIMIT 1');
        $stmt->execute([$teacherOfferingId, $teacherId]);
        $to = $stmt->fetch();
        if (!$to) throw new \RuntimeException('Unauthorized or not found.');
        // load offering
        $stmt2 = $this->db->prepare("SELECT to2.*, so.code AS offering_code, s.description AS subject_description, at.code AS term_code FROM teacher_offerings to2 JOIN subject_offerings so ON so.id=to2.offering_id LEFT JOIN subjects s ON s.id=so.subject_id LEFT JOIN academic_terms at ON at.id=to2.academic_term_id WHERE to2.id=? LIMIT 1");
        $stmt2->execute([$teacherOfferingId]);
        $offering = $stmt2->fetch();
        // get enrolled students via TeacherOfferingService logic
        $stmt3 = $this->db->prepare("SELECT s.id, s.student_number, s.last_name, s.first_name, s.middle_name, s.sex FROM students s JOIN enlistments e ON e.student_id=s.id WHERE e.subject_offering_id=? AND e.academic_term_id=? ORDER BY s.last_name, s.first_name");
        $stmt3->execute([$to['offering_id'], $to['academic_term_id']]);
        $students = $stmt3->fetchAll();
        $groups = ['male'=>['label'=>'Male','students'=>[]], 'female'=>['label'=>'Female','students'=>[]]];
        foreach ($students as $s) {
            $sex = strtolower($s['sex'] ?? 'male');
            if (!isset($groups[$sex])) $groups[$sex]=['label'=>ucfirst($sex),'students'=>[]];
            $groups[$sex]['students'][] = $s;
        }
        return ['teacherOffering'=>$to,'offering'=>$offering,'groups'=>$groups,'total'=>count($students)];
    }

    public function hasGradesForOffering(int $teacherOfferingId): bool
    {
        // Guard: block grading system change if any student already has grade inputed
        // Definition: raw_score.score IS NOT NULL OR grade.status != 'draft' OR grade with initial/period grade not null
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM grade WHERE teacher_offering_id=? LIMIT 1");
            $stmt->execute([$teacherOfferingId]);
            if (!$stmt->fetch()) return false;

            $stmt2 = $this->db->prepare("SELECT 1 FROM grade WHERE teacher_offering_id=? AND status != 'draft' LIMIT 1");
            $stmt2->execute([$teacherOfferingId]);
            if ($stmt2->fetch()) return true;

            $stmt3 = $this->db->prepare("SELECT 1 FROM grade WHERE teacher_offering_id=? AND (initial_grade IS NOT NULL OR period_grade IS NOT NULL) LIMIT 1");
            $stmt3->execute([$teacherOfferingId]);
            if ($stmt3->fetch()) return true;

            $stmt4 = $this->db->prepare("SELECT 1 FROM raw_score rs JOIN grade g ON g.id=rs.grade_id WHERE g.teacher_offering_id=? AND rs.score IS NOT NULL LIMIT 1");
            $stmt4->execute([$teacherOfferingId]);
            if ($stmt4->fetch()) return true;

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
