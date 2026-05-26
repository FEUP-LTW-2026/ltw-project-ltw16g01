<?php
// db/profile.php

function getUserProfileById($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT id, name, username, email, role, photo, active, created_at FROM users WHERE id = ? AND active = 1");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function usernameOrEmailExistsForAnotherUser($pdo, $userId, $username, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1");
    $stmt->execute([$username, $email, $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function updateBasicProfile($pdo, $userId, $name, $username, $email, $photo = null) {
    $name = trim($name);
    $username = trim($username);
    $email = trim($email);
    $photo = trim($photo ?? '');

    if ($name === '' || $username === '' || $email === '') return 'Please fill in name, username and email.';
    if (strlen($username) < 3) return 'Username must have at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Please enter a valid email address.';
    if (usernameOrEmailExistsForAnotherUser($pdo, $userId, $username, $email)) return 'That email or username is already being used by another account.';

    $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, photo = ? WHERE id = ?");
    $stmt->execute([$name, $username, $email, $photo !== '' ? $photo : null, $userId]);

    return true;
}

function updateUserPassword($pdo, $userId, $currentPassword, $newPassword, $confirmPassword) {
    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') return 'Please fill in all password fields.';
    if ($newPassword !== $confirmPassword) return 'Passwords do not match.';
    if (strlen($newPassword) < 8) return 'Password must be at least 8 characters.';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $storedHash = $stmt->fetchColumn();

    if (!$storedHash) return 'User not found.';

    $validCurrentPassword = password_verify($currentPassword, $storedHash) || $currentPassword === $storedHash;

    if (!$validCurrentPassword) return 'Current password is incorrect.';

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$newHash, $userId]);

    return true;
}

