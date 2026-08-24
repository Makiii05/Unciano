<?php
require_once __DIR__ . '/../../../../bootstrap.php';

use App\Services\TeacherOfferingService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
ensureDepartment();
if (!validate_csrf()) { flash('error','Invalid CSRF token.'); redirect(url('views/department/teacher-loadings/index.php')); }
$id = (int) ($_POST['id'] ?? 0);
if (!$id) { flash('error','Missing id.'); redirect(url('views/department/teacher-loadings/index.php')); }
$svc = new TeacherOfferingService();
$row = $svc->getById($id);
if (!$row) { flash('error','Loading not found.'); redirect(url('views/department/teacher-loadings/index.php')); }
$user = auth();
$stmt = \App\Core\Database::connection()->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
$stmt->execute([$row['offering_id']]);
$off = $stmt->fetch();
if ($off && (int)$off['department_id'] !== (int)$user['department_id']) { flash('error','Forbidden.'); redirect(url('views/department/teacher-loadings/index.php')); }
$svc->delete($id);
flash('success','Teacher loading deleted successfully.');
$referer = $_SERVER['HTTP_REFERER'] ?? url('views/department/teacher-loadings/index.php');
redirect($referer);
