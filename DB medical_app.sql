-- ---------------------------------------------
-- ✅ SETUP AWAL
-- ---------------------------------------------
CREATE DATABASE IF NOT EXISTS medical_app;
USE medical_app;

-- ---------------------------------------------
-- 📍 JENIS INSTITUSI
-- ---------------------------------------------
CREATE TABLE jenis_institusi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_jenis VARCHAR(50) NOT NULL UNIQUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 INSTITUSI
-- ---------------------------------------------
CREATE TABLE institusi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_institusi VARCHAR(100) NOT NULL,
  sub_institusi VARCHAR(100),
  jenis_id INT NOT NULL,
  alamat TEXT,
  telepon VARCHAR(50),
  email VARCHAR(100),
  logo VARCHAR(255),
  status ENUM('Aktif','Nonaktif') DEFAULT 'Aktif',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (jenis_id) REFERENCES jenis_institusi(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ---------------------------------------------
-- 📍 JENIS BAYAR
-- ---------------------------------------------
CREATE TABLE jenis_bayar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_jenis VARCHAR(50) NOT NULL UNIQUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 KELAS KAMAR
-- ---------------------------------------------
CREATE TABLE kelas_kamar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kelas VARCHAR(100) NOT NULL,
  jenis_pasien ENUM('Dewasa', 'Anak') NOT NULL,
  tarif DECIMAL(12,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 AMBULANCE
-- ---------------------------------------------
CREATE TABLE ambulance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipe ENUM('Internal','Eksternal') NOT NULL,
  asal_ambulance VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 LABORATORIUM
-- ---------------------------------------------
CREATE TABLE laboratorium (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipe ENUM('Internal','Eksternal') NOT NULL,
  asal_laboratorium VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 JABATAN
-- ---------------------------------------------
CREATE TABLE jabatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_jabatan VARCHAR(50) NOT NULL,
  keterangan VARCHAR(100),
  role ENUM('User', 'Admin') DEFAULT 'User',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 TENAGA MEDIS
-- ---------------------------------------------
CREATE TABLE dokter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_dokter VARCHAR(100) NOT NULL,
  spesialisasi VARCHAR(100),
  status ENUM('Aktif','Nonaktif') DEFAULT 'Aktif',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE asisten_dokter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_asisten VARCHAR(100) NOT NULL,
  keterangan VARCHAR(100),
  status ENUM('Aktif','Nonaktif') DEFAULT 'Aktif',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 PASIEN
-- ---------------------------------------------
CREATE TABLE pasien (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_register VARCHAR(50) NOT NULL UNIQUE,
  nama_pasien VARCHAR(100) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 USER PETUGAS
-- ---------------------------------------------
CREATE TABLE user (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('User','Admin','Superadmin') DEFAULT 'User',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- 📍 KASIR (Akses Petugas)
-- ---------------------------------------------
CREATE TABLE kasir (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  nama_petugas VARCHAR(100) NOT NULL,
  nip VARCHAR(50) NOT NULL UNIQUE,
  jabatan_id INT,
  status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  role ENUM('User', 'Admin') DEFAULT 'User',
  qr_filename VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (jabatan_id) REFERENCES jabatan(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- ---------------------------------------------
-- 📍 DATA AWAL
-- ---------------------------------------------
INSERT INTO jenis_institusi (nama_jenis) VALUES
('PMI'), ('Rumah Sakit'), ('Klinik'), ('Laboratorium'), ('Apotek');

INSERT INTO jenis_bayar (nama_jenis) VALUES ('BPJS'), ('UMUM');

INSERT INTO jabatan (nama_jabatan, keterangan, role) VALUES
('Ka.Bid', 'Kepala Bidang', 'User'),
('Ka.Ru', 'Kepala Ruang', 'User'),
('Ka.Unit', 'Kepala Unit', 'User'),
('Koord.', 'Koordinator', 'User'),
('Kary.', 'Karyawan', 'User');

INSERT INTO user (username, password, role) VALUES
('admin', '$2y$10$llF9QwPAzNCJbfZtAuTl6OtHVCu4MOhNxOTr/M.m9oSZdOjHWe/8u', 'Superadmin');

-- ---------------------------------------------
-- 📍 TIKET RAWAT INAP
-- ---------------------------------------------
CREATE TABLE tiket_rawat_inap (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pasien_id INT NOT NULL,
  tanggal_masuk DATE NOT NULL DEFAULT CURRENT_DATE,
  tanggal_keluar DATE DEFAULT NULL,
  status ENUM('AKTIF', 'SELESAI', 'BATAL') NOT NULL DEFAULT 'AKTIF',
  catatan TEXT,
  dibuat_oleh VARCHAR(50),
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ---------------------------------------------
-- 📍 UANG MUKA RAWAT INAP
-- ---------------------------------------------
CREATE TABLE uang_muka_rawat_inap (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rawat_inap_id INT NOT NULL,
  dibuat_oleh_id INT NOT NULL,
  dicetak_oleh_id INT,
  tanggal DATE NOT NULL,
  jumlah DECIMAL(12,2) NOT NULL,
  metode_pembayaran VARCHAR(50),
  keterangan TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (rawat_inap_id) REFERENCES tiket_rawat_inap(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ---------------------------------------------
-- 📍 POTONGAN RAWAT INAP
-- ---------------------------------------------
CREATE TABLE potongan_rawat_inap (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rawat_inap_id INT NOT NULL,
  dibuat_oleh_id INT NOT NULL,
  dicetak_oleh_id INT,
  tanggal DATE NOT NULL,
  jumlah DECIMAL(12,2) NOT NULL,
  tipe VARCHAR(50),
  keterangan TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (rawat_inap_id),
  FOREIGN KEY (rawat_inap_id) REFERENCES tiket_rawat_inap(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ---------------------------------------------
-- 📍 RINCIAN RAWAT INAP
-- ---------------------------------------------
CREATE TABLE rawat_inap_rincian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rawat_inap_id INT NOT NULL,

  -- 🔹 Metadata Pasien & Tiket (denormalisasi)
  nama_pasien VARCHAR(100),
  nomor_register VARCHAR(50),
  jenis_bayar_id INT DEFAULT NULL,
  nama_jenis_bayar VARCHAR(50),
  kelas_kamar_id INT DEFAULT NULL,
  nama_kelas_perawatan VARCHAR(50),

  -- 🔹 Jenis Rincian (Gabung / Sendiri)
  jenis_rincian ENUM('Gabung','Sendiri') DEFAULT 'Sendiri',

-- ---------------------------------------------
-- 📍 BAGIAN 1
-- ---------------------------------------------
  -- 🔹 Akomodasi
  ibu_mulai DATE,
  ibu_selesai DATE,
  ibu_hari INT DEFAULT 0,
  ibu_tarif DECIMAL(12,2) DEFAULT 0.00,
  ibu_total DECIMAL(12,2) DEFAULT 0.00,

  -- Perawatan Bayi (aktif jika jenis_rincian = 'Gabung')
  bayi_mulai DATE,
  bayi_selesai DATE,
  bayi_hari INT DEFAULT 0,
  bayi_tarif DECIMAL(12,2) DEFAULT 0.00,
  bayi_total DECIMAL(12,2) DEFAULT 0.00,

  anak_mulai DATE,
  anak_selesai DATE,
  anak_hari INT DEFAULT 0,
  anak_tarif DECIMAL(12,2) DEFAULT 0.00,
  anak_total DECIMAL(12,2) DEFAULT 0.00,

  total_kamar_perawatan DECIMAL(12,2) DEFAULT 0.00,
  total_bersalin DECIMAL(12,2) DEFAULT 0.00,

  -- 🔹 Ambulance
  ambulance_1_id INT DEFAULT 0,
  jumlah_ambulance_1 INT DEFAULT 0,
  ambulance_2_id INT DEFAULT 0,
  jumlah_ambulance_2 INT DEFAULT 0,
  total_ambulance DECIMAL(12,2) DEFAULT 0.00,

  -- 🔹 Jasa RS & Karcis & Materai
  total_jasa_rs DECIMAL(12,2) DEFAULT 0.00,
  total_karcis DECIMAL(12,2) DEFAULT 0.00,
  total_materai DECIMAL(12,2) DEFAULT 0.00,

  total_kamar_akomodasi DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 2
-- ---------------------------------------------
  -- 🔹 Jasa Tindakan Keperawatan
  jasa_tindakan DECIMAL(12,2) DEFAULT 0.00,
  total_jasa_tindakan DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 3
-- ---------------------------------------------
  -- 🔹 Tenaga Medis
  dokter_visite_1_id INT DEFAULT 0, 
  lama_visite_1 INT DEFAULT 0, 
  biaya_visite_1 DECIMAL(12,2) DEFAULT 0.00, 
  total_visite_1 DECIMAL(12,2) DEFAULT 0.00,
  dokter_visite_2_id INT DEFAULT 0, 
  lama_visite_2 INT DEFAULT 0, 
  biaya_visite_2 DECIMAL(12,2) DEFAULT 0.00, 
  total_visite_2 DECIMAL(12,2) DEFAULT 0.00,
  dokter_visite_3_id INT DEFAULT 0, 
  lama_visite_3 INT DEFAULT 0, 
  biaya_visite_3 DECIMAL(12,2) DEFAULT 0.00, 
  total_visite_3 DECIMAL(12,2) DEFAULT 0.00,
  total_visite_dokter DECIMAL(12,2) DEFAULT 0.00,

  dokter_tindakan_1_id INT DEFAULT 0, 
  biaya_tindakan_dokter_1 DECIMAL(12,2) DEFAULT 0.00,
  dokter_tindakan_2_id INT DEFAULT 0, 
  biaya_tindakan_dokter_2 DECIMAL(12,2) DEFAULT 0.00,
  dokter_tindakan_3_id INT DEFAULT 0, 
  biaya_tindakan_dokter_3 DECIMAL(12,2) DEFAULT 0.00,

  asisten_tindakan_1_id INT DEFAULT 0, 
  biaya_asisten_1 DECIMAL(12,2) DEFAULT 0.00,
  asisten_tindakan_2_id INT DEFAULT 0, 
  biaya_asisten_2 DECIMAL(12,2) DEFAULT 0.00,
  asisten_tindakan_3_id INT DEFAULT 0, 
  biaya_asisten_3 DECIMAL(12,2) DEFAULT 0.00,

  instrumen_onlop_id INT DEFAULT 0, 
  biaya_instrumen_onlop DECIMAL(12,2) DEFAULT 0.00,
  total_tindakan_medis DECIMAL(12,2) DEFAULT 0.00,
  total_tenaga_ahli DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 4
-- ---------------------------------------------
  -- 🔹 Konsultasi
  dokter_konsultasi_1_id INT DEFAULT 0, 
  jumlah_konsul_1 INT DEFAULT 0, 
  biaya_konsul_1 DECIMAL(12,2) DEFAULT 0.00,
  dokter_konsultasi_2_id INT DEFAULT 0, 
  jumlah_konsul_2 INT DEFAULT 0, 
  biaya_konsul_2 DECIMAL(12,2) DEFAULT 0.00,
  dokter_konsultasi_3_id INT DEFAULT 0, 
  jumlah_konsul_3 INT DEFAULT 0, 
  biaya_konsul_3 DECIMAL(12,2) DEFAULT 0.00,
  total_konsultasi DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 5
-- ---------------------------------------------
  -- 🔹 Pemeriksaan Laboratorium
  laboratorium_1_id INT DEFAULT 0, 
  jumlah_lab_1 INT DEFAULT 0,
  laboratorium_2_id INT DEFAULT 0, 
  jumlah_lab_2 INT DEFAULT 0,
  total_laboratorium DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 6
-- ---------------------------------------------
  -- 🔹 Pemeriksaan Radiologi (Total tersendiri)
  usg_kali INT DEFAULT 0, 
  jumlah_usg DECIMAL(12,2) DEFAULT 0.00,
  radiologi_tambahan_1 VARCHAR(100), 
  jumlah_radiologi_tambahan_1 INT DEFAULT 0,
  radiologi_tambahan_2 VARCHAR(100), 
  jumlah_radiologi_tambahan_2 INT DEFAULT 0,
  total_radiologi DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 7
-- ---------------------------------------------
  -- 🔹 Tindakan
  phototerapi_seri INT DEFAULT 0, 
  phototerapi_biaya DECIMAL(12,2) DEFAULT 0.00,
  biaya_suction DECIMAL(12,2) DEFAULT 0.00,
  biaya_syringe DECIMAL(12,2) DEFAULT 0.00,
  incubator_kali INT DEFAULT 0, 
  incubator_biaya DECIMAL(12,2) DEFAULT 0.00,
  nebulizer_kali INT DEFAULT 0, 
  nebulizer_biaya DECIMAL(12,2) DEFAULT 0.00,
  nama_tindakan_1 VARCHAR(100) DEFAULT NULL,
  tindakan_lain_1 DECIMAL(12,2) DEFAULT 0.00,
  nama_tindakan_2 VARCHAR(100) DEFAULT NULL,
  tindakan_lain_2 DECIMAL(12,2) DEFAULT 0.00,
  total_tindakan DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 8
-- ---------------------------------------------
  -- 🔹 Penunjang
  nst_kali INT DEFAULT 0, 
  nst_biaya DECIMAL(12,2) DEFAULT 0.00,
  ecg_biaya DECIMAL(12,2) DEFAULT 0.00,
  total_penunjang DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 9
-- ---------------------------------------------
  -- 🔹 Transfusi Darah
  jumlah_transfusi INT DEFAULT 0, 
  total_transfusi_darah DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 10
-- ---------------------------------------------
  -- 🔹 Prosedur Non Bedah
  biaya_persalinan DECIMAL(12,2) DEFAULT 0.00,
  total_prosedur_non_bedah DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 11
-- ---------------------------------------------
  -- 🔹 Obat-obatan
  biaya_obat DECIMAL(12,2) DEFAULT 0.00,
  total_obat DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 12
-- ---------------------------------------------
  -- 🔹 Prosedur Bedah
  biaya_kamar_operasi DECIMAL(12,2) DEFAULT 0.00,
  total_prosedur_bedah DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 13
-- ---------------------------------------------
  -- 🔹 Alkes
  biaya_alkes DECIMAL(12,2) DEFAULT 0.00,
  nama_alkes_1 VARCHAR(100) DEFAULT NULL,
  alkes_tambahan_1 DECIMAL(12,2) DEFAULT 0.00,
  nama_alkes_2 VARCHAR(100) DEFAULT NULL,
  alkes_tambahan_2 DECIMAL(12,2) DEFAULT 0.00,
  nama_alkes_3 VARCHAR(100) DEFAULT NULL,
  alkes_tambahan_3 DECIMAL(12,2) DEFAULT 0.00,
  total_alkes DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 14
-- ---------------------------------------------
  -- 🔹 Rehabilitasi
  biaya_rehabilitasi DECIMAL(12,2) DEFAULT 0.00,
  total_rehabilitasi DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 15
-- ---------------------------------------------
  -- 🔹 Rawat Intensif
  biaya_rawat_intensif DECIMAL(12,2) DEFAULT 0.00,
  total_rawat_intensif DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 16
-- ---------------------------------------------
  -- 🔹 BMHP
  biaya_bmhp DECIMAL(12,2) DEFAULT 0.00,
  total_bmhp DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 17
-- ---------------------------------------------
  -- 🔹 Rekap Biaya
  total_semua_bagian DECIMAL(12,2) DEFAULT 0.00,
  uang_muka DECIMAL(12,2) DEFAULT 0.00,
  potongan DECIMAL(12,2) DEFAULT 0.00,
  sisa_tagihan DECIMAL(12,2) DEFAULT 0.00,

-- ---------------------------------------------
-- 📍 BAGIAN 18
-- ---------------------------------------------
  -- 🔹 Metadata
  catatan TEXT,
  tanggal_pemeriksaan DATE DEFAULT curdate(),
  qr_validasi_path VARCHAR(255),
  dibuat_oleh INT,
  nip VARCHAR(30),

  -- 🔹 Status Finalisasi
  status VARCHAR(20) DEFAULT 'AKTIF',
  tanggal_selesai DATETIME DEFAULT NULL,

  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (rawat_inap_id) REFERENCES tiket_rawat_inap(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (jenis_bayar_id) REFERENCES jenis_bayar(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- ---------------------------------------------
-- 📍 SHIFT
-- ---------------------------------------------
CREATE TABLE shift (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_shift VARCHAR(20) NOT NULL,         -- Pagi / Siang / Malam
  jam_mulai TIME NOT NULL,                 -- 07:00 / 14:00 / 21:00
  jam_selesai TIME NOT NULL                -- 14:00 / 21:00 / 07:00
);

INSERT INTO shift (nama_shift, jam_mulai, jam_selesai) VALUES
('Pagi',  '07:00:00', '14:00:00'),
('Siang', '14:00:00', '21:00:00'),
('Malam', '21:00:00', '07:00:00');

-- ---------------------------------------------
-- 📍 JURNAL
-- ---------------------------------------------
CREATE TABLE jurnal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  shift_id INT NOT NULL,                      -- Relasi ke shift
  kasir_id INT(10) UNSIGNED NOT NULL,         -- Relasi ke kasir.user_id
  keterangan TEXT NOT NULL COLLATE utf8mb4_general_ci,
  debet DECIMAL(15,2) DEFAULT 0.00,
  kredit DECIMAL(15,2) DEFAULT 0.00,

  INDEX (shift_id),
  INDEX (kasir_id),

  CONSTRAINT fk_jurnal_shift FOREIGN KEY (shift_id)
    REFERENCES shift(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,

  CONSTRAINT fk_jurnal_kasir_user FOREIGN KEY (kasir_id)
    REFERENCES kasir(user_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;