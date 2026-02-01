<?php
session_start();


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ .'/../config/database.php';

$page = $_GET['page'] ?? 'login';

require_once __DIR__ . '/../routes/routing.php';
