<?php
require dirname(__DIR__).'/includes/bootstrap.php';
require APP_ROOT.'/services/cart_checkout.php';
$user=require_role(['KHACH_HANG']);require_post();
if(post('action')==='preview')json_response(cart_checkout_preview($user,post('ma_cau_hinh'),post('vin'),post('ma_km')));
must(post('action')==='create','Thao tác không hợp lệ.');
json_response(cart_checkout_create($user,$_POST));
