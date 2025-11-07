-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 05:16 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `username`, `email`, `password`, `role`, `fullname`, `team`, `age`, `is_admin`, `account_status`, `created_at`) VALUES
(2, 'PLAYER1', 'player_1@gmail.com', '$2y$10$1pkPU0xom1dzO.Xqhb9JmOJg7yBZdstZqDNRrmZjT5HZaRFtAYFem', 'player', 'Juan Dela Cruz', NULL, 18, 0, 'active', '2025-10-18 16:10:54'),
(3, 'Staff_1', 'staff1@gmail.com', '$2y$10$xkUAwar4GZsGtOzNKvJ4BOoVC/th8QxW1vZeG6smfOxNi3BifsFkC', 'organizer', '', NULL, 0, 0, 'active', '2025-10-24 14:32:24'),
(6, 'admin', 'admin@gamex.com', '$2y$10$qG915KrvV/OMBllGHuMexegLbztwarbu0tF8sbARvoSHxQDejI/Fq', '', NULL, NULL, NULL, 1, 'active', '2025-10-31 05:17:40'),
(7, 'ajmayran', 'aj@gmail.com', '$2y$10$/PjND3f2Pd.POi4uhjCrceuHtAUXtol4MiCCIBAaRqjREstaiZhXW', 'player', 'AJ', NULL, 21, 0, 'active', '2025-10-31 05:52:50');

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
(78, 6, 'Access denied: tried to access admin area', 'N/A', '2025-11-07 16:12:16');

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
  `max_teams` int(11) NOT NULL,
  `reg_deadline` datetime NOT NULL,
  `status` enum('open','completed','cancelled') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  MODIFY `organizer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_profiles`
--
ALTER TABLE `player_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
