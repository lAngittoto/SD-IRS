<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/advisories-model.php';

class AdvisoriesController {
    private $advisoriesModel;
    private $uploadDir    = __DIR__ . '/../../../public/storage/photos/';
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private $maxFileSize  = 5242880; // 5 MB

    public function __construct($pdo) {
        $this->advisoriesModel = new AdvisoriesModel($pdo);
        if (!is_dir($this->uploadDir)) mkdir($this->uploadDir, 0755, true);
    }

    public function getActiveSchoolYear() { return $this->advisoriesModel->getActiveSchoolYear(); }

    /* ── File helpers ─────────────────────────────────────────── */
    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK)             return ['valid'=>false,'message'=>'Upload error.'];
        if (!in_array($file['type'], $this->allowedTypes)) return ['valid'=>false,'message'=>'Only JPG, PNG, WebP allowed.'];
        if ($file['size'] > $this->maxFileSize)            return ['valid'=>false,'message'=>'File too large (max 5MB).'];
        return ['valid'=>true];
    }

    public function uploadProfilePicture() {
        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['profile_pic'])) {
            $file      = $_FILES['profile_pic'];
            $studentId = intval($_POST['student_id'] ?? 0);
            if ($studentId<=0) { echo json_encode(['success'=>false,'message'=>'Invalid student ID.']); exit; }
            $v = $this->validateFile($file);
            if (!$v['valid'])  { echo json_encode(['success'=>false,'message'=>$v['message']]); exit; }
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'student_'.$studentId.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
            $path     = $this->uploadDir.$filename;
            $rel      = '/student-discipline-and-incident-reporting-system/public/storage/photos/'.$filename;
            if (!move_uploaded_file($file['tmp_name'], $path)) { echo json_encode(['success'=>false,'message'=>'Save failed.']); exit; }
            chmod($path, 0644);
            echo json_encode(['success'=>true,'path'=>$rel]); exit;
        }
    }

    public function uploadTeacherProfilePicture() {
        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['profile_pic'])) {
            $file      = $_FILES['profile_pic'];
            $teacherId = intval($_POST['teacher_id'] ?? 0);
            if ($teacherId<=0) { echo json_encode(['success'=>false,'message'=>'Invalid teacher ID.']); exit; }
            $v = $this->validateFile($file);
            if (!$v['valid'])  { echo json_encode(['success'=>false,'message'=>$v['message']]); exit; }
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'teacher_'.$teacherId.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
            $path     = $this->uploadDir.$filename;
            $rel      = '/student-discipline-and-incident-reporting-system/public/storage/photos/'.$filename;
            if (!move_uploaded_file($file['tmp_name'], $path)) { echo json_encode(['success'=>false,'message'=>'Save failed.']); exit; }
            chmod($path, 0644);
            echo json_encode(['success'=>true,'path'=>$rel]); exit;
        }
    }

    /* ── Teacher / Student actions ───────────────────────────── */
    public function assignAdvisoryTeacher() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $data = [
                'teacher_id'    => intval($_POST['teacher_id']    ?? 0),
                'role_type'     => trim($_POST['role_type']       ?? ''),
                'advisory_name' => trim($_POST['advisory_name']   ?? ''),
                'grade_level'   => trim($_POST['grade_level']     ?? '')
            ];
            if ($data['teacher_id']<=0) { echo json_encode(['success'=>false,'message'=>'Select a teacher.']); exit; }
            if (!in_array($data['role_type'],['subject','advisory'])) { echo json_encode(['success'=>false,'message'=>'Select a role.']); exit; }
            if ($data['role_type']==='advisory') {
                if (empty($data['advisory_name'])) { echo json_encode(['success'=>false,'message'=>'Class name required.']); exit; }
                if (!in_array($data['grade_level'],['7','8','9','10'])) { echo json_encode(['success'=>false,'message'=>'Select grade 7–10.']); exit; }
            }
            echo json_encode($this->advisoriesModel->assignAdvisoryTeacher($data)); exit;
        }
    }

    public function convertToSubjectTeacher() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $id = intval($_POST['advisory_id'] ?? 0);
            if ($id<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
            echo json_encode($this->advisoriesModel->convertToSubjectTeacher($id)); exit;
        }
    }

    public function assignStudentsToAdvisory() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $advisory_id  = intval($_POST['advisory_id'] ?? 0);
            $student_ids  = $_POST['student_ids']  ?? [];
            $grade_levels = $_POST['grade_levels'] ?? [];
            if ($advisory_id<=0) { echo json_encode(['success'=>false,'message'=>'Select advisory.']); exit; }
            if (empty($student_ids)||!is_array($student_ids)) { echo json_encode(['success'=>false,'message'=>'Select students.']); exit; }

            $advisoryInfo = $this->advisoriesModel->getAdvisoryById($advisory_id);
            if (!$advisoryInfo) { echo json_encode(['success'=>false,'message'=>'Advisory not found.']); exit; }
            $advisoryGrade = $advisoryInfo['grade_level'];
            foreach ($student_ids as $sid) {
                $sg = $grade_levels[$sid] ?? '';
                if ($sg !== $advisoryGrade) { echo json_encode(['success'=>false,'message'=>"Advisory only accepts Grade {$advisoryGrade}."]); exit; }
            }

            $sy = $this->advisoriesModel->getActiveSchoolYear();
            $syId = $sy ? $sy['school_year_id'] : 1;
            echo json_encode($this->advisoriesModel->assignStudentsToAdvisory($advisory_id, $student_ids, $grade_levels, $syId)); exit;
        }
    }

    public function reassignStudent() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $aid   = intval($_POST['assignment_id']   ?? 0);
            $naid  = intval($_POST['new_advisory_id'] ?? 0);
            $grade = $_POST['current_grade'] ?? '';
            if ($aid<=0||$naid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            if (!in_array($grade,['7','8','9','10'])) { echo json_encode(['success'=>false,'message'=>'Invalid grade.']); exit; }
            echo json_encode($this->advisoriesModel->reassignStudent($aid,$naid,$grade)); exit;
        }
    }

    public function removeFromAdvisory() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $id = intval($_POST['assignment_id'] ?? 0);
            if ($id<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
            echo json_encode($this->advisoriesModel->removeFromAdvisory($id)); exit;
        }
    }

    public function updateStudentGrade() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $id = intval($_POST['assignment_id'] ?? 0);
            $g  = trim($_POST['new_grade']       ?? '');
            if ($id<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
            if (!in_array($g,['7','8','9','10'])) { echo json_encode(['success'=>false,'message'=>'Invalid grade.']); exit; }
            echo json_encode($this->advisoriesModel->updateStudentGrade($id,$g)); exit;
        }
    }

    public function bulkUpdateStudentGrade() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $ids = $_POST['assignment_ids'] ?? [];
            $g   = trim($_POST['new_grade'] ?? '');
            if (empty($ids)||!is_array($ids)) { echo json_encode(['success'=>false,'message'=>'Select students.']); exit; }
            if (!in_array($g,['7','8','9','10'])) { echo json_encode(['success'=>false,'message'=>'Invalid grade.']); exit; }
            $ids = array_filter(array_map('intval',$ids), fn($x)=>$x>0);
            if (empty($ids)) { echo json_encode(['success'=>false,'message'=>'Invalid.']); exit; }
            echo json_encode($this->advisoriesModel->bulkUpdateStudentGrade($ids,$g)); exit;
        }
    }

    public function updateStudentGradeById() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $sid = intval($_POST['student_id'] ?? 0);
            $g   = trim($_POST['new_grade']    ?? '');
            if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
            if (!in_array($g,['7','8','9','10','11','12'])) { echo json_encode(['success'=>false,'message'=>'Invalid grade.']); exit; }
            echo json_encode($this->advisoriesModel->updateStudentGradeByStudentId($sid,$g)); exit;
        }
    }

    public function updateSchoolYear() {
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $id  = intval($_POST['school_year_id'] ?? 0);
            $sy  = intval($_POST['start_year']     ?? 0);
            $ey  = intval($_POST['end_year']        ?? 0);
            if ($id<=0)                    { echo json_encode(['success'=>false,'message'=>'Invalid school year ID.']); exit; }
            if ($sy<2000||$sy>2100)        { echo json_encode(['success'=>false,'message'=>'Invalid start year.']); exit; }
            if ($ey<2000||$ey>2100)        { echo json_encode(['success'=>false,'message'=>'Invalid end year.']); exit; }
            if ($ey<=$sy)                  { echo json_encode(['success'=>false,'message'=>'End year must be after start year.']); exit; }
            echo json_encode($this->advisoriesModel->updateSchoolYear($id,$sy,$ey)); exit;
        }
    }

    /* ── Getters ─────────────────────────────────────────────── */
    public function getAllTeachers()      { return $this->advisoriesModel->getAllTeachers(); }
    public function getAdvisoryTeachers(){ return $this->advisoriesModel->getAdvisoryTeachers(); }
    public function getSubjectTeachers() { return $this->advisoriesModel->getSubjectTeachers(); }
    public function getAllStudents()      { return $this->advisoriesModel->getAllStudents(); }

    public function getAssignedStudents($filters=[]) {
        return $this->advisoriesModel->getAssignedStudents(
            $filters['teacher_role'] ?? '',
            $filters['grade_level']  ?? '',
            $filters['date_filter']  ?? '',
            $filters['search']       ?? '',
            $filters['sort_by']      ?? 'student_name',
            $filters['sort_order']   ?? 'ASC'
        );
    }

    public function getStudentsByAdvisory($advisory_id) {
        return $this->advisoriesModel->getStudentsByAdvisory($advisory_id);
    }

    /* ── AJAX dispatcher ─────────────────────────────────────── */
    public function handleAjaxRequest() {
        header('Content-Type: application/json');
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            /* ── filtered table data ── */
            case 'get_filtered_data':
                $filters = [
                    'teacher_role' => $_POST['teacher_role'] ?? '',
                    'grade_level'  => $_POST['grade_level']  ?? '',
                    'date_filter'  => $_POST['date_filter']  ?? '',
                    'search'       => $_POST['search']       ?? '',
                    'sort_by'      => $_POST['sort_by']      ?? 'student_name',
                    'sort_order'   => $_POST['sort_order']   ?? 'ASC',
                ];
                $data = $this->getAssignedStudents($filters);
                echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]);
                break;

            /* ── lists ── */
            case 'get_unassigned_students':
                $data = $this->advisoriesModel->getUnassignedStudents();
                echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]);
                break;

            case 'get_advisory_teachers':
                $data = $this->getAdvisoryTeachers();
                echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]);
                break;

            case 'get_subject_teachers':
                $data = $this->getSubjectTeachers();
                echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]);
                break;

            case 'get_advisory_list':
                $data = $this->advisoriesModel->getAdvisoryList($_POST['sort_by']??'advisory_name',$_POST['sort_order']??'ASC');
                echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]);
                break;

            case 'get_advisory_students':
                $data = $this->getStudentsByAdvisory(intval($_POST['advisory_id']??0));
                echo json_encode(['success'=>true,'data'=>$data,'count'=>count($data)]);
                break;

            /* ── student profile ── */
            case 'get_student_profile':
                $sid = intval($_POST['student_id']??0);
                if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $p = $this->advisoriesModel->getStudentProfile($sid);
                echo $p ? json_encode(['success'=>true,'data'=>$p]) : json_encode(['success'=>false,'message'=>'Not found.']);
                break;

            /* ── student academic history (assignment rows) ── */
            case 'get_student_history':
                $sid = intval($_POST['student_id']??0);
                if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $data = $this->advisoriesModel->getStudentHistory($sid);
                echo json_encode(['success'=>true,'data'=>$data]);
                break;

            /* ── student incidents ── */
            case 'get_student_incidents':
                $sid = intval($_POST['student_id']??0);
                if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $data = $this->advisoriesModel->getStudentIncidents($sid);
                echo json_encode(['success'=>true,'data'=>$data]);
                break;

            /* ── teacher profile ── */
            case 'get_teacher_profile':
                $aid = intval($_POST['advisory_id']??0);
                if ($aid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $p = $this->advisoriesModel->getTeacherProfile($aid);
                echo $p ? json_encode(['success'=>true,'data'=>$p]) : json_encode(['success'=>false,'message'=>'Not found.']);
                break;

            /* ── update student info ── */
            case 'update_student_info':
                $sid = intval($_POST['student_id']??0);
                if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $data = [
                    'first_name'       => trim($_POST['first_name']       ?? ''),
                    'mi'               => trim($_POST['mi']               ?? ''),
                    'last_name'        => trim($_POST['last_name']        ?? ''),
                    'lrn'              => trim($_POST['lrn']              ?? ''),
                    'contact_no'       => trim($_POST['contact_no']       ?? ''),
                    'home_address'     => trim($_POST['home_address']     ?? ''),
                    'guardian_name'    => trim($_POST['guardian_name']    ?? ''),
                    'guardian_contact' => trim($_POST['guardian_contact'] ?? ''),
                    'profile_pix'      => trim($_POST['profile_pix']      ?? ''),
                ];
                echo json_encode($this->advisoriesModel->updateStudentInfo($sid,$data));
                break;

            /* ── update teacher info ── */
            case 'update_teacher_info':
                $tid = intval($_POST['teacher_id']??0);
                if ($tid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $data = [
                    'teacher_id_field' => trim($_POST['teacher_id_field'] ?? ''),
                    'name'             => trim($_POST['name']             ?? ''),
                    'email'            => trim($_POST['email']            ?? ''),
                    'contact_no'       => trim($_POST['contact_no']       ?? ''),
                    'department'       => trim($_POST['department']       ?? ''),
                    'specialization'   => trim($_POST['specialization']   ?? ''),
                    'profile_pix'      => trim($_POST['profile_pix']      ?? ''),
                ];
                echo json_encode($this->advisoriesModel->updateTeacherInfo($tid,$data));
                break;

            /* ── grade update by student ID ── */
            case 'update_student_grade_by_id':
                $sid = intval($_POST['student_id']??0);
                $g   = trim($_POST['new_grade']??'');
                if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                if (!in_array($g,['7','8','9','10','11','12'])) { echo json_encode(['success'=>false,'message'=>'Invalid grade.']); break; }
                echo json_encode($this->advisoriesModel->updateStudentGradeByStudentId($sid,$g));
                break;

            /* ── photo uploads ── */
            case 'upload_profile_pic':         $this->uploadProfilePicture();        break;
            case 'upload_teacher_profile_pic': $this->uploadTeacherProfilePicture(); break;

            /* ────────────────────────────────────────────────────────
               GENERATE REPORT — single student full report as JSON
               (the view JS will open the print modal from this data)
            ──────────────────────────────────────────────────────── */
            case 'generate_student_report':
                $sid = intval($_POST['student_id']??0);
                if ($sid<=0) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); break; }
                $report = $this->advisoriesModel->getStudentFullReport($sid);
                echo json_encode(['success'=>true,'data'=>$report]);
                break;

            /* ── generate ALL students report ── */
            case 'generate_all_students_report':
                $reports = $this->advisoriesModel->getAllStudentsForReport();
                echo json_encode(['success'=>true,'data'=>$reports,'count'=>count($reports)]);
                break;

            default:
                echo json_encode(['success'=>false,'message'=>'Invalid action.']);
        }
        exit;
    }
}

