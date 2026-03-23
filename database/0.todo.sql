CREATE DATABASE IF NOT EXISTS white_care_db;
USE white_care_db;

-- Tablas de usuarios y Logs

CREATE TABLE IF NOT EXISTS roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
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
    CONSTRAINT fk_user_role FOREIGN KEY (id_role) REFERENCES roles(id_role)
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL,                     
    action VARCHAR(50) NOT NULL,          
    affected_table VARCHAR(50) NOT NULL,  
    record_id INT NULL,                   
    old_value TEXT NULL,                  
    new_value TEXT NULL,                  
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS error_logs (
    id_error INT AUTO_INCREMENT PRIMARY KEY,
    error_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NULL,                     
    procedure_name TEXT,
    error_code INT,
    error_message TEXT
);

-- Tablas de Catalogos

CREATE TABLE IF NOT EXISTS appointment_statuses (
    id_status INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS sale_statuses (
    id_status INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS movement_types (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE
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
    active BOOLEAN DEFAULT TRUE,
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
    CONSTRAINT fk_app_status FOREIGN KEY (id_appointment_status) REFERENCES appointment_statuses(id_status)
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
    description TEXT,
    active BOOLEAN DEFAULT TRUE
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
    active BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_prod_cat FOREIGN KEY (id_category) REFERENCES product_categories(id_category)
);

CREATE TABLE IF NOT EXISTS batches (
    id_batch INT AUTO_INCREMENT PRIMARY KEY,
    id_product INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    expiration_date DATE,
    initial_quantity INT NOT NULL,
    current_quantity INT NOT NULL,
    CONSTRAINT fk_batch_prod FOREIGN KEY (id_product) REFERENCES products(id_product)
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

-- Índices para activity_logs
CREATE INDEX idx_activity_user ON activity_logs(id_user);
CREATE INDEX idx_activity_table ON activity_logs(affected_table);
CREATE INDEX idx_activity_record ON activity_logs(record_id);
CREATE INDEX idx_activity_date ON activity_logs(activity_date);
CREATE INDEX idx_activity_table_date ON activity_logs(affected_table, activity_date);

-- Índices para error_logs
CREATE INDEX idx_error_date ON error_logs(error_date);
CREATE INDEX idx_error_user ON error_logs(id_user);
CREATE INDEX idx_error_code ON error_logs(error_code);

-- Índices para appointments
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_patient ON appointments(id_patient);
CREATE INDEX idx_appointments_dentist ON appointments(id_dentist_user);
CREATE INDEX idx_appointments_status ON appointments(id_appointment_status);
CREATE INDEX idx_appointments_date_status ON appointments(appointment_date, id_appointment_status);
CREATE INDEX idx_appointments_dentist_date ON appointments(id_dentist_user, appointment_date);

-- Índices para batches
CREATE INDEX idx_batches_product ON batches(id_product);
CREATE INDEX idx_batches_expiry ON batches(expiration_date);
CREATE INDEX idx_batches_current_qty ON batches(current_quantity);
CREATE INDEX idx_batches_product_expiry ON batches(id_product, expiration_date);
CREATE INDEX idx_batches_product_quantity ON batches(id_product, current_quantity);

-- Índices para sales
CREATE INDEX idx_sales_patient ON sales(id_patient);
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_status ON sales(id_sale_status);
CREATE INDEX idx_sales_date_status ON sales(sale_date, id_sale_status);
CREATE INDEX idx_sales_user_date ON sales(id_user, sale_date);
CREATE INDEX idx_sales_appointment ON sales(id_appointment);

-- Índices para inventory_movements
CREATE INDEX idx_movements_batch ON inventory_movements(id_batch);
CREATE INDEX idx_movements_date ON inventory_movements(movement_date);
CREATE INDEX idx_movements_type ON inventory_movements(id_movement_type);
CREATE INDEX idx_movements_batch_date ON inventory_movements(id_batch, movement_date);
CREATE INDEX idx_movements_user ON inventory_movements(id_user);
CREATE INDEX idx_movements_justification ON inventory_movements(justification);

-- Índices para users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(id_role);
CREATE INDEX idx_users_active ON users(active);
CREATE INDEX idx_users_email_active ON users(email, active);

-- Índices para patients
CREATE INDEX idx_patients_id_card ON patients(id_card);
CREATE INDEX idx_patients_name ON patients(last_name, first_name);
CREATE INDEX idx_patients_email ON patients(email);
CREATE INDEX idx_patients_active ON patients(active);

-- Índices para products
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_name ON products(product_name);
CREATE INDEX idx_products_category ON products(id_category);
CREATE INDEX idx_products_minstock ON products(min_stock);
CREATE INDEX idx_products_sale_price ON products(sale_price);
CREATE INDEX idx_products_active ON products(active);

-- Índices para medical_histories
CREATE INDEX idx_history_patient ON medical_histories(id_patient);
CREATE INDEX idx_history_appointment ON medical_histories(id_appointment);
CREATE INDEX idx_history_control ON medical_histories(requires_control, next_control_date);

-- Datos iniciales de prueba

-- Roles del sistema
INSERT INTO roles (role_name, description, active) VALUES 
('Admin', 'Full system access', 1),
('Dentist', 'Clinical management and patient history', 1),
('Warehouse Manager', 'Inventory and stock control', 1),
('Receptionist', 'Appointment and sales management', 1);

-- Estados de las citas
INSERT INTO appointment_statuses (status_name, description, active) VALUES 
('Scheduled', 'Appointment scheduled but not yet attended', 1),
('Attended', 'Patient was attended and history recorded', 1),
('Cancelled', 'Appointment cancelled by patient or clinic', 1),
('No-Show', 'Patient did not show up', 1);

-- Estados de las ventas
INSERT INTO sale_statuses (status_name, description, active) VALUES 
('Paid', 'Transaction completed successfully', 1),
('Pending', 'Invoice generated but not yet paid', 1),
('Refunded', 'Payment returned to customer', 1);

-- Tipos de movimientos de inventario
INSERT INTO movement_types (type_name, description, active) VALUES 
('Purchase', 'New stock received from supplier', 1),
('Sale', 'Stock reduced by customer purchase', 1),
('Adjustment - Loss', 'Manual reduction due to damage or theft', 1),
('Adjustment - Expiry', 'Manual reduction due to expired product', 1),
('Internal Use', 'Materials used during clinical procedures', 1);

-- Usuarios
-- Administrador (Admin)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (1, 'admin1', 'admin1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88881111', TRUE);

-- Dentista (Dentist)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (2, 'dentist1', 'dentist1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88882222', TRUE);

-- Bodeguero (Warehouse Manager)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (3, 'warehouse1', 'warehouse1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88883333', TRUE);

-- Recepcionista (Receptionist)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (4, 'receptionist1', 'receptionist1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88884444', TRUE);

-- Pacientes
INSERT INTO patients (id_card, first_name, last_name, birth_date, phone, email, address, active) VALUES
('101230101', 'Carlos', 'Rodríguez Méndez', '1985-03-15', '61234567', 'carlos.rodriguez@email.com', 'Avenida Central, San José', 1),
('202340202', 'María', 'García López', '1990-07-22', '69876543', 'maria.garcia@email.com', 'Calle 5, Alajuela', 1),
('303450303', 'Juan', 'Martínez Sánchez', '1978-11-08', '65544332', 'juan.martinez@email.com', 'Barrio Amón, San José', 1),
('404560404', 'Ana', 'Fernández Pérez', '1982-05-19', '67788990', 'ana.fernandez@email.com', 'Paseo Colón, San José', 1),
('505670505', 'Miguel', 'López González', '1995-09-30', '62233445', 'miguel.lopez@email.com', 'Guachipelín, Escazú', 1),
('606780606', 'Laura', 'Sánchez Díaz', '1988-12-12', '61122334', 'laura.sanchez@email.com', 'Santa Ana, San José', 1),
('707890707', 'David', 'Pérez Moreno', '1975-04-25', '69988776', 'david.perez@email.com', 'Curridabat, San José', 1),
('808900808', 'Elena', 'Gómez Ruiz', '1992-08-14', '64455667', 'elena.gomez@email.com', 'Heredia Centro', 1),
('909011909', 'Javier', 'Álvarez Jiménez', '1980-01-07', '63344556', 'javier.alvarez@email.com', 'Cartago, Villa Real', 1),
('101122101', 'Sara', 'Romero Navarro', '1987-06-21', '67711223', 'sara.romero@email.com', 'San Pedro, Montes de Oca', 1),
('111233111', 'Pablo', 'Torres Castro', '1993-10-03', '68899001', 'pablo.torres@email.com', 'Santa Bárbara, Heredia', 1),
('121344121', 'Marta', 'Ruiz Ortega', '1984-02-28', '62217890', 'marta.ruiz@email.com', 'Alajuela, El Coyol', 1),
('131455131', 'Daniel', 'Vázquez Serrano', '1972-09-17', '65566778', 'daniel.vazquez@email.com', 'San José, Zapote', 1),
('141566141', 'Carmen', 'Jiménez Muñoz', '1991-12-05', '64433221', 'carmen.jimenez@email.com', 'Cartago, Paraíso', 1),
('151677151', 'Alejandro', 'Díaz García', '1983-07-11', '61144556', 'alejandro.diaz@email.com', 'San José, Pavas', 1),
('161788161', 'Isabel', 'Hernández Blanco', '1976-03-29', '69911223', 'isabel.hernandez@email.com', 'Heredia, San Pablo', 1),
('171899171', 'Raúl', 'Santos Vidal', '1989-05-16', '67788334', 'raul.santos@email.com', 'San José, Rohrmoser', 1),
('182000182', 'Patricia', 'Castro Molina', '1994-11-23', '62245566', 'patricia.castro@email.com', 'Alajuela, San Ramón', 1),
('192111192', 'Jorge', 'Ortega Fuentes', '1981-08-09', '65599001', 'jorge.ortega@email.com', 'Cartago, Tres Ríos', 1),
('202222202', 'Nuria', 'Molina Aguirre', '1986-04-18', '68877332', 'nuria.molina@email.com', 'San José, San Francisco', 1);

-- Categorías de productos
INSERT INTO product_categories (category_name, description, active) VALUES 
('Protection Materials', 'Protection materials', 1),
('Dental Treatment Materials', 'Dental treatment materials', 1),
('Anesthesia Materials', 'Anesthesia materials', 1),
('Disposable Instruments', 'Disposable instruments', 1),
('Cleaning and Sterilization Materials', 'Cleaning and sterilization materials', 1),
('Dental Impression Materials', 'Dental impression materials', 1),
('Common Supplies', 'Common supplies', 1),
('Daily Hygiene Products', 'Daily hygiene products', 1),
('Orthodontic Products', 'Orthodontic products', 1),
('Post-treatment Products', 'Post-treatment products', 1),
('Whitening Products', 'Whitening products', 1),
('Denture Products', 'Denture products', 1);

-- Productos
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit, active) VALUES
(1, 'Disposable gloves (box 100u)', 17.00, 9.00, 5, 'Box', 1),
(1, 'Surgical masks (box 50u)', 13.00, 7.00, 5, 'Box', 1),
(1, 'Face shields', 9.60, 4.40, 5, 'Unit', 1),
(1, 'Disposable caps (pack 10u)', 4.80, 2.40, 5, 'Pack', 1),
(1, 'Disposable gowns (pack 5u)', 23.60, 13.40, 5, 'Pack', 1),
(2, 'Dental resin (syringe)', 48.40, 30.00, 5, 'Syringe', 1),
(2, 'Dental amalgam (capsules 50u)', 95.60, 55.80, 5, 'Box', 1),
(2, 'Dental cement (bottle)', 34.80, 19.20, 5, 'Bottle', 1),
(2, 'Etching gel (syringe 3ml)', 19.40, 10.20, 5, 'Syringe', 1),
(2, 'Dental adhesive (bottle 5ml)', 40.80, 23.60, 5, 'Bottle', 1),
(2, 'Glass ionomer (kit)', 72.00, 44.00, 5, 'Kit', 1),
(3, 'Local anesthetic (box 50 carpules)', 91.40, 55.80, 5, 'Box', 1),
(3, 'Anesthetic needles (box 100u)', 30.00, 16.00, 5, 'Box', 1),
(3, 'Anesthetic carpules (case 50u)', 83.80, 48.40, 5, 'Case', 1),
(4, 'Disposable syringes (box 100u)', 19.40, 9.60, 5, 'Box', 1),
(4, 'Disposable cups (pack 50u)', 4.20, 2.00, 5, 'Pack', 1),
(4, 'Dental bibs (pack 100u)', 13.00, 6.40, 5, 'Pack', 1),
(4, 'Sterile gauze (pack 100u)', 8.60, 4.20, 5, 'Pack', 1),
(4, 'Dental cotton (bag 500g)', 10.80, 5.60, 5, 'Bag', 1),
(5, 'Alcohol (gallon)', 13.00, 7.00, 5, 'Gallon', 1),
(5, 'Hypochlorite (bleach) (liter)', 3.60, 1.60, 5, 'Liter', 1),
(5, 'Surface disinfectant (liter)', 8.60, 4.40, 5, 'Liter', 1),
(5, 'Sterilization bags (pack 200u)', 17.20, 9.60, 5, 'Pack', 1),
(5, 'Sterilization indicators (strip 250u)', 30.00, 17.20, 5, 'Box', 1),
(6, 'Alginate (bag 500g)', 23.60, 13.00, 5, 'Bag', 1),
(6, 'Impression silicone (kit)', 75.20, 43.00, 5, 'Kit', 1),
(6, 'Impression trays (set 5 sizes)', 51.60, 28.00, 5, 'Set', 1),
(7, 'Dental floss (box 50u)', 17.20, 8.60, 5, 'Box', 1),
(7, 'Prophylaxis paste (bottle)', 10.80, 5.60, 5, 'Bottle', 1),
(7, 'Toothbrushes (unit)', 6.40, 3.00, 5, 'Unit', 1),
(7, 'Fluoride (bottle 250ml)', 19.40, 10.40, 5, 'Bottle', 1),
(7, 'Cotton rolls (pack 100u)', 7.60, 3.80, 5, 'Pack', 1),
(8, 'Toothbrush (unit)', 6.40, 3.00, 5, 'Unit', 1),
(8, 'Electric toothbrush', 51.60, 28.00, 5, 'Unit', 1),
(8, 'Dental floss (unit)', 4.20, 1.80, 5, 'Unit', 1),
(8, 'Toothpaste (tube 90ml)', 5.60, 2.60, 5, 'Tube', 1),
(8, 'Mouthwash (500ml)', 8.60, 4.40, 5, 'Bottle', 1),
(8, 'Interdental brushes (pack 6u)', 7.60, 3.60, 5, 'Pack', 1),
(8, 'Tongue cleaner', 5.00, 2.20, 5, 'Unit', 1),
(9, 'Bracket wax (box 6u)', 8.60, 4.20, 5, 'Box', 1),
(9, 'Orthodontic toothbrush', 7.60, 3.80, 5, 'Unit', 1),
(9, 'Orthodontic floss (box 10u)', 10.80, 5.60, 5, 'Box', 1),
(9, 'Orthodontic mouthwash (500ml)', 10.80, 5.60, 5, 'Bottle', 1),
(10, 'Desensitizing dental gel (tube)', 17.20, 9.00, 5, 'Tube', 1),
(10, 'Chlorhexidine mouthwash (500ml)', 13.00, 6.80, 5, 'Bottle', 1),
(10, 'Sensitive toothpaste (tube)', 7.60, 3.80, 5, 'Tube', 1),
(10, 'Fluoride gel (tube)', 10.80, 5.60, 5, 'Tube', 1),
(10, 'Analgesic (box 20 tablets)', 8.60, 4.20, 5, 'Box', 1),
(11, 'Teeth whitening kit', 129.00, 77.40, 5, 'Kit', 1),
(11, 'Whitening gel (syringe)', 43.00, 23.60, 5, 'Syringe', 1),
(11, 'Whitening syringes (pack 10u)', 17.20, 8.60, 5, 'Pack', 1),
(12, 'Denture adhesive (tube)', 10.80, 5.60, 5, 'Tube', 1),
(12, 'Denture cleaning tablets (box 30u)', 13.00, 6.80, 5, 'Box', 1),
(12, 'Denture brush', 6.40, 3.00, 5, 'Unit', 1);

-- Lotes
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(1, 'BATCH-GLOVES-001', '2025-01-10', '2026-01-10', 1000, 850),
(1, 'BATCH-GLOVES-002', '2025-03-15', '2026-03-15', 1000, 1000),
(6, 'BATCH-RESIN-001', '2025-01-15', '2026-01-15', 150, 120),
(6, 'BATCH-RESIN-002', '2025-03-01', '2026-03-01', 150, 150),
(12, 'BATCH-ANESTHETIC-001', '2025-01-05', '2025-12-05', 300, 250),
(12, 'BATCH-ANESTHETIC-002', '2025-03-01', '2026-02-01', 300, 300),
(15, 'BATCH-SYRINGES-001', '2025-01-12', '2026-01-12', 600, 520),
(15, 'BATCH-SYRINGES-002', '2025-03-12', '2026-03-12', 600, 600),
(20, 'BATCH-ALCOHOL-001', '2025-01-10', '2026-01-10', 200, 150),
(20, 'BATCH-ALCOHOL-002', '2025-03-10', '2026-03-10', 200, 200),
(25, 'BATCH-ALGINATE-001', '2025-01-08', '2025-11-08', 120, 95),
(25, 'BATCH-ALGINATE-002', '2025-03-08', '2026-01-08', 120, 120),
(28, 'BATCH-FLOSS-001', '2025-01-12', '2026-01-12', 300, 220),
(28, 'BATCH-FLOSS-002', '2025-03-12', '2026-03-12', 300, 300),
(35, 'BATCH-TOOTHBRUSH-001', '2025-01-05', '2027-01-05', 150, 130),
(35, 'BATCH-TOOTHBRUSH-002', '2025-03-05', '2027-03-05', 150, 150),
(42, 'BATCH-WAX-001', '2025-01-10', '2026-07-10', 200, 180),
(42, 'BATCH-WAX-002', '2025-03-10', '2026-09-10', 200, 200),
(46, 'BATCH-DESENSITIZING-001', '2025-01-08', '2026-01-08', 80, 60),
(46, 'BATCH-DESENSITIZING-002', '2025-03-08', '2026-03-08', 80, 80),
(51, 'BATCH-WHITENING-001', '2025-01-15', '2026-01-15', 30, 25),
(51, 'BATCH-WHITENING-002', '2025-03-15', '2026-03-15', 30, 30),
(54, 'BATCH-ADHESIVE-001', '2025-01-12', '2026-01-12', 100, 85),
(54, 'BATCH-ADHESIVE-002', '2025-03-12', '2026-03-12', 100, 100);

USE white_care_db;

DELIMITER //

-- ROLES

-- crear rol
CREATE PROCEDURE sp_roles_create(
    IN p_role_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO roles (role_name, description, active)
    VALUES (p_role_name, p_description, 1);
    
    SELECT LAST_INSERT_ID() AS id_role;
END //

-- leer roles
CREATE PROCEDURE sp_roles_read(
    IN p_id_role INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM roles 
        WHERE id_role = p_id_role OR p_id_role IS NULL
        ORDER BY role_name;
    ELSE
        SELECT * FROM roles 
        WHERE (id_role = p_id_role OR p_id_role IS NULL) AND active = 1
        ORDER BY role_name;
    END IF;
END //

-- actualizar rol
CREATE PROCEDURE sp_roles_update(
    IN p_id_role INT,
    IN p_role_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    UPDATE roles 
    SET role_name = p_role_name,
        description = p_description
    WHERE id_role = p_id_role;
    
    SELECT ROW_COUNT() AS rows_affected;
END //

-- desactivar rol
CREATE PROCEDURE sp_roles_deactivate(IN p_id_role INT)
BEGIN
    DECLARE user_count INT;
    
    SELECT COUNT(*) INTO user_count FROM users WHERE id_role = p_id_role AND active = 1;
    
    IF user_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede desactivar el rol porque tiene usuarios activos asociados';
    ELSE
        UPDATE roles SET active = 0 WHERE id_role = p_id_role;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

-- activar rol
CREATE PROCEDURE sp_roles_activate(IN p_id_role INT)
BEGIN
    UPDATE roles SET active = 1 WHERE id_role = p_id_role;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- USUARIOS

-- crear usuario
CREATE PROCEDURE sp_users_create(
    IN p_id_role INT,
    IN p_full_name VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_phone VARCHAR(15)
)
BEGIN
    INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
    VALUES (p_id_role, p_full_name, p_email, p_password_hash, p_phone, 1);
    
    SELECT LAST_INSERT_ID() AS id_user;
END //

-- leer usuarios
CREATE PROCEDURE sp_users_read(
    IN p_id_user INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT u.*, r.role_name 
        FROM users u
        INNER JOIN roles r ON u.id_role = r.id_role
        WHERE u.id_user = p_id_user OR p_id_user IS NULL
        ORDER BY u.full_name;
    ELSE
        SELECT u.*, r.role_name 
        FROM users u
        INNER JOIN roles r ON u.id_role = r.id_role
        WHERE (u.id_user = p_id_user OR p_id_user IS NULL) AND u.active = 1
        ORDER BY u.full_name;
    END IF;
END //

-- actualizar usuario
CREATE PROCEDURE sp_users_update(
    IN p_id_user INT,
    IN p_id_role INT,
    IN p_full_name VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_phone VARCHAR(15),
    IN p_active BOOLEAN
)
BEGIN
    UPDATE users 
    SET id_role = p_id_role,
        full_name = p_full_name,
        email = p_email,
        phone = p_phone,
        active = p_active
    WHERE id_user = p_id_user;
    
    SELECT ROW_COUNT() AS rows_affected;
END //

-- desactivar usuario
CREATE PROCEDURE sp_users_deactivate(IN p_id_user INT)
BEGIN
    UPDATE users SET active = 0 WHERE id_user = p_id_user;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- activar usuario
CREATE PROCEDURE sp_users_activate(IN p_id_user INT)
BEGIN
    UPDATE users SET active = 1 WHERE id_user = p_id_user;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- Verificar credenciales de usuario
CREATE PROCEDURE sp_users_login(
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    UPDATE users SET last_login = NOW() 
    WHERE email = p_email AND password_hash = p_password_hash;
    
    SELECT u.*, r.role_name 
    FROM users u
    INNER JOIN roles r ON u.id_role = r.id_role
    WHERE u.email = p_email AND u.password_hash = p_password_hash AND u.active = 1;
END //

-- ESTADOS DE CITAS

-- crear estado de cita
CREATE PROCEDURE sp_appointment_statuses_create(
    IN p_status_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO appointment_statuses (status_name, description, active)
    VALUES (p_status_name, p_description, 1);
    
    SELECT LAST_INSERT_ID() AS id_status;
END //

-- leer estados de cita
CREATE PROCEDURE sp_appointment_statuses_read(
    IN p_id_status INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM appointment_statuses 
        WHERE id_status = p_id_status OR p_id_status IS NULL
        ORDER BY status_name;
    ELSE
        SELECT * FROM appointment_statuses 
        WHERE (id_status = p_id_status OR p_id_status IS NULL) AND active = 1
        ORDER BY status_name;
    END IF;
END //

-- actualizar estado de cita
CREATE PROCEDURE sp_appointment_statuses_update(
    IN p_id_status INT,
    IN p_status_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    UPDATE appointment_statuses 
    SET status_name = p_status_name,
        description = p_description
    WHERE id_status = p_id_status;
    
    SELECT ROW_COUNT() AS rows_affected;
END //

-- desactivar estado de cita
CREATE PROCEDURE sp_appointment_statuses_deactivate(IN p_id_status INT)
BEGIN
    DECLARE appointment_count INT;
    
    SELECT COUNT(*) INTO appointment_count FROM appointments 
    WHERE id_appointment_status = p_id_status 
      AND id_appointment_status NOT IN (SELECT id_status FROM appointment_statuses WHERE status_name = 'Cancelled');
    
    IF appointment_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede desactivar el estado porque hay citas no canceladas que lo utilizan';
    ELSE
        UPDATE appointment_statuses SET active = 0 WHERE id_status = p_id_status;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

-- activar estado de cita
CREATE PROCEDURE sp_appointment_statuses_activate(IN p_id_status INT)
BEGIN
    UPDATE appointment_statuses SET active = 1 WHERE id_status = p_id_status;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- CITAS

-- crear cita
CREATE PROCEDURE sp_appointments_create(
    IN p_id_patient INT,
    IN p_id_dentist_user INT,
    IN p_appointment_date DATE,
    IN p_appointment_time TIME,
    IN p_reason TEXT,
    IN p_duration_minutes INT
)
BEGIN
    INSERT INTO appointments (id_patient, id_dentist_user, appointment_date, 
                              appointment_time, reason, id_appointment_status, duration_minutes)
    VALUES (p_id_patient, p_id_dentist_user, p_appointment_date, 
            p_appointment_time, p_reason, 1, p_duration_minutes);
    
    SELECT LAST_INSERT_ID() AS id_appointment;
END //

-- leer citas
CREATE PROCEDURE sp_appointments_read(IN p_id_appointment INT)
BEGIN
    SELECT a.*, 
           CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
           p.id_card,
           u.full_name AS dentist_name,
           s.status_name
    FROM appointments a
    INNER JOIN patients p ON a.id_patient = p.id_patient
    INNER JOIN users u ON a.id_dentist_user = u.id_user
    INNER JOIN appointment_statuses s ON a.id_appointment_status = s.id_status
    WHERE a.id_appointment = p_id_appointment OR p_id_appointment IS NULL
    ORDER BY a.appointment_date DESC, a.appointment_time;
END //

-- leer citas por fecha
CREATE PROCEDURE sp_appointments_read_by_date(IN p_date DATE)
BEGIN
    SELECT a.*, 
           CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
           u.full_name AS dentist_name,
           s.status_name
    FROM appointments a
    INNER JOIN patients p ON a.id_patient = p.id_patient
    INNER JOIN users u ON a.id_dentist_user = u.id_user
    INNER JOIN appointment_statuses s ON a.id_appointment_status = s.id_status
    WHERE a.appointment_date = p_date
    ORDER BY a.appointment_time;
END //

-- leer citas por paciente
CREATE PROCEDURE sp_appointments_read_by_patient(IN p_id_patient INT)
BEGIN
    SELECT a.*, 
           u.full_name AS dentist_name,
           s.status_name
    FROM appointments a
    INNER JOIN users u ON a.id_dentist_user = u.id_user
    INNER JOIN appointment_statuses s ON a.id_appointment_status = s.id_status
    WHERE a.id_patient = p_id_patient
    ORDER BY a.appointment_date DESC, a.appointment_time;
END //

-- actualizar cita
CREATE PROCEDURE sp_appointments_update(
    IN p_id_appointment INT,
    IN p_id_patient INT,
    IN p_id_dentist_user INT,
    IN p_appointment_date DATE,
    IN p_appointment_time TIME,
    IN p_reason TEXT,
    IN p_id_appointment_status INT,
    IN p_duration_minutes INT
)
BEGIN
    UPDATE appointments 
    SET id_patient = p_id_patient,
        id_dentist_user = p_id_dentist_user,
        appointment_date = p_appointment_date,
        appointment_time = p_appointment_time,
        reason = p_reason,
        id_appointment_status = p_id_appointment_status,
        duration_minutes = p_duration_minutes
    WHERE id_appointment = p_id_appointment;
    
    SELECT ROW_COUNT() AS rows_affected;
END //

-- Cancelar cita
CREATE PROCEDURE sp_appointments_cancel(IN p_id_appointment INT)
BEGIN
    UPDATE appointments 
    SET id_appointment_status = (SELECT id_status FROM appointment_statuses 
                                 WHERE status_name = 'Cancelled')
    WHERE id_appointment = p_id_appointment;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- HISTORIAL MEDICO

-- crear historial medico
CREATE PROCEDURE sp_medical_histories_create(
    IN p_id_patient INT,
    IN p_id_appointment INT,
    IN p_diagnosis TEXT,
    IN p_treatment TEXT,
    IN p_notes TEXT,
    IN p_requires_control BOOLEAN,
    IN p_next_control_date DATE
)
BEGIN
    INSERT INTO medical_histories (id_patient, id_appointment, diagnosis, treatment, notes, requires_control, next_control_date)
    VALUES (p_id_patient, p_id_appointment, p_diagnosis, p_treatment, p_notes, p_requires_control, p_next_control_date);
    
    SELECT LAST_INSERT_ID() AS id_history;
END //

-- leer historiales medicos
CREATE PROCEDURE sp_medical_histories_read(IN p_id_history INT)
BEGIN
    SELECT mh.*, 
           CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
           a.appointment_date, a.appointment_time,
           u.full_name AS dentist_name
    FROM medical_histories mh
    INNER JOIN patients p ON mh.id_patient = p.id_patient
    INNER JOIN appointments a ON mh.id_appointment = a.id_appointment
    INNER JOIN users u ON a.id_dentist_user = u.id_user
    WHERE mh.id_history = p_id_history OR p_id_history IS NULL
    ORDER BY a.appointment_date DESC;
END //

-- leer historiales por paciente
CREATE PROCEDURE sp_medical_histories_read_by_patient(IN p_id_patient INT)
BEGIN
    SELECT mh.*, 
           a.appointment_date, a.appointment_time,
           u.full_name AS dentist_name
    FROM medical_histories mh
    INNER JOIN appointments a ON mh.id_appointment = a.id_appointment
    INNER JOIN users u ON a.id_dentist_user = u.id_user
    WHERE mh.id_patient = p_id_patient
    ORDER BY a.appointment_date DESC;
END //

-- PACIENTES

-- crear paciente
CREATE PROCEDURE sp_patients_create(
    IN p_id_card VARCHAR(20),
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_birth_date DATE,
    IN p_phone VARCHAR(15),
    IN p_email VARCHAR(100),
    IN p_address TEXT
)
BEGIN
    INSERT INTO patients (id_card, first_name, last_name, birth_date, phone, email, address, active)
    VALUES (p_id_card, p_first_name, p_last_name, p_birth_date, p_phone, p_email, p_address, 1);
    
    SELECT LAST_INSERT_ID() AS id_patient;
END //

-- leer pacientes
CREATE PROCEDURE sp_patients_read(
    IN p_id_patient INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM patients 
        WHERE id_patient = p_id_patient OR p_id_patient IS NULL
        ORDER BY first_name, last_name;
    ELSE
        SELECT * FROM patients 
        WHERE (id_patient = p_id_patient OR p_id_patient IS NULL) AND active = 1
        ORDER BY first_name, last_name;
    END IF;
END //

-- buscar pacientes
CREATE PROCEDURE sp_patients_search(
    IN p_search_term VARCHAR(100),
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM patients 
        WHERE first_name LIKE CONCAT('%', p_search_term, '%')
           OR last_name LIKE CONCAT('%', p_search_term, '%')
           OR id_card LIKE CONCAT('%', p_search_term, '%')
           OR email LIKE CONCAT('%', p_search_term, '%')
        ORDER BY first_name, last_name;
    ELSE
        SELECT * FROM patients 
        WHERE (first_name LIKE CONCAT('%', p_search_term, '%')
           OR last_name LIKE CONCAT('%', p_search_term, '%')
           OR id_card LIKE CONCAT('%', p_search_term, '%')
           OR email LIKE CONCAT('%', p_search_term, '%'))
          AND active = 1
        ORDER BY first_name, last_name;
    END IF;
END //

-- actualizar paciente
CREATE PROCEDURE sp_patients_update(
    IN p_id_patient INT,
    IN p_id_card VARCHAR(20),
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_birth_date DATE,
    IN p_phone VARCHAR(15),
    IN p_email VARCHAR(100),
    IN p_address TEXT
)
BEGIN
    UPDATE patients 
    SET id_card = p_id_card,
        first_name = p_first_name,
        last_name = p_last_name,
        birth_date = p_birth_date,
        phone = p_phone,
        email = p_email,
        address = p_address
    WHERE id_patient = p_id_patient;
    
    SELECT ROW_COUNT() AS rows_affected;
END //

-- desactivar paciente
CREATE PROCEDURE sp_patients_deactivate(IN p_id_patient INT)
BEGIN
    UPDATE patients SET active = 0 WHERE id_patient = p_id_patient;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- activar paciente
CREATE PROCEDURE sp_patients_activate(IN p_id_patient INT)
BEGIN
    UPDATE patients SET active = 1 WHERE id_patient = p_id_patient;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- CATEGORÍAS DE PRODUCTOS

CREATE PROCEDURE sp_product_categories_create(
    IN p_category_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO product_categories (category_name, description, active)
    VALUES (p_category_name, p_description, 1);
    SELECT LAST_INSERT_ID() AS id_category;
END //

CREATE PROCEDURE sp_product_categories_read(
    IN p_id_category INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM product_categories 
        WHERE id_category = p_id_category OR p_id_category IS NULL
        ORDER BY category_name;
    ELSE
        SELECT * FROM product_categories 
        WHERE (id_category = p_id_category OR p_id_category IS NULL) AND active = 1
        ORDER BY category_name;
    END IF;
END //

CREATE PROCEDURE sp_product_categories_update(
    IN p_id_category INT,
    IN p_category_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    UPDATE product_categories 
    SET category_name = p_category_name,
        description = p_description
    WHERE id_category = p_id_category;
    SELECT ROW_COUNT() AS rows_affected;
END //

CREATE PROCEDURE sp_product_categories_deactivate(IN p_id_category INT)
BEGIN
    DECLARE product_count INT;
    SELECT COUNT(*) INTO product_count FROM products WHERE id_category = p_id_category AND active = 1;
    IF product_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede desactivar la categoría porque tiene productos activos asociados';
    ELSE
        UPDATE product_categories SET active = 0 WHERE id_category = p_id_category;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

CREATE PROCEDURE sp_product_categories_activate(IN p_id_category INT)
BEGIN
    UPDATE product_categories SET active = 1 WHERE id_category = p_id_category;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- TIPOS DE MOVIMIENTO

CREATE PROCEDURE sp_movement_types_create(
    IN p_type_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO movement_types (type_name, description, active)
    VALUES (p_type_name, p_description, 1);
    SELECT LAST_INSERT_ID() AS id_type;
END //

CREATE PROCEDURE sp_movement_types_read(
    IN p_id_type INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM movement_types 
        WHERE id_type = p_id_type OR p_id_type IS NULL
        ORDER BY type_name;
    ELSE
        SELECT * FROM movement_types 
        WHERE (id_type = p_id_type OR p_id_type IS NULL) AND active = 1
        ORDER BY type_name;
    END IF;
END //

CREATE PROCEDURE sp_movement_types_update(
    IN p_id_type INT,
    IN p_type_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    UPDATE movement_types 
    SET type_name = p_type_name,
        description = p_description
    WHERE id_type = p_id_type;
    SELECT ROW_COUNT() AS rows_affected;
END //

CREATE PROCEDURE sp_movement_types_deactivate(IN p_id_type INT)
BEGIN
    DECLARE movement_count INT;
    SELECT COUNT(*) INTO movement_count FROM inventory_movements WHERE id_movement_type = p_id_type;
    IF movement_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede desactivar el tipo porque tiene movimientos asociados';
    ELSE
        UPDATE movement_types SET active = 0 WHERE id_type = p_id_type;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

CREATE PROCEDURE sp_movement_types_activate(IN p_id_type INT)
BEGIN
    UPDATE movement_types SET active = 1 WHERE id_type = p_id_type;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- ESTADOS DE VENTA

CREATE PROCEDURE sp_sale_statuses_create(
    IN p_status_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO sale_statuses (status_name, description, active)
    VALUES (p_status_name, p_description, 1);
    SELECT LAST_INSERT_ID() AS id_status;
END //

CREATE PROCEDURE sp_sale_statuses_read(
    IN p_id_status INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT * FROM sale_statuses 
        WHERE id_status = p_id_status OR p_id_status IS NULL
        ORDER BY status_name;
    ELSE
        SELECT * FROM sale_statuses 
        WHERE (id_status = p_id_status OR p_id_status IS NULL) AND active = 1
        ORDER BY status_name;
    END IF;
END //

CREATE PROCEDURE sp_sale_statuses_update(
    IN p_id_status INT,
    IN p_status_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    UPDATE sale_statuses 
    SET status_name = p_status_name,
        description = p_description
    WHERE id_status = p_id_status;
    SELECT ROW_COUNT() AS rows_affected;
END //

CREATE PROCEDURE sp_sale_statuses_deactivate(IN p_id_status INT)
BEGIN
    DECLARE sale_count INT;
    SELECT COUNT(*) INTO sale_count FROM sales WHERE id_sale_status = p_id_status;
    IF sale_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede desactivar el estado porque tiene ventas asociadas';
    ELSE
        UPDATE sale_statuses SET active = 0 WHERE id_status = p_id_status;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

CREATE PROCEDURE sp_sale_statuses_activate(IN p_id_status INT)
BEGIN
    UPDATE sale_statuses SET active = 1 WHERE id_status = p_id_status;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- PRODUCTOS

CREATE PROCEDURE sp_products_create(
    IN p_id_category INT,
    IN p_barcode VARCHAR(20),
    IN p_product_name VARCHAR(100),
    IN p_sale_price DECIMAL(10,2),
    IN p_purchase_price DECIMAL(10,2),
    IN p_min_stock INT,
    IN p_measurement_unit VARCHAR(20)
)
BEGIN
    INSERT INTO products (id_category, barcode, product_name, sale_price, purchase_price, min_stock, measurement_unit, active)
    VALUES (p_id_category, p_barcode, p_product_name, p_sale_price, p_purchase_price, p_min_stock, p_measurement_unit, 1);
    SELECT LAST_INSERT_ID() AS id_product;
END //

CREATE PROCEDURE sp_products_read(
    IN p_id_product INT,
    IN p_include_inactive BOOLEAN
)
BEGIN
    IF p_include_inactive THEN
        SELECT p.*, c.category_name 
        FROM products p
        LEFT JOIN product_categories c ON p.id_category = c.id_category
        WHERE p.id_product = p_id_product OR p_id_product IS NULL
        ORDER BY p.product_name;
    ELSE
        SELECT p.*, c.category_name 
        FROM products p
        LEFT JOIN product_categories c ON p.id_category = c.id_category
        WHERE (p.id_product = p_id_product OR p_id_product IS NULL) AND p.active = 1
        ORDER BY p.product_name;
    END IF;
END //

CREATE PROCEDURE sp_products_update(
    IN p_id_product INT,
    IN p_id_category INT,
    IN p_barcode VARCHAR(20),
    IN p_product_name VARCHAR(100),
    IN p_sale_price DECIMAL(10,2),
    IN p_purchase_price DECIMAL(10,2),
    IN p_min_stock INT,
    IN p_measurement_unit VARCHAR(20)
)
BEGIN
    UPDATE products 
    SET id_category = p_id_category,
        barcode = p_barcode,
        product_name = p_product_name,
        sale_price = p_sale_price,
        purchase_price = p_purchase_price,
        min_stock = p_min_stock,
        measurement_unit = p_measurement_unit
    WHERE id_product = p_id_product;
    SELECT ROW_COUNT() AS rows_affected;
END //

CREATE PROCEDURE sp_products_deactivate(IN p_id_product INT)
BEGIN
    DECLARE batch_count INT;
    SELECT COUNT(*) INTO batch_count FROM batches WHERE id_product = p_id_product;
    IF batch_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede desactivar el producto porque tiene lotes asociados';
    ELSE
        UPDATE products SET active = 0 WHERE id_product = p_id_product;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

CREATE PROCEDURE sp_products_activate(IN p_id_product INT)
BEGIN
    UPDATE products SET active = 1 WHERE id_product = p_id_product;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- LOTES (CRUD básico)

CREATE PROCEDURE sp_batches_create(
    IN p_id_product INT,
    IN p_batch_number VARCHAR(50),
    IN p_entry_date DATE,
    IN p_expiration_date DATE,
    IN p_initial_quantity INT,
    IN p_current_quantity INT
)
BEGIN
    INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity)
    VALUES (p_id_product, p_batch_number, p_entry_date, p_expiration_date, p_initial_quantity, p_current_quantity);
    SELECT LAST_INSERT_ID() AS id_batch;
END //

CREATE PROCEDURE sp_batches_read(IN p_id_batch INT)
BEGIN
    SELECT b.*, p.product_name, p.sale_price, p.purchase_price, p.measurement_unit
    FROM batches b
    INNER JOIN products p ON b.id_product = p.id_product
    WHERE b.id_batch = p_id_batch OR p_id_batch IS NULL
    ORDER BY b.expiration_date ASC;
END //

CREATE PROCEDURE sp_batches_read_by_product(IN p_id_product INT)
BEGIN
    SELECT * FROM batches 
    WHERE id_product = p_id_product
    ORDER BY expiration_date ASC;
END //

CREATE PROCEDURE sp_batches_update(
    IN p_id_batch INT,
    IN p_current_quantity INT
)
BEGIN
    UPDATE batches 
    SET current_quantity = p_current_quantity
    WHERE id_batch = p_id_batch;
    SELECT ROW_COUNT() AS rows_affected;
END //

-- MOVIMIENTOS DE INVENTARIO

CREATE PROCEDURE sp_inventory_movements_create(
    IN p_id_user INT,
    IN p_id_batch INT,
    IN p_id_movement_type INT,
    IN p_quantity INT,
    IN p_justification VARCHAR(50)
)
BEGIN
    INSERT INTO inventory_movements (id_user, id_batch, id_movement_type, quantity, justification, movement_date)
    VALUES (p_id_user, p_id_batch, p_id_movement_type, p_quantity, p_justification, NOW());
    SELECT LAST_INSERT_ID() AS id_movement;
END //

CREATE PROCEDURE sp_inventory_movements_read(IN p_id_movement INT)
BEGIN
    SELECT im.*, 
           p.product_name, b.batch_number, mt.type_name,
           u.full_name AS user_name
    FROM inventory_movements im
    INNER JOIN batches b ON im.id_batch = b.id_batch
    INNER JOIN products p ON b.id_product = p.id_product
    INNER JOIN users u ON im.id_user = u.id_user
    INNER JOIN movement_types mt ON im.id_movement_type = mt.id_type
    WHERE im.id_movement = p_id_movement OR p_id_movement IS NULL
    ORDER BY im.movement_date DESC;
END //

-- VENTAS

CREATE PROCEDURE sp_sales_create(
    IN p_id_patient INT,
    IN p_id_user INT,
    IN p_id_appointment INT,
    IN p_subtotal DECIMAL(10,2),
    IN p_tax DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_payment_method VARCHAR(50),
    IN p_id_sale_status INT
)
BEGIN
    INSERT INTO sales (id_patient, id_user, id_appointment, subtotal, tax, total, payment_method, id_sale_status)
    VALUES (p_id_patient, p_id_user, p_id_appointment, p_subtotal, p_tax, p_total, p_payment_method, p_id_sale_status);
    SELECT LAST_INSERT_ID() AS id_sale;
END //

CREATE PROCEDURE sp_sales_read(IN p_id_sale INT)
BEGIN
    SELECT s.*, 
           CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
           u.full_name AS user_name,
           st.status_name
    FROM sales s
    INNER JOIN patients p ON s.id_patient = p.id_patient
    INNER JOIN users u ON s.id_user = u.id_user
    INNER JOIN sale_statuses st ON s.id_sale_status = st.id_status
    WHERE s.id_sale = p_id_sale OR p_id_sale IS NULL
    ORDER BY s.sale_date DESC;
END //

-- DETALLES DE VENTA

CREATE PROCEDURE sp_sale_details_create(
    IN p_id_sale INT,
    IN p_id_product INT,
    IN p_id_movement INT,
    IN p_quantity INT,
    IN p_unit_price DECIMAL(10,2),
    IN p_subtotal DECIMAL(10,2)
)
BEGIN
    INSERT INTO sale_details (id_sale, id_product, id_movement, quantity, unit_price, subtotal)
    VALUES (p_id_sale, p_id_product, p_id_movement, p_quantity, p_unit_price, p_subtotal);
    SELECT LAST_INSERT_ID() AS id_detail;
END //

CREATE PROCEDURE sp_sale_details_read_by_sale(IN p_id_sale INT)
BEGIN
    SELECT sd.*, p.product_name, p.measurement_unit
    FROM sale_details sd
    INNER JOIN products p ON sd.id_product = p.id_product
    WHERE sd.id_sale = p_id_sale
    ORDER BY sd.id_detail;
END //

DELIMITER ;