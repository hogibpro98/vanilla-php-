# Dự án Thực hành: Tối ưu và Quản lý CSDL Nhân viên 10 Triệu Bản ghi (Advanced MySQL 8)

## Bối cảnh
Bạn đang quản lý một cơ sở dữ liệu MySQL 8 chứa thông tin chi tiết về nhân viên, phòng ban, chức vụ và lương cho một công ty lớn. Cơ sở dữ liệu này (theo schema `fake_data_fixed.sql`) đã được nạp 10 triệu bản ghi nhân viên cùng các dữ liệu liên quan. Nhiệm vụ của bạn là áp dụng các kỹ thuật nâng cao để đảm bảo hiệu năng, tính sẵn sàng và khả năng quản lý của hệ thống này.

**Yêu cầu:**
* Đã tạo CSDL và chạy `CALL generate_fake_data();` (điều chỉnh `total_records` trong procedure nếu cần để đạt 10 triệu).
* Sử dụng các công cụ như `EXPLAIN`, `EXPLAIN ANALYZE` (nếu có), `SHOW STATUS`, `SHOW VARIABLES`, `performance_schema`, `sys` schema, và có thể là các công cụ đo thời gian thực thi query (ví dụ: `mysqlslap` hoặc scripting) để đánh giá hiệu năng.
* Ghi lại kết quả (thời gian thực thi, output `EXPLAIN`, giá trị status...) trước và sau khi thực hiện tối ưu hóa.

---

## Chương 2: Quản lý Truy cập và Tính năng Mới trên Dữ liệu Lớn

### Bài tập 2.1: Phân quyền Chi tiết với Roles
1.  **Thiết kế Roles:**
    * `DeptViewer`: Chỉ được xem (`SELECT`) thông tin nhân viên (`nth_employees`) và phòng ban (`nth_departments`, `nth_dept_emp`) thuộc một phòng ban cụ thể (ví dụ: chỉ phòng 'Engineering'). *(Yêu cầu tạo VIEW hoặc Stored Procedure để giới hạn dữ liệu)*.
    * `SalaryAdmin`: Có quyền `SELECT`, `INSERT`, `UPDATE` trên `nth_salaries` nhưng **không** được xem `nth_employees`.
    * `HRRecruiter`: Có quyền `SELECT`, `INSERT` trên `nth_employees` và `nth_dept_emp`.
2.  **Triển khai:** Tạo các roles, cấp quyền (sử dụng VIEW/Procedure nếu cần cho `DeptViewer`), tạo user mẫu cho mỗi role và gán role.
3.  **Kiểm tra:** Đăng nhập với từng user và xác thực quyền truy cập/hạn chế đúng như thiết kế.

### Bài tập 2.2: Đánh giá Index với Invisible Indexes
1.  **Xác định Index Nghi vấn:** Giả sử bạn nghi ngờ index trên `nth_employees.birth_date` không còn hiệu quả do các query thay đổi.
2.  **Đo lường:** Chạy một tập các query thường dùng lọc theo `birth_date` (ví dụ: tìm nhân viên sinh trong quý 1). Ghi lại thời gian thực thi trung bình và kết quả `EXPLAIN ANALYZE`.
3.  **Thử nghiệm:** Đặt index trên `birth_date` thành `INVISIBLE`. Chạy lại tập query ở bước 2. So sánh thời gian thực thi và kết quả `EXPLAIN ANALYZE`.
4.  **Quyết định:** Dựa trên kết quả, quyết định giữ index (đặt lại `VISIBLE`) hay xóa bỏ hoàn toàn. Giải thích quyết định của bạn.

