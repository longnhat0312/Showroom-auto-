CREATE TABLE showroom (
    ma_showroom VARCHAR(50) PRIMARY KEY,
    ten_showroom VARCHAR(100) NOT NULL,
    dia_chi VARCHAR(255) NOT NULL,
    hoat_dong BOOLEAN NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO showroom VALUES ('MAIN', 'Showroom chính', 'Cập nhật địa chỉ showroom', 1);

CREATE TABLE cau_hinh_xe (
    ma_cau_hinh VARCHAR(50) PRIMARY KEY,
    ma_dong_xe VARCHAR(50) NOT NULL,
    ten_cau_hinh VARCHAR(100) NOT NULL,
    nhien_lieu VARCHAR(30) NULL,
    hop_so VARCHAR(30) NULL,
    dong_co VARCHAR(100) NULL,
    gia_niem_yet DECIMAL(15,2) NOT NULL CHECK (gia_niem_yet >= 0),
    hien_thi BOOLEAN NOT NULL DEFAULT 1,
    UNIQUE KEY uq_cau_hinh_dong (ma_cau_hinh, ma_dong_xe),
    FOREIGN KEY (ma_dong_xe) REFERENCES dong_xe(ma_dong_xe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO cau_hinh_xe (ma_cau_hinh, ma_dong_xe, ten_cau_hinh, gia_niem_yet, hien_thi)
SELECT ma_dong_xe, ma_dong_xe, 'Tiêu chuẩn', gia_niem_yet, hien_thi FROM dong_xe;

ALTER TABLE xe
    ADD ma_cau_hinh VARCHAR(50) NULL,
    ADD ma_showroom VARCHAR(50) NOT NULL DEFAULT 'MAIN',
    ADD gia_von DECIMAL(15,2) NULL CHECK (gia_von >= 0),
    ADD ngay_nhap_dau DATETIME NULL,
    ADD odo INT NULL CHECK (odo >= 0),
    ADD tinh_trang_phap_ly VARCHAR(255) NULL,
    ADD bao_cao_kiem_dinh TEXT NULL;
UPDATE xe SET ma_cau_hinh = ma_dong_xe;
ALTER TABLE xe
    MODIFY ma_cau_hinh VARCHAR(50) NOT NULL,
    ADD FOREIGN KEY (ma_cau_hinh, ma_dong_xe) REFERENCES cau_hinh_xe(ma_cau_hinh, ma_dong_xe),
    ADD FOREIGN KEY (ma_showroom) REFERENCES showroom(ma_showroom);

CREATE TABLE tep_xe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_cau_hinh VARCHAR(50) NULL,
    so_khung_vin VARCHAR(50) NULL,
    loai VARCHAR(20) NOT NULL CHECK (loai IN ('ANH', 'VIDEO', 'ANH_360')),
    duong_dan VARCHAR(255) NOT NULL,
    thu_tu INT NOT NULL DEFAULT 0,
    FOREIGN KEY (ma_cau_hinh) REFERENCES cau_hinh_xe(ma_cau_hinh),
    FOREIGN KEY (so_khung_vin) REFERENCES xe(so_khung_vin),
    CHECK ((ma_cau_hinh IS NULL) <> (so_khung_vin IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bien_dong_kho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    so_khung_vin VARCHAR(50) NOT NULL,
    loai VARCHAR(20) NOT NULL CHECK (loai IN ('NHAP', 'CHUYEN', 'BAN_GIAO')),
    showroom_di VARCHAR(50) NULL,
    showroom_den VARCHAR(50) NULL,
    nguoi_lap_id INT NOT NULL,
    thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ghi_chu VARCHAR(255) NULL,
    FOREIGN KEY (so_khung_vin) REFERENCES xe(so_khung_vin),
    FOREIGN KEY (showroom_di) REFERENCES showroom(ma_showroom),
    FOREIGN KEY (showroom_den) REFERENCES showroom(ma_showroom),
    FOREIGN KEY (nguoi_lap_id) REFERENCES tai_khoan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
