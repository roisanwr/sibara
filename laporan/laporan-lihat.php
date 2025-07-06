<?php
// laporan/laporan-lihat.php

include '../auth_check.php';
include '../koneksi.php';

// Atur tanggal default: dari awal bulan ini sampai hari ini
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
// Filter jenis transaksi, defaultnya 'Semua'
$jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'Semua';

// Query untuk mengambil data transaksi berdasarkan filter
$query = "SELECT t.*, p.nama as nama_staff
          FROM transaksi t
          JOIN pengguna p ON t.id_pengguna_staff = p.id_pengguna
          WHERE DATE(t.tanggal_transaksi) BETWEEN '$tgl_awal' AND '$tgl_akhir'";

// Tambahkan filter jenis transaksi jika bukan 'Semua'
if ($jenis_filter != 'Semua') {
    // Amankan input untuk mencegah SQL Injection
    $escaped_jenis = mysqli_real_escape_string($koneksi, $jenis_filter);
    $query .= " AND t.jenis_transaksi = '$escaped_jenis'";
}

$query .= " ORDER BY t.tanggal_transaksi DESC";

$hasil = mysqli_query($koneksi, $query);

// Pengaturan Header
$page_title = "Laporan Transaksi Barang";
$breadcrumbs = "Laporan / Transaksi Barang";
$action_button = '';

require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-8 pb-6 border-b" style="border-color: var(--border-color);">
            <div>
                <label for="tgl_awal" class="block text-sm font-medium" style="color: var(--text-secondary);">Dari Tanggal</label>
                <input type="date" id="tgl_awal" name="tgl_awal" value="<?php echo htmlspecialchars($tgl_awal); ?>" class="mt-1 block w-full px-3 py-2 rounded-lg border focus:outline-none" style="background-color: var(--bg-main); border-color: var(--border-color);">
            </div>
            <div>
                <label for="tgl_akhir" class="block text-sm font-medium" style="color: var(--text-secondary);">Sampai Tanggal</label>
                <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?php echo htmlspecialchars($tgl_akhir); ?>" class="mt-1 block w-full px-3 py-2 rounded-lg border focus:outline-none" style="background-color: var(--bg-main); border-color: var(--border-color);">
            </div>
            
            <div>
                <label for="jenis" class="block text-sm font-medium" style="color: var(--text-secondary);">Jenis Transaksi</label>
                <select id="jenis" name="jenis" class="mt-1 block w-full px-3 py-2 rounded-lg border focus:outline-none" style="background-color: var(--bg-main); border-color: var(--border-color);">
                    <option value="Semua" <?php echo ($jenis_filter == 'Semua') ? 'selected' : ''; ?>>Semua Jenis</option>
                    <option value="Masuk" <?php echo ($jenis_filter == 'Masuk') ? 'selected' : ''; ?>>Masuk</option>
                    <option value="Keluar" <?php echo ($jenis_filter == 'Keluar') ? 'selected' : ''; ?>>Keluar</option>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg">Tampilkan</button>
                <a href="laporan-cetak.php?tgl_awal=<?php echo htmlspecialchars($tgl_awal); ?>&tgl_akhir=<?php echo htmlspecialchars($tgl_akhir); ?>&jenis=<?php echo htmlspecialchars($jenis_filter); ?>" target="_blank" class="w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">Cetak</a>
            </div>
        </form>

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
                                <a href="../transaksi/transaksi-detail.php?id=<?php echo $data['id_transaksi']; ?>" class="text-indigo-600 hover:text-indigo-900">Lihat Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm" style="color: var(--text-muted);">
                                Tidak ada data transaksi untuk periode dan jenis yang dipilih.
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