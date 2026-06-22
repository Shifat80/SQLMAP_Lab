<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please+login+to+delete+posts&type=error");
    exit;
}

$id = $_GET['id'] ?? '';

if ($id === '') {
    header("Location: index.php?msg=" . urlencode('Invalid post ID') . "&type=error");
    exit;
}

$sql = "SELECT id FROM posts WHERE id = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php?msg=" . urlencode('Post not found') . "&type=error");
    exit;
}

$sql = "DELETE FROM posts WHERE id = '$id'";
$deleted = mysqli_query($conn, $sql);

if ($deleted) {
    header("Location: index.php?msg=" . urlencode('Post deleted successfully') . "&type=success");
    exit;
} else {
    header("Location: index.php?msg=" . urlencode('Failed to delete post: ' . mysqli_error($conn)) . "&type=error");
    exit;
}
