<?php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// Fetch existing post
$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$post) {
    header("Location: index.php?msg=" . urlencode('Post not found') . "&type=error");
    exit;
}

$title = $post['title'];
$content = $post['content'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE posts SET title = ?, content = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: index.php?msg=" . urlencode('Post updated successfully') . "&type=success");
                exit;
            } else {
                $error = 'Failed to update post. Please try again.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - My Blog</title>
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
        <div class="card">
            <h2>Edit Post</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($title); ?>" placeholder="Enter post title">
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" rows="12" required placeholder="Write your post content here..."><?php echo htmlspecialchars($content); ?></textarea>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-success">Update Post</button>
                    <a href="index.php" class="btn btn-primary">Cancel</a>
                </div>
            </form>
        </div>
        <div class="card" style="border-left:4px solid #e74c3c;">
            <h3 style="color:#e74c3c;">Danger Zone</h3>
            <p style="margin-bottom:10px;color:#555;">Once you delete a post, there is no going back. Please be certain.</p>
            <a href="delete.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post permanently?')">Delete this post</a>
        </div>
    </div>
</body>
</html>
