-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2026 at 04:28 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `church_id` int(11) NOT NULL,
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

INSERT INTO `members` (`id`, `user_id`, `membership_type_id`, `membership_number`, `firstname`, `lastname`, `email`, `phone`, `gender`, `date_of_birth`, `address`, `year_joined_cep`, `church_id`, `other_church_name`, `is_born_again`, `is_baptized`, `profile_photo`, `bio`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `last_activity`) VALUES
(1, NULL, 1, NULL, 'ABAYO', 'Remy', 'abaremy1997@gmail.com', '+250787254817', 'Male', '1997-08-17', 'Nyamirambo, Kigali', '2016', 1, '', 'Yes', 'Yes', 'uploads/members/member_698f4987787fd_1770998151.jpg', 'I am Courageous Enough to work with you', 'pending', NULL, NULL, '2026-02-13 15:55:51', '2026-02-13 15:55:51', NULL);

--
-- Triggers `members`
--
DELIMITER $$
CREATE TRIGGER `trg_member_approved` BEFORE UPDATE ON `members` FOR EACH ROW BEGIN
    IF NEW.status = 'active' AND OLD.status = 'pending' AND NEW.membership_number IS NULL THEN
        CALL generate_membership_number(NEW.id, NEW.membership_type_id, NEW.year_joined_cep);
    END IF;
