<?php
// Start session for auth and CSRF demos
// In this lab, we explicitly disable HttpOnly so that JavaScript can read the cookie for the XSS demo.
session_start([
    'cookie_httponly' => false,
]);

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
