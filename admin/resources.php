<?php
$inAdmin = true;
require_once __DIR__ . '/../config.php';

require_admin($pdo);

$pageTitle = 'Manage Learning Resources';
$currentUser = current_user($pdo);

// Handle Resource Creation & Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_resource') {
        $title        = trim($_POST['title'] ?? '');
        $category     = trim($_POST['category'] ?? 'General');
        $resourceType = trim($_POST['resource_type'] ?? 'guide');
        $link         = trim($_POST['external_link'] ?? '');
        $description  = trim($_POST['description'] ?? '');

        if (empty($title) || empty($link)) {
            set_flash('error', 'Title and link URL are required.');
        } else {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO resources (title, category, resource_type, external_link, description, created_by)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$title, $category, $resourceType, $link, $description, $currentUser['id']]);
                set_flash('success', 'New learning material added successfully!');
            } catch (PDOException $e) {
                set_flash('error', 'Error: ' . $e->getMessage());
            }
        }
    } elseif ($_POST['action'] === 'delete_resource') {
        $resId = (int)($_POST['resource_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
            $stmt->execute([$resId]);
            set_flash('info', 'Resource deleted from library.');
        } catch (PDOException $e) {
            set_flash('error', 'Error: ' . $e->getMessage());
        }
    }
    redirect('resources.php');
}

// Fetch all resources
$resources = [];
try {
    $resources = $pdo->query("SELECT * FROM resources ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">CURRICULUM REPOSITORY</p>
            <h1>Manage Learning Materials</h1>
            <p>Upload tutorials, reference sheets, documentation links, and coding starter templates.</p>
        </div>
        <a href="index.php" class="button button-small button-secondary">&larr; Back to Admin Overview</a>
    </div>

    <!-- ADMIN SUB-BAR -->
    <div class="filter-bar" style="margin-bottom: 25px;">
        <a href="index.php" class="filter-chip">&#9881; Overview</a>
        <a href="users.php" class="filter-chip">&#128101; Manage Users</a>
        <a href="events.php" class="filter-chip">&#128197; Manage Workshops</a>
        <a href="resources.php" class="filter-chip active">&#128218; Manage Resources (<?= count($resources) ?>)</a>
        <a href="reports.php" class="filter-chip">&#128202; Reports & Analytics</a>
    </div>

    <div class="dashboard-grid">
        <!-- RESOURCES LIST -->
        <div class="card">
            <div class="card-header">
                <h2>All Curated Resources</h2>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Resource</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Added</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($resources)): ?>
                            <?php foreach ($resources as $r): ?>
                                <tr>
                                    <td>
                                        <strong><?= escape($r['title']) ?></strong><br>
                                        <a href="<?= escape($r['external_link']) ?>" target="_blank" style="font-size: 11.5px; color: var(--purple);">
                                            <?= escape(substr($r['external_link'], 0, 36)) ?>... &rarr;
                                        </a>
                                    </td>
                                    <td><span class="role-badge role-mentor"><?= escape($r['category']) ?></span></td>
                                    <td><span class="resource-type-pill"><?= escape($r['resource_type']) ?></span></td>
                                    <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                                    <td style="text-align: right;">
                                        <form method="POST" action="resources.php" style="margin: 0; display: inline-block;" onsubmit="return confirm('Delete this resource?');">
                                            <input type="hidden" name="action" value="delete_resource">
                                            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
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

        <!-- ADD RESOURCE FORM -->
        <div class="card">
            <div class="card-header">
                <h2>&#43; Add Learning Material</h2>
            </div>

            <form method="POST" action="resources.php">
                <input type="hidden" name="action" value="create_resource">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Django ORM Cheat Sheet" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" placeholder="e.g. Django, Python, Security, Frontend" required>
                </div>

                <div class="form-group">
                    <label for="resource_type">Material Type</label>
                    <select id="resource_type" name="resource_type">
                        <option value="guide">Guide</option>
                        <option value="tutorial">Tutorial</option>
                        <option value="template">Code Template</option>
                        <option value="video">Video Course</option>
                        <option value="pdf">PDF Handbook</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="external_link">Resource URL / Link</label>
                    <input type="url" id="external_link" name="external_link" placeholder="https://..." required>
                </div>

                <div class="form-group">
                    <label for="description">Short Summary</label>
                    <textarea id="description" name="description" rows="3" placeholder="Explain what students will learn from this resource..."></textarea>
                </div>

                <button type="submit" class="button button-full button-large">
                    Save to Library &rarr;
                </button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
