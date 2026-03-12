CREATE DATABASE IF NOT EXISTS white_care_db;
USE white_care_db;

-- Tablas de usuarios y Logs

CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    id_role INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    active BOOLEAN DEFAULT TRUE,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    failed_attempts INT DEFAULT 0,
    CONSTRAINT fk_user_role FOREIGN KEY (id_role) REFERENCES roles(id_rol)
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    action TEXT,
    affected_table VARCHAR(50),
    old_value TEXT,
    new_value TEXT,
    record_id INT,
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_act_user FOREIGN KEY (id_user) REFERENCES users(id_user)
);

CREATE TABLE IF NOT EXISTS error_logs (
    id_error INT AUTO_INCREMENT PRIMARY KEY,
    error_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT,
    procedure_name TEXT,
    error_code INT,
    error_message TEXT,
    CONSTRAINT fk_err_user FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Tablas de Catalogos

CREATE TABLE IF NOT EXISTS appointment_statuses (
    id_status INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS sale_statuses (
    id_status INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS movement_types (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Tablas de Pacientes y Citas

CREATE TABLE IF NOT EXISTS patients (
    id_patient INT AUTO_INCREMENT PRIMARY KEY,
    id_card VARCHAR(20) NOT NULL UNIQUE, 
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE,
    phone VARCHAR(15),
    email VARCHAR(100),
    address TEXT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_patients_email UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS appointments (
    id_appointment INT AUTO_INCREMENT PRIMARY KEY,
    id_patient INT NOT NULL,
    id_dentist_user INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT,
    id_appointment_status INT NOT NULL,
    duration_minutes INT DEFAULT 30,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_app_patient FOREIGN KEY (id_patient) REFERENCES patients(id_patient),
    CONSTRAINT fk_app_dentist FOREIGN KEY (id_dentist_user) REFERENCES users(id_user),
    CONSTRAINT fk_app_status FOREIGN KEY (id_appointment_status) REFERENCES appointment_statuses(id_status),
    CONSTRAINT chk_appointment_date_future CHECK (appointment_date >= CURDATE() - INTERVAL 1 DAY)
);

CREATE TABLE IF NOT EXISTS medical_histories (
    id_history INT AUTO_INCREMENT PRIMARY KEY,
    id_patient INT NOT NULL,
    id_appointment INT NOT NULL,
    diagnosis TEXT,
    treatment TEXT,
    notes TEXT,
    requires_control BOOLEAN DEFAULT FALSE,
    next_control_date DATE,
    CONSTRAINT fk_history_patient FOREIGN KEY (id_patient) REFERENCES patients(id_patient),
    CONSTRAINT fk_history_app FOREIGN KEY (id_appointment) REFERENCES appointments(id_appointment)
);

-- Tablas de inventario

CREATE TABLE IF NOT EXISTS product_categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    id_category INT NOT NULL,
    barcode VARCHAR(20) UNIQUE,
    product_name VARCHAR(100) NOT NULL,
    sale_price DECIMAL(10,2) NOT NULL,
    purchase_price DECIMAL(10,2) NOT NULL,
    min_stock INT DEFAULT 5,
    measurement_unit VARCHAR(20),
    CONSTRAINT fk_prod_cat FOREIGN KEY (id_category) REFERENCES product_categories(id_category),
    CONSTRAINT chk_products_prices CHECK (sale_price >= 0 AND purchase_price >= 0)
);

CREATE TABLE IF NOT EXISTS batches (
    id_batch INT AUTO_INCREMENT PRIMARY KEY,
    id_product INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    expiration_date DATE,
    initial_quantity INT NOT NULL,
    current_quantity INT NOT NULL,
    CONSTRAINT fk_batch_prod FOREIGN KEY (id_product) REFERENCES products(id_product),
    CONSTRAINT chk_batches_quantities CHECK (initial_quantity > 0 AND current_quantity >= 0)
);

CREATE TABLE IF NOT EXISTS inventory_movements (
    id_movement INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_batch INT NOT NULL,
    id_movement_type INT NOT NULL,
    quantity INT NOT NULL,
    justification VARCHAR(50) NOT NULL, 
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mov_user FOREIGN KEY (id_user) REFERENCES users(id_user),
    CONSTRAINT fk_mov_batch FOREIGN KEY (id_batch) REFERENCES batches(id_batch),
    CONSTRAINT fk_mov_type FOREIGN KEY (id_movement_type) REFERENCES movement_types(id_type)
);

-- Tablas de ventas

CREATE TABLE IF NOT EXISTS sales (
    id_sale INT AUTO_INCREMENT PRIMARY KEY,
    id_patient INT NOT NULL,
    id_user INT NOT NULL,
    id_appointment INT,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    id_sale_status INT NOT NULL,
    CONSTRAINT fk_sale_patient FOREIGN KEY (id_patient) REFERENCES patients(id_patient),
    CONSTRAINT fk_sale_user FOREIGN KEY (id_user) REFERENCES users(id_user),
    CONSTRAINT fk_sale_status FOREIGN KEY (id_sale_status) REFERENCES sale_statuses(id_status)
);

CREATE TABLE IF NOT EXISTS sale_details (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_sale INT NOT NULL,
    id_product INT NOT NULL,
    id_movement INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_det_sale FOREIGN KEY (id_sale) REFERENCES sales(id_sale),
    CONSTRAINT fk_det_prod FOREIGN KEY (id_product) REFERENCES products(id_product),
    CONSTRAINT fk_det_mov FOREIGN KEY (id_movement) REFERENCES inventory_movements(id_movement)
);
