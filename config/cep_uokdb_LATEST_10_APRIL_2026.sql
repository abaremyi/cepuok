-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.12.0.7122
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for cep_uokdb
CREATE DATABASE IF NOT EXISTS `cep_uokdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `cep_uokdb`;

-- Dumping structure for table cep_uokdb.budget_activities
CREATE TABLE IF NOT EXISTS `budget_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarter_id` int(11) NOT NULL,
  `pool_id` int(11) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `allocated_amount` decimal(15,2) DEFAULT 0.00,
  `spent_amount` decimal(15,2) DEFAULT 0.00,
  `is_external` tinyint(1) DEFAULT 0 COMMENT '1=Family/Choir own funds, CEP tracking only',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_q` (`quarter_id`),
  KEY `idx_pool` (`pool_id`),
  CONSTRAINT `fk_act_pool` FOREIGN KEY (`pool_id`) REFERENCES `indicator_pools` (`id`),
  CONSTRAINT `fk_act_q` FOREIGN KEY (`quarter_id`) REFERENCES `budget_quarters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.budget_activities: ~29 rows (approximately)
REPLACE INTO `budget_activities` (`id`, `quarter_id`, `pool_id`, `activity_name`, `allocated_amount`, `spent_amount`, `is_external`, `notes`, `created_at`) VALUES
	(21, 1, 13, 'Gutumira abashyitsi', 25000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(22, 1, 13, 'Outreach', 10000.00, 0.00, 0, 'Gusura ikigo cy\' amashuli', '2026-03-08 18:11:00'),
	(23, 1, 13, 'Seminar', 40000.00, 0.00, 0, 'Semirar yo kwiga Bibilya', '2026-03-08 18:11:00'),
	(24, 1, 13, 'Ibikorwa by\' amasengesho', 40000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(25, 1, 13, 'Gutegura Igiterane', 10000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(26, 1, 14, 'Ibikorwa by\' amafamille', 65000.00, 0.00, 1, 'Ubwitange bwagize amafaille', '2026-03-08 18:11:00'),
	(27, 1, 14, 'Gushyigikira ibikorwa bya Families', 25000.00, 0.00, 0, 'CEP Caise contribution', '2026-03-08 18:11:00'),
	(28, 1, 14, 'Gusura abarwayi', 10000.00, 0.00, 0, 'Gusura abarwayi kubitaro bya Kinyinya', '2026-03-08 18:11:00'),
	(29, 1, 14, 'Umusangiro wabakristo n\' Inama rusange', 10000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(30, 1, 15, 'Repetition zo muri studio', 60000.00, 0.00, 1, 'Uruhare rw\' abaterankunga', '2026-03-08 18:11:00'),
	(31, 1, 15, 'Audio Recording', 30000.00, 0.00, 1, 'Caise ya Chorale umusanzu wabaririmbyi', '2026-03-08 18:11:00'),
	(32, 1, 15, 'Sortie yo kukigo cyamashuli cya Kanombe', 5000.00, 0.00, 1, 'Umusanzu wabaririmbyi', '2026-03-08 18:11:00'),
	(33, 1, 15, 'CEP Caise contribution', 5000.00, 0.00, 0, 'amafranga azaturuka muri caise ya CEP', '2026-03-08 18:11:00'),
	(34, 1, 16, 'Airtime zabayobozi', 9000.00, 3000.00, 0, NULL, '2026-03-08 18:11:00'),
	(35, 1, 16, 'Airtime for IT Team for managing social media', 15000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(36, 1, 16, 'Inama rusange (General Assembly) numusangiro', 40000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(37, 1, 16, 'Ururabo nigitambaro cyo gutegura mumateraniro', 9000.00, 5200.00, 0, NULL, '2026-03-08 18:11:00'),
	(38, 1, 16, 'Amazi yo kwakiriza abashyitsi (Protocol)', 2000.00, 0.00, 0, NULL, '2026-03-08 18:11:00'),
	(39, 1, 17, 'Amafranga asigara muri Caise', 50000.00, 0.00, 0, 'Aya mafranga yakoreshwa mugihe havutse ikibazo kidasanzwe', '2026-03-08 18:11:00'),
	(40, 1, 18, 'Igikorwa cyo kugura Piano', 25000.00, 0.00, 0, 'Ubwitange buzakorwa n\' abanyamuryango', '2026-03-08 18:11:00'),
	(53, 3, 13, 'GUTUMIRA BASHYITSI', 25000.00, 0.00, 0, 'Tuzatumira xxx', '2026-03-28 16:37:56'),
	(54, 3, 14, 'Familys meeting ( Bethesda)', 20000.00, 0.00, 0, NULL, '2026-03-28 16:37:56'),
	(55, 3, 14, 'Naioth', 20000.00, 0.00, 0, NULL, '2026-03-28 16:37:56'),
	(56, 3, 14, 'Siloam', 2000.00, 0.00, 0, NULL, '2026-03-28 16:37:56'),
	(57, 3, 14, 'Betehrsdi celebration ya xxx', 25000.00, 0.00, 1, NULL, '2026-03-28 16:37:56'),
	(58, 3, 15, 'Practice rooms', 120000.00, 0.00, 1, NULL, '2026-03-28 16:37:56'),
	(59, 3, 16, 'Gutegera Piano', 15000.00, 1500.00, 0, NULL, '2026-03-28 16:37:56'),
	(60, 3, 16, 'Amazi y\' abashyitsi', 10000.00, 0.00, 0, NULL, '2026-03-28 16:37:56'),
	(61, 3, 17, 'Reserve', 50000.00, 0.00, 0, NULL, '2026-03-28 16:37:56');

-- Dumping structure for table cep_uokdb.budget_indicators
CREATE TABLE IF NOT EXISTS `budget_indicators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cep_session` enum('day','weekend') NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `base_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '100% reference balance',
  `lock_date` date DEFAULT NULL COMMENT 'President edit deadline',
  `status` enum('draft','confirmed','locked') DEFAULT 'draft',
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_year` (`cep_session`,`academic_year`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.budget_indicators: ~1 rows (approximately)
REPLACE INTO `budget_indicators` (`id`, `cep_session`, `academic_year`, `base_balance`, `lock_date`, `status`, `confirmed_by`, `confirmed_at`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'day', '2026-2027', 3000000.00, '2026-03-15', 'confirmed', 5, '2026-03-08 16:32:39', 1, '2026-03-08 16:25:30', '2026-03-08 18:25:03'),
	(3, 'day', '2027-2028', 4000000.00, NULL, 'draft', NULL, NULL, 5, '2026-03-28 16:31:45', '2026-03-28 16:31:45');

-- Dumping structure for table cep_uokdb.budget_lines
CREATE TABLE IF NOT EXISTS `budget_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `line_item` varchar(200) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `spent_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_budget` (`budget_id`),
  CONSTRAINT `fk_line_budget` FOREIGN KEY (`budget_id`) REFERENCES `finance_budgets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.budget_lines: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.budget_quarters
CREATE TABLE IF NOT EXISTS `budget_quarters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indicator_id` int(11) NOT NULL,
  `cep_session` enum('day','weekend') NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `quarter` enum('Q1','Q2','Q3') NOT NULL,
  `budget_name` varchar(200) NOT NULL,
  `total_allocated` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','suspended','approved') DEFAULT 'draft',
  `draft_created_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quarter` (`indicator_id`,`quarter`),
  KEY `idx_sess_year` (`cep_session`,`academic_year`),
  CONSTRAINT `fk_q_ind` FOREIGN KEY (`indicator_id`) REFERENCES `budget_indicators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.budget_quarters: ~1 rows (approximately)
REPLACE INTO `budget_quarters` (`id`, `indicator_id`, `cep_session`, `academic_year`, `quarter`, `budget_name`, `total_allocated`, `status`, `draft_created_at`, `approved_by`, `approved_at`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 1, 'day', '2026-2027', 'Q1', 'Q1 Budget', 325000.00, 'approved', '2026-03-08 17:55:08', 5, '2026-03-08 18:11:25', 'Budget yigihembwe cya mbere', 5, '2026-03-08 17:55:08', '2026-03-08 18:11:25'),
	(3, 1, 'day', '2026-2027', 'Q2', 'BUDGET YIGIHEMBWE CYA KABIRI MAY-AUGUST', 142000.00, 'approved', '2026-03-28 16:37:56', 5, '2026-03-28 16:38:11', 'Iyi budget ishoboriajjjs', 5, '2026-03-28 16:37:56', '2026-03-28 16:38:11');

-- Dumping structure for table cep_uokdb.cep_families
CREATE TABLE IF NOT EXISTS `cep_families` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(100) NOT NULL,
  `family_code` varchar(20) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'bi bi-people',
  `motto` varchar(255) DEFAULT NULL,
  `cep_session` enum('day','weekend','both') NOT NULL DEFAULT 'day',
  `description` text DEFAULT NULL,
  `parent_user_id` int(11) DEFAULT NULL COMMENT 'Current family parent (user)',
  `co_parent_user_id` int(11) DEFAULT NULL COMMENT 'Co-parent (user)',
  `color_code` varchar(10) DEFAULT '#007bff' COMMENT 'Family color for UI',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_family_name_session` (`family_name`,`cep_session`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.cep_families: ~3 rows (approximately)
REPLACE INTO `cep_families` (`id`, `family_name`, `family_code`, `icon_class`, `motto`, `cep_session`, `description`, `parent_user_id`, `co_parent_user_id`, `color_code`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Bethesda Family', 'FAM-BETH', 'bi bi-people', 'House of Mercy & Grace', 'day', NULL, NULL, NULL, '#28a745', 'active', '2026-02-22 15:12:54', '2026-02-22 15:12:54'),
	(2, 'Naioth Family', 'FAM-NAIT', 'bi bi-people', 'Dwelling of the Prophets', 'day', NULL, NULL, NULL, '#007bff', 'active', '2026-02-22 15:12:54', '2026-02-22 15:12:54'),
	(3, 'Siloam Family', 'FAM-SILO', 'bi bi-people', 'Sent to Heal & Restore', 'day', NULL, NULL, NULL, '#fd7e14', 'active', '2026-02-22 15:12:54', '2026-02-22 15:12:54');

-- Dumping structure for table cep_uokdb.cep_history_timeline
CREATE TABLE IF NOT EXISTS `cep_history_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-star',
  `is_current` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.cep_history_timeline: ~6 rows (approximately)
