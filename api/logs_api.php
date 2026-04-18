<?php
header("Content-Type: application/json");
require_once "../config/db.php"; // Ajusta la ruta según tu estructura

// optener opciones de filtro
if (isset($_GET['action']) && $_GET['action'] === 'filters') {
    $tipo = $_GET['type'] ?? 'activity';
    
    try {
        if ($tipo === 'activity') {
            // Usuarios que tienen registros en activity_logs
            $users = $pdo->query("
                SELECT DISTINCT u.id_user, u.full_name
                FROM activity_logs al
                JOIN users u ON al.id_user = u.id_user
                ORDER BY u.full_name
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            // Acciones distintas
            $actions = $pdo->query("
                SELECT DISTINCT action FROM activity_logs ORDER BY action
            ")->fetchAll(PDO::FETCH_COLUMN);
            
            // Tablas afectadas distintas
            $tables = $pdo->query("
                SELECT DISTINCT affected_table FROM activity_logs ORDER BY affected_table
            ")->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                "users"   => $users,
                "actions" => $actions,
                "tables"  => $tables
            ]);
        } 
        else { // error logs
            // Usuarios que tienen registros en error_logs
            $users = $pdo->query("
                SELECT DISTINCT u.id_user, u.full_name
                FROM error_logs el
                JOIN users u ON el.id_user = u.id_user
                ORDER BY u.full_name
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            // Mensajes de error distintos
            $errorMessages = $pdo->query("
                SELECT DISTINCT error_message FROM error_logs ORDER BY error_message
            ")->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                "users"          => $users,
                "errorMessages"  => $errorMessages
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener filtros: " . $e->getMessage()]);
    }
    exit;
}

//LOGS (CON PAGINACIÓN Y FILTROS)
$logType = $_GET['type'] ?? 'activity';
$userId   = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
$action   = $_GET['action_filter'] ?? null;
$table    = $_GET['table'] ?? null;
$errorMsg = $_GET['error_message'] ?? null;
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$offset   = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    if ($logType === 'activity') {
        // OBTENER DATOs
        $stmt = $pdo->prepare("
            CALL sp_activity_logs_read(
                :p_id_user,
                :p_action,
                :p_affected_table,
                :p_record_id,
                :p_from_date,
                :p_to_date,
                :p_limit,
                :p_offset
            )
        ");
        
        // Parámetros (el frontend no envía fechas ni record_id, los dejamos NULL)
        $recordId = null;
        $fromDate = null;
        $toDate   = null;
        
        $stmt->bindParam(':p_id_user',          $userId,   PDO::PARAM_INT);
        $stmt->bindParam(':p_action',           $action,   PDO::PARAM_STR);
        $stmt->bindParam(':p_affected_table',   $table,    PDO::PARAM_STR);
        $stmt->bindParam(':p_record_id',        $recordId, PDO::PARAM_INT);
        $stmt->bindParam(':p_from_date',        $fromDate, PDO::PARAM_STR);
        $stmt->bindParam(':p_to_date',          $toDate,   PDO::PARAM_STR);
        $stmt->bindParam(':p_limit',            $limit,    PDO::PARAM_INT);
        $stmt->bindParam(':p_offset',           $offset,   PDO::PARAM_INT);
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Necesario para poder hacer otra consulta
        
        // CONTAR TOTAL DE REGISTROS
        $countSql = "SELECT COUNT(*) FROM activity_logs al WHERE 1=1";
        if ($userId) {
            $countSql .= " AND al.id_user = " . (int)$userId;
        }
        if ($action) {
            $countSql .= " AND al.action = " . $pdo->quote($action);
        }
        if ($table) {
            $countSql .= " AND al.affected_table = " . $pdo->quote($table);
        }
        // Nota: record_id, fechas no se filtran en el frontend actual, pero se podrían añadir
        
        $total = (int)$pdo->query($countSql)->fetchColumn();
        
        echo json_encode([
            "data"  => $data,
            "total" => $total
        ]);
    } 
    else { // error logs
        // OBTENER DATOS
        $stmt = $pdo->prepare("
            CALL sp_error_logs_read(
                :p_id_user,
                :p_error_message_substr,
                :p_from_date,
                :p_to_date,
                :p_limit,
                :p_offset
            )
        ");
        
        $fromDate = null;
        $toDate   = null;
        
        $stmt->bindParam(':p_id_user',               $userId,   PDO::PARAM_INT);
        $stmt->bindParam(':p_error_message_substr',  $errorMsg, PDO::PARAM_STR);
        $stmt->bindParam(':p_from_date',             $fromDate, PDO::PARAM_STR);
        $stmt->bindParam(':p_to_date',               $toDate,   PDO::PARAM_STR);
        $stmt->bindParam(':p_limit',                 $limit,    PDO::PARAM_INT);
        $stmt->bindParam(':p_offset',                $offset,   PDO::PARAM_INT);
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        // CONTAR TOTAL DE REGISTROS
        $countStmt = $pdo->prepare("
            CALL sp_error_logs_count(
                :p_id_user,
                :p_error_message_substr,
                :p_from_date,
                :p_to_date,
                @p_total
            )
        ");
        $countStmt->bindParam(':p_id_user',               $userId,   PDO::PARAM_INT);
        $countStmt->bindParam(':p_error_message_substr',  $errorMsg, PDO::PARAM_STR);
        $countStmt->bindParam(':p_from_date',             $fromDate, PDO::PARAM_STR);
        $countStmt->bindParam(':p_to_date',               $toDate,   PDO::PARAM_STR);
        $countStmt->execute();
        $countStmt->closeCursor();
        
        // Obtener el valor del parámetro de salida
        $total = (int)$pdo->query("SELECT @p_total AS total")->fetchColumn();
        
        echo json_encode([
            "data"  => $data,
            "total" => $total
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al consultar logs: " . $e->getMessage()]);
}