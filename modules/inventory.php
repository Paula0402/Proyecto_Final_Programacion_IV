<div id="inventory" class="tab-content <?php echo $tab === 'inventory' ? 'active' : ''; ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .field-error {
        border-color: #d9534f !important;
        box-shadow: 0 0 0 2px rgba(217, 83, 79, 0.15);
    }

    .field-help {
        display: block;
        min-height: 16px;
        margin-top: 4px;
        margin-bottom: 8px;
        color: #d9534f;
        font-size: 12px;
    }
</style>

    <section>
        <h2>Products</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tablaInventario"></tbody>
        </table>

        <h3>Add Product</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="name_product" class="form-control" placeholder="Ej: Brakets">
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <input type="text" id="barcode" class="form-control" placeholder="12345678">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select id="category_select" class="form-control"></select>
            </div>
            <div class="form-group">
                <label>Purchase Price</label>
                <input type="number" id="purchase_price" class="form-control" placeholder="0.00" step="0.01">
            </div>
            <div class="form-group">
                <label>Sale Price</label>
                <input type="number" id="sale_price" class="form-control" placeholder="0.00" step="0.01">
            </div>
            <div class="form-group">
                <label>Minimum Stock</label>
                <input type="number" id="min_stock" class="form-control" placeholder="0">
            </div>
            <div class="form-group">
                <label>Measurement Unit</label>
                <input type="text" id="measurement_unit" class="form-control" placeholder="Unid, Kg, ml">
            </div>
            <button class="btn btn-primary" onclick="crearProducto()">Save Product</button>
        </div>
    </section>

    <hr>

    <section>
        <h2>Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tablaCategorias"></tbody>
        </table>

        <h3>New Category</h3>
        <div class="form-group d-flex gap-2">
            <input type="text" id="new_category_name" class="form-control" placeholder="Ej: Cleaning Supplies">
            <button class="btn btn-primary" onclick="crearCategoria()">Save Category</button>
        </div>
    </section>

    <hr>

    <section>
        <h2>Batches</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Batch Number</th>
                    <th>Expiration</th>
                    <th>Initial Qty</th>
                    <th>Current Qty</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tablaBatches"></tbody>
        </table>

        <h3>Add Batch</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Product</label>
                <select id="batch_product_select" class="form-control">
                    <option value="">Select Product...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Batch Number</label>
                <input type="text" id="batch_number" class="form-control" placeholder="Ej: LOT-2026-001">
            </div>
            <div class="form-group">
                <label>Expiration Date</label>
                <input type="date" id="batch_expiration_date" class="form-control">
            </div>
            <div class="form-group">
                <label>Initial Quantity</label>
                <input type="number" id="batch_initial_quantity" class="form-control" min="0" placeholder="0">
            </div>
            <button class="btn btn-primary" onclick="crearBatch()">Save Batch</button>
        </div>
    </section>

    <hr>

    <section class="container mt-4">
        <h2>Inventory Movements</h2>
<table class="table table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Product</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Justification</th>
            <th>Date</th>
            <th>Actions</th> </tr>
    </thead>
    <tbody id="movimientos_tbody_principal"></tbody>
        </tbody>
</table>

        <hr>

        <h3>Register Movement</h3>
<div class="form-group d-flex flex-column gap-2">
    <div class="d-flex gap-2">
        <select id="id_user_select" class="form-control">
            <option value="1">admin1</option>
        </select>

        <select id="id_batch_select" class="form-control">
            <option value="">Select Product...</option>
        </select>

        <select id="id_movement_type_select" class="form-control">
            <option value="">Select Type...</option>
        </select>
    </div>
    
    <input type="text" id="mov_justification" class="form-control" placeholder="Justification (e.g., Damaged, New Stock)">

    <div class="d-flex gap-2">
        <input type="number" id="mov_quantity" class="form-control" placeholder="Quantity">
        <button class="btn btn-primary" onclick="crearMovimiento()">Register</button>
    </div>
</div>
</div>

