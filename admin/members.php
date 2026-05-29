<?php
// File: admin/members.php
// Description: Listing page for members with filtering and management options.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../template/members.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Read and sanitize filter values from URL query string
$filters = [
    'name' => $_GET['name'] ?? '',
    'plan' => $_GET['plan'] ?? ''
];

// Fetch members using filters (no inline SQL)
$members = getAllMembersAdmin($pdo, $filters);

// Output page header with member-specific CSS
output_header('admin_members.css');
?>

<!-- Additional CSS for form elements (filter bar) -->
<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <!-- Page header with title and "Add Member" button -->
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>Manage Members</h1>
                    <p>View, filter, add, edit or remove members.</p>
                </div>

                <a href="create_member.php" class="main-button">+ Add Member</a>
            </div>
        </header>

        <!-- Filter form (GET method, preserves filters in URL) -->
        <form id="filter-form" method="get" class="filter-bar members-filter">
            <div class="filter-group">
                <label class="form-label" for="f-name">Name</label>
                <input 
                    type="text" 
                    id="f-name"
                    name="name" 
                    class="form-control" 
                    value="<?= htmlspecialchars($filters['name']) ?>"
                    placeholder="Search member name..."
                >
            </div>

            <div class="filter-group">
                <label class="form-label" for="f-plan">Plan</label>
                <select id="f-plan" name="plan" class="form-select">
                    <option value="">All plans</option>
                    <option value="basic" <?= $filters['plan'] === 'basic' ? 'selected' : '' ?>>Basic</option>
                    <option value="premium" <?= $filters['plan'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                    <option value="vip" <?= $filters['plan'] === 'vip' ? 'selected' : '' ?>>VIP</option>
                </select>
            </div>
        </form>

        <!-- Results count bar (updated by JavaScript when filters change) -->
        <div class="results-bar">
            <p class="results-count">
                Showing <strong id="member-count"><?= count($members) ?></strong> members
            </p>
        </div>

        <!-- Members table -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="member-table-body">
                    <?php output_members_admin_table($members); ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- External JavaScript for filter interactions and AJAX reloading -->
<script src="../js/members_admin.js"></script>

<?php output_footer('../admin/'); ?>