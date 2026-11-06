<?php
require_once APP_ROOT.'/services/catalog.php';
$me=require_role(['ADMIN']);
$defs=catalog_definitions();must(isset($defs[$module]),'Không có trang quản lý này.',404);$d=$defs[$module];$table=$d['table'];$pk=$d['pk'];$visibleWhere=$d['where']??'1=1';$edit=get('edit');
$row=$edit!==''?db_one("SELECT * FROM `$table` WHERE `$pk`=? AND $visibleWhere",[$edit]):null;
handle_form(function()use($d,$table,$pk,$me,$module,$visibleWhere){
 $id=post('_id');$old=$id!==''?db_one("SELECT * FROM `$table` WHERE `$pk`=? AND $visibleWhere",[$id]):null;must($id===''||$old,'Bản ghi không tồn tại.');$values=[];
 foreach($d['fields']as$name=>$f){if($name===$pk&&$old){$values[$name]=$old[$name];continue;}$v=post($name);$type=$f[1];$required=$f[3]??true;
  if(!$required&&$v===''){$values[$name]=null;continue;}
  if($type==='money')$v=(string)vnd($v,true);
  elseif($type==='int'){must(ctype_digit($v)&&((int)$v)>=1&&((int)$v)<=99,'Số nguyên phải từ 1 đến 99.');}
  elseif($type==='select'){must(array_key_exists($v,$f[2]),'Lựa chọn không hợp lệ.');}
  elseif($type==='datetime-local')$v=valid_date($v);
  elseif($type==='number'){must((bool)preg_match('/^\d{1,13}(\.\d{1,2})?$/D',$v),'Giá trị số không hợp lệ.');}
  else valid_text($v,$f[4]??255,$required);
  if($name===$pk&&!$old)must((bool)preg_match('/^[A-Za-z0-9_-]{1,50}$/D',$v),'Mã chỉ gồm chữ không dấu, số, dấu _ hoặc -.');
  $values[$name]=$v;
 }
 if($table==='khuyen_mai'){must($values['ket_thuc']>$values['bat_dau'],'Ngày kết thúc phải sau ngày bắt đầu.');must($values['gia_tri']>0&&($values['loai']!=='PHAN_TRAM'||$values['gia_tri']<=100),'Mức giảm không hợp lệ.');}
 if($table==='noi_dung_web'){must((bool)preg_match('/^[a-z0-9-]{1,150}$/D',$values['slug']),'Slug chỉ gồm chữ thường không dấu, số và dấu -.');if($values['lien_ket'])must(str_starts_with($values['lien_ket'],'https://')||str_starts_with($values['lien_ket'],BASE_URL.'/'),'Liên kết phải là HTTPS hoặc đường dẫn ứng dụng.');if(!$old)$values['nguoi_tao_id']=$me['id'];}
 transaction(function()use($old,$values,$table,$pk,$d,$id,$module){
  if($old){$sets=[];$params=[];foreach($values as$k=>$v){if($k===$pk)continue;$sets[]="`$k`=?";$params[]=$v;}$params[]=$id;execute("UPDATE `$table` SET ".implode(',',$sets)." WHERE `$pk`=?",$params);$saved=$id;}
  else {$keys=array_keys($values);execute("INSERT INTO `$table` (`".implode('`,`',$keys).'`) VALUES ('.implode(',',array_fill(0,count($keys),'?')).')',array_values($values));$saved=$values[$pk]??insert_id();}
  if($table==='khuyen_mai'){execute('DELETE FROM khuyen_mai_cau_hinh WHERE ma_km=?',[$saved]);$ids=$_POST['ap_dung']??[];must(is_array($ids),'Danh sách cấu hình không hợp lệ.');foreach(array_unique($ids)as$c){must(is_string($c)&&db_one('SELECT 1 FROM cau_hinh_xe WHERE ma_cau_hinh=?',[$c]),'Cấu hình không tồn tại.');execute('INSERT INTO khuyen_mai_cau_hinh VALUES (?,?)',[$saved,$c]);}}
  audit($old?'SUA':'THEM',$table,(string)$saved,$old,$values);
 });
},'admin/'.$module.'.php');
page_start($d['title'],'Thêm mới hoặc chọn Sửa để cập nhật. Dữ liệu đã phát sinh giao dịch được giữ lại trong lịch sử.');
echo '<div class="actions">';foreach($defs as$k=>$v)echo '<a class="button ghost small" href="'.e(url('admin/'.$k.'.php')).'">'.e($v['title']).'</a>';echo '</div><div class="panel">';
token_form();field('_id','',$row[$pk]??'','hidden');
echo '<div class="grid">';foreach($d['fields']as$name=>$f){$val=$row[$name]??($f[1]==='select'?array_key_first($f[2]):'');if($f[1]==='select')select_field($name,$f[0],$f[2],$val,$f[3]??true);elseif($f[1]==='textarea')textarea($name,$f[0],(string)$val);else field($name,$f[0],$val,in_array($f[1],['money','int'])?'number':$f[1],(($f[3]??true)?'required ':'').($name===$pk&&$row?'readonly ':'').($f[1]==='number'?'step="0.01" ':''));}echo '</div>';
if($table==='khuyen_mai'){$selected=$row?choices('SELECT ma_cau_hinh FROM khuyen_mai_cau_hinh WHERE ma_km=?','ma_cau_hinh','ma_cau_hinh',[$row[$pk]]):[];echo '<p>Áp dụng cho cấu hình:</p>';foreach(choices('SELECT ma_cau_hinh,ten_cau_hinh FROM cau_hinh_xe','ma_cau_hinh','ten_cau_hinh')as$k=>$v)echo '<label><input type="checkbox" name="ap_dung[]" value="'.e($k).'" '.(isset($selected[$k])?'checked':'').'>'.e($k.' · '.$v).'</label>';}
echo '<button>'.($row?'Lưu thay đổi':'Thêm mới').'</button></form></div>';
$rows=db_all("SELECT * FROM `$table` WHERE $visibleWhere ORDER BY `$pk` DESC LIMIT 200");table_start(array_merge(array_column($d['fields'],0),['Thao tác']));foreach($rows as$r){echo '<tr>';foreach($d['fields']as$k=>$f)echo '<td>'.($f[1]==='money'?e(money($r[$k])):e(mb_strimwidth((string)($r[$k]??''),0,90,'…'))).'</td>';echo '<td><a href="'.e(url('admin/'.$module.'.php?edit='.rawurlencode((string)$r[$pk]))).'">Sửa</a></td></tr>';}table_end();page_end();
