CREATE TABLE gio_hang (
    tai_khoan_id INT NOT NULL,
    ma_cau_hinh VARCHAR(50) NOT NULL,
    so_luong INT NOT NULL CHECK (so_luong BETWEEN 1 AND 10),
    PRIMARY KEY (tai_khoan_id, ma_cau_hinh),
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id),
    FOREIGN KEY (ma_cau_hinh) REFERENCES cau_hinh_xe(ma_cau_hinh)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE khuyen_mai (
    ma_km VARCHAR(50) PRIMARY KEY,
    ten VARCHAR(150) NOT NULL,
    loai VARCHAR(10) NOT NULL CHECK (loai IN ('TIEN', 'PHAN_TRAM')),
    gia_tri DECIMAL(15,2) NOT NULL CHECK (gia_tri > 0),
    giam_toi_da DECIMAL(15,2) NULL CHECK (giam_toi_da >= 0),
    gia_toi_thieu DECIMAL(15,2) NOT NULL DEFAULT 0,
    bat_dau DATETIME NOT NULL,
    ket_thuc DATETIME NOT NULL,
    hoat_dong BOOLEAN NOT NULL DEFAULT 1,
    CHECK (ket_thuc > bat_dau),
    CHECK (loai <> 'PHAN_TRAM' OR gia_tri <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE khuyen_mai_cau_hinh (
    ma_km VARCHAR(50) NOT NULL,
    ma_cau_hinh VARCHAR(50) NOT NULL,
    PRIMARY KEY (ma_km, ma_cau_hinh),
    FOREIGN KEY (ma_km) REFERENCES khuyen_mai(ma_km),
    FOREIGN KEY (ma_cau_hinh) REFERENCES cau_hinh_xe(ma_cau_hinh)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE hop_dong
    ADD ma_km VARCHAR(50) NULL,
    ADD gia_truoc_giam DECIMAL(15,2) NULL,
    ADD tien_giam DECIMAL(15,2) NOT NULL DEFAULT 0 CHECK (tien_giam >= 0),
    ADD FOREIGN KEY (ma_km) REFERENCES khuyen_mai(ma_km);
