<?php
// home.php

// 1. Memanggil file satpam (auth_check) dan koneksi database
include 'auth_check.php';
include 'koneksi.php';

// 2. Ambil data untuk ditampilkan di kartu statistik
$query_total_barang = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang");
$data_total_barang = mysqli_fetch_assoc($query_total_barang);
$total_barang = $data_total_barang['total'];

$query_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM permintaan_barang WHERE status='Pending'");
$data_pending = mysqli_fetch_assoc($query_pending);
$permintaan_pending = $data_pending['total'];

// =========================================================
// PENGATURAN UNTUK HEADER DINAMIS (BARU)
// =========================================================
$page_title = "Dashboard";
$breadcrumbs = "Pages / Dashboard";
// Untuk halaman home, kita tidak perlu tombol aksi, jadi variabel $action_button
// tidak perlu didefinisikan di sini.
// =========================================================


// 3. Panggil file-file template.
// Header akan otomatis menampilkan judul & breadcrumbs dari variabel di atas.
require_once 'templates/header.php';
?>

<!-- Konten Halaman Sebenarnya. Perhatikan, semua kode <header> sudah hilang! -->
<main class="flex-1 p-4 lg:p-8">
    <!-- Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        
        <div class="stat-card p-6 rounded-xl soft-shadow">
            <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Jenis Barang</p>
            <div class="flex items-end gap-2 mt-1">
               <p class="text-2xl font-bold"><?php echo $total_barang; ?></p>
            </div>
        </div>
        
        <div class="stat-card p-6 rounded-xl soft-shadow">
            <p class="text-sm font-medium" style="color: var(--text-secondary);">Permintaan Pending</p>
            <div class="flex items-end gap-2 mt-1">
               <p class="text-2xl font-bold"><?php echo $permintaan_pending; ?></p>
            </div>
        </div>
        
         <div class="stat-card p-6 rounded-xl soft-shadow">
            <p class="text-sm font-medium" style="color: var(--text-secondary);">Transaksi Bulan Ini</p>
            <div class="flex items-end gap-2 mt-1">
               <p class="text-2xl font-bold">12</p> <!-- Data statis sebagai contoh -->
            </div>
        </div>
         <div class="stat-card p-6 rounded-xl soft-shadow">
            <p class="text-sm font-medium" style="color: var(--text-secondary);">Stok Kritis</p>
            <div class="flex items-end gap-2 mt-1">
               <p class="text-2xl font-bold">3</p> <!-- Data statis sebagai contoh -->
            </div>
        </div>
    </div>

    <!-- Bagian lain, misal: Tabel Permintaan Terbaru -->
    <div class="mt-8">
         <div class="stat-card p-6 rounded-xl soft-shadow">
             <h3 class="font-bold text-lg">Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h3>
             <p class="text-sm mt-2" style="color: var(--text-secondary);">
                Anda login sebagai <strong><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $peran))); ?></strong>.
                Gunakan menu di sebelah kiri untuk menavigasi aplikasi.
             </p>
         </div>
    </div>
</main>

<?php
// 4. Panggil footer untuk menutup halaman
require_once 'templates/footer.php';
?>
