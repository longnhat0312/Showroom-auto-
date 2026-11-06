<?php
function tinh_gia(string $config,string $coupon='',bool $lock=false): array {
 $c=db_one('SELECT c.*,d.hien_thi AS dong_hien FROM cau_hinh_xe c JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe WHERE c.ma_cau_hinh=?'.($lock?' FOR UPDATE':''),[$config]);must($c&&$c['hien_thi']&&$c['dong_hien'],'Phiên bản này không còn bán.');$price=vnd($c['gia_niem_yet']);$discount=0;$km=null;
 if($coupon!==''){$km=db_one('SELECT * FROM khuyen_mai WHERE ma_km=?'.($lock?' FOR UPDATE':''),[$coupon]);must($km&&$km['hoat_dong']&&strtotime($km['bat_dau'])<=time()&&strtotime($km['ket_thuc'])>time(),'Mã ưu đãi chưa có hiệu lực hoặc đã hết hạn.');must(db_one('SELECT 1 FROM khuyen_mai_cau_hinh WHERE ma_km=? AND ma_cau_hinh=?',[$coupon,$config])!==null,'Mã không áp dụng cho cấu hình này.');must($price>=vnd($km['gia_toi_thieu'],true),'Giá mua chưa đạt điều kiện ưu đãi.');
  if($km['loai']==='TIEN')$discount=vnd($km['gia_tri']);else{[$whole,$frac]=array_pad(explode('.',$km['gia_tri']),2,'0');$bps=(int)$whole*100+(int)str_pad(substr($frac,0,2),2,'0');$discount=intdiv($price*$bps,10000);}
  if($km['giam_toi_da']!==null)$discount=min($discount,vnd($km['giam_toi_da'],true));$discount=min($discount,$price);}
 must($price-$discount>0,'Giá hợp đồng sau ưu đãi phải lớn hơn 0.');return ['gia'=>$price,'giam'=>$discount,'tong'=>$price-$discount,'ma_km'=>$km['ma_km']??null,'config'=>$c];
}
