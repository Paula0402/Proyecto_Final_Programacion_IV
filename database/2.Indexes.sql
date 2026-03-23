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