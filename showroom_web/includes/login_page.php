<?php
require_once APP_ROOT.'/services/auth.php';
handle_form(function()use($adminLogin){
 $id=valid_text(post('identity'),100);$pw=is_string($_POST['password']??null)?$_POST['password']:'';
 throttle('login-ip',$_SERVER['REMOTE_ADDR']??'local',30);throttle('login-user',mb_strtolower($id),8);
 $u=db_one('SELECT * FROM tai_khoan WHERE username=? OR email=? LIMIT 1',[$id,$id]);
 must($u&&password_verify($pw,$u['password'])&&$u['trang_thai']==='HOAT_DONG'&&in_array($u['quyen'],$adminLogin?['ADMIN']:['KHACH_HANG','NHAN_VIEN'],true),'Thông tin đăng nhập không hợp lệ hoặc tài khoản không được phép truy cập.');
 auth_complete($u);
},$adminLogin?'admin/dang_nhap.php':'dang_nhap.php');
page_start($adminLogin?'Đăng nhập quản trị':'Chào mừng bạn trở lại','Đăng nhập để theo dõi giao dịch và kết nối cùng showroom.');
echo '<div class="auth panel">';token_form();field('identity','Tên đăng nhập hoặc email',post('identity'),'text','required autocomplete="username"');field('password','Mật khẩu','','password','required autocomplete="current-password"');echo '<button>Đăng nhập</button></form>';
if(!$adminLogin)echo '<p>Chưa có tài khoản? <a href="'.e(url('dang_ky.php')).'">Đăng ký</a></p><a class="muted" href="'.e(url('admin/dang_nhap.php')).'">Dành cho quản trị viên</a>';
echo '</div>';page_end();
