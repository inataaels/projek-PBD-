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
  status TINYINT NULL,
  harga INT NULL,
  PRIMARY KEY (idbarang),
  CONSTRAINT fk_barang_satuan FOREIGN KEY (idsatuan) REFERENCES satuan (idsatuan)
);

CREATE TABLE pengadaan (
  idpengadaan BIGINT NOT NULL AUTO_INCREMENT, timestamp TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
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


INSERT INTO satuan (idsatuan, nama_satuan, status) 
VALUES (101, 'Buku', 1),
       (102, 'Paket', 1),
       (103, 'Eksemplar', 1);
       
INSERT INTO vendor (idvendor, nama_vendor, badan_hukum, status) 
VALUES (201, 'PT Pustaka Nusantara', 'Y', 'A'),
	   (202, 'CV Buku Cerdas', 'Y', 'A'),
       (203, 'Toko Grosir Literasi', 'N', 'A');
       
INSERT INTO role (idrole, nama_role) 
VALUES (301, 'Admin'),
	   (302, 'Kasir'),
       (303, 'Gudang');

INSERT INTO user (iduser, username, password, idrole) 
VALUES (401, 'admin_bukukita', 'contoh_hash_admin', 301),
       (402, 'kasir01', 'contoh_hash_kasir', 302),
       (403, 'gudang01', 'contoh_hash_gudang', 303);
       
INSERT INTO margin_penjualan (idmargin_penjualan, persen, status, iduser, updated_at)
VALUES (501, 10, 1, 401, NULL),
       (502, 20, 1, 401, NULL),
       (503, 30, 1, 401, NULL),
       (504, 40, 1, 401, NULL),
       (505, 50, 1, 401, NULL);
       
INSERT INTO barang (idbarang, jenis, nama, idsatuan, status, harga) 
VALUES (601, 'B', 'Laskar Pelangi', 101, 1, 85000),
       (602, 'B', 'Bumi Manusia', 101, 1, 60000),
       (603, 'B', 'Matematika Dasar', 101, 1, 45000),
       (604, 'B', 'Laut Bercerita', 101, 1, 99000),
       (605, 'B', 'Algoritma dan Struktur Data', 101, 1, 90000);

CREATE VIEW viewsatuanbarang 
AS SELECT * FROM satuan;

CREATE VIEW viewrole
AS SELECT * FROM role;

CREATE VIEW viewvendor
AS SELECT * FROM vendor;

CREATE VIEW viewbarang
AS SELECT * FROM barang;

CREATE VIEW viewuser
AS SELECT * FROM user;

CREATE VIEW viewmargin_penjualan
AS SELECT * FROM margin_penjualan;

CREATE OR REPLACE VIEW viewsatuanbarang 
AS SELECT idsatuan AS ID, nama_satuan AS SATUAN,
	 CASE 
		WHEN status = 0 THEN 'Tidak Aktif'
        ELSE 'Aktif'
	 END AS STATUS
FROM satuan;

CREATE OR REPLACE VIEW viewrole
AS SELECT idrole AS ID, nama_role AS ROLE
FROM role;


CREATE VIEW view_vendor_aktif AS
SELECT idvendor, nama_vendor,
       CASE badan_hukum
            WHEN 'Y' THEN 'Badan Hukum'
            ELSE 'Non Badan Hukum'
       END AS jenis_vendor
FROM vendor
WHERE status = 'A';

CREATE OR REPLACE VIEW viewbarang
AS SELECT b.idbarang AS ID,
	 CASE b.jenis
		WHEN 'A' THEN 'Aksesoris'
        WHEN 'B' THEN 'Boardgame'
        WHEN 'C' THEN 'Card'
        WHEN 'G' THEN 'Game'
	 END AS Kategori, 
     b.nama AS NAMA, 
     b.harga AS HARGA, 
     s.nama_satuan AS SATUAN,
	 CASE b.status
		WHEN 0 THEN 'Tidak Aktif'
        ELSE 'Aktif'
	 END AS STATUS
FROM barang b, satuan s 
WHERE b.idsatuan = s.idsatuan;

CREATE OR REPLACE VIEW viewuser
AS SELECT u.iduser AS ID, u.username AS USERNAME, r.nama_role AS ROLE
FROM user u, role r
WHERE u.idrole = r.idrole;

CREATE OR REPLACE VIEW viewmargin_penjualan
AS SELECT mp.idmargin_penjualan AS ID, mp.created_at AS 'TANGGAL DIBUAT', mp.persen AS PERSENTASE, 
CASE mp.status
		WHEN 0 THEN 'Tidak Aktif'
        ELSE 'Aktif'
	 END AS STATUS, 
     mp.updated_at AS 'TERAKHIR DIUBAH',
     u.username AS USER
FROM margin_penjualan mp, user u
WHERE mp.iduser = u.iduser;

CREATE VIEW view_role AS
SELECT idrole, nama_role
FROM role;

CREATE VIEW view_user AS
SELECT u.iduser, u.username, r.nama_role
FROM user u
JOIN role r ON u.idrole = r.idrole;

CREATE VIEW view_margin_aktif AS
SELECT m.idmargin_penjualan, m.persen, u.username AS dibuat_oleh, m.created_at
FROM margin_penjualan m
JOIN user u ON m.iduser = u.iduser
WHERE m.status = 1;

CREATE VIEW view_barang AS
SELECT b.idbarang, b.nama AS nama_barang, s.nama_satuan, b.harga
FROM barang b
JOIN satuan s ON b.idsatuan = s.idsatuan
WHERE b.status = 1;

-- function
DELIMITER $$

CREATE FUNCTION hitung_harga_jual(id_barang INT)
RETURNS DECIMAL(12,2)
READS SQL DATA
BEGIN
    DECLARE v_harga DECIMAL(12,2);
    -- ... isi function Anda ...
    RETURN v_harga;
END$$
DELIMITER ;

-- store procedured
DELIMITER $$
CREATE PROCEDURE sp_simpan_penjualan(IN k_idpenjualan INT,
									 IN k_iduser INT,
							 		 IN k_idmargin_penjualan INT,
                                     IN k_iddetail_penjualan BIGINT,
                                     IN k_harga_satuan INT,
                                     IN k_jumlah INT,
                                     IN k_idbarang INT)
BEGIN
	DECLARE v_subtotal INT;
    DECLARE v_ppn INT;
    DECLARE v_total INT;
    
    SET v_subtotal = hitung_subtotal(k_idbarang, k_jumlah);
    SET v_total = hitung_total_ppn(k_idbarang, k_jumlah);
	SET v_ppn = v_total - v_subtotal;
    
    INSERT INTO penjualan (idpenjualan, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
    VALUES (k_idpenjualan, v_subtotal, v_ppn, v_total, k_iduser, k_idmargin_penjualan);
    
    INSERT INTO detail_penjualan (iddetail_penjualan, harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
    VALUES (k_iddetail_penjualan, k_harga_Satuan, k_jumlah, v_subtotal, k_idpenjualan, k_idbarang);
END $$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_simpan_penjualan;
DELIMITER //
CREATE PROCEDURE sp_simpan_penjualan(IN k_iduser INT,
									 IN k_jumlah INT,
									 IN k_idbarang INT)
BEGIN
	DECLARE v_subtotal INT;
    DECLARE v_ppn INT;
    DECLARE v_total INT;
    DECLARE v_idpenjualan INT;
    DECLARE v_harga_jual INT;
    
    SET v_harga_jual = hitung_harga_jual(k_idbarang);
    SET v_subtotal = hitung_subtotal(k_idbarang, k_jumlah);
    SET v_total = hitung_total_ppn(k_idbarang, k_jumlah);
	SET v_ppn = v_total - v_subtotal;
    
    INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
    VALUES (v_subtotal, v_ppn, v_total, k_iduser, 
			(SELECT idmargin_penjualan FROM margin_penjualan
			 WHERE status = 1
             LIMIT 1)
		   );
           
	SET v_idpenjualan = LAST_INSERT_ID();
    
    INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
    VALUES (v_harga_jual, k_jumlah, v_subtotal, v_idpenjualan, k_idbarang);
    END //
    DELIMITER ;
    
--trigger
DELIMITER //
CREATE TRIGGER trigger_detail_penerimaan
AFTER INSERT ON detail_penerimaan
FOR EACH ROW 
BEGIN
	DECLARE v_stock INT;
    
    SELECT stock
    INTO v_stock
    FROM kartu_stok
    WHERE idbarang = NEW.barang_idbarang
    ORDER BY idkartu_stok DESC
    LIMIT 1;
    
    IF v_stock IS NULL THEN
    SET v_stock = 0;
    END IF;

    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
    VALUES ('P', NEW.jumlah_terima, 0, v_stock + NEW.jumlah_terima, NEW.idpenerimaan, NEW.barang_idbarang);
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER trigger_detail_penjualan
AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
	DECLARE v_stock INT;
    
    SELECT stock
    INTO v_stock
    FROM kartu_stok
    WHERE idbarang = NEW.idbarang
    ORDER BY idkartu_stok DESC
    LIMIT 1;
    
    IF v_stock IS NULL THEN
    SET v_stock = 0;
    END IF;
    
    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
    VALUES ('J', 0, NEW.jumlah, v_stock - NEW.jumlah, NEW.penjualan_idpenjualan, NEW.idbarang);
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER trigger_detail_retur
AFTER INSERT ON detail_retur
FOR EACH ROW
BEGIN
	DECLARE v_stock INT;
    DECLARE v_idbarang INT;
    SELECT barang_idbarang
    INTO v_idbarang
    FROM detail_penerimaan
    WHERE iddetail_penerimaan = NEW.iddetail_penerimaan;
    
    SELECT stock
    INTO v_stock
    FROM kartu_stok
    WHERE idbarang = v_idbarang
    ORDER BY idkartu_stok DESC
    LIMIT 1;
    
    IF v_stock IS NULL THEN
    SET v_stock = 0;
    END IF;
    
     INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
    VALUES ('R', 0, NEW.jumlah, v_stock - NEW.jumlah, NEW.idretur, v_idbarang);
END //
DELIMITER ;

-- view data transaksi
CREATE VIEW viewpengadaan AS
SELECT p.idpengadaan AS ID, 
		  CASE p.status
          WHEN 'P' THEN 'Diproses'
          WHEN 'S' THEN 'Selesai'
          END AS STATUS,
          p.subtotal_nilai AS SUBTOTAL,
          p.ppn AS PPN,
          p.total_nilai AS TOTAL,
          v.nama_vendor AS VENDOR,
          u.username AS OLEH
FROM pengadaan  p
JOIN vendor v
ON p.vendor_idvendor = v.idvendor
JOIN user u
ON p.user_iduser = u.iduser;

CREATE VIEW viewdetail_pengadaan AS
SELECT dp.iddetail_pengadaan AS 'ID DETAIL', 
	   p.idpengadaan AS 'ID PENGADAAN',
	   b.nama AS BARANG,
	   dp.jumlah AS 'JUMLAH BARANG',
	   dp.harga_satuan AS 'HARGA SATUAN BARANG',
       dp.sub_total AS SUBTOTAL
FROM detail_pengadaan dp
JOIN barang b
ON dp.idbarang = b.idbarang
JOIN pengadaan p
ON dp.idpengadaan = p.idpengadaan;


CREATE VIEW viewpenerimaan AS
SELECT pr.idpenerimaan AS ID,
	   p.idpengadaan AS 'ID PENGADAAN',
       u.username AS OLEH,
	   CASE pr.status 
       WHEN 'S' THEN 'Selesai'
       END AS STATUS
FROM penerimaan pr
JOIN pengadaan p
ON pr.idpengadaan = p.idpengadaan
JOIN user u
ON pr.iduser = u.iduser;


CREATE VIEW viewdetail_penerimaan AS
SELECT dpr.iddetail_penerimaan AS ID,
	   pr.idpenerimaan AS 'ID PENERIMAAN',
       b.nama AS BARANG,
	   dpr.jumlah_terima AS 'JUMLAH DITERIMA',
       dpr.harga_satuan_terima AS 'HARGA SATUAN',
       dpr.sub_total_terima AS 'SUBTOTAL PENERIMAAN'
FROM detail_penerimaan dpr
JOIN penerimaan pr
ON dpr.idpenerimaan = pr.idpenerimaan
JOIN barang b
ON dpr.barang_idbarang = b.idbarang;

CREATE VIEW viewpenjualan AS
SELECT j.idpenjualan AS ID,
	   j.subtotal_nilai AS SUBTOTAL,
       j.ppn AS PPN,
       j.total_nilai AS TOTAL,
       u.username AS OLEH
FROM penjualan j
JOIN user u
ON j.iduser = u.iduser;

CREATE VIEW viewdetail_penjualan AS
SELECT dj.iddetail_penjualan AS ID,
	   j.idpenjualan AS 'ID PENJUALAN',
       b.nama AS BARANG,
	   dj.harga_satuan AS 'HARGA SATUAN',
       dj.jumlah AS JUMLAH,
       dj.subtotal AS SUBTOTAL
FROM detail_penjualan dj
JOIN penjualan j
ON dj.penjualan_idpenjualan = j.idpenjualan
JOIN barang b
ON dj.idbarang = b.idbarang;

CREATE VIEW viewretur AS
SELECT r.idretur AS ID,
	   pr.idpenerimaan AS 'ID PENERIMAAN',
       u.username AS 'DIPROSES OLEH'
FROM retur r
JOIN penerimaan pr
ON r.idpenerimaan = pr.idpenerimaan
JOIN user u
ON r.iduser = u.iduser;

CREATE VIEW viewdetail_retur AS
SELECT dr.iddetail_retur AS ID,
	   r.idretur AS 'ID RETUR',
	   b.nama AS BARANG,
       dr.jumlah AS JUMLAH,
       dr.alasan AS 'ALASAN RETUR'
FROM detail_retur dr
JOIN retur r 
ON dr.idretur = r.idretur
JOIN detail_penerimaan dpr
ON dr.iddetail_penerimaan = dpr.iddetail_penerimaan
JOIN barang b
ON dpr.barang_idbarang = b.idbarang;

CREATE VIEW viewkartu_stok AS
SELECT ks.idkartu_stok AS ID,
	   ks.idtransaksi AS 'ID TRANSAKSI',
       b.nama AS BARANG,
	   CASE ks.jenis_transaksi 
       WHEN 'P' THEN 'PENERIMAAN'
       WHEN 'J' THEN 'PENJUALAN'
       WHEN 'R' THEN 'RETUR'
       END AS 'JENIS TRANSAKSI',
       ks.masuk AS 'BARANG MASUK',
       ks.keluar AS 'BARANG KELUAR',
       ks.stock AS 'STOCK BARANG'
FROM kartu_stok ks
JOIN barang b
ON ks.idbarang = b.idbarang;