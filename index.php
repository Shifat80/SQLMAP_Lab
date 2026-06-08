<?php
require __DIR__ . '/config.php';

$search = trim($_GET['search'] ?? '');
$sort = strtolower($_GET['sort'] ?? 'desc');
$sort = in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
$msg = $_GET['msg'] ?? '';
$msgType = $_GET['type'] ?? 'success';

// Build query
$where = '';
$params = [];
$types = '';
if ($search !== '') {
    $where = "WHERE title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%')";
    $params = [$search, $search];
    $types = 'ss';
}

$sql = "SELECT * FROM posts $where ORDER BY created_at $sort";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    $posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">My Blog</a></h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="create.php">New Post</a>
            </nav>
        </div>
    </header>
    <div class="container">
        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msgType === 'error' ? 'danger' : 'success'; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="toolbar">
            <form method="GET" action="index.php" class="search-bar">
                <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="index.php?sort=<?php echo $sort; ?>" class="btn btn-sm btn-warning">Clear</a>
                <?php endif; ?>
            </form>
            <div class="sort-links">
                <span style="font-size:0.85em;color:#7f8c8d;">Sort:</span>
                <a href="?<?php echo $search ? 'search=' . urlencode($search) . '&' : ''; ?>sort=desc" class="<?php echo $sort === 'desc' ? 'active' : ''; ?>">Newest</a>
                <a href="?<?php echo $search ? 'search=' . urlencode($search) . '&' : ''; ?>sort=asc" class="<?php echo $sort === 'asc' ? 'active' : ''; ?>">Oldest</a>
            </div>
        </div>

        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card post">
                    <h3><a href="edit.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    <div class="meta">
                        Posted on <?php echo date('F j, Y g:i A', strtotime($post['created_at'])); ?>
                        <?php if ($post['updated_at'] !== $post['created_at']): ?>
                            &middot; Updated <?php echo date('F j, Y g:i A', strtotime($post['updated_at'])); ?>
                        <?php endif; ?>
                    </div>
                    <div class="content"><?php echo nl2br(htmlspecialchars(strlen($post['content']) > 300 ? substr($post['content'], 0, 300) . '...' : $post['content'])); ?></div>
                    <div class="actions">
                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post permanently?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card empty-state">
                <p style="font-size:1.3em;margin-bottom:10px;">No posts found</p>
                <p><?php echo $search ? 'No results matching your search.' : 'The blog is empty.'; ?></p>
                <br>
                <a href="create.php" class="btn btn-success">Create the first post</a>
            </div>
        <?php endif; ?>
        <footer>&copy; <?php echo date('Y'); ?> My Blog</footer>
    </div>
</body>
</html>
