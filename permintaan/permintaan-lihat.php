<?php
// permintaan/permintaan-lihat.php

include '../auth_check.php';
include '../koneksi.php';

// Atur header dinamis berdasarkan peran
if ($peran == 'karyawan-toko') {
    $page_title = "Riwayat Permintaan Saya";
    $breadcrumbs = "Permintaan Barang";
    // PERUBAHAN: Tombol aksi di header dikosongkan
    $action_button = '';
    
    // Query untuk karyawan-toko: hanya lihat permintaan sendiri
    $query = "SELECT * FROM permintaan_barang WHERE id_pengguna_supervisor = '$id_pengguna' ORDER BY tanggal_permintaan DESC";

} else { // Asumsi peran lainnya adalah staff-gudang
    $page_title = "Verifikasi Permintaan";
    $breadcrumbs = "Manajemen Gudang / Verifikasi";
    $action_button = '';
    
    // Query untuk staff-gudang: lihat semua permintaan dan nama pemintanya
    $query = "SELECT pb.*, p.nama as nama_peminta 
              FROM permintaan_barang pb
              JOIN pengguna p ON pb.id_pengguna_supervisor = p.id_pengguna
              ORDER BY pb.tanggal_permintaan DESC";
}

$hasil = mysqli_query($koneksi, $query);

require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        
        <?php if ($peran == 'karyawan-toko'): ?>
        <!-- PERUBAHAN: Tombol Tambah dipindahkan ke sini, hanya muncul untuk Karyawan Toko -->
        <div class="flex justify-end mb-6">
            <a href="permintaan-tambah.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Tambah Permintaan</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border-color);">
                <thead style="background-color: var(--bg-main);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Tanggal</th>
                        <?php if ($peran == 'staff-gudang'): ?>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase">Peminta</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Catatan</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <?php if(mysqli_num_rows($hasil) > 0): ?>
                        <?php while($data = mysqli_fetch_assoc($hasil)): ?>
                        <tr class="theme-aware-hover">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#<?php echo $data['id_permintaan']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo date('d M Y', strtotime($data['tanggal_permintaan'])); ?></td>
                            <?php if ($peran == 'staff-gudang'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($data['nama_peminta']); ?></td>
                            <?php endif; ?>
                            <td class="px-6 py-4 text-sm max-w-xs truncate"><?php echo htmlspecialchars($data['catatan'] ? $data['catatan'] : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <?php
                                    $status = $data['status'];
                                    $badge_color = 'bg-gray-200 text-gray-800'; // Default
                                    if ($status == 'Pending') $badge_color = 'bg-yellow-200 text-yellow-800';
                                    if ($status == 'Disetujui') $badge_color = 'bg-green-200 text-green-800';
                                    if ($status == 'Ditolak') $badge_color = 'bg-red-200 text-red-800';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badge_color; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="permintaan-detail.php?id=<?php echo $data['id_permintaan']; ?>" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($peran == 'staff-gudang' ? 6 : 5); ?>" class="px-6 py-4 text-center text-sm" style="color: var(--text-muted);">
                                Tidak ada data permintaan.
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
