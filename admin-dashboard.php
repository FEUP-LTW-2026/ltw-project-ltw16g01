<?php
require_once 'includes/header.php';
require_once 'includes/footer.php';

output_header('admin-dashboard.css');
?>

    <main class="main">

        <!-- Dashboard Header with date and page title -->
        <header class="dash-header">
            <div class="dashboard-header">
                <div class="dash-top">
                    <div>
                        <p class="dash-date">Monday, 20 April 2026</p>
                        <h1 class="dash-h1">Admin <span>Dashboard</span></h1>
                    </div>
                    <div class="dash-top-right">
                        <a href="#" class="main-button btn-sm">+ New User</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="dashboard-content">

            <!-- KPI Cards: key metrics overview -->
            <section>
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <p class="kpi-num">247</p>
                        <p class="kpi-lbl">👥 Active Members</p>
                    </div>
                    <div class="kpi-card kpi-card-warning">
                        <p class="kpi-num kpi-num-warning">12</p>
                        <p class="kpi-lbl">🏋️ Trainers</p>
                    </div>
                    <div class="kpi-card kpi-card-success">
                        <p class="kpi-num kpi-num-success">48</p>
                        <p class="kpi-lbl">📅 Active Classes / Week</p>
                    </div>
                    <div class="kpi-card kpi-card-info">
                        <p class="kpi-num kpi-num-info">94%</p>
                        <p class="kpi-lbl">⭐ Satisfaction</p>
                    </div>
                    <div class="kpi-card">
                        <p class="kpi-num kpi-num-large">1 284</p>
                        <p class="kpi-lbl">📈 Enrollments / Month</p>
                    </div>
                    <div class="kpi-card kpi-card-danger">
                        <p class="kpi-num kpi-num-danger">6</p>
                        <p class="kpi-lbl">⚠️ Equipment Issues</p>
                    </div>
                </div>
            </section>

            <!-- Quick Actions: shortcuts to management sections -->
            <nav>
                <div class="quick-grid">
                    <a href="#" class="quick-item">
                        <span class="quick-icon">👤</span>
                        Manage Members
                    </a>
                    <a href="#" class="quick-item">
                        <span class="quick-icon">🏋️</span>
                        Manage Trainers
                    </a>
                    <a href="classes.php" class="quick-item">
                        <span class="quick-icon">📅</span>
                        Manage Classes
                    </a>
                    <a href="#" class="quick-item">
                        <span class="quick-icon">⚙️</span>
                        Manage Equipment
                    </a>
                    <a href="#" class="quick-item">
                        <span class="quick-icon">📊</span>
                        Reports
                    </a>
                    <a href="#" class="quick-item">
                        <span class="quick-icon">🔔</span>
                        Notifications
                    </a>
                </div>
            </nav>

            <!-- Two-Column Layout: left column (charts & tables) right column (activity & equipment) -->
            <div class="dash-layout">

                <!-- Left Column -->
                <div class="dash-col">

                    <!-- Bar Chart: weekly enrollments -->
                    <div class="card">
                        <div class="card-head">
                            <h2 class="card-title">Weekly Enrollments</h2>
                            <span class="badge badge-accent">+18% vs last month</span>
                        </div>
                        <div class="card-body card-body-no-bottom">
                            <div class="bar-chart-outer">
                                <div class="bar-chart">
                                    <div class="bar-col">
                                        <div class="bar-val">142</div>
                                        <div class="bar bar-h-52"></div>
                                    </div>
                                    <div class="bar-col">
                                        <div class="bar-val">168</div>
                                        <div class="bar bar-h-61"></div>
                                    </div>
                                    <div class="bar-col">
                                        <div class="bar-val">155</div>
                                        <div class="bar bar-h-56"></div>
                                    </div>
                                    <div class="bar-col">
                                        <div class="bar-val">189</div>
                                        <div class="bar bar-h-69"></div>
                                    </div>
                                    <div class="bar-col">
                                        <div class="bar-val">204</div>
                                        <div class="bar bar-h-74"></div>
                                    </div>
                                    <div class="bar-col">
                                        <div class="bar-val">231</div>
                                        <div class="bar bar-h-84"></div>
                                    </div>
                                    <div class="bar-col">
                                        <div class="bar-val bar-val-warning">247</div>
                                        <div class="bar current bar-h-90"></div>
                                    </div>
                                </div>
                                <div class="bar-labels">
                                    <span class="bar-lbl">W1<br>Mar</span>
                                    <span class="bar-lbl">W2<br>Mar</span>
                                    <span class="bar-lbl">W3<br>Mar</span>
                                    <span class="bar-lbl">W4<br>Mar</span>
                                    <span class="bar-lbl">W1<br>Apr</span>
                                    <span class="bar-lbl">W2<br>Apr</span>
                                    <span class="bar-lbl bar-lbl-warning">W3<br>Apr</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Most Popular Classes Table -->
                    <div class="card">
                        <div class="card-head">
                            <h2 class="card-title">Most Popular Classes</h2>
                            <a href="classes.php" class="secondary-button btn-sm">View all</a>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Trainer</th>
                                        <th>Schedule</th>
                                        <th>Enrolled</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="rank-num">1</span></td>
                                        <td>
                                            <div class="name-cell-text">
                                                <span class="name-main">HIIT</span>
                                                <span class="name-sub">Main Floor</span>
                                            </div>
                                        </td>
                                        <td class="table-muted">Bruno Costa</td>
                                        <td class="table-muted">Tue · 18:00</td>
                                        <td><span class="badge badge-accent">189</span></td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="rank-num">2</span></td>
                                        <td>
                                            <div class="name-cell-text">
                                                <span class="name-main">Cycling</span>
                                                <span class="name-sub">Cycling Studio</span>
                                            </div>
                                        </td>
                                        <td class="table-muted">Carla Mendes</td>
                                        <td class="table-muted">Wed · 19:00</td>
                                        <td><span class="badge badge-accent">167</span></td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="rank-num">3</span></td>
                                        <td>
                                            <div class="name-cell-text">
                                                <span class="name-main">Yoga</span>
                                                <span class="name-sub">Studio A</span>
                                            </div>
                                        </td>
                                        <td class="table-muted">Ana Silva</td>
                                        <td class="table-muted">Mon · 07:00</td>
                                        <td><span class="badge badge-accent">154</span></td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="rank-num">4</span></td>
                                        <td>
                                            <div class="name-cell-text">
                                                <span class="name-main">CrossFit</span>
                                                <span class="name-sub">Main Floor</span>
                                            </div>
                                        </td>
                                        <td class="table-muted">Bruno Costa</td>
                                        <td class="table-muted">Fri · 17:30</td>
                                        <td><span class="badge badge-accent">142</span></td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="rank-num">5</span></td>
                                        <td>
                                            <div class="name-cell-text">
                                                <span class="name-main">Pilates</span>
                                                <span class="name-sub">Studio B</span>
                                            </div>
                                        </td>
                                        <td class="table-muted">Ana Silva</td>
                                        <td class="table-muted">Thu · 10:00</td>
                                        <td><span class="badge badge-accent">128</span></td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- All Members Table -->
                    <div class="card">
                        <div class="card-head">
                            <h2 class="card-title">All Members</h2>
                            <div class="card-head-actions">
                                <span class="badge badge-muted">247 total</span>
                                <a href="#" class="main-button btn-sm">+ Add Member</a>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Plan</th>
                                        <th>Classes</th>
                                        <th>Joined</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-purple">S</div>
                                                <div class="name-cell-text">
                                                    <span class="name-main">Sofia Almeida</span>
                                                    <span class="name-sub">sofiaalmeida@gmail.com</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-warning">VIP</span></td>
                                        <td class="table-muted">14</td>
                                        <td class="table-muted table-small">Today</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td class="table-small">
                                            <a href="#" class="action-link">Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-green">R</div>
                                                <div class="name-cell-text">
                                                    <span class="name-main">Rui Tavares</span>
                                                    <span class="name-sub">ruitavares@gmail.com</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-accent">Premium</span></td>
                                        <td class="table-muted">32</td>
                                        <td class="table-muted table-small">Yesterday</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td class="table-small">
                                            <a href="#" class="action-link">Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar">I</div>
                                                <div class="name-cell-text">
                                                    <span class="name-main">Inês Correia</span>
                                                    <span class="name-sub">inescorreia@gmail.com</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-muted">Basic</span></td>
                                        <td class="table-muted">8</td>
                                        <td class="table-muted table-small">18 Apr</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td class="table-small">
                                            <a href="#" class="action-link">Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-orange">M</div>
                                                <div class="name-cell-text">
                                                    <span class="name-main">Marco Ferreira</span>
                                                    <span class="name-sub">marcoferreira@gmail.com</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-accent">Premium</span></td>
                                        <td class="table-muted">47</td>
                                        <td class="table-muted table-small">15 Apr</td>
                                        <td><span class="badge badge-danger">Suspended</span></td>
                                        <td class="table-small">
                                            <a href="#" class="action-link">Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-yellow">L</div>
                                                <div class="name-cell-text">
                                                    <span class="name-main">Lara Pinheiro</span>
                                                    <span class="name-sub">larapinheiro@gmail.com</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-warning">VIP</span></td>
                                        <td class="table-muted">61</td>
                                        <td class="table-muted table-small">3 Feb</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td class="table-small">
                                            <a href="#" class="action-link">Edit</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="dash-col">

                    <!-- Recent Activity Feed -->
                    <div class="card">
                        <div class="card-head">
                            <h2 class="card-title">Recent Activity</h2>
                            <span class="live-badge">
                                <span class="live-dot"></span>
                                Live
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="activity-feed">
                                <div class="activity-item">
                                    <div class="activity-dot green"></div>
                                    <div>
                                        <div class="activity-text">
                                            <strong>Maria Santos</strong> enrolled in <em>HIIT</em>
                                        </div>
                                        <div class="activity-time">3 minutes ago</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-dot blue"></div>
                                    <div>
                                        <div class="activity-text">
                                            <strong>Pedro Rodrigues</strong> rated <em>Cycling </em>
                                            <span class="activity-stars">★★★★★</span>
                                        </div>
                                        <div class="activity-time">11 minutes ago</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-dot orange"></div>
                                    <div>
                                        <div class="activity-text">
                                            <strong>Bruno Costa</strong> confirmed PT session with <em>João Ferreira</em>
                                        </div>
                                        <div class="activity-time">24 minutes ago</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-dot green"></div>
                                    <div>
                                        <div class="activity-text">
                                            New member registered: <strong>Sofia Almeida</strong>
                                        </div>
                                        <div class="activity-time">38 minutes ago</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-dot red"></div>
                                    <div>
                                        <div class="activity-text">
                                            Equipment <strong>Punching Bag #2</strong> flagged for maintenance
                                        </div>
                                        <div class="activity-time">1 hour ago</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-dot green"></div>
                                    <div>
                                        <div class="activity-text">
                                            <strong>Ana Silva</strong> updated her trainer profile
                                        </div>
                                        <div class="activity-time">2 hours ago</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-dot blue"></div>
                                    <div>
                                        <div class="activity-text">
                                            Class <strong>Yoga</strong> is now full (15/15 enrolled)
                                        </div>
                                        <div class="activity-time">3 hours ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Availability Status -->
                    <div class="card">
                        <div class="card-head">
                            <h2 class="card-title">Equipment Availability</h2>
                            <a href="#" class="secondary-button btn-sm">Manage</a>
                        </div>
                        <div class="card-body">
                            <div class="equip-list">

                                <div class="equip-item">
                                    <div class="equip-row">
                                        <span class="equip-name">Treadmills</span>
                                        <span class="badge badge-success">6 / 8 available</span>
                                    </div>
                                    <div class="equip-prog">
                                        <div class="progress-bar">
                                            <div class="progress-fill equip-fill-75"></div>
                                        </div>
                                        <span class="equip-percentage">75%</span>
                                    </div>
                                </div>

                                <div class="equip-item">
                                    <div class="equip-row">
                                        <span class="equip-name">Stationary Bikes</span>
                                        <span class="badge badge-success">5 / 6 available</span>
                                    </div>
                                    <div class="equip-prog">
                                        <div class="progress-bar">
                                            <div class="progress-fill equip-fill-83"></div>
                                        </div>
                                        <span class="equip-percentage">83%</span>
                                    </div>
                                </div>

                                <div class="equip-item">
                                    <div class="equip-row">
                                        <span class="equip-name">Weight Benches</span>
                                        <span class="badge badge-warning">3 / 10 available</span>
                                    </div>
                                    <div class="equip-prog">
                                        <div class="progress-bar">
                                            <div class="progress-fill mid equip-fill-30"></div>
                                        </div>
                                        <span class="equip-percentage">30%</span>
                                    </div>
                                </div>

                                <div class="equip-item">
                                    <div class="equip-row">
                                        <span class="equip-name">Punching Bags</span>
                                        <span class="badge badge-danger">1 / 3 available</span>
                                    </div>
                                    <div class="equip-prog">
                                        <div class="progress-bar">
                                            <div class="progress-fill low equip-fill-33"></div>
                                        </div>
                                        <span class="equip-percentage">33%</span>
                                    </div>
                                </div>

                                <div class="equip-item">
                                    <div class="equip-row">
                                        <span class="equip-name">Yoga Mats</span>
                                        <span class="badge badge-success">25 / 30 available</span>
                                    </div>
                                    <div class="equip-prog">
                                        <div class="progress-bar">
                                            <div class="progress-fill equip-fill-83"></div>
                                        </div>
                                        <span class="equip-percentage">83%</span>
                                    </div>
                                </div>

                                <div class="equip-item">
                                    <div class="equip-row">
                                        <span class="equip-name">Rowing Machines</span>
                                        <span class="badge badge-warning">2 / 4 available</span>
                                    </div>
                                    <div class="equip-prog">
                                        <div class="progress-bar">
                                            <div class="progress-fill mid equip-fill-50"></div>
                                        </div>
                                        <span class="equip-percentage">50%</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Trainers Table -->
                    <div class="card">
                        <div class="card-head">
                            <h2 class="card-title">Trainers</h2>
                            <a href="#" class="main-button btn-sm">+ Add Trainer</a>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Trainer</th>
                                        <th>Specialty</th>
                                        <th>Classes</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-lilac">A</div>
                                                <span class="name-main">Ana Silva</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">Yoga / Pilates</span>
                                        </td>
                                        <td class="table-muted">3 classes</td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-salmon">B</div>
                                                <span class="name-main">Bruno Costa</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">HIIT / CrossFit / Boxing</span>
                                        </td>
                                        <td class="table-muted">2 classes</td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-teal">C</div>
                                                <span class="name-main">Carla Mendes</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">Cycling / Senior Fitness </span>
                                        </td>
                                        <td class="table-muted">2 classes</td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="name-cell">
                                                <div class="avatar avatar-lilac">M</div>
                                                <span class="name-main">Miguel Rocha</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"> Strength </span>
                                        </td>
                                        <td class="table-muted">3 classes</td>
                                        <td>
                                            <div class="stars">
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                                <span class="star on">★</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Site Footer -->
    <?php output_footer(); ?>
