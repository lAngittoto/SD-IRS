<?php

if (!isset($_SESSION['user'])) {
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=log-in');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/student-models.php';
require_once __DIR__ . '/../models/student-report.php';

$dashModel   = new StudentModel($pdo);
$reportModel = new StudentReportModel($pdo);

// FIX: $_SESSION['user'] is an array — access user_id from within it
$userId = (int) ($_SESSION['user']['user_id'] ?? 0);

if ($userId === 0) {
    session_destroy();
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=log-in');
    exit;
}

// ── AJAX: incident detail ────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'detail') {
    header('Content-Type: application/json');
    $reportId = (int) ($_GET['report_id'] ?? 0);
    if (!$reportId) {
        echo json_encode(['success' => false, 'message' => 'Invalid report ID.']);
        exit;
    }
    $record = $dashModel->getIncidentDetail($reportId, $userId);
    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }
    $record['date_display']     = $record['created_at']
        ? date('M j, Y g:i A', strtotime($record['created_at']))  : '—';
    $record['reviewed_display'] = $record['reviewed_at']
        ? date('M j, Y g:i A', strtotime($record['reviewed_at'])) : '—';
    echo json_encode(['success' => true, 'data' => $record]);
    exit;
}

// ── POST: submit report ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $reportTarget    = trim($_POST['report_target']    ?? '');
    $targetStudentId = !empty($_POST['target_student_id'])  ? (int) $_POST['target_student_id']  : null;
    $targetTeacherId = !empty($_POST['target_teacher_id'])  ? (int) $_POST['target_teacher_id']  : null;
    $otherName       = trim($_POST['other_name']       ?? '');
    $location        = trim($_POST['location']         ?? '');
    $violationId     = !empty($_POST['violation_id']) && $_POST['violation_id'] !== 'other'
                       ? (int) $_POST['violation_id'] : null;
    $customViolation = trim($_POST['custom_violation'] ?? '');
    $description     = trim($_POST['description']      ?? '');

    $studentInfo = $dashModel->getStudentById($userId);
    $gradeLevel  = $studentInfo['grade_level'] ?? null;

    // Validation
    if (!in_array($reportTarget, ['student', 'teacher', 'other'], true))
        $errors[] = 'Please select who you are reporting.';
    if ($reportTarget === 'student' && !$targetStudentId)
        $errors[] = 'Please select the student involved.';
    if ($reportTarget === 'teacher' && !$targetTeacherId)
        $errors[] = 'Please select the teacher involved.';
    if ($reportTarget === 'other' && $otherName === '')
        $errors[] = 'Please enter the name of the person involved.';
    if ($location === '')
        $errors[] = 'Location is required.';
    if ($description === '')
        $errors[] = 'Description is required.';
    if (!$violationId && $customViolation === '')
        $errors[] = 'Please select or describe a violation type.';

    // File upload
    $evidencePath = null;
    $evidenceType = null;
    if (!empty($_FILES['evidence']['name'])) {
        $uploadResult = handleEvidenceUpload($_FILES['evidence'], $userId);
        if ($uploadResult['success']) {
            $evidencePath = $uploadResult['path'];
            $evidenceType = $uploadResult['type'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash']     = ['type' => 'error', 'messages' => $errors];
        $_SESSION['form_data'] = $_POST;
        header('Location: /student-discipline-and-incident-reporting-system/public/?page=student-report');
        exit;
    }

    $data = [
        'reporter_id'         => $userId,
        'report_target'       => $reportTarget,
        'student_id'          => $reportTarget === 'student' ? $targetStudentId : null,
        'teacher_involved_id' => $reportTarget === 'teacher' ? $targetTeacherId : null,
        'other_name'          => $reportTarget === 'other'   ? $otherName       : null,
        'grade_level'         => $gradeLevel,
        'location'            => $location,
        'violation_id'        => $violationId ?: null,
        'custom_violation'    => (!$violationId && $customViolation) ? $customViolation : null,
        'description'         => $description,
        'evidence_path'       => $evidencePath,
        'evidence_type'       => $evidenceType,
    ];

    $reportId = $reportModel->submitStudentReport($data);

    if ($reportId) {
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Your report has been submitted successfully. It is now under review.',
        ];
    } else {
        $_SESSION['flash'] = [
            'type'     => 'error',
            'messages' => ['Failed to submit report. Please try again.'],
        ];
    }

    header('Location: /student-discipline-and-incident-reporting-system/public/?page=student-dashboard');
    exit;
}

// ── GET: show report form ────────────────────────────────
$student    = $dashModel->getStudentById($userId);
$violations = $reportModel->getViolationList();
$teachers   = $reportModel->getTeacherList();
$students   = $reportModel->getStudentList($userId);

$flash = '';
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$old = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

ob_start();
require_once __DIR__ . '/../views/student-report.php';
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';

// ── FILE UPLOAD HELPER ───────────────────────────────────
function handleEvidenceUpload(array $file, int $userId): array
{
    $allowedMime = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/quicktime', 'video/webm',
        'audio/mpeg', 'audio/wav', 'audio/ogg',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    $maxSize = 10 * 1024 * 1024; // 10 MB

    if ($file['error'] !== UPLOAD_ERR_OK)
        return ['success' => false, 'error' => 'File upload failed. Please try again.'];
    if ($file['size'] > $maxSize)
        return ['success' => false, 'error' => 'File is too large. Maximum size is 10 MB.'];

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMime, true))
        return ['success' => false, 'error' => 'File type not allowed.'];

    if (str_starts_with($mimeType, 'image/'))      $folder = 'images';
    elseif (str_starts_with($mimeType, 'video/'))  $folder = 'videos';
    elseif (str_starts_with($mimeType, 'audio/'))  $folder = 'audio';
    else                                            $folder = 'documents';

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = "evidence_student_{$userId}_" . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../../../../storage/evidence/' . $folder . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destPath = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destPath))
        return ['success' => false, 'error' => 'Could not save the uploaded file.'];

    return [
        'success' => true,
        'path'    => '/student-discipline-and-incident-reporting-system/public/storage/evidence/' . $folder . '/' . $filename,
        'type'    => $folder,
    ];
}