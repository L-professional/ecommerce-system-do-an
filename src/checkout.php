<?php
session_start();
require_once 'db.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['customer_name'])) {
    $name = trim($_POST['customer_name']);
    $total_amount = 0;
    
    // Tính lại tổng tiền từ DB cho an toàn
    if (!empty($_SESSION['cart']) && !empty($name)) {
        foreach ($_SESSION['cart'] as $id => $quantity) {
            $pid = (int)$id;
            $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt_price->bind_param("i", $pid);
            $stmt_price->execute();
            $res = $stmt_price->get_result();
            if ($row = $res->fetch_assoc()) {
                $total_amount += $row['price'] * (int)$quantity;
            }
            $stmt_price->close();
        }
        
        // Hardening: Sử dụng Prepared Statements để phòng chống SQL Injection tuyệt đối
        $stmt_order = $conn->prepare("INSERT INTO orders (customer_name, total_amount, order_date) VALUES (?, ?, NOW())");
        $stmt_order->bind_param("sd", $name, $total_amount);
        
        if ($stmt_order->execute()) {
            $message = "Đặt hàng thành công! Cảm ơn bạn đã mua sắm.";
            $messageType = "success";
            unset($_SESSION['cart']); // Xóa giỏ hàng sau khi đặt thành công
        } else {
            $message = "Có lỗi xảy ra trong quá trình đặt hàng.";
            $messageType = "danger";
        }
        $stmt_order->close();
    } else {
        $message = "Vui lòng nhập tên khách hàng và kiểm tra lại giỏ hàng!";
        $messageType = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - E-Commerce Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>body { background-color: #f8f9fa; }</style>
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-shop me-2"></i>E-Commerce Pro
            </a>
            <div class="d-flex">
                <a href="cart.php" class="btn btn-outline-light position-relative">
                    <i class="bi bi-cart3"></i> Giỏ hàng
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-4">
                    <h3 class="fw-bold mb-4 text-center"><i class="bi bi-wallet2"></i> Thông tin thanh toán</h3>
                    
                    <?php if($message != ""): ?>
                        <div class="alert alert-<?php echo $messageType; ?> text-center fw-bold" role="alert">
                            <?php echo $message; ?>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary mt-3">Quay về trang chủ</a>
                        </div>
                    <?php else: ?>
                        <form action="checkout.php" method="POST">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label fw-bold">Họ và tên khách hàng</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Ví dụ: Nguyễn Văn A" required>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg fw-bold">Xác nhận đặt hàng</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="cart.php" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại giỏ hàng</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container"><p class="mb-0">&copy; 2026 E-Commerce Pro. Designed by Phung Van Loc.</p></div>
    </footer>
</body>
</html>