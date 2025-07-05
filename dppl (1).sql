-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2025 at 10:03 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dppl`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `jumlah_stok` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `satuan`, `jumlah_stok`) VALUES
('101', 'Pulpen', 'ATK', 'PCS', 20),
('102', 'Pensil', 'ATK', 'pcs', 61);

-- --------------------------------------------------------

--
-- Table structure for table `detail_permintaan`
--

CREATE TABLE `detail_permintaan` (
  `id_permintaan` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `jumlah_diminta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_permintaan`
--

INSERT INTO `detail_permintaan` (`id_permintaan`, `kode_barang`, `jumlah_diminta`) VALUES
(1, '101', 10),
(2, '102', 10),
(3, '102', 50),
(4, '102', 20);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_transaksi`, `kode_barang`, `jumlah`) VALUES
(1, '102', 11),
(2, '102', 50);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `peran` enum('karyawan-toko','staff-gudang') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama`, `username`, `password`, `email`, `peran`) VALUES
(1001, 'Ibnu Abdad', 'ibnu', 'ibnu123', 'ibnu@gmail.com', 'karyawan-toko'),
(1002, 'nurul', 'nurul', 'nurul123', 'nurul@gmail.com', 'staff-gudang');

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_barang`
--

CREATE TABLE `permintaan_barang` (
  `id_permintaan` int(11) NOT NULL,
  `tanggal_permintaan` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('Pending','Disetujui','Ditolak') NOT NULL DEFAULT 'Pending',
  `id_pengguna_supervisor` int(11) NOT NULL,
  `id_pengguna_staff` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permintaan_barang`
--

INSERT INTO `permintaan_barang` (`id_permintaan`, `tanggal_permintaan`, `catatan`, `status`, `id_pengguna_supervisor`, `id_pengguna_staff`) VALUES
(1, '2025-06-18', '', 'Disetujui', 1001, 1002),
(2, '2025-06-18', 'stok nipis', 'Ditolak', 1001, 1002),
(3, '2025-06-18', 'stok abis', 'Disetujui', 1001, 1002),
(4, '2025-06-20', 'abis', 'Pending', 1001, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal_transaksi` datetime NOT NULL,
  `jenis_transaksi` enum('Masuk','Keluar') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_pengguna_staff` int(11) NOT NULL,
  `id_permintaan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_transaksi`, `jenis_transaksi`, `keterangan`, `id_pengguna_staff`, `id_permintaan`) VALUES
(1, '2025-06-18 08:45:00', 'Masuk', 'dari suplier a', 1002, NULL),
(2, '2025-06-18 13:54:07', 'Keluar', 'Pengeluaran barang berdasarkan permintaan #3', 1002, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`kode_barang`);

--
-- Indexes for table `detail_permintaan`
--
ALTER TABLE `detail_permintaan`
  ADD PRIMARY KEY (`id_permintaan`,`kode_barang`),
  ADD KEY `kode_barang` (`kode_barang`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_transaksi`,`kode_barang`),
  ADD KEY `kode_barang` (`kode_barang`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  ADD PRIMARY KEY (`id_permintaan`),
  ADD KEY `id_pengguna_peminta` (`id_pengguna_supervisor`),
  ADD KEY `id_pengguna_penyetuju` (`id_pengguna_staff`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pengguna_staff` (`id_pengguna_staff`),
  ADD KEY `id_permintaan` (`id_permintaan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003;

--
-- AUTO_INCREMENT for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  MODIFY `id_permintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_permintaan`
--
ALTER TABLE `detail_permintaan`
  ADD CONSTRAINT `detail_permintaan_ibfk_1` FOREIGN KEY (`id_permintaan`) REFERENCES `permintaan_barang` (`id_permintaan`),
  ADD CONSTRAINT `detail_permintaan_ibfk_2` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`);

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`);

--
-- Constraints for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  ADD CONSTRAINT `permintaan_barang_ibfk_1` FOREIGN KEY (`id_pengguna_supervisor`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `permintaan_barang_ibfk_2` FOREIGN KEY (`id_pengguna_staff`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pengguna_staff`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_permintaan`) REFERENCES `permintaan_barang` (`id_permintaan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
