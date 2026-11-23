<?php
require_once 'config/crud.php';
crud_page([
  'title'=>'Margin Penjualan','file'=>'margin.php','table'=>'margin_penjualan','pk'=>'idmargin_penjualan','soft'=>['status',0],
  'fields'=>[
    ['name'=>'persen','label'=>'Persen Margin (%)','type'=>'number'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>[[1,'Aktif'],[0,'Nonaktif']]],
    ['name'=>'iduser','label'=>'Dibuat Oleh','type'=>'select','sql'=>"SELECT iduser v, username l FROM user"],
  ],
  'list_sql'=>"SELECT m.idmargin_penjualan, CONCAT(m.persen,' %') AS persen, IF(m.status=1,'Aktif','Nonaktif') AS status,
               u.username, m.created_at FROM margin_penjualan m JOIN user u ON m.iduser=u.iduser",
  'columns'=>[['ID','idmargin_penjualan'],['Margin','persen'],['Status','status'],['Dibuat Oleh','username'],['Dibuat','created_at']],
]);
