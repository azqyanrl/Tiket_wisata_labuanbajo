-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 20, 2025 at 06:24 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `labuanbajo`
--

-- --------------------------------------------------------

--
-- Table structure for table `galleries`
--

CREATE TABLE `galleries` (
  `id` int NOT NULL,
  `judul` varchar(100) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `galleries`
--

INSERT INTO `galleries` (`id`, `judul`, `gambar`, `kategori`, `created_at`) VALUES
(9, 'Pulau Padar', 'img_68f490c799d694.26849048.jpg', 'Adventure', '2025-10-19 07:18:31'),
(10, 'Beach Pink', 'img_68f59d206e2133.83734526.jpg', 'Snorkeling', '2025-10-20 02:23:28'),
(12, 'Kanawa Island', 'img_68f5d227023eb1.01393166.jpg', 'Snorkeling', '2025-10-20 06:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `pemesanan_id` int NOT NULL,
  `transaction_id` int DEFAULT NULL,
  `bukti_transfer` varchar(255) NOT NULL,
  `status` enum('unverified','verified','rejected') NOT NULL DEFAULT 'unverified',
  `tanggal_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `pemesanan_id`, `transaction_id`, `bukti_transfer`, `status`, `tanggal_upload`) VALUES
(4, 3, 7, 'Pembayaran langsung oleh admin - e-wallet', 'verified', '2025-10-09 10:09:32'),
(5, 1, 14, 'Pembayaran langsung oleh admin - tunai', 'verified', '2025-10-14 05:25:19'),
(6, 2, 9, 'Pembayaran langsung oleh admin - e-wallet', 'verified', '2025-10-14 05:25:39'),
(10, 4, 12, 'Diverifikasi langsung oleh admin', 'verified', '2025-10-19 03:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id` int NOT NULL,
  `kode_booking` varchar(50) NOT NULL,
  `user_id` int NOT NULL,
  `tiket_id` int NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jumlah_tiket` int NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('pending','dibayar','selesai','batal') DEFAULT 'pending',
  `metode_pembayaran` varchar(20) NOT NULL DEFAULT 'offline',
  `jenis` varchar(20) NOT NULL DEFAULT 'booking',
  `batas_waktu` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`id`, `kode_booking`, `user_id`, `tiket_id`, `tanggal_kunjungan`, `jumlah_tiket`, `total_harga`, `status`, `metode_pembayaran`, `jenis`, `batas_waktu`, `created_at`, `updated_at`) VALUES
(1, 'LBJ20251007093028313', 5, 2, '2025-10-21', 1, 1200000.00, 'selesai', 'Qris', 'booking', '2025-10-08 09:30:28', '2025-10-07 09:30:28', '2025-10-20 01:55:47'),
(2, 'LBJ20251008085055454', 5, 1, '2025-10-08', 2, 1700000.00, 'selesai', 'tunai', 'booking', '2025-10-09 08:50:55', '2025-10-08 08:50:55', '2025-10-14 06:50:43'),
(3, 'LBJ20251009094635879', 6, 1, '2025-10-09', 2, 1700000.00, 'selesai', 'Qris', 'booking', '2025-10-10 09:46:35', '2025-10-09 09:46:35', '2025-10-14 06:48:05'),
(4, 'LBJ20251014055502655', 5, 4, '2025-10-14', 3, 1950000.00, 'selesai', 'tunai', 'booking', '2025-10-15 05:55:02', '2025-10-14 05:55:02', '2025-10-19 03:49:41'),
(5, 'LBJ20251014063306306', 5, 3, '2025-10-14', 2, 1500000.00, 'batal', 'offline', 'booking', '2025-10-15 06:33:06', '2025-10-14 06:33:06', '2025-10-19 03:49:33'),
(6, 'LBJ20251020041045101', 5, 4, '2025-10-20', 3, 1950000.00, 'pending', 'offline', 'booking', '2025-10-21 04:10:45', '2025-10-20 04:10:45', '2025-10-20 04:10:45');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `rating` int NOT NULL,
  `review` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_harian`
--

CREATE TABLE `stok_harian` (
  `id` int NOT NULL,
  `tiket_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `stok_tersisa` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiket`
--

CREATE TABLE `tiket` (
  `id` int NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `durasi` varchar(50) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `stok` int DEFAULT '0',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `fasilitas` text,
  `itinerary` text,
  `syarat` text,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiket`
--

INSERT INTO `tiket` (`id`, `nama_paket`, `deskripsi`, `harga`, `durasi`, `kategori`, `gambar`, `stok`, `status`, `fasilitas`, `itinerary`, `syarat`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 'Pulau Padar', 'Nikmati keindahan panorama Pulau Padar dari puncak bukit dengan trekking yang menantang. Cocok bagi pecinta alam dan fotografi.', 850000.00, '1 Hari', 'Trekking', '68f5a85ee8cee_padar2.jpg', 50, 'aktif', 'Transportasi kapal PP, Makan siang, Air mineral, Pemandu wisata, Perlengkapan snorkeling, Asuransi perjalanan', '06:00 - Penjemputan di hotel; 07:00 - Berangkat ke lokasi; 08:30 - Aktivitas utama; 12:00 - Makan siang; 15:00 - Kembali ke Labuan Bajo', 'Pembayaran minimal 1 hari sebelum keberangkatan; Jadwal dapat berubah tergantung cuaca; Peserta wajib sehat; Anak-anak wajib didampingi orang dewasa', -8.649999, 119.706901, '2025-10-05 07:05:06', '2025-10-20 03:11:26'),
(2, 'Komodo Island', 'Petualangan seru menjelajahi habitat asli komodo serta snorkeling di Pink Beach dengan air laut sebening kristal.', 1200000.00, '2 Hari 1 Malam', 'Adventure', '68f5a1d05f4e0_komodo2.jpg', 40, 'aktif', 'Kapal PP, Akomodasi hotel, Makan 3x, Air mineral, Pemandu wisata, Snorkeling gear, Asuransi perjalanan', 'Hari 1: Penjemputan di hotel, perjalanan ke Pulau Komodo, trekking melihat komodo, snorkeling di Pink Beach, menginap di hotel; Hari 2: Sarapan, kegiatan tambahan, kembali ke Labuan Bajo', 'Pembayaran minimal 3 hari sebelum keberangkatan; Jadwal dapat berubah tergantung cuaca; Dilarang memberi makan komodo; Anak-anak wajib didampingi orang dewasa', -8.570000, 119.480000, '2025-10-05 07:05:06', '2025-10-20 03:10:34'),
(3, 'Kelor Island & Manjarite', 'Jelajahi Pulau Kelor dengan panorama menakjubkan dan snorkeling di Manjarite yang terkenal dengan biota lautnya.', 750000.00, '1 Hari', 'Snorkeling', '68f5a89f70c32_kelor_island.jpg', 77, 'aktif', 'Kapal PP, Makan siang, Air mineral, Pemandu wisata, Snorkeling gear, Asuransi perjalanan', '08:00 - Penjemputan di hotel; 09:00 - Berangkat ke Pulau Kelor; 10:00 - Trekking; 12:00 - Makan siang; 13:30 - Snorkeling di Manjarite; 16:30 - Kembali ke Labuan Bajo', 'Pembayaran minimal 1 hari sebelum keberangkatan; Jadwal dapat berubah tergantung cuaca; Peserta wajib sehat; Anak-anak wajib didampingi orang dewasa', -8.480000, 119.890000, '2025-10-05 07:05:06', '2025-10-20 03:12:31'),
(4, 'Pink Beach Snorkeling', 'Nikmati pengalaman unik di pantai berpasir merah muda sambil snorkeling bersama ikan tropis berwarna-warni.', 650000.00, '1 Hari', 'Snorkeling', '68f5a1bf84780_pantai.jpg', 88, 'aktif', 'Transportasi kapal, Pemandu wisata, Air mineral, Peralatan snorkeling, Asuransi', '08:00 - Penjemputan; 09:30 - Snorkeling di Pink Beach; 12:00 - Makan siang; 15:00 - Kembali ke pelabuhan', 'Jadwal tergantung cuaca; Pembayaran dilakukan sebelum keberangkatan; Peserta wajib mematuhi instruksi pemandu', -8.620000, 119.710000, '2025-10-05 07:05:06', '2025-10-20 02:43:11'),
(5, 'Gua Batu Cermin Tour', 'Eksplorasi gua alami dengan formasi batu yang memantulkan cahaya seperti cermin — wisata edukatif dan alami.', 350000.00, 'Setengah Hari', 'Cultural', '68f5a8feb7adb_gua_batu.jpg', 99, 'aktif', 'Transportasi darat, Pemandu wisata, Tiket masuk, Air mineral', '08:00 - Penjemputan; 08:30 - Kunjungan ke Gua Batu Cermin; 10:00 - Eksplorasi gua; 12:00 - Kembali ke hotel', 'Peserta wajib memakai sepatu tertutup; Dilarang menyentuh formasi batu; Peserta wajib sehat', -8.460000, 119.880000, '2025-10-05 07:05:06', '2025-10-20 03:14:06'),
(6, 'Kanawa Island', 'Pulau Kanawa menawarkan pasir putih halus dan terumbu karang berwarna-warni yang cocok untuk snorkeling.', 550000.00, '1 Hari', 'Snorkeling', '68f5a905b8283_kanawa.jpg', 80, 'aktif', NULL, NULL, NULL, -8.546000, 119.764000, '2025-10-05 07:05:06', '2025-10-20 06:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `jumlah_tiket` int NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `tanggal_pesan` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `ticket_id`, `jumlah_tiket`, `total_harga`, `status`, `tanggal_pesan`) VALUES
(1, 6, 1, 2, 1700000.00, 'paid', '2025-10-09 10:09:32'),
(2, 6, 1, 2, 1700000.00, 'paid', '2025-10-09 10:09:35'),
(3, 6, 1, 2, 1700000.00, 'paid', '2025-10-09 10:11:46'),
(4, 6, 1, 2, 1700000.00, 'paid', '2025-10-09 10:11:50'),
(5, 5, 2, 1, 1200000.00, 'paid', '2025-10-14 05:25:19'),
(6, 5, 1, 2, 1700000.00, 'paid', '2025-10-14 05:25:39'),
(7, 6, 1, 2, 1700000.00, 'paid', '2025-10-14 05:31:34'),
(8, 5, 2, 1, 1200000.00, 'paid', '2025-10-14 05:32:01'),
(9, 5, 1, 2, 1700000.00, 'paid', '2025-10-14 05:44:46'),
(10, 5, 2, 1, 1200000.00, 'paid', '2025-10-14 05:46:36'),
(11, 5, 2, 1, 1200000.00, 'paid', '2025-10-19 03:41:07'),
(12, 5, 4, 3, 1950000.00, 'paid', '2025-10-19 03:49:12'),
(13, 5, 2, 1, 1200000.00, 'paid', '2025-10-19 07:16:54'),
(14, 5, 2, 1, 1200000.00, 'paid', '2025-10-19 07:17:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nama_lengkap`, `no_hp`, `profile_photo`, `role`, `created_at`) VALUES
(1, 'nurulazqya', '$2y$10$Ppa.dT47Pr58xuZXEa2JpuFM7VDogewFZlegXEtJ/KYGKfB.RrDSS', 'nurulazqya@student.smkn1rongga.sch.id', 'nurul azqya', '083000000000', 'admin_1_1760858831.png', 'admin', '2025-09-30 08:45:54'),
(5, 'qya', '$2y$10$Zpc.lmOh.AKQwoE0nGVrK.kPErimCnfXRlxp0QOd6i53cwzypWwmC', 'qya@gmail.com', 'nurul azqya', '08310000000', NULL, 'user', '2025-10-02 09:07:36'),
(6, 'devi', '$2y$10$iDdi/Ow1HNw4yABAgJ02xuiXM7/JFN2t01O.cOUa8cY59aK0yr4P2', 'nengdevi@gmail.com', 'Neng Devi Yulianti', '083829010669', NULL, 'user', '2025-10-09 09:45:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `pemesanan_id` (`pemesanan_id`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tiket_id` (`tiket_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `stok_harian`
--
ALTER TABLE `stok_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tiket_id` (`tiket_id`);

--
-- Indexes for table `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stok_harian`
--
ALTER TABLE `stok_harian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`tiket_id`) REFERENCES `tiket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tiket` (`id`);

--
-- Constraints for table `stok_harian`
--
ALTER TABLE `stok_harian`
  ADD CONSTRAINT `stok_harian_ibfk_1` FOREIGN KEY (`tiket_id`) REFERENCES `tiket` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tiket` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
