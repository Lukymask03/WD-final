-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 10:25 AM
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
(5, 'Call Of Duty Mobile', 'fas fa-gamepad', 'assets/images/games/game_69044dad95758.png', 1);

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
(1, 'CCS', 'Valorant', 'CCS qpals', 'assets/images/teams/team_6904621a35fd2.png', 5, 7, '2025-10-31 07:15:38');

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
(1, 7, 'leader', '2025-10-31 07:15:38');

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
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD UNIQUE KEY `game_name` (`game_name`);

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
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