<div id="modalEditarProducto" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div class="modal-content">
        <h3>Edit Product</h3>
        <input type="hidden" id="edit_prod_id">
        <label>Name</label>
        <input type="text" id="edit_prod_name" class="form-control mb-2">
        <label>Barcode</label>
        <input type="text" id="edit_prod_barcode" class="form-control mb-2">
        <label>Cost Price</label>
        <input type="number" id="edit_prod_purchase" step="0.01" class="form-control mb-2">
        <label>Sale Price</label>
        <input type="number" id="edit_prod_sale" step="0.01" class="form-control mb-2">
        <label>Minimum Stock</label>
        <input type="number" id="edit_prod_stock" class="form-control mb-2">
        <label>Measurement Unit</label>
        <input type="text" id="edit_prod_unit" class="form-control mb-2">
        <div class="mt-3">
            <button onclick="guardarEdicionProducto()" class="btn btn-success w-100">Update Product</button>
            <button onclick="cerrarModalProducto()" class="btn btn-secondary w-100 mt-1">Cancel</button>
        </div>
    </div>
</div>

<div id="modalEditarBatch" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div class="modal-content">
        <h3>Edit Batch</h3>
        <input type="hidden" id="edit_batch_id">

        <label>Product</label>
        <select id="edit_batch_product_select" class="form-control mb-2">
            <option value="">Select Product...</option>
        </select>

        <label>Batch Number</label>
        <input type="text" id="edit_batch_number" class="form-control mb-2">

        <label>Expiration Date</label>
        <input type="date" id="edit_batch_expiration_date" class="form-control mb-2">

        <label>Initial Quantity</label>
        <input type="number" id="edit_batch_initial_quantity" class="form-control mb-2" min="0">

        <label>Current Quantity</label>
        <input type="number" id="edit_batch_current_quantity" class="form-control mb-2" min="0">

        <div class="mt-3">
            <button onclick="guardarEdicionBatch()" class="btn btn-success w-100">Update Batch</button>
            <button onclick="cerrarModalBatch()" class="btn btn-secondary w-100 mt-1">Cancel</button>
        </div>
    </div>
</div>

<script>
// --- UTILIDADES ---
async function apiRequest(url, method = "GET", data = null) {
    const options = {
        method,
        headers: { "Content-Type": "application/json" }
    };
    if (data) options.body = JSON.stringify(data);
    const res = await fetch(url, options);
    const responseText = await res.text();

    try {
        return JSON.parse(responseText);
    } catch (error) {
        return {
            error: true,
            message: "Server returned an invalid response. Check PHP errors.",
            raw: responseText
        };
    }
}

// --- CARGA INICIAL ---
document.addEventListener("DOMContentLoaded", async () => {
    await cargarCategorias();
    await cargarTiposMovimiento();
    await cargarProductos();
    await cargarProductosParaBatch();
    await cargarBatches();
    await cargarLotes();
    await cargarMovimientos();
    await cargarUsuarios2();
    inicializarModalBatch();
});

// --- LÓGICA DE CATEGORÍAS ---
async function cargarCategorias() {
    const categorias = await apiRequest("api/categories_api.php");
    
    // Llenar tabla
    const tabla = document.getElementById("tablaCategorias");
    tabla.innerHTML = categorias.map(c => `
        <tr>
            <td>${c.id_category}</td>
            <td>${c.category_name}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="editarCategoria(${c.id_category}, '${c.category_name}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarCategoria(${c.id_category})">Delete</button>
            </td>
        </tr>
    `).join('');

    // Llenar select de productos
    const selectProd = document.getElementById("category_select");
    if(selectProd) {
        selectProd.innerHTML = categorias.map(c => `<option value="${c.id_category}">${c.category_name}</option>`).join('');
    }
}

async function crearCategoria() {
    const nombre = document.getElementById("new_category_name").value.trim();
    if(!nombre) return alert("Please enter a category name.");

    const res = await apiRequest("api/categories_api.php", "POST", { category_name: nombre });
    alert(res.message);
    document.getElementById("new_category_name").value = "";
    cargarCategorias(); 
}

async function eliminarCategoria(id) {
    if(!confirm("Disable category? If it has active products, it cannot be deleted.")) return;
    
    try {
        const response = await fetch("api/categories_api.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_category: id })
        });
        
        const data = await response.json();
        
        // Mostrar el mensaje que venga del servidor
        alert(data.message);
        
        // Si la respuesta es exitosa, recargar la tabla de categorías
        if (response.ok && data.status === "success") {
            cargarCategorias();
        }
    } catch (error) {
        alert("Connection error: " + error);
    }
}

async function editarCategoria(id, nombreActual) {
    const nuevoNombre = prompt("Edit category name:", nombreActual);
    if (nuevoNombre && nuevoNombre !== nombreActual) {
        const res = await apiRequest("api/categories_api.php", "PUT", {
            id_category: id,
            category_name: nuevoNombre
        });
        alert(res.message);
        cargarCategorias();
    }
}

