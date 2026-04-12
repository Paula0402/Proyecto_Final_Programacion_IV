<div id="medical_history" class="tab-content" style="display: none;">
    <h2>Medical History</h2>

    <!-- Panel de filtros -->
    <div style="margin-bottom: 20px;">
        <div>
            <label>Filter by</label>
            <select id="historyFilter" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
                <option value="all">All records</option>
                <option value="patient">By Patient</option>
                <option value="dentist">By Dentist</option>
            </select>
        </div>
        <div id="patientSelectContainer" style="display:none; margin-top: 10px;">
            <label>Patient</label>
            <select id="patientSelect" class="form-control"></select>
        </div>
        <div id="dentistSelectContainer" style="display:none; margin-top: 10px;">
            <label>Dentist</label>
            <select id="dentistSelect" class="form-control"></select>
        </div>
        <div style="margin-top: 10px;">
            <label>Date from</label>
            <input type="date" id="dateFrom" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
            <label>Date to</label>
            <input type="date" id="dateTo" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
            <label>Limit</label>
            <select id="historyLimit" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
                <option value="50">50</option>
                <option value="100" selected>100</option>
                <option value="200">200</option>
            </select>
            <button class="btn btn-secondary" id="resetHistoryBtn" style="margin-left: 10px;">Reset Filters</button>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div style="overflow-x: auto;">
        <table style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Dentist</th>
                    <th>Appointment Date</th>
                    <th>Diagnosis</th>
                    <th>Treatment</th>
                    <th>Notes</th>
                    <th>Requires Control</th>
                    <th>Next Control</th>
                </tr>
            </thead>
            <tbody id="medicalHistoryTable"></tbody>
        </table>
    </div>
    <div id="historyPagination" style="margin-top: 15px; text-align: center;"></div>
</div>

