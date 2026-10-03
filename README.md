# E-Commerce System Infrastructure (Dockerized)

Dự án này triển khai một hệ thống website thương mại điện tử (PHP & MySQL) tích hợp đầy đủ các dịch vụ hạ tầng tiêu chuẩn bao gồm Reverse Proxy, Giám sát hệ thống toàn diện (Monitoring), Quản lý Log tập trung (Centralized Logging với Loki + Promtail) và các biện pháp bảo mật chuyên sâu (Hardening), được đóng gói hoàn toàn bằng Docker Compose.

---

## 🏗️ Cấu trúc hệ thống (Architecture)

Hệ thống bao gồm 10 container chạy đồng thời, được phân lập thành 2 mạng (Frontend và Backend) để đảm bảo an toàn:

1. **Web App (`ecommerce_web`)**: Source code PHP của website bán hàng (chạy non-root user `www-data` trên port 8080).
2. **Database (`ecommerce_db`)**: Cơ sở dữ liệu MySQL 8.0 (nằm riêng biệt trong `backend_net`, không mở port ra host).
3. **Nginx (`ecommerce_nginx`)**: Web Server & Reverse Proxy, được cấu hình Security Headers, ẩn version và endpoint `/stub_status`.
4. **phpMyAdmin (`ecommerce_pma`)**: Công cụ quản trị giao diện web cho Database MySQL.
5. **Prometheus (`ecommerce_prometheus`)**: Hệ thống thu thập metrics (chỉ số hệ thống).
6. **cAdvisor (`ecommerce_cadvisor`)**: Thu thập chỉ số tài nguyên của các Docker containers.
7. **Nginx Exporter (`ecommerce_nginx_exporter`)**: Thu thập chỉ số lưu lượng và kết nối của Nginx Web Server.
8. **MySQL Exporter (`ecommerce_mysql_exporter`)**: Thu thập chỉ số truy vấn, kết nối, bộ đệm của Database MySQL.
9. **Grafana (`ecommerce_grafana`)**: Hiển thị biểu đồ giám sát tự động (Provisioning) và truy vấn Log.
10. **Loki & Promtail (`ecommerce_loki`, `ecommerce_promtail`)**: Thu thập và quản lý log tập trung từ tất cả containers.

---

## 🛡️ Các biện pháp bảo mật (Hardening) đã áp dụng (Tiêu chí 6)

Hệ thống đã triển khai đầy đủ **7 biện pháp bảo mật** từ tầng mạng, container, dịch vụ cho đến mã nguồn:

1. **Non-root Container:**
   - Ứng dụng Web PHP/Apache được cấu hình trong `dockerfile` để chạy dưới quyền người dùng không đặc quyền **`USER www-data`** trên cổng 8080 thay vì chạy dưới quyền `root`.
   - Kiểm tra xác thực: `docker exec ecommerce_web whoami` trả về `www-data`.
2. **Network Isolation (Phân lập mạng nội bộ):**
   - Phân tách thành 2 mạng bridge: `frontend_net` (hướng ngoài) và `backend_net` (nội bộ).
   - MySQL nằm hoàn toàn trong `backend_net`, không map cổng 3306 ra máy chủ host, ngăn chặn mọi truy cập trực tiếp từ Internet vào CSDL.
3. **Mật khẩu mạnh & Quản lý môi trường an toàn:**
   - Sử dụng mật khẩu phức tạp gồm chữ hoa, chữ thường, số và ký tự đặc biệt cho mọi tài khoản hệ thống (`Sec_Root_P@ssw0rd_2026!`, `Shop_User_P@ssw0rd_2026!`, `Exp_Monit0r_P@ssw0rd_2026!`, `Grafana_Adm1n_Sec2026!`).
   - Mật khẩu được quản lý qua file biến môi trường `.env` (được đưa vào `.gitignore` để tránh rò rỉ mã nguồn) và cung cấp file mẫu `.env.example`.
