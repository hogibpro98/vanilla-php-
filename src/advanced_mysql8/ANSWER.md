# Lời giải Bài tập Thực hành: Tối ưu và Quản lý CSDL Nhân viên 10 Triệu Bản ghi (Advanced MySQL 8)

## Lưu ý chung:
* Các lệnh SQL dưới đây giả định bạn đang làm việc với schema chứa các bảng `nth_*` đã được nạp dữ liệu (khoảng 10 triệu bản ghi nhân viên).
* Kết quả `EXPLAIN`, thời gian thực thi, và các giá trị `STATUS` có thể thay đổi tùy thuộc vào cấu hình server, phiên bản MySQL, và khối lượng dữ liệu chính xác. Điều quan trọng là hiểu *quy trình* phân tích và tối ưu.
* Tham chiếu đến PDF là ước tính dựa trên nội dung các chương.

---

## Chương 2: Quản lý Truy cập và Tính năng Mới trên Dữ liệu Lớn

### Bài tập 2.1: Phân quyền Chi tiết với Roles

**Phân tích:** Chương 2 (tr. 12-13) và Chương 10 (tr. 234-236) giới thiệu về Roles như một cách để nhóm các quyền và gán cho user, đơn giản hóa quản lý quyền. Bài tập này yêu cầu tạo các role nghiệp vụ cụ thể (HR, Payroll) và gán quyền tương ứng trên các bảng `nth_*`.

**Các bước & Lời giải:**

1.  **Tạo Roles:**
    ```sql
    CREATE ROLE IF NOT EXISTS 'hr_manager', 'payroll_clerk';
    ```
2.  **Cấp quyền:**
    ```sql
    -- Quyền cho HR Manager
    GRANT SELECT ON `nth_employees` TO 'hr_manager';
    GRANT SELECT ON `nth_dept_emp` TO 'hr_manager';
    GRANT SELECT ON `nth_dept_manager` TO 'hr_manager';
    GRANT SELECT ON `nth_titles` TO 'hr_manager';
    GRANT SELECT ON `nth_departments` TO 'hr_manager'; -- Thêm bảng phòng ban

    -- Quyền cho Payroll Clerk
    GRANT SELECT ON `nth_employees` TO 'payroll_clerk';
    GRANT SELECT, UPDATE ON `nth_salaries` TO 'payroll_clerk';
    ```
    *(Lưu ý: Quyền SELECT trên nth_employees cho payroll_clerk là cần thiết để biết emp_id khi cập nhật lương)*.
3.  **Tạo Users và Gán Roles:**
    ```sql
    CREATE USER IF NOT EXISTS 'alice'@'localhost' IDENTIFIED BY 'HRPass@1';
    CREATE USER IF NOT EXISTS 'bob'@'localhost' IDENTIFIED BY 'PayrollP@ss2';

    GRANT 'hr_manager' TO 'alice'@'localhost';
    GRANT 'payroll_clerk' TO 'bob'@'localhost';

    -- Kích hoạt role mặc định (quan trọng trong MySQL 8+)
    SET DEFAULT ROLE 'hr_manager' FOR 'alice'@'localhost';
    SET DEFAULT ROLE 'payroll_clerk' FOR 'bob'@'localhost';

    FLUSH PRIVILEGES;
    ```
4.  **Kiểm tra:**
    * Đăng nhập bằng `mysql -u alice -pHRPass@1` và chạy:
        ```sql
        SELECT * FROM nth_employees LIMIT 10; -- OK
        SELECT * FROM nth_salaries LIMIT 10; -- Error (SELECT command denied)
        ```
    * Đăng nhập bằng `mysql -u bob -pPayrollP@ss2` và chạy:
        ```sql
        SELECT * FROM nth_salaries WHERE emp_id = 10001 LIMIT 1; -- OK
        UPDATE nth_salaries SET salary = salary + 1 WHERE emp_id = 10001 AND to_date='9999-01-01'; -- OK (nếu record tồn tại)
        SELECT * FROM nth_titles WHERE emp_id = 10001 LIMIT 1; -- Error (SELECT command denied)
        ```
    * Xem quyền của Bob thông qua role:
        ```sql
        -- Chạy với user root hoặc user có quyền
        SHOW GRANTS FOR 'bob'@'localhost' USING 'payroll_clerk';
        /* Output sẽ hiển thị các lệnh GRANT SELECT, UPDATE trên nth_salaries và SELECT trên nth_employees */
        ```

### Bài tập 2.2: Sử dụng Invisible Indexes

**Phân tích:** Chương 2 (tr. 21-23) giới thiệu Invisible Indexes như một cách để kiểm tra ảnh hưởng của việc bỏ index mà không cần xóa ngay. Ta sẽ tạo index, xem `EXPLAIN`, ẩn index, xem lại `EXPLAIN`, rồi hiện lại.

**Các bước & Lời giải:**

1.  **Tạo Index:**
    ```sql
    CREATE INDEX idx_hiredate ON nth_employees(hire_date);
    ```
2.  **Phân tích:** (Chọn một ngày thực tế, ví dụ '1990-01-15')
    ```sql
    EXPLAIN SELECT emp_no, first_name, last_name FROM nth_employees WHERE hire_date = '1990-01-15';
    -- Ghi lại output, chú ý cột 'key', có thể sẽ là 'idx_hiredate'.
    ```
3.  **Ẩn Index:**
    ```sql
    ALTER TABLE nth_employees ALTER INDEX idx_hiredate INVISIBLE;
    ```
4.  **Phân tích lại:**
    ```sql
    EXPLAIN SELECT emp_no, first_name, last_name FROM nth_employees WHERE hire_date = '1990-01-15';
    -- Ghi lại output. Cột 'key' bây giờ sẽ là NULL (hoặc một index khác nếu có), vì optimizer không "thấy" idx_hiredate nữa. Type có thể chuyển thành ALL nếu không có index nào khác phù hợp.
    ```
5.  **Hiện Index:**
    ```sql
    ALTER TABLE nth_employees ALTER INDEX idx_hiredate VISIBLE;
    ```
6.  **Kiểm tra trạng thái:**
    ```sql
    SHOW INDEXES FROM nth_employees WHERE Key_name = 'idx_hiredate';
    -- Quan sát cột 'Visible'. Giá trị sẽ là 'YES' hoặc 'NO'.
    ```

### Bài tập 2.3: Resource Groups (Mô tả)

**Phân tích:** Chương 2 (tr. 13-17) mô tả Resource Groups cho phép gán CPU và độ ưu tiên cho các nhóm thread. Áp dụng để giới hạn tài nguyên cho các user chạy báo cáo nặng.

**Các bước & Lời giải (Mô tả):**

