-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2025 at 04:25 PM
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
-- Database: `game_x_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('organizer','player') DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `team` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `account_status` enum('active','suspended','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notify_updates` tinyint(1) NOT NULL DEFAULT 1,
  `notify_tournaments` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `username`, `email`, `password`, `role`, `fullname`, `team`, `age`, `is_admin`, `account_status`, `created_at`, `notify_updates`, `notify_tournaments`) VALUES
(2, 'PLAYER1', 'player_1@gmail.com', '$2y$10$1pkPU0xom1dzO.Xqhb9JmOJg7yBZdstZqDNRrmZjT5HZaRFtAYFem', 'player', 'Juan Dela Cruz', NULL, 18, 0, 'active', '2025-10-18 16:10:54', 1, 1),
(3, 'Staff_1', 'staff1@gmail.com', '$2y$10$xkUAwar4GZsGtOzNKvJ4BOoVC/th8QxW1vZeG6smfOxNi3BifsFkC', 'organizer', '', NULL, 0, 0, 'active', '2025-10-24 14:32:24', 1, 1),
(6, 'admin', 'admin@gamex.com', '$2y$10$qG915KrvV/OMBllGHuMexegLbztwarbu0tF8sbARvoSHxQDejI/Fq', '', NULL, NULL, NULL, 1, 'active', '2025-10-31 05:17:40', 1, 1),
(7, 'ajmayran', 'aj@gmail.com', '$2y$10$/PjND3f2Pd.POi4uhjCrceuHtAUXtol4MiCCIBAaRqjREstaiZhXW', 'player', 'AJ', NULL, 21, 0, 'active', '2025-10-31 05:52:50', 1, 1),
(8, 'player2', 'player_2@gmail.com', '$2y$10$r6YdYZ7B0EUhohLjiaI5ruzMw9MNc2fOwCR3uhyKV9lFocgWteEWS', 'player', 'peter dela cruz', NULL, 18, 0, 'active', '2025-11-25 13:53:25', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `announcement_logs`
--

CREATE TABLE `announcement_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `account_id`, `action`, `details`, `created_at`) VALUES
(1, 6, 'Logout ()', 'N/A', '2025-11-04 15:57:27'),
(2, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 15:57:46'),
(3, 6, 'Logout ()', 'N/A', '2025-11-04 15:57:48'),
(4, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 15:58:10'),
(5, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:04:41'),
(6, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:04:42'),
(7, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:04:42'),
(8, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:23'),
(9, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:23'),
(10, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:23'),
(11, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:23'),
(12, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:24'),
(13, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:24'),
(14, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:24'),
(15, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:08:24'),
(16, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:10:48'),
(17, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:10:48'),
(18, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:10:48'),
(19, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:10:48'),
(20, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:10:49'),
(21, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:10:49'),
(22, 6, 'Logout ()', 'N/A', '2025-11-04 16:14:07'),
(23, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:19:49'),
(24, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:24:14'),
(25, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:24:52'),
(26, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:24:53'),
(27, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:24:53'),
(28, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-04 16:27:33'),
(29, NULL, 'Unauthorized access attempt (no session)', 'N/A', '2025-11-04 16:30:12'),
(30, NULL, 'Unauthorized access attempt (no session)', 'N/A', '2025-11-04 16:30:25'),
(31, NULL, 'Unauthorized access attempt (no session)', 'N/A', '2025-11-04 16:30:29'),
(32, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-05 14:31:03'),
(33, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 13:41:57'),
(34, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 14:38:51'),
(35, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 14:47:29'),
(36, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 14:48:33'),
(37, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:07:08'),
(38, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:09:21'),
(39, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 15:12:09'),
(40, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:14:29'),
(41, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:18:04'),
(42, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 15:18:06'),
(43, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:25:56'),
(44, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:25:57'),
(45, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 15:26:03'),
(46, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 15:28:57'),
(47, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:35:55'),
(48, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:37:02'),
(49, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:48:47'),
(50, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:52:23'),
(51, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 15:52:50'),
(52, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 15:53:04'),
(53, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 15:56:18'),
(54, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:09:10'),
(55, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:09:11'),
(56, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:09:11'),
(57, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:09:12'),
(58, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 16:13:05'),
(59, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 16:13:38'),
(60, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 16:15:55'),
(61, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:21:24'),
(62, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:22:53'),
(63, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:22:54'),
(64, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:22:54'),
(65, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:22:54'),
(66, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:47:25'),
(67, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-06 16:50:30'),
(68, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 16:50:34'),
(69, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-06 16:50:38'),
(70, 2, 'Logout (player)', 'N/A', '2025-11-07 15:00:07'),
(71, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-07 15:13:16'),
(72, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-07 15:13:25'),
(73, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-07 16:11:38'),
(74, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-07 16:11:58'),
(75, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-07 16:11:59'),
(76, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-07 16:12:03'),
(77, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-07 16:12:08'),
(78, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-07 16:12:16'),
(79, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-07 16:28:30'),
(80, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-07 16:28:31'),
(81, 6, 'Logout ()', 'N/A', '2025-11-07 16:28:33'),
(82, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-07 16:28:48'),
(83, 6, 'Logout ()', 'N/A', '2025-11-07 16:28:50'),
(84, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 15:33:31'),
(85, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 15:33:42'),
(86, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 15:34:03'),
(87, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 15:34:09'),
(88, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 15:34:12'),
(89, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 15:34:15'),
(90, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 15:34:19'),
(91, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 15:47:44'),
(92, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 15:50:27'),
(93, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 15:51:19'),
(94, 6, 'Access denied: tried to access player area', 'N/A', '2025-11-08 15:54:39'),
(95, 6, 'Access denied: tried to access player area', 'N/A', '2025-11-08 15:54:42'),
(96, 6, 'Logout ()', 'N/A', '2025-11-08 15:54:48'),
(97, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:00:49'),
(98, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:01:00'),
(99, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:02:49'),
(100, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 16:02:50'),
(101, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 16:02:53'),
(102, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:08:49'),
(103, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 16:08:52'),
(104, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 16:09:02'),
(105, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 16:09:04'),
(106, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:09:06'),
(107, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-08 16:10:01'),
(108, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:10:04'),
(109, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-08 16:10:33'),
(110, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-09 13:40:06'),
(111, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-09 13:40:30'),
(112, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-09 13:40:46'),
(113, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-09 13:40:48'),
(114, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-09 13:40:51'),
(115, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-09 13:40:52'),
(116, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-09 13:41:29'),
(117, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-09 13:41:29'),
(118, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 13:52:32'),
(119, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:53:01'),
(120, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:53:01'),
(121, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:53:03'),
(122, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:53:05'),
(123, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 13:53:08'),
(124, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:53:13'),
(125, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 13:53:29'),
(126, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:53:31'),
(127, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:54:26'),
(128, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:54:28'),
(129, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:54:32'),
(130, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:54:32'),
(131, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 13:56:01'),
(132, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 13:56:03'),
(133, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 13:56:04'),
(134, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 13:57:33'),
(135, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:00:51'),
(136, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:00:53'),
(137, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:10:51'),
(138, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:10:54'),
(139, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:11:00'),
(140, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:11:07'),
(141, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:11:07'),
(142, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:13:50'),
(143, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:13:50'),
(144, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 14:13:50'),
(145, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:21:09'),
(146, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:21:21'),
(147, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:21:24'),
(148, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 14:21:44'),
(149, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:07:02'),
(150, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 15:07:06'),
(151, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:07:20'),
(152, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:11:41'),
(153, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:27:21'),
(154, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:32:07'),
(155, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:35:11'),
(156, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:35:24'),
(157, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:36:46'),
(158, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:36:49'),
(159, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:36:49'),
(160, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:36:49'),
(161, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:36:50'),
(162, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:37:16'),
(163, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:37:17'),
(164, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:37:17'),
(165, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:37:41'),
(166, 6, 'Logout ()', 'N/A', '2025-11-09 15:38:07'),
(167, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:38:23'),
(168, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:39:08'),
(169, 6, 'Access denied: tried to access player area', 'N/A', '2025-11-09 15:39:42'),
(170, 2, 'Logout (player)', 'N/A', '2025-11-09 15:46:45'),
(171, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 15:46:54'),
(172, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-09 16:28:35'),
(173, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 16:34:50'),
(174, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-09 16:34:51'),
(175, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 06:53:43'),
(176, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 06:54:38'),
(177, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 06:55:19'),
(178, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-10 06:55:44'),
(179, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 06:55:49'),
(180, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 15:21:09'),
(181, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 15:21:51'),
(182, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 15:22:03'),
(183, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 15:39:08'),
(184, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 15:53:06'),
(185, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:03:17'),
(186, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:05:49'),
(187, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:13:32'),
(188, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:14:30'),
(189, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:14:30'),
(190, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-10 16:17:07'),
(191, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:18:59'),
(192, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-10 16:19:03'),
(193, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-10 16:19:54'),
(194, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:20:43'),
(195, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:21:18'),
(196, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:21:20'),
(197, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:21:35'),
(198, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:21:55'),
(199, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-10 16:22:08'),
(200, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:29:25'),
(201, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:29:25'),
(202, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:29:26'),
(203, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:29:26'),
(204, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:34:22'),
(205, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:34:47'),
(206, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:35:03'),
(207, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:35:13'),
(208, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:36:09'),
(209, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-10 16:36:17'),
(210, 6, 'Logout ()', 'N/A', '2025-11-10 16:46:34'),
(211, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 15:16:25'),
(212, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:18:02'),
(213, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:18:04'),
(214, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:25'),
(215, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:26'),
(216, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:28'),
(217, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:28'),
(218, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:28'),
(219, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:29'),
(220, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:29'),
(221, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:28:29'),
(222, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 15:28:31'),
(223, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 15:33:34'),
(224, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 15:34:08'),
(225, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 15:34:14'),
(226, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 15:45:27'),
(227, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 16:02:41'),
(228, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 16:02:55'),
(229, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 16:05:18'),
(230, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-11 16:05:41'),
(231, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-11 16:06:03'),
(232, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-11 16:06:09'),
(233, 6, 'Access denied: tried to access organizer area', 'N/A', '2025-11-11 16:06:16'),
(234, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 16:07:43'),
(235, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 16:07:58'),
(236, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 16:08:46'),
(237, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 16:12:44'),
(238, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 16:12:52'),
(239, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-11 17:04:52'),
(240, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-11 17:05:01'),
(241, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-12 13:59:45'),
(242, 3, 'Logout (organizer)', 'N/A', '2025-11-12 14:34:58'),
(243, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-12 14:35:10'),
(244, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-12 15:27:27'),
(245, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-12 15:27:39'),
(246, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-12 15:32:14'),
(247, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 13:47:06'),
(248, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 13:50:46'),
(249, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-13 13:50:51'),
(250, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 13:50:54'),
(251, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-13 14:02:06'),
(252, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-13 14:02:55'),
(253, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-13 14:03:00'),
(254, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 14:03:18'),
(255, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-13 14:03:20'),
(256, 6, 'Logout ()', 'N/A', '2025-11-13 14:21:19'),
(257, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 15:05:57'),
(258, 3, 'Logout (organizer)', 'N/A', '2025-11-13 15:59:53'),
(259, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 16:00:08'),
(260, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-13 16:36:10'),
(261, 2, 'Logout (player)', 'N/A', '2025-11-16 16:12:28'),
(262, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-16 16:12:56'),
(263, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:13:39'),
(264, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:13:47'),
(265, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:14:49'),
(266, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:48'),
(267, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:49'),
(268, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:49'),
(269, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:49'),
(270, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:50'),
(271, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:50'),
(272, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:50'),
(273, 3, 'View Tournaments', 'Organizer viewed tournament list', '2025-11-16 16:34:50'),
(274, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 16:53:58'),
(275, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:04:54'),
(276, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:04:55'),
(277, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:04:55'),
(278, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:04:55'),
(279, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:04:56'),
(280, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:09:02'),
(281, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:09:02'),
(282, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:09:02'),
(283, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:09:03'),
(284, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:23:25'),
(285, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:23:25'),
(286, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:23:25'),
(287, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:23:26'),
(288, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-16 17:23:58'),
(289, 2, 'Logout (player)', 'N/A', '2025-11-17 05:38:11'),
(290, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 05:38:35'),
(291, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 05:54:05'),
(292, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 05:56:52'),
(293, 6, 'Logout ()', 'N/A', '2025-11-17 05:56:55'),
(294, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-17 05:57:07'),
(295, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-17 05:57:19'),
(296, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-17 05:58:50'),
(297, 3, 'View Tournaments', 'Organizer viewed their created tournaments', '2025-11-17 06:04:38'),
(298, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 14:46:38'),
(299, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 14:46:51'),
(300, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:06:16'),
(301, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:06:36'),
(302, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:25:58'),
(303, 6, 'Logout ()', 'N/A', '2025-11-17 16:36:58'),
(304, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:37:21'),
(305, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:52:00'),
(306, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:53:19'),
(307, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:55:41'),
(308, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:55:42'),
(309, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:55:49'),
(310, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:55:52'),
(311, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-17 16:55:56'),
(312, 2, 'Logout (player)', 'N/A', '2025-11-18 16:21:40'),
(313, 2, 'Logout (player)', 'N/A', '2025-11-22 01:47:10'),
(314, NULL, 'Unauthorized access attempt (no session)', 'N/A', '2025-11-22 01:47:19'),
(315, NULL, 'Unauthorized access attempt (no session)', 'N/A', '2025-11-22 01:47:23'),
(316, NULL, 'Unauthorized access attempt (no session)', 'N/A', '2025-11-22 01:47:25'),
(317, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:37:59'),
(318, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:49:46'),
(319, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:51:43'),
(320, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:51:43'),
(321, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:55:59'),
(322, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:56:00'),
(323, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 14:56:00'),
(324, 3, 'Logout (organizer)', 'N/A', '2025-11-23 15:00:49'),
(325, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-23 15:00:59'),
(326, 6, 'Logout ()', 'N/A', '2025-11-23 15:02:07'),
(327, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-23 15:02:30'),
(328, 6, 'Logout ()', 'N/A', '2025-11-23 15:03:29'),
(329, 2, 'Logout (player)', 'N/A', '2025-11-23 15:03:43'),
(330, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-23 15:09:45'),
(331, 6, 'Logout ()', 'N/A', '2025-11-23 15:09:58'),
(332, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-11-23 15:10:29'),
(333, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-24 13:49:49'),
(334, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-24 14:29:15'),
(335, 6, 'Logout ()', 'N/A', '2025-11-24 14:29:19'),
(336, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-24 14:29:27'),
(337, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-11-24 14:30:25'),
(338, 2, 'Logout (player)', 'N/A', '2025-11-25 13:50:34'),
(339, 8, 'Registered new account', 'Role: player', '2025-11-25 13:53:25'),
(340, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-12-13 13:40:25'),
(341, 6, 'View Dashboard', 'Admin accessed the dashboard', '2025-12-13 13:46:47'),
(342, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-12-13 13:57:17'),
(343, 3, 'Logout (organizer)', 'N/A', '2025-12-13 14:06:05'),
(344, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-12-13 14:06:22'),
(345, 3, 'Logout (organizer)', 'N/A', '2025-12-13 14:37:14'),
(346, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-12-13 14:37:23'),
(347, 3, 'Logout (organizer)', 'N/A', '2025-12-13 14:46:57'),
(348, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-12-13 14:47:41'),
(349, 3, 'Logout (organizer)', 'N/A', '2025-12-13 14:58:35'),
(350, 2, 'Logout (player)', 'N/A', '2025-12-13 15:00:16'),
(351, 3, 'View Dashboard', 'Organizer accessed the dashboard', '2025-12-13 15:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `game_icon` varchar(255) DEFAULT NULL,
  `game_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `game_name`, `game_icon`, `game_image`, `is_active`) VALUES
