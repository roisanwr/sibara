<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$id_pengguna_login = $_SESSION['id_pengguna'];
$peran_pengguna = $_SESSION['peran'];
$nama_pengguna = $_SESSION['username'];

// 3. LOGIKA UNTUK STAFF: PROSES SETUJU / TOLAK PERMINTAAN
if ($peran_pengguna == 'staff' && isset($_GET['action']) && isset($_GET['id'])) {
    $id_permintaan = $_GET['id'];
    $action = $_GET['action'];

    // Mulai transaksi database untuk menjaga konsistensi data
    mysqli_begin_transaction($conn);

    try {
        if ($action == 'setujui') {
            // Langkah A: Cek ketersediaan stok untuk semua barang dalam permintaan
            $query_cek_stok = "SELECT dp.kode_barang, dp.jumlah_diminta, b.jumlah_stok, b.nama_barang FROM detail_permintaan dp JOIN barang b ON dp.kode_barang = b.kode_barang WHERE dp.id_permintaan = ?";
            $stmt_cek_stok = mysqli_prepare($conn, $query_cek_stok);
            mysqli_stmt_bind_param($stmt_cek_stok, "i", $id_permintaan);
            mysqli_stmt_execute($stmt_cek_stok);
            $result_cek_stok = mysqli_stmt_get_result($stmt_cek_stok);
            
            $stok_cukup = true;
            $barang_tidak_cukup = [];
            $detail_items = [];
            while($item = mysqli_fetch_assoc($result_cek_stok)){
                if($item['jumlah_diminta'] > $item['jumlah_stok']){
                    $stok_cukup = false;
                    $barang_tidak_cukup[] = "{$item['nama_barang']} (diminta: {$item['jumlah_diminta']}, stok: {$item['jumlah_stok']})";
                }
                $detail_items[] = $item; // Simpan detail untuk proses selanjutnya
            }

            if (!$stok_cukup) {
                // Jika stok tidak cukup, batalkan transaksi dan beri pesan error
                mysqli_rollback($conn);
                $error_msg = "Gagal menyetujui. Stok tidak mencukupi untuk barang: " . implode(", ", $barang_tidak_cukup);
                header("location: permintaan-lihat.php?status=gagal&msg=" . urlencode($error_msg));
                exit;
            }

            // Langkah B: Update status permintaan
            $query_update_permintaan = "UPDATE permintaan_barang SET status = 'Disetujui', id_pengguna_staff = ? WHERE id_permintaan = ?";
            $stmt_update_permintaan = mysqli_prepare($conn, $query_update_permintaan);
            mysqli_stmt_bind_param($stmt_update_permintaan, "ii", $id_pengguna_login, $id_permintaan);
            mysqli_stmt_execute($stmt_update_permintaan);

            // Langkah C: Buat transaksi keluar
            $query_insert_transaksi = "INSERT INTO transaksi (tanggal_transaksi, jenis_transaksi, keterangan, id_pengguna_staff, id_permintaan) VALUES (NOW(), 'Keluar', ?, ?, ?)";
            $keterangan_transaksi = "Pengeluaran barang berdasarkan permintaan #" . $id_permintaan;
            $stmt_insert_transaksi = mysqli_prepare($conn, $query_insert_transaksi);
            mysqli_stmt_bind_param($stmt_insert_transaksi, "sii", $keterangan_transaksi, $id_pengguna_login, $id_permintaan);
            mysqli_stmt_execute($stmt_insert_transaksi);
            $id_transaksi_baru = mysqli_insert_id($conn);

            // Langkah D: Masukkan detail transaksi dan kurangi stok
            foreach ($detail_items as $item) {
                // Masukkan ke detail_transaksi
                $query_insert_detail = "INSERT INTO detail_transaksi (id_transaksi, kode_barang, jumlah) VALUES (?, ?, ?)";
                $stmt_insert_detail = mysqli_prepare($conn, $query_insert_detail);
                mysqli_stmt_bind_param($stmt_insert_detail, "isi", $id_transaksi_baru, $item['kode_barang'], $item['jumlah_diminta']);
                mysqli_stmt_execute($stmt_insert_detail);

                // Kurangi stok di tabel barang
                $query_update_stok = "UPDATE barang SET jumlah_stok = jumlah_stok - ? WHERE kode_barang = ?";
                $stmt_update_stok = mysqli_prepare($conn, $query_update_stok);
                mysqli_stmt_bind_param($stmt_update_stok, "is", $item['jumlah_diminta'], $item['kode_barang']);
                mysqli_stmt_execute($stmt_update_stok);
            }

        } elseif ($action == 'tolak') {
            // Jika ditolak, hanya update status permintaan
            $query_update_permintaan = "UPDATE permintaan_barang SET status = 'Ditolak', id_pengguna_staff = ? WHERE id_permintaan = ?";
            $stmt_update_permintaan = mysqli_prepare($conn, $query_update_permintaan);
            mysqli_stmt_bind_param($stmt_update_permintaan, "ii", $id_pengguna_login, $id_permintaan);
            mysqli_stmt_execute($stmt_update_permintaan);
        }
        
        // Jika semua langkah berhasil, commit transaksi
        mysqli_commit($conn);
        header("location: permintaan-lihat.php?status=sukses");

    } catch (mysqli_sql_exception $exception) {
        // Jika ada error di tengah jalan, batalkan semua perubahan
        mysqli_rollback($conn);
        $error_msg = "Terjadi kesalahan pada database: " . $exception->getMessage();
        header("location: permintaan-lihat.php?status=gagal&msg=" . urlencode($error_msg));
    }
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <title>Daftar Permintaan Barang</title>
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

      <h2 class="mb-4">Daftar Permintaan Barang</h2>
      
      <?php if ($peran_pengguna == 'supervisor'): ?>
        <a href="permintaan-tambah.php" class="btn btn-success mb-3"><i class="fa fa-plus"></i> Buat Permintaan Baru</a>
      <?php endif; ?>

      <?php
        // Tampilkan pesan sukses atau gagal
        if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
            echo "<div class='alert alert-success'>Aksi berhasil diproses.</div>";
        } elseif (isset($_GET['status']) && $_GET['status'] == 'gagal') {
            $pesan_error = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "Terjadi kesalahan.";
            echo "<div class='alert alert-danger'>$pesan_error</div>";
        }
      ?>

      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead class="thead-dark">
            <tr>
              <th>ID Permintaan</th>
              <th>Tanggal</th>
              <?php if ($peran_pengguna == 'staff'): ?>
                <th>Diminta oleh (Supervisor)</th>
              <?php endif; ?>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $query_select = "";
              if ($peran_pengguna == 'supervisor') {
                // Query untuk Supervisor: hanya tampilkan permintaannya sendiri
                $query_select = "SELECT * FROM permintaan_barang WHERE id_pengguna_supervisor = '$id_pengguna_login' ORDER BY tanggal_permintaan DESC";
              } else {
                // Query untuk Staff: tampilkan semua permintaan, utamakan yang 'Pending'
                $query_select = "SELECT pb.*, pg.nama as nama_supervisor 
                                 FROM permintaan_barang pb 
                                 JOIN pengguna pg ON pb.id_pengguna_supervisor = pg.id_pengguna 
                                 ORDER BY FIELD(pb.status, 'Pending', 'Disetujui', 'Ditolak'), pb.tanggal_permintaan DESC";
              }
              
              $result = mysqli_query($conn, $query_select);
              if (mysqli_num_rows($result) > 0) {
                while ($data = mysqli_fetch_assoc($result)) {
            ?>
                  <tr>
                    <td><?php echo $data['id_permintaan']; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($data['tanggal_permintaan'])); ?></td>
                    <?php if ($peran_pengguna == 'staff'): ?>
                      <td><?php echo htmlspecialchars($data['nama_supervisor']); ?></td>
                    <?php endif; ?>
                    <td>
                      <?php 
                        $status = $data['status'];
                        $badge_class = 'badge-secondary';
                        if ($status == 'Disetujui') $badge_class = 'badge-success';
                        if ($status == 'Ditolak') $badge_class = 'badge-danger';
                        if ($status == 'Pending') $badge_class = 'badge-warning';
                        echo "<span class='badge $badge_class'>$status</span>";
                      ?>
                    </td>
                    <td>
                      <a href="permintaan-detail.php?id=<?php echo $data['id_permintaan']; ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i> Detail
                      </a>
                      
                      <?php if ($peran_pengguna == 'supervisor' && $data['status'] == 'Pending'): ?>
                        <a href="permintaan-edit.php?id=<?php echo $data['id_permintaan']; ?>" class="btn btn-warning btn-sm">
                          <i class="fa fa-edit"></i> Edit
                        </a>
                      <?php endif; ?>

                      <!-- <?php if ($peran_pengguna == 'staff' && $data['status'] == 'Pending'): ?>
                        <a href="permintaan-lihat.php?action=setujui&id=<?php echo $data['id_permintaan']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Anda yakin ingin menyetujui permintaan ini? Stok akan langsung dikurangi.')">
                          <i class="fa fa-check"></i> Setujui
                        </a>
                        <a href="permintaan-lihat.php?action=tolak&id=<?php echo $data['id_permintaan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menolak permintaan ini?')">
                          <i class="fa fa-times"></i> Tolak
                        </a>
                      <?php endif; ?> -->
                    </td>
                  </tr>
            <?php
                }
              } else {
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data permintaan.</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/main.js"></script>
</body>
</html>
