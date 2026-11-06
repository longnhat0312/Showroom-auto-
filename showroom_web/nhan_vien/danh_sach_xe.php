<?php
require dirname(__DIR__).'/includes/bootstrap.php';
require APP_ROOT.'/services/thanh_toan.php';

$me = require_role(['NHAN_VIEN', 'ADMIN']);
expire_holds();
$conditions = [];
$params = [];
if ($me['quyen'] !== 'ADMIN') {
    $conditions[] = 'EXISTS (SELECT 1 FROM pham_vi_showroom p WHERE p.ma_showroom=x.ma_showroom AND p.tai_khoan_id=?)';
    $params[] = $me['id'];
}
$search = mb_substr(get('q'), 0, 100);
if ($search !== '') {
    $conditions[] = '(x.so_khung_vin LIKE ? OR d.ten_dong_xe LIKE ? OR c.ten_cau_hinh LIKE ? OR h.ten_hang LIKE ?)';
    $params = [...$params, ...array_fill(0, 4, '%'.$search.'%')];
}
$statuses = ['SAN_SANG_BAN'=>'Sẵn sàng bán', 'DANG_GIU'=>'Đang giữ', 'DA_DAT_COC'=>'Đã đặt cọc', 'DA_BAN_GIAO'=>'Đã bàn giao'];
$status = get('trang_thai');
if ($status !== '') {
    must(isset($statuses[$status]), 'Trạng thái xe không hợp lệ.');
    $conditions[] = 'x.trang_thai=?';
    $params[] = $status;
}
foreach (['hang'=>'d.ma_hang', 'showroom'=>'x.ma_showroom'] as $key=>$column) {
    if (get($key) !== '') {
        $conditions[] = $column.'=?';
        $params[] = get($key);
    }
}
$where = $conditions ? implode(' AND ', $conditions) : '1=1';
$from = ' FROM xe x JOIN cau_hinh_xe c ON c.ma_cau_hinh=x.ma_cau_hinh
    JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe
    JOIN hang_xe h ON h.ma_hang=d.ma_hang
    JOIN showroom s ON s.ma_showroom=x.ma_showroom';
$total = (int)db_one('SELECT COUNT(*) n'.$from.' WHERE '.$where, $params)['n'];
$pages = max(1, (int)ceil($total / 20));
$page = max(1, min($pages, (int)get('page') ?: 1));
$offset = ($page - 1) * 20;
$rows = db_all('SELECT x.so_khung_vin,x.mau_sac,x.nam_san_xuat,x.trang_thai,
    c.ma_cau_hinh,c.ten_cau_hinh,c.gia_niem_yet,c.hien_thi AS cau_hinh_hien,
    d.ten_dong_xe,d.hien_thi AS dong_hien,h.ten_hang,s.ten_showroom'
    .$from.' WHERE '.$where.' ORDER BY h.ten_hang,d.ten_dong_xe,c.ten_cau_hinh,x.so_khung_vin LIMIT 20 OFFSET '.$offset, $params);
$showrooms = $me['quyen'] === 'ADMIN'
    ? choices('SELECT ma_showroom,ten_showroom FROM showroom ORDER BY ten_showroom','ma_showroom','ten_showroom')
    : choices('SELECT s.ma_showroom,s.ten_showroom FROM showroom s JOIN pham_vi_showroom p ON p.ma_showroom=s.ma_showroom WHERE p.tai_khoan_id=? ORDER BY s.ten_showroom','ma_showroom','ten_showroom',[$me['id']]);

page_start('Danh sách xe', 'Tra cứu giá bán, số khung VIN và trạng thái xe tại showroom phụ trách.');
echo '<form method="get" class="filters">';
field('q','Tên xe, hãng hoặc VIN',$search,'search','maxlength="100"');
select_field('hang','Hãng xe',choices('SELECT ma_hang,ten_hang FROM hang_xe ORDER BY ten_hang','ma_hang','ten_hang'),get('hang'));
select_field('showroom','Showroom',$showrooms,get('showroom'));
select_field('trang_thai','Trạng thái',$statuses,$status);
echo '<button>Tìm xe</button><a class="button ghost" href="'.e(url('nhan_vien/danh_sach_xe.php')).'">Xóa bộ lọc</a></form>';
echo '<p class="muted">'.$total.' xe phù hợp'.($total ? ' · Hiển thị '.($offset+1).'–'.min($offset+20,$total) : '').'</p>';

if (!$showrooms && $me['quyen'] !== 'ADMIN') {
    notice('Bạn chưa được phân công showroom. Quản trị viên có thể bổ sung tại mục Tài khoản / phân quyền.');
} elseif (!$rows) {
    echo '<div class="panel empty">Chưa có xe phù hợp trong showroom phụ trách.</div>';
} else {
    table_start(['Xe / phiên bản','VIN','Màu / năm','Giá bán niêm yết','Showroom','Trạng thái','']);
    foreach ($rows as $car) {
        echo '<tr><td><strong>'.e($car['ten_dong_xe'].' '.$car['ten_cau_hinh']).'</strong><br><small>'.e($car['ten_hang']).'</small></td>';
        echo '<td>'.e($car['so_khung_vin']).'</td><td>'.e($car['mau_sac'].' / '.$car['nam_san_xuat']).'</td>';
        echo '<td>'.e(money($car['gia_niem_yet'])).'</td><td>'.e($car['ten_showroom']).'</td><td>'.badge($car['trang_thai']).'</td><td>';
        if ($car['cau_hinh_hien'] && $car['dong_hien']) {
            echo '<a href="'.e(url('chi_tiet_xe.php?cau_hinh='.rawurlencode($car['ma_cau_hinh']))).'">Xem phiên bản</a>';
        }
        if ($car['trang_thai']==='SAN_SANG_BAN') echo '<br><a href="'.e(url('nhan_vien/tao_hop_dong.php?vin='.rawurlencode($car['so_khung_vin']))).'">Tạo hợp đồng</a>';
        echo '</td></tr>';
    }
    table_end();
}
if ($pages > 1) {
    echo '<div class="actions" aria-label="Phân trang danh sách xe">';
    $filters = ['q'=>$search,'hang'=>get('hang'),'showroom'=>get('showroom'),'trang_thai'=>$status];
    for ($i=max(1,$page-2); $i<=min($pages,$page+2); $i++) {
        echo '<a class="button '.($i===$page ? '' : 'ghost').'" href="'.e(url('nhan_vien/danh_sach_xe.php?'.http_build_query($filters+['page'=>$i]))).'"'.($i===$page?' aria-current="page"':'').'>'.$i.'</a>';
    }
    echo '</div>';
}
page_end();
