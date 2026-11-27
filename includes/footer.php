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
                <p class="student-name"><strong>Group:</strong> 7</p>
                <p><strong>Programme:</strong> BIT2101 (Semester 05)</p>
                <p><strong>Examiner:</strong> Oluwatosin Babarinde Ayorinde</p>
                <p><strong>Verified By:</strong> Sheik Umar Kanu</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Django Girls Sierra Leone | Developed by <strong>Group 7</strong>. All Rights Reserved.</p>
    </div>
</footer>

<script>
// =========================================================
// DJANGO GIRLS SIERRA LEONE - INTERACTIVE UI & ANIMATIONS
// Optimized for all devices: Phones, Tablets, Laptops & Desktops
// =========================================================
document.addEventListener('DOMContentLoaded', function() {
    // 1. RESPONSIVE NAVIGATION MENU TOGGLE & CLICK-OUTSIDE
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('open');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (navMenu.classList.contains('active') && !navMenu.contains(e.target) && !navToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('open');
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('open');
            }
        });
    }

    // 2. LIVE WALLPAPER PARTICLE CANVAS (Interactive Ambient Mesh)
    const canvas = document.getElementById('liveWallpaperCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width = 0;
        let height = 0;
        let particles = [];
        let animationFrameId = null;
        let isTabActive = true;
        
        // Touch & Mouse interactivity
        const mouse = { x: -1000, y: -1000, radius: 120 };

        function updateDimensions() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            initParticles();
        }

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.size = Math.random() * 2.5 + 1.2;
                this.baseX = this.x;
                this.baseY = this.y;
                // Gentle floating velocity
                this.vx = (Math.random() - 0.5) * 0.45;
                this.vy = (Math.random() - 0.5) * 0.45;
                // Palette: brand purple, soft lavender, coral accent
                const colors = [
                    'rgba(113, 56, 220, 0.45)',
                    'rgba(157, 107, 245, 0.40)',
                    'rgba(230, 74, 107, 0.28)',
                    'rgba(90, 40, 190, 0.35)'
                ];
                this.color = colors[Math.floor(Math.random() * colors.length)];
                this.alphaPulse = Math.random() * Math.PI * 2;
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.fill();
            }

            update() {
                this.alphaPulse += 0.02;
                this.x += this.vx;
                this.y += this.vy;

                // Screen wrapping
                if (this.x < -20) this.x = width + 20;
                if (this.x > width + 20) this.x = -20;
                if (this.y < -20) this.y = height + 20;
                if (this.y > height + 20) this.y = -20;

                // Mouse / Touch push interaction
                const dx = mouse.x - this.x;
                const dy = mouse.y - this.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < mouse.radius) {
                    const force = (mouse.radius - dist) / mouse.radius;
                    const angle = Math.atan2(dy, dx);
                    this.x -= Math.cos(angle) * force * 2.5;
                    this.y -= Math.sin(angle) * force * 2.5;
                }
            }
        }

        function initParticles() {
            // Adaptive density based on device width to ensure 60fps on mobile
            let count = 60;
            if (window.innerWidth < 600) {
                count = 24; // Mobile friendly
            } else if (window.innerWidth < 1024) {
                count = 40; // Tablet friendly
            }
            particles = [];
            for (let i = 0; i < count; i++) {
                particles.push(new Particle());
            }
        }

        function connectParticles() {
            const maxDist = window.innerWidth < 600 ? 80 : 120;
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < maxDist) {
                        const alpha = (1 - dist / maxDist) * 0.18;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(113, 56, 220, ${alpha})`;
                        ctx.lineWidth = 0.8;
                        ctx.stroke();
                    }
                }
            }
        }

        function animate() {
            if (!isTabActive) return;
            ctx.clearRect(0, 0, width, height);

            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
            }
            connectParticles();
            animationFrameId = requestAnimationFrame(animate);
        }

        // Pointer event listeners
        window.addEventListener('mousemove', function(e) {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        });

        window.addEventListener('mouseout', function() {
            mouse.x = -1000;
            mouse.y = -1000;
        });

        window.addEventListener('touchmove', function(e) {
            if (e.touches.length > 0) {
                mouse.x = e.touches[0].clientX;
                mouse.y = e.touches[0].clientY;
            }
        }, { passive: true });

        window.addEventListener('touchend', function() {
            mouse.x = -1000;
            mouse.y = -1000;
        });

        // Resize with debounce
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateDimensions, 150);
        });

        // Battery / performance saving when switching tabs
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                isTabActive = false;
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
            } else {
                isTabActive = true;
                animate();
            }
        });

        updateDimensions();
        animate();
    }

    // 3. SCROLL REVEAL ENTRANCE ANIMATIONS (Staggered observer)
    const animElements = document.querySelectorAll(
        '.card, .stat-card, .event-card, .resource-card, .mentor-card, ' +
        '.hero-content, .hero-art, .dashboard-section, .profile-card, ' +
        '.feature-box, .forum-thread-card, .admin-card, .table-container'
    );

    if ('IntersectionObserver' in window && animElements.length > 0) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: 0.1
        });

        animElements.forEach((el, index) => {
            el.classList.add('reveal-ready');
            // Stagger small delays for grid siblings
            const delay = (index % 4) * 0.08;
            el.style.animationDelay = `${delay}s`;
            observer.observe(el);
        });
    }

    // 4. DYNAMIC PROGRESS BARS SMOOTH EXPANSION
    const progressBars = document.querySelectorAll('.progress-bar-fill');
    progressBars.forEach(bar => {
        const targetWidth = bar.style.width || bar.getAttribute('data-width') || '0%';
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.transition = 'width 1.2s cubic-bezier(0.22, 1, 0.36, 1)';
            bar.style.width = targetWidth;
        }, 200);
    });
});
</script>

</body>
</html>
