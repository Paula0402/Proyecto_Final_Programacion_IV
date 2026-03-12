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


-- Usuarios de prueba

-- 1. Administrador (Admin)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (1, 'admin1', 'admin1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88881111', TRUE);

-- 2. Dentista (Dentist)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (2, 'dentist1', 'dentist1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88882222', TRUE);

-- 3. Bodeguero (Warehouse Manager)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (3, 'warehouse1', 'warehouse1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88883333', TRUE);

-- 4. Recepcionista (Receptionist)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (4, 'receptionist1', 'receptionist1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88884444', TRUE);

