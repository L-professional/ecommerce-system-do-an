<?php
session_start();
require_once 'db.php';
$result = $conn->query("SELECT * FROM products");
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - E-Commerce Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        /* Tùy chỉnh Navbar */
        .navbar-custom { background-color: #121820; padding: 15px 0; }
        .search-box { background-color: #1e252d; border: 1px solid #333; color: white; border-radius: 4px; padding-right: 35px;}
        .search-box:focus { background-color: #2a343f; color: white; border-color: #0d6efd; box-shadow: none; }
        .search-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #888; }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(to right, rgba(18,24,32,0.9) 0%, rgba(18,24,32,0.6) 50%, rgba(18,24,32,0.1) 100%), 
                        url('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
            padding: 120px 0;
            color: white;
        }
        .hero-badge { border-left: 3px solid #0d6efd; padding-left: 10px; font-size: 0.9rem; font-weight: 600; letter-spacing: 1px;}
        .btn-hero { background-color: #0d6efd; border: none; padding: 10px 25px; border-radius: 30px; font-weight: 600; }
        .btn-hero:hover { background-color: #0b5ed7; }
        
        /* Product Cards */
        .section-title { font-weight: 800; font-size: 1.8rem; color: #222; }
        .product-card { border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .product-card:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.1); transform: translateY(-3px); }
        .product-title { font-weight: 700; font-size: 1.1rem; color: #333; }
        .product-price { font-weight: 800; color: #ff0d2b; font-size: 1.1rem; }
        .btn-add-cart { background-color: #0d6efd; border: none; border-radius: 6px; font-weight: 600; padding: 10px;}
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <i class="bi bi-bag-check fs-4 me-2"></i> E-Commerce Pro
            </a>
            
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active fw-bold border-bottom border-primary border-2" href="#"><i class="bi bi-house-door-fill me-1"></i> Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Giới thiệu</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Liên hệ</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm search-box" placeholder="Tìm kiếm sản phẩm...">
                    <i class="bi bi-search search-icon"></i>
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

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <p class="hero-badge text-uppercase mb-3">Cửa hàng công nghệ</p>
                    <h1 class="display-4 fw-bold mb-4">Sản phẩm chất lượng<br>cho cuộc sống hiện đại</h1>
                    <p class="lead mb-4" style="color: #ccc; font-size: 1rem; max-width: 500px;">Khám phá những sản phẩm công nghệ, phụ kiện, thiết bị điện tử tốt nhất với giá cả hợp lý và dịch vụ uy tín.</p>
                    <a href="#products" class="btn btn-hero"><i class="bi bi-cart-plus me-1"></i> Mua sắm ngay <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Section -->
    <div class="container py-5" id="products">
        <div class="text-center mb-5">
            <h2 class="section-title text-uppercase">Danh sách sản phẩm</h2>
            <p class="text-muted small">Lựa chọn những thiết bị công nghệ tốt nhất</p>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $img_url = !empty($row["image_url"]) ? $row["image_url"] : "https://via.placeholder.com/300";
                    echo '
                    <div class="col">
                        <div class="card h-100 product-card p-3">
                            <img src="'.$img_url.'" class="card-img-top mx-auto" alt="'.$row["name"].'" style="height: 180px; width: auto; object-fit: contain;">
                            <div class="card-body text-center mt-3 p-0">
                                <h5 class="product-title mb-2">'.$row["name"].'</h5>
                                <p class="product-price mb-3">$'.number_format($row["price"], 2).'</p>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="'.$row["id"].'">
                                    <button type="submit" class="btn btn-primary btn-add-cart w-100">
                                        <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>';
                }
            }
            $conn->close();
            ?>
        </div>
    </div>

    <footer class="navbar-custom text-white text-center py-3 mt-4">
        <p class="mb-0 small" style="color: #888;">&copy; 2026 E-Commerce Pro. Designed by Phung Van Loc.</p>
    </footer>

</body>
</html>