<?php
/**
 * footer.php – Reusable footer component.
 * Renders the site footer with logo, platform links, contact info and copyright.
 * The $basePath parameter allows dynamic prefixing of internal links (e.g. '../public/').
 */

function output_footer($basePath = '') {
    // Prevent XSS in the base path parameter
    $safeBasePath = htmlspecialchars($basePath);
?>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Logo and tagline -->
                <div class="footer-box">
                    <span class="logo">FIT<span>PEAK</span></span>
                    <p>Your gym, your evolution. Technology, performance and community in one place.</p>
                </div>

                <!-- Platform navigation links -->
                <div class="footer-box">
                    <h3>Platform</h3>
                    <ul>
                        <li><a href="<?= $safeBasePath ?>classes.php">Classes</a></li>
                        <li><a href="<?= $safeBasePath ?>trainers.php">Trainers</a></li>
                        <li><a href="<?= $safeBasePath ?>equipment.php">Equipment</a></li>
                    </ul>
                </div>

                <!-- Contact information -->
                <div class="footer-box">
                    <h3>Contact</h3>
                    <p>Avenida da Boavista, 1203<br>4100-130 Porto, Portugal</p>
                    <p>fitpeakgym2026@gmail.com</p>
                    <p>+351 220 145 980</p>
                </div>
            </div>

            <!-- Copyright line -->
            <div class="footer-bottom">
                <p>&copy; 2026 FitPeak Gym. All rights reserved.</p>
            </div>
        </div>
    </footer>

    </body>
    </html>
<?php } ?>