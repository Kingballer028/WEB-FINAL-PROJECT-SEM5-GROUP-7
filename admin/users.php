<?php
$inAdmin = true;
require_once __DIR__ . '/../config.php';

require_admin($pdo);

$pageTitle = 'Manage Users';
$currentUser = current_user($pdo);

// Handle User Actions (Role Change, Status Toggle, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $targetUserId = (int)($_POST['user_id'] ?? 0);

    // Prevent admin from deleting or modifying themselves
    if ($targetUserId === $currentUser['id']) {
        set_flash('error', 'You cannot alter or delete your own active administrator account.');
    } else {
        if ($_POST['action'] === 'change_role') {
            $newRole = trim($_POST['new_role'] ?? 'member');
            if (in_array($newRole, ['member', 'mentor', 'admin'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$newRole, $targetUserId]);
                    set_flash('success', 'User role updated successfully.');
                } catch (PDOException $e) {
                    set_flash('error', 'Error: ' . $e->getMessage());
                }
            }
        } elseif ($_POST['action'] === 'toggle_status') {
            $currentStatus = trim($_POST['current_status'] ?? 'active');
            $newStatus = ($currentStatus === 'active') ? 'suspended' : 'active';
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $targetUserId]);
                set_flash('success', "User account status changed to {$newStatus}.");
            } catch (PDOException $e) {
                set_flash('error', 'Error: ' . $e->getMessage());
            }
        } elseif ($_POST['action'] === 'delete_user') {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$targetUserId]);
                set_flash('info', 'User has been permanently removed from the system.');
            } catch (PDOException $e) {
                set_flash('error', 'Error: ' . $e->getMessage());
            }
        }
    }
    redirect('users.php');
}

// Fetch all users
$users = [];
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">ADMINISTRATION & ACCESS CONTROL</p>
            <h1>User Management</h1>
            <p>View, update roles, toggle account suspensions, or remove community accounts.</p>
        </div>
        <a href="index.php" class="button button-small button-secondary">&larr; Back to Admin Overview</a>
    </div>

    <!-- ADMIN SUB-BAR -->
    <div class="filter-bar" style="margin-bottom: 25px;">
        <a href="index.php" class="filter-chip">&#9881; Overview</a>
        <a href="users.php" class="filter-chip active">&#128101; Manage Users (<?= count($users) ?>)</a>
        <a href="events.php" class="filter-chip">&#128197; Manage Workshops</a>
        <a href="resources.php" class="filter-chip">&#128218; Manage Resources</a>
        <a href="reports.php" class="filter-chip">&#128202; Reports & Analytics</a>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User / Details</th>
                    <th>Email & Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="../<?= escape($u['profile_picture'] ?? 'assets/default_avatar.svg') ?>" alt="Avatar" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;" onerror="this.src='../assets/default_avatar.svg'">
                                    <div>
                                        <strong><?= escape($u['full_name']) ?></strong>
                                        <small style="display: block; color: var(--muted);">ID: #<?= (int)$u['id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= escape($u['email']) ?><br>
                                <small style="color: var(--muted);"><?= escape($u['phone'] ?: 'No phone') ?></small>
                            </td>
                            <td>
                                <form method="POST" action="users.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 12px; border-radius: 4px; <?= $u['id'] === $currentUser['id'] ? 'pointer-events: none;' : '' ?>">
                                        <option value="member" <?= $u['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                                        <option value="mentor" <?= $u['role'] === 'mentor' ? 'selected' : '' ?>>Mentor</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: <?= $u['status'] === 'active' ? 'var(--success)' : 'var(--danger)' ?>;">
                                    <?= ucfirst(escape($u['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($u['id'] !== $currentUser['id']): ?>
                                    <div style="display: inline-flex; gap: 6px;">
                                        <!-- Toggle Status Form -->
                                        <form method="POST" action="users.php" style="margin: 0;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= escape($u['status']) ?>">
                                            <button type="submit" class="button button-small <?= $u['status'] === 'active' ? 'button-secondary' : 'button-success' ?>" style="font-size: 11px; padding: 4px 8px;">
                                                <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                            </button>
                                        </form>

                                        <!-- Delete User Form -->
                                        <form method="POST" action="users.php" style="margin: 0;" onsubmit="return confirm('Permanently remove this user?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit" class="button button-small button-danger" style="font-size: 11px; padding: 4px 8px;">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <small style="color: var(--muted); font-style: italic;">Current Session</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
