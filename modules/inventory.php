<div id="inventory" class="tab-content <?php echo $tab === 'inventory' ? 'active' : ''; ?>">

    <!-- Productos -->
    <section>
        <h2>Productos</h2>
        <table>
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
                <input type="text" id="name_product" class="form-control" placeholder="Ej:Brakets">
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

    <!-- Categorías -->
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
        <div class="form-group">
            <input type="text" id="new_category_name" class="form-control" placeholder="Ej: Cleaning Supplies">
            <button class="btn btn-primary" onclick="crearCategoria()">Save Category</button>
        </div>
    </section>

    <hr>

    <!-- Movimientos -->
    <section>
        <h2>Inventory Movements</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="tablaMovimientos"></tbody>
        </table>

        <h3>Register Movement</h3>
        <div class="form-group">
            <select id="id_movement" class="form-control"></select>
            <select id="id_movements_type" class="form-control">
                <option value="1">Entry</option>
                <option value="2">Exit</option>
            </select>
            <input type="number" id="mov_quantity" class="form-control" placeholder="Quantity">
            <button class="btn btn-primary" onclick="crearMovimiento()">Register</button>
        </div>
    </section>

</div>

<script>
    // Función genérica para peticiones API
    async function apiRequest(url, method = "GET", data = null) {
        const options = {
            method,
            headers: { "Content-Type": "application/json" }
        };
        if (data) options.body = JSON.stringify(data);
        const res = await fetch(url, options);
        return await res.json();
    }

    // --- CARGA INICIAL ---
    async function cargarTodo() {
        await cargarCategorias(); // Cargar categorías primero para que los selects de productos funcionen
        await cargarProductos();
        await cargarMovimientos();
    }

    // --- LÓGICA DE CATEGORÍAS ---
    async function cargarCategorias() {
        const categorias = await apiRequest("api/categories_api.php");
        
        // Llenar tabla de categorías
        const tabla = document.getElementById("tablaCategorias");
        tabla.innerHTML = categorias.map(c => `
            <tr>
                <td>${c.id_category}</td>
                <td>${c.category_name}</td>
                <td>
                    <button onclick="editarCategoria(${c.id_category}, '${c.category_name}')">Edit</button>
                    <button onclick="eliminarCategoria(${c.id_category})">Delete</button>
                </td>
            </tr>
        `).join('');

        // Llenar el select del formulario de productos
        const selectProd = document.getElementById("category_select");
        selectProd.innerHTML = categorias.map(c => `
            <option value="${c.id_category}">${c.category_name}</option>
        `).join('');
    }

    async function crearCategoria() {
        const nombre = document.getElementById("new_category_name").value;
        if(!nombre) return alert("Please enter a name for the category.");

        const res = await apiRequest("api/categories_api.php", "POST", { category_name: nombre });
        alert(res.message);
        document.getElementById("new_category_name").value = "";
        cargarCategorias(); // Refresca tablas y selects
    }

    async function eliminarCategoria(id) {
        if(!confirm("¿Eliminate category? it is affect the other products.")) return;
        const res = await apiRequest("api/categories_api.php", "DELETE", { id_category: id });
        alert(res.message);
        cargarCategorias();
    }

    // --- LÓGICA DE PRODUCTOS ---
let nombresExistentes = [];
let barcodesExistentes = [];

// 1. Función genérica para peticiones (Mantenla igual)
async function apiRequest(url, method = "GET", data = null) {
    const options = {
        method,
        headers: { "Content-Type": "application/json" }
    };
    if (data) options.body = JSON.stringify(data);
    const res = await fetch(url, options);
    return await res.json();
}

// 2. Cargar todo al iniciar
async function cargarTodo() {
    await cargarCategorias(); 
    await cargarProductos();
    await cargarMovimientos();
}

// 3. Cargar Productos (ACTUALIZADA con validación de listas)
async function cargarProductos() {
    const productos = await apiRequest("api/products_api.php");
    const tabla = document.getElementById("tablaInventario");
    
    tabla.innerHTML = productos.map(p => `
        <tr>
            <td><strong>${p.id_product}</strong></td>
            <td>
                <div>${p.product_name}</div>
                <div style="color: #888; font-size: 0.8rem;">${p.barcode || '---'}</div>
            </td>
            <td>${p.category_name || 'out category'}</td>
            <td>
                <div style="color: #28a745;">Sale: $${parseFloat(p.sale_price || 0).toFixed(2)}</div>
                <div style="color: #6c757d; font-size: 0.8rem;">Purchase: $${parseFloat(p.purchase_price || 0).toFixed(2)}</div>
            </td>
            <td>${p.min_stock} ${p.measurement_unit || 'Unid.'}</td>
            <td>
                <button onclick='abrirModalEditar(${JSON.stringify(p)})'>Edit</button>
                <button onclick="eliminarProducto(${p.id_product})">Delete</button>
            </td>
        </tr>
    `).join('');
}

