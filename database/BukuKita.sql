/* ===== 1. DDL ===== */

DROP DATABASE IF EXISTS BukuKita;
CREATE DATABASE BukuKita;
USE BukuKita;
CREATE TABLE satuan (
  idsatuan INT NOT NULL AUTO_INCREMENT,
  nama_satuan VARCHAR(45) NULL,
  status TINYINT NULL,
  PRIMARY KEY (idsatuan)
);
CREATE TABLE vendor (
  idvendor INT NOT NULL AUTO_INCREMENT,
  nama_vendor VARCHAR(100) NULL,
  badan_hukum CHAR(1) NULL,
  status CHAR(1) NULL,
  PRIMARY KEY (idvendor)
);
CREATE TABLE role (
  idrole INT NOT NULL AUTO_INCREMENT,
  nama_role VARCHAR(100) NULL,
  PRIMARY KEY (idrole)
);
CREATE TABLE user (
  iduser INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(45) NULL,
  password VARCHAR(100) NULL,
  idrole INT NOT NULL,
  PRIMARY KEY (iduser),
  CONSTRAINT fk_user_role FOREIGN KEY (idrole) REFERENCES role (idrole)
);
CREATE TABLE margin_penjualan (
  idmargin_penjualan INT NOT NULL AUTO_INCREMENT,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  persen DOUBLE NULL,
  status TINYINT NULL,
  iduser INT NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (idmargin_penjualan),
  CONSTRAINT fk_margin_penjualan_user FOREIGN KEY (iduser) REFERENCES user (iduser)
);
CREATE TABLE barang (
  idbarang INT NOT NULL AUTO_INCREMENT,
  jenis CHAR(1) NULL,
  nama VARCHAR(45) NULL,
  idsatuan INT NOT NULL,
  idvendor INT NOT NULL,
  status TINYINT NULL,
  harga INT NULL,
  PRIMARY KEY (idbarang),
  CONSTRAINT fk_barang_satuan FOREIGN KEY (idsatuan) REFERENCES satuan (idsatuan),
  CONSTRAINT fk_barang_vendor FOREIGN KEY (idvendor) REFERENCES vendor (idvendor)
);
CREATE TABLE pengadaan (
  idpengadaan BIGINT NOT NULL AUTO_INCREMENT,
  timestamp TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  user_iduser INT NOT NULL,
  status CHAR(1) NULL,
  vendor_idvendor INT NOT NULL,
  subtotal_nilai INT NULL,
  ppn INT NULL,
  total_nilai INT NULL,
  PRIMARY KEY (idpengadaan),
  CONSTRAINT fk_pengadaan_user FOREIGN KEY (user_iduser) REFERENCES user (iduser),
  CONSTRAINT fk_pengadaan_vendor FOREIGN KEY (vendor_idvendor) REFERENCES vendor (idvendor)
);
CREATE TABLE detail_pengadaan (
  iddetail_pengadaan BIGINT NOT NULL AUTO_INCREMENT,
  harga_satuan INT NULL,
  jumlah INT NULL,
  sub_total INT NULL,
  idbarang INT NOT NULL,
  idpengadaan BIGINT NOT NULL,
  PRIMARY KEY (iddetail_pengadaan),
  CONSTRAINT fk_detail_pengadaan_barang FOREIGN KEY (idbarang) REFERENCES barang (idbarang),
  CONSTRAINT fk_detail_pengadaan_pengadaan FOREIGN KEY (idpengadaan) REFERENCES pengadaan (idpengadaan)
);
CREATE TABLE penerimaan (
  idpenerimaan BIGINT NOT NULL AUTO_INCREMENT,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  status CHAR(1) NULL,
  idpengadaan BIGINT NOT NULL,
  iduser INT NOT NULL,
  PRIMARY KEY (idpenerimaan),
  CONSTRAINT fk_penerimaan_pengadaan FOREIGN KEY (idpengadaan) REFERENCES pengadaan (idpengadaan),
  CONSTRAINT fk_penerimaan_user FOREIGN KEY (iduser) REFERENCES user (iduser)
);
CREATE TABLE detail_penerimaan (
  iddetail_penerimaan BIGINT NOT NULL AUTO_INCREMENT,
  idpenerimaan BIGINT NOT NULL,
  barang_idbarang INT NOT NULL,
  jumlah_terima INT NULL,
  harga_satuan_terima INT NULL,
  sub_total_terima INT NULL,
  PRIMARY KEY (iddetail_penerimaan),
  CONSTRAINT fk_detail_penerimaan_penerimaan FOREIGN KEY (idpenerimaan) REFERENCES penerimaan (idpenerimaan),
  CONSTRAINT fk_detail_penerimaan_barang FOREIGN KEY (barang_idbarang) REFERENCES barang (idbarang)
);
CREATE TABLE retur (
  idretur BIGINT NOT NULL AUTO_INCREMENT,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  idpenerimaan BIGINT NOT NULL,
  iduser INT NOT NULL,
  PRIMARY KEY (idretur),
  CONSTRAINT fk_retur_penerimaan FOREIGN KEY (idpenerimaan) REFERENCES penerimaan (idpenerimaan),
  CONSTRAINT fk_retur_user FOREIGN KEY (iduser) REFERENCES user (iduser)
);
CREATE TABLE detail_retur (
  iddetail_retur INT NOT NULL AUTO_INCREMENT,
  jumlah INT NULL,
  alasan VARCHAR(200) NULL,
  idretur BIGINT NOT NULL,
  iddetail_penerimaan BIGINT NOT NULL,
  PRIMARY KEY (iddetail_retur),
  CONSTRAINT fk_detail_retur_retur FOREIGN KEY (idretur) REFERENCES retur (idretur),
  CONSTRAINT fk_detail_retur_detail_penerimaan FOREIGN KEY (iddetail_penerimaan) REFERENCES detail_penerimaan (iddetail_penerimaan)
);
CREATE TABLE penjualan (
  idpenjualan INT NOT NULL AUTO_INCREMENT,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  subtotal_nilai INT NULL,
  ppn INT NULL,
  total_nilai INT NULL,
  iduser INT NOT NULL,
  idmargin_penjualan INT NOT NULL,
  PRIMARY KEY (idpenjualan),
  CONSTRAINT fk_penjualan_user FOREIGN KEY (iduser) REFERENCES user (iduser),
  CONSTRAINT fk_penjualan_margin FOREIGN KEY (idmargin_penjualan) REFERENCES margin_penjualan (idmargin_penjualan)
);
CREATE TABLE detail_penjualan (
  iddetail_penjualan BIGINT NOT NULL AUTO_INCREMENT,
  harga_satuan INT NULL,
  jumlah INT NULL,
  subtotal INT NULL,
  penjualan_idpenjualan INT NOT NULL,
  idbarang INT NOT NULL,
  PRIMARY KEY (iddetail_penjualan),
  CONSTRAINT fk_detail_penjualan_penjualan FOREIGN KEY (penjualan_idpenjualan) REFERENCES penjualan (idpenjualan),
  CONSTRAINT fk_detail_penjualan_barang FOREIGN KEY (idbarang) REFERENCES barang (idbarang)
);
CREATE TABLE kartu_stok (
  idkartu_stok BIGINT NOT NULL AUTO_INCREMENT,
  jenis_transaksi CHAR(1) NULL,
  masuk INT NULL,
  keluar INT NULL,
  stock INT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  idtransaksi INT NULL,
  idbarang INT NOT NULL,
  PRIMARY KEY (idkartu_stok),
  CONSTRAINT fk_kartu_stok_barang FOREIGN KEY (idbarang) REFERENCES barang (idbarang)
);

