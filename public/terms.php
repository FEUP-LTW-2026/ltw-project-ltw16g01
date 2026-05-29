<?php
/**
 * terms.php – Static page displaying Terms of Service.
 */

require_once '../includes/session.php';

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
                        <li><span>Terms of Service</span></li>
                    </ol>
                </nav>

                <h1>Terms of Service</h1>
                <p>Last updated: 2026</p>
            </header>

            <div class="legal-card">
                <h2>1. Acceptance of Terms</h2>
                <p>
                    By creating an account or using FitPeak, you agree to follow these Terms of Service.
                    If you do not agree with these terms, you should not use the platform.
                </p>

                <h2>2. Use of the Platform</h2>
                <p>
                    FitPeak allows users to manage their gym experience, including class bookings,
                    trainer information, equipment availability and profile management.
                </p>

                <h2>3. User Account</h2>
                <p>
                    You are responsible for keeping your login details secure. You should not share your
                    password or allow other people to access your account.
                </p>

                <h2>4. Membership Plans</h2>
                <p>
                    Membership plans may include different access levels, class limits and personal training
                    options. The available features depend on the selected plan.
                </p>

                <h2>5. Class Bookings</h2>
                <p>
                    Members may enroll in available classes through the platform. Bookings may be limited
                    by capacity, schedule availability and membership conditions.
                </p>

                <h2>6. Cancellations</h2>
                <p>
                    Users should cancel bookings when they cannot attend. FitPeak may update or cancel
                    classes when necessary, including due to trainer availability or operational reasons.
                </p>

                <h2>7. Reviews and Ratings</h2>
                <p>
                    Users may leave reviews and ratings after attending classes. Reviews should be respectful,
                    honest and related to the gym experience.
                </p>

                <h2>8. Platform Availability</h2>
                <p>
                    We aim to keep FitPeak available and updated, but we do not guarantee that the platform
                    will always be free from interruptions, errors or maintenance periods.
                </p>

                <h2>9. Changes to the Terms</h2>
                <p>
                    FitPeak may update these Terms of Service when necessary. Continued use of the platform
                    after changes means that you accept the updated terms.
                </p>

                <h2>10. Contact</h2>
                <p>
                    If you have questions about these terms, you can contact FitPeak through the contact
                    information available on the platform.
                </p>
            </div>
        </div>
    </section>
</main>

<?php output_footer('../public/'); ?>