-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 21. Mai 2018 um 21:25
-- Server-Version: 10.1.31-MariaDB
-- PHP-Version: 5.6.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
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
-- Tabellenstruktur für Tabelle `occupation_viewer`
--

CREATE TABLE `occupation_viewer` (
  `id` int(11) NOT NULL,
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `presence_tracker_macs`
--

CREATE TABLE `presence_tracker_macs` (
  `mac_hash` varchar(40) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `blacklisted` tinyint(1) NOT NULL DEFAULT '0',
  `first_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `here_since` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `presence_tracker_orga`
--

CREATE TABLE `presence_tracker_orga` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `img` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `presence_tracker_users`
--

CREATE TABLE `presence_tracker_users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `orga_id` int(11) DEFAULT NULL,
  `profile_img` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `occupation_viewer`
--
ALTER TABLE `occupation_viewer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE OPENED_AT` (`opened_at`) USING BTREE;

--
-- Indizes für die Tabelle `presence_tracker_macs`
--
ALTER TABLE `presence_tracker_macs`
  ADD PRIMARY KEY (`mac_hash`);

--
-- Indizes für die Tabelle `presence_tracker_orga`
--
ALTER TABLE `presence_tracker_orga`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE NAME` (`name`);

--
-- Indizes für die Tabelle `presence_tracker_users`
--
ALTER TABLE `presence_tracker_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `occupation_viewer`
--
ALTER TABLE `occupation_viewer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `presence_tracker_orga`
--
ALTER TABLE `presence_tracker_orga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `presence_tracker_users`
--
ALTER TABLE `presence_tracker_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
