<div id="appointments" class="tab-content <?php echo $tab === 'appointments' ? 'active' : ''; ?>">
    <h2>Appointments and Patients</h2>

    <!-- Formulario -->
    <form method="post" action="api/appointments_api.php" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;">
        <input type="hidden" name="action" value="add_appointment">
        <h4>Create Appointment</h4>

        <div class="form-group">
            <label>Patient</label>
            <input type="text" id="patientSearch" name="patient_name_display" placeholder="Type patient name..." autocomplete="off" list="patientList" required>
            <datalist id="patientList">
                <?php foreach($patients_data as $p){ ?>
                    <option value="<?php echo htmlspecialchars($p['full_name']); ?>" data-id="<?php echo htmlspecialchars($p['id_patient']); ?>"></option>
                <?php } ?>
            </datalist>
            <input type="hidden" name="patient_id" id="patientIdHidden">
        </div>

        
<div class="form-group">
    <label>Doctor</label>
    <input type="text" id="doctorSearch" name="doctor_name_display" placeholder="Type doctor name..." autocomplete="off" list="doctorList" required>
    <datalist id="doctorList">
        <?php foreach($users_data as $u){ ?>
            <option value="<?php echo htmlspecialchars($u['full_name']); ?>" data-id="<?php echo htmlspecialchars($u['id_user']); ?>"></option>
        <?php } ?>
    </datalist>
    <input type="hidden" name="assigned_user" id="doctorIdHidden">
</div>

        <div class="form-group">
            <label>Scheduled Date</label>
            <input type="datetime-local" name="scheduled_at" required>
        </div>

        <div class="form-group">
            <label>Reason</label>
            <input type="text" name="reason" required>
        </div>

        <button class="btn btn-primary" type="submit">Create Appointment</button>
    </form>

    <!-- Tabla -->
    <table style="width:100%; table-layout:fixed;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Scheduled</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($appointments_data as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['id_appointment']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['appointment_date'] . ' ' . $a['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($a['status']); ?></td>
                    <td><?php echo htmlspecialchars($a['reason']); ?></td>
                    <td>
                        <?php if (strtolower($a['status']) !== 'attended' && strtolower($a['status']) !== 'completed'): ?>
                            
                            <!-- Botón limpio -->
                            <button class="btn btn-primary"
                                onclick="abrirFormulario(<?php echo $a['id_appointment']; ?>)">
                                Close
                            </button>

                        <?php else: ?>
                            <span>Finalized</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulario oculto -->
    <div id="formCerrar" class="form-cerrar" style="display:none;">
        <h4>Close Appointment</h4>

        <form method="post" action="api/appointments_api.php">
            <input type="hidden" name="action" value="close_appointment">
            <input type="hidden" name="appointment_id" id="appointment_id">

            <div class="form-group">
                <input type="text" name="diagnostic" placeholder="Diagnosis" required>
            </div>

            <div class="form-group">
                <input type="text" name="treatment" placeholder="Treatment" required>
            </div>

            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>

<!-- script -->
<script>
function syncSelection(inputId, listId, hiddenId) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const inputValue = input.value.trim();
    const options = document.querySelectorAll(`#${listId} option`);
    const match = Array.from(options).find(option => option.value.toLowerCase() === inputValue.toLowerCase());

    if (match) {
        hidden.value = match.getAttribute('data-id');
    } else {
        hidden.value = '';
    }
}

const patientInputEl = document.getElementById('patientSearch');
if (patientInputEl) {
    patientInputEl.addEventListener('input', () => syncSelection('patientSearch', 'patientList', 'patientIdHidden'));
    patientInputEl.addEventListener('change', () => syncSelection('patientSearch', 'patientList', 'patientIdHidden'));
}

const doctorInputEl = document.getElementById('doctorSearch');
if (doctorInputEl) {
    doctorInputEl.addEventListener('input', () => syncSelection('doctorSearch', 'doctorList', 'doctorIdHidden'));
    doctorInputEl.addEventListener('change', () => syncSelection('doctorSearch', 'doctorList', 'doctorIdHidden'));
}

function abrirFormulario(id) {
    document.getElementById("formCerrar").style.display = "block";
    document.getElementById("appointment_id").value = id;
}
</script>