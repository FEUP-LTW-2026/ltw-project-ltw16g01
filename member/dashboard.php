<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../db/personal_bookings.php';

requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];

$stats = getMemberStats($pdo, $memberId);
$upcoming = getMemberUpcomingEnrollments($pdo, $memberId);
$pastClasses = getMemberPastClasses($pdo, $memberId);
$ptRequests = getMemberPTRequests($pdo, $memberId);
$confirmedPT = getMemberConfirmedPT($pdo, $memberId);
$memberPlan = getMemberPlan($pdo, $memberId);
$planFeatures = getMemberPlanFeatures($memberPlan);

output_header('member-dashboard.css');
?>

<main class="main">
    <header class="dash-header">
        <div class="dashboard-header">
            <div class="dash-top">
                <div>
                    <p class="dash-date"><?= date('l, d F Y') ?></p>
                    <h1 class="dash-h1">Member <span>Dashboard</span></h1>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-content">
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-num"><?= (int)$stats['upcoming'] ?></p>
                <p class="kpi-lbl">📅 Upcoming Classes</p>
            </div>

            <div class="kpi-card">
                <p class="kpi-num"><?= (int)$stats['past'] ?></p>
                <p class="kpi-lbl">🏁 Classes Completed</p>
            </div>

            <div class="kpi-card">
                <p class="kpi-num"><?= htmlspecialchars($stats['avg_rating']) ?></p>
                <p class="kpi-lbl">⭐ Average Rating</p>
            </div>
        </div>

        <div class="card full-width-card membership-card">
            <div class="card-head">
                <h2 class="card-title">Your Membership Plan</h2>
                <span class="badge badge-admin"><?= htmlspecialchars($planFeatures['label']) ?></span>
            </div>

            <div class="membership-body">
                <ul class="membership-features">
                    <li>
                        <span class="membership-icon">🏋️</span>
                        <span><strong>Classes:</strong> <?= htmlspecialchars($planFeatures['classes']) ?></span>
                    </li>

                    <li>
                        <span class="membership-icon">👤</span>
                        <span><strong>Personal Trainer:</strong> <?= htmlspecialchars($planFeatures['pt']) ?></span>
                    </li>

                    <li>
                        <span class="membership-icon">🛠️</span>
                        <span><strong>Equipment:</strong> <?= htmlspecialchars($planFeatures['equipment']) ?></span>
                    </li>
                </ul>

                <?php if ($memberPlan === 'basic'): ?>
                    <p class="membership-note">
                        Upgrade to Premium or VIP to unlock unlimited classes and personal training options.
                    </p>
                <?php elseif ($memberPlan === 'premium'): ?>
                    <p class="membership-note">
                        Upgrade to VIP to unlock equipment reservation and daily PT sessions.
                    </p>
                <?php else: ?>
                    <p class="membership-note">
                        You have full access to all FitPeak member features.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de próximas aulas -->
        <div class="card full-width-card">
            <div class="card-head">
                <h2 class="card-title">Your Upcoming Classes</h2>
                <a href="classes.php" class="secondary-button btn-sm">Browse All Classes</a>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($upcoming)): ?>
                            <tr>
                                <td colspan="6" class="center-text">
                                    You have no upcoming classes. <a href="classes.php">Book one now!</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($upcoming as $class): ?>
                                <tr>
                                    <td><?= htmlspecialchars($class['class_name']) ?></td>
                                    <td><?= htmlspecialchars($class['trainer_name'] ?? 'TBD') ?></td>
                                    <td><?= htmlspecialchars($class['session_date']) ?></td>
                                    <td><?= htmlspecialchars($class['start_time']) ?></td>
                                    <td><?= htmlspecialchars($class['location']) ?></td>
                                    <td>
                                        <button 
                                            class="btn-cancel-enroll" 
                                            data-session-id="<?= (int)$class['session_id'] ?>"
                                        >
                                            Cancel
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pedidos de PT -->
        <div class="card full-width-card">
            <div class="card-head">
                <h2 class="card-title">Your Personal Training Requests</h2>

                <?php if ($memberPlan !== 'basic'): ?>
                    <a href="request_pt.php" class="secondary-button btn-sm">Request New PT</a>
                <?php else: ?>
                    <span class="badge badge-warning">Upgrade required</span>
                <?php endif; ?>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($ptRequests)): ?>
                            <?php if ($memberPlan === 'basic'): ?>
                                <tr>
                                    <td colspan="6" class="center-text">
                                        Personal Training is not available on the Basic plan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="center-text">
                                        You have no PT requests. <a href="request_pt.php">Request a personal trainer</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php foreach ($ptRequests as $req): ?>
                                <tr>
                                    <td><?= htmlspecialchars($req['trainer_name']) ?></td>
                                    <td><?= htmlspecialchars($req['booking_date']) ?></td>
                                    <td><?= htmlspecialchars($req['start_time']) ?></td>
                                    <td><?= (int)$req['duration_min'] ?> min</td>
                                    <td>
                                        <?php if ($req['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php elseif ($req['status'] === 'confirmed'): ?>
                                            <span class="badge badge-success">Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($req['notes'] ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sessões PT confirmadas -->
        <div class="card full-width-card">
            <div class="card-head">
                <h2 class="card-title">Confirmed Personal Training Sessions</h2>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Notes</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($confirmedPT)): ?>
                            <?php if ($memberPlan === 'basic'): ?>
                                <tr>
                                    <td colspan="5" class="center-text">
                                        Personal Training is not available on the Basic plan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="center-text">
                                        No confirmed PT sessions yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php foreach ($confirmedPT as $pt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pt['trainer_name']) ?></td>
                                    <td><?= htmlspecialchars($pt['booking_date']) ?></td>
                                    <td><?= htmlspecialchars($pt['start_time']) ?></td>
                                    <td><?= (int)$pt['duration_min'] ?> min</td>
                                    <td><?= htmlspecialchars($pt['notes'] ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Aulas recentes -->
        <div class="card full-width-card">
            <div class="card-head">
                <h2 class="card-title">Recent Classes</h2>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Your Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($pastClasses)): ?>
                            <tr>
                                <td colspan="4" class="center-text">
                                    You have no completed classes yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pastClasses as $class): ?>
                                <tr>
                                    <td><?= htmlspecialchars($class['class_name']) ?></td>
                                    <td><?= htmlspecialchars($class['session_date']) ?></td>
                                    <td class="rating-cell">
                                        <?php if (!empty($class['rating'])): ?>
                                            <span class="stars-display">
                                                <?= str_repeat('★', (int)$class['rating']) . str_repeat('☆', 5 - (int)$class['rating']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-rating">No rating yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($class['rating'])): ?>
                                            <button 
                                                class="rate-link" 
                                                data-session-id="<?= (int)$class['session_id'] ?>" 
                                                data-class-name="<?= htmlspecialchars($class['class_name']) ?>"
                                            >
                                                ⭐ Rate this class
                                            </button>
                                        <?php else: ?>
                                            <button 
                                                class="rate-link rate-edit" 
                                                data-session-id="<?= (int)$class['session_id'] ?>" 
                                                data-class-name="<?= htmlspecialchars($class['class_name']) ?>"
                                            >
                                                ✏️ Edit review
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="reviewModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Review: <span id="reviewClassName"></span></h3>
                <button class="modal-close">✕</button>
            </div>

            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" id="reviewSessionId" name="session_id">

                    <div class="form-group">
                        <label class="form-label">Rating</label>

                        <div class="star-input-group" style="display:flex;gap:0.3rem;">
                            <input type="hidden" name="rating" id="ratingInput" value="0">

                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" style="font-size:2rem;cursor:pointer;">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Comment (optional)</label>

                        <textarea 
                            name="comment" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Share your experience…" 
                            data-maxlength="300"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn btn-accent w-full">Save Review</button>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="../js/member-dashboard.js"></script>

<?php output_footer('../member/'); ?>