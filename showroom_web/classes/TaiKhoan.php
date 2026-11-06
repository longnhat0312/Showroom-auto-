<?php
abstract class TaiKhoan {
 public function __construct(public readonly array $data){}
 public static function fromRow(array $row): self {return match($row['quyen']){'ADMIN'=>new QuanTriVien($row),'NHAN_VIEN'=>new NhanVien($row),default=>new KhachHang($row)};}
 public function id(): int{return (int)$this->data['id'];}
 abstract public function allows(string $permission): bool;
}
