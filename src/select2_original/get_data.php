<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Set the response header to JSON
header('Content-Type: application/json');

try {
    $db = new Database();
    
    // Comprehensive query to get all employee data
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
    ORDER BY e.emp_id DESC
    -- LIMIT 10 OFFSET 2000000
    -- LIMIT 10 OFFSET 1000000
    LIMIT 10000 OFFSET 0
SQL;
    
    // Get all employees in one query
    $results = $db->select($query);
    
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
    
    // Close the database connection
    $db->close();
    
    // Return all results
    echo json_encode([
        'items' => $formattedResults
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

?>