/* ===== 2. DML ===== */

INSERT INTO satuan (idsatuan, nama_satuan, status) VALUES
 (101, 'Buku', 1), (102, 'Paket', 1), (103, 'Eksemplar', 1), (104, 'Pcs', 1), (105, 'Lusin', 1);
INSERT INTO vendor (idvendor, nama_vendor, badan_hukum, status) VALUES
 (201, 'PT Pustaka Nusantara', 'Y', 'A'),
 (202, 'CV Buku Cerdas', 'Y', 'A'),
 (203, 'Toko Grosir Literasi', 'N', 'A');
INSERT INTO role (idrole, nama_role) VALUES (301, 'Admin'), (302, 'Kasir'), (303, 'Gudang');
INSERT INTO user (iduser, username, password, idrole) VALUES
 (401, 'admin_bukukita', 'admin01', 301),
 (402, 'kasir01', 'kasir01', 302),
 (403, 'gudang01', 'gudang01', 303);
INSERT INTO margin_penjualan (idmargin_penjualan, persen, status, iduser, updated_at) VALUES
 (501, 10, 0, 401, NULL),
 (502, 20, 1, 401, NULL),
 (503, 30, 0, 401, NULL),
 (504, 40, 0, 401, NULL),
 (505, 50, 0, 401, NULL);
