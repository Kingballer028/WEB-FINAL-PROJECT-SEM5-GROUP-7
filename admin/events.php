<?php
$inAdmin = true;
require_once __DIR__ . '/../config.php';

require_admin($pdo);

$pageTitle = 'Manage Workshops';
$currentUser = current_user($pdo);

// Handle New Event Creation & Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_event') {
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Workshop');
        $eventDate   = trim($_POST['event_date'] ?? '');
        $venue       = trim($_POST['venue'] ?? '');
        $speaker     = trim($_POST['speaker'] ?? '');
        $capacity    = (int)($_POST['capacity'] ?? 40);
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($eventDate) || empty($venue) || empty($speaker)) {
            set_flash('error', 'Please fill in all required workshop fields.');
        } else {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO events (title, category, event_date, venue, speaker, capacity, description, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$title, $category, $eventDate, $venue, $speaker, $capacity, $description, $currentUser['id']]);
                set_flash('success', 'New workshop published successfully!');
            } catch (PDOException $e) {
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
    } elseif ($_POST['action'] === 'delete_event') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            set_flash('info', 'Workshop has been deleted.');
        } catch (PDOException $e) {
            set_flash('error', 'Error: ' . $e->getMessage());
        }
    }
    redirect('events.php');
}

// Check if viewing attendees for a specific event
$viewEventId = (int)($_GET['attendees'] ?? 0);
$attendeesList = [];
$viewEventTitle = '';

if ($viewEventId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
        $stmt->execute([$viewEventId]);
        $viewEventTitle = $stmt->fetchColumn() ?: '';

        $stmt = $pdo->prepare(
            "SELECT er.*, u.full_name, u.email, u.phone
             FROM event_registrations er
             JOIN users u ON er.user_id = u.id
             WHERE er.event_id = ?
             ORDER BY er.registered_at DESC"
        );
        $stmt->execute([$viewEventId]);
        $attendeesList = $stmt->fetchAll();
    } catch (PDOException $e) {}
}

// Fetch all events
$events = [];
try {
    $sql = "SELECT e.*, 
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as rsvp_count
            FROM events e
            ORDER BY e.event_date DESC";
    $events = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">WORKSHOP LOGISTICS & ATTENDANCE</p>
            <h1>Manage Workshops & Events</h1>
            <p>Create training sessions, monitor attendee rosters, and update scheduling.</p>
        </div>
        <a href="index.php" class="button button-small button-secondary">&larr; Back to Admin Overview</a>
    </div>

    <!-- ADMIN SUB-BAR -->
    <div class="filter-bar" style="margin-bottom: 25px;">
        <a href="index.php" class="filter-chip">&#9881; Overview</a>
        <a href="users.php" class="filter-chip">&#128101; Manage Users</a>
        <a href="events.php" class="filter-chip active">&#128197; Manage Workshops (<?= count($events) ?>)</a>
        <a href="resources.php" class="filter-chip">&#128218; Manage Resources</a>
        <a href="reports.php" class="filter-chip">&#128202; Reports & Analytics</a>
    </div>

    <!-- ATTENDEE ROSTER VIEW IF REQUESTED -->
    <?php if ($viewEventId > 0): ?>
        <section class="card" style="margin-bottom: 30px; border-left: 5px solid var(--purple);">
            <div class="card-header">
                <h2>&#128101; Registered Attendees: <?= escape($viewEventTitle) ?> (<?= count($attendeesList) ?>)</h2>
                <a href="events.php" class="button button-small button-secondary">&times; Close Roster</a>
            </div>

            <?php if (!empty($attendeesList)): ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Attendee Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendeesList as $att): ?>
                                <tr>
                                    <td><strong><?= escape($att['full_name']) ?></strong></td>
                                    <td><?= escape($att['email']) ?></td>
                                    <td><?= escape($att['phone'] ?: 'N/A') ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($att['registered_at'])) ?></td>
                                    <td><span class="role-badge role-member"><?= ucfirst(escape($att['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--muted); padding: 15px;">No members have registered for this event yet.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- WORKSHOP LISTING TABLE -->
        <div class="card">
            <div class="card-header">
                <h2>All Scheduled Workshops</h2>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title & Category</th>
                            <th>Date & Time</th>
                            <th>Venue & Speaker</th>
                            <th>RSVPs</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $e): ?>
                                <tr>
                                    <td>
                                        <strong><?= escape($e['title']) ?></strong><br>
                                        <span class="role-badge role-mentor"><?= escape($e['category']) ?></span>
                                    </td>
                                    <td><?= format_date($e['event_date']) ?></td>
                                    <td>
                                        <?= escape($e['venue']) ?><br>
                                        <small style="color: var(--muted);">&#127891; <?= escape($e['speaker']) ?></small>
                                    </td>
                                    <td>
                                        <a href="events.php?attendees=<?= (int)$e['id'] ?>" title="View Attendee List" style="font-weight: 700;">
                                            <?= (int)$e['rsvp_count'] ?> / <?= (int)$e['capacity'] ?>
                                        </a>
                                    </td>
                                    <td style="text-align: right;">
                                        <form method="POST" action="events.php" style="margin: 0; display: inline-block;" onsubmit="return confirm('Delete this event?');">
                                            <input type="hidden" name="action" value="delete_event">
                                            <input type="hidden" name="event_id" value="<?= (int)$e['id'] ?>">
                                            <button type="submit" class="button button-small button-danger" style="font-size: 11px; padding: 4px 8px;">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CREATE NEW WORKSHOP FORM -->
        <div class="card">
            <div class="card-header">
                <h2>&#43; Add New Workshop</h2>
            </div>

            <form method="POST" action="events.php">
                <input type="hidden" name="action" value="create_event">

                <div class="form-group">
                    <label for="title">Workshop Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Intro to Python & Data Analysis" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="Workshop">Workshop</option>
                        <option value="Bootcamp">Bootcamp</option>
                        <option value="Webinar">Webinar</option>
                        <option value="Networking">Networking</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="event_date">Date & Time</label>
                    <input type="datetime-local" id="event_date" name="event_date" required>
                </div>

                <div class="form-group">
                    <label for="venue">Venue / Meeting URL</label>
                    <input type="text" id="venue" name="venue" placeholder="e.g. Limkokwing ICT Lab 1 / Google Meet" required>
                </div>

                <div class="form-group">
                    <label for="speaker">Instructor / Speaker</label>
                    <input type="text" id="speaker" name="speaker" placeholder="e.g. Mariama Sesay" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Maximum Capacity</label>
                    <input type="number" id="capacity" name="capacity" value="40" min="1" max="500" required>
                </div>

                <div class="form-group">
                    <label for="description">Event Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief outline of the workshop objectives..." required></textarea>
                </div>

                <button type="submit" class="button button-full button-large">
                    Publish Workshop &rarr;
                </button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
