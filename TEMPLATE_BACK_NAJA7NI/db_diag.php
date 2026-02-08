<?php
try {
    $dsn = "mysql:host=localhost;port=3306";
    $user = "root";
    $pass = "";
    echo "Connecting to $dsn with user $user...\n";
    $pdo = new PDO($dsn, $user, $pass);
    echo "Connected successfully!\n";
    $stmt = $pdo->query("SHOW DATABASES");
    echo "Databases:\n";
    while ($row = $stmt->fetchColumn()) {
        echo "- $row\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
