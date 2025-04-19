DROP TABLE IF EXISTS nth_salaries;
DROP TABLE IF EXISTS nth_titles;
DROP TABLE IF EXISTS nth_dept_manager;
DROP TABLE IF EXISTS nth_dept_emp;
DROP TABLE IF EXISTS nth_employees;
DROP TABLE IF EXISTS nth_departments;

-- Create departments table first since it's referenced by dept_emp
CREATE TABLE nth_departments (
    dept_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dept_no CHAR(4) UNIQUE,
    dept_name VARCHAR(40)
);

-- Create employees table since it's referenced by multiple tables
CREATE TABLE nth_employees (
    emp_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    emp_no BIGINT UNIQUE,
    birth_date DATE,
    first_name VARCHAR(14),
    last_name VARCHAR(16),
    gender ENUM('M','F'),
    hire_date DATE
);

-- Create dept_emp table with foreign keys
CREATE TABLE nth_dept_emp (
    dept_emp_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    emp_id BIGINT,
    dept_id BIGINT,
    from_date DATE,
    to_date DATE,
    FOREIGN KEY (emp_id) REFERENCES nth_employees(emp_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (dept_id) REFERENCES nth_departments(dept_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create dept_manager table
CREATE TABLE nth_dept_manager (
    dept_manager_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dept_id BIGINT,
    emp_id BIGINT,
    from_date DATE,
    to_date DATE,
    UNIQUE KEY (emp_id, dept_id),
    FOREIGN KEY (emp_id) REFERENCES nth_employees(emp_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (dept_id) REFERENCES nth_departments(dept_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create titles table
CREATE TABLE nth_titles (
    title_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    emp_id BIGINT,
    title VARCHAR(50),
    from_date DATE,
    to_date DATE,
    UNIQUE KEY (emp_id, title, from_date),
    FOREIGN KEY (emp_id) REFERENCES nth_employees(emp_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create salaries table
CREATE TABLE nth_salaries (
    salary_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    emp_id BIGINT,
    salary INT(11),
    from_date DATE,
    to_date DATE,
    UNIQUE KEY (emp_id, from_date),
    FOREIGN KEY (emp_id) REFERENCES nth_employees(emp_id) ON DELETE CASCADE ON UPDATE CASCADE
);

DELIMITER //
DROP PROCEDURE IF EXISTS generate_fake_data//
CREATE PROCEDURE generate_fake_data()
BEGIN
    -- Khai báo biến
    DECLARE batch_size INT DEFAULT 10000; -- Kích thước lô
    DECLARE total_records BIGINT DEFAULT 10000000; -- Tổng số bản ghi mục tiêu
    DECLARE i BIGINT DEFAULT 0;
    DECLARE progress DECIMAL(5,2) DEFAULT 0.00;
    DECLARE emp_count BIGINT DEFAULT 0;
    DECLARE dept_count INT DEFAULT 0;
    DECLARE dept_names VARCHAR(1000) DEFAULT 'Marketing,Finance,Human Resources,Engineering,Sales,Customer Support,Research,Production,Quality Assurance,Legal';
    DECLARE last_emp_id BIGINT DEFAULT 0;
    DECLARE last_dept_id BIGINT DEFAULT 0;

    -- Tắt kiểm tra foreign key trước khi truncate
    SET FOREIGN_KEY_CHECKS = 0;

    -- Truncate các bảng theo thứ tự ngược lại để tránh lỗi
    TRUNCATE TABLE nth_salaries;
    TRUNCATE TABLE nth_titles;
    TRUNCATE TABLE nth_dept_manager;
    TRUNCATE TABLE nth_dept_emp;
    TRUNCATE TABLE nth_employees;
    TRUNCATE TABLE nth_departments;

    -- Bật lại kiểm tra foreign key
    SET FOREIGN_KEY_CHECKS = 1;

    -- Chèn dữ liệu vào bảng nth_departments
    SELECT 'Đang tạo dữ liệu cho bảng nth_departments...' AS status;

    SET i = 1;
    WHILE i <= 10 DO
        INSERT INTO nth_departments (dept_no, dept_name)
        VALUES (
            CONCAT('D', LPAD(i, 3, '0')),
            SUBSTRING_INDEX(SUBSTRING_INDEX(dept_names, ',', i), ',', -1)
        );
        SET i = i + 1;
    END WHILE;

    SELECT COUNT(*) INTO dept_count FROM nth_departments;
    SELECT CONCAT('Đã tạo ', dept_count, ' phòng ban') AS status;

    -- Chèn dữ liệu vào bảng nth_employees
    SELECT 'Đang tạo dữ liệu cho bảng nth_employees...' AS status;

    SET i = 0;

    WHILE i < total_records DO
        START TRANSACTION;

        INSERT INTO nth_employees (emp_no, birth_date, first_name, last_name, gender, hire_date)
        SELECT
            100000 + i + seq.n,
            DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 10000 + 8000) DAY),
            ELT(FLOOR(RAND() * 10) + 1, 'John', 'Mary', 'James', 'Linda', 'Michael', 'Sarah', 'Robert', 'Lisa', 'William', 'Elizabeth'),
            ELT(FLOOR(RAND() * 10) + 1, 'Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor'),
            ELT(FLOOR(RAND() * 2) + 1, 'M', 'F'),
            DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 7300) DAY)
        FROM (
            SELECT a.N + b.N * 10 + c.N * 100 + d.N * 1000 AS n
            FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
                 (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b,
                 (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) c,
                 (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) d
            LIMIT batch_size
        ) seq;

        COMMIT;

        SET i = i + batch_size;

        -- Cập nhật và hiển thị tiến độ
        SET progress = (i / total_records) * 100;
        SELECT CONCAT('Employees progress: ', ROUND(progress, 2), '%') AS status;

    END WHILE;

    SELECT COUNT(*) INTO emp_count FROM nth_employees;
    SELECT CONCAT('Đã tạo ', emp_count, ' nhân viên') AS status;

    -- Lấy ID nhân viên và phòng ban để sử dụng trong các bảng khác
    SELECT MAX(emp_id) INTO last_emp_id FROM nth_employees;
    SELECT MAX(dept_id) INTO last_dept_id FROM nth_departments;

    -- Chèn dữ liệu vào bảng nth_dept_emp
    SELECT 'Đang tạo dữ liệu cho bảng nth_dept_emp...' AS status;

    SET i = 0;

    WHILE i < emp_count DO
        START TRANSACTION;

        INSERT INTO nth_dept_emp (emp_id, dept_id, from_date, to_date)
        SELECT
            emp_id,
            FLOOR(RAND() * last_dept_id) + 1,
            hire_date,
            CASE
                WHEN RAND() < 0.8 THEN DATE_ADD(hire_date, INTERVAL FLOOR(RAND() * 3650) DAY)
                ELSE '9999-01-01'
            END
        FROM nth_employees
        WHERE emp_id > i AND emp_id <= i + batch_size;

        COMMIT;

        SET i = i + batch_size;

        -- Cập nhật và hiển thị tiến độ
        SET progress = (i / emp_count) * 100;
        SELECT CONCAT('Department-Employee progress: ', ROUND(progress, 2), '%') AS status;

    END WHILE;

    -- Chèn dữ liệu vào bảng nth_dept_manager
    SELECT 'Đang tạo dữ liệu cho bảng nth_dept_manager...' AS status;

    -- Mỗi phòng ban có 5 manager trong các khoảng thời gian khác nhau
    SET i = 1;
    WHILE i <= last_dept_id DO
        INSERT INTO nth_dept_manager (dept_id, emp_id, from_date, to_date)
        SELECT
            i,
            emp_id,
            CASE
                WHEN rn = 1 THEN DATE_SUB(CURDATE(), INTERVAL 6000 DAY)
                WHEN rn = 2 THEN DATE_SUB(CURDATE(), INTERVAL 4800 DAY)
                WHEN rn = 3 THEN DATE_SUB(CURDATE(), INTERVAL 3600 DAY)
                WHEN rn = 4 THEN DATE_SUB(CURDATE(), INTERVAL 2400 DAY)
                WHEN rn = 5 THEN DATE_SUB(CURDATE(), INTERVAL 1200 DAY)
            END,
            CASE
                WHEN rn = 1 THEN DATE_SUB(CURDATE(), INTERVAL 4800 DAY)
                WHEN rn = 2 THEN DATE_SUB(CURDATE(), INTERVAL 3600 DAY)
                WHEN rn = 3 THEN DATE_SUB(CURDATE(), INTERVAL 2400 DAY)
                WHEN rn = 4 THEN DATE_SUB(CURDATE(), INTERVAL 1200 DAY)
                WHEN rn = 5 THEN '9999-01-01'
            END
        FROM (
            SELECT emp_id,
                   ROW_NUMBER() OVER (ORDER BY RAND()) as rn
            FROM nth_employees
            ORDER BY RAND()
            LIMIT 5
        ) AS random_managers;

        SET i = i + 1;
    END WHILE;

    -- Chèn dữ liệu vào bảng nth_titles
    SELECT 'Đang tạo dữ liệu cho bảng nth_titles...' AS status;

    SET i = 0;

    WHILE i < emp_count DO
        START TRANSACTION;

        INSERT INTO nth_titles (emp_id, title, from_date, to_date)
        SELECT
            emp_id,
            ELT(FLOOR(RAND() * 7) + 1, 'Staff', 'Senior Staff', 'Assistant Engineer', 'Engineer', 'Senior Engineer', 'Technique Leader', 'Manager'),
            hire_date,
            CASE
                WHEN RAND() < 0.7 THEN DATE_ADD(hire_date, INTERVAL FLOOR(RAND() * 3650) DAY)
                ELSE '9999-01-01'
            END
        FROM nth_employees
        WHERE emp_id > i AND emp_id <= i + batch_size;

        COMMIT;

        SET i = i + batch_size;

        -- Cập nhật và hiển thị tiến độ
        SET progress = (i / emp_count) * 100;
        SELECT CONCAT('Titles progress: ', ROUND(progress, 2), '%') AS status;
    END WHILE;

    -- Chèn dữ liệu vào bảng nth_salaries
    SELECT 'Đang tạo dữ liệu cho bảng nth_salaries...' AS status;

    SET i = 0;

    WHILE i < emp_count DO
        START TRANSACTION;

        INSERT INTO nth_salaries (emp_id, salary, from_date, to_date)
        SELECT
            emp_id,
            FLOOR(RAND() * 100000) + 40000,
            hire_date,
            CASE
                WHEN RAND() < 0.8 THEN DATE_ADD(hire_date, INTERVAL FLOOR(RAND() * 3650) DAY)
                ELSE '9999-01-01'
            END
        FROM nth_employees
        WHERE emp_id > i AND emp_id <= i + batch_size;

        COMMIT;

        SET i = i + batch_size;

        -- Cập nhật và hiển thị tiến độ
        SET progress = (i / emp_count) * 100;
        SELECT CONCAT('Salaries progress: ', ROUND(progress, 2), '%') AS status;
    END WHILE;

    -- Kết thúc
    SELECT 'Đã hoàn thành tạo dữ liệu cho tất cả các bảng!' AS status;
END//

DELIMITER ;

-- Để chạy procedure:
CALL generate_fake_data();
DROP PROCEDURE IF EXISTS generate_fake_data;
-- sudo docker exec -i $(docker ps -qf "name=mysql_db") mysql -u app_user -psecret php_app < /home/ninja-vue/project/php/php-core/fake_data_fixed.sql