1.  **Tạo Resource Group:** Sử dụng lệnh `CREATE RESOURCE GROUP reporting_users TYPE = USER VCPU_IDS = 0 THREAD_PRIORITY = 15;` (Giả sử server có nhiều hơn 1 vCPU, ta chỉ gán vCPU 0 cho nhóm này với độ ưu tiên thấp là 15).
2.  **Gán cho User (Khi chạy Query):** Khi user `reporter` thực hiện một query nặng, cần xác định `PROCESSLIST` ID (ví dụ: 12345) của session đó. Sau đó, một user có quyền (như root) chạy lệnh: `SET RESOURCE GROUP reporting_users FOR 12345;`.
3.  **Kết quả mong đợi:** Các thread của session 12345 sẽ bị giới hạn chạy chủ yếu trên vCPU 0 và có độ ưu tiên thấp hơn các thread hệ thống hoặc user khác, giúp giảm ảnh hưởng lên hiệu năng chung của server, dù query của reporter có thể chạy chậm hơn.

---

## Chương 3: Tối ưu Indexing cho Hiệu năng Truy vấn 10 Triệu dòng

### Bài tập 3.1: Phân tích EXPLAIN ANALYZE với JOIN

**Phân tích:** Chương 3 (tr. 41-47, 57-60) hướng dẫn đọc `EXPLAIN`. Với dữ liệu lớn, `EXPLAIN ANALYZE` (có trong MySQL 8.0.18+) cung cấp thông tin thực thi thực tế (thời gian, số hàng thực tế) rất hữu ích. Query này cần JOIN nhiều bảng để lấy thông tin đầy đủ.

**Các bước & Lời giải:**

1.  **Query Phân tích:**
    ```sql
    SELECT
        e.emp_no,
        e.first_name,
        e.last_name,
        t.title,
        s.salary,
        d.dept_name
    FROM
        nth_employees e
    JOIN
        nth_titles t ON e.emp_id = t.emp_id AND t.to_date = '9999-01-01' -- Chức vụ hiện tại
    JOIN
        nth_salaries s ON e.emp_id = s.emp_id AND s.to_date = '9999-01-01' -- Lương hiện tại
    JOIN
        nth_dept_emp de ON e.emp_id = de.emp_id AND de.to_date = '9999-01-01' -- Phòng ban hiện tại
    JOIN
        nth_departments d ON de.dept_id = d.dept_id
    WHERE
        d.dept_name = 'Sales' -- Điều kiện lọc phòng ban
    ORDER BY
        s.salary DESC -- Sắp xếp theo lương giảm dần
    LIMIT 50;
    ```
2.  **Phân tích:**
    ```sql
    EXPLAIN ANALYZE -- Hoặc chỉ EXPLAIN nếu phiên bản < 8.0.18
    -- (Câu lệnh SELECT ở trên)
    ```
    * **Quan sát:** Chú ý đến các dòng có `actual time` cao nhất. Xem `rows` (ước tính) và `actual rows` (thực tế) có chênh lệch nhiều không.
    * **JOIN Order & Type:** Thứ tự MySQL chọn JOIN các bảng. `type` là gì (có phải `eq_ref`, `ref` hay tệ hơn là `index`, `ALL`?).
    * **Filtering:** Bước lọc `d.dept_name = 'Sales'` xảy ra sớm hay muộn? Có index trên `dept_name` không? (Mặc định là không).
    * **Sorting:** Bước `ORDER BY s.salary DESC` có `using filesort` không? Index trên `nth_salaries` có giúp gì không?
    * **Indexes:** Các điều kiện JOIN (`e.emp_id = ...`) và `WHERE` (`t.to_date`, `s.to_date`, `de.to_date`, `d.dept_name`) có được hỗ trợ bởi index không? (Các cột `emp_id` trong các bảng thường có index do FK, nhưng `to_date` và `dept_name` thì không).

### Bài tập 3.2: Thiết kế và Đánh giá Compound Index

**Phân tích:** Chương 3 (tr. 49-56, 62-67) nhấn mạnh lợi ích của compound index và thứ tự cột. Query này lọc theo chức vụ, phòng ban, và ngày thuê. Cần index hỗ trợ hiệu quả các điều kiện này.

**Các bước & Lời giải:**

1.  **Query Mục tiêu:**
    ```sql
    SELECT
        e.emp_no, e.first_name, e.last_name, e.hire_date
    FROM
        nth_employees e
    JOIN
        nth_titles t ON e.emp_id = t.emp_id
    JOIN
        nth_dept_emp de ON e.emp_id = de.emp_id
    JOIN
        nth_departments d ON de.dept_id = d.dept_id
    WHERE
        t.title = 'Senior Engineer'
        AND t.to_date = '9999-01-01'
        AND d.dept_name = 'Engineering'
        AND de.to_date = '9999-01-01'
        AND e.hire_date > '2000-01-01';
    ```
2.  **Đo lường Ban đầu:** Chạy query và `EXPLAIN ANALYZE`. Ghi lại thời gian và các điểm yếu (ví dụ: scan nhiều hàng, filesort nếu có ORDER BY).
3.  **Thiết kế Index:**
    * **Phân tích Selectivity & Query:** Điều kiện lọc chính là `title`, `dept_name`, `hire_date`, và các điều kiện `to_date = '9999-01-01'` để lấy thông tin hiện tại.
    * **Đề xuất:**
        * `nth_departments`: `CREATE INDEX idx_dept_name ON nth_departments(dept_name);` (Giúp lọc phòng ban nhanh).
        * `nth_titles`: `CREATE INDEX idx_title_todate_emp ON nth_titles(title, to_date, emp_id);` (Giúp lọc chức vụ và `to_date`, `emp_id` để JOIN). Thứ tự `(title, to_date)` quan trọng.
        * `nth_dept_emp`: `CREATE INDEX idx_dept_todate_emp ON nth_dept_emp(dept_id, to_date, emp_id);` (Giúp JOIN với `departments` qua `dept_id` đã lọc, lọc `to_date`, `emp_id` để JOIN).
        * `nth_employees`: `CREATE INDEX idx_hiredate ON nth_employees(hire_date);` (Giúp lọc `hire_date`).
    * **Giải thích thứ tự:** Trong `idx_title_todate_emp`, `title` được đặt trước vì thường có tính chọn lọc cao hơn `to_date` (chỉ có 1 giá trị '9999-01-01' là phổ biến). Tương tự cho `idx_dept_todate_emp`.
4.  **Tạo và Đo lường Lại:** Tạo các index trên. Chạy lại query và `EXPLAIN ANALYZE`. So sánh thời gian thực thi (kỳ vọng giảm đáng kể) và kế hoạch thực thi (kỳ vọng `type` tốt hơn, ít hàng được xử lý hơn).

### Bài tập 3.3: Kiểm tra và Bảo trì Index

**Phân tích:** Bảng lớn đòi hỏi quản lý index hiệu quả, kiểm tra kích thước để ước tính bộ nhớ cần thiết.

**Các bước & Lời giải:**

1.  **Kiểm tra Kích thước:**
    ```sql
    SELECT
        index_name,
        ROUND(SUM(stat_value * @@innodb_page_size) / 1024 / 1024, 2) AS index_size_mb
    FROM
        mysql.innodb_index_stats
    WHERE
        database_name = DATABASE() -- Thay DATABASE() bằng tên DB nếu cần
        AND table_name IN ('nth_employees', 'nth_salaries')
    GROUP BY index_name
    ORDER BY index_size_mb DESC;
    -- Hoặc dùng sys schema:
    -- SELECT index_name, table_name, index_size FROM sys.schema_index_statistics WHERE table_schema = DATABASE() AND table_name IN ('nth_employees', 'nth_salaries') ORDER BY index_size DESC;
    ```
    * Quan sát index nào chiếm nhiều dung lượng nhất (thường là PK hoặc index trên cột VARCHAR dài).
