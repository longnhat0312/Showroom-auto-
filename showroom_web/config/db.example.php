<?php
// Khi cài trên máy khác, sao chép thành db.php rồi điền tài khoản MariaDB/MySQL.
// Project hiện tại đã có db.php; không ghi đè tệp đó bằng ví dụ này.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('127.0.0.1', 'showroom_app', 'DIEN_MAT_KHAU_CSDL', 'showroom_auto', 3306);
mysqli_set_charset($conn, 'utf8mb4');
