<?php
require_once 'config.php';

$pageTitle = 'Community Forum';
$currentUser = current_user($pdo);

// Handle New Discussion Creation
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_thread') {
    require_login();
    $title      = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $content    = trim($_POST['content'] ?? '');

    if ($title === '' || strlen($title) > 255) {
        $errors[] = 'Please provide a descriptive discussion title (up to 255 characters).';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Please select a discussion category.';
    }
    if ($content === '') {
        $errors[] = 'Discussion message body cannot be empty.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO forum_threads (category_id, user_id, title, content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$categoryId, $currentUser['id'], $title, $content]);
            $threadId = $pdo->lastInsertId();

            set_flash('success', 'Your discussion topic has been published successfully!');
            redirect("thread.php?id={$threadId}");
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch categories
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM forum_categories ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {}

// Filter by category
$filterCat = (int)($_GET['cat'] ?? 0);

// Fetch threads
$threads = [];
try {
    $sql = "SELECT t.*, c.name as category_name, c.slug, u.full_name as author_name, u.role as author_role, u.profile_picture as author_avatar,
                   (SELECT COUNT(*) FROM forum_replies WHERE thread_id = t.id) as reply_count
            FROM forum_threads t
            JOIN forum_categories c ON t.category_id = c.id
            JOIN users u ON t.user_id = u.id";
    if ($filterCat > 0) {
        $sql .= " WHERE t.category_id = {$filterCat}";
    }
    $sql .= " ORDER BY t.created_at DESC";

    $threads = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {}

require_once 'includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">COMMUNITY INTERACTION & COLLABORATION</p>
            <h1>Developer Discussion Forum</h1>
            <p>Exchange knowledge, troubleshoot bugs, and network with Sierra Leone developers.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="mentorship.php" class="button button-small button-outline">&#129309; Mentorship Hub</a>
            <?php if (is_logged_in()): ?>
                <a href="#new-thread-form" class="button button-small">&#43; Start Discussion</a>
            <?php else: ?>
                <a href="login.php" class="button button-small">Log in to Post</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- CATEGORY PILLS -->
    <div class="filter-bar">
        <a href="community.php" class="filter-chip <?= ($filterCat === 0) ? 'active' : '' ?>">All Channels</a>
        <?php foreach ($categories as $cat): ?>
            <a href="community.php?cat=<?= (int)$cat['id'] ?>" class="filter-chip <?= ($filterCat === (int)$cat['id']) ? 'active' : '' ?>">
                <?= escape($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- THREADS LIST -->
    <div class="forum-thread-list">
        <?php if (!empty($threads)): ?>
            <?php foreach ($threads as $t): ?>
                <div class="thread-item">
                    <img src="<?= escape($t['author_avatar'] ?? 'assets/default_avatar.svg') ?>" 
                         alt="Author Avatar" 
                         style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--purple-light);"
                         onerror="this.src='assets/default_avatar.svg'">

                    <div class="thread-main">
                        <span class="role-badge role-mentor" style="font-size: 11px; margin-bottom: 4px; display: inline-block;">
                            <?= escape($t['category_name']) ?>
                        </span>
                        <h3>
                            <a href="thread.php?id=<?= (int)$t['id'] ?>"><?= escape($t['title']) ?></a>
                        </h3>
                        <div class="thread-meta">
                            <span>Started by <strong><?= escape($t['author_name']) ?></strong> (<?= ucfirst(escape($t['author_role'])) ?>)</span>
                            <span>&bull;</span>
                            <span><?= date('M d, Y h:i A', strtotime($t['created_at'])) ?></span>
                        </div>
                    </div>

                    <div class="thread-stats">
                        <div class="stat-box">
                            <strong><?= (int)$t['reply_count'] ?></strong>
                            <span>Replies</span>
                        </div>
                        <div class="stat-box">
                            <strong><?= (int)$t['views'] ?></strong>
                            <span>Views</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <p>No discussion threads have been opened in this channel yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- CREATE DISCUSSION THREAD SECTION -->
    <div id="new-thread-form" class="card" style="margin-top: 40px;">
        <div class="card-header">
            <h2>&#9998; Start a New Discussion Topic</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= escape($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (is_logged_in()): ?>
            <form method="POST" action="community.php">
                <input type="hidden" name="action" value="create_thread">

                <div class="form-group">
                    <label for="category_id">Select Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Choose Channel --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= escape($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Topic Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Best practices for structuring Django apps" required>
                </div>

                <div class="form-group">
                    <label for="content">Discussion Message</label>
                    <textarea id="content" name="content" rows="5" placeholder="Share your question, thoughts, or project details..." required></textarea>
                </div>

                <button type="submit" class="button button-large">
                    Publish Topic &rarr;
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 20px;">
                <p style="margin-bottom: 12px; color: var(--muted);">You must be logged in to start a new discussion.</p>
                <a href="login.php" class="button button-small">Log in to Participate</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
