<?php
require_once 'config.php';

$pageTitle = 'Events & Workshops';
$currentUser = current_user($pdo);
$userId = current_user_id();

// Handle RSVP Registration / Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_login();
    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($_POST['action'] === 'register') {
        try {
            // Check capacity
            $capStmt = $pdo->prepare("SELECT capacity, (SELECT COUNT(*) FROM event_registrations WHERE event_id = ?) as registered_count FROM events WHERE id = ?");
            $capStmt->execute([$eventId, $eventId]);
            $eventInfo = $capStmt->fetch();

            if ($eventInfo && $eventInfo['registered_count'] >= $eventInfo['capacity']) {
                set_flash('error', 'Sorry, this workshop is already at full capacity.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, 'registered') ON DUPLICATE KEY UPDATE status='registered'");
                $stmt->execute([$eventId, $userId]);
                set_flash('success', 'You have successfully registered for this event! Check your dashboard for updates.');
            }
        } catch (PDOException $e) {
            set_flash('error', 'Registration error: ' . $e->getMessage());
        }
    } elseif ($_POST['action'] === 'cancel') {
        try {
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$eventId, $userId]);
            set_flash('info', 'Your workshop registration has been cancelled.');
        } catch (PDOException $e) {
            set_flash('error', 'Cancellation error: ' . $e->getMessage());
        }
    }
    redirect('events.php');
}

// Fetch category filter
$filterCategory = trim($_GET['category'] ?? '');

// Fetch all events
$events = [];
try {
    $sql = "SELECT e.*, 
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as current_attendees,
            " . ($userId ? "(SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND user_id = {$userId}) as is_registered" : "0 as is_registered") . "
            FROM events e";
    
    if ($filterCategory !== '') {
        $sql .= " WHERE e.category = " . $pdo->quote($filterCategory);
    }
    $sql .= " ORDER BY e.event_date ASC";

    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch distinct categories for filter bar
$categories = [];
try {
    $categories = $pdo->query("SELECT DISTINCT category FROM events")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {}

require_once 'includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">COMMUNITY CALENDAR & TRAINING</p>
            <h1>Upcoming Workshops & Events</h1>
            <p>Hands-on coding bootcamps, masterclasses, and networking sessions for our members.</p>
        </div>
        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
            <a href="admin/events.php" class="button button-small">&#43; Add New Workshop</a>
        <?php endif; ?>
    </div>

    <!-- CATEGORY FILTER BAR -->
    <div class="filter-bar">
        <a href="events.php" class="filter-chip <?= ($filterCategory === '') ? 'active' : '' ?>">All Categories</a>
        <?php foreach ($categories as $cat): ?>
            <a href="events.php?category=<?= urlencode($cat) ?>" class="filter-chip <?= ($filterCategory === $cat) ? 'active' : '' ?>">
                <?= escape($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- EVENTS GRID -->
    <div class="events-grid">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $evt): ?>
                <?php
                    $isPast = strtotime($evt['event_date']) < time();
                    $seatsLeft = max(0, (int)$evt['capacity'] - (int)$evt['current_attendees']);
                ?>
                <div class="event-card">
                    <div class="event-card-header">
                        <span class="event-badge"><?= escape($evt['category']) ?></span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= $seatsLeft > 0 ? 'var(--purple-dark)' : 'var(--danger)' ?>;">
                            <?= $seatsLeft > 0 ? "{$seatsLeft} Seats Left" : 'Full Capacity' ?>
                        </span>
                    </div>

                    <div class="event-body">
                        <h3><?= escape($evt['title']) ?></h3>
                        <p style="font-size: 13.5px; color: var(--muted);"><?= escape($evt['description']) ?></p>

                        <div class="event-details">
                            <div class="event-detail-item">
                                <strong>&#128197; Date & Time:</strong>
                                <span><?= format_date($evt['event_date']) ?></span>
                            </div>
                            <div class="event-detail-item">
                                <strong>&#128205; Venue:</strong>
                                <span><?= escape($evt['venue']) ?></span>
                            </div>
                            <div class="event-detail-item">
                                <strong>&#127891; Lead Speaker:</strong>
                                <span><?= escape($evt['speaker']) ?></span>
                            </div>
                            <div class="event-detail-item">
                                <strong>&#128101; Registered:</strong>
                                <span><?= (int)$evt['current_attendees'] ?> / <?= (int)$evt['capacity'] ?> attendees</span>
                            </div>
                        </div>

                        <div class="event-actions">
                            <?php if (!is_logged_in()): ?>
                                <a href="login.php" class="button button-small button-outline button-full">
                                    Log in to Register
                                </a>
                            <?php elseif ($evt['is_registered'] > 0): ?>
                                <div style="display: flex; gap: 8px;">
                                    <span class="button button-small button-success" style="flex: 1; cursor: default;">
                                        &#10003; Registered
                                    </span>
                                    <form method="POST" action="events.php" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel your registration?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="event_id" value="<?= (int)$evt['id'] ?>">
                                        <button type="submit" class="button button-small button-danger" title="Cancel Registration">
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($isPast): ?>
                                <button class="button button-small button-secondary button-full" disabled>
                                    Event Concluded
                                </button>
                            <?php elseif ($seatsLeft <= 0): ?>
                                <button class="button button-small button-secondary button-full" disabled>
                                    Sold Out (Capacity Reached)
                                </button>
                            <?php else: ?>
                                <form method="POST" action="events.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="register">
                                    <input type="hidden" name="event_id" value="<?= (int)$evt['id'] ?>">
                                    <button type="submit" class="button button-small button-full">
                                        Register Now (Free) &rarr;
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <p>No workshops currently match your filter criteria.</p>
                <a href="events.php" class="button button-small" style="margin-top: 10px;">Clear Filter</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
