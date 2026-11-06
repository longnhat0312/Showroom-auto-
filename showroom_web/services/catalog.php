<?php
function catalog_definitions(): array {
 $brands=choices('SELECT ma_hang,ten_hang FROM hang_xe','ma_hang','ten_hang');$models=choices('SELECT ma_dong_xe,ten_dong_xe FROM dong_xe','ma_dong_xe','ten_dong_xe');$yes=['1'=>'Hiển thị / Hoạt động','0'=>'Ẩn / Ngừng'];
 return [
 'hang_xe'=>['table'=>'hang_xe','pk'=>'ma_hang','title'=>'Hãng xe','fields'=>['ma_hang'=>['Mã hãng','text',[],true,50],'ten_hang'=>['Tên hãng','text',[],true,100],'quoc_gia'=>['Quốc gia','text',[],false,50]]],
 'dong_xe'=>['table'=>'dong_xe','pk'=>'ma_dong_xe','title'=>'Dòng xe','fields'=>['ma_dong_xe'=>['Mã dòng','text',[],true,50],'ten_dong_xe'=>['Tên dòng','text',[],true,100],'ma_hang'=>['Hãng xe','select',$brands],'phan_khuc'=>['Phân khúc','select',['Sedan'=>'Sedan','SUV'=>'SUV','MPV'=>'MPV','Crossover'=>'Crossover','Pickup'=>'Bán tải']],'so_cho_ngoi'=>['Số chỗ','int'],'gia_niem_yet'=>['Giá tham khảo của dòng','money'],'hien_thi'=>['Công khai','select',$yes]]],
 'cau_hinh'=>['table'=>'cau_hinh_xe','pk'=>'ma_cau_hinh','title'=>'Phiên bản / cấu hình','fields'=>['ma_cau_hinh'=>['Mã cấu hình','text',[],true,50],'ma_dong_xe'=>['Dòng xe','select',$models],'ten_cau_hinh'=>['Tên phiên bản','text',[],true,100],'nhien_lieu'=>['Nhiên liệu','select',['XANG'=>'Xăng','DAU'=>'Dầu','DIEN'=>'Điện','HYBRID'=>'Hybrid']],'hop_so'=>['Hộp số','select',['AT'=>'Tự động','MT'=>'Số sàn','CVT'=>'CVT']],'dong_co'=>['Động cơ','text',[],false,100],'gia_niem_yet'=>['Giá bán hiện hành (đ)','money'],'hien_thi'=>['Công khai','select',$yes]]],
 'showroom'=>['table'=>'showroom','pk'=>'ma_showroom','title'=>'Showroom','fields'=>['ma_showroom'=>['Mã showroom','text',[],true,50],'ten_showroom'=>['Tên showroom','text',[],true,100],'dia_chi'=>['Địa chỉ','text'],'hoat_dong'=>['Trạng thái','select',$yes]]],
 'khuyen_mai'=>['table'=>'khuyen_mai','pk'=>'ma_km','title'=>'Khuyến mãi','fields'=>['ma_km'=>['Mã ưu đãi','text',[],true,50],'ten'=>['Tên chương trình','text',[],true,150],'loai'=>['Loại giảm','select',['TIEN'=>'Số tiền','PHAN_TRAM'=>'Phần trăm']],'gia_tri'=>['Mức giảm','number'],'giam_toi_da'=>['Giảm tối đa (đ)','money',[],false],'gia_toi_thieu'=>['Giá mua tối thiểu','money'],'bat_dau'=>['Bắt đầu','datetime-local'],'ket_thuc'=>['Kết thúc','datetime-local'],'hoat_dong'=>['Trạng thái','select',$yes]]],
 'noi_dung'=>['table'=>'noi_dung_web','pk'=>'id','where'=>"loai <> 'TIN_TUC'",'title'=>'Nội dung website','fields'=>['loai'=>['Loại','select',['BANNER'=>'Banner','GIOI_THIEU'=>'Giới thiệu']],'slug'=>['Slug đường dẫn','text',[],true,150],'tieu_de'=>['Tiêu đề','text',[],true,200],'noi_dung'=>['Nội dung (văn bản)','textarea',[],false,60000],'hinh_anh'=>['Đường dẫn ảnh đã tải lên','text',[],false],'lien_ket'=>['Liên kết','text',[],false],'trang_thai'=>['Trạng thái','select',['NHAP'=>'Bản nháp','CONG_KHAI'=>'Công khai','AN'=>'Ẩn']]]]
 ];
}
function image_url(?string $path): string { if(!$path)return '';if(!preg_match('~^assets/(?:image|images)/[A-Za-z0-9_./-]+$~D',$path)||str_contains($path,'..'))return '';return url($path); }
function upload_file(array $file,bool $private=false,bool $pdf=false,bool $video=false): array {
 must(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK,'Chọn một tệp hợp lệ.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if($pdf)$types['application/pdf']='pdf';if($video)$types['video/mp4']='mp4';must(isset($types[$mime]),'Định dạng tệp không được hỗ trợ.');$limit=$mime==='video/mp4'?30*1024*1024:(int)setting('max_upload');must(($file['size']??0)>0&&$file['size']<=$limit,'Tệp quá lớn.');
 $name=bin2hex(random_bytes(16)).'.'.$types[$mime];$dir=$private?PRIVATE_DIR.'/attachments':APP_ROOT.'/assets/image/xe';if(!is_dir($dir))mkdir($dir,0700,true);must(move_uploaded_file($file['tmp_name'],$dir.'/'.$name),'Không lưu được tệp.');
 return ['path'=>$private?'attachments/'.$name:'assets/image/xe/'.$name,'mime'=>$mime,'name'=>mb_substr(basename($file['name']),0,200)];
}
function stock_save(array $u,array $p): string {
 $vin=strtoupper(input($p,'so_khung_vin'));must((bool)preg_match('/^[A-HJ-NPR-Z0-9]{17}$/D',$vin),'VIN cần 17 ký tự chữ/số, không gồm I, O, Q.');
 $config=db_one('SELECT * FROM cau_hinh_xe WHERE ma_cau_hinh=?',[input($p,'ma_cau_hinh')]);must((bool)$config,'Cấu hình không tồn tại.');$sr=input($p,'ma_showroom');must(db_one('SELECT 1 FROM showroom WHERE ma_showroom=? AND hoat_dong=1',[$sr])&&scope_showroom($u,$sr),'Showroom không hợp lệ hoặc ngoài phạm vi.',403);
 $year=input($p,'nam_san_xuat');must(ctype_digit($year)&&(int)$year>=1950&&(int)$year<=((int)date('Y')+1),'Năm sản xuất không hợp lệ.');$cost=input($p,'gia_von')===''?null:vnd(input($p,'gia_von'),true);$date=valid_date(input($p,'ngay_nhap_dau'));$mau=valid_text(input($p,'mau_sac'),50);$odo=input($p,'odo');must($odo===''||(ctype_digit($odo)&&(int)$odo<10000000),'ODO không hợp lệ.');
 return transaction(function()use($u,$p,$vin,$config,$sr,$year,$cost,$date,$mau,$odo){
 $old=db_one('SELECT * FROM xe WHERE so_khung_vin=? FOR UPDATE',[$vin]);$editing=input($p,'_edit')==='1';must($editing?(bool)$old:!$old,$editing?'Không tìm thấy xe.':'VIN đã tồn tại.');
 if($old){must(scope_showroom($u,$old['ma_showroom']),'Xe ngoài phạm vi.',403);must($old['trang_thai']==='SAN_SANG_BAN','Không sửa thông tin xe đang giữ cọc hoặc đã giao.');$used=db_one("SELECT 1 FROM hop_dong WHERE so_khung_vin=? LIMIT 1",[$vin]);if($used)must($old['ma_cau_hinh']===$config['ma_cau_hinh'],'Xe đã có lịch sử hợp đồng: không đổi cấu hình.');}
 $values=[$config['ma_dong_xe'],$config['ma_cau_hinh'],input($p,'so_may')?:null,$mau,$year,$sr,$cost,$old['ngay_nhap_dau']??$date,$odo===''?null:$odo,input($p,'tinh_trang_phap_ly')?:null,input($p,'bao_cao_kiem_dinh')?:null];
 if($old)execute('UPDATE xe SET ma_dong_xe=?,ma_cau_hinh=?,so_may=?,mau_sac=?,nam_san_xuat=?,ma_showroom=?,gia_von=?,ngay_nhap_dau=?,odo=?,tinh_trang_phap_ly=?,bao_cao_kiem_dinh=? WHERE so_khung_vin=?',[...$values,$vin]);
 else execute('INSERT INTO xe(ma_dong_xe,ma_cau_hinh,so_may,mau_sac,nam_san_xuat,ma_showroom,gia_von,ngay_nhap_dau,odo,tinh_trang_phap_ly,bao_cao_kiem_dinh,so_khung_vin) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',[...$values,$vin]);
 if(!$old||$old['ma_showroom']!==$sr)execute('INSERT INTO bien_dong_kho(so_khung_vin,loai,showroom_di,showroom_den,nguoi_lap_id) VALUES (?,?,?,?,?)',[$vin,$old?'CHUYEN':'NHAP',$old['ma_showroom']??null,$sr,$u['id']]);
 audit($old?'SUA_XE':'NHAP_XE','xe',$vin,$old,['showroom'=>$sr,'gia_von'=>$cost]);return $vin;
 });
}
