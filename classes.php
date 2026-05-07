<?php
require_once 'includes/header.php';
require_once 'includes/footer.php';

output_header('classes.css');
?>

    <main class="main">
        <div class="container">

            <!-- Page Header with Breadcrumb -->
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
                    <p>Browse our schedule and enroll in upcoming sessions</p>
                </div>
            </header>

            <!-- Filter Section -->
            <section>
                <form class="filter-bar" method="get" action="classes.php">
                    <div class="filter-group">
                        <label class="form-label" for="f-type">Class type</label>
                        <select id="f-type" name="type" class="form-select">
                            <option value="">All types</option>
                            <option value="yoga">Yoga</option>
                            <option value="pilates">Pilates</option>
                            <option value="hiit">HIIT</option>
                            <option value="cycling">Cycling</option>
                            <option value="strength">Strength</option>
                            <option value="crossfit">CrossFit</option>
                            <option value="boxing">Boxing</option>
                            <option value="senior-fitness">Senior Fitness</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="form-label" for="f-trainer">Trainer</label>
                        <select id="f-trainer" name="trainer" class="form-select">
                            <option value="">All trainers</option>
                            <option value="1">Ana Silva</option>
                            <option value="2">Bruno Costa</option>
                            <option value="3">Carla Mendes</option>
                            <option value="4">Miguel Rocha</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="form-label" for="f-day">Day</label>
                        <select id="f-day" name="day" class="form-select">
                            <option value="any day">Any Day</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="filter-group filter-group-end">
                        <button type="submit" class="main-button full-width">Filter</button>
                    </div>
                </form>
            </section>

            <!-- Results Bar -->
            <div class="results-bar">
                <p class="results-count">
                    Showing <strong>8 classes</strong> available today
                </p>

                <div class="sort-row">
                    <span>Sort:</span>
                    <button type="button" class="sort-btn active">Date</button>
                    <button type="button" class="sort-btn">Name</button>
                    <button type="button" class="sort-btn">Spots</button>
                </div>
            </div>

            <!-- Classes Grid -->
            <section>
                <div class="class-grid">

                    <!-- Class 1: Yoga (enrolled) -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">07:00</span>
                            <span class="badge badge-info">Yoga</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">Yoga</h2>
                                <p>A balanced class focused on breathing, flexibility and body awareness. Ideal for starting the day with energy, mobility and a calmer mindset.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Mon, 21 Apr</li>
                                <li>⏱ 60 min</li>
                                <li>📍 Studio A</li>
                                <li>👤 Ana Silva</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                </div>
                                <span class="text-muted rating-text">4.9 &nbsp;(32 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill-80"></div>
                                </div>
                                <span>12 / 15</span>
                            </div>
                            <a href="classes.php" class="enrolled-btn">✓ Enrolled — Cancel</a>
                        </div>
                    </article>

                    <!-- Class 2: Pilates -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">10:00</span>
                            <span class="badge badge-info">Pilates</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">Pilates</h2>
                                <p>A controlled session focused on core strength, posture, stability and flexibility. Great for improving movement quality and preventing injuries.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Tue, 22 Apr</li>
                                <li>⏱ 55 min</li>
                                <li>📍 Studio B</li>
                                <li>👤 Ana Silva</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                </div>
                                <span class="text-muted rating-text">4.7 &nbsp;(19 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill-33"></div>
                                </div>
                                <span>4 / 12</span>
                            </div>
                            <a href="register.php" class="main-button btn-sm">Enroll</a>
                        </div>
                    </article>

                    <!-- Class 3: HIIT -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">18:00</span>
                            <span class="badge badge-danger">HIIT</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">HIIT</h2>
                                <p>A fast-paced interval workout mixing cardio bursts and strength exercises. Perfect for improving endurance, burning calories and boosting performance.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Wed, 23 Apr</li>
                                <li>⏱ 45 min</li>
                                <li>📍 Main Floor</li>
                                <li>👤 Bruno Costa</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                </div>
                                <span class="text-muted rating-text">4.8 &nbsp;(47 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill-40"></div>
                                </div>
                                <span>8 / 20</span>
                            </div>
                            <a href="register.php" class="main-button btn-sm">Enroll</a>
                        </div>
                    </article>

                    <!-- Class 4: Cycling (almost full) -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">19:00</span>
                            <span class="badge badge-warning">Cycling</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">Cycling</h2>
                                <p>A high-energy cycling session with sprint intervals, climbs and rhythm-based coaching. Ideal for cardio conditioning and lower-body endurance.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Wed, 24 Apr</li>
                                <li>⏱ 50 min</li>
                                <li>📍 Cycling Studio</li>
                                <li>👤 Carla Mendes</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star">★</span>
                                </div>
                                <span class="text-muted rating-text">4.6 &nbsp;(28 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill mid cap-fill-89"></div>
                                </div>
                                <span class="capacity-warning">16 / 18</span>
                            </div>
                            <a href="register.php" class="main-button btn-sm">Enroll</a>
                        </div>
                    </article>

                    <!-- Class 5: Strength -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">17:00</span>
                            <span class="badge badge-accent">Strength</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">Strength</h2>
                                <p>A structured strength-training class focused on major lifts, proper form and progressive overload. Suitable for building muscle and improving overall power.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Fri, 25 Apr</li>
                                <li>⏱ 60 min</li>
                                <li>📍 Weights Area</li>
                                <li>👤 Miguel Rocha</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                </div>
                                <span class="text-muted rating-text">4.7 &nbsp;(24 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill-43"></div>
                                </div>
                                <span>6 / 14</span>
                            </div>
                            <a href="register.php" class="main-button btn-sm">Enroll</a>
                        </div>
                    </article>

                    <!-- Class 6: CrossFit (full) -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">17:30</span>
                            <span class="badge badge-danger">CrossFit</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">CrossFit</h2>
                                <p>A demanding workout combining functional movements, intensity and variety. Designed to develop strength, stamina, mobility and total-body conditioning.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Fri, 25 Apr</li>
                                <li>⏱ 60 min</li>
                                <li>📍 Main Floor</li>
                                <li>👤 Bruno Costa</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star">★</span>
                                </div>
                                <span class="text-muted rating-text">4.4 &nbsp;(56 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div>
                                <span class="badge badge-danger">Class full</span>
                                <div class="text-muted enrollment-note">20 / 20 enrolled</div>
                            </div>
                        </div>
                    </article>

                    <!-- Class 7: Boxing -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">11:30</span>
                            <span class="badge badge-warning">Boxing</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">Boxing</h2>
                                <p>A dynamic boxing class focused on footwork, combinations, technique and conditioning. Great for coordination, cardio and explosive movement.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Sat, 26 Apr</li>
                                <li>⏱ 50 min</li>
                                <li>📍 Combat Studio</li>
                                <li>👤 Bruno Costa</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                </div>
                                <span class="text-muted rating-text">4.8 &nbsp;(26 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill-56"></div>
                                </div>
                                <span>9 / 16</span>
                            </div>
                            <a href="register.php" class="main-button btn-sm">Enroll</a>
                        </div>
                    </article>

                    <!-- Class 8: Senior Fitness -->
                    <article class="class-card">
                        <div class="class-head">
                            <span class="class-time-big">20:00</span>
                            <span class="badge badge-success">Senior Fitness</span>
                        </div>
                        <div class="class-body">
                            <div>
                                <h2 class="class-name">Senior Fitness</h2>
                                <p>A gentle and supervised class designed to improve mobility, balance, strength and daily functional movement in a safe and supportive environment.</p>
                            </div>
                            <ul class="class-meta">
                                <li>📅 Sat, 26 Apr</li>
                                <li>⏱ 45 min</li>
                                <li>📍 Studio C</li>
                                <li>👤 Carla Mendes</li>
                            </ul>
                            <div class="class-ratings">
                                <div class="stars">
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                    <span class="star on">★</span>
                                </div>
                                <span class="text-muted rating-text">5.0 &nbsp;(18 reviews)</span>
                            </div>
                        </div>
                        <div class="class-foot">
                            <div class="cap-wrap">
                                <div class="cap-bar">
                                    <div class="cap-fill cap-fill-36"></div>
                                </div>
                                <span>5 / 14</span>
                            </div>
                            <a href="register.php" class="main-button btn-sm">Enroll</a>
                        </div>
                    </article>

                </div>
            </section>

            <!-- Legend Section -->
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

    <!-- Site Footer -->
    <?php output_footer(); ?>