REPLACE INTO `cep_history_timeline` (`id`, `year`, `title`, `description`, `icon_class`, `is_current`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 2016, 'The Foundation', 'CEP UoK was officially established at the University of Kigali, bringing together Pentecostal students with a vision to impact the campus for Christ.', 'fas fa-church', 0, 1, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
	(2, 2018, 'Campus Expansion', 'Extended fellowship activities to include both Kacyiru and Remera campuses, reaching more students with the Gospel.', 'fas fa-expand-arrows-alt', 0, 2, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
	(3, 2019, 'Self-Reliance Initiative', 'Launched the first entrepreneurship and leadership training programs, emphasizing spiritual growth alongside practical skills.', 'fas fa-lightbulb', 0, 3, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
	(4, 2022, 'Dual Session Launch', 'Introduced Day and Weekend sessions to accommodate diverse student schedules, doubling our ministry reach.', 'fas fa-calendar-alt', 0, 4, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
	(5, 2024, 'Digital Ministry Era', 'Established comprehensive media team and online presence, extending our impact beyond physical campus boundaries.', 'fas fa-wifi', 0, 5, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
	(6, 2026, 'Continuous Growth', 'Celebrating sustained growth with over 200 active members and strengthened partnerships with local churches.', 'fas fa-trophy', 0, 6, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59');

-- Dumping structure for table cep_uokdb.cep_sessions
CREATE TABLE IF NOT EXISTS `cep_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_type` enum('day','weekend') NOT NULL,
  `session_label` varchar(100) NOT NULL COMMENT 'e.g. Day CEP 2026-2027',
  `academic_year` varchar(20) NOT NULL COMMENT 'e.g. 2026-2027',
  `committee_year_id` int(11) DEFAULT NULL COMMENT 'Links to leadership_years',
  `handover_date` date DEFAULT NULL COMMENT 'Date committee hands over',
  `portal_enabled` tinyint(1) DEFAULT 1 COMMENT 'Whether portal is accessible',
  `portal_locked_reason` text DEFAULT NULL COMMENT 'Reason if portal is locked',
  `locked_by` int(11) DEFAULT NULL COMMENT 'Super admin who locked it',
  `locked_at` timestamp NULL DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_type` (`session_type`),
  KEY `idx_is_current` (`is_current`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.cep_sessions: ~2 rows (approximately)
REPLACE INTO `cep_sessions` (`id`, `session_type`, `session_label`, `academic_year`, `committee_year_id`, `handover_date`, `portal_enabled`, `portal_locked_reason`, `locked_by`, `locked_at`, `is_current`, `created_at`, `updated_at`) VALUES
	(1, 'day', 'Day CEP 2026-2027', '2026-2027', 1, NULL, 1, NULL, NULL, NULL, 1, '2026-02-22 15:12:54', '2026-02-22 15:12:54'),
	(2, 'weekend', 'Weekend CEP 2026-2027', '2026-2027', 1, NULL, 1, NULL, NULL, NULL, 1, '2026-02-22 15:12:54', '2026-02-22 15:12:54');

-- Dumping structure for table cep_uokdb.cep_supporters
CREATE TABLE IF NOT EXISTS `cep_supporters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supporter_type` enum('alumni','external','choir','organization') NOT NULL DEFAULT 'external',
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `support_area` set('financial','instruments','service','prayers','general') DEFAULT 'general',
  `tier` enum('bronze','silver','gold','platinum') DEFAULT 'bronze',
  `is_alumni` tinyint(1) DEFAULT 0,
  `graduation_year` year(4) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_supporter_type` (`supporter_type`),
  KEY `idx_tier` (`tier`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.cep_supporters: ~1 rows (approximately)
REPLACE INTO `cep_supporters` (`id`, `supporter_type`, `firstname`, `lastname`, `organization_name`, `email`, `phone`, `address`, `cep_session`, `support_area`, `tier`, `is_alumni`, `graduation_year`, `notes`, `photo`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'external', 'MUTABAZI', 'Josue', 'Global Kwik Koders', 'mutabazijosue1@gmail.com', '0786055919', '1 KN 78 ST. Kigali', 'day', 'general', 'platinum', 1, '2019', 'Josue Yahoze aririmba muri Penuel Choir guher 2017 kugeza 2020. \nNyuma aza kujya munshingano aba n\' umuterankunga ushobora kugira icyo akora bitewe nuwamuganirije cyane cyane Remy cg Wiclef nabandi bake bakoranye icyo gihe. \n\nAkenera cyane ko ibintu bikorwa muri plan inoze kdi itangirwa report nawe akabona umusaruro uvuye muri contribution yatanze', NULL, 'active', '2026-02-28 09:08:34', '2026-03-05 00:26:19');

-- Dumping structure for table cep_uokdb.choir_attendance
CREATE TABLE IF NOT EXISTS `choir_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rehearsal_id` int(11) NOT NULL,
  `choir_member_id` int(11) NOT NULL,
  `status` enum('present','absent','excused','late') DEFAULT 'present',
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rehearsal_member` (`rehearsal_id`,`choir_member_id`),
  KEY `idx_rehearsal` (`rehearsal_id`),
  KEY `idx_member` (`choir_member_id`),
  CONSTRAINT `fk_att_member` FOREIGN KEY (`choir_member_id`) REFERENCES `choir_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_rehearsal` FOREIGN KEY (`rehearsal_id`) REFERENCES `choir_rehearsals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.choir_attendance: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.choir_members
CREATE TABLE IF NOT EXISTS `choir_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT 'Link to members table',
  `full_name` varchar(200) NOT NULL COMMENT 'Cached for display even if member unlinked',
  `voice_part` enum('soprano','alto','tenor','bass','other') DEFAULT 'soprano',
  `instrument` varchar(100) DEFAULT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `role` enum('member','section_leader','choir_president','accompanist') DEFAULT 'member',
  `joined_date` date DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_session` (`cep_session`),
  CONSTRAINT `fk_choir_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.choir_members: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.choir_rehearsals
CREATE TABLE IF NOT EXISTS `choir_rehearsals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rehearsal_date` date NOT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `location` varchar(200) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `conductor_id` int(11) DEFAULT NULL,
  `songs_practiced` text DEFAULT NULL COMMENT 'CSV of song IDs or free text',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_date` (`rehearsal_date`),
  KEY `fk_rehearsal_conductor` (`conductor_id`),
  CONSTRAINT `fk_rehearsal_conductor` FOREIGN KEY (`conductor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.choir_rehearsals: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.choir_songs
CREATE TABLE IF NOT EXISTS `choir_songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `composer` varchar(200) DEFAULT NULL,
  `arranger` varchar(200) DEFAULT NULL,
  `language` varchar(50) DEFAULT 'Kinyarwanda',
  `category` enum('worship','praise','anthem','christmas','easter','other') DEFAULT 'worship',
  `key_signature` varchar(10) DEFAULT NULL COMMENT 'e.g. C major, G minor',
  `tempo` varchar(50) DEFAULT NULL COMMENT 'e.g. 84 BPM, Andante',
  `youtube_url` varchar(500) DEFAULT NULL,
  `sheet_music_path` varchar(500) DEFAULT NULL,
  `lyrics_path` varchar(500) DEFAULT NULL,
  `lyrics` mediumtext DEFAULT NULL,
  `times_performed` int(11) DEFAULT 0,
  `last_performed` date DEFAULT NULL,
  `status` enum('active','archived','learning') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.choir_songs: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.churches
CREATE TABLE IF NOT EXISTS `churches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `church_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `denomination` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_church_name` (`church_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.churches: ~5 rows (approximately)
REPLACE INTO `churches` (`id`, `church_name`, `location`, `denomination`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'ADEPR Kimihurura International Service', 'Kimihurura, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(2, 'ADEPR Remera', 'Remera, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(3, 'ADEPR Kicukiro', 'Kicukiro, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(4, 'ADEPR Nyamirambo', 'Nyamirambo, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(5, 'Other Church', 'Various', 'Other', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01');

-- Dumping structure for table cep_uokdb.committee_handovers
CREATE TABLE IF NOT EXISTS `committee_handovers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cep_session` enum('day','weekend') NOT NULL,
  `outgoing_year_id` int(11) NOT NULL COMMENT 'Outgoing committee year',
  `incoming_year_id` int(11) DEFAULT NULL COMMENT 'Incoming committee year',
  `handover_date` date NOT NULL,
  `handover_summary` text DEFAULT NULL,
  `financial_balance` decimal(15,2) DEFAULT 0.00 COMMENT 'Balance handed over',
  `pending_issues` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `document_path` varchar(500) DEFAULT NULL COMMENT 'PDF report path',
  `conducted_by` int(11) DEFAULT NULL COMMENT 'Super admin or president',
  `status` enum('draft','completed') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.committee_handovers: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.credential_wallet
CREATE TABLE IF NOT EXISTS `credential_wallet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` enum('social_media','email','api_key','hosting','domain','analytics','payment','other') NOT NULL DEFAULT 'other' COMMENT 'Credential category',
  `platform` varchar(100) NOT NULL COMMENT 'e.g. Gmail, YouTube, Instagram, cPanel, AWS',
  `account_label` varchar(255) NOT NULL COMMENT 'Human-friendly label, e.g. CEP Photos Gmail',
  `username` varchar(255) DEFAULT NULL COMMENT 'Username or email used to log in',
  `password_encrypted` text DEFAULT NULL COMMENT 'AES-256-CBC encrypted password (iv:cipher)',
  `account_url` varchar(500) DEFAULT NULL COMMENT 'Login URL if applicable',
  `verification_phone` varchar(50) DEFAULT NULL COMMENT '2FA / recovery phone number',
  `verification_email` varchar(255) DEFAULT NULL COMMENT '2FA / recovery email address',
  `creator_name` varchar(255) DEFAULT NULL COMMENT 'Name of person who created this account',
  `creator_phone` varchar(50) DEFAULT NULL COMMENT 'Contact phone of account creator',
  `creator_email` varchar(255) DEFAULT NULL COMMENT 'Contact email of account creator',
  `purpose` text DEFAULT NULL COMMENT 'What this account is used for',
  `notes` text DEFAULT NULL COMMENT 'Additional notes, API scopes, plan details, etc.',
  `expiry_date` date DEFAULT NULL COMMENT 'Subscription / domain / API key expiry (nullable)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `added_by` int(11) DEFAULT NULL COMMENT 'admin user_id who added the record',
  `updated_by` int(11) DEFAULT NULL COMMENT 'admin user_id who last updated the record',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_platform` (`platform`),
  KEY `idx_added_by` (`added_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encrypted credentials vault for CEP social/digital accounts';

-- Dumping data for table cep_uokdb.credential_wallet: ~0 rows (approximately)
REPLACE INTO `credential_wallet` (`id`, `category`, `platform`, `account_label`, `username`, `password_encrypted`, `account_url`, `verification_phone`, `verification_email`, `creator_name`, `creator_phone`, `creator_email`, `purpose`, `notes`, `expiry_date`, `is_active`, `added_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 'email', 'Gmail', 'Cep Official Communication', 'cepuok01@gmail.com', 'bj//PBXpwrjjEwnwexs0xg==:YVlq9cOOaKfV43tiGr1w+g==', 'https://accounts.google.com', '+250787254817', 'aba1remy@gmail.com', 'ABAYO REMY', '+250787254817', 'aba1remy@gmail.com', 'Receiving Emails and the one used on the Youtube and other social Medias of CEP', 'This Email was first created by Dieudonne Buzima in 2019 and then he shared credentials to the next President Evras but after sometime all Presidents shared the credentials until it was Given to Remy Abayo who is responsible of knowing the use and security of the credentials used and the media team. so this is given to anyone who is chosen to be the leader and the one in charge of media in CEP', '2051-01-01', 1, 1, NULL, '2026-03-18 12:25:58', '2026-03-18 12:25:58');

-- Dumping structure for table cep_uokdb.credential_wallet_audit
CREATE TABLE IF NOT EXISTS `credential_wallet_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credential_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('viewed','copied_password','created','updated','deleted','toggled_status') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_credential_id` (`credential_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for every action on the credentials wallet';

-- Dumping data for table cep_uokdb.credential_wallet_audit: ~4 rows (approximately)
REPLACE INTO `credential_wallet_audit` (`id`, `credential_id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 1, 1, 'created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-18 12:25:58'),
	(2, 1, 1, 'copied_password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-18 12:26:20'),
	(3, 1, 1, 'viewed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-18 12:26:31'),
	(4, 1, 1, 'viewed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-18 12:26:37'),
	(5, 1, 1, 'viewed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-28 16:52:00');

-- Dumping structure for table cep_uokdb.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.departments: ~6 rows (approximately)
REPLACE INTO `departments` (`id`, `title`, `subtitle`, `description`, `icon_class`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Evangelism', 'Sharing the Gospel', 'Reaching out to fellow students with the message of Christ through campus evangelism, outreach programs, and personal testimonies.', 'fas fa-bible', '/img/departments/evangelism.jpg', 1, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(2, 'Choir', 'Worship in Song', 'Leading worship through music and song, bringing glory to God and ministering to the hearts of students.', 'fas fa-music', '/img/departments/choir.jpg', 2, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(3, 'Protocol', 'Order and Excellence', 'Ensuring smooth organization of events, proper protocols, and maintaining excellence in all CEP activities.', 'fas fa-tasks', '/img/departments/protocol.jpg', 3, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(4, 'Social Affairs', 'Care and Community', 'Caring for the social, emotional, and material needs of members while building strong community bonds.', 'fas fa-heart', '/img/departments/social-affairs.jpg', 4, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(5, 'Media Team', 'Digital Ministry', 'Managing CEP\'s online presence, documentation, and multimedia content to extend our reach and impact.', 'fas fa-camera', '/img/departments/media.jpg', 5, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(6, 'Worship Team', 'Leading Worship', 'Providing instrumental and vocal leadership in worship services, creating an atmosphere for encountering God.', 'fas fa-guitar', '/img/departments/worship.jpg', 6, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45');

-- Dumping structure for table cep_uokdb.disbursements
CREATE TABLE IF NOT EXISTS `disbursements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','mobile_money','bank_transfer','cheque') DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `recipient_name` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `disbursed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receipt_path` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_request` (`request_id`),
  KEY `idx_disbursed` (`disbursed_by`),
  CONSTRAINT `fk_disb_by_user` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_disb_request` FOREIGN KEY (`request_id`) REFERENCES `fund_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.disbursements: ~2 rows (approximately)
REPLACE INTO `disbursements` (`id`, `request_id`, `cep_session`, `amount`, `payment_method`, `reference_no`, `recipient_name`, `notes`, `disbursed_by`, `disbursed_at`, `receipt_path`) VALUES
	(1, 3, 'day', 5200.00, 'mobile_money', NULL, 'Patience na Innocent', 'Amafranga yururabo yohererejwe Patience', 3, '2026-03-10 21:15:24', NULL),
	(2, 2, 'day', 3000.00, 'cash', NULL, 'Ntagawa, Aaron, Alice, Emelyne', 'abayobozi bose bahawe amafranga 500 buri umwe yo kugura ama inite', 3, '2026-03-10 21:18:36', NULL),
	(3, 4, 'day', 1500.00, 'mobile_money', '88885', 'Ezeckiel', 'Done', 3, '2026-03-28 16:49:42', NULL);

-- Dumping structure for table cep_uokdb.finance_budgets
CREATE TABLE IF NOT EXISTS `finance_budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cep_session` enum('day','weekend') NOT NULL DEFAULT 'day',
  `budget_name` varchar(200) NOT NULL,
  `academic_year` varchar(20) NOT NULL COMMENT 'e.g. 2026-2027',
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `approved_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session` (`cep_session`),
  KEY `idx_status` (`status`),
  KEY `fk_budget_creator` (`created_by`),
  KEY `fk_budget_approver` (`approved_by`),
  CONSTRAINT `fk_budget_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budget_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.finance_budgets: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.finance_revenue
CREATE TABLE IF NOT EXISTS `finance_revenue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cep_session` enum('day','weekend','both') NOT NULL DEFAULT 'day',
  `revenue_type` enum('offering','tithe','donation','project','fundraising','other') NOT NULL DEFAULT 'offering',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `revenue_date` date NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session` (`cep_session`),
  KEY `idx_type` (`revenue_type`),
  KEY `idx_date` (`revenue_date`),
  KEY `idx_recorded` (`recorded_by`),
  CONSTRAINT `fk_revenue_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.finance_revenue: ~3 rows (approximately)
REPLACE INTO `finance_revenue` (`id`, `cep_session`, `revenue_type`, `amount`, `description`, `revenue_date`, `reference_no`, `recorded_by`, `created_at`, `updated_at`) VALUES
	(6, 'day', 'offering', 5000.00, 'Amaturo yuyu munsi', '2026-03-17', '', 3, '2026-03-17 16:40:42', '2026-03-17 16:40:42'),
	(7, 'day', 'other', 50000.00, 'Josue aduteye inkunga', '2026-03-28', '', 5, '2026-03-28 16:39:46', '2026-03-28 16:39:46'),
	(8, 'day', 'offering', 5000.00, 'Amaturo yo kuwa kane', '2026-03-28', '', 5, '2026-03-28 16:40:13', '2026-03-28 16:40:13');

-- Dumping structure for table cep_uokdb.fund_requests
CREATE TABLE IF NOT EXISTS `fund_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cep_session` enum('day','weekend') NOT NULL DEFAULT 'day',
  `request_number` varchar(30) DEFAULT NULL COMMENT 'Auto-generated: FR-2026-001',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `indicator_id` int(11) DEFAULT NULL,
  `budget_quarter_id` int(11) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `amount_requested` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_approved` decimal(15,2) DEFAULT NULL,
  `stage` enum('draft','to_president','rejected_by_president','to_finance','completed') NOT NULL DEFAULT 'draft',
  `requested_by` int(11) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `needed_by_date` date DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_request_number` (`request_number`),
  KEY `idx_session` (`cep_session`),
  KEY `idx_stage` (`stage`),
  KEY `idx_requester` (`requested_by`),
  KEY `fk_fr_reviewer` (`reviewed_by`),
  KEY `fk_fr_approver` (`approved_by`),
  CONSTRAINT `fk_fr_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fr_requester` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fr_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.fund_requests: ~3 rows (approximately)
REPLACE INTO `fund_requests` (`id`, `cep_session`, `request_number`, `title`, `description`, `department`, `indicator_id`, `budget_quarter_id`, `activity_id`, `amount_requested`, `amount_approved`, `stage`, `requested_by`, `reviewed_by`, `reviewed_at`, `approved_by`, `approved_at`, `rejection_reason`, `priority`, `needed_by_date`, `submitted_at`, `created_at`, `updated_at`) VALUES
	(2, 'day', 'FR-2026-001', 'Month 1 Airtime', 'Kugura ama inite y\' abayobozi y\' ukwezi kwa Mbere', NULL, 16, 1, 34, 3000.00, 3000.00, 'completed', 5, NULL, NULL, 5, '2026-03-08 18:55:05', NULL, 'medium', '2026-03-01', '2026-03-08 18:44:51', '2026-03-08 18:44:50', '2026-03-10 21:18:36'),
	(3, 'day', 'FR-2026-002', 'Amafranga yururabo', 'ururabo rwari ruhendutse', NULL, 16, 1, 37, 5200.00, 5200.00, 'completed', 5, NULL, NULL, 5, '2026-03-08 21:02:50', NULL, 'low', '2026-03-11', '2026-03-08 21:01:13', '2026-03-08 21:01:12', '2026-03-10 21:15:24'),
	(4, 'day', 'FR-2026-003', 'Ticket ya Piano', 'Execkiel azajya kuzana Piano KIS', NULL, 16, 3, 59, 1500.00, 1500.00, 'completed', 5, NULL, NULL, 5, '2026-03-28 16:45:21', NULL, 'urgent', '2026-03-30', '2026-03-28 16:44:31', '2026-03-28 16:44:31', '2026-03-28 16:49:42');

-- Dumping structure for table cep_uokdb.fund_request_comments
CREATE TABLE IF NOT EXISTS `fund_request_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_req` (`request_id`),
  CONSTRAINT `fk_comm_req` FOREIGN KEY (`request_id`) REFERENCES `fund_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.fund_request_comments: ~2 rows (approximately)
REPLACE INTO `fund_request_comments` (`id`, `request_id`, `user_id`, `comment`, `created_at`) VALUES
	(1, 2, 5, 'Request Approved with no issue', '2026-03-08 18:54:33'),
	(2, 2, 5, 'You have my final approval', '2026-03-08 18:55:05'),
	(3, 3, 5, 'Ko amafranga ari menshi', '2026-03-08 21:02:16'),
	(4, 4, 5, 'President nemeje ko aya mafranga asohoka', '2026-03-28 16:45:21');

-- Dumping structure for table cep_uokdb.gallery_images
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'general',
  `year` int(4) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `is_featured` (`is_featured`),
  KEY `status` (`status`),
  KEY `idx_year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.gallery_images: ~25 rows (approximately)
REPLACE INTO `gallery_images` (`id`, `title`, `description`, `image_url`, `thumbnail_url`, `category`, `year`, `display_order`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Monday English Fellowship', 'Students gathering for English service at Kacyiru Campus', 'gallery/fellowship-1.jpg', NULL, 'Fellowship', 2026, 1, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(2, 'Annual Conference 2024', 'CEP UoK annual conference bringing students together', 'gallery/event-1.jpg', NULL, 'Event', 2026, 2, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(3, 'Choir Performance', 'CEP choir ministering during Sunday service', 'gallery/choir-1.jpg', NULL, 'Choir', 2026, 3, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(4, 'CEP Families', 'Small group fellowship and discipleship', 'gallery/families-1.jpg', NULL, 'Families', 2026, 4, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(5, 'Campus Outreach', 'Evangelism and outreach at University of Kigali', 'gallery/outreach-1.gif', NULL, 'Outreach', 2026, 5, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(6, 'Leadership Initiative', 'Training future Christian leaders', 'gallery/initiative-1.jpg', NULL, 'Initiative', 2026, 6, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(7, 'Bible Seminar', 'In-depth Bible study and teaching session', 'gallery/seminar-1.jpg', NULL, 'Seminar', 2026, 7, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(8, 'Community Service', 'Serving the local community around campus', 'gallery/community-1.jpg', NULL, 'Community Work', 2026, 8, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(9, 'Social Welfare Project', 'Helping those in need through social programs', 'gallery/welfare-1.jpg', NULL, 'Social Welfare', 2026, 9, 1, 'active', '2026-01-29 15:00:45', '2026-02-05 11:28:04'),
	(10, 'Fellowship Service 2026', 'Weekly fellowship service', 'gallery/fellowship-1.jpg', 'gallery/fellowship-1.jpg', 'fellowship', 2026, 1, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(11, 'Worship Team 2026', 'Our worship team in action', 'gallery/worship-1.jpg', 'gallery/worship-1.jpg', 'worship', 2026, 2, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(12, 'Outreach Program 2026', 'Community outreach event', 'gallery/outreach-1.jpg', 'gallery/outreach-1.jpg', 'outreach', 2026, 3, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(13, 'Prayer Meeting 2026', 'Intercessory prayer session', 'gallery/prayer-1.jpg', 'gallery/prayer-1.jpg', 'prayer', 2026, 4, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(14, 'Annual Conference 2025', 'CEP Annual Conference', 'gallery/conference-1.jpg', 'gallery/conference-1.jpg', 'events', 2025, 1, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(15, 'Choir Performance 2025', 'Choir ministry', 'gallery/choir-1.jpg', 'gallery/choir-1.jpg', 'choir', 2025, 2, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(16, 'Leadership Training 2025', 'Leadership development program', 'gallery/leadership-1.jpg', 'gallery/leadership-1.jpg', 'training', 2025, 3, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(17, 'Campus Evangelism 2025', 'Reaching students for Christ', 'gallery/evangelism-1.jpg', 'gallery/evangelism-1.jpg', 'evangelism', 2025, 4, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(18, 'Retreat 2024', 'Spiritual retreat', 'gallery/retreat-1.jpg', 'gallery/retreat-1.jpg', 'events', 2024, 1, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(19, 'Bible Study 2024', 'Small group Bible study', 'gallery/biblestudy-1.jpg', 'gallery/biblestudy-1.jpg', 'biblestudy', 2024, 2, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(20, 'Social Welfare 2024', 'Community service', 'gallery/welfare-1.jpg', 'gallery/welfare-1.jpg', 'welfare', 2024, 3, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(21, 'Youth Summit 2024', 'CEP Youth Summit', 'gallery/summit-1.jpg', 'gallery/summit-1.jpg', 'events', 2024, 4, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:16:36'),
	(22, 'Worship Night 2023', 'Night of worship', 'gallery/worship-night.jpg', 'gallery/worship-night.jpg', 'worship', 2023, 1, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:18:05'),
	(23, 'Baptism Service 2023', 'Water baptism ceremony', 'gallery/baptism-1.jpg', 'gallery/baptism-1.jpg', 'events', 2023, 2, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:18:05'),
	(24, 'Dual Sessions Launch 2022', 'Launch of day and weekend sessions', 'gallery/dual-launch.jpg', 'gallery/dual-launch.jpg', 'events', 2022, 1, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:18:05'),
	(25, 'Christmas Celebration 2022', 'Christmas fellowship', 'gallery/christmas.jpg', 'gallery/christmas.jpg', 'events', 2022, 2, 0, 'active', '2026-02-05 11:24:46', '2026-02-05 19:18:05');

-- Dumping structure for table cep_uokdb.gallery_years
CREATE TABLE IF NOT EXISTS `gallery_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `year_label` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`),
  KEY `status` (`status`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.gallery_years: ~11 rows (approximately)
REPLACE INTO `gallery_years` (`id`, `year`, `year_label`, `description`, `display_order`, `status`, `created_at`) VALUES
	(1, 2026, '2026', 'Current year gallery', 1, 'active', '2026-02-05 11:24:46'),
	(2, 2025, '2025', 'Gallery from 2025', 2, 'active', '2026-02-05 11:24:46'),
	(3, 2024, '2024', 'Gallery from 2024', 3, 'active', '2026-02-05 11:24:46'),
	(4, 2023, '2023', 'Gallery from 2023', 4, 'active', '2026-02-05 11:24:46'),
	(5, 2022, '2022', 'Gallery from 2022', 5, 'active', '2026-02-05 11:24:46'),
	(6, 2021, '2021', 'Gallery from 2021', 6, 'active', '2026-02-05 11:24:46'),
	(7, 2020, '2020', 'Gallery from 2020', 7, 'active', '2026-02-05 11:24:46'),
	(8, 2019, '2019', 'Gallery from 2019', 8, 'active', '2026-02-05 11:24:46'),
	(9, 2018, '2018', 'Gallery from 2018', 9, 'active', '2026-02-05 11:24:46'),
	(10, 2017, '2017', 'Gallery from 2017', 10, 'active', '2026-02-05 11:24:46'),
	(11, 2016, '2016', 'Gallery from 2016', 11, 'active', '2026-02-05 11:24:46');

-- Dumping structure for table cep_uokdb.hero_sliders
CREATE TABLE IF NOT EXISTS `hero_sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `button1_text` varchar(100) DEFAULT 'Learn More',
  `button1_link` varchar(500) DEFAULT '#',
  `button2_text` varchar(100) DEFAULT 'Contact Us',
  `button2_link` varchar(500) DEFAULT '#',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.hero_sliders: ~3 rows (approximately)
REPLACE INTO `hero_sliders` (`id`, `title`, `subtitle`, `description`, `image_url`, `button1_text`, `button1_link`, `button2_text`, `button2_link`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Faith on Campus', 'WE GROW TOGETHER IN CHRIST', 'CEP UoK is a Christian students\' fellowship at the University of Kigali, nurturing spiritual growth, unity, and purpose.', '/img/slider/slider-1.jpg', 'Learn More', '/about', 'Contact Us', '/contact', 1, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(2, 'Christ-Centered Leaders', 'CALLED TO SERVE AND LEAD', 'Through prayer, worship, discipleship, and fellowship, we equip students to live out their faith and impact the university and society.', '/img/slider/slider-2.jpg', 'Our Departments', '/departments', 'Join Us', '/contact', 2, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(3, 'You Belong Here', 'A HOME FOR EVERY STUDENT', 'Open to all University of Kigali students, CEP UoK offers a welcoming community to grow spiritually, serve together, and walk in faith.', '/img/slider/slider-3.jpg', 'View Events', '/news', 'Contact Us', '/contact', 3, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44');

-- Dumping structure for table cep_uokdb.indicator_pools
CREATE TABLE IF NOT EXISTS `indicator_pools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indicator_id` int(11) NOT NULL,
  `pool_name` varchar(100) NOT NULL,
  `pool_slug` varchar(50) NOT NULL,
  `pool_type` enum('department','internal','reserve','other') DEFAULT 'department',
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `allocated_amount` decimal(15,2) DEFAULT 0.00,
  `color` varchar(20) DEFAULT '#377dff',
  `display_order` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_indicator` (`indicator_id`),
  CONSTRAINT `fk_pool_ind` FOREIGN KEY (`indicator_id`) REFERENCES `budget_indicators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.indicator_pools: ~12 rows (approximately)
REPLACE INTO `indicator_pools` (`id`, `indicator_id`, `pool_name`, `pool_slug`, `pool_type`, `percentage`, `allocated_amount`, `color`, `display_order`, `created_at`) VALUES
	(13, 1, 'Evangelism', 'evangelism', 'department', 25.00, 125000.00, '#f55151', 0, '2026-03-08 17:20:29'),
	(14, 1, 'Social Affairs', 'social_affairs', 'department', 25.00, 125000.00, '#000000', 1, '2026-03-08 17:20:29'),
	(15, 1, 'Choir', 'choir', 'department', 20.00, 100000.00, '#06e07a', 2, '2026-03-08 17:20:29'),
	(16, 1, 'Internal Processes', 'internal', 'internal', 15.00, 75000.00, '#f00098', 3, '2026-03-08 17:20:29'),
	(17, 1, 'Reserve Pool', 'reserve', 'reserve', 10.00, 50000.00, '#6f42c1', 4, '2026-03-08 17:20:29'),
	(18, 1, 'Buying Piano', 'buying_piano', 'other', 5.00, 25000.00, '#ffae00', 5, '2026-03-08 17:20:29'),
	(25, 3, 'Evangelism', 'evangelism', 'department', 20.00, 800000.00, '#377dff', 0, '2026-03-28 16:31:45'),
	(26, 3, 'Social Affairs', 'social_affairs', 'department', 25.00, 1000000.00, '#00c9a7', 1, '2026-03-28 16:31:45'),
	(27, 3, 'Choir', 'choir', 'department', 20.00, 800000.00, '#e8a838', 2, '2026-03-28 16:31:45'),
	(28, 3, 'Internal Processes', 'internal', 'internal', 15.00, 600000.00, '#ed4c78', 3, '2026-03-28 16:31:45'),
	(29, 3, 'Reserve Pool', 'reserve', 'reserve', 10.00, 400000.00, '#6f42c1', 4, '2026-03-28 16:31:45'),
	(30, 3, 'KUGURA CAMERA', 'kugura_camera', 'internal', 10.00, 400000.00, '#20c997', 5, '2026-03-28 16:31:45');

-- Dumping structure for table cep_uokdb.leadership_achievements
CREATE TABLE IF NOT EXISTS `leadership_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) NOT NULL,
  `achievement_title` varchar(255) NOT NULL,
  `achievement_description` text DEFAULT NULL,
  `achievement_date` date DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-trophy',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `year_id` (`year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.leadership_achievements: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.leadership_members
CREATE TABLE IF NOT EXISTS `leadership_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `session_type` enum('both','day','weekend') DEFAULT 'both',
  `image_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `year_id` (`year_id`),
  KEY `position_id` (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.leadership_members: ~101 rows (approximately)
REPLACE INTO `leadership_members` (`id`, `year_id`, `position_id`, `full_name`, `session_type`, `image_url`, `bio`, `display_order`, `status`, `created_at`) VALUES
	(1, 1, 1, 'Ntagawa David', 'day', 'leaders/David-Ntagawa.jpg', NULL, 1, 'active', '2026-02-04 21:09:51'),
	(2, 1, 3, 'Niyonzima Aaron', 'day', 'leaders/Aaron.jpg', NULL, 2, 'active', '2026-02-04 21:09:51'),
	(3, 1, 4, 'Uwineza Emelyne', 'day', 'leaders/Emelyne-Uwineza.jpg', NULL, 3, 'active', '2026-02-04 21:09:51'),
	(4, 1, 6, 'Mukashema Alice', 'day', 'leaders/Alice-Mukashema.jpg', NULL, 4, 'active', '2026-02-04 21:09:51'),
	(5, 1, 5, 'Niyobugingo Fidele', 'day', 'leaders/Fidele.jpg', NULL, 5, 'active', '2026-02-04 21:09:51'),
	(6, 1, 8, 'Nshuti Yves', 'day', 'leaders/Yves.jpg', NULL, 6, 'active', '2026-02-04 21:09:51'),
	(7, 1, 8, 'Igiraneza Argentine', 'day', 'leaders/Argentine.jpg', NULL, 7, 'active', '2026-02-04 21:09:51'),
	(8, 1, 8, 'Tambineza Patience', 'day', 'leaders/Patience.jpg', NULL, 8, 'active', '2026-02-04 21:09:51'),
	(9, 1, 10, 'Iradukunda Eric', 'day', 'leaders/Eric-Iradukunda.jpg', NULL, 9, 'active', '2026-02-04 21:09:51'),
	(10, 1, 1, 'Charles Uhagaze', 'weekend', 'leaders/Charles.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(11, 1, 2, 'Irakoze Emelyne', 'weekend', NULL, NULL, 2, 'active', '2026-02-04 21:10:38'),
	(12, 1, 4, 'Hagenimana Claude', 'weekend', NULL, NULL, 3, 'active', '2026-02-04 21:10:38'),
	(13, 1, 6, 'Mushimiyimana Eline', 'weekend', 'leaders/Eline.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(14, 1, 5, 'Ndayishimiye Clarisse', 'weekend', 'leaders/Clarisse.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(15, 1, 8, 'Mutuyimana Alex', 'weekend', NULL, NULL, 6, 'active', '2026-02-04 21:10:38'),
	(16, 1, 8, 'Niyotwizerwa Felix', 'weekend', NULL, NULL, 7, 'active', '2026-02-04 21:10:38'),
	(17, 1, 8, 'Uwera Françoise', 'weekend', NULL, NULL, 8, 'active', '2026-02-04 21:10:38'),
	(18, 1, 13, 'Igiraneza Amina', 'weekend', NULL, NULL, 9, 'active', '2026-02-04 21:10:38'),
	(19, 2, 1, 'Dushime Alimence', 'day', 'leaders/Alimence.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(20, 2, 2, 'Niyonzima Aaron', 'day', 'leaders/Aaron.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(21, 2, 4, 'Mucyo Cadeau Prince', 'day', 'leaders/Cadeau.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(22, 2, 6, 'Nkurunziza Mbabazi Virginie', 'day', 'leaders/Virginie.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(23, 2, 5, 'Uwumukiza Celine', 'day', 'leaders/Celine.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(24, 2, 11, 'Irakoze Jeanne Bella', 'day', 'leaders/Bella.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(25, 2, 12, 'Ntagawa David', 'day', 'leaders/David-Ntagawa.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(26, 2, 8, 'Niyobugingo Fidele', 'day', 'leaders/Fidele.jpg', NULL, 8, 'active', '2026-02-04 21:10:38'),
	(27, 2, 10, 'Ndatimana Elie', 'day', 'leaders/Elie.jpg', NULL, 9, 'active', '2026-02-04 21:10:38'),
	(28, 2, 1, 'Uwineza Marie Goreth', 'weekend', 'leaders/Goreth.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(29, 2, 2, 'Mugisha Dieudonné', 'weekend', 'leaders/Mugisha.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(30, 2, 4, 'Charles Uhagaze', 'weekend', 'leaders/Charles.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(31, 2, 6, 'Alice Uwizeyimana', 'weekend', 'leaders/Alice-Uwizeyimana.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(32, 2, 5, 'Belyse Irasubiza', 'weekend', 'leaders/Belyse-Irasubiza.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(33, 2, 8, 'Eline Mushimiyimana', 'weekend', 'leaders/Eline.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(34, 2, 8, 'Leandre Imanishimwe', 'weekend', 'leaders/Leandre.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(35, 2, 8, 'Aimé', 'weekend', 'leaders/Aime.jpg', NULL, 8, 'active', '2026-02-04 21:10:38'),
	(36, 2, 13, 'Joel Niyonkuru', 'weekend', 'leaders/Joel.jpg', NULL, 9, 'active', '2026-02-04 21:10:38'),
	(37, 3, 1, 'Nsengimana Emmanuel', 'day', 'leaders/Emmanuel.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(38, 3, 3, 'Dushime Alimance', 'day', 'leaders/Alimence.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(39, 3, 4, 'David Senga Uwumugisha', 'day', 'leaders/Senga.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(40, 3, 6, 'Emelyne Ishimirwe', 'day', 'leaders/Emelyne-Ishimirwe.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(41, 3, 5, 'Mushimiyima Anitha', 'day', 'leaders/Anitha.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(42, 3, 11, 'Uburiza Mbabazi Evelyne', 'day', 'leaders/Evelyne.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(43, 3, 10, 'Mbabazi Virginie', 'day', 'leaders/Virginie.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(44, 3, 12, 'Celine Uwumukiza', 'day', 'leaders/Celine.jpg', NULL, 8, 'active', '2026-02-04 21:10:38'),
	(45, 3, 1, 'Etienne Niyonshuti', 'weekend', 'leaders/Etienne.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(46, 3, 2, 'Uwineza Goreth', 'weekend', 'leaders/Goreth.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(47, 3, 4, 'Diane Itangishatse', 'weekend', 'leaders/Diane.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(48, 3, 6, 'Alice Uwizeyimana', 'weekend', 'leaders/Alice-Uwizeyimana.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(49, 3, 5, 'Mugisha Dieudonné', 'weekend', 'leaders/Mugisha.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(50, 3, 8, 'Kevin Christian', 'weekend', 'leaders/Kevin-Christian.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(51, 3, 8, 'Bizimana Rambert', 'weekend', 'leaders/Rambert.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(52, 3, 8, 'Evariste Ntacyombahishe', 'weekend', 'leaders/Evariste.jpg', NULL, 8, 'active', '2026-02-04 21:10:38'),
	(53, 3, 8, 'Kezia', 'weekend', 'leaders/Kezia.jpg', NULL, 9, 'active', '2026-02-04 21:10:38'),
	(54, 4, 1, 'Ishimwe David', 'day', 'leaders/David-Ishimwe.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(55, 4, 3, 'Nsengimana Emmanuel', 'day', 'leaders/Emmanuel.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(56, 4, 4, 'David Senga Uwumugisha', 'day', 'leaders/Senga.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(57, 4, 5, 'Uwijuru Ikirezi Deborah', 'day', 'leaders/Deborah.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(58, 4, 6, 'Emelyne Ishimirwe', 'day', 'leaders/Emelyne-Ishimirwe.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(59, 4, 14, 'Shalon Ingabire', 'day', 'leaders/Shalon.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(60, 4, 8, 'Ingabire Esperance', 'day', 'leaders/Esperance.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(61, 4, 1, 'Etienne Niyonshuti', 'weekend', 'leaders/Etienne.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(62, 4, 3, 'Alphonse Ndayisenga', 'weekend', 'leaders/Alphonse.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(63, 4, 4, 'Diane Itangishatse', 'weekend', 'leaders/Diane.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(64, 4, 6, 'Alice Uwizeyimana', 'weekend', 'leaders/Alice-Uwizeyimana.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(65, 4, 5, 'Belise Yvette Shimirwa', 'weekend', 'leaders/Belise-Yvette.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(66, 4, 8, 'John Mukunzi', 'weekend', 'leaders/John.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(67, 4, 8, 'Bizimana Rambert', 'weekend', 'leaders/Rambert.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(68, 4, 8, 'Evariste Ntacyombahishe', 'weekend', 'leaders/Evariste.jpg', NULL, 8, 'active', '2026-02-04 21:10:38'),
	(69, 5, 1, 'Evras Iteka', 'both', 'leaders/Evras.jpg', NULL, 1, 'active', '2026-02-04 21:10:38'),
	(70, 5, 3, 'Ishimwe Clémence', 'both', 'leaders/Clemence.jpg', NULL, 2, 'active', '2026-02-04 21:10:38'),
	(71, 5, 4, 'Aimé Divin Nshimiyimana', 'both', 'leaders/Aime-Divin.jpg', NULL, 3, 'active', '2026-02-04 21:10:38'),
	(72, 5, 6, 'Ishimwe David', 'both', 'leaders/David-Ishimwe.jpg', NULL, 4, 'active', '2026-02-04 21:10:38'),
	(73, 5, 5, 'Rachel Mukunde', 'both', 'leaders/Rachel-Mukunde.jpg', NULL, 5, 'active', '2026-02-04 21:10:38'),
	(74, 5, 8, 'Mpakaniye Daniel', 'both', 'leaders/Daniel.jpg', NULL, 6, 'active', '2026-02-04 21:10:38'),
	(75, 5, 10, 'Uwijuru Ikirezi Deborah', 'both', 'leaders/Deborah.jpg', NULL, 7, 'active', '2026-02-04 21:10:38'),
	(76, 6, 1, 'Buzima Dieudonné', 'both', 'leaders/Dieudonne.jpg', NULL, 1, 'active', '2026-02-04 21:10:39'),
	(77, 6, 3, 'Speciose Musabyimana', 'both', 'leaders/Speciose.jpg', NULL, 2, 'active', '2026-02-04 21:10:39'),
	(78, 6, 4, 'Nteziryayo Anastase', 'both', NULL, NULL, 3, 'active', '2026-02-04 21:10:39'),
	(79, 6, 5, 'Mpakaniye Daniel', 'both', 'leaders/Daniel.jpg', NULL, 4, 'active', '2026-02-04 21:10:39'),
	(80, 6, 6, 'Rachel Byukusenge', 'both', NULL, NULL, 5, 'active', '2026-02-04 21:10:39'),
	(81, 6, 9, 'Rutambuka Augustin', 'both', NULL, NULL, 6, 'active', '2026-02-04 21:10:39'),
	(82, 6, 10, 'Tangimpundu Laurence', 'both', NULL, NULL, 7, 'active', '2026-02-04 21:10:39'),
	(83, 7, 1, 'Carine Iradukunda', 'both', 'leaders/Carine.jpg', NULL, 1, 'active', '2026-02-04 21:10:39'),
	(84, 7, 3, 'Speciose Musabyimana', 'both', 'leaders/Speciose.jpg', NULL, 2, 'active', '2026-02-04 21:10:39'),
	(85, 7, 4, 'Buzima Dieudonné', 'both', 'leaders/Dieudonne.jpg', NULL, 3, 'active', '2026-02-04 21:10:39'),
	(86, 7, 5, 'Furaha Claudine', 'both', 'leaders/Furaha.jpg', NULL, 4, 'active', '2026-02-04 21:10:39'),
	(87, 7, 6, 'Rachel Byukusenge', 'both', NULL, NULL, 5, 'active', '2026-02-04 21:10:39'),
	(88, 7, 9, 'Jacqueline Mugeni', 'both', NULL, NULL, 6, 'active', '2026-02-04 21:10:39'),
	(89, 7, 10, 'Jean Wiclef Iryayo', 'both', 'leaders/Wiclef.jpg', NULL, 7, 'active', '2026-02-04 21:10:39'),
	(90, 8, 1, 'Yvan Hirwa', 'both', 'leaders/Yvan.jpg', NULL, 1, 'active', '2026-02-04 21:10:39'),
	(91, 8, 3, 'Carine Iradukunda', 'both', 'leaders/Carine.jpg', NULL, 2, 'active', '2026-02-04 21:10:39'),
	(92, 8, 4, 'Jacqueline Mugeni', 'both', NULL, NULL, 3, 'active', '2026-02-04 21:10:39'),
	(93, 8, 6, 'Francine Uwizeye', 'both', NULL, NULL, 4, 'active', '2026-02-04 21:10:39'),
	(94, 8, 5, 'Thimothee', 'both', NULL, NULL, 5, 'active', '2026-02-04 21:10:39'),
	(95, 9, 1, 'Mukashyaka Ruth', 'both', 'leaders/Ruth.jpg', NULL, 1, 'active', '2026-02-04 21:10:39'),
	(96, 9, 3, 'Nyirabanguka Vestine', 'both', NULL, NULL, 2, 'active', '2026-02-04 21:10:39'),
	(97, 9, 4, 'Ikundabayo Eric', 'both', NULL, NULL, 3, 'active', '2026-02-04 21:10:39'),
	(98, 9, 5, 'Uwanyuze Liliane', 'both', NULL, NULL, 4, 'active', '2026-02-04 21:10:39'),
	(99, 9, 6, 'Niyonsenga Gaspard', 'both', NULL, NULL, 5, 'active', '2026-02-04 21:10:39'),
	(100, 9, 7, 'Rwamuhizi Augustin', 'both', NULL, NULL, 6, 'active', '2026-02-04 21:10:39'),
	(101, 9, 7, 'Mukanyandwi Agnes', 'both', NULL, NULL, 7, 'active', '2026-02-04 21:10:39');

-- Dumping structure for table cep_uokdb.leadership_positions
CREATE TABLE IF NOT EXISTS `leadership_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_name` varchar(100) NOT NULL,
  `position_abbr` varchar(50) DEFAULT NULL,
  `position_level` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.leadership_positions: ~14 rows (approximately)
REPLACE INTO `leadership_positions` (`id`, `position_name`, `position_abbr`, `position_level`, `display_order`, `status`) VALUES
	(1, 'President', 'President', 1, 1, 'active'),
	(2, 'Vice President', 'VP', 2, 2, 'active'),
	(3, 'Vice President (Evangelism)', 'VP Evangelism', 2, 3, 'active'),
	(4, 'Vice President (Social Affairs)', 'VP Social Affairs', 2, 4, 'active'),
	(5, 'Secretary', 'Secretary', 3, 5, 'active'),
	(6, 'Accountant', 'Accountant', 3, 6, 'active'),
	(7, 'Counselor', 'Counselor', 4, 7, 'active'),
	(8, 'Advisor', 'Advisor', 4, 8, 'active'),
	(9, 'Advisor (Discipline)', 'Advisor - Discipline', 4, 9, 'active'),
	(10, 'Advisor (Choir President)', 'Advisor - Choir', 4, 10, 'active'),
	(11, 'Advisor (Protocol)', 'Advisor - Protocol', 4, 11, 'active'),
	(12, 'Advisor (Media & Communication)', 'Advisor - Media', 4, 12, 'active'),
	(13, 'Advisor (Worship Leader)', 'Advisor - Worship', 4, 13, 'active'),
	(14, 'Choir President', 'Choir President', 4, 14, 'active');

-- Dumping structure for table cep_uokdb.leadership_years
CREATE TABLE IF NOT EXISTS `leadership_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year_label` varchar(100) NOT NULL,
  `year_start` int(4) NOT NULL,
  `year_end` int(4) NOT NULL,
  `description` text DEFAULT NULL,
  `has_dual_sessions` tinyint(1) DEFAULT 0,
  `is_current` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.leadership_years: ~9 rows (approximately)
REPLACE INTO `leadership_years` (`id`, `year_label`, `year_start`, `year_end`, `description`, `has_dual_sessions`, `is_current`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Committee 2026-2027', 2026, 2027, 'Current leadership committee serving CEP UoK', 1, 1, 1, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
	(2, 'Committee 2025-2026', 2025, 2026, 'Leadership committee for academic year 2025-2026', 1, 0, 2, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
	(3, 'Committee 2024-2025', 2024, 2025, 'Leadership committee for academic year 2024-2025', 1, 0, 3, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
	(4, 'Committee 2023-2024', 2023, 2024, 'Leadership committee for academic year 2023-2024', 1, 0, 4, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
	(5, 'Committee 2021-2022', 2021, 2022, 'Leadership committee for academic year 2021-2022', 0, 0, 5, 'active', '2026-02-04 21:08:51', '2026-02-05 10:20:14'),
	(6, 'Committee 2019-2021', 2019, 2021, 'Leadership committee for academic years 2019-2021', 0, 0, 6, 'active', '2026-02-04 21:08:51', '2026-02-05 10:20:14'),
	(7, 'Committee 2018-2019', 2018, 2019, 'Leadership committee for academic year 2018-2019', 0, 0, 7, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
	(8, 'Committee 2017-2018', 2017, 2018, 'Leadership committee for academic year 2017-2018', 0, 0, 8, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
	(9, 'Committee 2016-2017', 2016, 2017, 'Leadership committee for academic year 2016-2017', 0, 0, 9, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51');

-- Dumping structure for table cep_uokdb.members
CREATE TABLE IF NOT EXISTS `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'Link to users table for leaders',
  `membership_type_id` int(11) NOT NULL,
  `membership_number` varchar(50) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `year_joined_cep` year(4) NOT NULL,
  `cep_session` enum('day','weekend') NOT NULL DEFAULT 'day' COMMENT 'CEP session: day or weekend',
  `faculty` varchar(100) DEFAULT NULL COMMENT 'Faculty/School at UoK',
  `program` varchar(255) DEFAULT NULL COMMENT 'Program/Course of study',
  `academic_year` varchar(20) DEFAULT NULL COMMENT 'e.g. Year 1, Year 2',
  `church_name` varchar(255) DEFAULT NULL COMMENT 'Church the member attends (free text)',
  `family_id` int(11) DEFAULT NULL COMMENT 'Spiritual family assignment',
  `other_church_name` varchar(255) DEFAULT NULL COMMENT 'If church is Other',
  `is_born_again` enum('Yes','No','Prefer not to say') DEFAULT 'Prefer not to say',
  `is_baptized` enum('Yes','No','Prefer not to say') DEFAULT 'Prefer not to say',
  `profile_photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('pending','active','inactive','suspended') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `unique_phone` (`phone`),
  UNIQUE KEY `membership_number` (`membership_number`),
  KEY `idx_membership_type` (`membership_type_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_year_joined` (`year_joined_cep`),
  KEY `fk_member_approver` (`approved_by`),
  KEY `idx_member_email` (`email`),
  KEY `idx_member_status_year` (`status`,`year_joined_cep`),
  KEY `idx_member_created` (`created_at`),
  KEY `idx_cep_session` (`cep_session`),
  KEY `idx_faculty` (`faculty`),
  KEY `idx_family_id` (`family_id`),
  CONSTRAINT `fk_member_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_member_family` FOREIGN KEY (`family_id`) REFERENCES `cep_families` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_member_membership_type` FOREIGN KEY (`membership_type_id`) REFERENCES `membership_types` (`id`),
  CONSTRAINT `fk_member_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.members: ~11 rows (approximately)
REPLACE INTO `members` (`id`, `user_id`, `membership_type_id`, `membership_number`, `firstname`, `lastname`, `email`, `phone`, `gender`, `date_of_birth`, `address`, `year_joined_cep`, `cep_session`, `faculty`, `program`, `academic_year`, `church_name`, `family_id`, `other_church_name`, `is_born_again`, `is_baptized`, `profile_photo`, `bio`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `last_activity`) VALUES
	(5, 2, 1, 'CEP-D-2026-0005', 'NIYONZIMA', 'Aaron', 'aaronniyonzima52@gmail.com', '0785729794', 'Male', '2000-01-01', 'Remera, Gasabo, Kigali', '2023', 'day', 'Accounting', 'BSc in Finance and Accounting', 'Year 3', 'ADEPR Kimihurura International Service', 1, NULL, 'Yes', 'Yes', 'members/699b5d4edf3e3_1771789646.jpg', 'I loved the way CEP members live together and work together', 'active', 1, '2026-02-27 07:17:16', '2026-02-22 19:47:26', '2026-03-15 14:24:37', NULL),
	(6, NULL, 1, 'CEP-D-2026-0006', 'MUKASHEMA', 'Alice', 'alicemukashema@gmail.com', '0787962735', 'Female', '2001-01-01', 'Kabuga, Kicukiro, Kigali', '2024', 'day', 'Accounting', 'Bsc in finance and Accounting', 'Year 3', 'ADEPR Kimihurura International Service', 2, NULL, 'Yes', 'Yes', 'members/69a0ae4b2b3d4_1772138059.png', 'Always courageous on the Work', 'active', 1, '2026-02-27 08:38:51', '2026-02-26 20:34:19', '2026-03-15 14:24:39', NULL),
	(7, 5, 1, 'CEP-D-2026-0007', 'NTAGAWA', 'David', 'david.ntagawa@gmail.com', '0791619272', 'Male', NULL, 'Karuruma, Gasabo, Kigali', '2024', 'day', 'Law', 'Law', 'Year 2', 'ADEPR Kimihurura International Service', 3, NULL, 'Yes', 'Yes', 'members/69b540fe58d91_1773486334.jpg', 'Always Remain Faithful to God', 'active', 1, '2026-02-27 07:17:39', '2026-02-27 08:17:39', '2026-03-14 11:05:34', NULL),
	(8, NULL, 1, 'CEP-D-2026-0008', 'IRADUKUNDA MBABAZI', 'Eric', 'iradukundaericmbabazi@gmail.com', '0784806931', 'Male', '2002-02-02', 'Gisiment, Gasabo, Kigali', '2025', 'day', 'IT ', 'Bsc in IT', 'Year 2', 'ADEPR Kimihurura International Service', 2, NULL, 'Yes', 'Yes', 'members/69b5409b73eac_1773486235.jpeg', 'Humbleness and Knowing God Every Day', 'active', 1, '2026-02-27 07:29:27', '2026-02-27 08:29:27', '2026-03-14 11:03:55', NULL),
	(9, NULL, 1, 'CEP-D-2026-0009', 'UWUMUKIZA', 'Celine', 'celineuwumukiza@gmail.com', '0728202199', 'Female', '2004-09-05', 'Kanombe, Kicukiro, Kigali', '2024', 'day', 'Law', 'Bsc in Law', 'Year 3', 'ADEPR KACYIRU KANSEREGE', 1, NULL, 'Yes', 'Yes', 'members/69b6c1823170d_1773584770.jpg', 'Always eager to work hard', 'active', 1, '2026-02-27 09:32:46', '2026-02-27 08:51:18', '2026-03-15 14:26:10', NULL),
	(10, NULL, 1, 'CEP-D-2026-0010', 'NSHUTI', 'YVES', 'nshutiyves2015@gmail.com', '0785865752', 'Male', '2006-12-02', 'Gasabo, Kigali', '2024', 'day', 'IT ', 'Bsc Information Technology', 'Year 2', 'ADEPR Kimihurura International Service', 1, NULL, 'Yes', 'Yes', 'members/69a2ece0e470d_1772285152.png', 'A man on Work', 'active', 1, '2026-03-01 06:09:13', '2026-02-28 13:25:52', '2026-03-15 14:24:15', NULL),
	(12, NULL, 1, 'CEP-D-2026-0012', 'ISHIMWE', 'David', 'ishimwe.davd@gmail.com', '0788277646', 'Male', '1997-01-01', 'Kanombe', '2019', 'day', 'Information Technology', 'IT', 'Year 4', 'ADEPR KACYIRU ', 2, NULL, 'Yes', 'Yes', 'members/69add87b83398_1773000827.jpg', NULL, 'active', 5, '2026-03-08 20:17:16', '2026-03-08 20:13:47', '2026-03-08 21:16:22', NULL),
	(13, NULL, 2, 'CEP-D-2026-0013', 'NSHIMIYIMANA', 'Aime Divin', 'aimedivin08@gmail.com', '0784659547', 'Male', '1997-03-08', 'Bugesera, Nyamata', '2019', 'day', 'Law', 'LLB', 'Year 4', 'ADEPR NYAMATA', 1, NULL, 'Yes', 'Yes', 'members/69ade55f9493d_1773004127.jpg', 'Social and serious', 'active', 1, '2026-03-08 21:11:03', '2026-03-08 21:08:47', '2026-03-08 21:15:14', NULL),
	(14, NULL, 2, 'CEP-D-2026-0014', 'ITEKA', 'Evras', 'evrasiteka@gmail.com', '+17125249287', 'Male', '1996-12-05', 'United States', '2020', 'day', 'Information Technology', 'Bsc in IT', 'Year 3', 'ADEPR Kimihurura International Service', 2, NULL, 'Yes', 'Yes', 'members/69b3c55a55728_1773389146.jpg', 'Enthusiastic Leader and creativity on all he do.', 'active', 5, '2026-03-13 17:22:21', '2026-03-13 08:05:46', '2026-03-14 09:27:35', NULL),
	(15, NULL, 2, 'CEP-D-2026-0015', 'TANGIMPUNDU', 'Laurence', 'laurencetangimpundu@gmail.com', '0782215084', 'Female', '1991-01-01', 'Kimironko', '2018', 'day', 'Accounting', 'Bsc in Finance and Accounting', 'Year 4', 'ADEPR KACYIRU KANSEREGE', 1, NULL, 'Yes', 'Yes', NULL, NULL, 'inactive', 5, '2026-03-17 13:13:24', '2026-03-17 13:12:56', '2026-03-17 16:29:56', NULL),
	(16, NULL, 1, 'CEP-D-2026-0016', 'NIYOBUGINGO', 'FIDELE', 'niyobugingo@gmail.com', '0789349702', 'Male', '2026-03-01', 'KACYIRU', '2024', 'day', 'Finance', 'BSc in Finance and Accounting', 'Year 3', 'ADEPR Kimihurura International Service (KIS)', 2, NULL, 'Yes', 'Yes', 'members/69c800797d9d3_1774715001.png', NULL, 'active', 5, '2026-03-28 16:24:28', '2026-03-28 16:23:21', '2026-03-28 16:25:54', NULL);

-- Dumping structure for table cep_uokdb.membership_applications
CREATE TABLE IF NOT EXISTS `membership_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `application_type` enum('new','renewal','update') DEFAULT 'new',
  `status` enum('submitted','under_review','approved','rejected') DEFAULT 'submitted',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `review_date` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewer_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_application` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `fk_application_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_application_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_application_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.membership_applications: ~9 rows (approximately)
REPLACE INTO `membership_applications` (`id`, `member_id`, `application_type`, `status`, `submission_date`, `review_date`, `reviewed_by`, `reviewer_notes`, `rejection_reason`) VALUES
	(1, 5, 'new', 'approved', '2026-02-22 19:47:26', '2026-02-27 07:17:16', 1, NULL, NULL),
	(2, 6, 'new', 'approved', '2026-02-26 20:34:19', '2026-02-27 08:38:51', 1, NULL, NULL),
	(3, 7, 'new', 'approved', '2026-02-27 08:17:39', NULL, NULL, NULL, NULL),
	(4, 8, 'new', 'approved', '2026-02-27 08:29:27', NULL, NULL, NULL, NULL),
	(5, 9, 'new', 'approved', '2026-02-27 08:51:18', NULL, NULL, NULL, NULL),
	(6, 10, 'new', 'approved', '2026-02-28 13:25:52', '2026-03-01 06:09:13', 1, NULL, NULL),
	(8, 12, 'new', 'approved', '2026-03-08 20:13:47', '2026-03-08 20:17:16', 5, NULL, NULL),
	(9, 13, 'new', 'approved', '2026-03-08 21:08:47', '2026-03-08 21:11:03', 1, NULL, NULL),
	(10, 14, 'new', 'approved', '2026-03-13 08:05:46', '2026-03-13 17:22:21', 5, NULL, NULL),
	(11, 15, 'new', 'approved', '2026-03-17 13:12:56', '2026-03-17 13:13:24', 5, NULL, NULL),
	(12, 16, 'new', 'approved', '2026-03-28 16:23:21', '2026-03-28 16:24:28', 5, NULL, NULL);

-- Dumping structure for table cep_uokdb.membership_types
CREATE TABLE IF NOT EXISTS `membership_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.membership_types: ~4 rows (approximately)
REPLACE INTO `membership_types` (`id`, `type_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Current Student & CEP Member', 'Currently enrolled students who are active CEP members', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(2, 'POST CEPiens (Alumni)', 'Former CEP members who have graduated', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(3, 'Frequent Visitor', 'Regular visitors who attend CEP events frequently', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
	(4, 'Donor/Partner', 'Financial supporters and ministry partners of CEP', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01');

-- Dumping structure for table cep_uokdb.member_activities
CREATE TABLE IF NOT EXISTS `member_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `activity_type` enum('registration','login','profile_update','status_change','other') NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member_activity` (`member_id`,`created_at`),
  CONSTRAINT `fk_activity_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.member_activities: ~8 rows (approximately)
REPLACE INTO `member_activities` (`id`, `member_id`, `activity_type`, `activity_description`, `ip_address`, `user_agent`, `created_at`) VALUES
	(2, 5, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 19:47:26'),
	(3, 6, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-26 20:34:19'),
	(4, 7, '', 'Member created by admin', '::1', NULL, '2026-02-27 08:17:39'),
	(5, 8, '', 'Member created by admin', '::1', NULL, '2026-02-27 08:29:27'),
	(6, 9, '', 'Member created by admin', '::1', NULL, '2026-02-27 08:51:18'),
	(7, 10, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 13:25:52'),
	(9, 12, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:13:47'),
	(10, 13, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 21:08:47'),
	(11, 14, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-13 08:05:46'),
	(12, 15, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-17 13:12:56'),
	(13, 16, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-28 16:23:21');

-- Dumping structure for table cep_uokdb.member_talents
CREATE TABLE IF NOT EXISTS `member_talents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `talent_id` int(11) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') DEFAULT 'Intermediate',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member_talent` (`member_id`,`talent_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_talent` (`talent_id`),
  CONSTRAINT `fk_member_talent_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_member_talent_talent` FOREIGN KEY (`talent_id`) REFERENCES `talents_gifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.member_talents: ~82 rows (approximately)
REPLACE INTO `member_talents` (`id`, `member_id`, `talent_id`, `proficiency_level`, `notes`, `created_at`) VALUES
	(1, 5, 11, 'Intermediate', NULL, '2026-02-22 19:47:26'),
	(2, 5, 12, 'Intermediate', NULL, '2026-02-22 19:47:26'),
	(3, 5, 13, 'Intermediate', NULL, '2026-02-22 19:47:26'),
	(4, 5, 14, 'Intermediate', NULL, '2026-02-22 19:47:26'),
	(5, 5, 17, 'Intermediate', NULL, '2026-02-22 19:47:26'),
	(6, 5, 16, 'Intermediate', NULL, '2026-02-22 19:47:26'),
	(7, 6, 6, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(8, 6, 9, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(9, 6, 10, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(10, 6, 11, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(11, 6, 12, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(12, 6, 17, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(13, 6, 15, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(14, 6, 16, 'Intermediate', NULL, '2026-02-26 20:34:19'),
	(15, 7, 2, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(16, 7, 9, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(17, 7, 8, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(18, 7, 10, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(19, 7, 11, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(20, 7, 13, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(21, 7, 14, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(22, 7, 17, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(23, 7, 15, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(24, 7, 16, 'Intermediate', NULL, '2026-02-27 08:17:39'),
	(25, 8, 2, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(26, 8, 1, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(27, 8, 6, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(28, 8, 7, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(29, 8, 9, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(30, 8, 10, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(31, 8, 11, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(32, 8, 12, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(33, 8, 13, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(34, 8, 14, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(35, 8, 17, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(36, 8, 15, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(37, 8, 16, 'Intermediate', NULL, '2026-02-27 08:29:27'),
	(38, 9, 1, 'Intermediate', NULL, '2026-02-27 08:51:18'),
	(39, 9, 8, 'Intermediate', NULL, '2026-02-27 08:51:18'),
	(40, 9, 12, 'Intermediate', NULL, '2026-02-27 08:51:18'),
	(41, 9, 15, 'Intermediate', NULL, '2026-02-27 08:51:18'),
	(42, 9, 16, 'Intermediate', NULL, '2026-02-27 08:51:18'),
	(43, 10, 1, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(44, 10, 6, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(45, 10, 7, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(46, 10, 10, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(47, 10, 11, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(48, 10, 12, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(49, 10, 13, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(50, 10, 14, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(51, 10, 17, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(52, 10, 15, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(53, 10, 16, 'Intermediate', NULL, '2026-02-28 13:25:52'),
	(58, 12, 1, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(59, 12, 3, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(60, 12, 7, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(61, 12, 8, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(62, 12, 10, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(63, 12, 11, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(64, 12, 14, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(65, 12, 17, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(66, 12, 15, 'Intermediate', NULL, '2026-03-08 20:13:47'),
	(67, 13, 1, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(68, 13, 9, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(69, 13, 8, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(70, 13, 10, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(71, 13, 11, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(72, 13, 12, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(73, 13, 14, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(74, 13, 17, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(75, 13, 15, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(76, 13, 16, 'Intermediate', NULL, '2026-03-08 21:08:47'),
	(77, 14, 1, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(78, 14, 4, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(79, 14, 7, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(80, 14, 9, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(81, 14, 8, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(82, 14, 10, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(83, 14, 11, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(84, 14, 13, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(85, 14, 14, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(86, 14, 17, 'Intermediate', NULL, '2026-03-13 08:05:46'),
	(87, 15, 1, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(88, 15, 10, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(89, 15, 11, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(90, 15, 12, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(91, 15, 13, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(92, 15, 14, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(93, 15, 17, 'Intermediate', NULL, '2026-03-17 13:12:56'),
	(94, 16, 7, 'Intermediate', NULL, '2026-03-28 16:23:21'),
	(95, 16, 10, 'Intermediate', NULL, '2026-03-28 16:23:21'),
	(96, 16, 11, 'Intermediate', NULL, '2026-03-28 16:23:21'),
	(97, 16, 12, 'Intermediate', NULL, '2026-03-28 16:23:21'),
	(98, 16, 13, 'Intermediate', NULL, '2026-03-28 16:23:21'),
	(99, 16, 14, 'Intermediate', NULL, '2026-03-28 16:23:21'),
	(100, 16, 17, 'Intermediate', NULL, '2026-03-28 16:23:21');

-- Dumping structure for table cep_uokdb.news_events
CREATE TABLE IF NOT EXISTS `news_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `description` longtext NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `category` enum('news','event','announcement','achievement') DEFAULT 'news',
  `author` varchar(100) DEFAULT NULL,
  `published_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `featured` tinyint(1) DEFAULT 0,
  `event_location` varchar(255) DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `published_date` (`published_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.news_events: ~4 rows (approximately)
REPLACE INTO `news_events` (`id`, `title`, `excerpt`, `description`, `image_url`, `thumbnail_url`, `category`, `author`, `published_date`, `end_date`, `status`, `views`, `created_at`, `updated_at`, `featured`, `event_location`, `event_time`) VALUES
	(1, 'Welcome to New Academic Year 2025', 'CEP UoK kicks off the new academic year with renewed vision and purpose', '<p>As we begin this new academic year, CEP UoK welcomes all students to join our fellowship. Whether you\'re a returning member or new to campus, there\'s a place for you in our community.</p>', '/img/news/new-year-2025.jpg', NULL, 'news', NULL, '2025-01-15', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 1, NULL, NULL),
	(2, 'Annual Conference 2025', 'Join us for our biggest gathering of the year - CEP UoK Annual Conference', '<p>Save the date for our Annual Conference! Three days of powerful worship, teaching, fellowship, and ministry. Registration opens soon.</p>', '/img/news/conference-2025.jpg', NULL, 'event', NULL, '2025-02-20', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 1, NULL, NULL),
	(3, 'Campus Evangelism Week', 'Reaching the campus with the love of Christ', '<p>This week, CEP members will be engaging in intensive evangelism across both campuses. Join us as we share the gospel through personal conversations, worship, and testimonies.</p>', '/img/news/evangelism-week.jpg', NULL, 'news', NULL, '2025-01-22', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 0, NULL, NULL),
	(4, 'Leadership Training Workshop', 'Developing the next generation of Christian leaders', '<p>A special workshop for all department heads and aspiring leaders. Learn practical skills in leadership, team management, and spiritual formation.</p>', '/img/news/leadership-training.jpg', NULL, 'event', NULL, '2025-01-28', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 0, NULL, NULL);

-- Dumping structure for table cep_uokdb.page_content
CREATE TABLE IF NOT EXISTS `page_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_section_unique` (`page_name`,`section_name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.page_content: ~23 rows (approximately)
REPLACE INTO `page_content` (`id`, `page_name`, `section_name`, `title`, `content`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'home', 'about_title', NULL, 'Building Christ-Centered Leaders at the University of Kigali', NULL, 1, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(2, 'home', 'about_description', NULL, 'CEP–UoK (Communauté des Étudiants Pentecôtistes à l\'Université de Kigali) is a Christian students\' fellowship that brings together university students who desire to grow spiritually, live out their faith, and serve God within the academic environment.', NULL, 2, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(3, 'home', 'about_vision', NULL, 'To raise Christ-centered leaders who honor God, uphold biblical values, and positively influence the Church, the University, and society.', NULL, 3, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(4, 'home', 'about_feature1_icon', NULL, 'fas fa-praying-hands', NULL, 4, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(5, 'home', 'about_feature1_title', NULL, 'Spiritual Growth', NULL, 5, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(6, 'home', 'about_feature1_desc', NULL, 'We nurture students through prayer, worship, biblical teaching, and discipleship, creating a supportive community of faith.', NULL, 6, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(7, 'home', 'about_feature2_icon', NULL, 'fas fa-hands-helping', NULL, 7, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(8, 'home', 'about_feature2_title', NULL, 'Unity & Service', NULL, 8, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(9, 'home', 'about_feature2_desc', NULL, 'We foster unity among believers and impact the university community through evangelism, outreach, and acts of love.', NULL, 9, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(10, 'home', 'welcome_video', NULL, 'https://www.youtube.com/embed/NZI3j_XpgWM', NULL, 10, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(11, 'home', 'history_video', NULL, 'https://www.youtube.com/embed/DaGMZsmDKBU', NULL, 11, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(12, 'home', 'history_title', NULL, 'Discover Our Journey', NULL, 12, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(13, 'home', 'history_description', NULL, 'Journey through the remarkable history of CEP UoK, from our humble beginnings in 2016 to becoming a vibrant community of faith at the University of Kigali. Witness God\'s faithfulness through testimonies, milestones, and the transformative impact of student-led ministry.', NULL, 13, 'active', '2026-01-29 15:00:44', '2026-02-01 13:29:41'),
	(14, 'about_cep', 'hero_title', NULL, 'About CEP UoK', NULL, 1, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(15, 'about_cep', 'hero_subtitle', NULL, 'Communauté des Étudiants Pentecôtistes à l\'Université de Kigali', NULL, 2, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(16, 'about_cep', 'hero_verse', NULL, '"For where two or three gather in my name, there am I with them."', NULL, 3, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(17, 'about_cep', 'hero_verse_ref', NULL, '— Matthew 18:20', NULL, 4, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(18, 'about_cep', 'who_title', NULL, 'Who We Are', NULL, 5, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(19, 'about_cep', 'who_content', NULL, '<p>CEP–UoK (Communauté des Étudiants Pentecôtistes à l\'Université de Kigali) is a Christian students\' fellowship that brings together university students who desire to grow spiritually, live according to biblical values, and serve God within the academic environment of the University of Kigali.</p><p>CEP–UoK exists as a platform for spiritual formation, leadership development, fellowship, and holistic empowerment of students, equipping them to impact the Church, the University, and society at large.</p>', NULL, 6, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(20, 'about_cep', 'who_image', NULL, '', '/img/about/who-we-are.jpg', 7, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(21, 'about_cep', 'vision', NULL, 'To raise Christ-centered leaders who honor God, uphold biblical values, and positively influence the Church, the University, and society.', NULL, 8, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(22, 'about_cep', 'mission_intro', NULL, 'CEP–UoK\'s mission is to nurture students spiritually and holistically by equipping them to live out their Christian faith with responsibility, leadership, and impact.', NULL, 9, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
	(23, 'about_cep', 'affiliation', NULL, 'CEP–UoK operates under the spiritual supervision of <strong>ADEPR Kimihurura International Service (Local Church)</strong> and functions in full compliance with:', NULL, 10, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40');

-- Dumping structure for table cep_uokdb.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `otp` varchar(6) DEFAULT NULL COMMENT '6-digit OTP',
  `email` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token` (`token`),
  KEY `idx_email` (`email`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `fk_reset_token_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL COMMENT 'Module name (e.g., membership, news, gallery)',
  `action` varchar(100) NOT NULL COMMENT 'Action name (e.g., view, create, edit, delete)',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_action` (`module`,`action`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.permissions: ~91 rows (approximately)
REPLACE INTO `permissions` (`id`, `module`, `action`, `description`, `created_at`) VALUES
	(1, 'dashboard', 'view', 'View dashboard', '2026-02-13 15:14:22'),
	(2, 'dashboard', 'admin_access', 'Access admin dashboard', '2026-02-13 15:14:22'),
	(3, 'users', 'view', 'View users', '2026-02-13 15:14:22'),
	(4, 'users', 'create', 'Create new users', '2026-02-13 15:14:22'),
	(5, 'users', 'edit', 'Edit user information', '2026-02-13 15:14:22'),
	(6, 'users', 'delete', 'Delete users', '2026-02-13 15:14:22'),
	(7, 'users', 'change_role', 'Change user roles', '2026-02-13 15:14:22'),
	(8, 'users', 'manage_permissions', 'Manage user permissions', '2026-02-13 15:14:22'),
	(9, 'roles', 'view', 'View roles', '2026-02-13 15:14:22'),
	(10, 'roles', 'create', 'Create new roles', '2026-02-13 15:14:22'),
	(11, 'roles', 'edit', 'Edit roles', '2026-02-13 15:14:22'),
	(12, 'roles', 'delete', 'Delete roles', '2026-02-13 15:14:22'),
	(13, 'roles', 'assign_permissions', 'Assign permissions to roles', '2026-02-13 15:14:22'),
	(14, 'membership', 'view', 'View membership records', '2026-02-13 15:14:22'),
	(15, 'membership', 'create', 'Create new membership records', '2026-02-13 15:14:22'),
	(16, 'membership', 'edit', 'Edit membership records', '2026-02-13 15:14:22'),
	(17, 'membership', 'delete', 'Delete membership records', '2026-02-13 15:14:22'),
	(18, 'membership', 'approve', 'Approve membership applications', '2026-02-13 15:14:22'),
	(19, 'membership', 'export', 'Export membership data', '2026-02-13 15:14:22'),
	(20, 'membership', 'manage_types', 'Manage membership types', '2026-02-13 15:14:22'),
	(21, 'membership', 'manage_churches', 'Manage churches list', '2026-02-13 15:14:22'),
	(22, 'membership', 'manage_talents', 'Manage talents/gifts list', '2026-02-13 15:14:22'),
	(23, 'news', 'view', 'View news articles', '2026-02-13 15:14:22'),
	(24, 'news', 'create', 'Create news articles', '2026-02-13 15:14:22'),
	(25, 'news', 'edit', 'Edit news articles', '2026-02-13 15:14:22'),
	(26, 'news', 'delete', 'Delete news articles', '2026-02-13 15:14:22'),
	(27, 'news', 'publish', 'Publish news articles', '2026-02-13 15:14:22'),
	(28, 'gallery', 'view', 'View gallery', '2026-02-13 15:14:22'),
	(29, 'gallery', 'upload', 'Upload images to gallery', '2026-02-13 15:14:22'),
	(30, 'gallery', 'edit', 'Edit gallery images', '2026-02-13 15:14:22'),
	(31, 'gallery', 'delete', 'Delete gallery images', '2026-02-13 15:14:22'),
	(32, 'videos', 'view', 'View videos', '2026-02-13 15:14:22'),
	(33, 'videos', 'upload', 'Upload videos', '2026-02-13 15:14:22'),
	(34, 'videos', 'edit', 'Edit videos', '2026-02-13 15:14:22'),
	(35, 'videos', 'delete', 'Delete videos', '2026-02-13 15:14:22'),
	(36, 'events', 'view', 'View events', '2026-02-13 15:14:22'),
	(37, 'events', 'create', 'Create events', '2026-02-13 15:14:22'),
	(38, 'events', 'edit', 'Edit events', '2026-02-13 15:14:22'),
	(39, 'events', 'delete', 'Delete events', '2026-02-13 15:14:22'),
	(40, 'programs', 'view', 'View programs', '2026-02-13 15:14:22'),
	(41, 'programs', 'create', 'Create programs', '2026-02-13 15:14:22'),
	(42, 'programs', 'edit', 'Edit programs', '2026-02-13 15:14:22'),
	(43, 'programs', 'delete', 'Delete programs', '2026-02-13 15:14:22'),
	(44, 'leadership', 'view', 'View leadership information', '2026-02-13 15:14:22'),
	(45, 'leadership', 'edit', 'Edit leadership information', '2026-02-13 15:14:22'),
	(46, 'testimonials', 'view', 'View testimonials', '2026-02-13 15:14:22'),
	(47, 'testimonials', 'create', 'Create testimonials', '2026-02-13 15:14:22'),
	(48, 'testimonials', 'edit', 'Edit testimonials', '2026-02-13 15:14:22'),
	(49, 'testimonials', 'delete', 'Delete testimonials', '2026-02-13 15:14:22'),
	(50, 'testimonials', 'approve', 'Approve testimonials', '2026-02-13 15:14:22'),
	(51, 'messages', 'view', 'View contact messages', '2026-02-13 15:14:22'),
	(52, 'messages', 'reply', 'Reply to messages', '2026-02-13 15:14:22'),
	(53, 'messages', 'delete', 'Delete messages', '2026-02-13 15:14:22'),
	(54, 'settings', 'view', 'View settings', '2026-02-13 15:14:22'),
	(55, 'settings', 'edit', 'Edit settings', '2026-02-13 15:14:22'),
	(56, 'reports', 'view', 'View reports', '2026-02-13 15:14:22'),
	(57, 'reports', 'export', 'Export reports', '2026-02-13 15:14:22'),
	(58, 'reports', 'create', 'Create custom reports', '2026-02-13 15:14:22'),
	(68, 'sessions', 'view', 'View session settings', '2026-02-22 15:12:55'),
	(69, 'sessions', 'manage', 'Manage portal sessions & access', '2026-02-22 15:12:55'),
	(70, 'sessions', 'lock', 'Lock/unlock portal sessions', '2026-02-22 15:12:55'),
	(71, 'families', 'view', 'View spiritual families', '2026-02-22 15:12:55'),
	(72, 'families', 'create', 'Create spiritual families', '2026-02-22 15:12:55'),
	(73, 'families', 'edit', 'Edit spiritual families', '2026-02-22 15:12:55'),
	(74, 'families', 'delete', 'Delete spiritual families', '2026-02-22 15:12:55'),
	(75, 'families', 'assign', 'Assign members to families', '2026-02-22 15:12:55'),
	(76, 'supporters', 'view', 'View supporters', '2026-02-22 15:12:55'),
	(77, 'supporters', 'create', 'Add new supporters', '2026-02-22 15:12:55'),
	(78, 'supporters', 'edit', 'Edit supporter information', '2026-02-22 15:12:55'),
	(79, 'supporters', 'delete', 'Delete supporters', '2026-02-22 15:12:55'),
	(80, 'supporters', 'contributions', 'Record contributions', '2026-02-22 15:12:55'),
	(81, 'finance', 'view', 'View financial records', '2026-02-22 15:12:55'),
	(82, 'finance', 'record_revenue', 'Record revenue/offerings', '2026-02-22 15:12:55'),
	(83, 'finance', 'manage_budget', 'Create and manage budgets', '2026-02-22 15:12:55'),
	(84, 'finance', 'fund_requests', 'Submit fund requests', '2026-02-22 15:12:55'),
	(85, 'finance', 'approve_funds', 'Approve fund requests', '2026-02-22 15:12:55'),
	(86, 'finance', 'disburse_funds', 'Disburse approved funds', '2026-02-22 15:12:55'),
	(87, 'finance', 'reports', 'Generate financial reports', '2026-02-22 15:12:55'),
	(88, 'departments', 'view', 'View department info', '2026-02-22 15:12:55'),
	(89, 'departments', 'manage_activities', 'Manage department activities', '2026-02-22 15:12:55'),
	(90, 'departments', 'manage_budget', 'Manage department budget', '2026-02-22 15:12:55'),
	(91, 'choir', 'view', 'View choir module', '2026-02-22 15:12:55'),
	(92, 'choir', 'manage_members', 'Manage choir members', '2026-02-22 15:12:55'),
	(93, 'choir', 'manage_songs', 'Manage choir repertoire', '2026-02-22 15:12:55'),
	(94, 'choir', 'manage_attendance', 'Track choir attendance', '2026-02-22 15:12:55'),
	(95, 'projects', 'view', 'View projects', '2026-02-22 15:12:55'),
	(96, 'projects', 'create', 'Create new projects', '2026-02-22 15:12:55'),
	(97, 'projects', 'edit', 'Edit projects', '2026-02-22 15:12:55'),
	(98, 'projects', 'manage_tasks', 'Manage project tasks', '2026-02-22 15:12:55'),
	(99, 'handover', 'view', 'View handover documents', '2026-02-22 15:12:55'),
	(100, 'handover', 'create', 'Create handover reports', '2026-02-22 15:12:55'),
	(101, 'wallet', 'view', 'View Credentials wallet', '2026-02-28 07:56:52'),
	(102, 'wallet', 'create', 'Create new Credential in Wallet', '2026-02-28 07:56:52'),
	(103, 'wallet', 'edit', 'Edit Credential Wallet information', '2026-02-28 07:56:52'),
	(104, 'wallet', 'delete', 'Delete Credentials From Wallet', '2026-02-28 07:56:52'),
	(105, 'finance', 'edit_revenue', 'Edit revenue records', '2026-03-07 09:18:15'),
	(106, 'finance', 'delete_revenue', 'Delete revenue records', '2026-03-07 09:18:15'),
	(107, 'finance', 'manage_indicators', 'Manage budget indicators', '2026-03-08 16:23:13');

-- Dumping structure for table cep_uokdb.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cep_session` enum('day','weekend','both') NOT NULL DEFAULT 'both',
  `project_code` varchar(30) DEFAULT NULL COMMENT 'e.g. PROJ-2026-001',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('evangelism','social','fundraising','infrastructure','training','other') DEFAULT 'other',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT 0.00,
  `spent` decimal(15,2) DEFAULT 0.00,
  `status` enum('planning','active','on_hold','completed','cancelled') DEFAULT 'planning',
  `progress` tinyint(3) DEFAULT 0 COMMENT '0-100 percent',
  `lead_user_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_code` (`project_code`),
  KEY `idx_session` (`cep_session`),
  KEY `idx_status` (`status`),
  KEY `fk_proj_lead` (`lead_user_id`),
  KEY `fk_proj_creator` (`created_by`),
  CONSTRAINT `fk_proj_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_proj_lead` FOREIGN KEY (`lead_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.projects: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.project_tasks
CREATE TABLE IF NOT EXISTS `project_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('todo','in_progress','done','blocked') DEFAULT 'todo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project_id`),
  KEY `idx_assigned` (`assigned_to`),
  CONSTRAINT `fk_task_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.project_tasks: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.project_updates
CREATE TABLE IF NOT EXISTS `project_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `update_text` text NOT NULL,
  `progress` tinyint(3) DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project_id`),
  KEY `fk_update_user` (`posted_by`),
  CONSTRAINT `fk_update_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_update_user` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.project_updates: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.quick_stats
CREATE TABLE IF NOT EXISTS `quick_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_name` varchar(100) NOT NULL,
  `stat_value` varchar(100) NOT NULL,
  `stat_label` varchar(255) NOT NULL,
  `stat_icon` varchar(100) DEFAULT 'fas fa-star',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_name_unique` (`stat_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.quick_stats: ~9 rows (approximately)
REPLACE INTO `quick_stats` (`id`, `stat_name`, `stat_value`, `stat_label`, `stat_icon`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'years_service', '10+', 'Years of Service', 'fas fa-calendar-alt', 1, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(2, 'post_cepiens', '500+', 'Post CEPiens', 'fas fa-users', 2, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(3, 'day_members', '80', 'Day Members', 'fas fa-user-friends', 3, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(4, 'weekend_members', '120', 'Weekend Members', 'fas fa-user-clock', 4, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(5, 'fellowship_services', '4', 'Fellowship Services', 'fas fa-church', 5, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(6, 'sessions', '2', 'Sessions', 'fas fa-layer-group', 6, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(7, 'campuses', '2', 'Campuses', 'fas fa-map-marker-alt', 7, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(8, 'choir', '1', 'Choir', 'fas fa-music', 8, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
	(9, 'worship_team', '1', 'Worship Team', 'fas fa-guitar', 9, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44');

-- Dumping structure for table cep_uokdb.recurring_events
CREATE TABLE IF NOT EXISTS `recurring_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `campus` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `event_type` varchar(100) DEFAULT 'Fellowship',
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `day_of_week` (`day_of_week`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.recurring_events: ~4 rows (approximately)
REPLACE INTO `recurring_events` (`id`, `title`, `description`, `day_of_week`, `campus`, `start_time`, `end_time`, `event_type`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'English Service', 'Join us for English fellowship with worship, teaching, and prayer', 'Monday', 'Kacyiru Campus', '11:30:00', '13:00:00', 'Fellowship', '/img/events/english-service.jpg', 1, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(2, 'Kinyarwanda Fellowship (Amateraniro)', 'Amateraniro y\'igifaransa kuri campus ya Kacyiru', 'Wednesday', 'Kacyiru Campus', '11:30:00', '13:00:00', 'Fellowship', '/img/events/wednesday-fellowship.jpg', 2, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(3, 'Kinyarwanda Fellowship (Amateraniro)', 'Amateraniro y\'igifaransa kuri campus ya Remera', 'Thursday', 'Remera Campus', '11:30:00', '13:00:00', 'Fellowship', '/img/events/thursday-fellowship.jpg', 3, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(4, 'Sunday Service (Amateraniro)', 'Weekend fellowship with extended worship and ministry time', 'Sunday', 'Kacyiru Campus', '14:00:00', '15:30:00', 'Fellowship', '/img/events/sunday-service.jpg', 4, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45');

-- Dumping structure for table cep_uokdb.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT 0 COMMENT '1 = Super Admin with full access',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.roles: ~10 rows (approximately)
REPLACE INTO `roles` (`id`, `name`, `description`, `is_super_admin`, `created_at`, `updated_at`) VALUES
	(1, 'Super Admin', 'Full system access - System Administrator', 1, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
	(2, 'President', 'CEP Session President - Full session access', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
	(3, 'VP Evangelism', 'Vice President of Evangelism & Prayers', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
	(4, 'VP Social Affairs', 'Vice President of Social Affairs', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
	(5, 'Secretary', 'Session Secretary', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
	(6, 'Accountant', 'Session Accountant', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
	(7, 'Advisor', 'Session Advisor', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
	(8, 'Choir President', 'Choir Department President', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
	(9, 'Department Head', 'Head of a CEP Department', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
	(10, 'Committee Member', 'General committee member', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
	(12, 'Post Cepien', 'CEP Alumni', 0, '2026-03-08 21:21:45', '2026-03-08 21:21:45');

-- Dumping structure for table cep_uokdb.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_permission_id` (`permission_id`),
  CONSTRAINT `fk_role_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=931 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.role_permissions: ~221 rows (approximately)
REPLACE INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
	(95, 3, 1, '2026-02-13 15:14:22'),
	(96, 3, 37, '2026-02-13 15:14:22'),
	(97, 3, 38, '2026-02-13 15:14:22'),
	(98, 3, 36, '2026-02-13 15:14:22'),
	(99, 3, 15, '2026-02-13 15:14:22'),
	(100, 3, 16, '2026-02-13 15:14:22'),
	(101, 3, 14, '2026-02-13 15:14:22'),
	(102, 3, 51, '2026-02-13 15:14:22'),
	(103, 3, 24, '2026-02-13 15:14:22'),
	(104, 3, 25, '2026-02-13 15:14:22'),
	(105, 3, 23, '2026-02-13 15:14:22'),
	(110, 4, 1, '2026-02-13 15:14:23'),
	(111, 4, 36, '2026-02-13 15:14:23'),
	(112, 4, 23, '2026-02-13 15:14:23'),
	(113, 4, 40, '2026-02-13 15:14:23'),
	(117, 5, 1, '2026-02-13 15:14:23'),
	(118, 5, 36, '2026-02-13 15:14:23'),
	(119, 5, 28, '2026-02-13 15:14:23'),
	(120, 5, 23, '2026-02-13 15:14:23'),
	(121, 5, 32, '2026-02-13 15:14:23'),
	(655, 1, 94, '2026-03-08 16:24:16'),
	(656, 1, 92, '2026-03-08 16:24:16'),
	(657, 1, 93, '2026-03-08 16:24:16'),
	(658, 1, 91, '2026-03-08 16:24:16'),
	(659, 1, 2, '2026-03-08 16:24:16'),
	(660, 1, 1, '2026-03-08 16:24:16'),
	(661, 1, 37, '2026-03-08 16:24:16'),
	(662, 1, 39, '2026-03-08 16:24:16'),
	(663, 1, 38, '2026-03-08 16:24:16'),
	(664, 1, 36, '2026-03-08 16:24:16'),
	(665, 1, 75, '2026-03-08 16:24:16'),
	(666, 1, 72, '2026-03-08 16:24:16'),
	(667, 1, 74, '2026-03-08 16:24:16'),
	(668, 1, 73, '2026-03-08 16:24:16'),
	(669, 1, 71, '2026-03-08 16:24:16'),
	(670, 1, 85, '2026-03-08 16:24:16'),
	(671, 1, 106, '2026-03-08 16:24:16'),
	(672, 1, 86, '2026-03-08 16:24:16'),
	(673, 1, 105, '2026-03-08 16:24:16'),
	(674, 1, 84, '2026-03-08 16:24:16'),
	(675, 1, 83, '2026-03-08 16:24:16'),
	(676, 1, 107, '2026-03-08 16:24:16'),
	(677, 1, 82, '2026-03-08 16:24:16'),
	(678, 1, 87, '2026-03-08 16:24:16'),
	(679, 1, 81, '2026-03-08 16:24:16'),
	(680, 1, 31, '2026-03-08 16:24:16'),
	(681, 1, 30, '2026-03-08 16:24:16'),
	(682, 1, 29, '2026-03-08 16:24:16'),
	(683, 1, 28, '2026-03-08 16:24:16'),
	(684, 1, 100, '2026-03-08 16:24:16'),
	(685, 1, 99, '2026-03-08 16:24:16'),
	(686, 1, 45, '2026-03-08 16:24:16'),
	(687, 1, 44, '2026-03-08 16:24:16'),
	(688, 1, 18, '2026-03-08 16:24:16'),
	(689, 1, 15, '2026-03-08 16:24:16'),
	(690, 1, 17, '2026-03-08 16:24:16'),
	(691, 1, 16, '2026-03-08 16:24:16'),
	(692, 1, 19, '2026-03-08 16:24:16'),
	(693, 1, 21, '2026-03-08 16:24:16'),
	(694, 1, 22, '2026-03-08 16:24:16'),
	(695, 1, 20, '2026-03-08 16:24:16'),
	(696, 1, 14, '2026-03-08 16:24:16'),
	(697, 1, 53, '2026-03-08 16:24:16'),
	(698, 1, 52, '2026-03-08 16:24:16'),
	(699, 1, 51, '2026-03-08 16:24:16'),
	(700, 1, 24, '2026-03-08 16:24:16'),
	(701, 1, 26, '2026-03-08 16:24:16'),
	(702, 1, 25, '2026-03-08 16:24:16'),
	(703, 1, 27, '2026-03-08 16:24:16'),
	(704, 1, 23, '2026-03-08 16:24:16'),
	(705, 1, 41, '2026-03-08 16:24:16'),
	(706, 1, 43, '2026-03-08 16:24:16'),
	(707, 1, 42, '2026-03-08 16:24:16'),
	(708, 1, 40, '2026-03-08 16:24:16'),
	(709, 1, 96, '2026-03-08 16:24:16'),
	(710, 1, 97, '2026-03-08 16:24:16'),
	(711, 1, 98, '2026-03-08 16:24:16'),
	(712, 1, 95, '2026-03-08 16:24:16'),
	(713, 1, 58, '2026-03-08 16:24:16'),
	(714, 1, 57, '2026-03-08 16:24:16'),
	(715, 1, 56, '2026-03-08 16:24:16'),
	(716, 1, 13, '2026-03-08 16:24:16'),
	(717, 1, 10, '2026-03-08 16:24:16'),
	(718, 1, 12, '2026-03-08 16:24:16'),
	(719, 1, 11, '2026-03-08 16:24:16'),
	(720, 1, 9, '2026-03-08 16:24:16'),
	(721, 1, 70, '2026-03-08 16:24:16'),
	(722, 1, 69, '2026-03-08 16:24:16'),
	(723, 1, 68, '2026-03-08 16:24:16'),
	(724, 1, 55, '2026-03-08 16:24:16'),
	(725, 1, 54, '2026-03-08 16:24:16'),
	(726, 1, 80, '2026-03-08 16:24:16'),
	(727, 1, 77, '2026-03-08 16:24:16'),
	(728, 1, 79, '2026-03-08 16:24:16'),
	(729, 1, 78, '2026-03-08 16:24:16'),
	(730, 1, 76, '2026-03-08 16:24:16'),
	(731, 1, 50, '2026-03-08 16:24:16'),
	(732, 1, 47, '2026-03-08 16:24:16'),
	(733, 1, 49, '2026-03-08 16:24:16'),
	(734, 1, 48, '2026-03-08 16:24:16'),
	(735, 1, 46, '2026-03-08 16:24:16'),
	(736, 1, 7, '2026-03-08 16:24:16'),
	(737, 1, 4, '2026-03-08 16:24:16'),
	(738, 1, 6, '2026-03-08 16:24:16'),
	(739, 1, 5, '2026-03-08 16:24:16'),
	(740, 1, 8, '2026-03-08 16:24:16'),
	(741, 1, 3, '2026-03-08 16:24:16'),
	(742, 1, 35, '2026-03-08 16:24:16'),
	(743, 1, 34, '2026-03-08 16:24:16'),
	(744, 1, 33, '2026-03-08 16:24:16'),
	(745, 1, 32, '2026-03-08 16:24:16'),
	(746, 1, 102, '2026-03-08 16:24:16'),
	(747, 1, 104, '2026-03-08 16:24:16'),
	(748, 1, 103, '2026-03-08 16:24:16'),
	(749, 1, 101, '2026-03-08 16:24:16'),
	(825, 6, 91, '2026-03-20 21:40:17'),
	(826, 6, 37, '2026-03-20 21:40:17'),
	(827, 6, 39, '2026-03-20 21:40:17'),
	(828, 6, 38, '2026-03-20 21:40:17'),
	(829, 6, 36, '2026-03-20 21:40:17'),
	(830, 6, 85, '2026-03-20 21:40:17'),
	(831, 6, 106, '2026-03-20 21:40:17'),
	(832, 6, 86, '2026-03-20 21:40:17'),
	(833, 6, 105, '2026-03-20 21:40:17'),
	(834, 6, 84, '2026-03-20 21:40:17'),
	(835, 6, 83, '2026-03-20 21:40:17'),
	(836, 6, 82, '2026-03-20 21:40:17'),
	(837, 6, 87, '2026-03-20 21:40:17'),
	(838, 6, 81, '2026-03-20 21:40:17'),
	(839, 6, 15, '2026-03-20 21:40:17'),
	(840, 6, 16, '2026-03-20 21:40:17'),
	(841, 6, 19, '2026-03-20 21:40:17'),
	(842, 6, 14, '2026-03-20 21:40:17'),
	(843, 6, 96, '2026-03-20 21:40:17'),
	(844, 6, 97, '2026-03-20 21:40:17'),
	(845, 6, 98, '2026-03-20 21:40:17'),
	(846, 6, 95, '2026-03-20 21:40:17'),
	(847, 6, 58, '2026-03-20 21:40:17'),
	(848, 6, 57, '2026-03-20 21:40:17'),
	(849, 6, 56, '2026-03-20 21:40:17'),
	(850, 6, 80, '2026-03-20 21:40:17'),
	(851, 6, 77, '2026-03-20 21:40:17'),
	(852, 6, 79, '2026-03-20 21:40:17'),
	(853, 6, 78, '2026-03-20 21:40:17'),
	(854, 6, 76, '2026-03-20 21:40:17'),
	(855, 2, 91, '2026-03-28 16:41:30'),
	(856, 2, 1, '2026-03-28 16:41:30'),
	(857, 2, 89, '2026-03-28 16:41:30'),
	(858, 2, 90, '2026-03-28 16:41:30'),
	(859, 2, 88, '2026-03-28 16:41:30'),
	(860, 2, 37, '2026-03-28 16:41:30'),
	(861, 2, 39, '2026-03-28 16:41:30'),
	(862, 2, 38, '2026-03-28 16:41:30'),
	(863, 2, 36, '2026-03-28 16:41:30'),
	(864, 2, 75, '2026-03-28 16:41:30'),
	(865, 2, 72, '2026-03-28 16:41:30'),
	(866, 2, 73, '2026-03-28 16:41:30'),
	(867, 2, 71, '2026-03-28 16:41:30'),
	(868, 2, 85, '2026-03-28 16:41:30'),
	(869, 2, 86, '2026-03-28 16:41:30'),
	(870, 2, 105, '2026-03-28 16:41:30'),
	(871, 2, 84, '2026-03-28 16:41:30'),
	(872, 2, 83, '2026-03-28 16:41:30'),
	(873, 2, 107, '2026-03-28 16:41:30'),
	(874, 2, 82, '2026-03-28 16:41:30'),
	(875, 2, 87, '2026-03-28 16:41:30'),
	(876, 2, 81, '2026-03-28 16:41:30'),
	(877, 2, 30, '2026-03-28 16:41:30'),
	(878, 2, 28, '2026-03-28 16:41:30'),
	(879, 2, 100, '2026-03-28 16:41:30'),
	(880, 2, 99, '2026-03-28 16:41:30'),
	(881, 2, 45, '2026-03-28 16:41:30'),
	(882, 2, 44, '2026-03-28 16:41:30'),
	(883, 2, 18, '2026-03-28 16:41:30'),
	(884, 2, 15, '2026-03-28 16:41:30'),
	(885, 2, 17, '2026-03-28 16:41:30'),
	(886, 2, 16, '2026-03-28 16:41:30'),
	(887, 2, 19, '2026-03-28 16:41:30'),
	(888, 2, 21, '2026-03-28 16:41:30'),
	(889, 2, 22, '2026-03-28 16:41:30'),
	(890, 2, 20, '2026-03-28 16:41:30'),
	(891, 2, 14, '2026-03-28 16:41:30'),
	(892, 2, 53, '2026-03-28 16:41:30'),
	(893, 2, 52, '2026-03-28 16:41:30'),
	(894, 2, 51, '2026-03-28 16:41:30'),
	(895, 2, 24, '2026-03-28 16:41:30'),
	(896, 2, 25, '2026-03-28 16:41:30'),
	(897, 2, 27, '2026-03-28 16:41:30'),
	(898, 2, 23, '2026-03-28 16:41:30'),
	(899, 2, 41, '2026-03-28 16:41:30'),
	(900, 2, 42, '2026-03-28 16:41:30'),
	(901, 2, 40, '2026-03-28 16:41:30'),
	(902, 2, 96, '2026-03-28 16:41:30'),
	(903, 2, 97, '2026-03-28 16:41:30'),
	(904, 2, 98, '2026-03-28 16:41:30'),
	(905, 2, 95, '2026-03-28 16:41:30'),
	(906, 2, 58, '2026-03-28 16:41:30'),
	(907, 2, 57, '2026-03-28 16:41:30'),
	(908, 2, 56, '2026-03-28 16:41:30'),
	(909, 2, 13, '2026-03-28 16:41:30'),
	(910, 2, 10, '2026-03-28 16:41:30'),
	(911, 2, 11, '2026-03-28 16:41:30'),
	(912, 2, 9, '2026-03-28 16:41:30'),
	(913, 2, 80, '2026-03-28 16:41:30'),
	(914, 2, 77, '2026-03-28 16:41:30'),
	(915, 2, 79, '2026-03-28 16:41:30'),
	(916, 2, 78, '2026-03-28 16:41:30'),
	(917, 2, 76, '2026-03-28 16:41:30'),
	(918, 2, 50, '2026-03-28 16:41:30'),
	(919, 2, 47, '2026-03-28 16:41:30'),
	(920, 2, 48, '2026-03-28 16:41:30'),
	(921, 2, 46, '2026-03-28 16:41:30'),
	(922, 2, 7, '2026-03-28 16:41:30'),
	(923, 2, 4, '2026-03-28 16:41:30'),
	(924, 2, 5, '2026-03-28 16:41:30'),
	(925, 2, 8, '2026-03-28 16:41:30'),
	(926, 2, 3, '2026-03-28 16:41:30'),
	(927, 2, 35, '2026-03-28 16:41:30'),
	(928, 2, 34, '2026-03-28 16:41:30'),
	(929, 2, 32, '2026-03-28 16:41:30'),
	(930, 2, 101, '2026-03-28 16:41:30');

-- Dumping structure for table cep_uokdb.site_settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('text','url','email','phone','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.site_settings: ~12 rows (approximately)
REPLACE INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
	(1, 'site_name', 'CEP UoK', 'text', 'Site name', '2026-01-29 15:00:45'),
	(2, 'site_tagline', 'Communauté des Étudiants Pentecôtistes à l\'Université de Kigali', 'text', 'Site tagline', '2026-01-29 15:00:45'),
	(3, 'contact_email', 'cepuok01@gmail.com', 'email', 'Primary contact email', '2026-01-29 15:00:45'),
	(4, 'contact_phone1', '+250 791 619 272', 'phone', 'Primary phone number', '2026-01-29 15:00:45'),
	(5, 'contact_phone2', '+250 722 276 153', 'phone', 'Secondary phone number', '2026-01-29 15:00:45'),
	(6, 'contact_address', 'KG 541 St, Kigali, Rwanda', 'text', 'Physical address', '2026-01-29 15:00:45'),
	(7, 'social_facebook', 'https://www.facebook.com/profile.php?id=100069626831778', 'url', 'Facebook page URL', '2026-01-29 15:00:45'),
	(8, 'social_twitter', 'https://x.com/cepuok01', 'url', 'Twitter/X profile URL', '2026-01-29 15:00:45'),
	(9, 'social_instagram', 'https://www.instagram.com/cepuok01?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==', 'url', 'Instagram profile URL', '2026-01-29 15:00:45'),
	(10, 'social_youtube', 'https://www.youtube.com/@cepuok9716', 'url', 'YouTube channel URL', '2026-01-29 15:00:45'),
	(11, 'footer_about', 'CEP UoK is a vibrant Christian students\' fellowship at the University of Kigali, nurturing spiritual growth, leadership development, and kingdom impact through prayer, worship, discipleship, and service.', 'text', 'Footer about text', '2026-01-29 15:00:45'),
	(12, 'footer_copyright', 'Copyright © 2026 CEP UoK. All rights reserved.', 'text', 'Footer copyright text', '2026-01-29 19:08:00');

-- Dumping structure for table cep_uokdb.supporter_contributions
CREATE TABLE IF NOT EXISTS `supporter_contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supporter_id` int(11) NOT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `contribution_type` enum('financial','material','service','prayer','mentorship') NOT NULL DEFAULT 'financial',
  `contribution_subtype` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contribution_date` date NOT NULL,
  `receipt_path` varchar(500) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_supporter_id` (`supporter_id`),
  CONSTRAINT `fk_contribution_supporter` FOREIGN KEY (`supporter_id`) REFERENCES `cep_supporters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.supporter_contributions: ~2 rows (approximately)
REPLACE INTO `supporter_contributions` (`id`, `supporter_id`, `cep_session`, `contribution_type`, `contribution_subtype`, `amount`, `description`, `contribution_date`, `receipt_path`, `recorded_by`, `created_at`) VALUES
	(7, 1, 'both', 'financial', NULL, 258700.00, 'Piano Buying', '2026-03-05', NULL, 1, '2026-03-05 00:39:00'),
	(13, 1, 'both', 'financial', NULL, 2600.00, 'kugura piano', '2026-03-08', NULL, 5, '2026-03-08 21:16:48');

-- Dumping structure for table cep_uokdb.talents_gifts
CREATE TABLE IF NOT EXISTS `talents_gifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `talent_name` varchar(100) NOT NULL,
  `category` enum('Music','Media','Leadership','Teaching','Evangelism','Service','Other') DEFAULT 'Other',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_talent_name` (`talent_name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.talents_gifts: ~18 rows (approximately)
REPLACE INTO `talents_gifts` (`id`, `talent_name`, `category`, `is_active`, `created_at`) VALUES
	(1, 'Singing', 'Music', 1, '2026-02-13 15:18:01'),
	(2, 'Playing Instrument', 'Music', 1, '2026-02-13 15:18:01'),
	(3, 'Worship Leading', 'Music', 1, '2026-02-13 15:18:01'),
	(4, 'Photography', 'Media', 1, '2026-02-13 15:18:01'),
	(5, 'Videography', 'Media', 1, '2026-02-13 15:18:01'),
	(6, 'Graphic Design', 'Media', 1, '2026-02-13 15:18:01'),
	(7, 'Social Media Management', 'Media', 1, '2026-02-13 15:18:01'),
	(8, 'Public Speaking', 'Leadership', 1, '2026-02-13 15:18:01'),
	(9, 'Event Planning', 'Leadership', 1, '2026-02-13 15:18:01'),
	(10, 'Team Leadership', 'Leadership', 1, '2026-02-13 15:18:01'),
	(11, 'Bible Teaching', 'Teaching', 1, '2026-02-13 15:18:01'),
	(12, 'Youth Ministry', 'Teaching', 1, '2026-02-13 15:18:01'),
	(13, 'Evangelism', 'Evangelism', 1, '2026-02-13 15:18:01'),
	(14, 'Prayer Ministry', 'Evangelism', 1, '2026-02-13 15:18:01'),
	(15, 'Hospitality', 'Service', 1, '2026-02-13 15:18:01'),
	(16, 'Protocol', 'Service', 1, '2026-02-13 15:18:01'),
	(17, 'Counseling', 'Service', 1, '2026-02-13 15:18:01'),
	(18, 'Other', 'Other', 1, '2026-02-13 15:18:01');

-- Dumping structure for table cep_uokdb.testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.testimonials: ~5 rows (approximately)
REPLACE INTO `testimonials` (`id`, `name`, `role`, `content`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Jean Claude NIYONZIMA', 'Alumni - Class of 2020', 'CEP UoK transformed my university life. I found not just friends, but a family that helped me grow in faith and leadership. The discipleship and mentorship I received prepared me for life beyond campus.', '/img/testimonials/testimonial-1.jpg', 1, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(2, 'Grace UWASE', 'Current Member - Engineering Student', 'Joining CEP was the best decision I made at UoK. The fellowship provided spiritual support during challenging times and helped me balance academics with my walk with God.', '/img/testimonials/testimonial-2.jpg', 2, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(3, 'Patrick HABIMANA', 'Alumni - Church Leader', 'My time at CEP UoK shaped me into the minister I am today. The prayer culture, biblical teaching, and hands-on ministry experience gave me a strong foundation for serving God\'s kingdom.', '/img/testimonials/testimonial-3.jpg', 3, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(4, 'Marie UWERA', 'Current Member - Business Student', 'CEP is more than a fellowship; it\'s a movement. Here, I discovered my spiritual gifts, developed leadership skills, and found my purpose in serving Christ on campus.', '/img/testimonials/testimonial-4.jpg', 4, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
	(5, 'David MUGISHA', 'Alumni - Missionary', 'The evangelism training and outreach opportunities at CEP UoK ignited my passion for missions. Today, I\'m serving in rural Rwanda because of what I learned and experienced in this fellowship.', '/img/testimonials/testimonial-5.jpg', 5, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45');

-- Dumping structure for table cep_uokdb.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT 5 COMMENT 'Foreign key to roles table, default is Student',
  `member_id` int(11) DEFAULT NULL COMMENT 'Link to members table (optional)',
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed password',
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL COMMENT 'Password reset OTP',
  `reset_expiry` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `status` enum('pending','active','inactive','suspended') DEFAULT 'pending',
  `is_adepr_member` tinyint(1) DEFAULT 0 COMMENT 'Must be 1 for CEP leaders',
  `can_manage_website` tinyint(1) DEFAULT 0 COMMENT 'Permission to update website content',
  `created_by` int(11) DEFAULT NULL COMMENT 'User ID who created this account',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `unique_username` (`username`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_last_login` (`last_login`),
  CONSTRAINT `fk_user_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.users: ~4 rows (approximately)
REPLACE INTO `users` (`id`, `role_id`, `member_id`, `firstname`, `lastname`, `username`, `email`, `phone`, `password`, `photo`, `bio`, `email_verified`, `email_verification_token`, `email_verification_expires`, `reset_token`, `reset_expiry`, `last_login`, `login_attempts`, `locked_until`, `status`, `is_adepr_member`, `can_manage_website`, `created_by`, `created_at`, `updated_at`, `last_activity`) VALUES
	(1, 1, NULL, 'Super', 'Admin', 'admin', 'admin@cepuok.com', '+250788000000', '$2y$10$cTKQFPz493I5.QQkU1MwzOW.YLOdQKqnHbWzpsnO13eI54jLUnCt6', NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-04-10 05:30:21', 0, NULL, 'active', 1, 1, NULL, '2026-02-13 15:14:23', '2026-04-10 05:30:21', NULL),
	(2, 3, 5, 'NIYONZIMA', 'Aaron', 'niyonzima.aaron', 'aaronniyonzima52@gmail.com', '0785729794', '$2y$10$ed2/5nm75Rd.7pie5UiNuOJWdtUnuwMEFoh0.oKz7Yr14lbpjJofa', 'users/699c5f8a848f6_1771855754.jpg', 'Boy to Christ', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'active', 1, 0, 1, '2026-02-23 14:09:14', '2026-03-04 22:19:33', NULL),
	(3, 6, 6, 'MUKASHEMA', 'Alice', 'mukashema.alice', 'alicemukashema@gmail.com', '0 787 962 735', '$2y$10$od7IhT1Z89vfkm5T8bzUhepdlGyBA4BGmXDauohyh3HDiRFi6OaJ2', 'users/69a0aef3dab68_1772138227.png', 'Courageous enough', 0, NULL, NULL, NULL, NULL, '2026-03-28 16:46:52', 0, NULL, 'active', 1, 0, 1, '2026-02-26 20:37:08', '2026-03-28 16:46:52', NULL),
	(5, 2, 7, 'NTAGAWA', 'David', 'ntagawa.david', 'david.ntagawa@gmail.com', '0791619272', '$2y$10$od7IhT1Z89vfkm5T8bzUhepdlGyBA4BGmXDauohyh3HDiRFi6OaJ2', 'users/69a3d927778da_1772345639.png', '', 0, NULL, NULL, NULL, NULL, '2026-03-28 16:23:33', 0, NULL, 'active', 1, 0, 1, '2026-03-01 06:13:59', '2026-03-28 16:23:33', NULL);

-- Dumping structure for table cep_uokdb.user_activity_log
CREATE TABLE IF NOT EXISTS `user_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'Action performed (e.g., login, logout, create, edit)',
  `module` varchar(100) DEFAULT NULL COMMENT 'Module/entity affected',
  `record_id` int(11) DEFAULT NULL COMMENT 'ID of affected record',
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_activity_user_date` (`user_id`,`created_at`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.user_activity_log: ~7 rows (approximately)
REPLACE INTO `user_activity_log` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 1, 'create', 'users', 2, 'User created: aaronniyonzima52@gmail.com', NULL, NULL, '2026-02-23 14:09:14'),
	(2, 1, 'create', 'users', 3, 'User created: alicemukashema@gmai.com', NULL, NULL, '2026-02-26 20:37:08'),
	(3, 2, 'status_change', 'users', 2, 'Status changed from pending to active', NULL, NULL, '2026-02-27 07:40:12'),
	(4, 1, 'create', 'users', 5, 'User created: david.ntagawa@gmail.com', NULL, NULL, '2026-03-01 06:13:59'),
	(5, 1, 'create', 'users', 6, 'User created: test@cepuok.com', NULL, NULL, '2026-03-04 16:34:56'),
	(6, 1, 'delete', 'finance', NULL, 'Deleted budget indicator ID: 2 - Session: day, Year: 2027-2028, Base Balance: 3500000.00 (Deleted 0 quarters, 6 pools)', '::1', NULL, '2026-03-10 21:02:13'),
	(7, 3, 'disburse', 'finance', NULL, 'Disbursed funds for request ID: 3 - Amount: 5200', NULL, NULL, '2026-03-10 21:15:24'),
	(8, 3, 'disburse', 'finance', NULL, 'Disbursed funds for request ID: 2 - Amount: 3000', NULL, NULL, '2026-03-10 21:18:36'),
	(9, 3, 'disburse', 'finance', NULL, 'Disbursed funds for request ID: 4 - Amount: 1500', NULL, NULL, '2026-03-28 16:49:42');

-- Dumping structure for table cep_uokdb.user_sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL COMMENT 'JWT token or session ID',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token` (`token`(255)),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_sessions_user` (`user_id`),
  KEY `idx_sessions_expires` (`expires_at`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cep_uokdb.user_sessions: ~0 rows (approximately)

-- Dumping structure for table cep_uokdb.videos
CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'general',
  `duration` varchar(20) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `year` (`year`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table cep_uokdb.videos: ~6 rows (approximately)
REPLACE INTO `videos` (`id`, `title`, `description`, `video_url`, `thumbnail_url`, `category`, `duration`, `year`, `views`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Welcome to CEP UoK', 'Introduction to our fellowship community', 'https://www.youtube.com/watch?v=NZI3j_XpgWM', NULL, 'Introduction', NULL, 2026, 1251, 0, 'active', '2026-02-05 12:42:16', '2026-03-08 21:29:48'),
	(2, 'Our History', 'Journey of CEP UoK from 2016 to present', 'https://www.youtube.com/watch?v=DaGMZsmDKBU', NULL, 'History', NULL, 2026, 891, 0, 'active', '2026-02-05 12:42:16', '2026-02-06 23:56:22'),
	(3, 'Sunday Worship Service', 'Highlights from our weekly worship', 'https://www.youtube.com/watch?v=abc123def', NULL, 'Worship', NULL, 2026, 560, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
	(4, 'Annual Conference 2025', 'CEP Annual Conference highlights', 'https://www.youtube.com/watch?v=xyz789abc', NULL, 'Events', NULL, 2025, 1200, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
	(5, 'Choir Performance', 'CEP choir ministering in worship', 'https://www.youtube.com/watch?v=def456ghi', NULL, 'Choir', NULL, 2025, 750, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
	(6, 'Campus Evangelism', 'Reaching students for Christ', 'https://www.youtube.com/watch?v=ghi789jkl', NULL, 'Evangelism', NULL, 2024, 430, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16');

-- Dumping structure for view cep_uokdb.v_active_users_summary
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_active_users_summary` (
	`role_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total_users` BIGINT(21) NOT NULL,
	`active_users` DECIMAL(22,0) NULL,
	`pending_users` DECIMAL(22,0) NULL,
	`active_last_30_days` DECIMAL(22,0) NULL
);

-- Dumping structure for view cep_uokdb.v_members_with_session
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_members_with_session` 
);

-- Dumping structure for view cep_uokdb.v_member_statistics
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_member_statistics` (
	`membership_type` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total_members` BIGINT(21) NOT NULL,
	`active_members` DECIMAL(22,0) NULL,
	`pending_members` DECIMAL(22,0) NULL,
	`male_members` DECIMAL(22,0) NULL,
	`female_members` DECIMAL(22,0) NULL,
	`avg_years_membership` DECIMAL(9,4) NULL
);

-- Dumping structure for view cep_uokdb.v_user_details
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_user_details` (
	`id` INT(11) NOT NULL,
	`firstname` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lastname` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`username` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`phone` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`photo` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`status` ENUM('pending','active','inactive','suspended') NULL COLLATE 'utf8mb4_unicode_ci',
	`is_adepr_member` TINYINT(1) NULL COMMENT 'Must be 1 for CEP leaders',
	`can_manage_website` TINYINT(1) NULL COMMENT 'Permission to update website content',
	`last_login` TIMESTAMP NULL,
	`created_at` TIMESTAMP NOT NULL,
	`role_id` INT(11) NULL,
	`role_name` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`is_super_admin` TINYINT(1) NULL COMMENT '1 = Super Admin with full access',
	`permissions` MEDIUMTEXT NULL COLLATE 'utf8mb4_unicode_ci'
);

-- Dumping structure for trigger cep_uokdb.trg_user_created
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `trg_user_created` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_activity_log (user_id, action, module, record_id, description)
    VALUES (NEW.created_by, 'create', 'users', NEW.id, CONCAT('User created: ', NEW.email));
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger cep_uokdb.trg_user_updated
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `trg_user_updated` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO user_activity_log (user_id, action, module, record_id, description)
        VALUES (NEW.id, 'status_change', 'users', NEW.id, CONCAT('Status changed from ', OLD.status, ' to ', NEW.status));
    END IF;
    
    IF OLD.role_id != NEW.role_id THEN
        INSERT INTO user_activity_log (user_id, action, module, record_id, description)
        VALUES (NEW.id, 'role_change', 'users', NEW.id, CONCAT('Role changed from ', OLD.role_id, ' to ', NEW.role_id));
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_active_users_summary`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_active_users_summary` AS SELECT `r`.`name` AS `role_name`, count(`u`.`id`) AS `total_users`, sum(case when `u`.`status` = 'active' then 1 else 0 end) AS `active_users`, sum(case when `u`.`status` = 'pending' then 1 else 0 end) AS `pending_users`, sum(case when `u`.`last_login` >= current_timestamp() - interval 30 day then 1 else 0 end) AS `active_last_30_days` FROM (`users` `u` join `roles` `r` on(`u`.`role_id` = `r`.`id`)) GROUP BY `r`.`id`, `r`.`name` ORDER BY count(`u`.`id`) DESC 
;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_members_with_session`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_members_with_session` AS SELECT 
  m.*,
  mt.type_name AS membership_type_name,
  cf.family_name,
  cf.family_code,
  cf.color_code AS family_color,
  u.email AS user_email,
  u.status AS user_status,
  r.name AS user_role
FROM members m
LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
LEFT JOIN cep_families cf ON m.family_id = cf.id
LEFT JOIN users u ON m.user_id = u.id
LEFT JOIN roles r ON u.role_id = r.id 
;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_member_statistics`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_member_statistics` AS SELECT `mt`.`type_name` AS `membership_type`, count(`m`.`id`) AS `total_members`, sum(case when `m`.`status` = 'active' then 1 else 0 end) AS `active_members`, sum(case when `m`.`status` = 'pending' then 1 else 0 end) AS `pending_members`, sum(case when `m`.`gender` = 'Male' then 1 else 0 end) AS `male_members`, sum(case when `m`.`gender` = 'Female' then 1 else 0 end) AS `female_members`, avg(year(curdate()) - `m`.`year_joined_cep`) AS `avg_years_membership` FROM (`members` `m` join `membership_types` `mt` on(`m`.`membership_type_id` = `mt`.`id`)) GROUP BY `mt`.`id`, `mt`.`type_name` 
;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_user_details`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_user_details` AS SELECT `u`.`id` AS `id`, `u`.`firstname` AS `firstname`, `u`.`lastname` AS `lastname`, `u`.`username` AS `username`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `u`.`photo` AS `photo`, `u`.`status` AS `status`, `u`.`is_adepr_member` AS `is_adepr_member`, `u`.`can_manage_website` AS `can_manage_website`, `u`.`last_login` AS `last_login`, `u`.`created_at` AS `created_at`, `r`.`id` AS `role_id`, `r`.`name` AS `role_name`, `r`.`is_super_admin` AS `is_super_admin`, group_concat(concat(`p`.`module`,'.',`p`.`action`) separator ',') AS `permissions` FROM (((`users` `u` left join `roles` `r` on(`u`.`role_id` = `r`.`id`)) left join `role_permissions` `rp` on(`r`.`id` = `rp`.`role_id`)) left join `permissions` `p` on(`rp`.`permission_id` = `p`.`id`)) GROUP BY `u`.`id`, `u`.`firstname`, `u`.`lastname`, `u`.`username`, `u`.`email`, `u`.`phone`, `u`.`photo`, `u`.`status`, `u`.`is_adepr_member`, `u`.`can_manage_website`, `u`.`last_login`, `u`.`created_at`, `r`.`id`, `r`.`name`, `r`.`is_super_admin` 
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