### Bài tập 2.3: Resource Groups cho Báo cáo Nặng
1.  **Xác định Query Nặng:** Viết một query phức tạp để tính tuổi trung bình và thâm niên trung bình (`hire_date`) cho từng phòng ban (`dept_name`). Query này có thể JOIN nhiều bảng và thực hiện tính toán trên 10 triệu nhân viên.
2.  **Đo lường:** Chạy query này với user thông thường và ghi lại thời gian, quan sát mức sử dụng CPU của tiến trình `mysqld`.
3.  **Áp dụng Giới hạn:**
    * Tạo một Resource Group `low_priority_reporting` với giới hạn VCPU thấp (ví dụ: chỉ 1 vCPU nếu server có nhiều hơn) và `THREAD_PRIORITY` thấp (ví dụ: 15).
    * Tạo một user `reporter` riêng.
    * Chạy lại query nặng ở bước 1 bằng user `reporter`, nhưng trước đó dùng `SET RESOURCE GROUP low_priority_reporting FOR <thread_id>;` (tìm thread_id của session `reporter`).
4.  **So sánh:** Quan sát thời gian thực thi và mức sử dụng CPU. Resource Group có giúp hạn chế tác động của query nặng lên hệ thống không?

---

## Chương 3: Tối ưu Indexing cho Hiệu năng Truy vấn 10 Triệu dòng

### Bài tập 3.1: Phân tích Query Phức tạp với EXPLAIN ANALYZE
1.  **Query Phân tích:** "Liệt kê 50 nhân viên (`emp_no`, tên, chức vụ hiện tại, lương hiện tại) có mức lương hiện tại (`to_date = '9999-01-01'`) cao nhất trong phòng ban 'Sales'."
2.  **Phân tích:** Chạy `EXPLAIN ANALYZE` cho query này.
    * Xác định các bước tốn nhiều thời gian nhất (actual time).
    * Loại JOIN nào đang được sử dụng? Có tối ưu không?
    * Có bước nào trả về số lượng hàng lớn (rows) mà sau đó bị lọc đi nhiều không?
    * Có index nào được sử dụng hiệu quả cho việc lọc (`WHERE`), JOIN, và sắp xếp (`ORDER BY`) không?

### Bài tập 3.2: Thiết kế và Đánh giá Compound Index
1.  **Query Mục tiêu:** "Tìm tất cả các 'Senior Engineer' (`title = 'Senior Engineer'`) đang làm việc (`de.to_date = '9999-01-01'`) trong phòng ban 'Engineering' (`dp.dept_name = 'Engineering'`) và được thuê sau ngày '2000-01-01'." (Yêu cầu JOIN `nth_employees`, `nth_titles`, `nth_dept_emp`, `nth_departments`).
2.  **Đo lường Ban đầu:** Chạy query và ghi lại thời gian thực thi. Chạy `EXPLAIN ANALYZE`.
3.  **Thiết kế Index:** Đề xuất một hoặc nhiều compound index trên các bảng liên quan để tối ưu query này. Ưu tiên index có thể "cover" được nhiều phần của query (WHERE, JOIN, ORDER BY nếu có). Giải thích thứ tự cột dựa trên selectivity ước tính (ví dụ: `dept_id` có thể ít giá trị hơn `hire_date`).
4.  **Tạo và Đo lường Lại:** Tạo index bạn đề xuất. Chạy lại query và `EXPLAIN ANALYZE`. So sánh thời gian thực thi và kế hoạch thực thi. Mức độ cải thiện là bao nhiêu?

### Bài tập 3.3: Kiểm tra và Bảo trì Index
1.  **Kiểm tra Kích thước:** Sử dụng `INFORMATION_SCHEMA.STATISTICS` hoặc view `sys.schema_index_statistics` để xem kích thước ước tính của các index trên bảng `nth_employees` và `nth_salaries`. Index nào lớn nhất?
2.  **Phân mảnh Index:** (Lý thuyết) Nếu một index trên bảng lớn bị phân mảnh nặng (fragmented), nó có thể ảnh hưởng đến hiệu năng như thế nào? Lệnh nào có thể dùng để tối ưu lại index (ví dụ: `OPTIMIZE TABLE` hoặc `ALTER TABLE ... ENGINE=InnoDB`)? Việc này có ảnh hưởng gì đến tính sẵn sàng của bảng không?

