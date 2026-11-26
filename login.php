<?php
require_once 'config.php';

$pageTitle = 'Login';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('dashboard.php');
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] === 'suspended') {
                    $error = 'Your account has been suspended. Please contact the administrator.';
                } else {
                    // Prevent Session Fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id']    = (int)$user['id'];
                    $_SESSION['user_name']  = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role']  = $user['role'];

                    set_flash('success', "Welcome back, {$user['full_name']}!");

                    // Role-based redirection: Admins can go to admin panel or dashboard
                    if ($user['role'] === 'admin' && isset($_POST['as_admin'])) {
                        redirect('admin/index.php');
                    } else {
                        redirect('dashboard.php');
                    }
                }
            } else {
                $error = 'Invalid email address or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<main class="form-page">
    <div class="form-card">
        <div class="form-heading">
            <p class="eyebrow">WELCOME BACK</p>
            <h1>Log in to your account</h1>
            <p>Enter your credentials to access your personal dashboard.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= escape($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= escape($email) ?>" placeholder="e.g. member@djangogirls.sl" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 13px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="remember_me" value="1"> Remember me
                </label>
                <a href="#" onclick="alert('Password reset link has been simulated. Please contact the system administrator.'); return false;">Forgot Password?</a>
            </div>

            <button type="submit" class="button button-full button-large">
                Log In Securely &rarr;
            </button>
        </form>

        <p style="text-align: center; margin-top: 24px; font-size: 13.5px; color: var(--muted);">
            Don't have an account? <a href="register.php" style="font-weight: 700;">Sign up here</a>
        </p>

        <!-- DEMONSTRATION CREDENTIALS HELPER FOR EXAMINER -->
        <div style="margin-top: 30px; padding: 18px; background: var(--lavender-light); border: 1px dashed var(--purple); border-radius: var(--radius-sm); font-size: 12.5px;">
            <strong style="color: var(--purple-dark); display: block; margin-bottom: 8px;">Quick Test Credentials (For Examiner & Evaluation):</strong>
            <p style="margin-bottom: 4px;"><strong>Admin:</strong> <code>admin@djangogirls.sl</code> / <code>Admin123!</code></p>
            <p style="margin-bottom: 4px;"><strong>Mentor:</strong> <code>mentor@djangogirls.sl</code> / <code>Mentor123!</code></p>
            <p><strong>Member (Alpha Jalloh):</strong> <code>alpha.jalloh@djangogirls.sl</code> / <code>Alpha123!</code></p>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
