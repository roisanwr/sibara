<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$peran_pengguna = $_SESSION['peran'];

// 3. Proses filter tanggal dan jenis transaksi
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Bangun query dasar
$query = "
    SELECT 
        t.*, 
        p.nama as nama_staff
    FROM 
        transaksi t
    JOIN 
        pengguna p ON t.id_pengguna_staff = p.id_pengguna
    WHERE 1=1
";

// Tambahkan filter jika ada
$params = [];
$types = "";

if (!empty($filter_jenis)) {
    $query .= " AND t.jenis_transaksi = ?";
    $params[] = $filter_jenis;
    $types .= "s";
}
if (!empty($filter_start_date)) {
    $query .= " AND DATE(t.tanggal_transaksi) >= ?";
    $params[] = $filter_start_date;
    $types .= "s";
}
if (!empty($filter_end_date)) {
    $query .= " AND DATE(t.tanggal_transaksi) <= ?";
    $params[] = $filter_end_date;
    $types .= "s";
}

$query .= " ORDER BY t.tanggal_transaksi DESC";

// Eksekusi query dengan prepared statement
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!doctype html>
<html lang="id">
<head>
  <title>Laporan Transaksi Stok</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* CSS untuk versi cetak */
    @media print {
      body * {
        visibility: hidden;
      }
      #area-cetak, #area-cetak * {
        visibility: visible;
      }
      #area-cetak {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .no-print {
          display: none;
      }
    }
  </style>
</head>
<body>
  <div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
      <div class="p-4 pt-5">
        <a href="#" class="img logo rounded-circle mb-5" style="background-image: url(../images/bengkel.png);"></a>
        
        <ul class="list-unstyled components mb-5">
            <li><a href="../home.php">Home</a></li>
            <?php if ($peran_pengguna == 'supervisor'): ?>
                <li><a href="../permintaan/permintaan-lihat.php">Permintaan Barang</a></li>
                <li class="active"><a href="#laporanSubmenu" data-toggle="collapse" aria-expanded="true">Laporan</a></li>
                <ul class="collapse list-unstyled show" id="laporanSubmenu">
                    <li><a href="laporan-lihat.php">Laporan Permintaan</a></li>
                    <li class="active"><a href="laporan-transaksi.php">Laporan Transaksi</a></li>
                </ul>
            <?php elseif ($peran_pengguna == 'staff'): ?>
                <li><a href="../permintaan/permintaan-lihat.php">Verifikasi Permintaan</a></li>
                <li><a href="../barang/barang-lihat.php">Master Barang</a></li>
                <li><a href="../transaksi/transaksi-lihat.php">Transaksi Stok</a></li>
                <li class="active"><a href="laporan-transaksi.php">Laporan Transaksi</a></li>
                <li><a href="../pengguna/pengguna-lihat.php">Kelola Pengguna</a></li>
            <?php endif; ?>
            <li><a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Logout</a></li>
        </ul>

        <div class="footer">
          <p>Gudang &copy;<script>document.write(new Date().getFullYear());</script></p>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <div id="content" class="p-4 p-md-5">
      <nav class="navbar navbar-expand-lg navbar-light bg-light no-print">
        <div class="container-fluid">
          <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
          </button>
        </div>
      </nav>

      <h2 class="mb-4">Laporan Transaksi Stok</h2>

      <!-- Form Filter -->
      <div class="card mb-4 no-print">
        <div class="card-body">
            <h5 class="card-title">Filter Laporan</h5>
            <form method="GET" action="laporan-transaksi.php">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="jenis">Jenis Transaksi</label>
                        <select name="jenis" id="jenis" class="form-control">
                            <option value="">Semua Jenis</option>
                            <option value="Masuk" <?php if($filter_jenis == 'Masuk') echo 'selected'; ?>>Masuk</option>
                            <option value="Keluar" <?php if($filter_jenis == 'Keluar') echo 'selected'; ?>>Keluar</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="laporan-transaksi.php" class="btn btn-secondary">Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-info float-right"><i class="fa fa-print"></i> Cetak Laporan</button>
            </form>
        </div>
      </div>

      <!-- Area yang akan dicetak -->
      <div id="area-cetak">
        <h3 class="text-center mb-3 d-none d-print-block">Laporan Transaksi Stok</h3>
        <p class="d-none d-print-block">Periode: <?php echo !empty($filter_start_date) ? date('d-m-Y', strtotime($filter_start_date)) : 'Semua'; ?> s/d <?php echo !empty($filter_end_date) ? date('d-m-Y', strtotime($filter_end_date)) : 'Semua'; ?></p>

        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead class="thead-dark">
              <tr>
                <th>ID</th>
                <th>Tgl Transaksi</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Dicatat oleh</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if (mysqli_num_rows($result) > 0) {
                  while ($data = mysqli_fetch_assoc($result)) {
              ?>
                <tr>
                  <td><?php echo $data['id_transaksi']; ?></td>
                  <td><?php echo date('d-m-Y H:i', strtotime($data['tanggal_transaksi'])); ?></td>
                  <td><?php echo htmlspecialchars($data['jenis_transaksi']); ?></td>
                  <td><?php echo htmlspecialchars($data['keterangan']); ?></td>
                  <td><?php echo htmlspecialchars($data['nama_staff']); ?></td>
                </tr>
              <?php
                  }
                } else {
                  echo "<tr><td colspan='5' class='text-center'>Tidak ada data transaksi untuk filter yang dipilih.</td></tr>";
                }
              ?>
            </tbody>
          </table>
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
