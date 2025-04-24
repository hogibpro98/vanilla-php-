<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Check if export request
if (isset($_GET['export']) && $_GET['export'] === 'true') {
    // Set execution time limit to ensure the script has enough time to complete
    set_time_limit(600); // 10 minutes

    // Get database connection
    $dbConnection = DatabaseConnection::getInstance();
    $pdo = $dbConnection->getConnection();
    $db = new Database();

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="employee_export_' . date('Y-m-d_H-i-s') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Write CSV header
    fputcsv($output, [
        'emp_id',
        'emp_no',
        'birth_date',
        'first_name',
        'last_name',
        'gender',
        'title',
        'salary',
        'nd1.dept_name',
        'nd2.dept_name'
    ]);

    // Initialize variables for pagination
    $lastId = 0;
    $limit = 5000;
    $totalRecords = 0;
    $maxRecords = 1000000;

    // Process data in batches
    while ($totalRecords < $maxRecords) {
        // Prepare the base query
        $query = "
        SELECT 
            ne.emp_id,
            ne.emp_no,
            ne.birth_date,
            ne.first_name,
            ne.last_name,
            ne.gender,
            nt.title,
            ns.salary,
            nd1.dept_name as 'nd1.dept_name',
            nd2.dept_name as 'nd2.dept_name'
        FROM
            (SELECT emp_id FROM nth_employees 
             WHERE emp_id > :lastId
             AND (last_name LIKE BINARY '%son%' OR first_name LIKE BINARY '%son%')
             ORDER BY emp_id ASC
             LIMIT :limit) sub_ne
        INNER JOIN nth_employees ne on sub_ne.emp_id = ne.emp_id
        INNER JOIN nth_titles nt ON (ne.emp_id = nt.emp_id AND nt.title = 'Engineer')
        LEFT JOIN nth_salaries ns ON ne.emp_id = ns.emp_id
        LEFT JOIN nth_dept_emp nde ON ne.emp_id = nde.emp_id
        LEFT JOIN nth_departments nd1 ON nde.dept_id = nd1.dept_id
        LEFT JOIN nth_dept_manager ndm ON ne.emp_id = ndm.emp_id
        LEFT JOIN nth_departments nd2 on ndm.dept_id = nd2.dept_id
        ORDER BY ne.emp_id ASC";

        try {
            // Prepare and execute the query
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':lastId', $lastId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            // Process the results
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rowCount = count($rows);

            // If no rows returned, break the loop
            if ($rowCount === 0) {
                break;
            }

            // Write data to CSV
            foreach ($rows as $row) {
                fputcsv($output, $row);
                $lastId = $row['emp_id']; // Update lastId for next batch
            }

            $totalRecords += $rowCount;
            
            // If we've reached our limit or there are no more records, exit the loop
            if ($totalRecords >= $maxRecords) {
                break;
            }

            // Free up memory
            $stmt->closeCursor();
            unset($rows);
            
        } catch (Exception $e) {
            // Write error to the CSV
            fputcsv($output, ['Error processing data: ' . $e->getMessage()]);
            break;
        }
    }

    // Close the CSV file
    fclose($output);
    exit;
}

// Get database connection to check if it works
$connectionStatus = '';
try {
    $dbConnection = DatabaseConnection::getInstance();
    $pdo = $dbConnection->getConnection();
    $connectionStatus = "<p class='success'>✓ Database connection successful!</p>";
} catch (Exception $e) {
    $connectionStatus = "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Engineers CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            margin-top: 0;
            color: #333;
        }
        .btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 15px 25px;
            margin: 20px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .btn:hover {
            background: #45a049;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Export Engineer Employees Data</h1>
        
        <?php echo $connectionStatus; ?>
        
        <p>Click the button below to export CSV data for Engineer employees with 'son' in their name.</p>
        
        <a href="?export=true" class="btn">Export CSV</a>
    </div>
</body>
</html> 