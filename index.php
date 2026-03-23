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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#a79aac;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family: "Segoe UI", Arial, sans-serif;
}

.login-card{
    background:#52234e;
    border-radius:18px;
    padding:40px;
    width:360px;
    box-shadow:0 12px 28px rgba(0,0,0,0.25);
}

.avatar{
    width:90px;
    height:90px;
    background:#e0e0e0;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:0 auto 25px auto;
}

.avatar svg{
    width:45px;
    fill:#7a7a7a;
}

.login-title{
    color:white;
    text-align:center;
    margin-bottom:25px;
    font-weight:500;
}

.form-label{
    color:#f1f1f1;
    font-size:14px;
}

.form-control{
    background:#eeeeee;
    border:none;
    padding:10px;
}

.form-control:focus{
    background:#f4f4f4;
    box-shadow:0 0 0 0.2rem rgba(255,255,255,0.15);
}

.btn-login{
    background:#b7a4b6;
    border:none;
    font-weight:500;
}

.btn-login:hover{
    background:#c7b3c6;
}

.error{
    color:#ffdede;
    text-align:center;
    margin-bottom:15px;
    font-size:14px;
}

</style>

</head>

<body>

<div class="login-card">

    <div class="avatar">
        <svg viewBox="0 0 24 24">
            <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 
            2.3-5 5 2.3 5 5 5zm0 
            2c-3.3 0-10 1.7-10 
            5v3h20v-3c0-3.3-6.7-5-10-5z"/>
        </svg>
    </div>

    <h4 class="login-title">Iniciar Sesión</h4>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Usuario o contraseña incorrectos</div>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-login w-100">
            Ingresar
        </button>
        <div>You forgot your password? <a href="forgot_password.php">Click here</a></div>

    </form>

</div>

</body>
</html>
<<<<<<< Updated upstream
=======

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
>>>>>>> Stashed changes
