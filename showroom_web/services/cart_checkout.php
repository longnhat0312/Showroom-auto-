<?php
require_once __DIR__.'/dat_coc.php';
require_once __DIR__.'/vnpay_gateway.php';

function cart_checkout_preview(array $user,string $config,string $vin,string $coupon): array {
    must($user['quyen']==='KHACH_HANG','Chỉ khách hàng có thể đặt cọc.',403);
    must(db_one('SELECT 1 FROM gio_hang WHERE tai_khoan_id=? AND ma_cau_hinh=? AND so_luong>0',[$user['id'],$config]),'Phiên bản này không còn trong giỏ. Hãy tải lại giỏ hàng.');
    $car=db_one("SELECT x.*,s.ten_showroom FROM xe x JOIN showroom s ON s.ma_showroom=x.ma_showroom WHERE x.so_khung_vin=? AND x.ma_cau_hinh=? AND s.hoat_dong=1",[$vin,$config]);
    must($car&&$car['trang_thai']==='SAN_SANG_BAN','Xe này không còn sẵn sàng. Hãy chọn xe khác hoặc tải lại giỏ hàng.');
    $price=tinh_gia($config,$coupon);
    return ['price'=>$price['gia'],'discount'=>$price['giam'],'total'=>$price['tong'],'deposit'=>min((int)setting('deposit_vnd'),$price['tong']),'showroom'=>$car['ten_showroom'],'hold_minutes'=>(int)setting('hold_minutes')];
}

function cart_checkout_create(array $user,array $data): array {
    must($user['quyen']==='KHACH_HANG','Chỉ khách hàng có thể đặt cọc.',403);
    $config=input($data,'ma_cau_hinh');$vin=input($data,'vin');$coupon=input($data,'ma_km');$key=input($data,'request_key');$method=input($data,'method');
    must(input($data,'agree')==='1','Vui lòng đồng ý điều kiện đặt cọc.');
    must(in_array($method,['SHOWROOM','VNPAY'],true),'Phương thức thanh toán không hợp lệ.');
    must((bool)preg_match('/^[a-f0-9]{32}$/D',$key),'Mã yêu cầu không hợp lệ.');
    // Check gateway readiness before creating a hold or reducing the cart.
    if($method==='VNPAY')vnp_config();
    return transaction(function()use($user,$config,$vin,$coupon,$key,$method,$data){
        // Match the VIN-first lock order used by all other reservation flows.
        $car=db_one('SELECT * FROM xe WHERE so_khung_vin=? FOR UPDATE',[$vin]);
        must($car&&$car['ma_cau_hinh']===$config,'Xe không thuộc phiên bản đã chọn.');
        $existing=db_one('SELECT * FROM hop_dong WHERE tai_khoan_id=? AND ma_yeu_cau=?',[$user['id'],$key]);
        if($existing){
            must($existing['so_khung_vin']===$vin,'Yêu cầu này đã được dùng cho xe khác.');
            $id=$existing['so_hop_dong'];
        }else{
            expire_vin_locked($car);
            $preview=cart_checkout_preview($user,$config,$vin,$coupon);
            must(vnd(input($data,'quoted_total'))===$preview['total'],'Giá vừa thay đổi. Hãy cập nhật lại số tiền trước khi xác nhận.');
            $id=reserve_vehicle($user,$vin,$coupon,$key);
        }
        // This signs a URL locally; it does not submit a payment to VNPay.
        $paymentUrl=$method==='VNPAY'?start_vnpay($user,$id):null;
        $order=contract($id,$user);
        return ['contract'=>$id,'order_url'=>url('khach_hang/don_mua.php?id='.rawurlencode($id)),'payment_url'=>$paymentUrl,'deadline'=>$order['han_giu_vin'],'deposit'=>(int)$order['tien_dat_coc'],'status'=>strip_tags(badge($order['trang_thai_hd']))];
    });
}
