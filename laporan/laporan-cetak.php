<?php
// laporan/laporan-cetak.php

include '../auth_check.php';
include '../koneksi.php';

// Ambil tanggal dan status dari URL
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
// --- TAMBAHAN ---
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Semua';

// Query untuk mengambil data permintaan berdasarkan rentang tanggal dan status
$query = "SELECT pb.*, p_peminta.nama AS nama_peminta, p_staff.nama AS nama_staff,
          GROUP_CONCAT(CONCAT(b.nama_barang, ' (', dp.jumlah_diminta, ' ', b.satuan, ')') SEPARATOR '; ') AS detail_barang
          FROM permintaan_barang pb
          JOIN pengguna p_peminta ON pb.id_pengguna_supervisor = p_peminta.id_pengguna
          LEFT JOIN pengguna p_staff ON pb.id_pengguna_staff = p_staff.id_pengguna
          JOIN detail_permintaan dp ON pb.id_permintaan = dp.id_permintaan
          JOIN barang b ON dp.kode_barang = b.kode_barang
          WHERE pb.tanggal_permintaan BETWEEN '$tgl_awal' AND '$tgl_akhir'";

// --- TAMBAHAN ---
// Tambahkan filter status jika bukan 'Semua'
if ($status_filter != 'Semua') {
    $escaped_status = mysqli_real_escape_string($koneksi, $status_filter);
    $query .= " AND pb.status = '$escaped_status'";
}

$query .= " GROUP BY pb.id_permintaan
            ORDER BY pb.tanggal_permintaan DESC";

$hasil = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Permintaan Barang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        h1 { text-align: center; }
        .periode { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <h1>Laporan Permintaan Barang</h1>
        <div class="periode">
            Periode: <?php echo date('d M Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d M Y', strtotime($tgl_akhir)); ?>
            <br>
            Status: <?php echo htmlspecialchars($status_filter); ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Peminta</th>
                    <th>Diproses Oleh</th>
                    <th>Status</th>
                    <th>Detail Barang</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($hasil) > 0): ?>
                    <?php while($data = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td>#<?php echo $data['id_permintaan']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($data['tanggal_permintaan'])); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_peminta']); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_staff'] ? $data['nama_staff'] : '-'); ?></td>
                        <td><?php echo $data['status']; ?></td>
                        <td><?php echo htmlspecialchars($data['detail_barang']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada data untuk periode dan status ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>