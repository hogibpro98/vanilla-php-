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
    <title>Select2 - Tiếng Việt</title>
    
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Select2 - Tiếng Việt</h1>
        
        <?php echo $connectionStatus; ?>
        
        <form>
            <div class="form-group">
                <label for="basic-select" class="form-label">Select2 Cơ bản:</label>
                <select class="form-select" id="basic-select">
                    <option value="">-- Chọn một mục --</option>
                    <option value="1">Lựa chọn 1</option>
                    <option value="2">Lựa chọn 2</option>
                    <option value="3">Lựa chọn 3</option>
                    <option value="4">Lựa chọn 4</option>
                    <option value="5">Lựa chọn 5</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="multiple-select" class="form-label">Select2 Nhiều lựa chọn:</label>
                <select class="form-select" id="multiple-select" multiple>
                    <option value="1">Lựa chọn 1</option>
                    <option value="2">Lựa chọn 2</option>
                    <option value="3">Lựa chọn 3</option>
                    <option value="4">Lựa chọn 4</option>
                    <option value="5">Lựa chọn 5</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ajax-select" class="form-label">Tìm kiếm nhân viên:</label>
                <select class="form-select" id="ajax-select"></select>
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
        // Vietnamese translations for Select2
        const vietnameseLanguage = {
            errorLoading: () => 'Không thể tải kết quả.',
            inputTooLong: args => `Vui lòng xóa ${args.input.length - args.maximum} ký tự`,
            inputTooShort: args => `Vui lòng nhập thêm ${args.minimum - args.input.length} ký tự`,
            loadingMore: () => 'Đang tải thêm kết quả…',
            maximumSelected: args => `Bạn chỉ có thể chọn ${args.maximum} mục`,
            noResults: () => 'Không tìm thấy kết quả',
            searching: () => 'Đang tìm…',
            removeAllItems: () => 'Xóa tất cả các mục'
        };
        
        // Set default language
        $.fn.select2.defaults.set('language', vietnameseLanguage);
        
        // Common Select2 settings
        const commonSettings = {
            theme: 'bootstrap-5',
            allowClear: true
        };
        
        // Basic Select2
        $('#basic-select').select2({
            ...commonSettings,
            placeholder: 'Chọn một mục'
        });
        
        // Multiple Select2
        $('#multiple-select').select2({
            ...commonSettings,
            placeholder: 'Chọn một hoặc nhiều mục'
        });
        
        // Format employee result item
        function formatEmployee(employee) {
            if (employee.loading) {
                return employee.text;
            }
            
            return $(`
                <div class="select2-result-employee">
                    <div class="select2-result-employee__name">${employee.text}</div>
                    ${employee.employee_data && employee.employee_data.title ? 
                        `<div class="select2-result-employee__title text-muted small">
                            ${employee.employee_data.title}
                        </div>` : ''}
                </div>
            `);
        }
        
        // Format selected employee item
        function formatEmployeeSelection(employee) {
            return employee.text || employee.id;
        }
        
        // Display employee details
        function displayEmployeeDetails(data) {
            if (data && data.employee_data) {
                const emp = data.employee_data;
                
                // Show employee name
                $('#employee-name')
                    .text(`${emp.first_name || ''} ${emp.last_name || ''}`)
                    .show();
                
                // Update details
                $('#emp-no').text(emp.emp_no || '');
                $('#birth-date').text(emp.birth_date || '');
                $('#full-name').text(`${emp.first_name || ''} ${emp.last_name || ''}`);
                $('#gender').text(emp.gender || '');
                $('#title').text(emp.title || '');
                $('#salary').text(emp.salary || '');
                $('#department').text(emp['nd1.dept_name'] || '');
                $('#managed-dept').text(emp['nd2.dept_name'] || '');
                
                // Show details table
                $('#employee-details').show();
            }
        }
        
        // AJAX Select2
        $('#ajax-select').select2({
            ...commonSettings,
            placeholder: 'Tìm kiếm nhân viên',
            ajax: {
                url: 'get_data.php',
                dataType: 'json',
                delay: 500,
                data: function (params) {
                    return {
                        q: params.term || '',
                        last_id: params.lastId || 0
                    };
                },
                processResults: function (data, params) {
                    // Handle errors
                    if (data.error) {
                        console.error('Error:', data.message);
                        return { results: [] };
                    }
                    
                    // Save last_id for next request
                    params.lastId = data.last_id;
                    
                    return {
                        results: data.items || [],
                        pagination: {
                            more: data.has_more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection
        })
        .on('select2:select', function(e) {
            displayEmployeeDetails(e.params.data);
        })
        .on('select2:clear', function() {
            $('#employee-name').hide();
            $('#employee-details').hide();
        });
    });
    </script>
</body>
</html>
