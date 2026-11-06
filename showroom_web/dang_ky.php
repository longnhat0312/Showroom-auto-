<?php
require __DIR__.'/includes/bootstrap.php';require APP_ROOT.'/services/auth.php';
handle_form(function(){
 throttle('register',$_SERVER['REMOTE_ADDR']??'local',8,3600);
 $name=post('username');$email=post('email');$full=valid_text(post('ho_ten'),100);$phone=post('so_dien_thoai');$pw=$_POST['password']??'';
 must((bool)preg_match('/^[A-Za-z0-9_]{3,50}$/D',$name),'Tên đăng nhập gồm 3–50 chữ không dấu, số hoặc dấu _.');
 must(filter_var($email,FILTER_VALIDATE_EMAIL)&&strlen($email)<=100,'Email không hợp lệ.');must((bool)preg_match('/^\+?[0-9]{9,14}$/D',$phone),'Số điện thoại không hợp lệ.');
 must(is_string($pw)&&strlen($pw)>=10&&strlen($pw)<=72&&$pw===($_POST['confirm']??''),'Mật khẩu cần 10–72 byte và hai lần nhập giống nhau.');
 execute("INSERT INTO tai_khoan(username,password,email,ho_ten,so_dien_thoai,quyen,trang_thai) VALUES (?,?,?,?,?,'KHACH_HANG','HOAT_DONG')",[$name,password_hash($pw,PASSWORD_DEFAULT),$email,$full,$phone]);
 flash('Đã tạo tài khoản. Bạn có thể đăng nhập.');redirect('dang_nhap.php');
},'dang_ky.php');
page_start('Bắt đầu hành trình của bạn');echo '<div class="auth panel">';token_form();foreach(['username'=>'Tên đăng nhập','ho_ten'=>'Họ và tên','email'=>'Email','so_dien_thoai'=>'Số điện thoại']as$k=>$v)field($k,$v,post($k),$k==='email'?'email':'text','required maxlength="100"');field('password','Mật khẩu','','password','required minlength="10" autocomplete="new-password"');field('confirm','Nhập lại mật khẩu','','password','required autocomplete="new-password"');echo '<button>Tạo tài khoản</button></form></div>';page_end();
