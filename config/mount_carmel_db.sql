-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2026 at 02:48 PM
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
-- Database: `mount_carmel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admission_content`
--

CREATE TABLE `admission_content` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `content_type` enum('requirement','fee_structure','registration') DEFAULT 'requirement',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_content`
--

INSERT INTO `admission_content` (`id`, `section_id`, `content_type`, `title`, `content`, `icon`, `display_order`, `metadata`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'requirement', 'Nursery School Requirements', '{\"age\": \"3-5 years old\", \"documents\": [\"Birth certificate\", \"Immunization records\", \"2 passport photos\"], \"assessment\": \"Basic readiness assessment\"}', 'fas fa-baby', 1, '{\"program\": \"nursery\"}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(2, 1, 'requirement', 'Primary School Requirements', '{\"age\": \"6-12 years old\", \"documents\": [\"Birth certificate\", \"Previous school report\", \"Transfer letter (if applicable)\", \"2 passport photos\"], \"assessment\": \"Grade level assessment\"}', 'fas fa-child', 2, '{\"program\": \"primary\"}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(3, 1, 'requirement', 'General Requirements', '{\"parent_interview\": \"Required for all new students\", \"medical_check\": \"Medical certificate from recognized clinic\", \"parent_commitment\": \"Signed agreement to school values\"}', 'fas fa-users', 3, '{\"applies_to\": \"all\"}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(4, 2, 'fee_structure', 'Nursery School Fees', '{\"tuition\":\"800,000 RWF per year\",\"registration\":\"70,000 RWF (one-time)\",\"materials\":\"150,000 RWF per year\",\"transport\":\"Optional: 300,000 RWF per year\"}', 'fas fa-baby-carriage', 1, '{\"program\":\"nursery\",\"payment_terms\":[\"Annual payment: 7% discount\",\"Term payment: 3 terms per year\"]}', 1, '2025-12-21 22:00:27', '2025-12-22 01:13:31'),
(5, 2, 'fee_structure', 'Primary School Fees', '{\"tuition\": \"1,200,000 RWF per year\", \"registration\": \"50,000 RWF (one-time)\", \"materials\": \"200,000 RWF per year\", \"transport\": \"Optional: 300,000 RWF per year\", \"extracurricular\": \"100,000 RWF per year\"}', 'fas fa-graduation-cap', 2, '{\"program\": \"primary\", \"payment_terms\": [\"Annual payment: 5% discount\", \"Term payment: 3 terms per year\"]}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(6, 2, 'fee_structure', 'Additional Fees', '{\"uniform\":\"80,000 RWF (full set)\",\"medical\":\"20,000 RWF per year\",\"field_trips\":\"Varies per activity\",\"Etude\":\"40,000 RWF per Month\",\"swimming\":\"Optional: 155,000 RWF per year\"}', 'fas fa-plus-circle', 3, '{\"optional\":true}', 1, '2025-12-21 22:00:27', '2025-12-22 01:09:03'),
(7, 3, 'registration', 'Application Process', '{\"steps\": [\"Submit online application\", \"Upload required documents\", \"Schedule assessment/interview\", \"Receive admission decision\", \"Complete registration and payment\"], \"timeline\": \"Applications processed within 5 business days\", \"contact\": \"admissions@mountcarmel.ac.rw\"}', 'fas fa-list-ol', 1, '{\"timeline_estimate\": \"2 weeks\"}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(8, 3, 'registration', 'Required Documents', '{\"mandatory\": [\"Completed application form\", \"Birth certificate\", \"Previous school reports\", \"Medical certificate\", \"Passport photos\"], \"additional\": [\"Recommendation letters\", \"Special needs documentation (if applicable)\"]}', 'fas fa-file-alt', 2, '{\"document_count\": 5}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(9, 3, 'registration', 'Important Dates', '{\"admission_cycles\": [\"January Intake\", \"September Intake\"], \"deadlines\": {\"january\": \"December 15th\", \"september\": \"August 15th\"}, \"orientation\": \"One week before classes start\"}', 'fas fa-calendar-alt', 3, '{\"current_cycle\": \"September Intake\"}', 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `admission_documents`
--

CREATE TABLE `admission_documents` (
  `id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `downloads` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_documents`
--

INSERT INTO `admission_documents` (`id`, `section_id`, `content_id`, `title`, `description`, `file_name`, `file_url`, `file_type`, `file_size`, `downloads`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 2, NULL, 'Fees', 'Description on Fees', 'doc_695903c842893_1767441352.pdf', '/uploads/admission_documents/doc_695903c842893_1767441352.pdf', 'PDF', 300527, 0, 1, 1, '2026-01-03 11:55:52', '2026-01-03 13:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `admission_sections`
--

CREATE TABLE `admission_sections` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-info-circle',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_sections`
--

