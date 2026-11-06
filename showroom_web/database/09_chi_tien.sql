CREATE TABLE chi_tien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_showroom VARCHAR(50) NOT NULL,
    so_khung_vin VARCHAR(50) NULL,
    loai VARCHAR(30) NOT NULL,
    so_tien DECIMAL(15,2) NOT NULL CHECK (so_tien > 0),
    ngay_chi DATETIME NOT NULL,
    ma_chung_tu VARCHAR(100) NOT NULL,
    nguoi_ghi_nhan_id INT NOT NULL,
    noi_dung VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_chi_chung_tu (ma_showroom, ma_chung_tu),
    FOREIGN KEY (ma_showroom) REFERENCES showroom(ma_showroom),
    FOREIGN KEY (so_khung_vin) REFERENCES xe(so_khung_vin),
    FOREIGN KEY (nguoi_ghi_nhan_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
