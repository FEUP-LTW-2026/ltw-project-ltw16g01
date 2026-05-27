<?php
    require_once 'includes/header.php';
    require_once 'includes/footer.php';
    require_once 'db/home.php';
    require_once 'db/connection.php';
    require_once 'template/home.php';

    $pdo = getDatabaseConnection();
    $homeStats = getHomeStats($pdo);
    $classTypes = getHomeClassTypes($pdo);

    output_header('index.css');

?>

    <main class="main">

        <!-- Hero Section with introductory text and stats -->
        <section class="intro-section">
            <div class="container">
                <div class="intro-layout">

                    <div class="intro-content">
                        <p class="small-text">The Gym of the Future</p>
                        <h1>
                            Train More.<br>
                            <span class="highlight">Live Better.</span>
                        </h1>
                        <p class="description">
                            A complete gym management platform. Join classes, check equipment availability and book trainer sessions — all in one place.
                        </p>
                        <div class="buttons-row">
                            <a href="pages/register.php" class="main-button">Create Free Account</a>
                            <a href="pages/classes.php" class="secondary-button">View Classes</a>
                        </div>
                    </div>

                    <!-- Stats Grid (visible on larger screens) -->
                    <?php outputStatsGrid($homeStats); ?>

                </div>
            </div>
        </section>

        <!-- Animated Ticker: scrolling class types for visual impact -->
        <?php outputClassTicker($classTypes); ?>

        <!-- Features Section: highlights platform capabilities -->
        <section class="features-section" id="features">
            <div class="container">
                <header class="section-title">
                    <h2>Everything You Need</h2>
                    <p>
                        Tools built for members, trainers and administrators.
                        Simple, fast and always available.
                    </p>
                </header>

                <div class="features-grid">
                    <?php
                        outputFeatureCard('🏠', 'About Us', 'Learn more about our mission, values and the community behind FitPeak.', 'pages/about.php', 'feature-card');

                        outputFeatureCard('👤', 'Personal Trainers', 'Detailed profiles with specializations, ratings and session bookings.', 'pages/pt.php', 'feature-card');

                        outputFeatureCard('⭐', 'Ratings & Reviews', 'Review your classes and help other members choose better.', 'pages/classes.php', 'feature-card');

                        outputFeatureCard('🏋️', 'Equipment Availability', 'Check equipment availability before leaving home.', 'pages/equipment.php', 'feature-card');

                        outputFeatureCard('💳', 'Membership Plans', 'Find the right plan for your lifestyle, goals and training needs.', '#plans', 'feature-card');

                        outputFeatureCard('🏃', 'Classes & Bookings', 'Check the full schedule, filter by type or trainer and join with one click.', 'pages/classes.php', 'feature-card');
                    ?>    
                </div>
            </div>
        </section>

        <!-- Pricing Plans Section: Basic, Premium (featured), VIP -->
        <section class="plans-section" id="plans">
            <div class="container">
                <header class="section-title">
                    <h2>Membership Plans</h2>
                    <p>Choose the plan that fits your lifestyle.</p>
                </header>

                <div class="plans-grid">

                    <!-- Basic Plan -->
                    <article class="plan-card">
                        <span class="plan-tag">Basic</span>
                        <div class="plan-price">
                            €19<span>/month</span>
                        </div>
                        <ul class="plan-list">
                            <li>✓ Gym access 9am-5pm</li>
                            <li>✓ 2 classes per week</li>
                            <li>✓ Management app</li>
                            <li>✗ PT sessions</li>
                            <li>✗ Equipment reservation</li>
                        </ul>
                        <a href="pages/register.php" class="secondary-button full-button">Get Started</a>
                    </article>

                    <!-- Premium Plan (featured) -->
                    <article class="plan-card featured-plan">
                        <div class="plan-top-text">
                            <span>Most Popular</span>
                        </div>
                        <span class="plan-tag highlight-tag">Premium</span>
                        <div class="plan-price accent-price">
                            €39<span>/month</span>
                        </div>
                        <ul class="plan-list">
                            <li>✓ Unlimited access</li>
                            <li>✓ Unlimited classes</li>
                            <li>✓ Management app</li>
                            <li>✓ 2 PT sessions/month</li>
                            <li>✗ Equipment reservation</li>
                        </ul>
                        <a href="pages/register.php" class="main-button full-button">Choose Premium</a>
                    </article>

                    <!-- VIP Plan -->
                    <article class="plan-card">
                        <span class="plan-tag vip-tag">VIP</span>
                        <div class="plan-price vip-price">
                            €69<span>/month</span>
                        </div>
                        <ul class="plan-list">
                            <li>✓ Unlimited access</li>
                            <li>✓ Unlimited classes</li>
                            <li>✓ Management app</li>
                            <li>✓ Daily PT sessions</li>
                            <li>✓ Equipment reservation</li>
                        </ul>
                        <a href="pages/register.php" class="secondary-button full-button">Choose VIP</a>
                    </article>

                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="start-section" id="contact">
            <div class="container center-text">
                <h2>Ready to get started?</h2>
                <p>Sign up for free today and transform your lifestyle.</p>
                <a href="pages/register.php" class="main-button">Create Account Now</a>
            </div>
        </section>

    </main>

    <!-- Site Footer -->
    <?php output_footer('pages/'); ?>