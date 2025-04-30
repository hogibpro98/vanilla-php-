<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Get and validate parameters
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$lastEmpId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$limit = 10; // Số lượng bản ghi cần tìm
$chunkSize = 5000; // Kích thước chunk xử lý

// Start time tracking for performance measurement
$startTime = microtime(true);

try {
    $db = new Database();

    // Check if a specific employee ID is requested for details
    if (isset($_GET['emp_id'])) {
        $empId = (int)$_GET['emp_id'];

        // Query to get full details for a specific employee
        $detailQuery = <<<SQL
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
            nth_employees ne
        LEFT JOIN nth_titles nt ON ne.emp_id = nt.emp_id
        LEFT JOIN nth_salaries ns ON ne.emp_id = ns.emp_id
        LEFT JOIN nth_dept_emp nde ON ne.emp_id = nde.emp_id
        LEFT JOIN nth_departments nd1 ON nde.dept_id = nd1.dept_id
        LEFT JOIN nth_dept_manager ndm ON ne.emp_id = ndm.emp_id
        LEFT JOIN nth_departments nd2 on ndm.dept_id = nd2.dept_id
        WHERE ne.emp_id = ?
        LIMIT 1
SQL;

        $employeeDetails = $db->selectOne($detailQuery, [$empId]);

        // Format and return the details
        $response = [
            'employee_data' => $employeeDetails ? array_map(function($value) {
                return is_string($value) ? htmlspecialchars($value) : $value;
            }, $employeeDetails) : null
        ];

        echo json_encode($response);

    } else {
        // Existing logic for search and pagination
        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
        $lastEmpId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $limit = 10; // Số lượng bản ghi cần tìm
        $chunkSize = 5000; // Kích thước chunk xử lý - no longer used in main query logic

        $formattedResults = []; // Kết quả cuối cùng
        $totalRecords = 0; // Tổng số bản ghi theo điều kiện tìm kiếm
        $processedChunks = 1; // Số chunk đã xử lý - now always 1

        $currentLastId = $lastEmpId; // ID cuối cùng hiện tại để theo dõi phân trang

        // Mặc định bắt đầu từ ID lớn nhất nếu last_id = 0
        if ($currentLastId === 0) {
            $maxIdQuery = "SELECT MAX(emp_id) as max_id FROM nth_employees";
            $maxIdResult = $db->selectOne($maxIdQuery);
            $currentLastId = isset($maxIdResult['max_id']) ? (int)$maxIdResult['max_id'] + 1 : PHP_INT_MAX;
        }

        // Đếm tổng số bản ghi theo điều kiện tìm kiếm
        // Chỉ đếm khi ở request đầu tiên để tối ưu hiệu suất
        if ($lastEmpId === 0) {
            $whereClause = "WHERE emp_id > 0";
            $countParams = [];

            if (!empty($searchTerm)) {
                $searchPattern = '%' . $searchTerm . '%';
                $whereClause .= " AND (last_name LIKE ? OR first_name LIKE ? OR emp_no LIKE ?)";
                $countParams[] = $searchPattern;
                $countParams[] = $searchPattern;
                $countParams[] = $searchPattern;
            }

            $countQuery = "SELECT COUNT(*) as total FROM nth_employees {$whereClause}";
            $countResult = $db->selectOne($countQuery, $countParams);
            $totalRecords = $countResult['total'];
        }

        // Create the main query to fetch data with pagination and search
        $mainQuery = <<<SQL
        SELECT
            ne.emp_id,
            ne.emp_no,
            ne.first_name,
            ne.last_name
        FROM
            nth_employees ne
        WHERE ne.emp_id < ?
        SQL;

        $mainParams = [$currentLastId];

        if (!empty($searchTerm)) {
            $searchPattern = '%' . $searchTerm . '%';
            $mainQuery .= " AND (ne.last_name LIKE ? OR ne.first_name LIKE ? OR ne.emp_no LIKE ?)";
            $mainParams[] = $searchPattern;
            $mainParams[] = $searchPattern;
            $mainParams[] = $searchPattern;
        }

        $mainQuery .= " ORDER BY ne.emp_id DESC LIMIT " . ($limit + 1);

        // Execute the main query
        $stmt = $db->query($mainQuery, $mainParams);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $formattedResults = [];
        $nextLastId = 0;
        $hasMore = false;

        // Process results
        if (count($results) > $limit) {
            // We fetched limit + 1, so there are more results
            $hasMore = true;
            // The next last_id is the ID of the last item in the current page (which is the limit-th item in the fetched results)
            $nextLastId = (int)$results[$limit - 1]['emp_id'];
            // Take only the first 'limit' results for the current page
            $results = array_slice($results, 0, $limit);
        } elseif (count($results) > 0) {
             // Fetched less than or equal to limit, no more results
             $hasMore = false;
             // The next last_id is the ID of the last item fetched
             $nextLastId = (int)end($results)['emp_id'];
        } else {
            // No results found
            $hasMore = false;
            $nextLastId = 0;
        }

        // Format the results for Select2
        foreach ($results as $row) {
            $formattedResults[] = [
                'id' => (int)$row['emp_id'],
                'text' => htmlspecialchars($row['emp_no'] . ' - ' . $row['first_name'] . ' ' . $row['last_name']),
                // employee_data is NOT included here for performance
            ];
        }

        // Giải phóng bộ nhớ
        if (method_exists($db, 'close')) {
            $db->close();
        }
        $db = null;

        // Tính thời gian thực thi
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Trả về kết quả
        $response = [
            'items' => $formattedResults,
            'has_more' => $hasMore,
            'last_id' => $nextLastId,
            'total_records' => $totalRecords,
            'execution_time' => round($executionTime, 3), // Gửi thời gian thực thi về client
            'processed_chunks' => $processedChunks
        ];

        echo json_encode($response);
    }

} catch (Exception $e) {
    // Log lỗi
    error_log("Database error: " . $e->getMessage());

    // Trả về thông báo lỗi
    echo json_encode([
        'error' => true,
        'message' => 'Lỗi truy vấn dữ liệu: ' . $e->getMessage()
    ]);
}

// Hàm hỗ trợ định dạng bytes thành định dạng dễ đọc
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
