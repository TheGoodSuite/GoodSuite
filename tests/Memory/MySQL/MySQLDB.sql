-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 23, 2014 at 04:59 AM
-- Server version: 5.5.35
-- PHP Version: 5.3.10-1ubuntu3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `goodtests`
--

-- --------------------------------------------------------

--
-- Table structure for table `advancedupdatetype`
--

CREATE TABLE IF NOT EXISTS `advancedupdatetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) DEFAULT NULL,
  `myfloat` float DEFAULT NULL,
  `mytext` text,
  `mydatetime` datetime DEFAULT NULL,
  `myreference` int(11) DEFAULT NULL,
  `ref` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `anothertype`
--

CREATE TABLE IF NOT EXISTS `anothertype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yourint` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `deletetype`
--

CREATE TABLE IF NOT EXISTS `deletetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) DEFAULT NULL,
  `myfloat` float DEFAULT NULL,
  `mytext` text,
  `mydatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gettype`
--

CREATE TABLE IF NOT EXISTS `gettype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) DEFAULT NULL,
  `myfloat` float DEFAULT NULL,
  `mytext` text,
  `mydatetime` datetime DEFAULT NULL,
  `myothertype` int(11) DEFAULT NULL,
  `mycircular` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `inserttype`
--

CREATE TABLE IF NOT EXISTS `inserttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) DEFAULT NULL,
  `myfloat` float DEFAULT NULL,
  `mytext` text,
  `mydatetime` datetime DEFAULT NULL,
  `mycircularreference` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `othertype`
--

CREATE TABLE IF NOT EXISTS `othertype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yourint` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `parenttype1`
--

CREATE TABLE IF NOT EXISTS `parenttype1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) DEFAULT NULL,
  `myfloat` float DEFAULT NULL,
  `mytext` text,
  `mydatetime` datetime DEFAULT NULL,
  `myothertype` int(11) DEFAULT NULL,
  `mycircular` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `parenttype2`
--

CREATE TABLE IF NOT EXISTS `parenttype2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yourint` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `persistencetype`
--

CREATE TABLE IF NOT EXISTS `persistencetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) NOT NULL,
  `myfloat` float NOT NULL,
  `mytext` text NOT NULL,
  `mydatetime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `simpleupdatetype`
--

CREATE TABLE IF NOT EXISTS `simpleupdatetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `myint` int(11) DEFAULT NULL,
  `myfloat` float DEFAULT NULL,
  `mytext` text,
  `mydatetime` datetime DEFAULT NULL,
  `myreference` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `thirdtype`
--

CREATE TABLE IF NOT EXISTS `thirdtype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `yetanothertype`
--

CREATE TABLE IF NOT EXISTS `yetanothertype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yourint` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
