<?php
require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/personal_bookings.php';
require_once '../template/admin.php';

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
$requests = getAllPTRequests($pdo, $filter === 'all' ? null : $filter);

output_header('admin-pt-requests.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Personal Training Requests</h1>
                    <p>View all PT requests from members.</p>
                </div>
            </div>
        </header>

        <div class="filter-bar">
            <div class="filter-group">
                <label class="form-label">Filter by status</label>
                <select id="status-filter" class="form-select">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="cancelled" <?= $filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>PT Requests (<span id="request-count"><?= count($requests) ?></span>)</h2>
            </div>

            <div class="card-body" id="requests-table-container">
                <?php render_requests_table($requests); ?>
            </div>
        </div>
    </div>
</main>

<script src="../js/pt-requests.js"></script>

<?php output_footer('../admin/'); ?>