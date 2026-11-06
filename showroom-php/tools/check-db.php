<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('SHOWROOM_APP', true);

try {
    require __DIR__ . '/../config/db.php';

    $result = mysqli_query(
        $conn,
        'SELECT DATABASE() AS database_name, CURRENT_USER() AS account_name, VERSION() AS server_version'
    );
    $row = mysqli_fetch_assoc($result);

    echo "Database connection successful.\n";
    echo 'Database: ' . $row['database_name'] . "\n";
    echo 'Account: ' . $row['account_name'] . "\n";
    echo 'Server: ' . $row['server_version'] . "\n";

    mysqli_close($conn);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, "Database connection failed. Check config/database.local.php.\n");
    fwrite(STDERR, 'Error code: ' . $exception->getCode() . "\n");
    exit(1);
}