---

## Chương 4: Kỹ thuật Dữ liệu Nâng cao cho Query Lớn

### Bài tập 4.1: Triển khai và Đánh giá Partitioning
1.  **Partition `nth_employees`:**
    * `ALTER TABLE nth_employees PARTITION BY RANGE (YEAR(hire_date))` với các partition cho mỗi 2 năm (ví dụ: p_before_1990, p1990_1991, p1992_1993,... p_future).
    * **Đo lường Query:** Chạy `SELECT COUNT(*) FROM nth_employees WHERE hire_date BETWEEN 'YYYY-01-01' AND 'YYYY-12-31';` (chọn 1 năm). Ghi thời gian. Chạy `EXPLAIN` để xem partition pruning.
    * **Đo lường Xóa:** *Trước khi partition*, chạy `DELETE FROM nth_employees WHERE YEAR(hire_date) < 1990;` và đo thời gian (có thể chạy trong transaction và rollback nếu không muốn xóa thật). *Sau khi partition*, chạy `ALTER TABLE nth_employees DROP PARTITION p_before_1990;` và đo thời gian. So sánh.
2.  **Partition `nth_salaries`:** Lặp lại quy trình tương tự, partition bảng `nth_salaries` theo `RANGE(YEAR(from_date))`. Đánh giá hiệu quả cho query lọc theo năm và xóa dữ liệu cũ.

### Bài tập 4.2: Tối ưu Query Tính toán Phức tạp
1.  **Query Thách thức:** "Tính mức lương trung bình (`AVG(salary)`) cho mỗi chức vụ (`title`) trong mỗi phòng ban (`dept_name`) cho các nhân viên *hiện đang* làm việc (`de.to_date = '9999-01-01'`, `t.to_date = '9999-01-01'`)." (Yêu cầu JOIN 5 bảng: `nth_salaries`, `nth_employees`, `nth_titles`, `nth_dept_emp`, `nth_departments`).
2.  **Phân tích & Tối ưu:**
    * Chạy `EXPLAIN ANALYZE`. Xác định các điểm nghẽn.
    * Kiểm tra các index hiện có. Có cần thêm/sửa index không? (Ví dụ: compound index trên `nth_dept_emp(dept_id, emp_id, to_date)`, `nth_titles(emp_id, title, to_date)`, `nth_salaries(emp_id, salary, to_date)`?)
    * Có thể viết lại query (ví dụ: dùng subquery thay vì JOIN tất cả cùng lúc) để cải thiện không?
3.  **Đo lường:** Ghi lại thời gian thực thi trước và sau khi tối ưu.

### Bài tập 4.3: Dọn dẹp Index Thực tế
1.  **Tạo Index Thử nghiệm:** Tạo thêm một vài index (có thể trùng lặp hoặc ít dùng) trên các bảng `nth_*`. Ví dụ: `CREATE INDEX idx_emp_fname ON nth_employees(first_name);`, `CREATE INDEX idx_emp_hire ON nth_employees(hire_date);`, `CREATE INDEX idx_sal_emp ON nth_salaries(emp_id);` (có thể trùng với FK).
2.  **Chạy Workload:** Thực hiện các truy vấn đa dạng (từ các bài tập trước) trong một khoảng thời gian.
3.  **Phát hiện:** Sử dụng `sys.schema_redundant_indexes` và `sys.schema_unused_indexes` để xác định các index bạn vừa tạo có bị đánh dấu là dư thừa hoặc không sử dụng không.
4.  **Hành động:** Quyết định và thực hiện lệnh `DROP INDEX` cho các index không cần thiết.

---

## Chương 5: Khai thác Data Dictionary và Metadata

