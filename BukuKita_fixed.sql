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


CREATE VIEW view_satuan_aktif AS
SELECT idsatuan, nama_satuan
FROM satuan
WHERE status = 1;

CREATE VIEW view_vendor_aktif AS
SELECT idvendor, nama_vendor,
       CASE badan_hukum
            WHEN 'Y' THEN 'Badan Hukum'
            ELSE 'Non Badan Hukum'
       END AS jenis_vendor
FROM vendor
WHERE status = 'A';

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

use BukuKita;
SELECT * FROM view_barang;