INSERT INTO barang (idbarang, jenis, nama, idsatuan, idvendor, status, harga) VALUES
 (601, 'B', 'Laskar Pelangi',               101, 201, 1, 85000),
 (602, 'B', 'Bumi Manusia',                 101, 201, 1, 60000),
 (603, 'B', 'Matematika Dasar',             101, 201, 1, 45000),
 (604, 'B', 'Laut Bercerita',               101, 201, 1, 99000),
 (605, 'B', 'Algoritma dan Struktur Data',  101, 202, 1, 90000),
 (606, 'B', 'Pemrograman Web dengan PHP',   101, 202, 1, 75000),
 (607, 'B', 'Basis Data untuk Pemula',      101, 202, 1, 70000),
 (608, 'B', 'Atomic Habits',                101, 201, 1, 95000),
 (609, 'A', 'Pulpen Hitam',                 104, 203, 1, 3000),
 (610, 'A', 'Pensil 2B',                    104, 203, 1, 2500),
 (611, 'A', 'Buku Tulis 38 Lembar',         101, 203, 1, 4500),
 (612, 'A', 'Penghapus',                    104, 203, 1, 2000),
 (613, 'A', 'Penggaris 30 cm',              104, 203, 1, 3500),
 (614, 'A', 'Spidol Papan Tulis',           104, 202, 1, 8000),
 (615, 'A', 'Stabilo Warna',                102, 202, 1, 18000),
 (616, 'L', 'Pembatas Buku',                104, 203, 1, 5000),
 (617, 'L', 'Sampul Buku Plastik',          102, 203, 1, 12000),
 (618, 'L', 'Lampu Baca Klip',              104, 202, 1, 35000),
 (619, 'L', 'Rak Buku Mini',                103, 202, 1, 120000),
 (620, 'L', 'Tas Kain Bukukita',            104, 201, 1, 25000);

/* ===== 3. VIEW ===== */

CREATE VIEW view_satuan_semua AS
SELECT idsatuan, nama_satuan, IF(status = 1, 'Aktif', 'Nonaktif') AS status FROM satuan;
CREATE VIEW view_satuan_aktif AS
SELECT idsatuan, nama_satuan FROM satuan WHERE status = 1;
CREATE VIEW view_vendor_semua AS
SELECT idvendor, nama_vendor,
       IF(badan_hukum = 'Y', 'Badan Hukum', 'Non Badan Hukum') AS jenis_vendor,
       IF(status = 'A', 'Aktif', 'Nonaktif') AS status
FROM vendor;
CREATE VIEW view_vendor_aktif AS
SELECT idvendor, nama_vendor,
       IF(badan_hukum = 'Y', 'Badan Hukum', 'Non Badan Hukum') AS jenis_vendor
FROM vendor WHERE status = 'A';
CREATE VIEW view_barang_semua AS
SELECT b.idbarang, b.nama AS nama_barang,
       CASE b.jenis WHEN 'B' THEN 'Buku' WHEN 'A' THEN 'Alat Tulis' ELSE 'Lainnya' END AS kategori,
       s.nama_satuan, v.nama_vendor, b.harga,
       IF(b.status = 1, 'Aktif', 'Nonaktif') AS status
FROM barang b
JOIN satuan s ON b.idsatuan = s.idsatuan
JOIN vendor v ON b.idvendor = v.idvendor;
CREATE VIEW view_barang_aktif AS
SELECT b.idbarang, b.nama AS nama_barang,
       CASE b.jenis WHEN 'B' THEN 'Buku' WHEN 'A' THEN 'Alat Tulis' ELSE 'Lainnya' END AS kategori,
       s.nama_satuan, v.nama_vendor, b.harga
