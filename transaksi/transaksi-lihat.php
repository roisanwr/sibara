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
?>
<!doctype html>
<html lang="id">
<head>
  <title>Riwayat Transaksi Stok</title>
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

      <h2 class="mb-4">Riwayat Transaksi Stok</h2>

      <?php
        if (isset($_GET['status']) && $_GET['status'] == 'penerimaan_sukses') {
            echo "<div class='alert alert-success'>Transaksi penerimaan barang berhasil dicatat.</div>";
        }
      ?>

      <a href="transaksi-penerimaan.php" class="btn btn-success mb-3"><i class="fa fa-plus"></i> Catat Penerimaan Barang</a>

      <div class="table-responsive">
        <table id="tabel-transaksi" class="table table-striped table-bordered" style="width:100%">
          <thead class="thead-dark">
            <tr>
              <th>ID Transaksi</th>
              <th>Tanggal</th>
              <th>Jenis</th>
              <th>Keterangan</th>
              <th>Dicatat oleh (Staff)</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $query = "
                  SELECT t.*, p.nama as nama_staff 
                  FROM transaksi t
                  JOIN pengguna p ON t.id_pengguna_staff = p.id_pengguna
                  ORDER BY t.tanggal_transaksi DESC
              ";
              $result = mysqli_query($conn, $query);
              while ($data = mysqli_fetch_assoc($result)) {
            ?>
              <tr>
                <td><?php echo $data['id_transaksi']; ?></td>
                <td><?php echo date('d-m-Y H:i', strtotime($data['tanggal_transaksi'])); ?></td>
                <td>
                  <?php 
                    $jenis = $data['jenis_transaksi'];
                    $badge_class = ($jenis == 'Masuk') ? 'badge-primary' : 'badge-info';
                    echo "<span class='badge $badge_class'>$jenis</span>";
                  ?>
                </td>
                <td>
                    <?php 
                        if ($data['jenis_transaksi'] == 'Keluar' && !empty($data['id_permintaan'])) {
                            echo "Berdasarkan Permintaan #" . $data['id_permintaan'];
                        } else {
                            echo htmlspecialchars($data['keterangan']);
                        }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($data['nama_staff']); ?></td>
                <td>
                  <a href="transaksi-detail.php?id=<?php echo $data['id_transaksi']; ?>" class="btn btn-info btn-sm" title="Lihat Detail">
                    <i class="fa fa-eye"></i>
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
    $(document).ready(function() {
        $('#tabel-transaksi').DataTable({
            "order": [[ 1, "desc" ]] // Urutkan berdasarkan kolom tanggal secara descending
        });
    });
  </script>
</body>
</html>
