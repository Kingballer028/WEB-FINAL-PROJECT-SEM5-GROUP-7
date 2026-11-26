<?php
require_once 'config.php';

$pageTitle = 'Join the Community';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('dashboard.php');
}

$fullName = '';
$email    = '';
$role     = 'member';
$skills   = '';
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName        = trim($_POST['full_name'] ?? '');
    $email           = strtolower(trim($_POST['email'] ?? ''));
    $role            = trim($_POST['role'] ?? 'member');
    $skills          = trim($_POST['skills'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if ($fullName === '' || strlen($fullName) > 100) {
        $errors[] = 'Please enter your full name (up to 100 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!in_array($role, ['member', 'mentor'])) {
        $role = 'member';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            // Check for duplicate email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = 'An account with this email address already exists. Please log in.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $avatar = 'assets/default_avatar.svg';

                $insert = $pdo->prepare(
                    "INSERT INTO users (full_name, email, password_hash, role, skills, profile_picture, status)
                     VALUES (?, ?, ?, ?, ?, ?, 'active')"
                );
                $insert->execute([$fullName, $email, $passwordHash, $role, $skills, $avatar]);

                $newUserId = (int)$pdo->lastInsertId();

                // Auto-enroll user in default learning tracks
                $tracks = $pdo->query("SELECT id FROM learning_tracks")->fetchAll();
                if ($tracks) {
                    $progStmt = $pdo->prepare("INSERT IGNORE INTO member_progress (user_id, track_id, completed_modules, status) VALUES (?, ?, 0, 'in_progress')");
                    foreach ($tracks as $t) {
                        $progStmt->execute([$newUserId, $t['id']]);
                    }
                }

                // Auto-login user immediately upon registration (matching Assignment 1 behavior)
                session_regenerate_id(true);
                $_SESSION['user_id']    = $newUserId;
                $_SESSION['user_name']  = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = $role;

                set_flash('success', "Welcome to Django Girls Sierra Leone, {$fullName}! Your account has been created successfully.");
                redirect('dashboard.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error during registration: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<main class="form-page">
    <div class="form-card">
        <div class="form-heading">
            <p class="eyebrow">NEW MEMBERS & MENTORS</p>
            <h1>Create your account</h1>
            <p>Join the Django Girls Sierra Leone community platform today.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= escape($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label for="full_name">Full Name <span style="color: var(--danger)">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?= escape($fullName) ?>" placeholder="e.g. Mariama Koroma" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span style="color: var(--danger)">*</span></label>
                <input type="email" id="email" name="email" value="<?= escape($email) ?>" placeholder="e.g. name@example.com" required>
            </div>

            <div class="form-group">
                <label for="role">Register As <span style="color: var(--danger)">*</span></label>
                <select id="role" name="role">
                    <option value="member" <?= ($role === 'member') ? 'selected' : '' ?>>Community Member / Learner</option>
                    <option value="mentor" <?= ($role === 'mentor') ? 'selected' : '' ?>>Tech Mentor / Workshop Instructor</option>
                </select>
            </div>

            <div class="form-group">
                <label for="skills">Skills or Interests (Optional)</label>
                <input type="text" id="skills" name="skills" value="<?= escape($skills) ?>" placeholder="e.g. Python, HTML/CSS, Web Design, Beginners">
            </div>

            <div class="form-group">
                <label for="password">Password (Minimum 8 characters) <span style="color: var(--danger)">*</span></label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password <span style="color: var(--danger)">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="button button-full button-large" style="margin-top: 10px;">
                Complete Registration
            </button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 13.5px; color: var(--muted);">
            Already have an account? <a href="login.php" style="font-weight: 700;">Log in here</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