4. **Hạn chế quyền người dùng Database (Least Privilege DB User):**
   - Website PHP chỉ sử dụng tài khoản `shop_user` với quyền hạn tối thiểu (`SELECT, INSERT, UPDATE, DELETE`) trên database `ecommerce_db`, không có quyền `DROP TABLE`, `ALTER` hay can thiệp vào các bảng quản trị của MySQL.
   - Exporter chỉ được cấp quyền xem trạng thái (`PROCESS, REPLICATION CLIENT, SELECT`).
5. **Nginx Security Headers & Ẩn thông tin phiên bản (Server Information Disclosure):**
   - Cấu hình `server_tokens off;` để ẩn phiên bản Nginx (`Server: nginx`).
   - Cấu hình `proxy_hide_header X-Powered-By;` và `expose_php = Off` để ẩn hoàn toàn dấu vết công nghệ PHP backend.
   - Thiết lập các HTTP Security Headers tiêu chuẩn:
     - `X-Frame-Options: "SAMEORIGIN"` (Chống Clickjacking)
     - `X-Content-Type-Options: "nosniff"` (Chống MIME sniffing)
     - `X-XSS-Protection: "1; mode=block"` (Chống phản xạ XSS)
     - `Referrer-Policy: "strict-origin-when-cross-origin"`
     - `Permissions-Policy: "geolocation=(), microphone=(), camera=()"`
     - `Content-Security-Policy: "default-src 'self' ..."` (Ngăn ngừa chèn mã độc XSS)
6. **Ngăn chặn leo thang đặc quyền (no-new-privileges):**
   - Khai báo cờ `security_opt: - no-new-privileges:true` cho tất cả các container trọng yếu, ngăn chặn các tiến trình con bên trong container chiếm quyền qua cơ chế SUID/SGID.
7. **Phòng chống SQL Injection ở tầng mã nguồn (Application Hardening):**
   - Nâng cấp toàn bộ các truy vấn nhạy cảm (như đặt hàng trong `src/checkout.php`) sang sử dụng **Prepared Statements** (`$stmt->prepare()` và `$stmt->bind_param()`), triệt tiêu hoàn toàn nguy cơ tấn công SQL Injection.

---

## 📊 Hệ thống giám sát (Monitoring - Prometheus & Grafana)

Hệ thống được cấu hình tự động nạp cấu hình (**Provisioning**) Datasources và Dashboards:
- **Datasources tự động:** Prometheus (`http://prometheus:9090`) và Loki (`http://loki:3100`).
- **4 Dashboards chuyên biệt (thư mục *Ecommerce System* trên Grafana):**
  1. **Container Monitoring (`Cadvisor exporter`)**: Theo dõi CPU, RAM, Network I/O, Disk usage từng container.
  2. **Web Server Monitoring (`Nginx Web Server Metrics`)**: Theo dõi trạng thái Nginx, số lượng Request/giây (QPS), Active connections, Connection States (Reading/Writing/Waiting).
  3. **Database Monitoring (`MySQL Database Overview`)**: Theo dõi trạng thái MySQL, Uptime, Threads connected, Tốc độ truy vấn (QPS), Lưu lượng mạng In/Out, Top Commands (SELECT, INSERT...).
  4. **Log Centralization (`Centralized Logging (Loki & LogQL)`)**: Giám sát log tập trung trực quan thời gian thực, biểu đồ tần suất log, tách biệt log Nginx, Web, MySQL và phát hiện log lỗi tự động.

---

## 📜 Hệ thống Log tập trung (Loki & Promtail + LogQL)

Promtail tự động phát hiện Docker socket, chuẩn hóa nhãn `service` và `container` của toàn bộ dịch vụ trong hệ thống `ecommerce_*` để đẩy về Loki.

### 🔍 Hướng dẫn truy vấn LogQL mẫu để Demo (Grafana Explore):
Vào **Grafana** (`http://localhost:3000`) -> **Explore** -> Chọn data source **Loki**, sau đó chạy các câu query sau:

