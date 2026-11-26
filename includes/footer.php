<?php
$basePath = (isset($inAdmin) && $inAdmin) ? '../' : '';
?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <div class="footer-brand">
                <span class="footer-badge">Django Girls</span>
                <h3>Sierra Leone</h3>
            </div>
            <p class="footer-tagline">
                Empowering women in Sierra Leone to learn programming, build dynamic web applications, and access life-changing tech careers.
            </p>
            <div class="footer-credentials">
                <strong>Limkokwing University of Creative Technology</strong>
                <p>Faculty of Information & Communication Technology</p>
                <p>DCOMP207 Web Programming Final Project (Semester 05, 2026)</p>
            </div>
        </div>

        <div class="footer-col">
            <h4>Quick Navigation</h4>
            <ul>
                <li><a href="<?= $basePath ?>index.php">Home</a></li>
                <li><a href="<?= $basePath ?>events.php">Workshops & Events</a></li>
                <li><a href="<?= $basePath ?>resources.php">Learning Library</a></li>
                <li><a href="<?= $basePath ?>community.php">Discussion Forum</a></li>
                <li><a href="<?= $basePath ?>mentorship.php">Mentorship Program</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Member Access</h4>
            <ul>
                <?php if (is_logged_in()): ?>
                    <li><a href="<?= $basePath ?>dashboard.php">Member Dashboard</a></li>
                    <li><a href="<?= $basePath ?>profile.php">My Profile & Settings</a></li>
                    <li><a href="<?= $basePath ?>logout.php">Log Out Securely</a></li>
                <?php else: ?>
                    <li><a href="<?= $basePath ?>login.php">Member Sign In</a></li>
                    <li><a href="<?= $basePath ?>register.php">Create Free Account</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="footer-col student-col">
            <h4>Developer Credentials</h4>
            <div class="student-card">
                <p class="student-name"><strong>Student:</strong> Alpha Hamidu Jalloh</p>
                <p><strong>Student ID:</strong> 905005193</p>
                <p><strong>Programme:</strong> BIT2101 (Semester 05)</p>
                <p><strong>Examiner:</strong> Oluwatosin Babarinde Ayorinde</p>
                <p><strong>Verified By:</strong> Sheik Umar Kanu</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Django Girls Sierra Leone | Developed by <strong>Alpha Hamidu Jalloh (ID: 905005193)</strong>. All Rights Reserved.</p>
    </div>
</footer>

<script>
// Responsive Navigation Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('open');
        });
    }
});
</script>

</body>
</html>
