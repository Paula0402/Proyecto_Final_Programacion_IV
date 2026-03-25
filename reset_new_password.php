<?php
session_start();
require_once 'config/db.php';

// Seguridad: Si no hay un ID de usuario en la sesión, redirigir al inicio
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['new_password'] ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if (strlen($pass1) < 5) {
        $mensaje = "The password must be at least 5 characters long.";
        $error = true;
    } elseif ($pass1 !== $pass2) {
        $mensaje = "The passwords do not match.";
        $error = true;
    } else {
        // Todo bien, procedemos a actualizar
        $new_hash = password_hash($pass1, PASSWORD_DEFAULT);
        $userId = $_SESSION['reset_user_id'];

        try {
            // ACTUALIZACIÓN CRÍTICA: Cambiamos clave Y RESTEAMOS BLOQUEO
            $sql = "UPDATE users SET 
                    password_hash = :hash, 
                    failed_attempts = 0, 
                    lock_until = NULL 
                    WHERE id_user = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':hash' => $new_hash,
                ':id'   => $userId
            ]);

            // Limpiamos la sesión de recuperación
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_code']);

            // Redirigir con éxito
            header("Location: index.php?success=password_changed");
            exit;

        } catch (PDOException $e) {
            $mensaje = "Error in the database. Please try again later.";
            $error = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>New Password</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .error-msg { color: #d9534f; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="card">
    <h2>New Password</h2>
    <p style="color: #666; font-size: 14px;">Enter your new access password.</p>

    <?php if ($mensaje): ?>
        <p class="error-msg"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="new_password" placeholder="New password" required minlength="5">
        <input type="password" name="confirm_password" placeholder="Confirm password" required minlength="5">
        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>