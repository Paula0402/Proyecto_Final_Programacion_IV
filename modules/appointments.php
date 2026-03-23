<div id="appointments" class="tab-content <?php echo $tab === 'appointments' ? 'active' : ''; ?>">
    <h2>Appointments and Patients</h2>

    <!-- Formulario -->
    <form method="post" style="border:1px solid #ddd;padding:12px;margin-bottom:16px;">
        <input type="hidden" name="action" value="add_appointment">
        <h4>Create Appointment</h4>

        <div class="form-group">
            <label>Patient</label>
            <select name="patient_id" required>
                <option value="">Select Patient</option>
                <?php foreach($patients_data as $p){
                    echo '<option value="'.htmlspecialchars($p['id_patient']).'">'.htmlspecialchars($p['full_name']).'</option>';
                } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Doctor</label>
            <select name="assigned_user" required>
                <option value="">Select Doctor</option>
                <?php foreach($users_data as $u){
                    echo '<option value="'.htmlspecialchars($u['id_user']).'">'.htmlspecialchars($u['full_name']).'</option>';
                } ?>
            </select>
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
    <div id="formCerrar" style="display:none; border:1px solid #ccc; padding:15px; margin-top:20px; background:#f9f9f9;">
        <h4>Close Appointment</h4>

        <form method="post">
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
function abrirFormulario(id) {
    document.getElementById("formCerrar").style.display = "block";
    document.getElementById("appointment_id").value = id;
}
</script>