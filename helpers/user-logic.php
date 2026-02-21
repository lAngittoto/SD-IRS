<?php
// Initialize controller with PDO (assuming $pdo is available from routing/config)
$userController = new UserController($pdo);

// 1. Handle form submission (Create User)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->createUser();
}

// 2. Kunin ang filters mula sa URL (Para sa Search, Role, at Sort)
$page = isset($_GET['p']) ? (int)$_GET['p'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
$sortFilter = isset($_GET['sort']) ? $_GET['sort'] : 'latest'; 
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

$limit = 100;

// 3. Kunin ang Total Users base sa filters para tama ang bilang ng pages
// Dapat ang getTotalUsers mo ay tumatanggap na ng ($role, $search)
$totalUsers = $userController->getTotalUsers($roleFilter, $searchFilter);
$totalPages = ceil($totalUsers / $limit);

// Siguraduhin na hindi lalampas ang page sa total pages
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$page = max($page, 1);

$offset = ($page - 1) * $limit;

// 4. Kunin ang users base sa 5 arguments para sa Smart Search
$users = $userController->getUsersPaginated($limit, $offset, $roleFilter, $sortFilter, $searchFilter);
?>