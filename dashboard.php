<?php
session_start();
// iniciar sesión y conexión a base de datos
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// permisos basados en rol
$user_role = $_SESSION['user_role'] ?? 4;

$all_tabs = [
    'authentication' => 'Authentication and Users',
    'appointments' => 'Appointments and Patients',
    'inventory' => 'Inventory and Products',
    'sales' => 'Sales and Payments',
];

$allowed_tabs = [];
switch ($user_role) {
    case 1: $allowed_tabs = array_keys($all_tabs); break;
    case 2: $allowed_tabs = ['appointments', 'inventory']; break;
    case 3: $allowed_tabs = ['inventory']; break;
    case 4: $allowed_tabs = ['appointments', 'sales']; break;
    default: $allowed_tabs = ['appointments']; break;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : ($allowed_tabs[0] ?? 'authentication');
if (!in_array($tab, $allowed_tabs, true)) { $tab = $allowed_tabs[0] ?? 'authentication'; }

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];
    try {
        // aquí van todas tus acciones POST (add_appointment, close_appointment, add_batch, etc.)
        // las copié igual que en tu código nuevo
        switch ($action) {
            case 'add_appointment':
                $patientId = (int)($_POST['patient_id'] ?? 0);
                $dentistId = (int)($_POST['assigned_user'] ?? $_SESSION['user_id']);
                $scheduledAt = $_POST['scheduled_at'] ?? '';
                $reason = trim($_POST['reason'] ?? '');
                if (!$patientId || !$dentistId || !$scheduledAt) {
                    throw new Exception('Missing data to create appointment.');
                }
                $dt = DateTime::createFromFormat('Y-m-d\TH:i', $scheduledAt);
                if (!$dt) { throw new Exception('Invalid date/time format for appointment.'); }
                $date = $dt->format('Y-m-d');
                $time = $dt->format('H:i:s');
                $stmt = $pdo->prepare("INSERT INTO appointments (id_patient, id_dentist_user, appointment_date, appointment_time, reason, id_appointment_status, duration_minutes, registration_date) VALUES (:patient_id, :dentist_id, :date, :time, :reason, 1, 30, NOW())");
                $stmt->execute([':patient_id'=>$patientId, ':dentist_id'=>$dentistId, ':date'=>$date, ':time'=>$time, ':reason'=>$reason]);
                $flash = 'Appointment registered successfully.'; $tab = 'appointments'; break;

            case 'close_appointment':
                $appointmentId = (int)($_POST['appointment_id'] ?? 0);
                $diagnostic = trim($_POST['diagnostic'] ?? '');
                $treatment = trim($_POST['treatment'] ?? '');
                if (!$appointmentId || !$diagnostic || !$treatment) { throw new Exception('Incomplete closure data.'); }
                $pdo->prepare("UPDATE appointments SET id_appointment_status = 2 WHERE id_appointment = :id")->execute([':id'=>$appointmentId]);
                $stmt = $pdo->prepare("INSERT INTO medical_histories (id_patient, id_appointment, diagnosis, treatment, notes, requires_control, next_control_date) SELECT id_patient, id_appointment, :diagnostic, :treatment, '', FALSE, NULL FROM appointments WHERE id_appointment = :id");
                $stmt->execute([':id'=>$appointmentId, ':diagnostic'=>$diagnostic, ':treatment'=>$treatment]);
                $flash = 'Appointment closed and medical history generated.'; $tab = 'appointments'; break;

            case 'add_batch':
                $productId = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                $expiry = $_POST['expiry_date'] ?? null;
                $batchNumber = trim($_POST['batch_number'] ?? '');
                if (!$productId || $quantity <= 0 || !$expiry || !$batchNumber) { throw new Exception('Incomplete batch data.'); }
                $stmt = $pdo->prepare("INSERT INTO batches (id_product, batch_number, entry_date, expiration_date, initial_quantity, current_quantity) VALUES (:product_id, :batch_number, CURDATE(), :expiration_date, :initial_quantity, :current_quantity)");
                $stmt->execute([':product_id'=>$productId, ':batch_number'=>$batchNumber, ':expiration_date'=>$expiry, ':initial_quantity'=>$quantity, ':current_quantity'=>$quantity]);
                $flash = 'Batch added successfully.'; $tab = 'inventory'; break;

            case 'add_inventory_movement':
                $batchId = (int)($_POST['batch_id'] ?? 0);
                $movementType = (int)($_POST['movement_type'] ?? 0);
                $quantity = (int)($_POST['movement_quantity'] ?? 0);
                $justification = trim($_POST['movement_reason'] ?? '');
                if (!$batchId || !$quantity || !$justification || !$movementType) { throw new Exception('Invalid movement data.'); }
                $qtySigned = $movementType === 2 ? -$quantity : $quantity;
                $pdo->prepare("INSERT INTO inventory_movements (id_user, id_batch, id_movement_type, quantity, justification) VALUES (:id_user, :id_batch, :id_type, :quantity, :justification)")->execute([':id_user'=>$_SESSION['user_id'], ':id_batch'=>$batchId, ':id_type'=>$movementType, ':quantity'=>$qtySigned, ':justification'=>$justification]);
                $pdo->prepare("UPDATE batches SET current_quantity = current_quantity + :adjust WHERE id_batch = :id_batch")->execute([':adjust'=>$qtySigned, ':id_batch'=>$batchId]);
                $flash = 'Movement registered and stock updated.'; $tab = 'inventory'; break;

            case 'create_sale':
                $patientId = (int)($_POST['sale_patient_id'] ?? 0);
                $batchId = (int)($_POST['sale_batch_id'] ?? 0);
                $saleQty = (int)($_POST['sale_quantity'] ?? 0);
                $price = (float)($_POST['sale_price'] ?? 0);
                if (!$patientId || !$batchId || $saleQty <= 0 || $price <= 0) { throw new Exception('Incomplete sale data.'); }
                $available = (int)$pdo->query("SELECT current_quantity FROM batches WHERE id_batch = $batchId")->fetchColumn();
                if ($saleQty > $available) { throw new Exception('Insufficient stock in the selected batch.'); }
                $pdo->beginTransaction();
                $subtotal = $saleQty * $price;
                $tax = 0; $total = $subtotal + $tax;
                $pdo->prepare("INSERT INTO sales (id_patient, id_user, id_appointment, subtotal, tax, total, payment_method, id_sale_status) VALUES (:id_patient, :id_user, NULL, :subtotal, :tax, :total, 'Cash', 1)")->execute([':id_patient'=>$patientId, ':id_user'=>$_SESSION['user_id'], ':subtotal'=>$subtotal, ':tax'=>$tax, ':total'=>$total]);
                $saleId = $pdo->lastInsertId();
                $batchProductId = (int)$pdo->query("SELECT id_product FROM batches WHERE id_batch = $batchId")->fetchColumn();
                $pdo->prepare("INSERT INTO sale_details (id_sale, id_product, id_movement, quantity, unit_price, subtotal) VALUES (:id_sale, :id_product, :id_movement, :quantity, :unit_price, :subtotal)")->execute([':id_sale'=>$saleId, ':id_product'=>$batchProductId, ':id_movement'=>NULL, ':quantity'=>$saleQty, ':unit_price'=>$price, ':subtotal'=>$subtotal]);
                $pdo->prepare("INSERT INTO inventory_movements (id_user, id_batch, id_movement_type, quantity, justification) VALUES (:id_user, :id_batch, 2, :quantity, 'Sale')")->execute([':id_user'=>$_SESSION['user_id'], ':id_batch'=>$batchId, ':quantity'=>-$saleQty]);
                $movementId = $pdo->lastInsertId();
                $pdo->prepare("UPDATE batches SET current_quantity = current_quantity - :d WHERE id_batch = :id_batch")->execute([':d'=>$saleQty, ':id_batch'=>$batchId]);
                $pdo->prepare("UPDATE sale_details SET id_movement = :id_movement WHERE id_sale = :id_sale")->execute([':id_movement'=>$movementId, ':id_sale'=>$saleId]);
                $pdo->commit();
                $flash = 'Sale registered successfully.'; $tab = 'sales'; break;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $flash = 'Error: ' . $e->getMessage();
    }
    header('Location: dashboard.php?tab=' . urlencode($tab) . '&msg=' . urlencode($flash));
    exit;
}

