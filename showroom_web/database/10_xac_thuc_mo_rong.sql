ALTER TABLE tai_khoan
    ADD email_xac_minh_luc DATETIME NULL,
    ADD sdt_dang_nhap VARCHAR(20) NULL,
    ADD sdt_xac_minh_luc DATETIME NULL,
    ADD phien_ban_xac_thuc INT NOT NULL DEFAULT 1,
    ADD UNIQUE KEY uq_sdt_dang_nhap (sdt_dang_nhap);
CREATE TABLE otp_thu_thach (
    id VARCHAR(64) PRIMARY KEY,
    tai_khoan_id INT NOT NULL,
    muc_dich VARCHAR(30) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    het_han DATETIME NOT NULL,
    so_lan_sai INT NOT NULL DEFAULT 0,
    da_dung_luc DATETIME NULL,
    tao_luc DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE danh_tinh_oauth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tai_khoan_id INT NOT NULL,
    nha_cung_cap VARCHAR(20) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_oauth_subject (nha_cung_cap, subject),
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE gioi_han_xac_thuc (
    khoa VARCHAR(64) PRIMARY KEY,
    so_lan INT NOT NULL DEFAULT 0,
    bat_dau DATETIME NOT NULL,
    khoa_den DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