// --- LÓGICA DE PRODUCTOS ---
async function cargarProductos() {
    const productos = await apiRequest("api/products_api.php");
    
    const tabla = document.getElementById("tablaInventario");
    tabla.innerHTML = productos.map(p => `
        <tr>
            <td><strong>${p.id_product}</strong></td>
            <td>
                <div>${p.product_name}</div>
                <small class="text-muted">${p.barcode || '---'}</small>
            </td>
            <td>${p.category_name || 'No category'}</td>
            <td>
                <div class="text-success">Sale: $${parseFloat(p.sale_price || 0).toFixed(2)}</div>
                <small class="text-muted">Cost: $${parseFloat(p.purchase_price || 0).toFixed(2)}</small>
            </td>
            <td>${p.min_stock} ${p.measurement_unit || 'Unid.'}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick='abrirModalEditar(${JSON.stringify(p)})'>Edit</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${p.id_product})">Delete</button>
            </td>
        </tr>
    `).join('');

}

async function cargarLotes() {
    const lotes = await apiRequest("api/batches_api.php", "GET");
    const selectMov = document.getElementById("id_batch_select");

    if (!selectMov) return;

    selectMov.innerHTML = '<option value="">Select Batch...</option>' +
        lotes.map(b => {
            const qty = Number(b.current_quantity || 0);
            const label = `${b.product_name} | Batch ${b.batch_number} | Stock: ${qty}`;
            return `<option value="${b.id_batch}">${label}</option>`;
        }).join('');
}

async function cargarBatches() {
    const lotes = await apiRequest("api/batches_api.php", "GET");
    const tabla = document.getElementById("tablaBatches");

    if (!tabla) return;

    tabla.innerHTML = lotes.map(b => `
        <tr>
            <td>${b.id_batch}</td>
            <td>${b.product_name || "-"}</td>
            <td>${b.batch_number}</td>
            <td>${b.expiration_date || "-"}</td>
            <td>${b.initial_quantity}</td>
            <td>${b.current_quantity}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick='abrirModalEditarBatch(${JSON.stringify(b)})'>Edit</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarBatch(${b.id_batch})">Delete</button>
            </td>
        </tr>
    `).join('');
}

async function cargarProductosParaBatch() {
    const productos = await apiRequest("api/products_api.php", "GET");
    const selectBatchProduct = document.getElementById("batch_product_select");
    const selectBatchProductEdit = document.getElementById("edit_batch_product_select");

    if (!selectBatchProduct && !selectBatchProductEdit) return;

    const optionsHtml = '<option value="">Select Product...</option>' +
        productos.map(p => `<option value="${p.id_product}">${p.product_name}</option>`).join('');

    if (selectBatchProduct) {
        selectBatchProduct.innerHTML = optionsHtml;
    }

    if (selectBatchProductEdit) {
        selectBatchProductEdit.innerHTML = optionsHtml;
    }
}

async function existeBatchDuplicado(idProduct, batchNumber, idBatchExcluir = null) {
    const lotes = await apiRequest("api/batches_api.php", "GET");
    const normalizedBatchNumber = (batchNumber || "").trim().toLowerCase();

    return lotes.some(b => {
        const mismoProducto = Number(b.id_product) === Number(idProduct);
        const mismoBatch = String(b.batch_number || "").trim().toLowerCase() === normalizedBatchNumber;
        const esMismoRegistro = idBatchExcluir !== null && Number(b.id_batch) === Number(idBatchExcluir);
        return mismoProducto && mismoBatch && !esMismoRegistro;
    });
}

async function crearBatch() {
    const data = {
        id_product: document.getElementById("batch_product_select").value,
        batch_number: document.getElementById("batch_number").value.trim(),
        expiration_date: document.getElementById("batch_expiration_date").value,
        initial_quantity: document.getElementById("batch_initial_quantity").value,
        current_quantity: document.getElementById("batch_initial_quantity").value
    };

    if (!data.id_product || !data.batch_number || !data.expiration_date || data.initial_quantity === "") {
        return alert("Fill required fields: Product, Batch Number, Expiration Date and Initial Quantity.");
    }

    if (await existeBatchDuplicado(data.id_product, data.batch_number)) {
        return alert("This batch number already exists for the selected product.");
    }

    const res = await apiRequest("api/batches_api.php", "POST", data);
    alert(res.message || "Batch created.");

    if (!res.error) {
        document.getElementById("batch_product_select").value = "";
        document.getElementById("batch_number").value = "";
        document.getElementById("batch_expiration_date").value = "";
        document.getElementById("batch_initial_quantity").value = "";
        await cargarBatches();
        await cargarLotes();
    }
}

