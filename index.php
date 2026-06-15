<?php
require __DIR__ . '/config.php';

$search = trim($_GET['search'] ?? '');
$sort = strtolower($_GET['sort'] ?? 'desc');
$sort = in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
$msg = $_GET['msg'] ?? '';
$msgType = $_GET['type'] ?? 'success';

// Build query
$where = '';
if ($search !== '') {
    $where = "WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}

$sql = "SELECT * FROM posts $where ORDER BY created_at $sort";
$result = mysqli_query($conn, $sql);
if ($result) {
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
            <h1><a href="index.php">Vulnerable Blog</a></h1>
            <nav>
                <a href="index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create.php">New Post</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="container">
        <!-- DOM XSS Vulnerability: Reads from hash and writes directly to innerHTML -->
        <div id="welcome" style="margin-bottom: 20px; font-style: italic; color: #555;"></div>
        <script>
            const welcomeDiv = document.getElementById('welcome');
            const hash = window.location.hash.substring(1);
            if (hash) {
                welcomeDiv.innerHTML = "Welcome back, " + decodeURIComponent(hash) + "!";
            }
        </script>
        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msgType === 'error' ? 'danger' : 'success'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <!-- Lab Debug Helper: Verify if JS can see cookies -->
        <div id="js-cookie-status" style="padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; background: #f9f9f9; font-size: 0.8em;">
            <b>Lab Debug:</b> Checking JavaScript cookie access...
        </div>
        <script>
            const statusDiv = document.getElementById('js-cookie-status');
            if (document.cookie) {
                statusDiv.innerHTML = "<b>Lab Debug:</b> ✅ JavaScript CAN see cookies. (Value: " + document.cookie.substring(0, 20) + "...)";
                statusDiv.style.color = "green";
            } else {
                statusDiv.innerHTML = "<b>Lab Debug:</b> ❌ JavaScript CANNOT see any cookies. (Check if you are logged in)";
                statusDiv.style.color = "red";
            }
        </script>

        <div class="toolbar">
            <form method="GET" action="index.php" class="search-bar">
                <input type="text" name="search" placeholder="Search posts..." value="<?php echo $search; ?>">
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
                    <h3><a href="edit.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h3>
                    <div class="meta">
                        Posted on <?php echo date('F j, Y g:i A', strtotime($post['created_at'])); ?>
                        <?php if ($post['updated_at'] !== $post['created_at']): ?>
                            &middot; Updated <?php echo date('F j, Y g:i A', strtotime($post['updated_at'])); ?>
                        <?php endif; ?>
                    </div>
                    <div class="content"><?php echo nl2br($post['content']); ?></div>
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
