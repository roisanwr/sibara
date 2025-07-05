<?php
// permintaan/permintaan-detail.php

include '../auth_check.php';
include '../koneksi.php';

// Cek apakah ada ID permintaan di URL
if (!isset($_GET['id'])) {
    header('Location: permintaan-lihat.php');
    exit;
}
$id_permintaan = $_GET['id'];

// Aksi untuk Staff Gudang (Setuju/Tolak)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $peran == 'staff-gudang') {
    if (isset($_POST['aksi'])) {
        $status_baru = $_POST['aksi'] == 'setujui' ? 'Disetujui' : 'Ditolak';
        
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);
        try {
            // 1. Update status di permintaan_barang
            $query_update_status = "UPDATE permintaan_barang SET status = '$status_baru', id_pengguna_staff = '$id_pengguna' WHERE id_permintaan = '$id_permintaan'";
            mysqli_query($koneksi, $query_update_status);

            // 2. Jika disetujui, lakukan pengurangan stok & catat transaksi
            if ($status_baru == 'Disetujui') {
                // Ambil semua item yang diminta
                $query_items = "SELECT kode_barang, jumlah_diminta FROM detail_permintaan WHERE id_permintaan = '$id_permintaan'";
                $hasil_items = mysqli_query($koneksi, $query_items);

                // Buat transaksi keluar baru
                $keterangan_transaksi = "Pengeluaran barang berdasarkan permintaan #" . $id_permintaan;
                $tgl_transaksi = date('Y-m-d H:i:s');
                $query_transaksi = "INSERT INTO transaksi (tanggal_transaksi, jenis_transaksi, keterangan, id_pengguna_staff, id_permintaan)
                                    VALUES ('$tgl_transaksi', 'Keluar', '$keterangan_transaksi', '$id_pengguna', '$id_permintaan')";
                mysqli_query($koneksi, $query_transaksi);
                $id_transaksi_baru = mysqli_insert_id($koneksi);

                while ($item = mysqli_fetch_assoc($hasil_items)) {
                    // Kurangi stok di tabel barang
                    $query_update_stok = "UPDATE barang SET jumlah_stok = jumlah_stok - {$item['jumlah_diminta']} WHERE kode_barang = '{$item['kode_barang']}'";
                    mysqli_query($koneksi, $query_update_stok);

                    // Catat di detail_transaksi
                    $query_detail_trans = "INSERT INTO detail_transaksi (id_transaksi, kode_barang, jumlah) VALUES ('$id_transaksi_baru', '{$item['kode_barang']}', '{$item['jumlah_diminta']}')";
                    mysqli_query($koneksi, $query_detail_trans);
                }
            }
            mysqli_commit($koneksi);
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            // Handle error, maybe log it or show a message
        }
        header("Location: permintaan-detail.php?id=$id_permintaan&status=aksi_sukses");
        exit;
    }
}


// Ambil data utama permintaan
$query_permintaan = "SELECT pb.*, p.nama AS nama_peminta 
                     FROM permintaan_barang pb 
                     JOIN pengguna p ON pb.id_pengguna_supervisor = p.id_pengguna 
                     WHERE pb.id_permintaan = '$id_permintaan'";
$hasil_permintaan = mysqli_query($koneksi, $query_permintaan);
$permintaan = mysqli_fetch_assoc($hasil_permintaan);

if (!$permintaan) { // Jika permintaan tidak ditemukan
    header('Location: permintaan-lihat.php');
    exit;
}

// Ambil detail barang yang diminta
$query_detail = "SELECT dp.*, b.nama_barang, b.satuan 
                 FROM detail_permintaan dp
                 JOIN barang b ON dp.kode_barang = b.kode_barang
                 WHERE dp.id_permintaan = '$id_permintaan'";
$hasil_detail = mysqli_query($koneksi, $query_detail);

// Pengaturan Header
$page_title = "Detail Permintaan #" . $permintaan['id_permintaan'];
$breadcrumbs = "Permintaan Barang / Detail";
$action_button = '';

require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">

        <!-- Info Utama Permintaan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-6 border-b" style="border-color: var(--border-color);">
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">ID Permintaan</p>
                <p class="text-lg font-bold">#<?php echo $permintaan['id_permintaan']; ?></p>
            </div>
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">Tanggal Permintaan</p>
                <p class="text-lg font-bold"><?php echo date('d F Y', strtotime($permintaan['tanggal_permintaan'])); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">Status</p>
                <?php
                    $status = $permintaan['status'];
                    $badge_color = 'bg-gray-200 text-gray-800'; // Default
                    if ($status == 'Pending') $badge_color = 'bg-yellow-200 text-yellow-800';
                    if ($status == 'Disetujui') $badge_color = 'bg-green-200 text-green-800';
                    if ($status == 'Ditolak') $badge_color = 'bg-red-200 text-red-800';
                ?>
                <span class="px-3 py-1 inline-flex text-md leading-5 font-semibold rounded-full <?php echo $badge_color; ?>">
                    <?php echo $status; ?>
                </span>
            </div>
            <div class="md:col-span-3">
                <p class="text-sm font-medium" style="color: var(--text-secondary);">Diminta oleh</p>
                <p class="text-lg font-bold"><?php echo htmlspecialchars($permintaan['nama_peminta']); ?></p>
            </div>
            <div class="md:col-span-3">
                <p class="text-sm font-medium" style="color: var(--text-secondary);">Catatan</p>
                <p class="text-lg"><?php echo htmlspecialchars($permintaan['catatan'] ? $permintaan['catatan'] : '-'); ?></p>
            </div>
        </div>

        <!-- Tabel Detail Barang -->
        <h3 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Rincian Barang yang Diminta</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border-color);">
                <thead style="background-color: var(--bg-main);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Kode Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Nama Barang</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Jumlah Diminta</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <?php while($item = mysqli_fetch_assoc($hasil_detail)): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold">
                            <?php echo htmlspecialchars($item['jumlah_diminta']) . ' ' . htmlspecialchars($item['satuan']); ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Tombol Aksi untuk Staff Gudang -->
        <?php if ($peran == 'staff-gudang' && $permintaan['status'] == 'Pending'): ?>
        <div class="flex justify-end gap-4 pt-8 mt-8 border-t" style="border-color: var(--border-color);">
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan ini?');">
                <input type="hidden" name="aksi" value="tolak">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg">Tolak</button>
            </form>
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui dan mengeluarkan barang-barang ini dari stok?');">
                <input type="hidden" name="aksi" value="setujui">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg">Setujui & Proses</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Tombol Kembali -->
         <div class="mt-8">
            <a href="permintaan-lihat.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Kembali ke Daftar Permintaan</a>
        </div>

    </div>
</main>

<?php
require_once '../templates/footer.php';
?>