function abrirModalEditarBatch(batch) {
    document.getElementById("modalEditarBatch").style.display = "block";
    document.getElementById("edit_batch_id").value = batch.id_batch;
    document.getElementById("edit_batch_product_select").value = batch.id_product;
    document.getElementById("edit_batch_number").value = batch.batch_number || "";
    document.getElementById("edit_batch_expiration_date").value = batch.expiration_date || "";
    document.getElementById("edit_batch_initial_quantity").value = batch.initial_quantity ?? 0;
    document.getElementById("edit_batch_current_quantity").value = batch.current_quantity ?? 0;
    validarFormularioBatch();
}

function cerrarModalBatch() {
    document.getElementById("modalEditarBatch").style.display = "none";
}

function setBatchFieldError(inputId, helpId, message) {
    const input = document.getElementById(inputId);
    const help = document.getElementById(helpId);
    if (!input || !help) return;

    if (message) {
        input.classList.add("field-error");
        help.textContent = message;
    } else {
        input.classList.remove("field-error");
        help.textContent = "";
    }
}

function validarFormularioBatch() {
    const data = {
        id_product: document.getElementById("edit_batch_product_select").value,
        batch_number: document.getElementById("edit_batch_number").value.trim(),
        expiration_date: document.getElementById("edit_batch_expiration_date").value,
        initial_quantity: document.getElementById("edit_batch_initial_quantity").value,
        current_quantity: document.getElementById("edit_batch_current_quantity").value
    };

    let valido = true;

    if (!data.id_product) {
        setBatchFieldError("edit_batch_product_select", "edit_batch_product_error", "Select a product.");
        valido = false;
    } else {
        setBatchFieldError("edit_batch_product_select", "edit_batch_product_error", "");
    }

    if (!data.batch_number) {
        setBatchFieldError("edit_batch_number", "edit_batch_number_error", "Batch number is required.");
        valido = false;
    } else {
        setBatchFieldError("edit_batch_number", "edit_batch_number_error", "");
    }

    if (!data.expiration_date) {
        setBatchFieldError("edit_batch_expiration_date", "edit_batch_expiration_error", "Expiration date is required.");
        valido = false;
    } else {
        setBatchFieldError("edit_batch_expiration_date", "edit_batch_expiration_error", "");
    }

    if (data.initial_quantity === "" || Number(data.initial_quantity) < 0) {
        setBatchFieldError("edit_batch_initial_quantity", "edit_batch_initial_qty_error", "Initial quantity must be 0 or higher.");
        valido = false;
    } else {
        setBatchFieldError("edit_batch_initial_quantity", "edit_batch_initial_qty_error", "");
    }

    if (data.current_quantity === "" || Number(data.current_quantity) < 0) {
        setBatchFieldError("edit_batch_current_quantity", "edit_batch_current_qty_error", "Current quantity must be 0 or higher.");
        valido = false;
    } else {
        setBatchFieldError("edit_batch_current_quantity", "edit_batch_current_qty_error", "");
    }

    const saveButton = document.getElementById("btnGuardarBatch");
    if (saveButton) {
        saveButton.disabled = !valido;
    }

    return valido;
}

function inicializarModalBatch() {
    const modal = document.getElementById("modalEditarBatch");
    if (!modal) return;

    const fields = [
        "edit_batch_product_select",
        "edit_batch_number",
        "edit_batch_expiration_date",
        "edit_batch_initial_quantity",
        "edit_batch_current_quantity"
    ];

    fields.forEach((id) => {
        const field = document.getElementById(id);
        if (!field) return;
        field.addEventListener("input", validarFormularioBatch);
        field.addEventListener("change", validarFormularioBatch);
    });

    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            cerrarModalBatch();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.style.display === "block") {
            cerrarModalBatch();
        }
    });
}