(1, 'Dota 2', 'fas fa-gamepad', 'assets/images/games/game_69044d6c785c5.png', 1),
(2, 'Valorant', 'fas fa-gamepad', 'assets/images/games/game_69044d7f01c4d.png', 1),
(3, 'Mobile Legend', 'fas fa-gamepad', 'assets/images/games/game_69044d8ec05d2.png', 1),
(4, 'League Of Legends', 'fas fa-gamepad', 'assets/images/games/game_69044da08384c.jpg', 1),
(5, 'Call Of Duty Mobile', 'fas fa-gamepad', 'assets/images/games/game_69044dad95758.png', 1),
(6, 'xyz', 'XYZ', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `name`, `email`, `message`, `reply_message`, `replied_at`, `created_at`) VALUES
(1, 2, NULL, 'Juan Dela Cruz', 'player_1@gmail.com', 'qwertyuiop[asdfghjkl;\'', NULL, NULL, '2025-12-13 13:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('system','tournament','message','warning','account','team','registration') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizer_profiles`
--

CREATE TABLE `organizer_profiles` (
  `organizer_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `website` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizer_profiles`
--

INSERT INTO `organizer_profiles` (`organizer_id`, `account_id`, `organization`, `contact_no`, `website`) VALUES
(1, 3, 'My Organization', '09123456789', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `player_profiles`
--

CREATE TABLE `player_profiles` (
  `profile_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `gamer_tag` varchar(255) NOT NULL,
  `age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `tournament_id`, `team_id`, `account_id`, `status`, `registered_at`) VALUES
(1, 1, 2, 2, 'pending', '2025-11-16 22:17:44'),
(2, 24, 2, 2, 'pending', '2025-12-13 14:59:16');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','Reviewed','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(255) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `introduction` text DEFAULT NULL,
  `team_logo` varchar(255) DEFAULT NULL,
  `max_members` int(11) NOT NULL DEFAULT 5,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `team_name`, `game_name`, `introduction`, `team_logo`, `max_members`, `created_by`, `created_at`) VALUES
(1, 'CCS', 'Valorant', 'CCS qpals', 'assets/images/teams/team_6904621a35fd2.png', 5, 7, '2025-10-31 07:15:38'),
(2, 'DARK MINISTRY', 'Valorant', 'THE TEAM MUST BE IN BALANCE', 'assets/images/teams/team_690769ee7abf0.png', 5, 2, '2025-11-02 14:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `team_invitations`
--

CREATE TABLE `team_invitations` (
  `invitation_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `invited_player` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `team_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `role` enum('leader','member','','') NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`team_id`, `account_id`, `role`, `joined_at`) VALUES
(1, 7, 'leader', '2025-10-31 07:15:38'),
(2, 2, 'leader', '2025-11-02 14:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `tournament_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `reg_start_date` datetime DEFAULT NULL,
  `reg_end_date` datetime DEFAULT NULL,
  `max_teams` int(11) NOT NULL,
  `status` enum('open','completed','cancelled') DEFAULT 'open',
  `game` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`tournament_id`, `organizer_id`, `title`, `description`, `start_date`, `end_date`, `reg_start_date`, `reg_end_date`, `max_teams`, `status`, `game`) VALUES
(1, 1, 'AUTUMN CUP 2025', 'A friendly 5v5 online gaming tournament for amateur players.', '2025-12-01 00:00:00', '2025-12-05 23:59:59', NULL, NULL, 12, 'open', ''),
(24, 1, 'WINTER TOURNAMENT', '', '2025-12-15 00:00:00', '2025-12-16 23:59:59', '2025-12-13 00:00:00', '2025-12-14 23:59:59', 12, 'open', 'VALORANT');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcement_logs`
--
ALTER TABLE `announcement_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD UNIQUE KEY `game_name` (`game_name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  ADD PRIMARY KEY (`organizer_id`),
  ADD UNIQUE KEY `account_id` (`account_id`);

--
-- Indexes for table `player_profiles`
--
ALTER TABLE `player_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `account_id` (`account_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `fk_reg_team` (`team_id`),
  ADD KEY `fk_reg_tournament` (`tournament_id`),
  ADD KEY `fk_reg_account` (`account_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `fk_team_creator` (`created_by`);

--
-- Indexes for table `team_invitations`
--
ALTER TABLE `team_invitations`
  ADD PRIMARY KEY (`invitation_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `invited_by` (`invited_by`),
  ADD KEY `invited_player` (`invited_player`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`team_id`,`account_id`),
  ADD KEY `fk_team_member_account` (`account_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`tournament_id`),
  ADD KEY `fk_tournament_organizer` (`organizer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `announcement_logs`
--
ALTER TABLE `announcement_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=352;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  MODIFY `organizer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `player_profiles`
--
ALTER TABLE `player_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_invitations`
--
ALTER TABLE `team_invitations`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement_logs`
--
ALTER TABLE `announcement_logs`
  ADD CONSTRAINT `announcement_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `accounts` (`account_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`);

--
-- Constraints for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  ADD CONSTRAINT `fk_org_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organizer_profiles_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `player_profiles`
--
ALTER TABLE `player_profiles`
  ADD CONSTRAINT `fk_player_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_profiles_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `fk_reg_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reg_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reg_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`),
  ADD CONSTRAINT `registrations_ibfk_3` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `accounts` (`account_id`);

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_team_creator` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`account_id`);

--
-- Constraints for table `team_invitations`
--
ALTER TABLE `team_invitations`
  ADD CONSTRAINT `team_invitations_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_invitations_ibfk_2` FOREIGN KEY (`invited_by`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_invitations_ibfk_3` FOREIGN KEY (`invited_player`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_team_member_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_team_member_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `fk_tournament_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `organizer_profiles` (`organizer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tournament_organizer_new` FOREIGN KEY (`organizer_id`) REFERENCES `organizer_profiles` (`organizer_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