2.  **Phân mảnh Index (Lý thuyết):**
    * Phân mảnh (fragmentation) xảy ra khi các trang dữ liệu/index không liên tục về mặt vật lý trên đĩa hoặc có nhiều không gian trống bên trong trang do các thao tác INSERT/DELETE/UPDATE.
    * Ảnh hưởng: Tăng I/O đọc vì phải đọc nhiều trang hơn cho cùng một lượng dữ liệu logic, giảm hiệu quả của buffer pool caching.
    * Lệnh tối ưu: `OPTIMIZE TABLE nth_employees;` (Rebuild bảng và index, có thể khóa bảng lâu). Hoặc `ALTER TABLE nth_employees ENGINE=InnoDB;` (cũng rebuild).
    * Ảnh hưởng tính sẵn sàng: Các lệnh rebuild thường yêu cầu khóa bảng (write lock hoặc full lock tùy phiên bản/cấu hình), gây downtime cho các thao tác ghi trên bảng đó. Cần thực hiện trong thời gian bảo trì. Các phiên bản mới hơn có cải tiến về Online DDL nhưng vẫn cần cẩn trọng.

---

## Chương 4: Kỹ thuật Dữ liệu Nâng cao cho Query Lớn

### Bài tập 4.1: Triển khai và Đánh giá Partitioning

**Phân tích:** Chương 4 (tr. 76-85) trình bày về Partitioning để quản lý bảng lớn, đặc biệt hiệu quả khi truy vấn hoặc xóa dữ liệu theo key phân hoạch (ví dụ: thời gian). `RANGE` partitioning theo năm là phổ biến.

**Các bước & Lời giải:**

1.  **Partition `nth_employees`:**
    ```sql
    ALTER TABLE nth_employees PARTITION BY RANGE (YEAR(hire_date)) (
        PARTITION p_before_1990 VALUES LESS THAN (1990),
        PARTITION p1990_1991 VALUES LESS THAN (1992),
        PARTITION p1992_1993 VALUES LESS THAN (1994),
        PARTITION p1994_1995 VALUES LESS THAN (1996),
        PARTITION p1996_1997 VALUES LESS THAN (1998),
        PARTITION p1998_1999 VALUES LESS THAN (2000),
        PARTITION p2000_2001 VALUES LESS THAN (2002),
        -- Thêm các partition khác cho đến năm hiện tại + 1
        PARTITION p_future VALUES LESS THAN MAXVALUE
    );
    ```
2.  **Đo lường Query:** (Chọn năm 1995 chẳng hạn)
    ```sql
    -- Chạy trước khi đo: FLUSH STATUS; FLUSH TABLES;
    SELECT COUNT(*) FROM nth_employees WHERE hire_date BETWEEN '1995-01-01' AND '1995-12-31';
    -- Ghi lại thời gian thực thi.
    EXPLAIN SELECT COUNT(*) FROM nth_employees WHERE hire_date BETWEEN '1995-01-01' AND '1995-12-31';
    -- Kiểm tra cột 'partitions', nên chỉ hiển thị partition 'p1994_1995'.
    ```
3.  **Đo lường Xóa:**
    ```sql
    -- Đo DELETE (chạy trong transaction và rollback để không mất dữ liệu)
    START TRANSACTION;
    -- Chạy trước khi đo: FLUSH STATUS; FLUSH TABLES;
    DELETE FROM nth_employees WHERE YEAR(hire_date) < 1990;
    -- Ghi lại thời gian thực thi.
    ROLLBACK;

    -- Đo DROP PARTITION
    -- Chạy trước khi đo: FLUSH STATUS; FLUSH TABLES;
    ALTER TABLE nth_employees DROP PARTITION p_before_1990;
    -- Ghi lại thời gian thực thi. (Kỳ vọng nhanh hơn nhiều so với DELETE)
    -- Lưu ý: Lệnh này xóa dữ liệu vĩnh viễn. Cần tạo lại partition nếu muốn thêm dữ liệu cũ.
    ```
4.  **Partition `nth_salaries`:** Lặp lại các bước tương tự với `ALTER TABLE nth_salaries PARTITION BY RANGE (YEAR(from_date)) (...)`.

### Bài tập 4.2: Tối ưu Query Tính toán Phức tạp

**Phân tích:** Query này JOIN 5 bảng lớn và thực hiện tổng hợp (`AVG`), là ứng viên điển hình cho việc tối ưu index và có thể cả cách viết query.

**Các bước & Lời giải:**

1.  **Query Thách thức:**
    ```sql
    SELECT
        d.dept_name,
        t.title,
        AVG(s.salary) AS avg_salary
    FROM
        nth_salaries s
    JOIN
        nth_employees e ON s.emp_id = e.emp_id
    JOIN
        nth_titles t ON e.emp_id = t.emp_id
    JOIN
        nth_dept_emp de ON e.emp_id = de.emp_id
    JOIN
        nth_departments d ON de.dept_id = d.dept_id
    WHERE
        s.to_date = '9999-01-01'
        AND t.to_date = '9999-01-01'
        AND de.to_date = '9999-01-01'
    GROUP BY
        d.dept_name,
        t.title
    ORDER BY
        d.dept_name,
        t.title;
    ```
2.  **Phân tích & Tối ưu:**
    * **`EXPLAIN ANALYZE`:** Chạy và tìm узкие места. Có thể JOIN `nth_salaries` với `nth_employees` trước, rồi mới JOIN các bảng khác.
    * **Indexes:**
        * `nth_salaries`: Cần index trên `(to_date, emp_id, salary)` để lọc và lấy salary hiệu quả. `(emp_id, to_date, salary)` cũng tốt cho join. Mặc định có `UNIQUE(emp_id, from_date)`, không tối ưu lắm cho query này. Nên tạo: `CREATE INDEX idx_sal_todate_emp_salary ON nth_salaries(to_date, emp_id, salary);`
        * `nth_titles`: Tương tự, `CREATE INDEX idx_title_todate_emp_title ON nth_titles(to_date, emp_id, title);`
        * `nth_dept_emp`: `CREATE INDEX idx_deptemp_todate_emp_dept ON nth_dept_emp(to_date, emp_id, dept_id);`
        * `nth_departments`: Index trên `dept_name` đã tạo ở Ch3. `PK(dept_id)` đã có.
        * `nth_employees`: `PK(emp_id)` đã có.
    * **Viết lại Query:** Thứ tự JOIN mặc định của MySQL thường khá tốt, nhưng đôi khi chỉ định thứ tự hoặc dùng subquery có thể hiệu quả hơn. Trong trường hợp này, việc đảm bảo các index trên các cột `to_date` và cột JOIN (`emp_id`, `dept_id`) là quan trọng nhất.
