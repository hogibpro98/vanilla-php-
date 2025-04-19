# Bài tập Thực hành Bảo mật PHP (Theo từng chương "Pro PHP Security")

Dưới đây là các bài tập thực hành được thiết kế dựa trên nội dung từng chương của cuốn sách "Pro PHP Security", giúp củng cố kiến thức và kỹ năng bảo mật cho lập trình viên PHP & MySQL.

## Chương 1: Why Is Secure Programming a Concern? (Tại sao Lập trình An toàn là Mối quan tâm?)

1.  **Phân tích Rủi ro:** Chọn một ứng dụng web bạn quen thuộc (ví dụ: diễn đàn, cửa hàng trực tuyến đơn giản, blog). Liệt kê ít nhất 5 mối đe dọa bảo mật tiềm ẩn mà ứng dụng đó có thể gặp phải. Giải thích ngắn gọn từng mối đe dọa.
2.  **Thảo luận Nguyên tắc:** Thảo luận về 5 thói quen tốt của lập trình viên có ý thức bảo mật được nêu trong chương (Không gì 100% an toàn, Không tin tưởng input, Phòng thủ theo chiều sâu, Đơn giản dễ bảo mật hơn, Peer review). Cho ví dụ cụ thể về cách một trong các thói quen này có thể ngăn chặn một cuộc tấn công thực tế.
3.  **Nghiên cứu Tình huống:** Tìm kiếm một vụ vi phạm dữ liệu hoặc tấn công website lớn gần đây. Tóm tắt vụ việc và phân tích xem những nguyên tắc bảo mật cơ bản nào có thể đã bị bỏ qua dẫn đến vụ tấn công.

## Chương 2: Validating and Sanitizing User Input (Xác thực và Làm sạch Đầu vào Người dùng)

1.  **Validation Thực tế (Email):** Viết một hàm PHP `validate_email($email)` kiểm tra xem một chuỗi có phải là định dạng email hợp lệ không và trả về `true`/`false`. Sử dụng hàm `filter_var` của PHP nếu có thể.
2.  **Form Validation (Đăng ký):** Xây dựng một form HTML đơn giản cho phép người dùng nhập:
    * `username`: Bắt buộc, chỉ chứa chữ cái (a-z, A-Z) và số (0-9), độ dài từ 6 đến 20 ký tự.
    * `password`: Bắt buộc, ít nhất 8 ký tự, phải chứa ít nhất một chữ hoa, một chữ thường và một chữ số.
    * `email`: Bắt buộc, phải có định dạng email hợp lệ.
    Viết code PHP xử lý dữ liệu `$_POST` từ form này. Thực hiện validate cho từng trường theo yêu cầu. Nếu bất kỳ trường nào không hợp lệ, hãy hiển thị lại form cùng với thông báo lỗi cụ thể cho từng trường bị lỗi.
3.  **Sanitization (HTML Output):** Viết hàm `sanitize_for_html($string)` sử dụng `htmlentities` để làm sạch một chuỗi trước khi hiển thị nó trong trang HTML. Đảm bảo xử lý cả dấu nháy đơn và kép (`ENT_QUOTES`) và sử dụng encoding `UTF-8`.

## Chương 3: Preventing SQL Injection (Ngăn chặn SQL Injection)

1.  **Prepared Statements:** Cho đoạn code PHP sau sử dụng hàm `mysql_*` (không an toàn). Hãy viết lại đoạn code này bằng cách sử dụng **Prepared Statements** với **MySQLi** hoặc **PDO** để lấy thông tin người dùng một cách an toàn dựa trên `$_GET['user_id']`.

    ```php
    <?php
    // Kết nối CSDL cũ (giả sử đã có)
    // $conn = mysql_connect(/*...*/);
    // mysql_select_db(/*...*/);

    // Code cũ không an toàn
    $userId = $_GET['user_id']; // Rất nguy hiểm!

    // Thiếu bước validate quan trọng!
    if (!ctype_digit($userId)) {
        die("Invalid User ID format");
    }

    $query = "SELECT username, email FROM users WHERE id = " . $userId;
    $result = mysql_query($query);

    if ($result && mysql_num_rows($result) > 0) {
        $user = mysql_fetch_assoc($result);
        // Cần escape output để chống XSS nữa!
        echo "Username: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'];
    } else {
        echo "User not found.";
    }

    // mysql_close($conn);
    ?>
    ```