INSERT INTO `admission_sections` (`id`, `title`, `subtitle`, `icon_class`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admission Requirements', 'What you need to join Mount Carmel School', 'fas fa-clipboard-check', 1, 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(2, 'Fee Structure', 'Transparent and affordable tuition fees', 'fas fa-money-bill-wave', 2, 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27'),
(3, 'Online Registration', 'Simple and secure online application', 'fas fa-file-signature', 3, 1, '2025-12-21 22:00:27', '2025-12-21 22:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `person_type` enum('parent','student','prospective_parent','guest','alumni','other') DEFAULT 'parent',
  `inquiry_type` enum('admissions','programs','visit','general','support') DEFAULT 'general',
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `person_type`, `inquiry_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'TEST Too', 'remy.abayo@auca.ac.rw', '+250788678211', 'Admissions Inquiry', 'weww', 'parent', 'admissions', 'new', '2025-12-21 19:54:30', '2025-12-21 19:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `educational_programs`
--

CREATE TABLE `educational_programs` (
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
-- Dumping data for table `educational_programs`
--

INSERT INTO `educational_programs` (`id`, `title`, `subtitle`, `description`, `icon_class`, `image_url`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nursery School', 'Francophone Program', 'Safe and stimulating environment for early childhood development through play-based learning in French.', 'fas fa-baby', NULL, 1, 'active', '2025-12-18 07:26:37', '2025-12-18 07:26:37'),
(2, 'Lower Primary', 'Bilingual Education', 'Comprehensive primary education focusing on foundational skills in both English and French.', 'fas fa-child', NULL, 2, 'active', '2025-12-18 07:26:37', '2025-12-18 07:26:37'),
(3, 'Upper Primary', 'Bilingual Excellence', 'Advanced primary education preparing students for secondary school with strong foundations.', 'fas fa-graduation-cap', '/programs/program-1766367483-856.jpg', 3, 'active', '2025-12-18 07:26:37', '2025-12-22 01:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `facilities_sections`
--

CREATE TABLE `facilities_sections` (
  `id` int(11) NOT NULL,
  `page_type` enum('academic','sports','services') NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-info-circle',
  `featured_image` varchar(500) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `detailed_content` longtext DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities_sections`
--

INSERT INTO `facilities_sections` (`id`, `page_type`, `title`, `slug`, `subtitle`, `icon_class`, `featured_image`, `short_description`, `detailed_content`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'academic', 'Computer Lab', 'computer-lab', 'Technology-Enabled Learning Environment', 'fas fa-laptop-code', '/facilities/computer-lab.jpg', 'Our state-of-the-art computer lab provides students with hands-on experience in technology and digital literacy.', '<h3>Modern Computing Facilities</h3>\r\n<p>Our computer laboratory is equipped with 30 high-performance computers, each featuring the latest software and educational tools. The lab provides a dedicated space for students to develop essential digital skills.</p>\r\n\r\n<h4>Key Features</h4>\r\n<ul>\r\n<li>High-speed internet connectivity</li>\r\n<li>Educational software for programming and design</li>\r\n<li>Interactive whiteboards for collaborative learning</li>\r\n<li>Air-conditioned environment for optimal comfort</li>\r\n<li>Supervised by qualified IT instructors</li>\r\n</ul>\r\n\r\n<h4>Learning Programs</h4>\r\n<p>We offer structured courses in computer basics, programming fundamentals, graphic design, and internet safety. Students learn through practical projects and hands-on activities.</p>', 1, 1, '2025-12-22 15:34:43', '2025-12-22 19:18:10'),
(2, 'academic', 'School Library', 'school-library', 'Knowledge Hub for Young Minds', 'fas fa-book-reader', '/facilities/library.jpg', 'A vibrant learning space with extensive resources to foster reading culture and research skills.', '<h3>Comprehensive Learning Resource Center</h3>\r\n<p>Our library houses over 5,000 books covering various subjects, age groups, and interests. It\'s designed as a quiet space for study, research, and reading pleasure.</p>\r\n\r\n<h4>Collection Highlights</h4>\r\n<ul>\r\n<li>Picture books and early readers for young learners</li>\r\n<li>Reference materials and encyclopedias</li>\r\n<li>Fiction and non-fiction collections</li>\r\n<li>Educational magazines and periodicals</li>\r\n<li>Digital resources and e-books</li>\r\n</ul>\r\n\r\n<h4>Library Activities</h4>\r\n<p>Weekly reading sessions, author visits, book clubs, and research workshops help students develop a lifelong love for reading and learning.</p>', 2, 1, '2025-12-22 15:34:43', '2025-12-22 15:34:43'),
(3, 'sports', 'Sports Activities', 'sports-activities', 'Comprehensive Physical Education Program', 'fas fa-futbol', '/facilities/sports-field.jpg', 'Developing athletic skills and promoting physical fitness through diverse sports programs.', '<h3>Holistic Sports Development</h3>\r\n<p>Our sports program focuses on physical fitness, skill development, teamwork, and sportsmanship through a variety of athletic activities.</p>\r\n\r\n<h4>Available Sports</h4>\r\n<ul>\r\n<li><strong>Football:</strong> Regular training sessions and inter-school matches</li>\r\n<li><strong>Basketball:</strong> Court facilities with professional coaching</li>\r\n<li><strong>Athletics:</strong> Track and field events including running, jumping</li>\r\n<li><strong>Volleyball:</strong> Competitive and recreational play</li>\r\n<li><strong>Traditional Games:</strong> Cultural sports activities</li>\r\n</ul>\r\n\r\n<h4>Training Schedule</h4>\r\n<p>Sports activities are scheduled during physical education classes and after-school hours. We participate in local tournaments and competitions.</p>', 1, 1, '2025-12-22 15:34:43', '2025-12-22 15:34:43'),
(4, 'sports', 'Swimming Courses', 'swimming-courses', 'Water Safety and Swimming Excellence', 'fas fa-swimmer', '/facilities/swimming-pool.jpg', 'Professional swimming instruction in our safe, heated swimming pool facility.', '<h3>Comprehensive Swimming Program</h3>\r\n<p>Our swimming program teaches water safety and swimming skills under the guidance of certified lifeguards and swimming instructors.</p>\r\n\r\n<h4>Course Levels</h4>\r\n<ul>\r\n<li><strong>Beginner:</strong> Water familiarization and basic floating</li>\r\n<li><strong>Intermediate:</strong> Stroke development and breathing techniques</li>\r\n<li><strong>Advanced:</strong> Competitive strokes and endurance training</li>\r\n<li><strong>Safety Skills:</strong> Water rescue and survival techniques</li>\r\n</ul>\r\n\r\n<h4>Facility Features</h4>\r\n<ul>\r\n<li>Heated 25-meter swimming pool</li>\r\n<li>Separate shallow pool for beginners</li>\r\n<li>Certified lifeguards on duty</li>\r\n<li>Changing rooms and shower facilities</li>\r\n<li>Safety equipment and first aid station</li>\r\n</ul>', 2, 1, '2025-12-22 15:34:43', '2025-12-22 15:34:43'),
(5, 'services', 'School Feeding', 'school-feeding', 'Nutritious Meals for Growing Minds', 'fas fa-utensils', '/facilities/dining-hall.jpg', 'Balanced, hygienic meals prepared in our modern kitchen facility.', '<h3>Nutrition Program</h3>\r\n<p>Our school feeding program provides nutritious meals that support students\' physical and cognitive development.</p>\r\n\r\n<h4>Meal Planning</h4>\r\n<ul>\r\n<li>Nutritionist-approved weekly menus</li>\r\n<li>Locally sourced fresh ingredients</li>\r\n<li>Variety of balanced meals</li>\r\n<li>Special dietary accommodations available</li>\r\n<li>Hygiene and food safety protocols</li>\r\n</ul>\r\n\r\n<h4>Dining Experience</h4>\r\n<p>Students enjoy meals in our spacious, clean dining hall. We promote healthy eating habits and table manners through supervised meal times.</p>\r\n\r\n<h4>Food Safety Standards</h4>\r\n<p>Our kitchen follows strict hygiene standards with trained staff, regular health inspections, and proper food storage practices.</p>', 1, 1, '2025-12-22 15:34:43', '2025-12-22 15:34:43'),
(6, 'services', 'School Transport', 'school-transport', 'Safe and Reliable Transportation', 'fas fa-bus', '/facilities/school-bus.jpg', 'Comfortable and secure transport services for students.', '<h3>Transportation Services</h3>\r\n<p>Our fleet of modern school buses provides safe and reliable transportation with trained drivers and attendants.</p>\r\n\r\n<h4>Safety Features</h4>\r\n<ul>\r\n<li>GPS-equipped vehicles</li>\r\n<li>First aid kits on board</li>\r\n<li>Seat belts for all passengers</li>\r\n<li>Trained female attendants</li>\r\n<li>Regular vehicle maintenance</li>\r\n</ul>\r\n\r\n<h4>Routes and Schedule</h4>\r\n<p>We cover major residential areas with optimized routes. Parents receive real-time updates about bus locations and schedules.</p>\r\n\r\n<h4>Registration Process</h4>\r\n<p>Transport services are available through registration. Parents can choose from different route options based on their location.</p>', 2, 1, '2025-12-22 15:34:43', '2025-12-22 15:34:43');

-- --------------------------------------------------------

--
-- Table structure for table `facility_features`
--

CREATE TABLE `facility_features` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_features`
--

INSERT INTO `facility_features` (`id`, `facility_id`, `title`, `description`, `icon`, `display_order`, `created_at`) VALUES
(1, 1, 'High-Speed Computers', '30 modern computers with latest specifications', 'fas fa-desktop', 1, '2025-12-22 15:34:43'),
(2, 1, 'Educational Software', 'Programming and design software for learning', 'fas fa-cogs', 2, '2025-12-22 15:34:43'),
(3, 1, 'Interactive Learning', 'Smart boards for collaborative sessions', 'fas fa-chalkboard-teacher', 3, '2025-12-22 15:34:43'),
(4, 2, 'Extensive Collection', '5000+ books across various subjects', 'fas fa-books', 1, '2025-12-22 15:34:43'),
(5, 2, 'Reading Area', 'Comfortable seating for quiet reading', 'fas fa-couch', 2, '2025-12-22 15:34:43'),
(6, 2, 'Digital Resources', 'E-books and online databases', 'fas fa-tablet-alt', 3, '2025-12-22 15:34:43'),
(7, 3, 'Professional Coaching', 'Certified sports instructors', 'fas fa-user-tie', 1, '2025-12-22 15:34:43'),
(8, 3, 'Modern Equipment', 'Quality sports gear and equipment', 'fas fa-baseball-ball', 2, '2025-12-22 15:34:43'),
(9, 4, 'Certified Lifeguards', 'Trained professionals on duty', 'fas fa-life-ring', 1, '2025-12-22 15:34:43'),
(10, 4, 'Heated Pool', 'Temperature-controlled swimming pool', 'fas fa-thermometer-half', 2, '2025-12-22 15:34:43'),
(11, 5, 'Nutritionist Approved', 'Balanced meal plans', 'fas fa-clipboard-check', 1, '2025-12-22 15:34:43'),
(12, 5, 'Hygienic Kitchen', 'Modern kitchen with safety standards', 'fas fa-shield-alt', 2, '2025-12-22 15:34:43'),
(13, 6, 'GPS Tracking', 'Real-time bus location tracking', 'fas fa-map-marker-alt', 1, '2025-12-22 15:34:43'),
(14, 6, 'Safety Attendants', 'Trained female attendants on board', 'fas fa-user-shield', 2, '2025-12-22 15:34:43');

-- --------------------------------------------------------

--
-- Table structure for table `facility_images`
--

CREATE TABLE `facility_images` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_images`
--

INSERT INTO `facility_images` (`id`, `facility_id`, `title`, `filename`, `image_url`, `thumbnail_url`, `description`, `display_order`, `is_featured`, `created_at`) VALUES
(1, 1, 'Computer Laboratory Interior', 'computer-lab-1766430694-5188.jpg', '/facilities/computer-lab-1766430694-5188.jpg', NULL, '', 0, 1, '2025-12-22 17:58:19'),
(2, 1, 'Student get Enough access', 'computer-lab-1766430949-6093.jpg', '/facilities/computer-lab-1766430949-6093.jpg', NULL, '', 0, 0, '2025-12-22 19:15:49'),
(3, 4, 'Swimming Course', 'swimming-courses-1766434412-1044.jpg', '/facilities/swimming-courses-1766434412-1044.jpg', NULL, '', 0, 0, '2025-12-22 20:13:32'),
(4, 4, 'Swimming Joy', 'swimming-courses-1766434520-8281.jpg', '/facilities/swimming-courses-1766434520-8281.jpg', NULL, '', 0, 0, '2025-12-22 20:15:20');

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
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `title`, `description`, `image_url`, `thumbnail_url`, `category`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Classroom Activities', 'Students engaged in interactive learning', '/gallery/sport-1.jpeg', NULL, 'academics', 1, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(2, 'Science Laboratory', 'Modern science lab facilities', '/gallery/sport-2.jpeg', NULL, 'facilities', 2, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(3, 'Sports Day Event', 'Annual sports day celebration', '/gallery/sport-3.jpg', NULL, 'events', 3, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(4, 'Library Study', 'Students reading in our library', '/gallery/sport-4.jpeg', NULL, 'facilities', 4, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(5, 'Music Class', 'Learning musical instruments', '/gallery/student-4.jpg', NULL, 'extracurricular', 5, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(6, 'Art Exhibition', 'Student artwork display', '/gallery/student-5.jpg', NULL, 'events', 6, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(7, 'Computer Lab', 'Technology-equipped learning space', '/gallery/student-6.jpg', NULL, 'facilities', 7, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(8, 'Graduation Ceremony', 'Annual graduation celebration', '/gallery/student-7.jpg', NULL, 'events', 8, 'active', '2025-11-25 20:52:49', '2025-11-28 07:51:09'),
(9, 'Playground Activities', 'Students enjoying outdoor time', '/gallery/Gallery-4850.jpeg', NULL, 'campus', 9, 'active', '2025-11-25 20:52:49', '2025-12-15 13:51:57'),
(10, 'Assembly Hall', 'Morning assembly gathering', '/gallery/Gallery-2121.jpg', NULL, 'campus', 10, 'active', '2025-11-25 20:52:49', '2025-12-15 13:52:43'),
(11, 'Mathematics Class', 'Students solving complex math problems in our modern classroom', '/gallery/Gallery-4203.jpg', NULL, 'academics', 11, 'active', '2025-12-20 12:52:05', '2025-12-27 11:30:32'),
(12, 'Chemistry Experiment', 'Hands-on chemistry experiment in our science lab', 'gallery/lab-chemistry.jpg', NULL, 'academics', 12, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(13, 'Literature Discussion', 'English literature class discussing classic novels', '/gallery/Gallery-8395.jpg', NULL, 'academics', 13, 'active', '2025-12-20 12:52:05', '2025-12-21 09:46:03'),
(14, 'Computer Programming', 'Students learning computer programming skills', 'gallery/computer-class.jpg', NULL, 'academics', 14, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(15, 'History Lesson', 'Interactive history lesson about Rwandan culture', 'gallery/history-class.jpg', NULL, 'academics', 15, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(16, 'Cultural Day', 'Annual cultural day celebration with traditional performances', 'gallery/cultural-day.jpg', NULL, 'events', 16, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(17, 'Graduation 2024', 'Graduation ceremony for the class of 2024', 'gallery/graduation-2024.jpg', NULL, 'events', 17, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(18, 'Parents Day', 'Parents visiting classrooms and meeting teachers', 'gallery/parents-day.jpg', NULL, 'events', 18, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(19, 'Career Fair', 'Annual career fair with industry professionals', 'gallery/career-fair.jpg', NULL, 'events', 19, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(20, 'Founders Day', 'Celebration of school founders and history', 'gallery/founders-day.jpg', NULL, 'events', 20, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(21, 'Library Interior', 'Modern library with extensive book collection', 'gallery/library-interior.jpg', NULL, 'facilities', 21, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(22, 'Science Lab Equipment', 'State-of-the-art science laboratory equipment', 'gallery/lab-equipment.jpg', NULL, 'facilities', 22, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(23, 'Sports Complex', 'Full sports complex with track and field', 'gallery/sports-complex.jpg', NULL, 'facilities', 23, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(25, 'Administration Block', 'Modern administration building', 'gallery/admin-block.jpg', NULL, 'facilities', 25, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(26, 'Football Team', 'School football team during practice', 'gallery/football-team.jpg', NULL, 'extracurricular', 26, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(27, 'Music Band Performance', 'School band performing at annual concert', 'gallery/band-performance.jpg', NULL, 'extracurricular', 27, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(28, 'Art Club Exhibition', 'Student artwork displayed in art exhibition', 'gallery/art-exhibition.jpg', NULL, 'extracurricular', 28, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(29, 'Drama Rehearsal', 'Drama club rehearsing for school play', 'gallery/drama-rehearsal.jpg', NULL, 'extracurricular', 29, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(30, 'Debate Competition', 'Students participating in debate competition', 'gallery/debate-competition.jpg', NULL, 'extracurricular', 30, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(31, 'Morning Assembly', 'Daily morning assembly in the school courtyard', 'gallery/morning-assembly.jpg', NULL, 'campus', 31, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(32, 'Student Break Time', 'Students relaxing during break time', 'gallery/break-time.jpg', NULL, 'campus', 32, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(33, 'Study Group', 'Students studying together in common area', 'gallery/study-group.jpg', NULL, 'campus', 33, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(34, 'Gardening Club', 'Students tending to school garden', 'gallery/gardening-club.jpg', NULL, 'campus', 34, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(35, 'Campus Aerial View', 'Aerial view of Mount Carmel School campus', 'gallery/aerial-view.jpg', NULL, 'campus', 35, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(36, 'Physics Lab Session', 'Students conducting physics experiments', 'gallery/physics-lab.jpg', NULL, 'academics', 36, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(37, 'Annual Sports Meet', 'Track and field events during sports meet', 'gallery/sports-meet.jpg', NULL, 'events', 37, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(38, 'Computer Lab Work', 'Students working on computer projects', 'gallery/computer-lab-work.jpg', NULL, 'facilities', 38, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(39, 'Basketball Practice', 'Basketball team practice session', 'gallery/basketball-practice.jpg', NULL, 'extracurricular', 39, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(40, 'Campus Pathways', 'Beautiful pathways around the campus', 'gallery/campus-pathways.jpg', NULL, 'campus', 40, 'active', '2025-12-20 12:52:05', '2025-12-20 12:52:05'),
(41, 'Colidors', 'Everywhere we assure cleanness', '/gallery/Gallery-169.jpg', NULL, 'facilities', 12, 'active', '2025-12-21 09:47:09', '2025-12-21 14:55:12'),
(42, 'Gallery Image 1', 'They all shows different parts of our School', '/gallery/Bulk-1-1767043818-6222.jpg', NULL, 'academics', 18, 'active', '2025-12-29 21:30:18', '2025-12-29 21:30:57'),
(43, 'Gallery Image 2', 'They all shows different parts of our School', '/gallery/Bulk-2-1767043818-8302.jpg', NULL, 'academics', 16, 'active', '2025-12-29 21:30:18', '2025-12-29 21:30:49');

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
(1, 'Start Your Beautiful And Bright Future', 'Excellence in Education', 'Empowering students to achieve excellence through quality education, modern facilities, and dedicated faculty members.', '/slider/Slider-3232.jpg', 'Explore Programs', '#programs', 'Contact Us', '/contact', 1, 'active', '2025-11-29 10:50:53', '2025-12-15 10:15:06'),
(2, 'Excellence In Education Since 2013', 'Building Future Leaders', 'Building tomorrow\'s leaders through comprehensive learning programs and character development.', '/slider/Slider-6031.jpg', 'Apply Now', '#admissions', 'Learn More', '/about', 2, 'active', '2025-11-29 10:50:53', '2025-12-15 10:18:16'),
(3, 'Modern Facilities Expert Teachers', 'Quality Education', 'State-of-the-art infrastructure combined with experienced educators for the best learning experience.', '/slider/slider-3.jpg', 'Our Facilities', '#facilities', 'View Gallery', '/gallery', 3, 'active', '2025-11-29 10:50:53', '2025-11-29 10:50:53');

-- --------------------------------------------------------

--
-- Table structure for table `leadership_team`
--

CREATE TABLE `leadership_team` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `role_badge` varchar(50) DEFAULT 'Leadership',
  `short_bio` text DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leadership_team`
--

INSERT INTO `leadership_team` (`id`, `full_name`, `position`, `role_badge`, `short_bio`, `qualifications`, `email`, `phone`, `image_url`, `facebook_url`, `twitter_url`, `linkedin_url`, `whatsapp_number`, `display_order`, `join_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SIBOMANA GERARD', 'Acting Legal Representative', 'Legal', 'Overseeing legal matters and ensuring compliance with educational regulations. With extensive experience in educational law and administration.', 'LL.B in Law, MBA in Educational Management', 'sibomana.gerard@mountcarmel.ac.rw', '+250 788 111 222', 'leadership/1766697008_Mount Carmel Admin Staff Photos-1.png', NULL, NULL, NULL, NULL, 1, '2022-01-15', 'active', '2025-12-23 07:25:12', '2025-12-25 21:10:08'),
(2, 'TUMUSIIME JOSEPH', 'School Director', 'Director', 'Leading the school with vision and dedication. Committed to academic excellence and holistic development of students.', 'M.Ed in Educational Leadership, PhD in Education', 'tumusiime.joseph@mountcarmel.ac.rw', '+250 788 222 333', 'leadership/leader_2_1766738839.png', NULL, NULL, NULL, NULL, 2, '2020-03-10', 'active', '2025-12-23 07:25:12', '2025-12-26 08:47:19'),
(3, 'BAHATI Guerschom', 'Deputy Director', 'Deputy', 'Supporting the director in school administration and daily operations. Special focus on academic programs and teacher development.', 'M.Sc in Educational Management, B.Ed', 'bahati.guerschom@mountcarmel.ac.rw', '+250 788 333 444', '/leadership/bahati.jpg', NULL, NULL, NULL, NULL, 3, '2021-06-01', 'active', '2025-12-23 07:25:12', '2025-12-23 07:25:12'),
(4, 'RUREMESHA Habib', 'Accountant', 'Finance', 'Managing school finances, budgeting, and financial planning. Ensuring transparent financial operations.', 'CPA, B.Com in Accounting', 'ruremesha.habib@mountcarmel.ac.rw', '+250 788 444 555', '/leadership/ruremesha.jpg', NULL, NULL, NULL, NULL, 4, '2019-08-20', 'active', '2025-12-23 07:25:12', '2025-12-23 07:25:12'),
(5, 'MUKABALISA Agnes', 'Assistant Administration', 'Admin', 'Handling administrative tasks, records management, and office coordination. Ensuring smooth daily operations.', 'Diploma in Office Management, Secretarial Studies', 'mukabalisa.agnes@mountcarmel.ac.rw', '+250 788 555 666', '/leadership/mukabalisa.jpg', NULL, NULL, NULL, NULL, 5, '2022-02-28', 'active', '2025-12-23 07:25:12', '2025-12-23 07:25:12'),
(6, 'HABIMANA Abel', 'Receptionist', 'Reception', 'First point of contact for visitors, parents, and students. Managing front desk operations and communications.', 'Certificate in Customer Service, Diploma in Communication', 'habimana.abel@mountcarmel.ac.rw', '+250 788 666 777', '/leadership/habimana.jpg', NULL, NULL, NULL, NULL, 6, '2023-01-10', 'active', '2025-12-23 07:25:12', '2025-12-23 07:25:12');

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
  `event_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_events`
--

INSERT INTO `news_events` (`id`, `title`, `excerpt`, `description`, `image_url`, `thumbnail_url`, `category`, `author`, `published_date`, `end_date`, `status`, `views`, `created_at`, `updated_at`, `featured`, `event_location`, `event_time`, `end_time`) VALUES
(1, 'Annual Sports Day 2024', 'Join us for our exciting Annual Sports Day featuring various competitions and activities for all students.', '<p>Our Annual Sports Day is a celebration of athleticism, teamwork, and school spirit. Students from all grades participate in various sporting events including track and field, basketball, football, and many more. This year promises to be even more exciting with new activities and competitions. Parents and guardians are warmly invited to attend and cheer for their children.</p>\r\n', '/news/news-1.jpg', NULL, 'event', 'Admin', '2026-01-02', '0000-00-00', 'published', 12, '2025-11-25 21:57:33', '2025-12-22 21:05:42', 0, 'School Playground', '09:00:00', '00:00:00'),
(2, 'Science Fair Winners', 'Congratulations to our students who won top prizes at the Regional Science Fair competition.', 'We are proud to announce that our students have excelled at the Regional Science Fair, bringing home multiple awards including first place in the Biology category and second place in Physics. Their innovative projects and dedication to scientific inquiry have made Mount Carmel School proud. Special congratulations to Sarah Mugisha, John Kamanzi, and their team members for their outstanding achievements.', '/news/news-2.jpg', NULL, 'achievement', 'Admin', '2025-12-14', NULL, 'published', 9, '2025-11-25 21:57:33', '2025-12-25 21:40:35', 0, 'Science Lab', '10:00:00', NULL),
(3, 'Parent-Teacher Meeting', 'Quarterly parent-teacher meeting scheduled to discuss student progress and development.', '<p>We invite all parents to attend our quarterly parent-teacher meeting where we will discuss student progress, upcoming events, and ways parents can support their children&#39;s learning at home. This is an excellent opportunity to meet with teachers, understand your child&#39;s academic journey, and participate in planning for the next term. Refreshments will be served.</p>\r\n', '/news/news-3.jpg', NULL, 'announcement', 'Admin', '2025-12-26', '0000-00-00', 'published', 15, '2025-11-25 21:57:33', '2025-12-25 21:40:22', 1, 'Main Hall', '14:00:00', '00:00:00'),
(4, 'New Library Inauguration', 'Our new state-of-the-art library has been officially inaugurated with over 5000 books.', 'Mount Carmel School is proud to announce the inauguration of our new library facility, equipped with modern amenities, comfortable reading spaces, and over 5,000 books covering various subjects and interests. The library features dedicated sections for different age groups, computer stations for research, and quiet study areas. We thank all our donors and supporters who made this project possible.', '/news/news-4.jpg', NULL, 'news', 'Admin', '2025-12-19', NULL, 'published', 7, '2025-11-25 21:57:33', '2025-12-19 00:19:28', 0, 'Library', '15:00:00', NULL),
(5, 'New Computer Lab Opens', 'Our new computer lab with 30 modern computers is now open for students.', 'Mount Carmel School is excited to announce the opening of our new state-of-the-art computer laboratory. Equipped with 30 modern computers, high-speed internet, and educational software, this facility will enhance digital literacy among our students.', '/news/computer-lab.jpg', NULL, 'news', 'Admin', '2024-11-15', NULL, 'published', 0, '2025-12-06 22:03:27', '2025-12-06 22:03:27', 0, NULL, NULL, NULL),
(6, 'Math Olympiad Winners', 'Our students win gold medals in the National Math Olympiad competition.', 'Congratulations to our mathematics team for winning three gold medals in the National Math Olympiad. Their dedication and problem-solving skills have brought great honor to our school.', '/news/math-olympiad.jpg', NULL, 'achievement', 'Admin', '2024-11-10', NULL, 'published', 0, '2025-12-06 22:03:27', '2025-12-06 22:03:27', 0, NULL, NULL, NULL),
(7, 'Thanksgiving Celebration', 'Join us for our annual Thanksgiving celebration and cultural show.', 'Our annual Thanksgiving celebration will feature cultural performances, traditional food, and expressions of gratitude. All parents and guardians are invited to join this joyful occasion.', '/news/thanksgiving.jpg', NULL, 'event', 'Admin', '2025-12-09', NULL, 'published', 4, '2025-12-06 22:03:27', '2025-12-18 19:21:05', 0, NULL, NULL, NULL),
(8, 'Exam Schedule Released', 'Important: End of term examination schedule is now available.', 'The end of term examination schedule has been released. Please check with class teachers for detailed timetables and preparation guidelines.', '/news/exam-schedule.jpg', NULL, 'announcement', 'Admin', '2024-11-12', NULL, 'published', 1, '2025-12-06 22:03:27', '2025-12-15 13:23:16', 0, NULL, NULL, NULL),
(9, 'Environmental Club Plantation Drive', 'Students plant 100 trees in the school campus as part of environmental awareness.', 'Our Environmental Club organized a successful plantation drive, planting 100 trees across the school campus. This initiative promotes environmental conservation and sustainability.', '/news/plantation-drive.jpg', NULL, 'news', 'Admin', '2024-11-08', NULL, 'published', 1, '2025-12-06 22:03:27', '2025-12-18 19:21:21', 0, NULL, NULL, NULL),
(10, 'Annual Day Preparations', 'Rehearsals for Annual Day celebrations begin next week.', 'Preparations for our Annual Day celebrations are underway. Students will participate in various performances showcasing their talents in music, dance, and drama.', '/news/annual-day.jpg', NULL, 'event', 'Admin', '2025-12-15', NULL, 'published', 8, '2025-12-06 22:03:27', '2025-12-19 00:16:59', 0, NULL, NULL, NULL),
(11, 'Debate Competition Results', 'Mount Carmel debaters win inter-school debate competition.', 'Our debate team emerged victorious in the Inter-School Debate Competition, showcasing exceptional oratory skills and critical thinking abilities.', '/news/debate.jpg', NULL, 'achievement', 'Admin', '2024-11-05', NULL, 'published', 1, '2025-12-06 22:03:27', '2025-12-21 20:47:28', 0, NULL, NULL, NULL),
(12, 'Parent Workshop: Digital Safety', 'Free workshop for parents on digital safety and cyber security.', 'Learn how to keep your children safe online in our special workshop for parents. Experts will share practical tips and strategies for digital safety.', '/news/digital-safety.jpg', NULL, 'announcement', 'Admin', '2024-11-18', NULL, 'published', 1, '2025-12-06 22:03:27', '2025-12-15 16:03:28', 0, NULL, NULL, NULL),
(14, 'Sports Equipment Donation', 'Local business donates new sports equipment to the school.', '<p>We thank XYZ Sports for their generous donation of new sports equipment including footballs, basketballs, and athletic gear.</p>\r\n', '/news/News-8260.jpg', NULL, 'news', 'Admin', '2024-11-03', '0000-00-00', 'published', 4, '2025-12-06 22:03:27', '2025-12-21 16:36:50', 0, '', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `organization_chart`
--

CREATE TABLE `organization_chart` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Organization Structure',
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organization_chart`
--

INSERT INTO `organization_chart` (`id`, `title`, `description`, `image_url`, `updated_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Organization Structure', 'Our hierarchical framework ensuring effective communication and management', '/organization-chart.png', NULL, 'active', '2025-12-22 10:03:41', '2025-12-22 10:03:41');

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
(1, 'about', 'who_we_are', 'Who We Are', '<h3>Building Tomorrow\'s Leaders</h3>\r\n<p>Mount Carmel School is a nurturing bilingual institution founded in 2013 by Reverend Pastor Jeanne D\'Arc Uwanyiligira. We are dedicated to providing quality education that combines academic excellence with spiritual growth.</p>\r\n<p>Our commitment extends beyond academics. We nurture curiosity, character, and a lifelong love of learning through comprehensive programs in academics, outdoor education, arts, sports, leadership, and community service.</p>', '/about/school-venue-1.jpg', 1, 'active', '2025-12-13 11:35:40', '2025-12-13 11:35:40'),
(2, 'about', 'mission', 'Our Mission', '<p>To train child to honor GOD, develop their potential skills; achieve excellence in academics, wisdom and character.</p>\r\n', '/about/mission2.png', 2, 'active', '2025-12-13 11:35:40', '2025-12-15 12:57:09'),
(3, 'about', 'vision', 'Our Vision', 'To bless Rwanda with GOD fearing citizens, highly skilled and generation transformers for GOD\'S glory.', '/about/vision.png', 3, 'active', '2025-12-13 11:35:40', '2025-12-13 11:35:40'),
(4, 'about', 'philosophy', 'Our Philosophy', 'We believe in holistic education that nurtures the mind, body, and spirit. Every child is unique and capable of excellence when given proper guidance, support, and a nurturing environment rooted in faith.', '/about/philosophy.png', 4, 'active', '2025-12-13 11:35:40', '2025-12-13 11:35:40'),
(5, 'about', 'history', 'Our History', '<h3>A Legacy of Faith and Excellence</h3>\r\n<p>Mount Carmel School was founded in 2013 by <strong>Reverend Pastor Jeanne D\'Arc Uwanyiligira</strong>, who was inspired by a deep desire to promote quality education rooted in Christian values.</p>', '/about/school-venue-2.jpg', 5, 'active', '2025-12-13 11:35:40', '2025-12-13 11:35:40'),
(6, 'home', 'director_message', 'A Message from Our Director', '<h2>A Letter from the Acting Legal Representative</h2>\r\n<p class=\"letter-greeting\">Dear Parents and Guardians,</p>\r\n<p>It is with great joy and privilege that I welcome you to <span class=\"highlight-text\">Mount Carmel School</span>, a nurturing bilingual institution committed to academic excellence and strong Christian values.</p>', '/director-photo.jpg', 1, 'active', '2025-12-13 11:35:40', '2025-12-13 11:35:40'),
(7, 'about', 'core_values', 'Our Core Values', 'Academic Excellence, Stewardship, Hard Work & Unity, Patriotism, Discipleship, Wisdom, Integrity, Love for All', NULL, 6, 'active', '2025-12-13 11:35:40', '2025-12-13 11:35:40'),
(8, 'home', 'welcome_section_title', NULL, 'Welcome to Mount Carmel School', NULL, 1, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(9, 'home', 'welcome_section_video', NULL, 'https://www.youtube.com/embed/NZI3j_XpgWM', NULL, 2, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(10, 'home', 'welcome_intro_head', NULL, 'Excellence in Education Since 2013', NULL, 3, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(11, 'home', 'welcome_intro_paragraph', NULL, 'Mount Carmel School is a nurturing bilingual institution founded by Reverend Pastor Jeanne D\'Arc Uwanyiligira, dedicated to providing quality education that combines academic excellence with spiritual growth.', NULL, 4, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(12, 'home', 'welcome_quote_title', NULL, 'Vision:', NULL, 5, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(13, 'home', 'welcome_quote_content', NULL, 'To bless Rwanda with GOD fearing citizens, highly skilled and generation transformers for GOD\'S glory.', NULL, 6, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(14, 'home', 'dir_letter_section_title', NULL, 'A Message from Our Director', NULL, 7, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(15, 'home', 'director_name', 'Director Name', '<p>SIBOMANA Gérard</p>', '', 8, 'active', '2025-12-18 07:27:41', '2025-12-18 09:32:03'),
(16, 'home', 'director_role', NULL, 'Acting Legal Representative', NULL, 9, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(17, 'home', 'letter_text_title', NULL, 'A Letter from the Acting Legal Representative', NULL, 10, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(18, 'home', 'letter_greeting', NULL, 'Dear Parents and Guardians,', NULL, 11, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(19, 'home', 'letter_paragraph_1', NULL, 'It is with great joy and privilege that I welcome you to <span class=\"highlight-text\">Mount Carmel School</span>, a nurturing bilingual institution committed to academic excellence and strong Christian values.', NULL, 12, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(20, 'home', 'letter_paragraph_2', NULL, 'Since our establishment in 2013, we have remained dedicated to offering quality education that shapes the mind, heart, and character of every learner. Our mission is to raise God-fearing, skilled, and responsible young people who will positively impact their communities and the nation.', NULL, 13, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(21, 'home', 'letter_paragraph_3', NULL, 'At Mount Carmel School, we believe in helping students grow beyond their limits. Through a supportive learning environment, experienced teachers, small class sizes, and a balanced approach to academics, leadership, creativity, and service, we focus on developing well-rounded individuals prepared for future opportunities.', NULL, 14, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(22, 'home', 'letter_signature_name', NULL, 'SIBOMANA Gérard', NULL, 15, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(23, 'home', 'letter_signature_role', NULL, 'Acting Legal Representative', NULL, 16, 'active', '2025-12-18 07:27:41', '2025-12-18 07:27:41'),
(24, 'home', 'director_photo', 'Director Photo', '<h3 class=\"ql-align-center\"><strong>SIBOMANA Gérard</strong></h3><p class=\"ql-align-center\">Acting Legal Representative</p><p><br></p>', '/home-director_photo-5700.png', 0, 'active', '2025-12-22 21:23:19', '2025-12-22 21:23:19');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `module`, `action`, `created_at`) VALUES
(1, 'view_dashboard', 'View dashboard', 'dashboard', 'view', '2025-12-09 16:32:49'),
(2, 'view_users', 'View users list', 'users', 'view', '2025-12-09 16:32:49'),
(3, 'create_users', 'Create new users', 'users', 'create', '2025-12-09 16:32:49'),
(4, 'edit_users', 'Edit users', 'users', 'edit', '2025-12-09 16:32:49'),
(5, 'delete_users', 'Delete users', 'users', 'delete', '2025-12-09 16:32:49'),
(6, 'view_roles', 'View roles', 'roles', 'view', '2025-12-09 16:32:49'),
(7, 'create_roles', 'Create roles', 'roles', 'create', '2025-12-09 16:32:49'),
(8, 'edit_roles', 'Edit roles', 'roles', 'edit', '2025-12-09 16:32:49'),
(9, 'delete_roles', 'Delete roles', 'roles', 'delete', '2025-12-09 16:32:49'),
(10, 'assign_permissions', 'Assign permissions to roles', 'roles', 'assign_permissions', '2025-12-09 16:32:49'),
(11, 'view_students', 'View students', 'students', 'view', '2025-12-09 16:32:49'),
(12, 'create_students', 'Add students', 'students', 'create', '2025-12-09 16:32:49'),
(13, 'edit_students', 'Edit student info', 'students', 'edit', '2025-12-09 16:32:49'),
(14, 'delete_students', 'Remove students', 'students', 'delete', '2025-12-09 16:32:49'),
(15, 'view_staff', 'View staff', 'staff', 'view', '2025-12-09 16:32:49'),
(16, 'create_staff', 'Add staff', 'staff', 'create', '2025-12-09 16:32:49'),
(17, 'edit_staff', 'Edit staff info', 'staff', 'edit', '2025-12-09 16:32:49'),
(18, 'delete_staff', 'Remove staff', 'staff', 'delete', '2025-12-09 16:32:49'),
(19, 'manage_classes', 'Manage classes', 'academics', 'manage_classes', '2025-12-09 16:32:49'),
(20, 'manage_subjects', 'Manage subjects', 'academics', 'manage_subjects', '2025-12-09 16:32:49'),
(21, 'manage_timetable', 'Manage timetable', 'academics', 'manage_timetable', '2025-12-09 16:32:49'),
(22, 'view_programs', 'View Programs', 'programs', 'view', '2025-12-09 16:32:49'),
(23, 'manage_fees', 'Manage fee structure', 'finance', 'manage_fees', '2025-12-09 16:32:49'),
(24, 'process_payments', 'Process payments', 'finance', 'process_payments', '2025-12-09 16:32:49'),
(25, 'manage_news', 'Manage news & events', 'website', 'manage_news', '2025-12-09 16:32:49'),
(26, 'manage_gallery', 'Manage gallery', 'website', 'manage_gallery', '2025-12-09 16:32:49'),
(27, 'manage_testimonials', 'Manage testimonials', 'website', 'manage_testimonials', '2025-12-09 16:32:49'),
(28, 'manage_sliders', 'Manage hero sliders', 'website', 'manage_sliders', '2025-12-09 16:32:49'),
(29, 'manage_content', 'Manage page content', 'website', 'manage_content', '2025-12-13 11:35:40'),
(30, 'manage_facilities', 'Manage school facilities', 'website', 'manage_facilities', '2025-12-22 16:48:25');

-- --------------------------------------------------------

--
-- Table structure for table `quick_stats`
--

CREATE TABLE `quick_stats` (
  `id` int(11) NOT NULL,
  `stat_name` varchar(100) NOT NULL,
  `stat_value` varchar(100) NOT NULL,
  `stat_label` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quick_stats`
--

INSERT INTO `quick_stats` (`id`, `stat_name`, `stat_value`, `stat_label`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'stats_years', '10', 'Years of Excellence', 1, 'active', '2025-12-18 07:26:37', '2025-12-18 07:26:37'),
(2, 'stats_sections', '3', 'Educational Sections', 2, 'active', '2025-12-18 07:26:37', '2025-12-18 09:22:44'),
(3, 'stats_success', '100', '% Success Rate', 3, 'active', '2025-12-18 07:26:37', '2025-12-18 07:26:37'),
(4, 'stats_bilingual', '1', 'Bilingual Curriculum', 4, 'active', '2025-12-18 07:26:37', '2025-12-18 07:26:37');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_super_admin`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Has full system access', 1, '2025-12-09 16:32:49', '2025-12-09 16:32:49'),
(2, 'Administrator', 'School administration staff', 0, '2025-12-09 16:32:49', '2025-12-09 16:32:49'),
(3, 'Teacher', 'Teaching staff', 0, '2025-12-09 16:32:49', '2025-12-09 16:32:49'),
(4, 'Parent', 'Student parents', 0, '2025-12-09 16:32:49', '2025-12-09 16:32:49'),
(5, 'Student', 'School students', 0, '2025-12-09 16:32:49', '2025-12-09 16:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 19, '2025-12-09 16:32:49'),
(2, 1, 20, '2025-12-09 16:32:49'),
(3, 1, 21, '2025-12-09 16:32:49'),
(4, 1, 1, '2025-12-09 16:32:49'),
(5, 1, 23, '2025-12-09 16:32:49'),
(6, 1, 24, '2025-12-09 16:32:49'),
(7, 1, 22, '2025-12-09 16:32:49'),
(8, 1, 10, '2025-12-09 16:32:49'),
(9, 1, 7, '2025-12-09 16:32:49'),
(10, 1, 9, '2025-12-09 16:32:49'),
(11, 1, 8, '2025-12-09 16:32:49'),
(12, 1, 6, '2025-12-09 16:32:49'),
(13, 1, 16, '2025-12-09 16:32:49'),
(14, 1, 18, '2025-12-09 16:32:49'),
(15, 1, 17, '2025-12-09 16:32:49'),
(16, 1, 15, '2025-12-09 16:32:49'),
(17, 1, 12, '2025-12-09 16:32:49'),
(18, 1, 14, '2025-12-09 16:32:49'),
(19, 1, 13, '2025-12-09 16:32:49'),
(20, 1, 11, '2025-12-09 16:32:49'),
(21, 1, 3, '2025-12-09 16:32:49'),
(22, 1, 5, '2025-12-09 16:32:49'),
(23, 1, 4, '2025-12-09 16:32:49'),
(24, 1, 2, '2025-12-09 16:32:49'),
(25, 1, 26, '2025-12-09 16:32:49'),
(26, 1, 25, '2025-12-09 16:32:49'),
(27, 1, 28, '2025-12-09 16:32:49'),
(28, 1, 27, '2025-12-09 16:32:49'),
(32, 1, 29, '2025-12-13 11:35:40'),
(33, 1, 30, '2025-12-22 16:49:51');

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
  `rating` int(11) DEFAULT 5,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `role`, `content`, `image_url`, `rating`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Alice Johnson', 'Parent of Grade 5 Student', 'Mount Carmel School has provided an excellent learning environment for my child. The bilingual program and dedicated teachers have made a significant difference in his academic growth.', '/testimonials/parent-1.jpg', 5, 0, 'active', '2025-11-28 14:54:03', '2025-11-29 10:08:51'),
(2, 'David Alan Smith', 'Parent of Nursery Student', 'The holistic approach to education at MCS has helped my daughter develop not just academically but also as a confident individual with strong moral values.', '/testimonials/parent-2.jpg', 5, 0, 'active', '2025-11-28 14:54:03', '2025-12-18 09:33:15'),
(3, 'Sarah Williams', 'Parent of Grade 3 Student', 'We\'re impressed with the school\'s commitment to safety and the individual attention given to each student. The bilingual education is preparing our child for global opportunities.', '/testimonials/parent-3.jpg', 5, 0, 'active', '2025-11-28 14:54:03', '2025-11-29 10:08:51'),
(4, 'Robert Brown', 'Parent of Grade 2 Student', 'The teachers at Mount Carmel are exceptional. They truly care about each student\'s success and provide personalized support when needed.', '/testimonials/parent-4.jpg', 4, 0, 'active', '2025-11-28 14:54:03', '2025-11-29 10:08:51'),
(5, 'Maria Garcia', 'Parent of Grade 4 Student', 'The school\'s facilities are outstanding, and the extracurricular activities have helped my child discover new interests and talents.', '/testimonials/parent-5.jpg', 5, 0, 'active', '2025-11-28 14:54:03', '2025-11-29 10:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT 'default-profile.jpg',
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive','pending','suspended') DEFAULT 'pending',
  `last_login` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(10) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `phone`, `username`, `password`, `photo`, `role_id`, `status`, `last_login`, `created_by`, `created_at`, `updated_at`, `reset_token`, `reset_expiry`) VALUES
(1, 'BAHATI', 'Gerchom', 'info@mountcarmel.ac.rw', '250787254817', 'superadmin', '$2y$10$U8MPqa6EFv0sKwCuUKY/sOJoBLgkbsiBlSZ4q1p4aeS84IdoDdKyq', 'default-profile.jpg', 1, 'active', '2026-01-03 15:09:18', NULL, '2025-12-09 16:32:49', '2026-01-03 13:09:18', NULL, NULL),
(3, 'Admin', 'User', 'aba1remy@gmail.com', '1234567890', 'admin', '$2y$10$cTKQFPz493I5.QQkU1MwzOW.YLOdQKqnHbWzpsnO13eI54jLUnCt6', 'default-profile.jpg', 1, 'active', '2026-01-03 15:28:27', 1, '2025-12-10 08:07:50', '2026-01-03 13:28:27', '949474', '2025-12-13 09:47:31'),
(4, 'Cathy', 'Den', 'info.abaremy@gmail.com', '250721053807', 'info.abaremy@gmail.com', '$2y$10$j/XsKcqWfDEUkfKlZfxipe76Ab5XmkV4.P30CJAB5Me.xzpmGsO0.', 'default-profile.jpg', 2, 'active', '2025-12-30 00:19:25', 1, '2025-12-11 12:13:07', '2025-12-29 22:19:25', NULL, NULL),
(5, 'KEVIN', 'PRO', 'kevinuzamurera@gmail.com', '2507877445522', 'kevinuzamurera@gmail.com', '$2y$10$ACGn5raqlzqH3U3KZOnjJuN1SZXCMuNM/MBe6KXu1Bnv89pTJbWEO', 'default-profile.jpg', 3, 'active', NULL, 1, '2025-12-20 14:44:33', '2025-12-20 14:50:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `video_gallery`
--

CREATE TABLE `video_gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(500) NOT NULL,
  `video_type` enum('youtube','vimeo','local') DEFAULT 'youtube',
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'general',
  `duration` varchar(20) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `video_gallery`
--

INSERT INTO `video_gallery` (`id`, `title`, `description`, `video_url`, `video_type`, `thumbnail_url`, `category`, `duration`, `views`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'School Annual Day 2024', 'Highlights from our annual day celebration with performances and awards', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube', 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg', 'events', '5:30', 246, 1, 'active', '2025-12-21 08:20:06', '2025-12-21 08:45:25'),
(2, 'Science Exhibition 2024', 'Students showcasing their innovative science projects', 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'youtube', 'https://img.youtube.com/vi/9bZkp7q19f0/maxresdefault.jpg', 'academics', '8:15', 192, 2, 'active', '2025-12-21 08:20:06', '2025-12-30 07:37:42'),
(3, 'Sports Day Highlights', 'Exciting moments from our inter-house sports competition', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'youtube', 'https://img.youtube.com/vi/jNQXAC9IVRw/maxresdefault.jpg', 'sports', '6:45', 312, 3, 'active', '2025-12-21 08:20:06', '2025-12-21 08:20:06'),
(4, 'School Tour Virtual', 'Take a virtual tour of our modern campus facilities', 'https://www.youtube.com/watch?v=yaqe1qesQ8c', 'youtube', 'https://img.youtube.com/vi/yaqe1qesQ8c/maxresdefault.jpg', 'campus', '4:20', 567, 4, 'active', '2025-12-21 08:20:06', '2025-12-21 08:20:06'),
(5, 'Music Concert 2024', 'Annual music concert featuring our talented students', 'https://www.youtube.com/watch?v=kJQP7kiw5Fk', 'youtube', 'https://img.youtube.com/vi/kJQP7kiw5Fk/maxresdefault.jpg', 'extracurricular', '7:30', 426, 5, 'active', '2025-12-21 08:20:06', '2025-12-22 00:44:56'),
(6, 'Graduation Ceremony', 'Celebrating our graduating class achievements', 'https://www.youtube.com/watch?v=tgbNymZ7vqY', 'youtube', 'https://img.youtube.com/vi/tgbNymZ7vqY/maxresdefault.jpg', 'events', '12:45', 891, 6, 'active', '2025-12-21 08:20:06', '2025-12-21 08:20:06'),
(7, 'Art & Craft Workshop', 'Students learning creative art techniques', 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ', 'youtube', 'https://img.youtube.com/vi/3JZ_D3ELwOQ/maxresdefault.jpg', 'extracurricular', '5:00', 156, 7, 'active', '2025-12-21 08:20:06', '2025-12-21 08:20:06'),
(8, 'Math Olympiad Winners', 'Interview with our national math olympiad champions', 'https://www.youtube.com/watch?v=M7lc1UVf-VE', 'youtube', 'https://img.youtube.com/vi/M7lc1UVf-VE/maxresdefault.jpg', 'academics', '6:15', 235, 8, 'active', '2025-12-21 08:20:06', '2025-12-21 09:49:14'),
(9, 'Football Match', 'Our students', 'https://youtu.be/DaGMZsmDKBU?si=ly8AUuYE2mYxs2xi', 'youtube', 'https://img.youtube.com/vi/DaGMZsmDKBU/maxresdefault.jpg', 'sports', '0:00', 1, 2, 'active', '2025-12-30 07:40:53', '2025-12-30 07:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `why_choose_items`
--

CREATE TABLE `why_choose_items` (
  `id` int(11) NOT NULL,
  `icon_class` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `why_choose_items`
--

INSERT INTO `why_choose_items` (`id`, `icon_class`, `title`, `description`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'fas fa-user-graduate', 'Experienced Staff', 'Our dedicated teachers are highly qualified and experienced in delivering quality education with personalized attention at EAC regional standards.', 1, 'active', '2025-12-18 07:26:37', '2025-12-20 11:44:43'),
(2, 'fas fa-laptop', 'Modern Infrastructure', 'State-of-the-art classrooms, laboratories, and facilities designed to enhance the learning experience and foster creativity.', 0, 'active', '2025-12-18 07:26:37', '2025-12-20 11:44:43'),
(3, 'fas fa-music', 'Holistic Development', 'We focus on academic excellence while nurturing physical, emotional, social, and spiritual development based on Christian values.', 2, 'active', '2025-12-18 07:26:37', '2025-12-20 11:44:43'),
(4, 'fas fa-shield-alt', 'Safe Environment', 'Secure campus with comprehensive safety measures to ensure student well-being at all times in a nurturing Christian atmosphere.', 3, 'active', '2025-12-18 07:26:37', '2025-12-20 11:46:03'),
(5, 'fas fa-globe', 'Bilingual Advantage', 'Master both English and French from early childhood, giving students a competitive edge in our globalized world.', 5, 'active', '2025-12-18 07:26:37', '2025-12-20 11:44:43'),
(6, 'fas fa-trophy', 'Proven Track Record', 'Consistent academic excellence and outstanding student achievements, including national recognition in primary exams.', 4, 'active', '2025-12-18 07:26:37', '2025-12-20 11:46:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admission_content`
--
ALTER TABLE `admission_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `content_type` (`content_type`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `admission_documents`
--
ALTER TABLE `admission_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `admission_sections`
--
ALTER TABLE `admission_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `display_order` (`display_order`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`),
  ADD KEY `inquiry_type` (`inquiry_type`),
  ADD KEY `person_type` (`person_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `educational_programs`
--
ALTER TABLE `educational_programs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facilities_sections`
--
ALTER TABLE `facilities_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_type` (`page_type`),
  ADD KEY `slug` (`slug`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `facility_features`
--
ALTER TABLE `facility_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility_images`
--
ALTER TABLE `facility_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `hero_sliders`
--
ALTER TABLE `hero_sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leadership_team`
--
ALTER TABLE `leadership_team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_events`
--
ALTER TABLE `news_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `published_date` (`published_date`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `organization_chart`
--
ALTER TABLE `organization_chart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_section_unique` (`page_name`,`section_name`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_unique` (`module`,`action`);

--
-- Indexes for table `quick_stats`
--
ALTER TABLE `quick_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stat_name_unique` (`stat_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_unique` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

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
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `video_gallery`
--
ALTER TABLE `video_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`),
  ADD KEY `category` (`category`),
  ADD KEY `video_type` (`video_type`);

--
-- Indexes for table `why_choose_items`
--
ALTER TABLE `why_choose_items`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admission_content`
--
ALTER TABLE `admission_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `admission_documents`
--
ALTER TABLE `admission_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admission_sections`
--
ALTER TABLE `admission_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `educational_programs`
--
ALTER TABLE `educational_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `facilities_sections`
--
ALTER TABLE `facilities_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `facility_features`
--
ALTER TABLE `facility_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `facility_images`
--
ALTER TABLE `facility_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `hero_sliders`
--
ALTER TABLE `hero_sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leadership_team`
--
ALTER TABLE `leadership_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `news_events`
--
ALTER TABLE `news_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `organization_chart`
--
ALTER TABLE `organization_chart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `quick_stats`
--
ALTER TABLE `quick_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `video_gallery`
--
ALTER TABLE `video_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `why_choose_items`
--
ALTER TABLE `why_choose_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admission_content`
--
ALTER TABLE `admission_content`
  ADD CONSTRAINT `fk_admission_content_section` FOREIGN KEY (`section_id`) REFERENCES `admission_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admission_documents`
--
ALTER TABLE `admission_documents`
  ADD CONSTRAINT `fk_admission_docs_content` FOREIGN KEY (`content_id`) REFERENCES `admission_content` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admission_docs_section` FOREIGN KEY (`section_id`) REFERENCES `admission_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
