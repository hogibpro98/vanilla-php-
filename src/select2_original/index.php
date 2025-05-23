<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select2 Local Data - Tiếng Việt</title>
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
        #loading-message {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            color: #6c757d;
        }
        #timer {
            display: inline-block;
            margin-left: 5px;
            font-weight: bold;
            color: #dc3545;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 3px solid rgba(0, 123, 255, 0.3);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select2 với Dữ liệu Cục bộ</h1>
        
        <div id="loading-message">
            <div class="loading-spinner"></div>
            Đang tải dữ liệu nhân viên... 
            <span id="timer">0</span> giây
        </div>
        
        <form style="display: none;" id="employee-form">
            <div class="form-group">
                <label for="employee-select" class="form-label">Tìm kiếm nhân viên:</label>
                <select class="form-select" id="employee-select"></select>
            </div>
            
            <div id="employee-name" class="mt-3" style="display: none;"></div>
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
        // Start timer for loading
        var seconds = 0;
        var timer = setInterval(function() {
            seconds++;
            $('#timer').text(seconds);
        }, 1000);
        
        // Time tracking
        var startTime = new Date();
        
        // Tải dữ liệu nhân viên một lần khi trang tải
        $.ajax({
            url: 'get_data.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Stop the timer
                clearInterval(timer);
                
                // Calculate elapsed time
                var endTime = new Date();
                var elapsedTime = (endTime - startTime) / 1000; // in seconds
                
                // Ẩn thông báo đang tải
                $('#loading-message').html('<div class="success">Đã tải xong dữ liệu trong ' + elapsedTime.toFixed(2) + ' giây.</div>');
                
                // Hiển thị form sau khi tải dữ liệu
                $('#employee-form').show();
                
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
                
                // Khởi tạo Select2 với dữ liệu đã tải
                $('#employee-select').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Tìm kiếm nhân viên',
                    allowClear: true,
                    data: response.items || [],
                    templateResult: formatEmployee,
                    templateSelection: formatEmployeeSelection
                });
                
                // Định dạng kết quả hiển thị
                function formatEmployee(employee) {
                    if (employee.loading) {
                        return employee.text;
                    }
                    
                    var $container = $(
                        '<div class="select2-result-employee">' +
                            '<div class="select2-result-employee__name">' + employee.text + '</div>' +
                            (employee.employee_data && employee.employee_data.title ? 
                                '<div class="select2-result-employee__title text-muted small">' + 
                                    employee.employee_data.title + 
                                '</div>' : '') +
                        '</div>'
                    );
                    
                    return $container;
                }
                
                // Định dạng item đã chọn
                function formatEmployeeSelection(employee) {
                    return employee.text || employee.id;
                }
                
                // Hiển thị chi tiết nhân viên khi chọn
                $('#employee-select').on('select2:select', function (e) {
                    var data = e.params.data;
                    if (data && data.employee_data) {
                        var emp = data.employee_data;
                        
                        // Hiển thị tên nhân viên trong thẻ h2
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
                    }
                });
                
                // Ẩn chi tiết khi xóa lựa chọn
                $('#employee-select').on('select2:clear', function (e) {
                    $('#employee-name').hide();
                    $('#employee-details').hide();
                });
                
                console.log('Đã tải ' + (response.items ? response.items.length : 0) + ' nhân viên trong ' + elapsedTime.toFixed(2) + ' giây');
            },
            error: function(xhr, status, error) {
                // Stop the timer
                clearInterval(timer);
                
                $('#loading-message').html('<div class="error">Lỗi khi tải dữ liệu: ' + error + '</div>');
                console.error('Lỗi AJAX:', xhr.responseText);
            }
        });
    });
    </script>
</body>
</html>
