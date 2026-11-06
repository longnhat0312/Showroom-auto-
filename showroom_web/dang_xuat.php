<?php require __DIR__.'/includes/bootstrap.php'; require_post(); $_SESSION=[];session_regenerate_id(true);session_destroy();redirect('dang_nhap.php');
