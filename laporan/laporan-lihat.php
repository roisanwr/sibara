<?php
// laporan/laporan-lihat.php

include '../auth_check.php';
include '../koneksi.php';

// Atur tanggal default: dari awal bulan ini sampai hari ini
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
// --- TAMBAHAN ---
// Ambil status dari GET, defaultnya 'Semua'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Semua';

// Bangun query dasar
$query = "SELECT pb.*, p_peminta.nama AS nama_peminta, p_staff.nama AS nama_staff
          FROM permintaan_barang pb
          JOIN pengguna p_peminta ON pb.id_pengguna_supervisor = p_peminta.id_pengguna
          LEFT JOIN pengguna p_staff ON pb.id_pengguna_staff = p_staff.id_pengguna
          WHERE pb.tanggal_permintaan BETWEEN '$tgl_awal' AND '$tgl_akhir'";

// --- TAMBAHAN ---
// Tambahkan filter status jika bukan 'Semua'
if ($status_filter != 'Semua') {
    // Pastikan untuk mengamankan input status untuk mencegah SQL Injection
    $escaped_status = mysqli_real_escape_string($koneksi, $status_filter);
    $query .= " AND pb.status = '$escaped_status'";
}

$query .= " ORDER BY pb.tanggal_permintaan DESC";

$hasil = mysqli_query($koneksi, $query);


// Pengaturan Header
$page_title = "Laporan Permintaan Barang"; // Judul diubah agar lebih sesuai
$breadcrumbs = "Laporan / Permintaan Barang"; // Breadcrumbs diubah
$action_button = '';

require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-8 pb-6 border-b" style="border-color: var(--border-color);">
            <div>
                <label for="tgl_awal" class="block text-sm font-medium" style="color: var(--text-secondary);">Dari Tanggal</label>
                <input type="date" id="tgl_awal" name="tgl_awal" value="<?php echo $tgl_awal; ?>" class="mt-1 block w-full px-3 py-2 rounded-lg border focus:outline-none" style="background-color: var(--bg-main); border-color: var(--border-color);">
            </div>
            <div>
                <label for="tgl_akhir" class="block text-sm font-medium" style="color: var(--text-secondary);">Sampai Tanggal</label>
                <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>" class="mt-1 block w-full px-3 py-2 rounded-lg border focus:outline-none" style="background-color: var(--bg-main); border-color: var(--border-color);">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium" style="color: var(--text-secondary);">Status</label>
                <select id="status" name="status" class="mt-1 block w-full px-3 py-2 rounded-lg border focus:outline-none" style="background-color: var(--bg-main); border-color: var(--border-color);">
                    <option value="Semua" <?php echo ($status_filter == 'Semua') ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Disetujui" <?php echo ($status_filter == 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="Ditolak" <?php echo ($status_filter == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg">Tampilkan</button>
                <a href="laporan-cetak.php?tgl_awal=<?php echo $tgl_awal; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>&status=<?php echo htmlspecialchars($status_filter); ?>" target="_blank" class="w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">Cetak</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border-color);">
                <thead style="background-color: var(--bg-main);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Peminta</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Diproses Oleh</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <?php if(mysqli_num_rows($hasil) > 0): ?>
                        <?php while($data = mysqli_fetch_assoc($hasil)): ?>
                        <tr class="theme-aware-hover">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#<?php echo $data['id_permintaan']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo date('d M Y', strtotime($data['tanggal_permintaan'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['nama_peminta']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['nama_staff'] ? $data['nama_staff'] : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                        if ($data['status'] == 'Pending') echo 'bg-yellow-100 text-yellow-800';
                                        elseif ($data['status'] == 'Disetujui') echo 'bg-green-100 text-green-800';
                                        else echo 'bg-red-100 text-red-800';
                                    ?>">
                                    <?php echo $data['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="../permintaan/permintaan-detail.php?id=<?php echo $data['id_permintaan']; ?>" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm" style="color: var(--text-muted);">
                                Tidak ada data permintaan untuk periode dan status yang dipilih.
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