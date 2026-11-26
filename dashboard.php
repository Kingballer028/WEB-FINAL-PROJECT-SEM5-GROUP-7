<?php
require_once 'config.php';

require_login();
$currentUser = current_user($pdo);

if (!$currentUser) {
    redirect('logout.php');
}

$pageTitle = 'Member Dashboard';

// Handle quick module progress completion directly from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_module') {
    $trackId = (int)($_POST['track_id'] ?? 0);
    try {
        // Fetch track total modules
        $stmt = $pdo->prepare("SELECT total_modules FROM learning_tracks WHERE id = ?");
        $stmt->execute([$trackId]);
        $track = $stmt->fetch();

        if ($track) {
            $stmt = $pdo->prepare("SELECT completed_modules FROM member_progress WHERE user_id = ? AND track_id = ?");
            $stmt->execute([$currentUser['id'], $trackId]);
            $prog = $stmt->fetch();

            if ($prog) {
                $newCompleted = min($track['total_modules'], $prog['completed_modules'] + 1);
                $status = ($newCompleted >= $track['total_modules']) ? 'completed' : 'in_progress';
                $update = $pdo->prepare("UPDATE member_progress SET completed_modules = ?, status = ? WHERE user_id = ? AND track_id = ?");
                $update->execute([$newCompleted, $status, $currentUser['id'], $trackId]);
            } else {
                $update = $pdo->prepare("INSERT INTO member_progress (user_id, track_id, completed_modules, status) VALUES (?, ?, 1, 'in_progress')");
                $update->execute([$currentUser['id'], $trackId]);
            }
            set_flash('success', 'Training module marked as completed! Keep going!');
            redirect('dashboard.php');
        }
    } catch (PDOException $e) {
        set_flash('error', 'Error updating progress: ' . $e->getMessage());
    }
}

// 1. Fetch Training Progress
$tracksProgress = [];
try {
    $sql = "SELECT lt.id as track_id, lt.title, lt.description, lt.total_modules,
                   COALESCE(mp.completed_modules, 0) as completed_modules,
                   COALESCE(mp.status, 'in_progress') as progress_status
            FROM learning_tracks lt
            LEFT JOIN member_progress mp ON lt.id = mp.track_id AND mp.user_id = ?
            ORDER BY lt.id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentUser['id']]);
    $tracksProgress = $stmt->fetchAll();
} catch (PDOException $e) {}

// Calculate overall progress percentage
$totalPossibleModules = 0;
$totalCompletedModules = 0;
foreach ($tracksProgress as $tp) {
    $totalPossibleModules += (int)$tp['total_modules'];
    $totalCompletedModules += (int)$tp['completed_modules'];
}
$overallPercent = ($totalPossibleModules > 0) ? round(($totalCompletedModules / $totalPossibleModules) * 100) : 0;

// 2. Fetch User Registered Workshops
$myEvents = [];
try {
    $sql = "SELECT e.*, er.status as reg_status 
            FROM events e
            JOIN event_registrations er ON e.id = er.event_id
            WHERE er.user_id = ?
            ORDER BY e.event_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentUser['id']]);
    $myEvents = $stmt->fetchAll();
} catch (PDOException $e) {}

// 3. Fetch Active Mentorship Request / Mentor
$mentorship = null;
try {
    $sql = "SELECT mr.*, u.full_name as mentor_name, u.email as mentor_email, u.skills as mentor_skills
            FROM mentorship_requests mr
            JOIN users u ON mr.mentor_id = u.id
            WHERE mr.mentee_id = ?
            ORDER BY mr.id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentUser['id']]);
    $mentorship = $stmt->fetch();
} catch (PDOException $e) {}

// 4. Fetch Forum Activity Count
$forumPostCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_threads WHERE user_id = ?");
    $stmt->execute([$currentUser['id']]);
    $forumPostCount = (int)$stmt->fetchColumn();
} catch (PDOException $e) {}