async function guardarEdicionBatch() {
    if (!validarFormularioBatch()) {
        return;
    }

    const data = {
        id_batch: document.getElementById("edit_batch_id").value,
        id_product: document.getElementById("edit_batch_product_select").value,
        batch_number: document.getElementById("edit_batch_number").value.trim(),
        expiration_date: document.getElementById("edit_batch_expiration_date").value,
        initial_quantity: document.getElementById("edit_batch_initial_quantity").value,
        current_quantity: document.getElementById("edit_batch_current_quantity").value
    };

    const saveButton = document.getElementById("btnGuardarBatch");
    if (saveButton) saveButton.disabled = true;

    if (await existeBatchDuplicado(data.id_product, data.batch_number, data.id_batch)) {
        setBatchFieldError("edit_batch_number", "edit_batch_number_error", "This batch number already exists for this product.");
        if (saveButton) saveButton.disabled = false;
        return alert("This batch number already exists for the selected product.");
    }

    const res = await apiRequest("api/batches_api.php", "PUT", data);
    alert(res.message || "Batch updated.");

    if (!res.error) {
        cerrarModalBatch();
        await cargarBatches();
        await cargarLotes();
    } else {
        if (saveButton) saveButton.disabled = false;
    }
}

async function eliminarBatch(idBatch) {
    if (!idBatch) return alert("Invalid batch ID.");
    if (!confirm("Delete this batch?")) return;

    const res = await apiRequest("api/batches_api.php", "DELETE", { id_batch: idBatch });
    alert(res.message || "Batch deleted.");

    if (!res.error) {
        await cargarBatches();
        await cargarLotes();
    }
}

async function crearProducto() {
    const data = {
        product_name: document.getElementById("name_product").value,
        barcode: document.getElementById("barcode").value,
        id_category: document.getElementById("category_select").value,
        purchase_price: document.getElementById("purchase_price").value,
        sale_price: document.getElementById("sale_price").value,
        min_stock: document.getElementById("min_stock").value,
        measurement_unit: document.getElementById("measurement_unit").value
    };
    const res = await apiRequest("api/products_api.php", "POST", data);
    alert(res.message);
    cargarProductos();
}

async function eliminarProducto(id) {
    if (!id) {
        alert("Invalid product ID.");
        return;
    }
    if (!confirm("Delete this product?")) return;
    
    try {
        const res = await apiRequest("api/products_api.php", "DELETE", { id_product: id });
        alert(res.message);
        cargarProductos();
    } catch (err) {
        alert("Error en la solicitud: " + err);
    }
}

// --- LÓGICA DE MOVIMIENTOS ---


async function cargarUsuarios2() {
    const usuarios = await apiRequest("api/users_api.php");
    const select = document.getElementById('id_user_select');
    if(select) {
        select.innerHTML = '<option value="">Select User...</option>' + 
            usuarios.map(u => `<option value="${u.id_user}">${u.full_name}</option>`).join('');
    }
}
async function cargarTiposMovimiento() {
    const tipos = await apiRequest("api/movement_types_api.php");
    const select = document.getElementById('id_movement_type_select');
    if(select) {
        select.innerHTML = '<option value="">Select Type...</option>' + 
            tipos.map(t => `<option value="${t.id_type}">${t.type_name}</option>`).join('');
    }
}

