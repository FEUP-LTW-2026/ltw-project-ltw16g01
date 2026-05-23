<?php

function authenticateUser($pdo, $identifier, $password) {
    $stmt = $pdo->prepare("
        SELECT id, name, email, username, password, role, is_admin, photo
        FROM users
        WHERE (email = ? OR username = ?)
          AND active = 1
        LIMIT 1
    ");

    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    $validPassword = password_verify($password, $user['password']) || $password === $user['password'];

    if (!$validPassword) {
        return false;
    }

    unset($user['password']);

    $user['is_admin'] = (int)($user['is_admin'] ?? 0);

    return $user;
}

function registerMember($pdo, $name, $username, $email, $password, $plan = 'basic') {
    $allowedPlans = ['basic', 'premium', 'vip'];

    if (!in_array($plan, $allowedPlans, true)) {
        $plan = 'basic';
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, username, email, password, role, active)
        VALUES (?, ?, ?, ?, 'member', 1)
    ");

    if (!$stmt->execute([$name, $username, $email, $hash])) {
        return false;
    }

    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end)
        VALUES (?, ?, DATE('now'), DATE('now', '+1 month'))
    ");

    $stmt->execute([$userId, $plan]);

    return $userId;
}

function createPasswordResetToken($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return false;
    }
    $userId = $user['id'];

    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    if ($stmt->execute([$userId, $token, $expiresAt])) {
        return $token;
    }
    return false;
}

function validateResetToken($pdo, $token) {
    $stmt = $pdo->prepare("
        SELECT user_id FROM password_resets
        WHERE token = ? AND used = 0 AND expires_at > CURRENT_TIMESTAMP
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return (int)$row['user_id'];
    }
    return false;
}

function resetPassword($pdo, $token, $newPassword) {
    $userId = validateResetToken($pdo, $token);
    if (!$userId) {
        return false;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt->execute([$hash, $userId])) {
        return false;
    }

    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);
    return true;
}