FROM barang b
JOIN satuan s ON b.idsatuan = s.idsatuan
JOIN vendor v ON b.idvendor = v.idvendor
WHERE b.status = 1;
CREATE VIEW view_barang AS
SELECT b.idbarang, b.nama AS nama_barang, s.nama_satuan, b.harga
FROM barang b JOIN satuan s ON b.idsatuan = s.idsatuan
WHERE b.status = 1;
CREATE VIEW view_margin_semua AS
SELECT m.idmargin_penjualan, m.persen, IF(m.status = 1, 'Aktif', 'Nonaktif') AS status,
       u.username AS dibuat_oleh, m.created_at, m.updated_at
FROM margin_penjualan m JOIN user u ON m.iduser = u.iduser;
CREATE VIEW view_margin_aktif AS
SELECT m.idmargin_penjualan, m.persen, u.username AS dibuat_oleh, m.created_at
FROM margin_penjualan m JOIN user u ON m.iduser = u.iduser
WHERE m.status = 1;
CREATE VIEW view_role AS SELECT idrole, nama_role FROM role;
CREATE VIEW view_user AS
SELECT u.iduser, u.username, r.nama_role
FROM user u JOIN role r ON u.idrole = r.idrole;
CREATE VIEW viewpengadaan AS
SELECT p.idpengadaan AS ID,
       CASE p.status WHEN 'P' THEN 'Diproses' WHEN 'S' THEN 'Selesai' END AS STATUS,
       p.subtotal_nilai AS SUBTOTAL, p.ppn AS PPN, p.total_nilai AS TOTAL,
       v.nama_vendor AS VENDOR, u.username AS OLEH
FROM pengadaan p
JOIN vendor v ON p.vendor_idvendor = v.idvendor
JOIN user u ON p.user_iduser = u.iduser;
CREATE VIEW viewdetail_pengadaan AS
SELECT dp.iddetail_pengadaan AS 'ID DETAIL', p.idpengadaan AS 'ID PENGADAAN',
       b.nama AS BARANG, dp.jumlah AS 'JUMLAH BARANG',
       dp.harga_satuan AS 'HARGA SATUAN BARANG', dp.sub_total AS SUBTOTAL
FROM detail_pengadaan dp
JOIN barang b ON dp.idbarang = b.idbarang
JOIN pengadaan p ON dp.idpengadaan = p.idpengadaan;
CREATE VIEW viewpenerimaan AS
SELECT pr.idpenerimaan AS ID, p.idpengadaan AS 'ID PENGADAAN', u.username AS OLEH,
       CASE pr.status WHEN 'P' THEN 'Diproses' WHEN 'S' THEN 'Selesai' END AS STATUS
FROM penerimaan pr
JOIN pengadaan p ON pr.idpengadaan = p.idpengadaan
JOIN user u ON pr.iduser = u.iduser;
CREATE VIEW viewdetail_penerimaan AS
SELECT dpr.iddetail_penerimaan AS ID, pr.idpenerimaan AS 'ID PENERIMAAN',
       b.nama AS BARANG, dpr.jumlah_terima AS 'JUMLAH DITERIMA',
       dpr.harga_satuan_terima AS 'HARGA SATUAN', dpr.sub_total_terima AS 'SUBTOTAL PENERIMAAN'
FROM detail_penerimaan dpr
JOIN penerimaan pr ON dpr.idpenerimaan = pr.idpenerimaan
JOIN barang b ON dpr.barang_idbarang = b.idbarang;
CREATE VIEW viewpenjualan AS
SELECT j.idpenjualan AS ID, j.subtotal_nilai AS SUBTOTAL, j.ppn AS PPN,
       j.total_nilai AS TOTAL, u.username AS OLEH
FROM penjualan j JOIN user u ON j.iduser = u.iduser;
CREATE VIEW viewdetail_penjualan AS
SELECT dj.iddetail_penjualan AS ID, j.idpenjualan AS 'ID PENJUALAN', b.nama AS BARANG,
       dj.harga_satuan AS 'HARGA SATUAN', dj.jumlah AS JUMLAH, dj.subtotal AS SUBTOTAL
