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
    $formattedResults = []; // Kết quả cuối cùng
    $totalRecords = 0; // Tổng số bản ghi theo điều kiện tìm kiếm
    $processedChunks = 0; // Số chunk đã xử lý
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
    
    // Tạo câu truy vấn để lấy tất cả ID phù hợp
    $idQuery = "SELECT emp_id FROM nth_employees WHERE emp_id < ? ";
    $idParams = [$currentLastId];
    
    if (!empty($searchTerm)) {
        $searchPattern = '%' . $searchTerm . '%';
        $idQuery .= " AND (last_name LIKE ? OR first_name LIKE ? OR emp_no LIKE ?)";
        $idParams[] = $searchPattern;
        $idParams[] = $searchPattern;
        $idParams[] = $searchPattern;
    }
    
    $idQuery .= " ORDER BY emp_id DESC LIMIT " . ($chunkSize * 2); // Lấy nhiều hơn để đảm bảo có đủ dữ liệu
    
    // Thực hiện truy vấn lấy tất cả ID phù hợp
    $stmt = $db->query($idQuery, $idParams);
    $allIds = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $allIds[] = $row['emp_id'];
    }
    $stmt->closeCursor();
    
    // Chia thành các chunk nhỏ để xử lý
    $idChunks = array_chunk($allIds, $chunkSize);
    $processedChunks = count($idChunks);
    
    // Biến theo dõi ID nhỏ nhất
    $smallestIdInResults = null;
    
    // Xử lý từng chunk ID
    foreach ($idChunks as $chunkIndex => $idChunk) {
        // Nếu đã đủ số lượng kết quả cần tìm, dừng lại
        if (count($formattedResults) >= $limit) {
            break;
        }
        
        // Chuẩn bị danh sách ID cho truy vấn IN
        $placeholders = implode(',', array_fill(0, count($idChunk), '?'));
        
        // Truy vấn chi tiết cho các ID trong chunk hiện tại
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
        WHERE ne.emp_id IN ({$placeholders})
        ORDER BY ne.emp_id DESC
SQL;
        
        // Thực hiện truy vấn chi tiết
        $detailStmt = $db->query($detailQuery, $idChunk);
        
        // Xử lý từng dòng kết quả
        while ($row = $detailStmt->fetch(PDO::FETCH_ASSOC)) {
            // Cập nhật ID nhỏ nhất cho phân trang
            if ($smallestIdInResults === null || $row['emp_id'] < $smallestIdInResults) {
                $smallestIdInResults = $row['emp_id'];
            }
            
            // Thêm vào kết quả nếu chưa đủ limit
            if (count($formattedResults) < $limit) {
                $formattedResults[] = [
                    'id' => (int)$row['emp_id'],
                    'text' => htmlspecialchars($row['emp_no'] . ' - ' . $row['first_name'] . ' ' . $row['last_name']),
                    'employee_data' => array_map(function($value) {
                        return is_string($value) ? htmlspecialchars($value) : $value;
                    }, $row)
                ];
            }
        }
        
        // Giải phóng tài nguyên
        $detailStmt->closeCursor();
    }
    
    // Xác định last_id cho lần gọi tiếp theo
    $nextLastId = $smallestIdInResults !== null ? $smallestIdInResults : 0;
    
    // Xác định có còn dữ liệu không
    $hasMore = $nextLastId > 0 && $totalRecords > 0 && count($formattedResults) == $limit && $nextLastId > 1;
    
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
