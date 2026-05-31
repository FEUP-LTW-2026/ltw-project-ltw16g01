<?php
/**
 * equipment.php – Functions for managing equipment and reservations (VIP only).
 */

function getAllEquipmentCategories($pdo) {
    $stmt = $pdo->prepare("
        SELECT id, name, label 
        FROM equipment_categories 
        WHERE active = 1 
        ORDER BY label
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllEquipment($pdo) {
    $sql = "
        SELECT 
            e.*, 
            ec.label AS category_label, 
            ec.name AS category_name 
        FROM equipment e 
        JOIN equipment_categories ec ON e.category_id = ec.id 
        ORDER BY ec.label, e.name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEquipmentById($pdo, $id) {
    $sql = "
        SELECT 
            e.*, 
            ec.label AS category_label 
        FROM equipment e 
        JOIN equipment_categories ec ON e.category_id = ec.id 
        WHERE e.id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEquipmentByCategory($pdo, $categoryId) {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM equipment 
        WHERE category_id = ? 
        ORDER BY name
    ");

    $stmt->execute([$categoryId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createEquipment($pdo, $name, $categoryId, $quantity, $available, $status, $notes) {
    $stmt = $pdo->prepare("
        INSERT INTO equipment 
            (name, category_id, quantity, available, status, notes) 
        VALUES 
            (?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $name,
        $categoryId,
        $quantity,
        $available,
        $status,
        $notes
    ]);
}

function updateEquipment($pdo, $id, $name, $categoryId, $quantity, $available, $status, $notes) {
    $stmt = $pdo->prepare("
        UPDATE equipment 
        SET name = ?, 
            category_id = ?, 
            quantity = ?, 
            available = ?, 
            status = ?, 
            notes = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");

    return $stmt->execute([
        $name,
        $categoryId,
        $quantity,
        $available,
        $status,
        $notes,
        $id
    ]);
}

function updateEquipmentStatus($pdo, $id, $available, $status, $notes = null) {
    $sql = "
        UPDATE equipment 
        SET available = ?, 
            status = ?, 
            updated_at = CURRENT_TIMESTAMP
    ";

    $params = [$available, $status];

    if ($notes !== null) {
        $sql .= ", notes = ?";
        $params[] = $notes;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);

    return $stmt->execute($params);
}

function deleteEquipment($pdo, $id) {
    $stmt = $pdo->prepare("
        DELETE FROM equipment 
        WHERE id = ?
    ");

    return $stmt->execute([$id]);
}

function getMemberEquipmentReservations($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT 
            er.*,
            e.name AS equipment_name
        FROM equipment_reservations er
        JOIN equipment e ON er.equipment_id = e.id
        WHERE er.member_id = ?
          AND er.status = 'active'
        ORDER BY er.reservation_date ASC, er.reservation_time ASC
    ");

    $stmt->execute([$memberId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function canMemberReserveEquipment($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT membership_plan
        FROM member_profiles
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$memberId]);
    $plan = $stmt->fetchColumn() ?: 'basic';

    return $plan === 'vip';
}

function reserveEquipment($pdo, $memberId, $equipmentId, $reservationDate, $reservationTime, $quantity = 1) {
    $equipmentId = (int)$equipmentId;
    $quantity = (int)$quantity;
    $reservationDate = trim($reservationDate);
    $reservationTime = trim($reservationTime);

    if (!canMemberReserveEquipment($pdo, $memberId)) {
        return [
            'success' => false,
            'message' => 'Only VIP members can reserve equipment.'
        ];
    }

    if ($equipmentId <= 0 || $quantity <= 0 || $reservationDate === '' || $reservationTime === '') {
        return [
            'success' => false,
            'message' => 'Please fill in all reservation fields.'
        ];
    }

    if ($reservationDate < date('Y-m-d')) {
        return [
            'success' => false,
            'message' => 'Reservation date cannot be in the past.'
        ];
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT 
                id, 
                name, 
                quantity, 
                available, 
                status
            FROM equipment
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$equipment) {
            $pdo->rollBack();

            return [
                'success' => false,
                'message' => 'Equipment not found.'
            ];
        }

        if ($equipment['status'] !== 'available') {
            $pdo->rollBack();

            return [
                'success' => false,
                'message' => 'This equipment is not available for reservation.'
            ];
        }

        if ((int)$equipment['available'] < $quantity) {
            $pdo->rollBack();

            return [
                'success' => false,
                'message' => 'Not enough available units for this equipment.'
            ];
        }

        $stmt = $pdo->prepare("
            INSERT INTO equipment_reservations
                (member_id, equipment_id, reservation_date, reservation_time, quantity, status, created_at)
            VALUES
                (?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            $memberId,
            $equipmentId,
            $reservationDate,
            $reservationTime,
            $quantity
        ]);

        $stmt = $pdo->prepare("
            UPDATE equipment
            SET available = available - ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
              AND available >= ?
        ");

        $stmt->execute([
            $quantity,
            $equipmentId,
            $quantity
        ]);

        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();

            return [
                'success' => false,
                'message' => 'Could not update equipment availability.'
            ];
        }

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Equipment reserved successfully.'
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'success' => false,
            'message' => 'Could not reserve equipment.'
        ];
    }
}

function cancelEquipmentReservation($pdo, $reservationId, $memberId) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT 
                equipment_id, 
                quantity
            FROM equipment_reservations
            WHERE id = ?
              AND member_id = ?
              AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            $reservationId,
            $memberId
        ]);

        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            $pdo->rollBack();

            return [
                'success' => false,
                'message' => 'Reservation not found.'
            ];
        }

        $stmt = $pdo->prepare("
            UPDATE equipment_reservations
            SET status = 'cancelled'
            WHERE id = ?
              AND member_id = ?
        ");

        $stmt->execute([
            $reservationId,
            $memberId
        ]);

        $stmt = $pdo->prepare("
            UPDATE equipment
            SET available = available + ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([
            (int)$reservation['quantity'],
            (int)$reservation['equipment_id']
        ]);

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Reservation cancelled successfully.'
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'success' => false,
            'message' => 'Could not cancel reservation.'
        ];
    }
}

function getEquipmentDeleteDetails($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT e.id, e.name, e.quantity, e.available, ec.label AS category_label
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.id = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
