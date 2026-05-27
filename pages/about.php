<?php
require_once '../includes/header.php';
require_once '../includes/footer.php';

output_header('about.css');
?>

<main class="main">

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <p class="small-text">About FitPeak</p>

                <h1>
                    Built for People Who Want to 
                    <span class="highlight">Train Better.</span>
                </h1>

                <p class="about-description">
                    FitPeak was created with a simple idea: make the gym experience easier,
                    more modern and more connected. What started as a small fitness space
                    quickly became a complete community focused on health, motivation and progress.
                </p>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-grid">

                <div class="about-text">
                    <span class="section-label">Our Story</span>
                    <h2>How Our Gym Started</h2>

                    <p>
                        FitPeak was born from the passion for fitness and the need to create
                        a gym that was more than just a place to train. The idea started with
                        a small group of people who believed that training should be simple,
                        accessible and motivating for everyone.
                    </p>

                    <p>
                        In the beginning, our goal was to create a space where members could
                        feel comfortable, supported and inspired. Over time, FitPeak grew into
                        a modern gym with different classes, personal trainers, equipment
                        management and digital tools to help members organise their training.
                    </p>

                    <p>
                        Today, FitPeak combines fitness, technology and community. Our platform
                        helps members book classes, check trainers, manage their profile and
                        follow their progress in a simple and practical way.
                    </p>
                </div>

                <div class="about-card">
                    <div class="about-card-icon">🏋️</div>
                    <h3>More Than a Gym</h3>
                    <p>
                        We are a community built around progress, discipline and support.
                        Our mission is to help every member become stronger, healthier and
                        more confident.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Mission / Vision / Values -->
    <section class="values-section">
        <div class="container">

            <header class="section-title">
                <h2>What We Stand For</h2>
                <p>
                    Our gym is guided by clear values that shape the way we train,
                    support and grow together.
                </p>
            </header>

            <div class="values-grid">

                <article class="value-card">
                    <div class="value-icon">🎯</div>
                    <h3>Mission</h3>
                    <p>
                        To provide a complete and motivating fitness experience,
                        helping members reach their goals through training, guidance
                        and technology.
                    </p>
                </article>

                <article class="value-card">
                    <div class="value-icon">🚀</div>
                    <h3>Vision</h3>
                    <p>
                        To become a modern fitness community where every person has
                        the tools, support and confidence to improve their lifestyle.
                    </p>
                </article>

                <article class="value-card">
                    <div class="value-icon">🤝</div>
                    <h3>Community</h3>
                    <p>
                        We believe that progress is easier when people feel supported.
                        That is why FitPeak values respect, motivation and teamwork.
                    </p>
                </article>

            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-section">
        <div class="container">
            <div class="why-layout">

                <div class="why-content">
                    <span class="section-label">Why Choose Us</span>
                    <h2>A Smarter Way to Train</h2>

                    <p>
                        FitPeak connects the physical gym experience with a digital platform.
                        This allows members to plan their workouts, check available services
                        and manage their gym activity more easily.
                    </p>

                    <ul class="why-list">
                        <li>Modern gym environment</li>
                        <li>Professional personal trainers</li>
                        <li>Group classes for different fitness levels</li>
                        <li>Book classes and trainer sessions quickly and easily</li>
                        <li>Strong focus on motivation and member progress</li>
                    </ul>

                    <a href="register.php" class="main-button">Join FitPeak</a>
                </div>

            </div>
        </div>
    </section>

</main>

<?php output_footer('../pages/'); ?>