2.  **Phân tích Lỗ hổng (ORDER BY):** Xem xét câu lệnh SQL sau được tạo động từ input: `SELECT * FROM products WHERE category = '{$_GET['category']}' ORDER BY {$_GET['sort_column']} {$_GET['sort_dir']}`. Chỉ ra các điểm tiềm ẩn SQL Injection (đặc biệt là ở `ORDER BY`) và cách khắc phục cho từng điểm (ví dụ: dùng whitelist cho tên cột và hướng sắp xếp).
3.  **Kiểm tra Whitelist:** Viết một hàm PHP kiểm tra giá trị `$column_name` nhận từ người dùng có nằm trong danh sách các cột được phép sắp xếp (`['name', 'price', 'date_added']`) hay không trước khi đưa vào mệnh đề `ORDER BY`.

## Chương 4: Preventing Cross-Site Scripting (Ngăn chặn XSS)

1.  **Output Escaping:** Một trang web hiển thị bình luận của người dùng lấy từ CSDL. Sửa đoạn code sau để đảm bảo an toàn trước XSS:
    ```php
    <?php
    // Giả sử $comment_text lấy từ CSDL
    // $comment_text = get_comment_from_db($comment_id);

    // Code cũ có lỗ hổng XSS
    echo "<div class='comment'>";
    echo "<p><strong>User says:</strong></p>";
    echo "<p>" . $comment_text . "</p>"; // Lỗ hổng ở đây
    echo "</div>";
    ?>
    ```
    Yêu cầu: Sử dụng `htmlentities($comment_text, ENT_QUOTES, 'UTF-8')`.
2.  **URL Sanitization:** Cho một chức năng cho phép người dùng nhập URL website cá nhân. Viết code PHP để kiểm tra URL này:
    * Chỉ sử dụng scheme `http` hoặc `https`.
    * Không chứa mã Javascript (ví dụ: `javascript:...`).
    * Sử dụng `filter_var` với `FILTER_VALIDATE_URL` và kiểm tra thêm scheme.
3.  **Thảo luận về HTML Input:** Nếu ứng dụng của bạn *thực sự cần* cho phép người dùng nhúng thẻ `<img>` vào nội dung họ gửi lên (ví dụ: bài đăng forum), hãy thảo luận về các rủi ro (XSS qua thuộc tính `onerror`, `src` trỏ đến script độc hại, tracking pixel) và đề xuất các biện pháp giảm thiểu rủi ro đó (lọc thuộc tính, chỉ cho phép `src` trỏ đến domain cụ thể, dùng proxy ảnh).

## Chương 5: Preventing Remote Execution (Ngăn chặn Thực thi Mã Từ xa)

1.  **Shell Argument Escaping:** Bạn cần thực thi lệnh `grep` để tìm kiếm một chuỗi do người dùng cung cấp trong một file cố định trên server. Viết code PHP nhận chuỗi tìm kiếm từ `$_POST['search_term']`, sử dụng `escapeshellarg` để escape chuỗi đó, và thực thi lệnh `grep "/path/to/fixed/file.log" -e $escaped_term` một cách an toàn bằng `shell_exec`.
2.  **Phân tích Code (File Inclusion):** Tìm lỗ hổng bảo mật (Local File Inclusion - LFI) trong đoạn code sau và đề xuất cách sửa an toàn (ví dụ: dùng whitelist các giá trị cho `$page`).
    ```php
    <?php
    $page = isset($_GET['p']) ? $_GET['p'] : 'home';
    // Giả sử không có kiểm tra $page đầy đủ
    // Kẻ tấn công có thể nhập $page = '../../../../etc/passwd'
    include('pages/' . $page . '.html');
    ?>
    ```
3.  **File Upload Security:** Thiết kế quy trình xử lý upload ảnh đại diện chi tiết:
    * Kiểm tra lỗi upload (`$_FILES['avatar']['error']`).
    * Kiểm tra kích thước file (`$_FILES['avatar']['size']`).
    * Kiểm tra extension file (chỉ cho phép `.jpg`, `.png`, `.gif`).
    * **Quan trọng:** Kiểm tra MIME type thực tế của file (dùng `finfo_open`).
    * Generate tên file mới, duy nhất để tránh ghi đè và LFI qua tên file.
    * Sử dụng `is_uploaded_file` trước khi di chuyển.
    * Di chuyển file đến một thư mục *ngoài* web root bằng `move_uploaded_file`.
    * Viết code PHP thực hiện các bước này.