/* ── Bootstrap ──────────────────────────────────────────────────── */
$advisoriesController = new AdvisoriesController($pdo);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';
    $directActions = [
        'assign_teacher'             => 'assignAdvisoryTeacher',
        'assign_students'            => 'assignStudentsToAdvisory',
        'reassign_student'           => 'reassignStudent',
        'remove_from_advisory'       => 'removeFromAdvisory',
        'convert_to_subject'         => 'convertToSubjectTeacher',
        'update_student_grade'       => 'updateStudentGrade',
        'bulk_update_student_grade'  => 'bulkUpdateStudentGrade',
        'update_student_grade_by_id' => 'updateStudentGradeById',
        'update_school_year'         => 'updateSchoolYear',
    ];

    if (isset($directActions[$action])) {
        $method = $directActions[$action];
        $advisoriesController->$method();
    } else {
        /* All AJAX actions */
        $ajaxActions = [
            'get_student_profile','get_student_history','get_student_incidents',
            'get_teacher_profile','update_student_info','update_teacher_info',
            'get_filtered_data','get_unassigned_students','get_advisory_teachers',
            'get_subject_teachers','get_advisory_list','get_advisory_students',
            'upload_profile_pic','upload_teacher_profile_pic',
            'update_student_grade_by_id',
            'generate_student_report','generate_all_students_report',
        ];
        if (in_array($action, $ajaxActions)) {
            $advisoriesController->handleAjaxRequest();
        }
    }
}

if ($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['ajax'])) {
    $advisoriesController->handleAjaxRequest();
}

require_once __DIR__ . '/../views/advisories.php';
?>