<?php

function authenticateUser($pdo, $identifier, $password) {
    $stmt = $pdo->prepare("
        SELECT id, name, email, username, password, role, active
        FROM users
        WHERE (email = ? OR username = ?) AND active = 1
    ");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    $storedHash = $user['password'];
    $authenticated = false;

    // Verificar se a password corresponde ao hash atual
    if (password_verify($password, $storedHash)) {
        $authenticated = true;
    }
    // Fallback para passwords antigas em texto claro (migração)
    elseif ($password === $storedHash) {
        $authenticated = true;
        // Migrar para hash e atualizar na base de dados
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$newHash, $user['id']]);
        // (Opcional) registrar log de migração
    }

    if (!$authenticated) {
        return false;
    }

    unset($user['password']);
    return $user;
}

/**
 * Regista um novo membro com password hashed.
 * Retorna o ID do utilizador criado ou false em caso de erro.
 */
function registerMember($pdo, $name, $username, $email, $password) {
    // Verificar se já existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        return false;
    }

    // Hash da password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (name, username, email, password, role, active)
        VALUES (?, ?, ?, ?, 'member', 1)
    ");
    if (!$stmt->execute([$name, $username, $email, $hash])) {
        return false;
    }
    $userId = $pdo->lastInsertId();

    // Inserir perfil de membro (plano básico por 1 mês)
    $stmt = $pdo->prepare("
        INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end)
        VALUES (?, 'basic', DATE('now'), DATE('now', '+1 month'))
    ");
    $stmt->execute([$userId]);

    return $userId;
}