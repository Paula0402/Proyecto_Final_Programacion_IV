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


-- Activity Logs

-- Ingresar actividad
CREATE PROCEDURE sp_activity_logs_create(
    IN p_id_user INT,
    IN p_action VARCHAR(50),
    IN p_affected_table VARCHAR(50),
    IN p_record_id INT,
    IN p_old_value TEXT,
    IN p_new_value TEXT
)
BEGIN
    INSERT INTO activity_logs (id_user, action, affected_table, record_id, old_value, new_value)
    VALUES (p_id_user, p_action, p_affected_table, p_record_id, p_old_value, p_new_value);
    
    SELECT LAST_INSERT_ID() AS id_log;
END //

-- leer logs de actividad
CREATE PROCEDURE sp_activity_logs_read(
    IN p_id_user INT,
    IN p_action VARCHAR(50),
    IN p_affected_table VARCHAR(50),
    IN p_record_id INT,
    IN p_from_date DATETIME,
    IN p_to_date DATETIME,
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    DECLARE v_limit INT DEFAULT 999999999;
    DECLARE v_offset INT DEFAULT 0;
    
    IF p_limit IS NOT NULL THEN
        SET v_limit = p_limit;
    END IF;
    
    IF p_offset IS NOT NULL THEN
        SET v_offset = p_offset;
    END IF;
    
    SELECT 
        al.id_log,
        al.id_user,
        u.full_name AS user_name,
        al.action,
        al.affected_table,
        al.record_id,
        al.old_value,
        al.new_value,
        al.activity_date
    FROM activity_logs al
    LEFT JOIN users u ON al.id_user = u.id_user
    WHERE (p_id_user IS NULL OR al.id_user = p_id_user)
      AND (p_action IS NULL OR al.action = p_action)
      AND (p_affected_table IS NULL OR al.affected_table = p_affected_table)
      AND (p_record_id IS NULL OR al.record_id = p_record_id)
      AND (p_from_date IS NULL OR al.activity_date >= p_from_date)
      AND (p_to_date IS NULL OR al.activity_date <= p_to_date)
    ORDER BY al.activity_date DESC
    LIMIT v_limit OFFSET v_offset;
END //

-- error logs

-- leer con filtros y paginación
CREATE PROCEDURE sp_error_logs_read(
    IN p_id_user INT,
    IN p_error_message_substr VARCHAR(255),
    IN p_from_date DATETIME,
    IN p_to_date DATETIME,
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    DECLARE v_limit INT DEFAULT 999999999;
    DECLARE v_offset INT DEFAULT 0;
    
    IF p_limit IS NOT NULL THEN
        SET v_limit = p_limit;
    END IF;
    
    IF p_offset IS NOT NULL THEN
        SET v_offset = p_offset;
    END IF;
    
    SELECT 
        el.id_error,
        el.id_user,
        u.full_name AS user_name,
        el.procedure_name,
        el.error_code,
        el.error_message,
        el.error_date
    FROM error_logs el
    LEFT JOIN users u ON el.id_user = u.id_user
    WHERE (p_id_user IS NULL OR el.id_user = p_id_user)
      AND (p_error_message_substr IS NULL OR el.error_message LIKE CONCAT('%', p_error_message_substr, '%'))
      AND (p_from_date IS NULL OR el.error_date >= p_from_date)
      AND (p_to_date IS NULL OR el.error_date <= p_to_date)
    ORDER BY el.error_date DESC
    LIMIT v_limit OFFSET v_offset;
END //

-- Procedimiento para contar errores
CREATE PROCEDURE sp_error_logs_count(
    IN p_id_user INT,
    IN p_error_message_substr VARCHAR(255),
    IN p_from_date DATETIME,
    IN p_to_date DATETIME,
    OUT p_total INT
)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM error_logs el
    WHERE (p_id_user IS NULL OR el.id_user = p_id_user)
      AND (p_error_message_substr IS NULL OR el.error_message LIKE CONCAT('%', p_error_message_substr, '%'))
      AND (p_from_date IS NULL OR el.error_date >= p_from_date)
      AND (p_to_date IS NULL OR el.error_date <= p_to_date);
END //

DELIMITER ;