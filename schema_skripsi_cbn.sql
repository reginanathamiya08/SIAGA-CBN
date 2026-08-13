CREATE TABLE roles (
    id VARCHAR(20) PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    id VARCHAR(20) PRIMARY KEY,
    role_id VARCHAR(20) NOT NULL,
    nip VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    divisi VARCHAR(50),
    jabatan VARCHAR(100),
    pendidikan VARCHAR(20),
    tanggal_masuk DATE,
    no_hp VARCHAR(20),
    gaji_atas_umr INT DEFAULT 0,
    is_shift INT DEFAULT 0,
    uang_makan_by_mitra INT DEFAULT 0,
    is_active INT DEFAULT 1,
    remember_token VARCHAR(100),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE mitra (
    id VARCHAR(20) PRIMARY KEY,
    nama_mitra VARCHAR(150) NOT NULL,
    alamat TEXT,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),
    radius_meter INT DEFAULT 200,
    ip_public VARCHAR(45),
    jam_masuk TIME,
    jam_pulang TIME,
    is_pusat INT DEFAULT 0,
    is_cabang INT DEFAULT 0,
    mitra_induk_id VARCHAR(20),
    FOREIGN KEY (mitra_induk_id) REFERENCES mitra(id)
);

CREATE TABLE shifts (
    id VARCHAR(20) PRIMARY KEY,
    mitra_id VARCHAR(20),
    nama_shift VARCHAR(50) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    FOREIGN KEY (mitra_id) REFERENCES mitra(id)
);

CREATE TABLE detail_riwayat_penempatan (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    mitra_id VARCHAR(20) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE,
    status VARCHAR(20) DEFAULT 'aktif',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (mitra_id) REFERENCES mitra(id)
);

CREATE TABLE absensi (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    mitra_id VARCHAR(20),
    shift_id VARCHAR(20),
    tanggal DATE NOT NULL,
    waktu_masuk DATETIME,
    waktu_pulang DATETIME,
    lat_masuk DECIMAL(10, 7),
    long_masuk DECIMAL(10, 7),
    ip_masuk VARCHAR(45),
    lat_pulang DECIMAL(10, 7),
    long_pulang DECIMAL(10, 7),
    ip_pulang VARCHAR(45),
    status VARCHAR(20) DEFAULT 'hadir',
    is_telat INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (mitra_id) REFERENCES mitra(id),
    FOREIGN KEY (shift_id) REFERENCES shifts(id)
);

CREATE TABLE jenis_perizinan (
    id VARCHAR(20) PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    nama_jenis VARCHAR(100) NOT NULL,
    memotong_kuota INT DEFAULT 0,
    memotong_uang_makan INT DEFAULT 0,
    wajib_upload_bukti INT DEFAULT 0
);

CREATE TABLE kuota_perizinan (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    tahun INT NOT NULL,
    kuota_total INT DEFAULT 12,
    terpakai INT DEFAULT 0,
    sisa INT DEFAULT 12,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE detail_perizinan (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    jenis_perizinan_id VARCHAR(20) NOT NULL,
    kuota_perizinan_id VARCHAR(20),
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    alasan TEXT NOT NULL,
    file_bukti VARCHAR(255),
    status_approval VARCHAR(20) DEFAULT 'menunggu',
    approved_by VARCHAR(20),
    catatan_pimpinan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (jenis_perizinan_id) REFERENCES jenis_perizinan(id),
    FOREIGN KEY (kuota_perizinan_id) REFERENCES kuota_perizinan(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE lembur (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    alasan TEXT NOT NULL,
    status_approval VARCHAR(20) DEFAULT 'menunggu',
    approved_by VARCHAR(20),
    catatan_pimpinan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE master_komponen_gaji (
    id VARCHAR(20) PRIMARY KEY,
    nama_komponen VARCHAR(100) NOT NULL,
    tipe VARCHAR(20) NOT NULL
);

CREATE TABLE periode_gaji (
    id VARCHAR(20) PRIMARY KEY,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    status VARCHAR(20) DEFAULT 'draft'
);

CREATE TABLE slip_gaji_periode (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    periode_id VARCHAR(20) NOT NULL,
    gaji_pokok DECIMAL(12, 2) DEFAULT 0,
    tunjangan DECIMAL(12, 2) DEFAULT 0,
    potongan DECIMAL(12, 2) DEFAULT 0,
    gaji_bersih DECIMAL(12, 2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'draft',
    alasan_tolak TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (periode_id) REFERENCES periode_gaji(id)
);

CREATE TABLE detail_gaji_komponen (
    id VARCHAR(20) PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    slip_gaji_periode_id VARCHAR(20),
    komponen_gaji_id VARCHAR(20) NOT NULL,
    nominal DECIMAL(12, 2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (slip_gaji_periode_id) REFERENCES slip_gaji_periode(id),
    FOREIGN KEY (komponen_gaji_id) REFERENCES master_komponen_gaji(id)
);

CREATE TABLE dokumen_user (
    id INT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    jenis_dokumen VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
