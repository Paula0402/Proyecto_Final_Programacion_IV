<div id="sales" class="tab-content <?php echo $tab === 'sales' ? 'active' : ''; ?>">
    <h2>Payments</h2>
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;" onsubmit="prepareSubmit(event)">
        <input type="hidden" name="action" value="create_sale">
        <div class="form-group"><label>Patient</label>
            <select name="sale_patient_id" id="miniPatient" required>
                <option value="">Select patient</option>
                <?php foreach($patients_data as $p){ ?>
                    <option value="<?php echo $p['id_patient']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group"><label>Batch</label>
            <select name="sale_batch_id" id="miniBatch" required onchange="miniSetPrice()">
                <option value="">Select batch</option>
                <?php foreach($batches_data as $b){
                    $prod = null;
                    foreach($products_data as $pp){ if($pp['id_product']==$b['id_product']) $prod = $pp; }
                    $price = $prod ? $prod['sale_price'] : 0;
                    $desc = $b['id_batch']." - ".($b['product_name']?:$b['id_product'])." (Stock: ".$b['current_quantity'].", $".$price.")";
                ?>
                    <option value="<?php echo $b['id_batch']; ?>" data-price="<?php echo $price; ?>"><?php echo htmlspecialchars($desc); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group"><label>Quantity</label><input type="number" step="0.01" id="miniQty" name="sale_quantity" required oninput="miniCalc()"></div>
        <input type="hidden" name="sale_price" id="miniPriceHidden">
        <div class="form-group"><label>Unit Price</label><input type="text" id="miniPrice" readonly></div>
        <input type="hidden" name="subtotal" id="miniSubtotalHidden">
        <input type="hidden" name="tax" id="miniTaxHidden">
        <div class="form-group"><label>Subtotal</label><input type="text" id="miniSubtotal" readonly></div>
        <div class="form-group"><label>Tax</label><input type="text" id="miniTax" readonly></div>
        <div class="form-group"><label>Total</label><input type="text" id="miniTotal" name="total_display" readonly></div>
        <input type="hidden" name="sale_user_id" id="saleUserId" value="<?php echo htmlspecialchars($_SESSION['user_id'] ?? ''); ?>">
        <div class="form-group"><label>Payment Method</label>
            <select name="payment_method" id="paymentMethod" required onchange="togglePhoneField()">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="sinpe">Sinpe</option>
            </select>
        </div>
        <div class="form-group" id="phoneField" style="display:none;">
            <label>Phone Number (for Card/Sinpe)</label>
            <input type="text" name="payment_phone" id="paymentPhone" pattern="[0-9]{8,15}" maxlength="15" placeholder="Enter phone number">
        </div>
        <div class="form-group"><label>Sale Status</label><select name="sale_status" required><option value="">Select Status</option><option value="1">Paid</option><option value="2">Pending</option><option value="3">Refunded</option></select></div>
        <button class="btn btn-primary" type="submit">Register Sale</button>
    </form>

    <h4>Sales</h4>
    <table>
        <thead><tr>
            <th>Sale ID</th>
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Subtotal</th>
            <th>Tax</th>
            <th>Total</th>
            <th>User Name</th>
            <th>Status ID</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Phone</th>
            <th>Date</th>
            <th>Action</th>
        </tr></thead>
        <tbody>
        <?php foreach ($sales_data as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['id_sale']); ?></td>
                <td><?php echo htmlspecialchars($s['id_patient']); ?></td>
                <td><?php echo htmlspecialchars($s['patient_name']); ?></td>
                <td>$<?php echo number_format($s['subtotal'], 2); ?></td>
                <td>$<?php echo number_format($s['tax'], 2); ?></td>
                <td>$<?php echo number_format($s['total'], 2); ?></td>
                <td><?php echo htmlspecialchars($s['user_name']); ?></td>
                <td><?php echo htmlspecialchars($s['id_sale_status'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($s['status']); ?></td>
                <td><?php echo htmlspecialchars($s['payment_method'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($s['payment_phone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($s['sale_date']); ?></td>
                <td>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-small btn-info" onclick="viewSaleInvoice(<?php echo $s['id_sale']; ?>)">View Invoice</button>
                        <button type="button" class="btn btn-small btn-danger" onclick="softDeleteSale(<?php echo $s['id_sale']; ?>)">Delete</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<!-- Modal para factura -->
<div id="invoiceModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div class="invoice-modal">
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
// Mostrar/ocultar campo de teléfono según método de pago
function togglePhoneField() {
    var method = document.getElementById('paymentMethod').value;
    var phoneDiv = document.getElementById('phoneField');
    if (method === 'card' || method === 'sinpe') {
        phoneDiv.style.display = '';
        document.getElementById('paymentPhone').required = true;
    } else {
        phoneDiv.style.display = 'none';
        document.getElementById('paymentPhone').required = false;
        document.getElementById('paymentPhone').value = '';
    }
}


// Calcular subtotal y tax automáticamente (4% de impuesto) para el formulario minimalista
function miniCalc() {
    const quantity = parseFloat(document.getElementById('miniQty').value) || 0;
    const unitPrice = parseFloat(document.getElementById('miniPriceHidden').value) || 0;
    const subtotal = quantity * unitPrice;
    const tax = subtotal * 0.04;
    const total = subtotal + tax;
    document.getElementById('miniSubtotal').value = subtotal > 0 ? '$' + subtotal.toFixed(2) : '';
    document.getElementById('miniTax').value = '$' + tax.toFixed(2);
    document.getElementById('miniTotal').value = total > 0 ? '$' + total.toFixed(2) : '';
    document.getElementById('miniSubtotalHidden').value = subtotal.toFixed(2);
    document.getElementById('miniTaxHidden').value = tax.toFixed(2);
}

// Sincronizar precio al cambiar lote
function miniSetPrice() {
    var sel = document.getElementById('miniBatch');
    var price = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price')) || 0;
    document.getElementById('miniPrice').value = price > 0 ? '$' + price.toFixed(2) : '';
    document.getElementById('miniPriceHidden').value = price.toFixed(2);
    miniCalc();
}

// Inicializar eventos para el formulario minimalista
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('miniBatch').addEventListener('change', miniSetPrice);
    document.getElementById('miniQty').addEventListener('input', miniCalc);
});

// Sincronizar selección de batch y rellenar precio automáticamente
function syncBatchSelection() {
    const batchInput = document.getElementById('batchSearch');
    const batchIdHidden = document.getElementById('batchIdHidden');
    const unitPriceInput = document.getElementById('unitPrice');
    const inputValue = batchInput.value.trim();
    const options = document.querySelectorAll('#batchList option');
    const match = Array.from(options).find(option => inputValue.includes(option.getAttribute('data-id')));
    if (match) {
        batchIdHidden.value = match.getAttribute('data-id');
        const price = parseFloat(match.getAttribute('data-price')) || 0;
        unitPriceInput.value = price.toFixed(2);
    } else {
        batchIdHidden.value = '';
        unitPriceInput.value = '';
    }
    calculateSaleTotal();
}

// Eventos para recalcular automáticamente
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('batchSearch').addEventListener('input', syncBatchSelection);
    document.getElementById('batchSearch').addEventListener('change', syncBatchSelection);
    document.getElementById('quantity').addEventListener('input', calculateSaleTotal);
});
(function() {
    // Evitar conflictos con otros módulos
    if (typeof window.calculateSaleTotal !== 'undefined') return;

    window.calculateSaleTotal = function() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
        const total = quantity * unitPrice;
        
        document.getElementById('totalPrice').value = total > 0 ? '$' + total.toFixed(2) : '';
    };

    // Función para normalizar texto (sin tildes, minúsculas, sin espacios extra)
    function normalizeText(str) {
        return str.normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

  window.syncPatientSelection = function() {
        const input = document.getElementById('patientSearch');
        const hidden = document.getElementById('patientIdHidden');
        const options = document.querySelectorAll('#patientList option');
        const val = normalize(input.value);
        let foundId = "";
        for (const option of options) {
            if (normalize(option.value) === val) {
                foundId = option.getAttribute('data-id_patient') || option.getAttribute('data-id');
                break;
            }
        }
        // Si no hay coincidencia exacta, buscar coincidencia parcial única
        if (!foundId && val.length > 0) {
            let partialMatches = Array.from(options).filter(option => normalize(option.value).includes(val));
            if (partialMatches.length === 1) {
                foundId = partialMatches[0].getAttribute('data-id_patient') || partialMatches[0].getAttribute('data-id');
            }
        }
        hidden.value = foundId;
        return foundId;
    };

    function syncBatchSelection() {
        const batchInput = document.getElementById('batchSearch');
        const batchIdHidden = document.getElementById('batchIdHidden');
        const unitPriceInput = document.getElementById('unitPrice');
        const inputValue = batchInput.value.trim();

        // Buscar coincidencia parcial (el valor incluye ID, nombre, stock y precio)
        const options = document.querySelectorAll('#batchList option');
        const match = Array.from(options).find(option => inputValue.includes(option.getAttribute('data-id')));

        if (match) {
            batchIdHidden.value = match.getAttribute('data-id');
            const price = parseFloat(match.getAttribute('data-price')) || 0;
            unitPriceInput.value = price.toFixed(2);
            calculateSaleTotal();
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
        patientInputEl.addEventListener('blur', function() {
            // Forzar sincronización al salir del campo
            syncPatientSelection();
            if (!document.getElementById('patientIdHidden').value && patientInputEl.value.trim() !== '') {
                alert('Selecciona un paciente válido de la lista.');
                patientInputEl.value = '';
            }
        });
    }

    const batchInputEl = document.getElementById('batchSearch');
    if (batchInputEl) {
        batchInputEl.addEventListener('input', syncBatchSelection);
        batchInputEl.addEventListener('change', syncBatchSelection);
    }

    window.prepareSubmit = function(event) {
        // Forzar sincronización antes de validar, permitiendo coincidencia parcial única
        syncPatientSelection(true);
        syncBatchSelection();
        // Obtener valores de los campos ocultos
        let patientId = document.getElementById('patientIdHidden').value;
        let batchId = document.getElementById('batchIdHidden').value;
        const patientInput = document.getElementById('patientSearch');
        const batchInput = document.getElementById('batchSearch');
        const patientOptions = document.querySelectorAll('#patientList option');
        const batchOptions = document.querySelectorAll('#batchList option');
        // Si el campo oculto sigue vacío, buscar coincidencia exacta o parcial única y asignar el match
        if (!patientId && patientInput && patientOptions.length > 0) {
            const inputValueNorm = normalizeText(patientInput.value);
            let exactMatches = Array.from(patientOptions).filter(option => normalizeText(option.value) === inputValueNorm);
            let partialMatches = Array.from(patientOptions).filter(option => normalizeText(option.value).includes(inputValueNorm));
            if (exactMatches.length === 1) {
                document.getElementById('patientIdHidden').value = exactMatches[0].getAttribute('data-id_patient') || exactMatches[0].getAttribute('data-id');
                patientId = exactMatches[0].getAttribute('data-id_patient') || exactMatches[0].getAttribute('data-id');
            } else if (partialMatches.length === 1) {
                document.getElementById('patientIdHidden').value = partialMatches[0].getAttribute('data-id_patient') || partialMatches[0].getAttribute('data-id');
                patientId = partialMatches[0].getAttribute('data-id_patient') || partialMatches[0].getAttribute('data-id');
            } else {
                // Buscar por coincidencia parcial más larga
                let bestMatch = null;
                let bestLength = 0;
                for (const option of patientOptions) {
                    const norm = normalize(option.value);
                    if (inputValueNorm.length > 2 && norm.includes(inputValueNorm) && inputValueNorm.length > bestLength) {
                        bestMatch = option;
                        bestLength = inputValueNorm.length;
                    }
                }
                if (bestMatch) {
                    document.getElementById('patientIdHidden').value = bestMatch.getAttribute('data-id_patient') || bestMatch.getAttribute('data-id');
                    patientId = bestMatch.getAttribute('data-id_patient') || bestMatch.getAttribute('data-id');
                } else {
                    document.getElementById('patientIdHidden').value = '';
                    patientId = '';
                }
            }
        }
        if (!batchId && batchInput && batchOptions.length > 0) {
            const inputValue = batchInput.value.trim();
            let match = Array.from(batchOptions).find(option => inputValue.includes(option.getAttribute('data-id')));
            if (match) {
                document.getElementById('batchIdHidden').value = match.getAttribute('data-id');
                batchId = match.getAttribute('data-id');
            } else {
                document.getElementById('batchIdHidden').value = '';
                batchId = '';
            }
        }
        // Validar que los campos ocultos de ID estén llenos
        const unitPriceValue = document.getElementById('unitPrice').value;
        const quantityValue = document.getElementById('quantity').value;
        const salePrice = document.querySelector('input[name="sale_price"]');
        let errorMsg = '';
        if (!patientId) {
            errorMsg += 'A valid patient must be selected.\n';
        }
        if (!batchId) {
            errorMsg += 'A valid batch must be selected.\n';
        }
        if (!unitPriceValue || isNaN(parseFloat(unitPriceValue))) {
            errorMsg += 'The unit price is invalid or empty.\n';
        }
        if (!quantityValue || isNaN(parseFloat(quantityValue)) || parseFloat(quantityValue) <= 0) {
            errorMsg += 'The quantity is invalid or empty.\n';
        }
        if (errorMsg) {
            alert(errorMsg);
            event.preventDefault();
            return false;
        }
        if (salePrice) {
            salePrice.value = unitPriceValue;
        }
        // Permitir el envío si todo está correcto
        return true;
    };

    window.softDeleteSale = function(saleId) {
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
    };
})();
</script>



<script>
(function() {
    // Funciones de factura
    window.viewSaleInvoice = function(saleId) {
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
                        detailsHtml += '<td style="text-align:center;padding:10px;">' + parseFloat(detail.quantity || 0).toFixed(2) + '</td>';
                        detailsHtml += '<td style="text-align:right;padding:10px;">$' + parseFloat(detail.unit_price || 0).toFixed(2) + '</td>';
                        detailsHtml += '<td style="text-align:right;padding:10px;">$' + parseFloat(detail.subtotal || 0).toFixed(2) + '</td>';
                        detailsHtml += '</tr>';
                    });
                    
                    detailsHtml += '</tbody></table>';
                } else {
                    detailsHtml = '<p style="text-align:center;color:#666;">No items found for this sale.</p>';
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
                            <p style="margin:5px 0;"><strong>Subtotal:</strong> $${parseFloat(data.subtotal || 0).toFixed(2)}</p>
                            <p style="margin:5px 0;"><strong>Tax:</strong> $${parseFloat(data.tax || 0).toFixed(2)}</p>
                            <p style="margin:10px 0;font-size:18px;"><strong>Total:</strong> $${parseFloat(data.total || 0).toFixed(2)}</p>
                        </div>
                        <div style="border-top:1px solid #ddd;padding-top:16px;margin-top:16px;">
                            <label style="font-weight:bold;">Change Status:</label>
                            <select id="invoiceStatusSelect" style="margin-left:10px;padding:6px 10px;border-radius:4px;border:1px solid #ccc;">
                                <option value="1" ${data.id_sale_status == 1 ? 'selected' : ''}>Paid</option>
                                <option value="2" ${data.id_sale_status == 2 ? 'selected' : ''}>Pending</option>
                                <option value="3" ${data.id_sale_status == 3 ? 'selected' : ''}>Refunded</option>
                            </select>
                            <button type="button" onclick="updateSaleStatus(${data.id_sale})" style="margin-left:10px;padding:6px 14px;background:#0066cc;color:#fff;border:none;border-radius:4px;cursor:pointer;">Save Status</button>
                            <span id="statusUpdateMsg" style="margin-left:10px;color:green;font-weight:bold;"></span>
                        </div>
                    </div>
                `;
                
                contentDiv.innerHTML = invoiceHtml;
            })
            .catch(error => {
                contentDiv.innerHTML = '<p style="color:red;">Error loading invoice: ' + error + '</p>';
            });
    };

    window.updateSaleStatus = function(saleId) {
        const sel = document.getElementById('invoiceStatusSelect');
        const newStatus = parseInt(sel.value);
        const msg = document.getElementById('statusUpdateMsg');
        msg.textContent = 'Saving...';
        msg.style.color = '#555';
        fetch('api/sales.api.php?id=' + saleId, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_sale_status: newStatus })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { msg.textContent = 'Error: ' + data.error; msg.style.color = 'red'; return; }
            msg.textContent = 'Status updated!';
            msg.style.color = 'green';
            setTimeout(() => { msg.textContent = ''; window.location.reload(); }, 1200);
        })
        .catch(e => { msg.textContent = 'Error: ' + e; msg.style.color = 'red'; });
    };

    window.closeSaleInvoice = function() {
        document.getElementById('invoiceModal').style.display = 'none';
    };

    window.printSaleInvoice = function() {
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
    };

    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('invoiceModal');
        if (event.target === modal) {
            closeSaleInvoice();
        }
    });
})();
</script>
