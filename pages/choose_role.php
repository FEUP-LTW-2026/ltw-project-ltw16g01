<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';

if (!isset($_SESSION['user_roles']) || empty($_SESSION['user_roles'])) {
    header('Location: ../pages/login.php');
    exit;
}

$roles = $_SESSION['user_roles'];
$userName = $_SESSION['user_name'];

$roleUrls = [
    'member'  => '../member/dashboard.php',
    'trainer' => '../trainer/dashboard.php',
    'admin'   => '../admin/dashboard.php',
];

output_header('choose_role.css');
?>

<link rel="stylesheet" href="../css/pages/choose_role.css">.

<main class="main">
    <div class="container choose-role-container">
        <div class="choose-role-card">
            <h1>Welcome, <?= htmlspecialchars($userName) ?></h1>
            <p>You have multiple roles. Please choose how you want to proceed:</p>

            <div class="role-buttons">
                <?php foreach ($roles as $role): ?>
                    <?php if (isset($roleUrls[$role])): ?>
                        <a href="<?= $roleUrls[$role] ?>" class="main-button role-btn" data-role="<?= $role ?>">
                            <?= ucfirst($role) ?> Area
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="logout-link">
                <a href="logout.php">Not now? Logout</a>
            </div>
        </div>
    </div>
</main>

<script>
    // Quando clicar num botão, guardar a role escolhida na sessão via fetch (ou redirecionar com parâmetro)
    document.querySelectorAll('.role-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const role = this.dataset.role;
            // Guardar a role ativa através de uma chamada AJAX simples
            fetch('../ajax/set_active_role.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'role=' + encodeURIComponent(role)
            })
            .then(() => {
                window.location.href = this.href;
            });
        });
    });
</script>

<?php output_footer('../'); ?>