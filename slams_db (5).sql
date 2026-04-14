-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 13, 2026 at 05:24 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `slams_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int UNSIGNED NOT NULL,
  `lab_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('available','maintenance','faulty') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'available',
  `quantity` int NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `lab_id`, `name`, `status`, `quantity`, `image`) VALUES
(5, 1, 'Projector', 'available', 2, 'images/assets/1768147143_aa463e8ab9064908fa35.png'),
(6, 1, 'Desktop Computer', 'available', 20, 'images/assets/1768147134_a534e4cf1f7c583b13d9.png'),
(7, 2, '3D Printer', 'available', 1, 'images/assets/1768147152_ab952277d0209f0714a5.png'),
(8, 3, 'Hydraulic Bench', 'available', 1, 'images/assets/1768147161_80d64e951f40c87b163d.png');

-- --------------------------------------------------------

--
-- Table structure for table `auth_groups_users`
--

CREATE TABLE `auth_groups_users` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_groups_users`
--

INSERT INTO `auth_groups_users` (`id`, `user_id`, `group`, `created_at`) VALUES
(1, 1, 'admin', '2025-11-24 03:27:02'),
(2, 2, 'pic', '2025-11-24 03:27:03'),
(3, 3, 'technician', '2025-11-24 03:27:03'),
(4, 4, 'staff', '2025-11-24 03:27:03'),
(5, 5, 'student', '2025-11-24 03:27:03'),
(6, 6, 'external', '2025-11-24 03:27:03'),
(7, 7, 'manager', '2025-12-07 14:16:58'),
(8, 10, 'pic', '2026-01-04 15:01:27'),
(12, 11, 'pic', '2026-01-09 18:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `auth_identities`
--

CREATE TABLE `auth_identities` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `secret` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `secret2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `extra` text COLLATE utf8mb4_general_ci,
  `force_reset` tinyint(1) NOT NULL DEFAULT '0',
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_identities`
--

INSERT INTO `auth_identities` (`id`, `user_id`, `type`, `name`, `secret`, `secret2`, `expires`, `extra`, `force_reset`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'email_password', NULL, 'admin@fkmp.uthm.edu.my', '$2y$12$KMK.I.EdmNO8ivaRa67n5eQSQxbwWZt723Zcl8xo/HoBx4Bu7SHIS', NULL, NULL, 0, '2026-01-13 07:25:48', '2025-11-24 03:27:02', '2026-01-13 07:25:48'),
(2, 2, 'email_password', NULL, 'pic01@uthm.edu.my', '$2y$12$jTU2GUjAOsrrHJK3ipFsQuY0S3CPQvMREixjLChIhr48NciEVF7xu', NULL, NULL, 0, '2026-01-13 17:12:30', '2025-11-24 03:27:02', '2026-01-13 17:12:30'),
(3, 3, 'email_password', NULL, 'tech01@fkmp.uthm.edu.my', '$2y$10$QvVsJ2feK/Gl2g4YcEItrOgbkKerivv7s0j8hOZxxL5PsEqMgaNu2', NULL, NULL, 0, NULL, '2025-11-24 03:27:03', NULL),
(4, 4, 'email_password', NULL, 'staff01@fkmp.uthm.edu.my', '$2y$10$0ewPJdfWmJgcx/5DKmiQbuH8LpuWAN4xwjGYeDZoIdF7y6ajtE8My', NULL, NULL, 0, NULL, '2025-11-24 03:27:03', NULL),
(5, 5, 'email_password', NULL, 'd1230042@student.uthm.edu.my', '$2y$12$t0zmZ9xu4dfwauYf8bUjauE18Wd/ynMi75ECtJ3CFQkYl5F4lLIh6', NULL, NULL, 0, '2026-01-13 17:03:09', '2025-11-24 03:27:03', '2026-01-13 17:03:09'),
(6, 6, 'email_password', NULL, 'external01@example.com', '$2y$10$Sd53qQdMsO/M1aw73mr1Juagp8.HR97wbLBnVWGfYv3L9tRYtz5vC', NULL, NULL, 0, NULL, '2025-11-24 03:27:03', NULL),
(7, 7, 'email_password', NULL, 'manager01@fkmp.uthm.edu.my', '$2y$12$J8OH6xAGNRoBMt.j0SFe/uDyE12IkpAPg.ToPQ0yMAgfCmKkks3Se', NULL, NULL, 0, '2026-01-13 07:49:11', '2025-12-07 14:16:58', '2026-01-13 07:49:11'),
(8, 10, 'email_password', NULL, 'pic02@uthm.edu.my', '$2y$12$0hmCZ2E/R0WIvzmm.tdHXOa6ld6PpWQQ9agMRVBCMbzBOLOavkpmS', NULL, NULL, 0, '2026-01-13 07:30:17', '2026-01-04 15:01:27', '2026-01-13 07:30:17'),
(9, 11, 'email_password', NULL, 'pic03@uthm.edu.my', '$2y$12$6G2Lfp8uFIXzeKvF9o7X9ea8gQRvvmjY8N9Hjrc53a00Y/BF8z4MW', NULL, NULL, 0, '2026-01-13 07:48:32', '2026-01-09 18:33:04', '2026-01-13 07:48:32');

-- --------------------------------------------------------

--
-- Table structure for table `auth_logins`
--

CREATE TABLE `auth_logins` (
  `id` int UNSIGNED NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_logins`
