CREATE TABLE phan_cong_khach (
    khach_hang_id INT PRIMARY KEY,
    nhan_vien_id INT NOT NULL,
    nhu_cau TEXT NULL,
    cap_nhat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (khach_hang_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE lich_hen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    khach_hang_id INT NOT NULL,
    nhan_vien_id INT NULL,
    so_khung_vin VARCHAR(50) NOT NULL,
    ma_showroom VARCHAR(50) NOT NULL,
    bat_dau DATETIME NOT NULL,
    ket_thuc DATETIME NOT NULL,
    de_xuat_bat_dau DATETIME NULL,
    de_xuat_ket_thuc DATETIME NULL,
    trang_thai VARCHAR(30) NOT NULL DEFAULT 'CHO_XAC_NHAN',
    yeu_cau TEXT NULL,
    ket_qua TEXT NULL,
    FOREIGN KEY (khach_hang_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (so_khung_vin) REFERENCES xe(so_khung_vin),
    FOREIGN KEY (ma_showroom) REFERENCES showroom(ma_showroom),
    CHECK (ket_thuc > bat_dau)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE lich_hen_lich_su (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lich_hen_id INT NOT NULL,
    nguoi_thao_tac_id INT NOT NULL,
    hanh_dong VARCHAR(50) NOT NULL,
    chi_tiet TEXT NOT NULL,
    thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lich_hen_id) REFERENCES lich_hen(id),
    FOREIGN KEY (nguoi_thao_tac_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
