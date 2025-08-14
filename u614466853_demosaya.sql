-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 11 Agu 2025 pada 06.16
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u614466853_demosaya`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_payables`
--

CREATE TABLE `account_payables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `account_payables`
--

INSERT INTO `account_payables` (`id`, `purchase_id`, `supplier_id`, `amount`, `due_date`, `status`, `paid_amount`, `payment_date`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 200000.00, '2025-06-28', 'unpaid', 0.00, NULL, 'Hutang dari pembelian invoice INV-20250529140739', 1, NULL, '2025-05-29 14:08:16', '2025-05-29 14:08:16'),
(2, 4, 2, 840.00, '2025-07-17', 'unpaid', 0.00, NULL, 'Hutang dari pembelian invoice INV-20250617092425', 1, NULL, '2025-06-17 09:24:40', '2025-06-17 09:24:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_receivables`
--

CREATE TABLE `account_receivables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_order_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `balance_categories`
--

CREATE TABLE `balance_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('asset','liability','equity') NOT NULL DEFAULT 'asset',
  `description` text DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `balance_categories`
--

INSERT INTO `balance_categories` (`id`, `name`, `type`, `description`, `store_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Kas', 'asset', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(2, 'Bank', 'asset', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(3, 'Piutang', 'asset', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(4, 'Modal', 'equity', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(5, 'Bank BCA', 'asset', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(6, 'Bank Mandiri', 'asset', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `show_in_pos` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `show_in_pos`) VALUES
(1, 'BAHAN BAKU', 'Bahan baku menu outlet', '2025-05-24 11:33:14', '2025-05-29 13:45:01', 0),
(2, 'Menu Store', 'Menu outlet', '2025-05-24 11:33:44', '2025-05-24 13:08:16', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `expenses`
--

INSERT INTO `expenses` (`id`, `date`, `category_id`, `amount`, `description`, `store_id`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '2025-06-20', 5, 25000.00, 'Ongkir untuk pesanan ORD-20250620104713', NULL, 3, 3, '2025-06-20 10:48:02', '2025-06-20 10:48:02'),
(2, '2025-06-20', 6, 25000.00, 'Beban transportasi untuk pesanan ORD-20250620104713', 1, 4, 4, '2025-06-20 10:48:44', '2025-06-20 10:48:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `description`, `store_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Biaya Gaji', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(2, 'Biaya Listrik', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(3, 'Biaya PDAM', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(4, 'Biaya Rumah Tangga', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(5, 'Biaya Operasional', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(6, 'Biaya Transportasi', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(7, 'Biaya Perawatan', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(8, 'Biaya Administrasi', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(9, 'Biaya Sewa', NULL, NULL, 1, '2025-05-24 10:29:09', '2025-05-24 10:29:09'),
(10, 'Biaya Air Kantor', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(11, 'Biaya Listrik Kantor', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(12, 'Biaya Telepon', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(13, 'Biaya Penyusutan Bangunan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(14, 'Biaya Penyusutan Kendaraan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(15, 'Biaya Penyusutan Peralatan Kantor', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(16, 'Biaya Pajak', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(17, 'Biaya Transport/BBM', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(18, 'Biaya ATK', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(19, 'Biaya Perjalanan Dinas', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(20, 'Biaya Pemeliharaan Bangunan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(21, 'Biaya Pemeliharaan Kendaraan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(22, 'Biaya Pemeliharaan Peralatan Kantor', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(23, 'Biaya Pembuat', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(24, 'Biaya Administrasi Bank', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(25, 'Biaya Kesejahteraan Karyawan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(26, 'Biaya Sosial', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(27, 'Biaya Rumah Tangga Kantor', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(28, 'Biaya Asuransi', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(29, 'Biaya Pajak Penghasilan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(30, 'Biaya Pajak Bumi dan Bangunan', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(31, 'Biaya Bunga Bank', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(32, 'Biaya Bunga Leasing', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00'),
(33, 'Biaya Lain-lain', NULL, NULL, 1, '2025-05-24 10:30:00', '2025-05-24 10:30:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `financial_journals`
--

CREATE TABLE `financial_journals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `debit_account` varchar(255) NOT NULL,
  `credit_account` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `initial_balances`
--

CREATE TABLE `initial_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_04_20_090754_create_permission_tables', 1),
(6, '2025_04_20_091739_create_stores_table', 1),
(7, '2025_04_20_091745_create_categories_table', 1),
(8, '2025_04_20_091751_create_units_table', 1),
(9, '2025_04_20_091757_create_products_table', 1),
(10, '2025_04_20_091801_create_product_units_table', 1),
(11, '2025_04_20_091806_create_suppliers_table', 1),
(12, '2025_04_20_092135_add_store_id_to_users_table', 1),
(13, '2025_04_20_092215_create_stock_warehouses_table', 1),
(14, '2025_04_20_092223_create_stock_stores_table', 1),
(15, '2025_04_20_092330_create_purchases_table', 1),
(16, '2025_04_20_092337_create_purchase_details_table', 1),
(17, '2025_04_20_092344_create_purchase_return_details_table', 1),
(18, '2025_04_20_092344_create_purchase_returns_table', 1),
(19, '2025_04_20_092352_create_store_orders_table', 1),
(20, '2025_04_20_092353_create_store_order_details_table', 1),
(21, '2025_04_20_092358_create_shipment_details_table', 1),
(22, '2025_04_20_092358_create_shipments_table', 1),
(23, '2025_04_20_092404_create_store_return_details_table', 1),
(24, '2025_04_20_092404_create_store_returns_table', 1),
(25, '2025_04_20_092410_create_sale_details_table', 1),
(26, '2025_04_20_092410_create_sales_table', 1),
(27, '2025_04_20_092416_create_expenses_table', 1),
(28, '2025_04_20_092422_create_stock_adjustment_details_table', 1),
(29, '2025_04_20_092422_create_stock_adjustments_table', 1),
(30, '2025_04_20_092427_create_stock_opnames_table', 1),
(31, '2025_04_20_092428_create_stock_opname_details_table', 1),
(32, '2025_04_20_092432_create_financial_journals_table', 1),
(33, '2025_04_20_100452_add_foreign_keys_to_detail_tables', 1),
(34, '2025_04_26_032224_add_store_id_and_is_processed_to_products_table', 1),
(35, '2025_04_26_032254_create_product_ingredients_table', 1),
(36, '2025_04_26_145752_add_timestamp_fields_to_store_orders_table', 1),
(37, '2025_04_26_145810_create_notifications_table', 1),
(38, '2025_04_26_150408_update_store_order_status_options', 1),
(39, '2025_04_28_044425_update_purchases_table_add_confirmed_status', 1),
(40, '2025_04_29_031613_create_account_payables_table', 1),
(41, '2025_04_29_031620_create_account_receivables_table', 1),
(42, '2025_05_04_044320_add_show_in_pos_to_categories_table', 1),
(43, '2025_05_05_152322_add_deleted_at_to_products_table', 1),
(44, '2025_05_13_101854_create_initial_balances_table', 1),
(45, '2025_05_13_181102_add_payment_fields_to_store_orders_table', 1),
(46, '2025_05_14_083733_create_balance_categories_table', 1),
(47, '2025_05_14_083733_create_expense_categories_table', 1),
(48, '2025_05_14_083734_modify_expenses_table', 1),
(49, '2025_05_14_083734_modify_initial_balances_table', 1),
(50, '2025_05_29_121003_add_tax_enabled_to_sales_table', 2),
(51, '2025_05_30_112635_add_dining_option_to_sales_table', 3),
(52, '2025_05_30_204347_fix_purchase_return_details_table', 4),
(53, '2025_06_14_212210_create_product_store_prices_table', 5),
(54, '2025_06_20_102831_add_shipping_cost_to_store_orders_table', 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 4),
(4, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 5),
(6, 'App\\Models\\User', 7);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 2, 'Konfirmasi Pembelian Barang', 'Ada pembelian barang baru yang perlu dikonfirmasi (No. Invoice: INV-20250524185)', 'http://103.119.60.112/durianbarbar/public/warehouse/purchases/2', 1, '2025-05-24 11:59:34', '2025-05-24 12:01:43'),
(2, 1, 'Pembelian Barang Berhasil Diterima', 'Pembelian dengan No. Invoice: INV-20250524185 telah diterima dan stok diperbarui.', 'http://103.119.60.112/durianbarbar/public/purchases/2', 1, '2025-05-24 12:02:28', '2025-05-24 12:11:21'),
(3, 3, 'Pembelian Barang Berhasil Diterima', 'Pembelian dengan No. Invoice: INV-20250524185 telah diterima dan stok diperbarui.', 'http://103.119.60.112/durianbarbar/public/purchases/2', 1, '2025-05-24 12:02:28', '2025-05-24 12:07:15'),
(4, 4, 'Pengiriman Barang Telah Dikirim', 'Pesanan Anda telah dikirim dari gudang pusat. Silakan konfirmasi saat barang telah diterima.', 'http://103.119.60.112/durianbarbar/public/store/orders/1', 1, '2025-05-24 12:13:27', '2025-05-24 12:14:36'),
(5, 4, 'Pengiriman Barang Telah Dikirim', 'Pesanan Anda telah dikirim dari gudang pusat. Silakan konfirmasi saat barang telah diterima.', 'https://demosaya.top/store/orders/2', 1, '2025-05-29 14:00:00', '2025-05-29 14:00:53'),
(6, 2, 'Konfirmasi Pembelian Barang', 'Ada pembelian barang baru yang perlu dikonfirmasi (No. Invoice: INV-20250529140739)', 'https://demosaya.top/warehouse/purchases/3', 1, '2025-05-29 14:08:28', '2025-05-29 14:09:51'),
(7, 1, 'Pembelian Barang Berhasil Diterima', 'Pembelian dengan No. Invoice: INV-20250529140739 telah diterima dan stok diperbarui.', 'https://demosaya.top/purchases/3', 1, '2025-05-29 14:09:55', '2025-05-29 14:22:08'),
(8, 3, 'Pembelian Barang Berhasil Diterima', 'Pembelian dengan No. Invoice: INV-20250529140739 telah diterima dan stok diperbarui.', 'https://demosaya.top/purchases/3', 1, '2025-05-29 14:09:55', '2025-05-30 17:50:01'),
(9, 4, 'Pengiriman Barang Telah Dikirim', 'Pesanan Anda telah dikirim dari gudang pusat. Silakan konfirmasi saat barang telah diterima.', 'https://demosaya.top/store/orders/9', 1, '2025-06-20 10:48:27', '2025-06-23 12:40:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view products', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(2, 'create products', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(3, 'edit products', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(4, 'delete products', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(5, 'view categories', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(6, 'create categories', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(7, 'edit categories', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(8, 'delete categories', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(9, 'view suppliers', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(10, 'create suppliers', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(11, 'edit suppliers', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(12, 'delete suppliers', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(13, 'view stores', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(14, 'create stores', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(15, 'edit stores', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(16, 'delete stores', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(17, 'view units', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(18, 'create units', 'web', '2025-05-24 10:29:53', '2025-05-24 10:29:53'),
(19, 'edit units', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(20, 'delete units', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(21, 'view purchases', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(22, 'create purchases', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(23, 'edit purchases', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(24, 'delete purchases', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(25, 'view purchase returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(26, 'create purchase returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(27, 'edit purchase returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(28, 'delete purchase returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(29, 'view store orders', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(30, 'create store orders', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(31, 'edit store orders', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(32, 'delete store orders', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(33, 'view shipments', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(34, 'create shipments', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(35, 'edit shipments', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(36, 'delete shipments', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(37, 'view store returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(38, 'create store returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(39, 'edit store returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(40, 'delete store returns', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(41, 'view sales', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(42, 'create sales', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(43, 'edit sales', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(44, 'delete sales', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(45, 'view expenses', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(46, 'create expenses', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(47, 'edit expenses', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(48, 'delete expenses', 'web', '2025-05-24 10:29:54', '2025-05-24 10:29:54'),
(49, 'view stock warehouses', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(50, 'adjust stock warehouses', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(51, 'view stock stores', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(52, 'adjust stock stores', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(53, 'view stock opnames', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(54, 'create stock opnames', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(55, 'edit stock opnames', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(56, 'delete stock opnames', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(57, 'view financial reports', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(58, 'create financial journals', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(59, 'edit financial journals', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(60, 'delete financial journals', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(61, 'manage users', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(62, 'manage roles', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(63, 'backup database', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(64, 'restore database', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(65, 'view ingredient reports', 'web', '2025-06-23 21:14:53', '2025-06-23 21:14:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `base_unit_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `min_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_processed` tinyint(1) NOT NULL DEFAULT 0,
  `store_source` enum('pusat','toko') NOT NULL DEFAULT 'pusat',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `description`, `category_id`, `base_unit_id`, `purchase_price`, `selling_price`, `min_stock`, `image`, `is_active`, `store_id`, `is_processed`, `store_source`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'DK01', 'DURIAN KOCOK REGULER', '-', 2, 4, 10000.00, 14000.00, 50.00, 'products/FuuHauk3c1aOkpZqNGmD95g98bNTyYtoOWDDD9kD.jpg', 1, NULL, 1, 'toko', '2025-05-24 11:38:30', '2025-07-22 19:56:16', NULL),
(2, 'DG01', 'DURIAN DAGING', '-', 1, 5, 50.00, 55.00, 500.00, NULL, 1, NULL, 0, 'pusat', '2025-05-24 11:40:23', '2025-06-17 09:20:16', NULL),
(3, 'KR01', 'KRIMER', '-', 1, 9, 25000.00, 36000.00, 50.00, NULL, 1, NULL, 0, 'pusat', '2025-05-24 11:41:04', '2025-07-22 20:05:18', NULL),
(4, 'ES01', 'ES KRISTAL', '-', 1, 5, 500.00, 1000.00, 50.00, NULL, 1, NULL, 0, 'pusat', '2025-05-24 11:44:04', '2025-05-24 11:44:04', NULL),
(5, 'U01', 'UHT', '-', 1, 7, 2000.00, 3000.00, 50.00, NULL, 1, NULL, 0, 'pusat', '2025-05-24 11:46:11', '2025-07-22 16:38:30', '2025-07-22 16:38:30'),
(6, 'C01', 'CUP REGULER', '-', 1, 4, 840.00, 1000.00, 50.00, NULL, 1, NULL, 0, 'pusat', '2025-05-24 11:47:28', '2025-05-29 13:41:03', NULL),
(17, 'S01', 'snack satu', NULL, 2, 4, 1000.00, 1500.00, 10.00, NULL, 1, NULL, 0, 'toko', '2025-06-15 09:01:13', '2025-06-15 09:01:13', NULL),
(18, 'UH01', 'UHT', NULL, 1, 9, 16000.00, 17000.00, 10.00, NULL, 1, NULL, 0, 'pusat', '2025-07-22 19:47:45', '2025-07-22 19:47:45', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_ingredients`
--

CREATE TABLE `product_ingredients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `ingredient_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_ingredients`
--

INSERT INTO `product_ingredients` (`id`, `product_id`, `ingredient_id`, `quantity`, `unit_id`, `created_at`, `updated_at`) VALUES
(45, 1, 6, 1.00, 4, '2025-07-22 19:56:16', '2025-07-22 19:56:16'),
(46, 1, 2, 250.00, 5, '2025-07-22 19:56:16', '2025-07-22 19:56:16'),
(47, 1, 4, 100.00, 5, '2025-07-22 19:56:16', '2025-07-22 19:56:16'),
(48, 1, 3, 1.00, 9, '2025-07-22 19:56:16', '2025-07-22 19:56:16'),
(49, 1, 18, 250.00, 8, '2025-07-22 19:56:16', '2025-07-22 19:56:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_store_prices`
--

CREATE TABLE `product_store_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `selling_price` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_store_prices`
--

INSERT INTO `product_store_prices` (`id`, `product_id`, `store_id`, `unit_id`, `selling_price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 4, 18000.00, 1, '2025-06-14 22:59:59', '2025-06-14 23:00:15'),
(2, 1, 4, 4, 15000.00, 1, '2025-06-15 08:13:50', '2025-06-15 09:12:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_units`
--

CREATE TABLE `product_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `conversion_value` decimal(15,4) NOT NULL,
  `purchase_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_units`
--

INSERT INTO `product_units` (`id`, `product_id`, `unit_id`, `conversion_value`, `purchase_price`, `selling_price`, `created_at`, `updated_at`) VALUES
(2, 3, 8, 1000.0000, 16.00, 17.00, '2025-07-22 17:12:08', '2025-07-22 17:12:08'),
(3, 18, 8, 10000.0000, 16.00, 17.00, '2025-07-22 19:47:45', '2025-07-22 19:47:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_type` enum('tunai','tempo') NOT NULL DEFAULT 'tunai',
  `due_date` date DEFAULT NULL,
  `status` enum('pending','confirmed','complete') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `purchases`
--

INSERT INTO `purchases` (`id`, `supplier_id`, `invoice_number`, `date`, `total_amount`, `payment_type`, `due_date`, `status`, `note`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'INV-20250524185108', '2025-05-24', 144500.00, 'tunai', NULL, 'pending', NULL, 3, NULL, '2025-05-24 11:57:38', '2025-05-24 11:57:38'),
(2, 1, 'INV-20250524185', '2025-05-24', 144500.00, 'tunai', NULL, 'complete', NULL, 3, 2, '2025-05-24 11:58:17', '2025-05-24 12:02:27'),
(3, 2, 'INV-20250529140739', '2025-05-29', 200000.00, 'tempo', '2025-06-28', 'complete', 'Testing', 1, 2, '2025-05-29 14:08:16', '2025-05-29 14:09:55'),
(4, 2, 'INV-20250617092425', '2025-06-17', 840.00, 'tempo', '2025-07-17', 'pending', NULL, 1, NULL, '2025-06-17 09:24:40', '2025-06-17 09:24:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `purchase_details`
--

INSERT INTO `purchase_details` (`id`, `purchase_id`, `product_id`, `unit_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 4, 50.0000, 840.00, 42000.00, '2025-05-24 11:57:38', '2025-05-24 11:57:38'),
(2, 1, 2, 5, 1000.0000, 50.00, 50000.00, '2025-05-24 11:57:38', '2025-05-24 11:57:38'),
(3, 1, 4, 5, 1000.0000, 5.00, 5000.00, '2025-05-24 11:57:38', '2025-05-24 11:57:38'),
(4, 1, 3, 5, 1000.0000, 25.00, 25000.00, '2025-05-24 11:57:38', '2025-05-24 11:57:38'),
(5, 1, 5, 7, 900.0000, 25.00, 22500.00, '2025-05-24 11:57:38', '2025-05-24 11:57:38'),
(6, 2, 6, 4, 50.0000, 840.00, 42000.00, '2025-05-24 11:58:17', '2025-05-24 11:58:17'),
(7, 2, 2, 5, 1000.0000, 50.00, 50000.00, '2025-05-24 11:58:17', '2025-05-24 11:58:17'),
(8, 2, 4, 5, 1000.0000, 5.00, 5000.00, '2025-05-24 11:58:17', '2025-05-24 11:58:17'),
(9, 2, 3, 5, 1000.0000, 25.00, 25000.00, '2025-05-24 11:58:17', '2025-05-24 11:58:17'),
(10, 2, 5, 7, 900.0000, 25.00, 22500.00, '2025-05-24 11:58:17', '2025-05-24 11:58:17'),
(11, 3, 5, 7, 100.0000, 2000.00, 200000.00, '2025-05-29 14:08:16', '2025-05-29 14:08:16'),
(12, 4, 6, 4, 1.0000, 840.00, 840.00, '2025-06-17 09:24:40', '2025-06-17 09:24:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_return_details`
--

CREATE TABLE `purchase_return_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_return_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_detail_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'owner', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(2, 'admin_back_office', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(3, 'admin_gudang', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(4, 'admin_store', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(5, 'kasir', 'web', '2025-05-24 10:29:55', '2025-05-24 10:29:55'),
(6, 'owner_store', 'web', '2025-06-10 13:28:35', '2025-06-10 13:29:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 1),
(2, 2),
(2, 4),
(2, 6),
(3, 1),
(3, 2),
(3, 4),
(3, 6),
(4, 1),
(4, 2),
(4, 6),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 6),
(6, 1),
(6, 2),
(6, 4),
(6, 6),
(7, 1),
(7, 2),
(7, 4),
(7, 6),
(8, 1),
(8, 2),
(8, 6),
(9, 1),
(9, 2),
(9, 3),
(9, 6),
(10, 1),
(10, 2),
(10, 6),
(11, 1),
(11, 2),
(11, 6),
(12, 1),
(12, 2),
(12, 6),
(13, 1),
(13, 2),
(13, 6),
(14, 1),
(14, 2),
(14, 6),
(15, 1),
(15, 2),
(15, 6),
(16, 1),
(16, 2),
(16, 6),
(17, 1),
(17, 2),
(17, 3),
(17, 4),
(17, 6),
(18, 1),
(18, 2),
(18, 6),
(19, 1),
(19, 2),
(19, 6),
(20, 1),
(20, 2),
(20, 6),
(21, 1),
(21, 2),
(21, 3),
(21, 6),
(22, 1),
(22, 2),
(22, 6),
(23, 1),
(23, 2),
(23, 6),
(24, 1),
(24, 2),
(24, 6),
(25, 1),
(25, 2),
(25, 3),
(25, 6),
(26, 1),
(26, 2),
(26, 6),
(27, 1),
(27, 2),
(27, 6),
(28, 1),
(28, 2),
(28, 6),
(29, 1),
(29, 2),
(29, 3),
(29, 4),
(29, 6),
(30, 1),
(30, 2),
(30, 4),
(30, 6),
(31, 1),
(31, 2),
(31, 4),
(31, 6),
(32, 1),
(32, 2),
(32, 6),
(33, 1),
(33, 2),
(33, 3),
(33, 6),
(34, 1),
(34, 2),
(34, 3),
(34, 6),
(35, 1),
(35, 2),
(35, 3),
(35, 6),
(36, 1),
(36, 2),
(36, 6),
(37, 1),
(37, 2),
(37, 3),
(37, 4),
(37, 6),
(38, 1),
(38, 2),
(38, 3),
(38, 6),
(39, 1),
(39, 2),
(39, 3),
(39, 6),
(40, 1),
(40, 2),
(40, 6),
(41, 1),
(41, 2),
(41, 4),
(41, 5),
(41, 6),
(42, 1),
(42, 2),
(42, 4),
(42, 5),
(42, 6),
(43, 1),
(43, 2),
(43, 4),
(43, 6),
(44, 1),
(44, 2),
(44, 4),
(44, 6),
(45, 1),
(45, 2),
(45, 4),
(45, 6),
(46, 1),
(46, 2),
(46, 4),
(46, 6),
(47, 1),
(47, 2),
(47, 4),
(47, 6),
(48, 1),
(48, 2),
(48, 6),
(49, 1),
(49, 2),
(49, 3),
(50, 1),
(50, 2),
(50, 3),
(51, 1),
(51, 2),
(51, 4),
(51, 5),
(51, 6),
(52, 1),
(52, 2),
(52, 4),
(52, 6),
(53, 1),
(53, 2),
(53, 3),
(53, 4),
(53, 6),
(54, 1),
(54, 2),
(54, 3),
(54, 4),
(54, 6),
(55, 1),
(55, 2),
(55, 3),
(55, 4),
(55, 6),
(56, 1),
(56, 2),
(56, 6),
(57, 1),
(57, 2),
(57, 6),
(58, 1),
(58, 2),
(58, 6),
(59, 1),
(59, 2),
(59, 6),
(60, 1),
(60, 2),
(60, 6),
(61, 1),
(61, 2),
(62, 1),
(62, 2),
(63, 1),
(63, 2),
(63, 6),
(64, 1),
(64, 2),
(64, 6),
(65, 1),
(65, 2),
(65, 4),
(65, 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_type` enum('tunai','tempo','kartu') NOT NULL DEFAULT 'tunai',
  `dining_option` enum('makan_di_tempat','dibawa_pulang') NOT NULL DEFAULT 'dibawa_pulang' COMMENT 'Pilihan makan di tempat atau dibawa pulang',
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `total_payment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `change` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('paid','pending') NOT NULL DEFAULT 'paid',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sales`
--

INSERT INTO `sales` (`id`, `store_id`, `invoice_number`, `date`, `customer_name`, `total_amount`, `payment_type`, `dining_option`, `discount`, `tax`, `tax_enabled`, `total_payment`, `change`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'INV/O/20250529/0001', '2025-05-29', 'Testing', 154000.00, 'tunai', 'dibawa_pulang', 0.00, 14000.00, 1, 160000.00, 6000.00, 'paid', 5, NULL, '2025-05-29 12:16:06', '2025-05-29 12:16:06'),
(2, 1, 'INV/O/20250529/0002', '2025-05-29', NULL, 14000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 20000.00, 6000.00, 'paid', 5, NULL, '2025-05-29 13:37:23', '2025-05-29 13:37:23'),
(3, 1, 'INV/O/20250529/0003', '2025-05-29', NULL, 28000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 30000.00, 2000.00, 'paid', 5, NULL, '2025-05-29 13:52:51', '2025-05-29 13:52:51'),
(6, 1, 'INV/O/20250529/0004', '2025-05-29', NULL, 28000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 28000.00, 0.00, 'paid', 5, NULL, '2025-05-29 14:15:02', '2025-05-29 14:15:02'),
(7, 1, 'INV/O/20250529/0005', '2025-05-29', NULL, 14000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 14000.00, 0.00, 'paid', 5, NULL, '2025-05-29 19:38:42', '2025-05-29 19:38:42'),
(13, 1, 'INV/O/20250607/0006', '2025-06-07', NULL, 14000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 15000.00, 1000.00, 'paid', 3, NULL, '2025-06-07 06:52:53', '2025-06-07 06:52:53'),
(14, 1, 'INV/O/20250614/0007', '2025-06-14', NULL, 18000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 20000.00, 2000.00, 'paid', 7, NULL, '2025-06-14 23:00:29', '2025-06-14 23:00:29'),
(15, 1, 'INV/O/20250615/0008', '2025-06-15', NULL, 36000.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 50000.00, 14000.00, 'paid', 7, NULL, '2025-06-15 07:38:10', '2025-06-15 07:38:10'),
(16, 1, 'INV/O/20250617/0009', '2025-06-17', NULL, 37500.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 40000.00, 2500.00, 'paid', 5, NULL, '2025-06-17 08:57:28', '2025-06-17 08:57:28'),
(17, 1, 'INV/O/20250624/0010', '2025-06-24', NULL, 19500.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 20000.00, 500.00, 'paid', 4, NULL, '2025-06-24 17:32:43', '2025-06-24 17:32:43'),
(18, 1, 'INV/O/20250626/0011', '2025-06-26', NULL, 19500.00, 'tunai', 'dibawa_pulang', 0.00, 0.00, 0, 20000.00, 500.00, 'paid', 5, NULL, '2025-06-26 16:59:12', '2025-06-26 16:59:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sale_details`
--

CREATE TABLE `sale_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sale_details`
--

INSERT INTO `sale_details` (`id`, `sale_id`, `product_id`, `unit_id`, `quantity`, `price`, `discount`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 4, 10.0000, 14000.00, 0.00, 140000.00, '2025-05-29 12:16:06', '2025-05-29 12:16:06'),
(2, 2, 1, 4, 1.0000, 14000.00, 0.00, 14000.00, '2025-05-29 13:37:23', '2025-05-29 13:37:23'),
(3, 3, 1, 4, 2.0000, 14000.00, 0.00, 28000.00, '2025-05-29 13:52:51', '2025-05-29 13:52:51'),
(6, 6, 1, 4, 2.0000, 14000.00, 0.00, 28000.00, '2025-05-29 14:15:02', '2025-05-29 14:15:02'),
(7, 7, 1, 4, 1.0000, 14000.00, 0.00, 14000.00, '2025-05-29 19:38:42', '2025-05-29 19:38:42'),
(13, 13, 1, 4, 1.0000, 14000.00, 0.00, 14000.00, '2025-06-07 06:52:53', '2025-06-07 06:52:53'),
(14, 14, 1, 4, 1.0000, 18000.00, 0.00, 18000.00, '2025-06-14 23:00:29', '2025-06-14 23:00:29'),
(15, 15, 1, 4, 2.0000, 18000.00, 0.00, 36000.00, '2025-06-15 07:38:10', '2025-06-15 07:38:10'),
(16, 16, 1, 4, 2.0000, 18000.00, 0.00, 36000.00, '2025-06-17 08:57:28', '2025-06-17 08:57:28'),
(17, 16, 17, 4, 1.0000, 1500.00, 0.00, 1500.00, '2025-06-17 08:57:28', '2025-06-17 08:57:28'),
(18, 17, 1, 4, 1.0000, 18000.00, 0.00, 18000.00, '2025-06-24 17:32:43', '2025-06-24 17:32:43'),
(19, 17, 17, 4, 1.0000, 1500.00, 0.00, 1500.00, '2025-06-24 17:32:43', '2025-06-24 17:32:43'),
(20, 18, 1, 4, 1.0000, 18000.00, 0.00, 18000.00, '2025-06-26 16:59:12', '2025-06-26 16:59:12'),
(21, 18, 17, 4, 1.0000, 1500.00, 0.00, 1500.00, '2025-06-26 16:59:12', '2025-06-26 16:59:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipments`
--

CREATE TABLE `shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_order_id` bigint(20) UNSIGNED NOT NULL,
  `shipment_number` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `status` enum('pending','shipped','delivered') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `shipments`
--

INSERT INTO `shipments` (`id`, `store_order_id`, `shipment_number`, `date`, `status`, `note`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'SHP-20250524191327', '2025-05-24', 'delivered', NULL, 2, 2, '2025-05-24 12:13:27', '2025-05-24 12:15:12'),
(2, 2, 'SHP-20250529140000', '2025-05-29', 'delivered', NULL, 2, 2, '2025-05-29 14:00:00', '2025-05-29 14:01:09'),
(3, 9, 'SHP-20250620104827', '2025-06-20', 'delivered', NULL, 2, 2, '2025-06-20 10:48:27', '2025-06-20 10:48:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipment_details`
--

CREATE TABLE `shipment_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipment_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `shipment_details`
--

INSERT INTO `shipment_details` (`id`, `shipment_id`, `product_id`, `unit_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 1000.0000, '2025-05-24 12:13:27', '2025-05-24 12:13:27'),
(2, 1, 3, 5, 1000.0000, '2025-05-24 12:13:27', '2025-05-24 12:13:27'),
(3, 1, 4, 5, 1000.0000, '2025-05-24 12:13:27', '2025-05-24 12:13:27'),
(4, 1, 5, 7, 900.0000, '2025-05-24 12:13:27', '2025-05-24 12:13:27'),
(5, 1, 6, 4, 50.0000, '2025-05-24 12:13:27', '2025-05-24 12:13:27'),
(6, 2, 5, 7, 10.0000, '2025-05-29 14:00:00', '2025-05-29 14:00:00'),
(7, 3, 2, 5, 5.0000, '2025-06-20 10:48:27', '2025-06-20 10:48:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(255) NOT NULL,
  `type` enum('warehouse','store') NOT NULL DEFAULT 'warehouse',
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`id`, `date`, `reference`, `type`, `store_id`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '2025-05-29', 'ADJ-20250529115432', 'warehouse', NULL, 1, NULL, '2025-05-29 11:54:49', '2025-05-29 11:54:49'),
(2, '2025-05-29', 'ADJ-20250529135937', 'warehouse', NULL, 3, NULL, '2025-05-29 13:59:49', '2025-05-29 13:59:49'),
(3, '2025-05-29', 'ADJ-20250529200859', 'store', 4, 6, NULL, '2025-05-29 20:10:12', '2025-05-29 20:10:12'),
(4, '2025-06-15', 'ADJ-20250615073641', 'store', 1, 7, NULL, '2025-06-15 07:37:01', '2025-06-15 07:37:01'),
(5, '2025-06-15', 'ADJ-20250615073725', 'store', 1, 7, NULL, '2025-06-15 07:37:39', '2025-06-15 07:37:39'),
(6, '2025-06-17', 'ADJ-20250617205222', 'warehouse', NULL, 1, NULL, '2025-06-17 20:52:50', '2025-06-17 20:52:50'),
(7, '2025-06-17', 'ADJ-20250617205306', 'warehouse', NULL, 1, NULL, '2025-06-17 20:53:57', '2025-06-17 20:53:57'),
(8, '2025-06-17', 'ADJ-20250617205420', 'warehouse', NULL, 1, NULL, '2025-06-17 20:54:32', '2025-06-17 20:54:32'),
(9, '2025-06-24', 'ADJ-20250624105042', 'warehouse', NULL, 1, NULL, '2025-06-24 10:51:51', '2025-06-24 10:51:51'),
(10, '2025-06-24', 'ADJ-20250624105207', 'warehouse', NULL, 1, NULL, '2025-06-24 10:52:24', '2025-06-24 10:52:24'),
(11, '2025-06-24', 'ADJ-20250624105245', 'warehouse', NULL, 1, NULL, '2025-06-24 10:52:53', '2025-06-24 10:52:53'),
(12, '2025-06-24', 'ADJ-20250624105318-1', 'store', 1, 1, NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(13, '2025-06-24', 'ADJ-20250624105318-4', 'store', 4, 1, NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(14, '2025-06-24', 'ADJ-20250624105517-1', 'store', 1, 1, NULL, '2025-06-24 10:55:32', '2025-06-24 10:55:32'),
(15, '2025-06-24', 'ADJ-20250624105517-4', 'store', 4, 1, NULL, '2025-06-24 10:55:32', '2025-06-24 10:55:32'),
(16, '2025-07-22', 'ADJ-20250722170819', 'warehouse', NULL, 1, NULL, '2025-07-22 17:08:35', '2025-07-22 17:08:35'),
(17, '2025-07-22', 'ADJ-20250722171225', 'store', 1, 4, NULL, '2025-07-22 17:12:41', '2025-07-22 17:12:41'),
(18, '2025-07-22', 'ADJ-20250722194851', 'warehouse', NULL, 1, NULL, '2025-07-22 19:49:03', '2025-07-22 19:49:03'),
(19, '2025-07-22', 'ADJ-20250722195030', 'store', 1, 4, NULL, '2025-07-22 19:50:42', '2025-07-22 19:50:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_adjustment_details`
--

CREATE TABLE `stock_adjustment_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_adjustment_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `type` enum('addition','reduction') NOT NULL DEFAULT 'addition',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stock_adjustment_details`
--

INSERT INTO `stock_adjustment_details` (`id`, `stock_adjustment_id`, `product_id`, `unit_id`, `quantity`, `type`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 4, 50.0000, 'addition', 'Testing', '2025-05-29 11:54:49', '2025-05-29 11:54:49'),
(2, 2, 5, 7, 20.0000, 'addition', 'TESTING', '2025-05-29 13:59:49', '2025-05-29 13:59:49'),
(3, 3, 6, 4, 100.0000, 'addition', NULL, '2025-05-29 20:10:12', '2025-05-29 20:10:12'),
(4, 3, 2, 5, 100.0000, 'addition', NULL, '2025-05-29 20:10:12', '2025-05-29 20:10:12'),
(5, 3, 4, 5, 100.0000, 'addition', NULL, '2025-05-29 20:10:12', '2025-05-29 20:10:12'),
(6, 3, 3, 5, 100.0000, 'addition', NULL, '2025-05-29 20:10:12', '2025-05-29 20:10:12'),
(7, 3, 5, 7, 100.0000, 'addition', NULL, '2025-05-29 20:10:12', '2025-05-29 20:10:12'),
(8, 4, 2, 5, 100.0000, 'addition', NULL, '2025-06-15 07:37:01', '2025-06-15 07:37:01'),
(9, 5, 2, 5, 300.0000, 'addition', NULL, '2025-06-15 07:37:39', '2025-06-15 07:37:39'),
(10, 6, 2, 5, 100.0000, 'addition', NULL, '2025-06-17 20:52:50', '2025-06-17 20:52:50'),
(11, 7, 3, 5, 100.0000, 'addition', NULL, '2025-06-17 20:53:57', '2025-06-17 20:53:57'),
(12, 7, 2, 5, 100.0000, 'addition', NULL, '2025-06-17 20:53:57', '2025-06-17 20:53:57'),
(13, 7, 6, 4, 100.0000, 'addition', NULL, '2025-06-17 20:53:57', '2025-06-17 20:53:57'),
(14, 7, 5, 7, 100.0000, 'addition', NULL, '2025-06-17 20:53:57', '2025-06-17 20:53:57'),
(15, 8, 4, 5, 1000.0000, 'addition', NULL, '2025-06-17 20:54:32', '2025-06-17 20:54:32'),
(16, 9, 6, 4, 1000.0000, 'addition', NULL, '2025-06-24 10:51:51', '2025-06-24 10:51:51'),
(17, 9, 2, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:51:51', '2025-06-24 10:51:51'),
(18, 9, 4, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:51:51', '2025-06-24 10:51:51'),
(19, 9, 3, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:51:51', '2025-06-24 10:51:51'),
(20, 9, 5, 7, 10000.0000, 'addition', NULL, '2025-06-24 10:51:51', '2025-06-24 10:51:51'),
(21, 10, 1, 4, 1000000.0000, 'addition', NULL, '2025-06-24 10:52:24', '2025-06-24 10:52:24'),
(22, 11, 17, 4, 100.0000, 'addition', NULL, '2025-06-24 10:52:53', '2025-06-24 10:52:53'),
(23, 12, 6, 4, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(24, 12, 2, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(25, 12, 1, 4, 100000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(26, 12, 4, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(27, 12, 3, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(28, 12, 17, 4, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(29, 12, 5, 7, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(30, 13, 6, 4, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(31, 13, 2, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(32, 13, 1, 4, 100000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(33, 13, 4, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(34, 13, 3, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(35, 13, 17, 4, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(36, 13, 5, 7, 1000.0000, 'addition', NULL, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(37, 14, 2, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:32', '2025-06-24 10:55:32'),
(38, 15, 2, 5, 1000.0000, 'addition', NULL, '2025-06-24 10:55:32', '2025-06-24 10:55:32'),
(39, 16, 3, 9, 10.0000, 'addition', NULL, '2025-07-22 17:08:35', '2025-07-22 17:08:35'),
(40, 17, 3, 9, 10.0000, 'addition', NULL, '2025-07-22 17:12:41', '2025-07-22 17:12:41'),
(41, 18, 18, 9, 10.0000, 'addition', NULL, '2025-07-22 19:49:03', '2025-07-22 19:49:03'),
(42, 19, 18, 9, 10.0000, 'addition', NULL, '2025-07-22 19:50:42', '2025-07-22 19:50:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_opnames`
--

CREATE TABLE `stock_opnames` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(255) NOT NULL,
  `type` enum('warehouse','store') NOT NULL DEFAULT 'warehouse',
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','confirmed') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_opname_details`
--

CREATE TABLE `stock_opname_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_opname_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `system_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `physical_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `difference` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_stores`
--

CREATE TABLE `stock_stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stock_stores`
--

INSERT INTO `stock_stores` (`id`, `store_id`, `product_id`, `unit_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 655.0000, '2025-05-24 12:15:12', '2025-06-24 10:55:32'),
(2, 1, 3, 5, 350.0000, '2025-05-24 12:15:12', '2025-06-24 10:55:02'),
(3, 1, 4, 5, 900.0000, '2025-05-24 12:15:12', '2025-06-24 10:55:02'),
(4, 1, 5, 7, 1360.0000, '2025-05-24 12:15:12', '2025-06-24 10:55:02'),
(5, 1, 6, 4, 1026.0000, '2025-05-24 12:15:12', '2025-06-24 10:55:02'),
(10, 4, 6, 4, 1100.0000, '2025-05-29 20:10:12', '2025-06-24 10:55:02'),
(11, 4, 2, 5, 2100.0000, '2025-05-29 20:10:12', '2025-06-24 10:55:32'),
(12, 4, 4, 5, 1100.0000, '2025-05-29 20:10:12', '2025-06-24 10:55:02'),
(13, 4, 3, 5, 1100.0000, '2025-05-29 20:10:12', '2025-06-24 10:55:02'),
(14, 4, 5, 7, 1100.0000, '2025-05-29 20:10:12', '2025-06-24 10:55:02'),
(15, 1, 17, 4, 997.0000, '2025-06-15 09:01:13', '2025-06-24 10:55:02'),
(16, 4, 17, 4, 1000.0000, '2025-06-15 09:01:13', '2025-06-24 10:55:02'),
(17, 1, 1, 4, 100000.0000, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(18, 4, 1, 4, 100000.0000, '2025-06-24 10:55:02', '2025-06-24 10:55:02'),
(19, 1, 3, 9, 10.0000, '2025-07-22 17:12:41', '2025-07-22 17:12:41'),
(20, 1, 18, 9, 10.0000, '2025-07-22 19:50:42', '2025-07-22 19:50:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_warehouses`
--

CREATE TABLE `stock_warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stock_warehouses`
--

INSERT INTO `stock_warehouses` (`id`, `product_id`, `unit_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 1000000.0000, '2025-05-24 11:38:30', '2025-06-24 10:52:24'),
(2, 2, 5, 1195.0000, '2025-05-24 11:40:23', '2025-06-24 10:51:51'),
(3, 3, 5, 1100.0000, '2025-05-24 11:41:04', '2025-06-24 10:51:51'),
(4, 4, 5, 2000.0000, '2025-05-24 11:44:05', '2025-06-24 10:51:51'),
(5, 5, 7, 10210.0000, '2025-05-24 11:46:11', '2025-06-24 10:51:51'),
(6, 6, 4, 1150.0000, '2025-05-24 11:47:28', '2025-06-24 10:51:51'),
(10, 17, 4, 100.0000, '2025-06-24 10:52:53', '2025-06-24 10:52:53'),
(11, 3, 9, 10.0000, '2025-07-22 17:08:35', '2025-07-22 17:08:35'),
(12, 18, 9, 10.0000, '2025-07-22 19:47:45', '2025-07-22 19:49:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stores`
--

INSERT INTO `stores` (`id`, `name`, `address`, `phone`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Outlet Es Teller Kroya', 'Jl. Jend. Sudirman no. 60 Kroya', '085777433065', 'tokopusat@example.com', 1, '2025-05-24 10:29:56', '2025-05-25 05:57:24'),
(4, 'Es Teller Purwokerto', 'pwt', '-', 'pwt@gmail.com', 1, '2025-05-29 20:06:06', '2025-05-29 20:06:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `store_orders`
--

CREATE TABLE `store_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `status` enum('pending','confirmed_by_admin','forwarded_to_warehouse','shipped','delivered','completed') NOT NULL DEFAULT 'pending' COMMENT 'Status pesanan: pending/confirmed_by_admin/forwarded_to_warehouse/shipped/delivered/completed',
  `payment_type` varchar(255) NOT NULL DEFAULT 'cash',
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `confirmed_at` datetime DEFAULT NULL,
  `forwarded_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `store_orders`
--

INSERT INTO `store_orders` (`id`, `store_id`, `order_number`, `date`, `status`, `payment_type`, `due_date`, `total_amount`, `shipping_cost`, `grand_total`, `confirmed_at`, `forwarded_at`, `shipped_at`, `delivered_at`, `completed_at`, `note`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD-20250524190628', '2025-05-24', 'completed', 'cash', NULL, 77342000.00, 0.00, 0.00, '2025-05-24 19:10:39', '2025-05-24 19:10:46', '2025-05-24 19:13:27', '2025-05-24 19:15:12', '2025-05-24 19:15:12', 'permintaan outlet kroya', 4, 1, '2025-05-24 12:06:28', '2025-05-24 12:15:12'),
(2, 1, 'ORD-20250529135730', '2025-05-29', 'completed', 'cash', NULL, 20000.00, 0.00, 0.00, '2025-05-29 13:58:28', '2025-05-29 13:58:44', '2025-05-29 14:00:00', '2025-05-29 14:01:09', '2025-05-29 14:01:09', 'TESTING', 4, 3, '2025-05-29 13:57:30', '2025-05-29 14:01:09'),
(3, 4, 'ORD-20250614200839', '2025-06-14', 'forwarded_to_warehouse', 'cash', NULL, 50000.00, 0.00, 0.00, '2025-06-17 07:38:48', '2025-06-17 07:38:52', NULL, NULL, NULL, 'ok', 6, 1, '2025-06-14 20:08:39', '2025-06-17 07:38:52'),
(4, 1, 'ORD-20250615074558', '2025-06-15', 'forwarded_to_warehouse', 'cash', NULL, 7500000.00, 0.00, 0.00, '2025-06-15 08:08:38', '2025-06-15 08:08:43', NULL, NULL, NULL, NULL, 4, 3, '2025-06-15 07:45:58', '2025-06-15 08:08:43'),
(5, 1, 'ORD-20250617080410', '2025-06-17', 'forwarded_to_warehouse', 'cash', NULL, 750000.00, 0.00, 0.00, '2025-06-17 08:04:55', '2025-06-17 08:05:08', NULL, NULL, NULL, NULL, 4, 3, '2025-06-17 08:04:10', '2025-06-17 08:05:08'),
(6, 1, 'ORD-20250617091142', '2025-06-17', 'forwarded_to_warehouse', 'cash', NULL, 75000000.00, 0.00, 0.00, '2025-06-17 09:22:04', '2025-06-17 09:25:16', NULL, NULL, NULL, NULL, 4, 3, '2025-06-17 09:11:42', '2025-06-17 09:25:16'),
(7, 1, 'ORD-20250617092647', '2025-06-17', 'forwarded_to_warehouse', 'cash', NULL, 25050.00, 0.00, 0.00, '2025-06-17 09:27:09', '2025-06-17 09:27:15', NULL, NULL, NULL, NULL, 4, 3, '2025-06-17 09:26:47', '2025-06-17 09:27:15'),
(8, 1, 'ORD-20250617210102', '2025-06-17', 'forwarded_to_warehouse', 'cash', NULL, 278900.00, 0.00, 0.00, '2025-06-17 21:01:57', '2025-06-17 21:02:04', NULL, NULL, NULL, NULL, 4, 3, '2025-06-17 21:01:02', '2025-06-17 21:02:04'),
(9, 1, 'ORD-20250620104713', '2025-06-20', 'completed', 'cash', NULL, 250.00, 25000.00, 25250.00, '2025-06-20 10:48:02', '2025-06-20 10:48:05', '2025-06-20 10:48:27', '2025-06-20 10:48:44', '2025-06-20 10:48:44', 'Testing Ongkir', 4, 3, '2025-06-20 10:47:13', '2025-06-20 10:48:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `store_order_details`
--

CREATE TABLE `store_order_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `store_order_details`
--

INSERT INTO `store_order_details` (`id`, `store_order_id`, `product_id`, `unit_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 1000.0000, 50000.00, 50000000.00, '2025-05-24 12:06:28', '2025-05-24 12:06:28'),
(2, 1, 3, 5, 1000.0000, 25000.00, 25000000.00, '2025-05-24 12:06:28', '2025-05-24 12:06:28'),
(3, 1, 4, 5, 1000.0000, 500.00, 500000.00, '2025-05-24 12:06:28', '2025-05-24 12:06:28'),
(4, 1, 5, 7, 900.0000, 2000.00, 1800000.00, '2025-05-24 12:06:28', '2025-05-24 12:06:28'),
(5, 1, 6, 4, 50.0000, 840.00, 42000.00, '2025-05-24 12:06:28', '2025-05-24 12:06:28'),
(6, 2, 5, 7, 10.0000, 2000.00, 20000.00, '2025-05-29 13:57:30', '2025-05-29 13:57:30'),
(7, 3, 2, 4, 1.0000, 50000.00, 50000.00, '2025-06-14 20:08:39', '2025-06-14 20:08:39'),
(8, 4, 2, 6, 100.0000, 50000.00, 5000000.00, '2025-06-15 07:45:58', '2025-06-15 07:45:58'),
(9, 4, 3, 5, 100.0000, 25000.00, 2500000.00, '2025-06-15 07:45:58', '2025-06-15 07:45:58'),
(10, 5, 2, 6, 10.0000, 50000.00, 500000.00, '2025-06-17 08:04:10', '2025-06-17 08:04:10'),
(11, 5, 3, 6, 10.0000, 25000.00, 250000.00, '2025-06-17 08:04:10', '2025-06-17 08:04:10'),
(12, 6, 2, 5, 1000.0000, 50000.00, 50000000.00, '2025-06-17 09:11:42', '2025-06-17 09:11:42'),
(13, 6, 3, 5, 1000.0000, 25000.00, 25000000.00, '2025-06-17 09:11:42', '2025-06-17 09:11:42'),
(14, 7, 2, 6, 1.0000, 50.00, 50.00, '2025-06-17 09:26:47', '2025-06-17 09:26:47'),
(15, 7, 3, 6, 1.0000, 25000.00, 25000.00, '2025-06-17 09:26:47', '2025-06-17 09:26:47'),
(16, 8, 2, 4, 10.0000, 50.00, 500.00, '2025-06-17 21:01:02', '2025-06-17 21:01:02'),
(17, 8, 3, 5, 10.0000, 25000.00, 250000.00, '2025-06-17 21:01:02', '2025-06-17 21:01:02'),
(18, 8, 5, 7, 10.0000, 2000.00, 20000.00, '2025-06-17 21:01:02', '2025-06-17 21:01:02'),
(19, 8, 6, 4, 10.0000, 840.00, 8400.00, '2025-06-17 21:01:02', '2025-06-17 21:01:02'),
(20, 9, 2, 5, 5.0000, 50.00, 250.00, '2025-06-20 10:47:13', '2025-06-20 10:47:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `store_returns`
--

CREATE TABLE `store_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `store_return_details`
--

CREATE TABLE `store_return_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_return_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `payment_term` enum('tunai','tempo') NOT NULL DEFAULT 'tunai',
  `credit_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `address`, `phone`, `email`, `payment_term`, `credit_limit`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'TOKO POJOK', 'KROYA', NULL, NULL, 'tunai', 0.00, 1, '2025-05-24 11:19:08', '2025-05-24 11:19:08'),
(2, 'MATURNUWUN', NULL, NULL, NULL, 'tempo', 50000000.00, 1, '2025-05-24 11:20:10', '2025-05-24 11:20:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_base_unit` tinyint(1) NOT NULL DEFAULT 0,
  `conversion_factor` decimal(15,4) NOT NULL DEFAULT 1.0000,
  `base_unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `units`
--

INSERT INTO `units` (`id`, `name`, `is_base_unit`, `conversion_factor`, `base_unit_id`, `created_at`, `updated_at`) VALUES
(2, 'PACK', 1, 1.0000, NULL, '2025-05-24 11:21:40', '2025-05-24 11:21:40'),
(3, 'KARTON', 1, 1.0000, NULL, '2025-05-24 11:21:59', '2025-05-24 11:21:59'),
(4, 'PCS', 1, 1.0000, NULL, '2025-05-24 11:22:11', '2025-05-24 11:22:11'),
(5, 'GR', 1, 1.0000, NULL, '2025-05-24 11:22:59', '2025-05-24 11:22:59'),
(6, 'KG', 0, 1000.0000, 5, '2025-05-24 11:23:20', '2025-05-24 11:23:20'),
(7, 'Liter', 0, 1000.0000, 8, '2025-05-24 11:45:00', '2025-07-22 16:43:07'),
(8, 'Ml', 1, 1.0000, NULL, '2025-07-22 16:37:20', '2025-07-22 16:48:45'),
(9, 'L', 1, 1.0000, NULL, '2025-07-22 16:43:36', '2025-07-22 16:46:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `store_id`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Owner', 'owner@gmail.com', NULL, NULL, '$2y$12$ZAxYD.bTXwU6trNhRnCn/.f18q5lINPp66y.nby/gCtjvpOIOrpsW', 'OrgDM16erE0UraNWL6m5yGLphHaQrDLc9ngYSYoJ202twZauLPR6LWobmcUq', '2025-05-24 10:29:56', '2025-05-24 10:41:19'),
(2, 'Gudang Pusat', 'gudangpusat@gmail.com', NULL, NULL, '$2y$12$LT99.FND6gArSK3SJe/gt.P25V4irMSbjY4ouH0MENf2mkYmyPkxm', 'OSGbBNdtOhIqOgKtZc3AkfYKxJo87EdjclbefUPTFRE62u3sMvoPQE92iT6A', '2025-05-24 10:40:48', '2025-05-24 10:40:48'),
(3, 'Admin', 'admin@gmail.com', NULL, NULL, '$2y$12$Cqxc/zhQEkmsfUxJ.anCAOe5Mb2c3xCgYxb4A5AMarQVdqOGoGrLy', NULL, '2025-05-24 10:41:54', '2025-05-24 10:41:54'),
(4, 'admin outlet es teller kroya', 'adminkroya1@gmail.com', 1, NULL, '$2y$12$R7adWT5iIShYG.Qf97TiXeAhlgwmf08sbblSLnyVk.JY2TEesEXAa', 'JLTM6in5H9Qh0FJKrD9mpjCZI2olGnWDGnPRdFRBSj8ITKi8iuFOI9uNmaGS', '2025-05-24 10:43:08', '2025-05-24 10:43:08'),
(5, 'kasir outlet es teller kroya', 'kasirkroya1@gmail.com', 1, NULL, '$2y$12$uGh3YXX9rxlpZ3Ks8tuxbeohb1KBQMLyDVgHHh8x/6mjJUAs0rbKy', NULL, '2025-05-24 10:43:56', '2025-05-24 10:43:56'),
(6, 'admin pwt 1', 'adminpwt1@gmail.com', 4, NULL, '$2y$12$j0Ut3jomc.2MH3W5my.kP.15Df.VXfhB13NA3f1jQRJV9rH6/4Mpa', NULL, '2025-05-29 20:08:08', '2025-05-29 20:08:08'),
(7, 'owner store kroya 1', 'ownerkroya1@gmail.com', 1, NULL, '$2y$12$gGx0hyDD5WfNvTV7xevsp.zOTEtjvJhD3TjPn3064OITMJe7Vn4.u', NULL, '2025-06-10 13:29:47', '2025-06-10 13:29:47');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `account_payables`
--
ALTER TABLE `account_payables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_payables_purchase_id_foreign` (`purchase_id`),
  ADD KEY `account_payables_supplier_id_foreign` (`supplier_id`),
  ADD KEY `account_payables_created_by_foreign` (`created_by`),
  ADD KEY `account_payables_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `account_receivables`
--
ALTER TABLE `account_receivables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_receivables_store_order_id_foreign` (`store_order_id`),
  ADD KEY `account_receivables_store_id_foreign` (`store_id`),
  ADD KEY `account_receivables_created_by_foreign` (`created_by`),
  ADD KEY `account_receivables_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `balance_categories`
--
ALTER TABLE `balance_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balance_categories_store_id_foreign` (`store_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_category_id_foreign` (`category_id`),
  ADD KEY `expenses_store_id_foreign` (`store_id`),
  ADD KEY `expenses_created_by_foreign` (`created_by`),
  ADD KEY `expenses_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_categories_store_id_foreign` (`store_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `financial_journals`
--
ALTER TABLE `financial_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financial_journals_store_id_foreign` (`store_id`),
  ADD KEY `financial_journals_created_by_foreign` (`created_by`),
  ADD KEY `financial_journals_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `initial_balances`
--
ALTER TABLE `initial_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `initial_balances_category_id_foreign` (`category_id`),
  ADD KEY `initial_balances_store_id_foreign` (`store_id`),
  ADD KEY `initial_balances_created_by_foreign` (`created_by`),
  ADD KEY `initial_balances_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_code_unique` (`code`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_base_unit_id_foreign` (`base_unit_id`),
  ADD KEY `products_store_id_foreign` (`store_id`);

--
-- Indeks untuk tabel `product_ingredients`
--
ALTER TABLE `product_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_ingredients_product_id_ingredient_id_unique` (`product_id`,`ingredient_id`),
  ADD KEY `product_ingredients_ingredient_id_foreign` (`ingredient_id`),
  ADD KEY `product_ingredients_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `product_store_prices`
--
ALTER TABLE `product_store_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_store_unit` (`product_id`,`store_id`,`unit_id`),
  ADD KEY `product_store_prices_unit_id_foreign` (`unit_id`),
  ADD KEY `product_store_prices_store_id_is_active_index` (`store_id`,`is_active`);

--
-- Indeks untuk tabel `product_units`
--
ALTER TABLE `product_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_units_product_id_unit_id_unique` (`product_id`,`unit_id`),
  ADD KEY `product_units_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchases_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchases_created_by_foreign` (`created_by`),
  ADD KEY `purchases_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_returns_purchase_id_foreign` (`purchase_id`),
  ADD KEY `purchase_returns_created_by_foreign` (`created_by`),
  ADD KEY `purchase_returns_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `purchase_return_details`
--
ALTER TABLE `purchase_return_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_return_details_purchase_return_id_foreign` (`purchase_return_id`),
  ADD KEY `purchase_return_details_product_id_foreign` (`product_id`),
  ADD KEY `purchase_return_details_unit_id_foreign` (`unit_id`),
  ADD KEY `purchase_return_details_purchase_detail_id_foreign` (`purchase_detail_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_store_id_foreign` (`store_id`),
  ADD KEY `sales_created_by_foreign` (`created_by`),
  ADD KEY `sales_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_details_sale_id_foreign` (`sale_id`),
  ADD KEY `sale_details_product_id_foreign` (`product_id`),
  ADD KEY `sale_details_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipments_store_order_id_foreign` (`store_order_id`),
  ADD KEY `shipments_created_by_foreign` (`created_by`),
  ADD KEY `shipments_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `shipment_details`
--
ALTER TABLE `shipment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_details_shipment_id_foreign` (`shipment_id`),
  ADD KEY `shipment_details_product_id_foreign` (`product_id`),
  ADD KEY `shipment_details_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustments_store_id_foreign` (`store_id`),
  ADD KEY `stock_adjustments_created_by_foreign` (`created_by`),
  ADD KEY `stock_adjustments_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `stock_adjustment_details`
--
ALTER TABLE `stock_adjustment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustment_details_stock_adjustment_id_foreign` (`stock_adjustment_id`),
  ADD KEY `stock_adjustment_details_product_id_foreign` (`product_id`),
  ADD KEY `stock_adjustment_details_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_opnames_store_id_foreign` (`store_id`),
  ADD KEY `stock_opnames_created_by_foreign` (`created_by`),
  ADD KEY `stock_opnames_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_opname_details_stock_opname_id_foreign` (`stock_opname_id`),
  ADD KEY `stock_opname_details_product_id_foreign` (`product_id`),
  ADD KEY `stock_opname_details_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `stock_stores`
--
ALTER TABLE `stock_stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_stores_store_id_product_id_unit_id_unique` (`store_id`,`product_id`,`unit_id`),
  ADD KEY `stock_stores_product_id_foreign` (`product_id`),
  ADD KEY `stock_stores_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `stock_warehouses`
--
ALTER TABLE `stock_warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_warehouses_product_id_unit_id_unique` (`product_id`,`unit_id`),
  ADD KEY `stock_warehouses_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `store_orders`
--
ALTER TABLE `store_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_orders_store_id_foreign` (`store_id`),
  ADD KEY `store_orders_created_by_foreign` (`created_by`),
  ADD KEY `store_orders_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `store_order_details`
--
ALTER TABLE `store_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_order_details_store_order_id_foreign` (`store_order_id`),
  ADD KEY `store_order_details_product_id_foreign` (`product_id`),
  ADD KEY `store_order_details_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `store_returns`
--
ALTER TABLE `store_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_returns_store_id_foreign` (`store_id`),
  ADD KEY `store_returns_created_by_foreign` (`created_by`),
  ADD KEY `store_returns_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `store_return_details`
--
ALTER TABLE `store_return_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_return_details_store_return_id_foreign` (`store_return_id`),
  ADD KEY `store_return_details_product_id_foreign` (`product_id`),
  ADD KEY `store_return_details_unit_id_foreign` (`unit_id`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `units_base_unit_id_foreign` (`base_unit_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_store_id_foreign` (`store_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `account_payables`
--
ALTER TABLE `account_payables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `account_receivables`
--
ALTER TABLE `account_receivables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `balance_categories`
--
ALTER TABLE `balance_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `financial_journals`
--
ALTER TABLE `financial_journals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `initial_balances`
--
ALTER TABLE `initial_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `product_ingredients`
--
ALTER TABLE `product_ingredients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `product_store_prices`
--
ALTER TABLE `product_store_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `product_units`
--
ALTER TABLE `product_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchase_return_details`
--
ALTER TABLE `purchase_return_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `shipment_details`
--
ALTER TABLE `shipment_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `stock_adjustment_details`
--
ALTER TABLE `stock_adjustment_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT untuk tabel `stock_opnames`
--
ALTER TABLE `stock_opnames`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stock_stores`
--
ALTER TABLE `stock_stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `stock_warehouses`
--
ALTER TABLE `stock_warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `store_orders`
--
ALTER TABLE `store_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `store_order_details`
--
ALTER TABLE `store_order_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `store_returns`
--
ALTER TABLE `store_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `store_return_details`
--
ALTER TABLE `store_return_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `account_payables`
--
ALTER TABLE `account_payables`
  ADD CONSTRAINT `account_payables_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `account_payables_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_payables_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `account_payables_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_receivables`
--
ALTER TABLE `account_receivables`
  ADD CONSTRAINT `account_receivables_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `account_receivables_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `account_receivables_store_order_id_foreign` FOREIGN KEY (`store_order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_receivables_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `balance_categories`
--
ALTER TABLE `balance_categories`
  ADD CONSTRAINT `balance_categories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
  ADD CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expenses_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `expenses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD CONSTRAINT `expense_categories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `financial_journals`
--
ALTER TABLE `financial_journals`
  ADD CONSTRAINT `financial_journals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `financial_journals_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `financial_journals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `initial_balances`
--
ALTER TABLE `initial_balances`
  ADD CONSTRAINT `initial_balances_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `balance_categories` (`id`),
  ADD CONSTRAINT `initial_balances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `initial_balances_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `initial_balances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_base_unit_id_foreign` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `product_ingredients`
--
ALTER TABLE `product_ingredients`
  ADD CONSTRAINT `product_ingredients_ingredient_id_foreign` FOREIGN KEY (`ingredient_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_ingredients_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_ingredients_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `product_store_prices`
--
ALTER TABLE `product_store_prices`
  ADD CONSTRAINT `product_store_prices_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_store_prices_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_store_prices_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_units`
--
ALTER TABLE `product_units`
  ADD CONSTRAINT `product_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_units_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchases_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_returns_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`),
  ADD CONSTRAINT `purchase_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_return_details`
--
ALTER TABLE `purchase_return_details`
  ADD CONSTRAINT `purchase_return_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_return_details_purchase_detail_id_foreign` FOREIGN KEY (`purchase_detail_id`) REFERENCES `purchase_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_return_details_purchase_return_id_foreign` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_return_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `sales_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `sale_details`
--
ALTER TABLE `sale_details`
  ADD CONSTRAINT `sale_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sale_details_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `shipments_store_order_id_foreign` FOREIGN KEY (`store_order_id`) REFERENCES `store_orders` (`id`),
  ADD CONSTRAINT `shipments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `shipment_details`
--
ALTER TABLE `shipment_details`
  ADD CONSTRAINT `shipment_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `shipment_details_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_adjustments_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_adjustment_details`
--
ALTER TABLE `stock_adjustment_details`
  ADD CONSTRAINT `stock_adjustment_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_adjustment_details_stock_adjustment_id_foreign` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD CONSTRAINT `stock_opnames_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_opnames_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_opnames_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  ADD CONSTRAINT `stock_opname_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_opname_details_stock_opname_id_foreign` FOREIGN KEY (`stock_opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_opname_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_stores`
--
ALTER TABLE `stock_stores`
  ADD CONSTRAINT `stock_stores_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_stores_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_stores_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stock_warehouses`
--
ALTER TABLE `stock_warehouses`
  ADD CONSTRAINT `stock_warehouses_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_warehouses_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `store_orders`
--
ALTER TABLE `store_orders`
  ADD CONSTRAINT `store_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `store_orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `store_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `store_order_details`
--
ALTER TABLE `store_order_details`
  ADD CONSTRAINT `store_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `store_order_details_store_order_id_foreign` FOREIGN KEY (`store_order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_order_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `store_returns`
--
ALTER TABLE `store_returns`
  ADD CONSTRAINT `store_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `store_returns_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `store_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `store_return_details`
--
ALTER TABLE `store_return_details`
  ADD CONSTRAINT `store_return_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `store_return_details_store_return_id_foreign` FOREIGN KEY (`store_return_id`) REFERENCES `store_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_return_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Ketidakleluasaan untuk tabel `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_base_unit_id_foreign` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
