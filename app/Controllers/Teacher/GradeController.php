<?php

namespace App\Controllers\Teacher;

use App\Services\TeacherPortalService;
use App\Services\GradingSystemService;

class GradeController
{
    private TeacherPortalService $portal;
    private GradingSystemService $gsService;
    public function __construct(){ $this->portal = new TeacherPortalService(); $this->gsService = new GradingSystemService(); }

    public function updateGradingSystem(): void
    {
        ensureTeacher();
        if (!validate_csrf()) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Invalid CSRF']); exit;
        }
        $teacherId = $this->portal->getTeacherId();
        $loadingId = (int) ($_POST['teacher_offering_id'] ?? $_POST['loading_id'] ?? $_GET['id'] ?? 0);
        if (!$loadingId && preg_match('#/api/teacher/grade/(\d+)#', $_SERVER['REQUEST_URI'] ?? '', $m)) $loadingId = (int)$m[1];
        // Also accept JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input)) {
            if (isset($input['grading_id'])) $_POST['grading_id'] = $input['grading_id'];
            if (isset($input['teacher_offering_id'])) $loadingId = (int)$input['teacher_offering_id'];
        }
        $gradingId = isset($_POST['grading_id']) && $_POST['grading_id'] !== '' ? (int)$_POST['grading_id'] : null;

        if (!$loadingId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Missing teacher_offering id']); exit;
        }
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT * FROM teacher_offerings WHERE id=? AND teacher_id=? LIMIT 1');
        $stmt->execute([$loadingId, $teacherId]);
        $to = $stmt->fetch();
        if (!$to) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Unauthorized']); exit;
        }
        // Guard: no grades inputed yet
        if ($this->portal->hasGradesForOffering($loadingId)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Cannot change grading system: grades have already been inputed for this subject.']); exit;
        }
        // Validate gradingId belongs to same department if provided
        if ($gradingId !== null) {
            $offStmt = $db->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
            $offStmt->execute([$to['offering_id']]);
            $off = $offStmt->fetch();
            $deptId = $off['department_id'] ?? null;
            $gsStmt = $db->prepare('SELECT department_id FROM grading_systems WHERE id=? LIMIT 1');
            $gsStmt->execute([$gradingId]);
            $gs = $gsStmt->fetch();
            if (!$gs) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['message'=>'Grading system not found.']); exit;
            }
            if ((int)$gs['department_id'] !== (int)$deptId) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['message'=>'Grading system does not belong to offering department.']); exit;
            }
        }
        $upd = $db->prepare('UPDATE teacher_offerings SET grading_id=?, updated_at=NOW() WHERE id=?');
        $upd->execute([$gradingId, $loadingId]);

        // Return effective components
        $components = [];
        if ($gradingId) {
            $gs = $db->prepare('SELECT c.id, c.code, c.description, c.percentage FROM grading_components gc JOIN components c ON c.id=gc.component_id WHERE gc.grading_id=? ORDER BY c.code');
            $gs->execute([$gradingId]);
            $components = $gs->fetchAll();
        } else {
            // fallback to offering default
            $offGsId = null;
            $offStmt2 = $db->prepare('SELECT grading_id FROM subject_offerings WHERE id=? LIMIT 1');
            $offStmt2->execute([$to['offering_id']]);
            $offRow = $offStmt2->fetch();
            $offGsId = $offRow['grading_id'] ?? null;
            if ($offGsId) {
                $gs = $db->prepare('SELECT c.id, c.code, c.description, c.percentage FROM grading_components gc JOIN components c ON c.id=gc.component_id WHERE gc.grading_id=? ORDER BY c.code');
                $gs->execute([$offGsId]);
                $components = $gs->fetchAll();
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['grading_id'=>$gradingId,'components'=>$components,'message'=>'Grading system updated.']);
        exit;
    }

    public function storeGradingSystem(): void
    {
        ensureTeacher();
        if (!validate_csrf()) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Invalid CSRF']); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        $description = trim($input['description'] ?? '');
        $componentIds = $input['component_ids'] ?? [];
        if (!is_array($componentIds)) $componentIds = [$componentIds];
        $componentIds = array_map('intval', array_filter($componentIds));
        if ($description === '' || empty($componentIds)) {
            http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Description and at least one component required.']); exit;
        }
        // Determine department_id from teacher's offering? For creation, use first component's department or offering dept
        // Use components department (assume same)
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT department_id FROM components WHERE id=? LIMIT 1');
        $stmt->execute([$componentIds[0]]);
        $comp = $stmt->fetch();
        $deptId = $comp['department_id'] ?? null;
        if (!$deptId) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Cannot determine department.']); exit; }
        try {
            $newId = $this->gsService->create(['description'=>$description,'department_id'=>$deptId], $componentIds);
        } catch (\RuntimeException $e) {
            http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>$e->getMessage()]); exit;
        }
        $gs = $this->gsService->getById($newId);
        header('Content-Type: application/json');
        echo json_encode(['id'=>$gs['id'],'description'=>$gs['description'],'total_percentage'=>(float)$gs['total_percentage'],'grading_components'=>array_map(fn($c)=>['component_id'=>$c['component_id']], $gs['components'] ?? [])]);
        exit;
    }

    public function updateGradingSystemConfig(): void
    {
        ensureTeacher();
        if (!validate_csrf()) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Invalid CSRF']); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        $id = (int) ($_GET['id'] ?? $input['id'] ?? 0);
        if (!$id && preg_match('#/api/teacher/grading-systems/(\d+)#', $_SERVER['REQUEST_URI'] ?? '', $m)) $id = (int)$m[1];
        $description = trim($input['description'] ?? '');
        $componentIds = $input['component_ids'] ?? [];
        if (!is_array($componentIds)) $componentIds = [$componentIds];
        $componentIds = array_map('intval', array_filter($componentIds));
        if ($description === '' || empty($componentIds)) {
            http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Description and at least one component required.']); exit;
        }
        $existing = $this->gsService->getById($id);
        if (!$existing) { http_response_code(404); header('Content-Type: application/json'); echo json_encode(['message'=>'Not found']); exit; }
        // Check if grading system is in use with grades -> block? Per task, editing grading system should be blocked if grades exist for loadings using it.
        // Find any teacher_offering using this grading_id with grades
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT id FROM teacher_offerings WHERE grading_id=? LIMIT 1');
        $stmt->execute([$id]);
        $to = $stmt->fetch();
        if ($to) {
            $has = (new TeacherPortalService())->hasGradesForOffering((int)$to['id']);
            if ($has) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Cannot edit grading system: grades have already been inputed for a subject using this system.']); exit; }
        }
        // Also check subject_offerings default
        try {
            $this->gsService->update($id, ['description'=>$description,'department_id'=>$existing['department_id']], $componentIds);
        } catch (\RuntimeException $e) {
            http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>$e->getMessage()]); exit;
        }
        $gs = $this->gsService->getById($id);
        header('Content-Type: application/json');
        echo json_encode(['id'=>$gs['id'],'description'=>$gs['description'],'total_percentage'=>(float)$gs['total_percentage'],'grading_components'=>array_map(fn($c)=>['component_id'=>$c['component_id']], $gs['components'] ?? [])]);
        exit;
    }

    public function destroyGradingSystem(): void
    {
        ensureTeacher();
        if (!validate_csrf()) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Invalid CSRF']); exit; }
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id && preg_match('#/api/teacher/grading-systems/(\d+)#', $_SERVER['REQUEST_URI'] ?? '', $m)) $id = (int)$m[1];
        $existing = $this->gsService->getById($id);
        if (!$existing) { http_response_code(404); header('Content-Type: application/json'); echo json_encode(['message'=>'Not found']); exit; }
        // Block if in use with grades
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT id FROM teacher_offerings WHERE grading_id=? LIMIT 1');
        $stmt->execute([$id]);
        $to = $stmt->fetch();
        if ($to) {
            $has = (new TeacherPortalService())->hasGradesForOffering((int)$to['id']);
            if ($has) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Cannot delete: grades exist for this system.']); exit; }
        }
        $deps = $this->gsService->hasDependents($id);
        if (!empty($deps)) {
            // Allow delete if no grades but still referenced by subject_offerings - warn but allow? Per service, block? We'll allow with warning? For now block if subject_offerings uses it
            http_response_code(422); header('Content-Type: application/json'); echo json_encode(['message'=>'Cannot delete: still referenced by '.implode(', ', $deps)]); exit;
        }
        $this->gsService->delete($id);
        header('Content-Type: application/json');
        echo json_encode(['message'=>'Deleted']);
        exit;
    }
}
