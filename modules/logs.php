<div id="logs" class="tab-content" style="display: none;">
    <h2>System Logs</h2>

    <!-- Panel de filtros -->
    <div style="margin-bottom: 20px;">
        <div>
            <label>Log type</label>
            <select id="logTypeSelect" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
                <option value="activity">Activity Logs</option>
                <option value="error">Error Logs</option>
            </select>

            <label>User</label>
            <select id="logUserSelect" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
                <option value="">All users</option>
            </select>

            <label>Limit</label>
            <select id="logLimit" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
                <option value="50">50</option>
                <option value="100" selected>100</option>
                <option value="200">200</option>
            </select>

            <button class="btn btn-secondary" id="resetLogsBtn">Reset Filters</button>
        </div>

        <!-- Campos específicos para activity logs -->
        <div id="actionFilterContainer" style="margin-top: 10px;">
            <label>Action</label>
            <select id="logActionSelect" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;">
                <option value="">All actions</option>
            </select>
            <label>Table</label>
            <select id="logTableSelect" class="form-control" style="width: auto; display: inline-block;">
                <option value="">All tables</option>
            </select>
        </div>

        <!-- Campo para filtrar por mensaje de error (solo error logs) -->
        <div id="errorMessageContainer" style="display:none; margin-top: 10px;">
            <label>Error message</label>
            <select id="errorMessageSelect" class="form-control" style="width: auto; display: inline-block;">
                <option value="">All error messages</option>
            </select>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div style="overflow-x: auto;">
        <table style="width:100%;">
            <thead id="logsTableHeader"></thead>
            <tbody id="logsTableBody">
                <tr><td colspan="10" style="text-align:center;">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="logsPagination" style="margin-top: 15px; text-align: center;"></div>
</div>

