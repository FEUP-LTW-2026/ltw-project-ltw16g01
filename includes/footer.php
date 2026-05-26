<?php
function output_footer($basePath = '') {
    $safeBasePath = htmlspecialchars($basePath, ENT_QUOTES);
?>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-box">
                    <span class="logo">FIT<span>PEAK</span></span>
                    <p>Your gym, your evolution. Technology, performance and community in one place.</p>
                </div>

                <div class="footer-box">
                    <h3>Platform</h3>
                    <ul>
                        <li><a href="<?= $safeBasePath ?>classes.php">Classes</a></li>
                        <li><a href="<?= $safeBasePath ?>trainers.php">Trainers</a></li>
                        <li><a href="<?= $safeBasePath ?>equipment.php">Equipment</a></li>
                    </ul>
                </div>

                <div class="footer-box">
                    <h3>Contact</h3>
                    <p>Avenida da Boavista, 1203<br>4100-130 Porto, Portugal</p>
                    <p>info@fitpeak.com</p>
                    <p>+351 220 145 980</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 FitPeak Gym. All rights reserved.</p>
            </div>
        </div>
    </footer>

    </body>
    </html>

<?php } ?>