-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 01:42 AM
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
(10, 3, 1, 'supi', '2026-03-18 22:05:12', 2),
(11, 4, 1, 'testy', '2026-03-19 19:15:02', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comment_likes`
--

CREATE TABLE `comment_likes` (
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `devlog_posts`
--

CREATE TABLE `devlog_posts` (
  `devlog_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video','none') NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devlog_posts`
--

INSERT INTO `devlog_posts` (`devlog_id`, `title`, `content`, `media_path`, `media_type`, `created_at`, `author_id`) VALUES
(3, 'pierwsze pomysły', 'wyniki naszego pierwszego spotkania na temat gry', 'images/idea.png', 'image', '2026-03-14 10:12:24', 1),
(4, 'dodanie tutorialu', 'Trochę zmagań z tym było, ale mamy tutorial :)', 'images/4.png', 'image', '2026-03-14 10:42:17', 1),
(5, 'mamy więcej kotków!!', 'dodaliśmy większą ilość kotków o różnych kolorach :)', 'images/3.png', 'image', '2026-03-19 20:29:54', 1),
(6, 'dodanie mapki', 'w naszej gierce jest teraz mapka z levelami, po której można sobie chodzić :)', 'images/map.png', 'image', '2026-03-19 20:31:12', 1),
(7, 'enemies', 'dodaliśmy więcej enemies, żeby było ciekawiej', 'images/enemy.png', 'image', '2026-03-19 20:34:35', 1),
(8, 'level 2', 'mamy nowy level!!', 'images/level2.png', 'image', '2026-03-19 20:35:44', 1),
(9, 'play', 'ostatnie testy przed końcową integracją :)', 'images/1.jpg', 'image', '2026-03-19 20:37:24', 1),
(10, 'published!', 'Nasza gierka jest już dostępna na itch.io :) \r\n\r\nhttps://raspberrycola.itch.io/catnetic-storm', 'images/5.png', 'image', '2026-03-19 20:39:04', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `forum_comments`
--

CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `likes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_comments`
--

INSERT INTO `forum_comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`, `likes`) VALUES
(4, 2, 10, 'Hejo!', '2026-03-24 00:58:42', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `forum_comment_likes`
--

CREATE TABLE `forum_comment_likes` (
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_comment_likes`
--

INSERT INTO `forum_comment_likes` (`user_id`, `comment_id`) VALUES
(10, 4);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video','none') DEFAULT 'none',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`post_id`, `user_id`, `title`, `content`, `media_path`, `media_type`, `created_at`) VALUES
(2, 1, 'teścik :)', 'zapraszamy do dodawania postów', 'uploads/forum_69c0f8cc7cdd65.56201408.png', 'image', '2026-03-23 09:24:44'),
(3, 10, 'Gra jest super!', 'Gra bardzo mi się podobała a kotki są cute.', 'uploads/forum_69c1d447ba8ed1.41508387.png', 'image', '2026-03-24 01:01:11');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `gallery`
--

CREATE TABLE `gallery` (
  `gallery_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video','none') NOT NULL DEFAULT 'none',
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`gallery_id`, `title`, `media_path`, `media_type`, `description`, `uploaded_at`, `uploaded_by`) VALUES
(1, 'pototyp', 'images/prototyp.png', 'image', 'nowa gra', '2026-03-14 11:05:19', 1),
(3, 'pierwsza ewaluacja', 'images/pierwsza.jpg', 'image', 'zdjęcie naszej grupy z pierwszej integracji :)', '2026-03-16 22:00:48', 1),
(4, 'testy z dwoma kotami', 'images/testy.png', 'image', NULL, '2026-03-19 19:02:03', 1),
(5, 'pierwsze upgrades', 'images/level_up.png', 'image', NULL, '2026-03-19 19:04:39', 1),
(6, 'pluszak', 'images/kotek.png', 'image', NULL, '2026-03-19 19:06:00', 1),
(7, 'mini pluszak', 'images/mini_kotek.jpg', 'image', NULL, '2026-03-19 19:06:27', 1),
(8, 'rysowanie animacji z Kirri', 'images/rysowanie.jpg', 'image', NULL, '2026-03-19 19:08:01', 1),
(9, 'naklejki na ostanią ewaluację', 'images/naklejki.jpg', 'image', NULL, '2026-03-19 20:32:06', 1),
(10, 'naklejka :)', 'images/cute.jpg', 'image', NULL, '2026-03-19 20:40:02', 1),
(11, 'kotek :c', 'images/2.jpg', 'image', NULL, '2026-03-19 20:40:31', 1);

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
(10, 'emily_cb97', '$2y$10$8TJjrZ1kF4WmfSEAsFunTO8m36A310c8M2TtvAHR5stJfju6pfbFG', 'user', '2026-03-23 23:43:05');

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
-- Indeksy dla tabeli `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indeksy dla tabeli `devlog_posts`
--
ALTER TABLE `devlog_posts`
  ADD PRIMARY KEY (`devlog_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indeksy dla tabeli `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  ADD PRIMARY KEY (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indeksy dla tabeli `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `devlog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE;

--
-- Constraints for table `devlog_posts`
--
ALTER TABLE `devlog_posts`
  ADD CONSTRAINT `devlog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  ADD CONSTRAINT `forum_comment_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comment_likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `forum_comments` (`comment_id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
