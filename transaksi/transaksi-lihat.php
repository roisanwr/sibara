<?php
// transaksi/transaksi-lihat.php

include '../auth_check.php';
include '../koneksi.php';

// Hanya 'staff-gudang' yang boleh mengakses halaman ini
if ($peran != 'staff-gudang') {
    header("location:../home.php?status=gagal_akses");
    exit;
}

// Query untuk mengambil semua data transaksi, diurutkan dari yang terbaru
$query = "SELECT t.*, p.nama as nama_staff
          FROM transaksi t
          JOIN pengguna p ON t.id_pengguna_staff = p.id_pengguna
          ORDER BY t.tanggal_transaksi DESC";
$hasil = mysqli_query($koneksi, $query);

// Pengaturan untuk Header Dinamis
$page_title = "Penerimaan Barang";
$breadcrumbs = "Penerimaan Barang";
// PERUBAHAN: Tombol aksi dikosongkan dari header
$action_button = '';

require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        
        <!-- PERUBAHAN: Tombol Catat Penerimaan dipindahkan ke sini -->
        <div class="flex justify-end mb-6">
            <a href="transaksi-penerimaan.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Catat Penerimaan Barang</span>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border-color);">
                <thead style="background-color: var(--bg-main);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">ID Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Staff Bertugas</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <?php if(mysqli_num_rows($hasil) > 0): ?>
                        <?php while($data = mysqli_fetch_assoc($hasil)): ?>
                        <tr class="theme-aware-hover">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">TR-<?php echo $data['id_transaksi']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo date('d M Y, H:i', strtotime($data['tanggal_transaksi'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <?php
                                    // Memberi warna berbeda untuk transaksi Masuk dan Keluar
                                    $jenis = $data['jenis_transaksi'];
                                    $badge_color = $jenis == 'Masuk' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badge_color; ?>">
                                    <?php echo $jenis; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm max-w-sm truncate"><?php echo htmlspecialchars($data['keterangan'] ? $data['keterangan'] : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['nama_staff']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="transaksi-detail.php?id=<?php echo $data['id_transaksi']; ?>" class="text-indigo-600 hover:text-indigo-900">Lihat Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm" style="color: var(--text-muted);">
                                Belum ada riwayat transaksi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
require_once '../templates/footer.php';
?>
