-- Datos iniciales de prueba

-- Roles del sistema
INSERT INTO roles (role_name, description) VALUES 
('Admin', 'Acceso total al sistema'),
('Dentist', 'Gestión clínica e historial de pacientes'),
('Warehouse Manager', 'Control de inventario y stock'),
('Receptionist', 'Gestión de citas y ventas');

-- Estados de las citas
INSERT INTO appointment_statuses (status_name, description) VALUES 
('Scheduled', 'Cita agendada pero aún no atendida'),
('Attended', 'El paciente fue atendido y se registró su historial'),
('Cancelled', 'Cita cancelada por el paciente o la clínica'),
('No-Show', 'El paciente no se presentó a la cita');

-- Estados de las ventas
INSERT INTO sale_statuses (status_name, description) VALUES 
('Paid', 'Transacción completada con éxito'),
('Pending', 'Factura generada pero aún no pagada'),
('Refunded', 'Pago devuelto al cliente');

-- Tipos de movimientos de inventario
INSERT INTO movement_types (type_name, description) VALUES 
('Purchase', 'Nuevo stock recibido de proveedor'),
('Sale', 'Stock reducido por compra de cliente'),
('Adjustment - Loss', 'Reducción manual por daño o robo'),
('Adjustment - Expiry', 'Reducción manual por producto vencido'),
('Internal Use', 'Materiales utilizados durante procedimientos clínicos');

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

-- Pacients
INSERT INTO patients (id_card, first_name, last_name, birth_date, phone, email, address) VALUES
('101230101', 'Carlos', 'Rodríguez Méndez', '1985-03-15', '612345678', 'carlos.rodriguez@email.com', 'Av. Principal 123, Madrid'),
('202340202', 'María', 'García López', '1990-07-22', '698765432', 'maria.garcia@email.com', 'Calle Mayor 45, Barcelona'),
('303450303', 'Juan', 'Martínez Sánchez', '1978-11-08', '655443322', 'juan.martinez@email.com', 'Plaza Central 7, Valencia'),
('404560404', 'Ana', 'Fernández Pérez', '1982-05-19', '677889900', 'ana.fernandez@email.com', 'Calle Real 89, Sevilla'),
('505670505', 'Miguel', 'López González', '1995-09-30', '622334455', 'miguel.lopez@email.com', 'Av. del Mar 234, Málaga'),
('606780606', 'Laura', 'Sánchez Díaz', '1988-12-12', '611223344', 'laura.sanchez@email.com', 'Calle Nueva 12, Bilbao'),
('707890707', 'David', 'Pérez Moreno', '1975-04-25', '699887766', 'david.perez@email.com', 'Paseo de la Castellana 567, Madrid'),
('808900808', 'Elena', 'Gómez Ruiz', '1992-08-14', '644556677', 'elena.gomez@email.com', 'Calle Ancha 34, Granada'),
('909011909', 'Javier', 'Álvarez Jiménez', '1980-01-07', '633445566', 'javier.alvarez@email.com', 'Rambla Catalunya 78, Barcelona'),
('101122101', 'Sara', 'Romero Navarro', '1987-06-21', '677112233', 'sara.romero@email.com', 'Calle San Miguel 56, Zaragoza'),
('111233111', 'Pablo', 'Torres Castro', '1993-10-03', '688990011', 'pablo.torres@email.com', 'Av. América 123, Valladolid'),
('121344121', 'Marta', 'Ruiz Ortega', '1984-02-28', '622178900', 'marta.ruiz@email.com', 'Calle del Sol 9, Alicante'),
('131455131', 'Daniel', 'Vázquez Serrano', '1972-09-17', '655667788', 'daniel.vazquez@email.com', 'Plaza Mayor 3, Salamanca'),
('141566141', 'Carmen', 'Jiménez Muñoz', '1991-12-05', '644332211', 'carmen.jimenez@email.com', 'Calle Larga 22, Córdoba'),
('151677151', 'Alejandro', 'Díaz García', '1983-07-11', '611445566', 'alejandro.diaz@email.com', 'Av. de la Paz 432, Murcia'),
('161788161', 'Isabel', 'Hernández Blanco', '1976-03-29', '699112233', 'isabel.hernandez@email.com', 'Calle Estrecha 15, Oviedo'),
('171899171', 'Raúl', 'Santos Vidal', '1989-05-16', '677883344', 'raul.santos@email.com', 'Ronda Norte 234, Santander'),
('182000182', 'Patricia', 'Castro Molina', '1994-11-23', '622455667', 'patricia.castro@email.com', 'Calle Verde 67, Logroño'),
('192111192', 'Jorge', 'Ortega Fuentes', '1981-08-09', '655990011', 'jorge.ortega@email.com', 'Av. de la Constitución 89, Pamplona'),
('202222202', 'Nuria', 'Molina Aguirre', '1986-04-18', '688773322', 'nuria.molina@email.com', 'Calle Real 123, San Sebastián');

