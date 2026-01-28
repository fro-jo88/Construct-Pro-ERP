-- CONSTRUCT PRO ERP Database Schema
-- Database: wechecha_con

CREATE DATABASE IF NOT EXISTS wechecha_con;
USE wechecha_con;

-- 1. AUTH & SECURITY
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perm_key VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    perm_id INT NOT NULL,
    PRIMARY KEY (role_id, perm_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (perm_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role_id INT,
    status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    table_affected VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 2. HR & EMPLOYEE MANAGEMENT
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    employee_code VARCHAR(20) UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    department VARCHAR(50),
    designation VARCHAR(50),
    joining_date DATE,
    salary_type ENUM('monthly', 'daily', 'hourly'),
    base_salary DECIMAL(15, 2),
    status ENUM('pending', 'active', 'resigned', 'terminated') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    doc_type VARCHAR(50),
    file_path VARCHAR(255),
    expiry_date DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    work_date DATE,
    clock_in TIME,
    clock_out TIME,
    status ENUM('present', 'absent', 'late', 'on_leave'),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

-- 3. TENDERS & BIDDING
CREATE TABLE tenders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_no VARCHAR(50) UNIQUE,
    title VARCHAR(255) NOT NULL,
    client_name VARCHAR(255),
    description TEXT,
    deadline DATETIME,
    status ENUM('draft', 'gm_review', 'bidding_process', 'submitted', 'won', 'lost', 'cancelled') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE tender_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT,
    doc_type ENUM('technical', 'financial', 'legal', 'other'),
    file_path VARCHAR(255),
    version INT DEFAULT 1,
    FOREIGN KEY (tender_id) REFERENCES tenders(id)
) ENGINE=InnoDB;

-- 4. PROJECTS & SITES
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNIQUE,
    project_code VARCHAR(20) UNIQUE,
    project_name VARCHAR(255) NOT NULL,
    start_date DATE,
    end_date DATE,
    budget DECIMAL(15, 2),
    status ENUM('planned', 'active', 'on_hold', 'completed', 'cancelled') DEFAULT 'planned',
    FOREIGN KEY (tender_id) REFERENCES tenders(id)
) ENGINE=InnoDB;

CREATE TABLE sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    site_name VARCHAR(255),
    location VARCHAR(255),
    foreman_id INT,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (foreman_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 5. PLANNING & SCHEDULING
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    site_id INT,
    schedule_type ENUM('ms_project', 'manpower', 'equipment', 'material'),
    file_path VARCHAR(255),
    version INT DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id)
) ENGINE=InnoDB;

-- 6. INVENTORY & STORE
CREATE TABLE stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_name VARCHAR(100),
    location VARCHAR(255),
    manager_id INT,
    FOREIGN KEY (manager_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE,
    product_name VARCHAR(255),
    unit VARCHAR(20),
    category VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE stock_levels (
    store_id INT,
    product_id INT,
    quantity DECIMAL(15,2) DEFAULT 0,
    PRIMARY KEY (store_id, product_id),
    FOREIGN KEY (store_id) REFERENCES stores(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- 7. FINANCE & BUDGET
CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    total_amount DECIMAL(15,2),
    spent_amount DECIMAL(15,2) DEFAULT 0,
    remaining_amount DECIMAL(15,2),
    FOREIGN KEY (project_id) REFERENCES projects(id)
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    site_id INT,
    category VARCHAR(50),
    amount DECIMAL(15,2),
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    finance_approved_by INT,
    gm_approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (finance_approved_by) REFERENCES users(id),
    FOREIGN KEY (gm_approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 8. PROCUREMENT
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(255),
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE purchase_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    site_id INT,
    requested_by INT,
    status ENUM('pending', 'finance_approved', 'gm_approved', 'ordered', 'received') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (requested_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 9. TRANSPORT & LOGISTICS
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_number VARCHAR(20) UNIQUE,
    model VARCHAR(50),
    vehicle_type VARCHAR(50),
    status ENUM('available', 'on_trip', 'maintenance') DEFAULT 'available'
) ENGINE=InnoDB;

CREATE TABLE trip_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT,
    driver_id INT,
    project_id INT,
    start_time DATETIME,
    end_time DATETIME,
    kms_start INT,
    kms_end INT,
    fuel_consumed DECIMAL(10,2),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (driver_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
) ENGINE=InnoDB;

-- 10. AUDIT FINDINGS
CREATE TABLE construction_audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    site_id INT,
    audit_date DATE,
    findings TEXT,
    material_variance DECIMAL(15,2),
    progress_variance DECIMAL(5,2),
    audited_by INT,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (audited_by) REFERENCES users(id)
) ENGINE=InnoDB;