END
$$
DELIMITER ;

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
(1, 1, 'registration', 'Member registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 15:55:51');

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
(58, 'reports', 'create', 'Create custom reports', '2026-02-13 15:14:22');

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
(1, 'Super Admin', 'Full system access with all permissions', 1, '2026-02-13 15:14:22', '2026-02-13 15:14:22'),
(2, 'Admin', 'Administrative access to manage content and members', 0, '2026-02-13 15:14:22', '2026-02-13 15:14:22'),
(3, 'Leader', 'CEP leader with limited administrative access', 0, '2026-02-13 15:14:22', '2026-02-13 15:14:22'),
(4, 'Teacher', 'Educational content management', 0, '2026-02-13 15:14:22', '2026-02-13 15:14:22'),
(5, 'Student', 'Basic member access', 0, '2026-02-13 15:14:22', '2026-02-13 15:14:22'),
(6, 'Parent', 'Parent/guardian access', 0, '2026-02-13 15:14:22', '2026-02-13 15:14:22');

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
(1, 1, 2, '2026-02-13 15:14:22'),
(2, 1, 1, '2026-02-13 15:14:22'),
(3, 1, 37, '2026-02-13 15:14:22'),
(4, 1, 39, '2026-02-13 15:14:22'),
(5, 1, 38, '2026-02-13 15:14:22'),
(6, 1, 36, '2026-02-13 15:14:22'),
(7, 1, 31, '2026-02-13 15:14:22'),
(8, 1, 30, '2026-02-13 15:14:22'),
(9, 1, 29, '2026-02-13 15:14:22'),
(10, 1, 28, '2026-02-13 15:14:22'),
(11, 1, 45, '2026-02-13 15:14:22'),
(12, 1, 44, '2026-02-13 15:14:22'),
(13, 1, 18, '2026-02-13 15:14:22'),
(14, 1, 15, '2026-02-13 15:14:22'),
(15, 1, 17, '2026-02-13 15:14:22'),
(16, 1, 16, '2026-02-13 15:14:22'),
(17, 1, 19, '2026-02-13 15:14:22'),
(18, 1, 21, '2026-02-13 15:14:22'),
(19, 1, 22, '2026-02-13 15:14:22'),
(20, 1, 20, '2026-02-13 15:14:22'),
(21, 1, 14, '2026-02-13 15:14:22'),
(22, 1, 53, '2026-02-13 15:14:22'),
(23, 1, 52, '2026-02-13 15:14:22'),
(24, 1, 51, '2026-02-13 15:14:22'),
(25, 1, 24, '2026-02-13 15:14:22'),
(26, 1, 26, '2026-02-13 15:14:22'),
(27, 1, 25, '2026-02-13 15:14:22'),
(28, 1, 27, '2026-02-13 15:14:22'),
(29, 1, 23, '2026-02-13 15:14:22'),
(30, 1, 41, '2026-02-13 15:14:22'),
(31, 1, 43, '2026-02-13 15:14:22'),
(32, 1, 42, '2026-02-13 15:14:22'),
(33, 1, 40, '2026-02-13 15:14:22'),
(34, 1, 58, '2026-02-13 15:14:22'),
(35, 1, 57, '2026-02-13 15:14:22'),
(36, 1, 56, '2026-02-13 15:14:22'),
(37, 1, 13, '2026-02-13 15:14:22'),
(38, 1, 10, '2026-02-13 15:14:22'),
(39, 1, 12, '2026-02-13 15:14:22'),
(40, 1, 11, '2026-02-13 15:14:22'),
(41, 1, 9, '2026-02-13 15:14:22'),
(42, 1, 55, '2026-02-13 15:14:22'),
(43, 1, 54, '2026-02-13 15:14:22'),
(44, 1, 50, '2026-02-13 15:14:22'),
(45, 1, 47, '2026-02-13 15:14:22'),
(46, 1, 49, '2026-02-13 15:14:22'),
(47, 1, 48, '2026-02-13 15:14:22'),
(48, 1, 46, '2026-02-13 15:14:22'),
(49, 1, 7, '2026-02-13 15:14:22'),
(50, 1, 4, '2026-02-13 15:14:22'),
(51, 1, 6, '2026-02-13 15:14:22'),
(52, 1, 5, '2026-02-13 15:14:22'),
(53, 1, 8, '2026-02-13 15:14:22'),
(54, 1, 3, '2026-02-13 15:14:22'),
(55, 1, 35, '2026-02-13 15:14:22'),
(56, 1, 34, '2026-02-13 15:14:22'),
(57, 1, 33, '2026-02-13 15:14:22'),
(58, 1, 32, '2026-02-13 15:14:22'),
(64, 2, 1, '2026-02-13 15:14:22'),
(65, 2, 37, '2026-02-13 15:14:22'),
(66, 2, 38, '2026-02-13 15:14:22'),
(67, 2, 36, '2026-02-13 15:14:22'),
(68, 2, 30, '2026-02-13 15:14:22'),
(69, 2, 28, '2026-02-13 15:14:22'),
(70, 2, 18, '2026-02-13 15:14:22'),
(71, 2, 15, '2026-02-13 15:14:22'),
(72, 2, 16, '2026-02-13 15:14:22'),
(73, 2, 14, '2026-02-13 15:14:22'),
(74, 2, 51, '2026-02-13 15:14:22'),
(75, 2, 24, '2026-02-13 15:14:22'),
(76, 2, 25, '2026-02-13 15:14:22'),
(77, 2, 27, '2026-02-13 15:14:22'),
(78, 2, 23, '2026-02-13 15:14:22'),
(79, 2, 41, '2026-02-13 15:14:22'),
(80, 2, 42, '2026-02-13 15:14:22'),
(81, 2, 40, '2026-02-13 15:14:22'),
(82, 2, 50, '2026-02-13 15:14:22'),
(83, 2, 47, '2026-02-13 15:14:22'),
(84, 2, 48, '2026-02-13 15:14:22'),
(85, 2, 46, '2026-02-13 15:14:22'),
(86, 2, 34, '2026-02-13 15:14:22'),
(87, 2, 32, '2026-02-13 15:14:22'),
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
(121, 5, 32, '2026-02-13 15:14:23');

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
(1, 1, NULL, 'Super', 'Admin', 'admin', 'admin@cepuok.com', '+250788000000', '$2y$10$cTKQFPz493I5.QQkU1MwzOW.YLOdQKqnHbWzpsnO13eI54jLUnCt6', NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-02-21 14:30:14', 0, NULL, 'active', 1, 1, NULL, '2026-02-13 15:14:23', '2026-02-21 14:30:14', NULL);

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
-- Indexes for table `cep_history_timeline`
--
ALTER TABLE `cep_history_timeline`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `churches`
--
ALTER TABLE `churches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_church_name` (`church_name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_church` (`church_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_year_joined` (`year_joined_cep`),
  ADD KEY `fk_member_approver` (`approved_by`),
  ADD KEY `idx_member_email` (`email`),
  ADD KEY `idx_member_status_year` (`status`,`year_joined_cep`),
  ADD KEY `idx_member_created` (`created_at`);

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
-- AUTO_INCREMENT for table `cep_history_timeline`
--
ALTER TABLE `cep_history_timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `churches`
--
ALTER TABLE `churches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `membership_applications`
--
ALTER TABLE `membership_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_types`
--
ALTER TABLE `membership_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `member_activities`
--
ALTER TABLE `member_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `member_talents`
--
ALTER TABLE `member_talents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_member_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_member_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`),
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
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

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
