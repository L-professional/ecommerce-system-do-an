<?php
// Cấu hình kết nối Database sử dụng biến môi trường hoặc mật khẩu mạnh mặc định
$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'shop_user';
$db_pass = getenv('DB_PASSWORD') ?: 'Shop_User_P@ssw0rd_2026!';
$db_name = getenv('DB_NAME') ?: 'ecommerce_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}
