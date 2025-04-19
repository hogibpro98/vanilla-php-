# Tóm tắt Pro PHP Security (Second Edition, 2010) cho Senior PHP/MySQL Developer

Cuốn sách này cung cấp cái nhìn toàn diện về các nguyên tắc và kỹ thuật bảo mật ứng dụng web PHP, tập trung vào PHP 5.3 và MySQL. Dưới đây là tóm tắt các điểm chính dành cho lập trình viên có kinh nghiệm.

## I. Nội dung cốt lõi

1.  **Tư duy Bảo mật (Security Mindset - Chapter 1):**
    * Không có gì là an toàn 100%. Luôn nghĩ về quản lý rủi ro.
    * **Không bao giờ tin tưởng đầu vào người dùng (Never Trust User Input):** Đây là nguyên tắc cơ bản nhất. Mọi dữ liệu từ client (GET, POST, COOKIE, headers, uploads) đều có thể bị giả mạo hoặc chứa mã độc.
    * **Phòng thủ theo chiều sâu (Defense in Depth):** Sử dụng nhiều lớp bảo vệ thay vì dựa vào một cơ chế duy nhất.
    * **Giữ cho đơn giản (Simpler Is Easier to Secure):** Code phức tạp khó kiểm tra và bảo mật hơn.
    * **Peer Review:** Đánh giá code bởi người khác là rất quan trọng để phát hiện lỗ hổng.
    * Hiểu rõ các loại tấn công phổ biến: SQL Injection, XSS, Remote Execution, Session Hijacking, DoS, v.v.

2.  **Xác thực và Làm sạch Đầu vào (Input Validation & Sanitization - Chapter 2):**
    * Là tuyến phòng thủ đầu tiên và quan trọng nhất.
    * Tắt `register_globals` (đã bị loại bỏ trong các phiên bản PHP mới hơn nhưng quan trọng thời điểm đó).
    * Khởi tạo biến trước khi sử dụng.
    * Chỉ chấp nhận các giá trị đầu vào dự kiến (whitelist validation).
    * Kiểm tra kỹ lưỡng: Kiểu dữ liệu (`is_int`, `is_string`, etc.), độ dài (`strlen`), định dạng (regex, `filter_var`).
    * **Làm sạch (Sanitize)** dữ liệu *trước khi* truyền sang hệ thống khác (DB, shell, HTML output).
    * Sử dụng các lớp/hàm trừu tượng để xử lý validation/sanitization nhất quán.
    * **Che giấu lỗi chi tiết (Obscure Errors):** Không hiển thị lỗi PHP hoặc thông tin hệ thống chi tiết cho người dùng cuối (`display_errors = Off`).

3.  **Ngăn chặn Tấn công Phổ biến (Part 2):**
    * **SQL Injection (Chapter 3):** Hiểu cách hoạt động. Sử dụng **Prepared Statements** (với `mysqli` hoặc PDO) là cách tốt nhất. Nếu không, phải escape *tất cả* input bằng hàm phù hợp với CSDL (`mysqli_real_escape_string`). Demarcate (dùng dấu nháy) mọi giá trị. Kiểm tra kiểu dữ liệu.
    * **Cross-Site Scripting (XSS - Chapter 4):** Hiểu các loại XSS (Reflected, Stored, DOM-based - sách này chủ yếu nói về 2 loại đầu). **Luôn escape HTML output** (`htmlentities` với `ENT_QUOTES` và encoding phù hợp như UTF-8). Cẩn thận với URL do người dùng cung cấp (kiểm tra scheme). Nếu cho phép HTML, dùng thư viện lọc đáng tin cậy (HTML Purifier - *sách đề cập Tidy, Safe_HTML*). SSL *không* chống được XSS.
    * **Remote Execution (Chapter 5):** Tránh `eval()` nếu có thể. Làm sạch đầu vào cho `eval()`. Hạn chế extension file upload. Lưu file upload ngoài web root. **Escape shell commands** (`escapeshellarg`, `escapeshellcmd`). Cẩn thận với modifier `e` trong `preg_replace` (đã bị loại bỏ trong PHP 7+). Không `include`/`require` file từ nguồn không đáng tin cậy hoặc dựa trên input.
    * **Session Hijacking (Chapter 7):** Hiểu session ID và cách truyền (cookie/URL). **Chỉ dùng cookie** (`session.use_only_cookies=1`). **Regenerate session ID** khi thay đổi trạng thái (đăng nhập, nâng quyền - `session_regenerate_id()`). Dùng session timeout ngắn. Sử dụng HTTPS để bảo vệ session cookie khi truyền đi.

