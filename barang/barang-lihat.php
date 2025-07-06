<?php
// barang/barang-lihat.php

include '../auth_check.php';
include '../koneksi.php';

// Hanya 'staff-gudang' yang boleh mengakses halaman ini
if ($peran != 'staff-gudang') {
    header("location:../home.php?status=gagal_akses");
    exit;
}

$query = "SELECT * FROM barang ORDER BY nama_barang ASC";
$hasil = mysqli_query($koneksi, $query);

// =========================================================
// PENGATURAN UNTUK HEADER DINAMIS
// =========================================================
$page_title = "Data Barang";
$breadcrumbs = "Manajemen Barang / Data Barang";
// PERUBAHAN: Tombol aksi di header kita hapus dari sini
$action_button = ''; 
// =========================================================


// Panggil template header. Header sekarang akan otomatis memanggil sidebar juga.
require_once '../templates/header.php';
?>

<!-- Konten halaman dimulai di sini. Perhatikan, tidak ada lagi tag <header> -->
<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        
        <!-- PERUBAHAN: Tombol Tambah Barang dipindahkan ke sini, di atas tabel -->
        <div class="flex justify-end mb-6">
            <a href="barang-tambah.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Tambah Barang</span>
            </a>
        </div>

        <!-- Tabel Data Barang -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border-color);">
                <thead style="background-color: var(--bg-main);">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">Kode</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">Nama Barang</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">Satuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">Stok</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider" style="color: var(--text-secondary);">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <?php if(mysqli_num_rows($hasil) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while($data = mysqli_fetch_assoc($hasil)): ?>
                        <tr class="theme-aware-hover">
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php echo htmlspecialchars($data['kode_barang']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['kategori']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['satuan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold"><?php echo htmlspecialchars($data['jumlah_stok']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="barang-ubah.php?kode=<?php echo $data['kode_barang']; ?>" class="text-blue-600 hover:text-blue-900">Ubah</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm" style="color: var(--text-muted);">Tidak ada data barang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php
// Panggil footer untuk menutup halaman
require_once '../templates/footer.php';
?>