function getMemberProfile($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT membership_plan, plan_start, plan_end FROM member_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getMemberProfileStats($pdo, $userId) {
    $stats = [];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e JOIN class_sessions s ON e.session_id = s.id WHERE e.member_id = ? AND e.status = 'enrolled' AND s.session_date >= date('now')");
    $stmt->execute([$userId]);
    $stats['upcoming_classes'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e JOIN class_sessions s ON e.session_id = s.id WHERE e.member_id = ? AND e.status = 'enrolled' AND s.session_date < date('now')");
    $stmt->execute([$userId]);
    $stats['past_classes'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE member_id = ?");
    $stmt->execute([$userId]);
    $stats['reviews'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM personal_bookings WHERE member_id = ? AND status IN ('pending','confirmed') AND booking_date >= date('now')");
    $stmt->execute([$userId]);
    $stats['personal_bookings'] = (int)$stmt->fetchColumn();

    return $stats;
}

function getMemberUpcomingClasses($pdo, $userId, $limit = 5) {
    $sql = "SELECT e.id AS enrollment_id, s.session_date, c.name AS class_name, ct.label AS class_type, u.name AS trainer_name, cs.start_time, cs.location, e.status FROM enrollments e JOIN class_sessions s ON e.session_id = s.id JOIN class_schedules cs ON s.schedule_id = cs.id JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE e.member_id = ? AND e.status = 'enrolled' AND s.session_date >= date('now') ORDER BY s.session_date ASC, cs.start_time ASC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberPersonalBookings($pdo, $userId, $limit = 5) {
    $sql = "SELECT pb.booking_date, pb.start_time, pb.duration_min, pb.status, pb.notes, u.name AS trainer_name FROM personal_bookings pb JOIN users u ON pb.trainer_id = u.id WHERE pb.member_id = ? AND pb.booking_date >= date('now') ORDER BY pb.booking_date ASC, pb.start_time ASC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainerPublicProfile($pdo, $trainerId) {
    $stmt = $pdo->prepare("SELECT bio, specializations, certifications FROM trainer_profiles WHERE user_id = ?");
    $stmt->execute([$trainerId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function ensureTrainerProfileExists($pdo, $trainerId) {
    $stmt = $pdo->prepare("SELECT id FROM trainer_profiles WHERE user_id = ?");
    $stmt->execute([$trainerId]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        $insert = $pdo->prepare("INSERT INTO trainer_profiles (user_id, bio, specializations, certifications) VALUES (?, '', '', '')");
        $insert->execute([$trainerId]);
    }
}

function updateTrainerPublicProfile($pdo, $trainerId, $bio, $specializations, $certifications) {
    ensureTrainerProfileExists($pdo, $trainerId);

    $stmt = $pdo->prepare("UPDATE trainer_profiles SET bio = ?, specializations = ?, certifications = ? WHERE user_id = ?");
    $stmt->execute([trim($bio), trim($specializations), trim($certifications), $trainerId]);

    return true;
}

function getTrainerStats($pdo, $trainerId) {
    $stats = [];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE cs.trainer_id = ? AND c.active = 1");
    $stmt->execute([$trainerId]);
    $stats['assigned_schedules'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT e.member_id) FROM enrollments e JOIN class_sessions s ON e.session_id = s.id JOIN class_schedules cs ON s.schedule_id = cs.id WHERE cs.trainer_id = ? AND e.status = 'enrolled'");
    $stmt->execute([$trainerId]);
    $stats['members_enrolled'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT AVG(r.rating) AS avg_rating, COUNT(r.id) AS review_count FROM reviews r JOIN class_sessions s ON r.session_id = s.id JOIN class_schedules cs ON s.schedule_id = cs.id WHERE cs.trainer_id = ?");
    $stmt->execute([$trainerId]);
    $rating = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['avg_rating'] = round($rating['avg_rating'] ?? 0, 1);
    $stats['review_count'] = (int)($rating['review_count'] ?? 0);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM personal_bookings WHERE trainer_id = ? AND status IN ('pending','confirmed') AND booking_date >= date('now')");
    $stmt->execute([$trainerId]);
    $stats['personal_bookings'] = (int)$stmt->fetchColumn();

    return $stats;
}

function getTrainerSchedulesForProfile($pdo, $trainerId, $limit = 8) {
    $sql = "SELECT cs.id, c.name AS class_name, ct.label AS class_type, cs.day_of_week, cs.start_time, cs.location, cs.capacity, cs.duration_min, (SELECT COUNT(*) FROM enrollments e JOIN class_sessions s ON e.session_id = s.id WHERE s.schedule_id = cs.id AND e.status = 'enrolled') AS enrolled_count FROM class_schedules cs JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id WHERE cs.trainer_id = ? AND c.active = 1 ORDER BY CASE cs.day_of_week WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 WHEN 'Sunday' THEN 7 ELSE 8 END, cs.start_time LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAdminProfileStats($pdo) {
    $stats = [];

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND active = 1");
    $stats['members'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND active = 1");
    $stats['trainers'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM classes WHERE active = 1");
    $stats['classes'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status IN ('maintenance', 'unavailable') OR available = 0");
    $stats['equipment_issues'] = (int)$stmt->fetchColumn();

    return $stats;
}

function uploadProfilePhoto($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return ['success' => true, 'path' => null];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'There was an error uploading the image.'];

    $maxSize = 2 * 1024 * 1024;

    if ($file['size'] > $maxSize) return ['success' => false, 'error' => 'The image must be smaller than 2MB.'];

    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mimeType = mime_content_type($file['tmp_name']);

    if (!array_key_exists($mimeType, $allowedTypes)) return ['success' => false, 'error' => 'Invalid image format. Please use JPG, PNG or WEBP.'];

    $uploadDir = __DIR__ . '/../uploads/profiles/';

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $extension = $allowedTypes[$mimeType];
    $fileName = 'profile_' . bin2hex(random_bytes(12)) . '.' . $extension;
    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) return ['success' => false, 'error' => 'Could not save the uploaded image.'];

    return ['success' => true, 'path' => '../uploads/profiles/' . $fileName];
}

function deleteOldProfilePhoto($photoPath) {
    if (empty($photoPath)) return;

    $fileName = basename($photoPath);
    $filePath = __DIR__ . '/../uploads/profiles/' . $fileName;

    if (file_exists($filePath)) unlink($filePath);
}