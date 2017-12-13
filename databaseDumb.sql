-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 18. Nov 2017 um 14:16
-- Server-Version: 5.7.20-0ubuntu0.16.04.1
-- PHP-Version: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `launchpadapp`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `opening_times`
--

CREATE TABLE `opening_times` (
  `id` int(10) UNSIGNED NOT NULL,
  `open_at` datetime NOT NULL,
  `close_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `opening_times`
--

INSERT INTO `opening_times` (`id`, `open_at`, `close_at`) VALUES

(865, '2017-09-13 19:21:16', '2017-10-06 17:41:35'),
(866, '2017-10-06 17:41:36', '2017-10-06 21:48:58'),
(867, '2017-10-08 11:20:17', '2017-10-26 19:32:47'),
(868, '2017-10-26 19:32:49', '2017-10-26 21:22:28'),
(869, '2017-10-26 21:22:30', '2017-10-26 23:26:00'),
(870, '2017-10-26 23:29:34', '2017-10-26 23:42:48'),
(871, '2017-10-26 23:42:50', '2017-10-27 00:50:06'),
(872, '2017-10-27 00:50:08', '2017-10-27 00:50:10'),
(873, '2017-10-27 00:50:12', '2017-11-17 22:39:48'),
(876, '2017-11-03 03:11:10', '2017-11-03 22:46:17'),
(877, '2017-11-17 22:53:00', '2017-11-17 22:53:15'),
(878, '2017-11-17 22:53:24', '2017-11-17 22:53:36'),
(887, '2017-11-18 00:20:52', '2017-11-18 11:14:30'),
(888, '2017-11-18 11:14:32', '2017-11-18 11:14:34'),
(889, '2017-11-18 11:33:06', '2017-11-18 12:23:09'),
(890, '2017-11-18 12:23:22', '2017-11-18 12:25:57'),
(891, '2017-11-18 12:26:05', '2017-11-18 12:26:15'),
(892, '2017-11-18 12:27:52', '2017-11-18 12:27:59'),
(893, '2017-11-18 12:28:42', '2017-11-18 13:01:00'),
(894, '2017-11-18 13:02:37', '2017-11-18 13:05:10'),
(895, '2017-11-18 13:05:12', '2017-11-18 13:05:14'),
(896, '2017-11-18 13:05:17', '2017-11-18 13:05:24'),
(897, '2017-11-18 13:07:51', '2017-11-18 13:07:58'),
(898, '2017-11-18 13:08:04', '2017-11-18 13:08:15'),
(899, '2017-11-18 13:08:24', '2017-11-18 13:10:43'),
(900, '2017-11-18 13:11:11', '2017-11-18 13:11:24'),
(901, '2017-11-18 13:11:30', '2017-11-18 13:11:37'),
(902, '2017-11-18 13:13:07', '2017-11-18 13:13:10'),
(903, '2017-11-18 13:13:13', '2017-11-18 13:13:21');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `opening_times`
--
ALTER TABLE `opening_times`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `opening_times`
--
ALTER TABLE `opening_times`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=904;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
