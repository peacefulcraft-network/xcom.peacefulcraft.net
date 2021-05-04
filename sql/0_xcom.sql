SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `xcom` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `xcom`;

DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `profile_link`;
CREATE TABLE `profile_link` (
  `profile_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to profile.id',
  `link_service` enum('DISCORD','MOJANG','FLARUM','') CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Remote service',
  `link_identifier` varchar(256) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'ID at the remote service',
  `is_speculative` tinyint(1) NOT NULL COMMENT 'Set by staff member or other internal accessor for accounting',
  `link_visibility` enum('STAFF_VISIBLE','USER_VISIBLE','PUBLIC_VISIBLE','') CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `_migrations`;
CREATE TABLE `_migrations` (
  `migration` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `profile_link`
  ADD UNIQUE KEY `profile_link` (`profile_id`,`link_service`),
  ADD UNIQUE KEY `remote_link` (`link_service`,`link_identifier`),
  ADD KEY `profile_id` (`profile_id`);

ALTER TABLE `_migrations`
  ADD UNIQUE KEY `migration` (`migration`);


ALTER TABLE `profile`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `profile_link`
  ADD CONSTRAINT `profile_id` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
