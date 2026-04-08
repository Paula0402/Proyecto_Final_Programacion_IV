<div id="sales" class="tab-content <?php echo $tab === 'sales' ? 'active' : ''; ?>">
    <h2>Payments</h2>
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;" onsubmit="prepareSubmit(event)">
        <input type="hidden" name="action" value="create_sale">
        <div class="form-group"><label>Patient</label>
            <input type="text" id="patientSearch" placeholder="Type patient name..." autocomplete="off" list="patientList" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            <datalist id="patientList">
                <?php foreach($patients_data as $p){ ?>
                    <option value="<?php echo htmlspecialchars($p['full_name']); ?>" data-id="<?php echo htmlspecialchars($p['id_patient']); ?>"></option>
                <?php } ?>
            </datalist>
            <input type="hidden" name="sale_patient_id" id="patientIdHidden">
        </div>
        <div class="form-group"><label>Batch</label>
            <input type="text" id="batchSearch" placeholder="Type batch name..." autocomplete="off" list="batchList" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            <datalist id="batchList">
                <?php 
                // Get prices from products for each batch
                $product_prices = [];
                foreach($products_data as $prod) {
                    $product_prices[$prod['id_product']] = $prod['sale_price'] ?? 0;
                }
                foreach($batches_data as $b){
                    $price = $product_prices[$b['id_product']] ?? 0;
                    $title = $b['id_batch'].' - '.($b['product_name']?:$b['id_product']).' (Stock: '.(float)$b['current_quantity'].', Price: $'.$price.')';
                    echo '<option value="'.htmlspecialchars($title).'" data-id="'.htmlspecialchars($b['id_batch']).'" data-price="'.$price.'"></option>';
                } ?>
            </datalist>
            <input type="hidden" name="sale_batch_id" id="batchIdHidden">
        </div>
        <div class="form-group"><label>Quantity</label><input type="number" step="0.01" id="quantity" name="sale_quantity" required oninput="calculateTotal()"></div>
        <div class="form-group"><label>Unit Price</label><input type="number" step="0.01" id="unitPrice" name="sale_price" readonly style="background-color:#f0f0f0;"></div>
        <div class="form-group"><label>Total</label><input type="text" id="totalPrice" name="total_display" readonly style="background-color:#f0f0f0;font-weight:bold;font-size:16px;color:#0066cc;"></div>
        <div class="form-group"><label>Sale Status</label><select name="sale_status" required><option value="">Select Status</option><option value="1">Paid</option><option value="2">Pending</option><option value="3">Refunded</option></select></div>
        <button class="btn btn-primary" type="submit">Register Sale</button>
    </form>

    <h4>Sales</h4>
    <table>
        <thead><tr><th>Sale ID</th><th>Patient ID</th><th>Patient Name</th><th>Total</th><th>User Name</th><th>Status ID</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody><?php foreach ($sales_data as $s): ?><tr><td><?php echo htmlspecialchars($s['id_sale']); ?></td><td><?php echo htmlspecialchars($s['id_patient']); ?></td><td><?php echo htmlspecialchars($s['patient_name']); ?></td><td>$<?php echo number_format(htmlspecialchars($s['total']), 2); ?></td><td><?php echo htmlspecialchars($s['user_name']); ?></td><td><?php echo htmlspecialchars($s['id_sale_status'] ?? 'N/A'); ?></td><td><?php echo htmlspecialchars($s['status']); ?></td><td><?php echo htmlspecialchars($s['sale_date']); ?></td><td><button type="button" class="btn btn-small btn-info" onclick="viewSaleInvoice(<?php echo $s['id_sale']; ?>)">View Invoice</button> <button type="button" class="btn btn-small btn-danger" onclick="softDeleteSale(<?php echo $s['id_sale']; ?>)">Delete</button></td></tr><?php endforeach; ?></tbody>
    </table>

</div>

