<?php
// barang/barang-tambah.php

include '../auth_check.php';
include '../koneksi.php';

// Hanya 'staff-gudang' yang boleh mengakses
if ($peran != 'staff-gudang') {
    header("location:../home.php?status=gagal_akses");
    exit;
}

// Proses saat form disubmit (METHOD POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan amankan
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $jumlah_stok = mysqli_real_escape_string($koneksi, $_POST['jumlah_stok']);
    
    // Cek apakah kode barang sudah ada
    $cek_kode_query = "SELECT kode_barang FROM barang WHERE kode_barang = '$kode_barang'";
    $cek_kode_hasil = mysqli_query($koneksi, $cek_kode_query);

    if (mysqli_num_rows($cek_kode_hasil) > 0) {
        // Jika kode barang sudah ada, siapkan pesan error
        $error_message = "Gagal menambahkan! Kode barang '$kode_barang' sudah digunakan.";
    } else {
        // Jika kode barang belum ada, lanjutkan proses insert
        $insert_query = "INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, jumlah_stok) 
                         VALUES ('$kode_barang', '$nama_barang', '$kategori', '$satuan', '$jumlah_stok')";

        $insert_hasil = mysqli_query($koneksi, $insert_query);

        if ($insert_hasil) {
            // Jika berhasil, redirect kembali ke halaman lihat dengan pesan sukses
            header('Location: barang-lihat.php?status=tambah_sukses');
            exit;
        } else {
            // Jika gagal insert, siapkan pesan error
            $error_message = "Terjadi kesalahan saat menyimpan data ke database.";
        }
    }
}

// =========================================================
// PENGATURAN UNTUK HEADER DINAMIS
// =========================================================
$page_title = "Tambah Barang Baru";
$breadcrumbs = "Data Barang / Tambah";
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

        <form action="barang-tambah.php" method="POST" class="space-y-6">

            <div>
                <label for="kode_barang" class="block text-sm font-medium" style="color: var(--text-secondary);">Kode Barang</label>
                <input type="text" id="kode_barang" name="kode_barang" required
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: ATK001">
            </div>

            <div>
                <label for="nama_barang" class="block text-sm font-medium" style="color: var(--text-secondary);">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: Pulpen Standard AE7">
            </div>

            <div>
                <label for="kategori" class="block text-sm font-medium" style="color: var(--text-secondary);">Kategori</label>
                <input type="text" id="kategori" name="kategori"
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: ATK, Elektronik, dll.">
            </div>
            
            <div>
                <label for="satuan" class="block text-sm font-medium" style="color: var(--text-secondary);">Satuan</label>
                <input type="text" id="satuan" name="satuan" required
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);"
                       placeholder="Contoh: PCS, Lusin, Box">
            </div>

            <div>
                <label for="jumlah_stok" class="block text-sm font-medium" style="color: var(--text-secondary);">Jumlah Stok Awal</label>
                <input type="number" id="jumlah_stok" name="jumlah_stok" required value="0" min="0"
                       class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                       style="background-color: var(--bg-main); border-color: var(--border-color);">
            </div>

            <div class="flex justify-end gap-4 pt-4">
                <a href="barang-lihat.php" class="px-6 py-2 rounded-lg" style="background-color: var(--bg-hover); color: var(--text-primary);">Batal</a>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                    Simpan Barang
                </button>
            </div>

        </form>
    </div>
</main>

<?php
require_once '../templates/footer.php';
?>
