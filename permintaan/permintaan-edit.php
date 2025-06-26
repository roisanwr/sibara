<?php
// 1. Sertakan file pengecekan sesi dan koneksi database
include '../auth_check.php';
include '../koneksi.php';

// 2. Ambil data pengguna dari sesi
$id_supervisor = $_SESSION['id_pengguna'];
$peran_pengguna = $_SESSION['peran'];
$nama_pengguna = $_SESSION['username'];

// 3. Pastikan hanya supervisor yang bisa mengakses halaman ini
if ($peran_pengguna != 'supervisor') {
    header("location: ../home.php");
    exit;
}

// 4. Ambil ID permintaan dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID Permintaan tidak valid.";
    exit;
}
$id_permintaan_edit = $_GET['id'];

// 5. LOGIKA UNTUK MEMPROSES FORM SAAT DI-SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form
    $tanggal_permintaan = $_POST['tanggal_permintaan'];
    $catatan_permintaan = $_POST['catatan'];
    
    // Mulai transaksi database
    mysqli_begin_transaction($conn);

    try {
        // Langkah A: UPDATE data utama di tabel `permintaan_barang`
        $sql_permintaan = "UPDATE permintaan_barang SET tanggal_permintaan = ?, catatan = ? WHERE id_permintaan = ? AND id_pengguna_supervisor = ?";
        $stmt_permintaan = mysqli_prepare($conn, $sql_permintaan);
        mysqli_stmt_bind_param($stmt_permintaan, "ssii", $tanggal_permintaan, $catatan_permintaan, $id_permintaan_edit, $id_supervisor);
        mysqli_stmt_execute($stmt_permintaan);
        
        // Langkah B: Hapus semua detail permintaan lama yang terkait
        $sql_delete_detail = "DELETE FROM detail_permintaan WHERE id_permintaan = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete_detail);
        mysqli_stmt_bind_param($stmt_delete, "i", $id_permintaan_edit);
        mysqli_stmt_execute($stmt_delete);

        // Langkah C: Insert ulang detail permintaan yang baru
        $kode_barang_list = $_POST['kode_barang'];
        $jumlah_list = $_POST['jumlah'];
        
        $sql_detail = "INSERT INTO detail_permintaan (id_permintaan, kode_barang, jumlah_diminta) VALUES (?, ?, ?)";
        $stmt_detail = mysqli_prepare($conn, $sql_detail);
        
        for ($i = 0; $i < count($kode_barang_list); $i++) {
            $kode_barang = $kode_barang_list[$i];
            $jumlah = $jumlah_list[$i];
            
            if (!empty($kode_barang) && !empty($jumlah) && $jumlah > 0) {
                mysqli_stmt_bind_param($stmt_detail, "isi", $id_permintaan_edit, $kode_barang, $jumlah);
                mysqli_stmt_execute($stmt_detail);
            }
        }
        
        // Jika semua berhasil, commit transaksi
        mysqli_commit($conn);
        
        // Arahkan kembali ke halaman lihat
        header("location: permintaan-lihat.php?status=edit_sukses");

    } catch (mysqli_sql_exception $exception) {
        // Jika ada error, batalkan semua perubahan
        mysqli_rollback($conn);
        echo "Error: Gagal memperbarui data. " . $exception->getMessage();
    }
    
    exit;
}

// 6. LOGIKA UNTUK MENGAMBIL DATA LAMA DAN MENAMPILKAN DI FORM
$query_permintaan = "SELECT * FROM permintaan_barang WHERE id_permintaan = ? AND id_pengguna_supervisor = ?";
$stmt = mysqli_prepare($conn, $query_permintaan);
mysqli_stmt_bind_param($stmt, "ii", $id_permintaan_edit, $id_supervisor);
mysqli_stmt_execute($stmt);
$result_permintaan = mysqli_stmt_get_result($stmt);
$permintaan = mysqli_fetch_assoc($result_permintaan);

// Jika permintaan tidak ditemukan atau statusnya bukan 'Pending', jangan izinkan edit
if (!$permintaan || $permintaan['status'] != 'Pending') {
    echo "Permintaan tidak dapat diedit.";
    exit;
}

// Ambil detail barang dari permintaan ini
$query_detail = "SELECT * FROM detail_permintaan WHERE id_permintaan = ?";
$stmt_detail_get = mysqli_prepare($conn, $query_detail);
mysqli_stmt_bind_param($stmt_detail_get, "i", $id_permintaan_edit);
mysqli_stmt_execute($stmt_detail_get);
$result_detail = mysqli_stmt_get_result($stmt_detail_get);

?>
<!doctype html>
<html lang="id">
<head>
  <title>Edit Permintaan Barang</title>
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
          <li class="active"><a href="permintaan-lihat.php">Permintaan Barang</a></li>
          <li><a href="../laporan/laporan-lihat.php">Laporan</a></li>
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

      <h2 class="mb-4">Edit Permintaan Barang #<?php echo $id_permintaan_edit; ?></h2>
      
      <div class="card">
        <div class="card-body">
          <form action="permintaan-edit.php?id=<?php echo $id_permintaan_edit; ?>" method="POST">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="tanggal_permintaan">Tanggal Permintaan</label>
                <input type="date" class="form-control" name="tanggal_permintaan" value="<?php echo $permintaan['tanggal_permintaan']; ?>" required>
              </div>
              <div class="form-group col-md-6">
                <label>Nama Peminta</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_pengguna); ?>" readonly>
              </div>
            </div>
            
            <hr>
            
            <h4>Daftar Barang yang Diminta</h4>
            <div id="daftar-barang">
              <?php 
                // Loop untuk menampilkan detail barang yang sudah ada
                $item_count = 0;
                while ($item = mysqli_fetch_assoc($result_detail)): 
                $item_count++;
              ?>
                <div class="form-row align-items-end barang-item mb-2">
                  <div class="form-group col-md-6">
                    <label>Barang</label>
                    <select name="kode_barang[]" class="form-control" required>
                      <option value="">-- Pilih Barang --</option>
                      <?php
                        // Ambil daftar semua barang untuk dropdown
                        $result_barang = mysqli_query($conn, "SELECT kode_barang, nama_barang, jumlah_stok FROM barang ORDER BY nama_barang");
                        while ($barang = mysqli_fetch_assoc($result_barang)) {
                          $selected = ($barang['kode_barang'] == $item['kode_barang']) ? 'selected' : '';
                          echo "<option value='{$barang['kode_barang']}' $selected>{$barang['nama_barang']} (Stok: {$barang['jumlah_stok']})</option>";
                        }
                      ?>
                    </select>
                  </div>
                  <div class="form-group col-md-4">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="1" value="<?php echo $item['jumlah_diminta']; ?>" required>
                  </div>
                  <div class="form-group col-md-2">
                    <button type="button" class="btn btn-danger btn-block" <?php if ($item_count <= 1) echo 'disabled'; ?>>Hapus</button>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>

            <button type="button" id="tambah-barang" class="btn btn-primary mt-2">Tambah Barang Lain</button>

            <hr>

            <div class="form-group">
              <label for="catatan">Catatan Tambahan</label>
              <textarea class="form-control" name="catatan" rows="3"><?php echo htmlspecialchars($permintaan['catatan']); ?></textarea>
            </div>
            
            <div class="mt-4">
              <button type="submit" class="btn btn-success">Simpan Perubahan</button>
              <a href="permintaan-lihat.php" class="btn btn-secondary">Batal</a>
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
          // Hanya hapus jika ada lebih dari satu baris barang
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
