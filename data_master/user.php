<?php
require_once 'config/crud.php';
crud_page([
  'title'=>'User','file'=>'user.php','table'=>'user','pk'=>'iduser',
  'fields'=>[
    ['name'=>'username','label'=>'Username','type'=>'text'],
    ['name'=>'password','label'=>'Password','type'=>'password'],
    ['name'=>'idrole','label'=>'Role','type'=>'select','sql'=>"SELECT idrole v, nama_role l FROM role"],
  ],
  'list_sql'=>"SELECT * FROM view_user",
  'columns'=>[['ID','iduser'],['Username','username'],['Role','nama_role']],
]);