4.  **Bảo mật Hoạt động (Part 3):**
    * **CAPTCHA (Chapter 9):** Dùng để chống bot, hữu ích cho form đăng ký, bình luận. Hiểu các loại và điểm yếu.
    * **Authentication & Authorization (Chapter 10):** Xác thực người dùng (email token, SMS, payment, digital signature). Quản lý quyền truy cập: **Roles-Based Access Control (RBAC)** được khuyến nghị hơn là chỉ dựa vào user type/group đơn giản để quản lý quyền hạn phức tạp. Ghi log hành động quan trọng để truy vết.
    * **Ngăn chặn Mất dữ liệu (Chapter 11):** Dùng cờ "lock" cho bản ghi quan trọng. Yêu cầu xác nhận cho hành động xóa/thay đổi quan trọng. Sử dụng **soft deletes** (đánh dấu đã xóa thay vì `DELETE` vật lý) để có thể khôi phục. Áp dụng **versioning** cho dữ liệu quan trọng. Cấp quyền DB tối thiểu cho user ứng dụng. Backup thường xuyên.
    * **Thực thi Lệnh Hệ thống/RPC An toàn (Chapter 12):** Các lệnh nguy hiểm (root, tốn tài nguyên). Sử dụng **API/Queue** để tách biệt yêu cầu (từ user web không đặc quyền) và thực thi (bởi process đặc quyền/background). Xử lý timeout cho các cuộc gọi mạng. Cache kết quả subrequest.

5.  **Môi trường An toàn (Part 4):**
    * **Bảo mật Unix (Chapter 13):** Hiểu quyền file (`chmod`, `chown`, `chgrp`). Dùng umask phù hợp. Sử dụng `chroot` jails (cẩn thận). PHP Safe Mode (đã bị loại bỏ, nhưng các khái niệm như `open_basedir`, `disable_functions` vẫn hữu ích).
    * **Bảo mật CSDL (MySQL - Chapter 14):** Bảo vệ thư mục dữ liệu, file log, file config (`my.cnf`). Loại bỏ user anonymous, yêu cầu mật khẩu mạnh, hạn chế host wildcards (`%`), cấp quyền tối thiểu (Principle of Least Privilege). Backup CSDL thường xuyên (`mysqldump`). Hạn chế kết nối mạng nếu không cần (`skip-networking`).
    * **Mã hóa (Encryption - Chapter 15):** Phân biệt **Hashing** (MD5 - yếu, SHA1 - yếu, nên dùng SHA-256+) và **Encryption**. Hashing dùng cho mật khẩu (phải dùng **salt**!) và kiểm tra tính toàn vẹn. Encryption (Symmetric: AES, Blowfish; Asymmetric: RSA) dùng cho dữ liệu cần giải mã. Dùng thư viện (`mcrypt` - cũ, `openssl` - hiện đại hơn). Hiểu Key Management, IVs, Modes (CBC).
    * **Kết nối Mạng An toàn (SSL/SSH - Chapter 16):** Dùng **SSL/TLS** (HTTPS, FTPS) để mã hóa dữ liệu truyền đi. Hiểu Certificates, CAs. Dùng **SSH** (scp, sftp) để truy cập/quản trị/truyền file an toàn. Dùng xác thực bằng key cho tự động hóa.
    * **Khác (Chapter 17):** Rủi ro của shared hosting. **Tách biệt môi trường Development và Production**. Luôn **cập nhật phần mềm** (OS, Web Server, PHP, MySQL, Libraries) để vá lỗ hổng.

## II. Tips and Tricks

