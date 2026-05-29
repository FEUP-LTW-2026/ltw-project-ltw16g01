<?php
// File: admin/personal_training_requests.php
// Description: Displays personal training requests from members with filtering by status.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/personal_bookings.php';
require_once '../template/admin.php';

// Restrict access to admin users only
requireAuth('admin');

/**
 * Render a table of PT requests.
 *
 * @param array $requests List of request records from database
 */
function render_requests_table($requests) {
    // Show message if no requests found
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

// Get database connection
$pdo = getDatabaseConnection();

// Read filter parameter from URL (all, pending, confirmed, cancelled)
$filter = $_GET['filter'] ?? 'all';

// Fetch PT requests based on filter (null = all statuses)
$requests = getAllPTRequests($pdo, $filter === 'all' ? null : $filter);

// Output page header with PT requests specific CSS
output_header('admin_pt_requests.css');
?>

<!-- Additional CSS for form elements (filter bar) -->
<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <!-- Page header with title -->
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Personal Training Requests</h1>
                    <p>View all PT requests from members.</p>
                </div>
            </div>
        </header>

        <!-- Filter bar for request status -->
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

        <!-- Card containing the requests table -->
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

<!-- External JavaScript for filter interactions and AJAX reloading -->
<script src="../js/pt_requests.js"></script>

<?php output_footer('../admin/'); ?>