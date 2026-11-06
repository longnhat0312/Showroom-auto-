ALTER TABLE hop_dong
    ADD ma_yeu_cau VARCHAR(64) NULL,
    ADD ma_showroom_chot VARCHAR(50) NULL,
    ADD gia_von_chot DECIMAL(15,2) NULL,
    ADD nhan_vien_chot_id INT NULL,
    ADD ngay_ky DATETIME NULL,
    ADD ho_so_day_du BOOLEAN NOT NULL DEFAULT 0,
    ADD bao_gia_id INT NULL,
    ADD ma_hoi_thoai INT NULL,
    ADD UNIQUE KEY uq_dat_mua_yeu_cau (tai_khoan_id, ma_yeu_cau),
    ADD FOREIGN KEY (ma_showroom_chot) REFERENCES showroom(ma_showroom),
    ADD FOREIGN KEY (nhan_vien_chot_id) REFERENCES tai_khoan(id),
    ADD FOREIGN KEY (bao_gia_id) REFERENCES bao_gia(id),
    ADD FOREIGN KEY (ma_hoi_thoai) REFERENCES hoi_thoai_chat(ma_hoi_thoai);
ALTER TABLE hoa_don ADD UNIQUE KEY uq_hoa_don_phien (nguon_tham_chieu, ma_phien_tt);
CREATE TABLE moc_thanh_toan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    so_hop_dong VARCHAR(50) NOT NULL,
    ten_moc VARCHAR(100) NOT NULL,
    so_tien_luy_ke DECIMAL(15,2) NOT NULL CHECK (so_tien_luy_ke > 0),
    han_thanh_toan DATETIME NULL,
    FOREIGN KEY (so_hop_dong) REFERENCES hop_dong(so_hop_dong)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
