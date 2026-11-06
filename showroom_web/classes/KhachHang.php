<?php class KhachHang extends TaiKhoan {
 public function allows(string $permission): bool{return false;}
 public function datCoc(string $vin,string $maKm,string $key,?int $baoGia=null):string{require_once APP_ROOT.'/services/dat_coc.php';return reserve_vehicle($this->data,$vin,$maKm,$key,$baoGia);}
 public function hopDong():array{return db_all('SELECT * FROM hop_dong WHERE tai_khoan_id=? ORDER BY ngay_lap DESC',[$this->id()]);}
}
