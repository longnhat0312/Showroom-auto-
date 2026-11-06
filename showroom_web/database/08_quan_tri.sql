CREATE TABLE quyen_chi_tiet (
    tai_khoan_id INT NOT NULL,
    ma_quyen VARCHAR(60) NOT NULL,
    PRIMARY KEY (tai_khoan_id, ma_quyen),
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE pham_vi_showroom (
    tai_khoan_id INT NOT NULL,
    ma_showroom VARCHAR(50) NOT NULL,
    PRIMARY KEY (tai_khoan_id, ma_showroom),
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (ma_showroom) REFERENCES showroom(ma_showroom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE noi_dung_web (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loai VARCHAR(20) NOT NULL CHECK (loai IN ('TIN_TUC', 'BANNER', 'GIOI_THIEU')),
    slug VARCHAR(150) NOT NULL UNIQUE,
    tieu_de VARCHAR(200) NOT NULL,
    noi_dung MEDIUMTEXT NULL,
    hinh_anh VARCHAR(255) NULL,
    lien_ket VARCHAR(255) NULL,
    trang_thai VARCHAR(20) NOT NULL DEFAULT 'NHAP',
    nguoi_tao_id INT NOT NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_tao_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE nhat_ky (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nguoi_thao_tac_id INT NULL,
    hanh_dong VARCHAR(60) NOT NULL,
    doi_tuong VARCHAR(60) NOT NULL,
    ma_doi_tuong VARCHAR(100) NOT NULL,
    truoc_json LONGTEXT NULL,
    sau_json LONGTEXT NULL,
    thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_thao_tac_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
