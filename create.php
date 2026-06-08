<?php
require __DIR__ . '/config.php';

$title = '';
$content = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO posts (title, content) VALUES (?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $title, $content);
            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                header("Location: index.php?msg=" . urlencode('Post published successfully') . "&type=success");
                exit;
            } else {
                $error = 'Failed to create post. Please try again.';
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
    <title>New Post - My Blog</title>
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
            <h2>Create New Post</h2>
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
                    <button type="submit" class="btn btn-success">Publish Post</button>
                    <a href="index.php" class="btn btn-primary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
