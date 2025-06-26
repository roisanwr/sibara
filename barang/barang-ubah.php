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

// 4. Ambil kode_barang dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: barang-lihat.php");
    exit;
}
$kode_barang_edit = $_GET['id'];

// 5. Proses form jika ada data yang dikirim (method POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_barang = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $satuan = $_POST['satuan'];
    
    // Siapkan kueri UPDATE yang aman
    $sql = "UPDATE barang SET nama_barang = ?, kategori = ?, satuan = ? WHERE kode_barang = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Ikat parameter
        mysqli_stmt_bind_param($stmt, "ssss", $nama_barang, $kategori, $satuan, $kode_barang_edit);
        
        // Eksekusi
        if (mysqli_stmt_execute($stmt)) {
            header("location: barang-lihat.php?status=edit_sukses");
            exit();
        } else {
            echo "Error: Gagal memperbarui data.";
        }
        mysqli_stmt_close($stmt);
    }
}

// 6. Ambil data lama barang untuk ditampilkan di form
$sql_select = "SELECT * FROM barang WHERE kode_barang = ?";
if ($stmt_select = mysqli_prepare($conn, $sql_select)) {
    mysqli_stmt_bind_param($stmt_select, "s", $kode_barang_edit);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    
    if(mysqli_num_rows($result) == 1){
        $barang = mysqli_fetch_assoc($result);
    } else {
        // Jika data tidak ditemukan, kembali ke halaman lihat
        header("location: barang-lihat.php");
        exit();
    }
    mysqli_stmt_close($stmt_select);
}
?>
<!doctype html>
<html lang="id">
<head>
  <title>Ubah Data Barang</title>
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

      <h2 class="mb-4">Ubah Data Barang</h2>

      <div class="card">
        <div class="card-body">
          <form action="barang-ubah.php?id=<?php echo htmlspecialchars($kode_barang_edit); ?>" method="POST">
            <div class="form-group">
              <label for="kode_barang">Kode Barang</label>
              <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="<?php echo htmlspecialchars($barang['kode_barang']); ?>" readonly>
              <small class="form-text text-muted">Kode Barang tidak dapat diubah.</small>
            </div>
            <div class="form-group">
              <label for="nama_barang">Nama Barang</label>
              <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" required>
            </div>
            <div class="form-group">
              <label for="kategori">Kategori</label>
              <input type="text" class="form-control" id="kategori" name="kategori" value="<?php echo htmlspecialchars($barang['kategori']); ?>">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="jumlah_stok">Jumlah Stok</label>
                    <input type="number" class="form-control" id="jumlah_stok" name="jumlah_stok" value="<?php echo htmlspecialchars($barang['jumlah_stok']); ?>" readonly>
                    <small class="form-text text-muted">Jumlah stok hanya bisa diubah melalui modul Transaksi.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="satuan">Satuan</label>
                    <input type="text" class="form-control" id="satuan" name="satuan" value="<?php echo htmlspecialchars($barang['satuan']); ?>" required>
                </div>
            </div>
            
            <div class="mt-4">
              <button type="submit" class="btn btn-success">Simpan Perubahan</button>
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
