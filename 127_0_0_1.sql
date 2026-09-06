-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 12:13 PM
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
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"rad_project\",\"table\":\"subjects\"},{\"db\":\"rad_project\",\"table\":\"payments\"},{\"db\":\"rad_project\",\"table\":\"notifications\"},{\"db\":\"rad_project\",\"table\":\"homework\"},{\"db\":\"rad_project\",\"table\":\"grades\"},{\"db\":\"rad_project\",\"table\":\"timetable\"},{\"db\":\"rad_project\",\"table\":\"users\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2026-07-03 12:38:16', '{\"Console\\/Mode\":\"show\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `rad_project`
--
CREATE DATABASE IF NOT EXISTS `rad_project` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rad_project`;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_knowledge`
--

CREATE TABLE `chatbot_knowledge` (
  `id` int(11) NOT NULL,
  `topic` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_knowledge`
--

INSERT INTO `chatbot_knowledge` (`id`, `topic`, `content`, `created_at`) VALUES
(1, 'Late fees', 'If school fees are not paid by the due date, a late fee notice will be sent. Contact the school office to arrange payment.', '2026-08-23 05:11:01'),
(2, 'Uniform policy', 'Students must wear the official Maplewood uniform on all days except designated Sport Day sessions.', '2026-08-23 05:11:01'),
(3, 'School hours', 'Year 1-6 classes run 8:00am to 12:45pm. Year 7-11 classes run 8:00am to 1:45pm.', '2026-08-23 05:11:01'),
(4, 'Contacting the office', 'For issues not covered here (lost ID, medical concerns, enrolment questions), contact the school office directly rather than using this chat.', '2026-08-23 05:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `target_year` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(10) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks` decimal(5,2) NOT NULL DEFAULT 0.00,
  `max_marks` int(11) NOT NULL DEFAULT 100,
  `grade` varchar(5) NOT NULL DEFAULT '',
  `term` varchar(20) NOT NULL DEFAULT 'Term 1',
  `year` int(11) NOT NULL,
  `graded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `marks`, `max_marks`, `grade`, `term`, `year`, `graded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 87.50, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(2, 1, 2, 80.00, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-07-06 12:58:30'),
(3, 1, 3, 80.00, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-07-06 12:58:07'),
(4, 1, 4, 85.00, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(5, 1, 5, 88.75, 100, 'A', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00'),
(6, 1, 6, 90.00, 100, 'A+', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-07-08 10:22:37'),
(7, 1, 7, 80.00, 100, 'B+', 'Term 1', 11, 2, '2026-06-08 10:00:00', '2026-06-08 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `homework`
--

CREATE TABLE `homework` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'Homework',
  `due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'missing',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homework`
--

INSERT INTO `homework` (`id`, `student_id`, `subject_id`, `title`, `type`, `due_date`, `status`, `created_by`, `created_at`) VALUES
(1, 1, NULL, 'chapter 6 worksheet', 'Homework', '2026-07-08', 'submitted', 2, '2026-07-03 12:35:03'),
(2, 3, NULL, 'chapter 6 worksheet', 'Homework', '2026-07-08', 'missing', 2, '2026-07-03 12:35:03'),
(3, 1, NULL, 'chapter 6 worksheet', 'Homework', '2026-07-09', 'submitted', 2, '2026-07-04 02:54:51'),
(4, 1, NULL, 'final exam revision', 'Homework', '2026-07-07', 'missing', 2, '2026-07-06 12:59:31'),
(5, 1, NULL, 'history chapter 3', 'Homework', '2026-08-26', 'missing', 2, '2026-07-06 13:16:29'),
(6, 1, NULL, 'chapter 12 exercises', 'Homework', '2026-07-21', 'missing', 2, '2026-07-06 13:30:11'),
(7, 1, NULL, 'exercises', 'Homework', '2026-07-07', 'missing', 2, '2026-07-06 13:55:33'),
(8, 1, NULL, 'test 2', 'Test', '2026-07-09', 'missing', 2, '2026-07-08 12:27:20'),
(9, 1, 6, 'Chapter 11 worksheet', 'Homework', '2026-08-27', 'missing', 2, '2026-08-26 11:54:24');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Term 1 Grades Released', 'Your Term 1 grades have been posted. Check your grades page to view your results.', 1, '2026-06-08 14:30:00'),
(2, 1, 'Homework Submission Reminder', 'Remember to submit your Math assignment by Friday. Late submissions will not be accepted.', 1, '2026-06-07 10:15:00'),
(3, 1, 'School Event Announcement', 'Annual Sports Day is scheduled for June 15, 2026. All students are required to participate.', 1, '2026-06-05 09:00:00'),
(4, 1, 'Payment Due', 'Your school fees for June are now due. Please make payment before June 30, 2026.', 1, '2026-06-01 08:30:00'),
(5, 1, 'Class Cancelled', 'Biology practical class on June 10 has been rescheduled to June 12 due to lab maintenance.', 1, '2026-06-06 15:45:00'),
(6, 1, 'Payment Received', 'We received your payment for invoice #6. Thank you.', 1, '2026-06-22 05:40:58'),
(7, 1, 'Payment Received', 'We received your payment for invoice #5. Thank you.', 1, '2026-06-22 05:42:41'),
(8, 1, 'Payment Received', 'We received your payment for invoice #4. Thank you.', 1, '2026-06-22 14:26:47'),
(9, 1, 'Payment Received', 'We received your payment for invoice #3. Thank you.', 1, '2026-06-22 14:28:58'),
(10, 1, 'Payment Received', 'We received your payment for invoice #1. Thank you.', 1, '2026-06-22 14:31:05'),
(11, 1, 'Payment Received', 'We received your payment for invoice #7. Thank you.', 1, '2026-06-22 14:32:17'),
(12, 1, 'Payment Received', 'We received your payment for invoice #8. Thank you.', 1, '2026-06-22 14:38:32'),
(13, 1, 'Payment Received', 'We received your payment for invoice #9. Thank you.', 1, '2026-06-22 14:53:54'),
(14, 1, 'Payment Received', 'We received your payment for invoice #10. Thank you.', 1, '2026-06-22 14:57:41'),
(15, 1, 'Payment Received', 'We received your payment for invoice #11. Thank you.', 1, '2026-06-22 15:00:04'),
(16, 1, 'Payment Received', 'We received your payment for invoice #12. Thank you.', 1, '2026-06-22 15:01:31'),
(17, 1, 'Payment Received', 'We received your payment for invoice #13. Thank you.', 1, '2026-06-22 15:05:11'),
(18, 1, 'New Homework', 'A new Homework \"chapter 6 work sheet\" is due on 17 Jul 2026.', 1, '2026-07-03 12:31:39'),
(19, 1, 'New Homework', 'A new Homework \"chapter 6 worksheet\" is due on 08 Jul 2026.', 1, '2026-07-03 12:35:03'),
(20, 3, 'New Homework', 'A new Homework \"chapter 6 worksheet\" is due on 08 Jul 2026.', 1, '2026-07-03 12:35:03'),
(21, 1, 'New Homework', 'A new Homework \"chapter 6 worksheet\" is due on 09 Jul 2026.', 1, '2026-07-04 02:54:51'),
(22, 1, 'Grade Updated', 'Your grades for Term 1 have been updated. Check your dashboard for details.', 1, '2026-07-06 12:58:07'),
(23, 1, 'Grade Updated', 'Your grades for Term 1 have been updated. Check your dashboard for details.', 1, '2026-07-06 12:58:30'),
(24, 1, 'New Homework', 'A new Homework \"final exam revision\" is due on 07 Jul 2026.', 1, '2026-07-06 12:59:31'),
(25, 1, 'New Homework', 'A new Homework \"history chapter 3\" is due on 26 Aug 2026.', 0, '2026-07-06 13:16:29'),
(26, 1, 'Payment Received', 'We received your payment for invoice #15. Thank you.', 0, '2026-07-06 13:23:24'),
(27, 1, 'New Homework', 'A new Homework \"chapter 12 exercises\" is due on 21 Jul 2026.', 0, '2026-07-06 13:30:11'),
(28, 1, 'Payment Received', 'We received your payment for invoice #16. Thank you.', 0, '2026-07-06 13:53:49'),
(29, 1, 'New Homework', 'A new Homework \"exercises\" is due on 07 Jul 2026.', 0, '2026-07-06 13:55:33'),
(30, 1, 'Grade Updated', 'Your grades for Term 1 have been updated. Check your dashboard for details.', 0, '2026-07-08 10:22:37'),
(31, 1, 'New Test', 'A new Test \"test 2\" is due on 09 Jul 2026.', 0, '2026-07-08 12:27:20');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(8) NOT NULL DEFAULT 'RM',
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Invoice Sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `currency`, `description`, `status`, `created_at`, `paid_at`) VALUES
(1, 1, 450.00, 'RM', 'School fees - June 2026', 'Paid', '2026-06-01 08:00:00', '2026-06-22 08:31:05'),
(2, 1, 450.00, 'RM', 'School fees - May 2026', 'Paid', '2026-05-01 08:00:00', '2026-05-02 12:30:00'),
(3, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-16 23:48:37', '2026-06-22 08:28:58'),
(4, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-17 00:04:15', '2026-06-22 08:26:47'),
(5, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-17 00:07:53', '2026-06-21 23:42:41'),
(6, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-17 00:10:00', '2026-06-21 23:40:58'),
(7, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 08:31:44', '2026-06-22 08:32:17'),
(8, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 08:37:55', '2026-06-22 08:38:32'),
(9, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 08:53:21', '2026-06-22 08:53:54'),
(10, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 08:57:08', '2026-06-22 08:57:41'),
(11, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 08:59:32', '2026-06-22 09:00:04'),
(12, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 09:00:59', '2026-06-22 09:01:31'),
(13, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 09:04:40', '2026-06-22 09:05:11'),
(14, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-22 09:04:40', '2026-06-23 02:16:37'),
(15, 1, 450.00, 'RM', 'Maplewood Academy Fee', 'Paid', '2026-06-29 01:54:23', '2026-07-06 07:23:24'),
(16, 1, 450.00, 'RM', 'Monthly tuition fee', 'Paid', '2026-07-06 12:59:45', '2026-07-06 07:53:49'),
(17, 3, 450.00, 'RM', 'Monthly tuition fee', 'Invoice Sent', '2026-07-06 13:17:27', NULL),
(18, 1, 450.00, 'RM', 'Monthly tuition fee', 'Invoice Sent', '2026-07-08 10:27:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `year_group` varchar(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `year_group`, `name`, `created_at`) VALUES
(1, '10-11', 'BM', '2026-06-02 12:12:04'),
(2, '10-11', 'Math', '2026-06-02 12:12:04'),
(3, '10-11', 'English', '2026-06-02 12:12:04'),
(4, '10-11', 'Science', '2026-06-02 12:12:04'),
(5, '10-11', 'History', '2026-06-02 12:12:04'),
(6, '10,11', 'Account', '2026-06-02 12:12:04'),
(7, '10-11', 'Geography', '2026-06-02 12:12:04'),
(8, '4-6', 'History', '2026-06-02 12:12:04'),
(9, '4-6', 'BM', '2026-06-02 12:12:04'),
(10, '4-6', 'English', '2026-06-02 12:12:04'),
(11, '4-6', 'Science', '2026-06-02 12:12:04'),
(12, '4-6', 'Math', '2026-06-02 12:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `year_group` varchar(10) NOT NULL,
  `year` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `p1` varchar(40) DEFAULT '',
  `p2` varchar(40) DEFAULT '',
  `p3` varchar(40) DEFAULT '',
  `p4` varchar(40) DEFAULT '',
  `p5` varchar(40) DEFAULT '',
  `p6` varchar(40) DEFAULT '',
  `p7` varchar(40) DEFAULT '',
  `sport_day` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `year_group`, `year`, `day`, `p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `p7`, `sport_day`) VALUES
(1, '10-11', 11, 'Monday', 'MATH', 'ENG', 'HIS', 'BREAK', 'ACC', 'ECO', 'SCI', 0),
(2, '10-11', 11, 'Tuesday', 'MATH', 'BM', 'MAN', 'BREAK', 'HIS', 'ACC', 'ECO', 0),
(3, '10-11', 11, 'Wednesday', 'ENG', 'BM', 'MATH', 'BREAK', 'SCI', 'HIS', 'ACC', 0),
(4, '10-11', 11, 'Thursday', 'ECO', 'ENG', 'BM', 'BREAK', 'MAN', 'SCI', 'ACC', 0),
(5, '10-11', 11, 'Friday', 'SPORT', 'SPORT', 'BM', 'BREAK', 'ACC', 'ECO', 'SCI', 1),
(6, '1-6', 5, 'Monday', 'SPORT', 'Math', 'BREAK', 'MAN', 'ENG', NULL, NULL, 1),
(7, '1-6', 5, 'Tuesday', 'Eng', 'Math', 'BREAK', 'SCI', 'HIS', NULL, NULL, 0),
(8, '1-6', 5, 'Wednesday', 'ENG', 'BM', 'BREAK', 'HIS', 'SCI', NULL, NULL, 0),
(9, '1-6', 5, 'Thursday', 'HIS', 'ENG', 'BREAK', 'MATH', 'SCI', NULL, NULL, 0),
(10, '1-6', 5, 'Friday', 'MATH', 'HIS', 'BREAK', 'BM', 'SCI', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `useremail` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT 'Student',
  `year` int(11) DEFAULT 10,
  `PASSWORD` varchar(25) DEFAULT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `useremail`, `name`, `year`, `PASSWORD`, `role`) VALUES
(1, 'isaac@student.maplewood.edu.my', 'Isaac', 11, '454567', 'student'),
(2, 'admin@teacher.maplewood.edu.my', 'Tommy', 0, '878756', 'teacher'),
(3, 'zixuan@student.maplewood.edu.my', 'Zi xuan', 6, '987654', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`term`);

--
-- Indexes for table `homework`
--
ALTER TABLE `homework`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `homework`
--
ALTER TABLE `homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
