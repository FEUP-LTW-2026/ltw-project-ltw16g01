<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/footer.php';
require_once 'db/connection.php';

$pdo = getDatabaseConnection();
$errors = [];
$success = '';

// ─────────── Handle CRUD operations ───────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        $classId   = (int)($_POST['class_id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $type      = trim($_POST['type'] ?? '');
        $trainerId = (int)($_POST['trainer_id'] ?? 0) ?: null;
        $capacity  = max(1, (int)($_POST['capacity'] ?? 20));
        $duration  = max(15, (int)($_POST['duration_min'] ?? 60));
        $day       = trim($_POST['day_of_week'] ?? '');
        $time      = trim($_POST['start_time'] ?? '');
        $location  = trim($_POST['location'] ?? 'Studio A');

        if (strlen($name) < 2) $errors[] = 'Name is too short.';
        if (empty($type))      $errors[] = 'Type is required.';
        if (empty($day))       $errors[] = 'Day of week is required.';
        if (empty($time))      $errors[] = 'Start time is required.';

        if (empty($errors)) {
            if ($action === 'create') {
                $pdo->prepare("INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
                    VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$name, $desc, $type, $trainerId, $capacity, $duration, $day, $time, $location]);
                $newClassId = $pdo->lastInsertId();

                // Generate sessions for the next 8 weeks
                $dayMap = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>0];
                $targetDay = $dayMap[$day];
                $today = new DateTime();
                $todayDay = (int)$today->format('N') % 7;
                $insertSess = $pdo->prepare("INSERT INTO class_sessions (class_id, session_date) VALUES (?,?)");
                for ($w = 0; $w < 8; $w++) {
                    $diff = ($targetDay - $todayDay + 7) % 7 + ($w * 7);
                    if ($diff === 0) $diff = 7;
                    $sessDate = (clone $today)->modify("+{$diff} days")->format('Y-m-d');
                    $insertSess->execute([$newClassId, $sessDate]);
                }

                $success = 'Class created successfully!';
            } else {
                $pdo->prepare("UPDATE classes SET name=?, description=?, type=?, trainer_id=?, capacity=?, duration_min=?, day_of_week=?, start_time=?, location=? WHERE id=?")
                    ->execute([$name, $desc, $type, $trainerId, $capacity, $duration, $day, $time, $location, $classId]);
                $success = 'Class updated!';
            }
        }
    } elseif ($action === 'delete') {
        $classId = (int)($_POST['class_id'] ?? 0);
        $pdo->prepare("UPDATE classes SET active=0 WHERE id=?")->execute([$classId]);
        $success = 'Class removed.';
    } elseif ($action === 'restore') {
        $classId = (int)($_POST['class_id'] ?? 0);
        $pdo->prepare("UPDATE classes SET active=1 WHERE id=?")->execute([$classId]);
        $success = 'Class restored.';
    }
}

// ─────────── Fetch data ───────────
$classes = $pdo->query("
    SELECT c.*, u.name AS trainer_name,
           COALESCE((SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.class_id = c.id), 0) AS avg_rating,
           COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.class_id = c.id), 0) AS review_count,
           COALESCE((SELECT COUNT(*)
                     FROM enrollments en
                     JOIN class_sessions cs ON en.session_id = cs.id
                     WHERE cs.class_id = c.id
                       AND cs.session_date >= DATE('now')
                       AND en.status = 'enrolled'), 0) AS enrolled_count
    FROM classes c
    LEFT JOIN users u ON u.id = c.trainer_id
    GROUP BY c.id
    ORDER BY c.active DESC, c.day_of_week, c.start_time
")->fetchAll();

$trainers = $pdo->query("SELECT id, name FROM users WHERE role='trainer' AND active=1 ORDER BY name")->fetchAll();
$days     = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$types    = ['yoga','pilates','hiit','spinning','crossfit','strength','cardio','stretching','boxing','other'];

// Badge colour map
$typeBadgeMap = [
    'yoga'      => 'badge-info',
    'pilates'   => 'badge-info',
    'hiit'      => 'badge-danger',
    'spinning'  => 'badge-warning',
    'crossfit'  => 'badge-danger',
    'strength'  => 'badge-accent',
    'cardio'    => 'badge-warning',
    'stretching'=> 'badge-info',
    'boxing'    => 'badge-warning',
    'other'     => 'badge-muted',
];

function capClass($enrolled, $capacity) {
    if ($capacity == 0) return '';
    $pct = $enrolled / $capacity;
    if ($pct >= 0.9) return 'mid';
    if ($pct >= 0.75) return 'medium';
    return '';
}

output_header('classes.css');
?>