* **Input Validation:** Luôn ưu tiên whitelist (chỉ cho phép cái tốt) hơn blacklist (chặn cái xấu). Dùng `filter_var` của PHP nếu có thể.
* **SQL Injection:** Prepared statements là bạn. Đừng tự viết hàm escape.
* **XSS:** `htmlentities($input, ENT_QUOTES, 'UTF-8')` là tối thiểu cho mọi output vào HTML.
* **Sessions:** `session_regenerate_id(true)` khi đăng nhập/đăng xuất/thay đổi quyền. Set cookie `HttpOnly` và `Secure`.
* **Passwords:** Dùng hàm hashing hiện đại của PHP (`password_hash`, `password_verify`) thay vì tự roll `md5`/`sha1` với salt.
* **File Uploads:** Đừng tin tên file/MIME type từ client. Generate tên file mới. Kiểm tra bằng `finfo` (PHP) hoặc `file` (Unix) nếu cần. Lưu ngoài web root.
* **Error Handling:** Log lỗi chi tiết vào file (ngoài web root), chỉ hiển thị thông báo chung chung cho user.
* **Configuration:** Không lưu credential (DB pass, API key) trong code hoặc web root. Dùng biến môi trường hoặc file config ngoài web root với quyền đọc hạn chế.
* **Permissions:** Áp dụng quyền tối thiểu cho file/folder và user CSDL. User web server (e.g., `www-data`, `nobody`) chỉ nên có quyền đọc tối thiểu và ghi vào các thư mục cụ thể (uploads, cache, logs).
* **HTTPS:** Ép buộc dùng HTTPS cho toàn bộ trang hoặc ít nhất là các phần nhạy cảm (login, forms).
* **Libraries:** Dùng thư viện/framework uy tín đã giải quyết nhiều vấn đề bảo mật cơ bản. Cập nhật chúng thường xuyên.

## III. Các Kỹ thuật Hay

* **Prepared Statements (MySQLi/PDO):** Kỹ thuật hiệu quả nhất chống SQL Injection bằng cách tách câu lệnh SQL và dữ liệu.
* **RBAC (Roles-Based Access Control):** Mô hình linh hoạt để quản lý quyền hạn phức tạp thay vì dùng cờ is_admin đơn giản hoặc group cố định.
* **Content Security Policy (CSP):** Header HTTP để kiểm soát tài nguyên nào (JS, CSS, images) trình duyệt được phép tải, giúp giảm thiểu XSS. (*Kỹ thuật hiện đại hơn sách*)
* **HMAC (Hash-based Message Authentication Code):** Dùng để xác thực tính toàn vẹn và nguồn gốc của dữ liệu/request, hữu ích trong API security.
* **Salted Password Hashing:** Kỹ thuật bắt buộc khi lưu trữ mật khẩu để chống lại rainbow table attacks. Dùng hàm PHP tích hợp là tốt nhất.
* **Asymmetric Encryption for Key Exchange:** Dùng RSA (OpenSSL) để mã hóa một key symmetric (ví dụ AES), sau đó dùng key symmetric đó để mã hóa/giải mã dữ liệu lớn. Key private RSA được giữ offline/an toàn.
* **API/Queuing for Privileged Operations:** Tách biệt logic ứng dụng web (chạy với quyền thấp) và các tác vụ cần quyền cao hoặc tốn tài nguyên bằng cách dùng hàng đợi (queue) và một worker process chạy nền với quyền phù hợp.
* **Secure File Transfer (SFTP/SCP over SSH):** Thay thế FTP không an toàn bằng các giao thức dựa trên SSH.
* **Versioning (Data & Files):** Áp dụng cơ chế versioning (dùng bảng riêng trong DB hoặc hệ thống như Git/SVN cho code/files) để có thể rollback khi có lỗi hoặc tấn công.
* **Automated Security Scanning Tools:** Sử dụng các công cụ tự động (OWASP ZAP, Nessus, etc.) để quét lỗ hổng thường xuyên. (*Ngoài phạm vi sách nhưng quan trọng*)
* **Dependency Management & Auditing:** Dùng Composer và các công cụ như `security-checker` để quản lý thư viện PHP và kiểm tra lỗ hổng đã biết trong các thư viện đó. (*Kỹ thuật hiện đại hơn sách*)