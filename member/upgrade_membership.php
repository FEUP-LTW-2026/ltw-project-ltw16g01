<?php
/**
 * upgrade_membership.php – Member page to change membership plan.
 * Shows available plans (Basic, Premium, VIP) and allows switching.
 */

require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';

// Only logged‑in members
requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = (int)$_SESSION['user_id'];

$currentPlan = getMemberPlan($pdo, $memberId);

$error = '';
$success = '';

// Plan definitions (name, price, features, etc.)
$plans = [
    'basic' => [
        'label' => 'Basic',
        'price' => '€19',
        'tag' => 'Starter',
        'description' => 'Essential access for casual training.',
        'features' => [
            ['text' => 'Gym access 9am-5pm', 'included' => true],
            ['text' => '2 classes per week', 'included' => true],
            ['text' => 'Personal training sessions', 'included' => false],
            ['text' => 'Equipment reservation', 'included' => false],
        ]
    ],
    'premium' => [
        'label' => 'Premium',
        'price' => '€39',
        'tag' => 'Popular',
        'description' => 'More freedom for consistent training.',
        'features' => [
            ['text' => 'Unlimited classes', 'included' => true],
            ['text' => 'Full-day gym access', 'included' => true],
            ['text' => '2 PT sessions per month', 'included' => true],
            ['text' => 'Equipment reservation', 'included' => false],
        ]
    ],
    'vip' => [
        'label' => 'VIP',
        'price' => '€69',
        'tag' => 'Best Value',
        'description' => 'Full access with premium benefits.',
        'features' => [
            ['text' => 'Unlimited classes', 'included' => true],
            ['text' => 'Full-day gym access', 'included' => true],
            ['text' => '1 PT session per day maximum', 'included' => true],
            ['text' => 'Equipment reservation included', 'included' => true],
        ]
    ]
];

// Process upgrade request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $newPlan = $_POST['plan'] ?? '';

    if (!isset($plans[$newPlan])) {
        $error = 'Please select a valid membership plan.';
    } elseif ($newPlan === $currentPlan) {
        $error = 'You already have this membership plan.';
    } else {
        if (upgradeMemberPlan($pdo, $memberId, $newPlan)) {
            $currentPlan = $newPlan;
            $success = 'Your membership plan has been updated successfully.';
        } else {
            $error = 'Could not update your membership plan. Please try again.';
        }
    }
}

output_header('upgrade_membership.css');
?>

<main class="main upgrade-page">
    <div class="container upgrade-container">

        <header class="upgrade-hero">
            <p class="upgrade-eyebrow">Membership Plans</p>
            <h1>Change Membership</h1>
            <p>Choose the plan that best fits your training goals.</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger upgrade-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success upgrade-alert">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Current plan panel -->
        <section class="current-plan-panel">
            <div>
                <p class="panel-label">Current Plan</p>
                <h2><?= htmlspecialchars($plans[$currentPlan]['label']) ?></h2>
                <p>
                    Your current membership is 
                    <strong><?= htmlspecialchars($plans[$currentPlan]['label']) ?></strong>.
                </p>
            </div>

            <span class="badge badge-admin">
                <?= htmlspecialchars($plans[$currentPlan]['label']) ?>
            </span>
        </section>

        <!-- Plan selection form -->
        <form method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <section class="plans-upgrade-grid">
                <?php foreach ($plans as $planKey => $plan): ?>
                    <?php
                        $isCurrent = $planKey === $currentPlan;
                        $isFeatured = $planKey === 'vip';
                    ?>

                    <article class="upgrade-plan-card <?= $isFeatured ? 'featured' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                        <div class="plan-top">
                            <div>
                                <p class="plan-tag"><?= htmlspecialchars($plan['tag']) ?></p>
                                <h2><?= htmlspecialchars($plan['label']) ?></h2>
                            </div>

                            <?php if ($isCurrent): ?>
                                <span class="badge badge-success">Current</span>
                            <?php elseif ($planKey === 'vip'): ?>
                                <span class="badge badge-admin">Best</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Change</span>
                            <?php endif; ?>
                        </div>

                        <p class="plan-description">
                            <?= htmlspecialchars($plan['description']) ?>
                        </p>

                        <div class="plan-price">
                            <?= htmlspecialchars($plan['price']) ?><span>/month</span>
                        </div>

                        <ul class="plan-features">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li class="<?= $feature['included'] ? 'included' : 'excluded' ?>">
                                    <span class="feature-icon">
                                        <?= $feature['included'] ? '✓' : '×' ?>
                                    </span>
                                    <span><?= htmlspecialchars($feature['text']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="plan-action">
                            <?php if ($isCurrent): ?>
                                <button type="button" class="secondary-button full-button current-plan-button" disabled>
                                    Current Plan
                                </button>
                            <?php else: ?>
                                <button
                                    type="submit"
                                    name="plan"
                                    value="<?= htmlspecialchars($planKey) ?>"
                                    class="main-button full-button"
                                >
                                    Choose <?= htmlspecialchars($plan['label']) ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </form>

        <div class="upgrade-back-row">
            <a href="dashboard.php" class="secondary-button">
                Back to Dashboard
            </a>
        </div>

    </div>
</main>

<?php output_footer('../member/'); ?>