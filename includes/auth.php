<?php
/**
 * auth.php – Authentication helper functions.
 * Provides easy access to session‑based user information and permission checks.
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function isAdmin() {
    return ($_SESSION['active_role'] ?? '') === 'admin';
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
        header('Location: ../public/login.php');
        exit;
    }

    if ($roles === null) {
        return;
    }

    $allowedRoles = (array)$roles;
    $userRole = $_SESSION['active_role'] ?? ($_SESSION['role'] ?? '');

    // Check if the user's role matches directly
    if (in_array($userRole, $allowedRoles, true)) {
        return;
    }

    // Special case: if 'admin' is allowed and the user is an administrator (active area or flag)
    if (in_array('admin', $allowedRoles, true) && isAdmin()) {
        return;
    }

    http_response_code(403);

    // Fallback redirects based on detected role
    if ($userRole === 'trainer') {
        header('Location: ../trainer/dashboard.php');
        exit;
    }

    if ($userRole === 'member') {
        header('Location: ../member/dashboard.php');
        exit;
    }

    if ($userRole === 'admin') {
        header('Location: ../admin/dashboard.php');
        exit;
    }

    // Default: go to login page
    header('Location: ../public/login.php');
    exit;
}