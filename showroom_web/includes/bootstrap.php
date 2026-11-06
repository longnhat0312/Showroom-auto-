<?php
declare(strict_types=1);

const BASE_URL = '/Showroom_LongNhat/showroom_web';
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => BASE_URL . '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/../config/db.php';
mysqli_query($conn, "SET time_zone = '+07:00'");
require_once __DIR__ . '/functions.php';
