<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/includes/bootstrap.php';
$data=json_decode(file_get_contents(APP_ROOT.'/database/xe_mau.json'),true,512,JSON_THROW_ON_ERROR);
$counts=transaction(function()use($data){
    $counts=['hang_moi'=>0,'dong_moi'=>0,'cau_hinh_moi'=>0,'xe_moi'=>0,'anh_moi'=>0];
    $admin=db_one("SELECT id FROM tai_khoan WHERE quyen='ADMIN' AND trang_thai='HOAT_DONG' ORDER BY id LIMIT 1");
    must($admin,'Cần có tài khoản quản trị trước khi nạp xe mẫu.');
    $brand=db_one("SELECT ma_hang FROM hang_xe WHERE LOWER(TRIM(ten_hang))='toyota' LIMIT 1");
    if(!$brand){execute("INSERT INTO hang_xe(ma_hang,ten_hang,quoc_gia)VALUES('TOYOTA','Toyota','Nhật Bản')");$brand=['ma_hang'=>'TOYOTA'];$counts['hang_moi']++;}
    must(db_one("SELECT ma_showroom FROM showroom WHERE ma_showroom='MAIN' AND hoat_dong=1"),'Showroom MAIN cần hoạt động để nạp xe mẫu.');
    foreach($data['models']as$m){
        $model=db_one('SELECT ma_dong_xe FROM dong_xe WHERE ma_hang=? AND LOWER(TRIM(ten_dong_xe))=LOWER(?) LIMIT 1',[$brand['ma_hang'],$m['ten_dong_xe']]);
        $modelId=$model['ma_dong_xe']??$m['ma_dong_xe'];
        if(!$model){execute('INSERT INTO dong_xe(ma_dong_xe,ten_dong_xe,ma_hang,phan_khuc,so_cho_ngoi,gia_niem_yet,hien_thi)VALUES(?,?,?,?,?,?,1)',[$modelId,$m['ten_dong_xe'],$brand['ma_hang'],$m['phan_khuc'],$m['so_cho_ngoi'],$m['gia_niem_yet']]);$counts['dong_moi']++;}
        $config=db_one('SELECT * FROM cau_hinh_xe WHERE ma_cau_hinh=?',[$m['ma_cau_hinh']]);
        must(!$config||$config['ma_dong_xe']===$modelId,'Mã cấu hình mẫu đã được dùng cho dòng xe khác.');
        if(!$config){execute('INSERT INTO cau_hinh_xe(ma_cau_hinh,ma_dong_xe,ten_cau_hinh,nhien_lieu,hop_so,dong_co,gia_niem_yet,hien_thi)VALUES(?,?,?,?,?,?,?,1)',[$m['ma_cau_hinh'],$modelId,$m['ten_cau_hinh'],$m['nhien_lieu'],$m['hop_so'],$m['dong_co'],$m['gia_niem_yet']]);$counts['cau_hinh_moi']++;}
        must(is_file(APP_ROOT.'/'.$m['anh']),'Thiếu ảnh mẫu: '.$m['anh']);
        if(!db_one('SELECT id FROM tep_xe WHERE ma_cau_hinh=? AND duong_dan=?',[$m['ma_cau_hinh'],$m['anh']])){execute("INSERT INTO tep_xe(ma_cau_hinh,loai,duong_dan,thu_tu)VALUES(?,'ANH',?,0)",[$m['ma_cau_hinh'],$m['anh']]);$counts['anh_moi']++;}
        foreach($m['vins']as$vin){
            must((bool)preg_match('/^ZZZ2026[0-9]{10}$/D',$vin),'VIN mẫu không đúng định dạng.');
            if(db_one('SELECT so_khung_vin FROM xe WHERE so_khung_vin=?',[$vin]))continue;
            execute("INSERT INTO xe(so_khung_vin,ma_dong_xe,mau_sac,nam_san_xuat,hinh_anh,ma_cau_hinh,ma_showroom,ngay_nhap_dau,odo,tinh_trang_phap_ly,bao_cao_kiem_dinh)VALUES(?,?,?,2026,?,?,'MAIN',NOW(),0,?,?)",[$vin,$modelId,$m['mau_sac'],$m['anh'],$m['ma_cau_hinh'],'Xe mẫu dùng thử','Dữ liệu mẫu cho đồ án; VIN mô phỏng, không đại diện xe tồn kho thực tế. Thông số và giá tham khảo: '.$m['nguon']]);
            execute("INSERT INTO bien_dong_kho(so_khung_vin,loai,showroom_den,nguoi_lap_id,ghi_chu)VALUES(?,'NHAP','MAIN',?,'Nạp dữ liệu xe mẫu cho đồ án bằng CLI')",[$vin,$admin['id']]);$counts['xe_moi']++;
        }
    }
    audit('NAP_XE_MAU','xe','mau_2026',null,$counts,(int)$admin['id']);return $counts;
});
echo json_encode($counts,JSON_UNESCAPED_UNICODE),PHP_EOL;
