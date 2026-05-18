<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUser() {
    if (!isLoggedIn()) return null;

    return ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name'] ?? '', 'role' => $_SESSION['role'] ?? '', 'email' => $_SESSION['user_email'] ?? '', 'photo' => $_SESSION['user_photo'] ?? null];
}

function requireAuth($roles = null) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit;
    }

    if ($roles !== null && !in_array($_SESSION['role'] ?? '', (array)$roles)) {
        http_response_code(403);
        die('Acesso negado.');
    }
}