<?php class DanhGia {public static function cuaHopDong(string $id):?array{return db_one('SELECT * FROM danh_gia WHERE so_hop_dong=?',[$id]);}}