## Chương 6: Enforcing Security for Temporary Files (Bảo mật File Tạm)

1.  **Tạo File Tạm An Toàn:** Viết code PHP tạo một file tạm thời trong thư mục hệ thống (`sys_get_temp_dir()`) với tên file ngẫu nhiên, khó đoán (sử dụng `tempnam` hoặc `uniqid` với prefix an toàn) và set quyền chỉ cho phép chủ sở hữu đọc/ghi (ví dụ: `0600`) bằng `chmod`.
2.  **Kiểm tra File Upload:** Bổ sung vào bài tập 3 của Chương 5: Trước khi gọi `move_uploaded_file`, hãy sử dụng `is_uploaded_file($_FILES['avatar']['tmp_name'])` để đảm bảo file đó thực sự là file được upload qua HTTP POST, không phải là một file đã tồn tại trên server mà kẻ tấn công cố tình trỏ vào.
3.  **Race Condition:** Mô tả một kịch bản tấn công Race Condition có thể xảy ra khi nhiều request cùng cố gắng ghi vào *cùng một* file tạm (ví dụ: file cache chung). Làm thế nào để sử dụng file locking (`flock` với `LOCK_EX`) trong PHP để ngăn chặn việc ghi đè dữ liệu không mong muốn trong trường hợp này?

## Chương 7: Preventing Session Hijacking (Ngăn chặn Chiếm đoạt Session)

1.  **Cấu hình Session An Toàn:** Viết các lệnh `ini_set` hoặc cấu hình trong `php.ini` (nếu có thể) để đảm bảo các thiết lập sau cho session:
    * Chỉ sử dụng cookie: `session.use_only_cookies = 1`
    * Không truyền session ID qua URL: `session.use_trans_sid = 0`
    * Cookie session chỉ gửi qua HTTPS: `session.cookie_secure = 1`
    * Cookie session không thể truy cập bằng Javascript: `session.cookie_httponly = 1`
    * Đặt thời gian timeout cho cookie session (ví dụ: 30 phút): `session.cookie_lifetime = 1800`
2.  **Session Fixation Prevention:** Giải thích Session Fixation là gì và tại sao việc gọi `session_regenerate_id(true)` **ngay sau khi** người dùng đăng nhập thành công lại là biện pháp quan trọng để ngăn chặn kiểu tấn công này. Tham số `true` có ý nghĩa gì?
3.  **Thực hành Login/Logout:** Xây dựng trang đăng nhập và một trang "dashboard" yêu cầu đăng nhập.
    * Implement việc regenerate session ID khi đăng nhập thành công (xem bài 2).
    * Implement chức năng đăng xuất: Xóa dữ liệu session (`$_SESSION = array()`), hủy session (`session_destroy()`), và xóa cả cookie session phía client (ví dụ: bằng `setcookie()`).

## Chương 8: Securing REST Services (Bảo mật Dịch vụ REST)

1.  **Input Validation (REST):** Thiết kế endpoint `POST /users` để tạo người dùng mới. Request body là JSON chứa `username` (alphanumeric, 5-15 chars) và `email` (valid format). Viết code PHP đọc JSON từ `php://input`, dùng `json_decode`, sau đó validate `username` và `email`. Trả về lỗi 400 Bad Request với thông báo JSON `{ "error": "Invalid input", "details": [...] }` nếu không hợp lệ.
2.  **Authentication (API Key):** Implement cơ chế xác thực API Key đơn giản cho tất cả các endpoint của bạn. Request phải chứa header `Authorization: Bearer <YOUR_API_KEY>`. Viết một hàm hoặc middleware trong PHP để kiểm tra header này, so sánh key với danh sách key hợp lệ (lưu trong CSDL hoặc file config an toàn). Nếu không hợp lệ hoặc thiếu header, trả về lỗi 401 Unauthorized.
3.  **Rate Limiting:** Thảo luận cách implement Rate Limiting cho một REST API (ví dụ: giới hạn 100 request/giờ cho mỗi API Key). Mô tả cách lưu trữ số lượng request (ví dụ: dùng Redis, Memcached hoặc CSDL), cách kiểm tra và cách trả về lỗi 429 Too Many Requests khi vượt giới hạn.

