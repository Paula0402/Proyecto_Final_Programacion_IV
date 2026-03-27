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
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

<div class="login-card">

    <div class="avatar">
        <img src="img/IsotipoSloganNombre1.png" alt="Logo">
    </div>

    <h4 class="login-title">Reset Password</h4>

    <?php if ($mensaje): ?>
        <div class="error"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label class="form-label">New password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>

        <div class="form-group">
            <label class="form-label">Confirm password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-login w-100">
            Update Password
        </button>

    </form>

    <!-- FUERA del form -->
    <div class="forgot-password">
        <a href="index.php">Back to login</a>
    </div>

</div>

</body>
</html>