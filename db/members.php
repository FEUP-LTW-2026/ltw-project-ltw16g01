<?php

/**
 * Retorna todos os membros (role='member') com filtros opcionais.
 */
function getAllMembersAdmin($pdo, $filters = []) {
    $sql = "SELECT u.id, u.name, u.username, u.email, u.active, u.created_at,
                   mp.membership_plan, mp.plan_start, mp.plan_end
            FROM users u
            LEFT JOIN member_profiles mp ON u.id = mp.user_id
            WHERE u.role = 'member'";
    $params = [];

    if (!empty($filters['name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }
    if (!empty($filters['plan'])) {
        $sql .= " AND mp.membership_plan = ?";
        $params[] = $filters['plan'];
    }

    $sql .= " ORDER BY u.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtém os dados de um membro específico (para edição).
 */
function getMemberById($pdo, $id) {
    $sql = "SELECT u.id, u.name, u.username, u.email, u.active, u.created_at,
                   mp.membership_plan, mp.plan_start, mp.plan_end
            FROM users u
            LEFT JOIN member_profiles mp ON u.id = mp.user_id
            WHERE u.id = ? AND u.role = 'member'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Cria um novo membro (user + member_profile).
 * @param string $defaultPassword Password em texto claro (será hasheada)
 */
function createMember($pdo, $name, $username, $email, $plan, $planEnd, $defaultPassword = 'changeme') {
    // Verificar duplicados
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) return false;

    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role, active) VALUES (?, ?, ?, ?, 'member', 1)");
        $stmt->execute([$name, $username, $email, $hash]);
        $userId = $pdo->lastInsertId();

        $planStart = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $plan, $planStart, $planEnd]);
        $pdo->commit();
        return $userId;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Atualiza os dados de um membro (user + perfil).
 */
function updateMember($pdo, $id, $name, $username, $email, $active, $plan, $planEnd) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, active = ? WHERE id = ? AND role = 'member'");
        $stmt->execute([$name, $username, $email, $active, $id]);

        $stmt = $pdo->prepare("UPDATE member_profiles SET membership_plan = ?, plan_end = ? WHERE user_id = ?");
        $stmt->execute([$plan, $planEnd, $id]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Elimina um membro (soft delete: active = 0).
 */
function deleteMember($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE users SET active = 0 WHERE id = ? AND role = 'member'");
    return $stmt->execute([$id]);
}