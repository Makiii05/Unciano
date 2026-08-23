<?php

namespace App\Controllers;

use App\Services\SubjectService;

class SubjectController
{
    private SubjectService $subjectService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];
    private const ALLOWED_TYPES = ['lecture', 'laboratory', 'lecture_lab', 'lecture-lab'];
    private const ALLOWED_EDUCATION_LEVELS = ['college', 'K12', 'k12'];

    public function __construct()
    {
        $this->subjectService = new SubjectService();
    }

    public function index(): void
    {
        ensureRegistrar();

        $pageTitle = 'Subjects';
        $pageSubheader = 'Manage academic subjects';
        $subjects = $this->subjectService->getAll();

        ob_start();
        require __DIR__ . '/../../views/registrar/subjects/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/subjects/index.php'));
        }

        $data = $this->extractData($_POST);
        $errors = $this->validate($data);

        if ($this->subjectService->codeExists($data['code'])) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/subjects/index.php'));
        }

        $this->subjectService->create($data);
        unset($_SESSION['old']);
        flash('success', 'Subject created successfully.');
        redirect(url('views/registrar/subjects/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/subjects/index.php'));
        }

        $id = (int) ($_POST['subject_id'] ?? $_POST['id'] ?? 0);
        $row = $this->subjectService->getById($id);
        if (!$row) {
            flash('error', 'Subject not found.');
            redirect(url('views/registrar/subjects/index.php'));
        }

        $data = $this->extractData($_POST);
        $errors = $this->validate($data);

        if ($this->subjectService->codeExists($data['code'], $id)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/subjects/index.php'));
        }

        $this->subjectService->update($id, $data);
        flash('success', 'Subject updated successfully.');
        redirect(url('views/registrar/subjects/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/subjects/index.php'));
        }

        $id = (int) ($_POST['subject_id'] ?? $_POST['id'] ?? 0);
        $row = $this->subjectService->getById($id);
        if (!$row) {
            flash('error', 'Subject not found.');
            redirect(url('views/registrar/subjects/index.php'));
        }

        $dependents = $this->subjectService->hasDependents($id);
        // For subjects, block only if prerequisites relationships exist; prospectuses is informational but we still allow delete? Keep block for prerequisites to avoid orphan.
        $block = array_intersect($dependents, ['prerequisites', 'prerequisite_of']);
        if (!empty($block)) {
            flash('error', 'Cannot delete subject. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/subjects/index.php'));
        }

        $this->subjectService->delete($id);
        flash('success', 'Subject deleted successfully.');
        redirect(url('views/registrar/subjects/index.php'));
    }

    // ---- Prerequisites JSON ----

    public function prerequisites(): void
    {
        ensureRegistrar();
        $subjectId = (int) ($_GET['subject_id'] ?? $_GET['id'] ?? 0);
        if ($subjectId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid subject.'], 400);
            return;
        }
        $subject = $this->subjectService->getById($subjectId);
        if (!$subject) {
            $this->json(['success' => false, 'message' => 'Subject not found.'], 404);
            return;
        }
        $prereqs = $this->subjectService->getPrerequisites($subjectId);
        ob_start();
        $prerequisites = $prereqs;
        $subjectForList = $subject;
        require __DIR__ . '/../../views/registrar/subjects/partials/prerequisites-list.php';
        $html = ob_get_clean();
        $this->json(['success' => true, 'data' => $prereqs, 'html' => $html]);
    }

    public function searchPrerequisites(): void
    {
        ensureRegistrar();
        $subjectId = (int) ($_GET['subject_id'] ?? $_GET['id'] ?? 0);
        $q = trim($_GET['q'] ?? $_GET['query'] ?? '');
        if ($subjectId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid subject.'], 400);
            return;
        }
        if ($q === '') {
            $this->json(['success' => true, 'data' => []]);
            return;
        }
        $results = $this->subjectService->searchPrerequisites($subjectId, $q);
        $this->json(['success' => true, 'data' => $results]);
    }

    public function storePrerequisite(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 419);
            return;
        }
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $prereqId = (int) ($_POST['prerequisite_subject_id'] ?? $_POST['prereq_id'] ?? 0);
        if ($subjectId <= 0 || $prereqId <= 0) {
            $this->json(['success' => false, 'message' => 'Subject and prerequisite are required.'], 422);
            return;
        }
        try {
            $this->subjectService->addPrerequisite($subjectId, $prereqId);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            return;
        }
        $prereqs = $this->subjectService->getPrerequisites($subjectId);
        ob_start();
        $prerequisites = $prereqs;
        $subjectForList = $this->subjectService->getById($subjectId);
        require __DIR__ . '/../../views/registrar/subjects/partials/prerequisites-list.php';
        $html = ob_get_clean();
        $this->json(['success' => true, 'message' => 'Prerequisite added.', 'data' => $prereqs, 'html' => $html]);
    }

    public function destroyPrerequisite(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 419);
            return;
        }
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $rowId = (int) ($_POST['prerequisite_id'] ?? $_POST['id'] ?? 0);
        if ($subjectId <= 0 || $rowId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid prerequisite.'], 422);
            return;
        }
        try {
            $this->subjectService->removePrerequisite($subjectId, $rowId);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            return;
        }
        $prereqs = $this->subjectService->getPrerequisites($subjectId);
        ob_start();
        $prerequisites = $prereqs;
        $subjectForList = $this->subjectService->getById($subjectId);
        require __DIR__ . '/../../views/registrar/subjects/partials/prerequisites-list.php';
        $html = ob_get_clean();
        $this->json(['success' => true, 'message' => 'Prerequisite removed.', 'data' => $prereqs, 'html' => $html]);
    }

    private function extractData(array $post): array
    {
        return [
            'code' => trim($post['code'] ?? ''),
            'description' => trim($post['description'] ?? ''),
            'unit' => (int) ($post['unit'] ?? 0),
            'lech' => (int) ($post['lech'] ?? 0),
            'lecu' => (int) ($post['lecu'] ?? 0),
            'labh' => (int) ($post['labh'] ?? 0),
            'labu' => (int) ($post['labu'] ?? 0),
            'type' => trim($post['type'] ?? ''),
            'education_level' => trim($post['education_level'] ?? 'college'),
            'status' => trim($post['status'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['code'] === '' || mb_strlen($data['code']) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($data['description'] === '' || mb_strlen($data['description']) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if ($data['unit'] < 0) {
            $errors[] = 'Unit must be 0 or greater.';
        }
        if ($data['lech'] < 0 || $data['lecu'] < 0 || $data['labh'] < 0 || $data['labu'] < 0) {
            $errors[] = 'Lecture/Lab hours/units must be 0 or greater.';
        }
        if (!in_array($data['type'], self::ALLOWED_TYPES, true)) {
            $errors[] = 'Invalid type. Use lecture, laboratory, or lecture_lab.';
        }
        $level = strtolower($data['education_level']) === 'k12' ? 'K12' : $data['education_level'];
        if ($level === '') $level = 'college';
        if (!in_array($level, ['college', 'K12'], true)) {
            $errors[] = 'Invalid education level.';
        }
        if (!in_array($data['status'], self::ALLOWED_STATUSES, true)) {
            $errors[] = 'Invalid status.';
        }
        return $errors;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
