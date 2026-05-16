<?php

/**
 * Renderiza a tabela de membros para o painel admin.
 */
function output_members_admin_table($members) {
    if (empty($members)) {
        echo '<tr><td colspan="6" class="center-text">No members found.</td></tr>';
        return;
    }
    foreach ($members as $m) {
        ?>
        <tr>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= htmlspecialchars($m['username']) ?></td>
            <td><?= htmlspecialchars($m['email']) ?></td>
            <td><?= htmlspecialchars($m['membership_plan'] ?? 'basic') ?></td>
            <td><?= $m['active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
            <td>
                <a href="edit_member.php?id=<?= $m['id'] ?>" class="action-link">Edit</a>
                <a href="delete_member.php?id=<?= $m['id'] ?>" class="action-link danger" onclick="return confirm('Delete this member?')">Delete</a>
            </td>
        </tr>
        <?php
    }
}

/**
 * Renderiza o formulário de criação/edição de membro.
 */
function output_member_form($member = null, $errors = [], $success = '') {
    $isEdit = !is_null($member);
    $action = $isEdit ? 'edit_member.php?id=' . $member['id'] : 'create_member.php';
    $name = $member['name'] ?? '';
    $username = $member['username'] ?? '';
    $email = $member['email'] ?? '';
    $plan = $member['membership_plan'] ?? 'basic';
    $planEnd = $member['plan_end'] ?? date('Y-m-d', strtotime('+1 year'));
    $active = $member['active'] ?? 1;
    $plans = ['basic', 'premium', 'vip'];
    ?>
    <link rel="stylesheet" href="../css/pages/form.css">
    <div class="container" style="max-width: 700px;">
        <header class="page-header">
            <h1><?= $isEdit ? 'Edit Member' : 'Add New Member' ?></h1>
        </header>

        <?php if ($errors): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form card" style="padding: var(--sp-lg);">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($username) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>
            </div>

            <?php if (!$isEdit): ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                    <small>Leave blank to use default (changeme).</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
            </div>
            <?php endif; ?>

            <div class="form-row">
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

            <?php if ($isEdit): ?>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="active" class="form-select">
                    <option value="1" <?= $active == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $active == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <?php endif; ?>

            <div style="display: flex; gap: var(--sp-md); margin-top: var(--sp-md);">
                <button type="submit" class="main-button"><?= $isEdit ? 'Update Member' : 'Create Member' ?></button>
                <a href="members.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}