<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>

<!-- bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!--css -->
<link rel="stylesheet" href="css/style.css">

</head>

<body class="login-page">

<div class="login-card">

    <div class="avatar">
        <img src="img/IsotipoSloganNombre1.png" alt="Logo">
    </div>

    <h4 class="login-title">Sign In</h4>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Invalid username or password</div>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-login w-100">
            Sign In
        </button>
        <div class="forgot-password">
            <a href="forgot_password.php">Forgot your password? Clic here</a>
        </div>

    </form>

</div>

</body>
</html>

<?php if (isset($_GET['error']) && $_GET['error'] === 'locked'): ?>
<div id="modalBloqueo" style="display: flex; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); align-items: center; justify-content: center; font-family: sans-serif;">
    <div style="background-color: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border-top: 5px solid #572853;">
        
        <div style="font-size: 50px; margin-bottom: 15px;">🔒</div>
        
        <h2 style="color: #572853; margin-top: 0;">¡Account Locked!</h2>
        
        <p style="color: #555; line-height: 1.6;">
            You have been locked out due to multiple failed login attempts.<br>
            For security reasons, your access has been restricted for <strong>5 minutes</strong>.
        </p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <p style="font-size: 14px; color: #777; margin-bottom: 20px;">
            ¿You forgot your password?
        </p>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="forgot_password.php" style="background-color: #572853; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; flex: 1;">
                Recover Password
            </a>
            
            <button onclick="cerrarModal()" style="background-color: #eee; color: #572853; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; flex: 1;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    // Función para cerrar el modal y limpiar la URL para que no vuelva a salir al recargar
    function cerrarModal() {
        document.getElementById('modalBloqueo').style.display = 'none';
        // Esto limpia el "?error=locked" de la barra de direcciones sin recargar la página
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
<?php endif; ?>