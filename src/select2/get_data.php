<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Lấy và validate các tham số
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$lastEmpId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$limit = 10; // Giới hạn cố định

try {
    $db = new Database();
    
    // Xây dựng điều kiện tìm kiếm
    $searchCondition = "";
    $params = [];
    
    // Điều kiện emp_id
    $searchCondition .= "WHERE ne.emp_id > :emp_id";
    $params['emp_id'] = $lastEmpId;
    
    // Thêm điều kiện tìm kiếm theo tên
    if (!empty($searchTerm)) {
        $searchCondition .= " AND (ne.last_name LIKE :search_term OR ne.first_name LIKE :search_term OR nt.title LIKE :search_term)";
        $searchParam = `'%' . $searchTerm . '%'`;
        $params['search_term'] = $searchParam;
    }
    
    // Tạo truy vấn theo mẫu yêu cầu
    $query = <<<SQL
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
    FROM nth_employees ne
    INNER JOIN nth_titles nt ON ne.emp_id = nt.emp_id
    LEFT JOIN nth_salaries ns ON ne.emp_id = ns.emp_id
    LEFT JOIN nth_dept_emp nde ON ne.emp_id = nde.emp_id
    LEFT JOIN nth_departments nd1 ON nde.dept_id = nd1.dept_id
    LEFT JOIN nth_dept_manager ndm ON ne.emp_id = ndm.emp_id
    LEFT JOIN nth_departments nd2 on ndm.dept_id = nd2.dept_id
    {$searchCondition}
    LIMIT 10
SQL;
    
    // Thêm param cho LIMIT
    $params[] = $limit;
    
    // Ghi log câu truy vấn để debug
    error_log("SQL Query: " . str_replace('{$searchCondition}', $searchCondition, $query));
    error_log("Params: " . json_encode($params));
    // Ghi log câu truy vấn và tham số vào file
    $logMessage = date('Y-m-d H:i:s') . " - " . str_replace('{$searchCondition}', $searchCondition, $query) . " - Query with last_id: " . $lastEmpId . ", Limit: " . $limit . ", Search key: " . ($searchParam ?? 'none') . "\n";
    $logFile = __DIR__ . '/logs/select2_query.txt';
    
    // Đảm bảo thư mục logs tồn tại
    // if (!is_dir(dirname($logFile))) {
    //     mkdir(dirname($logFile), 0755, true);
    // }
    
    // // Ghi log vào file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // Thực thi truy vấn
    // $stmt = $db->query($query, $params);
    $stmt = $db->query($query, $params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy emp_id cuối cùng cho lần truy vấn tiếp theo
    $lastId = !empty($results) ? end($results)['emp_id'] : $lastEmpId;
    
    // Định dạng kết quả cho Select2
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
    
    // Trả về kết quả
    echo json_encode([
        'items' => $formattedResults,
        'has_more' => count($results) >= $limit,
        'last_id' => $lastId
    ]);
    
} catch (Exception $e) {
    // Log lỗi chi tiết
    error_log("Database error: " . $e->getMessage());
    
    // Kiểm tra xem có phải đang ở môi trường phát triển không
    $isDevelopment = true; // Đặt thành false khi triển khai production
    
    if ($isDevelopment) {
        // Trả về thông tin lỗi chi tiết cho môi trường phát triển
        echo json_encode([
            'error' => true,
            'message' => 'Lỗi truy vấn dữ liệu',
            'debug_info' => [
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    } else {
        // Trả về thông báo chung cho người dùng
        echo json_encode([
            'error' => true,
            'message' => 'Đã xảy ra lỗi khi truy vấn dữ liệu'
        ]);
    }
}
