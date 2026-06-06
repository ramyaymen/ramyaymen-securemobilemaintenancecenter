-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 12:19 AM
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
-- Database: `secure_mobile_center`
--

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_model` varchar(100) NOT NULL,
  `problem_description` text NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `user_id`, `device_name`, `device_model`, `problem_description`, `status`, `created_at`) VALUES
(3, 6, 'oppo', 'a60', 'trtrtr', 'Pending', '2026-06-05 21:05:26'),
(4, 6, 'oppo1', '1212', '12121212', 'Completed', '2026-06-05 21:05:37'),
(5, 7, 'oppo2', '3434', 'wewsdsa', 'In Progress', '2026-06-05 21:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `repair_notes`
--

CREATE TABLE `repair_notes` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_notes`
--

INSERT INTO `repair_notes` (`id`, `device_id`, `note`, `created_at`) VALUES
(6, 5, 'trtrt', '2026-06-05 21:46:28'),
(7, 5, 'frer', '2026-06-05 21:46:38'),
(8, 3, 'wewew', '2026-06-05 21:47:06'),
(9, 3, 'wewewe', '2026-06-05 21:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(5, 'Hossam Mohamed', 'hossam545mohamed@gmail.com', '$2y$10$9/oI.U.Al8sbSBFCPFlvJum2wqsspQOp3ZG7ObCBp9XH5b3gVXjte', 'LE4LbxR8G2V1hZpU94ITCDNLTm5STjQrT0N2dmViRWRmS0JFd3c9PQ==', 'admin', '2026-06-05 20:25:29'),
(6, 'Hossam Mohamed', 'test@gmail.com', '$2y$10$OxtWLsCulrdStt6SM6zCXerH3YEDvlqNxAxDQK.MiCvQP7n8sJgZK', 'rRRSxWTnmRd/p/XYRNw1FGw0ajVFV0gyK3d0TU41TG9mclJQcEE9PQ==', 'user', '2026-06-05 21:05:11'),
(7, 'Hossam Mohamed', 'test1@gmail.com', '$2y$10$msr95ptriV/tit.UAWHOB.dbrXbvpCpGEtGcPK/pmwbRgKL3ECl1S', '11r0OMIwtEPH9dZ1UTW8skxHR1ptVzdDUUd5b2xtVUt5K0N3UHc9PQ==', 'user', '2026-06-05 21:06:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `repair_notes`
--
ALTER TABLE `repair_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `repair_notes`
--
ALTER TABLE `repair_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repair_notes`
--
ALTER TABLE `repair_notes`
  ADD CONSTRAINT `repair_notes_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
