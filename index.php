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
<div id="modalBloqueo" class="modal-overlay">
    <div class="modal-box">

        <div class="modal-icon">🔒</div>

        <h2 class="modal-title">¡Account Locked!</h2>

        <p class="modal-text">
            You have been locked out due to multiple failed login attempts.<br>
            For security reasons, your access has been restricted for <strong>5 minutes</strong>.
        </p>

        <hr class="modal-divider">

        <p class="modal-subtext">
            ¿You forgot your password?
        </p>

        <div class="modal-actions">
            <a href="forgot_password.php" class="btn-recover">
                Recover Password
            </a>

            <button onclick="cerrarModal()" class="btn-close">
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