<?php
// Start session for auth and CSRF demos
// Secure practice would be: session_start(['cookie_httponly' => true]);
// Vulnerable practice for this lab:
session_start();

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'blog_user';
$pass = getenv('DB_PASS') ?: 'blog_pass_2024';
$name = getenv('DB_NAME') ?: 'blog_app';

$conn = mysqli_connect($host, $user, $pass, $name);
if (!$conn) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("Cannot connect to the database. Please try again later.");
}
mysqli_set_charset($conn, 'utf8mb4');
