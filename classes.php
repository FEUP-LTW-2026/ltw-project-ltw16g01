<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/footer.php';
require_once 'db/connection.php';

$pdo = getDatabaseConnection();

// Treinadores para o filtro
$trainers = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' ORDER BY name")->fetchAll();

// Filtros e ordenação
$type    = $_GET['type']    ?? '';
$trainer = $_GET['trainer'] ?? '';
$day     = $_GET['day']     ?? '';   // monday, tuesday, …
$sort    = $_GET['sort']    ?? 'date'; // date | name | spots

$dayMap = [
    'sunday'    => 'Sunday',
    'monday'    => 'Monday',
    'tuesday'   => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday'  => 'Thursday',
    'friday'    => 'Friday',
    'saturday'  => 'Saturday',
];

// Query base: todas as aulas ativas com treinador, avaliação média e total de inscrições futuras
$sql = "
    SELECT
        cl.id AS class_id,
        cl.name AS class_name,
        cl.description,
        cl.type,
        cl.capacity,
        cl.duration_min,
        cl.location,
        cl.day_of_week,
        cl.start_time,
        u.name AS trainer_name,
        COALESCE((SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.class_id = cl.id), 0) AS avg_rating,
        COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.class_id = cl.id), 0) AS review_count,
        COALESCE((SELECT COUNT(*)
                  FROM enrollments en
                  JOIN class_sessions cs ON en.session_id = cs.id
                  WHERE cs.class_id = cl.id
                    AND cs.session_date >= DATE('now')
                    AND en.status = 'enrolled'), 0) AS enrolled_count
    FROM classes cl
    JOIN users u ON cl.trainer_id = u.id
    WHERE cl.active = 1
";

$conditions = [];
$params = [];

if ($type !== '') {
    $conditions[] = "cl.type = :type";
    $params[':type'] = $type;
}
if ($trainer !== '' && is_numeric($trainer)) {
    $conditions[] = "cl.trainer_id = :trainer";
    $params[':trainer'] = (int)$trainer;
}
if ($day !== '' && isset($dayMap[$day])) {
    $conditions[] = "cl.day_of_week = :dow";
    $params[':dow'] = $dayMap[$day];
}

