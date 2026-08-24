<?php
// Separate FPDF report file - Class List
require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../../../../lib/fpdf/fpdf.php';

ensureDepartment();

$loadingId = (int) ($_GET['loading_id'] ?? $_GET['id'] ?? 0);
if (!$loadingId) { http_response_code(404); exit('Missing loading_id'); }

$svc = new App\Services\TeacherOfferingService();
$loading = $svc->getById($loadingId);
if (!$loading) { http_response_code(404); exit('Loading not found'); }

$user = auth();
$stmt = App\Core\Database::connection()->prepare('SELECT department_id, code FROM subject_offerings WHERE id=? LIMIT 1');
$stmt->execute([$loading['offering_id']]);
$off = $stmt->fetch();
if (!$off || (int)$off['department_id'] !== (int)$user['department_id']) { http_response_code(403); exit('Forbidden'); }

// Fetch header details
$db = App\Core\Database::connection();
$teacherStmt = $db->prepare('SELECT * FROM teachers WHERE id=? LIMIT 1');
$teacherStmt->execute([$loading['teacher_id']]);
$teacher = $teacherStmt->fetch();

$termStmt = $db->prepare('SELECT at.*, sy.description AS sy_description FROM academic_terms at LEFT JOIN school_years sy ON sy.id=at.school_year_id WHERE at.id=? LIMIT 1');
$termStmt->execute([$loading['academic_term_id']]);
$term = $termStmt->fetch();

$offStmt = $db->prepare('SELECT so.code AS offering_code, so.description AS offering_description, s.code AS subject_code, s.description AS subject_description, p.code AS program_code, p.description AS program_description, l.description AS level_description FROM subject_offerings so LEFT JOIN subjects s ON s.id=so.subject_id LEFT JOIN programs p ON p.id=so.program_id LEFT JOIN levels l ON l.id=so.level_id WHERE so.id=? LIMIT 1');
$offStmt->execute([$loading['offering_id']]);
$offeringFull = $offStmt->fetch();

$students = $svc->getEnrolledStudents((int)$loading['offering_id'], (int)$loading['academic_term_id']);
usort($students, fn($a,$b)=> strcmp($a['last_name']??'', $b['last_name']??'') ?: strcmp($a['first_name']??'', $b['first_name']??''));

class ClassListPDF extends FPDF {
    function Header() {
        $this->SetFont('Helvetica','B',14);
        $this->Cell(0,7,'UCA Nexus - Class List',0,1,'C');
        $this->SetFont('Helvetica','',8);
        $this->Cell(0,4,'Department Portal',0,1,'C');
        $this->Ln(2);
        $this->SetDrawColor(200,200,200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica','',7);
        $this->SetTextColor(130,130,130);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new ClassListPDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Helvetica','',9);

$teacherName = trim(($teacher['last_name'] ?? '').', '.($teacher['first_name'] ?? '').' '.($teacher['middle_name'] ?? ''));
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Offering:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ($offeringFull['offering_code'] ?? $off['code'] ?? '') . ' - ' . ($offeringFull['subject_description'] ?? ''),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Subject:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ($offeringFull['subject_code'] ?? '') . ' - ' . ($offeringFull['subject_description'] ?? ''),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Teacher:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, $teacherName . ' (' . ($teacher['code'] ?? '') . ')',0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Term:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ($term['description'] ?? '') . ' ' . (!empty($term['sy_description']) ? '('.$term['sy_description'].')' : ''),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Total:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, count($students) . ' student(s)',0,1);
$pdf->Ln(4);

// Group by sex
$groups = ['male'=>[], 'female'=>[]];
foreach ($students as $s) {
    $sex = strtolower($s['sex'] ?? 'male');
    if (!isset($groups[$sex])) $groups[$sex]=[];
    $groups[$sex][] = $s;
}
$sexLabels = ['male'=>'Male','female'=>'Female'];

foreach ($sexLabels as $key=>$label) {
    $list = $groups[$key] ?? [];
    if (empty($list)) continue;
    $pdf->SetFillColor(15,23,42);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Helvetica','B',8);
    $pdf->Cell(0,7, '  '.$label.' ('.count($list).')',0,1,'L',true);
    $pdf->SetTextColor(0,0,0);
    // Table header
    $pdf->SetFillColor(241,245,249);
    $pdf->SetFont('Helvetica','B',7);
    $pdf->Cell(10,6,'#',1,0,'C',true);
    $pdf->Cell(30,6,'Student No',1,0,'C',true);
    $pdf->Cell(90,6,'Name',1,0,'C',true);
    $pdf->Cell(20,6,'Sex',1,0,'C',true);
    $pdf->Cell(40,6,'Signature',1,1,'C',true);
    $pdf->SetFont('Helvetica','',7);
    $i=1;
    foreach ($list as $s) {
        $name = trim(($s['last_name'] ?? '').', '.($s['first_name'] ?? '').' '.($s['middle_name'] ?? ''));
        // Check page overflow
        if ($pdf->GetY() > 270) { $pdf->AddPage(); }
        $pdf->Cell(10,6,$i++,1,0,'C');
        $pdf->Cell(30,6,$s['student_number'] ?? '',1,0,'C');
        $pdf->Cell(90,6, $name,1,0,'L');
        $pdf->Cell(20,6, ucfirst($s['sex'] ?? ''),1,0,'C');
        $pdf->Cell(40,6,'',1,1,'C');
    }
    $pdf->Ln(3);
}

if (empty($students)) {
    $pdf->SetFont('Helvetica','',9);
    $pdf->Cell(0,10,'No students enrolled for this offering.',0,1,'C');
}

$filename = 'class_list_' . preg_replace('/[^A-Za-z0-9_-]/','_', $off['code'] ?? $loadingId) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('I', $filename);
