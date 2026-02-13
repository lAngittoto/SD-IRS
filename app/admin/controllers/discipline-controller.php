<?php
// controllers/DisciplineController.php

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/discipline-model.php';

class DisciplineController {
    private $model;
    
    public function __construct($db) {
        $this->model = new DisciplineModel($db);
    }
    
    public function index() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        
        $sanctions = $this->model->getAllSanctions();
        $warnings = $this->model->getAllWarnings();
        $disciplines = $this->model->getDisciplineRecords($page, $perPage);
        $totalRecords = $this->model->getTotalDisciplineRecords();
        $totalPages = max(1, ceil($totalRecords / $perPage));
        
        include __DIR__ . '/../views/discipline.php';
    }
    
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'violation_name' => trim($_POST['violation_name'] ?? ''),
                'id_sanctions'   => $_POST['id_sanctions'] ?? '',
                'id_warning'     => $_POST['id_warning'] ?? '',
                'description'    => trim($_POST['description'] ?? '')
            ];
            
            // Validation
            if (empty($data['violation_name']) || empty($data['id_sanctions']) || empty($data['id_warning'])) {
                header("Location: /student-discipline-and-incident-reporting-system/public/discipline-records?status=error");
                exit();
            }
            
            if ($this->model->addDisciplineConfig($data)) {
                header("Location: /student-discipline-and-incident-reporting-system/public/discipline-records?status=success");
            } else {
                header("Location: /student-discipline-and-incident-reporting-system/public/discipline-records?status=error");
            }
            exit();
        }
    }
    
    // AJAX Endpoint for managing options
    public function manageOptions() {
        header('Content-Type: application/json');
        
        $type = $_POST['type'] ?? '';
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Name is required']);
                exit();
            }
            
            try {
                if ($type === 'sanction') {
                    $id = $this->model->addNewSanction($name);
                    $item = $this->model->getSanctionById($id);
                } elseif ($type === 'severity') {
                    $id = $this->model->addNewWarning($name);
                    $item = $this->model->getWarningById($id);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
                    exit();
                }
                
                echo json_encode([
                    'status' => 'success', 
                    'id' => $id, 
                    'name' => $name,
                    'data' => $item
                ]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ID is required']);
                exit();
            }
            
            try {
                if ($type === 'sanction') {
                    $result = $this->model->deleteSanction($id);
                } elseif ($type === 'severity') {
                    $result = $this->model->deleteWarning($id);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
                    exit();
                }
                
                if ($result === false) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'Cannot delete. This item is being used in discipline records.'
                    ]);
                } else {
                    echo json_encode(['status' => 'success']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
        
        exit();
    }
}

// Initialize controller
$controller = new DisciplineController($pdo);

// Route handling
$action = $_GET['action'] ?? 'index';

if ($action === 'save') {
    $controller->save();
} elseif ($action === 'manage-options') {
    $controller->manageOptions();
} else {
    $controller->index();
}
?>