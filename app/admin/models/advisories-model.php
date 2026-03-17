<?php
class AdvisoriesModel {
    private $conn;
    private $advisory_table    = 'advisory_classes';
    private $assignment_table  = 'student_advisory_assignments';
    private $users_table       = 'user_management';
    private $teacher_roles_table = 'teacher_roles';
    private $student_info_table  = 'student_info';
    private $school_years_table  = 'school_years';

    public function __construct($pdo) {
        $this->conn = $pdo;
        $this->ensureGuardianColumns();
    }

    private function ensureGuardianColumns() {
        try { $this->conn->exec("ALTER TABLE {$this->student_info_table} ADD COLUMN IF NOT EXISTS guardian_name VARCHAR(150) DEFAULT NULL"); } catch (PDOException $e) {}
        try { $this->conn->exec("ALTER TABLE {$this->student_info_table} ADD COLUMN IF NOT EXISTS guardian_contact VARCHAR(15) DEFAULT NULL"); } catch (PDOException $e) {}
    }

    /* ============================================================
       SCHOOL YEAR
    ============================================================ */
    public function getActiveSchoolYear() {
        try {
            $stmt = $this->conn->prepare("SELECT school_year_id, start_year, end_year FROM {$this->school_years_table} WHERE status = 'ACTIVE' LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return null; }
    }

    public function updateSchoolYear($school_year_id, $start_year, $end_year) {
        try {
            $stmt = $this->conn->prepare("UPDATE {$this->school_years_table} SET start_year = :sy, end_year = :ey WHERE school_year_id = :id");
            $stmt->execute([':sy' => $start_year, ':ey' => $end_year, ':id' => $school_year_id]);
            return ['success' => true, 'message' => "School year updated to {$start_year}–{$end_year}."];
        } catch (PDOException $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /* ============================================================
       INCIDENT HELPERS
    ============================================================ */

    /**
     * Get ALL incident reports for a student across ALL school years.
     * Returns full status (pending / reviewed / resolved / dismissed).
     */
    public function getStudentIncidents($student_id) {
        try {
            $sql = "
                SELECT
                    r.report_id,
                    r.status,
                    r.description,
                    r.location,
                    r.created_at,
                    r.admin_notes,
                    COALESCE(d.violation_name, r.custom_violation, 'N/A') AS violation_display,
                    wl.name  AS warning_level,
                    sa.name  AS sanction,
                    CONCAT(sy.start_year,'-',sy.end_year) AS school_year,
                    sy.start_year,
                    sy.end_year,
                    reporter.name AS reporter_name,
                    rev.name      AS reviewed_by_name,
                    r.reviewed_at
                FROM teacher_incident_reports r
                LEFT JOIN discipline      d       ON r.violation_id    = d.discipline_id
                LEFT JOIN warning_levels  wl      ON d.id_warning      = wl.id_warning
                LEFT JOIN sanctions       sa      ON d.id_sanctions    = sa.id_sanctions
                LEFT JOIN school_years    sy      ON r.school_year_id  = sy.school_year_id
                LEFT JOIN user_management reporter ON r.teacher_id     = reporter.user_id
                LEFT JOIN user_management rev      ON r.reviewed_by    = rev.user_id
                WHERE r.student_id = :sid
                ORDER BY r.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':sid' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getStudentIncidents: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns true if student has ANY incident with status pending OR reviewed.
     * Resolved and dismissed do NOT block promotion.
     */
    public function hasUnresolvedIncidents($student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS cnt
                FROM teacher_incident_reports
                WHERE student_id = :sid AND status IN ('pending','reviewed')
            ");
            $stmt->execute([':sid' => $student_id]);
            return intval($stmt->fetch(PDO::FETCH_ASSOC)['cnt']) > 0;
        } catch (PDOException $e) { return false; }
    }

    /**
     * Count of unresolved incidents for display in block modal.
     */
    public function countUnresolvedIncidents($student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS cnt
                FROM teacher_incident_reports
                WHERE student_id = :sid AND status IN ('pending','reviewed')
            ");
            $stmt->execute([':sid' => $student_id]);
            return intval($stmt->fetch(PDO::FETCH_ASSOC)['cnt']);
        } catch (PDOException $e) { return 0; }
    }

    /* ============================================================
       TEACHER ASSIGNMENT
    ============================================================ */
    public function assignAdvisoryTeacher($data) {
        try {
            $this->conn->beginTransaction();

            if ($data['role_type'] === 'advisory') {
                $chk = $this->conn->prepare("SELECT advisory_id FROM {$this->advisory_table} WHERE teacher_id = :tid");
                $chk->execute([':tid' => $data['teacher_id']]);
                if ($chk->rowCount() > 0) { $this->conn->rollBack(); return ['success' => false, 'message' => 'Teacher already assigned as advisory.']; }

                $chkN = $this->conn->prepare("SELECT advisory_id FROM {$this->advisory_table} WHERE LOWER(advisory_name)=LOWER(:n)");
                $chkN->execute([':n' => $data['advisory_name']]);
                if ($chkN->rowCount() > 0) { $this->conn->rollBack(); return ['success' => false, 'message' => 'Advisory class name already exists.']; }
            }

            $this->conn->prepare("DELETE FROM {$this->teacher_roles_table} WHERE teacher_id=:tid")->execute([':tid' => $data['teacher_id']]);
            $this->conn->prepare("INSERT INTO {$this->teacher_roles_table} (teacher_id,role_type) VALUES(:tid,:rt)")->execute([':tid' => $data['teacher_id'], ':rt' => $data['role_type']]);

            if ($data['role_type'] === 'advisory') {
                $this->conn->prepare("INSERT INTO {$this->advisory_table} (teacher_id,advisory_name,grade_level,created_at) VALUES(:tid,:an,:gl,NOW())")
                    ->execute([':tid' => $data['teacher_id'], ':an' => $data['advisory_name'], ':gl' => $data['grade_level']]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher assigned successfully!'];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function convertToSubjectTeacher($advisory_id) {
        try {
            $this->conn->beginTransaction();
            $t = $this->conn->prepare("SELECT teacher_id FROM {$this->advisory_table} WHERE advisory_id=:id");
            $t->execute([':id' => $advisory_id]);
            $teacher = $t->fetch(PDO::FETCH_ASSOC);
            if (!$teacher) { $this->conn->rollBack(); return ['success' => false, 'message' => 'Advisory not found.']; }

            $this->conn->prepare("DELETE FROM {$this->assignment_table} WHERE advisory_id=:id")->execute([':id' => $advisory_id]);
            $this->conn->prepare("DELETE FROM {$this->advisory_table}  WHERE advisory_id=:id")->execute([':id' => $advisory_id]);
            $this->conn->prepare("DELETE FROM {$this->teacher_roles_table} WHERE teacher_id=:tid")->execute([':tid' => $teacher['teacher_id']]);
            $this->conn->prepare("INSERT INTO {$this->teacher_roles_table} (teacher_id,role_type) VALUES(:tid,'subject')")->execute([':tid' => $teacher['teacher_id']]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher converted to Subject Teacher.'];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /* ============================================================
       STUDENT ASSIGNMENT
    ============================================================ */
    public function assignStudentsToAdvisory($advisory_id, $student_ids, $grade_levels, $school_year_id) {
        try {
            $this->conn->beginTransaction();

            $adv = $this->conn->prepare("SELECT advisory_id,grade_level FROM {$this->advisory_table} WHERE advisory_id=:id");
            $adv->execute([':id' => $advisory_id]);
            $advisory = $adv->fetch(PDO::FETCH_ASSOC);
            if (!$advisory) { $this->conn->rollBack(); return ['success' => false, 'message' => 'Advisory not found.']; }

            $cntStmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM {$this->assignment_table} WHERE advisory_id=:id");
            $cntStmt->execute([':id' => $advisory_id]);
            $current = intval($cntStmt->fetch(PDO::FETCH_ASSOC)['c']);
            if ($current + count($student_ids) > 40) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Only ' . (40 - $current) . ' slots remaining.'];
            }

            $successCount = 0; $skipped = 0;
            foreach ($student_ids as $sid) {
                $sid = intval($sid);
                $gl  = $grade_levels[$sid] ?? $advisory['grade_level'];

                /* Check if already assigned this school year */
                $chk = $this->conn->prepare("SELECT assignment_id FROM {$this->assignment_table} WHERE student_id=:sid AND school_year_id=:syid");
                $chk->execute([':sid' => $sid, ':syid' => $school_year_id]);
                if ($chk->rowCount() > 0) { $skipped++; continue; }

                $this->conn->prepare("INSERT INTO {$this->assignment_table} (advisory_id,student_id,grade_level,school_year_id,assigned_date) VALUES(:aid,:sid,:gl,:syid,NOW())")
                    ->execute([':aid' => $advisory_id, ':sid' => $sid, ':gl' => $gl, ':syid' => $school_year_id]);
                $successCount++;
            }
            $this->conn->commit();
            $msg = "{$successCount} student(s) assigned.";
            if ($skipped) $msg .= " {$skipped} already assigned.";
            return ['success' => true, 'message' => $msg];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function reassignStudent($assignment_id, $new_advisory_id, $current_grade) {
        try {
            $this->conn->beginTransaction();
            $cnt = $this->conn->prepare("SELECT COUNT(*) AS c FROM {$this->assignment_table} WHERE advisory_id=:id");
            $cnt->execute([':id' => $new_advisory_id]);
            if (intval($cnt->fetch(PDO::FETCH_ASSOC)['c']) >= 40) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Target advisory is full (40/40).'];
            }
            $this->conn->prepare("UPDATE {$this->assignment_table} SET advisory_id=:aid, assigned_date=NOW() WHERE assignment_id=:id")
                ->execute([':aid' => $new_advisory_id, ':id' => $assignment_id]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Student reassigned.'];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function removeFromAdvisory($assignment_id) {
        try {
            $this->conn->prepare("DELETE FROM {$this->assignment_table} WHERE assignment_id=:id")->execute([':id' => $assignment_id]);
            return ['success' => true, 'message' => 'Student removed.'];
        } catch (PDOException $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /* ============================================================
       GRADE PROMOTION
       KEY DESIGN: When promoting, we INSERT a new assignment row for
       the new school year (or same school year but different advisory
       grade) instead of deleting the old one. The old row remains as
       history. The "active" assignment is the one whose advisory's
       grade_level matches the student's current grade_level column.
       For simplicity we keep the same approach as original but
       PRESERVE history by logging to student_promotions and only
       removing the current advisory assignment.
    ============================================================ */

    /**
     * Promote from advisory view (single student, has assignment_id).
     * Blocks if unresolved incidents exist.
     * On grade change: logs to student_promotions, removes from current advisory.
     * History is preserved because old rows stay in student_advisory_assignments
     * for past school years. The student becomes "unassigned" in current SY
     * and must be manually re-assigned to a new Grade X advisory.
     */
    public function updateStudentGrade($assignment_id, $new_grade) {
        try {
            $this->conn->beginTransaction();

            $getQ = $this->conn->prepare("
                SELECT aa.advisory_id, aa.student_id, aa.grade_level AS current_grade, a.grade_level AS advisory_grade
                FROM {$this->assignment_table} aa
                INNER JOIN {$this->advisory_table} a ON aa.advisory_id = a.advisory_id
                WHERE aa.assignment_id = :id
            ");
            $getQ->execute([':id' => $assignment_id]);
            $asgn = $getQ->fetch(PDO::FETCH_ASSOC);
            if (!$asgn) { $this->conn->rollBack(); return ['success' => false, 'message' => 'Assignment not found.']; }

            /* Block promotion (upgrade) if unresolved incidents */
            if (intval($new_grade) > intval($asgn['current_grade'])) {
                if ($this->hasUnresolvedIncidents($asgn['student_id'])) {
                    $cnt = $this->countUnresolvedIncidents($asgn['student_id']);
                    $this->conn->rollBack();
                    return [
                        'success'         => false,
                        'blocked'         => true,
                        'message'         => 'Hindi maaaring i-promote: may hindi pa naresolbang incident report.',
                        'student_id'      => $asgn['student_id'],
                        'unresolved_count'=> $cnt,
                    ];
                }
            }

            if ($new_grade !== $asgn['current_grade']) {
                $this->ensurePromotionsTable();
                $this->conn->prepare("INSERT INTO student_promotions (student_id,from_grade,to_grade) VALUES(:sid,:fg,:tg)")
                    ->execute([':sid' => $asgn['student_id'], ':fg' => $asgn['current_grade'], ':tg' => $new_grade]);
                /* Remove from current advisory — history (old assignment rows) preserved */
                $this->conn->prepare("DELETE FROM {$this->assignment_table} WHERE assignment_id=:id")->execute([':id' => $assignment_id]);
                $this->conn->commit();
                return ['success' => true, 'message' => "Student promoted to Grade {$new_grade}. Assign to a Grade {$new_grade} advisory class.", 'grade_changed' => true];
            }

            /* Same grade, just update column */
            $this->conn->prepare("UPDATE {$this->assignment_table} SET grade_level=:g WHERE assignment_id=:id")
                ->execute([':g' => $new_grade, ':id' => $assignment_id]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Grade updated.'];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /**
     * Bulk promote selected students in an advisory.
     * Skips (with message) students with unresolved incidents.
     */
    public function bulkUpdateStudentGrade($assignment_ids, $new_grade) {
        try {
            $this->conn->beginTransaction();
            $this->ensurePromotionsTable();

            $successCount = 0; $blocked = []; $errors = [];

            foreach ($assignment_ids as $aid) {
                $getQ = $this->conn->prepare("
                    SELECT aa.student_id, aa.grade_level AS current_grade, u.name AS student_name
                    FROM {$this->assignment_table} aa
                    INNER JOIN {$this->users_table} u ON aa.student_id = u.user_id
                    WHERE aa.assignment_id = :id
                ");
                $getQ->execute([':id' => $aid]);
                $asgn = $getQ->fetch(PDO::FETCH_ASSOC);
                if (!$asgn) { $errors[] = 'Assignment not found'; continue; }

                if (intval($new_grade) <= intval($asgn['current_grade'])) {
                    $errors[] = "{$asgn['student_name']}: same/lower grade";
                    continue;
                }

                if ($this->hasUnresolvedIncidents($asgn['student_id'])) {
                    $blocked[] = $asgn['student_name'];
                    continue;
                }

                $this->conn->prepare("INSERT INTO student_promotions (student_id,from_grade,to_grade) VALUES(:sid,:fg,:tg)")
                    ->execute([':sid' => $asgn['student_id'], ':fg' => $asgn['current_grade'], ':tg' => $new_grade]);
                $this->conn->prepare("DELETE FROM {$this->assignment_table} WHERE assignment_id=:id")->execute([':id' => $aid]);
                $successCount++;
            }

            $this->conn->commit();

            $msg = '';
            if ($successCount > 0) $msg .= "{$successCount} student(s) promoted to Grade {$new_grade}. ";
            if (!empty($blocked))  $msg .= "Hindi na-promote (may unresolved incident): " . implode(', ', $blocked) . ". ";
            if (!empty($errors))   $msg .= "Note: " . implode(', ', $errors) . ".";

            if ($successCount > 0) return ['success' => true,  'message' => trim($msg), 'promoted_grade' => $new_grade, 'blocked_names' => $blocked];
            return ['success' => false, 'message' => trim($msg) ?: 'No students promoted.', 'blocked_names' => $blocked];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /**
     * Promote by student_id (from profile modal grade change).
     */
    public function updateStudentGradeByStudentId($student_id, $new_grade) {
        try {
            $this->conn->beginTransaction();

            if ($this->hasUnresolvedIncidents($student_id)) {
                $cnt = $this->countUnresolvedIncidents($student_id);
                $this->conn->rollBack();
                return [
                    'success'          => false,
                    'blocked'          => true,
                    'message'          => 'Hindi maaaring i-promote: may hindi pa naresolbang incident report.',
                    'student_id'       => $student_id,
                    'unresolved_count' => $cnt,
                ];
            }

            $cur = $this->conn->prepare("SELECT assignment_id,grade_level FROM {$this->assignment_table} WHERE student_id=:sid ORDER BY assigned_date DESC LIMIT 1");
            $cur->execute([':sid' => $student_id]);
            $current = $cur->fetch(PDO::FETCH_ASSOC);

            if ($current && $current['grade_level'] !== $new_grade) {
                $this->ensurePromotionsTable();
                $this->conn->prepare("INSERT INTO student_promotions (student_id,from_grade,to_grade) VALUES(:sid,:fg,:tg)")
                    ->execute([':sid' => $student_id, ':fg' => $current['grade_level'], ':tg' => $new_grade]);
                $this->conn->prepare("UPDATE {$this->assignment_table} SET grade_level=:g WHERE assignment_id=:id")
                    ->execute([':g' => $new_grade, ':id' => $current['assignment_id']]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => "Grade updated to Grade {$new_grade}."];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    private function ensurePromotionsTable() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS student_promotions (
            promotion_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id   INT UNSIGNED NOT NULL,
            from_grade   ENUM('7','8','9','10','11','12') NOT NULL,
            to_grade     ENUM('7','8','9','10','11','12') NOT NULL,
            promoted_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_student (student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /* ============================================================
       STUDENT PROFILE & HISTORY
    ============================================================ */
    public function getStudentProfile($student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT
                    u.user_id, u.name, u.email, u.lrn,
                    COALESCE(si.contact_no,'')       AS contact_no,
                    COALESCE(si.home_address,'')     AS home_address,
                    COALESCE(si.profile_pix,'')      AS profile_pix,
                    COALESCE(si.guardian_name,'')    AS guardian_name,
                    COALESCE(si.guardian_contact,'') AS guardian_contact,
                    COALESCE(aa.grade_level,'')      AS current_grade,
                    COALESCE(ac.advisory_name,'')    AS advisory_name,
                    COALESCE(t.name,'')              AS teacher_name
                FROM {$this->users_table} u
                LEFT JOIN {$this->student_info_table} si ON u.user_id = si.user_id
                LEFT JOIN {$this->assignment_table}   aa ON u.user_id = aa.student_id
                LEFT JOIN {$this->advisory_table}     ac ON aa.advisory_id = ac.advisory_id
                LEFT JOIN {$this->users_table}        t  ON ac.teacher_id  = t.user_id
                WHERE u.user_id = :sid AND u.role = 'Student'
                ORDER BY aa.assigned_date DESC LIMIT 1
            ");
            $stmt->execute([':sid' => $student_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return null; }
    }

    /**
     * Academic history: ALL assignment rows for the student across ALL school years.
     * Rows for past years are preserved (never deleted on promotion).
     */
    public function getStudentHistory($student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT
                    sy.school_year_id,
                    sy.start_year,
                    sy.end_year,
                    aa.grade_level,
                    aa.assigned_date,
                    ac.advisory_name,
                    t.name AS teacher_name
                FROM {$this->assignment_table} aa
                INNER JOIN {$this->school_years_table} sy ON aa.school_year_id = sy.school_year_id
                INNER JOIN {$this->advisory_table}     ac ON aa.advisory_id    = ac.advisory_id
                INNER JOIN {$this->users_table}        t  ON ac.teacher_id     = t.user_id
                WHERE aa.student_id = :sid
                ORDER BY sy.start_year DESC, aa.assigned_date DESC
            ");
            $stmt->execute([':sid' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    /**
     * Full student report data: profile + all history + all incidents.
     * Used for the print/generate report feature.
     */
    public function getStudentFullReport($student_id) {
        $profile  = $this->getStudentProfile($student_id);
        $history  = $this->getStudentHistory($student_id);
        $incidents = $this->getStudentIncidents($student_id);

        /* Also get all promotion logs */
        $promos = [];
        try {
            $stmt = $this->conn->prepare("
                SELECT from_grade, to_grade, promoted_at
                FROM student_promotions
                WHERE student_id = :sid
                ORDER BY promoted_at ASC
            ");
            $stmt->execute([':sid' => $student_id]);
            $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {}

        return [
            'profile'    => $profile,
            'history'    => $history,
            'incidents'  => $incidents,
            'promotions' => $promos,
        ];
    }

    /**
     * Get all students with full info for bulk report (print all).
     */
    public function getAllStudentsForReport() {
        try {
            $stmt = $this->conn->prepare("
                SELECT DISTINCT u.user_id
                FROM {$this->users_table} u
                WHERE u.role = 'Student'
                ORDER BY u.name ASC
            ");
            $stmt->execute();
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $results = [];
            foreach ($ids as $id) {
                $results[] = $this->getStudentFullReport($id);
            }
            return $results;
        } catch (PDOException $e) { return []; }
    }

    /* ============================================================
       STUDENT UPDATE
    ============================================================ */
    public function updateStudentInfo($student_id, $data) {
        try {
            $this->conn->beginTransaction();

            $fn = trim($data['first_name'] ?? '');
            $mi = trim($data['mi'] ?? '');
            $ln = trim($data['last_name'] ?? '');
            $full = $fn;
            if ($mi !== '') { if (substr($mi,-1)!=='.') $mi.='.'; $full .= ' '.$mi; }
            if ($ln !== '') $full .= ' '.$ln;
            $full = trim($full) ?: ($data['name'] ?? '');

            $this->conn->prepare("UPDATE {$this->users_table} SET name=:n, lrn=:lrn WHERE user_id=:uid AND role='Student'")
                ->execute([':n' => $full, ':lrn' => ($data['lrn'] ?: null), ':uid' => $student_id]);

            $this->conn->prepare("
                INSERT INTO {$this->student_info_table} (user_id,contact_no,home_address,profile_pix,guardian_name,guardian_contact)
                VALUES(:uid,:cn,:ha,:pp,:gn,:gc)
                ON DUPLICATE KEY UPDATE
                  contact_no=VALUES(contact_no), home_address=VALUES(home_address),
                  profile_pix=COALESCE(VALUES(profile_pix),profile_pix),
                  guardian_name=VALUES(guardian_name), guardian_contact=VALUES(guardian_contact)
            ")->execute([
                ':uid' => $student_id,
                ':cn'  => $data['contact_no']       ?? null,
                ':ha'  => $data['home_address']      ?? null,
                ':pp'  => $data['profile_pix']       ?: null,
                ':gn'  => $data['guardian_name']     ?? null,
                ':gc'  => $data['guardian_contact']  ?? null,
            ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Student record updated!'];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /* ============================================================
       TEACHER PROFILE
    ============================================================ */
    public function getTeacherProfile($advisory_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.user_id, u.name, u.email,
                       a.advisory_id, a.teacher_id, a.advisory_name, a.grade_level,
                       COALESCE(tr.role_type,'advisory') AS role_type
                FROM {$this->advisory_table} a
                INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id
                LEFT JOIN  {$this->teacher_roles_table} tr ON a.teacher_id = tr.teacher_id
                WHERE a.advisory_id = :id LIMIT 1
            ");
            $stmt->execute([':id' => $advisory_id]);
            $t = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$t) return null;

            $t['teacher_no']=$t['contact_no']=$t['department']=$t['advisory_section']=$t['profile_pix']=$t['specialization']='';
            try {
                $info = $this->conn->prepare("SELECT teacher_no,contact_no,department,advisory_section,profile_pix FROM teacher_info WHERE user_id=:uid LIMIT 1");
                $info->execute([':uid' => $t['user_id']]);
                $i = $info->fetch(PDO::FETCH_ASSOC);
                if ($i) {
                    $t['teacher_no']       = $i['teacher_no'] ?? '';
                    $t['contact_no']       = $i['contact_no'] ?? '';
                    $t['department']       = $i['department'] ?? '';
                    $t['advisory_section'] = $i['advisory_section'] ?? '';
                    $t['profile_pix']      = $i['profile_pix'] ?? '';
                    $t['specialization']   = $i['advisory_section'] ?? '';
                }
            } catch (PDOException $e) {}
            return $t;
        } catch (PDOException $e) { return null; }
    }

    public function updateTeacherInfo($teacher_id, $data) {
        try {
            $this->conn->beginTransaction();
            $name = trim($data['name'] ?? '');
            $this->conn->prepare("UPDATE {$this->users_table} SET name=:n, email=:e WHERE user_id=:uid AND role='Teacher'")
                ->execute([':n' => $name, ':e' => $data['email'] ?? null, ':uid' => $teacher_id]);

            $this->conn->exec("CREATE TABLE IF NOT EXISTS teacher_info (
                teacher_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                teacher_no VARCHAR(50), contact_no VARCHAR(15), department VARCHAR(100),
                advisory_section VARCHAR(100), profile_pix VARCHAR(255),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_teacher_user FOREIGN KEY (user_id) REFERENCES user_management(user_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->conn->prepare("
                INSERT INTO teacher_info (user_id,teacher_no,contact_no,department,advisory_section,profile_pix)
                VALUES(:uid,:tn,:cn,:dep,:as,:pp)
                ON DUPLICATE KEY UPDATE
                  teacher_no=VALUES(teacher_no), contact_no=VALUES(contact_no),
                  department=VALUES(department), advisory_section=VALUES(advisory_section),
                  profile_pix=COALESCE(VALUES(profile_pix),profile_pix)
            ")->execute([
                ':uid' => $teacher_id,
                ':tn'  => $data['teacher_id_field'] ?? null,
                ':cn'  => $data['contact_no']       ?? null,
                ':dep' => $data['department']        ?? null,
                ':as'  => $data['specialization']   ?? null,
                ':pp'  => $data['profile_pix']       ?: null,
            ]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher record updated!'];
        } catch (PDOException $e) { $this->conn->rollBack(); return ['success' => false, 'message' => $e->getMessage()]; }
    }

    /* ============================================================
       LISTS / FILTERS
    ============================================================ */
    public function getAdvisoryById($advisory_id) {
        try {
            $s = $this->conn->prepare("SELECT advisory_id,teacher_id,advisory_name,grade_level FROM {$this->advisory_table} WHERE advisory_id=:id");
            $s->execute([':id' => $advisory_id]);
            return $s->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return null; }
    }

    public function getAllTeachers() {
        try {
            $s = $this->conn->prepare("
                SELECT u.user_id, u.name, u.email FROM {$this->users_table} u
                LEFT JOIN {$this->advisory_table} a ON u.user_id = a.teacher_id
                WHERE u.role='Teacher' AND a.advisory_id IS NULL ORDER BY u.name
            ");
            $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getAdvisoryTeachers() {
        try {
            $s = $this->conn->prepare("
                SELECT a.advisory_id, a.teacher_id, u.name AS teacher_name, u.email AS teacher_email,
                       a.advisory_name, a.grade_level, tr.role_type, a.created_at AS assigned_date,
                       COUNT(aa.assignment_id) AS student_count
                FROM {$this->advisory_table} a
                INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id
                LEFT JOIN  {$this->teacher_roles_table} tr ON a.teacher_id = tr.teacher_id
                LEFT JOIN  {$this->assignment_table} aa ON a.advisory_id = aa.advisory_id
                GROUP BY a.advisory_id, a.teacher_id, u.name, u.email, a.advisory_name, a.grade_level, tr.role_type, a.created_at
                ORDER BY a.created_at DESC
            ");
            $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getSubjectTeachers() {
        try {
            $s = $this->conn->prepare("
                SELECT u.user_id AS teacher_id, u.name AS teacher_name, u.email AS teacher_email,
                       tr.role_type, tr.assigned_at
                FROM {$this->teacher_roles_table} tr
                INNER JOIN {$this->users_table} u ON tr.teacher_id = u.user_id
                WHERE tr.role_type='subject' ORDER BY tr.assigned_at DESC
            ");
            $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getAllStudents() {
        try {
            $s = $this->conn->prepare("
                SELECT DISTINCT u.user_id, u.name, u.lrn, '7' AS grade_level
                FROM {$this->users_table} u
                LEFT JOIN {$this->assignment_table} aa ON u.user_id = aa.student_id
                WHERE u.role='Student' AND aa.assignment_id IS NULL ORDER BY u.name
            ");
            $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getUnassignedStudents() {
        try {
            $activeSY = $this->getActiveSchoolYear();
            $syId     = $activeSY ? $activeSY['school_year_id'] : 0;

            $s = $this->conn->prepare("
                SELECT u.user_id, u.name, u.lrn,
                       COALESCE(
                           (SELECT sp.to_grade FROM student_promotions sp WHERE sp.student_id=u.user_id ORDER BY sp.promoted_at DESC LIMIT 1),
                           '7'
                       ) AS grade_level
                FROM {$this->users_table} u
                WHERE u.role='Student'
                  AND u.user_id NOT IN (
                      SELECT student_id FROM {$this->assignment_table} WHERE school_year_id = :syid
                  )
                ORDER BY u.name
            ");
            $s->execute([':syid' => $syId]);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getAssignedStudents($teacher_role='', $grade_level='', $date_filter='', $search='', $sort_by='student_name', $sort_order='ASC') {
        try {
            $sort_by    = in_array($sort_by, ['student_name','lrn','grade_level','teacher_name','advisory_name','assigned_date']) ? $sort_by : 'student_name';
            $sort_order = strtoupper($sort_order)==='DESC' ? 'DESC' : 'ASC';

            /* Only show CURRENT school year assignments */
            $activeSY = $this->getActiveSchoolYear();
            $syId     = $activeSY ? $activeSY['school_year_id'] : 0;

            $q = "
                SELECT aa.assignment_id, aa.student_id, s.name AS student_name, s.lrn,
                       aa.grade_level, aa.assigned_date,
                       at2.advisory_id, at2.advisory_name, tr.role_type,
                       t.name AS teacher_name, t.email AS teacher_email
                FROM {$this->assignment_table} aa
                INNER JOIN {$this->users_table}  s   ON aa.student_id  = s.user_id
                INNER JOIN {$this->advisory_table} at2 ON aa.advisory_id = at2.advisory_id
                INNER JOIN {$this->users_table}  t   ON at2.teacher_id  = t.user_id
                LEFT JOIN  {$this->teacher_roles_table} tr ON at2.teacher_id = tr.teacher_id
                WHERE aa.school_year_id = :syid
            ";
            $params = [':syid' => $syId];

            if (!empty($teacher_role))  { $q .= " AND tr.role_type=:tr";  $params[':tr']  = $teacher_role; }
            if (!empty($grade_level))   { $q .= " AND aa.grade_level=:gl"; $params[':gl'] = $grade_level; }
            if (!empty($date_filter))   { $q .= " AND DATE(aa.assigned_date)=:df"; $params[':df'] = $date_filter; }
            if (!empty($search)) {
                $q .= " AND (s.name LIKE :s OR s.lrn LIKE :s OR t.name LIKE :s OR at2.advisory_name LIKE :s)";
                $params[':s'] = "%{$search}%";
            }

            $colMap = ['student_name'=>'s.name','lrn'=>'s.lrn','grade_level'=>'aa.grade_level','teacher_name'=>'t.name','advisory_name'=>'at2.advisory_name','assigned_date'=>'aa.assigned_date'];
            $q .= " ORDER BY {$colMap[$sort_by]} {$sort_order}";

            $stmt = $this->conn->prepare($q);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getAdvisoryList($sort_by='advisory_name', $sort_order='ASC') {
        try {
            $sort_by    = in_array($sort_by, ['advisory_name','teacher_name','grade_level','student_count']) ? $sort_by : 'advisory_name';
            $sort_order = strtoupper($sort_order)==='DESC' ? 'DESC' : 'ASC';
            $colMap     = ['advisory_name'=>'a.advisory_name','teacher_name'=>'u.name','grade_level'=>'a.grade_level','student_count'=>'student_count'];

            $s = $this->conn->prepare("
                SELECT a.advisory_id, a.advisory_name, a.grade_level, u.name AS teacher_name,
                       COUNT(aa.assignment_id) AS student_count, a.created_at
                FROM {$this->advisory_table} a
                INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id
                LEFT JOIN  {$this->assignment_table} aa ON a.advisory_id = aa.advisory_id
                GROUP BY a.advisory_id, a.advisory_name, a.grade_level, u.name, a.created_at
                ORDER BY {$colMap[$sort_by]} {$sort_order}
            ");
            $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getStudentsByAdvisory($advisory_id) {
        try {
            $s = $this->conn->prepare("
                SELECT aa.assignment_id, aa.student_id, s.name AS student_name, s.lrn,
                       aa.grade_level, aa.assigned_date
                FROM {$this->assignment_table} aa
                INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id
                WHERE aa.advisory_id = :id
                ORDER BY aa.grade_level, s.name
            ");
            $s->execute([':id' => $advisory_id]);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
}
?>