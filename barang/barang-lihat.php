<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$peran_pengguna = $_SESSION['peran'];

// 3. Pastikan hanya staff yang bisa mengakses halaman ini
if ($peran_pengguna != 'staff') {
    // Jika bukan staff, tampilkan pesan error dan hentikan skrip
    die("Error: Anda tidak memiliki hak akses untuk halaman ini.");
}
?>
<!doctype html>
<html lang="id">
<head>
  <title>Master Barang</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
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

      <h2 class="mb-4">Master Data Barang</h2>

      <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'tambah_sukses') {
                echo "<div class='alert alert-success'>Data barang berhasil ditambahkan.</div>";
            } elseif ($_GET['status'] == 'edit_sukses') {
                echo "<div class='alert alert-success'>Data barang berhasil diubah.</div>";
            } elseif ($_GET['status'] == 'hapus_sukses') {
                echo "<div class='alert alert-success'>Data barang berhasil dihapus.</div>";
            }
        }
      ?>

      <a href="barang-tambah.php" class="btn btn-success mb-3"><i class="fa fa-plus"></i> Tambah Barang Baru</a>

      <div class="table-responsive">
        <table id="tabel-barang" class="table table-striped table-bordered" style="width:100%">
          <thead class="thead-dark">
            <tr>
              <th>Kode Barang</th>
              <th>Nama Barang</th>
              <th>Kategori</th>
              <th>Stok</th>
              <th>Satuan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $query_barang = "SELECT * FROM barang ORDER BY nama_barang ASC";
              $result = mysqli_query($conn, $query_barang);
              while ($data = mysqli_fetch_assoc($result)) {
            ?>
              <tr>
                <td><?php echo htmlspecialchars($data['kode_barang']); ?></td>
                <td><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                <td><?php echo htmlspecialchars($data['kategori']); ?></td>
                <td><?php echo htmlspecialchars($data['jumlah_stok']); ?></td>
                <td><?php echo htmlspecialchars($data['satuan']); ?></td>
                <td>
                  <a href="barang-ubah.php?id=<?php echo $data['kode_barang']; ?>" class="btn btn-warning btn-sm" title="Ubah">
                    <i class="fa fa-edit"></i>
                  </a>
                  <a href="barang-hapus.php?id=<?php echo $data['kode_barang']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Anda yakin ingin menghapus barang ini? Semua data terkait akan terpengaruh.')">
                    <i class="fa fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
  <script src="../js/main.js"></script>
  <script>
    // Inisialisasi DataTable
    $(document).ready(function() {
        $('#tabel-barang').DataTable();
    });
  </script>
</body>
</html>
