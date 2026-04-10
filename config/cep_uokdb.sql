-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 04:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cep_uokdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget_activities`
--

CREATE TABLE `budget_activities` (
  `id` int(11) NOT NULL,
  `quarter_id` int(11) NOT NULL,
  `pool_id` int(11) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `allocated_amount` decimal(15,2) DEFAULT 0.00,
  `spent_amount` decimal(15,2) DEFAULT 0.00,
  `is_external` tinyint(1) DEFAULT 0 COMMENT '1=Family/Choir own funds, CEP tracking only',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_indicators`
--

CREATE TABLE `budget_indicators` (
  `id` int(11) NOT NULL,
  `cep_session` enum('day','weekend') NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `base_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '100% reference balance',
  `lock_date` date DEFAULT NULL COMMENT 'President edit deadline',
  `status` enum('draft','confirmed','locked') DEFAULT 'draft',
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_lines`
--

CREATE TABLE `budget_lines` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `line_item` varchar(200) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `spent_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_quarters`
--

CREATE TABLE `budget_quarters` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cep_families`
--

CREATE TABLE `cep_families` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cep_families`
--

INSERT INTO `cep_families` (`id`, `family_name`, `family_code`, `icon_class`, `motto`, `cep_session`, `description`, `parent_user_id`, `co_parent_user_id`, `color_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bethesda Family', 'FAM-BETH', 'bi bi-people', 'House of Mercy & Grace', 'day', NULL, NULL, NULL, '#28a745', 'active', '2026-02-22 15:12:54', '2026-02-22 15:12:54'),
(2, 'Naioth Family', 'FAM-NAIT', 'bi bi-people', 'Dwelling of the Prophets', 'day', NULL, NULL, NULL, '#007bff', 'active', '2026-02-22 15:12:54', '2026-02-22 15:12:54'),
(3, 'Siloam Family', 'FAM-SILO', 'bi bi-people', 'Sent to Heal & Restore', 'day', NULL, NULL, NULL, '#fd7e14', 'active', '2026-02-22 15:12:54', '2026-02-22 15:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `cep_history_timeline`
--

CREATE TABLE `cep_history_timeline` (
  `id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-star',
  `is_current` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cep_history_timeline`
--

INSERT INTO `cep_history_timeline` (`id`, `year`, `title`, `description`, `icon_class`, `is_current`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 2016, 'The Foundation', 'CEP UoK was officially established at the University of Kigali, bringing together Pentecostal students with a vision to impact the campus for Christ.', 'fas fa-church', 0, 1, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
(2, 2018, 'Campus Expansion', 'Extended fellowship activities to include both Kacyiru and Remera campuses, reaching more students with the Gospel.', 'fas fa-expand-arrows-alt', 0, 2, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
(3, 2019, 'Self-Reliance Initiative', 'Launched the first entrepreneurship and leadership training programs, emphasizing spiritual growth alongside practical skills.', 'fas fa-lightbulb', 0, 3, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
(4, 2022, 'Dual Session Launch', 'Introduced Day and Weekend sessions to accommodate diverse student schedules, doubling our ministry reach.', 'fas fa-calendar-alt', 0, 4, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
(5, 2024, 'Digital Ministry Era', 'Established comprehensive media team and online presence, extending our impact beyond physical campus boundaries.', 'fas fa-wifi', 0, 5, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59'),
(6, 2026, 'Continuous Growth', 'Celebrating sustained growth with over 200 active members and strengthened partnerships with local churches.', 'fas fa-trophy', 0, 6, 'active', '2026-02-01 13:26:59', '2026-02-01 13:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `cep_sessions`
--

CREATE TABLE `cep_sessions` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cep_sessions`
--

INSERT INTO `cep_sessions` (`id`, `session_type`, `session_label`, `academic_year`, `committee_year_id`, `handover_date`, `portal_enabled`, `portal_locked_reason`, `locked_by`, `locked_at`, `is_current`, `created_at`, `updated_at`) VALUES
(1, 'day', 'Day CEP 2026-2027', '2026-2027', 1, NULL, 1, NULL, NULL, NULL, 1, '2026-02-22 15:12:54', '2026-02-22 15:12:54'),
(2, 'weekend', 'Weekend CEP 2026-2027', '2026-2027', 1, NULL, 1, NULL, NULL, NULL, 1, '2026-02-22 15:12:54', '2026-02-22 15:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `cep_supporters`
--

CREATE TABLE `cep_supporters` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cep_supporters`
--

INSERT INTO `cep_supporters` (`id`, `supporter_type`, `firstname`, `lastname`, `organization_name`, `email`, `phone`, `address`, `cep_session`, `support_area`, `tier`, `is_alumni`, `graduation_year`, `notes`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'external', 'MUTABAZI', 'Josue', 'Global Kwik Koders', 'mutabazijosue1@gmail.com', '0786055919', '1 KN 78 ST. Kigali', 'day', 'general', 'platinum', 1, '2019', 'Josue Yahoze aririmba muri Penuel Choir guher 2017 kugeza 2020. \nNyuma aza kujya munshingano aba n\' umuterankunga ushobora kugira icyo akora bitewe nuwamuganirije cyane cyane Remy cg Wiclef nabandi bake bakoranye icyo gihe. \n\nAkenera cyane ko ibintu bikorwa muri plan inoze kdi itangirwa report nawe akabona umusaruro uvuye muri contribution yatanze', NULL, 'active', '2026-02-28 09:08:34', '2026-03-05 00:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `choir_attendance`
--

CREATE TABLE `choir_attendance` (
  `id` int(11) NOT NULL,
  `rehearsal_id` int(11) NOT NULL,
  `choir_member_id` int(11) NOT NULL,
  `status` enum('present','absent','excused','late') DEFAULT 'present',
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `choir_members`
--

CREATE TABLE `choir_members` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL COMMENT 'Link to members table',
  `full_name` varchar(200) NOT NULL COMMENT 'Cached for display even if member unlinked',
  `voice_part` enum('soprano','alto','tenor','bass','other') DEFAULT 'soprano',
  `instrument` varchar(100) DEFAULT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `role` enum('member','section_leader','choir_president','accompanist') DEFAULT 'member',
  `joined_date` date DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `choir_rehearsals`
--

CREATE TABLE `choir_rehearsals` (
  `id` int(11) NOT NULL,
  `rehearsal_date` date NOT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `location` varchar(200) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `conductor_id` int(11) DEFAULT NULL,
  `songs_practiced` text DEFAULT NULL COMMENT 'CSV of song IDs or free text',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `choir_songs`
--

CREATE TABLE `choir_songs` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `churches`
--

CREATE TABLE `churches` (
  `id` int(11) NOT NULL,
  `church_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `denomination` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `churches`
--

INSERT INTO `churches` (`id`, `church_name`, `location`, `denomination`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ADEPR Kimihurura International Service', 'Kimihurura, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(2, 'ADEPR Remera', 'Remera, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(3, 'ADEPR Kicukiro', 'Kicukiro, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(4, 'ADEPR Nyamirambo', 'Nyamirambo, Kigali', 'ADEPR', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(5, 'Other Church', 'Various', 'Other', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01');

-- --------------------------------------------------------

--
-- Table structure for table `committee_handovers`
--

CREATE TABLE `committee_handovers` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credential_wallet`
--

CREATE TABLE `credential_wallet` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encrypted credentials vault for CEP social/digital accounts';

-- --------------------------------------------------------

--
-- Table structure for table `credential_wallet_audit`
--

CREATE TABLE `credential_wallet_audit` (
  `id` int(11) NOT NULL,
  `credential_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('viewed','copied_password','created','updated','deleted','toggled_status') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for every action on the credentials wallet';

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `title`, `subtitle`, `description`, `icon_class`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Evangelism', 'Sharing the Gospel', 'Reaching out to fellow students with the message of Christ through campus evangelism, outreach programs, and personal testimonies.', 'fas fa-bible', '/img/departments/evangelism.jpg', 1, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(2, 'Choir', 'Worship in Song', 'Leading worship through music and song, bringing glory to God and ministering to the hearts of students.', 'fas fa-music', '/img/departments/choir.jpg', 2, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(3, 'Protocol', 'Order and Excellence', 'Ensuring smooth organization of events, proper protocols, and maintaining excellence in all CEP activities.', 'fas fa-tasks', '/img/departments/protocol.jpg', 3, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(4, 'Social Affairs', 'Care and Community', 'Caring for the social, emotional, and material needs of members while building strong community bonds.', 'fas fa-heart', '/img/departments/social-affairs.jpg', 4, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(5, 'Media Team', 'Digital Ministry', 'Managing CEP\'s online presence, documentation, and multimedia content to extend our reach and impact.', 'fas fa-camera', '/img/departments/media.jpg', 5, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(6, 'Worship Team', 'Leading Worship', 'Providing instrumental and vocal leadership in worship services, creating an atmosphere for encountering God.', 'fas fa-guitar', '/img/departments/worship.jpg', 6, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','mobile_money','bank_transfer','cheque') DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `recipient_name` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `disbursed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receipt_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_budgets`
--

CREATE TABLE `finance_budgets` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_revenue`
--

CREATE TABLE `finance_revenue` (
  `id` int(11) NOT NULL,
  `cep_session` enum('day','weekend','both') NOT NULL DEFAULT 'day',
  `revenue_type` enum('offering','tithe','donation','project','fundraising','other') NOT NULL DEFAULT 'offering',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `revenue_date` date NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fund_requests`
--

CREATE TABLE `fund_requests` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fund_request_comments`
--

CREATE TABLE `fund_request_comments` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `title`, `description`, `image_url`, `thumbnail_url`, `category`, `year`, `display_order`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `gallery_years`
--

CREATE TABLE `gallery_years` (
  `id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `year_label` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_years`
--

INSERT INTO `gallery_years` (`id`, `year`, `year_label`, `description`, `display_order`, `status`, `created_at`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `hero_sliders`
--

CREATE TABLE `hero_sliders` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_sliders`
--

INSERT INTO `hero_sliders` (`id`, `title`, `subtitle`, `description`, `image_url`, `button1_text`, `button1_link`, `button2_text`, `button2_link`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Faith on Campus', 'WE GROW TOGETHER IN CHRIST', 'CEP UoK is a Christian students\' fellowship at the University of Kigali, nurturing spiritual growth, unity, and purpose.', '/img/slider/slider-1.jpg', 'Learn More', '/about', 'Contact Us', '/contact', 1, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(2, 'Christ-Centered Leaders', 'CALLED TO SERVE AND LEAD', 'Through prayer, worship, discipleship, and fellowship, we equip students to live out their faith and impact the university and society.', '/img/slider/slider-2.jpg', 'Our Departments', '/departments', 'Join Us', '/contact', 2, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(3, 'You Belong Here', 'A HOME FOR EVERY STUDENT', 'Open to all University of Kigali students, CEP UoK offers a welcoming community to grow spiritually, serve together, and walk in faith.', '/img/slider/slider-3.jpg', 'View Events', '/news', 'Contact Us', '/contact', 3, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `indicator_pools`
--

CREATE TABLE `indicator_pools` (
  `id` int(11) NOT NULL,
  `indicator_id` int(11) NOT NULL,
  `pool_name` varchar(100) NOT NULL,
  `pool_slug` varchar(50) NOT NULL,
  `pool_type` enum('department','internal','reserve','other') DEFAULT 'department',
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `allocated_amount` decimal(15,2) DEFAULT 0.00,
  `color` varchar(20) DEFAULT '#377dff',
  `display_order` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leadership_achievements`
--

CREATE TABLE `leadership_achievements` (
  `id` int(11) NOT NULL,
  `year_id` int(11) NOT NULL,
  `achievement_title` varchar(255) NOT NULL,
  `achievement_description` text DEFAULT NULL,
  `achievement_date` date DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-trophy',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leadership_members`
--

CREATE TABLE `leadership_members` (
  `id` int(11) NOT NULL,
  `year_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `session_type` enum('both','day','weekend') DEFAULT 'both',
  `image_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leadership_members`
--

INSERT INTO `leadership_members` (`id`, `year_id`, `position_id`, `full_name`, `session_type`, `image_url`, `bio`, `display_order`, `status`, `created_at`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `leadership_positions`
--

CREATE TABLE `leadership_positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `position_abbr` varchar(50) DEFAULT NULL,
  `position_level` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leadership_positions`
--

INSERT INTO `leadership_positions` (`id`, `position_name`, `position_abbr`, `position_level`, `display_order`, `status`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `leadership_years`
--

CREATE TABLE `leadership_years` (
  `id` int(11) NOT NULL,
  `year_label` varchar(100) NOT NULL,
  `year_start` int(4) NOT NULL,
  `year_end` int(4) NOT NULL,
  `description` text DEFAULT NULL,
  `has_dual_sessions` tinyint(1) DEFAULT 0,
  `is_current` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leadership_years`
--

INSERT INTO `leadership_years` (`id`, `year_label`, `year_start`, `year_end`, `description`, `has_dual_sessions`, `is_current`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Committee 2026-2027', 2026, 2027, 'Current leadership committee serving CEP UoK', 1, 1, 1, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
(2, 'Committee 2025-2026', 2025, 2026, 'Leadership committee for academic year 2025-2026', 1, 0, 2, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
(3, 'Committee 2024-2025', 2024, 2025, 'Leadership committee for academic year 2024-2025', 1, 0, 3, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
(4, 'Committee 2023-2024', 2023, 2024, 'Leadership committee for academic year 2023-2024', 1, 0, 4, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
(5, 'Committee 2021-2022', 2021, 2022, 'Leadership committee for academic year 2021-2022', 0, 0, 5, 'active', '2026-02-04 21:08:51', '2026-02-05 10:20:14'),
(6, 'Committee 2019-2021', 2019, 2021, 'Leadership committee for academic years 2019-2021', 0, 0, 6, 'active', '2026-02-04 21:08:51', '2026-02-05 10:20:14'),
(7, 'Committee 2018-2019', 2018, 2019, 'Leadership committee for academic year 2018-2019', 0, 0, 7, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
(8, 'Committee 2017-2018', 2017, 2018, 'Leadership committee for academic year 2017-2018', 0, 0, 8, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51'),
(9, 'Committee 2016-2017', 2016, 2017, 'Leadership committee for academic year 2016-2017', 0, 0, 9, 'active', '2026-02-04 21:08:51', '2026-02-04 21:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
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
  `last_activity` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `membership_type_id`, `membership_number`, `firstname`, `lastname`, `email`, `phone`, `gender`, `date_of_birth`, `address`, `year_joined_cep`, `cep_session`, `faculty`, `program`, `academic_year`, `church_name`, `family_id`, `other_church_name`, `is_born_again`, `is_baptized`, `profile_photo`, `bio`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `last_activity`) VALUES
(5, 2, 1, NULL, 'NIYONZIMA', 'Aaron', 'aaronniyonzima52@gmail.com', '0785729794', 'Male', '2000-01-01', 'Remera, Gasabo, Kigali', '2023', 'day', 'Accounting', 'BSc in Finance and Accounting', 'Year 3', 'ADEPR Kimihurura International Service', 1, NULL, 'Yes', 'Yes', 'members/699b5d4edf3e3_1771789646.jpg', 'I loved the way CEP members live together and work together', 'active', 1, '2026-02-27 07:17:16', '2026-02-22 19:47:26', '2026-03-04 22:19:33', NULL),
(6, NULL, 1, NULL, 'MUKASHEMA', 'Alice', 'alicemukashema@gmail.com', '0787962735', 'Female', '2001-01-01', 'Kabuga, Kicukiro, Kigali', '2024', 'day', 'Accounting', 'Bsc in finance and Accounting', 'Year 3', 'ADEPR Kimihurura International Service', 2, NULL, 'Yes', 'Yes', 'members/69a0ae4b2b3d4_1772138059.png', 'Always courageous on the Work', 'active', 1, '2026-02-27 08:38:51', '2026-02-26 20:34:19', '2026-02-27 18:55:01', NULL),
(7, 5, 1, 'CEP-D-2026-0007', 'NTAGAWA', 'David', 'david.ntagawa@gmail.com', '0791619272', 'Male', NULL, 'Karuruma, Gasabo, Kigali', '2024', 'day', 'Law', 'Law', 'Year 2', 'ADEPR Kimihurura International Service', 3, NULL, 'Yes', 'Yes', NULL, 'Always Remain Faithful to God', 'active', 1, '2026-02-27 07:17:39', '2026-02-27 08:17:39', '2026-03-04 22:03:39', NULL),
(8, NULL, 1, 'CEP-D-2026-0008', 'IRADUKUNDA MBABAZI', 'Eric', 'iradukundaericmbabazi@gmail.com', '0784806931', 'Male', '2002-02-02', 'Gisiment, Gasabo, Kigali', '2025', 'day', 'IT ', 'Bsc in IT', 'Year 2', 'ADEPR Kimihurura International Service', 2, NULL, 'Yes', 'Yes', NULL, 'Humbleness and Knowing God Every Day', 'active', 1, '2026-02-27 07:29:27', '2026-02-27 08:29:27', '2026-02-27 09:38:16', NULL),
(9, NULL, 1, 'CEP-D-2026-0009', 'UWUMUKIZA', 'Celine', 'celineuwumukiza@gmail.com', '0728202199', 'Female', '2004-09-05', 'Kanombe, Kicukiro, Kigali', '2024', 'day', 'Law', 'Bsc in Law', 'Year 3', 'ADEPR KACYIRU KANSEREGE', 1, NULL, 'Yes', 'Yes', NULL, 'Always eager to work hard', 'active', 1, '2026-02-27 09:32:46', '2026-02-27 08:51:18', '2026-02-27 09:33:15', NULL),
(10, NULL, 1, NULL, 'NSHUTI', 'YVES', 'nshutiyves2015@gmail.com', '0785865752', 'Male', '2006-12-02', 'Gasabo, Kigali', '2024', 'day', 'Information Technology', 'Bsc Information Technology', 'Year 2', 'ADEPR Kimihurura International Service', 1, NULL, 'Yes', 'Yes', 'members/69a2ece0e470d_1772285152.png', 'A man on Work', 'active', 1, '2026-03-01 06:09:13', '2026-02-28 13:25:52', '2026-03-01 06:10:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `membership_applications`
--

CREATE TABLE `membership_applications` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `application_type` enum('new','renewal','update') DEFAULT 'new',
  `status` enum('submitted','under_review','approved','rejected') DEFAULT 'submitted',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `review_date` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewer_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_applications`
--

INSERT INTO `membership_applications` (`id`, `member_id`, `application_type`, `status`, `submission_date`, `review_date`, `reviewed_by`, `reviewer_notes`, `rejection_reason`) VALUES
(1, 5, 'new', 'approved', '2026-02-22 19:47:26', '2026-02-27 07:17:16', 1, NULL, NULL),
(2, 6, 'new', 'approved', '2026-02-26 20:34:19', '2026-02-27 08:38:51', 1, NULL, NULL),
(3, 7, 'new', 'approved', '2026-02-27 08:17:39', NULL, NULL, NULL, NULL),
(4, 8, 'new', 'approved', '2026-02-27 08:29:27', NULL, NULL, NULL, NULL),
(5, 9, 'new', 'approved', '2026-02-27 08:51:18', NULL, NULL, NULL, NULL),
(6, 10, 'new', 'approved', '2026-02-28 13:25:52', '2026-03-01 06:09:13', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `membership_types`
--

CREATE TABLE `membership_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_types`
--

INSERT INTO `membership_types` (`id`, `type_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Current Student & CEP Member', 'Currently enrolled students who are active CEP members', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(2, 'POST CEPiens (Alumni)', 'Former CEP members who have graduated', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(3, 'Frequent Visitor', 'Regular visitors who attend CEP events frequently', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01'),
(4, 'Donor/Partner', 'Financial supporters and ministry partners of CEP', 1, '2026-02-13 15:18:01', '2026-02-13 15:18:01');

-- --------------------------------------------------------

--
-- Table structure for table `member_activities`
--

CREATE TABLE `member_activities` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `activity_type` enum('registration','login','profile_update','status_change','other') NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `member_activities`
--

INSERT INTO `member_activities` (`id`, `member_id`, `activity_type`, `activity_description`, `ip_address`, `user_agent`, `created_at`) VALUES
(2, 5, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 19:47:26'),
(3, 6, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-26 20:34:19'),
(4, 7, '', 'Member created by admin', '::1', NULL, '2026-02-27 08:17:39'),
(5, 8, '', 'Member created by admin', '::1', NULL, '2026-02-27 08:29:27'),
(6, 9, '', 'Member created by admin', '::1', NULL, '2026-02-27 08:51:18'),
(7, 10, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 13:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `member_talents`
--

CREATE TABLE `member_talents` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `talent_id` int(11) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') DEFAULT 'Intermediate',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `member_talents`
--

INSERT INTO `member_talents` (`id`, `member_id`, `talent_id`, `proficiency_level`, `notes`, `created_at`) VALUES
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
(53, 10, 16, 'Intermediate', NULL, '2026-02-28 13:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `news_events`
--

CREATE TABLE `news_events` (
  `id` int(11) NOT NULL,
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
  `event_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_events`
--

INSERT INTO `news_events` (`id`, `title`, `excerpt`, `description`, `image_url`, `thumbnail_url`, `category`, `author`, `published_date`, `end_date`, `status`, `views`, `created_at`, `updated_at`, `featured`, `event_location`, `event_time`) VALUES
(1, 'Welcome to New Academic Year 2025', 'CEP UoK kicks off the new academic year with renewed vision and purpose', '<p>As we begin this new academic year, CEP UoK welcomes all students to join our fellowship. Whether you\'re a returning member or new to campus, there\'s a place for you in our community.</p>', '/img/news/new-year-2025.jpg', NULL, 'news', NULL, '2025-01-15', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 1, NULL, NULL),
(2, 'Annual Conference 2025', 'Join us for our biggest gathering of the year - CEP UoK Annual Conference', '<p>Save the date for our Annual Conference! Three days of powerful worship, teaching, fellowship, and ministry. Registration opens soon.</p>', '/img/news/conference-2025.jpg', NULL, 'event', NULL, '2025-02-20', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 1, NULL, NULL),
(3, 'Campus Evangelism Week', 'Reaching the campus with the love of Christ', '<p>This week, CEP members will be engaging in intensive evangelism across both campuses. Join us as we share the gospel through personal conversations, worship, and testimonies.</p>', '/img/news/evangelism-week.jpg', NULL, 'news', NULL, '2025-01-22', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 0, NULL, NULL),
(4, 'Leadership Training Workshop', 'Developing the next generation of Christian leaders', '<p>A special workshop for all department heads and aspiring leaders. Learn practical skills in leadership, team management, and spiritual formation.</p>', '/img/news/leadership-training.jpg', NULL, 'event', NULL, '2025-01-28', NULL, 'published', 0, '2026-01-29 15:00:45', '2026-01-29 15:00:45', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE `page_content` (
  `id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_content`
--

INSERT INTO `page_content` (`id`, `page_name`, `section_name`, `title`, `content`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
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
(16, 'about_cep', 'hero_verse', NULL, '\"For where two or three gather in my name, there am I with them.\"', NULL, 3, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(17, 'about_cep', 'hero_verse_ref', NULL, '— Matthew 18:20', NULL, 4, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(18, 'about_cep', 'who_title', NULL, 'Who We Are', NULL, 5, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(19, 'about_cep', 'who_content', NULL, '<p>CEP–UoK (Communauté des Étudiants Pentecôtistes à l\'Université de Kigali) is a Christian students\' fellowship that brings together university students who desire to grow spiritually, live according to biblical values, and serve God within the academic environment of the University of Kigali.</p><p>CEP–UoK exists as a platform for spiritual formation, leadership development, fellowship, and holistic empowerment of students, equipping them to impact the Church, the University, and society at large.</p>', NULL, 6, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(20, 'about_cep', 'who_image', NULL, '', '/img/about/who-we-are.jpg', 7, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(21, 'about_cep', 'vision', NULL, 'To raise Christ-centered leaders who honor God, uphold biblical values, and positively influence the Church, the University, and society.', NULL, 8, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(22, 'about_cep', 'mission_intro', NULL, 'CEP–UoK\'s mission is to nurture students spiritually and holistically by equipping them to live out their Christian faith with responsibility, leadership, and impact.', NULL, 9, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40'),
(23, 'about_cep', 'affiliation', NULL, 'CEP–UoK operates under the spiritual supervision of <strong>ADEPR Kimihurura International Service (Local Church)</strong> and functions in full compliance with:', NULL, 10, 'active', '2026-02-01 13:29:40', '2026-02-01 13:29:40');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `otp` varchar(6) DEFAULT NULL COMMENT '6-digit OTP',
  `email` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL COMMENT 'Module name (e.g., membership, news, gallery)',
  `action` varchar(100) NOT NULL COMMENT 'Action name (e.g., view, create, edit, delete)',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `action`, `description`, `created_at`) VALUES
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
(106, 'finance', 'delete_revenue', 'Delete revenue records', '2026-03-07 09:18:15');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('todo','in_progress','done','blocked') DEFAULT 'todo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_updates`
--

CREATE TABLE `project_updates` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `update_text` text NOT NULL,
  `progress` tinyint(3) DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quick_stats`
--

CREATE TABLE `quick_stats` (
  `id` int(11) NOT NULL,
  `stat_name` varchar(100) NOT NULL,
  `stat_value` varchar(100) NOT NULL,
  `stat_label` varchar(255) NOT NULL,
  `stat_icon` varchar(100) DEFAULT 'fas fa-star',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quick_stats`
--

INSERT INTO `quick_stats` (`id`, `stat_name`, `stat_value`, `stat_label`, `stat_icon`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'years_service', '10+', 'Years of Service', 'fas fa-calendar-alt', 1, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(2, 'post_cepiens', '500+', 'Post CEPiens', 'fas fa-users', 2, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(3, 'day_members', '80', 'Day Members', 'fas fa-user-friends', 3, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(4, 'weekend_members', '120', 'Weekend Members', 'fas fa-user-clock', 4, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(5, 'fellowship_services', '4', 'Fellowship Services', 'fas fa-church', 5, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(6, 'sessions', '2', 'Sessions', 'fas fa-layer-group', 6, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(7, 'campuses', '2', 'Campuses', 'fas fa-map-marker-alt', 7, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(8, 'choir', '1', 'Choir', 'fas fa-music', 8, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44'),
(9, 'worship_team', '1', 'Worship Team', 'fas fa-guitar', 9, 'active', '2026-01-29 15:00:44', '2026-01-29 15:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `recurring_events`
--

CREATE TABLE `recurring_events` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recurring_events`
--

INSERT INTO `recurring_events` (`id`, `title`, `description`, `day_of_week`, `campus`, `start_time`, `end_time`, `event_type`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'English Service', 'Join us for English fellowship with worship, teaching, and prayer', 'Monday', 'Kacyiru Campus', '11:30:00', '13:00:00', 'Fellowship', '/img/events/english-service.jpg', 1, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(2, 'Kinyarwanda Fellowship (Amateraniro)', 'Amateraniro y\'igifaransa kuri campus ya Kacyiru', 'Wednesday', 'Kacyiru Campus', '11:30:00', '13:00:00', 'Fellowship', '/img/events/wednesday-fellowship.jpg', 2, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(3, 'Kinyarwanda Fellowship (Amateraniro)', 'Amateraniro y\'igifaransa kuri campus ya Remera', 'Thursday', 'Remera Campus', '11:30:00', '13:00:00', 'Fellowship', '/img/events/thursday-fellowship.jpg', 3, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(4, 'Sunday Service (Amateraniro)', 'Weekend fellowship with extended worship and ministry time', 'Sunday', 'Kacyiru Campus', '14:00:00', '15:30:00', 'Fellowship', '/img/events/sunday-service.jpg', 4, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT 0 COMMENT '1 = Super Admin with full access',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_super_admin`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Full system access - System Administrator', 1, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
(2, 'President', 'CEP Session President - Full session access', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
(3, 'VP Evangelism', 'Vice President of Evangelism & Prayers', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
(4, 'VP Social Affairs', 'Vice President of Social Affairs', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
(5, 'Secretary', 'Session Secretary', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
(6, 'Accountant', 'Session Accountant', 0, '2026-02-13 15:14:22', '2026-02-22 15:12:55'),
(7, 'Advisor', 'Session Advisor', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
(8, 'Choir President', 'Choir Department President', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
(9, 'Department Head', 'Head of a CEP Department', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55'),
(10, 'Committee Member', 'General committee member', 0, '2026-02-22 15:12:55', '2026-02-22 15:12:55');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
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
(438, 2, 91, '2026-03-04 23:50:25'),
(439, 2, 1, '2026-03-04 23:50:25'),
(440, 2, 89, '2026-03-04 23:50:25'),
(441, 2, 90, '2026-03-04 23:50:25'),
(442, 2, 88, '2026-03-04 23:50:25'),
(443, 2, 37, '2026-03-04 23:50:25'),
(444, 2, 39, '2026-03-04 23:50:25'),
(445, 2, 38, '2026-03-04 23:50:25'),
(446, 2, 36, '2026-03-04 23:50:25'),
(447, 2, 75, '2026-03-04 23:50:25'),
(448, 2, 72, '2026-03-04 23:50:25'),
(449, 2, 73, '2026-03-04 23:50:25'),
(450, 2, 71, '2026-03-04 23:50:25'),
(451, 2, 85, '2026-03-04 23:50:25'),
(452, 2, 86, '2026-03-04 23:50:25'),
(453, 2, 84, '2026-03-04 23:50:25'),
(454, 2, 83, '2026-03-04 23:50:25'),
(455, 2, 82, '2026-03-04 23:50:25'),
(456, 2, 87, '2026-03-04 23:50:25'),
(457, 2, 81, '2026-03-04 23:50:25'),
(458, 2, 30, '2026-03-04 23:50:25'),
(459, 2, 28, '2026-03-04 23:50:25'),
(460, 2, 100, '2026-03-04 23:50:25'),
(461, 2, 99, '2026-03-04 23:50:25'),
(462, 2, 44, '2026-03-04 23:50:25'),
(463, 2, 18, '2026-03-04 23:50:25'),
(464, 2, 15, '2026-03-04 23:50:25'),
(465, 2, 17, '2026-03-04 23:50:25'),
(466, 2, 16, '2026-03-04 23:50:25'),
(467, 2, 19, '2026-03-04 23:50:25'),
(468, 2, 21, '2026-03-04 23:50:25'),
(469, 2, 22, '2026-03-04 23:50:25'),
(470, 2, 20, '2026-03-04 23:50:25'),
(471, 2, 14, '2026-03-04 23:50:25'),
(472, 2, 53, '2026-03-04 23:50:25'),
(473, 2, 52, '2026-03-04 23:50:25'),
(474, 2, 51, '2026-03-04 23:50:25'),
(475, 2, 24, '2026-03-04 23:50:25'),
(476, 2, 25, '2026-03-04 23:50:25'),
(477, 2, 27, '2026-03-04 23:50:25'),
(478, 2, 23, '2026-03-04 23:50:25'),
(479, 2, 41, '2026-03-04 23:50:25'),
(480, 2, 42, '2026-03-04 23:50:25'),
(481, 2, 40, '2026-03-04 23:50:25'),
(482, 2, 96, '2026-03-04 23:50:25'),
(483, 2, 97, '2026-03-04 23:50:25'),
(484, 2, 98, '2026-03-04 23:50:25'),
(485, 2, 95, '2026-03-04 23:50:25'),
(486, 2, 58, '2026-03-04 23:50:25'),
(487, 2, 57, '2026-03-04 23:50:25'),
(488, 2, 56, '2026-03-04 23:50:25'),
(489, 2, 13, '2026-03-04 23:50:25'),
(490, 2, 10, '2026-03-04 23:50:25'),
(491, 2, 11, '2026-03-04 23:50:25'),
(492, 2, 9, '2026-03-04 23:50:25'),
(493, 2, 80, '2026-03-04 23:50:25'),
(494, 2, 77, '2026-03-04 23:50:25'),
(495, 2, 79, '2026-03-04 23:50:25'),
(496, 2, 78, '2026-03-04 23:50:25'),
(497, 2, 76, '2026-03-04 23:50:25'),
(498, 2, 50, '2026-03-04 23:50:25'),
(499, 2, 47, '2026-03-04 23:50:25'),
(500, 2, 48, '2026-03-04 23:50:25'),
(501, 2, 46, '2026-03-04 23:50:25'),
(502, 2, 7, '2026-03-04 23:50:25'),
(503, 2, 4, '2026-03-04 23:50:25'),
(504, 2, 5, '2026-03-04 23:50:25'),
(505, 2, 8, '2026-03-04 23:50:25'),
(506, 2, 3, '2026-03-04 23:50:25'),
(507, 2, 35, '2026-03-04 23:50:25'),
(508, 2, 34, '2026-03-04 23:50:25'),
(509, 2, 32, '2026-03-04 23:50:25'),
(510, 2, 101, '2026-03-04 23:50:25'),
(535, 6, 91, '2026-03-07 09:19:12'),
(536, 6, 37, '2026-03-07 09:19:12'),
(537, 6, 39, '2026-03-07 09:19:12'),
(538, 6, 38, '2026-03-07 09:19:12'),
(539, 6, 36, '2026-03-07 09:19:12'),
(540, 6, 85, '2026-03-07 09:19:12'),
(541, 6, 106, '2026-03-07 09:19:12'),
(542, 6, 86, '2026-03-07 09:19:12'),
(543, 6, 105, '2026-03-07 09:19:12'),
(544, 6, 84, '2026-03-07 09:19:12'),
(545, 6, 83, '2026-03-07 09:19:12'),
(546, 6, 82, '2026-03-07 09:19:12'),
(547, 6, 87, '2026-03-07 09:19:12'),
(548, 6, 81, '2026-03-07 09:19:12'),
(549, 6, 96, '2026-03-07 09:19:12'),
(550, 6, 97, '2026-03-07 09:19:12'),
(551, 6, 98, '2026-03-07 09:19:12'),
(552, 6, 95, '2026-03-07 09:19:12'),
(553, 6, 58, '2026-03-07 09:19:12'),
(554, 6, 57, '2026-03-07 09:19:12'),
(555, 6, 56, '2026-03-07 09:19:12'),
(556, 6, 80, '2026-03-07 09:19:12'),
(557, 6, 77, '2026-03-07 09:19:12'),
(558, 6, 79, '2026-03-07 09:19:12'),
(559, 6, 78, '2026-03-07 09:19:12'),
(560, 6, 76, '2026-03-07 09:19:12'),
(561, 1, 94, '2026-03-07 09:19:31'),
(562, 1, 92, '2026-03-07 09:19:31'),
(563, 1, 93, '2026-03-07 09:19:31'),
(564, 1, 91, '2026-03-07 09:19:31'),
(565, 1, 2, '2026-03-07 09:19:31'),
(566, 1, 1, '2026-03-07 09:19:31'),
(567, 1, 37, '2026-03-07 09:19:31'),
(568, 1, 39, '2026-03-07 09:19:31'),
(569, 1, 38, '2026-03-07 09:19:31'),
(570, 1, 36, '2026-03-07 09:19:31'),
(571, 1, 75, '2026-03-07 09:19:31'),
(572, 1, 72, '2026-03-07 09:19:31'),
(573, 1, 74, '2026-03-07 09:19:31'),
(574, 1, 73, '2026-03-07 09:19:31'),
(575, 1, 71, '2026-03-07 09:19:31'),
(576, 1, 85, '2026-03-07 09:19:31'),
(577, 1, 106, '2026-03-07 09:19:31'),
(578, 1, 86, '2026-03-07 09:19:31'),
(579, 1, 105, '2026-03-07 09:19:31'),
(580, 1, 84, '2026-03-07 09:19:31'),
(581, 1, 83, '2026-03-07 09:19:31'),
(582, 1, 82, '2026-03-07 09:19:31'),
(583, 1, 87, '2026-03-07 09:19:31'),
(584, 1, 81, '2026-03-07 09:19:31'),
(585, 1, 31, '2026-03-07 09:19:31'),
(586, 1, 30, '2026-03-07 09:19:31'),
(587, 1, 29, '2026-03-07 09:19:31'),
(588, 1, 28, '2026-03-07 09:19:31'),
(589, 1, 100, '2026-03-07 09:19:31'),
(590, 1, 99, '2026-03-07 09:19:31'),
(591, 1, 45, '2026-03-07 09:19:31'),
(592, 1, 44, '2026-03-07 09:19:31'),
(593, 1, 18, '2026-03-07 09:19:31'),
(594, 1, 15, '2026-03-07 09:19:31'),
(595, 1, 17, '2026-03-07 09:19:31'),
(596, 1, 16, '2026-03-07 09:19:31'),
(597, 1, 19, '2026-03-07 09:19:31'),
(598, 1, 21, '2026-03-07 09:19:31'),
(599, 1, 22, '2026-03-07 09:19:31'),
(600, 1, 20, '2026-03-07 09:19:31'),
(601, 1, 14, '2026-03-07 09:19:31'),
(602, 1, 53, '2026-03-07 09:19:31'),
(603, 1, 52, '2026-03-07 09:19:31'),
(604, 1, 51, '2026-03-07 09:19:31'),
(605, 1, 24, '2026-03-07 09:19:31'),
(606, 1, 26, '2026-03-07 09:19:31'),
(607, 1, 25, '2026-03-07 09:19:31'),
(608, 1, 27, '2026-03-07 09:19:31'),
(609, 1, 23, '2026-03-07 09:19:31'),
(610, 1, 41, '2026-03-07 09:19:31'),
(611, 1, 43, '2026-03-07 09:19:31'),
(612, 1, 42, '2026-03-07 09:19:31'),
(613, 1, 40, '2026-03-07 09:19:31'),
(614, 1, 96, '2026-03-07 09:19:31'),
(615, 1, 97, '2026-03-07 09:19:31'),
(616, 1, 98, '2026-03-07 09:19:31'),
(617, 1, 95, '2026-03-07 09:19:31'),
(618, 1, 58, '2026-03-07 09:19:31'),
(619, 1, 57, '2026-03-07 09:19:31'),
(620, 1, 56, '2026-03-07 09:19:31'),
(621, 1, 13, '2026-03-07 09:19:31'),
(622, 1, 10, '2026-03-07 09:19:31'),
(623, 1, 12, '2026-03-07 09:19:31'),
(624, 1, 11, '2026-03-07 09:19:31'),
(625, 1, 9, '2026-03-07 09:19:31'),
(626, 1, 70, '2026-03-07 09:19:31'),
(627, 1, 69, '2026-03-07 09:19:31'),
(628, 1, 68, '2026-03-07 09:19:31'),
(629, 1, 55, '2026-03-07 09:19:31'),
(630, 1, 54, '2026-03-07 09:19:31'),
(631, 1, 80, '2026-03-07 09:19:31'),
(632, 1, 77, '2026-03-07 09:19:31'),
(633, 1, 79, '2026-03-07 09:19:31'),
(634, 1, 78, '2026-03-07 09:19:31'),
(635, 1, 76, '2026-03-07 09:19:31'),
(636, 1, 50, '2026-03-07 09:19:31'),
(637, 1, 47, '2026-03-07 09:19:31'),
(638, 1, 49, '2026-03-07 09:19:31'),
(639, 1, 48, '2026-03-07 09:19:31'),
(640, 1, 46, '2026-03-07 09:19:31'),
(641, 1, 7, '2026-03-07 09:19:31'),
(642, 1, 4, '2026-03-07 09:19:31'),
(643, 1, 6, '2026-03-07 09:19:31'),
(644, 1, 5, '2026-03-07 09:19:31'),
(645, 1, 8, '2026-03-07 09:19:31'),
(646, 1, 3, '2026-03-07 09:19:31'),
(647, 1, 35, '2026-03-07 09:19:31'),
(648, 1, 34, '2026-03-07 09:19:31'),
(649, 1, 33, '2026-03-07 09:19:31'),
(650, 1, 32, '2026-03-07 09:19:31'),
(651, 1, 102, '2026-03-07 09:19:31'),
(652, 1, 104, '2026-03-07 09:19:31'),
(653, 1, 103, '2026-03-07 09:19:31'),
(654, 1, 101, '2026-03-07 09:19:31');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('text','url','email','phone','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `supporter_contributions`
--

CREATE TABLE `supporter_contributions` (
  `id` int(11) NOT NULL,
  `supporter_id` int(11) NOT NULL,
  `cep_session` enum('day','weekend','both') DEFAULT 'both',
  `contribution_type` enum('financial','material','service','prayer','mentorship') NOT NULL DEFAULT 'financial',
  `contribution_subtype` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contribution_date` date NOT NULL,
  `receipt_path` varchar(500) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supporter_contributions`
--

INSERT INTO `supporter_contributions` (`id`, `supporter_id`, `cep_session`, `contribution_type`, `contribution_subtype`, `amount`, `description`, `contribution_date`, `receipt_path`, `recorded_by`, `created_at`) VALUES
(7, 1, 'both', 'financial', NULL, 258700.00, 'Piano Buying', '2026-03-05', NULL, 1, '2026-03-05 00:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `talents_gifts`
--

CREATE TABLE `talents_gifts` (
  `id` int(11) NOT NULL,
  `talent_name` varchar(100) NOT NULL,
  `category` enum('Music','Media','Leadership','Teaching','Evangelism','Service','Other') DEFAULT 'Other',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `talents_gifts`
--

INSERT INTO `talents_gifts` (`id`, `talent_name`, `category`, `is_active`, `created_at`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `role`, `content`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Jean Claude NIYONZIMA', 'Alumni - Class of 2020', 'CEP UoK transformed my university life. I found not just friends, but a family that helped me grow in faith and leadership. The discipleship and mentorship I received prepared me for life beyond campus.', '/img/testimonials/testimonial-1.jpg', 1, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(2, 'Grace UWASE', 'Current Member - Engineering Student', 'Joining CEP was the best decision I made at UoK. The fellowship provided spiritual support during challenging times and helped me balance academics with my walk with God.', '/img/testimonials/testimonial-2.jpg', 2, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(3, 'Patrick HABIMANA', 'Alumni - Church Leader', 'My time at CEP UoK shaped me into the minister I am today. The prayer culture, biblical teaching, and hands-on ministry experience gave me a strong foundation for serving God\'s kingdom.', '/img/testimonials/testimonial-3.jpg', 3, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(4, 'Marie UWERA', 'Current Member - Business Student', 'CEP is more than a fellowship; it\'s a movement. Here, I discovered my spiritual gifts, developed leadership skills, and found my purpose in serving Christ on campus.', '/img/testimonials/testimonial-4.jpg', 4, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45'),
(5, 'David MUGISHA', 'Alumni - Missionary', 'The evangelism training and outreach opportunities at CEP UoK ignited my passion for missions. Today, I\'m serving in rural Rwanda because of what I learned and experienced in this fellowship.', '/img/testimonials/testimonial-5.jpg', 5, 'active', '2026-01-29 15:00:45', '2026-01-29 15:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
  `last_activity` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `member_id`, `firstname`, `lastname`, `username`, `email`, `phone`, `password`, `photo`, `bio`, `email_verified`, `email_verification_token`, `email_verification_expires`, `reset_token`, `reset_expiry`, `last_login`, `login_attempts`, `locked_until`, `status`, `is_adepr_member`, `can_manage_website`, `created_by`, `created_at`, `updated_at`, `last_activity`) VALUES
(1, 1, NULL, 'Super', 'Admin', 'admin', 'admin@cepuok.com', '+250788000000', '$2y$10$cTKQFPz493I5.QQkU1MwzOW.YLOdQKqnHbWzpsnO13eI54jLUnCt6', NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-03-08 14:50:03', 0, NULL, 'active', 1, 1, NULL, '2026-02-13 15:14:23', '2026-03-08 14:50:03', NULL),
(2, 3, 5, 'NIYONZIMA', 'Aaron', 'niyonzima.aaron', 'aaronniyonzima52@gmail.com', '0785729794', '$2y$10$ed2/5nm75Rd.7pie5UiNuOJWdtUnuwMEFoh0.oKz7Yr14lbpjJofa', 'users/699c5f8a848f6_1771855754.jpg', 'Boy to Christ', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'active', 1, 0, 1, '2026-02-23 14:09:14', '2026-03-04 22:19:33', NULL),
(3, 6, 6, 'MUKASHEMA', 'Alice', 'mukashema.alice', 'alicemukashema@gmail.com', '0 787 962 735', '$2y$10$od7IhT1Z89vfkm5T8bzUhepdlGyBA4BGmXDauohyh3HDiRFi6OaJ2', 'users/69a0aef3dab68_1772138227.png', 'Courageous enough', 0, NULL, NULL, NULL, NULL, '2026-03-07 09:20:11', 0, NULL, 'active', 1, 0, 1, '2026-02-26 20:37:08', '2026-03-07 09:20:11', NULL),
(5, 2, 7, 'NTAGAWA', 'David', 'ntagawa.david', 'david.ntagawa@gmail.com', '0791619272', '$2y$10$od7IhT1Z89vfkm5T8bzUhepdlGyBA4BGmXDauohyh3HDiRFi6OaJ2', 'users/69a3d927778da_1772345639.png', '', 0, NULL, NULL, NULL, NULL, '2026-03-05 09:15:25', 0, NULL, 'active', 1, 0, 1, '2026-03-01 06:13:59', '2026-03-05 09:15:25', NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_user_created` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_activity_log (user_id, action, module, record_id, description)
    VALUES (NEW.created_by, 'create', 'users', NEW.id, CONCAT('User created: ', NEW.email));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_user_updated` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO user_activity_log (user_id, action, module, record_id, description)
        VALUES (NEW.id, 'status_change', 'users', NEW.id, CONCAT('Status changed from ', OLD.status, ' to ', NEW.status));
    END IF;
    
    IF OLD.role_id != NEW.role_id THEN
        INSERT INTO user_activity_log (user_id, action, module, record_id, description)
        VALUES (NEW.id, 'role_change', 'users', NEW.id, CONCAT('Role changed from ', OLD.role_id, ' to ', NEW.role_id));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'Action performed (e.g., login, logout, create, edit)',
  `module` varchar(100) DEFAULT NULL COMMENT 'Module/entity affected',
  `record_id` int(11) DEFAULT NULL COMMENT 'ID of affected record',
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity_log`
--

INSERT INTO `user_activity_log` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'create', 'users', 2, 'User created: aaronniyonzima52@gmail.com', NULL, NULL, '2026-02-23 14:09:14'),
(2, 1, 'create', 'users', 3, 'User created: alicemukashema@gmai.com', NULL, NULL, '2026-02-26 20:37:08'),
(3, 2, 'status_change', 'users', 2, 'Status changed from pending to active', NULL, NULL, '2026-02-27 07:40:12'),
(4, 1, 'create', 'users', 5, 'User created: david.ntagawa@gmail.com', NULL, NULL, '2026-03-01 06:13:59'),
(5, 1, 'create', 'users', 6, 'User created: test@cepuok.com', NULL, NULL, '2026-03-04 16:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL COMMENT 'JWT token or session ID',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `description`, `video_url`, `thumbnail_url`, `category`, `duration`, `year`, `views`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to CEP UoK', 'Introduction to our fellowship community', 'https://www.youtube.com/watch?v=NZI3j_XpgWM', NULL, 'Introduction', NULL, 2026, 1250, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
(2, 'Our History', 'Journey of CEP UoK from 2016 to present', 'https://www.youtube.com/watch?v=DaGMZsmDKBU', NULL, 'History', NULL, 2026, 891, 0, 'active', '2026-02-05 12:42:16', '2026-02-06 23:56:22'),
(3, 'Sunday Worship Service', 'Highlights from our weekly worship', 'https://www.youtube.com/watch?v=abc123def', NULL, 'Worship', NULL, 2026, 560, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
(4, 'Annual Conference 2025', 'CEP Annual Conference highlights', 'https://www.youtube.com/watch?v=xyz789abc', NULL, 'Events', NULL, 2025, 1200, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
(5, 'Choir Performance', 'CEP choir ministering in worship', 'https://www.youtube.com/watch?v=def456ghi', NULL, 'Choir', NULL, 2025, 750, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16'),
(6, 'Campus Evangelism', 'Reaching students for Christ', 'https://www.youtube.com/watch?v=ghi789jkl', NULL, 'Evangelism', NULL, 2024, 430, 0, 'active', '2026-02-05 12:42:16', '2026-02-05 12:42:16');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_active_users_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_active_users_summary` (
`role_name` varchar(100)
,`total_users` bigint(21)
,`active_users` decimal(22,0)
,`pending_users` decimal(22,0)
,`active_last_30_days` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_members_with_session`
-- (See below for the actual view)
--
CREATE TABLE `v_members_with_session` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_member_statistics`
-- (See below for the actual view)
--
CREATE TABLE `v_member_statistics` (
`membership_type` varchar(100)
,`total_members` bigint(21)
,`active_members` decimal(22,0)
,`pending_members` decimal(22,0)
,`male_members` decimal(22,0)
,`female_members` decimal(22,0)
,`avg_years_membership` decimal(9,4)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_details`
-- (See below for the actual view)
--
CREATE TABLE `v_user_details` (
`id` int(11)
,`firstname` varchar(100)
,`lastname` varchar(100)
,`username` varchar(100)
,`email` varchar(255)
,`phone` varchar(20)
,`photo` varchar(255)
,`status` enum('pending','active','inactive','suspended')
,`is_adepr_member` tinyint(1)
,`can_manage_website` tinyint(1)
,`last_login` timestamp
,`created_at` timestamp
,`role_id` int(11)
,`role_name` varchar(100)
,`is_super_admin` tinyint(1)
,`permissions` mediumtext
);

-- --------------------------------------------------------

--
-- Structure for view `v_active_users_summary`
--
DROP TABLE IF EXISTS `v_active_users_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_active_users_summary`  AS SELECT `r`.`name` AS `role_name`, count(`u`.`id`) AS `total_users`, sum(case when `u`.`status` = 'active' then 1 else 0 end) AS `active_users`, sum(case when `u`.`status` = 'pending' then 1 else 0 end) AS `pending_users`, sum(case when `u`.`last_login` >= current_timestamp() - interval 30 day then 1 else 0 end) AS `active_last_30_days` FROM (`users` `u` join `roles` `r` on(`u`.`role_id` = `r`.`id`)) GROUP BY `r`.`id`, `r`.`name` ORDER BY count(`u`.`id`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_members_with_session`
--
DROP TABLE IF EXISTS `v_members_with_session`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_members_with_session`  AS SELECT `m`.`id` AS `id`, `m`.`user_id` AS `user_id`, `m`.`membership_type_id` AS `membership_type_id`, `m`.`membership_number` AS `membership_number`, `m`.`firstname` AS `firstname`, `m`.`lastname` AS `lastname`, `m`.`email` AS `email`, `m`.`phone` AS `phone`, `m`.`gender` AS `gender`, `m`.`date_of_birth` AS `date_of_birth`, `m`.`address` AS `address`, `m`.`year_joined_cep` AS `year_joined_cep`, `m`.`cep_session` AS `cep_session`, `m`.`faculty` AS `faculty`, `m`.`program` AS `program`, `m`.`academic_year` AS `academic_year`, `m`.`church_name` AS `church_name`, `m`.`family_id` AS `family_id`, `m`.`church_id` AS `church_id`, `m`.`other_church_name` AS `other_church_name`, `m`.`is_born_again` AS `is_born_again`, `m`.`is_baptized` AS `is_baptized`, `m`.`profile_photo` AS `profile_photo`, `m`.`bio` AS `bio`, `m`.`status` AS `status`, `m`.`approved_by` AS `approved_by`, `m`.`approved_at` AS `approved_at`, `m`.`created_at` AS `created_at`, `m`.`updated_at` AS `updated_at`, `m`.`last_activity` AS `last_activity`, `mt`.`type_name` AS `membership_type_name`, `cf`.`family_name` AS `family_name`, `cf`.`family_code` AS `family_code`, `cf`.`color_code` AS `family_color`, `u`.`email` AS `user_email`, `u`.`status` AS `user_status`, `r`.`name` AS `user_role` FROM ((((`members` `m` left join `membership_types` `mt` on(`m`.`membership_type_id` = `mt`.`id`)) left join `cep_families` `cf` on(`m`.`family_id` = `cf`.`id`)) left join `users` `u` on(`m`.`user_id` = `u`.`id`)) left join `roles` `r` on(`u`.`role_id` = `r`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_member_statistics`
--
DROP TABLE IF EXISTS `v_member_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_member_statistics`  AS SELECT `mt`.`type_name` AS `membership_type`, count(`m`.`id`) AS `total_members`, sum(case when `m`.`status` = 'active' then 1 else 0 end) AS `active_members`, sum(case when `m`.`status` = 'pending' then 1 else 0 end) AS `pending_members`, sum(case when `m`.`gender` = 'Male' then 1 else 0 end) AS `male_members`, sum(case when `m`.`gender` = 'Female' then 1 else 0 end) AS `female_members`, avg(year(curdate()) - `m`.`year_joined_cep`) AS `avg_years_membership` FROM (`members` `m` join `membership_types` `mt` on(`m`.`membership_type_id` = `mt`.`id`)) GROUP BY `mt`.`id`, `mt`.`type_name` ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_details`
--
DROP TABLE IF EXISTS `v_user_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_details`  AS SELECT `u`.`id` AS `id`, `u`.`firstname` AS `firstname`, `u`.`lastname` AS `lastname`, `u`.`username` AS `username`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `u`.`photo` AS `photo`, `u`.`status` AS `status`, `u`.`is_adepr_member` AS `is_adepr_member`, `u`.`can_manage_website` AS `can_manage_website`, `u`.`last_login` AS `last_login`, `u`.`created_at` AS `created_at`, `r`.`id` AS `role_id`, `r`.`name` AS `role_name`, `r`.`is_super_admin` AS `is_super_admin`, group_concat(concat(`p`.`module`,'.',`p`.`action`) separator ',') AS `permissions` FROM (((`users` `u` left join `roles` `r` on(`u`.`role_id` = `r`.`id`)) left join `role_permissions` `rp` on(`r`.`id` = `rp`.`role_id`)) left join `permissions` `p` on(`rp`.`permission_id` = `p`.`id`)) GROUP BY `u`.`id`, `u`.`firstname`, `u`.`lastname`, `u`.`username`, `u`.`email`, `u`.`phone`, `u`.`photo`, `u`.`status`, `u`.`is_adepr_member`, `u`.`can_manage_website`, `u`.`last_login`, `u`.`created_at`, `r`.`id`, `r`.`name`, `r`.`is_super_admin` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget_activities`
--
ALTER TABLE `budget_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_q` (`quarter_id`),
  ADD KEY `idx_pool` (`pool_id`);

--
-- Indexes for table `budget_indicators`
--
ALTER TABLE `budget_indicators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_session_year` (`cep_session`,`academic_year`);

--
-- Indexes for table `budget_lines`
--
ALTER TABLE `budget_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_budget` (`budget_id`);

--
-- Indexes for table `budget_quarters`
--
ALTER TABLE `budget_quarters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quarter` (`indicator_id`,`quarter`),
  ADD KEY `idx_sess_year` (`cep_session`,`academic_year`);

--
-- Indexes for table `cep_families`
--
ALTER TABLE `cep_families`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_family_name_session` (`family_name`,`cep_session`);

--
-- Indexes for table `cep_history_timeline`
--
ALTER TABLE `cep_history_timeline`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cep_sessions`
--
ALTER TABLE `cep_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_type` (`session_type`),
  ADD KEY `idx_is_current` (`is_current`);

--
-- Indexes for table `cep_supporters`
--
ALTER TABLE `cep_supporters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supporter_type` (`supporter_type`),
  ADD KEY `idx_tier` (`tier`);

--
-- Indexes for table `choir_attendance`
--
ALTER TABLE `choir_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rehearsal_member` (`rehearsal_id`,`choir_member_id`),
  ADD KEY `idx_rehearsal` (`rehearsal_id`),
  ADD KEY `idx_member` (`choir_member_id`);

--
-- Indexes for table `choir_members`
--
ALTER TABLE `choir_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_session` (`cep_session`);

--
-- Indexes for table `choir_rehearsals`
--
ALTER TABLE `choir_rehearsals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`rehearsal_date`),
  ADD KEY `fk_rehearsal_conductor` (`conductor_id`);

--
-- Indexes for table `choir_songs`
--
ALTER TABLE `choir_songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `churches`
--
ALTER TABLE `churches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_church_name` (`church_name`);

--
-- Indexes for table `committee_handovers`
--
ALTER TABLE `committee_handovers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credential_wallet`
--
ALTER TABLE `credential_wallet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_platform` (`platform`),
  ADD KEY `idx_added_by` (`added_by`);

--
-- Indexes for table `credential_wallet_audit`
--
ALTER TABLE `credential_wallet_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_credential_id` (`credential_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `idx_disbursed` (`disbursed_by`);

--
-- Indexes for table `finance_budgets`
--
ALTER TABLE `finance_budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`cep_session`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_budget_creator` (`created_by`),
  ADD KEY `fk_budget_approver` (`approved_by`);

--
-- Indexes for table `finance_revenue`
--
ALTER TABLE `finance_revenue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`cep_session`),
  ADD KEY `idx_type` (`revenue_type`),
  ADD KEY `idx_date` (`revenue_date`),
  ADD KEY `idx_recorded` (`recorded_by`);

--
-- Indexes for table `fund_requests`
--
ALTER TABLE `fund_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request_number` (`request_number`),
  ADD KEY `idx_session` (`cep_session`),
  ADD KEY `idx_stage` (`stage`),
  ADD KEY `idx_requester` (`requested_by`),
  ADD KEY `fk_fr_reviewer` (`reviewed_by`),
  ADD KEY `fk_fr_approver` (`approved_by`);

--
-- Indexes for table `fund_request_comments`
--
ALTER TABLE `fund_request_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_req` (`request_id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `gallery_years`
--
ALTER TABLE `gallery_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`);

--
-- Indexes for table `hero_sliders`
--
ALTER TABLE `hero_sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `indicator_pools`
--
ALTER TABLE `indicator_pools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_indicator` (`indicator_id`);

--
-- Indexes for table `leadership_achievements`
--
ALTER TABLE `leadership_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `year_id` (`year_id`);

--
-- Indexes for table `leadership_members`
--
ALTER TABLE `leadership_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `year_id` (`year_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `leadership_positions`
--
ALTER TABLE `leadership_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leadership_years`
--
ALTER TABLE `leadership_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_phone` (`phone`),
  ADD UNIQUE KEY `membership_number` (`membership_number`),
  ADD KEY `idx_membership_type` (`membership_type_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_year_joined` (`year_joined_cep`),
  ADD KEY `fk_member_approver` (`approved_by`),
  ADD KEY `idx_member_email` (`email`),
  ADD KEY `idx_member_status_year` (`status`,`year_joined_cep`),
  ADD KEY `idx_member_created` (`created_at`),
  ADD KEY `idx_cep_session` (`cep_session`),
  ADD KEY `idx_faculty` (`faculty`),
  ADD KEY `idx_family_id` (`family_id`);

--
-- Indexes for table `membership_applications`
--
ALTER TABLE `membership_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_application` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_application_reviewer` (`reviewed_by`);

--
-- Indexes for table `membership_types`
--
ALTER TABLE `membership_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_name` (`type_name`);

--
-- Indexes for table `member_activities`
--
ALTER TABLE `member_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_activity` (`member_id`,`created_at`);

--
-- Indexes for table `member_talents`
--
ALTER TABLE `member_talents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member_talent` (`member_id`,`talent_id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_talent` (`talent_id`);

--
-- Indexes for table `news_events`
--
ALTER TABLE `news_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `category` (`category`),
  ADD KEY `published_date` (`published_date`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_section_unique` (`page_name`,`section_name`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_module_action` (`module`,`action`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_project_code` (`project_code`),
  ADD KEY `idx_session` (`cep_session`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_proj_lead` (`lead_user_id`),
  ADD KEY `fk_proj_creator` (`created_by`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_assigned` (`assigned_to`);

--
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `fk_update_user` (`posted_by`);

--
-- Indexes for table `quick_stats`
--
ALTER TABLE `quick_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stat_name_unique` (`stat_name`);

--
-- Indexes for table `recurring_events`
--
ALTER TABLE `recurring_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `day_of_week` (`day_of_week`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_permission_id` (`permission_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `supporter_contributions`
--
ALTER TABLE `supporter_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supporter_id` (`supporter_id`);

--
-- Indexes for table `talents_gifts`
--
ALTER TABLE `talents_gifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_talent_name` (`talent_name`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_last_login` (`last_login`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_activity_user_date` (`user_id`,`created_at`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`token`(255)),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_sessions_user` (`user_id`),
  ADD KEY `idx_sessions_expires` (`expires_at`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `year` (`year`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget_activities`
--
ALTER TABLE `budget_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budget_indicators`
--
ALTER TABLE `budget_indicators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budget_lines`
--
ALTER TABLE `budget_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `budget_quarters`
--
ALTER TABLE `budget_quarters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cep_families`
--
ALTER TABLE `cep_families`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cep_history_timeline`
--
ALTER TABLE `cep_history_timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cep_sessions`
--
ALTER TABLE `cep_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cep_supporters`
--
ALTER TABLE `cep_supporters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `choir_attendance`
--
ALTER TABLE `choir_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `choir_members`
--
ALTER TABLE `choir_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `choir_rehearsals`
--
ALTER TABLE `choir_rehearsals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `choir_songs`
--
ALTER TABLE `choir_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `churches`
--
ALTER TABLE `churches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `committee_handovers`
--
ALTER TABLE `committee_handovers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credential_wallet`
--
ALTER TABLE `credential_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credential_wallet_audit`
--
ALTER TABLE `credential_wallet_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_budgets`
--
ALTER TABLE `finance_budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `finance_revenue`
--
ALTER TABLE `finance_revenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fund_requests`
--
ALTER TABLE `fund_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fund_request_comments`
--
ALTER TABLE `fund_request_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `gallery_years`
--
ALTER TABLE `gallery_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `hero_sliders`
--
ALTER TABLE `hero_sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `indicator_pools`
--
ALTER TABLE `indicator_pools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leadership_achievements`
--
ALTER TABLE `leadership_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leadership_members`
--
ALTER TABLE `leadership_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `leadership_positions`
--
ALTER TABLE `leadership_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `leadership_years`
--
ALTER TABLE `leadership_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `membership_applications`
--
ALTER TABLE `membership_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `membership_types`
--
ALTER TABLE `membership_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `member_activities`
--
ALTER TABLE `member_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `member_talents`
--
ALTER TABLE `member_talents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `news_events`
--
ALTER TABLE `news_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quick_stats`
--
ALTER TABLE `quick_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `recurring_events`
--
ALTER TABLE `recurring_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=655;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `supporter_contributions`
--
ALTER TABLE `supporter_contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `talents_gifts`
--
ALTER TABLE `talents_gifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget_activities`
--
ALTER TABLE `budget_activities`
  ADD CONSTRAINT `fk_act_pool` FOREIGN KEY (`pool_id`) REFERENCES `indicator_pools` (`id`),
  ADD CONSTRAINT `fk_act_q` FOREIGN KEY (`quarter_id`) REFERENCES `budget_quarters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_lines`
--
ALTER TABLE `budget_lines`
  ADD CONSTRAINT `fk_line_budget` FOREIGN KEY (`budget_id`) REFERENCES `finance_budgets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_quarters`
--
ALTER TABLE `budget_quarters`
  ADD CONSTRAINT `fk_q_ind` FOREIGN KEY (`indicator_id`) REFERENCES `budget_indicators` (`id`);

--
-- Constraints for table `choir_attendance`
--
ALTER TABLE `choir_attendance`
  ADD CONSTRAINT `fk_att_member` FOREIGN KEY (`choir_member_id`) REFERENCES `choir_members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_att_rehearsal` FOREIGN KEY (`rehearsal_id`) REFERENCES `choir_rehearsals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `choir_members`
--
ALTER TABLE `choir_members`
  ADD CONSTRAINT `fk_choir_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `choir_rehearsals`
--
ALTER TABLE `choir_rehearsals`
  ADD CONSTRAINT `fk_rehearsal_conductor` FOREIGN KEY (`conductor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD CONSTRAINT `fk_disb_by_user` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_disb_request` FOREIGN KEY (`request_id`) REFERENCES `fund_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finance_budgets`
--
ALTER TABLE `finance_budgets`
  ADD CONSTRAINT `fk_budget_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_budget_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_revenue`
--
ALTER TABLE `finance_revenue`
  ADD CONSTRAINT `fk_revenue_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fund_requests`
--
ALTER TABLE `fund_requests`
  ADD CONSTRAINT `fk_fr_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fr_requester` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fr_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fund_request_comments`
--
ALTER TABLE `fund_request_comments`
  ADD CONSTRAINT `fk_comm_req` FOREIGN KEY (`request_id`) REFERENCES `fund_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `indicator_pools`
--
ALTER TABLE `indicator_pools`
  ADD CONSTRAINT `fk_pool_ind` FOREIGN KEY (`indicator_id`) REFERENCES `budget_indicators` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_member_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_member_family` FOREIGN KEY (`family_id`) REFERENCES `cep_families` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_member_membership_type` FOREIGN KEY (`membership_type_id`) REFERENCES `membership_types` (`id`),
  ADD CONSTRAINT `fk_member_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `membership_applications`
--
ALTER TABLE `membership_applications`
  ADD CONSTRAINT `fk_application_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_application_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `member_activities`
--
ALTER TABLE `member_activities`
  ADD CONSTRAINT `fk_activity_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `member_talents`
--
ALTER TABLE `member_talents`
  ADD CONSTRAINT `fk_member_talent_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_member_talent_talent` FOREIGN KEY (`talent_id`) REFERENCES `talents_gifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_reset_token_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_proj_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_proj_lead` FOREIGN KEY (`lead_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD CONSTRAINT `fk_task_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD CONSTRAINT `fk_update_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_update_user` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supporter_contributions`
--
ALTER TABLE `supporter_contributions`
  ADD CONSTRAINT `fk_contribution_supporter` FOREIGN KEY (`supporter_id`) REFERENCES `cep_supporters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