-- categorías de productos
INSERT INTO product_categories (category_name, description) VALUES 
('Materiales de protección', 'Materiales de protección'),
('Materiales de tratamiento dental', 'Materiales de tratamiento dental'),
('Materiales de anestesia', 'Materiales de anestesia'),
('Instrumental desechable', 'Instrumental desechable'),
('Materiales de limpieza y esterilización', 'Materiales de limpieza y esterilización'),
('Materiales de impresión dental', 'Materiales de impresión dental'),
('Otros insumos comunes', 'Otros insumos comunes'),
('Productos de higiene diaria', 'Productos de higiene diaria'),
('Productos para ortodoncia', 'Productos para ortodoncia'),
('Productos post-tratamiento', 'Productos post-tratamiento'),
('Productos para blanqueamiento', 'Productos para blanqueamiento'),
('Productos para prótesis dentales', 'Productos para prótesis dentales');

-- Productos

-- Materiales de protección (id_category = 1)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(1, 'Guantes desechables (caja 100u)', 8500, 4500, 5, 'Caja'),
(1, 'Mascarillas quirúrgicas (caja 50u)', 6500, 3500, 5, 'Caja'),
(1, 'Caretas de protección', 4800, 2200, 5, 'Unidad'),
(1, 'Gorros desechables (paq 10u)', 2400, 1200, 5, 'Paquete'),
(1, 'Batas desechables (paq 5u)', 11800, 6700, 5, 'Paquete');

-- Materiales de tratamiento dental (id_category = 2)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(2, 'Resina dental (jeringa)', 24200, 15000, 5, 'Jeringa'),
(2, 'Amalgama dental (cápsulas 50u)', 47800, 27900, 5, 'Caja'),
(2, 'Cemento dental (frasco)', 17400, 9600, 5, 'Frasco'),
(2, 'Ácido grabador (jeringa 3ml)', 9700, 5100, 5, 'Jeringa'),
(2, 'Adhesivo dental (frasco 5ml)', 20400, 11800, 5, 'Frasco'),
(2, 'Ionómero de vidrio (kit)', 36000, 22000, 5, 'Kit');

-- Materiales de anestesia (id_category = 3)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(3, 'Anestesia local (caja 50 carpules)', 45700, 27900, 5, 'Caja'),
(3, 'Agujas para anestesia (caja 100u)', 15000, 8000, 5, 'Caja'),
(3, 'Carpules de anestesia (estuche 50u)', 41900, 24200, 5, 'Estuche');

-- Instrumental desechable (id_category = 4)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(4, 'Jeringas desechables (caja 100u)', 9700, 4800, 5, 'Caja'),
(4, 'Vasos desechables (paq 50u)', 2100, 1000, 5, 'Paquete'),
(4, 'Baberos dentales (paq 100u)', 6500, 3200, 5, 'Paquete'),
(4, 'Gasas estériles (paq 100u)', 4300, 2100, 5, 'Paquete'),
(4, 'Algodón dental (bolsa 500g)', 5400, 2800, 5, 'Bolsa');

-- Materiales de limpieza y esterilización (id_category = 5)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(5, 'Alcohol (galón)', 6500, 3500, 5, 'Galón'),
(5, 'Hipoclorito (cloro) (litro)', 1800, 800, 5, 'Litro'),
(5, 'Desinfectante de superficies (litro)', 4300, 2200, 5, 'Litro'),
(5, 'Bolsas de esterilización (paq 200u)', 8600, 4800, 5, 'Paquete'),
(5, 'Indicadores de esterilización (tira 250u)', 15000, 8600, 5, 'Caja');

-- Materiales de impresión dental (id_category = 6)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(6, 'Alginato (bolsa 500g)', 11800, 6500, 5, 'Bolsa'),
(6, 'Silicona de impresión (kit)', 37600, 21500, 5, 'Kit'),
(6, 'Cubetas de impresión (juego 5 tallas)', 25800, 14000, 5, 'Juego');

-- Otros insumos comunes (id_category = 7)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(7, 'Hilo dental (caja 50u)', 8600, 4300, 5, 'Caja'),
(7, 'Pasta profiláctica (frasco)', 5400, 2800, 5, 'Frasco'),
(7, 'Cepillos dentales (unidad)', 3200, 1500, 5, 'Unidad'),
(7, 'Fluoruro (frasco 250ml)', 9700, 5200, 5, 'Frasco'),
(7, 'Rollos de algodón (paq 100u)', 3800, 1900, 5, 'Paquete');

-- Productos de higiene diaria (id_category = 8)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(8, 'Cepillo dental (unidad)', 3200, 1500, 5, 'Unidad'),
(8, 'Cepillo dental eléctrico', 25800, 14000, 5, 'Unidad'),
(8, 'Hilo dental (unidad)', 2100, 900, 5, 'Unidad'),
(8, 'Pasta dental (tubo 90ml)', 2800, 1300, 5, 'Tubo'),
(8, 'Enjuague bucal (500ml)', 4300, 2200, 5, 'Botella'),
(8, 'Cepillos interdentales (paq 6u)', 3800, 1800, 5, 'Paquete'),
(8, 'Limpiador de lengua', 2500, 1100, 5, 'Unidad');

