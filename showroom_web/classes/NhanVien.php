<?php class NhanVien extends TaiKhoan {
 public function allows(string $p): bool{$common=['XEM_KHACH','SUA_KHACH','NHAN_CHAT','XAC_NHAN_LICH','GHI_NHAN_THU','DUYET_HUY','BAN_GIAO'];return in_array($p,$common,true)||db_one('SELECT 1 FROM quyen_chi_tiet WHERE tai_khoan_id=? AND ma_quyen=?',[$this->id(),$p])!==null;}
 public function nhanTuVan(int $id):void{require_once APP_ROOT.'/services/chat.php';claim_chat($this->data,$id);}
}
