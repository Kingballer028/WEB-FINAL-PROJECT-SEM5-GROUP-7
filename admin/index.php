<?php
$inAdmin = true;
require_once __DIR__ . '/../config.php';

require_admin($pdo);

$pageTitle = 'Administrator Console';
$currentUser = current_user($pdo);

// Compute Real Analytics & System Counts
$stats = [
    'total_users'     => 0,
    'total_members'   => 0,
    'total_mentors'   => 0,
    'total_events'    => 0,
    'total_rsvps'     => 0,
    'total_resources' => 0,
    'total_threads'   => 0,
    'total_replies'   => 0
];

try {
    $stats['total_users']     = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['total_members']   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
    $stats['total_mentors']   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('mentor', 'admin')")->fetchColumn();
    $stats['total_events']    = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $stats['total_rsvps']     = (int)$pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();
    $stats['total_resources'] = (int)$pdo->query("SELECT COUNT(*) FROM resources")->fetchColumn();
    $stats['total_threads']   = (int)$pdo->query("SELECT COUNT(*) FROM forum_threads")->fetchColumn();
    $stats['total_replies']   = (int)$pdo->query("SELECT COUNT(*) FROM forum_replies")->fetchColumn();

    // Recent 5 Users
    $recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

    // Recent 5 RSVPs
    $recentRSVPs = $pdo->query(
        "SELECT er.*, u.full_name as user_name, u.email as user_email, e.title as event_title
         FROM event_registrations er
         JOIN users u ON er.user_id = u.id
         JOIN events e ON er.event_id = e.id
         ORDER BY er.registered_at DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) {}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">DCOMP207 ADMINISTRATIVE CONTROL PANEL</p>
            <h1>Organisation & Community Management Console</h1>
            <p>Welcome, Administrator <?= escape($currentUser['full_name']) ?>. Manage platform members, workshops, and analytics.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="reports.php" class="button button-small button-outline">&#128202; Generate Analytics Report</a>
            <a href="../dashboard.php" class="button button-small button-secondary">&larr; Return to Member View</a>
        </div>
    </div>

    <!-- ADMIN NAVIGATION SUB-BAR -->
    <div class="filter-bar" style="margin-bottom: 25px;">
        <a href="index.php" class="filter-chip active">&#9881; Overview</a>
        <a href="users.php" class="filter-chip">&#128101; Manage Users</a>
        <a href="events.php" class="filter-chip">&#128197; Manage Workshops</a>
        <a href="resources.php" class="filter-chip">&#128218; Manage Resources</a>
        <a href="reports.php" class="filter-chip">&#128202; Reports & Analytics</a>
    </div>

    <!-- STATS COUNTERS -->
    <section class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon">&#128101;</div>
            <div class="stat-info">
                <h3><?= $stats['total_users'] ?></h3>
                <p>Total Community Users</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128197;</div>
            <div class="stat-info">
                <h3><?= $stats['total_events'] ?></h3>
                <p>Workshops & Events</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128221;</div>
            <div class="stat-info">
                <h3><?= $stats['total_rsvps'] ?></h3>
                <p>Total Event RSVPs</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128218;</div>
            <div class="stat-info">
                <h3><?= $stats['total_resources'] ?></h3>
                <p>Curated Tutorials</p>
            </div>
        </div>
    </section>

    <!-- RECENT ACTIVITY SECTION -->
    <div class="dashboard-grid">
        <!-- RECENT USERS -->
        <div class="card">
            <div class="card-header">
                <h2>&#128100; Newly Registered Members</h2>
                <a href="users.php" class="button button-small button-outline">View All &rarr;</a>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentUsers)): ?>
                            <?php foreach ($recentUsers as $ru): ?>
                                <tr>
                                    <td>
                                        <strong><?= escape($ru['full_name']) ?></strong><br>
                                        <small style="color: var(--muted);"><?= escape($ru['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?= escape($ru['role']) ?>">
                                            <?= ucfirst(escape($ru['role'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($ru['created_at'])) ?></td>
                                    <td>
                                        <span style="color: <?= $ru['status'] === 'active' ? 'var(--success)' : 'var(--danger)' ?>; font-weight: 700;">
                                            <?= ucfirst(escape($ru['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT EVENT RSVPS -->
        <div class="card">
            <div class="card-header">
                <h2>&#128197; Recent Workshop RSVPs</h2>
                <a href="events.php" class="button button-small button-outline">Manage &rarr;</a>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Workshop</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentRSVPs)): ?>
                            <?php foreach ($recentRSVPs as $rsvp): ?>
                                <tr>
                                    <td>
                                        <strong><?= escape($rsvp['user_name']) ?></strong>
                                    </td>
                                    <td>
                                        <small><?= escape(substr($rsvp['event_title'], 0, 32)) ?>...</small>
                                    </td>
                                    <td>
                                        <small><?= date('M d', strtotime($rsvp['registered_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
