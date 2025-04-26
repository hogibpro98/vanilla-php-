<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Function to check if request is AJAX
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Process AJAX requests for progress or data export
if (is_ajax()) {
    if (isset($_POST['action']) && $_POST['action'] == 'export') {
        // Start a new export session
        session_start();
        
        // Initialize export session variables
        $_SESSION['export_started'] = true;
        $_SESSION['export_lastId'] = isset($_POST['lastId']) ? (int)$_POST['lastId'] : 0;
        $_SESSION['export_totalRecords'] = isset($_POST['totalRecords']) ? (int)$_POST['totalRecords'] : 0;
        $_SESSION['export_limit'] = isset($_POST['limit']) ? (int)$_POST['limit'] : 5000;
        $_SESSION['export_maxRecords'] = isset($_POST['maxRecords']) ? (int)$_POST['maxRecords'] : 1000000;
        
        // Get a batch of records
        $result = exportBatch();
        
        // Return the result as JSON
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'progress') {
        // Get progress information
        session_start();
        
        $progress = [
            'current' => isset($_SESSION['export_totalRecords']) ? $_SESSION['export_totalRecords'] : 0,
            'total' => isset($_SESSION['export_maxRecords']) ? $_SESSION['export_maxRecords'] : 1000000,
            'finished' => !isset($_SESSION['export_started']) || !$_SESSION['export_started'],
        ];
        
        // Return progress as JSON
        header('Content-Type: application/json');
        echo json_encode($progress);
        exit;
    }
    
    // Invalid AJAX request
    header('HTTP/1.1 400 Bad Request');
    exit;
}

/**
 * Function to export a batch of data and update session variables
 */
function exportBatch() {
    // Set execution time limit for this batch
    set_time_limit(120); // 2 minutes
    
    // Get database connection
    $dbConnection = DatabaseConnection::getInstance();
    $pdo = $dbConnection->getConnection();
    
    // Get session variables
    $lastId = $_SESSION['export_lastId'];
    $limit = $_SESSION['export_limit'];
    $totalRecords = $_SESSION['export_totalRecords'];
    $maxRecords = $_SESSION['export_maxRecords'];
    
    // File path for temporary CSV storage
    $tempDir = sys_get_temp_dir();
    $tempFile = $tempDir . '/employee_export_temp.csv';
    
    // Open file in append mode if it exists, otherwise create it
    $fileMode = ($totalRecords === 0) ? 'w' : 'a';
    $output = fopen($tempFile, $fileMode);
    
    // Write CSV header if this is the first batch
    if ($totalRecords === 0) {
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
    }
    
    // Prepare the query
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
        // Execute the query
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        // Process the results
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rowCount = count($rows);
        
        // Write data to CSV
        foreach ($rows as $row) {
            fputcsv($output, $row);
            $lastId = $row['emp_id']; // Update lastId for next batch
        }
        
        // Update session variables
        $totalRecords += $rowCount;
        $_SESSION['export_lastId'] = $lastId;
        $_SESSION['export_totalRecords'] = $totalRecords;
        
        // Check if export is complete
        $isComplete = ($rowCount === 0 || $totalRecords >= $maxRecords);
        
        if ($isComplete) {
            $_SESSION['export_started'] = false;
        }
        
        // Close file
        fclose($output);
        
        // Return batch processing result
        return [
            'success' => true,
            'lastId' => $lastId,
            'processedRows' => $rowCount,
            'totalRows' => $totalRecords,
            'isComplete' => $isComplete,
            'fileReady' => $isComplete,
        ];
        
    } catch (Exception $e) {
        // Close file
        fclose($output);
        
        // End the export session
        $_SESSION['export_started'] = false;
        
        // Return error
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

// Create final CSV and send to browser if export is complete
function finishExport() {
    // File path for temporary CSV storage
    $tempDir = sys_get_temp_dir();
    $tempFile = $tempDir . '/employee_export_temp.csv';
    
    // Check if file exists
    if (!file_exists($tempFile)) {
        echo "Error: Export file not found.";
        return;
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="employee_export_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output file contents
    readfile($tempFile);
    
    // Delete the temporary file
    unlink($tempFile);
    
    // Reset session
    session_start();
    unset($_SESSION['export_started']);
    unset($_SESSION['export_lastId']);
    unset($_SESSION['export_totalRecords']);
    unset($_SESSION['export_limit']);
    unset($_SESSION['export_maxRecords']);
    session_write_close();
    
    exit;
}

// Check if this is a download request
if (isset($_GET['download']) && $_GET['download'] == 'true') {
    finishExport();
}

// Check if export request
if (isset($_GET['export']) && $_GET['export'] === 'true') {
    // Set execution time limit to ensure the script has enough time to complete
    set_time_limit(1200); // 20 minutes
    ini_set('memory_limit', '1024M'); // Increase memory limit to handle large dataset

    // Get database connection
    $dbConnection = DatabaseConnection::getInstance();
    $pdo = $dbConnection->getConnection();
    $db = new Database();

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="employee_export_' . date('Y-m-d_H-i-s') . '.csv"');

    // Start output buffering
    ob_start();

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

    // Fetch all records up to limit (1,000,000)
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
         WHERE emp_id > 0
         AND (last_name LIKE BINARY '%son%' OR first_name LIKE BINARY '%son%')
         ORDER BY emp_id ASC
         LIMIT 1000000) sub_ne
    INNER JOIN nth_employees ne on sub_ne.emp_id = ne.emp_id
    INNER JOIN nth_titles nt ON (ne.emp_id = nt.emp_id AND nt.title = 'Engineer')
    LEFT JOIN nth_salaries ns ON ne.emp_id = ns.emp_id
    LEFT JOIN nth_dept_emp nde ON ne.emp_id = nde.emp_id
    LEFT JOIN nth_departments nd1 ON nde.dept_id = nd1.dept_id
    LEFT JOIN nth_dept_manager ndm ON ne.emp_id = ndm.emp_id
    LEFT JOIN nth_departments nd2 on ndm.dept_id = nd2.dept_id
    ORDER BY ne.emp_id ASC";

    try {
        // Execute the query and get all records
        $stmt = $pdo->query($query);
        $allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalRecords = count($allRows);
        
        // Process records in chunks
        $chunkSize = 5000;
        $chunks = array_chunk($allRows, $chunkSize);
        
        // Write data to CSV in chunks
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                fputcsv($output, $row);
            }
            // Flush output buffer to send data to browser
            if (ob_get_level() > 0) {
                ob_flush();
                flush();
            }
        }
        
        // Free up memory
        unset($allRows);
        unset($chunks);
        
    } catch (Exception $e) {
        // Write error to the CSV
        fputcsv($output, ['Error processing data: ' . $e->getMessage()]);
    }

    // Close the CSV file
    fclose($output);
    
    // End output buffering if still active
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    
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
    <title>Export Engineers CSV (Bulk Method)</title>
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
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Export Engineer Employees Data</h1>
        
        <?php echo $connectionStatus; ?>
        
        <p>Click the button below to export CSV data for Engineer employees with 'son' in their name.</p>
        
        <div class="warning">
            <strong>Note:</strong> This method loads all records at once and processes them in chunks. 
            It may require more memory but can be faster for larger datasets.
        </div>
        
        <a href="?export=true" class="btn">Export CSV (Bulk Method)</a>
    </div>
</body>
</html> 