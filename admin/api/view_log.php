<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Unauthorized access');
}

$log_file = __DIR__ . '/debug.log';

echo "<h2>Log File Status:</h2>";
echo "<pre>";

if (file_exists($log_file)) {
    echo "Log file exists at: " . $log_file . "\n";
    echo "File size: " . filesize($log_file) . " bytes\n";
    echo "File permissions: " . decoct(fileperms($log_file) & 0777) . "\n";
    echo "Is readable: " . (is_readable($log_file) ? "Yes" : "No") . "\n";
    echo "Is writable: " . (is_writable($log_file) ? "Yes" : "No") . "\n\n";
    
    echo "<h2>Log Contents:</h2>";
    $contents = file_get_contents($log_file);
    if ($contents !== false) {
        echo htmlspecialchars($contents);
    } else {
        echo "Could not read log file. Error: " . error_get_last()['message'];
    }
} else {
    echo "Log file does not exist at: " . $log_file . "\n";
    echo "Current directory: " . __DIR__ . "\n";
    echo "PHP process user: " . get_current_user() . "\n";
    
    // Try to create the file
    echo "\nAttempting to create log file...\n";
    if (touch($log_file)) {
        echo "Successfully created log file\n";
        chmod($log_file, 0666);
        echo "Set permissions to 666\n";
    } else {
        echo "Failed to create log file. Error: " . error_get_last()['message'] . "\n";
    }
}

echo "</pre>"; 