--

INSERT INTO `auth_logins` (`id`, `ip_address`, `user_agent`, `id_type`, `identifier`, `user_id`, `date`, `success`) VALUES
(1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', NULL, '2025-12-05 17:21:12', 0),
(2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-05 17:21:37', 1),
(3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', NULL, '2025-12-05 18:49:04', 0),
(4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-05 18:49:18', 1),
(5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-06 08:42:50', 1),
(6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-06 09:11:00', 1),
(7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-06 13:36:56', 1),
(8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-06 13:38:04', 1),
(9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-06 17:14:12', 1),
(10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 12:18:25', 1),
(11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-07 12:19:24', 1),
(12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 12:20:29', 1),
(13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-07 12:39:17', 1),
(14, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-07 14:49:46', 1),
(15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-07 14:50:44', 1),
(16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-07 16:49:52', 1),
(17, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-07 17:54:47', 1),
(18, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-07 17:55:05', 1),
(19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 17:57:58', 1),
(20, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-07 18:11:47', 1),
(21, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 18:21:50', 1),
(22, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 19:05:14', 1),
(23, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 19:07:56', 1),
(24, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 19:30:40', 1),
(25, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-07 19:31:56', 1),
(26, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-08 00:00:57', 1),
(27, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-08 00:01:13', 1),
(28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-08 03:16:45', 1),
(29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic.lab01@fkmp.uthm.edu.my', 2, '2025-12-08 18:10:54', 1),
(30, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@fkmp.uthm.edu.my', 2, '2025-12-08 18:51:37', 1),
(31, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-08 19:36:36', 1),
(32, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-09 05:29:03', 1),
(33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-09 05:31:13', 1),
(34, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-09 06:00:54', 1),
(35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-12 01:02:19', 1),
(36, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-16 10:30:34', 1),
(37, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-16 10:35:05', 1),
(38, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-16 10:36:50', 1),
(39, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-16 10:37:45', 1),
(40, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-16 10:42:21', 1),
(41, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-16 10:49:14', 1),
(42, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-16 10:55:55', 1),
(43, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-16 11:06:20', 1),
(44, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-16 11:08:28', 1),
(45, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-16 11:11:00', 1),
(46, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-16 11:15:34', 1),
(47, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', NULL, '2025-12-16 11:15:56', 0),
(48, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-16 11:16:10', 1),
(49, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@uthm.edu.my', NULL, '2025-12-16 11:19:41', 0),
(50, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-16 11:19:52', 1),
(51, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-16 11:58:59', 1),
(52, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-16 12:27:48', 1),
(53, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-16 12:28:34', 1),
(54, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-16 12:31:09', 1),
(55, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-16 12:35:17', 1),
(56, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-17 04:55:50', 1),
(57, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-17 05:26:01', 1),
(58, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-17 05:54:00', 1),
(59, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-17 06:42:42', 1),
(60, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-17 07:42:24', 1),
(61, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-17 07:43:38', 1),
(62, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-21 13:41:02', 1),
(63, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-21 13:41:03', 1),
(64, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-21 13:41:24', 1),
(65, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-22 10:19:28', 1),
(66, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-22 10:22:05', 1),
(67, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-22 10:22:57', 1),
(68, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-22 10:24:32', 1),
(69, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-22 10:25:49', 1),
(70, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-22 10:27:53', 1),
(71, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-22 10:28:41', 1),
(72, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-22 10:29:12', 1),
(73, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-22 10:29:48', 1),
(74, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-22 10:36:02', 1),
(75, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-22 10:36:02', 1),
(76, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-22 10:36:10', 1),
(77, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-29 08:55:52', 1),
(78, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-29 09:00:07', 1),
(79, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-29 09:01:26', 1),
(80, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-29 09:03:02', 1),
(81, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2025-12-29 09:03:23', 1),
(82, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-29 09:04:36', 1),
(83, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-29 09:05:05', 1),
(84, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-29 09:10:23', 1),
(85, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-29 09:12:36', 1),
(86, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-31 03:42:30', 1),
(87, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2025-12-31 03:43:03', 1),
(88, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-31 08:34:43', 1),
(89, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2025-12-31 08:37:58', 1),
(90, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2025-12-31 08:38:39', 1),
(91, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:01:04', 1),
(92, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:03:45', 1),
(93, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:04:14', 1),
(94, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:04:38', 1),
(95, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-02 05:05:06', 1),
(96, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:05:39', 1),
(97, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:07:34', 1),
(98, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:08:03', 1),
(99, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:09:45', 1),
(100, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:10:07', 1),
(101, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:10:30', 1),
(102, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-02 05:11:19', 1),
(103, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:11:44', 1),
(104, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:13:14', 1),
(105, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:15:37', 1),
(106, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:16:24', 1),
(107, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:17:46', 1),
(108, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-02 05:18:21', 1),
(109, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:20:39', 1),
(110, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:22:42', 1),
(111, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:25:07', 1),
(112, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:26:40', 1),
(113, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:27:18', 1),
(114, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-02 05:27:33', 1),
(115, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:29:03', 1),
(116, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:31:02', 1),
(117, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:32:06', 1),
(118, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:32:19', 1),
(119, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:32:29', 1),
(120, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:32:58', 1),
(121, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:33:09', 1),
(122, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:33:18', 1),
(123, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-02 05:34:59', 1),
(124, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-02 05:35:32', 1),
(125, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-02 05:36:18', 1),
(126, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-02 05:37:28', 1),
(127, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-04 09:29:21', 1),
(128, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-04 09:29:33', 1),
(129, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-04 14:42:26', 1),
(130, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic02@uthm.edu.my', 10, '2026-01-04 15:08:21', 1),
(131, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-05 03:27:16', 1),
(132, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-05 03:27:30', 1),
(133, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-05 03:27:45', 1),
(134, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-05 03:28:01', 1),
(135, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-07 18:23:34', 1),
(136, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:16:25', 1),
(137, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-09 18:16:34', 1),
(138, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:33:23', 1),
(139, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'piclab03@uthm.edu.my', NULL, '2026-01-09 18:34:00', 0),
(140, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', NULL, '2026-01-09 18:34:26', 0),
(141, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', NULL, '2026-01-09 18:34:50', 0),
(142, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:34:56', 1),
(143, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:35:58', 1),
(144, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:36:15', 1),
(145, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:36:21', 1),
(146, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', 11, '2026-01-09 18:36:49', 1),
(147, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', NULL, '2026-01-09 18:38:18', 0),
(148, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-09 18:38:27', 1),
(149, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:38:38', 1),
(150, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', 11, '2026-01-09 18:39:16', 1),
(151, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-09 18:39:34', 1),
(152, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic02@uthm.edu.my', 10, '2026-01-09 18:39:43', 1),
(153, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-09 18:40:00', 1),
(154, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-09 18:40:12', 1),
(155, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-09 18:40:23', 1),
(156, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-10 13:40:39', 1),
(157, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic02@uthm.edu.my', 10, '2026-01-10 13:40:57', 1),
(158, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 11:12:50', 1),
(159, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 11:14:01', 1),
(160, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 11:32:21', 1),
(161, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 12:37:52', 1),
(162, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 13:24:34', 1),
(163, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', 11, '2026-01-11 14:01:18', 1),
(164, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-11 14:01:38', 1),
(165, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-11 14:04:38', 1),
(166, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 14:04:58', 1),
(167, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-11 15:58:26', 1),
(168, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-11 15:58:35', 1),
(169, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 08:05:39', 1),
(170, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-12 08:05:58', 1),
(171, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 08:06:10', 1),
(172, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 08:06:25', 1),
(173, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 08:09:25', 1),
(174, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-12 08:09:40', 1),
(175, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 08:09:55', 1),
(176, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 08:10:13', 1),
(177, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 08:12:15', 1),
(178, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 08:27:18', 1),
(179, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 08:34:27', 1),
(180, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 08:43:03', 1),
(181, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 16:05:12', 1),
(182, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 16:05:20', 1),
(183, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', 11, '2026-01-12 16:05:31', 1),
(184, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 16:05:38', 1),
(185, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 16:12:38', 1),
(186, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 16:15:45', 1),
(187, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 16:23:55', 1),
(188, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 16:29:37', 1),
(189, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 16:31:38', 1),
(190, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 16:32:00', 1),
(191, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 16:50:05', 1),
(192, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 17:19:00', 1),
(193, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 17:24:27', 1),
(194, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 17:30:14', 1),
(195, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-12 19:03:18', 1),
(196, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-12 19:03:36', 1),
(197, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 19:04:04', 1),
(198, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-12 19:04:41', 1),
(199, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 19:05:14', 1),
(200, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', NULL, '2026-01-12 19:16:31', 0),
(201, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-12 19:16:38', 1),
(202, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 07:03:59', 1),
(203, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-13 07:04:09', 1),
(204, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 07:25:33', 1),
(205, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'admin@fkmp.uthm.edu.my', 1, '2026-01-13 07:25:48', 1),
(206, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 07:26:07', 1),
(207, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-13 07:29:01', 1),
(208, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic02@uthm.edu.my', 10, '2026-01-13 07:29:18', 1),
(209, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 07:29:37', 1),
(210, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic02@uthm.edu.my', 10, '2026-01-13 07:30:18', 1),
(211, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-13 07:30:37', 1),
(212, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 07:44:51', 1),
(213, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', 11, '2026-01-13 07:47:17', 1),
(214, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 07:48:12', 1),
(215, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic03@uthm.edu.my', 11, '2026-01-13 07:48:32', 1),
(216, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-13 07:48:55', 1),
(217, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'manager01@fkmp.uthm.edu.my', 7, '2026-01-13 07:49:11', 1),
(218, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'd1230042@student.uthm.edu.my', 5, '2026-01-13 17:03:09', 1),
(219, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'email_password', 'pic01@uthm.edu.my', 2, '2026-01-13 17:12:30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `auth_permissions_users`
--

CREATE TABLE `auth_permissions_users` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `permission` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_remember_tokens`
--

CREATE TABLE `auth_remember_tokens` (
  `id` int UNSIGNED NOT NULL,
  `selector` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `hashedValidator` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_token_logins`
--

CREATE TABLE `auth_token_logins` (
  `id` int UNSIGNED NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `lab_id` int UNSIGNED NOT NULL,
  `user_type` enum('UTHM','EXTERNAL') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'UTHM',
  `approval_flow` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `faculty_id` int UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `activity` text COLLATE utf8mb4_general_ci,
  `supervisor_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `supervisor_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `supervisor_phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pdf_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDING',
  `approved_by_pic` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by_manager` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `lab_id`, `user_type`, `approval_flow`, `faculty_id`, `date`, `start_time`, `end_time`, `activity`, `supervisor_name`, `supervisor_email`, `supervisor_phone`, `pdf_path`, `status`, `approved_by_pic`, `approved_by_manager`, `created_at`, `updated_at`) VALUES
(2, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2025-12-08', '08:00:00', '10:00:00', 'Testing', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1765110012_1ba7785b706b824e1401.pdf', 'APPROVED', 1, 1, '2025-12-07 12:20:12', '2025-12-08 19:23:24'),
(3, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2025-12-08', '10:00:00', '12:00:00', 'Testing 2', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1765152597_20a9236458f2cff5e260.pdf', 'APPROVED', 1, 1, '2025-12-08 00:09:57', '2025-12-08 19:23:30'),
(4, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 6, '2025-12-10', '08:00:00', '10:00:00', 'Testing 3', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1765258230_776025c81497cea8148a.pdf', 'APPROVED', 1, 1, '2025-12-09 05:30:30', '2025-12-16 11:03:00'),
(5, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2025-12-17', '08:00:00', '10:00:00', 'Test 4', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1765881249_82cd3787d0d5bac9dd31.pdf', 'APPROVED', 1, 1, '2025-12-16 10:34:09', '2025-12-16 10:36:01'),
(6, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 6, '2025-12-17', '10:00:00', '12:00:00', 'Test 5', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1765883255_717ecc3b41409e6aa4ac.pdf', 'APPROVED', 1, 1, '2025-12-16 11:07:35', '2025-12-16 11:16:13'),
(7, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 6, '2025-12-17', '13:00:00', '15:00:00', 'Test 6', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1765888224_67d9eb637cc23f2e5969.pdf', 'APPROVED', 1, 1, '2025-12-16 12:30:24', '2025-12-16 12:35:51'),
(8, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2025-12-23', '08:00:00', '10:00:00', 'Test 21', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1766398881_a60efa4aee63ecfd0b63.pdf', 'REJECTED', 0, 0, '2025-12-22 10:21:21', '2025-12-22 10:23:58'),
(9, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 6, '2025-12-23', '08:00:00', '10:00:00', 'Test 30', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1766399258_e6686ca43b010ccca701.pdf', 'REJECTED', 1, 0, '2025-12-22 10:27:38', '2025-12-22 10:29:01'),
(10, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2025-12-30', '08:00:00', '10:00:00', 'Test 101', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1766998782_4ca8614eaff5ef60b7d8.pdf', 'APPROVED', 1, 1, '2025-12-29 08:59:42', '2025-12-29 09:01:16'),
(11, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 1, '2025-12-30', '10:00:00', '12:00:00', 'Test 3000', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1766998971_9d5c98279a827bf3d2ba.pdf', 'REJECTED', 1, 0, '2025-12-29 09:02:51', '2025-12-29 09:04:25'),
(12, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-01', '08:00:00', '10:00:00', 'Video PSM Test', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1767170165_94aab519275bdbb90a40.pdf', '', 0, 0, '2025-12-31 08:36:05', '2025-12-31 08:36:32'),
(13, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-02', '08:00:00', '10:00:00', 'Test Video PSM', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1767170246_8e35211c2311e1bc10af.pdf', 'APPROVED', 1, 1, '2025-12-31 08:37:26', '2025-12-31 08:38:23'),
(14, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-05', '08:00:00', '10:00:00', 'Test 10101', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1767330441_881f4d03b071ba3de6db.pdf', 'REJECTED', 0, 0, '2026-01-02 05:07:21', '2026-01-02 05:07:55'),
(15, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-05', '08:00:00', '10:00:00', 'Test 10250', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1767330777_b833335b428feb02ad67.pdf', 'APPROVED', 1, 1, '2026-01-02 05:12:57', '2026-01-02 05:26:13'),
(16, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 9, '2026-01-05', '10:00:00', '12:00:00', 'Test 12052', 'Dr Suhaila binti Mohd Yasin', 'ysuhaila@uthm.edu.my', '01127801583', '/uploads/pdfs/1767331291_9eca214c3cc1349a4db4.pdf', 'REJECTED', 0, 0, '2026-01-02 05:21:31', '2026-01-02 05:26:20'),
(17, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 1, '2026-01-05', '13:00:00', '15:00:00', 'Test booking 8', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1767331483_f603de3caaa78c4ab257.pdf', 'APPROVED', 1, 1, '2026-01-02 05:24:43', '2026-01-02 05:27:10'),
(18, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-05', '10:00:00', '12:00:00', 'SSTEST', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1767331850_5d6c02852dd3e05907ef.pdf', 'REJECTED', 0, 0, '2026-01-02 05:30:50', '2026-01-02 05:31:45'),
(19, 5, 3, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-13', '08:00:00', '10:00:00', 'Testing testing 123', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '0134177565', '/uploads/pdfs/1768289206_f7714931f4572327bb4f.pdf', 'APPROVED', 1, 1, '2026-01-13 07:26:46', '2026-01-13 07:29:06'),
(20, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 6, '2026-01-14', '13:00:00', '15:00:00', 'Testing 123', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768289243_8d9e908c037d32dce0c0.pdf', 'APPROVED', 1, 1, '2026-01-13 07:27:23', '2026-01-13 07:30:40'),
(21, 5, 3, 'UTHM', 'FACULTY_APPROVAL', 8, '2026-01-16', '10:00:00', '12:00:00', 'Testttt', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768289272_0a3cf967b98072a6b3ce.pdf', 'APPROVED', 1, 1, '2026-01-13 07:27:52', '2026-01-13 07:30:45'),
(22, 5, 2, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-13', '13:00:00', '15:00:00', 'Testtt', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768289305_65b7f2f0b8fb69973f79.pdf', 'APPROVED', 1, 1, '2026-01-13 07:28:25', '2026-01-13 07:29:22'),
(23, 5, 2, 'UTHM', 'FACULTY_APPROVAL', 5, '2026-01-15', '08:00:00', '10:00:00', 'Tessssrt', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768289330_db4c959f1b3fad8e4607.pdf', 'APPROVED', 1, 1, '2026-01-13 07:28:50', '2026-01-13 07:30:42'),
(24, 5, 2, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-16', '13:00:00', '15:00:00', 'Testt', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768289408_c1fff230ca9a5188aaa3.pdf', 'APPROVED', 1, 1, '2026-01-13 07:30:08', '2026-01-13 07:30:22'),
(25, 5, 1, 'UTHM', 'FKMP_APPROVAL', 3, '2026-01-14', '08:00:00', '10:00:00', 'bbb', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768290322_3d7649b56b23cea56b70.pdf', 'APPROVED', 1, 1, '2026-01-13 07:45:22', '2026-01-13 07:48:41'),
(26, 5, 1, 'UTHM', 'FACULTY_APPROVAL', 6, '2026-01-13', '10:00:00', '12:00:00', 'lll', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768290354_773aabf6a1421a6457d2.pdf', 'APPROVED', 1, 1, '2026-01-13 07:45:54', '2026-01-13 07:48:59'),
(27, 5, 1, 'UTHM', 'FACULTY_APPROVAL', 5, '2026-01-13', '15:00:00', '17:00:00', 'lllll', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768290392_47978a32961912ede670.pdf', 'APPROVED', 1, 1, '2026-01-13 07:46:32', '2026-01-13 07:49:01'),
(28, 5, 1, 'UTHM', 'FACULTY_APPROVAL', 2, '2026-01-16', '13:00:00', '15:00:00', 'ugiugi', 'Dr Suhaila binti Mohd Yasin', 'graaj15@gmail.com', '01127801583', '/uploads/pdfs/1768290426_7c0986f21000eb0ea1de.pdf', 'APPROVED', 1, 1, '2026-01-13 07:47:06', '2026-01-13 07:49:03');

-- --------------------------------------------------------

--
-- Table structure for table `booking_applicants`
--

CREATE TABLE `booking_applicants` (
  `id` int UNSIGNED NOT NULL,
  `booking_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `matric_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `faculty` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_assets`
--

CREATE TABLE `booking_assets` (
  `id` int UNSIGNED NOT NULL,
  `booking_id` int UNSIGNED NOT NULL,
  `asset_id` int UNSIGNED NOT NULL,
  `quantity_used` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_assets`
--

INSERT INTO `booking_assets` (`id`, `booking_id`, `asset_id`, `quantity_used`) VALUES
(2, 2, 8, 1),
(3, 3, 8, 1),
(4, 4, 8, 1),
(5, 5, 8, 1),
(6, 6, 8, 1),
(7, 7, 8, 1),
(8, 8, 8, 1),
(9, 9, 8, 1),
(10, 10, 8, 1),
(11, 11, 8, 1),
(12, 12, 8, 1),
(13, 13, 8, 1),
(14, 14, 8, 1),
(15, 15, 8, 1),
(16, 16, 8, 1),
(17, 17, 8, 1),
(18, 18, 8, 1),
(19, 19, 8, 1),
(20, 20, 8, 1),
(21, 21, 8, 1),
(22, 22, 7, 1),
(23, 23, 7, 1),
(24, 24, 7, 1),
(25, 25, 5, 1),
(26, 25, 6, 1),
(27, 26, 5, 2),
(28, 27, 5, 1),
(29, 27, 6, 1),
(30, 28, 5, 1),
(31, 28, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `name_bm` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_fkmp` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `code`, `name_bm`, `name_en`, `is_fkmp`) VALUES
(1, 'FKAAB', 'Fakulti Kejuruteraan Awam dan Alam Bina', 'Faculty of Civil Engineering and Built Environment', 0),
(2, 'FKEE', 'Fakulti Kejuruteraan Elektrik dan Elektronik', 'Faculty of Electrical and Electronic Engineering', 0),
(3, 'FKMP', 'Fakulti Kejuruteraan Mekanikal dan Pembuatan', 'Faculty of Mechanical and Manufacturing Engineering', 1),
(4, 'FPTP', 'Fakulti Pengurusan Teknologi dan Perniagaan', 'Faculty of Technology Management and Business', 0),
(5, 'FPTV', 'Fakulti Pendidikan Teknikal dan Vokasional', 'Faculty of Technical and Vocational Education', 0),
(6, 'FSKTM', 'Fakulti Sains Komputer dan Teknologi Maklumat', 'Faculty of Computer Science and Information Technology', 0),
(7, 'FAST', 'Fakulti Sains Gunaan dan Teknologi', 'Faculty of Applied Sciences and Technology', 0),
(8, 'FTK', 'Fakulti Teknologi Kejuruteraan', 'Faculty of Engineering Technology', 0),
(9, 'PPD', 'Pusat Pengajian Diploma', 'Centre for Diploma Studies', 0);

-- --------------------------------------------------------

--
-- Table structure for table `laboratories`
--

CREATE TABLE `laboratories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `room` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pic_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `pic_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pic_phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pic_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratories`
--

INSERT INTO `laboratories` (`id`, `name`, `room`, `pic_name`, `pic_email`, `pic_phone`, `image`, `pic_image`) VALUES
(1, 'Makmal Umum Bahasa Inggeris 1', 'B-213', 'Dr. Ahmad Hakimi', 'pic03@uthm.edu.my', '07-1234567', 'images/labs/1768136197_93c10411e1ff98fb858e.png', 'images/pic/1768137724_605b5f757fe5f421ddbf.png'),
(2, 'Makmal Reka Bentuk Mekanikal', 'A1-102', 'Ts. Nur Farah', 'pic02@uthm.edu.my', '07-9876543', 'images/labs/1768136188_a87586869c11d741d2e9.png', 'images/pic/1768137715_af50119f294b613e00a4.png'),
(3, 'Makmal Pneumatik & Hidraulik', 'C3-301', 'Ir. Mohamad Fazli', 'pic01@uthm.edu.my', '07-1112223', 'images/labs/1768136662_e491893a01bd7b75a68c.png', 'images/pic/1768137696_1beabb982adbda3613aa.png');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2020-12-28-223112', 'CodeIgniter\\Shield\\Database\\Migrations\\CreateAuthTables', 'default', 'CodeIgniter\\Shield', 1763954445, 1),
(2, '2021-07-04-041948', 'CodeIgniter\\Settings\\Database\\Migrations\\CreateSettingsTable', 'default', 'CodeIgniter\\Settings', 1763954446, 1),
(3, '2021-11-14-143905', 'CodeIgniter\\Settings\\Database\\Migrations\\AddContextColumn', 'default', 'CodeIgniter\\Settings', 1763954446, 1),
(4, '2025-11-16-101253', 'App\\Database\\Migrations\\CreateLaboratoriesTable', 'default', 'App', 1763954446, 1),
(5, '2025-11-16-101333', 'App\\Database\\Migrations\\CreateAssetsTable', 'default', 'App', 1763954446, 1),
(6, '2025-11-16-112555', 'App\\Database\\Migrations\\CreateBookingsTable', 'default', 'App', 1763954446, 1),
(7, '2025-11-16-112626', 'App\\Database\\Migrations\\CreateBookingApplicantsTable', 'default', 'App', 1763954446, 1),
(8, '2025-11-16-112644', 'App\\Database\\Migrations\\CreateBookingAssetsTable', 'default', 'App', 1763954446, 1),
(9, '2025-11-16-123751', 'App\\Database\\Migrations\\AddUserIdToBookings', 'default', 'App', 1763954446, 1),
(11, '2025-11-20-105649', 'App\\Database\\Migrations\\CreateFacultiesTable', 'default', 'App', 1763954618, 2),
(12, '2025-11-29-140646', 'App\\Database\\Migrations\\AddFacultyAndApprovalFlowToBookings', 'default', 'App', 1764425500, 3),
(13, '2025-12-05-105712', 'App\\Database\\Migrations\\AddApprovalStagesToBookings', 'default', 'App', 1764932255, 4),
(14, '2026-01-12-000001', 'App\\Database\\Migrations\\CreatePredictionsTable', 'default', 'App', 1768287165, 5),
(15, '2026-01-13-120000', 'App\\Database\\Migrations\\AddProfileFieldsToUsers', 'default', 'App', 1768323465, 6);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  `type` varchar(31) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'string',
  `context` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `class`, `key`, `value`, `type`, `context`, `created_at`, `updated_at`) VALUES
(1, 'system', 'lab_manager_email', 'manager01@fkmp.uthm.edu.my\r\n', 'string', NULL, '2025-12-05 18:41:20', '2025-12-05 18:41:20'),
(2, 'system', 'deputy_dean_email', 'deputydean@uthm.edu.my', 'string', NULL, '2025-12-05 18:41:28', '2025-12-05 18:41:28'),
(3, 'system', 'lab_assistant_email', 'assistant@uthm.edu.my', 'string', NULL, '2025-12-05 18:41:37', '2025-12-05 18:41:37'),
(4, 'system', 'fkmp_faculty_id', '3', 'integer', NULL, '2025-12-05 18:41:44', '2025-12-05 18:41:44'),
(5, 'system', 'booking_slots', '[{\"start\":\"08:00\",\"end\":\"10:00\",\"label\":\"08:00 - 10:00\"},{\"start\":\"10:00\",\"end\":\"12:00\",\"label\":\"10:00 - 12:00\"},{\"start\":\"13:00\",\"end\":\"15:00\",\"label\":\"13:00 - 15:00\"},{\"start\":\"15:00\",\"end\":\"17:00\",\"label\":\"15:00 - 17:00\"}]', 'string', NULL, '2026-01-12 17:26:15', '2026-01-12 17:26:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_message` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `full_name` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `faculty_id` int UNSIGNED DEFAULT NULL,
  `profile_photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `status`, `status_message`, `active`, `last_active`, `created_at`, `updated_at`, `deleted_at`, `full_name`, `phone`, `faculty_id`, `profile_photo`) VALUES
(1, 'admin', NULL, NULL, 1, '2026-01-13 07:25:49', '2025-11-24 03:27:02', '2025-11-24 03:27:02', NULL, NULL, NULL, NULL, NULL),
(2, 'piclab01', NULL, NULL, 1, '2026-01-13 17:23:56', '2025-11-24 03:27:02', '2025-11-24 03:27:02', NULL, NULL, NULL, NULL, NULL),
(3, 'tech01', NULL, NULL, 1, NULL, '2025-11-24 03:27:03', '2025-11-24 03:27:03', NULL, NULL, NULL, NULL, NULL),
(4, 'staff01', NULL, NULL, 1, NULL, '2025-11-24 03:27:03', '2025-11-24 03:27:03', NULL, NULL, NULL, NULL, NULL),
(5, 'd1230042', NULL, NULL, 1, '2026-01-13 17:03:14', '2025-11-24 03:27:03', '2025-11-24 03:27:03', NULL, NULL, NULL, NULL, NULL),
(6, 'external01', NULL, NULL, 1, NULL, '2025-11-24 03:27:03', '2025-11-24 03:27:03', NULL, NULL, NULL, NULL, NULL),
(7, 'manager01', NULL, NULL, 1, '2026-01-13 08:00:32', '2025-12-07 14:16:58', '2025-12-07 14:16:58', NULL, NULL, NULL, NULL, NULL),
(10, 'piclab02', NULL, NULL, 1, '2026-01-13 07:30:22', '2026-01-04 15:01:27', '2026-01-04 15:01:27', NULL, NULL, NULL, NULL, NULL),
(11, 'piclab03', NULL, NULL, 1, '2026-01-13 07:48:45', '2026-01-09 18:33:04', '2026-01-09 18:33:04', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assets_lab_id_foreign` (`lab_id`);

--
-- Indexes for table `auth_groups_users`
--
ALTER TABLE `auth_groups_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_groups_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `auth_identities`
--
ALTER TABLE `auth_identities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_secret` (`type`,`secret`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_logins`
--
ALTER TABLE `auth_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type_identifier` (`id_type`,`identifier`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_permissions_users`
--
ALTER TABLE `auth_permissions_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_permissions_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `auth_remember_tokens`
--
ALTER TABLE `auth_remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `auth_remember_tokens_user_id_foreign` (`user_id`);

--
-- Indexes for table `auth_token_logins`
--
ALTER TABLE `auth_token_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type_identifier` (`id_type`,`identifier`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_lab_id_foreign` (`lab_id`),
  ADD KEY `bookings_faculty_id_fk` (`faculty_id`);

--
-- Indexes for table `booking_applicants`
--
ALTER TABLE `booking_applicants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_applicants_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `booking_assets`
--
ALTER TABLE `booking_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_assets_booking_id_foreign` (`booking_id`),
  ADD KEY `booking_assets_asset_id_foreign` (`asset_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `laboratories`
--
ALTER TABLE `laboratories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `auth_groups_users`
--
ALTER TABLE `auth_groups_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `auth_identities`
--
ALTER TABLE `auth_identities`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `auth_logins`
--
ALTER TABLE `auth_logins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `auth_permissions_users`
--
ALTER TABLE `auth_permissions_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_remember_tokens`
--
ALTER TABLE `auth_remember_tokens`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `auth_token_logins`
--
ALTER TABLE `auth_token_logins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `booking_applicants`
--
ALTER TABLE `booking_applicants`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_assets`
--
ALTER TABLE `booking_assets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `laboratories`
--
ALTER TABLE `laboratories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_lab_id_foreign` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `auth_groups_users`
--
ALTER TABLE `auth_groups_users`
  ADD CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_identities`
--
ALTER TABLE `auth_identities`
  ADD CONSTRAINT `auth_identities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_permissions_users`
--
ALTER TABLE `auth_permissions_users`
  ADD CONSTRAINT `auth_permissions_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_remember_tokens`
--
ALTER TABLE `auth_remember_tokens`
  ADD CONSTRAINT `auth_remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_faculty_id_fk` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_lab_id_foreign` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_applicants`
--
ALTER TABLE `booking_applicants`
  ADD CONSTRAINT `booking_applicants_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_assets`
--
ALTER TABLE `booking_assets`
  ADD CONSTRAINT `booking_assets_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_assets_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
