<?php
require dirname(__DIR__).'/includes/bootstrap.php';require APP_ROOT.'/services/auth.php';
if (!setting('admin_2fa')) {
    unset($_SESSION['pending_admin_id'], $_SESSION['pending_at'], $_SESSION['enroll_secret'], $_SESSION['recovery_once']);
    $current = current_user();
    redirect($current && $current['quyen'] === 'ADMIN' ? 'admin/index.php' : 'admin/dang_nhap.php');
}
$id=$_SESSION['pending_admin_id']??null;must($id&&($_SESSION['pending_at']??0)>time()-600,'Phiên xác thực hết hạn. Hãy đăng nhập lại.',401);
$u=db_one("SELECT * FROM tai_khoan WHERE id=? AND quyen='ADMIN' AND trang_thai='HOAT_DONG'",[$id]);must((bool)$u,'Tài khoản không còn quyền quản trị.',403);
$enroll=empty($u['mfa_secret']);$secret=$enroll?($_SESSION['enroll_secret']??=base32_secret()):decrypt_secret($u['mfa_secret']);
handle_form(function()use($u,$secret,$enroll){
 throttle('admin-otp',(string)$u['id'],6,300);$code=post('code');$counter=totp_counter($secret,$code,(int)$u['mfa_counter']);
 if($counter===null&&!$enroll){$ok=transaction(function()use($u,$code){$rows=db_all('SELECT * FROM mfa_khoi_phuc WHERE tai_khoan_id=? AND da_dung_luc IS NULL FOR UPDATE',[$u['id']]);foreach($rows as$r)if(password_verify($code,$r['ma_hash'])){execute('UPDATE mfa_khoi_phuc SET da_dung_luc=NOW() WHERE id=?',[$r['id']]);return true;}return false;});must($ok,'Mã xác thực không đúng hoặc đã dùng.');}
 else {must($counter!==null,'Mã xác thực không đúng hoặc đã dùng.');$changed=execute('UPDATE tai_khoan SET mfa_secret=?,mfa_counter=? WHERE id=? AND mfa_counter<?',[encrypt_secret($secret),$counter,$u['id'],$counter]);must($changed===1,'Mã xác thực đã được dùng.');}
 $recovery=[];if($enroll)transaction(function()use($u,&$recovery){for($i=0;$i<6;$i++){$c=bin2hex(random_bytes(6));$recovery[]=$c;execute('INSERT INTO mfa_khoi_phuc(tai_khoan_id,ma_hash)VALUES(?,?)',[$u['id'],password_hash($c,PASSWORD_DEFAULT)]);}});
 login_user($u,true);if($recovery)$_SESSION['recovery_once']=$recovery;redirect('admin/index.php');
},'admin/xac_minh_otp.php');
page_start($enroll?'Thiết lập xác thực hai bước':'Xác thực quản trị');echo '<div class="auth panel">';if($enroll){notice('Thêm tài khoản trong Google Authenticator/Microsoft Authenticator, chọn nhập khóa thiết lập. Tên tài khoản: '.$u['username']);echo '<p>Khóa thiết lập (chỉ hiển thị lần đầu):</p><code style="word-break:break-all">'.e($secret).'</code><p class="muted">Loại khóa: theo thời gian, 6 chữ số, chu kỳ 30 giây. Lưu khóa riêng tư.</p>';}
token_form();field('code','Mã 6 số từ ứng dụng hoặc mã khôi phục','','text','required autocomplete="one-time-code"');echo '<button>Xác nhận</button></form></div>';page_end();
