<div id="sales" class="tab-content <?php echo $tab === 'sales' ? 'active' : ''; ?>">
    <h2>Sales and Payments</h2>
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;">
        <input type="hidden" name="action" value="create_sale">
        <div class="form-group"><label>Pacient</label><select name="sale_patient_id" required><option value="">Select</option><?php foreach($patients_data as $p){echo '<option value="'.htmlspecialchars($p['id_patient']).'">'.htmlspecialchars($p['full_name']).'</option>';} ?></select></div>
        <div class="form-group"><label>Batch</label><select name="sale_batch_id" required><option value="">Select</option><?php foreach($batches_data as $b){echo '<option value="'.htmlspecialchars($b['id_batch']).'">'.htmlspecialchars($b['id_batch'].' - '.($b['product_name']?:$b['id_product']).' ('.(float)$b['current_quantity'].')').'</option>';} ?></select></div>
        <div class="form-group"><label>Quantity</label><input type="number" step="0.01" name="sale_quantity" required></div>
        <div class="form-group"><label>Unit Price</label><input type="number" step="0.01" name="sale_price" required></div>
        <button class="btn btn-primary" type="submit">Register Sale</button>
    </form>

    <h4>Sales</h4>
    <table>
        <thead><tr><th>ID</th><th>Pacient</th><th>Total</th><th>User</th><th>Date</th></tr></thead>
        <tbody><?php foreach ($sales_data as $s): ?><tr><td><?php echo htmlspecialchars($s['id_sale']); ?></td><td><?php echo htmlspecialchars($s['patient_name']); ?></td><td><?php echo htmlspecialchars($s['total']); ?></td><td><?php echo htmlspecialchars($s['user_name']); ?></td><td><?php echo htmlspecialchars($s['sale_date']); ?></td></tr><?php endforeach; ?></tbody>
    </table>

    <h4>Sale Details</h4>
    <table>
        <thead><tr><th>Sale</th><th>Product</th><th>Quantity</th><th>Price</th></tr></thead>
        <tbody><?php foreach ($sale_details as $d): ?><tr><td><?php echo htmlspecialchars($d['id_sale']); ?></td><td><?php echo htmlspecialchars($d['product_name']); ?></td><td><?php echo htmlspecialchars($d['quantity']); ?></td><td><?php echo htmlspecialchars($d['unit_price']); ?></td></tr><?php endforeach; ?></tbody>
    </table>
</div>
