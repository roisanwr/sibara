<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$id_staff = $_SESSION['id_pengguna'];
$peran_pengguna = $_SESSION['peran'];
$nama_pengguna = $_SESSION['username'];

// 3. Pastikan hanya staff yang bisa mengakses halaman ini
if ($peran_pengguna != 'staff') {
    header("location: ../home.php");
    exit;
}

// 4. LOGIKA UNTUK MEMPROSES FORM SAAT DI-SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $keterangan = $_POST['keterangan'];
    
    // Mulai transaksi database untuk memastikan semua query berhasil
    mysqli_begin_transaction($conn);

    try {
        // Langkah A: Insert data ke tabel `transaksi`
        $sql_transaksi = "INSERT INTO transaksi (tanggal_transaksi, jenis_transaksi, keterangan, id_pengguna_staff) VALUES (?, 'Masuk', ?, ?)";
        
        $stmt_transaksi = mysqli_prepare($conn, $sql_transaksi);
        mysqli_stmt_bind_param($stmt_transaksi, "ssi", $tanggal_transaksi, $keterangan, $id_staff);
        mysqli_stmt_execute($stmt_transaksi);
        
        // Ambil ID transaksi yang baru saja dibuat
        $id_transaksi_baru = mysqli_insert_id($conn);
        
        // Langkah B: Proses setiap barang yang diterima
        $kode_barang_list = $_POST['kode_barang'];
        $jumlah_list = $_POST['jumlah'];
        
        // Siapkan statement untuk detail transaksi dan update stok
        $sql_detail = "INSERT INTO detail_transaksi (id_transaksi, kode_barang, jumlah) VALUES (?, ?, ?)";
        $stmt_detail = mysqli_prepare($conn, $sql_detail);

        $sql_update_stok = "UPDATE barang SET jumlah_stok = jumlah_stok + ? WHERE kode_barang = ?";
        $stmt_update_stok = mysqli_prepare($conn, $sql_update_stok);
        
        for ($i = 0; $i < count($kode_barang_list); $i++) {
            $kode_barang = $kode_barang_list[$i];
            $jumlah = (int)$jumlah_list[$i];
            
            if (!empty($kode_barang) && !empty($jumlah) && $jumlah > 0) {
                // Insert ke detail_transaksi
                mysqli_stmt_bind_param($stmt_detail, "isi", $id_transaksi_baru, $kode_barang, $jumlah);
                mysqli_stmt_execute($stmt_detail);

                // Update jumlah stok di tabel barang
                mysqli_stmt_bind_param($stmt_update_stok, "is", $jumlah, $kode_barang);
                mysqli_stmt_execute($stmt_update_stok);
            }
        }
        
        // Jika semua query berhasil, commit transaksi
        mysqli_commit($conn);
        
        // Arahkan kembali ke halaman lihat dengan status sukses
        header("location: transaksi-lihat.php?status=penerimaan_sukses");

    } catch (mysqli_sql_exception $exception) {
        // Jika ada error, batalkan semua perubahan (rollback)
        mysqli_rollback($conn);
        echo "Error: Gagal menyimpan data transaksi. " . $exception->getMessage();
    }
    
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <title>Catat Penerimaan Barang</title>
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
        
        <ul class="list-unstyled components mb-5">
          <li><a href="../home.php">Home</a></li>
          <li><a href="../permintaan/permintaan-lihat.php">Verifikasi Permintaan</a></li>
          <li><a href="../barang/barang-lihat.php">Master Barang</a></li>
          <li class="active"><a href="transaksi-lihat.php">Transaksi Stok</a></li>
          <li><a href="../pengguna/pengguna-lihat.php">Kelola Pengguna</a></li>
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

      <h2 class="mb-4">Catat Penerimaan Barang Baru</h2>
      
      <div class="card">
        <div class="card-body">
          <form action="transaksi-penerimaan.php" method="POST">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="tanggal_transaksi">Tanggal Penerimaan</label>
                <input type="datetime-local" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
              </div>
              <div class="form-group col-md-6">
                <label for="nama_staff">Dicatat oleh</label>
                <input type="text" class="form-control" id="nama_staff" value="<?php echo htmlspecialchars($nama_pengguna); ?>" readonly>
              </div>
            </div>
             <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Contoh: Penerimaan dari Supplier ABC" required>
            </div>
            
            <hr>
            
            <h4>Daftar Barang yang Diterima</h4>
            <div id="daftar-barang">
              <!-- Baris pertama untuk item barang -->
              <div class="form-row align-items-end barang-item mb-2">
                <div class="form-group col-md-6">
                  <label>Barang</label>
                  <select name="kode_barang[]" class="form-control" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php
                      $query_barang = "SELECT kode_barang, nama_barang FROM barang ORDER BY nama_barang";
                      $result_barang = mysqli_query($conn, $query_barang);
                      while ($barang = mysqli_fetch_assoc($result_barang)) {
                        echo "<option value='{$barang['kode_barang']}'>{$barang['nama_barang']}</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="form-group col-md-4">
                  <label>Jumlah Diterima</label>
                  <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="1" required>
                </div>
                <div class="form-group col-md-2">
                  <button type="button" class="btn btn-danger btn-block" disabled>Hapus</button>
                </div>
              </div>
            </div>

            <button type="button" id="tambah-barang" class="btn btn-primary mt-2">Tambah Barang Lain</button>

            <hr>
            
            <div class="mt-4">
              <button type="submit" class="btn btn-success">Simpan Transaksi</button>
              <a href="transaksi-lihat.php" class="btn btn-secondary">Batal</a>
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
    // Script JS ini sama persis dengan yang ada di permintaan-tambah.php
    document.addEventListener('DOMContentLoaded', function() {
      const daftarBarang = document.getElementById('daftar-barang');
      const tambahBarangBtn = document.getElementById('tambah-barang');

      function hapusBaris(event) {
        if (event.target.classList.contains('btn-danger')) {
          if (daftarBarang.querySelectorAll('.barang-item').length > 1) {
            event.target.closest('.barang-item').remove();
          }
        }
      }

      daftarBarang.addEventListener('click', hapusBaris);

      tambahBarangBtn.addEventListener('click', function() {
        const barisContoh = daftarBarang.querySelector('.barang-item');
        const barisBaru = barisContoh.cloneNode(true);
        
        barisBaru.querySelector('select').selectedIndex = 0;
        barisBaru.querySelector('input[type="number"]').value = '';
        
        const tombolHapus = barisBaru.querySelector('.btn-danger');
        tombolHapus.disabled = false;
        
        daftarBarang.appendChild(barisBaru);
      });
    });
  </script>
</body>
</html>
