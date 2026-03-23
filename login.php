<?php
session_start();
require_once 'config/db.php';

/**
 * 1. DEFINICIÓN DE LA FUNCIÓN (Debe ir arriba para que PHP la conozca)
 */
function log_auth_attempt(PDO $pdo, string $username, string $status, ?int $userId = null, int $error_code = 0): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO error_logs (id_user, procedure_name, error_code, error_message) VALUES (:id_user, :procedure_name, :error_code, :error_message)");
        $stmt->execute([
            ':id_user'        => $userId,
            ':procedure_name' => 'login',
            ':error_code'     => $error_code,
            ':error_message'  => $status,
        ]);
    } catch (PDOException $e) {
        // Si falla el log, que no se detenga el sistema
    }
}

/**
 * 2. LÓGICA DE PROCESAMIENTO
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        // Aquí se llama a la función en la línea 30 aprox.
        log_auth_attempt($pdo, $username, 'missing credentials', null, 100);
        header("Location: index.php?error=empty");
        exit;
    }

    // Consulta incluyendo failed_attempts y lock_until
    $sql = "SELECT id_user, full_name, password_hash, active, id_role, failed_attempts, lock_until 
            FROM users 
            WHERE full_name = :full_name 
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':full_name' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Aquí se llama a la función en la línea 48 aprox.
        log_auth_attempt($pdo, $username, 'user not found', null, 101);
        header("Location: index.php?error=1");
        exit;
    }

    // Verificar si está bloqueado por tiempo
    if (!empty($user['lock_until'])) {
        if (strtotime($user['lock_until']) > time()) {
            log_auth_attempt($pdo, $username, 'account locked', (int)$user['id_user'], 104);
            header("Location: index.php?error=locked");
            exit;
        }
    }

    // Verificar contraseña
    if (!password_verify($password, $user['password_hash'])) {
        $failedAttempts = (int)$user['failed_attempts'] + 1;
        $max_attempts = 5;

        if ($failedAttempts >= $max_attempts) {
            $lockUntil = date("Y-m-d H:i:s", strtotime("+5 minutes"));
            $update = $pdo->prepare("UPDATE users SET failed_attempts = :fa, lock_until = :lu WHERE id_user = :id");
            $update->execute([':fa' => $failedAttempts, ':lu' => $lockUntil, ':id' => $user['id_user']]);
            log_auth_attempt($pdo, $username, 'account locked due to attempts', (int)$user['id_user'], 105);
            header("Location: index.php?error=locked");
        } else {
            $update = $pdo->prepare("UPDATE users SET failed_attempts = :fa WHERE id_user = :id");
            $update->execute([':fa' => $failedAttempts, ':id' => $user['id_user']]);
            log_auth_attempt($pdo, $username, 'wrong password', (int)$user['id_user'], 103);
            header("Location: index.php?error=1");
        }
        exit;
    }

    // LOGIN EXITOSO
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['username'] = $user['full_name'];
    $_SESSION['user_role'] = (int)$user['id_role'];

<<<<<<< Updated upstream
    $role_names = [1 => 'Admin', 2 => 'Odontólogo', 3 => 'Bodega', 4 => 'Recepción'];
    $_SESSION['role_name'] = $role_names[$_SESSION['user_role']] ?? 'Invitado';

=======
>>>>>>> Stashed changes
    log_auth_attempt($pdo, $username, 'login success', (int)$user['id_user'], 0);
    $pdo->prepare("UPDATE users SET last_login = NOW(), failed_attempts = 0, lock_until = NULL WHERE id_user = :id")
        ->execute([':id' => $user['id_user']]);

    header("Location: dashboard.php");
    exit;
}

header("Location: index.php");
exit;