FROM detail_penjualan dj
JOIN penjualan j ON dj.penjualan_idpenjualan = j.idpenjualan
JOIN barang b ON dj.idbarang = b.idbarang;
CREATE VIEW viewretur AS
SELECT r.idretur AS ID, pr.idpenerimaan AS 'ID PENERIMAAN', u.username AS 'DIPROSES OLEH'
FROM retur r
JOIN penerimaan pr ON r.idpenerimaan = pr.idpenerimaan
JOIN user u ON r.iduser = u.iduser;
CREATE VIEW viewdetail_retur AS
SELECT dr.iddetail_retur AS ID, r.idretur AS 'ID RETUR', b.nama AS BARANG,
       dr.jumlah AS JUMLAH, dr.alasan AS 'ALASAN RETUR'
FROM detail_retur dr
JOIN retur r ON dr.idretur = r.idretur
JOIN detail_penerimaan dpr ON dr.iddetail_penerimaan = dpr.iddetail_penerimaan
JOIN barang b ON dpr.barang_idbarang = b.idbarang;
CREATE VIEW viewkartu_stok AS
SELECT ks.idkartu_stok AS ID, ks.idtransaksi AS 'ID TRANSAKSI', b.nama AS BARANG,
       CASE ks.jenis_transaksi WHEN 'P' THEN 'PENERIMAAN' WHEN 'J' THEN 'PENJUALAN' WHEN 'R' THEN 'RETUR' END AS 'JENIS TRANSAKSI',
       ks.masuk AS 'BARANG MASUK', ks.keluar AS 'BARANG KELUAR', ks.stock AS 'STOCK BARANG'
FROM kartu_stok ks JOIN barang b ON ks.idbarang = b.idbarang;

/* ===== 4. FUNCTION ===== */

DELIMITER $$

CREATE FUNCTION fn_total_pengadaan(p_idpengadaan BIGINT) RETURNS INT
READS SQL DATA
BEGIN
  DECLARE v_sub INT;
  SELECT IFNULL(SUM(sub_total), 0) INTO v_sub FROM detail_pengadaan WHERE idpengadaan = p_idpengadaan;
  RETURN v_sub + ROUND(v_sub * 0.11);
END$$

CREATE FUNCTION fn_harga_jual(p_idbarang INT) RETURNS INT
READS SQL DATA
BEGIN
  DECLARE v_harga INT;
  DECLARE v_margin DOUBLE;
  SELECT harga INTO v_harga FROM barang WHERE idbarang = p_idbarang;
  SELECT persen INTO v_margin FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC LIMIT 1;
  RETURN ROUND(IFNULL(v_harga, 0) * (1 + IFNULL(v_margin, 0) / 100));
END$$

DELIMITER ;

/* ===== 5. STORED PROCEDURE ===== */

DELIMITER $$

CREATE PROCEDURE sp_detail_pengadaan(IN p_idpengadaan BIGINT, IN p_idbarang INT, IN p_jumlah INT)
BEGIN
  DECLARE v_ok INT;
  DECLARE v_harga INT;
  DECLARE v_sub INT;
  IF p_jumlah IS NULL OR p_jumlah <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Jumlah pengadaan harus lebih dari 0.';
  END IF;
  SELECT COUNT(*) INTO v_ok
    FROM barang b JOIN pengadaan p ON p.vendor_idvendor = b.idvendor
   WHERE b.idbarang = p_idbarang AND p.idpengadaan = p_idpengadaan AND b.status = 1;
  IF v_ok = 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang tidak aktif atau bukan milik vendor ini.';
  END IF;
  SELECT harga INTO v_harga FROM barang WHERE idbarang = p_idbarang;
  INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan)
  VALUES (v_harga, p_jumlah, v_harga * p_jumlah, p_idbarang, p_idpengadaan);
  SELECT SUM(sub_total) INTO v_sub FROM detail_pengadaan WHERE idpengadaan = p_idpengadaan;
  UPDATE pengadaan
     SET subtotal_nilai = v_sub, ppn = ROUND(v_sub * 0.11), total_nilai = fn_total_pengadaan(p_idpengadaan)
   WHERE idpengadaan = p_idpengadaan;
END$$

