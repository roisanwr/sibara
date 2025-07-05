<?php
// auth_check.php

session_start();

// Cek apakah pengguna sudah login atau belum
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    // Jika belum, alihkan ke halaman login
    header("location:index.php");
    exit; // Pastikan untuk menghentikan eksekusi script setelah redirect
}

// Ambil data dari sesi untuk digunakan di semua halaman
$id_pengguna = $_SESSION['id_pengguna']; // <-- PERUBAHAN DI SINI
$username = $_SESSION['username'];
$peran = $_SESSION['peran'];

?>
