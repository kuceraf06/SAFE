-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Úte 11. bře 2025, 01:36
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `mywebsite`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `pastevents`
--

CREATE TABLE `pastevents` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Vypisuji data pro tabulku `pastevents`
--

INSERT INTO `pastevents` (`id`, `title`, `description`, `image_path`, `images`, `created_at`) VALUES
(23, '<h2>2022</h2>', '<p>Prvn&iacute; a z&aacute;roveň jubilejn&iacute; des&aacute;t&yacute; ročn&iacute;k dř&iacute;ve tradičn&iacute; akce&nbsp;<strong>SAFE!</strong>&nbsp;Na programu byly rozhovory s hosty, oceněn&iacute; hr&aacute;čů a videa ze sez&oacute;ny.</p>\r\n<div class=\"gallery\">&nbsp;</div>', NULL, '[\"images\\/safe01.jpg\",\"images\\/safe02.jpg\",\"images\\/safe03.jpg\",\"images\\/safe04.jpg\"]', '2024-11-27 15:37:36'),
(44, '<h2>2023</h2>', '<p>Posledn&iacute; ročn&iacute;k akce&nbsp;<strong>SAFE</strong>&nbsp;byl neuvěřitelně nabit&yacute;! Na&scaron;e pozv&aacute;n&iacute; přijali n&aacute;sleduj&iacute;c&iacute; host&eacute;:</p>\r\n<ul>\r\n<li><strong>Franti&scaron;ek Bure&scaron;</strong>&nbsp;- statut&aacute;rn&iacute; n&aacute;městek prim&aacute;tora a člen Rady města Kladna</li>\r\n<li><strong>Robert Bezděk</strong> - n&aacute;městek prim&aacute;tora a člen Rady města Kladna</li>\r\n<li><strong>Petr Ditrich</strong>&nbsp;- předseda česk&eacute; baseballov&eacute; asociace</li>\r\n<li><strong>Gabriel Waage</strong>&nbsp;- předseda česk&eacute; softballov&eacute; asociace</li>\r\n<li><strong>Evženie Vot&iacute;nsk&aacute;</strong>&nbsp;- vedouc&iacute; PR a marketingu česk&eacute; baseballov&eacute; asociace</li>\r\n<li><strong>Tom&aacute;&scaron; Duffek</strong>&nbsp;- baseballov&yacute; reprezentant Česk&eacute; republiky</li>\r\n<li><strong>Martin Muž&iacute;k</strong>&nbsp;- baseballov&yacute; reprezentant Česk&eacute; republiky</li>\r\n<li><strong>Filip Proch&aacute;zka</strong>&nbsp;- tren&eacute;r baseballov&eacute; reprezentace ČR U18</li>\r\n<li><strong>Michala Kuchařov&aacute;</strong>&nbsp;- b&yacute;val&aacute; elitn&iacute; softballov&aacute; hr&aacute;čka</li>\r\n<li><strong>Nela Jan&aacute;čkov&aacute;</strong>&nbsp;- softballov&aacute; reprezentantka Česk&eacute; republiky</li>\r\n</ul>\r\n<p>Tak vz&aacute;cn&iacute; host&eacute; nemohli zůstat jen tak sedět, a tak byla uspoř&aacute;d&aacute;na autogrami&aacute;da, kterou děti s &uacute;směvem přiv&iacute;taly. Nechybělo ani focen&iacute;.</p>', NULL, '[\"images\\/SAFE-225.jpg\",\"images\\/SAFE-380.jpg\",\"images\\/SAFE-396.jpg\",\"images\\/SAFE-125.jpg\"]', '2024-11-29 09:04:53');

-- --------------------------------------------------------

--
-- Struktura tabulky `pasteventsen`
--

CREATE TABLE `pasteventsen` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `pasteventsen`
--

