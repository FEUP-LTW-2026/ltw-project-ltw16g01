<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function isAdmin() {
    return $_SESSION['active_role'] === 'admin';
}

function currentUser() {
    if (!isLoggedIn()) return null;

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['active_role'] ?? '',
        'is_admin' => (int)($_SESSION['is_admin'] ?? 0),
        'email' => $_SESSION['user_email'] ?? '',
        'photo' => $_SESSION['user_photo'] ?? null
    ];
}

function requireAuth($roles = null) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit;
    }

    if ($roles === null) {
        return;
    }

    $allowedRoles = (array)$roles;
    $userRole = $_SESSION['active_role'] ?? '';

    if (in_array($userRole, $allowedRoles, true)) {
        return;
    }

    if (in_array('admin', $allowedRoles, true) && isAdmin()) {
        return;
    }

    http_response_code(403);
    session_destroy();
    header('Location: ../pages/login.php');
}