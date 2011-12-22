-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Serveur: 127.0.0.1
-- Généré le : Sam 17 Décembre 2011 à 00:09
-- Version du serveur: 5.1.59
-- Version de PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `ftp`
--

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(15) NOT NULL,
  `username` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `groups`
--


-- --------------------------------------------------------

--
-- Structure de la table `history`
--

CREATE TABLE IF NOT EXISTS `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `transfertype` varchar(10) DEFAULT NULL,
  `transfersize` int(11) DEFAULT NULL,
  `transferhost` varchar(30) DEFAULT NULL,
  `transfertime` varchar(20) DEFAULT NULL,
  `transferdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `history`
--


-- --------------------------------------------------------

--
-- Structure de la table `listgroups`
--

CREATE TABLE IF NOT EXISTS `listgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(15) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `listgroups`
--


-- --------------------------------------------------------

--
-- Structure de la table `userevents`
--

CREATE TABLE IF NOT EXISTS `userevents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) DEFAULT NULL,
  `eventtype` varchar(10) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `eventdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `userevents`
--


-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `passwd` varchar(15) NOT NULL,
  `fullname` varchar(60) DEFAULT NULL,
  `valid` smallint(6) DEFAULT '0',
  `count` int(11) DEFAULT '0',
  `lastlogin` datetime DEFAULT NULL,
  `homedir` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `frate` tinyint(4) NOT NULL DEFAULT '0',
  `fcred` tinyint(4) NOT NULL DEFAULT '0',
  `brate` tinyint(4) NOT NULL DEFAULT '0',
  `bcred` int(20) NOT NULL DEFAULT '0',
  `fstor` int(20) NOT NULL DEFAULT '0',
  `fretr` int(20) NOT NULL DEFAULT '0',
  `bstor` bigint(64) NOT NULL DEFAULT '0',
  `bretr` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`),
  UNIQUE KEY `id_2` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `users`
--
