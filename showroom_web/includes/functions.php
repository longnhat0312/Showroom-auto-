<?php
class AppError extends RuntimeException { public function __construct(string $message, public int $status=400) { parent::__construct($message); } }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $p=''): string { return BASE_URL.'/'.ltrim($p,'/'); }
function redirect(string $p=''): never { header('Location: '.url($p)); exit; }
function input(array $s,string $k): string { return is_string($s[$k]??null)?trim($s[$k]):''; }
function post(string $k): string { return input($_POST,$k); }
function get(string $k): string { return input($_GET,$k); }
function db_run(string $sql,array $params=[]): mysqli_stmt { global $conn; $s=$conn->prepare($sql); if($params) $s->bind_param(str_repeat('s',count($params)),...$params); $s->execute(); return $s; }
function db_all(string $sql,array $p=[]): array { $s=db_run($sql,$p); $r=$s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close(); return $r; }
function db_one(string $sql,array $p=[]): ?array { $s=db_run($sql,$p); $r=$s->get_result()->fetch_assoc(); $s->close(); return $r; }
function execute(string $sql,array $p=[]): int { $s=db_run($sql,$p); $n=$s->affected_rows; $s->close(); return $n; }
function insert_id(): int { global $conn; return (int)$conn->insert_id; }
function transaction(callable $fn): mixed { global $conn; static $depth=0; if($depth>0)return $fn();for($try=0;;$try++) { $conn->begin_transaction();$depth++; try { $r=$fn(); $conn->commit();$depth--; return $r; } catch(Throwable $e) { $conn->rollback();$depth--; if($e instanceof mysqli_sql_exception && in_array($e->getCode(),[1205,1213],true) && $try<2) { usleep(random_int(20000,70000)); continue; } throw $e; } } }
function must(mixed $ok,string $message,int $status=400): void { if(!$ok) throw new AppError($message,$status); }
function uid(string $prefix=''): string { return $prefix.strtoupper(bin2hex(random_bytes(12))); }
function setting(string $key): mixed { static $c; $c??=require APP_ROOT.'/config/nghiep_vu.php'; return $c[$key]??null; }
function private_config(string $name): array { $p=PRIVATE_DIR.'/'.$name.'.php'; if(!is_file($p)||filesize($p)===0)return []; $c=require $p; return is_array($c)?$c:[]; }
function private_save(string $name,array $data): void { if(!is_dir(PRIVATE_DIR)) mkdir(PRIVATE_DIR,0700,true); $p=PRIVATE_DIR.'/'.$name.'.php'; $tmp=$p.'.'.bin2hex(random_bytes(4)).'.tmp'; must(file_put_contents($tmp,"<?php\nreturn ".var_export($data,true).";\n",LOCK_EX)!==false,'Không ghi được cấu hình riêng.'); must(rename($tmp,$p),'Không lưu được cấu hình.'); }
function vnd(mixed $s,bool $zero=false): int { $s=(string)$s; must((bool)preg_match('/^[0-9]{1,13}(?:\.00)?$/D',$s),'Số tiền phải là số đồng nguyên, không có dấu phân cách.'); $n=(int)$s; must($zero?$n>=0:$n>0,'Số tiền phải lớn hơn 0.'); return $n; }
function money(mixed $s): string { return number_format((float)$s,0,',','.').' đ'; }
function csrf_token(): string { return $_SESSION['csrf']??=bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="'.e(csrf_token()).'">'; }
function require_post(): void { must(($_SERVER['REQUEST_METHOD']??'')==='POST','Thao tác cần gửi bằng biểu mẫu.',405); must(post('_csrf')!==''&&hash_equals(csrf_token(),post('_csrf')),'Biểu mẫu hết hiệu lực. Hãy tải lại trang.',419); }
function flash(string $m): void { $_SESSION['flash']=$m; }
function take_flash(): string { $m=$_SESSION['flash']??''; unset($_SESSION['flash']); return $m; }
function current_user(): ?array { if(empty($_SESSION['user_id'])) return null; $u=db_one('SELECT * FROM tai_khoan WHERE id=?',[$_SESSION['user_id']]); if(!$u||$u['trang_thai']!=='HOAT_DONG'||(int)$u['phien_ban_xac_thuc']!==($_SESSION['auth_version']??0)) { unset($_SESSION['user_id'],$_SESSION['mfa_ok']); return null; } return $u; }
function login_destination(string $role): string { return match($role){'ADMIN'=>'admin/index.php','NHAN_VIEN'=>'nhan_vien/index.php',default=>'index.php'}; }
function login_user(array $u,bool $mfa=false): void { session_regenerate_id(true); $_SESSION=['user_id'=>(int)$u['id'],'auth_version'=>(int)$u['phien_ban_xac_thuc'],'mfa_ok'=>$mfa,'csrf'=>bin2hex(random_bytes(32))]; }
function require_role(array $roles): array { $u=current_user(); if(!$u) { if(str_contains($_SERVER['SCRIPT_NAME']??'','/api/'))throw new AppError('Vui lòng đăng nhập.',401); redirect(str_contains($_SERVER['SCRIPT_NAME']??'','/admin/')?'admin/dang_nhap.php':'dang_nhap.php'); } must(in_array($u['quyen'],$roles,true),'Bạn không có quyền truy cập.',403); if($u['quyen']==='ADMIN'&&setting('admin_2fa')&&empty($_SESSION['mfa_ok'])) { unset($_SESSION['user_id']); redirect('admin/dang_nhap.php'); } return $u; }
function can(array $u,string $p): bool { return TaiKhoan::fromRow($u)->allows($p); }
function require_permission(string $p): array { $u=require_role(['ADMIN','NHAN_VIEN']); must(can($u,$p),'Bạn chưa được cấp quyền này.',403); return $u; }
function owns_customer(array $u,int $id): bool { return $u['quyen']==='ADMIN'||($u['quyen']==='KHACH_HANG'?(int)$u['id']===$id:db_one('SELECT 1 FROM phan_cong_khach WHERE khach_hang_id=? AND nhan_vien_id=?',[$id,$u['id']])!==null); }
function scope_showroom(array $u,string $sr): bool { if($u['quyen']==='ADMIN')return true; return db_one('SELECT 1 FROM pham_vi_showroom WHERE tai_khoan_id=? AND ma_showroom=?',[$u['id'],$sr])!==null; }
function contract_access(array $u,array $h): bool { if($u['quyen']==='KHACH_HANG')return (int)$h['tai_khoan_id']===(int)$u['id']; if($u['quyen']==='ADMIN')return true; return (int)$h['nhan_vien_id']===(int)$u['id']&&scope_showroom($u,$h['ma_showroom_chot']); }
function contract(string $id,array $u): array { $h=db_one('SELECT * FROM hop_dong WHERE so_hop_dong=?',[$id]); must($h&&contract_access($u,$h),'Không tìm thấy hợp đồng trong phạm vi truy cập.',404); return $h; }
function json_response(array $d,int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($d,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE); exit; }
function token_form(string $action='',string $class=''): void { echo '<form method="post" enctype="multipart/form-data" class="'.e($class).'" action="'.e($action).'">'.csrf_field(); }
function field(string $name,string $label,mixed $value='',string $type='text',string $extra=''): void { if($type==='datetime-local')$value=str_replace(' ','T',(string)$value);$control='<input name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" '.$extra.'>';echo $type==='hidden'?$control:'<label>'.e($label).$control.'</label>'; }
function select_field(string $name,string $label,array $options,mixed $selected='',bool $required=false): void { echo '<label>'.e($label).'<select name="'.e($name).'" '.($required?'required':'').'><option value="">— Chọn —</option>'; foreach($options as $key=>$text)echo '<option value="'.e($key).'" '.((string)$selected===(string)$key?'selected':'').'>'.e($text).'</option>'; echo '</select></label>'; }
function choices(string $sql,string $key,string $label,array $params=[]): array { $out=[]; foreach(db_all($sql,$params) as $r)$out[$r[$key]]=$r[$label]; return $out; }
function textarea(string $name,string $label,string $value=''): void { echo '<label>'.e($label).'<textarea name="'.e($name).'" rows="4">'.e($value).'</textarea></label>'; }
function badge(string $s): string { $labels=['SAN_SANG_BAN'=>'Sẵn sàng bán','DANG_GIU'=>'Đang giữ','DA_DAT_COC'=>'Đã đặt cọc','DA_BAN_GIAO'=>'Đã bàn giao','CHO_COC'=>'Chờ cọc','CHO_XU_LY'=>'Chờ xử lý','DA_THANH_TOAN'=>'Đã thanh toán','HOAN_TAT'=>'Hoàn tất','DA_HUY'=>'Đã hủy','HET_HAN'=>'Hết hạn','SUCCESS'=>'Thành công','FAILED'=>'Thất bại','CANCELLED'=>'Đã hủy','CHO_XAC_NHAN'=>'Chờ xác nhận','DA_XAC_NHAN'=>'Đã xác nhận','CHO_DUYET'=>'Chờ duyệt','DA_DUYET'=>'Đã duyệt','TU_CHOI'=>'Từ chối','CHO_TIEP_NHAN'=>'Chờ tiếp nhận','DANG_TU_VAN'=>'Đang tư vấn','DA_KET_THUC'=>'Đã kết thúc','HOAT_DONG'=>'Hoạt động','BI_KHOA'=>'Bị khóa','KHACH_HANG'=>'Khách hàng','NHAN_VIEN'=>'Nhân viên','ADMIN'=>'Quản trị','THU'=>'Thu tiền','HOAN_TIEN'=>'Hoàn tiền']; return '<span class="badge">'.e($labels[$s]??str_replace('_',' ',$s)).'</span>'; }
function page_start(string $title,string $sub=''): void { $GLOBALS['page_title']=$title; require APP_ROOT.'/includes/header.php'; echo '<div class="page-heading"><div><p class="eyebrow">SHOWROOM AUTO</p><h1>'.e($title).'</h1>'.($sub?'<p class="muted">'.e($sub).'</p>':'').'</div></div>'; }
function page_end(): void { require APP_ROOT.'/includes/footer.php'; }
function table_start(array $headers): void { echo '<div class="table-wrap"><table><thead><tr>'; foreach($headers as $h)echo '<th>'.e($h).'</th>'; echo '</tr></thead><tbody>'; }
function table_end(): void { echo '</tbody></table></div>'; }
function notice(string $s,bool $error=false): void { echo '<div class="notice '.($error?'error':'').'">'.e($s).'</div>'; }
function handle_form(callable $fn,string $back): void { if(($_SERVER['REQUEST_METHOD']??'')!=='POST')return; require_post(); try { $fn(); flash('Đã lưu thay đổi.'); redirect($back); } catch(AppError $e) { $GLOBALS['form_error']=$e->getMessage(); } catch(mysqli_sql_exception $e) { if(in_array($e->getCode(),[1062,1451,1452,4025],true))$GLOBALS['form_error']='Dữ liệu trùng, chưa hợp lệ hoặc đang được tham chiếu. Vui lòng kiểm tra lại.'; else throw $e; } }
function valid_text(string $s,int $max=255,bool $required=true): string { must((!$required||$s!=='')&&mb_strlen($s)<=$max,'Thông tin bắt buộc bị thiếu hoặc quá dài.'); return $s; }
function valid_date(string $s): string { $s=str_replace('T',' ',$s); if(strlen($s)===16)$s.=':00'; $d=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$s); must($d&&$d->format('Y-m-d H:i:s')===$s,'Ngày giờ không hợp lệ.'); return $s; }
function new_request_key(): string { return bin2hex(random_bytes(16)); }
