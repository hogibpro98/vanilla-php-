<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';

// Initialize database connection
try {
    $db = new Database();
    $connectionStatus = "<p class='success'>✓ Kết nối cơ sở dữ liệu thành công!</p>";
} catch (Exception $e) {
    $connectionStatus = "<p class='error'>✗ Kết nối thất bại: " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select2 Pagination - Tiếng Việt</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        #employee-name {
            color: #0d6efd;
            text-align: center;
            padding: 10px;
            background-color: #e7f1ff;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .pagination-info {
            background-color: #f0f8ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        .progress {
            height: 20px;
            margin-top: 10px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select2 Pagination (Chunks)</h1>
        
        <?php echo $connectionStatus; ?>
        
        <div id="time-box" class="alert alert-info mb-3" style="display: none;">
            <div id="time-info" class="fw-bold">Thời gian: 0s</div>
        </div>
        
        <form>
            <div class="form-group">
                <label for="ajax-select" class="form-label">Tìm kiếm nhân viên (Phân trang theo chunk):</label>
                <select class="form-select" id="ajax-select"></select>
            </div>
            
            <div id="pagination-info" class="pagination-info">
                <strong>Thông tin tìm kiếm:</strong>
                <div id="search-stats"></div>
                <div class="progress">
                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div class="stats">
                    <div id="chunks-info">Chunk: 0/0</div>
                    <div id="records-info">Bản ghi: 0/0</div>
                </div>
            </div>
            
            <h2 id="employee-name" class="mt-3" style="display: none;"></h2>
            <div id="employee-details" class="mt-3" style="display: none;">
                <h5>Thông tin nhân viên:</h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Mã nhân viên</th>
                            <td id="emp-no"></td>
                            <th>Ngày sinh</th>
                            <td id="birth-date"></td>
                        </tr>
                        <tr>
                            <th>Họ tên</th>
                            <td id="full-name"></td>
                            <th>Giới tính</th>
                            <td id="gender"></td>
                        </tr>
                        <tr>
                            <th>Chức vụ</th>
                            <td id="title"></td>
                            <th>Lương</th>
                            <td id="salary"></td>
                        </tr>
                        <tr>
                            <th>Phòng ban</th>
                            <td id="department"></td>
                            <th>Quản lý phòng</th>
                            <td id="managed-dept"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
        
        <a href="../index.php" class="back-link">Quay lại trang chính</a>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Dịch các văn bản Select2 sang tiếng Việt
        $.fn.select2.defaults.set('language', {
            errorLoading: function() { return 'Không thể tải kết quả.'; },
            inputTooLong: function(args) {
                var overChars = args.input.length - args.maximum;
                return 'Vui lòng xóa ' + overChars + ' ký tự';
            },
            inputTooShort: function(args) {
                var remainingChars = args.minimum - args.input.length;
                return 'Vui lòng nhập thêm ' + remainingChars + ' ký tự';
            },
            loadingMore: function() { return 'Đang tải thêm kết quả…'; },
            maximumSelected: function(args) {
                return 'Bạn chỉ có thể chọn ' + args.maximum + ' mục';
            },
            noResults: function() { return 'Không tìm thấy kết quả'; },
            searching: function() { return 'Đang tìm…'; },
            removeAllItems: function() { return 'Xóa tất cả các mục'; }
        });
        
        // Khởi tạo bộ đếm thời gian toàn cục
        var requestTimers = {};
        
        // Reset pagination info when starting a new search
        $(document).on('select2:open', '#ajax-select', function() {
            $('#pagination-info').hide();
            $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
            $('#chunks-info').text('Chunk: 0/0');
            $('#records-info').text('Bản ghi: 0/0');
            $('#time-info').text('Thời gian: 0s');
        });
        
        // Bắt đầu đếm thời gian khi bắt đầu request
        $(document).on('select2:opening', function() {
            requestTimers.start = new Date().getTime();
            
            // Hiển thị bộ đếm thời gian
            requestTimers.interval = setInterval(function() {
                var elapsedTime = (new Date().getTime() - requestTimers.start) / 1000;
                $('#time-info').text('Đang đợi phản hồi: ' + elapsedTime.toFixed(1) + 's');
                $('#time-box').show();
            }, 100);
        });
        
        // Initialize Select2
        $('#ajax-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Tìm kiếm nhân viên',
            allowClear: true,
            ajax: {
                url: 'get_data.php',
                dataType: 'json',
                delay: 500,
                data: function (params) {
                    // Bắt đầu đếm thời gian khi gọi request
                    if (!requestTimers.requestStart) {
                        requestTimers.requestStart = new Date().getTime();
                    }
                    
                    return {
                        q: params.term || '',
                        last_id: params.last_id || 0
                    };
                },
                processResults: function (data, params) {
                    // Dừng bộ đếm thời gian
                    clearInterval(requestTimers.interval);

                    // Tính thời gian phản hồi
                    var requestEndTime = new Date().getTime();
                    var totalTime = (requestEndTime - requestTimers.start) / 1000;
                    requestTimers = {}; // Reset bộ đếm

                    // Show pagination info
                    if (data && data.total_records > 0) {
                        $('#pagination-info').show();

                        // Update stats
                        $('#search-stats').text('Tìm thấy ' + data.total_records + ' bản ghi với từ khóa "' + (params.term || '') + '"');

                        // Hiển thị thông tin về thời gian thực thi
                        // processed_chunks is now always 1 in the new backend logic
                        $('#chunks-info').text('Chunks đã xử lý: 1');
                        $('#time-info').html('<strong>Thời gian:</strong> Server: ' + data.execution_time + 's | Tổng: ' + totalTime.toFixed(2) + 's');
                        $('#time-box').show();
                        $('#records-info').text('Kết quả: ' + data.items.length + ' / ' + data.total_records);

                        // Cập nhật thanh tiến trình
                        var progress = 100;
                        if (data.has_more) {
                            progress = Math.min(Math.floor((data.items.length / data.total_records) * 100), 90);
                        }
                        $('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).text(progress.toFixed(0) + '%');
                    } else {
                        $('#pagination-info').hide();
                    }

                    // Set up pagination for the next request
                    params.last_id = data.last_id;

                    // Note: employee_data is no longer included in the initial search results
                    return {
                        results: data.items || [],
                        pagination: {
                            more: data.has_more
                        }
                    };
                },
                error: function() {
                    // Dừng bộ đếm thời gian nếu có lỗi
                    clearInterval(requestTimers.interval);
                    $('#time-info').text('Lỗi kết nối!');
                    $('#time-box').show();
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection
        });

        // Định dạng kết quả hiển thị
        function formatEmployee(employee) {
            if (employee.loading) {
                return employee.text;
            }

            // Only display basic info in the dropdown list
            var $container = $(
                '<div class="select2-result-employee">' +
                    '<div class="select2-result-employee__name">' + employee.text + '</div>' +
                '</div>'
            );

            return $container;
        }

        // Định dạng item đã chọn
        function formatEmployeeSelection(employee) {
            return employee.text || employee.id;
        }

        // Hiển thị chi tiết nhân viên khi chọn
        $('#ajax-select').on('select2:select', function (e) {
            var data = e.params.data;
            if (data && data.id) {
                // Fetch full employee details via a new AJAX request
                $.ajax({
                    url: 'get_data.php', // Use the same endpoint
                    dataType: 'json',
                    data: {
                        emp_id: data.id // Send the selected employee ID
                    },
                    success: function(detailData) {
                        if (detailData && detailData.employee_data) {
                            var emp = detailData.employee_data;

                            // Hiển thị tên nhân viên
                            $('#employee-name').text((emp.first_name || '') + ' ' + (emp.last_name || '')).show();

                            // Cập nhật thông tin chi tiết
                            $('#emp-no').text(emp.emp_no || '');
                            $('#birth-date').text(emp.birth_date || '');
                            $('#full-name').text((emp.first_name || '') + ' ' + (emp.last_name || ''));
                            $('#gender').text(emp.gender || '');
                            $('#title').text(emp.title || '');
                            $('#salary').text(emp.salary || '');
                            $('#department').text(emp['nd1.dept_name'] || '');
                            $('#managed-dept').text(emp['nd2.dept_name'] || '');

                            // Hiển thị bảng chi tiết
                            $('#employee-details').show();
                        } else {
                            // Handle case where details are not found
                            $('#employee-name').text('Không tìm thấy chi tiết nhân viên').show();
                            $('#employee-details').hide();
                        }
                    },
                    error: function() {
                        // Handle AJAX error for details fetch
                        $('#employee-name').text('Lỗi khi tải chi tiết nhân viên').show();
                        $('#employee-details').hide();
                    }
                });
            }
        });

        // Ẩn chi tiết khi xóa lựa chọn
        $('#ajax-select').on('select2:clear', function (e) {
            $('#employee-name').hide();
            $('#employee-details').hide();
            $('#pagination-info').hide();
            $('#time-box').hide();
        });
    });
    </script>
</body>
</html> 