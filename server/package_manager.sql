-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Ven 18 Septembre 2015 à 03:00
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `package_manager`
--

-- --------------------------------------------------------

--
-- Structure de la table `lib`
--

CREATE TABLE IF NOT EXISTS `lib` (
  `id_lib` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `version` varchar(32) NOT NULL,
  PRIMARY KEY (`id_lib`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `lib`
--

INSERT INTO `lib` (`id_lib`, `name`, `version`) VALUES
(1, 'glibc', '1.0'),
(2, 'glibc', '2.0');

-- --------------------------------------------------------

--
-- Structure de la table `package`
--

CREATE TABLE IF NOT EXISTS `package` (
  `id_package` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) NOT NULL,
  `brief` text NOT NULL,
  `version` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_package`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `package`
--

INSERT INTO `package` (`id_package`, `name`, `alias`, `brief`, `version`) VALUES
(1, 'gcc', '', '', 10000),
(2, 'gcc', '', '', 10200),
(3, 'gcc', '', '', 100000),
(4, 'g++', '', '', 10000);

-- --------------------------------------------------------

--
-- Structure de la table `package_lib`
--

CREATE TABLE IF NOT EXISTS `package_lib` (
  `id_package` int(10) unsigned NOT NULL,
  `id_lib` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_package`,`id_lib`),
  KEY `id_lib` (`id_lib`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `package_lib`
--

INSERT INTO `package_lib` (`id_package`, `id_lib`) VALUES
(1, 1),
(2, 1),
(4, 1),
(3, 2);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `package_lib`
--
ALTER TABLE `package_lib`
  ADD CONSTRAINT `package_lib_ibfk_2` FOREIGN KEY (`id_lib`) REFERENCES `lib` (`id_lib`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `package_lib_ibfk_1` FOREIGN KEY (`id_package`) REFERENCES `package` (`id_package`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