3.  **Đo lường:** Chạy query và `EXPLAIN ANALYZE` trước và sau khi tạo các index đề xuất. Thời gian thực thi nên giảm đáng kể.

### Bài tập 4.3: Dọn dẹp Index Thực tế

**Phân tích:** Sau khi tạo nhiều index thử nghiệm, cần xác định index nào thực sự cần thiết.

**Các bước & Lời giải:**

1.  **Tạo Index Thử nghiệm:**
    ```sql
    CREATE INDEX idx_emp_fname ON nth_employees(first_name);
    CREATE INDEX idx_emp_hire ON nth_employees(hire_date); -- Có thể đã tạo ở 2.2
    CREATE INDEX idx_sal_emp ON nth_salaries(emp_id); -- Trùng với FK index
    ```
2.  **Chạy Workload:** Thực thi lại các query từ bài 3.1, 3.2, 4.2, và các query khác bạn nghĩ ra trong vài phút.
3.  **Phát hiện:**
    ```sql
    SELECT * FROM sys.schema_redundant_indexes WHERE table_schema = DATABASE();
    -- Kỳ vọng thấy idx_sal_emp là redundant với FK index của nó.

    SELECT * FROM sys.schema_unused_indexes WHERE table_schema = DATABASE();
    -- Kỳ vọng thấy idx_emp_fname nếu không có query nào lọc theo first_name được chạy. idx_emp_hire có thể được dùng hoặc không tùy workload.
    ```
4.  **Hành động:**
    ```sql
    DROP INDEX idx_sal_emp ON nth_salaries;
    -- DROP INDEX idx_emp_fname ON nth_employees; (Nếu chắc chắn không dùng)
    -- Giữ lại idx_emp_hire nếu workload có sử dụng.
    ```

---

## Chương 5: Khai thác Data Dictionary và Metadata

### Bài tập 5.1: Scripting với INFORMATION_SCHEMA

**Phân tích:** `INFORMATION_SCHEMA` cung cấp metadata về cấu trúc CSDL, hữu ích cho việc tự động hóa hoặc kiểm tra.

**Các bước & Lời giải:**

1.  **So sánh:** Lý thuyết: Query `INFORMATION_SCHEMA.TABLES` trong MySQL 8 nhanh hơn vì nó truy vấn trực tiếp các bảng Data Dictionary (InnoDB) đã được cache, thay vì phải tạo bảng tạm hoặc quét file như phiên bản cũ.
2.  **Scripting Comments:**
    ```sql
    SELECT
        CONCAT(
            'ALTER TABLE `', TABLE_SCHEMA, '`.`', TABLE_NAME,
            '` MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE,
            IF(IS_NULLABLE = 'NO', ' NOT NULL', ''),
            IF(COLUMN_DEFAULT IS NULL, '', CONCAT(' DEFAULT ', QUOTE(COLUMN_DEFAULT))), -- Cần xử lý kỹ hơn cho các default khác nhau
            ' COMMENT \'Cột này chứa ngày...\';' -- Comment mẫu
        ) AS alter_statement
    FROM
        INFORMATION_SCHEMA.COLUMNS
    WHERE
        TABLE_SCHEMA = 'your_db_name' -- Thay bằng tên DB của bạn
        AND DATA_TYPE = 'date'
        AND TABLE_NAME LIKE 'nth_%';
    ```
    *(Lưu ý: Lệnh ALTER thực tế cần cẩn thận với DEFAULT values và các thuộc tính khác)*.
3.  **Liệt kê Foreign Keys:**
    ```sql
    SELECT
        rc.CONSTRAINT_NAME,
        rc.TABLE_NAME AS referencing_table,
        kcu.COLUMN_NAME AS referencing_column,
        rc.REFERENCED_TABLE_NAME AS referenced_table,
        kcu.REFERENCED_COLUMN_NAME AS referenced_column,
        rc.UPDATE_RULE,
        rc.DELETE_RULE
    FROM
        INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
    JOIN
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA AND rc.TABLE_NAME = kcu.TABLE_NAME
    WHERE
        rc.CONSTRAINT_SCHEMA = 'your_db_name' -- Thay bằng tên DB của bạn
        AND rc.TABLE_NAME LIKE 'nth_%';
    ```

### Bài tập 5.2: Kiểm tra Cấu trúc Partition

**Phân tích:** `INFORMATION_SCHEMA.PARTITIONS` chứa thông tin chi tiết về các partition đã tạo.

**Các bước & Lời giải:**

```sql
SELECT
    PARTITION_NAME,
    PARTITION_METHOD,
    PARTITION_EXPRESSION,
    PARTITION_DESCRIPTION, -- Giá trị VALUES LESS THAN
    TABLE_ROWS AS estimated_rows
FROM
    INFORMATION_SCHEMA.PARTITIONS
WHERE
    TABLE_SCHEMA = 'your_db_name' -- Thay bằng tên DB
    AND TABLE_NAME = 'nth_employees' -- Hoặc nth_salaries
ORDER BY PARTITION_ORDINAL_POSITION;

