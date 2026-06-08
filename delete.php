<?php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?msg=" . urlencode('Invalid post ID') . "&type=error");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: index.php?msg=" . urlencode('Post not found') . "&type=error");
    exit;
}
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    header("Location: index.php?msg=" . urlencode('Post deleted successfully') . "&type=success");
} else {
    header("Location: index.php?msg=" . urlencode('Failed to delete post') . "&type=error");
}
exit;