INSERT INTO `pasteventsen` (`id`, `title`, `description`, `image_path`, `images`, `created_at`) VALUES
(6, '<h2>2022</h2>', '<p>The first and also the tenth anniversary year of the previously traditional<strong> SAFE event! </strong>The program included interviews with guests, player awards, and videos from the season.</p>', NULL, '[\"..\\/..\\/..\\/..\\/images\\/safe01.jpg\",\"..\\/..\\/..\\/..\\/images\\/safe02.jpg\",\"..\\/..\\/..\\/..\\/images\\/safe03.jpg\",\"..\\/..\\/..\\/..\\/images\\/safe04.jpg\"]', '2025-03-03 04:58:53'),
(7, '<h2>2023</h2>', '<p>The last year of the <strong>SAFE </strong>event was incredibly busy! The following guests accepted our invitation:</p>\r\n<p><strong>Franti&scaron;ek Bure&scaron; </strong>-<strong> </strong>Statutory Deputy Mayor and Member of the Kladno City Council<br><strong>Robert Bezděk</strong> - Deputy Mayor and Member of the Kladno City Council<br><strong>Petr Ditrich</strong> - Chairman of the Czech Baseball Association<br><strong>Gabriel Waage</strong> - Chairman of the Czech Softball Association<br><strong>Evženie Vot&iacute;nsk&aacute;</strong> - Head of PR and Marketing of the Czech Baseball Association<br><strong>Tom&aacute;&scaron; Duffek</strong> - Baseball representative of the Czech Republic<br><strong>Martin Muž&iacute;k </strong>- Baseball representative of the Czech Republic<br><strong>Filip Proch&aacute;zka</strong> - Coach of the Czech U18 Baseball National Team<br><strong>Michala Kuchařov&aacute;</strong> - Former Elite Softball Player<br><strong>Nela Jan&aacute;čkov&aacute;</strong> - Softball representative of the Czech Republic<br>Such distinguished guests could not just sit there, so an autograph signing was organized, which the children welcomed with smiles. There was also a photo shoot.</p>\r\n<div class=\"gallery\">&nbsp;</div>', NULL, '[\"..\\/..\\/..\\/..\\/images\\/SAFE-225.jpg\",\"..\\/..\\/..\\/..\\/images\\/SAFE-380.jpg\",\"..\\/..\\/..\\/..\\/images\\/SAFE-396.jpg\",\"..\\/..\\/..\\/..\\/images\\/SAFE-125.jpg\"]', '2025-03-03 05:00:49');

-- --------------------------------------------------------

--
-- Struktura tabulky `programme`
--

CREATE TABLE `programme` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `programme`
--

INSERT INTO `programme` (`id`, `description`, `created_at`) VALUES
(60, '17:30 otevření sálu', '2024-12-01 19:36:56'),
(61, '18:00 slavnostní zahájení SAFE 2024', '2024-12-01 19:36:56'),
(62, 'Ocenění hráčů kategorií U5 – U9\r\n', '2024-12-01 19:36:56'),
(63, 'Vyhlášení nejlepších hráčů U11 – muži/ženy\r\n', '2024-12-01 19:36:56'),
(64, 'Fotografování týmů/oceněných', '2024-12-01 19:36:56'),
(65, 'Autogramiáda hostů', '2024-12-01 19:36:56'),
(66, 'Medailonky oceněných hráčů', '2024-12-01 19:36:56'),
(67, 'Afterparty', '2024-12-01 19:36:56');

-- --------------------------------------------------------

--
-- Struktura tabulky `programmeen`
--

CREATE TABLE `programmeen` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `programmeen`
--

INSERT INTO `programmeen` (`id`, `description`, `created_at`) VALUES
(4, '5:30pm opening of the hall', '2025-03-03 03:55:04'),
(5, '6pm SAFE 2024 opening ceremony', '2025-03-03 03:55:04'),
(6, 'Awards for players in categories U5 – U9', '2025-03-03 03:55:04'),
(7, 'Announcement of the best U11 players – men/women', '2025-03-03 03:55:04'),
(8, 'Team/awardee photography', '2025-03-03 03:55:04'),
(9, 'Guest autograph session', '2025-03-03 03:55:04'),
(10, 'Medallions of awarded players', '2025-03-03 03:55:04'),
(11, 'Afterparty', '2025-03-03 03:55:04');

--
-- Indexy pro tabulku `pastevents`
--
ALTER TABLE `pastevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `pasteventsen`
--
ALTER TABLE `pasteventsen`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `programme`
--
ALTER TABLE `programme`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `programmeen`
--
ALTER TABLE `programmeen`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `pastevents`
--
ALTER TABLE `pastevents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT pro tabulku `pasteventsen`
--
ALTER TABLE `pasteventsen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pro tabulku `programme`
--
ALTER TABLE `programme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT pro tabulku `programmeen`
--
ALTER TABLE `programmeen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Omezení pro exportované tabulky
--

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
