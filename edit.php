<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please+login+to+edit+posts&type=error");
    exit;
}

$id = $_GET['id'] ?? '';
$error = '';
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];

// Fetch existing post
$sql = "SELECT * FROM posts WHERE id = '$id'";
$result = mysqli_query($conn, $sql);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    header("Location: index.php?msg=" . urlencode('Post not found') . "&type=error");
    exit;
}

// Check if user_id column exists
$checkCol = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'user_id'");
$has_user_id = mysqli_num_rows($checkCol) > 0;

// VULNERABLE: Authorization check that can be bypassed via CSRF
// Shifat cannot directly edit victim's posts, but can force victim to do it via CSRF
if ($has_user_id && $current_username === 'shifat' && $post['user_id'] != $current_user_id) {
    $error = "Access Denied: You cannot edit posts that belong to other users. (Hint: Try a CSRF attack to bypass this!)";
}

$title = $post['title'];
$content = $post['content'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if ($title === '' || $content === '') {
        $error = 'Please fill in all fields.';
    } else {
        // VULNERABLE: No CSRF token - any logged-in user can be forced to submit this
        // Also SQL Injection vulnerable
        $sql = "UPDATE posts SET title = '$title', content = '$content' WHERE id = '$id'";
        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?msg=" . urlencode('Post updated successfully') . "&type=success");
            exit;
        } else {
            $error = 'Failed to update post. Please try again: ' . mysqli_error($conn);
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
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" id="edit-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" required value="<?php echo $title; ?>" placeholder="Enter post title">
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" rows="12" required placeholder="Write your post content here..."><?php echo $content; ?></textarea>
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