async function eliminarProducto(id) {
    if(!confirm("Do you want to delete this product?")) return;
    const res = await apiRequest("api/products_api.php", "DELETE", { id_product: id });
    alert(res.message);
    cargarProductos();
}

    // Actualizar el select de movimientos también
    const selectMov = document.getElementById("mov_product");
    if(selectMov) {
        selectMov.innerHTML = productos.map(p => `<option value="${p.id_product}">${p.product_name}</option>`).join('');
    }


// 4. Crear Producto (ACTUALIZADA con bloqueo de duplicados)
async function crearProducto() {
    const nombreInput = document.getElementById("name_product").value.trim();
    const barcodeInput = document.getElementById("barcode").value.trim();

    // Validaciones en el cliente
    if (!nombreInput) return alert("The name is required.");

    if (nombresExistentes.includes(nombreInput.toLowerCase())) {
        return alert("¡Error! already exists the product: " + nombreInput);
    }

    if (barcodeInput !== "" && barcodesExistentes.includes(barcodeInput)) {
        return alert("¡Error! The barcode " + barcodeInput + " is already registered.");
    }

    const data = {
        product_name: nombreInput,
        barcode: barcodeInput,
        id_category: document.getElementById("category_select").value,
        purchase_price: document.getElementById("purchase_price").value,
        sale_price: document.getElementById("sale_price").value,
        min_stock: document.getElementById("min_stock").value,
        measurement_unit: document.getElementById("measurement_unit").value
    };

    const res = await apiRequest("api/products_api.php", "POST", data);
    alert(res.message);

    if (!res.message.includes("Error")) {
        // Limpiar formulario
        document.getElementById("name_product").value = "";
        document.getElementById("barcode").value = "";
        document.getElementById("purchase_price").value = "";
        document.getElementById("sale_price").value = "";
        document.getElementById("min_stock").value = "";
        document.getElementById("measurement_unit").value = "";
        
        await cargarProductos(); // Recargar la tabla y las listas de validación
    }
}

// ... Mantén tus funciones de cargarCategorias, eliminarProducto y movimientos abajo ...

document.addEventListener("DOMContentLoaded", cargarTodo);
    

    // --- LÓGICA DE MOVIMIENTOS ---
    async function cargarMovimientos() {
        const movimientos = await apiRequest("api/inventory_movements_api.php");
        const tabla = document.getElementById("tablaMovimientos");
        tabla.innerHTML = movimientos.map(m => `
            <tr>
                <td>${m.id_movement}</td>
                <td>${m.product_name}</td>
                <td>${m.type_name}</td>
                <td>${m.quantity}</td>
                <td>${m.movement_date}</td>
            </tr>
        `).join('');
    }

    async function crearMovimiento() {
        const data = {
            id_product: document.getElementById("id_product").value,
            id_type: document.getElementById("id_movements_type").value,
            quantity: document.getElementById("quantity").value
        };
        const res = await apiRequest("api/inventory_movements_api.php", "POST", data);
        alert(res.message);
        cargarMovimientos();
        cargarProductos();
    }

    document.addEventListener("DOMContentLoaded", cargarTodo);




    // Abrir modal y cargar datos

// --- FUNCIONES PARA PRODUCTOS ---

function abrirModalEditar(p) {
    // Abrimos el modal específico de productos
    document.getElementById("modalEditarProducto").style.display = "block";

    // Llenamos los campos específicos de productos usando prefijos 'edit_prod_'
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
    
    if (!res.message.includes("Error")) {
        cerrarModalProducto();
        cargarProductos();
    }
}

async function editarCategoria(id, nombreActual) {
    const nuevoNombre = prompt("Edit category name:", nombreActual);
    if (nuevoNombre && nuevoNombre !== nombreActual) {
        const res = await apiRequest("api/categories_api.php", "PUT", {
            id_category: id,
            name_category: nuevoNombre
        });
        alert(res.message);
        cargarCategorias();
    }
}
</script>


<div id="modalEditarProducto" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div style="background:white; width:450px; margin:50px auto; padding:20px; border-radius:8px;">
        <h3>Edit Product</h3>
        
        <input type="hidden" id="edit_prod_id">
        
        <label>Name</label>
        <input type="text" id="edit_prod_name" style="width:100%; margin-bottom:10px;">
        
        <label>Barcode</label>
        <input type="text" id="edit_prod_barcode" style="width:100%; margin-bottom:10px;">
        
        <label>Cost Price</label>
        <input type="number" id="edit_prod_purchase" step="0.01" style="width:100%; margin-bottom:10px;">
        
        <label>Sale Price</label>
        <input type="number" id="edit_prod_sale" step="0.01" style="width:100%; margin-bottom:10px;">
        
        <label>Minimum Stock</label>
        <input type="number" id="edit_prod_stock" style="width:100%; margin-bottom:10px;">
        
        <label>Measurement Unit</label>
        <input type="text" id="edit_prod_unit" style="width:100%; margin-bottom:10px;">
        
        <div style="margin-top:15px;">
            <button onclick="guardarEdicionProducto()" class="btn-guardar" style="width:100%; padding:10px;">Update Product</button>
            <button onclick="cerrarModalProducto()" style="background:#ccc; border:none; padding:10px; width:100%; margin-top:5px; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>