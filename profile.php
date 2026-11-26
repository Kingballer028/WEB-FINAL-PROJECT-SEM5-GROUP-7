<?php
require_once 'config.php';

require_login();
$currentUser = current_user($pdo);

if (!$currentUser) {
    redirect('logout.php');
}

$pageTitle = 'Member Profile';
$errors = [];
$successMsg = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Check if Profile Information form submitted
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $fullName    = trim($_POST['full_name'] ?? '');
        $bio         = trim($_POST['bio'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $skills      = trim($_POST['skills'] ?? '');
        $githubUrl   = trim($_POST['github_url'] ?? '');
        $linkedinUrl = trim($_POST['linkedin_url'] ?? '');

        if ($fullName === '' || strlen($fullName) > 100) {
            $errors[] = 'Full name is required (maximum 100 characters).';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE users 
                     SET full_name = ?, bio = ?, phone = ?, skills = ?, github_url = ?, linkedin_url = ?
                     WHERE id = ?"
                );
                $stmt->execute([$fullName, $bio, $phone, $skills, $githubUrl, $linkedinUrl, $currentUser['id']]);

                set_flash('success', 'Profile details updated successfully.');
                redirect('profile.php');
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // 2. Check if Profile Picture Upload submitted (Criterion 3)
    if (isset($_POST['action']) && $_POST['action'] === 'upload_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatar'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'An error occurred during file upload. Error code: ' . $file['error'];
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $allowedTypes)) {
                    $errors[] = 'Invalid file format. Please upload a valid JPG, PNG, or WEBP image.';
                } elseif ($file['size'] > 3 * 1024 * 1024) {
                    $errors[] = 'File is too large. Maximum allowed size is 3 MB.';
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newFilename = 'avatar_' . $currentUser['id'] . '_' . time() . '.' . strtolower($ext);
                    $destinationDir = __DIR__ . '/assets/uploads/avatars/';
                    
                    if (!is_dir($destinationDir)) {
                        mkdir($destinationDir, 0755, true);
                    }

                    $targetPath = $destinationDir . $newFilename;
                    $dbAvatarPath = 'assets/uploads/avatars/' . $newFilename;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        try {
                            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                            $stmt->execute([$dbAvatarPath, $currentUser['id']]);

                            set_flash('success', 'Profile picture uploaded and updated successfully!');
                            redirect('profile.php');
                        } catch (PDOException $e) {
                            $errors[] = 'Failed to update profile picture in database: ' . $e->getMessage();
                        }
                    } else {
                        $errors[] = 'Failed to save the uploaded image to the server.';
                    }
                }
            }
        } else {
            $errors[] = 'Please select an image file to upload.';
        }
    }
}

// Reload user details
$currentUser = current_user($pdo);

require_once 'includes/header.php';
?>

<main class="container">
    <div class="page-header">
        <p class="eyebrow">COMMUNITY MEMBER PROFILE</p>
        <h1>Manage Your Profile & Portfolio</h1>
        <p>Update your public information, upload an avatar, and share your technical skills.</p>
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

    <div class="profile-layout">
        <!-- LEFT: AVATAR & QUICK STATS -->
        <aside class="profile-sidebar">
            <img src="<?= escape($currentUser['profile_picture'] ?? 'assets/default_avatar.svg') ?>" 
                 alt="Profile Picture" 
                 class="profile-picture-lg"
                 onerror="this.src='assets/default_avatar.svg'">
            
            <h2 style="font-size: 20px; color: var(--ink); margin-bottom: 4px;"><?= escape($currentUser['full_name']) ?></h2>
            <span class="role-badge role-<?= escape($currentUser['role']) ?>" style="margin-bottom: 12px;">
                <?= ucfirst(escape($currentUser['role'])) ?>
            </span>
            <p style="font-size: 13px; color: var(--muted); margin-bottom: 16px;"><?= escape($currentUser['email']) ?></p>

            <!-- UPLOAD PROFILE PICTURE FORM (Criterion 3) -->
            <form method="POST" action="profile.php" enctype="multipart/form-data" style="width: 100%; border-top: 1px solid var(--border); padding-top: 16px;">
                <input type="hidden" name="action" value="upload_avatar">
                <label for="avatar_input" style="display: block; font-size: 12.5px; font-weight: 700; color: var(--ink); margin-bottom: 8px;">
                    Change Profile Picture
                </label>
                <input type="file" id="avatar_input" name="avatar" accept="image/*" required style="font-size: 12px; margin-bottom: 10px;">
                <button type="submit" class="button button-small button-outline button-full">
                    &#128247; Upload Picture
                </button>
            </form>

            <div style="margin-top: 24px; text-align: left; width: 100%; font-size: 12.5px; border-top: 1px solid var(--border); padding-top: 16px;">
                <p><strong>Member ID:</strong> #<?= (int)$currentUser['id'] ?></p>
                <p><strong>Joined:</strong> <?= date('F d, Y', strtotime($currentUser['created_at'])) ?></p>
                <p><strong>Status:</strong> <span style="color: var(--success); font-weight: 700;"><?= ucfirst(escape($currentUser['status'])) ?></span></p>
            </div>
        </aside>

        <!-- RIGHT: EDITABLE PROFILE DETAILS -->
        <section class="card">
            <div class="card-header">
                <h2>Edit Personal Information</h2>
                <a href="dashboard.php" class="button button-small button-secondary">&larr; Back to Dashboard</a>
            </div>

            <form method="POST" action="profile.php">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= escape($currentUser['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email_display">Email Address (Read-only)</label>
                    <input type="email" id="email_display" value="<?= escape($currentUser['email']) ?>" disabled style="background: #f1f3f5; cursor: not-allowed;">
                    <small style="color: var(--muted); font-size: 12px;">Contact an administrator to change your registered email.</small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= escape($currentUser['phone']) ?>" placeholder="e.g. +232 77 123 456">
                </div>

                <div class="form-group">
                    <label for="skills">Skills & Technical Interests</label>
                    <input type="text" id="skills" name="skills" value="<?= escape($currentUser['skills']) ?>" placeholder="e.g. Python, Django, HTML5, CSS3, Database Design">
                    <small style="color: var(--muted); font-size: 12px;">Separate skills with commas. These help us pair you with the right mentors.</small>
                </div>

                <div class="form-group">
                    <label for="bio">Short Biography / Goals</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Tell the community about yourself, your background, and your aspirations in technology..."><?= escape($currentUser['bio']) ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="github_url">GitHub Profile URL</label>
                        <input type="url" id="github_url" name="github_url" value="<?= escape($currentUser['github_url']) ?>" placeholder="https://github.com/username">
                    </div>
                    <div class="form-group">
                        <label for="linkedin_url">LinkedIn Profile URL</label>
                        <input type="url" id="linkedin_url" name="linkedin_url" value="<?= escape($currentUser['linkedin_url']) ?>" placeholder="https://linkedin.com/in/username">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="submit" class="button button-large">
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
