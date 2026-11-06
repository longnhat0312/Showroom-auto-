<?php
require dirname(__DIR__).'/includes/bootstrap.php';require APP_ROOT.'/services/customers.php';
$me=require_permission('XEM_KHACH');$query=mb_substr(get('q'),0,100);
handle_form(function()use($me){
    $id=(int)post('id');$employee=$me['quyen']==='ADMIN'?(int)post('staff'):(int)$me['id'];
    if(post('action')==='claim'){accept_customer($me,$id,$employee);return;}
    must(can($me,'SUA_KHACH'),'Không có quyền sửa hồ sơ khách.',403);
    $name=valid_text(post('name'),100);$phone=contact_phone(post('phone'));$address=valid_text(post('address'),255,false);$needs=valid_text(post('needs'),4000,false);
    transaction(function()use($me,$id,$employee,$name,$phone,$address,$needs){
        staff_customer($me,$id,true,false);accept_customer($me,$id,$employee);
        execute('UPDATE tai_khoan SET ho_ten=?,so_dien_thoai=?,dia_chi=? WHERE id=?',[$name,$phone,$address,$id]);
        execute('UPDATE phan_cong_khach SET nhu_cau=?,cap_nhat=NOW() WHERE khach_hang_id=?',[$needs,$id]);audit('SUA_KHACH','tai_khoan',(string)$id);
    });
},'nhan_vien/khach_hang.php'.($query!==''?'?q='.rawurlencode($query):''));
$rows=search_customers($me,$query);
page_start('Hồ sơ khách hàng','Tìm khách đã tiếp nhận hoặc khách mới chưa có nhân viên phụ trách.');
echo '<form class="filters" method="get">';field('q','Tên khách / số điện thoại',$query,'search','placeholder="Ví dụ: 0901234567 hoặc +84 901 234 567"');echo '<button>Tìm</button><a class="button ghost" href="'.e(url('nhan_vien/khach_hang.php')).'">Xóa bộ lọc</a></form>';
echo '<p class="muted">'.count($rows).' khách phù hợp (tối đa 150 kết quả).</p>';
if(!$rows)echo '<div class="panel empty">Không có khách phù hợp. Kiểm tra số điện thoại hoặc tên; khách đã do nhân viên khác phụ trách không nằm trong danh sách này.</div>';
foreach($rows as$customer){
    $id=(int)$customer['id'];$unassigned=$customer['nhan_vien_id']===null;
    echo '<details'.($query!==''?' open':'').'><summary>'.e($customer['ho_ten'].' · '.$customer['so_dien_thoai']).($unassigned?' · Chưa tiếp nhận':'').'</summary>';
    echo '<p>'.badge($customer['trang_thai']).' · '.e($unassigned?'Chưa có nhân viên phụ trách':('Phụ trách: '.$customer['ten_nhan_vien'])).'</p>';
    if($customer['trang_thai']==='HOAT_DONG')echo '<div class="actions"><a class="button" href="'.e(url('nhan_vien/tao_hop_dong.php?khach='.$id)).'">Tạo hợp đồng cho khách</a></div>';
    if($unassigned){
        echo '<p>'.e($customer['email']).'<br>'.e($customer['dia_chi']).'</p>';
        if($customer['trang_thai']==='HOAT_DONG'){token_form();field('id','',$id,'hidden');if($me['quyen']==='ADMIN')select_field('staff','Nhân viên tiếp nhận',choices("SELECT id,ho_ten FROM tai_khoan WHERE quyen='NHAN_VIEN' AND trang_thai='HOAT_DONG'",'id','ho_ten'),'',true);echo '<button name="action" value="claim">Tiếp nhận khách</button></form>';}
    }else{
        token_form();field('id','',$id,'hidden');field('name','Họ tên',$customer['ho_ten']);field('phone','Số điện thoại',$customer['so_dien_thoai']);field('address','Địa chỉ',$customer['dia_chi']);
        if($me['quyen']==='ADMIN')select_field('staff','Nhân viên phụ trách',choices("SELECT id,ho_ten FROM tai_khoan WHERE quyen='NHAN_VIEN' AND trang_thai='HOAT_DONG'",'id','ho_ten'),$customer['nhan_vien_id'],true);
        textarea('needs','Nhu cầu / ghi chú',$customer['nhu_cau']??'');echo '<button name="action" value="save">Lưu hồ sơ</button></form>';
        $history=db_one('SELECT (SELECT COUNT(*) FROM hop_dong WHERE tai_khoan_id=?) hd,(SELECT COUNT(*) FROM lich_hen WHERE khach_hang_id=?) lh,(SELECT COUNT(*) FROM hoi_thoai_chat WHERE khach_hang_id=?) chat',[$id,$id,$id]);
        echo '<p>Lịch sử: '.e($history['hd'].' hợp đồng · '.$history['lh'].' lịch hẹn · '.$history['chat'].' hội thoại').'</p>';
    }
    echo '</details>';
}
page_end();
