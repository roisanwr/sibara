<?php
// barang/barang-ubah.php

include '../auth_check.php';
include '../koneksi.php';

// Hanya 'staff-gudang' yang boleh mengakses
if ($peran != 'staff-gudang') {
    header("location:../home.php?status=gagal_akses");
    exit;
}

// Cek apakah ada 'kode' di URL, jika tidak, tendang kembali
if (!isset($_GET['kode'])) {
    header('Location: barang-lihat.php');
    exit;
}

$kode_barang = $_GET['kode'];

// Ambil data barang yang akan diubah dari database
$query = "SELECT * FROM barang WHERE kode_barang = '$kode_barang'";
$hasil = mysqli_query($koneksi, $query);
$barang = mysqli_fetch_assoc($hasil);

// Jika barang dengan kode tersebut tidak ditemukan, tendang kembali
if (!$barang) {
    header('Location: barang-lihat.php');
    exit;
}

// Proses saat form disubmit (METHOD POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan amankan
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    
    // Query hanya mengupdate data master, BUKAN stok.
    $update_query = "UPDATE barang SET 
                        nama_barang = '$nama_barang', 
                        kategori = '$kategori', 
                        satuan = '$satuan' 
                     WHERE kode_barang = '$kode_barang'";

    $update_hasil = mysqli_query($koneksi, $update_query);

    if ($update_hasil) {
        // Jika berhasil, redirect kembali ke halaman lihat dengan pesan sukses
        header('Location: barang-lihat.php?status=update_sukses');
    } else {
        // Jika gagal, bisa ditambahkan pesan error
        $error_message = "Terjadi kesalahan saat memperbarui data.";
    }
    exit;
}

// =========================================================
// PENGATURAN UNTUK HEADER DINAMIS
// =========================================================
$page_title = "Ubah Data Barang";
$breadcrumbs = "Master Barang / Ubah";
$action_button = ''; // Tidak ada tombol aksi di header
// =========================================================

// Panggil template
require_once '../templates/header.php';
?>

<main class="flex-1 p-4 lg:p-8">
    <div class="stat-card p-6 rounded-xl soft-shadow">
        
        <?php if (isset($error_message)): ?>
        <!-- Pesan Error -->
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $error_message; ?></span>
        </div>
        <?php endif; ?>

        <form action="barang-ubah.php?kode=<?php echo $kode_barang; ?>" method="POST" class="space-y-6">

            <div>
                <label for="kode_barang" class="block text-sm font-medium" style="color: var(--text-secondary);">Kode Barang (Tidak bisa diubah)</label>
                <input type="text" id="kode_barang" name="kode_barang" value="<?php echo htmlspecialchars($barang['kode_barang']); ?>" readonly
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none"
                       style="background-color: var(--bg-hover); border-color: var(--border-color); cursor: not-allowed;">
            </div>

            <div>
                <label for="nama_barang" class="block text-sm font-medium" style="color: var(--text-secondary);">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" required
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: Pulpen Standard AE7">
            </div>

            <div>
                <label for="kategori" class="block text-sm font-medium" style="color: var(--text-secondary);">Kategori</label>
                <input type="text" id="kategori" name="kategori" value="<?php echo htmlspecialchars($barang['kategori']); ?>"
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: ATK">
            </div>
            
            <div>
                <label for="satuan" class="block text-sm font-medium" style="color: var(--text-secondary);">Satuan</label>
                <input type="text" id="satuan" name="satuan" value="<?php echo htmlspecialchars($barang['satuan']); ?>" required
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: PCS, Lusin, Box">
            </div>

            <div>
                <label for="jumlah_stok" class="block text-sm font-medium" style="color: var(--text-secondary);">Jumlah Stok (Tidak bisa diubah di sini)</label>
                <input type="number" id="jumlah_stok" name="jumlah_stok" value="<?php echo htmlspecialchars($barang['jumlah_stok']); ?>" readonly
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none"
                       style="background-color: var(--bg-hover); border-color: var(--border-color); cursor: not-allowed;">
            </div>


            <div class="flex justify-end gap-4 pt-4">
                <a href="barang-lihat.php" class="px-6 py-2 rounded-lg" style="background-color: var(--bg-hover); color: var(--text-primary);">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</main>

<?php
require_once '../templates/footer.php';
?>
