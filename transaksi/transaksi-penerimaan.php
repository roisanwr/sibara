<?php
// transaksi/transaksi-penerimaan.php

include '../auth_check.php';
include '../koneksi.php';

// Hanya 'staff-gudang' yang boleh mengakses
if ($peran != 'staff-gudang') {
    header("location:../home.php?status=gagal_akses");
    exit;
}

// Ambil semua data barang untuk dropdown
$query_barang = "SELECT * FROM barang ORDER BY nama_barang ASC";
$hasil_barang = mysqli_query($koneksi, $query_barang);

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $tgl_transaksi = date('Y-m-d H:i:s');
    
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert ke tabel transaksi
        $query_transaksi = "INSERT INTO transaksi (tanggal_transaksi, jenis_transaksi, keterangan, id_pengguna_staff) 
                            VALUES ('$tgl_transaksi', 'Masuk', '$keterangan', '$id_pengguna')";
        mysqli_query($koneksi, $query_transaksi);
        $id_transaksi_baru = mysqli_insert_id($koneksi);

        // 2. Loop item, update stok & insert ke detail_transaksi
        $kode_barangs = $_POST['kode_barang'];
        $jumlahs = $_POST['jumlah'];

        foreach ($kode_barangs as $index => $kode_barang) {
            $jumlah = (int)$jumlahs[$index];
            if (!empty($kode_barang) && $jumlah > 0) {
                // Update stok di tabel barang (tambah stok)
                mysqli_query($koneksi, "UPDATE barang SET jumlah_stok = jumlah_stok + $jumlah WHERE kode_barang = '$kode_barang'");
                // Insert ke detail transaksi
                mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, kode_barang, jumlah) VALUES ('$id_transaksi_baru', '$kode_barang', '$jumlah')");
            }
        }
        
        mysqli_commit($koneksi);
        header('Location: transaksi-lihat.php?status=penerimaan_sukses');
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

$page_title = "Penerimaan Barang";
$breadcrumbs = "Penerimaan Barang / Tambah Penerimaan";
$action_button = '';
require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        <form action="transaksi-penerimaan.php" method="POST">
            <div class="mb-6">
                <label for="keterangan" class="block text-sm font-medium" style="color: var(--text-secondary);">Keterangan (Opsional)</label>
                <input type="text" id="keterangan" name="keterangan" class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400" style="background-color: var(--bg-main); border-color: var(--border-color);" placeholder="Contoh: Penerimaan dari Supplier A">
            </div>

            <h3 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Barang yang Diterima</h3>
            <div id="daftar-barang" class="space-y-4">
                <div class="flex items-center gap-4 p-4 rounded-lg" style="background-color: var(--bg-main);">
                    <div class="flex-grow">
                        <label class="block text-sm font-medium">Pilih Barang</label>
                        <select name="kode_barang[]" class="mt-1 block w-full px-4 py-3 rounded-lg border" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php mysqli_data_seek($hasil_barang, 0); while($barang = mysqli_fetch_assoc($hasil_barang)): ?>
                                <option value="<?php echo $barang['kode_barang']; ?>"><?php echo htmlspecialchars($barang['nama_barang']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="w-1/4">
                        <label class="block text-sm font-medium">Jumlah</label>
                        <input type="number" name="jumlah[]" class="mt-1 block w-full px-4 py-3 rounded-lg border" min="1" required>
                    </div>
                    <div class="self-end"><button type="button" class="hapus-barang-btn bg-red-500 text-white p-3 rounded-lg" style="display: none;">Hapus</button></div>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" id="tambah-barang-btn" class="text-sm font-medium text-orange-600 hover:text-orange-800">Tambah Barang Lain</button>
            </div>
            
            <div class="flex justify-end gap-4 pt-8 mt-8 border-t" style="border-color: var(--border-color);">
                <a href="transaksi-lihat.php" class="px-6 py-2 rounded-lg" style="background-color: var(--bg-hover);">Batal</a>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
</main>

<script>
// Script ini sama persis dengan di permintaan-tambah.php
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('daftar-barang');
    const addButton = document.getElementById('tambah-barang-btn');
    const template = container.children[0].cloneNode(true);
    template.querySelector('select').value = '';
    template.querySelector('input').value = '';

    function updateRemoveButtons() {
        const rows = container.children;
        for (let i = 0; i < rows.length; i++) {
            const removeBtn = rows[i].querySelector('.hapus-barang-btn');
            removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
        }
    }

    addButton.addEventListener('click', () => {
        container.appendChild(template.cloneNode(true));
        updateRemoveButtons();
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('hapus-barang-btn')) {
            e.target.parentElement.parentElement.remove();
            updateRemoveButtons();
        }
    });
    updateRemoveButtons();
});
</script>

<?php
require_once '../templates/footer.php';
?>
