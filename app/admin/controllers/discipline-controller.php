<?php
// controllers/DisciplineController.php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/discipline-model.php';

class DisciplineController {
    private $model;
    
    public function __construct($db) {
        $this->model = new DisciplineModel($db);
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /student-discipline-and-incident-reporting-system/public');
            exit;
        }
    }
    
    public function index() {
        $this->checkAuth();
        
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
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_discipline'] ?? '';
            $data = [
                'violation_name' => trim($_POST['violation_name'] ?? ''),
                'id_sanctions'   => $_POST['id_sanctions'] ?? '',
                'id_warning'     => $_POST['id_warning'] ?? '',
                'description'    => trim($_POST['description'] ?? '')
            ];
            
            // Validation
            if (empty($data['violation_name']) || empty($data['id_sanctions']) || empty($data['id_warning'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'All fields are required.'
                ]);
                exit();
            }
            
            if ($id) {
                // Update existing
                $result = $this->model->updateDisciplineConfig($id, $data);
                $message = 'Discipline record updated successfully!';
            } else {
                // Add new
                $result = $this->model->addDisciplineConfig($data);
                $message = 'Discipline record added successfully!';
            }
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $message
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save discipline record.'
                ]);
            }
        }
        exit();
    }
    
    public function getData() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        
        $disciplines = $this->model->getDisciplineRecords($page, $perPage);
        $totalRecords = $this->model->getTotalDisciplineRecords();
        $totalPages = max(1, ceil($totalRecords / $perPage));
        
        echo json_encode([
            'status' => 'success',
            'disciplines' => $disciplines,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords
        ]);
        exit();
    }
    
    public function getDiscipline() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            exit();
        }
        
        $discipline = $this->model->getDisciplineById($id);
        
        if ($discipline) {
            echo json_encode([
                'status' => 'success',
                'discipline' => $discipline
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Record not found'
            ]);
        }
        exit();
    }
    
    public function deleteDiscipline() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ID is required']);
                exit();
            }
            
            if ($this->model->deleteDiscipline($id)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete'
                ]);
            }
        }
        exit();
    }
    
    public function getOptions() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? '';
        
        if ($type === 'sanction') {
            $options = $this->model->getAllSanctions();
            $options = array_map(function($item) {
                return ['id' => $item['id_sanctions'], 'name' => $item['name']];
            }, $options);
        } elseif ($type === 'severity') {
            $options = $this->model->getAllWarnings();
            $options = array_map(function($item) {
                return ['id' => $item['id_warning'], 'name' => $item['name']];
            }, $options);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
            exit();
        }
        
        echo json_encode([
            'status' => 'success',
            'options' => $options
        ]);
        exit();
    }
    
    public function getDropdowns() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        echo json_encode([
            'status' => 'success',
            'sanctions' => $this->model->getAllSanctions(),
            'warnings' => $this->model->getAllWarnings()
        ]);
        exit();
    }
    
    public function manageOptions() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
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
            
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            
            if (empty($id) || empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'ID and name are required']);
                exit();
            }
            
            try {
                if ($type === 'sanction') {
                    $result = $this->model->updateSanction($id, $name);
                } elseif ($type === 'severity') {
                    $result = $this->model->updateWarning($id, $name);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
                    exit();
                }
                
                if ($result) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
                }
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
} elseif ($action === 'get-data') {
    $controller->getData();
} elseif ($action === 'get-discipline') {
    $controller->getDiscipline();
} elseif ($action === 'delete-discipline') {
    $controller->deleteDiscipline();
} elseif ($action === 'manage-options') {
    $controller->manageOptions();
} elseif ($action === 'get-options') {
    $controller->getOptions();
} elseif ($action === 'get-dropdowns') {
    $controller->getDropdowns();
} else {
    $controller->index();
}
?>