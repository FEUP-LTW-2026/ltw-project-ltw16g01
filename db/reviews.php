<?php

function memberCanReviewSession(PDO $pdo, int $memberId, int $sessionId): bool {
    // Check if the member attended a past class session before allowing a review.
    $stmt = $pdo->prepare("
        SELECT 1
        FROM enrollments e
        JOIN class_sessions sess ON e.session_id = sess.id
        WHERE e.member_id = ?
          AND sess.id = ?
          AND e.status = 'enrolled'
          AND sess.session_date < date('now')
        LIMIT 1
    ");

    $stmt->execute([$memberId, $sessionId]);

    return $stmt->fetch() !== false;
}

function saveClassReview(PDO $pdo, int $memberId, int $sessionId, int $rating, string $comment): bool {
    // Insert a new review or update the existing review for the same member and session.
    $stmt = $pdo->prepare("
        INSERT INTO reviews (member_id, session_id, rating, comment)
        VALUES (?, ?, ?, ?)
        ON CONFLICT(member_id, session_id) DO UPDATE SET
            rating = excluded.rating,
            comment = excluded.comment,
            created_at = CURRENT_TIMESTAMP
    ");

    return $stmt->execute([$memberId, $sessionId, $rating, $comment]);
}