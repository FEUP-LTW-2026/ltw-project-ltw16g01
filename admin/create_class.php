<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$error = '';
$success = '';

// Obter listas para os selects
$classTypes = $pdo->query("SELECT id, name, label FROM class_types WHERE active = 1 ORDER BY label")->fetchAll();
$trainers = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name")->fetchAll();

$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    $classDesc = trim($_POST['class_description'] ?? '');
    $classTypeId = $_POST['class_type_id'] ?? '';
    $trainerId = $_POST['trainer_id'] ?? null;
    $dayOfWeek = $_POST['day_of_week'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $location = trim($_POST['location'] ?? 'Studio A');
    $capacity = (int)($_POST['capacity'] ?? 20);
    $duration = (int)($_POST['duration_min'] ?? 60);

    if (empty($className) || empty($classTypeId) || empty($dayOfWeek) || empty($startTime)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Inserir classe (se ainda não existir exatamente com o mesmo nome e tipo)
            $stmt = $pdo->prepare("INSERT INTO classes (name, description, class_type_id, active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$className, $classDesc, $classTypeId]);
            $classId = $pdo->lastInsertId();

            // Inserir horário
            $stmt = $pdo->prepare("
                INSERT INTO class_schedules (class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$classId, $trainerId ?: null, $dayOfWeek, $startTime, $location, $capacity, $duration]);

            $success = 'Class created successfully!';
            // Limpar formulário (opcional)
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

output_header('form.css'); // Pode usar um CSS genérico para formulários
?>

<main class="main">
    <div class="container" style="max-width: 800px;">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="classes.php">Classes</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>New Class</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Create New Class</h1>
                <p>Add a new class schedule to the system.</p>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form card" style="padding: var(--sp-lg);">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Class Name *</label>
                    <input type="text" name="class_name" class="form-control" required value="<?= htmlspecialchars($_POST['class_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Class Type *</label>
                    <select name="class_type_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($classTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= (($_POST['class_type_id'] ?? '') == $type['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="class_description" class="form-control" rows="3"><?= htmlspecialchars($_POST['class_description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Day of Week *</label>
                    <select name="day_of_week" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($daysOfWeek as $day): ?>
                            <option value="<?= $day ?>" <?= (($_POST['day_of_week'] ?? '') == $day) ? 'selected' : '' ?>><?= $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Trainer</label>
                    <select name="trainer_id" class="form-select">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($trainers as $trainer): ?>
                            <option value="<?= $trainer['id'] ?>" <?= (($_POST['trainer_id'] ?? '') == $trainer['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($trainer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($_POST['location'] ?? 'Studio A') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="1" value="<?= htmlspecialchars($_POST['capacity'] ?? 20) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_min" class="form-control" min="15" step="15" value="<?= htmlspecialchars($_POST['duration_min'] ?? 60) ?>">
                </div>
            </div>

            <div style="display: flex; gap: var(--sp-md); margin-top: var(--sp-md);">
                <button type="submit" class="main-button">Create Class</button>
                <a href="classes.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>