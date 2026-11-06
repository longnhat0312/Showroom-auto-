# Showroom Auto — bản khởi đầu PHP thuần

Bản này dùng PHP thông thường, MySQLi, HTML/CSS và MariaDB trong XAMPP, theo lựa chọn học tập của người viết. Đây là phần khởi tạo gồm trang chủ và kết nối CSDL; các chức năng đăng nhập, danh mục xe, đặt cọc và thanh toán sẽ được xây ở bước tiếp theo.

## Bản đã đặt trên máy của bạn

Thư mục chạy: `C:\xampp\htdocs\Showroom_LongNhat\showroom-php`

Mở bằng trình duyệt: http://localhost/Showroom_LongNhat/showroom-php/

Bật Apache và dịch vụ CSDL trong XAMPP khi sử dụng. Chạy qua URL localhost để PHP được xử lý; không mở index.php bằng cách nhấp đúp tệp trên máy.

Bản Laravel vẫn ở `C:\xampp\htdocs\Showroom_LongNhat\showroom-auto`. Bản PHP thuần dùng lại database showroom_auto và tài khoản showroom_app đang hoạt động. Bước khởi tạo không tạo/xóa bảng hoặc thay dữ liệu nghiệp vụ.

## Cấu trúc dễ theo dõi

```text
showroom-php/
  index.php                       Trang chủ
  config/
    db.php                        Mở kết nối, tạo biến $conn
    database.example.php          Mẫu thông số kết nối
    database.local.php            Thông số thật trên máy, không có trong gói chia sẻ
  includes/
    header.php                    Đầu trang và menu dùng chung
    footer.php                    Cuối trang dùng chung
  assets/
    style.css                     CSS
  tools/
    check-db.php                  Lệnh kiểm tra kết nối
```

Trang chủ gọi config/db.php bằng require, rồi dùng include để ghép header/footer. Cú pháp `<?= ... ?>` là PHP thông thường. Không có cú pháp Blade trong bản này.

## Kết nối theo kiểu file config

phpMyAdmin là công cụ để tạo database, bảng và quản lý tài khoản. Mã PHP kết nối tới MariaDB thông qua thư viện MySQLi.

Thông số thật được lưu trong `config/database.local.php`. Tệp `config/db.php` đọc thông số đó và gọi:

```php
$conn = mysqli_connect(
    $database['host'],
    $database['username'],
    $database['password'],
    $database['database'],
    (int) $database['port']
);
mysqli_set_charset($conn, 'utf8mb4');
```

Hai tệp config tách riêng để phần kết nối dễ dùng lại và bản mã nguồn chia sẻ không chứa mật khẩu thật. Nếu chỉ học cách kết nối, tập trung vào lời gọi mysqli_connect và biến $conn.

Trong một trang PHP mới đặt ở thư mục gốc, dùng:

```php
<?php
define('SHOWROOM_APP', true);
require __DIR__ . '/config/db.php';

// Từ đây dùng $conn để truy vấn CSDL.
```

SHOWROOM_APP là dấu hiệu cho biết config đang được nạp từ trang của ứng dụng; truy cập trực tiếp các tệp config qua URL sẽ bị từ chối. __DIR__ giúp đường dẫn tới config luôn tính từ thư mục của tệp PHP đang chạy.

## Kiểm tra kết nối

Mở terminal tại thư mục showroom-php rồi chạy đúng dòng lệnh bên trong khối mã:

```powershell
php tools/check-db.php
```

Kết quả đúng gồm Database connection successful và tên database showroom_auto. Mật khẩu không được in ra. Đây là lệnh PHP thuần; không dùng php artisan.

## Nếu lấy bản ZIP sang máy khác

1. Giải nén vào thư mục htdocs của XAMPP.
2. Tạo database và tài khoản trong phpMyAdmin của máy đó.
3. Sao chép config/database.example.php thành config/database.local.php.
4. Điền đúng host, port, tên database, tài khoản và mật khẩu trong database.local.php.
5. Bật Apache và CSDL, chạy lệnh kiểm tra rồi mở URL localhost theo đường dẫn thư mục.

Gói ZIP không chứa cấu hình có mật khẩu của máy hiện tại. Các thư viện Composer của dự án Laravel không cần cho việc chạy bản PHP thuần này.

## Thứ tự học và xây tiếp theo báo cáo

| Thứ tự | Phần thực hành |
|---|---|
| 1 | Hiểu index.php, config kết nối và include header/footer |
| 2 | Tạo các bảng dữ liệu gốc bằng SQL trong phpMyAdmin; lưu lại tệp SQL để dựng lại trên máy khác |
| 3 | Đăng ký, đăng nhập, đăng xuất bằng session; phân quyền khách/nhân viên/admin |
| 4 | Nhập hãng, dòng, cấu hình và VIN; trang danh sách/chi tiết và bộ lọc xe |
| 5 | Giỏ hàng, khuyến mãi, lịch hẹn, tư vấn và báo giá; thêm dữ liệu tương ứng khi làm chức năng |
| 6 | Đơn đặt cọc/hợp đồng chung, giữ VIN có thời hạn bằng transaction |
| 7 | Thanh toán/hoàn tiền, callback có xác minh và chống ghi trùng |
| 8 | Bàn giao, đánh giá theo hợp đồng, báo cáo và các tích hợp đã giữ trong báo cáo |

Mật khẩu tài khoản website dùng password_hash/password_verify; tài khoản đang đăng nhập lưu trong session. Truy vấn nhận dữ liệu từ form dùng prepared statement (mysqli_prepare và mysqli_stmt_bind_param). Không nối trực tiếp dữ liệu form vào câu SQL. Kiểm tra quyền ở từng trang xử lý, không chỉ ở menu.

PHP thuần vẫn hỗ trợ class và lập trình hướng đối tượng. Khi xây nghiệp vụ xe/hợp đồng/thanh toán, có thể tách lớp PHP để bám mô hình trong báo cáo; phần giao diện vẫn là các tệp .php và include quen thuộc.
