<?php
if (!defined('SHOWROOM_APP')) {
    http_response_code(404);
    exit;
}

// Sao chép thành database.local.php rồi điền mật khẩu của bạn.
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'showroom_auto',
    'username' => 'showroom_app',
    'password' => '',
];