<script>
    // Variables globales 
    var paginaActual = 0;
    var totalRegistros = 0;
    var tipoActual = 'activity';

    // Cargar opciones de filtros (usuarios, acciones, tablas o mensajes de error)
    function cargarFiltros() {
        var tipo = document.getElementById("logTypeSelect").value;
        var url = "api/logs_api.php?action=filters&type=" + tipo;
        fetch(url)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                // Select de usuarios
                var userSelect = document.getElementById("logUserSelect");
                userSelect.innerHTML = ''; // limpiar
                var optionAll = document.createElement('option');
                optionAll.value = '';
                optionAll.textContent = 'All the users';
                userSelect.appendChild(optionAll);
                if (data.users && data.users.length) {
                    for (var i = 0; i < data.users.length; i++) {
                        var u = data.users[i];
                        var option = document.createElement('option');
                        option.value = u.id_user;
                        option.textContent = u.full_name;
                        userSelect.appendChild(option);
                    }
                }
                // Filtros específicos de activity
                if (tipo === 'activity' && data.actions && data.tables) {
                    var actionSelect = document.getElementById("logActionSelect");
                    actionSelect.innerHTML = '';
                    var optAllAct = document.createElement('option');
                    optAllAct.value = '';
                    optAllAct.textContent = 'All the actions';
                    actionSelect.appendChild(optAllAct);
                    for (var j = 0; j < data.actions.length; j++) {
                        var opt = document.createElement('option');
                        opt.value = data.actions[j];
                        opt.textContent = data.actions[j];
                        actionSelect.appendChild(opt);
                    }
                    
                    var tableSelect = document.getElementById("logTableSelect");
                    tableSelect.innerHTML = '';
                    var optAllTab = document.createElement('option');
                    optAllTab.value = '';
                    optAllTab.textContent = 'All the tables';
                    tableSelect.appendChild(optAllTab);
                    for (var k = 0; k < data.tables.length; k++) {
                        var optTab = document.createElement('option');
                        optTab.value = data.tables[k];
                        optTab.textContent = data.tables[k];
                        tableSelect.appendChild(optTab);
                    }
                }
                // Filtros de error
                if (tipo === 'error' && data.errorMessages) {
                    var errorMsgSelect = document.getElementById("errorMessageSelect");
                    errorMsgSelect.innerHTML = '';
                    var optAllErr = document.createElement('option');
                    optAllErr.value = '';
                    optAllErr.textContent = 'All the massages';
                    errorMsgSelect.appendChild(optAllErr);
                    for (var m = 0; m < data.errorMessages.length; m++) {
                        var msg = data.errorMessages[m];
                        var corto = msg.length > 100 ? msg.substring(0, 100) + '...' : msg;
                        var optErr = document.createElement('option');
                        optErr.value = msg;
                        optErr.textContent = corto;
                        errorMsgSelect.appendChild(optErr);
                    }
                }
            })
            .catch(function(error) {
                console.error("Error al cargar filtros:", error);
            });
    }

    // Mostrar/ocultar campos según el tipo de log
    function alternarCampos() {
        var tipo = document.getElementById("logTypeSelect").value;
        tipoActual = tipo;
        if (tipo === 'activity') {
            document.getElementById("actionFilterContainer").style.display = "block";
            document.getElementById("errorMessageContainer").style.display = "none";
        } else {
            document.getElementById("actionFilterContainer").style.display = "none";
            document.getElementById("errorMessageContainer").style.display = "block";
        }
        cargarFiltros();
        cargarLogs(0);
    }

    // Resetear todos los filtros
    function resetearFiltros() {
        document.getElementById("logTypeSelect").value = "activity";
        document.getElementById("logUserSelect").value = "";
        document.getElementById("logActionSelect").value = "";
        document.getElementById("logTableSelect").value = "";
        document.getElementById("errorMessageSelect").value = "";
        document.getElementById("logLimit").value = "100";
        alternarCampos();
    }

    // Cargar logs según filtros
    function cargarLogs(pagina) {
        var tipo = document.getElementById("logTypeSelect").value;
        var userId = document.getElementById("logUserSelect").value;
        var limite = parseInt(document.getElementById("logLimit").value);
        var offset = pagina * limite;

        var url = "api/logs_api.php?type=" + tipo + "&limit=" + limite + "&offset=" + offset;
        if (userId) url += "&user_id=" + userId;

        if (tipo === 'activity') {
            var accion = document.getElementById("logActionSelect").value;
            var tabla = document.getElementById("logTableSelect").value;
            if (accion) url += "&action_filter=" + encodeURIComponent(accion);
            if (tabla) url += "&table=" + encodeURIComponent(tabla);
        } else {
            var errorMsg = document.getElementById("errorMessageSelect").value;
            if (errorMsg) url += "&error_message=" + encodeURIComponent(errorMsg);
        }

        fetch(url)
            .then(function(res) { return res.json(); })
            .then(function(resultado) {
                if (resultado.error) {
                    alert(resultado.error);
                    return;
                }
                totalRegistros = resultado.total;
                paginaActual = pagina;
                renderizarTabla(resultado.data, tipo);
                renderizarPaginacion();
            })
            .catch(function(error) {
                console.error("Error cargando logs:", error);
                alert("Error de conexión");
            });
    }

    // Renderizar tabla según tipo de log
    function renderizarTabla(datos, tipo) {
        var thead = document.getElementById("logsTableHeader");
        var tbody = document.getElementById("logsTableBody");
        tbody.innerHTML = ''; // Limpiar contenido previo

        if (!datos.length) {
            var filaVacia = document.createElement('tr');
            var celdaVacia = document.createElement('td');
            celdaVacia.colSpan = 10;
            celdaVacia.style.textAlign = 'center';
            celdaVacia.textContent = 'There are no records';
            filaVacia.appendChild(celdaVacia);
            tbody.appendChild(filaVacia);
            
            if (tipo === 'activity') {
                thead.innerHTML = '<tr><th>ID</th><th>User</th><th>Action</th><th>Tables</th><th>ID Register</th><th>Old Value</th><th>New Value</th><th>Date</th></tr>';
            } else {
                thead.innerHTML = '<tr><th>ID</th><th>User</th><th>Error massage</th><th>Procedure</th><th>Error Code</th><th>Date</th></tr>';
            }
            return;
        }

        if (tipo === 'activity') {
            thead.innerHTML = '<tr><th>ID</th><th>User</th><th>Action</th><th>Tables</th><th>ID</th><th>Old Value</th><th>New Value</th><th>Date</th></tr>';
            for (var i = 0; i < datos.length; i++) {
                var r = datos[i];
                var fila = document.createElement('tr');
                
                var celdaId = document.createElement('td'); celdaId.textContent = r.id_log; fila.appendChild(celdaId);
                var celdaUser = document.createElement('td'); celdaUser.textContent = r.user_name || 'Sistema'; fila.appendChild(celdaUser);
                var celdaAction = document.createElement('td'); celdaAction.textContent = r.action; fila.appendChild(celdaAction);
                var celdaTable = document.createElement('td'); celdaTable.textContent = r.affected_table; fila.appendChild(celdaTable);
                var celdaRecord = document.createElement('td'); celdaRecord.textContent = (r.record_id !== null && r.record_id !== undefined) ? r.record_id : '-'; fila.appendChild(celdaRecord);
                
                var celdaOld = document.createElement('td');
                var preOld = document.createElement('pre'); preOld.style.margin = '0'; preOld.textContent = r.old_value || '-';
                celdaOld.appendChild(preOld); fila.appendChild(celdaOld);
                
                var celdaNew = document.createElement('td');
                var preNew = document.createElement('pre'); preNew.style.margin = '0'; preNew.textContent = r.new_value || '-';
                celdaNew.appendChild(preNew); fila.appendChild(celdaNew);
                
                var celdaDate = document.createElement('td'); celdaDate.textContent = r.activity_date; fila.appendChild(celdaDate);
                
                tbody.appendChild(fila);
            }
        } else {
            thead.innerHTML = '<tr><th>ID</th><th>User</th><th>Error Message</th><th>Procedure</th><th>Error Code</th><th>Date</th></tr>';
            for (var j = 0; j < datos.length; j++) {
                var e = datos[j];
                var filaErr = document.createElement('tr');
                
                var celdaIdErr = document.createElement('td'); celdaIdErr.textContent = e.id_error; filaErr.appendChild(celdaIdErr);
                var celdaUserErr = document.createElement('td'); celdaUserErr.textContent = e.user_name || 'Sistema'; filaErr.appendChild(celdaUserErr);
                var celdaMsg = document.createElement('td'); celdaMsg.textContent = e.error_message; filaErr.appendChild(celdaMsg);
                var celdaProc = document.createElement('td'); celdaProc.textContent = e.procedure_name || '-'; filaErr.appendChild(celdaProc);
                var celdaCode = document.createElement('td'); celdaCode.textContent = (e.error_code !== null && e.error_code !== undefined) ? e.error_code : '-'; filaErr.appendChild(celdaCode);
                var celdaDateErr = document.createElement('td'); celdaDateErr.textContent = e.error_date; filaErr.appendChild(celdaDateErr);
                
                tbody.appendChild(filaErr);
            }
        }
    }

    // Paginación
    function renderizarPaginacion() {
        var limite = parseInt(document.getElementById("logLimit").value);
        var totalPaginas = Math.ceil(totalRegistros / limite);
        var contenedor = document.getElementById("logsPagination");
        
        if (totalPaginas <= 1) {
            contenedor.innerHTML = '';
            return;
        }
        
        var html = '<div style="display:inline-block;">';
        
        // Botón Anterior
        if (paginaActual > 0) {
            html += '<button class="btn btn-secondary" onclick="cargarLogs(' + (paginaActual - 1) + ')">« Anterior</button> ';
        } else {
            html += '<button class="btn btn-secondary" disabled>« Anterior</button> ';
        }
        
        // Indicador de página
        html += '<span style="margin: 0 10px;">Página ' + (paginaActual + 1) + ' de ' + totalPaginas + '</span> ';
        
        // Botón Siguiente
        if (paginaActual < totalPaginas - 1) {
            html += '<button class="btn btn-secondary" onclick="cargarLogs(' + (paginaActual + 1) + ')">Siguiente »</button>';
        } else {
            html += '<button class="btn btn-secondary" disabled>Siguiente »</button>';
        }
        
        html += '</div>';
        contenedor.innerHTML = html;
    }

    // Inicialización
    document.addEventListener("DOMContentLoaded", function() {
        cargarFiltros();
        cargarLogs(0);
        document.getElementById("logTypeSelect").addEventListener("change", alternarCampos);
        document.getElementById("logUserSelect").addEventListener("change", function() { cargarLogs(0); });
        document.getElementById("logActionSelect").addEventListener("change", function() { cargarLogs(0); });
        document.getElementById("logTableSelect").addEventListener("change", function() { cargarLogs(0); });
        document.getElementById("errorMessageSelect").addEventListener("change", function() { cargarLogs(0); });
        document.getElementById("logLimit").addEventListener("change", function() { cargarLogs(0); });
        document.getElementById("resetLogsBtn").addEventListener("click", resetearFiltros);
    });
</script>