-- Output sẽ liệt kê chi tiết từng partition, giúp xác minh cấu trúc phân hoạch.
```
# Lời giải Bài tập Thực hành: Tối ưu và Quản lý CSDL Nhân viên (Advanced MySQL 8) - Từ Chương 6

## Lưu ý chung:
* Các lệnh SQL dưới đây giả định bạn đang làm việc với schema chứa các bảng `nth_*` đã được nạp dữ liệu (khoảng 10 triệu bản ghi nhân viên).
* Kết quả `EXPLAIN`, thời gian thực thi, và các giá trị `STATUS` có thể thay đổi tùy thuộc vào cấu hình server, phiên bản MySQL, và khối lượng dữ liệu chính xác. Điều quan trọng là hiểu *quy trình* phân tích và tối ưu.
* Tham chiếu đến PDF là ước tính dựa trên nội dung các chương trong file "Advanced MySQL 8.pdf".
* **Yêu cầu:** Đã tạo CSDL và chạy `CALL generate_fake_data();`

---

## Chương 6: Tuning Cấu hình Server cho Workload Lớn

### Bài tập 6.1: Tuning InnoDB Buffer Pool Thực tế

**Phân tích:** Buffer pool là yếu tố then chốt cho hiệu năng InnoDB. Kích thước cần đủ lớn để chứa working set (dữ liệu & index thường dùng). Theo dõi hit rate và wait_free để đánh giá. (Tham khảo PDF: Ch 6, tr. 123, tr. 127-128).

**Các bước & Lời giải:**

1.  **Ước tính Kích thước:**
    ```sql
    SELECT
        ROUND(SUM(data_length + index_length) / 1024 / 1024 / 1024, 2) AS total_size_gb
    FROM
        INFORMATION_SCHEMA.TABLES
    WHERE
        TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'nth_%';
        -- Giả sử kết quả là 60 GB.
    ```
2.  **Thiết lập:** Nếu RAM server là 128GB, có thể thử `128 * 0.75 = 96GB`. Hoặc nếu data+index là 60GB, thử `60 * 1.2 = 72GB`. Chọn `72G`.
    ```sql
    -- Cần quyền SUPER hoặc tương đương
    SET GLOBAL innodb_buffer_pool_size = 72 * 1024 * 1024 * 1024; -- 72GB
    -- Lưu ý: Thay đổi này chỉ có hiệu lực cho các kết nối mới.
    -- Để thay đổi vĩnh viễn, cần sửa file my.cnf và khởi động lại MySQL.
    -- Nếu server có nhiều CPU cores (ví dụ > 8), cân nhắc đặt instances:
    -- SET GLOBAL innodb_buffer_pool_instances = 16; -- (Giá trị ví dụ)
    ```
3.  **Warm-up:** Chạy các query phân tích phức tạp (ví dụ từ Ch4) nhiều lần để nạp dữ liệu và index vào buffer pool.
4.  **Giám sát:** Chạy sau một thời gian workload:
    ```sql
    SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_wait_free';
    SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_read%';
    ```
    * **Tính Hit Rate:** Lấy giá trị `Innodb_buffer_pool_read_requests` và `Innodb_buffer_pool_reads`. Hit Rate = `(read_requests - reads) / read_requests * 100%`. Mong muốn > 99%.
    * **Kiểm tra Wait Free:** `Innodb_buffer_pool_wait_free` nên gần 0.
5.  **Đánh giá:**
    * Nếu Hit Rate thấp (< 99%) và còn RAM trống -> Cân nhắc tăng `innodb_buffer_pool_size`.
    * Nếu `Innodb_buffer_pool_wait_free` > 0 thường xuyên -> Buffer pool đang bị tranh chấp, cân nhắc tăng `innodb_buffer_pool_instances` (nếu chưa làm hoặc giá trị còn thấp).

### Bài tập 6.2: Ảnh hưởng của Sort Buffer

**Phân tích:** So sánh sort trong RAM (nhanh) và sort dùng đĩa (chậm) bằng cách thay đổi `sort_buffer_size` cho session và theo dõi `Sort_merge_passes`. (Tham khảo PDF: Ch 6, tr. 133).

**Các bước & Lời giải:**

1.  **Đặt thấp:**
    ```sql
    SET SESSION sort_buffer_size = 262144; -- 256KB
    ```
2.  **Chạy Query Sort Lớn & Kiểm tra:**
    ```sql
    FLUSH STATUS; -- Reset session status counters
    -- Query sort lượng lớn dữ liệu không có index hỗ trợ hoàn hảo
    SELECT SQL_NO_CACHE emp_no FROM nth_employees ORDER BY birth_date LIMIT 50000;
    -- Ghi lại thời gian thực thi (ước tính)
    SHOW SESSION STATUS LIKE 'Sort_merge_passes';
    -- Ghi lại giá trị Sort_merge_passes (kỳ vọng > 0, ví dụ: 5, 10, ...)
    ```
3.  **Đặt cao:**
    ```sql
    SET SESSION sort_buffer_size = 268435456; -- 256MB
    ```
4.  **Chạy lại Query & So sánh:**
    ```sql
    FLUSH STATUS;
    SELECT SQL_NO_CACHE emp_no FROM nth_employees ORDER BY birth_date LIMIT 50000;
    -- Ghi lại thời gian thực thi (kỳ vọng thấp hơn đáng kể)
    SHOW SESSION STATUS LIKE 'Sort_merge_passes';
    -- Ghi lại giá trị Sort_merge_passes (kỳ vọng = 0)
    ```
    * **Kết luận:** Tăng `sort_buffer_size` đủ lớn cho phép MySQL thực hiện sort hoàn toàn trong bộ nhớ, loại bỏ việc ghi/đọc file tạm trên đĩa (`Sort_merge_passes = 0`), do đó tăng tốc độ đáng kể. Tuy nhiên, đặt `sort_buffer_size` ở mức GLOBAL quá cao rất nguy hiểm vì nó được cấp phát *cho mỗi thread* cần sort, có thể nhanh chóng làm cạn kiệt bộ nhớ server nếu nhiều session cùng sort dữ liệu lớn. Chỉ nên tăng giá trị này cho các session cụ thể hoặc đặt giá trị global một cách thận trọng.

### Bài tập 6.3: I/O Tuning

**Phân tích:** Điều chỉnh `innodb_io_capacity` và các thread I/O để phù hợp với tốc độ của hệ thống lưu trữ (SSD, NVMe). (Tham khảo PDF: Ch 6, tr. 125-126).

**Các bước & Lời giải:**

1.  **Kiểm tra:**
    ```sql
    SHOW VARIABLES LIKE 'innodb_io_capacity%';
    SHOW VARIABLES LIKE '%_io_threads';
    ```
2.  **Thiết lập cho NVMe:** Giả sử ổ NVMe có khả năng xử lý > 10,000 IOPS.
    ```sql
    -- Đặt IO capacity cao hơn nhiều so với mặc định (200)
    SET GLOBAL innodb_io_capacity = 10000;
    -- Đặt mức tối đa cao hơn nữa để cho phép burst
    SET GLOBAL innodb_io_capacity_max = 20000;
    ```
    *(Lưu ý: Cần có quyền SUPER. Thay đổi GLOBAL chỉ ảnh hưởng kết nối mới hoặc cần reload cấu hình)*.
3.  **I/O Threads:** Trên server có 32 core CPU và ổ NVMe mạnh:
    * **Lợi ích:** Tăng `innodb_read_io_threads` và `innodb_write_io_threads` (ví dụ từ 4 lên 16 hoặc 32) cho phép InnoDB xử lý đồng thời nhiều yêu cầu đọc/ghi nền hơn (ví dụ: đọc trước dữ liệu vào buffer pool, ghi các trang bẩn ra đĩa, merge dữ liệu từ change buffer). Điều này giúp giảm tắc nghẽn I/O, đặc biệt khi có nhiều hoạt động ghi hoặc khi cần đọc nhiều dữ liệu không có sẵn trong buffer pool.
    * **Yêu cầu:** Cần sửa đổi file cấu hình `my.cnf` và khởi động lại MySQL Server để các thay đổi về số lượng thread có hiệu lực.
    ```ini
    # Ví dụ trong my.cnf
    [mysqld]
    # ... các cấu hình khác ...
    innodb_read_io_threads=16
    innodb_write_io_threads=16
    ```

---

## Chương 7: Group Replication trong Môi trường Dữ liệu Lớn (Lý thuyết/Mô tả)

### Bài tập 7.1: Đánh giá Tính sẵn sàng cho GR

**Phân tích:** Group Replication (GR) yêu cầu nghiêm ngặt về schema: mọi bảng phải dùng InnoDB và có Primary Key (hoặc non-null unique key làm "primary key ảo"). (Tham khảo PDF: Ch 7, tr. 156).

**Lời giải:**
* **Schema Check:**
    * `SHOW CREATE TABLE <table_name>;` cho tất cả bảng `nth_*`.
    * **Engine:** Script `fake_data_fixed.sql` tạo bảng với engine mặc định. Nếu MySQL 8 là mặc định InnoDB -> OK. Nếu không, cần đảm bảo tất cả bảng là InnoDB.
    * **Primary Key:** Script đã định nghĩa `PRIMARY KEY` cho tất cả các bảng (`dept_id`, `emp_id`, `dept_emp_id`, `dept_manager_id`, `title_id`, `salary_id`). -> OK.
* **Kết luận:** Schema `nth_*` như trong file SQL **đã sẵn sàng** cho Group Replication.
* **Thêm PK vào Bảng Lớn (Nếu thiếu):** Lệnh `ALTER TABLE your_large_table ADD PRIMARY KEY (id_column);` trên bảng 10 triệu dòng là một thao tác **metadata lock** và **rebuild table**, sẽ khóa ghi (và có thể cả đọc) trong thời gian rất dài (hàng giờ hoặc hơn). Điều này gây downtime không chấp nhận được. **Giải pháp:**
    * **Online DDL Tools:** Sử dụng `pt-online-schema-change` (Percona Toolkit) hoặc `gh-ost` (GitHub). Các công cụ này tạo bản sao của bảng, áp dụng thay đổi trên bản sao, đồng bộ dữ liệu thay đổi từ bảng gốc sang bản sao bằng trigger, sau đó đổi tên bảng gốc và bản sao một cách nhanh chóng.
    * **MySQL Online DDL:** Một số thao tác `ALTER TABLE` trong MySQL 8 hỗ trợ `ALGORITHM=INPLACE, LOCK=NONE` (tùy thuộc vào thay đổi cụ thể), nhưng việc thêm PK thường vẫn yêu cầu rebuild và khóa ở mức độ nào đó.
    * **Cửa sổ bảo trì:** Thực hiện trong thời gian bảo trì dài đã được lên kế hoạch.

### Bài tập 7.2: Kịch bản Xung đột và Giải quyết

**Phân tích:** Multi-Primary cho phép ghi đồng thời trên nhiều node, dẫn đến nguy cơ xung đột nếu sửa cùng dữ liệu logic. GR dùng cơ chế chứng thực (certification) dựa trên writeset (tập các khóa chính bị thay đổi) và quy tắc "first committer wins". (Tham khảo PDF: Ch 7, tr. 153-154).

**Lời giải:**
* **Kịch bản Xung đột (Cập nhật chức vụ và lương):**
    1.  **Thời điểm T0:** Nhân viên `emp_id=123` có chức vụ 'Staff', lương $5000.
    2.  **Node1 (T1):** User A chạy transaction `TXN_A` để thăng chức `emp_id=123` lên 'Senior Staff', đồng thời tăng lương lên $6000. Bao gồm:
        * `UPDATE nth_titles SET to_date = CURDATE() WHERE emp_id=123 AND to_date='9999-01-01';`
        * `INSERT INTO nth_titles (emp_id, title, from_date, to_date) VALUES (123, 'Senior Staff', CURDATE(), '9999-01-01');`
        * `UPDATE nth_salaries SET to_date = CURDATE() WHERE emp_id=123 AND to_date='9999-01-01';`
        * `INSERT INTO nth_salaries (emp_id, salary, from_date, to_date) VALUES (123, 6000, CURDATE(), '9999-01-01');`
        * User A `COMMIT TXN_A`.
    3.  **Node2 (T1+delta):** Gần như đồng thời, User B chạy transaction `TXN_B` chỉ để tăng lương `emp_id=123` lên $5500 (ví dụ: thưởng đột xuất). Bao gồm:
        * `UPDATE nth_salaries SET to_date = CURDATE() WHERE emp_id=123 AND to_date='9999-01-01';`
        * `INSERT INTO nth_salaries (emp_id, salary, from_date, to_date) VALUES (123, 5500, CURDATE(), '9999-01-01');`
        * User B `COMMIT TXN_B`.
    4.  **Xử lý GR:**
        * Cả `TXN_A` và `TXN_B` đều thay đổi các dòng trong `nth_titles` và/hoặc `nth_salaries` có cùng `emp_id=123`.
        * **Writeset:** Writeset của `TXN_A` bao gồm PK của dòng `nth_titles` cũ và PK của dòng `nth_salaries` cũ. Writeset của `TXN_B` bao gồm PK của dòng `nth_salaries` cũ.
        * **Certification:** Giả sử `TXN_A` đến certifier trước. Nó được chấp thuận vì chưa có xung đột. Khi `TXN_B` đến, certifier thấy rằng PK của dòng `nth_salaries` cũ trong writeset của `TXN_B` đã bị thay đổi bởi `TXN_A` (đã commit hoặc đang chờ commit). -> **Xung đột được phát hiện**.
        * **Kết quả:** `TXN_A` commit thành công trên Node1 và được réplice. `TXN_B` bị rollback trên Node2, User B nhận lỗi.
* **Hậu quả & Giảm thiểu:**
    * **Hậu quả:** Thao tác tăng lương $5500 của User B thất bại. Dữ liệu cuối cùng là chức vụ 'Senior Staff', lương $6000.
    * **Giảm thiểu:**
        * **Single-Primary:** Đảm bảo mọi thay đổi cho cùng `emp_id` đi qua một node duy nhất, loại bỏ xung đột GR.
        * **Định tuyến ứng dụng (Multi-Primary):** Cố gắng gửi tất cả các request liên quan đến cùng `emp_id` tới cùng một node trong một khoảng thời gian ngắn.
        * **Transaction Ngắn:** Chia nhỏ các quy trình nghiệp vụ phức tạp thành các transaction nhỏ hơn, độc lập hơn nếu có thể.
        * **Khóa Ứng dụng (Application-level Locking):** Trước khi thực hiện chuỗi cập nhật cho một nhân viên, ứng dụng có thể yêu cầu một khóa logic (ví dụ: trong một bảng `employee_locks`) để ngăn các tiến trình khác thao tác cùng lúc.
        * **Retry Logic:** Ứng dụng phải có cơ chế bắt lỗi commit và thử lại (retry) transaction bị rollback do xung đột.

### Bài tập 7.3: Giám sát Lag và Flow Control

**Phân tích:** Flow control giúp cluster hoạt động ổn định khi có node chậm, dựa trên việc giám sát hàng đợi của applier và certifier. (Tham khảo PDF: Ch 7, tr. 165-166, tr. 174-176).

**Lời giải:**
* **Phát hiện Lag:**
    * Sử dụng query sau trên bất kỳ node nào:
      ```sql
      SELECT
          MEMBER_HOST,
          COUNT_TRANSACTIONS_IN_QUEUE AS certifier_queue,
          COUNT_TRANSACTIONS_REMOTE_APPLIED AS applied_count,
          COUNT_TRANSACTIONS_REMOTE_IN_APPLIER_QUEUE AS applier_queue
      FROM performance_schema.replication_group_member_stats;
      ```
    * **Dấu hiệu lag:** Nếu `node3` có `applied_count` thấp hơn đáng kể so với các node khác, và/hoặc `applier_queue` của `node3` cao và đang tăng -> `node3` bị lag ở tầng applier (không áp dụng kịp transaction đã được chứng thực). Nếu `certifier_queue` cao trên TẤT CẢ các node -> có thể certifier đang chờ `node3` hoặc có tắc nghẽn ở tầng chứng thực/mạng.
* **Flow Control:**
    * **Kích hoạt:** Khi `applier_queue` trên `node3` vượt `group_replication_flow_control_applier_threshold` (mặc định 25000) HOẶC `certifier_queue` trên bất kỳ node nào vượt `group_replication_flow_control_certifier_threshold` (mặc định 25000).
    * **Hoạt động (Mode=QUOTA):** Các node **ghi** (writers) sẽ bị hạn chế số lượng transaction chúng được phép commit trong mỗi chu kỳ (mặc định 1 giây). Giới hạn (quota) này được tính toán dựa trên tốc độ xử lý của node chậm nhất (hoặc các yếu tố khác như % member quota). Khi node ghi đạt đến quota, nó sẽ tạm dừng commit transaction mới cho đến khi chu kỳ tiếp theo bắt đầu hoặc cho đến khi node chậm bắt kịp và các hàng đợi giảm xuống dưới ngưỡng `group_replication_flow_control_release_percent` (mặc định 50%). Mục đích là để giảm áp lực lên node chậm và cho phép nó "đuổi kịp" cluster.

---

## Chương 8: InnoDB Cluster và Quản lý Thực tế

### Bài tập 8.1: Tạo và Kiểm tra Cluster (Sandbox)

**Phân tích:** AdminAPI trong MySQL Shell (`dba` object) đơn giản hóa việc tạo và quản lý InnoDB Cluster. (Tham khảo PDF: Ch 8, tr. 181, tr. 186-189).

**Các bước & Lời giải:**

1.  **Deploy Instances:** (Chạy từ command line hoặc terminal)
    ```bash
    # Đảm bảo đã cài đặt MySQL Server để mysqlsh tìm thấy mysqld
    mysqlsh -- dba deploySandboxInstance 4001 --password='S@ndb0xPass!'
    mysqlsh -- dba deploySandboxInstance 4002 --password='S@ndb0xPass!'
    mysqlsh -- dba deploySandboxInstance 4003 --password='S@ndb0xPass!'
    ```
2.  **Kết nối Shell:**
    ```bash
    mysqlsh --uri root@localhost:4001 --password='S@ndb0xPass!'
    ```
3.  **Tạo và Quản lý Cluster:** (Trong phiên mysqlsh)
    ```javascript
    // (Đã kết nối tới localhost:4001)

    // Tạo cluster với instance hiện tại làm seed
    var cluster = dba.createCluster('employeeCluster');
    print("--- Cluster status after creation ---");
    print(cluster.status());

    // Thêm instance thứ hai (cần cung cấp mật khẩu)
    // recoveryMethod: 'clone' thường được dùng cho sandbox để sao chép dữ liệu nhanh
    print("--- Adding instance 4002 ---");
    cluster.addInstance('root@localhost:4002', {password: 'S@ndb0xPass!', recoveryMethod: 'clone'});
    print("--- Cluster status after adding 4002 ---");
    print(cluster.status());

    // Thêm instance thứ ba
    print("--- Adding instance 4003 ---");
    cluster.addInstance('root@localhost:4003', {password: 'S@ndb0xPass!', recoveryMethod: 'clone'});
    print("--- Final Cluster status ---");
    print(cluster.status());
    ```
4.  **Phân tích `cluster.status()` cuối cùng:**
    * `clusterName`: "employeeCluster"
    * `status`: "OK" (nếu tất cả thành công)
    * `statusText`: "Cluster is ONLINE and can tolerate up to ONE failure." (Vì có 3 thành viên)
    * `topology`: Sẽ liệt kê 3 thành viên (localhost:4001, localhost:4002, localhost:4003). Một thành viên sẽ có `role: "HA"`, `mode: "R/W"` (là PRIMARY). Hai thành viên còn lại sẽ có `role: "HA"`, `mode: "R/O"` (là SECONDARY, vì sandbox mặc định tạo Single-Primary).

### Bài tập 8.2: Bootstrap Router

**Phân tích:** Router cần kết nối tới cluster để lấy metadata (danh sách thành viên, trạng thái...) và bắt đầu định tuyến. Lệnh `bootstrap` tự động hóa việc này. (Tham khảo PDF: Ch 8, tr. 197-198).

**Các bước & Lời giải:**

1.  **Bootstrap Router:** (Chạy từ command line trên máy cài Router)
    ```bash
    # Tạo thư mục cho cấu hình router nếu chưa có
    # mkdir /tmp/myrouter

    # Chạy bootstrap, kết nối tới node 4001 của cluster
    mysqlrouter --bootstrap root@localhost:4001 --directory=/tmp/myrouter --user=mysqlrouter --conf-use-sockets --password='S@ndb0xPass!'
    ```
    * `--user=mysqlrouter`: Chỉ định user hệ điều hành để chạy tiến trình router.
    * `--directory`: Nơi lưu file cấu hình (`mysqlrouter.conf`) và log.
    * `--conf-use-sockets`: Tùy chọn, nếu muốn router giao tiếp với mysqld trên cùng máy qua socket thay vì TCP/IP.
    * Cần đảm bảo user `root@localhost` (hoặc user admin khác được chỉ định) có quyền cần thiết để router lấy metadata.
2.  **Cổng kết nối:** Sau khi bootstrap và khởi động router (`/tmp/myrouter/start.sh`), ứng dụng sẽ kết nối tới các cổng **của Router** (chứ không phải cổng MySQL trực tiếp):
    * **Read/Write (RW) Port:** Mặc định là `6446` (cho classic protocol). Ứng dụng kết nối tới cổng này sẽ được Router tự động định tuyến tới node PRIMARY hiện tại của cluster.
    * **Read-Only (RO) Port:** Mặc định là `6447` (cho classic protocol). Ứng dụng kết nối tới cổng này sẽ được Router định tuyến (thường là round-robin) tới các node SECONDARY khả dụng.
    * (Các cổng tương ứng cho X Protocol là 64460 và 64470).

### Bài tập 8.3: Quản lý Cluster và Storage Engines

**Phân tích:** AdminAPI cho phép quản lý thành viên. Hiểu yêu cầu về storage engine là nền tảng. (Tham khảo PDF: Ch 8, tr. 199-207).

**Các bước & Lời giải:**

1.  **Xóa Instance:** (Trong mysqlsh, đã kết nối cluster `employeeCluster`)
    ```javascript
    // Giả sử 'cluster' là biến object cluster
    print("--- Removing instance 4003 ---");
    var result = cluster.removeInstance('root@localhost:4003'); // Không cần force nếu node online
    print(result); // Xem kết quả
    print("--- Cluster status after removing 4003 ---");
    print(cluster.status());
    // statusText sẽ đổi thành: "Cluster is ONLINE but is not fault-tolerant (2 members)."
    ```
2.  **Kiểm tra Instance:**
    ```javascript
    print("--- Checking instance 4002 config ---");
    var configStatus = cluster.checkInstanceConfiguration('root@localhost:4002');
    print(configStatus);
    // Output nên là { "status": "ok" }
    ```
3.  **Storage Engines khác:**
    * **ARCHIVE:** Dùng để lưu trữ dữ liệu lịch sử, log ít truy cập. Ưu điểm: Nén dữ liệu tốt, tiết kiệm dung lượng. Chỉ hỗ trợ INSERT, SELECT. Không transaction, không index (trừ cột auto_increment).
    * **MEMORY (HEAP):** Dùng cho bảng tạm, cache dữ liệu cần truy cập cực nhanh. Ưu điểm: Tốc độ rất cao do lưu trong RAM. Nhược điểm: Mất dữ liệu khi restart, giới hạn bởi RAM, table-level locking.
    * **CSV:** Lưu dữ liệu dưới dạng file CSV. Ưu điểm: Dễ dàng trao đổi dữ liệu với các ứng dụng khác (spreadsheet). Nhược điểm: Không index, không transaction, yêu cầu NOT NULL.
    * **Không phù hợp Cluster:** Các engine này thiếu các tính năng cốt lõi mà Group Replication/InnoDB Cluster dựa vào để đảm bảo tính nhất quán, đồng bộ và khả năng phục hồi:
        * **Thiếu Transactions (ACID):** Không thể đảm bảo các thay đổi được áp dụng một cách nguyên tử trên tất cả các node hoặc rollback khi có xung đột.
        * **Table-level Locking (thay vì Row-level):** Giảm nghiêm trọng khả năng ghi đồng thời, đặc biệt trong Multi-Primary mode.
        * **Thiếu độ bền (Durability):** MEMORY engine mất dữ liệu khi restart.
        * **Khó xử lý xung đột:** Cơ chế certification của GR dựa trên writeset của InnoDB rows.

---

## Chương 11: Áp dụng Tips & Techniques vào Dự án

### Bài tập 11.1: Tối ưu Query Báo cáo Tổng hợp

**Phân tích:** Áp dụng tip "Chỉ SELECT cột cần thiết" và đảm bảo index phù hợp cho query JOIN và WHERE. (Tham khảo PDF: Ch 11, tr. 247-250).

**Các bước & Lời giải:**

1.  **Query:** "Lấy tổng số nhân viên và mức lương trung bình hiện tại cho mỗi phòng ban, chỉ tính những nhân viên được thuê trong 5 năm gần nhất."
    ```sql
    -- Đặt biến cho dễ đọc
    SET @five_years_ago = DATE_SUB(CURDATE(), INTERVAL 5 YEAR);

    -- Query tối ưu (chỉ lấy cột cần thiết)
    SELECT
        d.dept_name,                 -- Cần cho GROUP BY và hiển thị
        COUNT(DISTINCT e.emp_id) AS num_employees, -- Cần e.emp_id để đếm
        AVG(s.salary) AS avg_current_salary -- Cần s.salary để tính AVG
    FROM
        nth_departments d
    -- JOIN với dept_emp để lấy emp_id theo dept_id, lọc theo to_date
    JOIN nth_dept_emp de ON d.dept_id = de.dept_id AND de.to_date = '9999-01-01'
    -- JOIN với employees để lấy hire_date và lọc, lấy emp_id
    JOIN nth_employees e ON de.emp_id = e.emp_id
    -- JOIN với salaries để lấy salary, lọc theo to_date
    JOIN nth_salaries s ON e.emp_id = s.emp_id AND s.to_date = '9999-01-01'
    WHERE
        e.hire_date >= @five_years_ago -- Điều kiện lọc chính
    GROUP BY
        d.dept_name -- Nhóm theo tên phòng ban
    ORDER BY
        d.dept_name; -- Sắp xếp kết quả
    ```
2.  **Tối ưu:**
    * **`EXPLAIN ANALYZE`:** Chạy query trên và phân tích.
    * **Index:** Đảm bảo các index sau tồn tại (tham khảo bài 4.2):
        * `nth_employees(hire_date)`
        * `nth_dept_emp(to_date, dept_id, emp_id)` (Hoặc `(dept_id, to_date, emp_id)`)
        * `nth_salaries(to_date, emp_id, salary)`
        * `nth_departments(dept_id)` (PK)
    * **Kiểm tra lại:** Chạy lại `EXPLAIN ANALYZE`. So sánh thời gian thực thi so với việc dùng `SELECT *` (nếu có thể thử). Việc chỉ lấy các cột cần thiết giúp giảm lượng dữ liệu phải đọc từ đĩa/buffer pool và truyền qua các bước JOIN/GROUP BY.

### Bài tập 11.2: Xử lý Xóa Dữ liệu Lớn An Toàn

**Phân tích:** Chia nhỏ `DELETE` để tránh khóa bảng lâu và giảm thiểu tác động lên hệ thống. Có FK `ON DELETE CASCADE` nên chỉ cần xóa từ bảng chính `nth_employees`. (Tham khảo PDF: Ch 11, tr. 254-256).

**Các bước & Lời giải (SQL Logic với Loop):**

```sql
DELIMITER //

