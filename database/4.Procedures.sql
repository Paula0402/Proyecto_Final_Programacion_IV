USE white_care_db;

DELIMITER //

-- Roles

-- crear rol
CREATE PROCEDURE sp_roles_create(
    IN p_role_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO roles (role_name, description)
    VALUES (p_role_name, p_description);
    
    SELECT LAST_INSERT_ID() AS id_rol;
END //

-- leer roles
CREATE PROCEDURE sp_roles_read(IN p_id_rol INT)
BEGIN
    SELECT * FROM roles 
    WHERE id_rol = p_id_rol OR p_id_rol IS NULL
    ORDER BY role_name;
END //

-- actualizar rol
CREATE PROCEDURE sp_roles_update(
    IN p_id_rol INT,
    IN p_role_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    UPDATE roles 
    SET role_name = p_role_name,
        description = p_description
    WHERE id_rol = p_id_rol;
    
    SELECT ROW_COUNT() AS rows_affected;
END //

-- eliminar rol
CREATE PROCEDURE sp_roles_delete(IN p_id_rol INT)
BEGIN
    -- Verificar si hay usuarios usando este rol
    DECLARE user_count INT;
    
    SELECT COUNT(*) INTO user_count FROM users WHERE id_role = p_id_rol;
    
    IF user_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede eliminar el rol porque tiene usuarios asociados';
    ELSE
        DELETE FROM roles WHERE id_rol = p_id_rol;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

-- Usuarios

-- crear usuario
CREATE PROCEDURE sp_users_create(
    IN p_id_role INT,
    IN p_full_name VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_phone VARCHAR(15)
)
BEGIN
    INSERT INTO users (id_role, full_name, email, password_hash, phone)
    VALUES (p_id_role, p_full_name, p_email, p_password_hash, p_phone);
    
    SELECT LAST_INSERT_ID() AS id_user;
END //

-- leer usuarios
CREATE PROCEDURE sp_users_read(IN p_id_user INT)
BEGIN
    SELECT u.*, r.role_name 
    FROM users u
    INNER JOIN roles r ON u.id_role = r.id_rol
    WHERE u.id_user = p_id_user OR p_id_user IS NULL;
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

-- Eliminar usuario
CREATE PROCEDURE sp_users_delete(IN p_id_user INT)
BEGIN
    DECLARE appointment_count INT;
    DECLARE sale_count INT;
    DECLARE log_count INT;
    DECLARE error_count INT;
    
    SELECT COUNT(*) INTO appointment_count FROM appointments WHERE id_dentist_user = p_id_user;
    SELECT COUNT(*) INTO sale_count FROM sales WHERE id_user = p_id_user;
    SELECT COUNT(*) INTO log_count FROM activity_logs WHERE id_user = p_id_user;
    SELECT COUNT(*) INTO error_count FROM error_logs WHERE id_user = p_id_user;
    
    IF appointment_count > 0 OR sale_count > 0 OR log_count > 0 OR error_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede eliminar el usuario porque tiene actividad registrada';
    ELSE
        DELETE FROM users WHERE id_user = p_id_user;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
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
    INNER JOIN roles r ON u.id_role = r.id_rol
    WHERE u.email = p_email AND u.password_hash = p_password_hash AND u.active = TRUE;
END //

-- Estado de citas

-- crear estado de cita
CREATE PROCEDURE sp_appointment_statuses_create(
    IN p_status_name VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO appointment_statuses (status_name, description)
    VALUES (p_status_name, p_description);
    
    SELECT LAST_INSERT_ID() AS id_status;
END //

-- leer estados de cita
CREATE PROCEDURE sp_appointment_statuses_read(IN p_id_status INT)
BEGIN
    SELECT * FROM appointment_statuses 
    WHERE id_status = p_id_status OR p_id_status IS NULL
    ORDER BY status_name;
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

-- eliminar estado de cita
CREATE PROCEDURE sp_appointment_statuses_delete(IN p_id_status INT)
BEGIN
    DECLARE appointment_count INT;
    
    SELECT COUNT(*) INTO appointment_count FROM appointments WHERE id_appointment_status = p_id_status;
    
    IF appointment_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede eliminar el estado porque hay citas que lo utilizan';
    ELSE
        DELETE FROM appointment_statuses WHERE id_status = p_id_status;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //

-- Citas

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

-- Historial medico

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

-- Pacientes

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
    INSERT INTO patients (id_card, first_name, last_name, birth_date, phone, email, address)
    VALUES (p_id_card, p_first_name, p_last_name, p_birth_date, p_phone, p_email, p_address);
    
    SELECT LAST_INSERT_ID() AS id_patient;
END //

-- leer pacientes
CREATE PROCEDURE sp_patients_read(IN p_id_patient INT)
BEGIN
    SELECT * FROM patients 
    WHERE id_patient = p_id_patient OR p_id_patient IS NULL
    ORDER BY first_name, last_name;
END //

-- buscar pacientes
CREATE PROCEDURE sp_patients_search(IN p_search_term VARCHAR(100))
BEGIN
    SELECT * FROM patients 
    WHERE first_name LIKE CONCAT('%', p_search_term, '%')
       OR last_name LIKE CONCAT('%', p_search_term, '%')
       OR id_card LIKE CONCAT('%', p_search_term, '%')
       OR email LIKE CONCAT('%', p_search_term, '%')
    ORDER BY first_name, last_name;
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

-- eliminar paciente
CREATE PROCEDURE sp_patients_delete(IN p_id_patient INT)
BEGIN
    DECLARE appointment_count INT;
    DECLARE sale_count INT;
    DECLARE history_count INT;
    
    SELECT COUNT(*) INTO appointment_count FROM appointments WHERE id_patient = p_id_patient;
    SELECT COUNT(*) INTO sale_count FROM sales WHERE id_patient = p_id_patient;
    SELECT COUNT(*) INTO history_count FROM medical_histories WHERE id_patient = p_id_patient;
    
    IF appointment_count > 0 OR sale_count > 0 OR history_count > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede eliminar el paciente porque tiene actividad registrada';
    ELSE
        DELETE FROM patients WHERE id_patient = p_id_patient;
        SELECT ROW_COUNT() AS rows_affected;
    END IF;
END //