-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2026 at 09:04 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simosis`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int UNSIGNED NOT NULL,
  `tahun_ajaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'nonaktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `tahun_ajaran`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`) VALUES
(1, '2026/2027', '2026-07-01', '2027-06-30', 'aktif', '2026-09-01 12:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int UNSIGNED NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `nama_kegiatan` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `lokasi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('rencana','berlangsung','selesai','dibatalkan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'rencana',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_committees`
--

CREATE TABLE `activity_committees` (
  `id` int UNSIGNED NOT NULL,
  `activity_id` int UNSIGNED NOT NULL,
  `osis_member_id` int UNSIGNED NOT NULL,
  `jabatan_panitia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` enum('semua','siswa','osis') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'semua',
  `tanggal_publish` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('draft','publish') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_settings`
--

CREATE TABLE `cash_settings` (
  `id` int UNSIGNED NOT NULL,
  `jenis_kas` enum('osis','kelas') NOT NULL,
  `class_id` int UNSIGNED DEFAULT NULL,
  `frekuensi` enum('bulanan','mingguan') NOT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tahun` year NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `dibuat_oleh` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cash_settings`
--

INSERT INTO `cash_settings` (`id`, `jenis_kas`, `class_id`, `frekuensi`, `nominal`, `tahun`, `status`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
(61, 'osis', NULL, 'mingguan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:21:20', '2026-09-10 16:21:20'),
(62, 'kelas', 8, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-10 16:22:04'),
(63, 'kelas', 8, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-14 07:22:38'),
(64, 'kelas', 8, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-14 07:22:38'),
(65, 'kelas', 7, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-10 16:22:04'),
(66, 'kelas', 9, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-10 16:22:04'),
(67, 'kelas', 7, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-14 07:22:38'),
(68, 'kelas', 7, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-14 07:22:38'),
(69, 'kelas', 5, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-10 16:22:04'),
(70, 'kelas', 5, 'bulanan', 50000.00, '2026', 'aktif', 2, '2026-09-10 16:22:04', '2026-09-14 07:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int UNSIGNED NOT NULL,
  `nama_kelas` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jurusan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `nama_kelas`, `tingkat`, `jurusan`, `created_at`) VALUES
(5, 'XII', 'XII', 'Rekayasa Perangkat Lunak', '2026-09-01 08:14:58'),
(7, 'XI', 'XI', 'Rekayasa Perangkat Lunak', '2026-09-09 14:45:37'),
(8, 'X', 'X', 'Rekayasa Perangkat Lunak', '2026-09-09 15:13:32'),
(9, 'XI', 'XI', 'Teknik Kendaraan Ringan', '2026-09-09 16:26:22'),
(10, 'XI', 'XI', 'Akutansi', '2026-09-15 07:03:29');

-- --------------------------------------------------------

--
-- Table structure for table `class_due_confirmations`
--

CREATE TABLE `class_due_confirmations` (
  `id` int UNSIGNED NOT NULL,
  `student_due_id` int UNSIGNED NOT NULL,
  `class_id` int UNSIGNED NOT NULL,
  `submitted_by` int UNSIGNED NOT NULL,
  `confirmed_by` int UNSIGNED DEFAULT NULL,
  `status` enum('menunggu','diterima','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `nominal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_officers`
--

CREATE TABLE `class_officers` (
  `id` int UNSIGNED NOT NULL,
  `student_id` int UNSIGNED NOT NULL,
  `class_id` int UNSIGNED NOT NULL,
  `position` enum('bendahara_kelas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year_id` int UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('aktif','menunggu','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_officers`
--

INSERT INTO `class_officers` (`id`, `student_id`, `class_id`, `position`, `academic_year_id`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 8, 'bendahara_kelas', 1, '2026-09-09', NULL, 'aktif', '2026-09-09 15:13:32', '2026-09-09 15:13:32'),
(2, 5, 9, 'bendahara_kelas', 1, '2026-09-10', NULL, 'aktif', '2026-09-10 03:08:22', '2026-09-10 03:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int UNSIGNED NOT NULL,
  `nama_jurusan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `singkatan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `nama_jurusan`, `singkatan`, `status`, `created_at`) VALUES
(1, 'Rekayasa Perangkat Lunak', 'RPL', 'aktif', '2026-09-09 13:25:54'),
(3, 'Teknik Komputer dan Jaringan', 'TKJ', 'aktif', '2026-09-09 16:24:36'),
(4, 'Teknik Kendaraan Ringan', 'TKR', 'aktif', '2026-09-09 16:25:09'),
(6, 'Agribisnis Pengelolahan Hasil Pertanian', 'APHP', 'aktif', '2026-09-11 04:20:08'),
(7, 'Akutansi', 'AKL', 'aktif', '2026-09-11 04:20:37'),
(8, 'Agribisnis Tanaman Pertanian', 'ATP', 'aktif', '2026-09-11 04:21:22');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int UNSIGNED NOT NULL,
  `activity_id` int UNSIGNED NOT NULL,
  `uploaded_by` int UNSIGNED NOT NULL,
  `nama_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_file` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `osis_members`
--

CREATE TABLE `osis_members` (
  `id` int UNSIGNED NOT NULL,
  `student_id` int UNSIGNED NOT NULL,
  `position_id` int UNSIGNED NOT NULL,
  `bagian` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year_id` int UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `alasan_keluar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','menunggu','nonaktif','keluar','lulus') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `osis_members`
--

INSERT INTO `osis_members` (`id`, `student_id`, `position_id`, `bagian`, `academic_year_id`, `tanggal_mulai`, `tanggal_selesai`, `alasan_keluar`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 4, NULL, 1, '2026-09-09', NULL, NULL, 'aktif', '2026-09-09 14:45:37', '2026-09-09 14:46:10'),
(3, 6, 5, NULL, 1, '2026-09-15', NULL, '', 'keluar', '2026-09-15 07:03:29', '2026-09-16 02:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `osis_positions`
--

CREATE TABLE `osis_positions` (
  `id` int UNSIGNED NOT NULL,
  `nama_jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `osis_positions`
--

INSERT INTO `osis_positions` (`id`, `nama_jabatan`, `deskripsi`, `created_at`) VALUES
(1, 'Ketua', 'Ketua OSIS', '2026-09-01 07:38:03'),
(2, 'Wakil Ketua', 'Wakil Ketua OSIS', '2026-09-01 07:38:03'),
(3, 'Sekretaris', 'Sekretaris OSIS', '2026-09-01 07:38:03'),
(4, 'Bendahara', 'Bendahara OSIS', '2026-09-01 07:38:03'),
(5, 'Anggota', 'Anggota OSIS', '2026-09-01 07:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `osis_transactions`
--

CREATE TABLE `osis_transactions` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bukti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `osis_transaction_categories`
--

CREATE TABLE `osis_transaction_categories` (
  `id` int UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('pemasukan','pengeluaran') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `osis_transaction_categories`
--

INSERT INTO `osis_transaction_categories` (`id`, `nama_kategori`, `jenis`, `created_at`) VALUES
(1, 'Kas Anggota', 'pemasukan', '2026-09-01 07:40:22'),
(2, 'Dana Sekolah', 'pemasukan', '2026-09-01 07:40:22'),
(3, 'Donasi', 'pemasukan', '2026-09-01 07:40:22'),
(4, 'Sponsor', 'pemasukan', '2026-09-01 07:40:22'),
(5, 'Dana Kegiatan', 'pemasukan', '2026-09-01 07:40:22'),
(6, 'Konsumsi', 'pengeluaran', '2026-09-01 07:40:22'),
(7, 'Peralatan', 'pengeluaran', '2026-09-01 07:40:22'),
(8, 'Transportasi', 'pengeluaran', '2026-09-01 07:40:22'),
(9, 'Dekorasi', 'pengeluaran', '2026-09-01 07:40:22'),
(10, 'Dokumentasi', 'pengeluaran', '2026-09-01 07:40:22');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int UNSIGNED NOT NULL,
  `activity_id` int UNSIGNED DEFAULT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `tipe` enum('kegiatan','pembayaran','rapat','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'lainnya',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `nama_lengkap` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_id` int UNSIGNED NOT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `nama_lengkap`, `kelas_id`, `jenis_kelamin`, `no_hp`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 2, 'albu', 7, 'L', NULL, NULL, '2026-09-09 14:45:37', '2026-09-09 14:45:37'),
(2, 3, 'yusuf', 8, 'L', NULL, NULL, '2026-09-09 15:13:32', '2026-09-09 15:13:32'),
(3, NULL, 'ridho', 8, 'L', NULL, NULL, '2026-09-09 15:20:44', '2026-09-09 15:20:44'),
(5, 5, 'marcelino', 9, 'L', NULL, NULL, '2026-09-10 03:08:22', '2026-09-10 03:08:22'),
(6, 6, 'fikky', 10, 'L', NULL, NULL, '2026-09-15 07:03:29', '2026-09-15 07:03:29');

-- --------------------------------------------------------

--
-- Table structure for table `student_class_history`
--

CREATE TABLE `student_class_history` (
  `id` int UNSIGNED NOT NULL,
  `student_id` int UNSIGNED NOT NULL,
  `class_id` int UNSIGNED NOT NULL,
  `academic_year_id` int UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('aktif','nonaktif','pindah','lulus') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_class_history`
--

INSERT INTO `student_class_history` (`id`, `student_id`, `class_id`, `academic_year_id`, `tanggal_mulai`, `tanggal_selesai`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 1, '2026-07-01', NULL, 'aktif', 'Data awal saat migrasi riwayat kelas', '2026-09-14 02:49:52', '2026-09-14 02:49:52'),
(2, 2, 8, 1, '2026-07-01', NULL, 'aktif', 'Data awal saat migrasi riwayat kelas', '2026-09-14 02:49:52', '2026-09-14 02:49:52'),
(3, 3, 8, 1, '2026-07-01', NULL, 'aktif', 'Data awal saat migrasi riwayat kelas', '2026-09-14 02:49:52', '2026-09-14 02:49:52'),
(4, 5, 9, 1, '2026-07-01', NULL, 'aktif', 'Data awal saat migrasi riwayat kelas', '2026-09-14 02:49:52', '2026-09-14 02:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `student_dues`
--

CREATE TABLE `student_dues` (
  `id` int UNSIGNED NOT NULL,
  `setting_id` int UNSIGNED DEFAULT NULL,
  `jenis_kas` enum('osis','kelas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'osis',
  `class_id` int UNSIGNED DEFAULT NULL,
  `frekuensi` enum('bulanan','mingguan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bulanan',
  `periode` int NOT NULL DEFAULT '1',
  `bulan` tinyint UNSIGNED NOT NULL,
  `tahun` year NOT NULL,
  `minggu_ke` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `nominal` decimal(15,2) NOT NULL DEFAULT '10000.00',
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `dibuat_oleh` int UNSIGNED DEFAULT NULL,
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_dues`
--

INSERT INTO `student_dues` (`id`, `setting_id`, `jenis_kas`, `class_id`, `frekuensi`, `periode`, `bulan`, `tahun`, `minggu_ke`, `nominal`, `tanggal_jatuh_tempo`, `tanggal_mulai`, `tanggal_selesai`, `dibuat_oleh`, `status`, `created_at`) VALUES
(1, 1, 'osis', NULL, 'bulanan', 1, 1, '2026', 0, 50000.00, NULL, '2026-01-01', '2026-01-31', 2, 'nonaktif', '2026-09-09 14:59:23'),
(2, 1, 'osis', NULL, 'bulanan', 1, 2, '2026', 0, 50000.00, NULL, '2026-02-01', '2026-02-28', 2, 'nonaktif', '2026-09-09 14:59:23'),
(3, 1, 'osis', NULL, 'bulanan', 1, 3, '2026', 0, 50000.00, NULL, '2026-03-01', '2026-03-31', 2, 'nonaktif', '2026-09-09 14:59:23'),
(4, 1, 'osis', NULL, 'bulanan', 1, 4, '2026', 0, 50000.00, NULL, '2026-04-01', '2026-04-30', 2, 'nonaktif', '2026-09-09 14:59:23'),
(5, 1, 'osis', NULL, 'bulanan', 1, 5, '2026', 0, 50000.00, NULL, '2026-05-01', '2026-05-31', 2, 'nonaktif', '2026-09-09 14:59:23'),
(6, 1, 'osis', NULL, 'bulanan', 1, 6, '2026', 0, 50000.00, NULL, '2026-06-01', '2026-06-30', 2, 'nonaktif', '2026-09-09 14:59:23'),
(7, 1, 'osis', NULL, 'bulanan', 1, 7, '2026', 0, 50000.00, NULL, '2026-07-01', '2026-07-31', 2, 'nonaktif', '2026-09-09 14:59:23'),
(8, 1, 'osis', NULL, 'bulanan', 1, 8, '2026', 0, 50000.00, NULL, '2026-08-01', '2026-08-31', 2, 'nonaktif', '2026-09-09 14:59:23'),
(13, 2, 'kelas', 8, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(14, 2, 'kelas', 8, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(15, 2, 'kelas', 8, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(16, 2, 'kelas', 8, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(17, 2, 'kelas', 8, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(18, 2, 'kelas', 8, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(19, 2, 'kelas', 8, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(20, 2, 'kelas', 8, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(21, 2, 'kelas', 8, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(22, 2, 'kelas', 8, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(23, 2, 'kelas', 8, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(24, 2, 'kelas', 8, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(25, 2, 'kelas', 8, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(26, 2, 'kelas', 8, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(27, 2, 'kelas', 8, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(28, 2, 'kelas', 8, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(29, 2, 'kelas', 8, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(30, 2, 'kelas', 8, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(31, 2, 'kelas', 8, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(32, 2, 'kelas', 8, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(33, 2, 'kelas', 8, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(34, 2, 'kelas', 8, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(35, 2, 'kelas', 8, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(36, 2, 'kelas', 8, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(37, 2, 'kelas', 8, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(38, 2, 'kelas', 8, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(39, 2, 'kelas', 8, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(40, 2, 'kelas', 8, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(41, 2, 'kelas', 8, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(42, 2, 'kelas', 8, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(43, 2, 'kelas', 8, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(44, 2, 'kelas', 8, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(45, 2, 'kelas', 8, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(46, 2, 'kelas', 8, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(47, 2, 'kelas', 8, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(48, 2, 'kelas', 8, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(49, 2, 'kelas', 8, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(50, 2, 'kelas', 8, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(51, 2, 'kelas', 8, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(52, 2, 'kelas', 8, 'mingguan', 1, 9, '2026', 1, 50000.00, NULL, '2026-09-01', '2026-09-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(56, 2, 'kelas', 8, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(66, 2, 'kelas', 8, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(71, 2, 'kelas', 8, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(72, 3, 'kelas', 8, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(73, 3, 'kelas', 8, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(74, 3, 'kelas', 8, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(75, 3, 'kelas', 8, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(76, 3, 'kelas', 8, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(77, 3, 'kelas', 8, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(78, 3, 'kelas', 8, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(79, 3, 'kelas', 8, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(80, 3, 'kelas', 8, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(81, 3, 'kelas', 8, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(82, 3, 'kelas', 8, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(83, 3, 'kelas', 8, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(84, 3, 'kelas', 8, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(85, 3, 'kelas', 8, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(86, 3, 'kelas', 8, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(87, 3, 'kelas', 8, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(88, 3, 'kelas', 8, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(89, 3, 'kelas', 8, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(90, 3, 'kelas', 8, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(91, 3, 'kelas', 8, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(92, 3, 'kelas', 8, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(93, 3, 'kelas', 8, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(94, 3, 'kelas', 8, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(95, 3, 'kelas', 8, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(96, 3, 'kelas', 8, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(97, 3, 'kelas', 8, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(98, 3, 'kelas', 8, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(99, 3, 'kelas', 8, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(100, 3, 'kelas', 8, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(101, 3, 'kelas', 8, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(102, 3, 'kelas', 8, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(103, 3, 'kelas', 8, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(104, 3, 'kelas', 8, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(105, 3, 'kelas', 8, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(106, 3, 'kelas', 8, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(107, 3, 'kelas', 8, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(108, 3, 'kelas', 8, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(109, 3, 'kelas', 8, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(110, 3, 'kelas', 8, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(115, 3, 'kelas', 8, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(125, 3, 'kelas', 8, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(130, 3, 'kelas', 8, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(131, 4, 'kelas', 8, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(132, 4, 'kelas', 8, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(133, 4, 'kelas', 8, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(134, 4, 'kelas', 8, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(135, 4, 'kelas', 8, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(136, 4, 'kelas', 8, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(137, 4, 'kelas', 8, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(138, 4, 'kelas', 8, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(139, 4, 'kelas', 8, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(140, 4, 'kelas', 8, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(141, 4, 'kelas', 8, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(142, 4, 'kelas', 8, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(143, 4, 'kelas', 8, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(144, 4, 'kelas', 8, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(145, 4, 'kelas', 8, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(146, 4, 'kelas', 8, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(147, 4, 'kelas', 8, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(148, 4, 'kelas', 8, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(149, 4, 'kelas', 8, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(150, 4, 'kelas', 8, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(151, 4, 'kelas', 8, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(152, 4, 'kelas', 8, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(153, 4, 'kelas', 8, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(154, 4, 'kelas', 8, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(155, 4, 'kelas', 8, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(156, 4, 'kelas', 8, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(157, 4, 'kelas', 8, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(158, 4, 'kelas', 8, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(159, 4, 'kelas', 8, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(160, 4, 'kelas', 8, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(161, 4, 'kelas', 8, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(162, 4, 'kelas', 8, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(163, 4, 'kelas', 8, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(164, 4, 'kelas', 8, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(165, 4, 'kelas', 8, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(166, 4, 'kelas', 8, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(167, 4, 'kelas', 8, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(168, 4, 'kelas', 8, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(169, 4, 'kelas', 8, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(174, 4, 'kelas', 8, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(184, 4, 'kelas', 8, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(189, 4, 'kelas', 8, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(190, 5, 'kelas', 7, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(191, 5, 'kelas', 7, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(192, 5, 'kelas', 7, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(193, 5, 'kelas', 7, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(194, 5, 'kelas', 7, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(195, 5, 'kelas', 7, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(196, 5, 'kelas', 7, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(197, 5, 'kelas', 7, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(198, 5, 'kelas', 7, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(199, 5, 'kelas', 7, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(200, 5, 'kelas', 7, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(201, 5, 'kelas', 7, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(202, 5, 'kelas', 7, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(203, 5, 'kelas', 7, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(204, 5, 'kelas', 7, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(205, 5, 'kelas', 7, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(206, 5, 'kelas', 7, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(207, 5, 'kelas', 7, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(208, 5, 'kelas', 7, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(209, 5, 'kelas', 7, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(210, 5, 'kelas', 7, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(211, 5, 'kelas', 7, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(212, 5, 'kelas', 7, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(213, 5, 'kelas', 7, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(214, 5, 'kelas', 7, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(215, 5, 'kelas', 7, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(216, 5, 'kelas', 7, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(217, 5, 'kelas', 7, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(218, 5, 'kelas', 7, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(219, 5, 'kelas', 7, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(220, 5, 'kelas', 7, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(221, 5, 'kelas', 7, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(222, 5, 'kelas', 7, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(223, 5, 'kelas', 7, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(224, 5, 'kelas', 7, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(225, 5, 'kelas', 7, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(226, 5, 'kelas', 7, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(227, 5, 'kelas', 7, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(228, 5, 'kelas', 7, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(233, 5, 'kelas', 7, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(243, 5, 'kelas', 7, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(248, 5, 'kelas', 7, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(249, 6, 'kelas', 7, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(250, 6, 'kelas', 7, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(251, 6, 'kelas', 7, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(252, 6, 'kelas', 7, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(253, 6, 'kelas', 7, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(254, 6, 'kelas', 7, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(255, 6, 'kelas', 7, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(256, 6, 'kelas', 7, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(257, 6, 'kelas', 7, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(258, 6, 'kelas', 7, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(259, 6, 'kelas', 7, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(260, 6, 'kelas', 7, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(261, 6, 'kelas', 7, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(262, 6, 'kelas', 7, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(263, 6, 'kelas', 7, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(264, 6, 'kelas', 7, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(265, 6, 'kelas', 7, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(266, 6, 'kelas', 7, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(267, 6, 'kelas', 7, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(268, 6, 'kelas', 7, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(269, 6, 'kelas', 7, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(270, 6, 'kelas', 7, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(271, 6, 'kelas', 7, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(272, 6, 'kelas', 7, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(273, 6, 'kelas', 7, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(274, 6, 'kelas', 7, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(275, 6, 'kelas', 7, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(276, 6, 'kelas', 7, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(277, 6, 'kelas', 7, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(278, 6, 'kelas', 7, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(279, 6, 'kelas', 7, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(280, 6, 'kelas', 7, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(281, 6, 'kelas', 7, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(282, 6, 'kelas', 7, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(283, 6, 'kelas', 7, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(284, 6, 'kelas', 7, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(285, 6, 'kelas', 7, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(286, 6, 'kelas', 7, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(287, 6, 'kelas', 7, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(292, 6, 'kelas', 7, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(302, 6, 'kelas', 7, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(307, 6, 'kelas', 7, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(308, 7, 'kelas', 7, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(309, 7, 'kelas', 7, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(310, 7, 'kelas', 7, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(311, 7, 'kelas', 7, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(312, 7, 'kelas', 7, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(313, 7, 'kelas', 7, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(314, 7, 'kelas', 7, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(315, 7, 'kelas', 7, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(316, 7, 'kelas', 7, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(317, 7, 'kelas', 7, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(318, 7, 'kelas', 7, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(319, 7, 'kelas', 7, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(320, 7, 'kelas', 7, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(321, 7, 'kelas', 7, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(322, 7, 'kelas', 7, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(323, 7, 'kelas', 7, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(324, 7, 'kelas', 7, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(325, 7, 'kelas', 7, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(326, 7, 'kelas', 7, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(327, 7, 'kelas', 7, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(328, 7, 'kelas', 7, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(329, 7, 'kelas', 7, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(330, 7, 'kelas', 7, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(331, 7, 'kelas', 7, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(332, 7, 'kelas', 7, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(333, 7, 'kelas', 7, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(334, 7, 'kelas', 7, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(335, 7, 'kelas', 7, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(336, 7, 'kelas', 7, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(337, 7, 'kelas', 7, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(338, 7, 'kelas', 7, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(339, 7, 'kelas', 7, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(340, 7, 'kelas', 7, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(341, 7, 'kelas', 7, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(342, 7, 'kelas', 7, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(343, 7, 'kelas', 7, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(344, 7, 'kelas', 7, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(345, 7, 'kelas', 7, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(346, 7, 'kelas', 7, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(351, 7, 'kelas', 7, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(361, 7, 'kelas', 7, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(366, 7, 'kelas', 7, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(367, 8, 'kelas', 5, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(368, 8, 'kelas', 5, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(369, 8, 'kelas', 5, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(370, 8, 'kelas', 5, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(371, 8, 'kelas', 5, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(372, 8, 'kelas', 5, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(373, 8, 'kelas', 5, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(374, 8, 'kelas', 5, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(375, 8, 'kelas', 5, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(376, 8, 'kelas', 5, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(377, 8, 'kelas', 5, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(378, 8, 'kelas', 5, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(379, 8, 'kelas', 5, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(380, 8, 'kelas', 5, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(381, 8, 'kelas', 5, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(382, 8, 'kelas', 5, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(383, 8, 'kelas', 5, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(384, 8, 'kelas', 5, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(385, 8, 'kelas', 5, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(386, 8, 'kelas', 5, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(387, 8, 'kelas', 5, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(388, 8, 'kelas', 5, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(389, 8, 'kelas', 5, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(390, 8, 'kelas', 5, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(391, 8, 'kelas', 5, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(392, 8, 'kelas', 5, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(393, 8, 'kelas', 5, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(394, 8, 'kelas', 5, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(395, 8, 'kelas', 5, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(396, 8, 'kelas', 5, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(397, 8, 'kelas', 5, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(398, 8, 'kelas', 5, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(399, 8, 'kelas', 5, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(400, 8, 'kelas', 5, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(401, 8, 'kelas', 5, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:19'),
(402, 8, 'kelas', 5, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:19'),
(403, 8, 'kelas', 5, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:19'),
(404, 8, 'kelas', 5, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:19'),
(405, 8, 'kelas', 5, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:19'),
(410, 8, 'kelas', 5, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:19'),
(420, 8, 'kelas', 5, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:20'),
(425, 8, 'kelas', 5, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(426, 9, 'kelas', 5, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(427, 9, 'kelas', 5, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(428, 9, 'kelas', 5, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(429, 9, 'kelas', 5, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(430, 9, 'kelas', 5, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(431, 9, 'kelas', 5, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(432, 9, 'kelas', 5, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(433, 9, 'kelas', 5, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(434, 9, 'kelas', 5, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(435, 9, 'kelas', 5, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(436, 9, 'kelas', 5, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(437, 9, 'kelas', 5, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(438, 9, 'kelas', 5, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(439, 9, 'kelas', 5, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(440, 9, 'kelas', 5, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(441, 9, 'kelas', 5, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(442, 9, 'kelas', 5, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(443, 9, 'kelas', 5, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(444, 9, 'kelas', 5, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'nonaktif', '2026-09-09 16:00:20'),
(445, 9, 'kelas', 5, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(446, 9, 'kelas', 5, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(447, 9, 'kelas', 5, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(448, 9, 'kelas', 5, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(449, 9, 'kelas', 5, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(450, 9, 'kelas', 5, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(451, 9, 'kelas', 5, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(452, 9, 'kelas', 5, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(453, 9, 'kelas', 5, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(454, 9, 'kelas', 5, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'nonaktif', '2026-09-09 16:00:20'),
(455, 9, 'kelas', 5, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(456, 9, 'kelas', 5, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(457, 9, 'kelas', 5, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(458, 9, 'kelas', 5, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(459, 9, 'kelas', 5, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(460, 9, 'kelas', 5, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'nonaktif', '2026-09-09 16:00:20'),
(461, 9, 'kelas', 5, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'nonaktif', '2026-09-09 16:00:20'),
(462, 9, 'kelas', 5, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'nonaktif', '2026-09-09 16:00:20'),
(463, 9, 'kelas', 5, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'nonaktif', '2026-09-09 16:00:20'),
(464, 9, 'kelas', 5, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(469, 9, 'kelas', 5, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-09 16:00:20'),
(479, 9, 'kelas', 5, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-09 16:00:20'),
(484, 9, 'kelas', 5, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-09 16:00:20'),
(485, 10, 'osis', NULL, 'bulanan', 1, 1, '2026', 0, 50000.00, NULL, '2026-01-01', '2026-01-31', 2, 'nonaktif', '2026-09-10 03:34:51'),
(486, 10, 'osis', NULL, 'bulanan', 1, 2, '2026', 0, 50000.00, NULL, '2026-02-01', '2026-02-28', 2, 'nonaktif', '2026-09-10 03:34:51'),
(487, 10, 'osis', NULL, 'bulanan', 1, 3, '2026', 0, 50000.00, NULL, '2026-03-01', '2026-03-31', 2, 'nonaktif', '2026-09-10 03:34:51'),
(488, 10, 'osis', NULL, 'bulanan', 1, 4, '2026', 0, 50000.00, NULL, '2026-04-01', '2026-04-30', 2, 'nonaktif', '2026-09-10 03:34:51'),
(489, 10, 'osis', NULL, 'bulanan', 1, 5, '2026', 0, 50000.00, NULL, '2026-05-01', '2026-05-31', 2, 'nonaktif', '2026-09-10 03:34:51'),
(490, 10, 'osis', NULL, 'bulanan', 1, 6, '2026', 0, 50000.00, NULL, '2026-06-01', '2026-06-30', 2, 'nonaktif', '2026-09-10 03:34:51'),
(491, 10, 'osis', NULL, 'bulanan', 1, 7, '2026', 0, 50000.00, NULL, '2026-07-01', '2026-07-31', 2, 'nonaktif', '2026-09-10 03:34:51'),
(492, 10, 'osis', NULL, 'bulanan', 1, 8, '2026', 0, 50000.00, NULL, '2026-08-01', '2026-08-31', 2, 'nonaktif', '2026-09-10 03:34:51'),
(497, 11, 'osis', NULL, 'bulanan', 1, 1, '2026', 0, 50000.00, NULL, '2026-01-01', '2026-01-31', 2, 'nonaktif', '2026-09-10 03:35:17'),
(498, 11, 'osis', NULL, 'bulanan', 1, 2, '2026', 0, 50000.00, NULL, '2026-02-01', '2026-02-28', 2, 'nonaktif', '2026-09-10 03:35:17'),
(499, 11, 'osis', NULL, 'bulanan', 1, 3, '2026', 0, 50000.00, NULL, '2026-03-01', '2026-03-31', 2, 'nonaktif', '2026-09-10 03:35:17'),
(500, 11, 'osis', NULL, 'bulanan', 1, 4, '2026', 0, 50000.00, NULL, '2026-04-01', '2026-04-30', 2, 'nonaktif', '2026-09-10 03:35:17'),
(501, 11, 'osis', NULL, 'bulanan', 1, 5, '2026', 0, 50000.00, NULL, '2026-05-01', '2026-05-31', 2, 'nonaktif', '2026-09-10 03:35:17'),
(502, 11, 'osis', NULL, 'bulanan', 1, 6, '2026', 0, 50000.00, NULL, '2026-06-01', '2026-06-30', 2, 'nonaktif', '2026-09-10 03:35:17'),
(503, 11, 'osis', NULL, 'bulanan', 1, 7, '2026', 0, 50000.00, NULL, '2026-07-01', '2026-07-31', 2, 'nonaktif', '2026-09-10 03:35:17'),
(504, 11, 'osis', NULL, 'bulanan', 1, 8, '2026', 0, 50000.00, NULL, '2026-08-01', '2026-08-31', 2, 'nonaktif', '2026-09-10 03:35:17'),
(509, 12, 'osis', NULL, 'mingguan', 1, 1, '2026', 1, 50000.00, NULL, '2026-01-01', '2026-01-07', 2, 'aktif', '2026-09-10 03:35:41'),
(510, 12, 'osis', NULL, 'mingguan', 1, 1, '2026', 2, 50000.00, NULL, '2026-01-08', '2026-01-14', 2, 'aktif', '2026-09-10 03:35:41'),
(511, 12, 'osis', NULL, 'mingguan', 1, 1, '2026', 3, 50000.00, NULL, '2026-01-15', '2026-01-21', 2, 'aktif', '2026-09-10 03:35:41'),
(512, 12, 'osis', NULL, 'mingguan', 1, 1, '2026', 4, 50000.00, NULL, '2026-01-22', '2026-01-28', 2, 'aktif', '2026-09-10 03:35:41'),
(513, 12, 'osis', NULL, 'mingguan', 1, 1, '2026', 5, 50000.00, NULL, '2026-01-29', '2026-01-31', 2, 'aktif', '2026-09-10 03:35:41'),
(514, 12, 'osis', NULL, 'mingguan', 1, 2, '2026', 1, 50000.00, NULL, '2026-02-01', '2026-02-07', 2, 'aktif', '2026-09-10 03:35:41'),
(515, 12, 'osis', NULL, 'mingguan', 1, 2, '2026', 2, 50000.00, NULL, '2026-02-08', '2026-02-14', 2, 'aktif', '2026-09-10 03:35:41'),
(516, 12, 'osis', NULL, 'mingguan', 1, 2, '2026', 3, 50000.00, NULL, '2026-02-15', '2026-02-21', 2, 'aktif', '2026-09-10 03:35:41'),
(517, 12, 'osis', NULL, 'mingguan', 1, 2, '2026', 4, 50000.00, NULL, '2026-02-22', '2026-02-28', 2, 'aktif', '2026-09-10 03:35:41'),
(518, 12, 'osis', NULL, 'mingguan', 1, 3, '2026', 1, 50000.00, NULL, '2026-03-01', '2026-03-07', 2, 'aktif', '2026-09-10 03:35:41'),
(519, 12, 'osis', NULL, 'mingguan', 1, 3, '2026', 2, 50000.00, NULL, '2026-03-08', '2026-03-14', 2, 'aktif', '2026-09-10 03:35:41'),
(520, 12, 'osis', NULL, 'mingguan', 1, 3, '2026', 3, 50000.00, NULL, '2026-03-15', '2026-03-21', 2, 'aktif', '2026-09-10 03:35:41'),
(521, 12, 'osis', NULL, 'mingguan', 1, 3, '2026', 4, 50000.00, NULL, '2026-03-22', '2026-03-28', 2, 'aktif', '2026-09-10 03:35:41'),
(522, 12, 'osis', NULL, 'mingguan', 1, 3, '2026', 5, 50000.00, NULL, '2026-03-29', '2026-03-31', 2, 'aktif', '2026-09-10 03:35:41'),
(523, 12, 'osis', NULL, 'mingguan', 1, 4, '2026', 1, 50000.00, NULL, '2026-04-01', '2026-04-07', 2, 'aktif', '2026-09-10 03:35:41'),
(524, 12, 'osis', NULL, 'mingguan', 1, 4, '2026', 2, 50000.00, NULL, '2026-04-08', '2026-04-14', 2, 'aktif', '2026-09-10 03:35:41'),
(525, 12, 'osis', NULL, 'mingguan', 1, 4, '2026', 3, 50000.00, NULL, '2026-04-15', '2026-04-21', 2, 'aktif', '2026-09-10 03:35:41'),
(526, 12, 'osis', NULL, 'mingguan', 1, 4, '2026', 4, 50000.00, NULL, '2026-04-22', '2026-04-28', 2, 'aktif', '2026-09-10 03:35:41'),
(527, 12, 'osis', NULL, 'mingguan', 1, 4, '2026', 5, 50000.00, NULL, '2026-04-29', '2026-04-30', 2, 'aktif', '2026-09-10 03:35:41');
INSERT INTO `student_dues` (`id`, `setting_id`, `jenis_kas`, `class_id`, `frekuensi`, `periode`, `bulan`, `tahun`, `minggu_ke`, `nominal`, `tanggal_jatuh_tempo`, `tanggal_mulai`, `tanggal_selesai`, `dibuat_oleh`, `status`, `created_at`) VALUES
(528, 12, 'osis', NULL, 'mingguan', 1, 5, '2026', 1, 50000.00, NULL, '2026-05-01', '2026-05-07', 2, 'aktif', '2026-09-10 03:35:41'),
(529, 12, 'osis', NULL, 'mingguan', 1, 5, '2026', 2, 50000.00, NULL, '2026-05-08', '2026-05-14', 2, 'aktif', '2026-09-10 03:35:41'),
(530, 12, 'osis', NULL, 'mingguan', 1, 5, '2026', 3, 50000.00, NULL, '2026-05-15', '2026-05-21', 2, 'aktif', '2026-09-10 03:35:41'),
(531, 12, 'osis', NULL, 'mingguan', 1, 5, '2026', 4, 50000.00, NULL, '2026-05-22', '2026-05-28', 2, 'aktif', '2026-09-10 03:35:41'),
(532, 12, 'osis', NULL, 'mingguan', 1, 5, '2026', 5, 50000.00, NULL, '2026-05-29', '2026-05-31', 2, 'aktif', '2026-09-10 03:35:41'),
(533, 12, 'osis', NULL, 'mingguan', 1, 6, '2026', 1, 50000.00, NULL, '2026-06-01', '2026-06-07', 2, 'aktif', '2026-09-10 03:35:41'),
(534, 12, 'osis', NULL, 'mingguan', 1, 6, '2026', 2, 50000.00, NULL, '2026-06-08', '2026-06-14', 2, 'aktif', '2026-09-10 03:35:41'),
(535, 12, 'osis', NULL, 'mingguan', 1, 6, '2026', 3, 50000.00, NULL, '2026-06-15', '2026-06-21', 2, 'aktif', '2026-09-10 03:35:41'),
(536, 12, 'osis', NULL, 'mingguan', 1, 6, '2026', 4, 50000.00, NULL, '2026-06-22', '2026-06-28', 2, 'aktif', '2026-09-10 03:35:41'),
(537, 12, 'osis', NULL, 'mingguan', 1, 6, '2026', 5, 50000.00, NULL, '2026-06-29', '2026-06-30', 2, 'aktif', '2026-09-10 03:35:41'),
(538, 12, 'osis', NULL, 'mingguan', 1, 7, '2026', 1, 50000.00, NULL, '2026-07-01', '2026-07-07', 2, 'aktif', '2026-09-10 03:35:41'),
(539, 12, 'osis', NULL, 'mingguan', 1, 7, '2026', 2, 50000.00, NULL, '2026-07-08', '2026-07-14', 2, 'aktif', '2026-09-10 03:35:41'),
(540, 12, 'osis', NULL, 'mingguan', 1, 7, '2026', 3, 50000.00, NULL, '2026-07-15', '2026-07-21', 2, 'aktif', '2026-09-10 03:35:41'),
(541, 12, 'osis', NULL, 'mingguan', 1, 7, '2026', 4, 50000.00, NULL, '2026-07-22', '2026-07-28', 2, 'aktif', '2026-09-10 03:35:41'),
(542, 12, 'osis', NULL, 'mingguan', 1, 7, '2026', 5, 50000.00, NULL, '2026-07-29', '2026-07-31', 2, 'aktif', '2026-09-10 03:35:41'),
(543, 12, 'osis', NULL, 'mingguan', 1, 8, '2026', 1, 50000.00, NULL, '2026-08-01', '2026-08-07', 2, 'aktif', '2026-09-10 03:35:41'),
(544, 12, 'osis', NULL, 'mingguan', 1, 8, '2026', 2, 50000.00, NULL, '2026-08-08', '2026-08-14', 2, 'aktif', '2026-09-10 03:35:41'),
(545, 12, 'osis', NULL, 'mingguan', 1, 8, '2026', 3, 50000.00, NULL, '2026-08-15', '2026-08-21', 2, 'aktif', '2026-09-10 03:35:41'),
(546, 12, 'osis', NULL, 'mingguan', 1, 8, '2026', 4, 50000.00, NULL, '2026-08-22', '2026-08-28', 2, 'aktif', '2026-09-10 03:35:41'),
(547, 12, 'osis', NULL, 'mingguan', 1, 8, '2026', 5, 50000.00, NULL, '2026-08-29', '2026-08-31', 2, 'aktif', '2026-09-10 03:35:41'),
(548, 12, 'osis', NULL, 'mingguan', 1, 9, '2026', 1, 50000.00, NULL, '2026-09-01', '2026-09-07', 2, 'aktif', '2026-09-10 03:35:41'),
(549, 12, 'osis', NULL, 'mingguan', 1, 9, '2026', 2, 50000.00, NULL, '2026-09-08', '2026-09-14', 2, 'aktif', '2026-09-10 03:35:41'),
(550, 12, 'osis', NULL, 'mingguan', 1, 9, '2026', 3, 50000.00, NULL, '2026-09-15', '2026-09-21', 2, 'aktif', '2026-09-10 03:35:41'),
(552, 12, 'osis', NULL, 'mingguan', 1, 9, '2026', 5, 50000.00, NULL, '2026-09-29', '2026-09-30', 2, 'aktif', '2026-09-10 03:35:41'),
(562, 12, 'osis', NULL, 'mingguan', 1, 11, '2026', 5, 50000.00, NULL, '2026-11-29', '2026-11-30', 2, 'aktif', '2026-09-10 03:35:41'),
(567, 12, 'osis', NULL, 'mingguan', 1, 12, '2026', 5, 50000.00, NULL, '2026-12-29', '2026-12-31', 2, 'aktif', '2026-09-10 03:35:41'),
(568, 13, 'kelas', 8, 'mingguan', 1, 9, '2026', 1, 10000.00, NULL, '2026-09-01', '2026-09-07', 2, 'nonaktif', '2026-09-10 03:46:10'),
(569, 13, 'kelas', 8, 'mingguan', 1, 9, '2026', 2, 10000.00, NULL, '2026-09-08', '2026-09-14', 2, 'nonaktif', '2026-09-10 03:46:10'),
(570, 13, 'kelas', 8, 'mingguan', 1, 9, '2026', 3, 10000.00, NULL, '2026-09-15', '2026-09-21', 2, 'nonaktif', '2026-09-10 03:46:10'),
(571, 13, 'kelas', 8, 'mingguan', 1, 9, '2026', 4, 10000.00, NULL, '2026-09-22', '2026-09-28', 2, 'nonaktif', '2026-09-10 03:46:10'),
(572, 13, 'kelas', 8, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(582, 13, 'kelas', 8, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(587, 13, 'kelas', 8, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(592, 14, 'kelas', 8, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(602, 14, 'kelas', 8, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(607, 14, 'kelas', 8, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(612, 15, 'kelas', 8, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(622, 15, 'kelas', 8, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(627, 15, 'kelas', 8, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(632, 16, 'kelas', 7, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(642, 16, 'kelas', 7, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(647, 16, 'kelas', 7, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(652, 17, 'kelas', 9, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(662, 17, 'kelas', 9, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(667, 17, 'kelas', 9, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(672, 18, 'kelas', 7, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(682, 18, 'kelas', 7, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(687, 18, 'kelas', 7, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(692, 19, 'kelas', 7, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(702, 19, 'kelas', 7, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(707, 19, 'kelas', 7, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(712, 20, 'kelas', 5, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(722, 20, 'kelas', 5, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(727, 20, 'kelas', 5, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(732, 21, 'kelas', 5, 'mingguan', 1, 9, '2026', 5, 10000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(742, 21, 'kelas', 5, 'mingguan', 1, 11, '2026', 5, 10000.00, NULL, '2026-11-29', '2026-11-30', 2, 'nonaktif', '2026-09-10 03:46:10'),
(747, 21, 'kelas', 5, 'mingguan', 1, 12, '2026', 5, 10000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 03:46:10'),
(752, 22, 'osis', NULL, 'mingguan', 1, 9, '2026', 5, 100000.00, NULL, '2026-09-29', '2026-09-30', 2, 'nonaktif', '2026-09-10 05:41:50'),
(762, 22, 'osis', NULL, 'mingguan', 1, 11, '2026', 5, 100000.00, NULL, '2026-11-30', '2026-11-30', 2, 'nonaktif', '2026-09-10 05:41:50'),
(767, 22, 'osis', NULL, 'mingguan', 1, 12, '2026', 5, 100000.00, NULL, '2026-12-29', '2026-12-31', 2, 'nonaktif', '2026-09-10 05:41:50'),
(785, 24, 'osis', NULL, 'bulanan', 1, 9, '2026', 0, 12000.00, NULL, '2026-09-01', '2026-09-30', 2, 'nonaktif', '2026-09-10 05:55:35'),
(786, 24, 'osis', NULL, 'bulanan', 1, 10, '2026', 0, 12000.00, NULL, '2026-10-01', '2026-10-31', 2, 'nonaktif', '2026-09-10 05:55:35'),
(825, 34, 'kelas', 8, 'mingguan', 1, 9, '2026', 1, 10000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 05:57:23'),
(1014, 52, 'kelas', 8, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1018, 52, 'kelas', 8, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1019, 52, 'kelas', 8, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1020, 52, 'kelas', 8, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1021, 52, 'kelas', 8, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1022, 52, 'kelas', 8, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1023, 52, 'kelas', 8, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1024, 52, 'kelas', 8, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1025, 52, 'kelas', 8, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1026, 52, 'kelas', 8, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1027, 52, 'kelas', 8, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1028, 52, 'kelas', 8, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1029, 52, 'kelas', 8, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1030, 52, 'kelas', 8, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1031, 53, 'kelas', 8, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1032, 53, 'kelas', 8, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1033, 53, 'kelas', 8, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1034, 53, 'kelas', 8, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1035, 53, 'kelas', 8, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1036, 53, 'kelas', 8, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1037, 53, 'kelas', 8, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1038, 53, 'kelas', 8, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1039, 53, 'kelas', 8, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1040, 53, 'kelas', 8, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1041, 53, 'kelas', 8, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1042, 53, 'kelas', 8, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1043, 53, 'kelas', 8, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1044, 53, 'kelas', 8, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1045, 53, 'kelas', 8, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1046, 53, 'kelas', 8, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1047, 53, 'kelas', 8, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1048, 54, 'kelas', 8, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1049, 54, 'kelas', 8, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1050, 54, 'kelas', 8, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1051, 54, 'kelas', 8, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1052, 54, 'kelas', 8, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1053, 54, 'kelas', 8, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1054, 54, 'kelas', 8, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1055, 54, 'kelas', 8, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1056, 54, 'kelas', 8, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1057, 54, 'kelas', 8, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1058, 54, 'kelas', 8, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1059, 54, 'kelas', 8, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1060, 54, 'kelas', 8, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1061, 54, 'kelas', 8, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1062, 54, 'kelas', 8, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1063, 54, 'kelas', 8, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1064, 54, 'kelas', 8, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1065, 55, 'kelas', 7, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1066, 55, 'kelas', 7, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1067, 55, 'kelas', 7, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1068, 55, 'kelas', 7, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1069, 55, 'kelas', 7, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1070, 55, 'kelas', 7, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1071, 55, 'kelas', 7, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1072, 55, 'kelas', 7, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1073, 55, 'kelas', 7, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1074, 55, 'kelas', 7, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1075, 55, 'kelas', 7, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1076, 55, 'kelas', 7, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1077, 55, 'kelas', 7, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1078, 55, 'kelas', 7, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1079, 55, 'kelas', 7, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1080, 55, 'kelas', 7, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1081, 55, 'kelas', 7, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1082, 56, 'kelas', 9, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1083, 56, 'kelas', 9, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1084, 56, 'kelas', 9, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1085, 56, 'kelas', 9, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1086, 56, 'kelas', 9, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1087, 56, 'kelas', 9, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1088, 56, 'kelas', 9, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1089, 56, 'kelas', 9, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1090, 56, 'kelas', 9, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1091, 56, 'kelas', 9, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1092, 56, 'kelas', 9, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1093, 56, 'kelas', 9, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1094, 56, 'kelas', 9, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1095, 56, 'kelas', 9, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1096, 56, 'kelas', 9, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1097, 56, 'kelas', 9, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1098, 56, 'kelas', 9, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1099, 57, 'kelas', 7, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1100, 57, 'kelas', 7, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1101, 57, 'kelas', 7, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1102, 57, 'kelas', 7, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1103, 57, 'kelas', 7, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1104, 57, 'kelas', 7, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1105, 57, 'kelas', 7, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1106, 57, 'kelas', 7, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1107, 57, 'kelas', 7, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1108, 57, 'kelas', 7, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1109, 57, 'kelas', 7, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1110, 57, 'kelas', 7, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1111, 57, 'kelas', 7, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1112, 57, 'kelas', 7, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1113, 57, 'kelas', 7, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1114, 57, 'kelas', 7, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1115, 57, 'kelas', 7, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1116, 58, 'kelas', 7, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1117, 58, 'kelas', 7, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1118, 58, 'kelas', 7, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1119, 58, 'kelas', 7, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1120, 58, 'kelas', 7, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1121, 58, 'kelas', 7, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1122, 58, 'kelas', 7, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1123, 58, 'kelas', 7, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1124, 58, 'kelas', 7, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1125, 58, 'kelas', 7, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1126, 58, 'kelas', 7, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1127, 58, 'kelas', 7, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1128, 58, 'kelas', 7, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1129, 58, 'kelas', 7, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1130, 58, 'kelas', 7, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1131, 58, 'kelas', 7, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1132, 58, 'kelas', 7, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1133, 59, 'kelas', 5, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1134, 59, 'kelas', 5, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1135, 59, 'kelas', 5, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1136, 59, 'kelas', 5, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1137, 59, 'kelas', 5, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1138, 59, 'kelas', 5, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1139, 59, 'kelas', 5, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1140, 59, 'kelas', 5, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1141, 59, 'kelas', 5, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1142, 59, 'kelas', 5, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1143, 59, 'kelas', 5, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1144, 59, 'kelas', 5, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1145, 59, 'kelas', 5, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1146, 59, 'kelas', 5, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1147, 59, 'kelas', 5, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1148, 59, 'kelas', 5, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1149, 59, 'kelas', 5, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1150, 60, 'kelas', 5, 'mingguan', 1, 9, '2026', 1, 40000.00, NULL, '2026-09-01', '2026-09-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1151, 60, 'kelas', 5, 'mingguan', 1, 9, '2026', 2, 40000.00, NULL, '2026-09-07', '2026-09-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1152, 60, 'kelas', 5, 'mingguan', 1, 9, '2026', 3, 40000.00, NULL, '2026-09-14', '2026-09-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1153, 60, 'kelas', 5, 'mingguan', 1, 9, '2026', 4, 40000.00, NULL, '2026-09-21', '2026-09-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1154, 60, 'kelas', 5, 'mingguan', 1, 10, '2026', 1, 40000.00, NULL, '2026-10-01', '2026-10-02', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1155, 60, 'kelas', 5, 'mingguan', 1, 10, '2026', 2, 40000.00, NULL, '2026-10-05', '2026-10-09', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1156, 60, 'kelas', 5, 'mingguan', 1, 10, '2026', 3, 40000.00, NULL, '2026-10-12', '2026-10-16', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1157, 60, 'kelas', 5, 'mingguan', 1, 10, '2026', 4, 40000.00, NULL, '2026-10-19', '2026-10-23', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1158, 60, 'kelas', 5, 'mingguan', 1, 10, '2026', 5, 40000.00, NULL, '2026-10-26', '2026-10-30', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1159, 60, 'kelas', 5, 'mingguan', 1, 11, '2026', 1, 40000.00, NULL, '2026-11-02', '2026-11-06', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1160, 60, 'kelas', 5, 'mingguan', 1, 11, '2026', 2, 40000.00, NULL, '2026-11-09', '2026-11-13', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1161, 60, 'kelas', 5, 'mingguan', 1, 11, '2026', 3, 40000.00, NULL, '2026-11-16', '2026-11-20', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1162, 60, 'kelas', 5, 'mingguan', 1, 11, '2026', 4, 40000.00, NULL, '2026-11-23', '2026-11-27', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1163, 60, 'kelas', 5, 'mingguan', 1, 12, '2026', 1, 40000.00, NULL, '2026-12-01', '2026-12-04', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1164, 60, 'kelas', 5, 'mingguan', 1, 12, '2026', 2, 40000.00, NULL, '2026-12-07', '2026-12-11', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1165, 60, 'kelas', 5, 'mingguan', 1, 12, '2026', 3, 40000.00, NULL, '2026-12-14', '2026-12-18', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1166, 60, 'kelas', 5, 'mingguan', 1, 12, '2026', 4, 40000.00, NULL, '2026-12-21', '2026-12-25', 2, 'nonaktif', '2026-09-10 06:27:37'),
(1170, 61, 'osis', NULL, 'mingguan', 1, 9, '2026', 4, 50000.00, NULL, '2026-09-21', '2026-09-25', 2, 'aktif', '2026-09-10 16:21:20'),
(1171, 61, 'osis', NULL, 'mingguan', 1, 10, '2026', 1, 50000.00, NULL, '2026-10-01', '2026-10-02', 2, 'aktif', '2026-09-10 16:21:20'),
(1172, 61, 'osis', NULL, 'mingguan', 1, 10, '2026', 2, 50000.00, NULL, '2026-10-05', '2026-10-09', 2, 'aktif', '2026-09-10 16:21:20'),
(1173, 61, 'osis', NULL, 'mingguan', 1, 10, '2026', 3, 50000.00, NULL, '2026-10-12', '2026-10-16', 2, 'aktif', '2026-09-10 16:21:20'),
(1174, 61, 'osis', NULL, 'mingguan', 1, 10, '2026', 4, 50000.00, NULL, '2026-10-19', '2026-10-23', 2, 'aktif', '2026-09-10 16:21:20'),
(1175, 61, 'osis', NULL, 'mingguan', 1, 10, '2026', 5, 50000.00, NULL, '2026-10-26', '2026-10-30', 2, 'aktif', '2026-09-10 16:21:20'),
(1176, 61, 'osis', NULL, 'mingguan', 1, 11, '2026', 1, 50000.00, NULL, '2026-11-02', '2026-11-06', 2, 'aktif', '2026-09-10 16:21:20'),
(1177, 61, 'osis', NULL, 'mingguan', 1, 11, '2026', 2, 50000.00, NULL, '2026-11-09', '2026-11-13', 2, 'aktif', '2026-09-10 16:21:20'),
(1178, 61, 'osis', NULL, 'mingguan', 1, 11, '2026', 3, 50000.00, NULL, '2026-11-16', '2026-11-20', 2, 'aktif', '2026-09-10 16:21:20'),
(1179, 61, 'osis', NULL, 'mingguan', 1, 11, '2026', 4, 50000.00, NULL, '2026-11-23', '2026-11-27', 2, 'aktif', '2026-09-10 16:21:20'),
(1180, 61, 'osis', NULL, 'mingguan', 1, 12, '2026', 1, 50000.00, NULL, '2026-12-01', '2026-12-04', 2, 'aktif', '2026-09-10 16:21:20'),
(1181, 61, 'osis', NULL, 'mingguan', 1, 12, '2026', 2, 50000.00, NULL, '2026-12-07', '2026-12-11', 2, 'aktif', '2026-09-10 16:21:20'),
(1182, 61, 'osis', NULL, 'mingguan', 1, 12, '2026', 3, 50000.00, NULL, '2026-12-14', '2026-12-18', 2, 'aktif', '2026-09-10 16:21:20'),
(1183, 61, 'osis', NULL, 'mingguan', 1, 12, '2026', 4, 50000.00, NULL, '2026-12-21', '2026-12-25', 2, 'aktif', '2026-09-10 16:21:20'),
(1184, 62, 'kelas', 8, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1185, 62, 'kelas', 8, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1186, 62, 'kelas', 8, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1187, 62, 'kelas', 8, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1188, 63, 'kelas', 8, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1189, 63, 'kelas', 8, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1190, 63, 'kelas', 8, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1191, 63, 'kelas', 8, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1192, 64, 'kelas', 8, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1193, 64, 'kelas', 8, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1194, 64, 'kelas', 8, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1195, 64, 'kelas', 8, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1196, 65, 'kelas', 7, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1197, 65, 'kelas', 7, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1198, 65, 'kelas', 7, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1199, 65, 'kelas', 7, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1200, 66, 'kelas', 9, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1201, 66, 'kelas', 9, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1202, 66, 'kelas', 9, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1203, 66, 'kelas', 9, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1204, 67, 'kelas', 7, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1205, 67, 'kelas', 7, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1206, 67, 'kelas', 7, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1207, 67, 'kelas', 7, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1208, 68, 'kelas', 7, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1209, 68, 'kelas', 7, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1210, 68, 'kelas', 7, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1211, 68, 'kelas', 7, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1212, 69, 'kelas', 5, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1213, 69, 'kelas', 5, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1214, 69, 'kelas', 5, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1215, 69, 'kelas', 5, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1216, 70, 'kelas', 5, 'bulanan', 1, 9, '2026', 0, 50000.00, NULL, '2026-09-01', '2026-09-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1217, 70, 'kelas', 5, 'bulanan', 1, 10, '2026', 0, 50000.00, NULL, '2026-10-01', '2026-10-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1218, 70, 'kelas', 5, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'aktif', '2026-09-10 16:22:04'),
(1219, 70, 'kelas', 5, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'aktif', '2026-09-10 16:22:04'),
(1222, 71, 'osis', NULL, 'bulanan', 1, 11, '2026', 0, 50000.00, NULL, '2026-11-01', '2026-11-30', 2, 'nonaktif', '2026-09-10 16:25:47'),
(1223, 71, 'osis', NULL, 'bulanan', 1, 12, '2026', 0, 50000.00, NULL, '2026-12-01', '2026-12-31', 2, 'nonaktif', '2026-09-10 16:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `student_due_payments`
--

CREATE TABLE `student_due_payments` (
  `id` int UNSIGNED NOT NULL,
  `student_due_id` int UNSIGNED NOT NULL,
  `student_id` int UNSIGNED NOT NULL,
  `recorded_by` int UNSIGNED NOT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `status` enum('belum_bayar','lunas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'belum_bayar',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_due_payments`
--

INSERT INTO `student_due_payments` (`id`, `student_due_id`, `student_id`, `recorded_by`, `tanggal_bayar`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 13, 3, 3, '2026-09-09', 50000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-09 16:09:37', '2026-09-09 16:09:37'),
(2, 13, 2, 3, '2026-09-09', 50000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-09 16:09:41', '2026-09-09 16:09:41'),
(3, 509, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:38:23', '2026-09-10 07:20:41'),
(4, 510, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:38:28', '2026-09-10 07:20:44'),
(5, 511, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:38:30', '2026-09-10 07:20:47'),
(6, 512, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:38:34', '2026-09-10 07:20:50'),
(7, 513, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:38:37', '2026-09-10 07:20:53'),
(8, 514, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:39:27', '2026-09-10 07:20:56'),
(9, 515, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:39:30', '2026-09-10 07:21:00'),
(10, 516, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:39:33', '2026-09-10 07:21:04'),
(11, 517, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:39:37', '2026-09-10 07:21:06'),
(12, 548, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:46:49', '2026-09-10 05:17:28'),
(13, 549, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:46:53', '2026-09-10 05:17:08'),
(14, 550, 1, 2, NULL, 0.00, 'belum_bayar', 'Pembayaran dibatalkan oleh Bendahara OSIS', '2026-09-10 03:46:56', '2026-09-10 05:16:53'),
(15, 568, 3, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:47:11', '2026-09-10 03:47:11'),
(16, 568, 2, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:47:14', '2026-09-10 03:47:14'),
(17, 569, 3, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:52:36', '2026-09-10 03:52:36'),
(18, 569, 2, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:52:39', '2026-09-10 03:52:39'),
(19, 570, 3, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:52:50', '2026-09-10 03:52:50'),
(20, 571, 3, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:52:55', '2026-09-10 03:52:55'),
(21, 572, 3, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 03:52:58', '2026-09-10 03:52:58'),
(22, 570, 2, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 04:11:06', '2026-09-10 04:11:06'),
(23, 571, 2, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 04:11:10', '2026-09-10 04:11:10'),
(24, 572, 2, 3, '2026-09-10', 10000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 04:11:13', '2026-09-10 04:11:13'),
(25, 1014, 3, 3, '2026-09-10', 40000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 07:11:37', '2026-09-10 15:53:55'),
(26, 1014, 2, 3, '2026-09-10', 40000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 07:11:42', '2026-09-10 15:54:04'),
(27, 785, 1, 2, '2026-09-10', 12000.00, 'lunas', 'Dicatat oleh Bendahara OSIS', '2026-09-10 07:21:21', '2026-09-10 08:52:43'),
(28, 786, 1, 2, '2026-09-10', 12000.00, 'lunas', 'Dicatat oleh Bendahara OSIS', '2026-09-10 08:52:49', '2026-09-10 08:52:49'),
(29, 1184, 3, 3, '2026-09-10', 50000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 16:25:09', '2026-09-10 16:25:09'),
(30, 1184, 2, 3, '2026-09-10', 50000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 16:25:17', '2026-09-10 16:25:17'),
(31, 1185, 3, 3, '2026-09-10', 50000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 16:25:21', '2026-09-10 16:25:21'),
(32, 1185, 2, 3, '2026-09-10', 50000.00, 'lunas', 'Dicatat oleh Bendahara Kelas', '2026-09-10 16:25:25', '2026-09-10 16:25:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','osis','osis_pending','siswa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@simosis.test', '$2y$10$854K2/tbaXqMzxqK0zDyuuqcgJ8gKcX3pyQsGMUX801APOP5xMQ/O', 'admin', 'aktif', '2026-09-01 08:09:33', '2026-09-01 08:09:33'),
(2, '1111', 'albukhorifirdaus09@gmail.com', '$2y$10$jO2rtlocdgHG/fsuVxUXiOGVpJhFBPIoBzJe7p4TCShNS1pPe3OBa', 'osis', 'aktif', '2026-09-09 14:45:37', '2026-09-09 14:58:35'),
(3, '123456', 'yusuf@gmail.com', '$2y$10$WwAv3XmkL.T8AkmKCKI0C.ZiQ6qAia/NEV8Py7xHdE5QISS8xz5wi', 'siswa', 'aktif', '2026-09-09 15:13:32', '2026-09-09 15:18:08'),
(5, 'user_477be0fe69910866', 'acel@gmail.com', '$2y$10$7uXZALuYBeQgd6zS9kgLp.pRPAFpwG6VN3n1R3tbKyhkhSND1iMOW', 'siswa', 'aktif', '2026-09-10 03:08:22', '2026-09-10 03:08:22'),
(6, 'user_b1d81ce07d990c29', 'albukhorifirdaus0@gmail.com', '$2y$10$aJdZbiR9pfL9NllFlBuVAOJhpQetXDkeAgtrv0EvibiivBSiyRR56', 'siswa', 'aktif', '2026-09-15 07:03:29', '2026-09-16 02:55:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_creator` (`created_by`);

--
-- Indexes for table `activity_committees`
--
ALTER TABLE `activity_committees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_committee_activity` (`activity_id`),
  ADD KEY `fk_committee_member` (`osis_member_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_announcement_user` (`user_id`);

--
-- Indexes for table `cash_settings`
--
ALTER TABLE `cash_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_due_confirmations`
--
ALTER TABLE `class_due_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_class_due_confirmation` (`student_due_id`,`class_id`),
  ADD KEY `idx_confirmation_submitter` (`submitted_by`),
  ADD KEY `idx_confirmation_confirmer` (`confirmed_by`),
  ADD KEY `idx_confirmation_status` (`status`),
  ADD KEY `fk_confirmation_class` (`class_id`);

--
-- Indexes for table `class_officers`
--
ALTER TABLE `class_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_officers_student` (`student_id`),
  ADD KEY `idx_class_officers_class` (`class_id`),
  ADD KEY `idx_class_officers_year` (`academic_year_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_department_name` (`nama_jurusan`),
  ADD UNIQUE KEY `unique_department_abbreviation` (`singkatan`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_document_activity` (`activity_id`),
  ADD KEY `fk_document_user` (`uploaded_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_user` (`user_id`);

--
-- Indexes for table `osis_members`
--
ALTER TABLE `osis_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_osis_member_student` (`student_id`),
  ADD KEY `fk_osis_member_position` (`position_id`),
  ADD KEY `fk_osis_member_year` (`academic_year_id`),
  ADD KEY `idx_osis_members_year_status` (`academic_year_id`,`status`);

--
-- Indexes for table `osis_positions`
--
ALTER TABLE `osis_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `osis_transactions`
--
ALTER TABLE `osis_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaction_category` (`category_id`),
  ADD KEY `fk_transaction_user` (`user_id`);

--
-- Indexes for table `osis_transaction_categories`
--
ALTER TABLE `osis_transaction_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedule_activity` (`activity_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_students_user` (`user_id`),
  ADD KEY `fk_students_class` (`kelas_id`);

--
-- Indexes for table `student_class_history`
--
ALTER TABLE `student_class_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_academic_year` (`student_id`,`academic_year_id`),
  ADD KEY `idx_student_class_history_year` (`academic_year_id`),
  ADD KEY `idx_student_class_history_class` (`class_id`);

--
-- Indexes for table `student_dues`
--
ALTER TABLE `student_dues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_due_period` (`setting_id`,`tahun`,`bulan`,`frekuensi`,`periode`,`minggu_ke`,`jenis_kas`);

--
-- Indexes for table `student_due_payments`
--
ALTER TABLE `student_due_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_due` (`student_due_id`,`student_id`),
  ADD UNIQUE KEY `unique_student_due_payment` (`student_due_id`,`student_id`),
  ADD KEY `fk_payment_student` (`student_id`),
  ADD KEY `fk_payment_recorder` (`recorded_by`);

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
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_committees`
--
ALTER TABLE `activity_committees`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_settings`
--
ALTER TABLE `cash_settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `class_due_confirmations`
--
ALTER TABLE `class_due_confirmations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `class_officers`
--
ALTER TABLE `class_officers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `osis_members`
--
ALTER TABLE `osis_members`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `osis_positions`
--
ALTER TABLE `osis_positions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `osis_transactions`
--
ALTER TABLE `osis_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `osis_transaction_categories`
--
ALTER TABLE `osis_transaction_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_class_history`
--
ALTER TABLE `student_class_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `student_dues`
--
ALTER TABLE `student_dues`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1224;

--
-- AUTO_INCREMENT for table `student_due_payments`
--
ALTER TABLE `student_due_payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activity_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `activity_committees`
--
ALTER TABLE `activity_committees`
  ADD CONSTRAINT `fk_committee_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_committee_member` FOREIGN KEY (`osis_member_id`) REFERENCES `osis_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcement_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `class_due_confirmations`
--
ALTER TABLE `class_due_confirmations`
  ADD CONSTRAINT `fk_confirmation_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_confirmation_confirmer` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_confirmation_due` FOREIGN KEY (`student_due_id`) REFERENCES `student_dues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_confirmation_submitter` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `class_officers`
--
ALTER TABLE `class_officers`
  ADD CONSTRAINT `fk_class_officers_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_officers_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_officers_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_document_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `osis_members`
--
ALTER TABLE `osis_members`
  ADD CONSTRAINT `fk_osis_member_position` FOREIGN KEY (`position_id`) REFERENCES `osis_positions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_osis_member_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_osis_member_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `osis_transactions`
--
ALTER TABLE `osis_transactions`
  ADD CONSTRAINT `fk_transaction_category` FOREIGN KEY (`category_id`) REFERENCES `osis_transaction_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedule_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_class` FOREIGN KEY (`kelas_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `student_class_history`
--
ALTER TABLE `student_class_history`
  ADD CONSTRAINT `fk_student_class_history_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_class_history_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_class_history_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `student_due_payments`
--
ALTER TABLE `student_due_payments`
  ADD CONSTRAINT `fk_payment_due` FOREIGN KEY (`student_due_id`) REFERENCES `student_dues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