### Bài tập 5.1: Scripting với INFORMATION_SCHEMA
1.  Viết một truy vấn SQL sử dụng `INFORMATION_SCHEMA.COLUMNS` để tạo ra các lệnh `ALTER TABLE ... MODIFY COLUMN ...` nhằm thêm `COMMENT 'Cột này chứa...'` cho tất cả các cột kiểu `DATE` trong các bảng `nth_*`.
2.  Viết một truy vấn SQL sử dụng `INFORMATION_SCHEMA.KEY_COLUMN_USAGE` và `INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS` để liệt kê tất cả các Foreign Key constraints trong schema, bao gồm tên constraint, bảng tham chiếu, cột tham chiếu, bảng được tham chiếu, cột được tham chiếu.

### Bài tập 5.2: Kiểm tra Cấu trúc Partition
* Sau khi đã partition các bảng ở Chương 4, viết truy vấn sử dụng `INFORMATION_SCHEMA.PARTITIONS` để:
    * Liệt kê tất cả các partition của bảng `nth_employees`.
    * Hiển thị tên partition, phương thức partition (`PARTITION_METHOD`), biểu thức partition (`PARTITION_EXPRESSION`), và mô tả giá trị (`PARTITION_DESCRIPTION`) cho từng partition.
    * Ước tính số lượng hàng (`TABLE_ROWS`) trong mỗi partition.

---

## Chương 6: Tuning Cấu hình Server cho Workload Lớn

### Bài tập 6.1: Tuning InnoDB Buffer Pool Thực tế
1.  **Ước tính Kích thước:** Sử dụng truy vấn `INFORMATION_SCHEMA.TABLES` để tính tổng kích thước (`data_length + index_length`) của tất cả các bảng `nth_*`.
2.  **Thiết lập:** Đặt `innodb_buffer_pool_size` bằng khoảng 110-120% tổng kích thước dữ liệu + index (nếu RAM cho phép và server chỉ chạy MySQL). Ghi lại giá trị thiết lập.
3.  **Warm-up:** Chạy các query phân tích phức tạp (từ Ch4) nhiều lần để "làm nóng" buffer pool.
4.  **Giám sát:** Sử dụng `SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_%';` để theo dõi:
    * `Innodb_buffer_pool_wait_free`: Có cao không? (Nếu cao -> pool quá nhỏ hoặc bị tranh chấp).
    * `Innodb_buffer_pool_reads` vs `Innodb_buffer_pool_read_requests`: Tính hit rate.
    * `Innodb_buffer_pool_pages_dirty`: Số lượng trang bẩn.
5.  **Đánh giá:** Dựa vào các chỉ số giám sát, bạn có cần điều chỉnh lại `innodb_buffer_pool_size` hoặc `innodb_buffer_pool_instances` không?

### Bài tập 6.2: Ảnh hưởng của Sort Buffer
1.  **Đặt thấp:** `SET SESSION sort_buffer_size = 262144;` (256KB).
2.  **Chạy Query Sort Lớn:** `SELECT emp_no, first_name, last_name, birth_date FROM nth_employees ORDER BY birth_date DESC LIMIT 10000;` (Không dùng index trên `birth_date`). Ghi lại thời gian thực thi. Kiểm tra `SHOW SESSION STATUS LIKE 'Sort_merge_passes';`.
3.  **Đặt cao:** `SET SESSION sort_buffer_size = 134217728;` (128MB).
4.  **Chạy lại Query:** Chạy lại query ở bước 2. So sánh thời gian và giá trị `Sort_merge_passes`. Việc tăng `sort_buffer_size` có cải thiện hiệu năng sort trên bộ dữ liệu lớn này không? Tại sao không nên đặt `sort_buffer_size` quá cao ở mức GLOBAL?

### Bài tập 6.3: I/O Tuning
1.  Kiểm tra giá trị hiện tại của `innodb_io_capacity` và `innodb_io_capacity_max`.
2.  Giả sử bạn đang sử dụng ổ SSD NVMe tốc độ cao. Các giá trị mặc định của `innodb_io_capacity` (ví dụ: 200) có thể là quá thấp. Bạn sẽ đặt giá trị này là bao nhiêu để tận dụng tối đa I/O của SSD (ví dụ: 2000, 5000, hoặc hơn)?
3.  Việc tăng `innodb_read_io_threads` và `innodb_write_io_threads` lên cao hơn (ví dụ: 16 hoặc 32) có thể mang lại lợi ích gì trên hệ thống có I/O mạnh và nhiều CPU core?

