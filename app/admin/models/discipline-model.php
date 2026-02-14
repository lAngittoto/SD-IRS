<?php
// models/discipline-model.php
class DisciplineModel {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    // ==================== DISCIPLINE RECORDS ====================
    
    public function getDisciplineRecords($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                s.name as sanction,
                w.name as severity
            FROM discipline d
            LEFT JOIN sanctions s ON d.id_sanctions = s.id_sanctions
            LEFT JOIN warning_levels w ON d.id_warning = w.id_warning
            ORDER BY d.date_created DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalDisciplineRecords() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM discipline");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getDisciplineById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                s.name as sanction,
                w.name as severity
            FROM discipline d
            LEFT JOIN sanctions s ON d.id_sanctions = s.id_sanctions
            LEFT JOIN warning_levels w ON d.id_warning = w.id_warning
            WHERE d.id_discipline = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addDisciplineConfig($data) {
        $stmt = $this->db->prepare("
            INSERT INTO discipline (violation_name, id_sanctions, id_warning, description, date_created) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['violation_name'], 
            $data['id_sanctions'], 
            $data['id_warning'], 
            $data['description']
        ]);
    }
    
    public function updateDisciplineConfig($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE discipline 
            SET violation_name = ?, 
                id_sanctions = ?, 
                id_warning = ?, 
                description = ?
            WHERE id_discipline = ?
        ");
        return $stmt->execute([
            $data['violation_name'], 
            $data['id_sanctions'], 
            $data['id_warning'], 
            $data['description'],
            $id
        ]);
    }
    
    public function deleteDiscipline($id) {
        $stmt = $this->db->prepare("DELETE FROM discipline WHERE id_discipline = ?");
        return $stmt->execute([$id]);
    }
    
    // ==================== SANCTIONS ====================
    
    public function getAllSanctions() {
        return $this->db->query("SELECT * FROM sanctions ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSanctionById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sanctions WHERE id_sanctions = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addNewSanction($name) {
        $stmt = $this->db->prepare("INSERT INTO sanctions (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }
    
    public function updateSanction($id, $name) {
        $stmt = $this->db->prepare("UPDATE sanctions SET name = ? WHERE id_sanctions = ?");
        return $stmt->execute([$name, $id]);
    }
    
    public function deleteSanction($id) {
        // Check if sanction is being used
        $check = $this->db->prepare("SELECT COUNT(*) as count FROM discipline WHERE id_sanctions = ?");
        $check->execute([$id]);
        if ($check->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // Cannot delete if in use
        }
        
        $stmt = $this->db->prepare("DELETE FROM sanctions WHERE id_sanctions = ?");
        return $stmt->execute([$id]);
    }
    
    // ==================== WARNING LEVELS ====================
    
    public function getAllWarnings() {
        return $this->db->query("SELECT * FROM warning_levels ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getWarningById($id) {
        $stmt = $this->db->prepare("SELECT * FROM warning_levels WHERE id_warning = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addNewWarning($name) {
        $stmt = $this->db->prepare("INSERT INTO warning_levels (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }
    
    public function updateWarning($id, $name) {
        $stmt = $this->db->prepare("UPDATE warning_levels SET name = ? WHERE id_warning = ?");
        return $stmt->execute([$name, $id]);
    }
    
    public function deleteWarning($id) {
        // Check if warning is being used
        $check = $this->db->prepare("SELECT COUNT(*) as count FROM discipline WHERE id_warning = ?");
        $check->execute([$id]);
        if ($check->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // Cannot delete if in use
        }
        
        $stmt = $this->db->prepare("DELETE FROM warning_levels WHERE id_warning = ?");
        return $stmt->execute([$id]);
    }
}
?>