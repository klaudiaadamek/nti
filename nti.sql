-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2026 at 11:01 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nti`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`, `likes`) VALUES
(10, 3, 1, 'supi', '2026-03-18 22:05:12', 0),
(11, 4, 1, 'testy', '2026-03-19 19:15:02', 1),
(12, 8, 7, 'jest git! :)', '2026-03-19 20:43:37', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `devlog_posts`
--

CREATE TABLE `devlog_posts` (
  `devlog_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devlog_posts`
--

INSERT INTO `devlog_posts` (`devlog_id`, `title`, `content`, `image_path`, `created_at`, `author_id`) VALUES
(3, 'pierwsze pomysły', 'wyniki naszego pierwszego spotkania na temat gry', 'images/idea.png', '2026-03-14 10:12:24', 1),
(4, 'dodanie tutorialu', 'Trochę zmagań z tym było, ale mamy tutorial :)', 'images/4.png', '2026-03-14 10:42:17', 1),
(5, 'mamy więcej kotków!!', 'dodaliśmy większą ilość kotków o różnych kolorach :)', 'images/3.png', '2026-03-19 20:29:54', 1),
(6, 'dodanie mapki', 'w naszej gierce jest teraz mapka z levelami, po której można sobie chodzić :)', 'images/map.png', '2026-03-19 20:31:12', 1),
(7, 'enemies', 'dodaliśmy więcej enemies, żeby było ciekawiej', 'images/enemy.png', '2026-03-19 20:34:35', 1),
(8, 'level 2', 'mamy nowy level!!', 'images/level2.png', '2026-03-19 20:35:44', 1),
(9, 'play', 'ostatnie testy przed końcową integracją :)', 'images/1.jpg', '2026-03-19 20:37:24', 1),
(10, 'published!', 'Nasza gierka jest już dostępna na itch.io :) \r\n\r\nhttps://raspberrycola.itch.io/catnetic-storm', 'images/5.png', '2026-03-19 20:39:04', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `gallery`
--

CREATE TABLE `gallery` (
  `gallery_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`gallery_id`, `title`, `image_path`, `description`, `uploaded_at`, `uploaded_by`) VALUES
(1, 'pototyp', 'images/prototyp.png', 'nowa gra', '2026-03-14 11:05:19', 1),
(3, 'pierwsza ewaluacja', 'images/pierwsza.jpg', 'zdjęcie naszej grupy z pierwszej integracji :)', '2026-03-16 22:00:48', 1),
(4, 'testy z dwoma kotami', 'images/testy.png', NULL, '2026-03-19 19:02:03', 1),
(5, 'pierwsze upgrades', 'images/level_up.png', NULL, '2026-03-19 19:04:39', 1),
(6, 'pluszak', 'images/kotek.png', NULL, '2026-03-19 19:06:00', 1),
(7, 'mini pluszak', 'images/mini_kotek.jpg', NULL, '2026-03-19 19:06:27', 1),
(8, 'rysowanie animacji z Kirri', 'images/rysowanie.jpg', NULL, '2026-03-19 19:08:01', 1),
(9, 'naklejki na ostanią ewaluację', 'images/naklejki.jpg', NULL, '2026-03-19 20:32:06', 1),
(10, 'naklejka :)', 'images/cute.jpg', NULL, '2026-03-19 20:40:02', 1),
(11, 'kotek :c', 'images/2.jpg', NULL, '2026-03-19 20:40:31', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Klaudia', '$2y$10$yU5nF3g9DiF6ZZ4YUB5uPuw.H.BS/y2nZWgHSFvXxeVb8Z4LCloyi', 'admin', '2026-03-13 23:47:07'),
(4, 'test', '$2y$10$WuWrjX/i9YnAF7PB0Oyse.98Ldnf4mXBruFZ6vGUYnErQcKzh0hcu', 'user', '2026-03-15 00:23:48'),
(5, 'testowe', '$2y$10$0oj0TdbWq5r8uoqYmr7X6.JT/DS/QEXtrO.MduCwnZszsM0Ay5X2i', 'user', '2026-03-16 09:06:22'),
(6, 'test1', '$2y$10$CgvE6XWr1E954kvEgozodeNwyY2Sn2qoST9uc6LehS5j5JuKXHhe6', 'user', '2026-03-17 20:48:10'),
(7, 'emi', '$2y$10$bdfzkgDVmc1KlCozq88S2eTQjqeqjnAp4.p3YsGhXeWiHmjzheL4C', 'user', '2026-03-19 18:47:53');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `devlog_posts`
--
ALTER TABLE `devlog_posts`
  ADD PRIMARY KEY (`devlog_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indeksy dla tabeli `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uniq_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `devlog_posts`
--
ALTER TABLE `devlog_posts`
  MODIFY `devlog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `devlog_posts` (`devlog_id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `devlog_posts`
--
ALTER TABLE `devlog_posts`
  ADD CONSTRAINT `devlog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
