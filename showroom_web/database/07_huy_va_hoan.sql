CREATE TABLE yeu_cau_huy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    so_hop_dong VARCHAR(50) NOT NULL,
    khach_hang_id INT NOT NULL,
    ly_do TEXT NOT NULL,
    trang_thai VARCHAR(20) NOT NULL DEFAULT 'CHO_DUYET',
    nguoi_duyet_id INT NULL,
    tien_duoc_hoan DECIMAL(15,2) NULL CHECK (tien_duoc_hoan >= 0),
    ket_qua TEXT NULL,
    ngay_gui DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_duyet DATETIME NULL,
    FOREIGN KEY (so_hop_dong) REFERENCES hop_dong(so_hop_dong),
    FOREIGN KEY (khach_hang_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (nguoi_duyet_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
