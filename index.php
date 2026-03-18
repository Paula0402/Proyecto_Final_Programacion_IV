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

    </form>

</div>

</body>
</html>