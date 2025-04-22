<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Don't show notices if display_errors is off
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Error type mapping
    $errorTypes = array(
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    );

    $errorType = isset($errorTypes[$errno]) ? $errorTypes[$errno] : 'Unknown Error';
    
    // Build debug information
    $debugInfo = array(
        'Error Type' => $errorType,
        'Error Message' => $errstr,
        'File' => $errfile,
        'Line' => $errline,
        'Request URI' => $_SERVER['REQUEST_URI'],
        'HTTP Method' => $_SERVER['REQUEST_METHOD'],
        'Server Software' => $_SERVER['SERVER_SOFTWARE'],
        'PHP Version' => PHP_VERSION,
        'Session Data' => isset($_SESSION) ? $_SESSION : 'No session data',
        'POST Data' => !empty($_POST) ? $_POST : 'No POST data',
        'GET Data' => !empty($_GET) ? $_GET : 'No GET data'
    );

    // Display error page with debug information
    header('HTTP/1.1 500 Internal Server Error');
    include 'app/views/error/debug.php';
    return true;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Custom exception handler
function customExceptionHandler($exception) {
    $debugInfo = array(
        'Error Type' => get_class($exception),
        'Error Message' => $exception->getMessage(),
        'File' => $exception->getFile(),
        'Line' => $exception->getLine(),
        'Stack Trace' => $exception->getTraceAsString(),
        'Request URI' => $_SERVER['REQUEST_URI'],
        'HTTP Method' => $_SERVER['REQUEST_METHOD']
    );

    // Display error page with debug information
    header('HTTP/1.1 500 Internal Server Error');
    include 'app/views/error/debug.php';
}

// Set custom exception handler
set_exception_handler('customExceptionHandler');

// Handle 404 errors
function handle404() {
    $debugInfo = array(
        'Error Type' => '404 Not Found',
        'Error Message' => 'The requested page was not found',
        'Request URI' => $_SERVER['REQUEST_URI'],
        'HTTP Method' => $_SERVER['REQUEST_METHOD'],
        'Server Software' => $_SERVER['SERVER_SOFTWARE'],
        'PHP Version' => PHP_VERSION,
        'Session Data' => isset($_SESSION) ? $_SESSION : 'No session data',
        'GET Data' => !empty($_GET) ? $_GET : 'No GET data'
    );

    header('HTTP/1.1 404 Not Found');
    include 'app/views/error/debug.php';
    exit;
} 