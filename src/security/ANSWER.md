# Lời giải Bài tập Thực hành Bảo mật PHP (Chương 1-8 - "Pro PHP Security")

## Chương 1: Why Is Secure Programming a Concern?

**Bài tập 1.1: Phân tích Rủi ro (Blog Đơn giản)**

* **Kiến thức vận dụng:** Hiểu các loại tấn công cơ bản mà ứng dụng web dễ gặp phải khi tương tác với người dùng (nhập liệu, hiển thị). (Chapter 1, "What Kinds of Attacks Are Web Applications Vulnerable To?", `[source: 120]`-`[source: 211]`).
* **Phân tích & Áp dụng:** Một blog cho phép đăng bài và bình luận có các điểm tương tác chính: form đăng bài, form bình luận, hiển thị bài viết, hiển thị bình luận. Mỗi điểm này là một vector tấn công tiềm năng.
* **Lời giải (Ví dụ):**
    1.  **SQL Injection:** Attacker nhập mã SQL độc hại vào form bình luận hoặc form tìm kiếm (nếu có) để đánh cắp/thay đổi dữ liệu CSDL. (`[source: 124]`, `[source: 374]`).
    2.  **Cross-Site Scripting (XSS):** Attacker nhập mã Javascript vào nội dung bài đăng hoặc bình luận. Khi người dùng khác xem, mã độc thực thi trên trình duyệt của họ, có thể đánh cắp cookie session. (`[source: 124]`, `[source: 150]`).
    3.  **Cross-Site Request Forgery (CSRF):** Attacker lừa người quản trị blog (đang đăng nhập) thực hiện một hành động không mong muốn (ví dụ: xóa bài viết) bằng cách khiến họ click vào một link hoặc submit một form độc hại trên trang khác. (Không được đề cập rõ ràng là CSRF trong Ch1, nhưng thuộc nhóm tấn công lợi dụng hành động người dùng).
    4.  **Spam Bình luận/Bài viết:** Bot tự động gửi hàng loạt bình luận hoặc bài viết quảng cáo, làm giảm chất lượng blog. (`[source: 168]`, `[source: 178]`-`[source: 181]`).
    5.  **Brute Force Login:** Attacker thử hàng loạt username/password để chiếm quyền quản trị blog. (Liên quan đến "Secrets", `[source: 91]`-`[source: 93]`).

**Bài tập 1.2: Thảo luận Nguyên tắc ("Không tin tưởng input")**

* **Kiến thức vận dụng:** Nguyên tắc cơ bản về việc không tin tưởng dữ liệu đến từ client và sự cần thiết phải xác thực/làm sạch. (Chapter 1, "Never Trust User Input", `[source: 248]`-`[source: 258]`).
* **Phân tích & Áp dụng:** Dữ liệu từ client (form, URL, cookie, header) có thể bị sửa đổi dễ dàng bằng các công cụ của trình duyệt hoặc các script tự động. Việc tin tưởng dữ liệu này có thể dẫn đến nhiều loại tấn công.
* **Lời giải (Thảo luận):**
    Nguyên tắc "Không bao giờ tin tưởng đầu vào người dùng" là tối quan trọng vì mọi dữ liệu gửi từ phía client (trình duyệt, ứng dụng di động, API client) đều có thể bị thao túng bởi người dùng hoặc kẻ tấn công. Kẻ tấn công không nhất thiết phải sử dụng giao diện web của bạn; họ có thể gửi các request HTTP tùy chỉnh bằng các công cụ như `curl` hoặc Burp Suite.
    **Ví dụ:** Một form cho phép người dùng cập nhật email. Nếu server chỉ kiểm tra định dạng email ở phía Javascript mà không kiểm tra lại ở phía PHP, kẻ tấn công có thể bypass Javascript và gửi một chuỗi chứa mã SQL độc hại (`'; DROP TABLE users; --`) vào trường email. Nếu PHP tin tưởng và sử dụng trực tiếp chuỗi này trong câu lệnh SQL `UPDATE users SET email = '...' WHERE ...`, nó có thể dẫn đến SQL Injection và xóa bảng `users`. (`[source: 253]`). Việc không tin tưởng input đòi hỏi server phải *luôn luôn* validate (đúng định dạng, kiểu, độ dài) và sanitize (escape cho ngữ cảnh sử dụng) mọi dữ liệu nhận được từ client.

**Bài tập 1.3: Giải thích "Phòng thủ theo chiều sâu" (Form Đăng nhập)**

* **Kiến thức vận dụng:** Khái niệm về việc sử dụng nhiều lớp bảo vệ độc lập thay vì chỉ dựa vào một cơ chế duy nhất. (Chapter 1, "Defense in Depth Is the Only Defense", `[source: 268]`-`[source: 276]`).
* **Phân tích & Áp dụng:** Áp dụng nhiều lớp kiểm tra và bảo vệ cho một chức năng cụ thể như đăng nhập.
* **Lời giải (Giải thích):**
    Phòng thủ theo chiều sâu (Defense in Depth) có nghĩa là xây dựng nhiều lớp bảo mật khác nhau, sao cho nếu một lớp bị vượt qua, vẫn còn các lớp khác ngăn chặn kẻ tấn công. Áp dụng vào form đăng nhập:
    1.  **Lớp 1 (Client-side):** Validate định dạng cơ bản (ví dụ: email hợp lệ, password không trống) bằng Javascript để cải thiện trải nghiệm người dùng (nhưng không tin tưởng lớp này về mặt bảo mật).
    2.  **Lớp 2 (Server-side Input Validation):** Kiểm tra lại *tất cả* định dạng, độ dài username/password ở phía PHP. (`[source: 261]`-`[source: 262]`).
    3.  **Lớp 3 (Password Hashing):** Lưu trữ mật khẩu trong CSDL dưới dạng hash mạnh (ví dụ: bcrypt) với salt, không lưu plaintext. (`[source: 244]` - đề cập chung về bảo vệ pass, Ch15 đi sâu hơn).
    4.  **Lớp 4 (Rate Limiting):** Giới hạn số lần đăng nhập thất bại từ một IP hoặc cho một tài khoản trong khoảng thời gian nhất định để chống Brute Force. (Không có trong Ch1, nhưng là ví dụ thực tế).
    5.  **Lớp 5 (HTTPS):** Sử dụng HTTPS để mã hóa thông tin đăng nhập khi truyền đi, chống nghe lén. (`[source: 272]` - đề cập log activity, Ch16 đi sâu hơn về SSL).
    6.  **Lớp 6 (Session Management):** Sau khi đăng nhập thành công, tạo session ID mới, an toàn. (`[source: 272]` - đề cập log activity, Ch7 đi sâu hơn).
    Nếu kẻ tấn công bypass được Javascript (Lớp 1), validation phía server (Lớp 2) vẫn chặn input không hợp lệ. Nếu họ đoán được password (Lớp 3 khó hơn nhiều nhờ hash), rate limiting (Lớp 4) có thể chặn họ. Nghe lén bị chặn bởi HTTPS (Lớp 5).

## Chương 2: Validating and Sanitizing User Input

**Bài tập 2.1: Validate Email**

* **Kiến thức vận dụng:** Kiểm tra định dạng input. Sử dụng hàm hoặc biểu thức chính quy. (Chapter 2, "Check Input Type, Length, and Format", `[source: 420]`-`[source: 422]`, `[source: 470]`-`[source: 476]`; Sách đề cập kiểm tra email ở `[source: 543]`-`[source: 551]`).
* **Phân tích & Áp dụng:** Cần một hàm nhận chuỗi, kiểm tra xem nó có cấu trúc như `user@domain.tld` hay không. Cách tốt nhất và hiện đại là dùng `filter_var`.
* **Lời giải (PHP):**
    ```php
    <?php
    /**
     * Kiểm tra xem một chuỗi có phải là định dạng email hợp lệ không.
     *
     * @param string $email Chuỗi cần kiểm tra.
     * @return bool True nếu hợp lệ, False nếu không.
     */
    function validate_email($email) {
        // Sử dụng filter_var để validate email - cách được khuyến nghị
        // [source: Không có trực tiếp filter_var nhưng nguyên tắc kiểm tra format được nêu]
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }

        // Cách dùng regex (phức tạp hơn và có thể không bao quát hết các trường hợp edge case)
        // $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        // return preg_match($pattern, $email);
    }

    // Ví dụ sử dụng
    $email1 = "test@example.com";
    $email2 = "invalid-email";
    $email3 = "another@domain.co.uk";

    var_dump(validate_email($email1)); // bool(true)
    var_dump(validate_email($email2)); // bool(false)
    var_dump(validate_email($email3)); // bool(true)
    ?>
    ```

**Bài tập 2.2: Form Validation (Đăng ký)**

* **Kiến thức vận dụng:** Xử lý `$_POST`, kiểm tra điều kiện bắt buộc (không rỗng), kiểm tra độ dài (`strlen`), kiểm tra kiểu/ký tự cho phép (regex `preg_match`), kiểm tra định dạng email (`filter_var` hoặc regex). (Chapter 2, "Strategies for Validating User Input in PHP", `[source: 379]`-`[source: 491]`).
* **Phân tích & Áp dụng:** Cần lấy từng giá trị từ `$_POST`, áp dụng các quy tắc validate tương ứng, lưu lỗi vào một mảng để hiển thị lại cho người dùng.
* **Lời giải (PHP):**
    ```php
    <?php
    $errors = [];
    $username = '';
    $email = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // [source: 414] - Lấy giá trị từ POST, kiểm tra empty
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : ''; // Không trim password
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        // Validate Username
        // [source: 421] - Kiểm tra type, length, format
        if (empty($username)) {
            $errors['username'] = 'Username là bắt buộc.';
        } elseif (strlen($username) < 6 || strlen($username) > 20) {
            $errors['username'] = 'Username phải từ 6 đến 20 ký tự.';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $errors['username'] = 'Username chỉ được chứa chữ cái và số.';
        }

        // Validate Password
        if (empty($password)) {
            $errors['password'] = 'Password là bắt buộc.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password phải có ít nhất 8 ký tự.';
            // [source: 470] - Ví dụ kiểm tra format (dùng regex)
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password phải chứa ít nhất một chữ hoa.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Password phải chứa ít nhất một chữ thường.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password phải chứa ít nhất một chữ số.';
        }

        // Validate Email
        // [source: 550] - Đề cập sự phức tạp của regex email, filter_var tốt hơn
        if (empty($email)) {
            $errors['email'] = 'Email là bắt buộc.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Định dạng email không hợp lệ.';
        }

        // Nếu không có lỗi
        if (empty($errors)) {
            // Xử lý đăng ký (lưu vào DB, etc.)
            echo "Đăng ký thành công!";
            // Reset form fields for next potential registration
            $username = '';
            $email = '';
            // Không hiển thị lại form
            exit;
        }
    }
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Register</title>
        <style> .error { color: red; font-size: 0.8em; } </style>
    </head>
    <body>
        <h1>Đăng ký</h1>
        <form method="post" action="">
            <div>
                <label for="username">Username:</label><br>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <span class="error"><?php echo $errors['username']; ?></span>
                <?php endif; ?>
            </div>
            <div>
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required>
                 <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            <div>
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                 <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            <br>
            <div>
                <button type="submit">Đăng ký</button>
            </div>
        </form>
    </body>
    </html>
    ```

**Bài tập 2.3: Sanitization (HTML Output)**

* **Kiến thức vận dụng:** Làm sạch dữ liệu trước khi hiển thị trong ngữ cảnh HTML để chống XSS. Sử dụng `htmlentities` với các tham số đúng. (Chapter 2, "Sanitize Values Passed to Other Systems", `[source: 491]`; Mục "HTML Output", `[source: 569]`-`[source: 573]`). `[source: 917]` nhấn mạnh việc dùng `ENT_QUOTES`.
* **Phân tích & Áp dụng:** Cần dùng `htmlentities` để chuyển đổi các ký tự đặc biệt của HTML (như `<`, `>`, `&`, `"`, `'`) thành các thực thể HTML tương ứng.
* **Lời giải (PHP):**
    ```php
    <?php
    /**
     * Làm sạch chuỗi để hiển thị an toàn trong HTML.
     * Chuyển đổi các ký tự đặc biệt thành thực thể HTML, bao gồm cả nháy đơn và kép.
     *
     * @param string $string Chuỗi đầu vào.
     * @return string Chuỗi đã được làm sạch.
     */
    function sanitize_for_html_output($string) {
        // [source: 573] Đề cập htmlentities là bắt buộc
        // [source: 917] Nhấn mạnh dùng ENT_QUOTES
        // Luôn chỉ định encoding (UTF-8 là phổ biến nhất)
        return htmlentities($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    // Ví dụ sử dụng
    $unsafe_input = '<script>alert("XSS!");</script> User\'s "input" & data';
    $safe_output = sanitize_for_html_output($unsafe_input);

    echo "Dữ liệu gốc: " . $unsafe_input . "<br>";
    echo "Dữ liệu an toàn để hiển thị: " . $safe_output;
    // Output sẽ hiển thị mã script dưới dạng text, không thực thi
    // &lt;script&gt;alert(&quot;XSS!&quot;);&lt;/script&gt; User&#039;s &quot;input&quot; &amp; data
    ?>
    ```

## Chương 3: Preventing SQL Injection

**Bài tập 3.1: Prepared Statements**

* **Kiến thức vận dụng:** Sử dụng Prepared Statements (MySQLi hoặc PDO) để tách biệt câu lệnh SQL và dữ liệu người dùng, ngăn chặn SQL Injection. (Chapter 3, "Abstract to Improve Security", `[source: 726]`, ví dụ với `mysqli_prepare`, `mysqli_stmt_bind_param`, `mysqli_stmt_execute`, `[source: 749]`-`[source: 753]`).
* **Phân tích & Áp dụng:** Code cũ nối chuỗi trực tiếp rất nguy hiểm. Cần tạo câu lệnh SQL với placeholder (`?`), chuẩn bị nó, bind giá trị `$userId` vào placeholder, rồi thực thi.
* **Lời giải (Sử dụng MySQLi - Object Oriented):**
    ```php
    <?php
    // --- Giả lập kết nối CSDL an toàn ---
    $servername = "localhost";
    $username_db = "your_username";
    $password_db = "your_password";
    $dbname = "your_database";

    // Tạo kết nối
    // [source: 773] - Tạo đối tượng mysqli
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        // [source: 584]-[source: 585] - Che giấu lỗi chi tiết
        error_log("Connection failed: " . $conn->connect_error); // Ghi log lỗi
        die("Lỗi kết nối CSDL. Vui lòng thử lại sau.");
    }
    mysqli_set_charset($conn, 'utf8mb4'); // Nên set charset
    // --- Hết phần giả lập kết nối ---

    $userId = $_GET['user_id'] ?? null; // Lấy user ID, xử lý nếu không tồn tại

    // 1. Validate Input (Rất quan trọng!)
    if ($userId === null || !ctype_digit((string)$userId) || (int)$userId <= 0) {
         // [source: 716] - Nhấn mạnh kiểm tra type trước khi query
        die("User ID không hợp lệ.");
    }
    $userId = (int)$userId; // Ép kiểu thành số nguyên

    // 2. Sử dụng Prepared Statement
    // [source: 775] - Chuẩn bị câu lệnh với placeholder (?)
    $sql = "SELECT username, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        die("Lỗi truy vấn CSDL. Vui lòng thử lại sau.");
    }

    // [source: 776] - Bind tham số (i: integer, s: string, d: double, b: blob)
    //              và biến vào placeholder
    $stmt->bind_param("i", $userId);

    // [source: 776] - Thực thi câu lệnh đã chuẩn bị
    $stmt->execute();

    // [source: 776] - Lấy kết quả
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // [source: 913] (Chương 4) - Luôn escape output cho HTML
        echo "Username: " . htmlspecialchars($user['username']) . "<br>";
        echo "Email: " . htmlspecialchars($user['email']);
    } else {
        echo "User không tồn tại.";
    }

    // [source: 778] - Đóng statement
    $stmt->close();
    // [source: 778] - Đóng kết nối
    $conn->close();
    ?>
    ```
    *(Lưu ý: Cần thay thông tin kết nối CSDL thực tế)*

**Bài tập 3.2: Phân tích Lỗ hổng (ORDER BY)**

* **Kiến thức vận dụng:** Hiểu rằng Prepared Statements thường không dùng được cho các phần động của câu lệnh SQL như tên cột (`ORDER BY`, `GROUP BY`) hoặc tên bảng. Cần validate các giá trị này bằng phương pháp khác như whitelist. (Chapter 3 không đi sâu vào `ORDER BY` nhưng nhấn mạnh việc kiểm tra type và escape *giá trị*, `[source: 712]`, `[source: 723]`). Lỗ hổng xảy ra khi attacker kiểm soát trực tiếp phần câu lệnh SQL.
* **Phân tích & Áp dụng:** `$_GET['category']` có thể chứa dấu nháy đơn gây lỗi hoặc mã SQL khác nếu không được escape đúng cách (dù ít nguy hiểm hơn nếu được bao trong nháy đơn). Nguy hiểm hơn là `$_GET['sort_column']` và `$_GET['sort_dir']` được đưa thẳng vào câu lệnh. Attacker có thể chèn tên cột không tồn tại, hàm SQL (`ORDER BY SLEEP(5)`), hoặc thậm chí cấu trúc SQL phức tạp hơn nếu ứng dụng xử lý chuỗi kém.
* **Lời giải (Phân tích & Cách khắc phục):**
    1.  **`{$_GET['category']}`:**
        * **Rủi ro:** SQL Injection cổ điển nếu giá trị không được escape đúng cách và không nằm trong dấu nháy đơn (hoặc nếu attacker có thể thoát khỏi dấu nháy).
        * **Khắc phục:** Sử dụng Prepared Statements. Bind giá trị `$_GET['category']` vào placeholder `?`.
            ```sql
            SELECT * FROM products WHERE category = ? ORDER BY ...
            ```
    2.  **`{$_GET['sort_column']}`:**
        * **Rủi ro:** Cực kỳ nguy hiểm. Attacker có thể chèn tên cột không tồn tại, hàm SQL (`RAND()`, `SLEEP(10)`), hoặc các biểu thức SQL phức tạp, có thể dùng để dò thông tin hoặc gây DoS. Ví dụ: `?sort_column=(SELECT password FROM users LIMIT 1)`.
        * **Khắc phục:** **Không** dùng Prepared Statements cho tên cột. Sử dụng **Whitelist**: Định nghĩa một mảng các tên cột được phép sắp xếp. Kiểm tra xem giá trị `$_GET['sort_column']` có nằm trong mảng này không. Nếu không, sử dụng giá trị mặc định hoặc báo lỗi.
            ```php
            $allowed_sort_columns = ['name', 'price', 'date_added'];
            $sort_column = 'name'; // Default
            if (isset($_GET['sort_column']) && in_array($_GET['sort_column'], $allowed_sort_columns)) {
                $sort_column = $_GET['sort_column'];
            }
            // Sau đó mới đưa $sort_column vào câu SQL (không cần escape tên cột)
            ```
    3.  **`{$_GET['sort_dir']}`:**
        * **Rủi ro:** Tương tự `sort_column` nhưng ít nguy hiểm hơn vì giá trị thường chỉ là `ASC` hoặc `DESC`. Attacker vẫn có thể chèn các giá trị khác gây lỗi hoặc cố gắng inject thêm.
        * **Khắc phục:** Sử dụng **Whitelist**: Kiểm tra chặt chẽ giá trị `$_GET['sort_dir']` chỉ là `ASC` hoặc `DESC`. Nếu không, sử dụng giá trị mặc định (`ASC`).
            ```php
            $sort_dir = 'ASC'; // Default
            if (isset($_GET['sort_dir']) && strtoupper($_GET['sort_dir']) === 'DESC') {
                $sort_dir = 'DESC';
            }
            // Sau đó mới đưa $sort_dir vào câu SQL
            ```

**Bài tập 3.3: Kiểm tra Whitelist (ORDER BY)**

