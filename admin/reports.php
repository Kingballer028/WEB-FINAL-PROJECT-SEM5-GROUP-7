<?php
$inAdmin = true;
require_once __DIR__ . '/../config.php';

require_admin($pdo);

$pageTitle = 'Analytics & Engagement Reports';
$currentUser = current_user($pdo);

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $reportType = $_GET['type'] ?? 'members';

    if ($reportType === 'members') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=djangogirls_members_report_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['User ID', 'Full Name', 'Email', 'Role', 'Status', 'Phone', 'Skills', 'Date Joined']);

        $stmt = $pdo->query("SELECT id, full_name, email, role, status, phone, skills, created_at FROM users ORDER BY id ASC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    } elseif ($reportType === 'events') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=djangogirls_workshop_attendance_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Registration ID', 'Event Title', 'Event Date', 'Venue', 'Member Name', 'Member Email', 'RSVP Status', 'Registered At']);

        $stmt = $pdo->query(
            "SELECT er.id, e.title, e.event_date, e.venue, u.full_name, u.email, er.status, er.registered_at
             FROM event_registrations er
             JOIN events e ON er.event_id = e.id
             JOIN users u ON er.user_id = u.id
             ORDER BY er.id ASC"
        );
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}

// Analytics Queries
// 1. Role distribution
$roleCounts = ['member' => 0, 'mentor' => 0, 'admin' => 0];
try {
    $stmt = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
    while ($row = $stmt->fetch()) {
        $roleCounts[$row['role']] = (int)$row['cnt'];
    }
} catch (PDOException $e) {}

// 2. Events engagement ranking
$eventsEngagement = [];
try {
    $sql = "SELECT e.id, e.title, e.capacity, e.category, e.event_date,
                   COUNT(er.id) as rsvp_count,
                   ROUND((COUNT(er.id) / e.capacity) * 100) as fill_rate
            FROM events e
            LEFT JOIN event_registrations er ON e.id = er.event_id
            GROUP BY e.id
            ORDER BY rsvp_count DESC";
    $eventsEngagement = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {}

// 3. Curriculum Track completions
$tracksAnalytics = [];
try {
    $sql = "SELECT lt.title, 
                   COUNT(mp.id) as total_enrolled,
                   SUM(CASE WHEN mp.status = 'completed' THEN 1 ELSE 0 END) as completed_count
            FROM learning_tracks lt
            LEFT JOIN member_progress mp ON lt.id = mp.track_id
            GROUP BY lt.id";
    $tracksAnalytics = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">MONITORING & EVALUATION</p>
            <h1>Analytics & Engagement Reports</h1>
            <p>Quantitative statistics on student enrollment, workshop participation, and curriculum progress.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="reports.php?export=csv&type=members" class="button button-small">&#128190; Export Members CSV</a>
            <a href="reports.php?export=csv&type=events" class="button button-small button-outline">&#128190; Export Workshop RSVPs CSV</a>
        </div>
    </div>

    <!-- ADMIN SUB-BAR -->
    <div class="filter-bar" style="margin-bottom: 25px;">
        <a href="index.php" class="filter-chip">&#9881; Overview</a>
        <a href="users.php" class="filter-chip">&#128101; Manage Users</a>
        <a href="events.php" class="filter-chip">&#128197; Manage Workshops</a>
        <a href="resources.php" class="filter-chip">&#128218; Manage Resources</a>
        <a href="reports.php" class="filter-chip active">&#128202; Reports & Analytics</a>
    </div>

    <!-- ROLE COMPOSITION SUMMARY -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon">&#128105;&#8205;&#128187;</div>
            <div class="stat-info">
                <h3><?= $roleCounts['member'] ?></h3>
                <p>Community Learners</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127891;</div>
            <div class="stat-info">
                <h3><?= $roleCounts['mentor'] ?></h3>
                <p>Verified Tech Mentors</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#9881;</div>
            <div class="stat-info">
                <h3><?= $roleCounts['admin'] ?></h3>
                <p>System Administrators</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128200;</div>
            <div class="stat-info">
                <h3><?= array_sum($roleCounts) ?></h3>
                <p>Total User Accounts</p>
            </div>
        </div>
    </div>

    <!-- WORKSHOP PARTICIPATION REPORT -->
    <div class="card" style="margin-bottom: 25px;">
        <div class="card-header">
            <h2>&#128197; Workshop Attendance & Fill Rate Performance</h2>
            <a href="reports.php?export=csv&type=events" class="button button-small button-secondary">Download Attendance CSV</a>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Workshop Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Registrations</th>
                        <th>Capacity</th>
                        <th>Capacity Fill Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($eventsEngagement)): ?>
                        <?php foreach ($eventsEngagement as $ee): ?>
                            <tr>
                                <td><strong><?= escape($ee['title']) ?></strong></td>
                                <td><span class="role-badge role-mentor"><?= escape($ee['category']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($ee['event_date'])) ?></td>
                                <td><strong><?= (int)$ee['rsvp_count'] ?></strong></td>
                                <td><?= (int)$ee['capacity'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="progress-bar-bg" style="width: 100px;">
                                            <div class="progress-bar-fill" style="width: <?= min(100, (int)$ee['fill_rate']) ?>%;"></div>
                                        </div>
                                        <span><?= (int)$ee['fill_rate'] ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CURRICULUM TRACK PERFORMANCE -->
    <div class="card">
        <div class="card-header">
            <h2>&#128218; Curriculum Track Progression Report</h2>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Curriculum Track</th>
                        <th>Enrolled Members</th>
                        <th>Completed Track</th>
                        <th>Completion Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tracksAnalytics)): ?>
                        <?php foreach ($tracksAnalytics as $ta): ?>
                            <?php 
                                $ratio = ($ta['total_enrolled'] > 0) ? round(($ta['completed_count'] / $ta['total_enrolled']) * 100) : 0;
                            ?>
                            <tr>
                                <td><strong><?= escape($ta['title']) ?></strong></td>
                                <td><?= (int)$ta['total_enrolled'] ?> members</td>
                                <td><span class="role-badge role-member"><?= (int)$ta['completed_count'] ?> finished</span></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="progress-bar-bg" style="width: 100px;">
                                            <div class="progress-bar-fill" style="width: <?= $ratio ?>%;"></div>
                                        </div>
                                        <span><?= $ratio ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