async function cargarMovimientos() {
    const res = await apiRequest("api/inventory_movements_api.php", "GET");
    
    
    const tbody = document.getElementById("movimientos_tbody_principal");
    
    if (!tbody) return;
    tbody.innerHTML = ""; 

    res.forEach(mov => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${mov.id_movement}</td>
            <td><strong>${mov.user_name}</strong></td> 
            <td>${mov.product_name}</td>
            <td>
                <span class="badge ${mov.type_name === 'Purchase' ? 'bg-success' : 'bg-danger'}">
                    ${mov.type_name}
                </span>
            </td>
            <td>${mov.quantity}</td>
            <td><small>${mov.justification || '-'}</small></td>
            <td>${mov.movement_date}</td>
            <td>
                <button class="btn btn-danger btn-sm" 
                        onclick="eliminarMovimientoSeguro(${mov.id_movement})" 
                        style="min-width: 40px;" 
                        title="Eliminar Movimiento">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function crearMovimiento() {
    const data = {
        id_user: document.getElementById("id_user_select").value,
        id_batch: document.getElementById("id_batch_select").value,
        id_type: document.getElementById("id_movement_type_select").value,
        quantity: document.getElementById("mov_quantity").value,
        justification: document.getElementById("mov_justification").value 
    };

    if(!data.id_batch || !data.id_type || !data.quantity || !data.id_user) {
        return alert("Fill required fields: User, Batch, Type and Quantity");
    }

    const res = await apiRequest("api/inventory_movements_api.php", "POST", data);
    alert(res.message);

    if(!res.error) {
        document.getElementById("mov_quantity").value = "";
        document.getElementById("mov_justification").value = "";
        await cargarMovimientos(); 
        await cargarLotes();
        await cargarProductos();   
    }
}

async function eliminarMovimientoSeguro(id) {
    console.log("Intentando eliminar movimiento ID:", id); // Para depuración
    
    if (!id) return alert("No ID provided");
    if (!confirm("Are you sure you want to delete this transaction? The stock will be reversed.")) return;

    try {
        const res = await apiRequest("api/inventory_movements_api.php", "DELETE", { id_movement: id });
        
        if (res.error) {
            alert("Server error: " + res.message);
        } else {
            alert(res.message);
            await cargarMovimientos(); // Recargar tabla
            await cargarLotes();
            if (window.cargarProductos) await cargarProductos(); // Recargar stock
        }
    } catch (err) {
        console.error("Request error:", err);
        alert("An error occurred while deleting. Please try again.");
    }
}

// --- MODAL EDITAR PRODUCTO ---
function abrirModalEditar(p) {
    document.getElementById("modalEditarProducto").style.display = "block";
    document.getElementById("edit_prod_id").value = p.id_product;
    document.getElementById("edit_prod_name").value = p.product_name;
    document.getElementById("edit_prod_barcode").value = p.barcode || "";
    document.getElementById("edit_prod_purchase").value = p.purchase_price || 0;
    document.getElementById("edit_prod_sale").value = p.sale_price || 0;
    document.getElementById("edit_prod_stock").value = p.min_stock;
    document.getElementById("edit_prod_unit").value = p.measurement_unit || "";
}

function cerrarModalProducto() {
    document.getElementById("modalEditarProducto").style.display = "none";
}

async function guardarEdicionProducto() {
    const data = {
        id_product: document.getElementById("edit_prod_id").value,
        product_name: document.getElementById("edit_prod_name").value,
        barcode: document.getElementById("edit_prod_barcode").value,
        purchase_price: document.getElementById("edit_prod_purchase").value,
        sale_price: document.getElementById("edit_prod_sale").value,
        min_stock: document.getElementById("edit_prod_stock").value,
        measurement_unit: document.getElementById("edit_prod_unit").value
    };
    const res = await apiRequest("api/products_api.php", "PUT", data);
    alert(res.message);
    cerrarModalProducto();
    cargarProductos();
}
</script>


<div id="modalEditarProducto" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div style="background:white; width:450px; margin:50px auto; padding:20px; border-radius:8px;">
        <h3>Edit Product</h3>
        
        <input type="hidden" id="edit_prod_id">
        
        <label>Name</label>
        <input type="text" id="edit_prod_name" style="width:100%; margin-bottom:10px;">
        
        <label>Barcode</label>
        <small id="edit_batch_product_error" class="field-help"></small>
        <input type="text" id="edit_prod_barcode" style="width:100%; margin-bottom:10px;">
        
        <label>Cost Price</label>
        <small id="edit_batch_number_error" class="field-help"></small>
        <input type="number" id="edit_prod_purchase" step="0.01" style="width:100%; margin-bottom:10px;">
        
        <label>Sale Price</label>
        <small id="edit_batch_expiration_error" class="field-help"></small>
        <input type="number" id="edit_prod_sale" step="0.01" style="width:100%; margin-bottom:10px;">
        
        <label>Minimum Stock</label>
        <small id="edit_batch_initial_qty_error" class="field-help"></small>
        <input type="number" id="edit_prod_stock" style="width:100%; margin-bottom:10px;">
        
        <label>Measurement Unit</label>
        <small id="edit_batch_current_qty_error" class="field-help"></small>
        <input type="text" id="edit_prod_unit" style="width:100%; margin-bottom:10px;">
        
            <button id="btnGuardarBatch" onclick="guardarEdicionBatch()" class="btn btn-success w-100">Update Batch</button>
            <button onclick="guardarEdicionProducto()" class="btn-guardar" style="width:100%; padding:10px;">Update Product</button>
            <button onclick="cerrarModalProducto()" style="background:#ccc; border:none; padding:10px; width:100%; margin-top:5px; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>