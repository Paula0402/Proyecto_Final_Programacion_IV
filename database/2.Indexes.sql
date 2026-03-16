-- Índices para appointments
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_patient ON appointments(id_patient);
CREATE INDEX idx_appointments_dentist ON appointments(id_dentist_user);
CREATE INDEX idx_appointments_status ON appointments(id_appointment_status);
CREATE INDEX idx_appointments_date_status ON appointments(appointment_date, id_appointment_status);

-- Índices para batches
CREATE INDEX idx_batches_product ON batches(id_product);
CREATE INDEX idx_batches_expiry ON batches(expiration_date);
CREATE INDEX idx_batches_current_qty ON batches(current_quantity);
CREATE INDEX idx_batches_product_expiry ON batches(id_product, expiration_date);

-- Índices para sales
CREATE INDEX idx_sales_patient ON sales(id_patient);
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_status ON sales(id_sale_status);
CREATE INDEX idx_sales_date_status ON sales(sale_date, id_sale_status);

-- Índices para inventory_movements
CREATE INDEX idx_movements_batch ON inventory_movements(id_batch);
CREATE INDEX idx_movements_date ON inventory_movements(movement_date);
CREATE INDEX idx_movements_type ON inventory_movements(id_movement_type);
CREATE INDEX idx_movements_batch_date ON inventory_movements(id_batch, movement_date);

-- Índices para users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(id_role);
CREATE INDEX idx_users_active ON users(active);

-- Índices para patients
CREATE INDEX idx_patients_id_card ON patients(id_card);
CREATE INDEX idx_patients_name ON patients(last_name, first_name);
CREATE INDEX idx_patients_email ON patients(email);

-- Índices para products
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_name ON products(product_name);
CREATE INDEX idx_products_category ON products(id_category);
CREATE INDEX idx_products_minstock ON products(min_stock);