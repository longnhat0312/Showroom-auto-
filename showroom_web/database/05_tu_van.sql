ALTER TABLE hoi_thoai_chat
    ADD ma_cau_hinh VARCHAR(50) NULL,
    ADD ket_qua TEXT NULL,
    ADD FOREIGN KEY (ma_cau_hinh) REFERENCES cau_hinh_xe(ma_cau_hinh);
CREATE TABLE tep_tin_nhan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_tin_nhan INT NOT NULL,
    duong_dan VARCHAR(255) NOT NULL,
    ten_goc VARCHAR(255) NOT NULL,
    mime VARCHAR(100) NOT NULL,
    FOREIGN KEY (ma_tin_nhan) REFERENCES tin_nhan(ma_tin_nhan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE bao_gia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoi_thoai INT NOT NULL,
    nhan_vien_id INT NOT NULL,
    ma_cau_hinh VARCHAR(50) NOT NULL,
    gia_chot DECIMAL(15,2) NOT NULL CHECK (gia_chot > 0),
    hieu_luc_den DATETIME NOT NULL,
    dieu_kien TEXT NULL,
    ngay_lap DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_hoi_thoai) REFERENCES hoi_thoai_chat(ma_hoi_thoai),
    FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (ma_cau_hinh) REFERENCES cau_hinh_xe(ma_cau_hinh)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE nhan_vien_truc (
    nhan_vien_id INT PRIMARY KEY,
    online_luc DATETIME NOT NULL,
    nhan_gan_nhat DATETIME NULL,
    FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
