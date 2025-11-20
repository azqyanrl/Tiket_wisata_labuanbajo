-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 14, 2025 at 01:40 AM
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
  `kategori_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `galleries`
--

INSERT INTO `galleries` (`id`, `judul`, `gambar`, `kategori_id`, `created_at`) VALUES
(1, 'Pulau Padar', 'img_68f89274854823.34792108.jpg', 7, '2025-10-22 08:14:44'),
(2, 'Beach Pink', 'img_68fc75eae041e2.98224391.jpg', 4, '2025-10-25 07:02:02'),
(3, 'Kanawa Island', 'img_68fc760a4d8ca4.10410939.jpg', 1, '2025-10-25 07:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`, `created_at`) VALUES
(1, 'Snorkeling', 'Aktivitas menyelam permukaan untuk menikmati terumbu karang dan biota laut', '2025-10-25 05:15:00'),
(2, 'Diving', 'Selam scuba di Taman Nasional Komodo dengan spot mantaray dan karang', '2025-10-25 05:15:00'),
(3, 'Trekking', 'Hiking ke puncak Pulau Padar, bukit Amelia, dan air terjun Cunca Wulang', '2025-10-25 05:15:00'),
(4, 'Photography', 'Tur berfotografi di Pink Beach, Pulau Komodo, Taka Makassar, dan sunset', '2025-10-25 05:15:00'),
(5, 'Cultural', 'Kunjungan desa adat, festival Komodo, dan interaksi budaya lokal', '2025-10-25 05:15:00'),
(6, 'Liveaboard', 'Paket menginap di kapal phinisi untuk eksplorasi kepulauan', '2025-10-25 05:15:00'),
(7, 'Adventure', 'Aktivitas ekstrim dan petualangan di alam Labuan Bajo', '2025-10-25 05:15:00'),
(8, 'Relaxation', 'Paket relaksasi dan menikmati keindahan alam dengan santai', '2025-10-25 05:15:00'),
(9, 'Family', 'Paket wisata keluarga dengan aktivitas aman untuk anak-anak', '2025-10-25 05:15:00'),
(10, 'Eco Tourism', 'Wisata berbasis konservasi alam dan lingkungan', '2025-10-25 05:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `lokasi`
--

CREATE TABLE `lokasi` (
  `id` int NOT NULL,
  `nama_lokasi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lokasi`
--

INSERT INTO `lokasi` (`id`, `nama_lokasi`) VALUES
(1, 'Labuan Bajo'),
(2, 'Komodo Island'),
(3, 'Rinca Island'),
(4, 'Padar Island'),
(5, 'Pink Beach'),
(6, 'Kelor Island'),
(7, 'Kanawa Island'),
(8, 'Manta Point');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `pemesanan_id` int NOT NULL,
  `transaction_id` int DEFAULT NULL,
  `bukti_transfer` varchar(255) NOT NULL,
  `status` enum('unverified','verified','rejected') DEFAULT 'unverified',
  `tanggal_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `pemesanan_id`, `transaction_id`, `bukti_transfer`, `status`, `tanggal_upload`) VALUES
(2, 10, 2, 'Pembayaran langsung oleh admin - Qris', 'verified', '2025-11-06 09:41:41');

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
(7, 'LBJ20251025083051686', 2, 1, '2025-10-26', 1, '750000.00', 'batal', 'offline', 'booking', NULL, '2025-10-25 08:30:51', '2025-11-06 10:04:01'),
(8, 'LBJ20251025083142191', 2, 1, '2025-10-26', 1, '750000.00', 'batal', 'offline', 'booking', NULL, '2025-10-25 08:31:42', '2025-11-06 10:04:05'),
(9, 'LBJ20251025084009205', 2, 10, '2025-10-25', 1, '1200000.00', 'selesai', 'Tunai', 'booking', NULL, '2025-10-25 08:40:09', '2025-11-05 08:55:25'),
(10, 'LBJ20251106094033196', 2, 2, '2025-11-06', 1, '650000.00', 'selesai', 'Qris', 'booking', NULL, '2025-11-06 09:40:33', '2025-11-06 09:42:01');

-- --------------------------------------------------------

--
-- Table structure for table `tiket`
--

CREATE TABLE `tiket` (
  `id` int NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `durasi` varchar(50) NOT NULL,
  `kategori_id` int DEFAULT NULL,
  `tipe_trip_id` int DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `stok` int DEFAULT '0',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `fasilitas` text,
  `itinerary` text,
  `syarat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stok_default` int NOT NULL DEFAULT '0',
  `kapasitas` int DEFAULT NULL,
  `jadwal` varchar(255) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT 'Labuan Bajo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiket`
--

INSERT INTO `tiket` (`id`, `nama_paket`, `deskripsi`, `harga`, `durasi`, `kategori_id`, `tipe_trip_id`, `gambar`, `stok`, `status`, `fasilitas`, `itinerary`, `syarat`, `created_at`, `updated_at`, `stok_default`, `kapasitas`, `jadwal`, `lokasi`) VALUES
(1, 'One Day Snorkeling Trip', 'Tur satu hari full snorkeling ke 3 spot terbaik', '750000.00', '1 Hari', 1, NULL, 'tiket_1761370704.jpg', 50, 'aktif', 'Perahu speedboat AC, perlengkapan snorkel, makan siang, air mineral', '08.00 Penjemputan → 09.30 Snorkel spot 1 → 11.30 Spot 2 → 13.00 Makan → 14.30 Spot 3 → 16.30 Kembali', 'Wajib bisa berenang, membawa sunblock, topi', '2025-10-25 05:15:00', '2025-10-25 05:38:24', 50, NULL, NULL, 'Labuan Bajo'),
(2, 'One Day Trekking Padar', 'Hiking ke puncak Pulau Padar untuk sunrise dan landscape', '650000.00', '1 Hari', 3, NULL, 'tiket_1761370765.jpg', 40, 'aktif', 'Transportasi boat, pemandu, air mineral, snack', '04.30 Start → 06.00 Trekking → 08.00 Sunrise & foto → 10.00 Turun → 12.00 Kembali', 'Fisik prima, sepatu trekking, baju ganti', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 40, NULL, NULL, 'Padar Island'),
(3, '2D1N Liveaboard', 'Menginap di kapal phinisi, kunjungi 4 pulau', '1900000.00', '2 Hari 1 Malam', 6, NULL, 'tiket_1761371071.jpg', 30, 'aktif', 'Kabin AC, 3x makan, snack, teh/kopi, snorkel set, dokumetasi', 'Day1: Labuan Bajo–Kelor–Manjarite–Kalong. Day2: Padar–Pink Beach–Komodo–Labuan Bajo', 'Identitas valid, turut serta dalam safety briefing', '2025-10-25 05:15:00', '2025-10-25 05:44:31', 30, NULL, NULL, 'Labuan Bajo'),
(4, '3D2N Komodo Expedition', 'Eksplorasi taman nasional dengan liveaboard premium', '3300000.00', '3 Hari 2 Malam', 6, NULL, 'tiket_1761371326.jpg', 20, 'aktif', 'Kabin deluxe AC, 6x makan, snack, teh/kopi, snorkel & dive gear, drone, go-pro', 'Day1: Kelor–Manjarite–Kalong. Day2: Padar–Pink Beach–Komodo. Day3: Rinca–Kanawa–Labuan Bajo', 'Surat keterangan sehat, dive license (jika ikut diving)', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 20, NULL, NULL, 'Komodo Island'),
(5, 'Full Day Dive Trip', 'Selam scuba di Manta Point dan Batu Bolong', '1200000.00', '1 Hari', 2, NULL, 'tiket_1761373246.jpg', 25, 'aktif', 'Kapal dive, tank, weights, dive guide, makan siang', '07.00 Briefing → 08.30 Dive 1 → 11.00 Surface → 12.30 Dive 2 → 15.00 Kembali', 'Certifikasi diver, logbook', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 25, NULL, NULL, 'Manta Point'),
(6, 'Photography Tour', 'Tur berfotografi dengan spot sunset & wildlife', '850000.00', '1 Hari', 4, NULL, 'tiket_1761373319.jpg', 35, 'aktif', 'Transportasi boat, fotografer guide, air mineral, snack', '15.00 Start → 16.00 Pink Beach → 17.30 Kalong Sunset → 19.00 Kembali', 'Membawa kamera, tripod (opsional)', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 35, NULL, NULL, 'Pink Beach'),
(8, 'Cave Exploration', 'Eksplorasi Gua Rangko dan Gua Batu Cermin', '550000.00', '1 Hari', 7, NULL, 'tiket_1761379488.jpg', 30, 'aktif', 'Transportasi, pemandu gua, helm, senter, air mineral', '08.00 Start → 09.30 Gua Rangko → 12.00 Makan → 13.30 Gua Batu Cermin → 16.00 Kembali', 'Fisik prima, sepatu anti-slip, baju ganti', '2025-10-25 05:15:00', '2025-10-25 08:04:48', 30, NULL, NULL, 'Labuan Bajo'),
(9, 'Waterfall Adventure', 'Trekking ke Air Terjun Cunca Wulang dan Cunca Rami', '600000.00', '1 Hari', 3, NULL, 'tiket_1761379475.jpg', 35, 'aktif', 'Transportasi, pemandu, air mineral, snack', '07.00 Start → 09.00 Cunca Wulang → 12.00 Makan → 13.30 Cunca Rami → 16.30 Kembali', 'Fisik prima, sepatu trekking, baju ganti', '2025-10-25 05:15:00', '2025-10-25 08:04:35', 35, NULL, NULL, 'Labuan Bajo'),
(10, 'Sunset Cruise', 'Menikmati sunset dari atas kapal dengan dinner romantis', '1200000.00', '1 Hari', 8, NULL, 'tiket_1761379501.jpg', 20, 'aktif', 'Kapal cruise, dinner romantis, minuman, musik live', '16.00 Start → 17.30 Sunset → 19.00 Dinner → 21.00 Kembali', 'Pakaian formal, pasangan (untuk couple package)', '2025-10-25 05:15:00', '2025-10-25 08:05:01', 20, NULL, NULL, 'Labuan Bajo'),
(11, 'Family Package', 'Paket keluarga dengan aktivitas aman untuk anak-anak', '1500000.00', '1 Hari', 9, NULL, 'tiket_1761379516.jpg', 15, 'aktif', 'Kapal family, makan siang, snack, life jacket anak, pemandu', '09.00 Start → 10.30 Pink Beach → 12.00 Makan → 14.00 Snorkeling aman → 16.30 Kembali', 'Minimal 2 dewasa, anak usia 5-12 tahun', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 15, NULL, NULL, 'Pink Beach'),
(12, 'Eco Tourism', 'Wisata konservasi penyu dan penanaman mangrove', '800000.00', '1 Hari', 10, NULL, 'tiket_1761379535.jpg', 25, 'aktif', 'Transportasi, pemandu konservasi, alat penanaman, makan siang', '08.00 Start → 09.30 Konservasi penyu → 12.00 Makan → 13.30 Penanaman mangrove → 16.00 Kembali', 'Mencintai lingkungan, membawa topi', '2025-10-25 05:15:00', '2025-10-25 08:05:35', 25, NULL, NULL, 'Labuan Bajo'),
(13, 'Island Hopping', 'Mengunjungi 5 pulau dalam satu hari', '1100000.00', '1 Hari', 1, NULL, 'tiket_1761379555.jpg', 40, 'aktif', 'Speedboat, makan siang, air mineral, pemandu', '08.00 Start → 09.00 Pulau Kelor → 10.30 Pulau Manjarite → 12.00 Makan → 13.30 Pulau Kanawa → 15.00 Pulau Bidadari → 16.30 Kembali', 'Bisa berenang, membawa sunblock', '2025-10-25 05:15:00', '2025-10-25 08:05:55', 40, NULL, NULL, 'Labuan Bajo'),
(14, 'Romantic Getaway', 'Paket romantis untuk pasangan dengan dinner di pantai', '2500000.00', '2 Hari 1 Malam', 8, NULL, 'tiket_1761379545.jpg', 10, 'aktif', 'Resort pantai, dinner romantis, spa, transportasi', 'Day1: Check-in → 15.00 Spa → 18.00 Dinner → Day2: Breakfast → Check-out', 'Pasangan suami-istri/berpacaran, KTP', '2025-10-25 05:15:00', '2025-10-25 08:05:45', 10, NULL, NULL, 'Labuan Bajo'),
(15, 'Adventure Package', 'Kombinasi trekking, snorkeling, dan camping', '1800000.00', '2 Hari 1 Malam', 7, NULL, 'tiket_1761379566.jpg', 20, 'aktif', 'Tenda, sleeping bag, makan 3x, transportasi, pemandu', 'Day1: Trekking → Camping → Day2: Snorkeling → Kembali', 'Fisik prima, perlengkapan camping', '2025-10-25 05:15:00', '2025-10-25 08:06:06', 20, NULL, NULL, 'Labuan Bajo'),
(16, 'Premium Liveaboard', 'Liveaboard luxury dengan fasilitas premium', '5500000.00', '4 Hari 3 Malam', 6, NULL, 'tiket_1761379580.jpg', 10, 'aktif', 'Kabin suite, jacuzzi, private chef, drone, spa, dive equipment', 'Full exploration: Padar, Komodo, Rinca, Manta Point, Pink Beach, dll', 'Budget tinggi, persyaratan khusus', '2025-10-25 05:15:00', '2025-10-25 08:06:20', 10, NULL, NULL, 'Labuan Bajo'),
(17, 'Sunrise Photography', 'Tur fotografi sunrise di spot terbaik', '750000.00', '1 Hari', 4, NULL, 'tiket_1761379603.jpg', 30, 'aktif', 'Transportasi, fotografer guide, air mineral, snack', '04.00 Start → 05.30 Bukit Cinta → 07.00 Sunrise → 09.30 Sarapan → 11.00 Kembali', 'Membawa kamera, tripod, baju hangat', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 30, NULL, NULL, 'Padar Island'),
(18, 'Manta Point Special', 'Tur khusus ke Manta Point dengan peluang lihat manta', '950000.00', '1 Hari', 1, NULL, 'tiket_1761379591.avif', 25, 'aktif', 'Speedboat, perlengkapan snorkel, pemandu manta, makan siang', '07.00 Start → 08.30 Manta Point → 11.00 Spot 2 → 13.00 Makan → 15.00 Kembali', 'Bisa berenang, membawa underwater camera', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 25, NULL, NULL, 'Manta Point'),
(19, 'Desa Adat Tour', 'Mengenal kehidupan masyarakat adat Kampung Komodo', '650000.00', '1 Hari', 5, NULL, 'tiket_1761379612.jpg', 35, 'aktif', 'Transportasi, pemandu lokal, makan tradisional, souvenir', '08.00 Start → 09.30 Kampung Komodo → 12.00 Makan siang → 14.00 Interaksi → 16.00 Kembali', 'Menghormati adat, membawa uang tunai', '2025-10-25 05:15:00', '2025-11-05 10:19:47', 35, NULL, NULL, 'Komodo Island'),
(20, 'Beach Relaxation', 'Menikmati pantai dengan fasilitas beach club', '450000.00', '1 Hari', 8, NULL, 'tiket_1761379624.jpg', 50, 'aktif', 'Beach club access, sunbed, minuman, snack', '10.00 Start → Full day beach activities → 18.00 Kembali', 'Membawa swimwear, sunblock', '2025-10-25 05:15:00', '2025-10-25 08:07:04', 50, NULL, NULL, 'Labuan Bajo');

-- --------------------------------------------------------

--
-- Table structure for table `tiket_terjual_harian`
--

CREATE TABLE `tiket_terjual_harian` (
  `id` int NOT NULL,
  `tiket_id` int NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jumlah_terjual` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiket_terjual_harian`
--

INSERT INTO `tiket_terjual_harian` (`id`, `tiket_id`, `tanggal_kunjungan`, `jumlah_terjual`) VALUES
(4, 1, '2025-10-26', 1),
(5, 1, '2025-10-26', 1),
(6, 10, '2025-10-25', 1),
(7, 2, '2025-11-06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tipe_trip`
--

CREATE TABLE `tipe_trip` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipe_trip`
--

INSERT INTO `tipe_trip` (`id`, `nama`, `deskripsi`, `created_at`) VALUES
(1, 'Open Trip', 'Perjalanan bareng peserta lain', '2025-11-06 08:58:33'),
(2, 'Private Trip', 'Perjalanan khusus untuk grup sendiri', '2025-11-06 08:58:33'),
(3, 'Honeymoon Trip', 'Paket wisata romantis untuk pasangan', '2025-11-06 08:58:33');

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
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `tanggal_pesan` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `ticket_id`, `jumlah_tiket`, `total_harga`, `status`, `tanggal_pesan`) VALUES
(2, 2, 2, 1, '650000.00', 'paid', '2025-11-06 09:41:41');

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
  `lokasi` varchar(100) DEFAULT NULL,
  `role` enum('admin','user','posko') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lokasi_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nama_lengkap`, `no_hp`, `profile_photo`, `lokasi`, `role`, `created_at`, `lokasi_id`) VALUES
(1, 'nurulazqya', '$2y$10$1WfzyuZ7DoKakzDMqPdBTOmz/JZs8CJ5L8zJ7yWxTCBW89kI3LI0u', 'nurulazqya@student.smkn1rongga.sch.id', 'Nurul Azqya', '0812345678910', 'admin_1_1761120901.png', NULL, 'admin', '2025-10-22 08:13:33', NULL),
(2, 'satoru', '$2y$10$bRoUn6M9FO0YaG66gcUjs.2TSH3tvRqSwW/mrPQbe9YsL4tkyejUC', 'satoru@gmail.com', 'Gojo Satoru', '0812345678910', NULL, NULL, 'user', '2025-10-22 08:24:03', NULL),
(3, 'pulau_padar', '$2y$10$RGB3I2jzYgwYXfcGZJ6oxe4UxAxQOnqtQ7DqK8Tn.cJqKP.LVFGdO', 'admin_pulau_padar@gmail.com', 'pulau padar', '0812345678910', NULL, 'labuan bajo', 'posko', '2025-10-29 08:37:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `verifikasi_history`
--

CREATE TABLE `verifikasi_history` (
  `id` int NOT NULL,
  `pemesanan_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `metode_pembayaran` varchar(20) NOT NULL,
  `status` enum('pending','dibayar','selesai','batal') NOT NULL,
  `catatan` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `verifikasi_history`
--

INSERT INTO `verifikasi_history` (`id`, `pemesanan_id`, `admin_id`, `metode_pembayaran`, `status`, `catatan`, `created_at`) VALUES
(1, 9, 3, 'Tunai', 'dibayar', '', '2025-11-05 08:55:08'),
(2, 9, 3, 'Tunai', 'selesai', '', '2025-11-05 08:55:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_galleries_kategori` (`kategori_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_unique` (`nama`);

--
-- Indexes for table `lokasi`
--
ALTER TABLE `lokasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_fk_transaction` (`transaction_id`),
  ADD KEY `payments_fk_pemesanan` (`pemesanan_id`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pemesanan_fk_user` (`user_id`),
  ADD KEY `pemesanan_fk_tiket` (`tiket_id`),
  ADD KEY `idx_kode_booking` (`kode_booking`);

--
-- Indexes for table `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tiket_kategori` (`kategori_id`),
  ADD KEY `idx_lokasi` (`lokasi`),
  ADD KEY `fk_tiket_tipe_trip` (`tipe_trip_id`);

--
-- Indexes for table `tiket_terjual_harian`
--
ALTER TABLE `tiket_terjual_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal` (`tanggal_kunjungan`),
  ADD KEY `idx_tiket_tanggal` (`tiket_id`,`tanggal_kunjungan`);

--
-- Indexes for table `tipe_trip`
--
ALTER TABLE `tipe_trip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_fk_user` (`user_id`),
  ADD KEY `transactions_fk_tiket` (`ticket_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_lokasi` (`lokasi_id`);

--
-- Indexes for table `verifikasi_history`
--
ALTER TABLE `verifikasi_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pemesanan_id` (`pemesanan_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tiket_terjual_harian`
--
ALTER TABLE `tiket_terjual_harian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tipe_trip`
--
ALTER TABLE `tipe_trip`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `verifikasi_history`
--
ALTER TABLE `verifikasi_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `galleries`
--
ALTER TABLE `galleries`
  ADD CONSTRAINT `fk_galleries_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_fk_pemesanan` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_fk_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_fk_tiket` FOREIGN KEY (`tiket_id`) REFERENCES `tiket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pemesanan_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `fk_tiket_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tiket_tipe_trip` FOREIGN KEY (`tipe_trip_id`) REFERENCES `tipe_trip` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tiket_terjual_harian`
--
ALTER TABLE `tiket_terjual_harian`
  ADD CONSTRAINT `tiket_terjual_fk_tiket` FOREIGN KEY (`tiket_id`) REFERENCES `tiket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_fk_tiket` FOREIGN KEY (`ticket_id`) REFERENCES `tiket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_lokasi` FOREIGN KEY (`lokasi_id`) REFERENCES `lokasi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_lokasi` FOREIGN KEY (`lokasi_id`) REFERENCES `lokasi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `verifikasi_history`
--
ALTER TABLE `verifikasi_history`
  ADD CONSTRAINT `verifikasi_history_ibfk_1` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `verifikasi_history_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
