<?php
session_start();
require_once 'config/db.php';

function log_auth_attempt(PDO $pdo, string $username, string $status, ?int $userId = null, int $error_code = 0): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO error_logs (id_user, procedure_name, error_code, error_message) VALUES (:id_user, :procedure_name, :error_code, :error_message)");
        $stmt->execute([
            ':id_user' => $userId,
            ':procedure_name' => 'login',
            ':error_code' => $error_code,
            ':error_message' => $status,
        ]);
    } catch (PDOException $e) {
        // en caso de error de logging no bloquea login
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        log_auth_attempt($pdo, $username, 'missing credentials', null, 100);
        header("Location: index.php?error=1");
        exit;
    }

    $sql = "SELECT id_user, full_name, password_hash, active, id_role
            FROM users
            WHERE full_name = :full_name
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':full_name', $username, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        log_auth_attempt($pdo, $username, 'user not found', null, 101);
        header("Location: index.php?error=1");
        exit;
    }

    if ((int)$user['active'] !== 1) {
        log_auth_attempt($pdo, $username, 'account inactive', (int)$user['id_user'], 102);
        header("Location: index.php?error=1");
        exit;
    }

    if (!password_verify($password, $user['password_hash'])) {
        log_auth_attempt($pdo, $username, 'wrong password', (int)$user['id_user'], 103);
        header("Location: index.php?error=1");
        exit;
    }

    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['username'] = $user['full_name'];
    $_SESSION['fullname'] = $user['full_name'];
    $_SESSION['user_role'] = (int)$user['id_role'];

    $role_names = [1 => 'Admin', 2 => 'Dentist', 3 => 'Warehouse', 4 => 'Receptionist'];
    $_SESSION['role_name'] = $role_names[$_SESSION['user_role']] ?? 'Guest';

    log_auth_attempt($pdo, $username, 'login success', (int)$user['id_user'], 0);

    $pdo->prepare("UPDATE users SET last_login = NOW(), failed_attempts = 0 WHERE id_user = :id")->execute([':id' => $user['id_user']]);

    header("Location: dashboard.php");
    exit;
}

header("Location: index.php");
exit;