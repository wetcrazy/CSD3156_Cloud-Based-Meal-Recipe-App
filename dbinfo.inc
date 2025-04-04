<?php
    define('DB_SERVER', '<change this>');
    define('DB_USERNAME', 'admin');
    define('DB_PASSWORD', 'testtest');
    define('DB_DATABASE', 'recipes');

    // Connect to MySQL RDS
    $connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
?>
