<?php
require_once 'config.php';

$pageTitle = 'Learning Resources';
$currentUser = current_user($pdo);

$searchQuery    = trim($_GET['search'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');

$resources = [];
try {
    $sql = "SELECT r.*, u.full_name as author_name 
            FROM resources r
            LEFT JOIN users u ON r.created_by = u.id
            WHERE 1=1";
    $params = [];

    if ($searchQuery !== '') {
        $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
        $params[] = "%{$searchQuery}%";
        $params[] = "%{$searchQuery}%";
    }

    if ($filterCategory !== '') {
        $sql .= " AND r.category = ?";
        $params[] = $filterCategory;
    }

    $sql .= " ORDER BY r.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resources = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch categories
$categories = [];
try {
    $categories = $pdo->query("SELECT DISTINCT category FROM resources ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {}

require_once 'includes/header.php';
?>

<main class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <p class="eyebrow">CURRICULUM & SELF-PACED STUDY</p>
            <h1>Resources & Learning Materials</h1>
            <p>Access downloadable documentation, coding cheat sheets, and hands-on video tutorials.</p>
        </div>
        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
            <a href="admin/resources.php" class="button button-small">&#43; Add Resource</a>
        <?php endif; ?>
    </div>

    <!-- SEARCH & CATEGORY FILTER -->
    <div class="card" style="margin-bottom: 24px; padding: 18px 24px;">
        <form method="GET" action="resources.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <input type="text" name="search" value="<?= escape($searchQuery) ?>" placeholder="Search resources by keyword (e.g. Django, Git, SQL)..." style="flex: 1; min-width: 250px;">
            <?php if ($filterCategory !== ''): ?>
                <input type="hidden" name="category" value="<?= escape($filterCategory) ?>">
            <?php endif; ?>
            <button type="submit" class="button button-small">&#128269; Search</button>
            <?php if ($searchQuery !== '' || $filterCategory !== ''): ?>
                <a href="resources.php" class="button button-small button-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="filter-bar">
        <a href="resources.php<?= $searchQuery !== '' ? '?search=' . urlencode($searchQuery) : '' ?>" class="filter-chip <?= ($filterCategory === '') ? 'active' : '' ?>">All Topics</a>
        <?php foreach ($categories as $cat): ?>
            <a href="resources.php?category=<?= urlencode($cat) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>" class="filter-chip <?= ($filterCategory === $cat) ? 'active' : '' ?>">
                <?= escape($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- RESOURCES GRID -->
    <div class="resources-grid">
        <?php if (!empty($resources)): ?>
            <?php foreach ($resources as $res): ?>
                <div class="resource-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="resource-type-pill"><?= escape($res['resource_type']) ?></span>
                        <span style="font-size: 11.5px; font-weight: 700; color: var(--purple); background: var(--lavender); padding: 2px 8px; border-radius: 4px;">
                            <?= escape($res['category']) ?>
                        </span>
                    </div>

                    <h3><?= escape($res['title']) ?></h3>
                    <p><?= escape($res['description']) ?></p>

                    <div style="font-size: 12px; color: var(--muted); margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border-light);">
                        Added: <?= date('M d, Y', strtotime($res['created_at'])) ?>
                    </div>

                    <a href="<?= escape($res['external_link']) ?>" target="_blank" rel="noopener noreferrer" class="button button-small button-full">
                        &#128279; Open Learning Material &rarr;
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <p>No resources found matching your search query.</p>
                <a href="resources.php" class="button button-small" style="margin-top: 10px;">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
