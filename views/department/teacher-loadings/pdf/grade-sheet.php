<?php
// Separate FPDF report file - Grade Sheet
require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../../../../lib/fpdf/fpdf.php';

ensureDepartment();

$loadingId = (int) ($_GET['loading_id'] ?? $_GET['id'] ?? 0);
if (!$loadingId) { http_response_code(404); exit('Missing loading_id'); }

$svc = new App\Services\TeacherOfferingService();
$loading = $svc->getById($loadingId);
if (!$loading) { http_response_code(404); exit('Loading not found'); }

$user = auth();
$stmt = App\Core\Database::connection()->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
$stmt->execute([$loading['offering_id']]);
$offDept = $stmt->fetch();
if (!$offDept || (int)$offDept['department_id'] !== (int)$user['department_id']) { http_response_code(403); exit('Forbidden'); }

$data = $svc->getGradeSheetData($loadingId);
$offering = $data['offering'];
$groups = $data['groups'];
$period = $data['period'] ?? 'final';

$db = App\Core\Database::connection();
$teacherStmt = $db->prepare('SELECT * FROM teachers WHERE id=? LIMIT 1');
$teacherStmt->execute([$loading['teacher_id']]);
$teacher = $teacherStmt->fetch();

$termStmt = $db->prepare('SELECT at.*, sy.description AS sy_description FROM academic_terms at LEFT JOIN school_years sy ON sy.id=at.school_year_id WHERE at.id=? LIMIT 1');
$termStmt->execute([$loading['academic_term_id']]);
$term = $termStmt->fetch();

$offStmt = $db->prepare('SELECT so.code AS offering_code, s.code AS subject_code, s.description AS subject_description, p.code AS program_code FROM subject_offerings so LEFT JOIN subjects s ON s.id=so.subject_id LEFT JOIN programs p ON p.id=so.program_id WHERE so.id=? LIMIT 1');
$offStmt->execute([$loading['offering_id']]);
$offFull = $offStmt->fetch();

class GradeSheetPDF extends FPDF {
    function Header() {
        $this->SetFont('Helvetica','B',14);
        $this->Cell(0,7,'UCA Nexus - Grade Sheet',0,1,'C');
        $this->SetFont('Helvetica','',8);
        $this->Cell(0,4,'Department Portal - Hardcoded 4 periods (prelim, midterm, prefinal, final)',0,1,'C');
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

$pdf = new GradeSheetPDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

$teacherName = trim(($teacher['last_name'] ?? '').', '.($teacher['first_name'] ?? '').' '.($teacher['middle_name'] ?? ''));

$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Offering:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ($offFull['offering_code'] ?? $offFull['code'] ?? $offering['offering_code'] ?? '') . ' - ' . ($offFull['subject_description'] ?? ''),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Subject:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ($offFull['subject_code'] ?? '') . ' - ' . ($offFull['subject_description'] ?? ''),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Teacher:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, $teacherName . ' (' . ($teacher['code'] ?? '') . ')',0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Term:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ($term['description'] ?? '') . ' ' . (!empty($term['sy_description']) ? '('.$term['sy_description'].')' : ''),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Period:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, ucfirst($period),0,1);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(30,6,'Program:',0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,6, $offFull['program_code'] ?? '',0,1);
$pdf->Ln(4);

$totalStudents = 0;
foreach ($groups as $g) $totalStudents += count($g['students']);
$pdf->SetFont('Helvetica','',8);
$pdf->Cell(0,5,'Total Students: '.$totalStudents,0,1,'R');
$pdf->Ln(2);

$sexLabels = ['male'=>'Male','female'=>'Female'];
foreach ($sexLabels as $key=>$label) {
    $list = $groups[$key]['students'] ?? [];
    if (empty($list)) continue;
    $pdf->SetFillColor(15,23,42);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Helvetica','B',8);
    $pdf->Cell(0,7,'  '.$label.' ('.count($list).')',0,1,'L',true);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(241,245,249);
    $pdf->SetFont('Helvetica','B',7);
    $pdf->Cell(10,6,'#',1,0,'C',true);
    $pdf->Cell(30,6,'Student No',1,0,'C',true);
    $pdf->Cell(85,6,'Name',1,0,'C',true);
    $pdf->Cell(25,6,'Grade',1,0,'C',true);
    $pdf->Cell(40,6,'Remarks',1,1,'C',true);
    $pdf->SetFont('Helvetica','',7);
    $i=1;
    foreach ($list as $s) {
        if ($pdf->GetY() > 270) $pdf->AddPage();
        $grade = $s['grade'];
        $gradeStr = $grade !== null ? number_format((float)$grade,2) : '—';
        $remarks = '';
        if ($grade !== null) {
            // Simple pass/fail: 1.00-3.00 pass for college 4-scale, 75+ for base 50
            if ((float)$grade <= 3.00 && (float)$grade >= 1.00) $remarks = 'Passed';
            elseif ((float)$grade > 3.00) $remarks = 'Failed';
            else $remarks = '';
        }
        $pdf->Cell(10,6,$i++,1,0,'C');
        $pdf->Cell(30,6,$s['number'] ?? '',1,0,'C');
        $pdf->Cell(85,6,$s['name'] ?? '',1,0,'L');
        $pdf->Cell(25,6,$gradeStr,1,0,'C');
        $pdf->Cell(40,6,$remarks,1,1,'C');
    }
    $pdf->Ln(3);
}

if ($totalStudents===0) {
    $pdf->SetFont('Helvetica','',9);
    $pdf->Cell(0,10,'No students enrolled for this offering.',0,1,'C');
}

$pdf->Ln(10);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(60,6,'',0,0);
$pdf->Cell(60,6,'',0,0);
$pdf->Cell(60,6,'_________________________',0,1,'C');
$pdf->Cell(60,6,'',0,0);
$pdf->Cell(60,6,'',0,0);
$pdf->Cell(60,6,'Instructor Signature',0,1,'C');

$filename = 'grade_sheet_' . preg_replace('/[^A-Za-z0-9_-]/','_', $offFull['offering_code'] ?? $loadingId) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('I', $filename);