| STT | Mục đích truy vấn | Câu lệnh LogQL | Giải thích kỹ thuật |
| :---: | :--- | :--- | :--- |
| **1** | **Xem toàn bộ Access Log của Nginx** | `{service="nginx"}` | Lọc toàn bộ luồng log từ Web Server Nginx (đầy đủ IP client, method, URL, status code). |
| **2** | **Lọc các request HTTP cụ thể (GET/POST)** | `{service="nginx"} \|= "GET"` | Dùng toán tử lọc chuỗi `\|= "GET"` để trích xuất các request đọc trang hoặc API. |
| **3** | **Lọc lỗi truy cập HTTP 4xx hoặc 5xx** | `{service="nginx"} \|~ "HTTP/1.1\\\" [45]"` | Sử dụng Regex Filter `\|~` để tìm nhanh các lỗi Client (404, 403) hoặc lỗi Server (500, 502). |
| **4** | **Xem log hoạt động của Database MySQL** | `{service="db"}` | Theo dõi tiến trình khởi động, kết nối và trạng thái của MySQL Server. |
| **5** | **Xem log ứng dụng Web PHP** | `{service="web"}` | Giám sát các request xử lý qua Apache/PHP và các lỗi runtime/exception của mã nguồn. |
| **6** | **Thống kê số lượng log theo từng dịch vụ** | `sum by (service) (count_over_time({job="ecommerce_logs"}[1m]))` | Metric Query: Chuyển đổi dữ liệu Log thành biểu đồ thống kê lưu lượng log theo thời gian. |

---

## 🚀 Hướng dẫn cài đặt và chạy hệ thống

### Yêu cầu tiên quyết

- Đã cài đặt [Docker](https://www.docker.com/) và [Docker Compose](https://docs.docker.com/compose/).
- Git (để clone repository).

### Các bước khởi chạy

1. **Clone repository này về máy:**

   ```bash
   git clone <đường-dẫn-repo-github-của-bạn>
   cd ecommerce-project
   ```
2. **Khởi tạo file cấu hình biến môi trường:**

   ```bash
   cp .env.example .env
   ```
3. **Khởi chạy hệ thống bằng Docker Compose:**

   ```bash
   docker-compose up -d
   ```
4. **Đợi khoảng 30s - 1 phút** để các container khởi động hoàn toàn và Grafana tự động import đầy đủ Dashboards & Datasources.

---

## 🌐 Danh sách các cổng truy cập (Access Points)

Sau khi hệ thống chạy thành công, bạn có thể truy cập các dịch vụ qua trình duyệt:

| Dịch vụ | URL Truy cập | Ghi chú / Đăng nhập |
| :--- | :--- | :--- |
| **Website (Nginx)** | `http://localhost` | Giao diện web bán hàng bảo mật |
| **phpMyAdmin** | `http://localhost:8080` | Quản trị DB (User: `shop_user` / Pass: `Shop_User_P@ssw0rd_2026!` hoặc User: `root` / Pass: `Sec_Root_P@ssw0rd_2026!`) |
| **Grafana** | `http://localhost:3000` | User: `admin` / Pass: `Grafana_Adm1n_Sec2026!` (Đầy đủ 4 Dashboards sẵn sàng) |
| **Prometheus** | `http://localhost:9090` | Quản lý metrics và targets |
| **cAdvisor** | `http://localhost:8082` | Thông số tài nguyên container |
| **Loki API** | `http://localhost:3100` | Cổng lưu trữ và truy vấn log tập trung |
| **Nginx Metrics** | `http://localhost:9113/metrics` | Endpoint metrics Nginx |
| **MySQL Metrics** | `http://localhost:9104/metrics` | Endpoint metrics MySQL |

---

## 🛑 Dừng và dọn dẹp hệ thống

Để dừng hệ thống:

```bash
docker-compose down
```
