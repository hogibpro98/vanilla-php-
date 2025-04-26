<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Get and validate parameters
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$lastEmpId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$limit = 10; // Fixed limit

try {
    $db = new Database();
    $params = [];
    
    // If this is the first request (last_id = 0), get the maximum employee ID
    if ($lastEmpId === 0) {
        // Get the highest employee ID to start pagination from top
        $maxIdQuery = "SELECT MAX(emp_id) as max_id FROM nth_employees";
        $maxIdResult = $db->selectOne($maxIdQuery);
        $maxId = isset($maxIdResult['max_id']) ? (int)$maxIdResult['max_id'] + 1 : 1;
        
        // Start from one above the maximum ID to include all records
        $innerCondition = "WHERE e.emp_id < ?";
        $params[] = $maxId;
    } else {
        // For subsequent pages, use the provided last_id
        $innerCondition = "WHERE e.emp_id < ?";
        $params[] = $lastEmpId;
    }
    
    // Add search condition if search term exists
    if (!empty($searchTerm)) {
        $searchTerm = '%' . $searchTerm . '%';
        $innerCondition .= " AND (e.last_name LIKE ? OR e.first_name LIKE ? OR e.emp_no LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Create query with all necessary LEFT JOINs
    $query = <<<SQL
    SELECT
        e.emp_id,
        e.emp_no,
        e.birth_date,
        e.first_name,
        e.last_name,
        e.gender,
        t.title,
        s.salary,
        d1.dept_name as 'nd1.dept_name',
        d2.dept_name as 'nd2.dept_name'
    FROM nth_employees e
    LEFT JOIN nth_titles t ON e.emp_id = t.emp_id
    LEFT JOIN nth_salaries s ON e.emp_id = s.emp_id
    LEFT JOIN nth_dept_emp de ON e.emp_id = de.emp_id
    LEFT JOIN nth_departments d1 ON de.dept_id = d1.dept_id
    LEFT JOIN nth_dept_manager dm ON e.emp_id = dm.emp_id
    LEFT JOIN nth_departments d2 ON dm.dept_id = d2.dept_id
    {$innerCondition}
    ORDER BY e.emp_id DESC
    LIMIT {$limit}
SQL;
    
    // Execute the query using select() method
    $results = $db->select($query, $params);
    
    // Get the last employee ID for next pagination (smallest ID in the result set)
    $lastId = !empty($results) ? end($results)['emp_id'] : 0;
    
    // Format results for Select2
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            'id' => (int)$row['emp_id'],
            'text' => htmlspecialchars($row['emp_no'] . ' - ' . $row['first_name'] . ' ' . $row['last_name']),
            'employee_data' => array_map(function($value) {
                return is_string($value) ? htmlspecialchars($value) : $value;
            }, $row)
        ];
    }
    
    // Return the results
    echo json_encode([
        'items' => $formattedResults,
        'has_more' => count($results) >= $limit,
        'last_id' => $lastId
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // Return appropriate error response
    echo json_encode([
        'error' => true,
        'message' => 'Lỗi truy vấn dữ liệu',
        'debug_info' => [
            'error_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