## Chương 9: Using CAPTCHAs (Sử dụng CAPTCHA)

1.  **Tích hợp reCAPTCHA:** Tìm hiểu và tích hợp Google reCAPTCHA v2 ("I'm not a robot" Checkbox) vào một form đăng ký đơn giản. Viết code PHP phía server để nhận `g-recaptcha-response` từ `$_POST` và gửi request đến API của Google để xác thực. Chỉ xử lý đăng ký nếu xác thực CAPTCHA thành công.
2.  **Phân tích:** So sánh ưu và nhược điểm của việc sử dụng dịch vụ CAPTCHA bên ngoài (như reCAPTCHA) so với việc tự xây dựng CAPTCHA bằng thư viện GD về mặt bảo mật, khả năng truy cập (accessibility), và chi phí/công sức triển khai.
3.  **Thiết kế CAPTCHA Tốt hơn:** Nếu phải tự tạo CAPTCHA hình ảnh, hãy liệt kê ít nhất 3 kỹ thuật (ngoài việc hiển thị text) để làm khó bot OCR nhưng vẫn đảm bảo người dùng thông thường có thể đọc được (ví dụ: thêm nhiễu nền, làm méo chữ vừa phải, chồng chéo ký tự, dùng nhiều font). Thảo luận thêm về việc cung cấp phương án thay thế (ví dụ: audio CAPTCHA) cho người dùng khuyết tật.

## Chương 10: User Authentication, Authorization, and Logging (Xác thực, Phân quyền và Ghi Log Người dùng)

1.  **Xác thực Email bằng Token:** Implement quy trình xác thực email:
    * Form cho user nhập email đăng ký.
    * Khi submit, lưu email và tạo một token ngẫu nhiên, duy nhất, lưu token này vào CSDL cùng email và trạng thái "chưa xác thực", đặt thời gian hết hạn cho token (ví dụ: 1 giờ).
    * Gửi email cho người dùng chứa một link đặc biệt (ví dụ: `/verify_email.php?token=xxx`).
    * Khi người dùng click link, script `verify_email.php` kiểm tra token trong CSDL:
        * Nếu hợp lệ và chưa hết hạn: Cập nhật trạng thái email thành "đã xác thực", xóa token.
        * Nếu không hợp lệ hoặc hết hạn: Báo lỗi.
2.  **RBAC Implementation:** Dựa trên thiết kế CSDL RBAC cơ bản (bảng `users`, `roles`, `permissions`, `role_permissions`, `user_roles`), viết các hàm PHP sau:
    * `assign_role_to_user($user_id, $role_id)`
    * `grant_permission_to_role($role_id, $permission_id)`
    * `user_has_permission($user_id, $permission_name)`: Hàm này cần truy vấn CSDL để kiểm tra xem user có role nào (trực tiếp hoặc qua group nếu có) mà role đó được gán quyền `$permission_name` hay không.
3.  **Logging:** Thiết kế định dạng chi tiết cho file log hoạt động của ứng dụng (ví dụ: JSON hoặc format chuẩn như Apache Combined Log Format). Viết một lớp `AppLogger` trong PHP sử dụng `file_put_contents` (với `FILE_APPEND | LOCK_EX`) để ghi log vào file với định dạng đã thiết kế, bao gồm timestamp, user ID (nếu có), địa chỉ IP, action, và dữ liệu context liên quan. Đảm bảo file log được lưu ở vị trí an toàn.

## Chương 11: Preventing Data Loss (Ngăn chặn Mất dữ liệu)

1.  **Soft Delete & Undelete:** Sửa đổi bảng `products` trong CSDL, thêm cột `deleted_at` (kiểu `TIMESTAMP`, `NULL` mặc định). Viết lại hàm `delete_product($id)` trong PHP để nó `UPDATE` cột `deleted_at` thành thời gian hiện tại thay vì thực hiện `DELETE` vật lý. Viết hàm `undelete_product($id)` để đặt lại `deleted_at = NULL`.
2.  **Querying Soft Deletes:** Viết lại các hàm sau để làm việc với soft delete:
    * `get_active_products()`: Chỉ `SELECT` các sản phẩm có `deleted_at IS NULL`.
    * `get_deleted_products()`: Chỉ `SELECT` các sản phẩm có `deleted_at IS NOT NULL`.