---

## Chương 7: Group Replication trong Môi trường Dữ liệu Lớn (Lý thuyết/Mô tả)

### Bài tập 7.1: Đánh giá Tính sẵn sàng cho GR
* Xem lại schema `nth_*`. Tất cả các bảng đã sẵn sàng cho Group Replication chưa (InnoDB, Primary Key)? Nếu có bảng nào chưa có PK, việc thêm PK vào bảng 10 triệu dòng sẽ ảnh hưởng thế nào đến hoạt động?

### Bài tập 7.2: Kịch bản Xung đột và Giải quyết
* Mô tả chi tiết một kịch bản thực tế có thể xảy ra xung đột ghi (write conflict) trong chế độ Multi-Primary khi hai người dùng trên hai node khác nhau cùng lúc:
    1.  Thăng chức (cập nhật `nth_titles.to_date` cũ, `INSERT` dòng `nth_titles` mới) cho cùng một `emp_id`.
    2.  Tăng lương (cập nhật `nth_salaries.to_date` cũ, `INSERT` dòng `nth_salaries` mới) cho cùng một `emp_id`.
* Group Replication sẽ xử lý xung đột này như thế nào? Hậu quả đối với ứng dụng là gì? Làm thế nào để giảm thiểu xung đột trong thiết kế ứng dụng?

### Bài tập 7.3: Giám sát Lag và Flow Control
* Giả sử `node3` trong group bị chậm phần cứng và bắt đầu lag (xử lý transaction chậm hơn các node khác).
    * Bạn sẽ sử dụng các cột nào trong `performance_schema.replication_group_member_stats` để phát hiện tình trạng lag này (ví dụ: `COUNT_TRANSACTIONS_IN_QUEUE`, `COUNT_TRANSACTIONS_REMOTE_APPLIED` so với các node khác)?
    * Flow control sẽ được kích hoạt khi nào (dựa vào các ngưỡng `*_threshold`)? Khi kích hoạt, nó sẽ làm chậm các node nào và bằng cách nào (quota)?

---

## Chương 8: InnoDB Cluster và Quản lý Thực tế

### Bài tập 8.1: Di chuyển Schema vào Cluster (Mô tả)
* Mô tả chi tiết các bước cần thực hiện để di chuyển CSDL `nth_*` (đang chạy trên một server MySQL độc lập) vào một InnoDB Cluster mới gồm 3 node (`hr-clus-1`, `hr-clus-2`, `hr-clus-3`) sử dụng MySQL Shell AdminAPI, đảm bảo downtime tối thiểu. Các bước bao gồm:
    * Chuẩn bị các node cluster (cài đặt, cấu hình cơ bản).
    * Kiểm tra schema nguồn (PK, engine).
    * Backup/Restore hoặc dùng Replication để đồng bộ dữ liệu ban đầu lên một node cluster.
    * Sử dụng AdminAPI (`createCluster`, `addInstance`) để hình thành cluster.
    * Đồng bộ dữ liệu phát sinh trong quá trình di chuyển.
    * Chuyển đổi ứng dụng sang kết nối qua Router.

### Bài tập 8.2: Kịch bản Failover với Router
1.  Giả sử `hr-clus-1` là PRIMARY hiện tại của `employeeCluster` (Single-Primary mode).
2.  Mô phỏng `hr-clus-1` bị lỗi (ví dụ: `systemctl stop mysqld`).
3.  Điều gì sẽ xảy ra trong cluster? Node nào sẽ được bầu làm PRIMARY mới (giả sử weight bằng nhau)?
4.  MySQL Router sẽ phát hiện sự thay đổi này như thế nào? Các kết nối mới từ ứng dụng đến cổng Read/Write của Router sẽ được định tuyến đến node nào? Các kết nối đang tồn tại tới `hr-clus-1` sẽ bị ảnh hưởng ra sao?

