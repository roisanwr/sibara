-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 18, 2025 at 12:42 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbpenjualan`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `no_barang` varchar(5) NOT NULL,
  `nama_barang` varchar(25) NOT NULL,
  `harga_satuan` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`no_barang`, `nama_barang`, `harga_satuan`) VALUES
('1', 'Busi Honda', '45000'),
('2', 'Oli Mesin Full Sintetic', '180000'),
('3', 'Air Radiator', '37000'),
('4', 'Oli Filter', '70000'),
('5', 'Ongkos Tune Up', '300000'),
('6', 'Ban', '200000'),
('7', 'Filter Udara', '300000');

-- --------------------------------------------------------

--
-- Table structure for table `detailbarang`
--

CREATE TABLE `detailbarang` (
  `nota_no` varchar(15) NOT NULL,
  `no_barang` varchar(5) NOT NULL,
  `banyaknya` int(11) NOT NULL,
  `jumlah` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailbarang`
--

INSERT INTO `detailbarang` (`nota_no`, `no_barang`, `banyaknya`, `jumlah`) VALUES
('SRV-2023-001', '2', 4, 720000),
('SRV-2023-001', '4', 1, 70000),
('SRV-2023-002', '6', 4, 800000),
('SRV-2023-003', '5', 1, 300000),
('SRV-2023-004', '4', 1, 70000),
('SRV-2023-004', '1', 4, 180000),
('SRV-2023-005', '3', 2, 74000),
('SRV-2023-007', '2', 3, 540000),
('SRV-2023-007', '1', 1, 45000);

-- --------------------------------------------------------

--
-- Table structure for table `nota`
--

CREATE TABLE `nota` (
  `nota_no` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `km_mobil` varchar(10) NOT NULL,
  `nopol` varchar(12) NOT NULL,
  `kasir` varchar(20) NOT NULL,
  `jumlah_rp` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nota`
--

INSERT INTO `nota` (`nota_no`, `tanggal`, `km_mobil`, `nopol`, `kasir`, `jumlah_rp`) VALUES
('SRV-2023-001', '2023-06-05', '73652', 'B 1374 VFZ', 'Budi', 790000),
('SRV-2023-002', '2024-06-26', '90721', 'B 1313 JKT', 'Elbert', 800000),
('SRV-2023-003', '2024-06-13', '62013', 'B 3000 VVF', 'Elbert', 300000),
('SRV-2023-004', '2024-06-12', '49021', 'B 6718 CF', 'Deni', 250000),
('SRV-2023-005', '2024-06-13', '87583', 'B 9101 GH', 'Elbert', 74000),
('SRV-2023-007', '2025-05-18', '1200', 'B 1374 VFZ', 'Ahmad', 585000);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `nopol` varchar(12) NOT NULL,
  `nama` varchar(25) NOT NULL,
  `tipe` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`nopol`, `nama`, `tipe`) VALUES
('B 1122 IJ', 'Putra', 'Hatchback'),
('B 1313 JKT', 'Eka', 'Civic'),
('B 1374 VFZ', 'Hadi', 'Mobilio'),
('B 3000 VVF', 'Tama', 'Rize'),
('B 6718 CF', 'Satya', 'Sedan'),
('B 9101 GH', 'Putra', 'SUV');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`no_barang`);

--
-- Indexes for table `detailbarang`
--
ALTER TABLE `detailbarang`
  ADD KEY `fk_detail_barang_barang_1` (`no_barang`),
  ADD KEY `fk_detail_barang_nota_1` (`nota_no`);

--
-- Indexes for table `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`nota_no`),
  ADD KEY `fk_nota_Pelanggan_1` (`nopol`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`nopol`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detailbarang`
--
ALTER TABLE `detailbarang`
  ADD CONSTRAINT `fk_detail_barang_barang_1` FOREIGN KEY (`no_barang`) REFERENCES `barang` (`no_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_barang_nota_1` FOREIGN KEY (`nota_no`) REFERENCES `nota` (`nota_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `fk_nota_Pelanggan_1` FOREIGN KEY (`nopol`) REFERENCES `pelanggan` (`nopol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
