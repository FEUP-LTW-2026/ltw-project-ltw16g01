<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/personal_bookings.php';

requireAuth('admin');

function render_requests_table($requests) {
    if (empty($requests)) {
        echo '<p class="text-muted">No requests found.</p>';
        return;
    }
    ?>
    <div class="table-wrap">
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Trainer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= htmlspecialchars($req['member_name']) ?></td>
                        <td><?= htmlspecialchars($req['trainer_name']) ?></td>
                        <td><?= date('d/m/Y', strtotime($req['booking_date'])) ?></td>
                        <td><?= htmlspecialchars($req['start_time']) ?></td>
                        <td><?= (int)$req['duration_min'] ?> min</td>
                        <td><?= htmlspecialchars($req['notes'] ?: '—') ?></td>
                        <td>
                            <?php if ($req['status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php elseif ($req['status'] === 'confirmed'): ?>
                                <span class="badge badge-success">Confirmed</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

$pdo = getDatabaseConnection();

$filter = $_GET['filter'] ?? 'all';
$allowedFilters = ['all', 'pending', 'confirmed', 'cancelled'];

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$requests = getAllPTRequests($pdo, $filter === 'all' ? null : $filter);

ob_start();
render_requests_table($requests);
$html = ob_get_clean();

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'html' => $html,
    'count' => count($requests)
]);

exit;