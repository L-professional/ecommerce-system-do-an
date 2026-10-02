<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$pid] = isset($_SESSION['cart'][$pid]) ? $_SESSION['cart'][$pid] + 1 : 1;
    header("Location: cart.php"); exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    unset($_SESSION['cart']); header("Location: cart.php"); exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    unset($_SESSION['cart'][(int)$_GET['id']]); header("Location: cart.php"); exit;
}
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - E-Commerce Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
        .navbar-custom { background-color: #121820; padding: 15px 0; }
        .search-box { background-color: #1e252d; border: 1px solid #333; color: white; border-radius: 4px; padding-right: 35px;}
        
        .cart-header { font-size: 1.5rem; font-weight: 800; color: #222; margin-bottom: 25px;}
        .cart-table th { background-color: #f8f9fa; font-weight: 700; color: #555; border-bottom: none; }
        .cart-table td { vertical-align: middle; background-color: #fff; border-bottom: 1px solid #f0f0f0; }
        .text-red { color: #ff0d2b !important; font-weight: 800; }
        
        .qty-box { display: flex; align-items: center; background: #f8f9fa; border-radius: 4px; width: fit-content; margin: 0 auto;}
        .qty-btn { border: none; background: transparent; padding: 5px 12px; cursor: pointer; color: #555; }
        .qty-input { width: 40px; text-align: center; border: none; background: transparent; font-weight: 600; }
        
        .summary-card { background-color: #fff; border-radius: 8px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); padding: 25px;}
        .btn-checkout { background-color: #0d6efd; color: white; font-weight: 600; padding: 12px; border-radius: 6px; }
        .btn-clear { border: 1px solid #ff0d2b; color: #ff0d2b; font-weight: 600; padding: 12px; border-radius: 6px; background: white;}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <i class="bi bi-bag-check fs-4 me-2"></i> E-Commerce Pro
            </a>
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill me-1"></i> Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Giới thiệu</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Liên hệ</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm search-box" placeholder="Tìm kiếm sản phẩm...">
                    <i class="bi bi-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #888;"></i>
                </div>
                <a href="cart.php" class="btn btn-outline-light btn-sm rounded-1 px-3 py-1 position-relative">
                    <i class="bi bi-cart2"></i> Giỏ hàng
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        <?php echo $cart_count; ?>
                    </span>
                </a>
                <a href="#" class="text-light text-decoration-none small"><i class="bi bi-person me-1"></i> Đăng nhập</a>
            </div>
        </div>
    </nav>

    <!-- Main Cart Area -->
    <div class="container flex-grow-1">
        <h2 class="cart-header"><i class="bi bi-cart3"></i> Giỏ hàng của bạn</h2>
        
        <div class="row">
            <!-- Left Column: Products -->
            <div class="col-lg-8 mb-4">
                <div class="table-responsive rounded bg-white shadow-sm">
                    <table class="table cart-table mb-0 text-center">
                        <thead>
                            <tr>
                                <th class="text-start ps-4">Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_amount = 0;
                            if (!empty($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $id => $quantity) {
                                    $result = $conn->query("SELECT * FROM products WHERE id = $id");
                                    if ($row = $result->fetch_assoc()) {
                                        $subtotal = $row['price'] * $quantity;
                                        $total_amount += $subtotal;
                                        $img_url = !empty($row["image_url"]) ? $row["image_url"] : "https://via.placeholder.com/80";
                                        
                                        echo "
                                        <tr>
                                            <td class='text-start ps-4 d-flex align-items-center'>
                                                <img src='$img_url' alt='img' style='width: 60px; height: 60px; object-fit: contain; border: 1px solid #eee; border-radius: 4px;' class='me-3 p-1'>
                                                <div>
                                                    <div class='fw-bold text-dark' style='font-size: 0.95rem;'>{$row['name']}</div>
                                                    <a href='cart.php?action=remove&id={$id}' class='text-danger text-decoration-none small'><i class='bi bi-trash'></i> Xóa</a>
                                                </div>
                                            </td>
                                            <td class='text-red'>$" . number_format($row['price'], 2) . "</td>
                                            <td>
                                                <div class='qty-box'>
                                                    <button class='qty-btn'>-</button>
                                                    <input type='text' class='qty-input' value='$quantity' readonly>
                                                    <button class='qty-btn'>+</button>
                                                </div>
                                            </td>
                                            <td class='text-red'>$" . number_format($subtotal, 2) . "</td>
                                        </tr>";
                                    }
                                }
                            } else {
                                echo "<tr><td colspan='4' class='py-5 text-muted'>Giỏ hàng đang trống.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="index.php" class="text-decoration-none text-primary fw-semibold"><i class="bi bi-arrow-left"></i> Tiếp tục mua sắm</a>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="fw-bold mb-4">Tổng cộng</h5>
                    <div class="text-center mb-4">
                        <span class="text-red" style="font-size: 2rem;">$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="checkout.php" class="btn btn-checkout <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>">
                            <i class="bi bi-cart-check"></i> Thanh toán
                        </a>
                        <a href="cart.php?action=clear" class="btn btn-clear">
                            <i class="bi bi-trash"></i> Xóa giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="navbar-custom text-white text-center py-3 mt-5">
        <p class="mb-0 small" style="color: #888;">&copy; 2026 E-Commerce Pro. Designed by Phung Van Loc.</p>
    </footer>

</body>
</html>