require_once 'includes/header.php';
?>

<main class="container">
    <!-- PERSONALIZED WELCOME BANNER -->
    <section class="card" style="background: linear-gradient(135deg, #ffffff 0%, #f9f6ff 100%); margin-bottom: 26px; border-left: 6px solid var(--purple);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <img src="<?= escape($currentUser['profile_picture'] ?? 'assets/default_avatar.svg') ?>" 
                     alt="Profile Picture" 
                     style="width: 76px; height: 76px; border-radius: 50%; object-fit: cover; border: 3px solid var(--purple);"
                     onerror="this.src='assets/default_avatar.svg'">
                <div>
                    <span class="eyebrow">PERSONALIZED MEMBER HUB</span>
                    <h1 style="font-size: 26px; color: var(--ink); margin-top: 2px;">
                        Welcome back, <?= escape($currentUser['full_name']) ?>!
                    </h1>
                    <p style="color: var(--muted); font-size: 13.5px;">
                        Role: <span class="role-badge role-<?= escape($currentUser['role']) ?>"><?= ucfirst(escape($currentUser['role'])) ?></span> &bull; 
                        Member Since: <strong><?= date('M d, Y', strtotime($currentUser['created_at'])) ?></strong> &bull;
                        Account ID: <strong>#<?= (int)$currentUser['id'] ?></strong>
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="profile.php" class="button button-small button-outline">&#9998; Edit Profile</a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="button button-small">&#9881; Admin Console</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- KEY PERFORMANCE INDICATORS / STATS -->
    <section class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon">&#127891;</div>
            <div class="stat-info">
                <h3><?= $overallPercent ?>%</h3>
                <p>Overall Curriculum Progress</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128197;</div>
            <div class="stat-info">
                <h3><?= count($myEvents) ?></h3>
                <p>Registered Workshops</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128172;</div>
            <div class="stat-info">
                <h3><?= $forumPostCount ?></h3>
                <p>Community Forum Posts</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#129309;</div>
            <div class="stat-info">
                <h3><?= $mentorship ? ucfirst(escape($mentorship['status'])) : 'Not Connected' ?></h3>
                <p>Mentorship Status</p>
            </div>
        </div>
    </section>

    <!-- MAIN TWO-COLUMN DASHBOARD GRID -->
    <div class="dashboard-grid">
        <!-- LEFT COLUMN: TRAINING PROGRESS & REGISTERED EVENTS -->
        <div class="dashboard-column">
            <!-- 1. TRAINING PROGRESS TRACKER (Criterion 5) -->
            <div class="card">
                <div class="card-header">
                    <h2>&#128218; My Training Progress & Learning Tracks</h2>
                    <span style="font-weight: 700; color: var(--purple);"><?= $totalCompletedModules ?> / <?= $totalPossibleModules ?> Modules</span>
                </div>

                <div class="tracks-list">
                    <?php if (!empty($tracksProgress)): ?>
                        <?php foreach ($tracksProgress as $track): ?>
                            <?php 
                                $percent = round(($track['completed_modules'] / max(1, $track['total_modules'])) * 100);
                            ?>
                            <div class="track-item">
                                <div class="track-meta">
                                    <span class="track-title"><?= escape($track['title']) ?></span>
                                    <span class="track-percent"><?= $track['completed_modules'] ?> / <?= $track['total_modules'] ?> (<?= $percent ?>%)</span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill" style="width: <?= $percent ?>%;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                    <small style="color: var(--muted);"><?= escape($track['description']) ?></small>
                                    <?php if ($track['completed_modules'] < $track['total_modules']): ?>
                                        <form method="POST" action="dashboard.php" style="margin: 0;">
                                            <input type="hidden" name="action" value="complete_module">
                                            <input type="hidden" name="track_id" value="<?= (int)$track['track_id'] ?>">
                                            <button type="submit" class="button button-small" style="font-size: 11.5px; padding: 4px 10px;">
                                                + Mark Next Module
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="role-badge role-member" style="font-size: 11px;">&#10003; Track Completed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--muted);">No learning tracks initialized yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. REGISTERED EVENTS / PARTICIPATION TRACKING (Criterion 6) -->
            <div class="card">
                <div class="card-header">
                    <h2>&#128197; My Registered Workshops & Sessions</h2>
                    <a href="events.php" class="button button-small button-outline">Browse All Events &rarr;</a>
                </div>

                <?php if (!empty($myEvents)): ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($myEvents as $me): ?>
                            <div style="padding: 14px; background: var(--lavender-light); border: 1px solid var(--border); border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <strong style="color: var(--ink); font-size: 14.5px;"><?= escape($me['title']) ?></strong>
                                    <div style="font-size: 12.5px; color: var(--muted); margin-top: 4px;">
                                        <span>&#128197; <?= format_date($me['event_date']) ?></span> &bull;
                                        <span>&#128205; <?= escape($me['venue']) ?></span> &bull;
                                        <span>&#127891; Instructor: <?= escape($me['speaker']) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <span class="role-badge role-member">&#10003; <?= ucfirst(escape($me['reg_status'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 25px; color: var(--muted);">
                        <p>You have not registered for any upcoming workshops yet.</p>
                        <a href="events.php" class="button button-small" style="margin-top: 10px;">Find a Workshop to Attend</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT COLUMN: MENTORSHIP & QUICK SHORTCUTS -->
        <div class="dashboard-column">
            <!-- 3. MENTORSHIP CONNECTION WIDGET (Criterion 8) -->
            <div class="card">
                <div class="card-header">
                    <h2>&#129309; Mentorship Hub</h2>
                </div>

                <?php if ($mentorship): ?>
                    <div style="text-align: center; padding: 10px 0;">
                        <span class="role-badge <?= ($mentorship['status'] === 'accepted') ? 'role-member' : 'role-mentor' ?>" style="margin-bottom: 12px; display: inline-block;">
                            Status: <?= ucfirst(escape($mentorship['status'])) ?>
                        </span>
                        <h3 style="font-size: 16px; color: var(--ink);"><?= escape($mentorship['mentor_name']) ?></h3>
                        <p style="font-size: 12.5px; color: var(--muted); margin-top: 4px;">Skills: <?= escape($mentorship['mentor_skills']) ?></p>
                        <div style="margin-top: 14px; padding: 12px; background: var(--lavender-light); border-radius: var(--radius-sm); font-size: 12.5px; text-align: left;">
                            <strong>Your Learning Goal:</strong>
                            <p style="color: var(--text-dark); margin-top: 4px;"><?= escape($mentorship['goal_notes']) ?></p>
                        </div>
                        <a href="mentorship.php" class="button button-small button-outline button-full" style="margin-top: 14px;">
                            View Mentorship Program
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 15px 0;">
                        <p style="font-size: 13.5px; color: var(--muted); margin-bottom: 15px;">
                            Connect with experienced Sierra Leonean software engineers and mentors to accelerate your coding career.
                        </p>
                        <a href="mentorship.php" class="button button-small button-full">
                            Find a Mentor &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 4. QUICK COMMUNITY SHORTCUTS -->
            <div class="card">
                <div class="card-header">
                    <h2>&#128172; Community Activity</h2>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="community.php" class="button button-secondary button-full" style="justify-content: flex-start;">
                        &#128172; Browse Discussion Forum
                    </a>
                    <a href="resources.php" class="button button-secondary button-full" style="justify-content: flex-start;">
                        &#128218; Download Learning Resources
                    </a>
                    <a href="profile.php" class="button button-secondary button-full" style="justify-content: flex-start;">
                        &#128100; Update Bio & Social Links
                    </a>
                    <a href="logout.php" class="button button-danger button-full" style="justify-content: flex-start; margin-top: 10px;">
                        &#128682; Log Out Securely
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
