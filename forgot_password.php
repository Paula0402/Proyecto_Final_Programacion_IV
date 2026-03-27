<?php
session_start();
require_once 'config/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCode = trim($_POST['code']);

    if (!empty($inputCode)) {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE recovery_code = :code LIMIT 1");
        $stmt->execute([':code' => $inputCode]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_user_id'] = $user['id_user'];
            header("Location: reset_new_password.php");
            exit;
        } else {
            $mensaje = "The recovery code is invalid.";
        }
    } else {
        $mensaje = "Please enter the 4-digit code.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>recover access</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page d-flex justify-content-center align-items-center">

<div class="login-card text-center">

    <div class="avatar">
        <img src="img/IsotipoSloganNombre1.png" alt="logo">
    </div>

    <h4 class="login-title">Security</h4>

    <?php if (!empty($mensaje)): ?>
        <div class="error"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <p class="text-light mb-3">enter your 4-digit code to change your password</p>

    <form method="POST">

        <input 
            type="password"
            name="code"
            class="form-control text-center code-input mb-3"
            placeholder="****"
            maxlength="4"
            pattern="\d*"
            inputmode="numeric"
            required
        >

        <button type="submit" class="btn btn-login w-100">
            validate code
        </button>

    </form>

    <div class="forgot-password mt-3">
        <a href="login.php">cancel</a>
    </div>

</div>

</body>
</html>