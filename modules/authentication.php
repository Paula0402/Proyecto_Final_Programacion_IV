<div id="authentication" class="tab-content <?php echo $tab === 'authentication' ? 'active' : ''; ?>">

    <section>
        <h2>Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios"></tbody>
        </table>
    </section>

    <section>
        <h3>Add user</h3>
        <div class="form-group">
            <input type="text" id="name" placeholder="Name" class="form-control">
            <input type="email" id="email" placeholder="Email" class="form-control">
            <input type="text" id="phone" placeholder="Phone" class="form-control ">
            <input type="password" id="password" placeholder="Password" class="form-control ">
            <select id="role" class="form-control">
                <option value="1">Admin</option>
                <option value="2">Dentist</option>
                <option value="3">Warehouse</option>
                <option value="4">Receptionist</option>
            </select>
            <button class="btn btn-primary" onclick="crearUsuario()">Save</button>
        </div>
    </section>

    <!-- Modal de edición -->
    <div id="modalEditar" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-content">
            <h3>Edit User</h3>
            <input type="hidden" id="edit_id">

            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit_name" class="form-control">
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="edit_email" class="form-control">
            </div>

            <div class="form-group">
                <label>Phone:</label>
                <input type="text" id="edit_phone" class="form-control">
            </div>

            <div class="form-group">
                <label>Rol:</label>
                <select id="edit_role" class="form-control">
                    <option value="1">Admin</option>
                    <option value="2">Dentist</option>
                    <option value="3">Warehouse</option>
                    <option value="4">Receptionist</option>
                </select>
            </div>

            <div class="form-group">
                <label>State:</label>
                <select id="edit_active" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-group" style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-primary" onclick="guardarEdicion()">Update</button>
                <button class="btn" style="background:#ccc;color:black" onclick="cerrarModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
   
    const ROLES_MAP = {
        1: "Admin",
        2: "Dentist",
        3: "Warehouse",
        4: "Receptionist"
    };

    document.addEventListener("DOMContentLoaded", () => {
        cargarUsuarios();
    });

    // --- CRUD DE USUARIOS ---
    function cargarUsuarios() {
        fetch("api/users_api.php")
            .then(res => res.json())
            .then(data => renderTablaUsuarios(data))
            .catch(err => console.error("Error loading users:", err));
    }

    function renderTablaUsuarios(usuarios) {
        const tabla = document.getElementById("tablaUsuarios");
        tabla.innerHTML = usuarios.map(user => `
            <tr>
                <td>${user.id_user}</td>
                <td>${user.full_name}</td>
                <td>${user.email}</td>
                <td>${user.phone}</td>
                <td>${ROLES_MAP[user.id_role] || "Unknown"}</td>
                <td>${user.active == 1 ? "✔︎ Yes" : "✖︎ No"}</td>
                <td>
                    <button class="btn btn-primary" onclick="prepararEdicion(${JSON.stringify(user).replace(/"/g, '&quot;')})">Edit</button>
                    <button class="btn" style="background:#ff6b6b;color:white" onclick="eliminarUsuario(${user.id_user})">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function crearUsuario() {
        const data = {
            full_name: document.getElementById("name").value,
            email:    document.getElementById("email").value,
            phone:    document.getElementById("phone").value,
            password: document.getElementById("password").value,
            id_role:  document.getElementById("role").value
        };
        fetch("api/users_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            limpiarFormularioAlta();
            cargarUsuarios();
        });
    }

    function guardarEdicion() {
        const data = {
            id_user:   document.getElementById("edit_id").value,
            full_name: document.getElementById("edit_name").value,
            email:     document.getElementById("edit_email").value,
            phone:     document.getElementById("edit_phone").value,
            id_role:   document.getElementById("edit_role").value,
            active:    document.getElementById("edit_active").value
        };
        fetch("api/users_api.php", {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            cerrarModal();
            cargarUsuarios();
        });
    }

function eliminarUsuario(id) {
    if (!confirm("Are you sure you want to delete this user?")) return;

    fetch("api/users_api.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_user: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            alert(res.message + "\n" + res.error);
        } else {
            alert(res.message);
        }
        cargarUsuarios();
    })
    .catch(() => {
        alert("Error deleting user. Please try again later.");
    });
    }

    function prepararEdicion(user) {
        document.getElementById("modalEditar").style.display = "block";
        document.getElementById("edit_id").value = user.id_user;
        document.getElementById("edit_name").value = user.full_name;
        document.getElementById("edit_email").value = user.email;
        document.getElementById("edit_phone").value = user.phone;
        document.getElementById("edit_role").value = user.id_role;
        document.getElementById("edit_active").value = user.active;
    }

    function cerrarModal() {
        document.getElementById("modalEditar").style.display = "none";
    }

    function limpiarFormularioAlta() {
        ["name", "email", "phone", "password"].forEach(id => {
            document.getElementById(id).value = "";
        });
    }
</script>