if ($conditions) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Ordenação
switch ($sort) {
    case 'name':
        $sql .= " ORDER BY cl.name ASC";
        break;
    case 'spots':
        $sql .= " ORDER BY enrolled_count ASC, cl.name ASC";
        break;
    default: // date – ordena por dia da semana e depois hora
        $sql .= " ORDER BY
            CASE cl.day_of_week
                WHEN 'Sunday'    THEN 0
                WHEN 'Monday'    THEN 1
                WHEN 'Tuesday'   THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday'  THEN 4
                WHEN 'Friday'    THEN 5
                WHEN 'Saturday'  THEN 6
            END,
            cl.start_time";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$classes = $stmt->fetchAll();

// Função auxiliar para a cor da barra de capacidade
function capClass($enrolled, $capacity) {
    if ($capacity == 0) return '';
    $pct = $enrolled / $capacity;
    if ($pct >= 0.9) return 'mid';      // >90% preenchido → laranja
    if ($pct >= 0.75) return 'medium'; // >75% → amarelo
    return '';
}

output_header('classes.css');
?>

<main class="main">
    <div class="container">

        <!-- Cabeçalho com breadcrumb -->
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Classes</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Classes</h1>
                <p>Browse our weekly class catalogue</p>
            </div>
        </header>

        <!-- Filtros -->
        <section>
            <form class="filter-bar" method="get" action="classes.php">
                <div class="filter-group">
                    <label class="form-label" for="f-type">Class type</label>
                    <select id="f-type" name="type" class="form-select">
                        <option value="">All types</option>
                        <?php
                        $types = ['yoga','pilates','hiit','cycling','strength','crossfit','boxing','senior-fitness'];
                        foreach ($types as $t):
                            $sel = $type === $t ? 'selected' : '';
                        ?>
                            <option value="<?= $t ?>" <?= $sel ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-trainer">Trainer</label>
                    <select id="f-trainer" name="trainer" class="form-select">
                        <option value="">All trainers</option>
                        <?php foreach ($trainers as $tr): ?>
                            <option value="<?= $tr['id'] ?>" <?= ($trainer == $tr['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tr['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-day">Day</label>
                    <select id="f-day" name="day" class="form-select">
                        <option value="">Any Day</option>
                        <?php foreach (array_keys($dayMap) as $d):
                            $sel = $day === $d ? 'selected' : '';
                        ?>
                            <option value="<?= $d ?>" <?= $sel ?>><?= ucfirst($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group filter-group-end">
                    <button type="submit" class="main-button full-width">Filter</button>
                </div>
            </form>
        </section>

        <!-- Barra de resultados -->
        <div class="results-bar">
            <p class="results-count">
                Showing <strong><?= count($classes) ?></strong> class<?= count($classes) !== 1 ? 'es' : '' ?>
            </p>
            <div class="sort-row">
                <span>Sort:</span>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'date'])) ?>" class="sort-btn <?= $sort === 'date' ? 'active' : '' ?>">Date</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'name'])) ?>" class="sort-btn <?= $sort === 'name' ? 'active' : '' ?>">Name</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'spots'])) ?>" class="sort-btn <?= $sort === 'spots' ? 'active' : '' ?>">Spots</a>
            </div>
        </div>

        <!-- Grelha de aulas -->
        <section>
            <div class="class-grid">
                <?php if (empty($classes)): ?>
                    <p class="text-muted">No classes match your criteria.</p>
                <?php else: ?>
                    <?php foreach ($classes as $c):
                        $enrolled     = (int)$c['enrolled_count'];
                        $capacity     = (int)$c['capacity'];
                        $pctFull      = ($capacity > 0) ? round(($enrolled / $capacity) * 100) : 0;
                        $capClass     = capClass($enrolled, $capacity);
                        $rating       = $c['avg_rating'] ? number_format($c['avg_rating'], 1) : '—';
                        $reviewCount  = $c['review_count'];

                        $typeBadgeMap = [
                            'yoga'           => 'badge-info',
                            'pilates'        => 'badge-info',
                            'hiit'           => 'badge-danger',
                            'cycling'        => 'badge-warning',
                            'strength'       => 'badge-accent',
                            'crossfit'       => 'badge-danger',
                            'boxing'         => 'badge-warning',
                            'senior-fitness' => 'badge-success',
                        ];
                        $badgeClass = $typeBadgeMap[$c['type']] ?? 'badge-muted';

                        $dayText = $c['day_of_week'] . 's'; // "Mondays", "Tuesdays", …
                        $time    = date('H:i', strtotime($c['start_time']));
                    ?>
                        <article class="class-card">
                            <div class="class-head">
                                <span class="class-time-big"><?= $time ?></span>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($c['type'])) ?></span>
                            </div>
                            <div class="class-body">
                                <div>
                                    <h2 class="class-name"><?= htmlspecialchars($c['class_name']) ?></h2>
                                    <p><?= htmlspecialchars($c['description']) ?></p>
                                </div>
                                <ul class="class-meta">
                                    <li>📅 <?= $dayText ?></li>
                                    <li>⏱ <?= $c['duration_min'] ?> min</li>
                                    <li>📍 <?= htmlspecialchars($c['location']) ?></li>
                                    <li>👤 <?= htmlspecialchars($c['trainer_name']) ?></li>
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
                                    <span class="text-muted rating-text"><?= $rating ?> &nbsp;(<?= $reviewCount ?> review<?= $reviewCount != 1 ? 's' : '' ?>)</span>
                                </div>
                            </div>
                            <div class="class-foot">
                                <div class="cap-wrap">
                                    <div class="cap-bar">
                                        <div class="cap-fill <?= $capClass ?>" style="width: <?= $pctFull ?>%"></div>
                                    </div>
                                    <span><?= $enrolled ?> / <?= $capacity ?></span>
                                </div>
                                <?php if ($enrolled >= $capacity): ?>
                                    <span class="badge badge-danger">Class full</span>
                                <?php elseif (isset($_SESSION['user_id'])): ?>
                                    <span class="text-muted" style="font-size:0.75rem;">No upcoming sessions</span>
                                <?php else: ?>
                                    <a href="login.php" class="main-button btn-sm">Login to Enroll</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Legenda -->
        <section class="legend-section">
            <p class="legend-title">Class type legend</p>
            <div class="legend-row">
                <span class="badge badge-danger">Class Full</span>
                <span class="badge badge-success">✓ Enrolled - Cancel</span>
                <span class="legend-capacity-text">
                    Capacity bar:
                    <span class="dot-success">●</span> available &nbsp;
                    <span class="dot-warning">●</span> filling up &nbsp;
                    <span class="dot-danger">●</span> nearly full
                </span>
            </div>
        </section>

    </div>
</main>

<?php output_footer(); ?>