3.  **Confirmation Dialog:** Implement chức năng xóa sản phẩm (sử dụng soft delete ở bài 1). Trước khi thực hiện `UPDATE deleted_at = NOW()`, sử dụng Javascript phía client (`confirm()`) hoặc một trang xác nhận riêng phía server để hỏi người dùng "Bạn có chắc chắn muốn xóa sản phẩm [tên sản phẩm]?". Chỉ thực hiện xóa nếu người dùng xác nhận.

## Chương 12: Safe Execution of System and Remote Procedure Calls (Thực thi An toàn Lệnh Hệ thống và RPC)

1.  **Thảo luận Rủi ro:** Thảo luận các rủi ro bảo mật khi cho phép ứng dụng PHP thực thi trực tiếp các lệnh hệ thống như `rm`, `chmod`, `convert` (ImageMagick) bằng `shell_exec` hoặc `system`, đặc biệt khi có đầu vào từ người dùng. Đề xuất giải pháp sử dụng hàng đợi (message queue) và một worker process riêng biệt để xử lý các tác vụ này an toàn hơn.
2.  **Secure Network Client (cURL):** Viết đoạn code PHP sử dụng thư viện cURL để thực hiện một request `POST` dữ liệu JSON đến một API endpoint qua HTTPS. Cấu hình cURL để:
    * Xác thực SSL certificate của server (`CURLOPT_SSL_VERIFYPEER => true`, `CURLOPT_SSL_VERIFYHOST => 2`).
    * Chỉ định đường dẫn đến CA bundle (`CURLOPT_CAINFO`).
    * Đặt timeout hợp lý cho toàn bộ request (`CURLOPT_TIMEOUT`).
    * Gửi header `Content-Type: application/json`.
3.  **Phân tích Rủi ro RPC Client:** Khi ứng dụng PHP của bạn đóng vai trò client gọi đến một web service (API) của bên thứ ba:
    * Làm thế nào kẻ tấn công có thể lợi dụng ứng dụng của bạn để thực hiện tấn công DoS lên API đó?
    * Nếu API đó trả về dữ liệu không được sanitize đúng cách, làm thế nào nó có thể dẫn đến lỗ hổng (ví dụ: Stored XSS) trong ứng dụng của bạn khi bạn hiển thị dữ liệu đó?

## Chương 13: Securing Unix (Bảo mật Unix)

1.  **Phân tích Quyền File:** Cho kịch bản: Một script PHP (`/var/www/html/cron.php`) cần được chạy bởi user `www-data` thông qua `cron`. Script này cần đọc file config (`/etc/app/config.secure`) chỉ được đọc bởi root và group `appadmin`, và ghi log vào `/var/log/app/cron.log` chỉ được ghi bởi `www-data`. Hãy đề xuất quyền sở hữu (user:group) và permission mode (số octal) cho `cron.php`, `config.secure`, `/var/log/app/`, và `cron.log`. Giải thích lý do.
2.  **Thảo luận `open_basedir` và `disable_functions`:** Giải thích mục đích và cách hoạt động của hai directive này trong `php.ini`. Chúng giúp ngăn chặn loại tấn công nào và có những hạn chế gì?
3.  **So sánh PHP Execution Models:** So sánh ưu/nhược điểm về bảo mật khi chạy PHP theo các cách sau trong môi trường web server:
    * `mod_php` (Apache module)
    * CGI
    * PHP-FPM

## Chương 14: Securing Your Database (Bảo mật CSDL - MySQL)

1.  **Tạo User An Toàn:** Viết các câu lệnh SQL (`CREATE USER`, `GRANT`) để tạo một user MySQL mới `report_user` chỉ được phép kết nối từ mạng nội bộ (`10.0.0.%`), có mật khẩu mạnh, và chỉ có quyền `SELECT` trên các bảng trong database `analytics_db`.
2.  **Audit Users:** Viết các câu lệnh `SELECT` để truy vấn các bảng hệ thống của MySQL (`mysql.user`, `mysql.db`, etc.) nhằm liệt kê:
    * Tất cả user và host họ được phép kết nối.
    * User nào không có mật khẩu (`authentication_string` hoặc `password` rỗng/null).
    * User nào có quyền quản trị toàn cục (ví dụ: `SUPER`, `GRANT OPTION`).