<main class="main">
    <div class="container">

        <!-- Page Header -->
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><a href="admin-dashboard.php">Admin</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Manage Classes</span></li>
                </ol>
            </nav>
            <div class="page-header-row flex-between" style="flex-wrap:wrap; gap:1rem;">
                <div>
                    <h1>Manage Classes</h1>
                    <p>Create, edit and manage your class catalogue</p>
                </div>
                <button class="main-button" id="openCreateModal">+ New Class</button>
            </div>
        </header>

        <!-- Alerts -->
        <?php foreach ($errors as $e): ?>
            <div class="auth-alert alert-error" role="alert">
                <span class="auth-alert-icon">✕</span>
                <span><?= htmlspecialchars($e) ?></span>
            </div>
        <?php endforeach; ?>
        <?php if ($success): ?>
            <div class="auth-alert alert-success" role="alert">
                <span class="auth-alert-icon">✓</span>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <!-- Filters + Search -->
        <form class="filter-bar" method="get" action="">
            <div class="filter-group">
                <label class="form-label" for="f-type">Type</label>
                <select id="f-type" name="type" class="form-select">
                    <option value="">All types</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t ?>" <?= ($_GET['type'] ?? '') === $t ? 'selected' : '' ?>>
                            <?= ucfirst($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label" for="f-day">Day</label>
                <select id="f-day" name="day" class="form-select">
                    <option value="">Any Day</option>
                    <?php foreach ($days as $d): ?>
                        <option value="<?= $d ?>" <?= ($_GET['day'] ?? '') === $d ? 'selected' : '' ?>>
                            <?= $d ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label" for="f-status">Status</label>
                <select id="f-status" name="status" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="filter-group filter-group-end">
                <button type="submit" class="main-button full-width">Filter</button>
            </div>
        </form>

        <!-- Results count -->
        <div class="results-bar">
            <p class="results-count">
                Showing <strong><?= count($classes) ?></strong> class<?= count($classes) !== 1 ? 'es' : '' ?>
            </p>
        </div>

        <!-- Card Grid -->
        <section>
            <div class="class-grid">
                <?php if (empty($classes)): ?>
                    <p class="text-muted">No classes found.</p>
                <?php else: ?>
                    <?php foreach ($classes as $c):
                        $enrolled     = (int)$c['enrolled_count'];
                        $capacity     = (int)$c['capacity'];
                        $pctFull      = ($capacity > 0) ? round(($enrolled / $capacity) * 100) : 0;
                        $capClass     = capClass($enrolled, $capacity);
                        $rating       = $c['avg_rating'] ? number_format($c['avg_rating'], 1) : '—';
                        $reviewCount  = $c['review_count'];
                        $badgeClass   = $typeBadgeMap[$c['type']] ?? 'badge-muted';
                        $time         = date('H:i', strtotime($c['start_time']));
                        $activeClass  = $c['active'] ? '' : 'inactive-card';
                    ?>
                        <article class="class-card <?= $activeClass ?>" style="<?= !$c['active'] ? 'opacity:0.55;' : '' ?>">
                            <div class="class-head">
                                <span class="class-time-big"><?= $time ?></span>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($c['type'])) ?></span>
                            </div>
                            <div class="class-body">
                                <div>
                                    <h2 class="class-name"><?= htmlspecialchars($c['name']) ?></h2>
                                    <p><?= htmlspecialchars($c['description'] ?: 'No description.') ?></p>
                                </div>
                                <ul class="class-meta">
                                    <li>📅 <?= htmlspecialchars($c['day_of_week']) ?>s</li>
                                    <li>⏱ <?= $c['duration_min'] ?> min</li>
                                    <li>📍 <?= htmlspecialchars($c['location']) ?></li>
                                    <li>👤 <?= htmlspecialchars($c['trainer_name'] ?? 'Unassigned') ?></li>
                                </ul>
                                <div class="class-ratings">
                                    <div class="stars">
                                        <?php
                                        $fullStars = round($c['avg_rating'] ?? 0);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo '<span class="star ' . ($i <= $fullStars ? 'on' : '') . '">★</span>';
                                        }
                                        ?>
                                    </div>
                                    <span class="text-muted rating-text"><?= $rating ?> &nbsp;(<?= $reviewCount ?>)</span>
                                </div>
                            </div>
                            <div class="class-foot">
                                <div class="cap-wrap">
                                    <div class="cap-bar">
                                        <div class="cap-fill <?= $capClass ?>" style="width: <?= $pctFull ?>%"></div>
                                    </div>
                                    <span><?= $enrolled ?> / <?= $capacity ?></span>
                                </div>
                                <div class="flex gap-sm">
                                    <button class="secondary-button btn-sm edit-class-btn"
                                        data-id="<?= $c['id'] ?>"
                                        data-name="<?= htmlspecialchars($c['name']) ?>"
                                        data-desc="<?= htmlspecialchars($c['description'] ?? '') ?>"
                                        data-type="<?= htmlspecialchars($c['type']) ?>"
                                        data-trainer="<?= $c['trainer_id'] ?? '' ?>"
                                        data-capacity="<?= $c['capacity'] ?>"
                                        data-duration="<?= $c['duration_min'] ?>"
                                        data-day="<?= htmlspecialchars($c['day_of_week']) ?>"
                                        data-time="<?= htmlspecialchars($c['start_time']) ?>"
                                        data-location="<?= htmlspecialchars($c['location']) ?>">
                                        Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="action" value="<?= $c['active'] ? 'delete' : 'restore' ?>">
                                        <button type="submit" class="btn-sm" style="padding:0.4rem 0.9rem; font-size:0.8rem; min-height:36px; border-radius:var(--r-sm); font-weight:600; cursor:pointer; <?= $c['active'] ? 'background:var(--danger);color:#fff;border:2px solid transparent;' : 'background:var(--success);color:#fff;border:2px solid transparent;' ?>">
                                            <?= $c['active'] ? 'Delete' : 'Restore' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<!-- ─────────── CREATE MODAL ─────────── -->
