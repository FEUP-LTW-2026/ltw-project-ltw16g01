<?php
// template/members.php

function output_members_admin_table($members) {
    if (empty($members)) {
        echo '<tr><td colspan="6" class="center-text">No members found.</td></tr>';
        return;
    }

    foreach ($members as $m) {
        $id = (int)$m['id'];
        ?>
        <tr>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= htmlspecialchars($m['username']) ?></td>
            <td><?= htmlspecialchars($m['email']) ?></td>
            <td><?= htmlspecialchars($m['membership_plan'] ?? 'basic') ?></td>
            <td><?= $m['active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
            <td>
                <a href="edit_member.php?id=<?= $id ?>" class="action-link">Edit</a>
                <a href="delete_member.php?id=<?= $id ?>" class="action-link danger delete-member-link">Delete</a>
            </td>
        </tr>
        <?php
    }
}

function output_member_form($member = null, $errors = [], $success = '', $old = []) {
    $isEdit = isset($member['id']);
    $plans = ['basic', 'premium', 'vip'];

    $name = $old['name'] ?? ($member['name'] ?? '');
    $username = $old['username'] ?? ($member['username'] ?? '');
    $email = $old['email'] ?? ($member['email'] ?? '');
    $plan = $old['plan'] ?? ($member['membership_plan'] ?? 'basic');
    $planEnd = $old['plan_end'] ?? ($member['plan_end'] ?? date('Y-m-d', strtotime('+1 year')));
    $active = $old['active'] ?? ($member['active'] ?? 1);
    ?>

    <div class="container form-container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="members.php">Members</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span><?= $isEdit ? 'Edit Member' : 'Add Member' ?></span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <h1><?= $isEdit ? 'Edit Member' : 'Add New Member' ?></h1>
                <p><?= $isEdit ? 'Update member information and membership details.' : 'Register a new gym member.' ?></p>
            </div>
        </header>

        <?php if ($errors): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form card form-card">
            <div class="form-group form-spaced">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
            </div>

            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($username) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>
            </div>

            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Membership Plan</label>
                    <select name="plan" class="form-select">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p ?>" <?= $plan === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Plan End Date</label>
                    <input type="date" name="plan_end" class="form-control" value="<?= htmlspecialchars($planEnd) ?>">
                </div>
            </div>

            <?php if (!$isEdit): ?>
                <div class="form-row form-spaced">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isEdit): ?>
                <div class="form-group form-spaced">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-select">
                        <option value="1" <?= (int)$active === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int)$active === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="main-button"><?= $isEdit ? 'Update Member' : 'Create Member' ?></button>
                <a href="members.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>

    <?php
}