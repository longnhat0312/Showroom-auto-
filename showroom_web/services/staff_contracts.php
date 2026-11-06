<?php
require_once __DIR__.'/customers.php';
require_once __DIR__.'/dat_coc.php';

function create_staff_contract(array $actor,int $customerId,string $vin,string $coupon,string $key,?int $employeeId=null): string {
    must(in_array($actor['quyen'],['NHAN_VIEN','ADMIN'],true)&&can($actor,'XEM_KHACH'),'Không có quyền tạo hợp đồng.',403);
    $employeeId=$actor['quyen']==='ADMIN'?($employeeId??0):(int)$actor['id'];
    return transaction(function()use($actor,$customerId,$vin,$coupon,$key,$employeeId){
        // Cùng thứ tự khóa VIN với luồng khách tự đặt cọc để tránh giữ trùng xe.
        $car=db_one('SELECT * FROM xe WHERE so_khung_vin=? FOR UPDATE',[$vin]);
        must($car&&scope_showroom($actor,$car['ma_showroom']),'Xe nằm ngoài showroom phụ trách.',403);
        $employee=db_one("SELECT * FROM tai_khoan WHERE id=? AND quyen='NHAN_VIEN' AND trang_thai='HOAT_DONG'",[$employeeId]);
        must($employee&&scope_showroom($employee,$car['ma_showroom']),'Nhân viên chưa được phân công showroom của xe.');
        $customer=staff_customer($actor,$customerId,true);
        must($customer['trang_thai']==='HOAT_DONG','Tài khoản khách đang bị khóa.');
        $existing=db_one('SELECT * FROM hop_dong WHERE tai_khoan_id=? AND ma_yeu_cau=?',[$customerId,$key]);
        if($existing){
            must($existing['so_khung_vin']===$vin&&(int)$existing['nhan_vien_id']===$employeeId,'Mã yêu cầu đã dùng cho hợp đồng khác.');
            return $existing['so_hop_dong'];
        }
        accept_customer($actor,$customerId,$employeeId);
        $id=reserve_vehicle($customer,$vin,$coupon,$key);
        execute('UPDATE hop_dong SET nhan_vien_id=? WHERE so_hop_dong=?',[$employeeId,$id]);
        audit('NHAN_VIEN_TAO_HOP_DONG','hop_dong',$id,null,['khach_hang_id'=>$customerId,'nhan_vien_id'=>$employeeId,'vin'=>$vin]);
        return $id;
    });
}
