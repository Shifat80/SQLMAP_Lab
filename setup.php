<?php
require __DIR__ . '/config.php';

echo "<h2>Database Setup</h2>";

// Create users table
$sqlUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sqlUsers)) {
    echo "Table 'users' created successfully.<br>";
} else {
    echo "Error creating table 'users': " . mysqli_error($conn) . "<br>";
}

// Create posts table with user_id for ownership
$sqlPosts = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL DEFAULT 1,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sqlPosts)) {
    echo "Table 'posts' created successfully.<br>";
} else {
    echo "Error creating table 'posts': " . mysqli_error($conn) . "<br>";
}

// Insert default admin user if not exists
$checkAdmin = mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($checkAdmin) === 0) {
    $insertAdmin = "INSERT INTO users (username, password) VALUES ('admin', 'password')";
    if (mysqli_query($conn, $insertAdmin)) {
        echo "Default admin user created (admin/password).<br>";
    }
}

// Insert default victim user if not exists
$checkVictim = mysqli_query($conn, "SELECT id FROM users WHERE username = 'victim'");
if (mysqli_num_rows($checkVictim) === 0) {
    $insertVictim = "INSERT INTO users (username, password) VALUES ('victim', 'victim123')";
    if (mysqli_query($conn, $insertVictim)) {
        echo "Default victim user created (victim/victim123).<br>";
    } else {
        echo "Error creating victim user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Victim user already exists.<br>";
}

// Insert shifat user (the attacker) if not exists
$checkShifat = mysqli_query($conn, "SELECT id FROM users WHERE username = 'shifat'");
if (mysqli_num_rows($checkShifat) === 0) {
    $insertShifat = "INSERT INTO users (username, password) VALUES ('shifat', '1234')";
    if (mysqli_query($conn, $insertShifat)) {
        echo "Attacker user 'shifat' created (shifat/1234).<br>";
    } else {
        echo "Error creating shifat user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Shifat user already exists.<br>";
}

// Add default posts if none exist
$checkPosts = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
$row = mysqli_fetch_assoc($checkPosts);
if ($row['count'] == 0) {
    $insertPost = "INSERT INTO posts (user_id, title, content) VALUES (2, 'Welcome to the Blog', 'This is a sample post created by victim.')";
    if (mysqli_query($conn, $insertPost)) {
        echo "Default post created by victim.<br>";
    }
}

echo "<br><a href='index.php'>Go to Home</a>";
