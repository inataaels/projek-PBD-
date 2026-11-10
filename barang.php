<?php
require_once 'config/crud.php';
crud_page([
  'title'=>'Barang','file'=>'barang.php','table'=>'barang','pk'=>'idbarang','soft'=>['status',0],
  'fields'=>[
    ['name'=>'jenis','label'=>'Jenis','type'=>'select','options'=>[['B','Buku'],['A','Alat Tulis'],['L','Lainnya']]],
    ['name'=>'nama','label'=>'Nama Barang','type'=>'text'],
    ['name'=>'idsatuan','label'=>'Satuan','type'=>'select','sql'=>"SELECT idsatuan v, nama_satuan l FROM satuan WHERE status=1"],
    ['name'=>'harga','label'=>'Harga Beli (Rp)','type'=>'number'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>[[1,'Aktif'],[0,'Nonaktif']]],
  ],
  'list_sql'=>"SELECT b.idbarang, b.jenis, b.nama, s.nama_satuan, b.harga, IF(b.status=1,'Aktif','Nonaktif') AS status,
     COALESCE((SELECT k.stock FROM kartu_stok k WHERE k.idbarang=b.idbarang ORDER BY k.idkartu_stok DESC LIMIT 1),0) AS stok
     FROM barang b JOIN satuan s ON b.idsatuan=s.idsatuan ORDER BY b.idbarang",
  'columns'=>[['ID','idbarang'],['Jenis','jenis'],['Nama','nama'],['Satuan','nama_satuan'],['Harga Beli','harga','rp'],['Stok','stok'],['Status','status']],
]);
