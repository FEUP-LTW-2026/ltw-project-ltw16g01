<?php
/**
 * member/my_classes.php
 * Lista todas as aulas futuras em que o membro está inscrito.
 */

require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/enrollments.php';
require_once '../db/classes.php';
require_once '../db/notifications.php';

// Apenas membros autenticados
requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];

// Processar cancelamento de inscrição
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $sessionId = (int)($_POST['session_id'] ?? 0);
    if ($sessionId <= 0) {
        $error = 'Invalid session.';
    } else {
        $result = cancelMemberEnrollment($pdo, $memberId, $sessionId);
        if ($result['success']) {
            $message = $result['message'];
            // Opcional: notificar o trainer? Podes chamar notifyTrainerClassEnrollment se quiseres.
            // Para já, apenas mostra mensagem.
        } else {
            $error = $result['message'];
        }
    }
}

$sessions = getUpcomingEnrolledSessions($pdo, $memberId);

output_header('my-classes.css'); // Podes criar um CSS específico ou usar o geral
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <h1>My Upcoming Classes</h1>
            <p>Classes you have enrolled in that are scheduled for the future.</p>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($sessions)): ?>
            <div class="card">
                <div class="card-body center-text">
                    <p>You are not enrolled in any upcoming classes.</p>
                    <a href="classes.php" class="main-button">Browse Classes</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Trainer</th>
                            <th>Location</th>
                            <th>Duration</th>
                            <th>Spots Left</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <?php
                                $spotsLeft = (int)$session['capacity'] - (int)$session['enrolled_count'];
                                $sessionDateObj = new DateTime($session['session_date']);
                                $formattedDate = $sessionDateObj->format('l, F j, Y');
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($session['class_name']) ?></td>
                                <td><?= htmlspecialchars($formattedDate) ?></td>
                                <td><?= htmlspecialchars($session['start_time']) ?></td>
                                <td><?= htmlspecialchars($session['trainer_name'] ?? 'TBD') ?></td>
                                <td><?= htmlspecialchars($session['location']) ?></td>
                                <td><?= (int)$session['duration_min'] ?> min</td>
                                <td>
                                    <?php if ($spotsLeft <= 0): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php elseif ($spotsLeft <= 5): ?>
                                        <span class="badge badge-warning"><?= $spotsLeft ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?= $spotsLeft ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to cancel your enrollment?');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="session_id" value="<?= (int)$session['session_id'] ?>">
                                        <button type="submit" class="secondary-button btn-sm danger-button">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="dashboard.php" class="secondary-button">Back to Dashboard</a>
        </div>
    </div>
</main>

<?php output_footer('../member/'); ?>