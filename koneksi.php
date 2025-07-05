<?php
/**
 * koneksi.php
 * File untuk menghubungkan aplikasi ke database menggunakan MySQLi.
 */

// Konfigurasi Database
$host = "localhost";    // Biasanya "localhost"
$user = "root";         // User database XAMPP default
$password = "";         // Password database XAMPP default
$database = "dppl"; // Nama database sesuai file .sql kamu

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $user, $password, $database);

// Memeriksa status koneksi
// Jika koneksi gagal, hentikan script dan tampilkan pesan error.
if (!$koneksi) {
    die("KONEKSI GAGAL: " . mysqli_connect_error());
}
?>
