<?php
// logout.php

// Selalu mulai sesi di baris paling atas untuk mengakses dan memanipulasi sesi.
session_start();

// Hapus semua variabel sesi yang sudah terdaftar.
// Ini akan membersihkan data seperti id_pengguna, username, peran, dan status loggedin.
$_SESSION = array();

// Hancurkan sesi.
// Ini akan menghapus sesi dari server.
session_destroy();

// Arahkan pengguna kembali ke halaman login.
header("location: login.php");
exit;
?>