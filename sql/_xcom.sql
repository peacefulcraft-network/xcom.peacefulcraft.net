-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Generation Time: May 04, 2021 at 08:51 PM
-- Server version: 10.5.9-MariaDB-1:10.5.9+maria~focal
-- PHP Version: 7.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xcom`
--

-- --------------------------------------------------------

--
-- Table structure for table `party`
--

CREATE TABLE `party` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) DEFAULT NULL,
  `leader_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `party_membership`
--

CREATE TABLE `party_membership` (
  `party_id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `profile_link`
--

CREATE TABLE `profile_link` (
  `profile_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to profile.id',
  `link_service` enum('DISCORD','MOJANG','FLARUM','') CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Remote service',
  `link_identifier` varchar(256) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'ID at the remote service',
  `is_speculative` tinyint(1) NOT NULL COMMENT 'Set by staff member or other internal accessor for accounting',
  `link_visibility` enum('STAFF_VISIBLE','USER_VISIBLE','PUBLIC_VISIBLE','') CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `party`
--
ALTER TABLE `party`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leader_id` (`leader_id`);

--
-- Indexes for table `party_membership`
--
ALTER TABLE `party_membership`
  ADD UNIQUE KEY `profile_id` (`profile_id`),
  ADD KEY `party_id` (`party_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_link`
--
ALTER TABLE `profile_link`
  ADD UNIQUE KEY `profile_link` (`profile_id`,`link_service`),
  ADD UNIQUE KEY `remote_link` (`link_service`,`link_identifier`),
  ADD KEY `profile_id` (`profile_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `party`
--
ALTER TABLE `party`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `party`
--
ALTER TABLE `party`
  ADD CONSTRAINT `leader_id_link` FOREIGN KEY (`leader_id`) REFERENCES `profile` (`id`);

--
-- Constraints for table `party_membership`
--
ALTER TABLE `party_membership`
  ADD CONSTRAINT `party_id_link` FOREIGN KEY (`party_id`) REFERENCES `party` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `profile_id_link` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `profile_link`
--
ALTER TABLE `profile_link`
  ADD CONSTRAINT `profile_id` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
