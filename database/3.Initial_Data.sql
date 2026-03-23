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