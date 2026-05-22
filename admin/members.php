<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../template/members.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$filters = ['name' => $_GET['name'] ?? '', 'plan' => $_GET['plan'] ?? ''];
$members = getAllMembersAdmin($pdo, $filters);

output_header('admin-members.css');
?>

<link rel="stylesheet" href= "../css/pages/form.css">

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>Manage Members</h1>
                    <p>View, filter, add, edit or remove members.</p>
                </div>

                <a href="create_member.php" class="main-button">+ Add Member</a>
            </div>
        </header>

        <form method="get" class="filter-bar members-filter">
            <div class="filter-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($filters['name']) ?>">
            </div>

            <div class="filter-group">
                <label class="form-label">Plan</label>
                <select name="plan" class="form-select">
                    <option value="">All plans</option>
                    <option value="basic" <?= $filters['plan'] === 'basic' ? 'selected' : '' ?>>Basic</option>
                    <option value="premium" <?= $filters['plan'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                    <option value="vip" <?= $filters['plan'] === 'vip' ? 'selected' : '' ?>>VIP</option>
                </select>
            </div>

            <div class="filter-group filter-group-end">
                <button type="submit" class="main-button full-width">Filter</button>
            </div>
        </form>

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

                <tbody>
                    <?php output_members_admin_table($members); ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="../js/members-admin.js"></script>

<?php output_footer('../admin/'); ?>