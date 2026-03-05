<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/teacher-sidebar.php'; ?>
    hhh
    <main/>
<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>