<script>
    let currentHistoryPage = 0;
    let totalHistoryRecords = 0;

    // Cargar opciones de pacientes y dentistas para los filtros
    async function loadHistoryFilters() {
        try {
            const patientsRes = await fetch("api/medical_history_api.php?action=filters&type=patients");
            const patients = await patientsRes.json();
            const patientSelect = document.getElementById("patientSelect");
            patientSelect.innerHTML = '<option value="">Select patient</option>';
            patients.forEach(p => {
                patientSelect.innerHTML += `<option value="${p.id_patient}">${escapeHtml(p.full_name)}</option>`;
            });

            const dentistsRes = await fetch("api/medical_history_api.php?action=filters&type=dentists");
            const dentists = await dentistsRes.json();
            const dentistSelect = document.getElementById("dentistSelect");
            dentistSelect.innerHTML = '<option value="">Select dentist</option>';
            dentists.forEach(d => {
                dentistSelect.innerHTML += `<option value="${d.id_user}">${escapeHtml(d.full_name)}</option>`;
            });
        } catch (error) {
            console.error("Error loading filters:", error);
            alert("Could not load filter options");
        }
    }

    // Mostrar/ocultar selects según el tipo de filtro
    function toggleHistoryFields() {
        const filter = document.getElementById("historyFilter").value;
        document.getElementById("patientSelectContainer").style.display = filter === "patient" ? "block" : "none";
        document.getElementById("dentistSelectContainer").style.display = filter === "dentist" ? "block" : "none";
    }

    // Resetear todos los filtros
    function resetHistoryFilters() {
        document.getElementById("historyFilter").value = "all";
        document.getElementById("patientSelect").value = "";
        document.getElementById("dentistSelect").value = "";
        document.getElementById("dateFrom").value = "";
        document.getElementById("dateTo").value = "";
        document.getElementById("historyLimit").value = "100";
        toggleHistoryFields();
        loadMedicalHistory(0);
    }

    // Cargar historial médico según filtros
    async function loadMedicalHistory(page = 0) {
        const filter = document.getElementById("historyFilter").value;
        let id = null;

        if (filter === "patient") {
            id = document.getElementById("patientSelect").value;
            if (!id) {
                alert("Please select a patient");
                return;
            }
        } else if (filter === "dentist") {
            id = document.getElementById("dentistSelect").value;
            if (!id) {
                alert("Please select a dentist");
                return;
            }
        }

        const date_from = document.getElementById("dateFrom").value;
        const date_to = document.getElementById("dateTo").value;

        if (date_from && date_to && date_from > date_to) {
            alert("Date 'from' cannot be greater than date 'to'");
            return;
        }

        const limit = document.getElementById("historyLimit").value;
        const offset = page * limit;

        let url = `api/medical_history_api.php?filter=${filter}&limit=${limit}&offset=${offset}`;
        if (id) url += `&id=${id}`;
        if (date_from) url += `&date_from=${date_from}`;
        if (date_to) url += `&date_to=${date_to}`;

        try {
            const res = await fetch(url);
            const result = await res.json();
            if (result.error) {
                alert(result.error);
                return;
            }
            totalHistoryRecords = result.total;
            currentHistoryPage = page;
            renderMedicalHistoryTable(result.data);
            renderHistoryPagination();
        } catch (error) {
            console.error("Error loading medical history:", error);
            alert("Connection error");
        }
    }

    // Renderizar tabla de historial
    function renderMedicalHistoryTable(data) {
        const tbody = document.getElementById("medicalHistoryTable");
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No records found</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(record => `
            <tr>
                <td>${record.id_history || record.id_medical_history || record.id}</td>
                <td>${escapeHtml(record.patient_name)}</td>
                <td>${escapeHtml(record.dentist_name)}</td>
                <td>${record.appointment_date} ${record.appointment_time ? record.appointment_time.substring(0,5) : ''}</td>
                <td>${escapeHtml(record.diagnosis || '-')}</td>
                <td>${escapeHtml(record.treatment || '-')}</td>
                <td>${escapeHtml(record.notes || '-')}</td>
                <td>${record.requires_control ? 'Yes' : 'No'}</td>
                <td>${record.next_control_date || '-'}</td>
            </tr>
        `).join('');
    }

    // Paginación
    function renderHistoryPagination() {
        const limit = parseInt(document.getElementById("historyLimit").value);
        const totalPages = Math.ceil(totalHistoryRecords / limit);
        const container = document.getElementById("historyPagination");
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        let html = '<div style="display:inline-block;">';
        if (currentHistoryPage > 0) {
            html += `<button class="btn btn-secondary" onclick="loadMedicalHistory(${currentHistoryPage-1})">Previous</button>`;
        }
        html += `<span style="margin:0 10px;">Page ${currentHistoryPage+1} of ${totalPages}</span>`;
        if (currentHistoryPage < totalPages-1) {
            html += `<button class="btn btn-secondary" onclick="loadMedicalHistory(${currentHistoryPage+1})">Next</button>`;
        }
        html += '</div>';
        container.innerHTML = html;
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

    // Inicialización de eventos y carga inicial
    document.addEventListener("DOMContentLoaded", function() {
        loadHistoryFilters();
        loadMedicalHistory(0);

        document.getElementById("historyFilter").addEventListener("change", function() {
            toggleHistoryFields();
            loadMedicalHistory(0);
        });
        document.getElementById("patientSelect").addEventListener("change", () => loadMedicalHistory(0));
        document.getElementById("dentistSelect").addEventListener("change", () => loadMedicalHistory(0));
        document.getElementById("dateFrom").addEventListener("change", () => loadMedicalHistory(0));
        document.getElementById("dateTo").addEventListener("change", () => loadMedicalHistory(0));
        document.getElementById("historyLimit").addEventListener("change", () => loadMedicalHistory(0));
        document.getElementById("resetHistoryBtn").addEventListener("click", resetHistoryFilters);
    });
</script>