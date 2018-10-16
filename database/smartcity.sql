-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Set 23, 2018 alle 19:49
-- Versione del server: 10.1.28-MariaDB
-- Versione PHP: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartcity`
--
CREATE DATABASE IF NOT EXISTS `smartcity` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `smartcity`;

-- --------------------------------------------------------

--
-- Struttura della tabella `appointment`
--

CREATE TABLE `appointment` (
  `id` int(11) NOT NULL,
  `usrId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `lat` mediumtext NOT NULL,
  `lon` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `appointment`
--

INSERT INTO `appointment` (`id`, `usrId`, `title`, `address`, `city`, `lat`, `lon`) VALUES
(1, 1, 'Dentista', 'via Di Sotto, 13', 'Pescara', '42.4632', '14.1864074'),
(2, 1, 'Barbiere', 'viale Bovio, 341', 'Pescara', '42.4443003', '14.12615'),
(3, 1, 'Colloquio', 'via Adige, 6', 'Roma', '41.9218717', '12.4999481'),
(4, 1, 'Yo', 'Via torre di cerrano, 2', 'Pescara', '42.4484964', '14.1535043'),
(5, 1, 'Yo', 'aa', 'saa', '43.6570926', '52.0966291');

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(150) NOT NULL,
  `usrLat` mediumtext NOT NULL,
  `usrLong` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `email`, `password`, `usrLat`, `usrLong`) VALUES
(1, 'Manuel', 'Nardone', 'nardonemanuel@gmail.com', 'BC16A537E5B57ACAB3B7E219C2218567', '42.4484964', '14.1535043');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_user_id_fk` (`usrId`);

--
-- Indici per le tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `appointment`
--
ALTER TABLE `appointment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_user_id_fk` FOREIGN KEY (`usrId`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
