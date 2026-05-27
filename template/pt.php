<?php
// template/pt.php

function output_pt_hero() { ?>
    <section class="pt-hero">
        <div class="container">
            <div class="pt-hero-content">
                <p class="small-text">Personal Training</p>

                <h1>
                    Train With Our
                    <span class="highlight">Personal Trainers.</span>
                </h1>

                <p class="pt-description">
                    Our personal trainers are here to help you train with purpose,
                    improve your technique and stay consistent. Choose the right PT
                    for your goals and start building a stronger version of yourself.
                </p>

                <div class="pt-hero-actions">
                    <a href="#trainers" class="main-button">View Trainers</a>
                    <a href="classes.php" class="secondary-button">View Classes</a>
                </div>
            </div>
        </div>
    </section>
<?php }

function output_pt_benefits() { ?>
    <section class="pt-benefits-section">
        <div class="container">
            <header class="section-title">
                <h2>Why Train With a PT?</h2>
                <p>
                    Personal training gives you structure, motivation and professional
                    guidance adapted to your fitness level.
                </p>
            </header>

            <div class="pt-benefits-grid">
                <article class="pt-benefit-card">
                    <div class="pt-benefit-icon">🎯</div>
                    <h3>Personal Goals</h3>
                    <p>
                        Get support based on your own objectives, from fat loss and muscle gain
                        to mobility, strength or general fitness.
                    </p>
                </article>

                <article class="pt-benefit-card">
                    <div class="pt-benefit-icon">🏋️</div>
                    <h3>Better Technique</h3>
                    <p>
                        Learn how to perform exercises safely and correctly, reducing the risk
                        of injury and improving your results.
                    </p>
                </article>

                <article class="pt-benefit-card">
                    <div class="pt-benefit-icon">📈</div>
                    <h3>Progress Tracking</h3>
                    <p>
                        Follow your evolution with structured sessions, adjustments and guidance
                        from a professional trainer.
                    </p>
                </article>
            </div>
        </div>
    </section>
<?php }

function output_pt_cta() { ?>
    <section class="pt-cta-section">
        <div class="container">
            <div class="pt-cta-box">
                <div>
                    <p class="small-text">Start Today</p>
                    <h2>Ready to train with support?</h2>
                    <p>
                        Create your account and start booking personal training sessions
                        with our certified trainers.
                    </p>
                </div>

                <a href="register.php" class="main-button">Create Account</a>
            </div>
        </div>
    </section>
<?php }