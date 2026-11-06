<?php
if (!defined('SHOWROOM_APP')) {
    http_response_code(404);
    exit;
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="brand" href="index.php">SHOWROOM<span>AUTO</span></a>
            <nav aria-label="Điều hướng chính">
                <a href="index.php" aria-current="page">Trang chủ</a>
                <a href="#gioi-thieu">Giới thiệu</a>
            </nav>
        </div>
    </header>
