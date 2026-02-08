<?php
 session_start();
        session_destroy();
        header('Location: /student-discipline-and-incident-reporting-system/public');
        exit;
?>