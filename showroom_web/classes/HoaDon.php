<?php class HoaDon {public static function theoHopDong(string $id,array $u):array{contract($id,$u);return db_all('SELECT * FROM hoa_don WHERE so_hop_dong=? ORDER BY ngay_thu DESC',[$id]);}}
