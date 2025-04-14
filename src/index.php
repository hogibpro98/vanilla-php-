<?php

require_once __DIR__ . '/db/connection.php';
require_once __DIR__ . '/db/database.php';

try {
    // Get database connection
    $dbConnection = DatabaseConnection::getInstance();
    $pdo = $dbConnection->getConnection();
    $connectionStatus = "<p class='success'>✓ Successfully connected to MySQL database!</p>";
    
    // Get MySQL version
    $mysqlVersion = ['version' => $dbConnection->getMySQLVersion()];
    
    // Example of using the Database class for a secure query
    // $db = new Database();
    // $users = $db->select("SELECT * FROM users WHERE status = ?", ['active']);
} catch (Exception $e) {
    $connectionStatus = "<p class='error'>✗ Connection failed: " . $e->getMessage() . "</p>";
    $mysqlVersion = ['version' => 'N/A'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>PHP MySQL Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background: #f4f4f4;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .info {
            margin-top: 20px;
            background: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class='container'>
        <h1>PHP MySQL Connection Test</h1>

        <?php echo $connectionStatus; ?>

        <div class='info'>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>MySQL Version:</strong> <?php echo $mysqlVersion['version']; ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
            <a href="http://localhost:80/select2/index.php">Select2</a>
        </div>
    </div>
</body>

</html>