ALTER TABLE tai_khoan ADD mfa_secret TEXT NULL, ADD mfa_counter BIGINT NOT NULL DEFAULT 0;
ALTER TABLE hoa_don ADD het_han_phien DATETIME NULL, ADD tao_phien_luc DATETIME NULL,
 ADD ma_yeu_cau VARCHAR(64) NULL, ADD yeu_cau_huy_id INT NULL,
 ADD UNIQUE KEY uq_thu_yeu_cau (so_hop_dong, ma_yeu_cau),
 ADD FOREIGN KEY (yeu_cau_huy_id) REFERENCES yeu_cau_huy(id);
ALTER TABLE lich_hen ADD yeu_cau_loai VARCHAR(20) NULL, ADD nguoi_de_xuat_id INT NULL,
 ADD FOREIGN KEY (nguoi_de_xuat_id) REFERENCES tai_khoan(id),
 ADD INDEX ix_lich_vin (so_khung_vin,bat_dau,ket_thuc);
CREATE INDEX ix_hd_vin ON hop_dong(so_khung_vin,trang_thai_hd);
CREATE INDEX ix_tin_hoi_id ON tin_nhan(ma_hoi_thoai,ma_tin_nhan);
CREATE TABLE mfa_khoi_phuc (
 id INT AUTO_INCREMENT PRIMARY KEY, tai_khoan_id INT NOT NULL,
 ma_hash VARCHAR(255) NOT NULL, da_dung_luc DATETIME NULL,
 FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO pham_vi_showroom (tai_khoan_id,ma_showroom)
 SELECT id,'MAIN' FROM tai_khoan WHERE quyen='NHAN_VIEN';
