<?php
// laporan/laporan-detail.php

include '../auth_check.php';
include '../koneksi.php';

// Pastikan ada ID di URL, jika tidak, arahkan ke halaman laporan
if (!isset($_GET['id'])) {
    header('Location: laporan-lihat.php');
    exit;
}
$id_transaksi = $_GET['id'];

// Ambil data utama transaksi dari database
$query_transaksi = "SELECT t.*, p.nama AS nama_staff 
                    FROM transaksi t 
                    JOIN pengguna p ON t.id_pengguna_staff = p.id_pengguna 
                    WHERE t.id_transaksi = '$id_transaksi'";
$hasil_transaksi = mysqli_query($koneksi, $query_transaksi);
$transaksi = mysqli_fetch_assoc($hasil_transaksi);

// Jika transaksi tidak ditemukan, kembali ke halaman laporan
if (!$transaksi) {
    header('Location: laporan-lihat.php'); 
    exit;
}

// Ambil detail barang yang terlibat dalam transaksi
$query_detail = "SELECT dt.*, b.nama_barang, b.satuan 
                 FROM detail_transaksi dt
                 JOIN barang b ON dt.kode_barang = b.kode_barang
                 WHERE dt.id_transaksi = '$id_transaksi'";
$hasil_detail = mysqli_query($koneksi, $query_detail);

// Pengaturan Header
$page_title = "Detail Laporan Transaksi #TR-" . $transaksi['id_transaksi'];
$breadcrumbs = "Laporan / Detail Transaksi";
$action_button = '';
require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-6 border-b" style="border-color: var(--border-color);">
            <div>
                <p class="text-sm">ID Transaksi</p>
                <p class="text-lg font-bold">#TR-<?php echo $transaksi['id_transaksi']; ?></p>
            </div>
            <div>
                <p class="text-sm">Tanggal Transaksi</p>
                <p class="text-lg font-bold"><?php echo date('d F Y, H:i', strtotime($transaksi['tanggal_transaksi'])); ?></p>
            </div>
            <div>
                <p class="text-sm">Jenis Transaksi</p>
                <span class="px-3 py-1 inline-flex text-md font-semibold rounded-full <?php echo $transaksi['jenis_transaksi'] == 'Masuk' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                    <?php echo $transaksi['jenis_transaksi']; ?>
                </span>
            </div>
            <div class="md:col-span-3">
                <p class="text-sm">Staff Bertugas</p>
                <p class="text-lg font-bold"><?php echo htmlspecialchars($transaksi['nama_staff']); ?></p>
            </div>
             <div class="md:col-span-3">
                <p class="text-sm">Keterangan</p>
                <p class="text-lg"><?php echo htmlspecialchars($transaksi['keterangan'] ? $transaksi['keterangan'] : '-'); ?></p>
            </div>
        </div>

        <h3 class="text-lg font-bold mb-4">Rincian Barang</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border-color);">
                <thead style="background-color: var(--bg-main);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Kode Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Nama Barang</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background-color: var(--bg-card);">
                    <?php if(mysqli_num_rows($hasil_detail) > 0): ?>
                        <?php while($item = mysqli_fetch_assoc($hasil_detail)): ?>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium"><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td class="px-6 py-4 text-center text-sm font-bold">
                                <?php echo htmlspecialchars($item['jumlah']) . ' ' . htmlspecialchars($item['satuan']); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                     <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm" style="color: var(--text-muted);">
                                Tidak ada rincian barang untuk transaksi ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
         <div class="mt-8">
            <a href="laporan-lihat.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Kembali ke Laporan Transaksi</a>
        </div>
    </div>
</main>

<?php
require_once '../templates/footer.php';
?>