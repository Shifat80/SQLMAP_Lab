<?php
require __DIR__ . '/config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please+login+to+access+your+profile&type=error");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    
    // VULNERABLE: No CSRF tokens, and SQL Injection in the update query
    // Also, normally we'd verify the old password, but we're keeping it simple and vulnerable.
    $sql = "UPDATE users SET password = '$new_password' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $msg = "Password updated successfully!";
    } else {
        $msg = "Error updating password: " . mysqli_error($conn);
        $msgType = 'error';
    }
}

// Fetch current user details
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Vulnerable Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">Vulnerable Blog</a></h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="create.php">New Post</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 20px auto;">
            <h2>User Profile</h2>
            <?php if ($msg): ?>
                <div class="alert alert-<?php echo $msgType === 'error' ? 'danger' : 'success'; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
            <p><strong>Joined:</strong> <?php echo $user['created_at']; ?></p>
            
            <hr style="margin: 20px 0;">
            
            <h3>Change Password</h3>
            <p style="font-size: 0.85em; color: #e74c3c; margin-bottom: 10px;">
                (Warning: This form is vulnerable to CSRF!)
            </p>
            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required placeholder="Enter new password">
                </div>
                <button type="submit" class="btn btn-warning">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>
