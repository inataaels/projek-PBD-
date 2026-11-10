<?php
require_once 'config/crud.php';
crud_page([
  'title'=>'Vendor','file'=>'vendor.php','table'=>'vendor','pk'=>'idvendor','soft'=>['status','N'],
  'fields'=>[
    ['name'=>'nama_vendor','label'=>'Nama Vendor','type'=>'text'],
    ['name'=>'badan_hukum','label'=>'Badan Hukum','type'=>'select','options'=>[['Y','Ya'],['N','Tidak']]],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>[['A','Aktif'],['N','Nonaktif']]],
  ],
  'list_sql'=>"SELECT idvendor, nama_vendor, IF(badan_hukum='Y','Badan Hukum','Non Badan Hukum') AS jenis,
               IF(status='A','Aktif','Nonaktif') AS status FROM vendor",
  'columns'=>[['ID','idvendor'],['Nama Vendor','nama_vendor'],['Jenis','jenis'],['Status','status']],
]);
