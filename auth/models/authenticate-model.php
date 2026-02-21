<?php
function getUser($pdo, $usernamename) {
    $stmt = $pdo->prepare('SELECT * FROM user_management WHERE name = ?');
    $stmt->execute([$usernamename]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}