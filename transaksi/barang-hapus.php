<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$peran_pengguna = $_SESSION['peran'];

// 3. Pastikan hanya staff yang bisa mengakses halaman ini
if ($peran_pengguna != 'staff') {
    die("Error: Anda tidak memiliki hak akses untuk halaman ini.");
}

// 4. Cek apakah ada ID yang dikirim melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $kode_barang_hapus = $_GET['id'];

    // Siapkan kueri DELETE yang aman dengan prepared statements
    $sql = "DELETE FROM barang WHERE kode_barang = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Ikat parameter ke statement
        mysqli_stmt_bind_param($stmt, "s", $kode_barang_hapus);

        // Eksekusi statement
        if (mysqli_stmt_execute($stmt)) {
            // Jika berhasil, arahkan kembali ke halaman lihat dengan pesan sukses
            header("location: barang-lihat.php?status=hapus_sukses");
            exit();
        } else {
            // Jika gagal, mungkin karena barang ini sudah digunakan di transaksi atau permintaan.
            // Tampilkan pesan error yang lebih informatif.
            echo "Error: Gagal menghapus data. Barang ini mungkin sudah pernah digunakan dalam transaksi atau permintaan. " . mysqli_error($conn);
        }

        // Tutup statement
        mysqli_stmt_close($stmt);
    }
} else {
    // Jika tidak ada ID, arahkan kembali ke halaman lihat
    header("location: barang-lihat.php");
    exit();
}
?>
