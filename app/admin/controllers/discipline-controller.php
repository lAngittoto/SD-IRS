<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

$isAjax = (
    (isset($_POST['ajax_action']) || isset($_GET['ajax_action'])) &&
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

if ($isAjax) {
    while (ob_get_level()) ob_end_clean();
    ob_start();
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/discipline-model.php';
require_once __DIR__ . '/audit-log-recorder.php'; // ← ADD

class DisciplineController {
    private $disciplineModel;
    private $pdo; // ← ADD
    private $perPage = 10;

    public function __construct($pdo) {
        $this->pdo = $pdo; // ← ADD
        $this->disciplineModel = new DisciplineModel($pdo);
    }

    private function sendJson($data) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        die(json_encode($data));
    }

    public function save() {
        try {
            $data = [
                'violation_name' => trim($_POST['violation_name'] ?? ''),
                'id_sanctions'   => intval($_POST['id_sanctions'] ?? 0),
                'id_warning'     => intval($_POST['id_warning']   ?? 0),
                'description'    => trim($_POST['description']    ?? '')
            ];

            if (empty($data['violation_name'])) $this->sendJson(['status'=>'error','message'=>'Violation name required']);
            if ($data['id_sanctions'] <= 0)      $this->sendJson(['status'=>'error','message'=>'Select a sanction']);
            if ($data['id_warning']   <= 0)      $this->sendJson(['status'=>'error','message'=>'Select severity level']);

            $id = isset($_POST['id_discipline']) ? intval($_POST['id_discipline']) : 0;

            if ($id > 0) {
                $result  = $this->disciplineModel->updateDisciplineConfig($id, $data);
                $message = 'Record updated successfully!';
                if ($result) auditRecord($this->pdo, 'UPDATE', 'Discipline', "Updated violation: {$data['violation_name']} (ID: {$id})"); // ← LOG
            } else {
                $result  = $this->disciplineModel->addDisciplineConfig($data);
                $message = 'Record added successfully!';
                if ($result) auditRecord($this->pdo, 'CREATE', 'Discipline', "Added new violation: {$data['violation_name']}"); // ← LOG
            }

            $this->sendJson([
                'status'  => $result ? 'success' : 'error',
                'message' => $result ? $message : 'Failed to save'
            ]);
        } catch (Exception $e) {
            $this->sendJson(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function getData() {
        try {
            $page = isset($_POST['page']) ? intval($_POST['page']) : (isset($_GET['page']) ? intval($_GET['page']) : 1);
            $page = max(1, $page);

            $disciplines  = $this->disciplineModel->getDisciplineRecords($page, $this->perPage);
            $totalRecords = $this->disciplineModel->getTotalDisciplineRecords();
            $totalPages   = max(1, ceil($totalRecords / $this->perPage));

            if ($page > $totalPages && $totalPages > 0) {
                $page        = $totalPages;
                $disciplines = $this->disciplineModel->getDisciplineRecords($page, $this->perPage);
            }

            $this->sendJson([
                'status'       => 'success',
                'disciplines'  => $disciplines,
                'page'         => $page,
                'totalPages'   => $totalPages,
                'totalRecords' => $totalRecords
            ]);
        } catch (Exception $e) {
            $this->sendJson(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function delete() {
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id <= 0) $this->sendJson(['status'=>'error','message'=>'Invalid ID']);

            $record = $this->disciplineModel->getDisciplineById($id);
            if (!$record) $this->sendJson(['status'=>'error','message'=>'Record not found']);

            $result = $this->disciplineModel->deleteDiscipline($id);
            if ($result) auditRecord($this->pdo, 'DELETE', 'Discipline', "Deleted violation: {$record['violation_name']} (ID: {$id})"); // ← LOG

            $this->sendJson([
                'status'  => $result ? 'success' : 'error',
                'message' => $result ? 'Deleted successfully!' : 'Failed to delete'
            ]);
        } catch (Exception $e) {
            $this->sendJson(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function getOptions() {
        try {
            $type = $_POST['type'] ?? $_GET['type'] ?? '';

            if ($type === 'sanction') {
                $options = $this->disciplineModel->getAllSanctions();
                $idField = 'id_sanctions';
            } elseif ($type === 'severity') {
                $options = $this->disciplineModel->getAllWarnings();
                $idField = 'id_warning';
            } else {
                $this->sendJson(['status'=>'error','message'=>'Invalid type']);
            }

            $formattedOptions = array_map(function($option) use ($idField) {
                return ['id' => $option[$idField], 'name' => $option['name']];
            }, $options);

            $this->sendJson(['status'=>'success','options'=>$formattedOptions]);
        } catch (Exception $e) {
            $this->sendJson(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function manageOptions() {
        try {
            $type   = $_POST['type']   ?? '';
            $action = $_POST['action'] ?? '';

            if (!in_array($type, ['sanction','severity'])) $this->sendJson(['status'=>'error','message'=>'Invalid type']);

            if ($action === 'add') {
                $name = trim($_POST['name'] ?? '');
                if (empty($name)) $this->sendJson(['status'=>'error','message'=>'Name required']);
                $result = $type === 'sanction'
                    ? $this->disciplineModel->addNewSanction($name)
                    : $this->disciplineModel->addNewWarning($name);
                if ($result) auditRecord($this->pdo, 'CREATE', 'Discipline', "Added new {$type}: {$name}"); // ← LOG
                $this->sendJson([
                    'status'  => $result ? 'success' : 'error',
                    'message' => $result ? 'Added successfully!' : 'Failed to add'
                ]);

            } elseif ($action === 'edit') {
                $id   = intval($_POST['id']   ?? 0);
                $name = trim($_POST['name'] ?? '');
                if ($id   <= 0)     $this->sendJson(['status'=>'error','message'=>'Invalid ID']);
                if (empty($name))   $this->sendJson(['status'=>'error','message'=>'Name required']);
                $result = $type === 'sanction'
                    ? $this->disciplineModel->updateSanction($id, $name)
                    : $this->disciplineModel->updateWarning($id, $name);
                if ($result) auditRecord($this->pdo, 'UPDATE', 'Discipline', "Updated {$type} ID {$id} to: {$name}"); // ← LOG
                $this->sendJson([
                    'status'  => $result ? 'success' : 'error',
                    'message' => $result ? 'Updated successfully!' : 'Failed to update'
                ]);

            } elseif ($action === 'delete') {
                $id = intval($_POST['id'] ?? 0);
                if ($id <= 0) $this->sendJson(['status'=>'error','message'=>'Invalid ID']);
                $result = $type === 'sanction'
                    ? $this->disciplineModel->deleteSanction($id)
                    : $this->disciplineModel->deleteWarning($id);
                if ($result === false) $this->sendJson(['status'=>'error','message'=>'Cannot delete. Item is being used.']);
                if ($result) auditRecord($this->pdo, 'DELETE', 'Discipline', "Deleted {$type} ID {$id}"); // ← LOG
                $this->sendJson([
                    'status'  => $result ? 'success' : 'error',
                    'message' => $result ? 'Deleted successfully!' : 'Failed to delete'
                ]);
            }

            $this->sendJson(['status'=>'error','message'=>'Invalid action']);
        } catch (Exception $e) {
            $this->sendJson(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function getDropdowns() {
        try {
            $sanctions = $this->disciplineModel->getAllSanctions();
            $warnings  = $this->disciplineModel->getAllWarnings();
            $this->sendJson(['status'=>'success','sanctions'=>$sanctions,'warnings'=>$warnings]);
        } catch (Exception $e) {
            $this->sendJson(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function getPageData($page = 1) {
        $page         = max(1, intval($page));
        $disciplines  = $this->disciplineModel->getDisciplineRecords($page, $this->perPage);
        $totalRecords = $this->disciplineModel->getTotalDisciplineRecords();
        $totalPages   = max(1, ceil($totalRecords / $this->perPage));
        return [
            'disciplines'  => $disciplines,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'totalRecords' => $totalRecords,
            'sanctions'    => $this->disciplineModel->getAllSanctions(),
            'warnings'     => $this->disciplineModel->getAllWarnings()
        ];
    }
}

$controller = new DisciplineController($pdo);

if ($isAjax) {
    $action = $_POST['ajax_action'] ?? $_GET['ajax_action'] ?? '';
    switch ($action) {
        case 'save':           $controller->save();          break;
        case 'delete':         $controller->delete();        break;
        case 'get-data':       $controller->getData();       break;
        case 'get-options':    $controller->getOptions();    break;
        case 'manage-options': $controller->manageOptions(); break;
        case 'get-dropdowns':  $controller->getDropdowns();  break;
        default:
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            die(json_encode(['status'=>'error','message'=>'Invalid action']));
    }
    exit;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$data = $controller->getPageData($page);
extract($data);

require_once __DIR__ . '/../views/discipline.php';