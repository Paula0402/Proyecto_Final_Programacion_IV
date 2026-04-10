<div id="patients" class="tab-content" style="display: none;">
    <section>
        <h2>Patient List</h2>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID Card</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Birth Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tablaPacientes"></tbody>
            </table>
        </div>
    </section>

    <!-- Formulario para agregar paciente -->
    <section class="mt-4">
        <h3>Add Patient</h3>
        <div class="form-group">
            <label>ID Card *</label>
            <input type="text" id="create_id_card" class="form-control">

            <label>First Name *</label>
            <input type="text" id="create_first_name" class="form-control">

            <label>Last Name *</label>
            <input type="text" id="create_last_name" class="form-control">

            <label>Birth Date</label>
            <input type="date" id="create_birth_date" class="form-control">

            <label>Phone (8-15 digits)</label>
            <input type="text" id="create_phone" class="form-control">

            <label>Email</label>
            <input type="email" id="create_email" class="form-control">

            <label>Address</label>
            <input type="text" id="create_address" class="form-control">

            <button class="btn btn-primary" onclick="crearPaciente()">Save Patient</button>
        </div>
    </section>

    <!-- Modal para editar paciente -->
    <div id="modalEditarPaciente" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="background:white; width:450px; margin:50px auto; padding:20px; border-radius:8px;">
            <h3>Edit Patient</h3>
            <div class="form-group">
                <input type="hidden" id="edit_patient_id">

                <label>ID Card *</label>
                <input type="text" id="edit_patient_id_card" class="form-control">

                <label>First Name *</label>
                <input type="text" id="edit_patient_first_name" class="form-control">

                <label>Last Name *</label>
                <input type="text" id="edit_patient_last_name" class="form-control">

                <label>Birth Date</label>
                <input type="date" id="edit_patient_birth_date" class="form-control">

                <label>Phone (8-15 digits)</label>
                <input type="text" id="edit_patient_phone" class="form-control">

                <label>Email</label>
                <input type="email" id="edit_patient_email" class="form-control">

                <label>Address</label>
                <input type="text" id="edit_patient_address" class="form-control">

                <label>Status</label>
                <select id="edit_patient_active" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>

                <button class="btn btn-primary" onclick="guardarEdicionPaciente()">Update</button>
                <button class="btn btn-secondary" onclick="cerrarModalPaciente()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Cargar todos los pacientes (se llama al mostrar la pestaña)
    async function cargarPacientes() {
        const res = await fetch("api/patients_api.php?all=1");
        const pacientes = await res.json();
        const tbody = document.getElementById("tablaPacientes");
        tbody.innerHTML = pacientes.map(p => `
            <tr>
                <td>${p.id_patient}</td>
                <td>${escapeHtml(p.id_card)}</td>
                <td>${escapeHtml(p.first_name)} ${escapeHtml(p.last_name)}</td>
                <td>${p.phone ? escapeHtml(p.phone) : '-'}</td>
                <td>${p.email ? escapeHtml(p.email) : '-'}</td>
                <td>${p.birth_date || '-'}</td>
                <td style="color: ${p.active == 1 ? 'green' : 'red'};">${p.active == 1 ? 'Active' : 'Inactive'}</td>
                <td>
                    <button class="btn btn-primary" onclick='prepararEdicionPaciente(${JSON.stringify(p).replace(/'/g, "&#39;")})'>Edit</button>
                    <button class="btn" style="background: ${p.active == 1 ? '#ff6b6b' : '#28a745'}; color:white;" onclick="togglePaciente(${p.id_patient}, ${p.active})">
                        ${p.active == 1 ? 'Deactivate' : 'Activate'}
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Crear paciente
    async function crearPaciente() {
        const data = {
            id_card: document.getElementById("create_id_card").value.trim(),
            first_name: document.getElementById("create_first_name").value.trim(),
            last_name: document.getElementById("create_last_name").value.trim(),
            birth_date: document.getElementById("create_birth_date").value,
            phone: document.getElementById("create_phone").value.trim(),
            email: document.getElementById("create_email").value.trim(),
            address: document.getElementById("create_address").value.trim()
        };
        if (!data.id_card || !data.first_name || !data.last_name) {
            alert("ID Card, First Name and Last Name are required");
            return;
        }
        const res = await fetch("api/patients_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (res.ok) {
            alert(result.message);
            // Limpiar formulario
            document.getElementById("create_id_card").value = "";
            document.getElementById("create_first_name").value = "";
            document.getElementById("create_last_name").value = "";
            document.getElementById("create_birth_date").value = "";
            document.getElementById("create_phone").value = "";
            document.getElementById("create_email").value = "";
            document.getElementById("create_address").value = "";
            cargarPacientes();
        } else {
            alert(result.error || "Error creating patient");
        }
    }

    // Preparar edición (llenar modal)
    function prepararEdicionPaciente(p) {
        document.getElementById("modalEditarPaciente").style.display = "block";
        document.getElementById("edit_patient_id").value = p.id_patient;
        document.getElementById("edit_patient_id_card").value = p.id_card;
        document.getElementById("edit_patient_first_name").value = p.first_name;
        document.getElementById("edit_patient_last_name").value = p.last_name;
        document.getElementById("edit_patient_birth_date").value = p.birth_date || '';
        document.getElementById("edit_patient_phone").value = p.phone || '';
        document.getElementById("edit_patient_email").value = p.email || '';
        document.getElementById("edit_patient_address").value = p.address || '';
        document.getElementById("edit_patient_active").value = p.active;
    }

    // Guardar cambios de edición
    async function guardarEdicionPaciente() {
        const data = {
            id_patient: document.getElementById("edit_patient_id").value,
            id_card: document.getElementById("edit_patient_id_card").value.trim(),
            first_name: document.getElementById("edit_patient_first_name").value.trim(),
            last_name: document.getElementById("edit_patient_last_name").value.trim(),
            birth_date: document.getElementById("edit_patient_birth_date").value,
            phone: document.getElementById("edit_patient_phone").value.trim(),
            email: document.getElementById("edit_patient_email").value.trim(),
            address: document.getElementById("edit_patient_address").value.trim(),
            active: document.getElementById("edit_patient_active").value
        };
        const res = await fetch("api/patients_api.php", {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (res.ok) {
            alert(result.message);
            cerrarModalPaciente();
            cargarPacientes();
        } else {
            alert(result.error || "Error updating patient");
        }
    }

    // Activar o desactivar paciente
    async function togglePaciente(id, currentStatus) {
        const action = currentStatus == 1 ? "deactivate" : "activate";
        if (!confirm(`Are you sure you want to ${action} this patient?`)) return;
        const res = await fetch("api/patients_api.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_patient: id })
        });
        const result = await res.json();
        alert(result.message);
        cargarPacientes();
    }

    function cerrarModalPaciente() {
        document.getElementById("modalEditarPaciente").style.display = "none";
    }

    // Escape HTML para prevenir XSS (La IA me dijo, me falta estudiarmelo bien)
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Observar cuándo la pestaña de pacientes se vuelve visible para cargar datos
    document.addEventListener("DOMContentLoaded", function() {
        const pacientesDiv = document.getElementById("patients");
        if (!pacientesDiv) return;
        function checkAndLoad() {
            if (pacientesDiv.style.display !== "none") {
                cargarPacientes();
                return true;
            }
            return false;
        }
        if (!checkAndLoad()) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === "style") {
                        if (pacientesDiv.style.display !== "none") {
                            cargarPacientes();
                            observer.disconnect();
                        }
                    }
                });
            });
            observer.observe(pacientesDiv, { attributes: true });
        }
    });
</script>