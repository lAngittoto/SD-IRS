<?php
ob_start();
?>
<h1>Hi admin</h1>
<?php
$content =ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>