<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Ganti dengan username DB kamu
define('DB_PASSWORD', ''); // Ganti dengan password DB kamu
define('DB_NAME', 'dppl'); // Nama database sesuai file .sql

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn === false){
    die("ERROR: Tidak bisa terhubung. " . mysqli_connect_error());
}
?>