<?php
// Dùng chung việc tìm khách và kiểm tra nhân viên phụ trách.
function phone_key(string $phone): string {
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($digits, '0084')) $digits = '0'.substr($digits, 4);
    elseif (str_starts_with($digits, '84')) $digits = '0'.substr($digits, 2);
    return $digits;
}
function contact_phone(string $phone): string {
    must((bool)preg_match('/^[+0-9().\s-]+$/D', $phone), 'Số điện thoại không hợp lệ.');
    $phone = phone_key($phone);
    must((bool)preg_match('/^[0-9]{9,14}$/D', $phone), 'Số điện thoại cần từ 9 đến 14 chữ số.');
    return $phone;
}
function search_customers(array $actor, string $query = ''): array {
    must(in_array($actor['quyen'], ['NHAN_VIEN','ADMIN'], true) && can($actor,'XEM_KHACH'), 'Không có quyền xem khách hàng.', 403);
    $where = ["t.quyen='KHACH_HANG'"]; $params = [];
    if ($actor['quyen'] !== 'ADMIN') {
        $where[] = '(p.nhan_vien_id=? OR p.nhan_vien_id IS NULL)'; $params[] = $actor['id'];
    }
    $query = mb_substr(trim($query),0,100);
    if ($query !== '') {
        $where[] = '(t.ho_ten LIKE ? OR t.so_dien_thoai LIKE ?';
        $params[] = '%'.$query.'%'; $params[] = '%'.$query.'%';
        if (preg_match('/^[+0-9().\s-]+$/D',$query) && phone_key($query) !== '') {
            $clean = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(t.so_dien_thoai,' ',''),'.',''),'-',''),'(',''),')',''),'+','')";
            $normalized = "CASE WHEN LEFT($clean,4)='0084' THEN CONCAT('0',SUBSTRING($clean,5)) WHEN LEFT($clean,2)='84' THEN CONCAT('0',SUBSTRING($clean,3)) ELSE $clean END";
            $where[array_key_last($where)] .= ' OR '.$normalized.' LIKE ?';
            $params[] = '%'.phone_key($query).'%';
        }
        $where[array_key_last($where)] .= ')';
    }
    return db_all('SELECT t.id,t.ho_ten,t.email,t.so_dien_thoai,t.dia_chi,t.trang_thai,t.quyen,
        p.nhan_vien_id,p.nhu_cau,n.ho_ten AS ten_nhan_vien
        FROM tai_khoan t LEFT JOIN phan_cong_khach p ON p.khach_hang_id=t.id
        LEFT JOIN tai_khoan n ON n.id=p.nhan_vien_id WHERE '.implode(' AND ',$where).' ORDER BY t.ho_ten,t.id LIMIT 150',$params);
}
function staff_customer(array $actor, int $id, bool $lock=false, bool $allowUnassigned=true): array {
    $customer=db_one("SELECT id,ho_ten,email,so_dien_thoai,dia_chi,quyen,trang_thai FROM tai_khoan WHERE id=? AND quyen='KHACH_HANG'".($lock?' FOR UPDATE':''),[$id]);
    must($customer,'Không tìm thấy khách hàng.',404);
    $assignment=db_one('SELECT * FROM phan_cong_khach WHERE khach_hang_id=?'.($lock?' FOR UPDATE':''),[$id]);
    must($actor['quyen']==='ADMIN'||($actor['quyen']==='NHAN_VIEN'&&(($assignment && (int)$assignment['nhan_vien_id']===(int)$actor['id'])||(!$assignment&&$allowUnassigned))),'Khách hàng đã có nhân viên khác phụ trách.',403);
    return $customer+['nhan_vien_id'=>$assignment['nhan_vien_id']??null,'nhu_cau'=>$assignment['nhu_cau']??''];
}
function accept_customer(array $actor, int $id, ?int $employeeId=null): void {
    must(can($actor,'XEM_KHACH'),'Không có quyền tiếp nhận khách.',403);
    $employeeId=$actor['quyen']==='ADMIN'?($employeeId??0):(int)$actor['id'];
    transaction(function()use($actor,$id,$employeeId){
        $customer=staff_customer($actor,$id,true);
        must($customer['trang_thai']==='HOAT_DONG','Tài khoản khách đang bị khóa.');
        must(db_one("SELECT id FROM tai_khoan WHERE id=? AND quyen='NHAN_VIEN' AND trang_thai='HOAT_DONG'",[$employeeId]),'Chọn nhân viên phụ trách hợp lệ.');
        execute('INSERT INTO phan_cong_khach(khach_hang_id,nhan_vien_id) VALUES(?,?) ON DUPLICATE KEY UPDATE nhan_vien_id=VALUES(nhan_vien_id),cap_nhat=NOW()',[$id,$employeeId]);
        audit('TIEP_NHAN_KHACH','tai_khoan',(string)$id,null,['nhan_vien_id'=>$employeeId]);
    });
}