<!-- Modal para factura -->
<div id="invoiceModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:white;padding:30px;border-radius:8px;max-width:800px;width:90%;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2>Invoice</h2>
            <button type="button" onclick="closeSaleInvoice()" style="background:none;border:none;font-size:24px;cursor:pointer;">&times;</button>
        </div>
        
        <div id="invoiceContent">
            <p style="text-align:center;">Loading...</p>
        </div>
        
        <div style="margin-top:30px;text-align:right;">
            <button type="button" class="btn btn-primary" onclick="printSaleInvoice()">Print</button>
            <button type="button" class="btn btn-secondary" onclick="closeSaleInvoice()">Close</button>
        </div>
    </div>
</div>

<script>
function updatePrice() {
    const batchSelect = document.getElementById('batchSelect');
    const priceInput = document.getElementById('unitPrice');
    const selectedOption = batchSelect.options[batchSelect.selectedIndex];
    
    if (selectedOption.value) {
        const price = selectedOption.getAttribute('data-price');
        priceInput.value = price || 0;
        calculateTotal();
    } else {
        priceInput.value = '';
        document.getElementById('totalPrice').value = '';
    }
}

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
    const total = quantity * unitPrice;
    
    document.getElementById('totalPrice').value = total > 0 ? '$' + total.toFixed(2) : '';
}

function syncPatientSelection() {
    const patientInput = document.getElementById('patientSearch');
    const patientIdHidden = document.getElementById('patientIdHidden');
    const inputValue = patientInput.value.trim();

    const options = document.querySelectorAll('#patientList option');
    const match = Array.from(options).find(option => option.value.toLowerCase() === inputValue.toLowerCase());

    if (match) {
        patientIdHidden.value = match.getAttribute('data-id');
    } else {
        patientIdHidden.value = '';
    }
}

function syncBatchSelection() {
    const batchInput = document.getElementById('batchSearch');
    const batchIdHidden = document.getElementById('batchIdHidden');
    const unitPriceInput = document.getElementById('unitPrice');
    const inputValue = batchInput.value.trim();

    const options = document.querySelectorAll('#batchList option');
    const match = Array.from(options).find(option => option.value.toLowerCase() === inputValue.toLowerCase());

    if (match) {
        batchIdHidden.value = match.getAttribute('data-id');
        const price = parseFloat(match.getAttribute('data-price')) || 0;
        unitPriceInput.value = price.toFixed(2);
        calculateTotal();
    } else {
        batchIdHidden.value = '';
        unitPriceInput.value = '';
        document.getElementById('totalPrice').value = '';
    }
}

const patientInputEl = document.getElementById('patientSearch');
if (patientInputEl) {
    patientInputEl.addEventListener('input', syncPatientSelection);
    patientInputEl.addEventListener('change', syncPatientSelection);
}

const batchInputEl = document.getElementById('batchSearch');
if (batchInputEl) {
    batchInputEl.addEventListener('input', syncBatchSelection);
    batchInputEl.addEventListener('change', syncBatchSelection);
}

function prepareSubmit(event) {
    // Copy the unit price value to the hidden field for submission
    const unitPriceValue = document.getElementById('unitPrice').value;
    const salePrice = document.querySelector('input[name="sale_price"]');
    if (salePrice) {
        salePrice.value = unitPriceValue;
    }
}

function softDeleteSale(saleId) {
    if (!confirm('Confirm soft delete for sale ' + saleId + '?')) return;

    fetch('api/sales.api.php?id=' + saleId, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        alert('Sale soft deleted successfully.');
        window.location.reload();
    })
    .catch(err => {
        alert('Error deleting sale: ' + err);
    });
}
</script>



