<?php
require dirname(__DIR__).'/includes/bootstrap.php';
require APP_ROOT.'/services/staff_contracts.php';
$me=require_permission('XEM_KHACH');
handle_form(function()use($me){
    $id=create_staff_contract($me,(int)post('khach_hang_id'),post('vin'),post('ma_km'),post('request_key'),(int)post('nhan_vien_id'));
    flash('Đã tạo hợp đồng. Tiếp tục ghi nhận khoản tiền thực thu và hoàn thiện hồ sơ.');
    redirect('nhan_vien/hop_dong.php?id='.rawurlencode($id));
},'nhan_vien/tao_hop_dong.php');
expire_holds();
$query=get('q');$customers=search_customers($me,$query);$selected=post('khach_hang_id')?:get('khach');
if($selected!==''&&!in_array((int)$selected,array_column($customers,'id'),true))$customers[]=staff_customer($me,(int)$selected);
$choices=[];foreach($customers as$c)if($c['trang_thai']==='HOAT_DONG')$choices[$c['id']]=$c['ho_ten'].' · '.$c['so_dien_thoai'];
$scope=$me['quyen']==='ADMIN'?'1=1':'EXISTS(SELECT 1 FROM pham_vi_showroom p WHERE p.ma_showroom=x.ma_showroom AND p.tai_khoan_id='.(int)$me['id'].')';
$cars=db_all("SELECT x.so_khung_vin,x.mau_sac,d.ten_dong_xe,c.ten_cau_hinh,c.gia_niem_yet,s.ten_showroom
    FROM xe x JOIN cau_hinh_xe c ON c.ma_cau_hinh=x.ma_cau_hinh JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe
    JOIN showroom s ON s.ma_showroom=x.ma_showroom
    WHERE x.trang_thai='SAN_SANG_BAN' AND c.hien_thi=1 AND d.hien_thi=1 AND s.hoat_dong=1 AND $scope ORDER BY d.ten_dong_xe,x.so_khung_vin");
$carChoices=[];foreach($cars as$c)$carChoices[$c['so_khung_vin']]=$c['ten_dong_xe'].' '.$c['ten_cau_hinh'].' · '.$c['mau_sac'].' · '.money($c['gia_niem_yet']).' · '.$c['so_khung_vin'];
page_start('Tạo hợp đồng cho khách','Chọn khách hàng và một xe sẵn sàng bán tại showroom phụ trách.');
echo '<form class="filters" method="get">';field('q','Tìm khách theo tên hoặc số điện thoại',$query,'search');if(get('vin'))field('vin','',get('vin'),'hidden');echo '<button>Tìm khách</button></form>';
if(!$choices)notice('Chưa có khách phù hợp. Khách cần có tài khoản đăng ký; chỉ chọn được khách của bạn hoặc khách chưa được tiếp nhận.');
if(!$carChoices)notice('Chưa có xe sẵn sàng bán tại showroom phụ trách.');
echo '<div class="panel">';token_form();field('request_key','',post('request_key')?:new_request_key(),'hidden');
select_field('khach_hang_id','Khách hàng',$choices,$selected,true);
select_field('vin','Xe / giá niêm yết / VIN',$carChoices,post('vin')?:get('vin'),true);
field('ma_km','Mã khuyến mãi (nếu có)',post('ma_km'));
if($me['quyen']==='ADMIN')select_field('nhan_vien_id','Nhân viên phụ trách',choices("SELECT id,ho_ten FROM tai_khoan WHERE quyen='NHAN_VIEN' AND trang_thai='HOAT_DONG'",'id','ho_ten'),post('nhan_vien_id'),true);
echo '<p class="muted">Hợp đồng mới ở trạng thái chờ cọc và giữ xe '.(int)setting('hold_minutes').' phút. Chỉ ghi nhận tiền sau khi đối chiếu khoản thực nhận. Khách chưa có người phụ trách sẽ được tiếp nhận cùng lúc tạo hợp đồng.</p>';
echo '<div class="actions"><button'.(!$choices||!$carChoices?' disabled':'').'>Tạo hợp đồng</button><a class="button ghost" href="'.e(url('nhan_vien/hop_dong.php')).'">Danh sách hợp đồng</a></div></form></div>';
page_end();
