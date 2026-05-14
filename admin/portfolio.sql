-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 05:46 AM
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
-- Database: `portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:21:43'),
(2, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:21:44'),
(3, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:21:44'),
(4, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:21:44'),
(5, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:21:50'),
(6, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:29:44'),
(7, 'Pradip Subedi', 'sprasamedia@gmail.com', 'Hey Its For testing Propose\r\n', '2026-04-28 16:29:52'),
(8, 'test 1', 'sprasamedia@gmail.com', 'hi ', '2026-04-28 16:32:14'),
(9, 'test 1', 'sprasamedia@gmail.com', 'hi ', '2026-04-28 16:32:14'),
(10, 'test 1', 'sprasamedia@gmail.com', 'hi ', '2026-04-28 16:40:33'),
(11, 'test 1', 'sprasamedia@gmail.com', 'hi ', '2026-04-28 16:40:33'),
(12, 'test 1', 'sprasamedia@gmail.com', 'hi ', '2026-04-28 16:42:11'),
(13, 'test 1', 'sprasamedia@gmail.com', 'hi ', '2026-04-28 16:42:11'),
(14, 'test 2', 'sprasamedia@gmail.com', 'hi', '2026-04-28 17:06:15'),
(15, 'test 2', 'sprasamedia@gmail.com', 'hi', '2026-04-28 17:07:35'),
(16, 'test 2', 'sprasamedia@gmail.com', 'hi', '2026-04-28 17:08:11'),
(17, 'test 2', 'sprasamedia@gmail.com', 'hi', '2026-04-28 17:09:14'),
(18, 'test 3', 'sprasamedia@gmail.com', 'hiii', '2026-04-28 17:13:43');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