* **Kiến thức vận dụng:** Kỹ thuật whitelist để giới hạn input vào một tập các giá trị an toàn đã biết, đặc biệt hữu ích cho các phần động của SQL không thể dùng prepared statement. (Tuy sách không có ví dụ whitelist cụ thể cho ORDER BY, nhưng nguyên tắc "Allow Only Expected Input" ở Chương 2, `[source: 413]` và việc kiểm tra type `[source: 712]` là nền tảng).
* **Phân tích & Áp dụng:** Cần tạo một mảng chứa các tên cột hợp lệ. So sánh giá trị người dùng cung cấp với mảng này.
* **Lời giải (PHP):**
    ```php
    <?php
    /**
     * Kiểm tra xem tên cột sắp xếp có hợp lệ không và trả về tên cột an toàn.
     *
     * @param string|null $user_input Tên cột do người dùng cung cấp.
     * @param array $allowed_columns Mảng các tên cột được phép.
     * @param string $default_column Tên cột mặc định nếu input không hợp lệ.
     * @return string Tên cột an toàn để sử dụng trong SQL.
     */
    function get_safe_sort_column($user_input, array $allowed_columns, $default_column) {
        // [source: 413] - Chỉ cho phép input dự kiến
        if ($user_input !== null && in_array(strtolower($user_input), $allowed_columns, true)) {
             // So sánh không phân biệt hoa thường và đảm bảo có trong whitelist
             // Trả về chính xác giá trị trong whitelist để đảm bảo đúng tên cột
             $key = array_search(strtolower($user_input), array_map('strtolower', $allowed_columns));
             return $allowed_columns[$key];
        }
        // Nếu không hợp lệ, trả về giá trị mặc định
        // [source: 716] - Cần xử lý giá trị không hợp lệ
        if (!in_array($default_column, $allowed_columns)) {
             // Đảm bảo default cũng hợp lệ
             if(count($allowed_columns) > 0) return $allowed_columns[0];
             else throw new InvalidArgumentException("Default column is not in allowed list and allowed list is empty.");
        }
        return $default_column;
    }

    // Ví dụ sử dụng
    $allowed = ['name', 'price', 'date_added'];
    $default = 'date_added';

    $sort_col_input1 = $_GET['sort'] ?? null; // Ví dụ: 'price'
    $safe_sort_col1 = get_safe_sort_column($sort_col_input1, $allowed, $default);
    echo "Sorting by: " . $safe_sort_col1 . "<br>"; // Output: Sorting by: price

    $sort_col_input2 = 'invalid_column; DROP TABLE users; --';
    $safe_sort_col2 = get_safe_sort_column($sort_col_input2, $allowed, $default);
    echo "Sorting by: " . $safe_sort_col2 . "<br>"; // Output: Sorting by: date_added

    $sort_col_input3 = 'NAME'; // Test case-insensitivity
    $safe_sort_col3 = get_safe_sort_column($sort_col_input3, $allowed, $default);
    echo "Sorting by: " . $safe_sort_col3 . "<br>"; // Output: Sorting by: name
    ?>
    ```

## Chương 4: Preventing Cross-Site Scripting

**Bài tập 4.1: Output Escaping**

* **Kiến thức vận dụng:** Sử dụng `htmlentities` để escape dữ liệu trước khi hiển thị trong HTML, ngăn chặn việc trình duyệt diễn giải các thẻ HTML/script độc hại. (Chapter 4, "Encode HTML Entities in All Non-HTML Output", `[source: 907]`-`[source: 919]`).
* **Phân tích & Áp dụng:** Biến `$comment_text` được in trực tiếp vào HTML, nếu nó chứa `<script>alert('XSS')</script>`, mã Javascript sẽ được thực thi. Cần chuyển `<` thành `&lt;`, `>` thành `&gt;`, v.v.
* **Lời giải (PHP - Sửa code):**
    ```php
    <?php
    // Giả sử $comment_text lấy từ CSDL
    // $comment_text = get_comment_from_db($comment_id);
    $comment_text = '<script>window.location="[http://evil.com](http://evil.com)"</script><b>Bold comment!</b>'; // Ví dụ input nguy hiểm

    echo "<div class='comment'>";
    echo "<p><strong>User says:</strong></p>";
    // Sửa ở đây: Sử dụng htmlentities
    // [source: 913] - Ví dụ sử dụng safe() với htmlentities
    // [source: 917] - Nhấn mạnh dùng ENT_QUOTES
    echo "<p>" . htmlentities($comment_text ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
    echo "</div>";
    ?>
    ```
    *(Output HTML sẽ là `<p>&lt;script&gt;window.location=&quot;http://evil.com&quot;&lt;/script&gt;&lt;b&gt;Bold comment!&lt;/b&gt;</p>`, trình duyệt sẽ hiển thị text này thay vì thực thi script.)*

**Bài tập 4.2: URL Sanitization**

* **Kiến thức vận dụng:** Kiểm tra và làm sạch URL do người dùng cung cấp, đặc biệt là kiểm tra scheme để ngăn chặn `javascript:` hoặc các scheme nguy hiểm khác. (Chapter 4, "Sanitize All User-submitted URIs", `[source: 920]`-`[source: 931]`). Sách đề cập dùng `parse_url` (`[source: 922]`-`[source: 927]`).
* **Phân tích & Áp dụng:** Cần parse URL, kiểm tra thành phần `scheme`. Cũng cần kiểm tra các ký tự điều khiển (`\r`, `\n` tương ứng `%0d`, `%0a`) có thể dùng để tấn công HTTP Response Splitting (mặc dù đây là vấn đề khi *sử dụng* URL làm header, việc lọc sớm cũng tốt).
* **Lời giải (PHP):**
    ```php
    <?php
    /**
     * Kiểm tra URL có an toàn không (chỉ http/https, không chứa ký tự CRLF).
     *
     * @param string $url URL cần kiểm tra.
     * @return bool True nếu an toàn, False nếu không.
     */
    function validate_safe_url($url) {
        if (empty($url)) {
            return false; // URL rỗng không hợp lệ
        }

        // [source: 922] - Sử dụng parse_url
        $parsed_url = parse_url($url);

        // Kiểm tra parse thành công và có scheme
        if ($parsed_url === false || !isset($parsed_url['scheme'])) {
            return false;
        }

        // [source: 923] - Kiểm tra scheme
        $scheme = strtolower($parsed_url['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            return false; // Chỉ cho phép http và https
        }

        // [source: 3133] (Chương 12) - Kiểm tra ký tự CRLF (%0d %0a) để chống Response Splitting
        // Mặc dù không phải mục tiêu chính của Ch4, nhưng nên làm
        if (preg_match('/(%0a|%0d|\r|\n)/i', $url)) {
             return false; // Chứa ký tự xuống dòng nguy hiểm
        }

        // (Tùy chọn) Kiểm tra host có tồn tại không (có thể dùng filter_var)
        if (!isset($parsed_url['host']) || !filter_var('http://' . $parsed_url['host'], FILTER_VALIDATE_URL)) {
             // Thêm http:// để filter_var hoạt động đúng với host
             // return false; // Nếu muốn kiểm tra host phải hợp lệ
        }

        // (Tùy chọn) Kiểm tra whitelist domain nếu cần
        // $allowed_domains = ['example.com', 'trusted.org'];
        // if (!in_array($parsed_url['host'], $allowed_domains)) {
        //     return false;
        // }

        return true;
    }

    // Ví dụ sử dụng
    var_dump(validate_safe_url("[https://google.com/search](https://google.com/search)")); // bool(true)
    var_dump(validate_safe_url("[http://example.com](http://example.com)"));      // bool(true)
    var_dump(validate_safe_url("javascript:alert('XSS')")); // bool(false)
    var_dump(validate_safe_url("ftp://example.com"));         // bool(false)
    var_dump(validate_safe_url("[https://example.com/%0aheader:inject](https://example.com/%0aheader:inject)")); // bool(false)
    var_dump(validate_safe_url("invalid-url"));             // bool(false)
    ?>
    ```

**Bài tập 4.3: Thảo luận về HTML Input (`<img>`)**

