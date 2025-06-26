<?php
// auth_check.php

// Selalu mulai sesi di baris paling atas untuk bisa mengakses variabel $_SESSION.
session_start();

// Cek apakah variabel sesi "loggedin" ada dan nilainya TRUE.
// Jika tidak ada atau nilainya bukan TRUE, berarti pengguna belum login.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Jika belum login, paksa pengguna kembali ke halaman login.
    header("location: login.php");
    exit; // Pastikan untuk keluar dari skrip setelah redirect.
}

// Jika lolos dari pengecekan di atas, berarti pengguna sudah login.
// Skrip di halaman yang menyertakan file ini akan lanjut dieksekusi.
// Variabel sesi seperti $_SESSION["id_pengguna"] dan $_SESSION["peran"] akan tersedia.
?>
