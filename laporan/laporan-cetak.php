<?php
// laporan/laporan-cetak.php (Versi Desain Baru)

include '../auth_check.php';
include '../koneksi.php';

// Ambil tanggal dan jenis transaksi dari URL
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'Semua';

// Query untuk mengambil data transaksi beserta detail barangnya
$query = "SELECT t.*, p.nama AS nama_staff,
          GROUP_CONCAT(CONCAT(b.nama_barang, ' (', dt.jumlah, ' ', b.satuan, ')') SEPARATOR '|') AS detail_barang
          FROM transaksi t
          JOIN pengguna p ON t.id_pengguna_staff = p.id_pengguna
          LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
          LEFT JOIN barang b ON dt.kode_barang = b.kode_barang
          WHERE DATE(t.tanggal_transaksi) BETWEEN '$tgl_awal' AND '$tgl_akhir'";

// Tambahkan filter jenis transaksi jika bukan 'Semua'
if ($jenis_filter != 'Semua') {
    $escaped_jenis = mysqli_real_escape_string($koneksi, $jenis_filter);
    $query .= " AND t.jenis_transaksi = '$escaped_jenis'";
}

$query .= " GROUP BY t.id_transaksi
            ORDER BY t.tanggal_transaksi DESC";

$hasil = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Transaksi - SIBara</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            width: 95%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .report-meta {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #fd9a3f;
        }
        .report-meta p {
            margin: 0;
            padding: 4px 0;
        }
        .report-meta strong {
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 600;
            color: #344767;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .badge.masuk {
            background-color: #e7f3ff;
            color: #007bff;
        }
        .badge.keluar {
            background-color: #fdeeee;
            color: #dc3545;
        }
        .item-list {
            margin: 0;
            padding-left: 15px;
            list-style-type: square;
        }
        .item-list li {
            margin-bottom: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #aaa;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            tr:nth-child(even) { background-color: #f9f9f9 !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h1>SIBara</h1>
            <p>Sistem Informasi Barang</p>
            <h2>Laporan Transaksi Barang</h2>
        </div>

        <div class="report-meta">
            <p><strong>Periode Laporan</strong>: <?php echo date('d F Y', strtotime($tgl_awal)); ?> - <?php echo date('d F Y', strtotime($tgl_akhir)); ?></p>
            <p><strong>Jenis Transaksi</strong>: <?php echo htmlspecialchars($jenis_filter); ?></p>
            <p><strong>Tanggal Cetak</strong>: <?php echo date('d F Y, H:i'); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID Transaksi</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 8%;">Jenis</th>
                    <th style="width: 15%;">Staff Bertugas</th>
                    <th>Keterangan</th>
                    <th style="width: 25%;">Detail Barang</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($hasil) > 0): ?>
                    <?php while($data = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><strong>TR-<?php echo $data['id_transaksi']; ?></strong></td>
                        <td><?php echo date('d M Y, H:i', strtotime($data['tanggal_transaksi'])); ?></td>
                        <td>
                            <?php 
                                $badge_class = $data['jenis_transaksi'] == 'Masuk' ? 'masuk' : 'keluar';
                                echo "<span class='badge {$badge_class}'>{$data['jenis_transaksi']}</span>";
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($data['nama_staff']); ?></td>
                        <td><?php echo htmlspecialchars($data['keterangan'] ? $data['keterangan'] : '-'); ?></td>
                        <td>
                            <?php if(!empty($data['detail_barang'])): ?>
                                <ul class="item-list">
                                    <?php 
                                        $items = explode('|', $data['detail_barang']);
                                        foreach ($items as $item) {
                                            echo '<li>' . htmlspecialchars($item) . '</li>';
                                        }
                                    ?>
                                </ul>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            Tidak ada data transaksi untuk periode dan jenis yang dipilih.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh sistem SIBara.</p>
        </div>
    </div>
</body>
</html>