* **Kiến thức vận dụng:** Hiểu các vector tấn công XSS qua các thuộc tính HTML, đặc biệt là các thuộc tính xử lý sự kiện hoặc tải tài nguyên bên ngoài. Cần các kỹ thuật lọc HTML nâng cao. (Chapter 4, "Forged Image Source URIs", `[source: 868]`-`[source: 881]`; "Use a Proven XSS Filter on HTML Input", `[source: 943]`-`[source: 958]`).
* **Phân tích & Áp dụng:** Thẻ `<img>` tưởng chừng vô hại nhưng có thể bị lợi dụng.
* **Lời giải (Thảo luận):**
    Việc cho phép người dùng nhúng thẻ `<img>` tiềm ẩn nhiều rủi ro XSS và các vấn đề khác:
    1.  **XSS qua thuộc tính sự kiện:** Các thuộc tính như `onerror`, `onload`, `onmouseover` có thể chứa mã Javascript. Ví dụ: `<img src="invalid" onerror="alert('XSS')">`. (`[source: 945]` đề cập JS event handling attributes).
    2.  **XSS qua `src` với scheme `javascript:`:** Mặc dù hầu hết trình duyệt hiện đại đã chặn, nhưng một số trình duyệt cũ hoặc cấu hình lạ có thể thực thi: `<img src="javascript:alert('XSS')">`. (`[source: 920]` đề cập `javascript:` scheme).
    3.  **Tải tài nguyên độc hại/CSRF qua `src`:** Thuộc tính `src` có thể trỏ đến một URL thực hiện hành động (CSRF) hoặc một script phía server được thiết kế để ghi log thông tin người dùng, hoặc thậm chí cố gắng khai thác lỗ hổng trình duyệt khi xử lý ảnh lỗi. Ví dụ: `<img src="http://example.com/delete_account.php?confirm=yes">`. (`[source: 869]`-`[source: 870]`).
    4.  **Tracking/Privacy:** `src` có thể trỏ đến server của kẻ tấn công để theo dõi ai, khi nào, từ IP nào đã xem nội dung chứa ảnh đó.
    5.  **Phishing/Giao diện giả:** Ảnh có thể được thiết kế trông giống như một phần giao diện đáng tin cậy để lừa người dùng click vào một link độc hại khác.

    **Biện pháp giảm thiểu:**
    * **Sử dụng thư viện lọc HTML mạnh mẽ:** Dùng thư viện như [HTML Purifier](http://htmlpurifier.org/) (hiện đại và tốt hơn Tidy/Safe_HTML được đề cập trong sách `[source: 956]`, `[source: 957]`) để tạo whitelist các thẻ và *chỉ các thuộc tính an toàn* của thẻ `<img>` (thường chỉ là `src`, `alt`, `width`, `height`). Loại bỏ hoàn toàn các thuộc tính xử lý sự kiện (`on*`).
    * **Validate thuộc tính `src`:** Chỉ cho phép `src` bắt đầu bằng `http://` hoặc `https://`. Phân tích URL trong `src` và chỉ cho phép trỏ đến các domain đáng tin cậy (nếu có thể) hoặc ít nhất là chặn các domain độc hại đã biết.
    * **Proxy ảnh:** Định tuyến lại tất cả các URL ảnh qua một script proxy trên server của bạn. Script này sẽ tải ảnh từ nguồn gốc và trả về cho người dùng. Lợi ích: Che giấu IP người dùng cuối khỏi server chứa ảnh gốc, có thể cache ảnh, có thể kiểm tra content type của ảnh trước khi trả về.

## Chương 5: Preventing Remote Execution

**Bài tập 5.1: Shell Argument Escaping**

* **Kiến thức vận dụng:** Sử dụng `escapeshellarg` để bao quanh và escape một đối số duy nhất, và `escapeshellcmd` để escape các ký tự đặc biệt trong *toàn bộ* câu lệnh shell. (Chapter 5, "Properly Escape All Shell Commands", `[source: 1134]`-`[source: 1170]`).
* **Phân tích & Áp dụng:** Cần nhận input, escape nó bằng `escapeshellarg` vì nó là một *đối số* cho lệnh `grep`. Lệnh `grep` và đường dẫn file là cố định, nhưng nên chạy `escapeshellcmd` trên toàn bộ lệnh cuối cùng để tăng cường bảo vệ.
* **Lời giải (PHP):**
    ```php
    <?php
    // Đường dẫn đến file cố định cần tìm kiếm (Ví dụ)
    $target_file = '/var/log/app.log';
    // Lệnh grep cơ bản
    $grep_command = '/bin/grep'; // Hoặc /usr/bin/grep

    // Giả sử nhận từ POST
    $search_term = $_POST['search_term'] ?? '';

    // === Biện pháp Bảo mật ===
    // 1. Validate input (Ví dụ: không cho phép quá dài, hoặc ký tự đặc biệt nếu không cần)
    if (strlen($search_term) > 100) { // Giới hạn độ dài tùy ý
        die("Search term is too long.");
    }
    if (empty($search_term)) {
        die("Search term cannot be empty.");
    }

    // 2. Escape đối số tìm kiếm bằng escapeshellarg
    // [source: 1135]-[source: 1137] - escapeshellarg dùng cho đối số
    $escaped_search_term = escapeshellarg($search_term);

    // 3. Escape đường dẫn file nếu nó không hoàn toàn cố định hoặc để an toàn hơn
    $escaped_target_file = escapeshellarg($target_file);

    // 4. Xây dựng câu lệnh đầy đủ
    // Dùng -e để đảm bảo $escaped_search_term được coi là pattern
    $full_command_unsafe = $grep_command . ' -e ' . $escaped_search_term . ' ' . $escaped_target_file;

    // 5. Escape toàn bộ câu lệnh bằng escapeshellcmd (Defense in depth)
    // [source: 1138]-[source: 1140] - escapeshellcmd escape các ký tự nguy hiểm trong lệnh
    $safe_full_command = escapeshellcmd($full_command_unsafe);

    // === Thực thi ===
    echo "Executing: " . htmlspecialchars($safe_full_command) . "<br><pre>";
    // [source: 1153] - Ví dụ dùng shell_exec
    $output = shell_exec($safe_full_command);

    if ($output === null) {
        echo "Error executing command or no output.";
    } else {
        // Luôn escape output trước khi hiển thị!
        echo htmlspecialchars($output);
    }
    echo "</pre>";
    ?>

    <form method="post">
        Search Term: <input type="text" name="search_term">
        <input type="submit" value="Search Log">
    </form>
    ```

**Bài tập 5.2: Phân tích Code (File Inclusion)**

* **Kiến thức vận dụng:** Hiểu về lỗ hổng Local File Inclusion (LFI) và Remote File Inclusion (RFI) khi đường dẫn file trong `include` hoặc `require` bị kiểm soát bởi người dùng mà không có validation chặt chẽ. (Chapter 5, `[source: 1004]`, `[source: 1126]`).
* **Phân tích & Áp dụng:** Biến `$page` được lấy trực tiếp từ `$_GET['p']` và nối chuỗi vào đường dẫn file include. Kẻ tấn công có thể cung cấp `p=../../../../etc/passwd` (LFI) để đọc file hệ thống, hoặc nếu `allow_url_include=On` (rất nguy hiểm, mặc định Off) thì có thể cung cấp `p=http://evil.com/shell.txt?` (RFI) để include mã độc từ xa.
* **Lời giải (Phân tích & Cách sửa):**
    * **Lỗ hổng:** Local File Inclusion (LFI) và tiềm ẩn Remote File Inclusion (RFI) nếu `allow_url_include=On`. Kẻ tấn công kiểm soát hoàn toàn biến `$page` được dùng để xây dựng đường dẫn file include.
    * **Cách sửa (Whitelist):** Phương pháp an toàn nhất là sử dụng whitelist để giới hạn các giá trị hợp lệ cho `$page`.
        ```php
        <?php
        $allowed_pages = [
            'home' => 'pages/home.html',
            'about' => 'pages/about.html',
            'contact' => 'pages/contact.html'
            // Thêm các trang hợp lệ khác vào đây
        ];

        // Lấy giá trị 'p', mặc định là 'home'
        $page_key = $_GET['p'] ?? 'home';

        // Kiểm tra xem page_key có trong whitelist không
        // [source: 413] (Chương 2) - Nguyên tắc chỉ cho phép input dự kiến
        if (array_key_exists($page_key, $allowed_pages)) {
            $file_to_include = $allowed_pages[$page_key];

            // (Optional but recommended) Kiểm tra file tồn tại trước khi include
            if (file_exists($file_to_include)) {
                 // [source: 1087] - Nguy cơ include file ngay cả khi ngoài webroot
                 // Cần đảm bảo file được include là file dự kiến
                 include($file_to_include);
            } else {
                // Xử lý lỗi: file không tồn tại
                error_log("Include file not found: " . $file_to_include);
                include('pages/error_404.html'); // Hoặc trang lỗi chung
            }
        } else {
            // Xử lý lỗi: trang không hợp lệ
            include('pages/error_404.html'); // Hoặc trang lỗi chung
        }
        ?>
        ```

**Bài tập 5.3: Modifier `e` trong `preg_replace`**

* **Kiến thức vận dụng:** Hiểu rằng modifier `e` khiến chuỗi thay thế trong `preg_replace` được *thực thi* như code PHP, tạo ra lỗ hổng thực thi mã nếu pattern hoặc chuỗi thay thế có thể bị ảnh hưởng bởi input. (Chapter 5, "Beware of preg_replace() Patterns with the e Modifier", `[source: 1188]`-`[source: 1228]`).
* **Phân tích & Áp dụng:** Modifier `e` rất nguy hiểm vì nó tương đương với việc gọi `eval()` trên chuỗi thay thế. Nếu kẻ tấn công kiểm soát được nội dung mà pattern bắt được (backreferences), họ có thể inject code PHP.
* **Lời giải (Giải thích & Thay thế):**
    * **Nguy hiểm:** Modifier `e` trong `preg_replace` nói với PHP rằng: "Sau khi tìm thấy một chuỗi khớp với pattern, hãy lấy chuỗi thay thế (replacement string), coi nó như là code PHP, và thực thi nó". Nếu chuỗi thay thế được xây dựng động dựa trên các phần khớp được từ pattern (ví dụ: dùng backreferences như `\\1`, `$1`), và chuỗi gốc (subject string) đến từ nguồn không đáng tin cậy (ví dụ: input người dùng), kẻ tấn công có thể tạo ra một chuỗi subject sao cho khi khớp pattern, các backreferences chứa mã PHP độc hại. Khi chuỗi thay thế được thực thi, mã độc sẽ chạy. (`[source: 1214]`, `[source: 1218]`, `[source: 1222]`). Modifier `e` đã bị **loại bỏ hoàn toàn** từ PHP 7.0 vì quá nguy hiểm.
    * **Cách thay thế an toàn:** Sử dụng hàm `preg_replace_callback()` hoặc `preg_replace_callback_array()`. Các hàm này nhận một hàm callback (có thể là hàm ẩn danh - anonymous function) làm đối số thay thế. Khi `preg_replace_callback` tìm thấy một chuỗi khớp, nó sẽ gọi hàm callback này và truyền các phần khớp được (matches) vào làm tham số. Bên trong hàm callback, bạn có thể thực hiện logic xử lý an toàn bằng code PHP thông thường để tạo ra chuỗi thay thế mong muốn mà **không cần thực thi code từ chuỗi**.
        ```php
        <?php
        // Ví dụ: Chuyển đổi [tag]thẻ[/tag] thành <strong>thẻ</strong>
        $input = "Đây là [tag]văn bản[/tag] cần làm đậm.";

        // Cách cũ nguy hiểm với modifier 'e' (KHÔNG DÙNG!)
        // $pattern_e = "/\[tag\](.+?)\[\/tag\]/ie";
        // $replacement_e = "'<strong>' . strtoupper('\\1') . '</strong>'";
        // $output_e = preg_replace($pattern_e, $replacement_e, $input);

        // Cách mới an toàn với preg_replace_callback
        // [source: Không có callback trong sách, đây là kiến thức PHP hiện đại]
        $pattern = "/\[tag\](.+?)\[\/tag\]/i";
        $output = preg_replace_callback(
            $pattern,
            function ($matches) {
                // $matches[0] là toàn bộ chuỗi khớp ([tag]văn bản[/tag])
                // $matches[1] là nội dung bên trong thẻ (văn bản)
                $text_inside = $matches[1];
                // Xử lý an toàn bên trong hàm callback
                $processed_text = strtoupper($text_inside);
                return '<strong>' . htmlspecialchars($processed_text) . '</strong>'; // Vẫn nên escape output
            },
            $input
        );

        echo $output; // Output: Đây là <strong>VĂN BẢN</strong> cần làm đậm.
        ?>
        ```

## Chương 6: Enforcing Security for Temporary Files

**Bài tập 6.1: Tạo File Tạm An Toàn**

* **Kiến thức vận dụng:** Sử dụng các hàm PHP để tạo file tạm với tên khó đoán và đặt quyền truy cập hạn chế. (Chapter 6, "Make Locations Difficult", `[source: 1320]`-`[source: 1355]`; "Make Permissions Restrictive", `[source: 1373]`). `[source: 1327]` đề cập `tempnam`, `[source: 1352]` đề cập `uniqid`. `[source: 1335]` và `[source: 1357]` đề cập quyền 600.
* **Phân tích & Áp dụng:** Cần lấy thư mục tạm của hệ thống, tạo tên file ngẫu nhiên (ví dụ dùng `uniqid` kết hợp `rand`), tạo file rỗng (`touch`), rồi đổi quyền (`chmod`).
* **Lời giải (PHP):**
    ```php
    <?php
    /**
     * Tạo file tạm an toàn với tên ngẫu nhiên và quyền 0600.
     *
     * @param string $prefix Tiền tố tùy chọn cho tên file.
     * @return string|false Đường dẫn đầy đủ đến file tạm đã tạo, hoặc false nếu lỗi.
     */
    function create_secure_temp_file($prefix = 'myapp_tmp_') {
        // Lấy thư mục tạm của hệ thống
        $temp_dir = sys_get_temp_dir();
        if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
            error_log("Thư mục tạm không tồn tại hoặc không thể ghi: " . $temp_dir);
            return false;
        }

        // Tạo tên file ngẫu nhiên, khó đoán
        // [source: 1354] - uniqid với entropy bổ sung
        $temp_filename = uniqid($prefix, true);
        $temp_filepath = $temp_dir . DIRECTORY_SEPARATOR . $temp_filename;

        // Tạo file rỗng
        // [source: 1355] - Dùng touch()
        if (@touch($temp_filepath)) {
            // Đặt quyền 0600 (chỉ chủ sở hữu đọc/ghi)
            // [source: 1357], [source: 1361] - Dùng chmod()
            if (@chmod($temp_filepath, 0600)) {
                return $temp_filepath;
            } else {
                error_log("Không thể chmod(0600) cho file tạm: " . $temp_filepath);
                @unlink($temp_filepath); // Xóa file nếu không set được quyền
                return false;
            }
        } else {
            error_log("Không thể tạo file tạm: " . $temp_filepath);
            return false;
        }
    }

    // Ví dụ sử dụng
    $tmp_file = create_secure_temp_file('upload_');
    if ($tmp_file) {
        echo "Đã tạo file tạm thành công: " . $tmp_file . "<br>";
        // ... Ghi dữ liệu vào file ...
        // file_put_contents($tmp_file, "Some data");
        // ... Sau khi dùng xong, nên xóa đi ...
        // unlink($tmp_file);
    } else {
        echo "Tạo file tạm thất bại.";
    }
    ?>
    ```

**Bài tập 6.2: Kiểm tra File Upload**

* **Kiến thức vận dụng:** Sử dụng `is_uploaded_file` để xác minh nguồn gốc file và `move_uploaded_file` để di chuyển file an toàn. (Chapter 6, "Checking Uploaded Files", `[source: 1409]`-`[source: 1419]`).
* **Phân tích & Áp dụng:** Đây là bước kiểm tra quan trọng trước khi xử lý file upload để đảm bảo file không phải do attacker tạo ra trên server hoặc trỏ đến file hệ thống.
* **Lời giải (PHP - Bổ sung vào code xử lý upload):**
    ```php
    <?php
    // Giả sử đã thực hiện các kiểm tra khác (lỗi, size, type, tên file mới...)
    $uploaded_file_info = $_FILES['avatar'] ?? null;
    $destination_path = '/path/to/safe/storage/' . $new_unique_filename; // Ngoài web root

    if ($uploaded_file_info && $uploaded_file_info['error'] === UPLOAD_ERR_OK) {
        $tmp_path = $uploaded_file_info['tmp_name'];

        // *** Kiểm tra is_uploaded_file ***
        // [source: 1410] - Dùng is_uploaded_file
        // [source: 1412]-[source: 1414] - Dùng với $_FILES['...']['tmp_name']
        if (is_uploaded_file($tmp_path)) {
            // *** Di chuyển file ***
             // [source: 1410] - Sau khi kiểm tra mới move
             // Sách không có move_uploaded_file nhưng đây là cách chuẩn
            if (move_uploaded_file($tmp_path, $destination_path)) {
                echo "File uploaded successfully to: " . htmlspecialchars($destination_path);
                // Nên đặt quyền phù hợp cho file đích nếu cần
                // chmod($destination_path, 0644);
            } else {
                echo "Error moving uploaded file.";
                error_log("Failed to move uploaded file from $tmp_path to $destination_path");
            }
        } else {
            // [source: 1415]-[source: 1417] - Xử lý khi is_uploaded_file trả về false
            echo "Security check failed: Invalid upload source.";
            error_log("is_uploaded_file failed for tmp_path: " . $tmp_path);
        }
    } else {
        // Xử lý lỗi upload ban đầu (nếu có)
        echo "Upload error or no file uploaded.";
        if ($uploaded_file_info) {
             error_log("Upload error code: " . $uploaded_file_info['error']);
        }
    }
    ?>
    ```

**Bài tập 6.3: Race Condition và File Locking**

* **Kiến thức vận dụng:** Hiểu về race condition khi nhiều process cùng truy cập tài nguyên chia sẻ (file tạm). Sử dụng `flock` để khóa file. (Chapter 6, "Hijacking" và "Race condition", `[source: 1299]`-`[source: 1315]`). Sách không có ví dụ `flock` nhưng đề cập đến khái niệm khóa (`[source: 4659]` ở Ch17).
* **Phân tích & Áp dụng:** Nếu 2 request gần như đồng thời cùng đọc giá trị từ file cache, sửa đổi, rồi ghi lại, request ghi sau sẽ ghi đè mất thay đổi của request ghi trước. Cần khóa độc quyền (`LOCK_EX`) khi ghi.
* **Lời giải (Mô tả & Ví dụ `flock`):**
    * **Kịch bản Race Condition:** Giả sử có một file `/tmp/counter.txt` chứa một số nguyên. Hai request đồng thời (Request A và Request B) muốn tăng giá trị này lên 1.
        1.  A đọc file, thấy giá trị là 5.
        2.  B đọc file, cũng thấy giá trị là 5.
        3.  A tính toán giá trị mới là 6.
        4.  B tính toán giá trị mới là 6.
        5.  A ghi 6 vào file.
        6.  B ghi 6 vào file.
        Kết quả cuối cùng là 6, trong khi đúng ra phải là 7.
    * **Giảm thiểu bằng `flock`:** Sử dụng khóa độc quyền (`LOCK_EX`) khi thực hiện thao tác đọc-sửa-ghi để đảm bảo chỉ một process thực hiện tại một thời điểm.
        ```php
        <?php
        $counter_file = '/tmp/counter.txt';
        $max_wait_microseconds = 500000; // 0.5 giây chờ khóa

        // Mở file để đọc và ghi, con trỏ ở đầu
        // [source: 1340]-[source: 1343] - Mở file bằng fopen
        $fp = fopen($counter_file, 'c+'); // 'c+' tạo nếu chưa có, không xóa nội dung

        if (!$fp) {
            die("Không thể mở file counter.");
        }

        $start_time = microtime(true);
        $locked = false;
        // Cố gắng lấy khóa độc quyền (ghi), chờ tối đa $max_wait_microseconds
        // [source: 4659] (Ch17) đề cập flock
        while (!(($locked = flock($fp, LOCK_EX))) && (microtime(true) - $start_time < ($max_wait_microseconds / 1000000))) {
            usleep(50000); // Chờ 50ms rồi thử lại
        }


        if ($locked) {
            // --- Vùng Critical Section ---
            // Đọc giá trị hiện tại
            rewind($fp); // Đảm bảo đọc từ đầu
            $count = (int)fread($fp, filesize($counter_file) ?: 1); // Đọc toàn bộ file

            // Tăng giá trị
            $count++;
            echo "Giá trị mới: " . $count . "\n";

            // Ghi lại giá trị mới (xóa nội dung cũ trước)
            ftruncate($fp, 0); // Xóa nội dung file
            rewind($fp);       // Đưa con trỏ về đầu
            fwrite($fp, (string)$count);
            fflush($fp); // Đảm bảo dữ liệu được ghi xuống đĩa

            // Nhả khóa
            flock($fp, LOCK_UN);
            // --- Hết Critical Section ---

        } else {
            // Không lấy được khóa sau thời gian chờ
            echo "Không thể lấy khóa file sau khi chờ.\n";
        }

        fclose($fp);
        ?>
        ```
        *(Lưu ý: File locking có thể hoạt động khác nhau trên các hệ thống file khác nhau (đặc biệt là NFS). Cần test kỹ.)*

## Chương 7: Preventing Session Hijacking

**Bài tập 7.1: Cấu hình Session An Toàn**

* **Kiến thức vận dụng:** Sử dụng `ini_set` hoặc cấu hình `php.ini` để thiết lập các directive liên quan đến bảo mật session cookie. (Chapter 7, "Use Cookies Instead of $_GET Variables", `[source: 1553]`-`[source: 1555]`; "Use Secure Sockets Layer", `[source: 1543]`; "Use Session Timeouts", `[source: 1559]`-`[source: 1561]`). Sách không đề cập `cookie_httponly` nhưng nó rất quan trọng.
* **Phân tích & Áp dụng:** Cần đặt các giá trị phù hợp cho `session.use_only_cookies`, `session.cookie_lifetime`, `session.cookie_secure`, `session.cookie_httponly`.
* **Lời giải (PHP - Đặt ở đầu script trước `session_start()`):**
    ```php
    <?php
    // --- Cấu hình Session An Toàn ---

    // [source: 1553] - Chỉ dùng cookie, không dùng URL (Chống fixation)
    ini_set('session.use_only_cookies', 1);

    // Nếu đang dùng HTTPS, bắt buộc cookie chỉ gửi qua HTTPS
    // [source: 1543] - Khuyến nghị dùng SSL
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    // Chống XSS đánh cắp cookie qua Javascript
    // (Kiến thức ngoài sách nhưng cực kỳ quan trọng)
    ini_set('session.cookie_httponly', 1);

    // Đặt thời gian sống cho cookie (ví dụ: 30 phút = 1800 giây)
    // Nếu đặt 0, cookie sẽ hết hạn khi đóng trình duyệt
    // [source: 1560]-[source: 1561] - Đặt cookie_lifetime
    ini_set('session.cookie_lifetime', 1800);

    // (Tùy chọn) Đặt thời gian dọn rác session phía server (phải >= cookie_lifetime nếu muốn timeout chính xác)
    // [source: 1565] - Đặt gc_maxlifetime
    ini_set('session.gc_maxlifetime', 1800);

    // (Tùy chọn) Thay đổi tên session mặc định
    // session_name("MYAPPSESSID");

    // --- Bắt đầu Session ---
    session_start();

    echo "Session đã được cấu hình an toàn.";
    // ... code ứng dụng khác ...
    ?>
    ```

**Bài tập 7.2: Session Fixation Prevention**

* **Kiến thức vận dụng:** Hiểu Session Fixation là attacker cố định session ID cho nạn nhân. Sử dụng `session_regenerate_id(true)` để tạo ID mới sau khi xác thực, vô hiệu hóa ID cũ. (Chapter 7, "Fixation", `[source: 1528]`-`[source: 1541]`; "Regenerate IDs for Users with Changed Status", `[source: 1572]`-`[source: 1587]`).
* **Phân tích & Áp dụng:** Ngay sau khi kiểm tra thông tin đăng nhập là đúng, phải gọi `session_regenerate_id(true)` trước khi lưu bất kỳ thông tin nào vào `$_SESSION`. Tham số `true` đảm bảo session ID cũ bị xóa.
* **Lời giải (Giải thích):**
    Session Fixation xảy ra khi kẻ tấn công (Attacker) có thể "ép" người dùng (Victim) sử dụng một session ID mà Attacker đã biết trước. Cách phổ biến là Attacker truy cập trang đăng nhập trước để lấy một session ID hợp lệ (nhưng chưa được xác thực), sau đó gửi link chứa session ID đó cho Victim (ví dụ: `http://example.com/login.php?PHPSESSID=attacker_known_id`). Nếu Victim click vào link đó và đăng nhập thành công trên *chính session ID đó*, Attacker (vốn đã biết ID) giờ đây cũng có quyền truy cập vào session đã được xác thực của Victim.

    Việc gọi `session_regenerate_id(true)` **ngay sau khi** xác thực thông tin đăng nhập thành công (tức là biết chắc user là người hợp lệ) sẽ giải quyết vấn đề này. (`[source: 1583]`).
    * `session_regenerate_id()`: Tạo ra một session ID hoàn toàn mới, ngẫu nhiên.
    * `true` (tham số `$delete_old_session`): **Xóa file/dữ liệu session liên quan đến session ID cũ**. Đây là phần quan trọng. Nếu không có `true`, session ID cũ vẫn có thể hợp lệ trong một thời gian ngắn (cho đến khi bị garbage collection), cho phép Attacker vẫn có thể sử dụng nó. Bằng cách xóa ngay lập tức dữ liệu session cũ, session ID mà Attacker biết trở nên vô dụng ngay cả khi Victim vừa đăng nhập thành công bằng ID đó.
    Sau khi regenerate, mọi thông tin xác thực (như user ID) mới được lưu vào `$_SESSION` thuộc về session ID mới, an toàn.

**Bài tập 7.3: Thực hành Login/Logout**

* **Kiến thức vận dụng:** Kết hợp các kiến thức về xử lý form, xác thực (giả lập), quản lý session (`session_start`, `$_SESSION`, `session_regenerate_id`), và hủy session (`session_destroy`, `setcookie`). (Chapter 7).
* **Phân tích & Áp dụng:** Cần tạo 3 file/phần logic: form login, xử lý login, trang dashboard. Trang dashboard kiểm tra session. Chức năng logout cần hủy session hoàn toàn.
* **Lời giải (PHP - Ví dụ cấu trúc 3 file):**

    **1. `login.php` (Hiển thị form và xử lý login)**
    ```php
    <?php
    // --- Cấu hình Session An Toàn (như bài 7.1) ---
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_lifetime', 1800);
    ini_set('session.gc_maxlifetime', 1800);
    session_start();

    $error = '';

    // Nếu đã đăng nhập, chuyển đến dashboard
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
    }

    // Xử lý login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // --- Giả lập kiểm tra CSDL ---
        $valid_user = 'admin';
        $valid_pass = 'password123';
        // Trong thực tế: $user = fetch_user_from_db($username);
        //              if ($user && password_verify($password, $user['password_hash'])) { ... }
        // --- Hết giả lập ---

        if ($username === $valid_user && $password === $valid_pass) {
            // *** Đăng nhập thành công ***

            // [source: 1583] - Regenerate ID TRƯỚC KHI lưu dữ liệu session
            session_regenerate_id(true); // true để xóa session cũ

            // Lưu thông tin user vào session mới
            $_SESSION['user_id'] = 1; // ID giả lập
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = time();

            // Chuyển hướng đến dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Sai username hoặc password.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Login</title></head>
    <body>
        <h1>Login</h1>
        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post">
            Username: <input type="text" name="username"><br>
            Password: <input type="password" name="password"><br>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    ```

    **2. `dashboard.php` (Trang cần đăng nhập)**
    ```php
    <?php
    // --- Cấu hình Session An Toàn (như bài 7.1) ---
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_httponly', 1);
    session_start();

    // Kiểm tra đã đăng nhập chưa
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php'); // Chưa đăng nhập, quay về trang login
        exit;
    }

    // (Tùy chọn) Kiểm tra timeout session phía server
    // $session_duration = 1800; // 30 phút
    // if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $session_duration)) {
    //     session_unset();
    //     session_destroy();
    //     header('Location: login.php?timeout=1');
    //     exit;
    // }
    // $_SESSION['login_time'] = time(); // Cập nhật thời gian hoạt động cuối

    $username = $_SESSION['username'] ?? 'Guest';
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Dashboard</title></head>
    <body>
        <h1>Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        <p><a href="logout.php">Logout</a></p>
    </body>
    </html>
    ```

    **3. `logout.php` (Xử lý đăng xuất)**
    ```php
    <?php
    session_start(); // Phải start session để có thể hủy nó

    // Xóa tất cả biến session
    $_SESSION = array();

    // Nếu muốn hủy session hoàn toàn, xóa cả session cookie.
    // Lưu ý: Điều này sẽ hủy session, và không chỉ dữ liệu session!
    // [source: Không có chi tiết hủy cookie trong sách, nhưng đây là cách chuẩn]
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, // Đặt thời gian về quá khứ
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Cuối cùng, hủy session phía server
    // [source: Không có ví dụ destroy, nhưng đây là hàm chuẩn]
    session_destroy();

    // Chuyển hướng về trang login
    header('Location: login.php?logged_out=1');
    exit;
    ?>
    ```

## Chương 8: Securing REST Services

**Bài tập 8.1: Input Validation (REST)**

* **Kiến thức vận dụng:** Xử lý request body JSON, validate dữ liệu (kiểu, giá trị, phạm vi), trả về mã lỗi HTTP phù hợp (400). (Chapter 8, "A Basic REST Server in PHP", `[source: 1707]`, `[source: 1713]` xử lý POST/PUT; "Restricting Access to Resources and Formats", `[source: 1654]`, `[source: 1667]`-`[source: 1672]` đề cập validate input).
* **Phân tích & Áp dụng:** Cần đọc `php://input`, dùng `json_decode`, kiểm tra sự tồn tại và tính hợp lệ của `product_id` và `quantity`. Nếu lỗi, dùng hàm `sendResponse` (hoặc tương tự) để gửi mã 400 và thông báo lỗi JSON.
* **Lời giải (PHP - Giả sử có hàm `sendJsonResponse`):**
    ```php
    <?php
    // Hàm giả lập để gửi response JSON (Trong thực tế nên dùng class RestUtilities như sách hoặc framework)
    function sendJsonResponse($statusCode, $data) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Chỉ xử lý POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(405, ['error' => 'Method Not Allowed']);
    }

    // Đọc request body
    // [source: 1713] - Đọc input cho PUT (tương tự cho POST JSON)
    $json_payload = file_get_contents('php://input');
    $data = json_decode($json_payload, true); // true để chuyển thành mảng

    // Kiểm tra JSON hợp lệ
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(400, ['error' => 'Invalid JSON payload']);
    }

    // --- Validation ---
    // [source: 1668]-[source: 1671] - Nguyên tắc validate type, length, range
    $errors = [];
    $product_id = null;
    $quantity = null;

    // Validate product_id
    if (!isset($data['product_id'])) {
        $errors['product_id'] = 'Missing product_id';
    } elseif (!is_int($data['product_id']) || $data['product_id'] <= 0) {
        $errors['product_id'] = 'product_id must be a positive integer';
    } else {
        $product_id = $data['product_id'];
    }

    // Validate quantity
    if (!isset($data['quantity'])) {
        $errors['quantity'] = 'Missing quantity';
    } elseif (!is_int($data['quantity']) || $data['quantity'] < 1 || $data['quantity'] > 10) {
        $errors['quantity'] = 'quantity must be an integer between 1 and 10';
    } else {
        $quantity = $data['quantity'];
    }

    // Kiểm tra lỗi validation
    if (!empty($errors)) {
        // [source: 1715]-[source: 1717] - Gửi response lỗi (ví dụ 400)
        sendJsonResponse(400, [
            'error' => 'Invalid input',
            'details' => $errors
        ]);
    }

    // --- Nếu hợp lệ, xử lý tạo đơn hàng ---
    // $order_id = create_order_in_db($product_id, $quantity);
    $order_id = rand(1000, 9999); // Giả lập

    if ($order_id) {
        // [source: 1715]-[source: 1717] - Gửi response thành công (ví dụ 201 Created)
        sendJsonResponse(201, [
            'message' => 'Order created successfully',
            'order_id' => $order_id
        ]);
    } else {
         // [source: 1715], [source: 1720] - Gửi response lỗi server (ví dụ 500)
        sendJsonResponse(500, ['error' => 'Failed to create order']);
    }
    ?>
    ```

**Bài tập 8.2: Authentication (API Key)**

* **Kiến thức vận dụng:** Kiểm tra header request, so sánh giá trị với danh sách/CSDL key hợp lệ, trả về lỗi 401 nếu không khớp. (Chapter 8, "Authenticating/Authorizing RESTful Requests", `[source: 1678]`-`[source: 1680]`).
* **Phân tích & Áp dụng:** Cần lấy giá trị header `Authorization`, trích xuất key (sau `Bearer `), kiểm tra xem key có tồn tại và hợp lệ không. Có thể viết thành hàm hoặc tích hợp vào router/middleware.
* **Lời giải (PHP - Hàm kiểm tra):**
    ```php
    <?php
    /**
     * Kiểm tra API Key từ header Authorization: Bearer.
     *
     * @return bool|string Trả về API key nếu hợp lệ, false nếu không.
     */
    function authenticate_api_key() {
        $headers = apache_request_headers(); // Hoặc dùng cách khác để lấy header

        if (!isset($headers['Authorization'])) {
            // [source: 1715], [source: 1718] - Gửi lỗi 401
            sendJsonResponse(401, ['error' => 'Authorization header missing']);
            return false; // Mặc dù sendJsonResponse đã exit
        }

        $auth_header = $headers['Authorization'];
        // [source: 1679] - Ví dụ kiểm tra key (ở đây là header)
        if (preg_match('/^Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $api_key = $matches[1];

            // --- Giả lập kiểm tra key trong CSDL hoặc danh sách ---
            $valid_keys = ['key123abc', 'key456def', 'secretkey']; // Ví dụ
            $is_valid = in_array($api_key, $valid_keys);
            // Trong thực tế: $is_valid = check_key_in_database($api_key);
            // --- Hết giả lập ---

            if ($is_valid) {
                return $api_key; // Trả về key nếu hợp lệ
            } else {
                 sendJsonResponse(401, ['error' => 'Invalid API Key']);
                 return false;
            }
        } else {
            sendJsonResponse(401, ['error' => 'Malformed Authorization header. Use Bearer schema.']);
            return false;
        }
    }

    // --- Cách sử dụng trong endpoint ---
    /*
    // Đặt ở đầu endpoint cần bảo vệ
    $authenticated_key = authenticate_api_key();
    if (!$authenticated_key) {
        exit; // Đã gửi response lỗi trong hàm authenticate_api_key
    }
    // Nếu đến đây, request đã được xác thực, $authenticated_key chứa key hợp lệ
    echo "Authenticated with key: " . htmlspecialchars($authenticated_key);
    // ... Tiếp tục xử lý request ...
    */

    // Hàm giả lập sendJsonResponse từ bài trước
    function sendJsonResponse($statusCode, $data) {
        // Chỉ để ví dụ chạy được, không nên lặp lại code
        if (!headers_sent()) {
             header('Content-Type: application/json; charset=utf-8');
             http_response_code($statusCode);
        }
        echo json_encode($data);
        // Trong ngữ cảnh thực tế, hàm này sẽ exit
    }

    // Test thử
    // Giả lập header (Cách này chỉ để test, không chạy đúng trên web server)
    // $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer key123abc';
    // $headers['Authorization'] = 'Bearer key123abc'; // Cần hàm apache_request_headers hoặc tương tự
    // authenticate_api_key();

    ?>
    ```
    *(Lưu ý: Lấy header trong PHP có thể khác nhau tùy môi trường (Apache, Nginx/FPM). `apache_request_headers` chỉ hoạt động với Apache. Cách phổ biến hơn là kiểm tra `$_SERVER['HTTP_AUTHORIZATION']`)*.

**Bài tập 8.3: Rate Limiting (Thảo luận)**

* **Kiến thức vận dụng:** Hiểu mục đích của rate limiting (chống lạm dụng, DoS) và các kỹ thuật cơ bản để implement (lưu trữ request count, kiểm tra giới hạn). (Chapter 8, "Enforcing Quotas and Rate Limits", `[source: 1690]`-`[source: 1697]`).
* **Phân tích & Áp dụng:** Cần một cơ chế lưu trữ (DB, cache) để đếm số request theo API key trong một khoảng thời gian.
* **Lời giải (Thảo luận):**
    Để implement Rate Limiting (giới hạn 100 request/giờ/API Key), ta cần:
    1.  **Xác thực API Key:** Trước tiên phải xác định được API key nào đang thực hiện request (như Bài tập 8.2).
    2.  **Lưu trữ Request Count:** Cần một nơi lưu trữ số lượng request đã thực hiện và thời điểm thực hiện cho mỗi API key. Các lựa chọn phổ biến:
        * **Redis/Memcached:** Rất hiệu quả. Sử dụng key có dạng `rate_limit:<api_key>:<giờ_hiện_tại>`. Mỗi khi có request, dùng lệnh `INCR` (atomic increment) cho key này. Đặt `EXPIRE` cho key là 1 giờ. (`[source: 1698]` đề cập cache).
        * **Database:** Tạo bảng `api_requests` với các cột `api_key` (VARCHAR, index), `request_timestamp` (TIMESTAMP, index). Mỗi request hợp lệ sẽ `INSERT` một dòng vào bảng này. (`[source: 1692]`).
    3.  **Kiểm tra Giới hạn:**
        * **Với Redis/Memcached:** Trước khi xử lý request, đọc giá trị của key `rate_limit:<api_key>:<giờ_hiện_tại>`. Nếu giá trị >= 100, từ chối request. Nếu < 100, thực hiện `INCR` và xử lý request.
        * **Với Database:** Trước khi xử lý request, chạy câu lệnh `SELECT COUNT(*) FROM api_requests WHERE api_key = ? AND request_timestamp >= NOW() - INTERVAL 1 HOUR`. Nếu kết quả >= 100, từ chối request. Nếu < 100, `INSERT` dòng mới và xử lý request. (`[source: 1694]`).
    4.  **Trả về Lỗi:** Nếu vượt giới hạn, trả về mã lỗi HTTP `429 Too Many Requests`. Có thể kèm theo header `Retry-After` để chỉ thời gian cần chờ trước khi thử lại.
    5.  **Dọn dẹp (Nếu dùng DB):** Cần có cơ chế xóa các bản ghi request cũ (ví dụ: chạy cron job hàng giờ để xóa các bản ghi `request_timestamp < NOW() - INTERVAL 1 HOUR`). (`[source: 1696]`, `[source: 1697]`). Redis/Memcached tự động xóa key khi hết hạn.

    **Ưu/Nhược điểm:**
    * Redis/Memcached nhanh hơn, tự động hết hạn, phù hợp cho lượng request lớn.
    * Database dễ implement hơn nếu đã có sẵn DB, nhưng chậm hơn và cần cơ chế dọn dẹp.
# Lời giải Bài tập Thực hành Bảo mật PHP (Chương 9-17 - "Pro PHP Security")

## Chương 9: Using CAPTCHAs (Sử dụng CAPTCHA)

**Bài tập 9.1: Tích hợp reCAPTCHA**

* **Kiến thức vận dụng:** Hiểu cách hoạt động của CAPTCHA bên ngoài, cách lấy khóa API, cách hiển thị widget phía client và cách xác thực token phía server. Sách chủ yếu mô tả CAPTCHA tự tạo (`[source: 1861]`) và dịch vụ captchas.net (`[source: 1819]`-`[source: 1827]`), nhưng nguyên tắc chung là gửi dữ liệu user nhập và một định danh thử thách (nonce/token) lên server để xác thực. reCAPTCHA hiện đại hơn và thay thế các dịch vụ cũ.
* **Phân tích & Áp dụng:**
    1.  Đăng ký website với Google reCAPTCHA (v2 Checkbox) để nhận Site Key và Secret Key.
    2.  Nhúng Javascript của reCAPTCHA và thẻ `div` với `data-sitekey` vào form HTML.
    3.  Phía server (PHP), khi nhận form submission, lấy giá trị `$_POST['g-recaptcha-response']`.
    4.  Gửi request POST từ server PHP đến API endpoint của Google (`https://www.google.com/recaptcha/api/siteverify`) kèm theo `secret` (Secret Key) và `response` (giá trị từ `$_POST`).
    5.  Phân tích kết quả JSON trả về từ Google. Nếu `success` là `true`, CAPTCHA hợp lệ.
* **Lời giải (PHP - Phần xử lý Server-side):**
    ```php
    <?php
    $recaptcha_secret = 'YOUR_SECRET_KEY'; // Thay bằng Secret Key của bạn
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($recaptcha_response)) {
            echo "Lỗi: Vui lòng xác nhận bạn không phải là robot.";
        } else {
            // [source: 1819] - Nguyên tắc: Gửi dữ liệu lên server ngoài để xác thực
            // Thay vì captchas.net, ta dùng Google API
            $verify_url = '[https://www.google.com/recaptcha/api/siteverify](https://www.google.com/recaptcha/api/siteverify)';
            $data = [
                'secret'   => $recaptcha_secret,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR'] // Tùy chọn nhưng nên có
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 5 // Đặt timeout
                ],
                 // [source: 4303] (Ch16) - Cần xử lý context cho kết nối mạng, ví dụ SSL verify
                 'ssl' => [
                     'verify_peer' => true,
                     'verify_peer_name' => true,
                 ]
            ];
            $context  = stream_context_create($options);
            $verify_result = @file_get_contents($verify_url, false, $context);

            if ($verify_result === FALSE) {
                 echo "Lỗi: Không thể kết nối đến dịch vụ reCAPTCHA.";
                 error_log("reCAPTCHA verification failed: Could not connect");
            } else {
                $response_data = json_decode($verify_result);
                if ($response_data && $response_data->success) {
                    // CAPTCHA hợp lệ, xử lý form submission (ví dụ: gửi email liên hệ)
                    echo "Xác thực CAPTCHA thành công! Đã xử lý form.";
                    // ... Xử lý dữ liệu form khác ...
                } else {
                    echo "Lỗi: Xác thực CAPTCHA thất bại. Vui lòng thử lại.";
                    // Log lỗi chi tiết từ $response_data->{'error-codes'} nếu cần
                    error_log("reCAPTCHA verification failed: " . print_r($response_data->{'error-codes'} ?? 'Unknown error', true));
                }
            }
        }
    }

    // Phần HTML Form (cần có Javascript và div của reCAPTCHA)
    /*
    <form action="" method="POST">
        <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
        <br/>
        <input type="submit" value="Gửi">
    </form>
    <script src="[https://www.google.com/recaptcha/api.js](https://www.google.com/recaptcha/api.js)" async defer></script>
    */
    ?>
    ```

**Bài tập 9.2: Phân tích CAPTCHA Ngoài vs Tự xây dựng**

* **Kiến thức vận dụng:** Hiểu ưu nhược điểm của các loại CAPTCHA và quá trình triển khai. (Chapter 9, "Potential Problems in Using Captchas", `[source: 1958]`-`[source: 1985]`; "Creating Your Own Captcha Test", `[source: 1861]`).
* **Phân tích & Áp dụng:** Cần so sánh về độ khó triển khai, bảo mật, khả năng chống bot, khả năng truy cập, chi phí, và khả năng tùy biến.
* **Lời giải (Phân tích):**

    | Tiêu chí             | Dịch vụ Ngoài (reCAPTCHA)                     | Tự xây dựng (GD Library)                       |
    | :------------------- | :-------------------------------------------- | :-------------------------------------------- |
    | **Triển khai** | Dễ dàng, chỉ cần đăng ký, nhúng JS, gọi API.  | Phức tạp hơn, cần code tạo ảnh, font, logic.  |
    | **Bảo mật** | Cao (thường xuyên cập nhật thuật toán chống bot bởi Google). (`[source: 1962]`) | Thấp hơn, dễ bị tấn công nếu thuật toán đơn giản hoặc không được cập nhật. (`[source: 1771]`-`[source: 1773]`, `[source: 1964]`) |
    | **Chống Bot** | Rất tốt, sử dụng AI/Machine Learning phức tạp. | Hiệu quả tùy thuộc độ phức tạp của ảnh tạo ra. Dễ bị lỗi thời. (`[source: 1964]`) |
    | **Khả năng Truy cập** | Tốt hơn (thường có tùy chọn audio, reCAPTCHA v3 gần như vô hình). (`[source: 1790]`) | Thường kém (chỉ hình ảnh). Cần tự implement audio/alternative khác. (`[source: 1769]`) |
    | **Chi phí** | Miễn phí (với giới hạn sử dụng nhất định) hoặc trả phí. | Miễn phí (ngoại trừ công sức phát triển). |
    | **Tài nguyên Server** | Tối thiểu (chỉ gọi API).                      | Tốn CPU/Memory để tạo ảnh. (`[source: 1969]`) |
    | **Tùy biến** | Hạn chế (chủ yếu giao diện widget).           | Hoàn toàn tùy biến giao diện và logic.      |
    | **Phụ thuộc** | Phụ thuộc vào dịch vụ của Google.             | Không phụ thuộc bên ngoài.                     |

    **Kết luận:** Dịch vụ ngoài như reCAPTCHA thường là lựa chọn tốt hơn cho hầu hết ứng dụng vì dễ triển khai, bảo mật cao và khả năng truy cập tốt hơn. Tự xây dựng chỉ nên cân nhắc nếu có yêu cầu tùy biến rất đặc biệt hoặc không muốn phụ thuộc bên thứ ba và chấp nhận rủi ro bảo mật/công sức cao hơn.

**Bài tập 9.3: Thiết kế CAPTCHA Tốt hơn (GD)**

* **Kiến thức vận dụng:** Hiểu các kỹ thuật làm khó OCR được đề cập trong sách. (Chapter 9, "Text Image Captchas", `[source: 1774]`-`[source: 1779]`).
* **Phân tích & Áp dụng:** Cần kết hợp nhiều yếu tố gây nhiễu cho máy nhưng vẫn dễ đọc với người.
* **Lời giải (Các kỹ thuật):**
    1.  **Nền nhiễu (Noise Background):** Vẽ các đường kẻ ngẫu nhiên, chấm pixel màu sắc khác nhau, hoặc sử dụng ảnh nền có hoa văn phức tạp lên trên ảnh CAPTCHA. (`[source: 1774]` đề cập confusing background).
    2.  **Làm méo/xoay ký tự (Distortion/Rotation):** Xoay nhẹ từng ký tự hoặc toàn bộ chuỗi một góc ngẫu nhiên. Áp dụng hiệu ứng sóng (wave) hoặc các biến dạng hình học khác. (`[source: 1774]` đề cập twisted image, `[source: 1918]` ví dụ code xoay).
    3.  **Chồng chéo/dính liền ký tự (Overlapping/Connected Characters):** Làm cho các ký tự hơi chồng lên nhau hoặc nối liền nhau một cách tinh tế, gây khó khăn cho việc phân đoạn ký tự của OCR. (`[source: 1782]` đề cập overlapping pairs).
    4.  **(Nâng cao) Dùng nhiều Font/Kích thước:** Sử dụng nhiều loại font chữ và kích thước khác nhau trong cùng một chuỗi CAPTCHA.
    5.  **(Nâng cao) Màu sắc tương phản thấp:** Sử dụng màu chữ và màu nền không quá tương phản, nhưng vẫn đủ để người đọc được.

    **Phương án thay thế (Accessibility):** Cung cấp nút "Tải lại CAPTCHA khác" (`[source: 1979]`). Quan trọng nhất là cung cấp **Audio CAPTCHA** (`[source: 1790]`) cho người khiếm thị. Có thể tạo file âm thanh đọc các ký tự/số với giọng nói bị làm nhiễu hoặc có tạp âm nền.

## Chương 10: User Authentication, Authorization, and Logging

**Bài tập 10.1: Xác thực Email bằng Token**

* **Kiến thức vận dụng:** Quy trình gửi email chứa token duy nhất để xác minh người dùng sở hữu địa chỉ email. Lưu và kiểm tra token. (Chapter 10, "Verifying Receipt with a Token", `[source: 2037]`-`[source: 2040]`). Ví dụ code chi tiết ở `[source: 2041]`-`[source: 2065]`.
* **Phân tích & Áp dụng:** Cần các bước: Lưu email + token (với status 'pending' + expiry) vào DB -> Gửi email chứa link với token -> Script xử lý link: tìm token trong DB, kiểm tra expiry, cập nhật status thành 'verified'.
* **Lời giải (PHP - Logic chính):**
    ```php
    <?php
    // Giả sử có kết nối CSDL $db (MySQLi/PDO)

    // Bước 1: Khi User Đăng ký / Yêu cầu Xác thực
    function request_email_verification($email) {
        global $db;
        // 1. Tạo token ngẫu nhiên, duy nhất và khó đoán
        // [source: 2047] - Dùng uniqid + rand
        $token = bin2hex(random_bytes(16)); // An toàn hơn uniqid
        $expires_at = date('Y-m-d H:i:s', time() + 3600); // Hết hạn sau 1 giờ

        // 2. Lưu vào CSDL (Bảng verification_tokens(email, token, expires_at, is_verified))
        // Dùng Prepared Statement!
        $sql = "INSERT INTO verification_tokens (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sss", $email, $token, $expires_at);
        if (!$stmt->execute()) {
            error_log("Failed to save verification token for " . $email);
            return false;
        }
        $stmt->close();

        // 3. Gửi Email
        // [source: 2048] - Tạo URI xác thực
        $verification_link = "[https://yourdomain.com/verify_email.php?token=](https://yourdomain.com/verify_email.php?token=)" . $token;
        $subject = "Xác thực địa chỉ email của bạn";
        // [source: 2049] - Nội dung email
        $message = "Chào bạn,\n\nVui lòng click vào link sau để xác thực email:\n" . $verification_link . "\n\nLink có hiệu lực trong 1 giờ.";
        $headers = 'From: webmaster@yourdomain.com'; // Thay đổi địa chỉ From

        // [source: 2049] - Gửi mail
        if (mail($email, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Failed to send verification email to " . $email);
            // Có thể cần xóa token đã lưu nếu gửi mail thất bại
            return false;
        }
    }

    // Bước 2: Script verify_email.php (Khi User Click Link)
    function verify_email_token($token) {
        global $db;
        if (empty($token) || !ctype_xdigit($token) || strlen($token) !== 32) { // Kiểm tra định dạng token (ví dụ bin2hex)
             return ['success' => false, 'message' => 'Token không hợp lệ.'];
        }

        // 1. Tìm token trong CSDL
        $sql = "SELECT email, expires_at, is_verified FROM verification_tokens WHERE token = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $token_data = $result->fetch_assoc();
        $stmt->close();

        // [source: 2057]-[source: 2060] - Kiểm tra token
        if (!$token_data) {
            return ['success' => false, 'message' => 'Token không tồn tại.'];
        }

        if ($token_data['is_verified']) {
             return ['success' => true, 'message' => 'Email đã được xác thực trước đó.']; // Hoặc thông báo khác
        }

        // Kiểm tra hết hạn
        if (strtotime($token_data['expires_at']) < time()) {
            // Xóa token hết hạn nếu muốn
            // $db->query("DELETE FROM verification_tokens WHERE token = '$token'");
            return ['success' => false, 'message' => 'Token đã hết hạn. Vui lòng yêu cầu lại.'];
        }

        // 2. Xác thực thành công: Cập nhật CSDL
        $email_to_verify = $token_data['email'];
        // Cập nhật bảng users (ví dụ: đặt is_email_verified = 1)
        $update_user_sql = "UPDATE users SET is_email_verified = 1 WHERE email = ?";
        $stmt_user = $db->prepare($update_user_sql);
        $stmt_user->bind_param("s", $email_to_verify);
        $user_updated = $stmt_user->execute();
        $stmt_user->close();

        // Cập nhật bảng tokens (đánh dấu đã dùng hoặc xóa)
        $update_token_sql = "UPDATE verification_tokens SET is_verified = 1 WHERE token = ?";
        // Hoặc "DELETE FROM verification_tokens WHERE token = ?"
        $stmt_token = $db->prepare($update_token_sql);
        $stmt_token->bind_param("s", $token);
        $token_updated = $stmt_token->execute();
        $stmt_token->close();
        // [source: 2058] - Xóa token khỏi session (nếu dùng session thay DB)

        if ($user_updated && $token_updated) {
             // [source: 2057] - Thông báo thành công
             return ['success' => true, 'message' => 'Xác thực email thành công!'];
        } else {
             error_log("Failed to update verification status for token: " . $token);
             return ['success' => false, 'message' => 'Có lỗi xảy ra trong quá trình xác thực.'];
        }
    }

    // --- Trong verify_email.php ---
    /*
    if (isset($_GET['token'])) {
        $result = verify_email_token($_GET['token']);
        echo $result['message'];
    } else {
        echo "Thiếu token xác thực.";
    }
    */
    ?>
    ```

**Bài tập 10.2: Thiết kế CSDL RBAC**

* **Kiến thức vận dụng:** Hiểu mô hình RBAC, các thành phần cơ bản (User, Role, Permission) và mối quan hệ giữa chúng. (Chapter 10, "Roles-Based Access Control", `[source: 2210]`-`[source: 2233]`).
* **Phân tích & Áp dụng:** Cần các bảng để lưu thông tin user, role, permission và các bảng trung gian để thể hiện mối quan hệ nhiều-nhiều.
* **Lời giải (SQL Schema):**
    ```sql
    -- Bảng người dùng
    CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL, -- Dùng bcrypt
        email VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Bảng vai trò (Roles)
    -- [source: 2235] - Role là đơn vị cơ bản
    CREATE TABLE roles (
        role_id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) UNIQUE NOT NULL,
        description TEXT
    );

    -- Bảng quyền hạn (Permissions)
    -- [source: 2237] - Permission là danh sách action được phép
    CREATE TABLE permissions (
        permission_id INT AUTO_INCREMENT PRIMARY KEY,
        permission_name VARCHAR(100) UNIQUE NOT NULL, -- Ví dụ: 'edit_article', 'delete_user'
        description TEXT
    );

    -- Bảng trung gian: Gán quyền cho vai trò (Nhiều-Nhiều)
    -- [source: 2216] - Role chứa tập hợp permission
    CREATE TABLE role_permissions (
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
    );

    -- Bảng trung gian: Gán vai trò cho người dùng (Nhiều-Nhiều)
    -- [source: 2238] - User được gán role
    CREATE TABLE user_roles (
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE
    );

    -- (Tùy chọn) Có thể thêm bảng Groups và gán user/role vào group
    -- CREATE TABLE groups ( group_id INT..., group_name... );
    -- CREATE TABLE user_groups ( user_id INT, group_id INT ... );
    -- CREATE TABLE group_roles ( group_id INT, role_id INT ... );
    ```

**Bài tập 10.3: Logging Hành động**

* **Kiến thức vận dụng:** Ghi lại các sự kiện quan trọng trong ứng dụng để phục vụ việc audit, debug, và phân tích bảo mật. (Chapter 10, "Logging Data", `[source: 2405]`-`[source: 2417]`). Cần ghi đủ thông tin ngữ cảnh (`[source: 2439]`-`[source: 2455]`).
* **Phân tích & Áp dụng:** Cần tạo bảng log, viết lớp Logger để đóng gói việc ghi log, định dạng thông điệp log chuẩn hóa.
* **Lời giải (PHP Class & Bảng SQL):**

    **SQL (Bảng activity_log):**
    ```sql
    CREATE TABLE activity_log (
        log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        log_level VARCHAR(10) NOT NULL, -- e.g., INFO, WARNING, ERROR, SECURITY
        user_id INT NULL, -- NULL nếu hành động không do user cụ thể thực hiện
        ip_address VARCHAR(45) NULL, -- Hỗ trợ IPv6
        action_name VARCHAR(100) NOT NULL, -- Ví dụ: 'user_login', 'update_profile', 'delete_order'
        details TEXT NULL, -- Lưu dữ liệu context dạng JSON hoặc text
        INDEX idx_log_time (log_time),
        INDEX idx_user_id (user_id),
        INDEX idx_action_name (action_name)
        -- FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL -- Tùy chọn
    );
    ```

    **PHP (Lớp Logger):**
    ```php
    <?php
    class Logger {
        private $log_file_path;
        private $db_connection; // Tùy chọn: Ghi vào DB

        // [source: 2435] - Nên lưu log vào file riêng hoặc DB
        public function __construct($log_file = '/var/log/app/activity.log', $db = null) {
            $this->log_file_path = $log_file;
            $this->db_connection = $db;

            // Đảm bảo thư mục log tồn tại và có thể ghi
            $log_dir = dirname($this->log_file_path);
            if (!is_dir($log_dir)) {
                @mkdir($log_dir, 0750, true); // Chỉ chủ sở hữu và group đọc/ghi/execute
            }
            if (!is_writable($log_dir) || (file_exists($this->log_file_path) && !is_writable($this->log_file_path))) {
                 // Fallback hoặc ném exception nghiêm trọng hơn
                error_log("Cảnh báo: Không thể ghi vào file log: " . $this->log_file_path);
                $this->log_file_path = null; // Không ghi vào file nếu lỗi
            }
        }

        /**
         * Ghi log một hành động.
         *
         * @param string $level Mức độ log (INFO, WARNING, ERROR, SECURITY, etc.)
         * @param string $action Tên hành động
         * @param array $context Dữ liệu ngữ cảnh (ví dụ: user_id, ip, params)
         */
        public function log($level, $action, array $context = []) {
            // [source: 2445] - Luôn ghi timestamp
            $timestamp = date('Y-m-d H:i:s');
            // [source: 2448] - Lấy user ID nếu có
            $user_id = $context['user_id'] ?? ($_SESSION['user_id'] ?? null); // Lấy từ context hoặc session
            // Lấy IP
            $ip_address = $context['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
            // Chuyển context array thành JSON để dễ lưu trữ/truy vấn
            // [source: 2451]-[source: 2455] - Có thể cần serialize dữ liệu request/context
            $details_json = !empty($context) ? json_encode($context) : null;

            // Định dạng log cho file
            $log_entry = sprintf(
                "[%s] [%s] [User:%s] [IP:%s] [Action:%s] %s\n",
                $timestamp,
                strtoupper($level),
                $user_id ?? 'N/A',
                $ip_address ?? 'N/A',
                $action,
                $details_json ?? ''
            );

            // Ghi vào file (nếu có thể)
            // [source: 2456] - Có thể dùng file_put_contents hoặc error_log type 3
            if ($this->log_file_path) {
                 // Dùng LOCK_EX để tránh race condition khi ghi log đồng thời
                @file_put_contents($this->log_file_path, $log_entry, FILE_APPEND | LOCK_EX);
            } else {
                // Fallback ghi vào error_log hệ thống nếu không ghi được file riêng
                error_log("AppLog Fallback: " . trim($log_entry));
            }


            // (Tùy chọn) Ghi vào CSDL
            if ($this->db_connection) {
                try {
                    $sql = "INSERT INTO activity_log (log_time, log_level, user_id, ip_address, action_name, details)
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->db_connection->prepare($sql);
                    // Chuyển null PHP thành NULL SQL
                    $db_user_id = $user_id !== null ? (int)$user_id : null;
                    $db_ip = $ip_address !== null ? (string)$ip_address : null;

                    $stmt->bind_param(
                        "ssisss",
                        $timestamp,
                        $level,
                        $db_user_id,
                        $db_ip,
                        $action,
                        $details_json
                    );
                    $stmt->execute();
                    $stmt->close();
                } catch (Exception $e) {
                    error_log("DB Logging failed: " . $e->getMessage());
                }
            }
        }
    }

    // Ví dụ sử dụng Logger
    /*
    session_start(); // Giả sử session đã start và có user_id
    $_SESSION['user_id'] = 123;

    // Giả sử có kết nối CSDL $db
    // $db = new mysqli(...);

    $logger = new Logger('/var/log/app/my_app.log', null); // Ghi ra file, ko ghi DB

    // Ghi log đăng nhập thành công
    $logger->log('INFO', 'user_login_success', ['username' => 'admin']);

    // Ghi log lỗi thanh toán
    $logger->log('ERROR', 'payment_failed', ['order_id' => 456, 'error_code' => 'declined']);

    // Ghi log bảo mật (ví dụ: thay đổi quyền)
    $logger->log('SECURITY', 'permission_change', ['admin_id' => 1, 'target_user' => 789, 'new_role' => 'editor']);
    */
    ?>
    ```

## Chương 11: Preventing Data Loss

**Bài tập 11.1: Soft Delete & Undelete**

* **Kiến thức vận dụng:** Sử dụng cờ (flag) hoặc timestamp để đánh dấu bản ghi đã xóa thay vì xóa vật lý. (Chapter 11, "Adding a Deleted Flag to a Table", `[source: 2528]`-`[source: 2542]`). Dùng `deleted_at` tốt hơn `is_deleted` vì biết được thời điểm xóa.
* **Phân tích & Áp dụng:** Sửa bảng `products` thêm cột `deleted_at TIMESTAMP NULL DEFAULT NULL`. Hàm `delete_product` sẽ `UPDATE products SET deleted_at = NOW() WHERE id = ?`. Hàm `undelete_product` sẽ `UPDATE products SET deleted_at = NULL WHERE id = ?`.
* **Lời giải (PHP - Hàm):**
    ```php
    <?php
    // Giả sử có kết nối $db (MySQLi/PDO)

    /**
     * Thực hiện soft delete cho sản phẩm.
     * @param int $product_id ID sản phẩm cần xóa.
     * @return bool True nếu thành công, False nếu thất bại.
     */
    function delete_product($product_id) {
        global $db;
        // Validate ID
        if (!ctype_digit((string)$product_id) || (int)$product_id <= 0) return false;
        $product_id = (int)$product_id;

        // [source: 2540]-[source: 2541] - UPDATE cờ deleted (ở đây là deleted_at)
        $sql = "UPDATE products SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL"; // Chỉ xóa nếu chưa bị xóa
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $success = $stmt->execute();
        // Có thể kiểm tra $stmt->affected_rows > 0 để chắc chắn có bản ghi bị update
        $stmt->close();
        return $success;
    }

    /**
     * Khôi phục sản phẩm đã bị soft delete.
     * @param int $product_id ID sản phẩm cần khôi phục.
     * @return bool True nếu thành công, False nếu thất bại.
     */
    function undelete_product($product_id) {
        global $db;
        // Validate ID
        if (!ctype_digit((string)$product_id) || (int)$product_id <= 0) return false;
        $product_id = (int)$product_id;

        // [source: 2581] - Có thể tạo giao diện undelete
        $sql = "UPDATE products SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL"; // Chỉ khôi phục nếu đang bị xóa
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $success = $stmt->execute();
        // Có thể kiểm tra $stmt->affected_rows > 0
        $stmt->close();
        return $success;
    }

    /**
     * Lấy danh sách sản phẩm đã bị soft delete.
     * @return array Mảng các sản phẩm đã xóa hoặc mảng rỗng.
     */
    function get_deleted_products() {
        global $db;
        // [source: 2582] - Truy vấn các bản ghi đã xóa
        $sql = "SELECT id, name, deleted_at FROM products WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
        $result = $db->query($sql);
        $deleted_products = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $deleted_products[] = $row;
            }
            $result->free();
        }
        return $deleted_products;
    }
    ?>
    ```

**Bài tập 11.2: Querying Soft Deletes**

* **Kiến thức vận dụng:** Điều chỉnh các câu lệnh `SELECT` để loại trừ các bản ghi đã bị soft delete. (Chapter 11, "Enforcing the Deleted Field in SELECT Queries", `[source: 2549]`-`[source: 2551]`).
* **Phân tích & Áp dụng:** Cần thêm điều kiện `WHERE deleted_at IS NULL` vào các câu lệnh `SELECT` lấy dữ liệu thông thường.
* **Lời giải (PHP - Hàm):**
    ```php
    <?php
    // Giả sử có kết nối $db (MySQLi/PDO)

    /**
     * Lấy danh sách các sản phẩm đang hoạt động (chưa bị soft delete).
     * @return array Mảng các sản phẩm hoặc mảng rỗng.
     */
    function get_active_products() {
        global $db;
        // [source: 2550]-[source: 2551] - Thêm điều kiện WHERE deleted = '0' (hoặc IS NULL)
        $sql = "SELECT id, name, price FROM products WHERE deleted_at IS NULL ORDER BY name ASC";
        $result = $db->query($sql);
        $active_products = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $active_products[] = $row;
            }
            $result->free();
        }
        return $active_products;
    }

    // Hàm get_deleted_products() đã có ở bài 11.1
    ?>
    ```

**Bài tập 11.3: Confirmation Dialog**

* **Kiến thức vận dụng:** Sử dụng cơ chế xác nhận (client-side hoặc server-side) trước khi thực hiện hành động nguy hiểm như xóa. (Chapter 11, "Adding a Confirmation Dialog Box to an Action", `[source: 2490]`-`[source: 2500]`). Sách đề cập dùng secret key trong session để chống XSS/CSRF (`[source: 2509]`-`[source: 2519]`).
* **Phân tích & Áp dụng:**
    1.  Khi user click link/nút "Xóa", không xóa ngay.
    2.  **Cách 1 (Client-side - Đơn giản):** Dùng Javascript `confirm()`. Dễ bị bypass, không an toàn lắm.
    3.  **Cách 2 (Server-side - An toàn hơn):** Chuyển hướng user đến trang `confirm_delete.php?id=xxx`. Trang này hiển thị thông tin sản phẩm và form với nút "Đồng ý xóa" và "Hủy".
    4.  **Bảo mật thêm (Chống CSRF):** Trang `confirm_delete.php` tạo một token ngẫu nhiên, lưu vào session và đặt vào hidden field trong form xác nhận. Khi user submit form xác nhận, server kiểm tra token này có khớp với token trong session không trước khi thực hiện xóa. (`[source: 2509]`)
* **Lời giải (Mô tả luồng Server-side):**
    1.  **Trang danh sách sản phẩm (`products.php`):**
        * Mỗi sản phẩm có link "Xóa": `<a href="confirm_delete.php?id=<?php echo $product['id']; ?>">Xóa</a>`
    2.  **Trang xác nhận (`confirm_delete.php`):**
        ```php
        <?php
        session_start();
        // Lấy ID sản phẩm, validate
        $product_id = $_GET['id'] ?? null;
        if (!ctype_digit((string)$product_id)) die('ID không hợp lệ');
        // Lấy thông tin sản phẩm từ DB để hiển thị
        // $product = get_product_by_id($product_id);
        $product = ['id' => $product_id, 'name' => 'Sản phẩm Test']; // Giả lập
        if (!$product) die('Sản phẩm không tồn tại');

        // [source: 2514] - Tạo confirmation key (CSRF token)
        $csrf_token = bin2hex(random_bytes(32));
        $_SESSION['delete_token'] = $csrf_token; // Lưu vào session
        $_SESSION['token_product_id'] = $product_id; // Lưu cả ID để chắc chắn đúng sp

        ?>
        <h1>Xác nhận Xóa</h1>
        <p>Bạn có chắc chắn muốn xóa sản phẩm sau?</p>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($product['id']); ?></p>
        <p><strong>Tên:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
        <form action="process_delete.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Đồng ý Xóa</button>
            <a href="products.php">Hủy</a>
        </form>
        ```
    3.  **Trang xử lý xóa (`process_delete.php`):**
        ```php
        <?php
        session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Invalid request method');

        $product_id = $_POST['product_id'] ?? null;
        $submitted_token = $_POST['csrf_token'] ?? null;

        // [source: 2517] - Kiểm tra token và ID khớp với session
        if (empty($product_id) ||
            empty($submitted_token) ||
            !isset($_SESSION['delete_token']) ||
            !isset($_SESSION['token_product_id']) ||
            !hash_equals($_SESSION['delete_token'], $submitted_token) || // Dùng hash_equals chống timing attack
            $_SESSION['token_product_id'] != $product_id )
        {
            // Xóa token cũ để tránh dùng lại
            unset($_SESSION['delete_token']);
            unset($_SESSION['token_product_id']);
            die('Lỗi xác nhận hoặc token không hợp lệ/hết hạn. Vui lòng thử lại.');
        }

        // Xóa token sau khi dùng
        unset($_SESSION['delete_token']);
        unset($_SESSION['token_product_id']);

        // Thực hiện soft delete (gọi hàm từ bài 11.1)
        // [source: 2518] - Nếu xác nhận thì thực hiện
        // $success = delete_product($product_id);
        $success = true; // Giả lập

        if ($success) {
            echo "Đã xóa sản phẩm thành công.";
            // Redirect về trang danh sách
            // header('Location: products.php?deleted=success'); exit;
        } else {
            echo "Có lỗi xảy ra khi xóa sản phẩm.";
        }
        ?>
        ```

## Chương 12: Safe Execution of System and Remote Procedure Calls

**Bài tập 12.1: Thảo luận Rủi ro & Giải pháp (Lệnh hệ thống)**

* **Kiến thức vận dụng:** Hiểu sự nguy hiểm của việc thực thi lệnh hệ thống có đặc quyền (`mount`, `rm`, `chown`) trực tiếp từ ứng dụng web (thường chạy với quyền thấp). Cần tách biệt quyền và dùng cơ chế giao tiếp an toàn. (Chapter 12, "Dangerous Operations", `[source: 2706]`-`[source: 2710]`; "Making Dangerous Operations Safe", `[source: 2730]`, "Create an API for Root-level Operations", `[source: 2735]`-`[source: 2751]`; "Queue Resource-intensive Operations", `[source: 2752]`-`[source: 2764]`).
* **Phân tích & Áp dụng:** User web server (e.g., `www-data`) không nên có quyền thực thi các lệnh này. Nếu thực thi trực tiếp (ví dụ qua `sudo` được cấu hình sai hoặc SUID binary), một lỗ hổng trong PHP có thể bị leo thang thành chiếm quyền root.
* **Lời giải (Thảo luận):**
    **Rủi ro khi thực thi trực tiếp lệnh hệ thống đặc quyền từ PHP:**
    1.  **Leo thang Đặc quyền (Privilege Escalation):** Rủi ro lớn nhất. Nếu script PHP (chạy dưới quyền user web server như `www-data`) có thể thực thi lệnh bằng `sudo` (do cấu hình `/etc/sudoers` quá lỏng lẻo) hoặc gọi một SUID binary tùy chỉnh không an toàn (`[source: 2711]`, `[source: 2715]`-`[source: 2717]`), một kẻ tấn công tìm được lỗ hổng trong script PHP (như command injection) có thể lợi dụng để thực thi lệnh với quyền root, chiếm toàn bộ server.
    2.  **Command Injection:** Nếu lệnh hệ thống được xây dựng bằng cách nối chuỗi với input từ người dùng mà không được escape đúng cách (`escapeshellarg`/`cmd`), kẻ tấn công có thể inject thêm lệnh shell tùy ý (ví dụ: `rm -rf /`). (`[source: 1037]` Ch5).
    3.  **Phá hoại/Gián đoạn Dịch vụ:** Ngay cả khi không chiếm được quyền root, kẻ tấn công có thể thực thi các lệnh như `rm`, `mount`, `shutdown` để xóa dữ liệu hoặc làm ngừng hoạt động của server. (`[source: 2719]`).

    **Giải pháp an toàn hơn:**
    1.  **Tách biệt và Giao tiếp qua API/Queue (Khuyến nghị):**
        * Xây dựng một script hoặc daemon riêng biệt chạy nền với quyền cần thiết (ví dụ: root hoặc user đặc biệt). Script này **không** được truy cập trực tiếp từ web. (`[source: 2733]`-`[source: 2734]`).
        * Ứng dụng PHP (chạy với quyền `www-data`) sẽ **không** thực thi lệnh trực tiếp. Thay vào đó, nó gửi một **yêu cầu** (message) vào một hàng đợi (message queue như RabbitMQ, Redis Queue, hoặc đơn giản là bảng trong CSDL - `[source: 2856]`) hoặc gọi một API nội bộ (ví dụ: socket Unix, HTTP request đến localhost trên port khác) đã được định nghĩa chặt chẽ. (`[source: 2742]`-`[source: 2744]`).
        * Yêu cầu này chỉ chứa thông tin cần thiết và đã được validate/sanitize (ví dụ: chỉ tên file cần `chown`, không phải toàn bộ lệnh).
        * Worker process (chạy với quyền cao) đọc yêu cầu từ queue/API, kiểm tra tính hợp lệ lần nữa, và **chỉ thực thi hành động cụ thể đã được lập trình sẵn** (ví dụ: chỉ gọi `chown` trên file trong thư mục cho phép), không bao giờ thực thi lệnh shell tùy ý từ yêu cầu. (`[source: 2749]`-`[source: 2751]`).
        * Kết quả có thể được gửi lại qua queue/API hoặc cập nhật vào CSDL.
    2.  **(Ít an toàn hơn) SUID Wrapper Script Cẩn thận:** Tạo một script wrapper (ví dụ: bằng C hoặc thậm chí Perl/Python) rất nhỏ, được kiểm định kỹ lưỡng, có SUID bit và thuộc sở hữu của root. Script này chỉ chấp nhận các tham số rất cụ thể và thực hiện một hành động duy nhất, cực kỳ hạn chế. PHP gọi script wrapper này. *Cách này vẫn tiềm ẩn rủi ro nếu wrapper có lỗi.*
    3.  **Tránh hoàn toàn:** Xem xét lại thiết kế ứng dụng để không cần thực thi các lệnh hệ thống đặc quyền từ PHP nếu có thể.

**Bài tập 12.2: Xử lý Timeout Kết nối Mạng (fsockopen)**

* **Kiến thức vận dụng:** Sử dụng tham số timeout trong `fsockopen` và hàm `stream_set_timeout` để giới hạn thời gian chờ kết nối và chờ phản hồi, tránh treo process PHP. (Chapter 12, "Handle Network Timeouts", `[source: 3082]`-`[source: 3090]`). Ví dụ code ở `[source: 3091]`-`[source: 3103]`.
* **Phân tích & Áp dụng:** Cần truyền tham số timeout thứ 5 cho `fsockopen`. Sau khi kết nối thành công, dùng `stream_set_timeout` để đặt thời gian chờ đọc. Sau khi đọc (`stream_get_contents` hoặc `fgets`), kiểm tra metadata (`stream_get_meta_data`) xem có bị timeout không.
* **Lời giải (PHP):**
    ```php
    <?php
    $host = 'example.com'; // Host cần kết nối
    $port = 80;            // Port (80 for http, 443 for https)
    $connection_timeout = 5; // Giây - Thời gian chờ tối đa để thiết lập kết nối ban đầu
    $response_timeout = 10;  // Giây - Thời gian chờ tối đa để đọc dữ liệu sau khi đã kết nối

    // [source: 3096] - Mở socket với connection timeout
    echo "Đang kết nối đến $host:$port (timeout $connection_timeout giây)...\n";
    $fp = @fsockopen($host, $port, $errno, $errstr, $connection_timeout);

    if (!$fp) {
        echo "Lỗi kết nối: $errstr ($errno)\n";
    } else {
        echo "Kết nối thành công!\n";

        // [source: 3099] - Đặt response timeout cho stream
        // Cần đặt blocking mode là TRUE để timeout đọc hoạt động đúng
        stream_set_blocking($fp, true);
        stream_set_timeout($fp, $response_timeout);
        echo "Đặt timeout đọc là $response_timeout giây.\n";

        // Gửi request (ví dụ: HTTP GET)
        $request = "GET / HTTP/1.1\r\n";
        $request .= "Host: $host\r\n";
        $request .= "Connection: Close\r\n\r\n";
        fwrite($fp, $request);
        echo "Đã gửi request.\n";

        // Đọc response
        echo "Đang đọc response...\n";
        $response = '';
        // [source: 3100] - Đọc dữ liệu
        // Có thể dùng stream_get_contents hoặc vòng lặp fgets/fread
        while (!feof($fp)) {
            $line = fgets($fp, 1024); // Đọc từng dòng
            if ($line === false) { // Kiểm tra lỗi đọc hoặc timeout trong khi đọc
                 break;
            }
            $response .= $line;
        }
        echo "Đã đọc xong.\n";

        // [source: 3101]-[source: 3102] - Kiểm tra metadata sau khi đọc xong
        $meta = stream_get_meta_data($fp);
        fclose($fp); // Đóng kết nối

        // [source: 3102], [source: 3114] - Kiểm tra cờ timed_out
        if ($meta['timed_out']) {
            echo "\n!!! CẢNH BÁO: Đã hết thời gian chờ đọc dữ liệu (timed_out) !!!\n";
            echo "Dữ liệu nhận được có thể không hoàn chỉnh.\n";
        } else {
            echo "\nĐọc dữ liệu hoàn tất, không bị timeout.\n";
        }

        echo "\n--- Metadata ---\n";
        print_r($meta);
        echo "\n--- Response (một phần) ---\n";
        echo substr(htmlspecialchars($response), 0, 500) . "...\n"; // Hiển thị một phần response

    }
    ?>
    ```

**Bài tập 12.3: Phân tích Rủi ro RPC Client (Response Splitting/Request Smuggling)**

* **Kiến thức vận dụng:** Hiểu cách attacker có thể inject ký tự CRLF (`%0d%0a`) vào dữ liệu mà client gửi đi (trong URL hoặc header), khiến server trung gian hoặc server cuối diễn giải sai request/response. (Chapter 12, "HTTP Response Splitting", `[source: 3133]`-`[source: 3141]`; "HTTP Request Smuggling", `[source: 3146]`-`[source: 3160]`).
* **Phân tích & Áp dụng:** Nếu ứng dụng PHP lấy input từ user (ví dụ: ID sản phẩm) và đưa thẳng vào URL hoặc header khi gọi API khác mà không lọc CRLF, attacker có thể inject header giả (Response Splitting) hoặc làm sai lệch cách các server (proxy, load balancer) xử lý request (Request Smuggling).
* **Lời giải (Phân tích):**
    Khi ứng dụng PHP của bạn (Client) gọi đến một Web Service/API khác (Server), các rủi ro này có thể phát sinh nếu Client không xử lý cẩn thận dữ liệu **trước khi gửi đi**, hoặc nếu cấu trúc hạ tầng mạng (proxy, firewall, load balancer) giữa Client và Server diễn giải HTTP request không nhất quán.

    1.  **HTTP Response Splitting:**
        * **Nguy cơ:** Nếu Client lấy một giá trị từ người dùng (ví dụ `$_GET['redirect_url']`) và đặt trực tiếp vào header `Location` trong request gửi đến Server (ví dụ, Server là một URL shortener), kẻ tấn công có thể nhập `redirect_url=http://legit.com%0d%0aContent-Length:%200%0d%0a%0d%0aHTTP/1.1%20200%20OK%0d%0aContent-Type:%20text/html%0d%0a%0d%0a<script>alert('XSS')</script>`. (`[source: 3136]`)
        * Nếu Server hoặc proxy trung gian không lọc CRLF trong header `Location`, nó có thể trả về một response HTTP chứa cả header `Location` gốc và phần response giả mạo (bắt đầu từ `HTTP/1.1 200 OK...`) do attacker inject vào. (`[source: 3139]`)
        * Trình duyệt của người dùng cuối (được redirect bởi Client) có thể bị lừa thực thi response giả mạo này, dẫn đến XSS hoặc các tấn công khác.
        * **Giảm thiểu:** Client PHP phải **luôn luôn lọc bỏ hoặc encode** các ký tự CRLF (`\r`, `\n`, `%0d`, `%0a`) khỏi bất kỳ dữ liệu nào được đặt vào header HTTP hoặc URL trước khi gửi request. (`[source: 3142]`-`[source: 3145]`). Sử dụng `filter_var` hoặc `urlencode` đúng cách.

    2.  **HTTP Request Smuggling:**
        * **Nguy cơ:** Xảy ra khi có sự không nhất quán trong cách server trung gian (ví dụ: Load Balancer, Proxy) và server cuối (API Server) xử lý các header như `Content-Length` hoặc `Transfer-Encoding`. Kẻ tấn công tạo ra một request HTTP "nhập nhằng" mà hai server hiểu khác nhau. (`[source: 3147]`-`[source: 3149]`).
        * Ví dụ, attacker gửi request qua Client PHP đến API Server (có proxy ở giữa):
            ```http
            POST /api/resource HTTP/1.1
            Host: api.example.com
            Connection: keep-alive
            Content-Type: application/x-www-form-urlencoded
            Content-Length: 45
            Transfer-Encoding: chunked  <-- Proxy có thể ưu tiên cái này

            0                       <-- Proxy nghĩ body kết thúc ở đây

            POST /api/admin_delete HTTP/1.1   <-- Server cuối có thể thấy request này
            Host: api.example.com
            ... (Phần request độc hại) ...
            ```
        * Nếu Client PHP chỉ đơn thuần forward request này mà không chuẩn hóa header, proxy có thể chỉ thấy request POST `/api/resource` đầu tiên (vì `Transfer-Encoding: chunked` và `0`), nhưng API server cuối lại xử lý theo `Content-Length: 45` và đọc luôn cả phần request `POST /api/admin_delete` độc hại mà attacker "buôn lậu" (smuggled) vào. (`[source: 3154]`-`[source: 3159]`).
        * Hậu quả có thể là cache poisoning, bypass firewall/WAF, chiếm quyền session của người dùng khác.
        * **Giảm thiểu:**
            * Client PHP nên **chuẩn hóa** các request gửi đi: chỉ sử dụng một trong `Content-Length` hoặc `Transfer-Encoding: chunked`, không dùng cả hai; loại bỏ các header không cần thiết hoặc không hợp lệ.
            * Sử dụng kết nối HTTP/1.1 keep-alive một cách cẩn thận hoặc xem xét dùng HTTP/2 nếu có thể (ít bị ảnh hưởng hơn).
            * Quan trọng nhất là các server trung gian (proxy, load balancer) phải được cấu hình đúng để xử lý HTTP request một cách nhất quán và chặt chẽ. (`[source: 3161]`-`[source: 3162]`). Client PHP khó kiểm soát hoàn toàn việc này nhưng có thể giảm rủi ro bằng cách gửi request chuẩn.

## Chương 13: Securing Unix

**Bài tập 13.1: Phân tích Quyền File**

* **Kiến thức vận dụng:** Hiểu ý nghĩa của quyền đọc (r=4), ghi (w=2), thực thi (x=1) cho user (u), group (g), other (o) và cách tính permission mode octal. (Chapter 13, "An Introduction to Unix Permissions", `[source: 3171]`-`[source: 3194]`; "Manipulating Permissions", `[source: 3195]`-`[source: 3215]`).
* **Phân tích & Áp dụng:**
    * `cron.php`: Cần được đọc và thực thi bởi `www-data` (để cron chạy). Không cần ghi. Owner (ví dụ: root hoặc deploy user) cần đọc/ghi. Group và Other không cần quyền.
    * `config.secure`: Chỉ được đọc bởi group `appadmin` và root. User `www-data` không được đọc.
    * `/var/log/app/`: Thư mục log cần được `www-data` tạo file (quyền write + execute cho thư mục). Admin cần đọc/liệt kê.
    * `cron.log`: Cần được tạo và ghi bởi `www-data`. Admin cần đọc.
* **Lời giải (Đề xuất):**
    * `cron.php`:
        * Owner: `root` (hoặc deploy user)
        * Group: `www-data`
        * Mode: `750` (rwxr-x---) -> Owner full quyền, group `www-data` đọc & thực thi, other không có quyền.
        * Lệnh: `chown root:www-data /var/www/html/cron.php; chmod 750 /var/www/html/cron.php`
    * `config.secure`:
        * Owner: `root`
        * Group: `appadmin`
        * Mode: `640` (rw-r-----) -> Owner đọc/ghi, group `appadmin` chỉ đọc, other không có quyền. `www-data` (nếu không thuộc group `appadmin`) sẽ không đọc được.
        * Lệnh: `chown root:appadmin /etc/app/config.secure; chmod 640 /etc/app/config.secure`
    * `/var/log/app/` (Thư mục chứa log):
        * Owner: `root` (hoặc user quản lý log)
        * Group: `www-data`
        * Mode: `770` (rwxrwx---) -> Owner full quyền, group `www-data` có thể vào thư mục (`x`) và tạo/ghi file (`w`), other không có quyền. Hoặc `750` nếu chỉ cần đọc log từ group khác. An toàn hơn là `770` để `www-data` tự tạo file log.
        * Lệnh: `mkdir -p /var/log/app; chown root:www-data /var/log/app; chmod 770 /var/log/app`
        * *(Tùy chọn nâng cao: Set SGID bit `chmod 2770` để file tạo ra tự động thuộc group `www-data` - `[source: 3238]`-`[source: 3246]`)*.
    * `cron.log` (File log):
        * Owner: `www-data` (sẽ được tạo bởi process `www-data`)
        * Group: `www-data` (hoặc `adm` nếu muốn admin group khác đọc)
        * Mode: `640` (rw-r-----) -> `www-data` đọc/ghi, group đọc, other không có quyền.
        * Lệnh (thiết lập umask cho process cron hoặc set quyền sau khi file tạo): Cần đảm bảo file log được tạo với quyền này (ví dụ: `umask(027)` trong script PHP hoặc `chmod` sau khi tạo).

**Bài tập 13.2: Thảo luận `chroot` jail**

* **Kiến thức vận dụng:** Hiểu `chroot` thay đổi thư mục gốc ảo của một process, giới hạn tầm nhìn của nó vào hệ thống file. Biết được nó không phải là giải pháp bảo mật tuyệt đối. (Chapter 13, "Keeping Developers (and Daemons) in Their Home Directories", `[source: 3258]`-`[source: 3265]`).
* **Phân tích & Áp dụng:** Cần nêu lợi ích (giới hạn thiệt hại nếu process bị compromise) và hạn chế (có thể bị bypass bởi user root hoặc các kỹ thuật khác, phức tạp khi cài đặt thư viện cần thiết vào jail).
* **Lời giải (Thảo luận):**
    **Lợi ích:**
    * **Giới hạn Tầm nhìn Hệ thống File:** Lợi ích chính là process bị jail (ví dụ: web server `httpd` hoặc `php-fpm`) chỉ "nhìn thấy" các file và thư mục bên trong môi trường jail đó. Nó không thể truy cập trực tiếp các file hệ thống nhạy cảm bên ngoài như `/etc/passwd`, `/etc/shadow` hoặc các thư mục home của user khác, ngay cả khi có lỗ hổng LFI trong ứng dụng web. (`[source: 3261]`).
    * **Giảm Thiệt hại:** Nếu kẻ tấn công chiếm được quyền thực thi lệnh thông qua process bị jail (ví dụ: qua lỗ hổng remote execution), phạm vi phá hoại của chúng bị giới hạn trong môi trường jail. Chúng không thể dễ dàng ghi đè file hệ thống quan trọng hoặc cài đặt rootkit lên hệ thống chính. (`[source: 3263]`).
    * **Tăng cường Cách ly:** Giúp cách ly môi trường chạy ứng dụng khỏi phần còn lại của hệ thống.

    **Hạn chế:**
    * **Không phải là Giải pháp Tuyệt đối:** `chroot` **không** được thiết kế như một cơ chế bảo mật chống lại user có quyền root hoặc attacker có chủ đích. User root bên trong jail vẫn có thể tìm cách thoát ra (break out) bằng nhiều kỹ thuật khác nhau. (`[source: 3262]`). Nó chủ yếu hữu ích chống lại các lỗi vô tình hoặc attacker trình độ thấp.
    * **Phức tạp trong Cài đặt và Bảo trì:** Để process trong jail hoạt động, bạn phải sao chép hoặc mount tất cả các thư viện (`/lib`, `/usr/lib`), file thực thi (`/bin`), file thiết bị (`/dev` như `/dev/null`, `/dev/random`), và file cấu hình (`/etc`) cần thiết vào bên trong môi trường jail. Việc này tốn công sức và khó cập nhật, bảo trì.
    * **Không giới hạn Tài nguyên Hệ thống:** `chroot` không giới hạn việc sử dụng CPU, Memory, Network của process bị jail. Các cơ chế khác như Cgroups (Linux) hoặc Resource Limits cần được sử dụng cho mục đích này.

**Bài tập 13.3: Mục đích `open_basedir` và `disable_functions`**

* **Kiến thức vận dụng:** Hiểu cách hai directive này trong `php.ini` hạn chế khả năng của script PHP, đặc biệt là trong việc truy cập filesystem và thực thi hàm nguy hiểm. (Chapter 13, "Safe Mode Alternatives", `[source: 3368]`-`[source: 3377]`).
* **Phân tích & Áp dụng:** Cần giải thích `open_basedir` giới hạn đường dẫn file, còn `disable_functions` vô hiệu hóa hàm PHP cụ thể. Cả hai đều hữu ích để giảm bề mặt tấn công, nhất là khi không kiểm soát được code (shared hosting).
* **Lời giải (Giải thích):**
    Mặc dù PHP Safe Mode đã bị loại bỏ, `open_basedir` và `disable_functions` vẫn là các directive quan trọng trong `php.ini` để tăng cường bảo mật, đặc biệt trong môi trường shared hosting hoặc khi chạy code không đáng tin cậy.

    * **`open_basedir`:**
        * **Mục đích:** Giới hạn các đường dẫn file mà script PHP được phép truy cập thông qua các hàm xử lý file (như `fopen`, `file_get_contents`, `require`, `include`, `is_dir`, `stat`, etc.). (`[source: 3370]`, `[source: 3372]`).
        * **Hoạt động:** Khi directive này được đặt (ví dụ: `open_basedir = /var/www/user1/:/tmp/`), PHP sẽ kiểm tra đường dẫn file trong các hàm liên quan. Nếu đường dẫn nằm ngoài các thư mục đã chỉ định (và các thư mục con của chúng), thao tác sẽ thất bại và PHP phát sinh lỗi warning. (`[source: 3373]`-`[source: 3375]`).
        * **Vấn đề bảo mật giải quyết:** Ngăn chặn các cuộc tấn công Local File Inclusion (LFI) hoặc đọc file tùy ý, nơi kẻ tấn công lừa script PHP đọc các file nhạy cảm bên ngoài thư mục gốc của ứng dụng (ví dụ: `/etc/passwd`, file config của user khác). Rất hữu ích trong shared hosting để ngăn user này đọc file của user khác.

    * **`disable_functions`:**
        * **Mục đích:** Vô hiệu hóa hoàn toàn việc gọi một số hàm PHP nội bộ nhất định bị coi là nguy hiểm hoặc có thể bị lạm dụng. (`[source: 3375]`).
        * **Hoạt động:** Liệt kê danh sách các tên hàm (cách nhau bởi dấu phẩy) mà trình thông dịch PHP sẽ không cho phép thực thi. Nếu script cố gắng gọi một hàm bị vô hiệu hóa, PHP sẽ phát sinh lỗi fatal error hoặc warning. (`[source: 3376]`-`[source: 3377]`).
        * **Vấn đề bảo mật giải quyết:** Ngăn chặn việc thực thi các hàm có khả năng gây nguy hiểm cao, đặc biệt là các hàm thực thi lệnh hệ thống (`system`, `shell_exec`, `passthru`, `exec`, `popen`, `proc_open`), các hàm đọc thông tin hệ thống (`phpinfo`, `posix_getpwuid`), hoặc các hàm có thể bị lạm dụng khác tùy thuộc vào môi trường. Giảm thiểu khả năng kẻ tấn công thực thi mã tùy ý trên server nếu họ tìm được cách inject code PHP hoặc kiểm soát việc gọi hàm.

## Chương 14: Securing Your Database (MySQL)

**Bài tập 14.1: Harden Cài đặt MySQL**

* **Kiến thức vận dụng:** Các bước cơ bản để tăng cường bảo mật cho MySQL sau khi cài đặt, bao gồm xóa user/DB không cần thiết và đặt mật khẩu root. (Chapter 14, "Hardening a Default MySQL Installation", `[source: 3467]`-`[source: 3478]`).
* **Phân tích & Áp dụng:** Cần thực hiện các lệnh SQL và lệnh command-line để loại bỏ các cấu hình mặc định không an toàn.
* **Lời giải (Các bước):**
    1.  **Đặt mật khẩu cho user `root`:** Đây là bước quan trọng nhất và nên làm đầu tiên. Dùng lệnh `mysqladmin` hoặc `ALTER USER` (MySQL 5.7.6+) / `SET PASSWORD` (phiên bản cũ).
        ```bash
        # Ví dụ dùng mysqladmin (cần chạy khi mysqld đang chạy)
        mysqladmin -u root password 'YourNewStrongPassword'
        # Hoặc kết nối vào mysql client và chạy (MySQL 5.7.6+)
        # ALTER USER 'root'@'localhost' IDENTIFIED BY 'YourNewStrongPassword';
        # Hoặc (phiên bản cũ hơn)
        # SET PASSWORD FOR 'root'@'localhost' = PASSWORD('YourNewStrongPassword');
        # FLUSH PRIVILEGES;
        ```
        (`[source: 3498]`-`[source: 3500]`)
    2.  **Xóa các user anonymous:** Các user này cho phép kết nối mà không cần tên người dùng, rất nguy hiểm.
        ```sql
        -- Kết nối vào MySQL client với quyền root
        USE mysql;
        DELETE FROM user WHERE User = '';
        -- [source: 3442]
        FLUSH PRIVILEGES;
        ```
    3.  **Xóa database `test`:** Database này mặc định cho phép truy cập bởi user anonymous.
        ```sql
        -- Kết nối vào MySQL client với quyền root
        DROP DATABASE IF EXISTS test;
        -- [source: 3481]
        -- Xóa quyền liên quan đến db test (nếu có)
        USE mysql;
        DELETE FROM db WHERE Db = 'test' OR Db = 'test\\_%';
        -- [source: 3484]
        FLUSH PRIVILEGES;
        ```
    4.  **Hạn chế host cho user `root`:** Mặc định, root có thể kết nối từ localhost và hostname của server. Nên xóa quyền kết nối từ hostname hoặc giới hạn chặt chẽ hơn nếu cần truy cập từ xa (nên dùng SSH tunnel thay thế).
        ```sql
        -- Kết nối vào MySQL client với quyền root
        USE mysql;
        -- Kiểm tra user root khác ngoài localhost
        -- SELECT User, Host FROM user WHERE User='root';
        -- Xóa user root không phải localhost (ví dụ: root@'hostname', root@'%')
        -- DELETE FROM user WHERE User = 'root' AND Host != 'localhost';
        -- [source: 3491]-[source: 3493] (Ví dụ xóa root@'example.com')
        FLUSH PRIVILEGES;
        ```
    5.  **Xóa các quyền không cần thiết (nếu có):** Mặc dù user root cần nhiều quyền, nhưng nếu có user khác được tạo mặc định với quyền rộng, cần thu hồi (`REVOKE`).

**Bài tập 14.2: Phân tích Quyền Nguy hiểm**

* **Kiến thức vận dụng:** Hiểu ý nghĩa của các quyền quản trị cấp cao trong MySQL và tại sao chúng nguy hiểm nếu bị cấp cho user ứng dụng thông thường. (Chapter 14, "Grant Privileges Conservatively", `[source: 3501]`-`[source: 3510]`). `[source: 3396]` đề cập FILE, PROCESS, SHUTDOWN.
* **Phân tích & Áp dụng:** Cần giải thích từng quyền cho phép làm gì và hậu quả nếu user ứng dụng (có thể bị compromise) sở hữu quyền đó.
* **Lời giải (Phân tích):**
    Việc cấp các quyền như `FILE`, `PROCESS`, `SHUTDOWN` cho user MySQL mà ứng dụng PHP sử dụng (ví dụ: `webapp_user`) là cực kỳ nguy hiểm vì:
    * **`FILE`:**
        * **Chức năng:** Cho phép user đọc và ghi file trên hệ thống file của server MySQL với quyền của tiến trình `mysqld`. (`[source: 3396]`).
        * **Rủi ro:** Nếu user ứng dụng bị compromise (ví dụ qua SQL Injection), kẻ tấn công có thể:
            * Đọc các file nhạy cảm trên server (ví dụ: `/etc/passwd`, file cấu hình ứng dụng khác, mã nguồn PHP chứa thông tin nhạy cảm) bằng `LOAD DATA INFILE` hoặc `SELECT ... INTO OUTFILE`.
            * Ghi file tùy ý lên server (ví dụ: upload webshell PHP vào thư mục web root) bằng `SELECT ... INTO OUTFILE` hoặc `SELECT ... INTO DUMPFILE`, dẫn đến thực thi mã từ xa.
    * **`PROCESS`:**
        * **Chức năng:** Cho phép user xem danh sách các tiến trình đang chạy trên server MySQL bằng lệnh `SHOW PROCESSLIST`. (`[source: 3396]`).
        * **Rủi ro:** Kẻ tấn công có thể xem các câu lệnh SQL đang được thực thi bởi các user khác, bao gồm cả các câu lệnh có thể chứa dữ liệu nhạy cảm (ví dụ: mật khẩu trong câu lệnh `SET PASSWORD` của người khác nếu không dùng hash, hoặc dữ liệu nhạy cảm trong các câu `INSERT`/`UPDATE` đang chạy). Thông tin này có thể hỗ trợ các cuộc tấn công khác.
    * **`SHUTDOWN`:**
        * **Chức năng:** Cho phép user tắt server MySQL. (`[source: 3396]`).
        * **Rủi ro:** Quá rõ ràng. Kẻ tấn công có thể thực hiện tấn công Denial of Service (DoS) bằng cách tắt CSDL của bạn bất cứ lúc nào, làm ngừng hoạt động toàn bộ ứng dụng phụ thuộc vào nó.

    **Quyền tối thiểu cho ứng dụng CRUD:** Một ứng dụng CRUD (Create, Read, Update, Delete) thông thường chỉ nên được cấp các quyền sau trên **database cụ thể** của nó, không phải quyền toàn cục (`ON *.*`):
    * `SELECT`: Đọc dữ liệu.
    * `INSERT`: Thêm dữ liệu mới.
    * `UPDATE`: Sửa dữ liệu hiện có.
    * `DELETE`: Xóa dữ liệu (Nếu không dùng soft delete. Nếu dùng soft delete thì chỉ cần `UPDATE`). (`[source: 3507]`)
    * (Tùy chọn) `LOCK TABLES`: Cần thiết nếu ứng dụng sử dụng khóa bảng tường minh (ít phổ biến hơn). (`[source: 3507]`).
    * (Tùy chọn) `EXECUTE`: Nếu ứng dụng cần gọi Stored Procedures/Functions.
    **Tuyệt đối không** cấp các quyền như `GRANT OPTION`, `ALTER`, `DROP`, `CREATE`, `FILE`, `PROCESS`, `SHUTDOWN`, `SUPER` cho user ứng dụng. (`[source: 3505]`).

**Bài tập 14.3: Bảo mật File Config & Thư mục Dữ liệu**

* **Kiến thức vận dụng:** Áp dụng quyền Unix phù hợp để bảo vệ các file và thư mục quan trọng của MySQL. (Chapter 14, "Database Filesystem Permissions", `[source: 3399]`-`[source: 3415]`; "Securing Option Files", `[source: 3417]`-`[source: 3429]`).
* **Phân tích & Áp dụng:** Cần đảm bảo chỉ user chạy `mysqld` (thường là `mysql`) mới có quyền ghi vào thư mục dữ liệu. File config toàn cục có thể cần quyền đọc rộng hơn nhưng chỉ root được ghi. File config user cần được bảo vệ bởi chính user đó.
* **Lời giải (Quyền đề xuất):**
    1.  **Thư mục Dữ liệu MySQL (Ví dụ: `/var/lib/mysql` hoặc `/usr/local/mysql/data`):**
        * **Tầm quan trọng:** Chứa tất cả các file dữ liệu thực tế của các bảng, index, và cả file log nhị phân, log lỗi nếu cấu hình lưu tại đây. Truy cập trái phép có thể đọc/sửa/xóa toàn bộ dữ liệu hoặc xem thông tin nhạy cảm trong log. (`[source: 3400]`, `[source: 3403]`).
        * **Quyền sở hữu:** User `mysql`, Group `mysql` (User và Group mà tiến trình `mysqld` chạy). (`[source: 3410]`).
        * **Permission Mode (Thư mục):** `700` (drwx------). Chỉ user `mysql` mới có toàn quyền (đọc, ghi, vào thư mục). Group và Other không có quyền gì. (`[source: 3414]`).
        * **Permission Mode (Files bên trong):** Thường là `600` (rw-------) hoặc `660` (rw-rw----) tùy cấu hình MySQL, nhưng mode `700` của thư mục cha đã hạn chế truy cập hiệu quả.
        * **Lệnh:**
            ```bash
            chown -R mysql:mysql /var/lib/mysql
            chmod -R 700 /var/lib/mysql
            # (Có thể cần chạy lại sau khi tạo DB mới nếu quyền không đúng)
            ```
            (`[source: 3411]`, `[source: 3414]`)
    2.  **File Cấu hình Toàn cục (Ví dụ: `/etc/my.cnf` hoặc `/etc/mysql/my.cnf`):**
        * **Tầm quan trọng:** Chứa các thiết lập hoạt động của server MySQL. Sửa đổi trái phép có thể thay đổi hành vi server (ví dụ: bật logging nguy hiểm, thay đổi user chạy server). Cũng có thể bị lộ thông tin nếu chứa password (không nên). (`[source: 3419]`, `[source: 3420]`).
        * **Quyền sở hữu:** User `root`, Group `root` (hoặc group admin).
        * **Permission Mode:** `644` (rw-r--r--). Chỉ `root` được sửa. Mọi user khác có thể đọc (cần thiết cho các client tool đọc config). **Không lưu mật khẩu vào file này.** (`[source: 3421]`).
        * **Lệnh:** `chown root:root /etc/my.cnf; chmod 644 /etc/my.cnf`
    3.  **File Cấu hình User (Ví dụ: `~/.my.cnf`):**
        * **Tầm quan trọng:** Thường chứa username và password để user kết nối vào MySQL client mà không cần gõ lại. Nếu bị lộ, attacker có thể lấy được credential của user đó. (`[source: 3426]`-`[source: 3428]`).
        * **Quyền sở hữu:** User tương ứng, Group tương ứng.
        * **Permission Mode:** `600` (rw-------). Chỉ user chủ sở hữu file mới có quyền đọc/ghi. Group và Other không có quyền gì. (`[source: 3429]`).
        * **Lệnh (User tự chạy trong home dir):** `chmod 600 ~/.my.cnf`

## Chương 15: Using Encryption

**Bài tập 15.1: Hashing Mật khẩu An toàn (PHP)**

* **Kiến thức vận dụng:** Sử dụng hàm hashing hiện đại của PHP (`password_hash`, `password_verify`) được thiết kế riêng cho mật khẩu, tự động bao gồm salt và thuật toán mạnh (mặc định là bcrypt). Tránh dùng `md5`/`sha1` trực tiếp cho mật khẩu. (Sách này cũ (2010) nên tập trung vào `sha1` với salt thủ công (`[source: 3868]`, `[source: 3888]`, `[source: 3900]`). Tuy nhiên, `password_*` là cách làm đúng và hiện đại).
* **Phân tích & Áp dụng:** Dùng `password_hash` khi đăng ký/đổi mật khẩu. Dùng `password_verify` khi kiểm tra đăng nhập.
* **Lời giải (PHP Class):**
    ```php
    <?php
    class Auth {
        // Giả lập CSDL bằng mảng tĩnh (chỉ để demo)
        private static $users = [];

        /**
         * Đăng ký user mới, hash mật khẩu.
         * @param string $username
         * @param string $password
         * @return bool True nếu thành công.
         */
        public function register($username, $password) {
            if (isset(self::$users[$username])) {
                echo "Username đã tồn tại!<br>";
                return false;
            }
            if (empty($password) || strlen(trim($password)) < 8) {
                 echo "Mật khẩu quá yếu hoặc rỗng!<br>"; // Nên có kiểm tra độ mạnh
                 return false;
            }

            // Hash mật khẩu bằng password_hash() - Tự động tạo salt, dùng bcrypt
            // [source: Không có password_hash, nhưng thay thế cho [source: 3888]]
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if ($hashed_password === false) {
                 echo "Lỗi hash mật khẩu!<br>";
                 return false;
            }

            // Lưu vào CSDL (giả lập)
            self::$users[$username] = $hashed_password;
            echo "Đăng ký thành công cho user: " . htmlspecialchars($username) . "<br>";
            return true;
        }

        /**
         * Kiểm tra đăng nhập.
         * @param string $username
         * @param string $password
         * @return bool True nếu đăng nhập thành công.
         */
        public function login($username, $password) {
            // Lấy hash đã lưu (giả lập)
            // [source: 3893]-[source: 3895] - Lấy hash và salt đã lưu
            if (!isset(self::$users[$username])) {
                echo "Username không tồn tại!<br>";
                return false;
            }
            $stored_hash = self::$users[$username];

            // Kiểm tra mật khẩu bằng password_verify()
            // [source: 3896] - So sánh hash (thay vì sha1(...) == ...)
            if (password_verify($password, $stored_hash)) {
                echo "Đăng nhập thành công cho user: " . htmlspecialchars($username) . "<br>";
                // (Tùy chọn) Kiểm tra xem hash có cần rehash với thuật toán mới không
                if (password_needs_rehash($stored_hash, PASSWORD_DEFAULT)) {
                     $new_hash = password_hash($password, PASSWORD_DEFAULT);
                     self::$users[$username] = $new_hash; // Cập nhật hash mới
                     echo "Mật khẩu đã được rehash với thuật toán mới.<br>";
                }
                return true;
            } else {
                echo "Sai mật khẩu cho user: " . htmlspecialchars($username) . "<br>";
                return false;
            }
        }
    }

    // Ví dụ sử dụng:
    $auth = new Auth();
    $auth->register('testuser', 'Password123');
    $auth->login('testuser', 'Password123'); // Thành công
    $auth->login('testuser', 'wrongpassword'); // Thất bại
    ?>

    ```

**Bài tập 15.2: Mã hóa/Giải mã Đối xứng (OpenSSL PHP)**

* **Kiến thức vận dụng:** Sử dụng hàm OpenSSL của PHP (`openssl_encrypt`, `openssl_decrypt`) cho mã hóa đối xứng. Cần chọn thuật toán (cipher), key, và xử lý Initialization Vector (IV). (Sách tập trung vào `mcrypt` (`[source: 3912]`-`[source: 3918]`) nhưng `openssl_*` là lựa chọn hiện đại và được tích hợp sẵn).
* **Phân tích & Áp dụng:** Chọn cipher AES-256-CBC. Key phải đúng độ dài cho cipher. Tạo IV ngẫu nhiên cho mỗi lần mã hóa. Ghép IV vào đầu ciphertext khi lưu/gửi. Tách IV ra trước khi giải mã. Dùng base64 để dễ truyền/lưu trữ.
* **Lời giải (PHP - Hàm):**
    ```php
    <?php
    // [source: 3622] - OpenSSL được đề cập là có sẵn
    // [source: 3647] - AES được đề cập là thuật toán mạnh
    // [source: 3820] - CBC mode được khuyến nghị
    define('ENCRYPTION_METHOD', 'aes-256-cbc'); // Chọn thuật toán

    /**
     * Mã hóa dữ liệu bằng AES-256-CBC.
     *
     * @param string $plaintext Dữ liệu cần mã hóa.
     * @param string $key Khóa mã hóa (phải là 32 bytes cho AES-256).
     * @return string|false Ciphertext đã mã hóa (base64 encoded, gồm IV), hoặc false nếu lỗi.
     */
    function encrypt_data($plaintext, $key) {
        // Key phải đúng 32 bytes cho AES-256
        if (mb_strlen($key, '8bit') !== 32) {
            error_log("Encryption key must be 32 bytes long for AES-256.");
            return false;
        }

        // [source: 3823]-[source: 3824] - Cần Initialization Vector (IV) cho CBC mode
        $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        if ($iv_length === false) {
             error_log("Could not get IV length for " . ENCRYPTION_METHOD);
             return false;
        }
        // Tạo IV ngẫu nhiên, an toàn
        $iv = openssl_random_pseudo_bytes($iv_length);

        // [source: Sách dùng mcrypt, đây là hàm openssl tương ứng]
        $ciphertext = openssl_encrypt(
            $plaintext,
            ENCRYPTION_METHOD,
            $key,
            OPENSSL_RAW_DATA, // Trả về raw binary data
            $iv
        );

        if ($ciphertext === false) {
            error_log("openssl_encrypt failed: " . openssl_error_string());
            return false;
        }

        // [source: 3826] - Ghép IV vào đầu ciphertext trước khi encode base64
        // [source: 3767] - Base64 hiệu quả hơn hex
        return base64_encode($iv . $ciphertext);
    }

    /**
     * Giải mã dữ liệu AES-256-CBC.
     *
     * @param string $ciphertext_base64 Ciphertext (đã base64 encoded, bao gồm IV ở đầu).
     * @param string $key Khóa mã hóa (phải là 32 bytes).
     * @return string|false Plaintext đã giải mã, hoặc false nếu lỗi.
     */
    function decrypt_data($ciphertext_base64, $key) {
        // Key phải đúng 32 bytes
        if (mb_strlen($key, '8bit') !== 32) {
            error_log("Decryption key must be 32 bytes long for AES-256.");
            return false;
        }

        // Decode base64
        // [source: 3767]
        $decoded_data = base64_decode($ciphertext_base64, true); // true để kiểm tra ký tự base64 hợp lệ
        if ($decoded_data === false) {
             error_log("Failed to base64 decode ciphertext.");
             return false;
        }

        // [source: 3826] - Tách IV ra khỏi ciphertext
        $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
         if ($iv_length === false) {
             error_log("Could not get IV length for " . ENCRYPTION_METHOD);
             return false;
        }
        if (mb_strlen($decoded_data, '8bit') < $iv_length) {
            error_log("Ciphertext is too short to contain IV.");
            return false;
        }

        $iv = mb_substr($decoded_data, 0, $iv_length, '8bit');
        $ciphertext_raw = mb_substr($decoded_data, $iv_length, null, '8bit');

        // [source: Sách dùng mcrypt, đây là hàm openssl tương ứng]
        $plaintext = openssl_decrypt(
            $ciphertext_raw,
            ENCRYPTION_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // openssl_decrypt trả về false nếu giải mã thất bại (sai key, sai IV, dữ liệu lỗi)
        if ($plaintext === false) {
            error_log("openssl_decrypt failed: " . openssl_error_string());
        }
        return $plaintext; // Trả về false nếu lỗi, hoặc plaintext nếu thành công
    }

    // Ví dụ sử dụng
    $my_key = random_bytes(32); // Tạo key ngẫu nhiên 32 bytes (256 bit) - Cần lưu key này an toàn!
    $my_data = "Đây là dữ liệu bí mật cần mã hóa!";

    $encrypted = encrypt_data($my_data, $my_key);
    if ($encrypted) {
        echo "Đã mã hóa (Base64): " . $encrypted . "<br>";

        $decrypted = decrypt_data($encrypted, $my_key);
        if ($decrypted !== false) {
            echo "Đã giải mã: " . htmlspecialchars($decrypted) . "<br>";
            if ($decrypted === $my_data) {
                 echo "=> Giải mã thành công, dữ liệu khớp!<br>";
            } else {
                 echo "=> LỖI: Dữ liệu giải mã không khớp!<br>";
            }
        } else {
            echo "Giải mã thất bại!<br>";
        }
    } else {
        echo "Mã hóa thất bại!<br>";
    }

    // Thử giải mã với sai key
    $wrong_key = random_bytes(32);
    $decrypted_wrong = decrypt_data($encrypted, $wrong_key);
    if ($decrypted_wrong === false) {
         echo "Giải mã với sai key thất bại (đúng mong đợi).<br>";
    } else {
         echo "LỖI: Giải mã với sai key lại thành công??<br>";
    }
    ?>
    ```

**Bài tập 15.3: Mã hóa Đối xứng vs Bất đối xứng (Conceptual)**

* **Kiến thức vận dụng:** Phân biệt hai loại mã hóa chính dựa trên cách quản lý khóa (key). (Chapter 15, "Encryption", `[source: 3530]`; "Symmetric", `[source: 3541]`-`[source: 3544]`; "Asymmetric", `[source: 3545]`-`[source: 3552]`). Lý do kết hợp (`[source: 3940]`-`[source: 3942]`).
* **Phân tích & Áp dụng:** Cần nêu điểm khác biệt cốt lõi (1 key vs 2 keys), ưu/nhược điểm về tốc độ, quản lý key, và ứng dụng thực tế.
* **Lời giải (Giải thích):**

    * **Mã hóa Đối xứng (Symmetric Encryption):**
        * **Khác biệt cốt lõi:** Sử dụng **cùng một khóa bí mật (secret key)** cho cả quá trình mã hóa và giải mã. (`[source: 3541]`-`[source: 3542]`). Bên gửi và bên nhận phải chia sẻ khóa bí mật này một cách an toàn trước khi trao đổi dữ liệu mã hóa.
        * **Thuật toán ví dụ:** AES, Blowfish, 3DES (`[source: 3544]`).
        * **Ưu điểm:** Rất nhanh, hiệu quả về mặt tính toán, phù hợp để mã hóa lượng lớn dữ liệu. (`[source: 3543]`).
        * **Nhược điểm:** Thách thức lớn nhất là **phân phối khóa (key distribution)**. Làm thế nào để hai bên trao đổi khóa bí mật một cách an toàn qua một kênh không an toàn? Nếu khóa bí mật bị lộ, toàn bộ dữ liệu bị compromise. (`[source: 3625]`).
        * **Khi nào dùng:** Mã hóa dữ liệu lưu trữ (ví dụ: file trên disk, dữ liệu trong DB khi ứng dụng có thể truy cập key an toàn), mã hóa luồng dữ liệu lớn trong kết nối đã được thiết lập an toàn (ví dụ: sau khi đã trao đổi key qua mã hóa bất đối xứng).

    * **Mã hóa Bất đối xứng (Asymmetric Encryption / Public-Key Cryptography):**
        * **Khác biệt cốt lõi:** Sử dụng một **cặp khóa (key pair)** liên kết toán học với nhau: một **khóa công khai (public key)** và một **khóa riêng tư (private key)**. Public key có thể chia sẻ công khai, private key phải được giữ bí mật tuyệt đối bởi chủ sở hữu. Dữ liệu được mã hóa bằng public key chỉ có thể giải mã bằng private key tương ứng, và ngược lại (dùng cho chữ ký số). (`[source: 3546]`-`[source: 3549]`).
        * **Thuật toán ví dụ:** RSA, Diffie-Hellman (dùng cho trao đổi khóa), ECC. (`[source: 3550]`).
        * **Ưu điểm:** Giải quyết vấn đề phân phối khóa của mã hóa đối xứng. Bạn có thể gửi public key cho bất kỳ ai mà không sợ lộ private key. Dùng để xác thực (chữ ký số - digital signature). (`[source: 3670]`).
        * **Nhược điểm:** **Chậm hơn đáng kể** so với mã hóa đối xứng do các phép toán phức tạp. (`[source: 3552]`, `[source: 3670]`). Thường có giới hạn về lượng dữ liệu có thể mã hóa trực tiếp trong một lần (ví dụ RSA). (`[source: 3940]`, `[source: 3944]`).
        * **Khi nào dùng:** Trao đổi khóa bí mật cho mã hóa đối xứng một cách an toàn; Chữ ký số để xác thực nguồn gốc và tính toàn vẹn dữ liệu; Mã hóa lượng nhỏ dữ liệu rất nhạy cảm (ví dụ: mã hóa key đối xứng).

    * **Tại sao thường kết hợp:** Do mã hóa bất đối xứng chậm và có giới hạn kích thước dữ liệu, người ta thường sử dụng mô hình **hybrid (lai)**:
        1.  Bên gửi tạo một khóa đối xứng ngẫu nhiên (session key) chỉ dùng cho phiên giao dịch/dữ liệu hiện tại (ví dụ: khóa AES).
        2.  Bên gửi mã hóa dữ liệu thực tế bằng khóa đối xứng này (nhanh).
        3.  Bên gửi lấy public key RSA của bên nhận.
        4.  Bên gửi mã hóa khóa đối xứng (session key) bằng public key RSA của bên nhận (chậm nhưng chỉ mã hóa key ngắn). (`[source: 3941]`).
        5.  Bên gửi gửi cả dữ liệu đã mã hóa bằng AES và khóa AES đã mã hóa bằng RSA cho bên nhận.
        6.  Bên nhận dùng private key RSA của mình để giải mã khóa AES.
        7.  Bên nhận dùng khóa AES vừa giải mã được để giải mã phần dữ liệu lớn.
        Cách này tận dụng tốc độ của mã hóa đối xứng và khả năng trao đổi khóa an toàn của mã hóa bất đối xứng. Đây là cách SSL/TLS và PGP/GnuPG hoạt động. (`[source: 3688]`-`[source: 3691]`, `[source: 3698]`).

## Chương 16: Securing Network Connections: SSL and SSH

**Bài tập 16.1: HTTPS Check & Redirect**

* **Kiến thức vận dụng:** Kiểm tra các biến `$_SERVER` để xác định giao thức đang sử dụng (`HTTPS` hoặc `SERVER_PORT`). Thực hiện redirect HTTP (301). (Sách không có code check HTTPS cụ thể nhưng nhấn mạnh tầm quan trọng của SSL/TLS cho bảo mật `[source: 4171]`, `[source: 1543]`).
* **Phân tích & Áp dụng:** Cần kiểm tra `$_SERVER['HTTPS']` có tồn tại và là 'on' không, HOẶC `$_SERVER['SERVER_PORT']` có phải là 443 không. Nếu không phải, tạo URL mới với `https://` và redirect.
* **Lời giải (PHP - Đặt ở đầu script):**
    ```php
    <?php
    /**
     * Kiểm tra nếu request hiện tại không phải HTTPS và thực hiện redirect 301.
     */
    function force_https() {
        // [source: Không có code check, nhưng logic dựa trên hoạt động của HTTPS]
        $is_https = false;
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $is_https = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
             // Kiểm tra header từ Load Balancer / Proxy ngược
             $is_https = true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
             // Một số cấu hình proxy khác
             $is_https = true;
        } elseif (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
             $is_https = true;
        }


        if (!$is_https) {
            // Xây dựng URL mới với https
            $new_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            // Thực hiện redirect 301
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $new_url);
            // Dừng script sau khi redirect
            exit();
        }
    }

    // Gọi hàm ở đầu các trang cần bảo vệ bằng HTTPS
    // force_https();

    // Ví dụ:
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
         echo "Kết nối đang sử dụng HTTPS.";
    } else {
         echo "Kết nối đang sử dụng HTTP.";
         // force_https(); // Gọi ở đây nếu muốn ép buộc
    }

    // ... phần còn lại của trang ...
    ?>
    ```
    *(Lưu ý: Việc kiểm tra header `X-Forwarded-Proto` rất quan trọng nếu ứng dụng của bạn nằm sau một Load Balancer hoặc Reverse Proxy xử lý SSL termination.)*

**Bài tập 16.2: SSH Key Authentication Setup**

* **Kiến thức vận dụng:** Hiểu cách SSH sử dụng key pair để xác thực thay cho mật khẩu và các bước cấu hình cơ bản. (Chapter 16, "Automating Connections", `[source: 4474]`-`[source: 4477]`).
* **Phân tích & Áp dụng:** Cần tạo key, copy public key lên server đích vào đúng file, và đảm bảo quyền truy cập file đúng.
* **Lời giải (Các bước):**
    1.  **Tạo Key Pair trên Client (Server A):** Mở terminal trên server A (nơi sẽ thực thi script PHP), chạy lệnh `ssh-keygen`.
        ```bash
        # Chạy với user mà script PHP sẽ thực thi (ví dụ: www-data hoặc user riêng)
        ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa_server_b -C "key_for_server_b"
        # -t rsa: Loại key (có thể dùng ed25519 hiện đại hơn)
        # -b 4096: Độ dài key (khuyến nghị cho RSA)
        # -f ~/.ssh/id_rsa_server_b: Tên file lưu key (nên đặt tên gợi nhớ)
        # -C "comment": Ghi chú cho key
        ```
        (`[source: 4463]` đề cập ssh-keygen). Lệnh này sẽ hỏi passphrase. **Để tự động hóa hoàn toàn, bạn cần để passphrase trống**. Tuy nhiên, điều này làm giảm bảo mật nếu private key (`id_rsa_server_b`) bị lộ. Nếu đặt passphrase, script PHP cần cung cấp passphrase khi kết nối (`[source: 4477]`). Lệnh này tạo ra 2 file: `id_rsa_server_b` (private key - **GIỮ BÍ MẬT**) và `id_rsa_server_b.pub` (public key).
    2.  **Copy Public Key lên Server Đích (Server B):** Cần đưa nội dung của file `id_rsa_server_b.pub` vào file `~/.ssh/authorized_keys` của user trên server B mà bạn muốn kết nối tới.
        * **Cách 1 (Dùng `ssh-copy-id` - dễ nhất nếu có):**
            ```bash
            # Chạy từ server A
            ssh-copy-id -i ~/.ssh/id_rsa_server_b.pub user_on_server_b@server_b_hostname_or_ip
            ```
            Lệnh này sẽ yêu cầu mật khẩu của `user_on_server_b` một lần để copy key.
        * **Cách 2 (Thủ công):**
            a.  Copy nội dung file `id_rsa_server_b.pub` trên server A.
            b.  SSH vào server B bằng mật khẩu: `ssh user_on_server_b@server_b_hostname_or_ip`
            c.  Trên server B, tạo thư mục `~/.ssh` nếu chưa có: `mkdir -p ~/.ssh`
            d.  Đặt quyền đúng cho thư mục `.ssh`: `chmod 700 ~/.ssh`
            e.  Mở file `~/.ssh/authorized_keys` (tạo nếu chưa có) bằng editor (ví dụ: `nano ~/.ssh/authorized_keys`).
            f.  Paste nội dung public key đã copy vào file này (mỗi key trên một dòng).
            g.  Lưu file và thoát editor.
            h.  Đặt quyền đúng cho file `authorized_keys`: `chmod 600 ~/.ssh/authorized_keys` (`[source: 4475]`)
            i.  Thoát khỏi server B.
    3.  **Kiểm tra Kết nối:** Từ server A, thử kết nối lại đến server B bằng key:
        ```bash
        ssh -i ~/.ssh/id_rsa_server_b user_on_server_b@server_b_hostname_or_ip
        ```
        Nếu cấu hình đúng, bạn sẽ đăng nhập được mà không cần mật khẩu (hoặc sẽ hỏi passphrase nếu bạn đã đặt khi tạo key).
    4.  **Sử dụng trong PHP (với extension SSH2):** Khi gọi `ssh2_auth_pubkey_file`, chỉ định đường dẫn đến file public key và private key đã tạo. (`[source: 4516]`, `[source: 4597]`).

**Bài tập 16.3: SSL vs SSH**

* **Kiến thức vận dụng:** Hiểu mục đích và cơ chế hoạt động cốt lõi của SSL/TLS và SSH. (Chapter 16, "Should I Use SSL or SSH?", `[source: 4616]`-`[source: 4624]`).
* **Phân tích & Áp dụng:** Cần so sánh dựa trên mục đích chính (bảo mật transport vs quản trị/truy cập shell), cách xác thực, và các ứng dụng điển hình.
* **Lời giải (So sánh):**

    | Tiêu chí                 | SSL/TLS                                     | SSH (Secure Shell)                           |
    | :----------------------- | :------------------------------------------ | :------------------------------------------- |
    | **Mục đích chính** | Bảo mật kênh truyền dữ liệu (Transport Layer Security) giữa client và server cho một ứng dụng cụ thể. (`[source: 4170]`) | Cung cấp kênh đăng nhập và thực thi lệnh từ xa an toàn (secure shell), truyền file an toàn (scp/sftp), và tạo đường hầm (tunneling) cho các giao thức khác. (`[source: 4424]`-`[source: 4426]`) |
    | **Lớp hoạt động** | Hoạt động ở tầng vận chuyển (Transport Layer), "bọc" các giao thức ứng dụng như HTTP, FTP, SMTP, IMAP. (`[source: 4170]`) | Thường hoạt động như một giao thức ứng dụng riêng biệt, cung cấp shell hoặc các dịch vụ con (sftp). Cũng có thể tạo tunnel ở tầng transport/application. |
    | **Xác thực Server** | **Bắt buộc:** Client luôn xác thực server qua SSL Certificate do một CA cấp (hoặc tự ký). (`[source: 4210]`, `[source: 4255]`) | **Bắt buộc (lần đầu):** Client xác thực server qua "host key" (thường tự tạo bởi server). Client lưu key này và cảnh báo nếu key thay đổi ở lần kết nối sau. (`[source: 4438]`) |
    | **Xác thực Client** | **Tùy chọn:** Server có thể yêu cầu Client Certificate (ít phổ biến trong ứng dụng web thông thường). Xác thực người dùng thường do lớp ứng dụng (ví dụ: login form) xử lý. (`[source: 4255]`) | **Bắt buộc:** Server luôn yêu cầu client xác thực danh tính user hệ điều hành trên server thông qua mật khẩu, public key, hoặc các phương thức khác (Kerberos...). (`[source: 4439]`-`[source: 4443]`) |
    | **Yêu cầu Tài khoản** | Client không nhất thiết cần tài khoản hệ thống trên server. | Client **phải** có tài khoản user hợp lệ trên server để đăng nhập. (`[source: 4442]`) |
    | **Ứng dụng điển hình** | HTTPS (web), FTPS (FTP qua SSL), SMTPS, IMAPS, POPS (email), bảo mật kết nối API/Web Service. (`[source: 4171]`, `[source: 4263]`) | Đăng nhập quản trị server từ xa, truyền file an toàn (scp/sftp), chạy lệnh từ xa, Git qua SSH, tạo VPN đơn giản qua SSH tunneling. (`[source: 4464]`) |
    | **Khi nào dùng?** | Khi cần mã hóa dữ liệu truyền giữa client và một dịch vụ cụ thể mà không yêu cầu client phải có tài khoản OS trên server. Phổ biến cho các dịch vụ công cộng. (`[source: 4618]`) | Khi cần truy cập tương tác (shell) hoặc thực thi lệnh/truyền file đến server với quyền của một user hệ thống cụ thể. Chủ yếu cho quản trị và developer. (`[source: 4618]`) |

    **Ví dụ:**
    * Bảo vệ trang đăng nhập website, API công cộng -> Dùng **SSL/TLS (HTTPS)**.
    * Developer push code lên server Git -> Dùng **SSH**.
    * Admin quản trị server từ xa -> Dùng **SSH**.
    * Ứng dụng gửi email an toàn -> Dùng **SSL/TLS (SMTPS)**.
    * Backup file giữa các server nội bộ an toàn -> Có thể dùng `rsync over **SSH**` hoặc FTPS (nếu dùng FTP).

# Lời giải Bài tập Thực hành Bảo mật PHP (Chương 17 - "Pro PHP Security")

Chương cuối cùng của cuốn sách tập trung vào các khuyến nghị tổng quát hơn về môi trường, quy trình phát triển và bảo trì phần mềm.

## Chương 17: Final Recommendations (Tổng kết và Khuyến nghị)

**Bài tập 17.1: Phân tích Rủi ro Shared Hosting**

* **Yêu cầu:** Thảo luận và liệt kê ít nhất 3 rủi ro bảo mật chính khi chạy ứng dụng PHP trên môi trường shared hosting truyền thống. Giải thích tại sao mỗi điểm lại là một rủi ro.
* **Kiến thức vận dụng:** Hiểu các vấn đề bảo mật cố hữu của môi trường shared hosting nơi nhiều người dùng/website chia sẻ cùng tài nguyên hệ thống và thường chạy dưới cùng một user web server (ví dụ: `nobody`). (Chapter 17, "Security Issues Related to Shared Hosting", `[source: 4628]` và "An Inventory of Effects", `[source: 4637]`-`[source: 4669]`).
* **Phân tích & Áp dụng:** Môi trường chia sẻ tài nguyên dẫn đến việc một người dùng có thể ảnh hưởng đến người dùng khác, cả vô tình lẫn cố ý, nếu các biện pháp cô lập không đủ mạnh. User web server chung có thể đọc file của nhiều người dùng khác nhau.
* **Lời giải (Phân tích rủi ro):**
    1.  **Rò rỉ/Đánh cắp mã nguồn và dữ liệu nhạy cảm:**
        * **Giải thích:** Trong cấu hình shared hosting truyền thống, file của các website khác nhau thường có quyền đọc bởi user chạy web server (ví dụ: `nobody`, `www-data`). Một script PHP bị lỗi hoặc độc hại trên một website (Website A) có thể bị lợi dụng để đọc file của website khác (Website B) trên cùng server, ví dụ đọc file cấu hình chứa mật khẩu CSDL (`config.php`). (`[source: 4628]`-`[source: 4636]`, `[source: 4653]`).
        * **Tại sao là rủi ro:** Mất mật khẩu CSDL dẫn đến mất toàn bộ dữ liệu. Lộ mã nguồn có thể giúp kẻ tấn công tìm ra các lỗ hổng khác.
    2.  **Can thiệp/Xung đột File Tạm và Session:**
        * **Giải thích:** Các website thường sử dụng chung thư mục tạm hệ thống (ví dụ: `/tmp`) hoặc thư mục lưu session mặc định. Một website (A) có thể đọc, ghi hoặc thậm chí xóa file tạm/session của website khác (B) nếu quyền không được cấu hình chặt chẽ. Kẻ tấn công trên A có thể cố gắng chiếm session của người dùng trên B hoặc tiêm dữ liệu vào file tạm của B. (`[source: 4655]`-`[source: 4657]`).
        * **Tại sao là rủi ro:** Dẫn đến chiếm quyền người dùng, thay đổi dữ liệu hoặc gây lỗi ứng dụng.
    3.  **Khai thác Lỗ hổng Chéo (Vulnerability Transfer):**
        * **Giải thích:** Nếu Website A có một lỗ hổng bảo mật (ví dụ: SQL Injection, Remote Code Execution) và bị kẻ tấn công chiếm quyền kiểm soát (dù chỉ với quyền của user web server), kẻ tấn công đó có thể sử dụng quyền truy cập này để dò quét và tấn công các website khác (như Website B) trên cùng server, đọc file config, hoặc thực hiện các hành động khác mà user web server được phép. (`[source: 4666]`-`[source: 4669]`).
        * **Tại sao là rủi ro:** Bảo mật của bạn phụ thuộc vào người yếu nhất trên server. Dù bạn code cẩn thận, bạn vẫn có thể bị ảnh hưởng bởi lỗ hổng của người khác.
    4.  **(Bonus) Tấn công Từ chối Dịch vụ (Denial of Service - DoS):**
        * **Giải thích:** Một website có thể chạy các script tốn nhiều tài nguyên CPU hoặc bộ nhớ (vô tình hoặc cố ý), làm cạn kiệt tài nguyên của server và khiến tất cả các website khác trên cùng server chạy chậm hoặc không thể truy cập. (`[source: 4663]`-`[source: 4665]`).
        * **Tại sao là rủi ro:** Ảnh hưởng trực tiếp đến tính sẵn sàng và hiệu năng của ứng dụng của bạn.

**Bài tập 17.2: Tách biệt Môi trường Development và Production**

* **Yêu cầu:** Tại sao việc tách biệt môi trường Development và Production lại quan trọng cho bảo mật? Liệt kê các biện pháp cần thực hiện để đảm bảo sự tách biệt này.
* **Kiến thức vận dụng:** Hiểu sự khác biệt về mục đích, cấu hình và yêu cầu truy cập giữa môi trường phát triển (nơi code được viết, sửa, test) và môi trường production (nơi ứng dụng chạy thực tế cho người dùng cuối). (Chapter 17, "Maintaining Separate Development and Production Environments", `[source: 4792]`-`[source: 4904]`).
* **Phân tích & Áp dụng:** Môi trường dev cần linh hoạt, mở cho dev team, chứa công cụ debug, code chưa ổn định. Môi trường prod cần ổn định, bảo mật cao, hạn chế truy cập tối đa, chỉ chứa code đã được kiểm thử kỹ. Trộn lẫn hai môi trường này tạo ra nhiều rủi ro.
* **Lời giải:**
    **Tại sao quan trọng:**
    1.  **Giảm thiểu Rủi ro cho Production:** Code đang phát triển, chưa ổn định hoặc chứa lỗi (kể cả lỗi bảo mật) không bao giờ nên chạy trên production, nơi nó có thể bị người dùng cuối hoặc kẻ tấn công khai thác. Việc thử nghiệm tính năng mới, debug trực tiếp trên production là cực kỳ nguy hiểm. (`[source: 4839]`-`[source: 4842]`).
    2.  **Kiểm thử An toàn và Đầy đủ:** Môi trường dev cho phép thực hiện các bài kiểm thử sâu rộng (unit test, integration test, security test, performance test) mà không ảnh hưởng đến người dùng thật. (`[source: 4840]`-`[source: 4841]`).
    3.  **Bảo mật Dữ liệu Production:** Môi trường dev thường dùng dữ liệu giả hoặc dữ liệu mẫu đã được làm sạch (anonymized). Việc tách biệt ngăn chặn nguy cơ dev vô tình làm lộ hoặc hỏng dữ liệu thật của người dùng trên production.
    4.  **Quản lý Truy cập Khác biệt:** Production cần kiểm soát truy cập cực kỳ chặt chẽ (chỉ sysadmin, deployer). Dev cần cởi mở hơn cho team phát triển. Gộp chung khiến việc quản lý quyền trở nên phức tạp và dễ mắc lỗi. (`[source: 4835]`-`[source: 4836]`).
    5.  **Môi trường và Công cụ Khác biệt:** Môi trường dev thường cài đặt các công cụ gỡ lỗi (Xdebug), profiling, hệ thống quản lý phiên bản (Git, SVN), bug tracking, v.v. Những công cụ này không nên có trên production vì lý do hiệu năng và bảo mật. (`[source: 4811]`-`[source: 4833]`, `[source: 4838]`).
    6.  **Phân tích Log hiệu quả hơn:** Log trên production sẽ "sạch" hơn, ít nhiễu từ các hoạt động dev/test, giúp dễ dàng phát hiện các hành vi bất thường hoặc dấu hiệu tấn công. (`[source: 4845]`-`[source: 4846]`).

    **Biện pháp đảm bảo tách biệt:**
    1.  **Server Vật lý/Virtual Riêng biệt:** Lý tưởng nhất là sử dụng các máy chủ vật lý hoặc máy ảo (VM) hoàn toàn riêng biệt cho dev, staging (nếu có), và production. (`[source: 4851]`-`[source: 4855]` - Mặc dù nói về dev trên prod là xấu, nhưng ngụ ý cần server riêng).
    2.  **Mạng Riêng biệt:** Đặt các môi trường vào các network segment khác nhau với firewall kiểm soát chặt chẽ luồng dữ liệu giữa chúng. Production chỉ nên cho phép truy cập từ các nguồn cần thiết (ví dụ: cổng 80/443 từ Internet, SSH từ IP quản trị cố định).
    3.  **Credentials Khác nhau:** **Tuyệt đối không** dùng chung mật khẩu (CSDL, SSH, API keys, user admin) giữa các môi trường. (`[source: 4890]`-`[source: 4892]`).
    4.  **Quản lý Truy cập Chặt chẽ:** Hạn chế tối đa quyền truy cập SSH/FTP/Admin vào server production. Chỉ cấp cho những người thực sự cần thiết và ghi log mọi hành động.
    5.  **Quy trình Deploy Rõ ràng:** Sử dụng quy trình deploy tự động hoặc bán tự động (ví dụ: qua Git hooks, CI/CD pipelines, rsync) để chuyển code *đã được kiểm thử* từ môi trường thấp hơn (dev/staging) lên production. **Nên dùng cơ chế "pull" từ production thay vì "push" từ dev.** (`[source: 4895]`-`[source: 4904]`).
    6.  **Dữ liệu Giả/Anonymized cho Dev:** Không bao giờ copy trực tiếp dữ liệu production xuống dev mà không qua bước làm sạch hoặc ẩn danh hóa các thông tin nhạy cảm.
    7.  **Không cài công cụ Dev trên Production:** Đảm bảo các công cụ như Xdebug, trình biên dịch (nếu không cần thiết), hệ thống quản lý phiên bản client không được cài đặt trên server production.

**Bài tập 17.3: Security Maintenance Checklist**

* **Kiến thức vận dụng:** Tổng hợp các hoạt động bảo trì cần thiết từ nhiều chương, bao gồm giám sát log, cập nhật phần mềm, backup, kiểm tra cấu hình, kiểm tra quyền. (Chapter 17 nhấn mạnh về updates `[source: 5132]`, backups `[source: 4734]`, `[source: 4991]`, monitoring `[source: 5006]`; các chương khác về logs - Ch10 `[source: 2405]`, permissions - Ch13/14 `[source: 3195]`, `[source: 3399]`).
* **Phân tích & Áp dụng:** Cần tạo ra một danh sách các công việc cụ thể, thực tế, có thể thực hiện định kỳ để đảm bảo an ninh liên tục cho ứng dụng.
* **Lời giải (Ví dụ Checklist):**

    **Checklist Bảo trì Bảo mật Ứng dụng PHP Production**

    **Hàng ngày:**

    * [ ] **Kiểm tra Log Hệ thống & Ứng dụng:**
        * Review log web server (Apache/Nginx) tìm lỗi bất thường (4xx, 5xx), lượng truy cập tăng đột biến, request đáng ngờ. (`[source: 5006]`).
        * Review log PHP (error_log) tìm lỗi nghiêm trọng (Fatal errors, Warnings).
        * Review log ứng dụng (nếu có) tìm hành động bất thường, lỗi logic. (`[source: 2410]`).
        * Review log CSDL (slow query log, error log) tìm lỗi hoặc truy vấn chậm bất thường. (`[source: 2427]`).
        * Review log hệ thống (`/var/log/messages`, `auth.log`/`secure`) tìm dấu hiệu đăng nhập thất bại, lỗi phần cứng, thay đổi hệ thống. (`[source: 2424]`, `[source: 2428]`).
    * [ ] **Kiểm tra Backup:** Xác nhận các job backup (CSDL, files) đã chạy thành công và file backup được tạo đúng dung lượng dự kiến. (`[source: 4991]` - cần kiểm tra kết quả).
    * [ ] **Giám sát Tài nguyên:** Kiểm tra CPU, RAM, Disk I/O, Network traffic có dấu hiệu bất thường (ví dụ: CPU 100% liên tục, disk đầy). (`[source: 4804]`).

    **Hàng tuần:**

    * [ ] **Kiểm tra Cập nhật Phần mềm:**
        * Chạy lệnh cập nhật của hệ điều hành (`apt update && apt list --upgradable`, `yum check-update`) để xem các bản vá bảo mật có sẵn cho OS và các package hệ thống. (`[source: 5150]`).
        * Kiểm tra các kênh thông báo bảo mật cho Web Server (Apache/Nginx), PHP, MySQL/PostgreSQL xem có bản vá mới không. (`[source: 5156]`).
        * Chạy `composer outdated` hoặc công cụ tương đương để kiểm tra các thư viện PHP có phiên bản mới (đặc biệt là các bản vá lỗi).
        * *Lên kế hoạch* áp dụng các bản vá quan trọng (ưu tiên vá ngay các lỗ hổng nghiêm trọng).
    * [ ] **Rà soát Quyền Truy cập:** Xem lại danh sách user có quyền truy cập SSH/FTP/Admin vào server production. Loại bỏ các tài khoản không còn cần thiết.
    * [ ] **Kiểm tra Backup Restore (Thử nghiệm):** Định kỳ (có thể hàng tháng/quý) thử restore backup CSDL và file ra một môi trường riêng biệt để đảm bảo backup có thể sử dụng được.

    **Hàng tháng/Quý:**

    * [ ] **Áp dụng Bản vá:** Thực hiện cập nhật các phần mềm đã lên kế hoạch (OS, web server, PHP, DB, libraries) sau khi đã kiểm thử trên môi trường staging. (`[source: 5205]`-`[source: 5207]`).
    * [ ] **Review Cấu hình Bảo mật:** Kiểm tra lại các file cấu hình quan trọng (Apache/Nginx vhost, php.ini, my.cnf, firewall rules) xem có cài đặt nào chưa tối ưu hoặc bị thay đổi không mong muốn.
    * [ ] **Quét Lỗ hổng (Tự động):** Sử dụng các công cụ quét lỗ hổng tự động (ví dụ: OWASP ZAP cho web app, Nessus/OpenVAS cho hạ tầng) để tìm các vấn đề phổ biến.
    * [ ] **Audit Quyền CSDL:** Kiểm tra lại quyền của các user CSDL, đảm bảo tuân thủ nguyên tắc quyền tối thiểu. (`[source: 3501]`-`[source: 3510]`).
    * [ ] **Đổi Mật khẩu:** Xem xét chính sách đổi mật khẩu định kỳ cho các tài khoản quản trị (SSH, DB admin).

    **Khi có Thay đổi Code/Hạ tầng:**

    * [ ] **Review Code:** Thực hiện peer review cho các thay đổi code lớn, đặc biệt là các phần liên quan đến xử lý input, authentication, authorization, session, file operations. (`[source: 233]`, `[source: 290]`).
    * [ ] **Kiểm thử Bảo mật:** Chạy lại các bài kiểm thử bảo mật (security testing) liên quan đến phần code bị thay đổi.
    * [ ] **Cập nhật Checklist:** Nếu thay đổi hạ tầng hoặc thêm dịch vụ mới, cập nhật checklist này cho phù hợp.