DROP PROCEDURE IF EXISTS SafeDeleteOldEmployees //
CREATE PROCEDURE SafeDeleteOldEmployees(IN cutoff_date DATE, IN batch_size INT)
BEGIN
    DECLARE total_deleted BIGINT DEFAULT 0;
    DECLARE batch_deleted INT DEFAULT 0;
    DECLARE done INT DEFAULT 0;
    DECLARE start_time DATETIME;
    DECLARE end_time DATETIME;

    -- Tắt tạm thời binlog nếu không cần replicate thao tác xóa này (cẩn thận!)
    -- SET sql_log_bin = 0;

    SELECT CONCAT('Starting deletion of employees hired before ', cutoff_date) AS log_message;

    REPEAT
        SET start_time = NOW();
        SET batch_deleted = 0;

        -- Bắt đầu transaction cho batch
        START TRANSACTION;

        -- Xóa một batch nhân viên (CASCADE sẽ xử lý FKs)
        DELETE FROM nth_employees
        WHERE hire_date < cutoff_date
        LIMIT batch_size;

        -- Lấy số dòng đã xóa trong batch
        SET batch_deleted = ROW_COUNT();

        -- Commit transaction
        COMMIT;

        SET total_deleted = total_deleted + batch_deleted;
        SET end_time = NOW();

        -- Ghi log hoặc hiển thị tiến độ
        SELECT CONCAT(
            'Batch completed at ', end_time,
            '. Deleted: ', batch_deleted,
            '. Total deleted: ', total_deleted,
            '. Time taken: ', TIMEDIFF(end_time, start_time)
        ) AS log_message;

        -- Nếu không xóa được dòng nào nữa thì dừng
        IF batch_deleted = 0 THEN
            SET done = 1;
        END IF;

        -- Tạm dừng ngắn giữa các batch để giảm tải
        IF NOT done THEN
            DO SLEEP(0.5); -- Tạm dừng 0.5 giây
        END IF;

    UNTIL done END REPEAT;

    -- Bật lại binlog nếu đã tắt
    -- SET sql_log_bin = 1;

    SELECT CONCAT('Finished deletion. Total employees deleted: ', total_deleted) AS log_message;

END //

DELIMITER ;

-- Cách sử dụng: Xóa nhân viên thuê trước 1995, mỗi lần 500 người
-- CALL SafeDeleteOldEmployees('1995-01-01', 500);