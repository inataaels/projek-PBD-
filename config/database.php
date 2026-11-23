<?php
// Konfigurasi koneksi database BukuKita
//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$user = 'root';
$pass = '';   
$db   = 'bukukita';

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    die("Koneksi database gagal. " . mysqli_connect_error());
}
$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    die("Koneksi database gagal. " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}