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

// Create posts table (ensure it exists)
$sqlPosts = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sqlPosts)) {
    echo "Table 'posts' created successfully.<br>";
} else {
    echo "Error creating table 'posts': " . mysqli_error($conn) . "<br>";
}

// Insert default admin user if not exists
$checkAdmin = mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($checkAdmin) === 0) {
    // In a real app, use password_hash. Here we use plaintext for "educational" vulnerability reasons (and simplicity for students).
    $insertAdmin = "INSERT INTO users (username, password) VALUES ('admin', 'password')";
    if (mysqli_query($conn, $insertAdmin)) {
        echo "Default admin user created (admin/password).<br>";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists.<br>";
}

echo "<br><a href='index.php'>Go to Home</a>";
