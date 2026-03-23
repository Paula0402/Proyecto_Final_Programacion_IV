<?php
session_start();
require_once 'config/db.php';

$mensaje = "";
$paso = 1; // 1: Pedir número, 2: Pedir código

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // PASO 1: Verificar el teléfono y generar código
    if (isset($_POST['phone'])) {
        $phone = trim($_POST['phone']);
        
        // Buscamos si el teléfono existe en la tabla users (ajusta el nombre de la columna si es necesario)
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE phone = :phone LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        $user = $stmt->fetch();

        if ($user) {
            // Generamos un código aleatorio de 6 dígitos
            $codigo = rand(100000, 999999);
            $_SESSION['reset_user_id'] = $user['id_user'];
            $_SESSION['reset_code'] = $codigo;
            
            // Aquí normalmente enviarías el SMS con una API (Twilio, etc.)
            // Por ahora, lo guardamos en sesión para probar
            $mensaje = "The code has been sent to " . $phone;
            $paso = 2; 
        } else {
            $mensaje = "The phone number is not registered.";
        }
    }

    // PASO 2: Verificar el código ingresado
    if (isset($_POST['code'])) {
        $inputCode = trim($_POST['code']);
        
        if ($inputCode == $_SESSION['reset_code']) {
            // Código correcto: Redirigir a cambiar contraseña
            header("Location: reset_new_password.php");
            exit;
        } else {
            $mensaje = "El código ingresado es incorrecto.";
            $paso = 2;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recover Password</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .msg { color: #d9534f; margin-bottom: 10px; font-size: 14px; }
        .code-display { background: #e9ecef; padding: 10px; border-radius: 5px; margin: 10px 0; font-weight: bold; color: #333; }
    </style>
</head>
<body>

<div class="card">
    <h2>Recover Password</h2>
    
    <?php if ($mensaje): ?>
        <p class="msg"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <?php if ($paso === 1): ?>
        <p>Enter your registered phone number to receive an access code.</p>
        <form method="POST">
            <input type="text" name="phone" placeholder="e.g.: 8888-8888" required>
            <button type="submit">Send Code</button>
        </form>
    <?php else: ?>
        <p>Enter the 6-digit code sent to your phone:</p>
        <div class="code-display">Your code is: <?php echo $_SESSION['reset_code']; ?></div>
        
        <form method="POST">
            <input type="number" name="code" placeholder="000000" required>
            <button type="submit" style="background-color: #28a745;">Verify Code</button>
        </form>
    <?php endif; ?>

    <br>
    <a href="index.php" style="font-size: 13px; color: #666; text-decoration: none;">Back to Home</a>
</div>

</body>
</html>