<div class="modal-overlay" id="createClassModal" style="display:none;">
    <div class="auth-card add-equipment-card" style="position:relative; z-index:1001; max-height:90vh; overflow-y:auto;">
        <div class="auth-header">
            <h1>New Class</h1>
            <p>Add a class to the weekly schedule</p>
        </div>
        <form method="POST" class="auth-form">
            <input type="hidden" name="action" value="create">
            <?php include __DIR__ . '/includes/class-form-fields.php';
                  classFormFields($trainers, $days, $types); ?>
            <button type="submit" class="main-button full-button auth-submit">Create Class</button>
        </form>
        <button class="modal-close-btn" id="closeCreateModal" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--muted); font-size:1.5rem; cursor:pointer;">✕</button>
    </div>
</div>

<!-- ─────────── EDIT MODAL ─────────── -->
<div class="modal-overlay" id="editClassModal" style="display:none;">
    <div class="auth-card add-equipment-card" style="position:relative; z-index:1001; max-height:90vh; overflow-y:auto;">
        <div class="auth-header">
            <h1>Edit Class</h1>
            <p>Update class details</p>
        </div>
        <form method="POST" class="auth-form" id="editClassForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="class_id" id="editClassId">
            <div id="editClassFields"></div>
            <button type="submit" class="main-button full-button auth-submit">Save Changes</button>
        </form>
        <button class="modal-close-btn" id="closeEditModal" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--muted); font-size:1.5rem; cursor:pointer;">✕</button>
    </div>
</div>

<!-- ─────────── MODAL BACKDROP ─────────── -->
<div class="modal-backdrop" id="modalBackdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000;"></div>

<style>
/* Modal and extra helpers (inlined to keep the page self‑contained) */
.modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:1000; }
.modal-overlay { display:none; position:fixed; inset:0; z-index:1001; justify-content:center; align-items:flex-start; padding-top:5vh; }
.modal-overlay.open { display:flex; }

.flex-between { display:flex; align-items:center; justify-content:space-between; }
.gap-sm { gap:var(--sp-sm); }
.inactive-card { opacity:0.55; }
</style>

<script>
const trainers = <?= json_encode($trainers) ?>;
const days     = <?= json_encode($days) ?>;
const types    = <?= json_encode($types) ?>;

// ── Modal helpers ──
const backdrop = document.getElementById('modalBackdrop');

function openModal(id) {
    document.getElementById(id).classList.add('open');
    backdrop.style.display = 'block';
}
function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
    backdrop.style.display = 'none';
}

document.getElementById('openCreateModal').addEventListener('click', () => openModal('createClassModal'));
document.getElementById('closeCreateModal').addEventListener('click', closeAllModals);
document.getElementById('closeEditModal').addEventListener('click', closeAllModals);
backdrop.addEventListener('click', closeAllModals);

// ── Edit button ──
document.querySelectorAll('.edit-class-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('editClassId').value = d.id;
        document.getElementById('editClassFields').innerHTML = buildClassForm(d);
        openModal('editClassModal');
    });
});

function buildClassForm(d) {
    const trainerOpts = '<option value="">No trainer</option>' + trainers.map(t =>
        `<option value="${t.id}" ${d.trainer == t.id ? 'selected' : ''}>${t.name}</option>`
    ).join('');

    const dayOpts = days.map(day =>
        `<option value="${day}" ${d.day === day ? 'selected' : ''}>${day}</option>`
    ).join('');

    const typeOpts = types.map(t =>
        `<option value="${t}" ${d.type === t ? 'selected' : ''}>${t.charAt(0).toUpperCase() + t.slice(1)}</option>`
    ).join('');

    return `
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="${d.name}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">${typeOpts}</select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="2">${d.desc || ''}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Trainer</label>
                <select name="trainer_id" class="form-select">${trainerOpts}</select>
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input" value="${d.location}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Day</label>
                <select name="day_of_week" class="form-select">${dayOpts}</select>
            </div>
            <div class="form-group">
                <label class="form-label">Time</label>
                <input type="time" name="start_time" class="form-input" value="${d.time}" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" class="form-input" value="${d.capacity}" min="1">
            </div>
            <div class="form-group">
                <label class="form-label">Duration (min)</label>
                <input type="number" name="duration_min" class="form-input" value="${d.duration}" min="15">
            </div>
        </div>
    `;
}
</script>

<?php output_footer(); ?>