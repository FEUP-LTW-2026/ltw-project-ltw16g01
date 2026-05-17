<?php
    require_once 'includes/header.php';
    require_once 'includes/footer.php';

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
                    <div class="stats-grid">
                        <div class="stat-box">
                            <p class="stat-number">500</p>
                            <p class="stat-label">Active Members</p>
                        </div>
                        <div class="stat-box">
                            <p class="stat-number">48</p>
                            <p class="stat-label">Classes / Week</p>
                        </div>
                        <div class="stat-box">
                            <p class="stat-number">15</p>
                            <p class="stat-label">Trainers</p>
                        </div>
                        <div class="stat-box">
                            <p class="stat-number">98%</p>
                            <p class="stat-label">Satisfaction</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Animated Ticker: scrolling class types for visual impact -->
        <section class="classes-section">
            <div class="ticker-track">
                <div class="ticker-group">
                    <span>Yoga</span><span>●</span>
                    <span>Pilates</span><span>●</span>
                    <span>HIIT</span><span>●</span>
                    <span>Cycling</span><span>●</span>
                    <span>Strength</span><span>●</span>
                    <span>CrossFit</span><span>●</span>
                    <span>Boxing</span><span>●</span>
                    <span>Senior Fitness</span><span>●</span>
                </div>

                <!-- Duplicate groups for seamless infinite scroll -->
                <div class="ticker-group">
                    <span>Yoga</span><span>●</span>
                    <span>Pilates</span><span>●</span>
                    <span>HIIT</span><span>●</span>
                    <span>Cycling</span><span>●</span>
                    <span>Strength</span><span>●</span>
                    <span>CrossFit</span><span>●</span>
                    <span>Boxing</span><span>●</span>
                    <span>Senior Fitness</span><span>●</span>
                </div>

                <div class="ticker-group">
                    <span>Yoga</span><span>●</span>
                    <span>Pilates</span><span>●</span>
                    <span>HIIT</span><span>●</span>
                    <span>Cycling</span><span>●</span>
                    <span>Strength</span><span>●</span>
                    <span>CrossFit</span><span>●</span>
                    <span>Boxing</span><span>●</span>
                    <span>Senior Fitness</span><span>●</span>
                </div>

                <div class="ticker-group">
                    <span>Yoga</span><span>●</span>
                    <span>Pilates</span><span>●</span>
                    <span>HIIT</span><span>●</span>
                    <span>Cycling</span><span>●</span>
                    <span>Strength</span><span>●</span>
                    <span>CrossFit</span><span>●</span>
                    <span>Boxing</span><span>●</span>
                    <span>Senior Fitness</span><span>●</span>
                </div>

            </div>
        </section>

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
                    <a href="pages/about.php" class="feature-card">
                        <div class="feature-icon">🏠</div>
                        <h3>About Us</h3>
                        <p>Learn more about our mission, values and the community behind FitPeak.</p>
                    </a>

                    <a href="pages/pt.php" class="feature-card">
                        <div class="feature-icon">👤</div>
                        <h3>Personal Trainers</h3>
                        <p>Detailed profiles with specializations, ratings and session bookings.</p>
                    </a>

                    <a href="pages/ratings-reviews.php" class="feature-card">
                        <div class="feature-icon">⭐</div>
                        <h3>Ratings &amp; Reviews</h3>
                        <p>Review your classes and help other members choose better.</p>
                    </a>

                    <article class="feature-card-two">
                        <div class="feature-icon">🏋️</div>
                        <h3>Equipment Availability</h3>
                        <p>Check equipment availability before leaving home.</p>
                    </article>

                    <article class="feature-card-two">
                        <div class="feature-icon">📊</div>
                        <h3>Admin Dashboard</h3>
                        <p>Admins can manage users, classes and equipment in one place.</p>
                    </article>

                    <article class="feature-card-two">
                        <div class="feature-icon">🏃</div>
                        <h3>Classes &amp; Bookings</h3>
                        <p>Check the full schedule, filter by type or trainer and join with one click.</p>
                    </article>
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
    <?php output_footer(); ?>