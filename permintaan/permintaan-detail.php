<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$id_pengguna_login = $_SESSION['id_pengguna'];
$peran_pengguna = $_SESSION['peran'];

// 3. Validasi ID Permintaan dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID Permintaan tidak ditemukan.");
}
$id_permintaan = $_GET['id'];

// 4. Ambil data utama permintaan dari database
// Kita JOIN ke tabel pengguna untuk mendapatkan nama supervisor dan staff
$query_permintaan = "
    SELECT 
        pb.*, 
        supervisor.nama as nama_supervisor, 
        staff.nama as nama_staff
    FROM 
        permintaan_barang pb
    JOIN 
        pengguna supervisor ON pb.id_pengguna_supervisor = supervisor.id_pengguna
    LEFT JOIN 
        pengguna staff ON pb.id_pengguna_staff = staff.id_pengguna
    WHERE 
        pb.id_permintaan = ?
";

$stmt = mysqli_prepare($conn, $query_permintaan);
mysqli_stmt_bind_param($stmt, "i", $id_permintaan);
mysqli_stmt_execute($stmt);
$result_permintaan = mysqli_stmt_get_result($stmt);
$permintaan = mysqli_fetch_assoc($result_permintaan);

// Jika data tidak ditemukan, hentikan skrip
if (!$permintaan) {
    die("Error: Data permintaan tidak ditemukan.");
}

// 5. KEAMANAN: Jika yang login adalah supervisor, pastikan dia hanya bisa melihat permintaannya sendiri
if ($peran_pengguna == 'supervisor' && $permintaan['id_pengguna_supervisor'] != $id_pengguna_login) {
    die("Error: Anda tidak memiliki hak akses untuk melihat detail permintaan ini.");
}

// 6. Ambil data detail barang untuk permintaan ini
$query_detail = "
    SELECT 
        dp.jumlah_diminta, 
        b.kode_barang, 
        b.nama_barang,
        b.satuan
    FROM 
        detail_permintaan dp
    JOIN 
        barang b ON dp.kode_barang = b.kode_barang
    WHERE 
        dp.id_permintaan = ?
";
$stmt_detail = mysqli_prepare($conn, $query_detail);
mysqli_stmt_bind_param($stmt_detail, "i", $id_permintaan);
mysqli_stmt_execute($stmt_detail);
$result_detail = mysqli_stmt_get_result($stmt_detail);

?>
<!doctype html>
<html lang="id">
<head>
  <title>Detail Permintaan #<?php echo $id_permintaan; ?></title>
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
          
          <?php if ($peran_pengguna == 'supervisor'): ?>
            <li class="active"><a href="permintaan-lihat.php">Permintaan Barang</a></li>
            <li><a href="../laporan/laporan-lihat.php">Laporan</a></li>

          <?php elseif ($peran_pengguna == 'staff'): ?>
            <li class="active"><a href="permintaan-lihat.php">Verifikasi Permintaan</a></li>
            <li><a href="../barang/barang-lihat.php">Master Barang</a></li>
            <li><a href="../transaksi/transaksi-lihat.php">Transaksi Stok</a></li>
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
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
          </button>
        </div>
      </nav>

      <h2 class="mb-4">Detail Permintaan Barang #<?php echo $id_permintaan; ?></h2>
      
      <div class="card">
        <div class="card-header">
          Informasi Permintaan
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tanggal Permintaan:</strong> <?php echo date('d F Y', strtotime($permintaan['tanggal_permintaan'])); ?></p>
                    <p><strong>Diminta oleh:</strong> <?php echo htmlspecialchars($permintaan['nama_supervisor']); ?> (Supervisor)</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> 
                        <?php 
                            $status = $permintaan['status'];
                            $badge_class = 'badge-secondary';
                            if ($status == 'Disetujui') $badge_class = 'badge-success';
                            if ($status == 'Ditolak') $badge_class = 'badge-danger';
                            if ($status == 'Pending') $badge_class = 'badge-warning';
                            echo "<span class='badge $badge_class' style='font-size: 1rem;'>$status</span>";
                        ?>
                    </p>
                    <p><strong>Diproses oleh:</strong> 
                        <?php 
                            if (!empty($permintaan['nama_staff'])) {
                                echo htmlspecialchars($permintaan['nama_staff']) . " (Staff)";
                            } else {
                                echo "-";
                            }
                        ?>
                    </p>
                </div>
                <div class="col-md-12">
                    <p><strong>Catatan:</strong> <?php echo !empty($permintaan['catatan']) ? htmlspecialchars($permintaan['catatan']) : '-'; ?></p>
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
                  <th>Jumlah Diminta</th>
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
                    <td><?php echo $item['jumlah_diminta'] . ' ' . htmlspecialchars($item['satuan']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <a href="permintaan-lihat.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
        
        <?php if ($peran_pengguna == 'staff' && $permintaan['status'] == 'Pending'): ?>
            <a href="permintaan-lihat.php?action=setujui&id=<?php echo $id_permintaan; ?>" class="btn btn-success" onclick="return confirm('Anda yakin ingin menyetujui permintaan ini?')">
              <i class="fa fa-check"></i> Setujui
            </a>
            <a href="permintaan-lihat.php?action=tolak&id=<?php echo $id_permintaan; ?>" class="btn btn-danger" onclick="return confirm('Anda yakin ingin menolak permintaan ini?')">
              <i class="fa fa-times"></i> Tolak
            </a>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
