<?php
define('SHOWROOM_APP', true);

try {
    require __DIR__ . '/config/db.php';
} catch (Throwable $exception) {
    error_log('Showroom database connection failed. Code: ' . $exception->getCode());
    http_response_code(503);
    exit('Website tạm thời chưa kết nối được cơ sở dữ liệu.');
}

$pageTitle = 'Showroom Auto';
include __DIR__ . '/includes/header.php';
?>

<main class="container">
    <section class="intro" id="gioi-thieu">
        <p class="eyebrow">SHOWROOM AUTO</p>
        <h1>Tìm chiếc xe phù hợp<br>với hành trình của bạn.</h1>
        <p class="description">Website giới thiệu xe và hỗ trợ quá trình tư vấn, đặt lịch xem xe, đặt cọc và bàn giao tại showroom.</p>
    </section>

    <section class="services" aria-label="Dịch vụ tại showroom">
        <article>
            <span class="number">01</span>
            <h2>Tìm hiểu xe</h2>
            <p>Thông tin hãng xe, dòng xe và những lựa chọn phù hợp với nhu cầu.</p>
        </article>
        <article>
            <span class="number">02</span>
            <h2>Tư vấn và xem xe</h2>
            <p>Trao đổi với nhân viên và sắp xếp lịch đến xem xe tại showroom.</p>
        </article>
        <article>
            <span class="number">03</span>
            <h2>Theo dõi giao dịch</h2>
            <p>Theo dõi quá trình đặt cọc, thanh toán và nhận bàn giao xe.</p>
        </article>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
