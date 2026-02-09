<?php
ob_start();
?>

<main class="ml-64 min-h-screen bg-gray-100 p-8 w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

</main>
<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>