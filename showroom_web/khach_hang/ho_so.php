<?php
require dirname(__DIR__).'/includes/bootstrap.php';require APP_ROOT.'/services/customers.php';
$me=require_role(['KHACH_HANG','NHAN_VIEN','ADMIN']);
handle_form(function()use($me){
    $name=valid_text(post('ho_ten'),100);$email=post('email');$phone=contact_phone(post('so_dien_thoai'));$address=valid_text(post('dia_chi'),255,false);
    must(filter_var($email,FILTER_VALIDATE_EMAIL)&&strlen($email)<=100,'Email chưa hợp lệ.');
    transaction(function()use($me,$name,$email,$phone,$address){execute('UPDATE tai_khoan SET ho_ten=?,email=?,so_dien_thoai=?,dia_chi=? WHERE id=?',[$name,$email,$phone,$address,$me['id']]);audit('SUA_HO_SO','tai_khoan',(string)$me['id']);});
},'khach_hang/ho_so.php');
page_start('Hồ sơ của bạn');echo '<div class="cols"><div class="panel">';token_form();
foreach(['ho_ten'=>'Họ tên','email'=>'Email liên hệ','so_dien_thoai'=>'Số điện thoại liên hệ','dia_chi'=>'Địa chỉ']as$key=>$label)field($key,$label,$me[$key],$key==='email'?'email':'text',$key!=='dia_chi'?'required':'');
echo '<button>Lưu thông tin</button></form></div><div class="panel"><h2>Tài khoản</h2><p>'.badge($me['quyen']).'</p><p>Email và số điện thoại được dùng làm thông tin liên hệ.</p><a class="button ghost" href="'.e(url('khach_hang/doi_mat_khau.php')).'">Đổi mật khẩu</a></div></div>';page_end();
