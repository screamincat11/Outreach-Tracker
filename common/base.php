<?php
    // Set the error reporting level
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    // Start a PHP session
    session_start();

    // Include site constants
    require_once "inc/constants.inc.php";
    //require_once "inc/swift/lib/swift_required.php";

    // Create a database object
    try {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
        $db = new PDO($dsn, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        exit;
    }
?>