CREATE PROCEDURE sp_detail_penerimaan(IN p_idpenerimaan BIGINT, IN p_idbarang INT, IN p_jumlah INT, IN p_harga INT)
BEGIN
  DECLARE v_idp BIGINT;
  DECLARE v_pesan INT;
  DECLARE v_terima INT;
  DECLARE v_harga INT;
  SELECT idpengadaan INTO v_idp FROM penerimaan WHERE idpenerimaan = p_idpenerimaan;
  SELECT IFNULL(SUM(jumlah), 0) INTO v_pesan FROM detail_pengadaan WHERE idpengadaan = v_idp AND idbarang = p_idbarang;
  SELECT IFNULL(SUM(dp.jumlah_terima), 0) INTO v_terima
    FROM detail_penerimaan dp JOIN penerimaan pn ON dp.idpenerimaan = pn.idpenerimaan
   WHERE pn.idpengadaan = v_idp AND dp.barang_idbarang = p_idbarang;
  IF p_jumlah IS NULL OR p_jumlah <= 0 OR p_jumlah > v_pesan - v_terima THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Jumlah terima melebihi sisa pesanan.';
  END IF;
  SET v_harga = p_harga;
  IF v_harga IS NULL OR v_harga <= 0 THEN
    SELECT harga_satuan INTO v_harga FROM detail_pengadaan WHERE idpengadaan = v_idp AND idbarang = p_idbarang LIMIT 1;
  END IF;
  INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
  VALUES (p_idpenerimaan, p_idbarang, p_jumlah, v_harga, v_harga * p_jumlah);
END$$

CREATE PROCEDURE sp_detail_penjualan(IN p_idpenjualan INT, IN p_idbarang INT, IN p_jumlah INT)
BEGIN
  DECLARE v_ok INT;
  DECLARE v_stok INT;
  DECLARE v_harga INT;
  DECLARE v_sub INT;
  SELECT COUNT(*) INTO v_ok FROM barang WHERE idbarang = p_idbarang AND status = 1;
  IF v_ok = 0 OR p_jumlah IS NULL OR p_jumlah <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang atau jumlah tidak valid.';
  END IF;
  SELECT stock INTO v_stok FROM kartu_stok WHERE idbarang = p_idbarang ORDER BY idkartu_stok DESC LIMIT 1;
  IF IFNULL(v_stok, 0) < p_jumlah THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok barang tidak mencukupi.';
  END IF;
  SET v_harga = fn_harga_jual(p_idbarang);
  INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
  VALUES (v_harga, p_jumlah, v_harga * p_jumlah, p_idpenjualan, p_idbarang);
  SELECT SUM(subtotal) INTO v_sub FROM detail_penjualan WHERE penjualan_idpenjualan = p_idpenjualan;
  UPDATE penjualan
     SET subtotal_nilai = v_sub, ppn = ROUND(v_sub * 0.11), total_nilai = v_sub + ROUND(v_sub * 0.11)
   WHERE idpenjualan = p_idpenjualan;
END$$

CREATE PROCEDURE sp_set_margin_aktif(IN p_idmargin INT)
BEGIN
  UPDATE margin_penjualan SET status = 0 WHERE idmargin_penjualan <> p_idmargin;
  UPDATE margin_penjualan SET status = 1 WHERE idmargin_penjualan = p_idmargin;
END$$

DELIMITER ;

/* ===== 6. TRIGGER ===== */

DELIMITER $$

CREATE TRIGGER ai_detail_penerimaan AFTER INSERT ON detail_penerimaan
FOR EACH ROW
BEGIN
  DECLARE v_stock INT;
  DECLARE v_idpengadaan BIGINT;
  DECLARE v_kurang INT;
  DECLARE v_status CHAR(1);
  SELECT stock INTO v_stock FROM kartu_stok
   WHERE idbarang = NEW.barang_idbarang ORDER BY idkartu_stok DESC LIMIT 1;
  INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
  VALUES ('P', NEW.jumlah_terima, 0, IFNULL(v_stock, 0) + NEW.jumlah_terima, NEW.idpenerimaan, NEW.barang_idbarang);
  UPDATE barang SET harga = NEW.harga_satuan_terima WHERE idbarang = NEW.barang_idbarang;
  SELECT idpengadaan INTO v_idpengadaan FROM penerimaan WHERE idpenerimaan = NEW.idpenerimaan;
  SELECT COUNT(*) INTO v_kurang
    FROM detail_pengadaan d
   WHERE d.idpengadaan = v_idpengadaan
     AND d.jumlah > IFNULL((SELECT SUM(dp.jumlah_terima)
                              FROM detail_penerimaan dp JOIN penerimaan pn ON dp.idpenerimaan = pn.idpenerimaan
                             WHERE pn.idpengadaan = v_idpengadaan AND dp.barang_idbarang = d.idbarang), 0);
  SET v_status = IF(v_kurang = 0, 'S', 'P');
  UPDATE penerimaan SET status = v_status WHERE idpengadaan = v_idpengadaan;
  UPDATE pengadaan SET status = v_status WHERE idpengadaan = v_idpengadaan;
