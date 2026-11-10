<?php
require_once 'config/crud.php';
crud_page([
  'title'=>'Role','file'=>'role.php','table'=>'role','pk'=>'idrole',
  'fields'=>[['name'=>'nama_role','label'=>'Nama Role','type'=>'text']],
  'list_sql'=>"SELECT * FROM view_role",
  'columns'=>[['ID','idrole'],['Nama Role','nama_role']],
]);
