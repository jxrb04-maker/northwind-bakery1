<?php
// เชื่อมต่อฐานข้อมูล (ดึงค่าจาก Environment Variable ของ Railway)
$host = getenv('MYSQLHOST') ?: 'roundhouse.proxy.rlwy.net'; // เปลี่ยนเป็น Host ของคุณถ้าทดสอบในเครื่อง
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'dbUYribAfSnxaHcpuwuhXaIxIfMvkCGW';
$db   = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$alertMessage = "";
$alertType = "";

// ------------------- CRUD Logic -------------------

// 1. เพิ่มสินค้าใหม่ (Create)
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['ProductName']);
    $price = $_POST['UnitPrice'];
    $stock = $_POST['UnitsInStock'];

    // Validation
    if (empty($name) || !is_numeric($price) || !is_numeric($stock) || $price < 0 || $stock < 0) {
        $alertMessage = "กรุณากรอกข้อมูลให้ถูกต้องและครบถ้วน (ราคาและสต็อกต้องเป็นตัวเลขไม่ติดลบ)";
        $alertType = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (ProductName, CategoryName, UnitPrice, UnitsInStock) VALUES (?, 'Bakery', ?, ?)");
        $stmt->bind_param("sdi", $name, $price, $stock);
        if ($stmt->execute()) {
            $alertMessage = "เพิ่มรายการเบเกอรี่เรียบร้อยแล้ว!";
            $alertType = "success";
        } else {
            $alertMessage = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            $alertType = "danger";
        }
    }
}

// 2. แก้ไขสินค้า (Update)
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['ProductID'];
    $name = trim($_POST['ProductName']);
    $price = $_POST['UnitPrice'];
    $stock = $_POST['UnitsInStock'];

    if (empty($name) || !is_numeric($price) || !is_numeric($stock)) {
        $alertMessage = "กรุณากรอกข้อมูลการแก้ไขให้ถูกต้อง";
        $alertType = "danger";
    } else {
        $stmt = $conn->prepare("UPDATE products SET ProductName=?, UnitPrice=?, UnitsInStock=? WHERE ProductID=?");
        $stmt->bind_param("sdii", $name, $price, $stock, $id);
        if ($stmt->execute()) {
            $alertMessage = "อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว!";
            $alertType = "success";
        }
    }
}

// 3. ลบสินค้า (Delete)
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['ProductID'];
    $stmt = $conn->prepare("DELETE FROM products WHERE ProductID=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $alertMessage = "ลบรายการสินค้าเรียบร้อยแล้ว!";
        $alertType = "success";
    }
}

// 4. ค้นหา/ดึงข้อมูล (Read)
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE ProductName LIKE ? ORDER BY ProductID DESC");
    $searchTerm = "%{$search}%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM products ORDER BY ProductID DESC");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Northwind Bakery - Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.net/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #fdfaf6; font-family: 'Kanit', sans-serif; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-bakery { background-color: #d97706; color: white; }
        .btn-bakery:hover { background-color: #b45309; color: white; }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="fw-bold text-dark"><i class="fa-solid fa-cake-candles text-warning"></i> Northwind Bakery Store</h1>
        <p class="text-muted">ระบบจัดการสต็อกและรายการสินค้าเบเกอรี่ (Product Management System)</p>
    </div>

    <!-- Notification Alert -->
    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
            <i class="fa-solid <?= $alertType == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
            <?= $alertMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form เพิ่มสินค้า -->
    <div class="card p-4 mb-4">
        <h5 class="card-title fw-bold mb-3"><i class="fa-solid fa-plus-circle"></i> เพิ่มเมนูใหม่</h5>
        <form method="POST" action="index.php" class="row g-3">
            <input type="hidden" name="action" value="add">
            <div class="col-md-5">
                <input type="text" name="ProductName" class="form-control" placeholder="ชื่อสินค้า (เช่น Strawberry Shortcake)" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="UnitPrice" class="form-control" placeholder="ราคา (บาท)" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="UnitsInStock" class="form-control" placeholder="จำนวนสต็อก" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-bakery w-100"><i class="fa-solid fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>

    <!-- Search & Table -->
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list"></i> รายการสินค้าทั้งหมด</h5>
            <form method="GET" action="index.php" class="d-flex w-50">
                <input type="text" name="search" class="form-control me-2" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อเมนู</th>
                        <th>ราคา (บาท)</th>
                        <th>สต็อก</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['ProductID'] ?></td>
                                <td class="fw-bold text-secondary"><?= htmlspecialchars($row['ProductName']) ?></td>
                                <td>฿<?= number_format($row['UnitPrice'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['UnitsInStock'] > 10 ? 'success' : 'danger' ?>">
                                        <?= $row['UnitsInStock'] ?> ชิ้น
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- ปุ่ม Edit Modal Trigger -->
                                    <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['ProductID'] ?>">
                                        <i class="fa-solid fa-pen"></i> แก้ไข
                                    </button>
                                    
                                    <!-- ปุ่ม Delete -->
                                    <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="ProductID" value="<?= $row['ProductID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> ลบ</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $row['ProductID'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="index.php">
                                            <div class="modal-header">
                                                <h5 class="modal-title">แก้ไขรายการ #<?= $row['ProductID'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="ProductID" value="<?= $row['ProductID'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">ชื่อสินค้า</label>
                                                    <input type="text" name="ProductName" class="form-control" value="<?= htmlspecialchars($row['ProductName']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">ราคา (บาท)</label>
                                                    <input type="number" step="0.01" name="UnitPrice" class="form-control" value="<?= $row['UnitPrice'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">จำนวนสต็อก</label>
                                                    <input type="number" name="UnitsInStock" class="form-control" value="<?= $row['UnitsInStock'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-primary">อัปเดต</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลสินค้า</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>