<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$id_supervisor = $_SESSION['id_pengguna'];
$peran_pengguna = $_SESSION['peran'];
$nama_pengguna = $_SESSION['username'];

// 3. Pastikan hanya supervisor yang bisa mengakses halaman ini
if ($peran_pengguna != 'supervisor') {
    // Jika bukan supervisor, tendang ke halaman home
    header("location: ../home.php");
    exit;
}

// 4. LOGIKA UNTUK MEMPROSES FORM SAAT DI-SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form
    $tanggal_permintaan = $_POST['tanggal_permintaan'];
    $catatan_permintaan = $_POST['catatan'];
    
    // Mulai transaksi database untuk memastikan semua query berhasil
    mysqli_begin_transaction($conn);

    try {
        // Langkah A: Insert data ke tabel `permintaan_barang`
        $sql_permintaan = "INSERT INTO permintaan_barang (tanggal_permintaan, catatan, id_pengguna_supervisor, status) VALUES (?, ?, ?, 'Pending')";
        
        $stmt_permintaan = mysqli_prepare($conn, $sql_permintaan);
        mysqli_stmt_bind_param($stmt_permintaan, "ssi", $tanggal_permintaan, $catatan_permintaan, $id_supervisor);
        mysqli_stmt_execute($stmt_permintaan);
        
        // Ambil ID permintaan yang baru saja dibuat
        $id_permintaan_baru = mysqli_insert_id($conn);
        
        // Langkah B: Insert data ke tabel `detail_permintaan` untuk setiap barang
        $kode_barang_list = $_POST['kode_barang'];
        $jumlah_list = $_POST['jumlah'];
        
        $sql_detail = "INSERT INTO detail_permintaan (id_permintaan, kode_barang, jumlah_diminta) VALUES (?, ?, ?)";
        $stmt_detail = mysqli_prepare($conn, $sql_detail);
        
        for ($i = 0; $i < count($kode_barang_list); $i++) {
            $kode_barang = $kode_barang_list[$i];
            $jumlah = $jumlah_list[$i];
            
            // Hanya proses jika kode barang dan jumlah diisi
            if (!empty($kode_barang) && !empty($jumlah) && $jumlah > 0) {
                mysqli_stmt_bind_param($stmt_detail, "isi", $id_permintaan_baru, $kode_barang, $jumlah);
                mysqli_stmt_execute($stmt_detail);
            }
        }
        
        // Jika semua query berhasil, commit transaksi
        mysqli_commit($conn);
        
        // Arahkan kembali ke halaman lihat dengan status sukses
        header("location: permintaan-lihat.php?status=tambah_sukses");

    } catch (mysqli_sql_exception $exception) {
        // Jika ada error, batalkan semua perubahan (rollback)
        mysqli_rollback($conn);
        echo "Error: Gagal menyimpan data. " . $exception->getMessage();
    }
    
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <title>Buat Permintaan Barang</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
      <div class="p-4 pt-5">
        <a href="#" class="img logo rounded-circle mb-5" style="background-image: url(../images/bengkel.png);"></a>
        
        <!-- Menu Sidebar Dinamis -->
        <ul class="list-unstyled components mb-5">
          <li><a href="../home.php">Home</a></li>
          <li class="active"><a href="permintaan-lihat.php">Permintaan Barang</a></li>
          <li><a href="../laporan/laporan-lihat.php">Laporan</a></li>
          <li><a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Logout</a></li>
        </ul>

        <div class="footer">
          <p>Gudang &copy;<script>document.write(new Date().getFullYear());</script></p>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <div id="content" class="p-4 p-md-5">
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
          </button>
        </div>
      </nav>

      <h2 class="mb-4">Buat Permintaan Barang Baru</h2>
      
      <div class="card">
        <div class="card-body">
          <form action="permintaan-tambah.php" method="POST" id="form-permintaan">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="tanggal_permintaan">Tanggal Permintaan</label>
                <input type="date" class="form-control" id="tanggal_permintaan" name="tanggal_permintaan" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
              <div class="form-group col-md-6">
                <label for="nama_supervisor">Nama Peminta</label>
                <input type="text" class="form-control" id="nama_supervisor" value="<?php echo htmlspecialchars($nama_pengguna); ?>" readonly>
              </div>
            </div>
            
            <hr>
            
            <h4>Daftar Barang yang Diminta</h4>
            <div id="daftar-barang">
              <!-- Baris pertama untuk item barang -->
              <div class="form-row align-items-end barang-item mb-2">
                <div class="form-group col-md-6">
                  <label>Barang</label>
                  <select name="kode_barang[]" class="form-control" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php
                      $query_barang = "SELECT kode_barang, nama_barang, jumlah_stok FROM barang ORDER BY nama_barang";
                      $result_barang = mysqli_query($conn, $query_barang);
                      while ($barang = mysqli_fetch_assoc($result_barang)) {
                        echo "<option value='{$barang['kode_barang']}'>{$barang['nama_barang']} (Stok: {$barang['jumlah_stok']})</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="form-group col-md-4">
                  <label>Jumlah</label>
                  <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="1" required>
                </div>
                <div class="form-group col-md-2">
                  <button type="button" class="btn btn-danger btn-block" disabled>Hapus</button>
                </div>
              </div>
            </div>

            <button type="button" id="tambah-barang" class="btn btn-primary mt-2">Tambah Barang Lain</button>

            <hr>

            <div class="form-group">
              <label for="catatan">Catatan Tambahan</label>
              <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
            </div>
            
            <div class="mt-4">
              <button type="submit" class="btn btn-success">Kirim Permintaan</button>
              <a href="permintaan-lihat.php" class="btn btn-secondary">Batal</a>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const daftarBarang = document.getElementById('daftar-barang');
      const tambahBarangBtn = document.getElementById('tambah-barang');

      // Fungsi untuk menghapus baris barang
      function hapusBaris(event) {
        if (event.target.classList.contains('btn-danger')) {
          event.target.closest('.barang-item').remove();
        }
      }

      daftarBarang.addEventListener('click', hapusBaris);

      tambahBarangBtn.addEventListener('click', function() {
        // Kloning baris barang pertama
        const barisPertama = daftarBarang.querySelector('.barang-item');
        const barisBaru = barisPertama.cloneNode(true);
        
        // Reset nilai input pada baris baru
        barisBaru.querySelector('select').selectedIndex = 0;
        barisBaru.querySelector('input[type="number"]').value = '';
        
        // Aktifkan tombol hapus
        const tombolHapus = barisBaru.querySelector('.btn-danger');
        tombolHapus.disabled = false;
        
        daftarBarang.appendChild(barisBaru);
      });
    });
  </script>
</body>
</html>
