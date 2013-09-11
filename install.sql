-- Neural Mesh Install SQL Dump
-- v1.6.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nmesh`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `cacheID` varchar(32) NOT NULL,
  `networkID` int(5) unsigned NOT NULL,
  `cacheContent` longtext NOT NULL,
  `cacheDate` datetime NOT NULL,
  PRIMARY KEY (`cacheID`),
  KEY `networkID` (`networkID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `epochs`
--

CREATE TABLE IF NOT EXISTS `epochs` (
  `epochID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `networkID` int(5) unsigned NOT NULL,
  `iterations` int(6) unsigned NOT NULL,
  `startMSE` decimal(10,9) unsigned NOT NULL,
  `endMSE` decimal(10,9) unsigned NOT NULL,
  `epochDate` datetime NOT NULL,
  `execTime` decimal(15,8) unsigned NOT NULL DEFAULT '0.00000000',
  `trainsetID` int(5) DEFAULT NULL,
  PRIMARY KEY (`epochID`),
  KEY `networkID` (`networkID`),
  KEY `trainsetID` (`trainsetID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE IF NOT EXISTS `networks` (
  `networkID` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `networkName` varchar(50) NOT NULL,
  `snapshot` longtext,
  `authkey` varchar(32) NOT NULL,
  `networkType` enum('unmanaged','managed') NOT NULL DEFAULT 'unmanaged',
  `momentumrate` decimal(5,4) NOT NULL DEFAULT '0.5000',
  `learningrate` decimal(5,4) unsigned NOT NULL DEFAULT '1.0000',
  `targetmse` decimal(5,4) unsigned DEFAULT '0.0020',
  `epochmax` int(5) unsigned DEFAULT '10000',
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`networkID`,`networkType`),
  UNIQUE KEY `authkey` (`authkey`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `patterns`
--

CREATE TABLE IF NOT EXISTS `patterns` (
  `patternID` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `trainsetID` int(5) unsigned NOT NULL,
  `pattern` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `output` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`patternID`),
  KEY `train` (`trainsetID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trainsets`
--

CREATE TABLE IF NOT EXISTS `trainsets` (
  `trainsetID` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `networkID` int(5) unsigned NOT NULL,
  `label` varchar(20) NOT NULL,
  PRIMARY KEY (`trainsetID`),
  KEY `network` (`networkID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `usernetwork`
--

CREATE TABLE IF NOT EXISTS `usernetwork` (
  `userID` int(5) NOT NULL,
  `networkID` int(5) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`networkID`),
  KEY `networkID` (`networkID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userID` int(5) NOT NULL AUTO_INCREMENT,
  `userName` varchar(50) NOT NULL,
  `userPass` varchar(41) NOT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `userName` (`userName`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cache`
--
ALTER TABLE `cache`
  ADD CONSTRAINT `networkID_fk` FOREIGN KEY (`networkID`) REFERENCES `networks` (`networkID`) ON DELETE CASCADE;

--
-- Constraints for table `epochs`
--
ALTER TABLE `epochs`
  ADD CONSTRAINT `epochs_ibfk_1` FOREIGN KEY (`networkID`) REFERENCES `networks` (`networkID`) ON DELETE CASCADE;

--
-- Constraints for table `patterns`
--
ALTER TABLE `patterns`
  ADD CONSTRAINT `patterns_ibfk_1` FOREIGN KEY (`trainsetID`) REFERENCES `trainsets` (`trainsetID`) ON DELETE CASCADE;

--
-- Constraints for table `trainsets`
--
ALTER TABLE `trainsets`
  ADD CONSTRAINT `trainsets_ibfk_1` FOREIGN KEY (`networkID`) REFERENCES `networks` (`networkID`) ON DELETE CASCADE;

--
-- Constraints for table `usernetwork`
--
ALTER TABLE `usernetwork`
  ADD CONSTRAINT `usernetwork_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `usernetwork_ibfk_2` FOREIGN KEY (`networkID`) REFERENCES `networks` (`networkID`) ON DELETE CASCADE;
  
--
-- Add a default user
--  
INSERT INTO `users`(`userName`,`userPass`) VALUES ("admin",PASSWORD("admin"));
