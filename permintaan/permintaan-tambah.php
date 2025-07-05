<?php
// permintaan/permintaan-tambah.php

include '../auth_check.php';
include '../koneksi.php';

// Hanya 'karyawan-toko' yang boleh mengakses
if ($peran != 'karyawan-toko') {
    header("location:../home.php?status=gagal_akses");
    exit;
}

// Ambil semua data barang untuk dropdown
$query_barang = "SELECT * FROM barang ORDER BY nama_barang ASC";
$hasil_barang = mysqli_query($koneksi, $query_barang);

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $tanggal_permintaan = date('Y-m-d');
    
    // Mulai transaksi database
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert ke tabel permintaan_barang
        $query_permintaan = "INSERT INTO permintaan_barang (tanggal_permintaan, catatan, status, id_pengguna_supervisor) 
                             VALUES ('$tanggal_permintaan', '$catatan', 'Pending', '$id_pengguna')";
        $hasil_permintaan = mysqli_query($koneksi, $query_permintaan);

        if (!$hasil_permintaan) {
            throw new Exception("Gagal menyimpan data permintaan utama.");
        }

        // Ambil ID permintaan yang baru saja dibuat
        $id_permintaan_baru = mysqli_insert_id($koneksi);

        // 2. Insert ke tabel detail_permintaan (bisa lebih dari satu barang)
        $kode_barangs = $_POST['kode_barang'];
        $jumlahs = $_POST['jumlah'];

        foreach ($kode_barangs as $index => $kode_barang) {
            $jumlah = $jumlahs[$index];
            if (!empty($kode_barang) && $jumlah > 0) {
                $query_detail = "INSERT INTO detail_permintaan (id_permintaan, kode_barang, jumlah_diminta) 
                                 VALUES ('$id_permintaan_baru', '$kode_barang', '$jumlah')";
                $hasil_detail = mysqli_query($koneksi, $query_detail);
                if (!$hasil_detail) {
                    throw new Exception("Gagal menyimpan detail barang.");
                }
            }
        }
        
        // Jika semua query berhasil, commit transaksi
        mysqli_commit($koneksi);
        header('Location: permintaan-lihat.php?status=tambah_sukses');
        exit;

    } catch (Exception $e) {
        // Jika ada error, rollback semua perubahan
        mysqli_rollback($koneksi);
        $error_message = $e->getMessage();
    }
}


// Pengaturan Header Dinamis
$page_title = "Buat Permintaan Barang Baru";
$breadcrumbs = "Permintaan Barang / Buat Baru";
$action_button = '';

require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        <form action="permintaan-tambah.php" method="POST" id="form-permintaan">
            <!-- Catatan -->
            <div class="mb-6">
                <label for="catatan" class="block text-sm font-medium" style="color: var(--text-secondary);">Catatan (Opsional)</label>
                <textarea id="catatan" name="catatan" rows="3" class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400" style="background-color: var(--bg-main); border-color: var(--border-color);" placeholder="Contoh: Butuh cepat untuk acara hari Senin"></textarea>
            </div>

            <!-- Daftar Barang yang Diminta -->
            <h3 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Barang yang Diminta</h3>
            <div id="daftar-barang" class="space-y-4">
                <!-- Baris pertama untuk item barang -->
                <div class="flex items-center gap-4 p-4 rounded-lg" style="background-color: var(--bg-main);">
                    <div class="flex-grow">
                        <label class="block text-sm font-medium" style="color: var(--text-secondary);">Pilih Barang</label>
                        <select name="kode_barang[]" class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php while($barang = mysqli_fetch_assoc($hasil_barang)): ?>
                                <option value="<?php echo $barang['kode_barang']; ?>"><?php echo htmlspecialchars($barang['nama_barang']); ?> (Stok: <?php echo $barang['jumlah_stok']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="w-1/4">
                        <label class="block text-sm font-medium" style="color: var(--text-secondary);">Jumlah</label>
                        <input type="number" name="jumlah[]" class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400" min="1" required>
                    </div>
                    <div class="self-end">
                        <button type="button" class="hapus-barang-btn bg-red-500 hover:bg-red-600 text-white font-bold p-3 rounded-lg flex items-center justify-center transition-colors duration-200" style="display: none;">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tombol untuk menambah baris barang -->
            <div class="mt-4">
                <button type="button" id="tambah-barang-btn" class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tambah Barang Lain
                </button>
            </div>
            
            <!-- Tombol Aksi Form -->
            <div class="flex justify-end gap-4 pt-8 mt-8 border-t" style="border-color: var(--border-color);">
                <a href="permintaan-lihat.php" class="px-6 py-2 rounded-lg" style="background-color: var(--bg-hover); color: var(--text-primary);">Batal</a>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                    Kirim Permintaan
                </button>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const daftarBarangContainer = document.getElementById('daftar-barang');
    const tambahBarangBtn = document.getElementById('tambah-barang-btn');

    // Template untuk baris barang baru
    const barisBarangTemplate = daftarBarangContainer.children[0].cloneNode(true);
    // Reset nilai input pada template
    barisBarangTemplate.querySelector('select').value = '';
    barisBarangTemplate.querySelector('input').value = '';

    function updateHapusButtons() {
        const barisBarang = daftarBarangContainer.querySelectorAll('.flex');
        barisBarang.forEach((baris, index) => {
            const hapusBtn = baris.querySelector('.hapus-barang-btn');
            if (barisBarang.length > 1) {
                hapusBtn.style.display = 'flex';
            } else {
                hapusBtn.style.display = 'none';
            }
        });
    }

    tambahBarangBtn.addEventListener('click', function() {
        const barisBaru = barisBarangTemplate.cloneNode(true);
        daftarBarangContainer.appendChild(barisBaru);
        updateHapusButtons();
    });

    daftarBarangContainer.addEventListener('click', function(e) {
        if (e.target && e.target.closest('.hapus-barang-btn')) {
            e.target.closest('.flex').remove();
            updateHapusButtons();
        }
    });
    
    // Inisialisasi tombol hapus saat halaman dimuat
    updateHapusButtons();
});
</script>

<?php
require_once '../templates/footer.php';
?>
