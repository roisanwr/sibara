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

// 4. Validasi ID Transaksi dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID Transaksi tidak ditemukan.");
}
$id_transaksi = $_GET['id'];

// 5. Ambil data utama transaksi dari database
$query_transaksi = "
    SELECT 
        t.*, 
        p.nama as nama_staff
    FROM 
        transaksi t
    JOIN 
        pengguna p ON t.id_pengguna_staff = p.id_pengguna
    WHERE 
        t.id_transaksi = ?
";

$stmt = mysqli_prepare($conn, $query_transaksi);
mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
mysqli_stmt_execute($stmt);
$result_transaksi = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result_transaksi);

// Jika data tidak ditemukan, hentikan skrip
if (!$transaksi) {
    die("Error: Data transaksi tidak ditemukan.");
}

// 6. Ambil data detail barang untuk transaksi ini
$query_detail = "
    SELECT 
        dt.jumlah, 
        b.kode_barang, 
        b.nama_barang,
        b.satuan
    FROM 
        detail_transaksi dt
    JOIN 
        barang b ON dt.kode_barang = b.kode_barang
    WHERE 
        dt.id_transaksi = ?
";
$stmt_detail = mysqli_prepare($conn, $query_detail);
mysqli_stmt_bind_param($stmt_detail, "i", $id_transaksi);
mysqli_stmt_execute($stmt_detail);
$result_detail = mysqli_stmt_get_result($stmt_detail);

?>
<!doctype html>
<html lang="id">
<head>
  <title>Detail Transaksi #<?php echo $id_transaksi; ?></title>
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

      <h2 class="mb-4">Detail Transaksi Stok #<?php echo $id_transaksi; ?></h2>
      
      <div class="card">
        <div class="card-header">
          Informasi Transaksi
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tanggal Transaksi:</strong> <?php echo date('d F Y H:i', strtotime($transaksi['tanggal_transaksi'])); ?></p>
                    <p><strong>Jenis Transaksi:</strong> 
                        <?php 
                            $jenis = $transaksi['jenis_transaksi'];
                            $badge_class = ($jenis == 'Masuk') ? 'badge-primary' : 'badge-info';
                            echo "<span class='badge $badge_class' style='font-size: 1rem;'>$jenis</span>";
                        ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Dicatat oleh:</strong> <?php echo htmlspecialchars($transaksi['nama_staff']); ?> (Staff)</p>
                    <p><strong>Referensi Permintaan:</strong> 
                        <?php 
                            if ($transaksi['jenis_transaksi'] == 'Keluar' && !empty($transaksi['id_permintaan'])) {
                                echo "<a href='../permintaan/permintaan-detail.php?id={$transaksi['id_permintaan']}'>#{$transaksi['id_permintaan']}</a>";
                            } else {
                                echo "-";
                            }
                        ?>
                    </p>
                </div>
                <div class="col-md-12">
                    <p><strong>Keterangan:</strong> <?php echo !empty($transaksi['keterangan']) ? htmlspecialchars($transaksi['keterangan']) : '-'; ?></p>
                </div>
            </div>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-header">
          Rincian Barang
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead class="thead-light">
                <tr>
                  <th>No.</th>
                  <th>Kode Barang</th>
                  <th>Nama Barang</th>
                  <th>Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $no = 1;
                  while ($item = mysqli_fetch_assoc($result_detail)): 
                ?>
                  <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $item['kode_barang']; ?></td>
                    <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                    <td><?php echo $item['jumlah'] . ' ' . htmlspecialchars($item['satuan']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <a href="transaksi-lihat.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Riwayat</a>
      </div>

    </div>
  </div>

  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