3.  **Backup & Restore:**
    * Viết lệnh `mysqldump` để backup chỉ cấu trúc (schema) của database `my_app_db` vào file `schema.sql`.
    * Viết lệnh `mysqldump` để backup chỉ dữ liệu của bảng `logs` trong database `my_app_db` vào file `logs_data.sql`.
    * Viết lệnh `mysql` để restore database `my_app_db` từ file backup `full_backup.sql`.

## Chương 15: Using Encryption (Sử dụng Mã hóa)

1.  **Password Hashing:** Viết một lớp `Auth` đơn giản trong PHP có các phương thức:
    * `register($username, $password)`: Hash mật khẩu bằng `password_hash()` và lưu `$username`, `$hashed_password` (giả lập lưu vào CSDL).
    * `login($username, $password)`: Lấy `$hashed_password` đã lưu (giả lập) và dùng `password_verify()` để kiểm tra. Trả về `true`/`false`.
2.  **Symmetric Encryption (OpenSSL):** Viết hai hàm PHP:
    * `encrypt_data($plaintext, $key)`: Mã hóa `$plaintext` bằng AES-256-CBC sử dụng `$key`. Hàm cần tạo IV ngẫu nhiên, thêm IV vào đầu ciphertext, và trả về chuỗi đã mã hóa (nên dùng base64 encode).
    * `decrypt_data($ciphertext_with_iv, $key)`: Giải mã chuỗi nhận được từ hàm trên. Hàm cần tách IV ra khỏi ciphertext, sau đó giải mã bằng AES-256-CBC với `$key` và IV.
3.  **Asymmetric Encryption (Conceptual):** Giải thích quy trình sử dụng cặp key Public/Private RSA để mã hóa và giải mã một thông điệp ngắn. Trong kịch bản ứng dụng web, public key thường được đặt ở đâu và private key nên được bảo vệ như thế nào?

## Chương 16: Securing Network Connections: SSL and SSH (Bảo mật Kết nối Mạng: SSL và SSH)

1.  **HTTPS Check & Redirect:** Viết một đoạn code PHP đặt ở đầu các trang quan trọng (ví dụ: trang login, thanh toán) để kiểm tra xem kết nối hiện tại có phải là HTTPS không (`isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'` hoặc kiểm tra `$_SERVER['SERVER_PORT'] == 443`). Nếu không phải HTTPS, thực hiện redirect 301 (Permanent Redirect) đến URL tương ứng nhưng với `https://`.
2.  **SSH Key Authentication:** Mô tả các bước cần thiết để một script PHP chạy trên server A có thể kết nối đến server B qua SSH và thực thi một lệnh mà không cần nhập mật khẩu, bằng cách sử dụng SSH key pair. (Nêu các bước tạo key, copy public key, và cấu hình).
3.  **Secure File Transfer:** So sánh `scp`, `sftp` và `rsync over ssh`. Khi nào nên sử dụng phương thức nào để truyền file giữa các server một cách an toàn?

## Chương 17: Final Recommendations (Tổng kết và Khuyến nghị)

1.  **Checklist Dev vs Prod:** Tạo một checklist chi tiết các điểm khác biệt về cấu hình bảo mật và môi trường cần đảm bảo giữa server development và server production cho một ứng dụng PHP. Ví dụ:
    * Error Reporting (`display_errors`, `error_reporting`)
    * Database Credentials
    * Debug Tools (Xdebug, Profilers)
    * Phiên bản phần mềm (PHP, libraries)
    * Quyền ghi file
    * Truy cập mạng (Firewall rules)
    * SSL Certificates (Self-signed vs Valid CA)
2.  **Update & Patching Strategy:** Xây dựng một quy trình/chính sách đề xuất cho việc theo dõi và áp dụng các bản vá bảo mật cho toàn bộ stack ứng dụng (OS, Web Server, PHP, MySQL, Composer dependencies). Quy trình nên bao gồm tần suất kiểm tra, nguồn thông tin tin cậy, quy trình kiểm thử (staging), và rollback plan.
3.  **Tự đánh giá Dự án:** Chọn một dự án PHP thực tế bạn đã tham gia. Dựa trên các nguyên tắc và kỹ thuật đã học từ cuốn sách, thực hiện một cuộc "audit" bảo mật nhỏ cho dự án đó. Liệt kê ra 3-5 điểm yếu bảo mật tiềm ẩn bạn tìm thấy và đề xuất giải pháp khắc phục cụ thể cho từng điểm.