$flash = $_GET['msg'] ?? '';

// Carga de datos para módulos (igual que antes)
$users_data = $pdo->query("SELECT id_user, full_name, email, id_role, active FROM users ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$products_data = $pdo->query("SELECT id_product, product_name FROM products ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
$patients_data = $pdo->query("SELECT id_patient, CONCAT(first_name,' ',last_name) AS full_name, id_card, phone FROM patients ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
$appointments_data = $pdo->query("SELECT a.id_appointment, CONCAT(p.first_name,' ',p.last_name) AS patient_name, u.full_name AS doctor_name, a.appointment_date, a.appointment_time, s.status_name AS status, a.reason FROM appointments a LEFT JOIN patients p ON a.id_patient=p.id_patient LEFT JOIN users u ON a.id_dentist_user=u.id_user LEFT JOIN appointment_statuses s ON a.id_appointment_status=s.id_status ORDER BY a.appointment_date DESC, a.appointment_time DESC")->fetchAll(PDO::FETCH_ASSOC);
$batches_data = $pdo->query("SELECT b.id_batch, b.id_product, b.batch_number, b.current_quantity, b.expiration_date, b.initial_quantity, p.product_name FROM batches b LEFT JOIN products p ON b.id_product=p.id_product ORDER BY b.expiration_date ASC")->fetchAll(PDO::FETCH_ASSOC);
$movements_data = $pdo->query("SELECT im.id_movement, b.id_batch AS batch_id, p.product_name AS product_name, im.quantity, mt.type_name AS type, im.justification, im.movement_date, u.full_name AS user_name FROM inventory_movements im LEFT JOIN batches b ON im.id_batch=b.id_batch LEFT JOIN products p ON b.id_product=p.id_product LEFT JOIN users u ON im.id_user=u.id_user LEFT JOIN movement_types mt ON im.id_movement_type=mt.id_type ORDER BY im.movement_date DESC")->fetchAll(PDO::FETCH_ASSOC);
$sales_data = $pdo->query("SELECT s.id_sale, s.id_patient, CONCAT(p.first_name,' ',p.last_name) AS patient_name, s.total, s.sale_date, u.full_name AS user_name, ss.status_name AS status FROM sales s LEFT JOIN patients p ON s.id_patient=p.id_patient LEFT JOIN users u ON s.id_user=u.id_user LEFT JOIN sale_statuses ss ON s.id_sale_status=ss.id_status ORDER BY s.sale_date DESC")->fetchAll(PDO::FETCH_ASSOC);
$sale_details = $pdo->query("SELECT sd.id_sale, p.product_name, sd.quantity, sd.unit_price, sd.subtotal, sd.id_movement FROM sale_details sd LEFT JOIN products p ON sd.id_product=p.id_product")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<!-- bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- css -->
<link rel="stylesheet" href="css/style.css">
</head>

<?php /* body */ ?>
<body>

<!-- layout principal -->
<div class="main-layout">

    <!-- sidebar -->
    <div class="sidebar">

        <!-- buscador -->
        <input type="text" class="search-bar" placeholder="Search...">

        <!-- usuario -->
        <div class="user-box">
                <!-- logo -->
            <div class="user-avatar">
                <img src="img/Isotipo.png" alt="Logo">
            </div>

            <p><strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></strong></p>

        </div>

        <!-- botones sidebar -->
        <?php foreach ($allowed_tabs as $key): ?>
            <button class="side-btn <?php echo $tab === $key ? 'active' : ''; ?>" onclick="tab('<?php echo $key; ?>')">
                <?php echo htmlspecialchars($all_tabs[$key]); ?>
            </button>
        <?php endforeach; ?>

        <!-- logout -->
        <a href="logout.php" class="logout-btn mt-3">Sign Out</a>

        <!-- Modo oscuro -->
        <button id="darkModeBtn" class="logout-btn mt-2">Dark Mode</button>

    </div>

    <!-- contenido principal -->
    <div class="main-content">

        <div class="content">

            <!-- mensaje flash -->
            <?php if (!empty($flash)): ?>
                <div class="flash-message"><?php echo htmlspecialchars($flash); ?></div>
            <?php endif; ?>

            <!-- módulos -->
            <?php include __DIR__ . '/modules/authentication.php'; ?>
            <?php include __DIR__ . '/modules/appointments.php'; ?>
            <?php include __DIR__ . '/modules/inventory.php'; ?>
            <?php include __DIR__ . '/modules/sales.php'; ?>

        </div>
    </div>

</div>

<!-- script para cambiar pestañas -->
<script>
function tab(value){
    const sections = ['authentication','appointments','inventory','sales'];

    sections.forEach(section => {
        const el = document.getElementById(section);
        if(el) el.style.display = section === value ? 'block' : 'none';
    });

    document.querySelectorAll('.side-btn').forEach(btn => 
        btn.classList.remove('active'));

    document.querySelectorAll('.side-btn').forEach(btn => {
        if(btn.getAttribute('onclick').includes(value)) 
            btn.classList.add('active');
    });

    history.replaceState(null,null,'?tab='+value);

    // ocultar mensaje si NO es el módulo correcto
    const flash = document.querySelector('.flash-message');
    if (flash && value !== '<?php echo $tab; ?>') {
        flash.style.display = 'none';
    }
}

// carga inicial
tab('<?php echo $tab; ?>');


// DOM listo
document.addEventListener("DOMContentLoaded", function() {

    // auto ocultar mensaje después de 2 minutos
    const flash = document.querySelector('.flash-message');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = "opacity 0.5s ease";
            flash.style.opacity = "0";
            setTimeout(() => flash.remove(), 500);
        }, 120000);
    }

    // modo oscuro
    const btn = document.getElementById("darkModeBtn");

    if (btn) {
        btn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("modo", "oscuro");
            } else {
                localStorage.setItem("modo", "claro");
            }
        });
    }

    // cargar preferencia guardada
    if (localStorage.getItem("modo") === "oscuro") {
        document.body.classList.add("dark-mode");
    }

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>