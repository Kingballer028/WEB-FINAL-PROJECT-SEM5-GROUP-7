<?php
require_once 'config.php';

$threadId = (int)($_GET['id'] ?? 0);
if ($threadId <= 0) {
    redirect('community.php');
}

$currentUser = current_user($pdo);

// Handle new reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_reply') {
    require_login();
    $content = trim($_POST['content'] ?? '');

    if ($content !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO forum_replies (thread_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$threadId, $currentUser['id'], $content]);
            set_flash('success', 'Your reply has been posted!');
            redirect("thread.php?id={$threadId}#replies");
        } catch (PDOException $e) {
            set_flash('error', 'Error posting reply: ' . $e->getMessage());
        }
    } else {
        set_flash('error', 'Reply cannot be blank.');
    }
}

// Fetch thread details
$thread = null;
try {
    // Increment view count
    $pdo->query("UPDATE forum_threads SET views = views + 1 WHERE id = {$threadId}");

    $sql = "SELECT t.*, c.name as category_name, u.full_name as author_name, u.role as author_role, u.profile_picture as author_avatar
            FROM forum_threads t
            JOIN forum_categories c ON t.category_id = c.id
            JOIN users u ON t.user_id = u.id
            WHERE t.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$threadId]);
    $thread = $stmt->fetch();
} catch (PDOException $e) {}

if (!$thread) {
    redirect('community.php');
}

$pageTitle = $thread['title'];

// Fetch replies
$replies = [];
try {
    $sql = "SELECT r.*, u.full_name as author_name, u.role as author_role, u.profile_picture as author_avatar
            FROM forum_replies r
            JOIN users u ON r.user_id = u.id
            WHERE r.thread_id = ?
            ORDER BY r.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$threadId]);
    $replies = $stmt->fetchAll();
} catch (PDOException $e) {}

require_once 'includes/header.php';
?>

<main class="container">
    <div style="margin-bottom: 20px;">
        <a href="community.php" class="button button-small button-secondary">&larr; Back to All Discussions</a>
    </div>

    <!-- MAIN THREAD CARD -->
    <article class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; margin-bottom: 16px;">
            <div>
                <span class="role-badge role-mentor" style="margin-bottom: 8px; display: inline-block;">
                    <?= escape($thread['category_name']) ?>
                </span>
                <h1 style="font-size: 24px; color: var(--ink); line-height: 1.3;">
                    <?= escape($thread['title']) ?>
                </h1>
            </div>
            <div style="font-size: 13px; color: var(--muted); text-align: right;">
                &#128065; <?= (int)$thread['views'] ?> views
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border-light);">
            <img src="<?= escape($thread['author_avatar'] ?? 'assets/default_avatar.svg') ?>" 
                 alt="Author" 
                 style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--purple);"
                 onerror="this.src='assets/default_avatar.svg'">
            <div>
                <strong><?= escape($thread['author_name']) ?></strong>
                <span class="role-badge role-<?= escape($thread['author_role']) ?>"><?= ucfirst(escape($thread['author_role'])) ?></span>
                <div style="font-size: 12px; color: var(--muted);">Posted on <?= date('F d, Y \a\t h:i A', strtotime($thread['created_at'])) ?></div>
            </div>
        </div>

        <div style="font-size: 15px; line-height: 1.7; color: var(--text-dark); white-space: pre-wrap;">
            <?= nl2br(escape($thread['content'])) ?>
        </div>
    </article>

    <!-- REPLIES LIST -->
    <section id="replies" style="margin-bottom: 30px;">
        <h2 style="font-size: 20px; color: var(--ink); margin-bottom: 16px;">
            Discussion Replies (<?= count($replies) ?>)
        </h2>

        <?php if (!empty($replies)): ?>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($replies as $r): ?>
                    <div class="card" style="padding: 18px 24px; background: #ffffff;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?= escape($r['author_avatar'] ?? 'assets/default_avatar.svg') ?>" 
                                     alt="Avatar" 
                                     style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover;"
                                     onerror="this.src='assets/default_avatar.svg'">
                                <div>
                                    <strong style="font-size: 13.5px;"><?= escape($r['author_name']) ?></strong>
                                    <span class="role-badge role-<?= escape($r['author_role']) ?>" style="font-size: 10.5px;"><?= ucfirst(escape($r['author_role'])) ?></span>
                                </div>
                            </div>
                            <small style="color: var(--muted);"><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></small>
                        </div>
                        <div style="font-size: 14px; line-height: 1.6; color: var(--text-dark); white-space: pre-wrap; padding-left: 44px;">
                            <?= nl2br(escape($r['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 30px; color: var(--muted);">
                <p>No replies posted yet. Be the first to share your insights!</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- POST REPLY FORM -->
    <div class="card">
        <div class="card-header">
            <h2>&#128172; Post Your Reply</h2>
        </div>

        <?php if (is_logged_in()): ?>
            <form method="POST" action="thread.php?id=<?= $threadId ?>">
                <input type="hidden" name="action" value="post_reply">
                <div class="form-group">
                    <label for="reply_content">Your Response / Answer</label>
                    <textarea id="reply_content" name="content" rows="4" placeholder="Write a supportive, helpful response..." required></textarea>
                </div>
                <button type="submit" class="button button-large">
                    Submit Reply
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 20px;">
                <p style="margin-bottom: 12px; color: var(--muted);">You must be logged in to reply to this discussion.</p>
                <a href="login.php" class="button button-small">Log in to Reply</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
