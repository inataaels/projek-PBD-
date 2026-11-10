<?php
require_once 'config/crud.php';
crud_page([
  'title'=>'Satuan','file'=>'satuan.php','table'=>'satuan','pk'=>'idsatuan','soft'=>['status',0],
  'fields'=>[
    ['name'=>'nama_satuan','label'=>'Nama Satuan','type'=>'text'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>[[1,'Aktif'],[0,'Nonaktif']]],
  ],
  'list_sql'=>"SELECT idsatuan, nama_satuan, IF(status=1,'Aktif','Nonaktif') AS status FROM satuan",
  'columns'=>[['ID','idsatuan'],['Nama Satuan','nama_satuan'],['Status','status']],
]);