<script>
function viewSaleInvoice(saleId) {
    const modal = document.getElementById('invoiceModal');
    const contentDiv = document.getElementById('invoiceContent');
    
    modal.style.display = 'flex';
    contentDiv.innerHTML = '<p style="text-align:center;">Loading...</p>';
    
    fetch('api/sales_details_api.php?id=' + saleId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                contentDiv.innerHTML = '<p style="color:red;">Error: ' + data.error + '</p>';
                return;
            }
            
            let detailsHtml = '';
            if (data.details && data.details.length > 0) {
                detailsHtml = '<table style="width:100%;border-collapse:collapse;margin:20px 0;">';
                detailsHtml += '<thead><tr style="border-bottom:2px solid #333;"><th style="text-align:left;padding:10px;">Product</th><th style="text-align:center;padding:10px;">Qty</th><th style="text-align:right;padding:10px;">Unit Price</th><th style="text-align:right;padding:10px;">Subtotal</th></tr></thead>';
                detailsHtml += '<tbody>';
                
                data.details.forEach(detail => {
                    detailsHtml += '<tr style="border-bottom:1px solid #ddd;">';
                    detailsHtml += '<td style="padding:10px;">' + (detail.product_name || 'N/A') + '</td>';
                    detailsHtml += '<td style="text-align:center;padding:10px;">' + parseFloat(detail.quantity).toFixed(2) + '</td>';
                    detailsHtml += '<td style="text-align:right;padding:10px;">$' + parseFloat(detail.unit_price).toFixed(2) + '</td>';
                    detailsHtml += '<td style="text-align:right;padding:10px;">$' + parseFloat(detail.subtotal).toFixed(2) + '</td>';
                    detailsHtml += '</tr>';
                });
                
                detailsHtml += '</tbody></table>';
            }
            
            const invoiceHtml = `
                <div style="border:1px solid #ddd;padding:20px;border-radius:5px;">
                    <div style="text-align:center;margin-bottom:20px;">
                        <h3>INVOICE</h3>
                        <p>Sale ID: ${data.id_sale}</p>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0;">
                        <div>
                            <h4>Patient Information</h4>
                            <p><strong>Name:</strong> ${data.patient_name || 'N/A'}</p>
                            <p><strong>ID:</strong> ${data.id_patient || 'N/A'}</p>
                            <p><strong>ID Card:</strong> ${data.id_card || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                        </div>
                        <div>
                            <h4>Sale Information</h4>
                            <p><strong>Date:</strong> ${data.sale_date || 'N/A'}</p>
                            <p><strong>Attended by:</strong> ${data.user_name || 'N/A'}</p>
                            <p><strong>Payment Method:</strong> ${data.payment_method || 'N/A'}</p>
                            <p><strong>Status:</strong> ${data.status || 'N/A'}</p>
                        </div>
                    </div>
                    
                    <h4>Items</h4>
                    ${detailsHtml}
                    
                    <div style="border-top:2px solid #333;padding-top:20px;margin-top:20px;text-align:right;">
                        <p style="margin:5px 0;"><strong>Subtotal:</strong> $${parseFloat(data.subtotal).toFixed(2)}</p>
                        <p style="margin:5px 0;"><strong>Tax:</strong> $${parseFloat(data.tax).toFixed(2)}</p>
                        <p style="margin:10px 0;font-size:18px;"><strong>Total:</strong> $${parseFloat(data.total).toFixed(2)}</p>
                    </div>
                </div>
            `;
            
            contentDiv.innerHTML = invoiceHtml;
        })
        .catch(error => {
            contentDiv.innerHTML = '<p style="color:red;">Error loading invoice: ' + error + '</p>';
        });
}

function closeSaleInvoice() {
    document.getElementById('invoiceModal').style.display = 'none';
}

function printSaleInvoice() {
    const modal = document.getElementById('invoiceModal');
    const content = document.getElementById('invoiceContent').innerHTML;
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Invoice</title><style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin: 20px 0; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(event) {
    const modal = document.getElementById('invoiceModal');
    if (event.target === modal) {
        closeSaleInvoice();
    }
});
</script>
