<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$schedule_id = (int)($_GET['id'] ?? 0);

// Buscar horário e dados da aula
$stmt = $pdo->prepare("
    SELECT cs.*, c.name AS class_name, c.description AS class_description, c.class_type_id
    FROM class_schedules cs
    JOIN classes c ON cs.class_id = c.id
    WHERE cs.id = ?
");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header('Location: classes.php?error=notfound');
    exit;
}

// Listas para selects
$classTypes = $pdo->query("SELECT id, label FROM class_types WHERE active = 1 ORDER BY label")->fetchAll();
$trainers = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name")->fetchAll();
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name'] ?? '');
    $class_desc = trim($_POST['class_description'] ?? '');
    $class_type_id = (int)($_POST['class_type_id'] ?? 0);
    $trainer_id = $_POST['trainer_id'] ? (int)$_POST['trainer_id'] : null;
    $day_of_week = $_POST['day_of_week'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 20);
    $duration_min = (int)($_POST['duration_min'] ?? 60);

    if (empty($class_name) || $class_type_id == 0 || empty($day_of_week) || empty($start_time)) {
        $error = 'Please fill all required fields.';
    } else {
        try {
            $pdo->beginTransaction();
            // Atualizar classe
            $stmt = $pdo->prepare("UPDATE classes SET name = ?, description = ?, class_type_id = ? WHERE id = ?");
            $stmt->execute([$class_name, $class_desc, $class_type_id, $schedule['class_id']]);

            // Atualizar horário
            $stmt = $pdo->prepare("
                UPDATE class_schedules 
                SET trainer_id = ?, day_of_week = ?, start_time = ?, location = ?, capacity = ?, duration_min = ?
                WHERE id = ?
            ");
            $stmt->execute([$trainer_id, $day_of_week, $start_time, $location, $capacity, $duration_min, $schedule_id]);
            $pdo->commit();
            $success = 'Class updated successfully!';
            // Recarregar dados
            $stmt = $pdo->prepare("
                SELECT cs.*, c.name AS class_name, c.description AS class_description, c.class_type_id
                FROM class_schedules cs
                JOIN classes c ON cs.class_id = c.id
                WHERE cs.id = ?
            ");
            $stmt->execute([$schedule_id]);
            $schedule = $stmt->fetch();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <div class="container" style="max-width: 800px; margin-bottom: var(--sp-lg);">
        <header class="page-header">
            <h1>Edit Class</h1>
            <p>Update class details and schedule.</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="card" style="padding: var(--sp-lg);">
            <div class="form-group">
                <label class="form-label">Class Name *</label>
                <input type="text" name="class_name" class="form-control" required value="<?= htmlspecialchars($schedule['class_name']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="class_description" class="form-control" rows="3"><?= htmlspecialchars($schedule['class_description'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Class Type *</label>
                    <select name="class_type_id" class="form-select" required>
                        <?php foreach ($classTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $schedule['class_type_id'] == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Trainer</label>
                    <select name="trainer_id" class="form-select">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($trainers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $schedule['trainer_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Day of Week *</label>
                    <select name="day_of_week" class="form-select" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?= $day ?>" <?= $schedule['day_of_week'] == $day ? 'selected' : '' ?>><?= $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required value="<?= htmlspecialchars($schedule['start_time']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($schedule['location']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="1" value="<?= $schedule['capacity'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Duration (min)</label>
                    <input type="number" name="duration_min" class="form-control" min="15" step="15" value="<?= $schedule['duration_min'] ?>">
                </div>
            </div>
            <div class="form-actions" style="display: flex; gap: var(--sp-md); margin-top: var(--sp-lg);">
                <button type="submit" class="main-button">Update Class</button>
                <a href="classes.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>