END$$

CREATE TRIGGER ai_detail_penjualan AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
  DECLARE v_stock INT;
  SELECT stock INTO v_stock FROM kartu_stok WHERE idbarang = NEW.idbarang ORDER BY idkartu_stok DESC LIMIT 1;
  INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
  VALUES ('J', 0, NEW.jumlah, IFNULL(v_stock, 0) - NEW.jumlah, NEW.penjualan_idpenjualan, NEW.idbarang);
END$$

CREATE TRIGGER ai_detail_retur AFTER INSERT ON detail_retur
FOR EACH ROW
BEGIN
  DECLARE v_stock INT;
  DECLARE v_idbarang INT;
  SELECT barang_idbarang INTO v_idbarang FROM detail_penerimaan WHERE iddetail_penerimaan = NEW.iddetail_penerimaan;
  SELECT stock INTO v_stock FROM kartu_stok WHERE idbarang = v_idbarang ORDER BY idkartu_stok DESC LIMIT 1;
  INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
  VALUES ('R', 0, NEW.jumlah, IFNULL(v_stock, 0) - NEW.jumlah, NEW.idretur, v_idbarang);
END$$

DELIMITER ;


DROP FUNCTION IF EXISTS fn_harga_jual;
DROP PROCEDURE IF EXISTS sp_detail_penjualan;

DELIMITER $$

CREATE FUNCTION fn_harga_jual(p_idbarang INT, p_idpenjualan INT) RETURNS INT
READS SQL DATA
BEGIN
  DECLARE v_harga INT;
  DECLARE v_margin DOUBLE;
  SELECT harga INTO v_harga FROM barang WHERE idbarang = p_idbarang;
  SELECT m.persen INTO v_margin
    FROM penjualan p JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan
   WHERE p.idpenjualan = p_idpenjualan;
  RETURN ROUND(IFNULL(v_harga, 0) * (1 + IFNULL(v_margin, 0) / 100));
END$$

CREATE PROCEDURE sp_detail_penjualan(IN p_idpenjualan INT, IN p_idbarang INT, IN p_jumlah INT)
BEGIN
  DECLARE v_ok INT;
  DECLARE v_stok INT;
  DECLARE v_harga INT;
  DECLARE v_sub INT;
  SELECT COUNT(*) INTO v_ok FROM barang WHERE idbarang = p_idbarang AND status = 1;
  IF v_ok = 0 OR p_jumlah IS NULL OR p_jumlah <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang atau jumlah tidak valid.';
  END IF;
  SELECT stock INTO v_stok FROM kartu_stok WHERE idbarang = p_idbarang ORDER BY idkartu_stok DESC LIMIT 1;
  IF IFNULL(v_stok, 0) < p_jumlah THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok barang tidak mencukupi.';
  END IF;
  SET v_harga = fn_harga_jual(p_idbarang, p_idpenjualan);
  INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
  VALUES (v_harga, p_jumlah, v_harga * p_jumlah, p_idpenjualan, p_idbarang);
  SELECT SUM(subtotal) INTO v_sub FROM detail_penjualan WHERE penjualan_idpenjualan = p_idpenjualan;
  UPDATE penjualan
     SET subtotal_nilai = v_sub, ppn = ROUND(v_sub * 0.11), total_nilai = v_sub + ROUND(v_sub * 0.11)
   WHERE idpenjualan = p_idpenjualan;
END$$

DELIMITER ;