<?php
require_once 'config.php';

$pageTitle = 'Mentorship Connections';
$currentUser = current_user($pdo);
$userId = current_user_id();

// Handle Mentorship Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_login();

    // 1. Mentee sends request to mentor
    if ($_POST['action'] === 'request_mentorship') {
        $mentorId  = (int)($_POST['mentor_id'] ?? 0);
        $goalNotes = trim($_POST['goal_notes'] ?? '');

        if ($mentorId <= 0 || empty($goalNotes)) {
            set_flash('error', 'Please select a mentor and explain your learning goals.');
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO mentorship_requests (mentee_id, mentor_id, goal_notes, status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$userId, $mentorId, $goalNotes]);
                set_flash('success', 'Your mentorship request has been sent to the mentor for review!');
            } catch (PDOException $e) {
                set_flash('error', 'Request error: ' . $e->getMessage());
            }
        }
        redirect('mentorship.php');
    }

    // 2. Mentor updates status (Accept, Reject, Complete)
    if ($_POST['action'] === 'update_status' && ($currentUser['role'] === 'mentor' || $currentUser['role'] === 'admin')) {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $newStatus = trim($_POST['new_status'] ?? '');

        if (in_array($newStatus, ['accepted', 'rejected', 'completed'])) {
            try {
                $stmt = $pdo->prepare("UPDATE mentorship_requests SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $requestId]);
                set_flash('success', "Mentorship status updated to {$newStatus}.");
            } catch (PDOException $e) {
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        redirect('mentorship.php');
    }
}

// Fetch all available mentors
$mentors = [];
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role IN ('mentor', 'admin') AND status = 'active' ORDER BY full_name ASC");
    $mentors = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch my mentorship history
$myRequests = [];
$incomingRequests = [];
if ($userId) {
    try {
        // As Mentee
        $stmt = $pdo->prepare(
            "SELECT mr.*, u.full_name as mentor_name, u.email as mentor_email, u.skills as mentor_skills, u.profile_picture as mentor_avatar
             FROM mentorship_requests mr
             JOIN users u ON mr.mentor_id = u.id
             WHERE mr.mentee_id = ?
             ORDER BY mr.id DESC"
        );
        $stmt->execute([$userId]);
        $myRequests = $stmt->fetchAll();

        // As Mentor (if user is mentor or admin)
        if ($currentUser['role'] === 'mentor' || $currentUser['role'] === 'admin') {
            $stmt = $pdo->prepare(
                "SELECT mr.*, u.full_name as mentee_name, u.email as mentee_email, u.skills as mentee_skills, u.profile_picture as mentee_avatar
                 FROM mentorship_requests mr
                 JOIN users u ON mr.mentee_id = u.id
                 WHERE mr.mentor_id = ?
                 ORDER BY mr.id DESC"
            );
            $stmt->execute([$userId]);
            $incomingRequests = $stmt->fetchAll();
        }
    } catch (PDOException $e) {}
}

require_once 'includes/header.php';
?>

<main class="container">
    <div class="page-header">
        <p class="eyebrow">DEVELOPER MENTORSHIP PROGRAM</p>
        <h1>Facilitate Mentorship Connections</h1>
        <p>Connecting experienced Sierra Leonean software engineers with beginner learners to accelerate their tech journey.</p>
    </div>

    <!-- MENTOR DASHBOARD ACTIONS (IF MENTOR) -->
    <?php if (!empty($incomingRequests)): ?>
        <section class="card" style="margin-bottom: 30px; border-left: 5px solid var(--purple);">
            <div class="card-header">
                <h2>&#128229; Incoming Mentorship Requests (For You)</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($incomingRequests as $req): ?>
                    <div style="padding: 16px; background: var(--lavender-light); border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px;">
                        <div style="display: flex; gap: 14px; align-items: flex-start;">
                            <img src="<?= escape($req['mentee_avatar'] ?? 'assets/default_avatar.svg') ?>" alt="Mentee" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;" onerror="this.src='assets/default_avatar.svg'">
                            <div>
                                <strong style="font-size: 15px; color: var(--ink);"><?= escape($req['mentee_name']) ?></strong> (<?= escape($req['mentee_email']) ?>)
                                <div style="font-size: 12.5px; color: var(--muted); margin-top: 3px;">Skills: <?= escape($req['mentee_skills'] ?: 'Beginner') ?></div>
                                <div style="margin-top: 8px; font-size: 13.5px; background: #ffffff; padding: 10px; border-radius: 4px; border: 1px solid var(--border-light);">
                                    <strong>Goal:</strong> <?= escape($req['goal_notes']) ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                            <span class="role-badge <?= $req['status'] === 'accepted' ? 'role-member' : 'role-mentor' ?>">
                                Status: <?= ucfirst(escape($req['status'])) ?>
                            </span>

                            <?php if ($req['status'] === 'pending'): ?>
                                <div style="display: flex; gap: 8px;">
                                    <form method="POST" action="mentorship.php" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                                        <input type="hidden" name="new_status" value="accepted">
                                        <button type="submit" class="button button-small button-success">&#10003; Accept</button>
                                    </form>
                                    <form method="POST" action="mentorship.php" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                                        <input type="hidden" name="new_status" value="rejected">
                                        <button type="submit" class="button button-small button-danger">&#10007; Decline</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- MY SENT MENTORSHIP REQUESTS -->
    <?php if (!empty($myRequests)): ?>
        <section class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2>&#128100; My Mentorship Applications</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($myRequests as $mr): ?>
                    <div style="padding: 14px; background: var(--white); border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?= escape($mr['mentor_avatar'] ?? 'assets/default_avatar.svg') ?>" alt="Mentor" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover;" onerror="this.src='assets/default_avatar.svg'">
                            <div>
                                <strong style="color: var(--ink);"><?= escape($mr['mentor_name']) ?></strong>
                                <p style="font-size: 12.5px; color: var(--muted);"><?= escape($mr['goal_notes']) ?></p>
                            </div>
                        </div>
                        <div>
                            <span class="role-badge <?= $mr['status'] === 'accepted' ? 'role-member' : 'role-mentor' ?>">
                                <?= ucfirst(escape($mr['status'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- MENTOR DIRECTORY -->
    <h2 style="font-size: 22px; color: var(--ink); margin-bottom: 20px;">Available Industry Mentors</h2>
    <div class="mentor-grid">
        <?php if (!empty($mentors)): ?>
            <?php foreach ($mentors as $m): ?>
                <div class="mentor-card">
                    <img src="<?= escape($m['profile_picture'] ?? 'assets/default_avatar.svg') ?>" alt="Mentor Avatar" class="mentor-avatar" onerror="this.src='assets/default_avatar.svg'">
                    <h3 style="font-size: 18px; color: var(--ink);"><?= escape($m['full_name']) ?></h3>
                    <span class="role-badge role-mentor"><?= ucfirst(escape($m['role'])) ?></span>
                    
                    <p style="font-size: 13px; color: var(--muted); min-height: 40px;">
                        <?= escape($m['bio'] ?: 'Passionate tech educator dedicated to supporting junior engineers in building real-world projects.') ?>
                    </p>

                    <div class="skills-tags">
                        <?php 
                            $skillsList = explode(',', $m['skills'] ?? 'Python, Django, Web Dev');
                            foreach ($skillsList as $sk): 
                        ?>
                            <span class="skill-tag"><?= escape(trim($sk)) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <?php if (is_logged_in()): ?>
                        <?php if ($m['id'] != $userId): ?>
                            <button type="button" class="button button-small button-full" onclick="openRequestModal(<?= (int)$m['id'] ?>, '<?= escape(addslashes($m['full_name'])) ?>')">
                                Connect with <?= escape(explode(' ', $m['full_name'])[0]) ?> &rarr;
                            </button>
                        <?php else: ?>
                            <span class="button button-small button-secondary button-full" style="cursor: default;">This is You</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="button button-small button-outline button-full">Log in to Connect</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- MODAL FOR MENTORSHIP REQUEST -->
<div id="requestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 20px;">
        <div class="card-header">
            <h2>Request Mentorship</h2>
            <button type="button" onclick="closeRequestModal()" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="mentorship.php">
            <input type="hidden" name="action" value="request_mentorship">
            <input type="hidden" id="modal_mentor_id" name="mentor_id" value="">

            <p style="margin-bottom: 14px; font-size: 13.5px; color: var(--muted);">
                Send a personalized mentorship request to <strong id="modal_mentor_name" style="color: var(--ink);"></strong>:
            </p>

            <div class="form-group">
                <label for="goal_notes">What are your learning goals or questions?</label>
                <textarea id="goal_notes" name="goal_notes" rows="4" placeholder="e.g. I am learning Django for my final semester project and would love feedback on database modeling and career tips..." required></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px;">
                <button type="button" class="button button-secondary button-small" onclick="closeRequestModal()">Cancel</button>
                <button type="submit" class="button button-small">Send Request &rarr;</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRequestModal(mentorId, mentorName) {
    document.getElementById('modal_mentor_id').value = mentorId;
    document.getElementById('modal_mentor_name').innerText = mentorName;
    const modal = document.getElementById('requestModal');
    modal.style.display = 'flex';
}

function closeRequestModal() {
    const modal = document.getElementById('requestModal');
    modal.style.display = 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
