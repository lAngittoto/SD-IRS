<?php
class DashboardModel {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    // ==================== STUDENT STATISTICS ====================
    
    public function getTotalStudents() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM user_management 
            WHERE role = 'Student'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    // ==================== FACULTY STATISTICS ====================
    
    public function getTotalFaculty() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM user_management 
            WHERE role = 'Teacher'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    // ==================== INCIDENT STATISTICS ====================
    
    public function getTotalIncidents() {
        // Adjust table name based on your database
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM incidents
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0; // Return 0 if table doesn't exist
        }
    }
    
    public function getPendingReports() {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM incidents 
                WHERE status = 'Pending'
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getResolvedCases() {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM incidents 
                WHERE status = 'Resolved'
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    // ==================== POLICY VIOLATIONS ====================
    
    public function getAllViolationNames() {
        $stmt = $this->db->query("
            SELECT 
                d.discipline_id,
                d.violation_name,
                d.description,
                s.name as sanction,
                w.name as severity
            FROM discipline d
            LEFT JOIN sanctions s ON d.id_sanctions = s.id_sanctions
            LEFT JOIN warning_levels w ON d.id_warning = w.id_warning
            ORDER BY d.violation_name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopViolations($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    d.violation_name,
                    w.name as severity,
                    s.name as sanction,
                    COUNT(i.incident_id) as violation_count
                FROM discipline d
                LEFT JOIN incidents i ON i.discipline_id = d.discipline_id
                LEFT JOIN warning_levels w ON d.id_warning = w.id_warning
                LEFT JOIN sanctions s ON d.id_sanctions = s.id_sanctions
                GROUP BY d.discipline_id, d.violation_name, w.name, s.name
                HAVING violation_count > 0
                ORDER BY violation_count DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // ==================== TRENDS AND ANALYTICS ====================
    
    public function getMonthlyTrends() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(incident_date, '%b %Y') as month_name,
                    COUNT(*) as incident_count
                FROM incidents
                WHERE incident_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(incident_date, '%Y-%m')
                ORDER BY incident_date ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getViolationBreakdown() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    w.name as severity,
                    COUNT(i.incident_id) as count
                FROM warning_levels w
                LEFT JOIN discipline d ON w.id_warning = d.id_warning
                LEFT JOIN incidents i ON i.discipline_id = d.discipline_id
                WHERE i.incident_id IS NOT NULL
                GROUP BY w.id_warning, w.name
                ORDER BY count DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // ==================== SUMMARY STATISTICS ====================
    
    public function getDashboardSummary() {
        return [
            'total_students' => $this->getTotalStudents(),
            'total_faculty' => $this->getTotalFaculty(),
            'total_incidents' => $this->getTotalIncidents(),
            'pending_reports' => $this->getPendingReports(),
            'resolved_cases' => $this->getResolvedCases()
        ];
    }
}
?>