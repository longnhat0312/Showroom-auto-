<?php
require dirname(__DIR__).'/includes/bootstrap.php';
require APP_ROOT.'/services/cart_checkout.php';
require APP_ROOT.'/services/catalog.php';
$me=require_role(['KHACH_HANG']);
handle_form(function()use($me){
 $id=post('ma_cau_hinh');$action=post('action');
 if($action==='xoa'){execute('DELETE FROM gio_hang WHERE tai_khoan_id=? AND ma_cau_hinh=?',[$me['id'],$id]);return;}
 must(in_array($action,['them','sua'],true),'Thao tác không hợp lệ.');
 $n=(int)post('so_luong');must($n>=1&&$n<=10,'Số lượng phải từ 1 đến 10.');tinh_gia($id);
 transaction(function()use($me,$id,$n,$action){
  db_one('SELECT id FROM tai_khoan WHERE id=? FOR UPDATE',[$me['id']]);
  $old=db_one('SELECT * FROM gio_hang WHERE tai_khoan_id=? AND ma_cau_hinh=?',[$me['id'],$id]);
  $total=$action==='them'?$n+(int)($old['so_luong']??0):$n;must($total<=10,'Mỗi cấu hình tối đa 10 xe trong giỏ.');
  execute('INSERT INTO gio_hang VALUES(?,?,?) ON DUPLICATE KEY UPDATE so_luong=VALUES(so_luong)',[$me['id'],$id,$total]);
 });
},'khach_hang/gio_hang.php');
expire_holds();
$rows=db_all("SELECT g.*,c.ten_cau_hinh,d.ten_dong_xe,c.gia_niem_yet,h.ten_hang,
 (SELECT duong_dan FROM tep_xe t WHERE t.ma_cau_hinh=c.ma_cau_hinh AND t.loai='ANH' ORDER BY thu_tu,id LIMIT 1) anh
 FROM gio_hang g JOIN cau_hinh_xe c ON c.ma_cau_hinh=g.ma_cau_hinh JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe JOIN hang_xe h ON h.ma_hang=d.ma_hang WHERE g.tai_khoan_id=?",[$me['id']]);
$gatewayReady=true;try{vnp_config();}catch(AppError){$gatewayReady=false;}
page_start('Giỏ xe của bạn','Chọn chiếc xe cụ thể và hoàn tất yêu cầu đặt cọc ngay tại đây.');
if(!$rows){echo '<section class="panel empty"><h2>Chưa có xe trong giỏ</h2><p>Khám phá các phiên bản và lưu lại chiếc xe bạn yêu thích.</p><a class="button" href="'.e(url('index.php#bo-suu-tap')).'">Khám phá xe</a></section>';page_end();exit;}
$sum=0;$count=0;foreach($rows as$r){$sum+=(int)$r['gia_niem_yet']*(int)$r['so_luong'];$count+=(int)$r['so_luong'];}
?>
<div class="cart-layout"><div class="cart-items">
<?php foreach($rows as$i=>$r):
 $img=image_url($r['anh']);$dialog='checkout-'.$i;
 $cars=db_all("SELECT x.so_khung_vin,x.mau_sac,x.nam_san_xuat,s.ten_showroom FROM xe x JOIN showroom s ON s.ma_showroom=x.ma_showroom JOIN cau_hinh_xe c ON c.ma_cau_hinh=x.ma_cau_hinh JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe WHERE x.ma_cau_hinh=? AND x.trang_thai='SAN_SANG_BAN' AND s.hoat_dong=1 AND c.hien_thi=1 AND d.hien_thi=1 ORDER BY x.so_khung_vin",[$r['ma_cau_hinh']]);
 $carChoices=[];foreach($cars as$c)$carChoices[$c['so_khung_vin']]=$c['mau_sac'].' · '.$c['nam_san_xuat'].' · '.$c['ten_showroom'].' · VIN '.$c['so_khung_vin'];
 $first=array_key_first($carChoices);$name=$r['ten_dong_xe'].' '.$r['ten_cau_hinh'];
?>
<article class="panel cart-item">
 <a class="cart-photo" href="<?= e(url('chi_tiet_xe.php?cau_hinh='.rawurlencode($r['ma_cau_hinh']))) ?>"><?php if($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>"><?php else: ?><span class="vehicle-visual">AUTO</span><?php endif; ?></a>
 <div class="cart-info"><p class="eyebrow"><?= e($r['ten_hang']) ?></p><h2><?= e($name) ?></h2><p class="price"><?= e(money($r['gia_niem_yet'])) ?></p>
 <?php token_form('','quantity-form');field('ma_cau_hinh','',$r['ma_cau_hinh'],'hidden');field('so_luong','Số lượng',$r['so_luong'],'number','min="1" max="10"'); ?>
 <button name="action" value="sua" class="small ghost">Cập nhật</button><button name="action" value="xoa" class="text-button">Xóa</button></form>
 <div class="actions"><button type="button" data-open-checkout="<?= e($dialog) ?>" <?= !$cars?'disabled':'' ?>>Chọn VIN / đặt cọc</button>
 <?php if($first): ?><a class="text-link" href="<?= e(url('khach_hang/lich_hen.php?vin='.rawurlencode($first))) ?>">Đặt lịch xem xe <span aria-hidden="true">↗</span></a><?php endif; ?></div>
 <?php if(!$cars): ?><p class="muted">Hiện chưa có xe sẵn sàng để đặt cọc.</p><?php else: ?><noscript><a href="<?= e(url('khach_hang/dat_coc.php?vin='.rawurlencode($first))) ?>">Mở biểu mẫu đặt cọc</a></noscript><?php endif; ?>
 </div>
</article>
<?php if($cars): ?>
<dialog id="<?= e($dialog) ?>" class="checkout-dialog" aria-labelledby="<?= e($dialog) ?>-title">
 <div class="dialog-heading"><div><p class="eyebrow">ĐẶT CỌC GIỮ XE</p><h2 id="<?= e($dialog) ?>-title">Chọn chiếc xe của bạn</h2></div><button type="button" class="dialog-close ghost" data-close-checkout aria-label="Đóng cửa sổ đặt cọc">×</button></div>
 <div class="checkout-vehicle"><?php if($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>"><?php endif; ?><div><strong><?= e($name) ?></strong><p class="muted">Mỗi lần đặt cọc dành cho 1 xe.</p></div></div>
 <form method="post" action="<?= e(url('api/cart_checkout.php')) ?>" data-checkout-form data-async>
 <?= csrf_field() ?>
 <?php field('ma_cau_hinh','',$r['ma_cau_hinh'],'hidden');field('request_key','',new_request_key(),'hidden');field('quoted_total','','','hidden');select_field('vin','Xe tại showroom',$carChoices,$first,true); ?>
 <p class="checkout-note" data-selected-vin></p>
 <div class="coupon-row"><?php field('ma_km','Mã ưu đãi (nếu có)'); ?><button type="button" class="ghost" data-preview-checkout>Áp dụng</button></div>
 <dl class="checkout-totals"><div><dt>Giá xe</dt><dd data-total="price">—</dd></div><div><dt>Ưu đãi</dt><dd data-total="discount">—</dd></div><div><dt>Giá sau ưu đãi</dt><dd data-total="total">—</dd></div><div class="deposit-total"><dt>Đặt cọc hôm nay</dt><dd data-total="deposit">—</dd></div></dl>
 <fieldset class="payment-options"><legend>Phương thức đặt cọc</legend>
 <label><input type="radio" name="method" value="VNPAY" <?= $gatewayReady?'checked':'disabled' ?>><span>VNPay Sandbox<small>Thanh toán trực tuyến thử nghiệm</small></span></label>
 <label><input type="radio" name="method" value="SHOWROOM" <?= !$gatewayReady?'checked':'' ?>><span>Tại showroom<small>Nhân viên xác nhận khi thực nhận tiền</small></span></label>
 </fieldset>
 <?php if(!$gatewayReady): ?><p class="checkout-note">Thanh toán trực tuyến hiện chưa khả dụng. Bạn có thể chọn đặt cọc tại showroom.</p><?php endif; ?>
 <p class="checkout-note">Xe được giữ <?= (int)setting('hold_minutes') ?> phút sau khi tạo đơn. Khoản còn lại được thanh toán theo hợp đồng trước khi bàn giao.</p>
 <label class="consent"><input type="checkbox" name="agree" value="1" required><span>Tôi đồng ý điều kiện giữ xe và đặt cọc.</span></label>
 <p class="checkout-error" role="alert" data-checkout-error></p>
 <button type="submit" class="checkout-submit" disabled data-checkout-submit><?= $gatewayReady?'Tiếp tục với VNPay':'Xác nhận đặt cọc tại showroom' ?></button>
 <p class="checkout-note">Tạo đơn không đồng nghĩa với đã thanh toán.</p>
 </form>
 <div class="checkout-result" data-checkout-result hidden tabindex="-1"><p class="eyebrow">YÊU CẦU ĐÃ ĐƯỢC GHI NHẬN</p><h3>Đơn mua của bạn đã được tạo</h3><p data-order-message></p><p class="checkout-note">Đơn chưa được xác nhận thanh toán. Vui lòng hoàn tất khoản cọc tại showroom trong thời hạn giữ xe.</p><div class="actions"><a class="button" data-order-link>Xem đơn mua</a><button type="button" class="ghost" data-close-checkout>Đóng</button></div></div>
</dialog>
<?php endif; endforeach; ?>
</div><aside class="panel cart-summary"><p class="eyebrow">LỰA CHỌN CỦA BẠN</p><h2><?= $count ?> xe trong giỏ</h2><p class="muted">Tổng giá tham khảo</p><p class="price"><?= e(money($sum)) ?></p><hr><p>Chọn <strong>“Chọn VIN / đặt cọc”</strong> cạnh chiếc xe bạn muốn mua để mở cửa sổ đặt cọc.</p><p class="muted">Giỏ hàng chưa giữ xe. Bạn có thể đặt cọc lần lượt từng xe.</p><a class="text-link" href="<?= e(url('index.php#bo-suu-tap')) ?>">Tiếp tục khám phá <span aria-hidden="true">→</span></a></aside></div>
<?php page_end(); ?>
