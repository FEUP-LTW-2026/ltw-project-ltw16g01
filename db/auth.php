<?php

function authenticateUser($pdo, $identifier, $password) {
    $stmt = $pdo->prepare("SELECT id, name, email, username, password, role, photo FROM users WHERE (email = ? OR username = ?) AND active = 1");
    $stmt->execute([$identifier, $identifier]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    unset($user['password']);

    return $user;
}

function registerMember($pdo, $name, $username, $email, $password) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role, active) VALUES (?, ?, ?, ?, 'member', 1)");

    if (!$stmt->execute([$name, $username, $email, $hash])) {
        return false;
    }

    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end) VALUES (?, 'basic', DATE('now'), DATE('now', '+1 month'))");
    $stmt->execute([$userId]);

    return $userId;
}