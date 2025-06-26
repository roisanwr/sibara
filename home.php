<?php
// 1. Sertakan auth_check.php di baris paling atas
// Ini akan memastikan hanya pengguna yang sudah login yang bisa akses halaman ini.
include 'auth_check.php';

// 2. Sertakan koneksi database untuk mengambil data dashboard
include 'koneksi.php';

// Ambil nama pengguna dari sesi untuk pesan selamat datang
$nama_pengguna = $_SESSION['username'];
$peran_pengguna = $_SESSION['peran'];
?>
<!doctype html>
<html lang="id">

<head>
  <title>Home - Sistem Gudang</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
  <!-- Menggunakan Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
      <div class="p-4 pt-5">
        <a href="#" class="img logo rounded-circle mb-5" style="background-image: url(images/bengkel.png);"></a>
        
        <!-- ===== 3. MENU SIDEBAR DINAMIS ===== -->
        <ul class="list-unstyled components mb-5">
          <li class="active">
            <a href="home.php">Home</a>
          </li>
          
          <?php if ($peran_pengguna == 'supervisor'): ?>
            <!-- Menu untuk Supervisor -->
            <li><a href="permintaan/permintaan-lihat.php">Permintaan Barang</a></li>
            <li><a href="laporan/laporan-lihat.php">Laporan</a></li>

          <?php elseif ($peran_pengguna == 'staff'): ?>
            <!-- Menu untuk Staff -->
            <li><a href="permintaan/permintaan-lihat.php">Verifikasi Permintaan</a></li>
            <li><a href="barang/barang-lihat.php">Master Barang</a></li>
            <li><a href="transaksi/transaksi-lihat.php">Transaksi Stok</a></li>
            <li><a href="pengguna/pengguna-lihat.php">Kelola Pengguna</a></li>
          <?php endif; ?>
          
          <li>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Logout</a>
          </li>
        </ul>

        <div class="footer">
          <p>
            Gudang &copy;<script>document.write(new Date().getFullYear());</script>
          </p>
        </div>

      </div>
    </nav>

    <!-- Page Content  -->
    <div id="content" class="p-4 p-md-5">

      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
          </button>
          <!-- ... Tombol dan menu navbar atas bisa dibiarkan atau disesuaikan ... -->
        </div>
      </nav>

      <h2 class="mb-4">Selamat Datang, <?php echo htmlspecialchars($nama_pengguna); ?>!</h2>
      <p>Anda login sebagai: <strong><?php echo ucfirst($peran_pengguna); ?></strong></p>
      <hr>

      <!-- ===== 4. KONTEN DASHBOARD DINAMIS ===== -->
      <div class="row">
        <?php if ($peran_pengguna == 'supervisor'): ?>
            <?php
                // Query untuk Supervisor
                $id_supervisor = $_SESSION['id_pengguna'];
                // ===== PERBAIKAN DI SINI =====
                // Mengganti 'id_supervisor' menjadi 'id_pengguna_supervisor' sesuai dengan database.
                $q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_barang WHERE id_pengguna_supervisor = $id_supervisor");
                $q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_barang WHERE id_pengguna_supervisor = $id_supervisor AND status = 'Pending'");
                $q_disetujui = mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_barang WHERE id_pengguna_supervisor = $id_supervisor AND status = 'Disetujui'");
                
                $total_permintaan = mysqli_fetch_assoc($q_total)['total'];
                $total_pending = mysqli_fetch_assoc($q_pending)['total'];
                $total_disetujui = mysqli_fetch_assoc($q_disetujui)['total'];
            ?>
            <!-- Tampilan untuk Supervisor -->
            <div class="col-md-4 mb-4">
              <div class="card text-white bg-primary">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title">Total Permintaan Saya</h5>
                    <p class="card-text" style="font-size: 2rem;"><?php echo $total_permintaan; ?></p>
                  </div>
                  <i class="fa-solid fa-file-alt" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-4">
              <div class="card text-white bg-warning">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title">Menunggu Persetujuan</h5>
                    <p class="card-text" style="font-size: 2rem;"><?php echo $total_pending; ?></p>
                  </div>
                  <i class="fa-solid fa-clock" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-4">
              <div class="card text-white bg-success">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title">Sudah Disetujui</h5>
                    <p class="card-text" style="font-size: 2rem;"><?php echo $total_disetujui; ?></p>
                  </div>
                  <i class="fa-solid fa-check-circle" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
              </div>
            </div>

        <?php elseif ($peran_pengguna == 'staff'): ?>
            <?php
                // Query untuk Staff
                $q_barang = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang");
                $q_verifikasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_barang WHERE status = 'Pending'");
                $q_pengguna = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengguna");

                $total_barang = mysqli_fetch_assoc($q_barang)['total'];
                $total_verifikasi = mysqli_fetch_assoc($q_verifikasi)['total'];
                $total_pengguna = mysqli_fetch_assoc($q_pengguna)['total'];
            ?>
            <!-- Tampilan untuk Staff -->
            <div class="col-md-4 mb-4">
              <div class="card text-white bg-info">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title">Jumlah Master Barang</h5>
                    <p class="card-text" style="font-size: 2rem;"><?php echo $total_barang; ?></p>
                  </div>
                  <i class="fa-solid fa-box-open" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-4">
              <div class="card text-white bg-danger">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title">Permintaan Diverifikasi</h5>
                    <p class="card-text" style="font-size: 2rem;"><?php echo $total_verifikasi; ?></p>
                  </div>
                  <i class="fa-solid fa-hourglass-half" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-4">
              <div class="card text-white bg-dark">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <h5 class="card-title">Total Pengguna</h5>
                    <p class="card-text" style="font-size: 2rem;"><?php echo $total_pengguna; ?></p>
                  </div>
                  <i class="fa-solid fa-users" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
              </div>
            </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <script src="js/jquery.min.js"></script>
  <script src="js/popper.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>
</body>

</html>