-- Productos para ortodoncia (id_category = 9)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(9, 'Cera para brackets (caja 6u)', 4300, 2100, 5, 'Caja'),
(9, 'Cepillo especial para ortodoncia', 3800, 1900, 5, 'Unidad'),
(9, 'Hilo dental para brackets (caja 10u)', 5400, 2800, 5, 'Caja'),
(9, 'Enjuague bucal especial ortodoncia (500ml)', 5400, 2800, 5, 'Botella');

-- Productos post-tratamiento (id_category = 10)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(10, 'Gel desensibilizante dental (tubo)', 8600, 4500, 5, 'Tubo'),
(10, 'Enjuague bucal con clorhexidina (500ml)', 6500, 3400, 5, 'Botella'),
(10, 'Pasta dental para dientes sensibles (tubo)', 3800, 1900, 5, 'Tubo'),
(10, 'Gel de fluoruro (tubo)', 5400, 2800, 5, 'Tubo'),
(10, 'Analgésico (caja 20 tabletas)', 4300, 2100, 5, 'Caja');

-- Productos para blanqueamiento (id_category = 11)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(11, 'Kit de blanqueamiento dental', 64500, 38700, 5, 'Kit'),
(11, 'Gel blanqueador (jeringa)', 21500, 11800, 5, 'Jeringa'),
(11, 'Jeringas de blanqueamiento (paq 10u)', 8600, 4300, 5, 'Paquete');

-- Productos para prótesis dentales (id_category = 12)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(12, 'Adhesivo para dentaduras (tubo)', 5400, 2800, 5, 'Tubo'),
(12, 'Pastillas limpiadoras de prótesis (caja 30u)', 6500, 3400, 5, 'Caja'),
(12, 'Cepillo para prótesis dentales', 3200, 1500, 5, 'Unidad');

-- Lotes

-- Materiales de protección (id_category = 1) - Guantes desechables
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(1, 'LOTE-GUANTES-001', '2025-01-10', '2026-01-10', 1000, 850),
(1, 'LOTE-GUANTES-002', '2025-03-15', '2026-03-15', 1000, 1000);

-- Materiales de tratamiento dental (id_category = 2) - Resina dental
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(6, 'LOTE-RESINA-001', '2025-01-15', '2026-01-15', 150, 120),
(6, 'LOTE-RESINA-002', '2025-03-01', '2026-03-01', 150, 150);

-- Materiales de anestesia (id_category = 3) - Anestesia local
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(12, 'LOTE-ANESTESIA-001', '2025-01-05', '2025-12-05', 300, 250),
(12, 'LOTE-ANESTESIA-002', '2025-03-01', '2026-02-01', 300, 300);

-- Instrumental desechable (id_category = 4) - Jeringas desechables
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(15, 'LOTE-JERINGAS-001', '2025-01-12', '2026-01-12', 600, 520),
(15, 'LOTE-JERINGAS-002', '2025-03-12', '2026-03-12', 600, 600);

-- Materiales de limpieza y esterilización (id_category = 5) - Alcohol
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(20, 'LOTE-ALCOHOL-001', '2025-01-10', '2026-01-10', 200, 150),
(20, 'LOTE-ALCOHOL-002', '2025-03-10', '2026-03-10', 200, 200);

-- Materiales de impresión dental (id_category = 6) - Alginato
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(25, 'LOTE-ALGINATO-001', '2025-01-08', '2025-11-08', 120, 95),
(25, 'LOTE-ALGINATO-002', '2025-03-08', '2026-01-08', 120, 120);

-- Otros insumos comunes (id_category = 7) - Hilo dental
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(28, 'LOTE-HILO-001', '2025-01-12', '2026-01-12', 300, 220),
(28, 'LOTE-HILO-002', '2025-03-12', '2026-03-12', 300, 300);

-- Productos de higiene diaria (id_category = 8) - Cepillo dental
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(35, 'LOTE-CEPILLO-001', '2025-01-05', '2027-01-05', 150, 130),
(35, 'LOTE-CEPILLO-002', '2025-03-05', '2027-03-05', 150, 150);

-- Productos para ortodoncia (id_category = 9) - Cera para brackets
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(42, 'LOTE-CERA-001', '2025-01-10', '2026-07-10', 200, 180),
(42, 'LOTE-CERA-002', '2025-03-10', '2026-09-10', 200, 200);

-- Productos post-tratamiento (id_category = 10) - Gel desensibilizante
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(46, 'LOTE-GEL-001', '2025-01-08', '2026-01-08', 80, 60),
(46, 'LOTE-GEL-002', '2025-03-08', '2026-03-08', 80, 80);

-- Productos para blanqueamiento (id_category = 11) - Kit de blanqueamiento
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(51, 'LOTE-BLANCO-001', '2025-01-15', '2026-01-15', 30, 25),
(51, 'LOTE-BLANCO-002', '2025-03-15', '2026-03-15', 30, 30);

-- Productos para prótesis dentales (id_category = 12) - Adhesivo para dentaduras
INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES
(54, 'LOTE-ADHESIVO-001', '2025-01-12', '2026-01-12', 100, 85),
(54, 'LOTE-ADHESIVO-002', '2025-03-12', '2026-03-12', 100, 100);