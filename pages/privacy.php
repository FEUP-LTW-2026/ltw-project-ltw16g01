<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';

output_header('legal.css');
?>

<main class="main">
    <section class="legal-section">
        <div class="container">
            <header class="page-header">
                <nav>
                    <ol class="breadcrumb">
                        <li><a href="../index.php">Home</a></li>
                        <li><span class="breadcrumb-sep">›</span></li>
                        <li><span>Privacy Policy</span></li>
                    </ol>
                </nav>

                <h1>Privacy Policy</h1>
                <p>Last updated: 2026</p>
            </header>

            <div class="legal-card">
                <h2>1. Introduction</h2>
                <p>
                    This Privacy Policy explains how FitPeak collects, uses and protects user information
                    when people use the platform.
                </p>

                <h2>2. Information We Collect</h2>
                <p>
                    We may collect information such as name, username, email address, password, profile photo,
                    membership plan, class enrollments, reviews and platform activity.
                </p>

                <h2>3. How We Use Information</h2>
                <p>
                    We use this information to create and manage accounts, process class bookings,
                    manage memberships, show trainer profiles, improve the platform and provide notifications.
                </p>

                <h2>4. Passwords and Security</h2>
                <p>
                    Passwords should be stored securely. Users are responsible for choosing a safe password
                    and keeping their account credentials private.
                </p>

                <h2>5. Profile Information</h2>
                <p>
                    Users may update personal profile information, including name, username, email and profile
                    photo. Some information may be visible to administrators or trainers when needed for gym operations.
                </p>

                <h2>6. Class and Booking Data</h2>
                <p>
                    FitPeak stores class enrollment information so members, trainers and administrators can
                    manage schedules, attendance and class availability.
                </p>

                <h2>7. Reviews and Ratings</h2>
                <p>
                    Reviews and ratings may be used to calculate trainer or class feedback and help other users
                    make informed choices.
                </p>

                <h2>8. Data Access</h2>
                <p>
                    Administrators may access user information when necessary to manage the gym, users, classes,
                    trainers, equipment and platform activity.
                </p>

                <h2>9. Data Protection</h2>
                <p>
                    FitPeak aims to protect user data through reasonable security measures. However, no digital
                    platform can guarantee complete protection against all risks.
                </p>

                <h2>10. Contact</h2>
                <p>
                    If you have questions about this Privacy Policy, you can contact FitPeak through the contact
                    information available on the platform.
                </p>
            </div>
        </div>
    </section>
</main>

<?php output_footer('../pages/'); ?>