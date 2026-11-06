-- SHOWROOM AUTO - 9 bang cot loi theo chuong 3 cua bao cao.
-- Trong phpMyAdmin: chon database showroom_auto, sau do Import tep nay.
-- Chi chay mot lan tren database chua co bang. Khong co lenh xoa du lieu.
-- Khong chua tai khoan mau hay mat khau dang nhap.

SET NAMES utf8mb4;

-- 1. Tai khoan dung chung cho khach hang, nhan vien va quan tri vien.
-- Cot password luu ket qua password_hash() cua PHP.
CREATE TABLE tai_khoan (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    ho_ten VARCHAR(100) NOT NULL,
    so_dien_thoai VARCHAR(15) NOT NULL,
    dia_chi VARCHAR(255) NULL,
    cccd VARCHAR(20) NULL,
    chuc_vu VARCHAR(50) NULL,
    quyen VARCHAR(20) NOT NULL DEFAULT 'KHACH_HANG',
    trang_thai VARCHAR(20) NOT NULL DEFAULT 'HOAT_DONG',
    PRIMARY KEY (id),
    UNIQUE KEY uq_tai_khoan_username (username),
    UNIQUE KEY uq_tai_khoan_email (email),
    CONSTRAINT ck_tai_khoan_quyen
        CHECK (quyen IN ('KHACH_HANG', 'NHAN_VIEN', 'ADMIN')),
    CONSTRAINT ck_tai_khoan_trang_thai
        CHECK (trang_thai IN ('HOAT_DONG', 'BI_KHOA'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Hang xe, vi du TOYOTA, FORD.
CREATE TABLE hang_xe (
    ma_hang VARCHAR(50) NOT NULL,
    ten_hang VARCHAR(100) NOT NULL,
    quoc_gia VARCHAR(50) NULL,
    logo VARCHAR(255) NULL,
    PRIMARY KEY (ma_hang),
    UNIQUE KEY uq_hang_xe_ten (ten_hang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Dong xe, vi du CAMRY, EVEREST.
-- INT(2) trong bao cao van la INT; so 2 khong gioi han so chu so.
CREATE TABLE dong_xe (
    ma_dong_xe VARCHAR(50) NOT NULL,
    ten_dong_xe VARCHAR(100) NOT NULL,
    ma_hang VARCHAR(50) NOT NULL,
    phan_khuc VARCHAR(50) NOT NULL,
    so_cho_ngoi INT NOT NULL,
    gia_niem_yet DECIMAL(15,2) NOT NULL,
    hien_thi BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (ma_dong_xe),
    CONSTRAINT fk_dong_xe_hang
        FOREIGN KEY (ma_hang) REFERENCES hang_xe (ma_hang)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_dong_xe_so_cho CHECK (so_cho_ngoi > 0),
    CONSTRAINT ck_dong_xe_gia CHECK (gia_niem_yet >= 0),
    CONSTRAINT ck_dong_xe_hien_thi CHECK (hien_thi IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tung chiec xe trong kho, phan biet bang so khung VIN.
CREATE TABLE xe (
    so_khung_vin VARCHAR(50) NOT NULL,
    ma_dong_xe VARCHAR(50) NOT NULL,
    so_may VARCHAR(50) NULL,
    mau_sac VARCHAR(50) NOT NULL,
    nam_san_xuat INT NOT NULL,
    hinh_anh VARCHAR(255) NULL,
    trang_thai VARCHAR(50) NOT NULL DEFAULT 'SAN_SANG_BAN',
    PRIMARY KEY (so_khung_vin),
    CONSTRAINT fk_xe_dong
        FOREIGN KEY (ma_dong_xe) REFERENCES dong_xe (ma_dong_xe)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_xe_trang_thai
        CHECK (trang_thai IN ('SAN_SANG_BAN', 'DANG_GIU', 'DA_DAT_COC', 'DA_BAN_GIAO'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Giu chung don dat coc va hop dong trong mot bang.
-- Khong UNIQUE so_khung_vin tren toan bo lich su hop dong.
-- Buoc PHP dat coc se dung transaction va khoa de ngan giu trung VIN.
CREATE TABLE hop_dong (
    so_hop_dong VARCHAR(50) NOT NULL,
    tai_khoan_id INT NOT NULL,
    nhan_vien_id INT NULL,
    so_khung_vin VARCHAR(50) NOT NULL,
    ngay_lap DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    han_giu_vin DATETIME NULL,
    tien_dat_coc DECIMAL(15,2) NOT NULL,
    tong_gia_tri DECIMAL(15,2) NOT NULL,
    trang_thai_hd VARCHAR(20) NOT NULL DEFAULT 'CHO_COC',
    tien_do_giao_xe VARCHAR(50) NULL,
    ngay_ban_giao DATETIME NULL,
    PRIMARY KEY (so_hop_dong),
    CONSTRAINT fk_hop_dong_khach_hang
        FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_hop_dong_nhan_vien
        FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_hop_dong_xe
        FOREIGN KEY (so_khung_vin) REFERENCES xe (so_khung_vin)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_hop_dong_tong_gia_tri CHECK (tong_gia_tri > 0),
    CONSTRAINT ck_hop_dong_tien_coc CHECK (tien_dat_coc >= 0 AND tien_dat_coc <= tong_gia_tri),
    CONSTRAINT ck_hop_dong_trang_thai
        CHECK (trang_thai_hd IN ('CHO_COC', 'DA_DAT_COC', 'CHO_XU_LY', 'DA_THANH_TOAN', 'HOAN_TAT', 'DA_HUY', 'HET_HAN')),
    CONSTRAINT ck_hop_dong_han_giu CHECK (trang_thai_hd <> 'CHO_COC' OR han_giu_vin IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Cac khoan thu/hoan tien va bien lai noi bo.
-- ngay_thu dung DATETIME NULL: giao dich cho xac nhan chua phai tien da thu.
-- Khi SUCCESS, ngay_thu va ma_tham_chieu bat buoc co gia tri.
-- Khac dong TIMESTAMP NOT NULL con ghi trong bang mo ta 3.1.6.
CREATE TABLE hoa_don (
    ma_hoa_don VARCHAR(50) NOT NULL,
    so_hop_dong VARCHAR(50) NOT NULL,
    ngay_thu DATETIME NULL,
    so_tien_thu DECIMAL(15,2) NOT NULL,
    noi_dung_thu VARCHAR(255) NULL,
    phuong_thuc_tt VARCHAR(20) NOT NULL,
    trang_thai VARCHAR(20) NOT NULL DEFAULT 'CHO_XAC_NHAN',
    nguon_tham_chieu VARCHAR(100) NOT NULL,
    ma_tham_chieu VARCHAR(100) NULL,
    ma_phien_tt VARCHAR(100) NULL,
    nguoi_ghi_nhan_id INT NULL,
    loai_giao_dich VARCHAR(10) NOT NULL DEFAULT 'THU',
    giao_dich_goc VARCHAR(50) NULL,
    PRIMARY KEY (ma_hoa_don),
    UNIQUE KEY uq_hoa_don_nguon_ma (nguon_tham_chieu, ma_tham_chieu),
    CONSTRAINT fk_hoa_don_hop_dong
        FOREIGN KEY (so_hop_dong) REFERENCES hop_dong (so_hop_dong)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_hoa_don_nguoi_ghi_nhan
        FOREIGN KEY (nguoi_ghi_nhan_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_hoa_don_giao_dich_goc
        FOREIGN KEY (giao_dich_goc) REFERENCES hoa_don (ma_hoa_don)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_hoa_don_so_tien CHECK (so_tien_thu > 0),
    CONSTRAINT ck_hoa_don_phuong_thuc
        CHECK (phuong_thuc_tt IN ('TIEN_MAT', 'CHUYEN_KHOAN', 'CONG_THANH_TOAN')),
    CONSTRAINT ck_hoa_don_trang_thai
        CHECK (trang_thai IN ('CHO_XAC_NHAN', 'SUCCESS', 'FAILED', 'CANCELLED')),
    CONSTRAINT ck_hoa_don_nguon CHECK (CHAR_LENGTH(TRIM(nguon_tham_chieu)) > 0),
    CONSTRAINT ck_hoa_don_thanh_cong CHECK (
        trang_thai <> 'SUCCESS'
        OR (ngay_thu IS NOT NULL AND ma_tham_chieu IS NOT NULL AND CHAR_LENGTH(TRIM(ma_tham_chieu)) > 0)
    ),
    CONSTRAINT ck_hoa_don_loai CHECK (loai_giao_dich IN ('THU', 'HOAN_TIEN')),
    CONSTRAINT ck_hoa_don_hoan_tien CHECK (
        (loai_giao_dich = 'THU' AND giao_dich_goc IS NULL)
        OR (loai_giao_dich = 'HOAN_TIEN' AND giao_dich_goc IS NOT NULL AND giao_dich_goc <> ma_hoa_don)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Phien tu van cua khach hang voi nhan vien.
CREATE TABLE hoi_thoai_chat (
    ma_hoi_thoai INT NOT NULL AUTO_INCREMENT,
    khach_hang_id INT NOT NULL,
    nhan_vien_id INT NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(20) NOT NULL DEFAULT 'CHO_TIEP_NHAN',
    PRIMARY KEY (ma_hoi_thoai),
    CONSTRAINT fk_hoi_thoai_khach_hang
        FOREIGN KEY (khach_hang_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_hoi_thoai_nhan_vien
        FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_hoi_thoai_trang_thai
        CHECK (trang_thai IN ('CHO_TIEP_NHAN', 'DANG_TU_VAN', 'DA_KET_THUC'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Moi hop dong toi da mot danh gia.
-- PHP can kiem tra dung chu hop dong va da ban giao xe truoc khi luu.
CREATE TABLE danh_gia (
    ma_danh_gia INT NOT NULL AUTO_INCREMENT,
    so_hop_dong VARCHAR(50) NOT NULL,
    tai_khoan_id INT NOT NULL,
    so_sao TINYINT NOT NULL,
    nhan_xet TEXT NULL,
    ngay_danh_gia DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ma_danh_gia),
    UNIQUE KEY uq_danh_gia_hop_dong (so_hop_dong),
    CONSTRAINT fk_danh_gia_hop_dong
        FOREIGN KEY (so_hop_dong) REFERENCES hop_dong (so_hop_dong)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_danh_gia_tai_khoan
        FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_danh_gia_so_sao CHECK (so_sao BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Nhieu tin nhan thuoc mot hoi thoai.
-- PHP kiem tra nguoi gui thuoc hoi thoai va noi_dung khong rong sau trim().
CREATE TABLE tin_nhan (
    ma_tin_nhan INT NOT NULL AUTO_INCREMENT,
    ma_hoi_thoai INT NOT NULL,
    nguoi_gui_id INT NOT NULL,
    noi_dung TEXT NOT NULL,
    thoi_gian_gui DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ma_tin_nhan),
    KEY idx_tin_nhan_lich_su (ma_hoi_thoai, thoi_gian_gui, ma_tin_nhan),
    CONSTRAINT fk_tin_nhan_hoi_thoai
        FOREIGN KEY (ma_hoi_thoai) REFERENCES hoi_thoai_chat (ma_hoi_thoai)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_tin_nhan_nguoi_gui
        FOREIGN KEY (nguoi_gui_id) REFERENCES tai_khoan (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
