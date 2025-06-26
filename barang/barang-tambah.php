<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$peran_pengguna = $_SESSION['peran'];

// 3. Pastikan hanya staff yang bisa mengakses halaman ini
if ($peran_pengguna != 'staff') {
    die("Error: Anda tidak memiliki hak akses untuk halaman ini.");
}

// 4. Proses form jika ada data yang dikirim (method POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $satuan = $_POST['satuan'];
    $jumlah_stok = $_POST['jumlah_stok'];

    // Validasi sederhana (bisa ditambahkan validasi lain jika perlu)
    if (!empty($kode_barang) && !empty($nama_barang) && !empty($satuan)) {
        
        // Siapkan kueri INSERT yang aman dengan prepared statements
        $sql = "INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, jumlah_stok) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Ikat parameter ke statement
            mysqli_stmt_bind_param($stmt, "ssssi", $kode_barang, $nama_barang, $kategori, $satuan, $jumlah_stok);
            
            // Eksekusi statement
            if (mysqli_stmt_execute($stmt)) {
                // Jika berhasil, arahkan kembali ke halaman lihat dengan pesan sukses
                header("location: barang-lihat.php?status=tambah_sukses");
                exit();
            } else {
                echo "Error: Gagal menambahkan data. " . mysqli_error($conn);
            }
            
            // Tutup statement
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Kode Barang, Nama Barang, dan Satuan wajib diisi.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <title>Tambah Barang Baru</title>
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
        
        <!-- Menu Sidebar Dinamis untuk Staff -->
        <ul class="list-unstyled components mb-5">
          <li><a href="../home.php">Home</a></li>
          <li><a href="../permintaan/permintaan-lihat.php">Verifikasi Permintaan</a></li>
          <li class="active"><a href="barang-lihat.php">Master Barang</a></li>
          <li><a href="../transaksi/transaksi-lihat.php">Transaksi Stok</a></li>
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

      <h2 class="mb-4">Tambah Barang Baru</h2>

      <div class="card">
        <div class="card-body">
          <?php 
            if(!empty($error_message)){
                echo "<div class='alert alert-danger'>$error_message</div>";
            }
          ?>
          <form action="barang-tambah.php" method="POST">
            <div class="form-group">
              <label for="kode_barang">Kode Barang</label>
              <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Contoh: ATK-001" required>
            </div>
            <div class="form-group">
              <label for="nama_barang">Nama Barang</label>
              <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Masukkan nama barang" required>
            </div>
            <div class="form-group">
              <label for="kategori">Kategori</label>
              <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Contoh: Alat Tulis Kantor">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="jumlah_stok">Jumlah Stok Awal</label>
                    <input type="number" class="form-control" id="jumlah_stok" name="jumlah_stok" value="0" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="satuan">Satuan</label>
                    <input type="text" class="form-control" id="satuan" name="satuan" placeholder="Contoh: pcs, box, rim" required>
                </div>
            </div>
            
            <div class="mt-4">
              <button type="submit" class="btn btn-success">Simpan Barang</button>
              <a href="barang-lihat.php" class="btn btn-secondary">Batal</a>
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
</body>
</html>