### Bài tập 8.3: Lựa chọn Storage Engine
* Ngoài InnoDB, nếu bạn cần một bảng để lưu trữ tạm thời dữ liệu log rất lớn, chỉ ghi vào và rất hiếm khi đọc, và không yêu cầu transaction, bạn có thể cân nhắc Storage Engine nào khác? Nêu ưu điểm của nó cho trường hợp này. (Gợi ý: ARCHIVE, MyISAM...). Tại sao những engine này không phù hợp cho dữ liệu chính (`nth_employees`, `nth_salaries`...) trong InnoDB Cluster?

---

## Chương 11: Áp dụng Tips & Techniques vào Dự án

### Bài tập 11.1: Tối ưu Query Báo cáo Tổng hợp
1.  **Query:** "Lấy tổng số nhân viên và mức lương trung bình hiện tại cho mỗi phòng ban, chỉ tính những nhân viên được thuê trong 5 năm gần nhất."
2.  **Tối ưu:**
    * Viết query ban đầu (sử dụng JOIN nhiều bảng).
    * Chạy `EXPLAIN ANALYZE`.
    * Áp dụng các kỹ thuật: chỉ `SELECT` các cột cần thiết, đảm bảo có index phù hợp cho JOIN và WHERE (`hire_date`, `dept_id`, `to_date` trên các bảng liên quan), xem xét thứ tự JOIN.
    * Viết lại query (nếu cần) và tạo/điều chỉnh index.
    * Chạy lại `EXPLAIN ANALYZE` và so sánh kết quả.

### Bài tập 11.2: Xử lý Xóa Dữ liệu Lớn An Toàn
1.  **Yêu cầu:** Xóa toàn bộ lịch sử phòng ban (`nth_dept_emp`) và lịch sử chức vụ (`nth_titles`) của những nhân viên (`emp_id`) đã nghỉ việc (`to_date != '9999-01-01'` trong `nth_dept_emp`).
2.  **Rủi ro:** Lệnh `DELETE` trực tiếp với JOIN hoặc subquery lớn có thể khóa nhiều bảng trong thời gian dài.
3.  **Giải pháp:** Thiết kế một quy trình gồm nhiều bước:
    * Bước 1: Lấy danh sách các `emp_id` đã nghỉ việc vào một bảng tạm (`tmp_retired_emps`). Nên xử lý theo batch nhỏ để tránh select quá lớn.
    * Bước 2: Sử dụng vòng lặp, xóa dữ liệu trong `nth_dept_emp` theo từng batch `emp_id` từ bảng tạm.
    * Bước 3: Sử dụng vòng lặp, xóa dữ liệu trong `nth_titles` theo từng batch `emp_id` từ bảng tạm.
    * Bước 4: (Tùy chọn) Xóa các `emp_id` này khỏi bảng `nth_employees` (nếu cần).
4.  Viết mã SQL (hoặc pseudo-code) cho quy trình này.

### Bài tập 11.3: Đánh giá Lựa chọn Thiết kế
1.  **ID vs Dữ liệu:** Thảo luận về quyết định sử dụng `emp_id BIGINT AUTO_INCREMENT` làm PK cho `nth_employees` thay vì dùng `emp_no BIGINT UNIQUE`. Ưu điểm của `emp_id` là gì khi JOIN với các bảng khác như `nth_salaries`, `nth_titles`?
2.  **ENUM:** Cột `title` trong `nth_titles` hiện là `VARCHAR(50)`. Nếu công ty chỉ có khoảng 20 chức danh cố định, việc đổi `title` thành `ENUM(...)` có lợi ích gì về hiệu năng và lưu trữ trên 10 triệu bản ghi lịch sử chức vụ? Có nhược điểm gì không?

---