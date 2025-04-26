# PHP Docker Project

A simple PHP 8 project using Docker with MySQL and Nginx.

## Components

- PHP 8.0 with essential extensions
- MySQL 8.0
- Nginx webserver

## Directory Structure

```
.
├── docker-compose.yml
├── Dockerfile
├── nginx
│   └── conf.d
│       └── app.conf
└── src
    └── index.php
```

## Setup Instructions

1. Make sure you have Docker and Docker Compose installed on your system.

2. Clone this repository:
   ```
   git clone <repository-url>
   cd <repository-directory>
   ```

3. Start the Docker containers:
   ```
   docker-compose up -d
   ```

4. Wait for the containers to start up (this may take a minute or two for the first run).

5. Access the application in your web browser:
   ```
   http://localhost
   ```

## Database Connection

The application is configured with the following database settings:

- Host: db
- Database: php_app
- Username: app_user
- Password: secret
- Root Password: root

You can connect to MySQL from your host machine using:
```
Host: localhost
Port: 3306
Username: app_user or root
Password: secret or root
```

## Stopping the Application

To stop the containers:
```
docker-compose down
```

To stop and remove all containers, networks, and volumes:
```
docker-compose down -v
``` 

## Count
```
SELECT 'nth_employees' AS table_name, COUNT(*) AS record_count FROM nth_employees
UNION ALL
SELECT 'nth_dept_emp', COUNT(*) FROM nth_dept_emp
UNION ALL
SELECT 'nth_titles', COUNT(*) FROM nth_titles
UNION ALL
SELECT 'nth_salaries', COUNT(*) FROM nth_salaries
UNION ALL
SELECT 'nth_dept_manager', COUNT(*) FROM nth_dept_manager;
```
mst_number disgist > 6 ~10^6 = feature > 1.000.000 rows

# Compare Select2 vs Select2 get all
## So sánh giữa hai phiên bản Select2

| Tiêu chí | Select2 (AJAX) | Select2 Original (Local Data) | Ưu điểm của Select2 AJAX |
|----------|----------------|-------------------------------|--------------------------|
| **Tải dữ liệu** | Tải theo trang (10 bản ghi/lần) | Tải tất cả dữ liệu cùng một lúc | Giảm tải cho máy chủ và mạng khi cơ sở dữ liệu lớn |
| **Thời gian tải ban đầu** | Nhanh (chỉ tải 10 bản ghi) | Chậm (tải toàn bộ dữ liệu) | Hiển thị kết quả đầu tiên nhanh hơn |
| **Phân trang** | Có (server-side pagination) | Không (client-side filtering) | Xử lý được bộ dữ liệu lớn không giới hạn kích thước |
| **Tìm kiếm** | Gửi yêu cầu tìm kiếm đến máy chủ | Tìm kiếm cục bộ trong dữ liệu đã tải | Tìm kiếm chính xác hơn, có thể áp dụng các thuật toán tìm kiếm phức tạp ở phía server |
| **Hiệu suất với dữ liệu lớn** | Tốt (không bị ảnh hưởng bởi kích thước dữ liệu) | Kém (chậm khi dữ liệu lớn) | Hiệu suất ổn định ngay cả với hàng triệu bản ghi |
| **Tốc độ phản hồi khi tìm kiếm** | Phụ thuộc mạng, cần thêm thời gian gửi request | Ngay lập tức (không cần kết nối mạng) | Kết quả chính xác hơn, phù hợp với dữ liệu thay đổi thường xuyên |

## explain analyze mysql
```
SHOW INDEX FROM table;
explain analyze
SELECT SQL_NO_CACHE
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
FROM nth_employees ne
LEFT JOIN nth_titles nt ON ne.emp_id = nt.emp_id
LEFT JOIN nth_salaries ns ON ne.emp_id = ns.emp_id
LEFT JOIN nth_dept_emp nde ON ne.emp_id = nde.emp_id
LEFT JOIN nth_departments nd1 ON nde.dept_id = nd1.dept_id
LEFT JOIN nth_dept_manager ndm ON ne.emp_id = ndm.emp_id
LEFT JOIN nth_departments nd2 on ndm.dept_id = nd2.dept_id
WHERE ne.emp_id > 10000000
AND (ne.last_name LIKE '%son%' OR ne.first_name LIKE '%son%')
ORDER BY ne.emp_id ASC
LIMIT 10;

explain analyze        
SELECT SQL_NO_CACHE
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
FROM nth_employees ne
LEFT JOIN nth_titles nt ON ne.emp_id = nt.emp_id
LEFT JOIN nth_salaries ns ON ne.emp_id = ns.emp_id
LEFT JOIN nth_dept_emp nde ON ne.emp_id = nde.emp_id
LEFT JOIN nth_departments nd1 ON nde.dept_id = nd1.dept_id
LEFT JOIN nth_dept_manager ndm ON ne.emp_id = ndm.emp_id
LEFT JOIN nth_departments nd2 on ndm.dept_id = nd2.dept_id
AND (ne.last_name LIKE '%son%' OR ne.first_name LIKE '%son%')
ORDER BY ne.emp_id ASC
LIMIT 10 OFFSET 500000;

Index scan on ne using PRIMARY  (cost=1157 rows=195928) (actual time=0.299..129 rows=200010 loops=1)
= 129 - 0.299 ~ 1s
```

## Pagination
```
SELECT ne.emp_id FROM nth_employees ne ORDER BY ne.emp_id DESC LIMIT 1 OFFSET 9000000;
```
