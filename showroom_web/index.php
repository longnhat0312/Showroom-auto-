<?php
require __DIR__.'/includes/bootstrap.php';require APP_ROOT.'/services/catalog.php';require APP_ROOT.'/services/thanh_toan.php';expire_holds();
$where=['c.hien_thi=1','d.hien_thi=1'];$p=[];$q=mb_substr(get('q'),0,100);
if($q!==''){$where[]='(d.ten_dong_xe LIKE ? OR c.ten_cau_hinh LIKE ?)';$p[]='%'.$q.'%';$p[]='%'.$q.'%';}
foreach(['hang'=>'d.ma_hang','phan_khuc'=>'d.phan_khuc','nhien_lieu'=>'c.nhien_lieu','hop_so'=>'c.hop_so']as$key=>$col)if(get($key)!==''){$where[]="$col=?";$p[]=get($key);}
foreach(['gia_min'=>'>=','gia_max'=>'<=']as$key=>$op)if(get($key)!==''){$where[]="c.gia_niem_yet $op ?";$p[]=vnd(get($key),true);}
if(get('nam')!==''){must(ctype_digit(get('nam')),'Năm không hợp lệ.');$where[]='EXISTS (SELECT 1 FROM xe y WHERE y.ma_cau_hinh=c.ma_cau_hinh AND y.nam_san_xuat=?)';$p[]=get('nam');}
$page=max(1,min(10000,(int)get('page')));$offset=($page-1)*9;$sql=implode(' AND ',$where);$order=['gia_tang'=>'c.gia_niem_yet ASC','gia_giam'=>'c.gia_niem_yet DESC','ten'=>'d.ten_dong_xe ASC'][get('sort')]??'d.ten_dong_xe ASC';
$total=db_one("SELECT COUNT(*) n FROM cau_hinh_xe c JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe WHERE $sql",$p)['n'];
$rows=db_all("SELECT c.*,d.ten_dong_xe,d.phan_khuc,d.so_cho_ngoi,h.ten_hang,
 (SELECT COUNT(*) FROM xe x WHERE x.ma_cau_hinh=c.ma_cau_hinh AND x.trang_thai='SAN_SANG_BAN') ton,
 (SELECT duong_dan FROM tep_xe t WHERE t.ma_cau_hinh=c.ma_cau_hinh AND t.loai='ANH' ORDER BY thu_tu,id LIMIT 1) anh
 FROM cau_hinh_xe c JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe JOIN hang_xe h ON h.ma_hang=d.ma_hang WHERE $sql ORDER BY $order,c.ma_cau_hinh LIMIT 9 OFFSET $offset",$p);
$GLOBALS['page_title']='Auto Showroom · Khám phá chiếc xe của bạn';$GLOBALS['page_class']='home-page';require APP_ROOT.'/includes/header.php';
$featured=db_one("SELECT c.ma_cau_hinh,c.ten_cau_hinh,d.ten_dong_xe,h.ten_hang,(SELECT duong_dan FROM tep_xe t WHERE t.ma_cau_hinh=c.ma_cau_hinh AND t.loai='ANH' ORDER BY thu_tu,id LIMIT 1) anh FROM cau_hinh_xe c JOIN dong_xe d ON d.ma_dong_xe=c.ma_dong_xe JOIN hang_xe h ON h.ma_hang=d.ma_hang WHERE c.hien_thi=1 AND d.hien_thi=1 AND EXISTS(SELECT 1 FROM tep_xe t WHERE t.ma_cau_hinh=c.ma_cau_hinh AND t.loai='ANH') ORDER BY c.gia_niem_yet DESC LIMIT 1");
echo '<section class="showcase"><div><p class="eyebrow">AUTO SHOWROOM · YOUR NEXT JOURNEY</p><h1>Dấu ấn riêng.<br>Trên mọi hành trình.</h1><p class="lead">Khám phá chiếc xe phù hợp với phong cách sống của bạn. Trải nghiệm trực tiếp, lựa chọn theo cách của riêng mình.</p><div class="actions"><a class="button" href="#bo-suu-tap">Khám phá bộ sưu tập</a><a class="text-link" href="'.e(url('khach_hang/lich_hen.php?dat_lich=1')).'">Đặt lịch xem xe <span aria-hidden="true">↗</span></a></div></div>';
if($featured&&$photo=image_url($featured['anh']))echo '<figure class="showcase-image"><img fetchpriority="high" src="'.e($photo).'" alt="'.e($featured['ten_dong_xe'].' '.$featured['ten_cau_hinh']).'"><figcaption><div>'.e($featured['ten_dong_xe'].' '.$featured['ten_cau_hinh']).'<small>'.e($featured['ten_hang']).'</small></div><a class="text-link" href="'.e(url('chi_tiet_xe.php?cau_hinh='.rawurlencode($featured['ma_cau_hinh']))).'">Khám phá phiên bản <span aria-hidden="true">↗</span></a></figcaption></figure>';
echo '</section><div class="journey-strip"><div><span>01 / KHÁM PHÁ</span><strong>Tìm phiên bản phù hợp</strong></div><div><span>02 / TRẢI NGHIỆM</span><strong>Đặt lịch xem xe trực tiếp</strong></div><div><span>03 / LỰA CHỌN</span><strong>Đặt cọc chiếc xe của bạn</strong></div></div>';

foreach(db_all("SELECT * FROM noi_dung_web WHERE loai='BANNER' AND trang_thai='CONG_KHAI' ORDER BY ngay_tao DESC LIMIT 3")as$banner){echo '<section class="panel">';if($img=image_url($banner['hinh_anh']))echo '<img class="image-main" src="'.e($img).'" alt="'.e($banner['tieu_de']).'">';echo '<h2>'.e($banner['tieu_de']).'</h2><p>'.nl2br(e($banner['noi_dung'])).'</p>';if($banner['lien_ket']&&(str_starts_with($banner['lien_ket'],'https://')||str_starts_with($banner['lien_ket'],BASE_URL.'/')))echo '<a class="button ghost" href="'.e($banner['lien_ket']).'">Tìm hiểu thêm</a>';echo '</section>';}
echo '<section id="bo-suu-tap"><div class="collection-heading"><div><p class="eyebrow">ĐƯỢC CHỌN BỞI BẠN</p><h2>Bộ sưu tập xe</h2></div><p class="muted">Một lựa chọn. Nhiều hành trình mới.</p></div>';
echo '<form class="filters catalog-filters" method="get" action="'.e(url('index.php#bo-suu-tap')).'"><div class="primary-filters">';
field('q','Tìm xe / phiên bản',$q,'search','placeholder="Tên xe bạn đang tìm" list="suggestions" data-suggest="'.e(url('api/goi_y.php')).'"');
select_field('hang','Hãng xe',choices('SELECT * FROM hang_xe ORDER BY ten_hang','ma_hang','ten_hang'),get('hang'));
select_field('phan_khuc','Kiểu dáng',['Sedan'=>'Sedan','SUV'=>'SUV','MPV'=>'MPV','Crossover'=>'Crossover','Pickup'=>'Bán tải'],get('phan_khuc'));
echo '<button>Tìm xe</button></div><datalist id="suggestions"></datalist>';
$advanced=false;foreach(['nhien_lieu','hop_so','gia_min','gia_max','nam','sort']as$k)if(get($k)!=='')$advanced=true;
echo '<details class="advanced-filters" '.($advanced?'open':'').'><summary>Bộ lọc chi tiết · giá, nhiên liệu, năm sản xuất</summary><div class="advanced-grid">';
select_field('nhien_lieu','Nhiên liệu',['XANG'=>'Xăng','DAU'=>'Dầu','DIEN'=>'Điện','HYBRID'=>'Hybrid'],get('nhien_lieu'));
select_field('hop_so','Hộp số',['AT'=>'Tự động','MT'=>'Số sàn','CVT'=>'CVT'],get('hop_so'));
field('gia_min','Giá từ (đ)',get('gia_min'),'number','min="0"');field('gia_max','Giá đến (đ)',get('gia_max'),'number','min="0"');field('nam','Năm sản xuất',get('nam'),'number');
select_field('sort','Sắp xếp',['ten'=>'Tên xe','gia_tang'=>'Giá tăng','gia_giam'=>'Giá giảm'],get('sort'));
echo '</div></details></form><p class="muted catalog-count">'.(int)$total.' phiên bản phù hợp</p><div class="grid vehicle-grid">';
foreach($rows as$r){$img=image_url($r['anh']);echo '<article class="card vehicle-card">'.($img?'<img src="'.e($img).'" alt="'.e($r['ten_dong_xe']).'">':'<div class="vehicle-visual" aria-label="Chưa có ảnh xe">AUTO</div>').'<p class="eyebrow">'.e($r['ten_hang']).'</p><h2>'.e($r['ten_dong_xe'].' '.$r['ten_cau_hinh']).'</h2><p class="card-meta">'.e($r['phan_khuc'].' · '.$r['so_cho_ngoi'].' chỗ · '.$r['hop_so']).'</p><p class="price">'.e(money($r['gia_niem_yet'])).'</p><p class="availability">'.(int)$r['ton'].' xe sẵn sàng</p><a class="button ghost" href="'.e(url('chi_tiet_xe.php?cau_hinh='.rawurlencode($r['ma_cau_hinh']))).'">Xem chi tiết</a></article>';}
echo '</div>';if(!$rows)echo '<div class="panel empty">Chưa có xe phù hợp. Thử thay đổi bộ lọc hoặc quay lại sau.</div>';
echo '<div class="actions">';for($i=max(1,$page-2);$i<=min((int)ceil($total/9),$page+2);$i++)echo '<a class="button '.($i===$page?'':'ghost').'" href="'.e(url('index.php?'.http_build_query(array_merge($_GET,['page'=>$i])))).'">'.$i.'</a>';echo '</div></section>';page_end();
