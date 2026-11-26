<?php
require_once 'config.php';

// If already logged in, automatically bring user into the dashboard (direct continuation of Assignment 1)
if (is_logged_in() && !isset($_GET['public'])) {
    redirect('dashboard.php');
}

$pageTitle = 'Home';

// Fetch 3 upcoming workshops
$upcomingEvents = [];
$featuredResources = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC LIMIT 3");
        $upcomingEvents = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC LIMIT 3");
        $featuredResources = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback to empty if DB not yet loaded
    }
}

require_once 'includes/header.php';
?>

<main>
    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <p class="eyebrow">DCOMP207 WEB PROGRAMMING FINAL PROJECT</p>
            <h1>Learn, Connect and Build with Technology.</h1>
            <div class="accent-line"></div>
            <p class="hero-description">
                Welcome to <strong>Django Girls Sierra Leone</strong> — a community dedicated to bringing more women into the digital economy through hands-on Python and Django programming workshops, mentorship, and open-source collaboration.
            </p>

            <div class="hero-actions">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php" class="button button-large">
                        Go to My Dashboard &rarr;
                    </a>
                    <a href="events.php" class="button button-large button-outline">
                        View Workshops
                    </a>
                <?php else: ?>
                    <a href="register.php" class="button button-large">
                        Join Community (Sign Up)
                    </a>
                    <a href="login.php" class="button button-large button-outline">
                        Member Sign In
                    </a>
                <?php endif; ?>
            </div>

            <div class="feature-row">
                <div class="feature">
                    <span class="feature-icon">01</span>
                    <span>Free Coding Bootcamps</span>
                </div>
                <div class="feature">
                    <span class="feature-icon">02</span>
                    <span>1-on-1 Tech Mentorship</span>
                </div>
                <div class="feature">
                    <span class="feature-icon">03</span>
                    <span>Progress Tracking</span>
                </div>
            </div>
        </div>

        <div class="hero-art">
            <div class="logo-frame">
                <img src="assets/jgirls.png" alt="Django Girls Sierra Leone" class="hero-logo" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/a/a6/Djangogirls-logo.png'">
            </div>
            <p class="art-caption">"Empowering Women in Tech across Sierra Leone"</p>
        </div>
    </section>

    <!-- UPCOMING WORKSHOPS SNEAK PEEK -->
    <section class="container">
        <div class="page-header" style="text-align: center; margin-bottom: 35px;">
            <p class="eyebrow">COMMUNITY CALENDAR</p>
            <h2>Upcoming Workshops & Training Sessions</h2>
            <p>Register for hands-on sessions led by industry experts and experienced mentors.</p>
        </div>

        <div class="events-grid">
            <?php if (!empty($upcomingEvents)): ?>
                <?php foreach ($upcomingEvents as $evt): ?>
                    <div class="event-card">
                        <div class="event-card-header">
                            <span class="event-badge"><?= escape($evt['category']) ?></span>
                            <small>Capacity: <?= (int)$evt['capacity'] ?></small>
                        </div>
                        <div class="event-body">
                            <h3><?= escape($evt['title']) ?></h3>
                            <p style="font-size: 13.5px; color: var(--muted);"><?= escape(substr($evt['description'], 0, 110)) ?>...</p>
                            <div class="event-details">
                                <div class="event-detail-item">
                                    <strong>Date:</strong> <?= format_date($evt['event_date']) ?>
                                </div>
                                <div class="event-detail-item">
                                    <strong>Venue:</strong> <?= escape($evt['venue']) ?>
                                </div>
                                <div class="event-detail-item">
                                    <strong>Speaker:</strong> <?= escape($evt['speaker']) ?>
                                </div>
                            </div>
                            <div class="event-actions">
                                <a href="events.php" class="button button-small button-full">View & Register &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p>No scheduled events found yet. Connect your database or check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- LEARNING RESOURCES SPOTLIGHT -->
    <section class="container" style="background: #ffffff; border-radius: var(--radius-lg); margin-top: 40px; margin-bottom: 40px; border: 1px solid var(--border);">
        <div class="page-header" style="text-align: center; margin-bottom: 35px;">
            <p class="eyebrow">CURATED MATERIALS</p>
            <h2>Learning Library & Tutorials</h2>
            <p>Access high-quality guides, code cheat sheets, and video tutorials designed for every level.</p>
        </div>

        <div class="resources-grid">
            <?php if (!empty($featuredResources)): ?>
                <?php foreach ($featuredResources as $res): ?>
                    <div class="resource-card">
                        <span class="resource-type-pill"><?= escape($res['resource_type']) ?></span>
                        <h3><?= escape($res['title']) ?></h3>
                        <p><?= escape($res['description']) ?></p>
                        <a href="<?= escape($res['external_link']) ?>" target="_blank" rel="noopener noreferrer" class="button button-small button-outline">
                            Access Resource &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="resources.php" class="button button-secondary">Explore All Learning Materials &rarr;</a>
        </div>
    </section>

    <!-- MENTORSHIP & COMMUNITY BANNER -->
    <section class="container" style="margin-bottom: 50px;">
        <div class="card" style="background: linear-gradient(135deg, #7138dc 0%, #5423b8 100%); color: #ffffff; padding: 40px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px;">
            <span style="background: rgba(255,255,255,0.2); padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">1-on-1 Tech Mentorship</span>
            <h2 style="color: #ffffff; font-size: 30px;">Need Guidance on Your Coding Journey?</h2>
            <p style="max-width: 650px; font-size: 15px; color: #f2ebff;">
                Our community pairs beginners with experienced developers who provide project reviews, career advice, and continuous encouragement.
            </p>
            <div style="display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap; justify-content: center;">
                <a href="mentorship.php" class="button" style="background: #ffffff; color: var(--purple); border-color: #ffffff;">Find a Mentor</a>
                <a href="community.php" class="button button-outline" style="color: #ffffff; border-color: #ffffff;">Join Forum Discussion</a>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
