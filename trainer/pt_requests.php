<?php
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/personal_bookings.php';
require_once '../db/notifications.php';

requireAuth('trainer');
$pdo = getDatabaseConnection();
$trainerId = $_SESSION['user_id'];

$toastMessage = '';
$toastType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'accept') {
        if (updatePTRequestStatus($pdo, $requestId, $trainerId, 'confirmed')) {
            $stmt = $pdo->prepare("SELECT member_id FROM personal_bookings WHERE id = ?");
            $stmt->execute([$requestId]);
            $memberId = $stmt->fetchColumn();
            if ($memberId) {
                $message = "Your personal training request has been confirmed by trainer " . htmlspecialchars($_SESSION['user_name']) . ".";
                createNotification($pdo, $memberId, $message, 'success');
            }
            $toastMessage = '✓ Request accepted! Member has been notified.';
            $toastType = 'success';
        } else {
            $toastMessage = '✗ Could not accept request.';
            $toastType = 'error';
        }
    } elseif ($action === 'reject') {
        if (updatePTRequestStatus($pdo, $requestId, $trainerId, 'cancelled')) {
            $stmt = $pdo->prepare("SELECT member_id FROM personal_bookings WHERE id = ?");
            $stmt->execute([$requestId]);
            $memberId = $stmt->fetchColumn();
            if ($memberId) {
                $message = "Your personal training request has been declined by trainer " . htmlspecialchars($_SESSION['user_name']) . ".";
                createNotification($pdo, $memberId, $message, 'warning');
            }
            $toastMessage = '✓ Request declined.';
            $toastType = 'warning';
        } else {
            $toastMessage = '✗ Could not decline request.';
            $toastType = 'error';
        }
    }
}

$pendingRequests = getTrainerPendingRequests($pdo, $trainerId);
$pastRequests = getTrainerPastRequests($pdo, $trainerId, 10);

output_header('request_pt.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <h1>Personal Training Requests</h1>
            <p>Manage pending PT requests from members.</p>
        </header>

        <div class="card">
            <div class="card-head">
                <h2>Pending Requests (<?= count($pendingRequests) ?>)</h2>
            </div>
            <div class="card-body">
                <?php if (empty($pendingRequests)): ?>
                    <p class="text-muted">No pending requests.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="requests-table">
                            <thead>
                                <tr><th>Member</th><th>Date</th><th>Time</th><th>Duration</th><th>Notes</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $req): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($req['member_name']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($req['booking_date'])) ?></td>
                                        <td><?= htmlspecialchars($req['start_time']) ?></td>
                                        <td><?= $req['duration_min'] ?> min</td>
                                        <td><?= htmlspecialchars($req['notes'] ?: '—') ?></td>
                                        <td>
                                            <form method="post" style="display: inline-block;">
                                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                <button type="submit" name="action" value="accept" class="main-button btn-sm">Accept</button>
                                            </form>
                                            <form method="post" style="display: inline-block;">
                                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                <button type="submit" name="action" value="reject" id="reject">Decline</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card" id="past-requests">
            <div class="card-head">
                <h2>Past Requests</h2>
            </div>
            <div class="card-body">
                <?php if (empty($pastRequests)): ?>
                    <p class="text-muted">No past requests.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="requests-table">
                            <thead><tr><th>Member</th><th>Date</th><th>Time</th><th>Duration</th><th>Status</th><th>Notes</th></tr></thead>
                            <tbody>
                                <?php foreach ($pastRequests as $req): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($req['member_name']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($req['booking_date'])) ?></td>
                                        <td><?= htmlspecialchars($req['start_time']) ?></td>
                                        <td><?= $req['duration_min'] ?> min</td>
                                        <td><?= $req['status'] === 'confirmed' ? '<span class="badge badge-success">Confirmed</span>' : '<span class="badge badge-danger">Cancelled</span>' ?></td>
                                        <td><?= htmlspecialchars($req['notes'] ?: '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<div id="toast" class="toast-notification"></div>

<script>
    const toastMessage = <?= json_encode($toastMessage) ?>;
    const toastType = <?= json_encode($toastType) ?>;

    if (toastMessage) {
        const toast = document.getElementById('toast');
        toast.textContent = toastMessage;
        toast.className = 'toast-notification toast-' + (toastType || 'info');
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 5000);
    }
</script>

<?php output_footer(); ?>