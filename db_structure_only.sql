-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Serveur: 127.0.0.1:9999
-- Généré le : Ven 12 Mars 2010 à 18:35
-- Version du serveur: 5.0.51
-- Version de PHP: 5.2.10-2ubuntu6.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `cybercity2034_v4`
--

-- --------------------------------------------------------

--
-- Structure de la table `cc_account`
--

DROP TABLE IF EXISTS `cc_account`;
CREATE TABLE IF NOT EXISTS `cc_account` (
  `id` mediumint(5) unsigned NOT NULL auto_increment,
  `idcookie` varchar(50) NOT NULL default '',
  `user` varchar(25) NOT NULL,
  `pass` varchar(25) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sexe` enum('m','f') NOT NULL,
  `date_inscr` int(10) NOT NULL default '0',
  `pub` varchar(10) NOT NULL,
  `pub_detail` varchar(50) NOT NULL,
  `remise` int(10) NOT NULL default '0',
  `remise_tag` varchar(25) default NULL,
  `last_conn` int(10) NOT NULL default '0',
  `skin` varchar(15) NOT NULL default 'dark_blue',
  `skin_localpath` varchar(200) NOT NULL default '',
  `heitems` int(3) NOT NULL default '10',
  `bloque` enum('0','1') NOT NULL default '0',
  `code_validation` varchar(15) default NULL,
  `mp` int(1) NOT NULL default '0',
  `mp_expiration` int(10) NOT NULL default '0',
  `auth_doublons` enum('0','1') NOT NULL default '0',
  `auth_creation_perso` tinyint(1) NOT NULL default '1',
  `log_login` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`email`),
  KEY `remise_tag` (`remise_tag`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_banque`
--

DROP TABLE IF EXISTS `cc_banque`;
CREATE TABLE IF NOT EXISTS `cc_banque` (
  `banque_id` int(12) NOT NULL auto_increment,
  `banque_lieu` varchar(100) NOT NULL,
  `banque_no` int(4) NOT NULL default '0',
  `banque_nom` varchar(50) NOT NULL,
  `banque_retrait` smallint(1) NOT NULL default '1',
  `banque_frais_ouverture` char(3) NOT NULL default '50',
  `banque_telephone` smallint(1) NOT NULL default '0',
  PRIMARY KEY  (`banque_id`),
  KEY `no` (`banque_no`),
  KEY `lieu` (`banque_lieu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_banque_cartes`
--

DROP TABLE IF EXISTS `cc_banque_cartes`;
CREATE TABLE IF NOT EXISTS `cc_banque_cartes` (
  `carte_id` int(12) NOT NULL auto_increment COMMENT 'nocarte',
  `carte_banque` varchar(4) NOT NULL,
  `carte_compte` varchar(14) NOT NULL,
  `carte_nom` varchar(25) NOT NULL,
  `carte_nip` int(5) NOT NULL default '0',
  `carte_valid` tinyint(1) default '1',
  PRIMARY KEY  (`carte_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_banque_comptes`
--

DROP TABLE IF EXISTS `cc_banque_comptes`;
CREATE TABLE IF NOT EXISTS `cc_banque_comptes` (
  `compte_id` int(12) NOT NULL auto_increment,
  `compte_idperso` int(12) NOT NULL default '0',
  `compte_nom` varchar(50) NOT NULL,
  `compte_banque` int(4) NOT NULL default '0',
  `compte_compte` varchar(14) NOT NULL,
  `compte_cash` int(12) NOT NULL default '0',
  `compte_nip` int(5) NOT NULL default '0',
  PRIMARY KEY  (`compte_id`),
  KEY `compte_compte` (`compte_compte`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=113 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_banque_historique`
--

DROP TABLE IF EXISTS `cc_banque_historique`;
CREATE TABLE IF NOT EXISTS `cc_banque_historique` (
  `id` int(11) NOT NULL auto_increment,
  `compte` varchar(19) NOT NULL,
  `date` varchar(10) NOT NULL,
  `compte2` varchar(19) NOT NULL,
  `code` varchar(20) NOT NULL,
  `retrait` int(12) NOT NULL default '0',
  `depot` int(12) NOT NULL default '0',
  `solde` int(12) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `account` (`compte`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=131 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_buglog`
--

DROP TABLE IF EXISTS `cc_buglog`;
CREATE TABLE IF NOT EXISTS `cc_buglog` (
  `id` int(11) NOT NULL auto_increment,
  `date` int(10) NOT NULL default '0',
  `ip` varchar(100) NOT NULL,
  `vardump` longtext NOT NULL,
  `msg` text NOT NULL,
  `file` text NOT NULL,
  `line` int(5) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `datetime` (`date`,`ip`),
  FULLTEXT KEY `msg` (`msg`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_caract`
--

DROP TABLE IF EXISTS `cc_caract`;
CREATE TABLE IF NOT EXISTS `cc_caract` (
  `id` smallint(2) unsigned NOT NULL auto_increment,
  `catid` int(12) NOT NULL COMMENT '0 = categorie seulement',
  `type` enum('system','custom') NOT NULL,
  `nom` tinytext NOT NULL,
  `desc` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `catId` (`catid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=85 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_caract_incompatible`
--

DROP TABLE IF EXISTS `cc_caract_incompatible`;
CREATE TABLE IF NOT EXISTS `cc_caract_incompatible` (
  `id1` smallint(2) unsigned NOT NULL,
  `id2` smallint(2) unsigned NOT NULL,
  PRIMARY KEY  (`id1`,`id2`),
  KEY `id2` (`id2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_casino`
--

DROP TABLE IF EXISTS `cc_casino`;
CREATE TABLE IF NOT EXISTS `cc_casino` (
  `casino_id` int(12) NOT NULL auto_increment,
  `casino_lieu` varchar(100) NOT NULL,
  `casino_nom` varchar(50) NOT NULL,
  `casino_cash` int(12) NOT NULL default '0',
  PRIMARY KEY  (`casino_id`),
  KEY `lieu` (`casino_lieu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_competence`
--

DROP TABLE IF EXISTS `cc_competence`;
CREATE TABLE IF NOT EXISTS `cc_competence` (
  `id` smallint(2) unsigned NOT NULL auto_increment,
  `abbr` varchar(4) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `efface` enum('0','1') NOT NULL COMMENT 'Si la compÃ©tence peut-Ãªtre effacÃ©e par le paneau d''administration',
  `inscription` enum('0','1') NOT NULL COMMENT 'Si la compÃ©tence apparaitra lors de l''inscription',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `abbr` (`abbr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_competence_stat`
--

DROP TABLE IF EXISTS `cc_competence_stat`;
CREATE TABLE IF NOT EXISTS `cc_competence_stat` (
  `compid` smallint(2) unsigned NOT NULL,
  `statid` smallint(2) unsigned NOT NULL,
  `stat_multi` tinyint(1) NOT NULL COMMENT 'multiplicateur, utile pour faire ARMB = 1xint+2xagi',
  PRIMARY KEY  (`compid`,`statid`),
  KEY `statid` (`statid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `cc_he`
--

DROP TABLE IF EXISTS `cc_he`;
CREATE TABLE IF NOT EXISTS `cc_he` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `date` int(10) unsigned NOT NULL default '1107366894',
  `type` varchar(20) NOT NULL,
  `msg` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56611 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_he_fromto`
--

DROP TABLE IF EXISTS `cc_he_fromto`;
CREATE TABLE IF NOT EXISTS `cc_he_fromto` (
  `msgid` int(12) unsigned NOT NULL default '0',
  `fromto` enum('from','to') NOT NULL default 'from',
  `persoid` varchar(12) NOT NULL default '0',
  `lieuid` mediumint(5) unsigned NOT NULL default '0',
  `masque` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `show` tinyint(1) NOT NULL default '1' COMMENT '0=non, 1=oui, 2=uniquement si moi-mÃªme',
  PRIMARY KEY  (`persoid`,`show`,`msgid`,`fromto`),
  KEY `msgid` (`msgid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_item_db`
--

DROP TABLE IF EXISTS `cc_item_db`;
CREATE TABLE IF NOT EXISTS `cc_item_db` (
  `db_id` mediumint(8) unsigned NOT NULL auto_increment,
  `db_type` enum('arme','autre','badge','cartebanque','cartememoire','clef','defense','drogue','livre','munition','nourriture','ordinateur','sac','talkiewalkie','telephone','trousse') NOT NULL default 'autre',
  `db_soustype` enum('aucun','arme_feu','arme_blanche','arme_lancee','arme_lourde','arme_paralysante','arme_explosif','def_tete','def_torse','def_bras','def_main','def_jambe','def_pied','drogue_drogue','drogue_substance','drogue_antirejet','drogue_autre') NOT NULL default 'aucun',
  `db_regrouper` enum('0','1') NOT NULL default '0',
  `db_nom` varchar(150) NOT NULL default '' COMMENT 'Livre: titre;',
  `db_desc` text NOT NULL,
  `db_valeur` int(12) NOT NULL default '0',
  `db_img` varchar(150) NOT NULL default 'SYS_none.gif',
  `db_pr` smallint(3) NOT NULL default '0',
  `db_pn` smallint(3) default NULL,
  `db_force` smallint(3) default NULL COMMENT 'PN',
  `db_portee` enum('TC','C','M','L','TL') default NULL,
  `db_tir_par_tour` smallint(1) default NULL,
  `db_fiabilite` smallint(3) default NULL,
  `db_precision` smallint(3) default NULL,
  `db_capacite` int(11) default NULL,
  `db_pass` varchar(20) default NULL COMMENT 'Livre: Auteur',
  `db_forumaccess` int(12) default NULL,
  `db_masque` enum('0','1') default NULL,
  `db_seuilresistance` smallint(2) default NULL,
  `db_resistance` smallint(3) default NULL,
  `db_duree` smallint(3) default NULL,
  `db_shock_pa` smallint(3) default NULL,
  `db_shock_pv` smallint(3) default NULL,
  `db_boost_pa` smallint(3) default NULL,
  `db_boost_pv` smallint(3) default NULL,
  `db_perc_stat_agi` smallint(3) default NULL,
  `db_perc_stat_dex` smallint(3) default NULL,
  `db_perc_stat_per` smallint(3) default NULL,
  `db_perc_stat_for` smallint(3) default NULL,
  `db_perc_stat_int` smallint(3) default NULL,
  `db_internet` enum('0','1') default NULL,
  `db_mcread` enum('0','1') default NULL,
  `db_mcwrite` enum('0','1') default NULL COMMENT 'CM: 0 -> non editable',
  `db_memoire` mediumint(7) default NULL,
  `db_afficheur` enum('0','1','2') default NULL,
  `db_anonyme` enum('0','1') default NULL COMMENT 'Cryptage radio',
  `db_param` longtext COMMENT 'Livre: Texte du livre ; CM: Message par defaut sur la carte et non modifiable',
  `db_notemj` text NOT NULL,
  PRIMARY KEY  (`db_id`),
  KEY `db_type` (`db_type`,`db_soustype`,`db_nom`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=509 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_item_db_armemunition`
--

DROP TABLE IF EXISTS `cc_item_db_armemunition`;
CREATE TABLE IF NOT EXISTS `cc_item_db_armemunition` (
  `id` int(8) NOT NULL auto_increment,
  `db_armeid` int(8) NOT NULL default '0',
  `db_munitionid` int(8) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `db_armeid` (`db_armeid`,`db_munitionid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Quelle munition utilise une arme?' AUTO_INCREMENT=69 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_item_inv`
--

DROP TABLE IF EXISTS `cc_item_inv`;
CREATE TABLE IF NOT EXISTS `cc_item_inv` (
  `inv_id` int(12) unsigned NOT NULL auto_increment,
  `inv_dbid` mediumint(8) unsigned NOT NULL default '0',
  `inv_persoid` int(12) default NULL,
  `inv_equip` enum('0','1') default NULL,
  `inv_lieutech` varchar(150) default NULL,
  `inv_boutiquelieutech` varchar(150) default NULL,
  `inv_itemid` int(12) default NULL,
  `inv_idcasier` int(11) default NULL,
  `inv_desc` text,
  `inv_img` varchar(150) default NULL,
  `inv_qte` smallint(7) unsigned NOT NULL default '1',
  `inv_munition` mediumint(5) default NULL,
  `inv_resistance` mediumint(3) default NULL,
  `inv_duree` smallint(3) default NULL,
  `inv_shock_pa` smallint(3) default NULL,
  `inv_shock_pv` smallint(3) default NULL,
  `inv_boost_pa` smallint(3) default NULL,
  `inv_boost_pv` smallint(3) default NULL,
  `inv_perc_stat_agi` smallint(3) default NULL,
  `inv_perc_stat_dex` smallint(3) default NULL,
  `inv_perc_stat_per` smallint(3) default NULL,
  `inv_perc_stat_for` smallint(3) default NULL,
  `inv_perc_stat_int` smallint(3) default NULL,
  `inv_remiseleft` smallint(2) default NULL,
  `inv_pn` smallint(3) default NULL,
  `inv_notel` varchar(8) default NULL COMMENT 'pour les radio: Frequence sur laquelle est réglé la radio',
  `inv_memoiretext` text COMMENT 'Clé: Nom',
  `inv_nobanque` varchar(4) default NULL,
  `inv_nocompte` varchar(14) default NULL,
  `inv_nocarte` mediumint(7) default NULL,
  `inv_nip` int(5) default NULL COMMENT 'pour les radio: clef de cryptage entrée',
  `inv_boutiquePrixVente` float default NULL,
  `inv_boutiquePrixAchat` float default NULL,
  `inv_param` text COMMENT 'Clé: Pass',
  `inv_extradesc` text NOT NULL,
  `inv_notemj` text NOT NULL,
  `inv_cacheno` int(11) default NULL,
  `inv_cachetaux` int(11) default NULL,
  PRIMARY KEY  (`inv_id`),
  KEY `inv_dbid` (`inv_dbid`),
  KEY `inv_persoid` (`inv_persoid`),
  KEY `inv_lieutech` (`inv_lieutech`),
  KEY `inv_boutiquelieutech` (`inv_boutiquelieutech`),
  KEY `inv_itemid` (`inv_itemid`),
  KEY `inv_idcasier` (`inv_idcasier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5082 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_item_menu`
--

DROP TABLE IF EXISTS `cc_item_menu`;
CREATE TABLE IF NOT EXISTS `cc_item_menu` (
  `id` int(12) NOT NULL auto_increment,
  `item_dbid` int(12) NOT NULL default '0',
  `caption` varchar(30) NOT NULL,
  `url` varchar(150) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lieutech` (`item_dbid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu`
--

DROP TABLE IF EXISTS `cc_lieu`;
CREATE TABLE IF NOT EXISTS `cc_lieu` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `nom_technique` varchar(150) NOT NULL,
  `nom_affiche` tinytext NOT NULL,
  `description` text NOT NULL,
  `dimension` enum('TC','C','M','L','TL') NOT NULL default 'M',
  `image` varchar(30) NOT NULL,
  `proprioid` int(12) default NULL,
  `boutique_cash` float default NULL,
  `boutique_compte` varchar(19) default NULL,
  `boutique_vol` smallint(1) NOT NULL,
  `coeff_soin` smallint(6) NOT NULL default '0',
  `qteMateriel` int(11) NOT NULL default '0',
  `notemj` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `nom_technique` (`nom_technique`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=604 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_ban`
--

DROP TABLE IF EXISTS `cc_lieu_ban`;
CREATE TABLE IF NOT EXISTS `cc_lieu_ban` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `persoid` int(5) unsigned NOT NULL default '0',
  `lieu` varchar(150) NOT NULL,
  `remiseleft` smallint(1) NOT NULL default '9',
  PRIMARY KEY  (`id`),
  KEY `persoid` (`persoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_casier`
--

DROP TABLE IF EXISTS `cc_lieu_casier`;
CREATE TABLE IF NOT EXISTS `cc_lieu_casier` (
  `id_casier` int(12) unsigned NOT NULL auto_increment,
  `nom_casier` varchar(150) default NULL,
  `lieuId` mediumint(8) unsigned default NULL,
  `capacite_casier` int(11) default NULL,
  `protection_casier` enum('pass','clef') default NULL,
  `resistance_casier` smallint(6) default NULL,
  `pass_casier` varchar(150) default NULL,
  PRIMARY KEY  (`id_casier`),
  KEY `lieuId` (`lieuId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_distributeur`
--

DROP TABLE IF EXISTS `cc_lieu_distributeur`;
CREATE TABLE IF NOT EXISTS `cc_lieu_distributeur` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `lieuId` mediumint(8) unsigned NOT NULL,
  `producteurId` smallint(2) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `lieuId` (`lieuId`,`producteurId`),
  KEY `producteurId` (`producteurId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_etude`
--

DROP TABLE IF EXISTS `cc_lieu_etude`;
CREATE TABLE IF NOT EXISTS `cc_lieu_etude` (
  `lieuId` mediumint(8) unsigned NOT NULL,
  `comp` enum('ACRO','ARMB','ARMC','ARMF','ARML','ARMU','ARTI','ATHL','CHIM','CHRG','CROC','CRYP','CUIS','CYBR','DRSG','ELEC','ENSG','ESQV','EXPL','FORG','FRTV','GENE','HCKG','HRDW','LNCR','MECA','MRCH','PCKP','PLTG','PROG','PSYC','SCRS','TOXI') NOT NULL,
  `cout_cash` int(8) NOT NULL default '0' COMMENT 'Cout Cash / 1 tour',
  `cout_pa` int(8) NOT NULL default '3' COMMENT 'Cout PA / 1 tour',
  `qualite_lieu` tinyint(3) NOT NULL default '70' COMMENT 'Ambience du lieu propice à l''étude',
  PRIMARY KEY  (`lieuId`,`comp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_lien`
--

DROP TABLE IF EXISTS `cc_lieu_lien`;
CREATE TABLE IF NOT EXISTS `cc_lieu_lien` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `from` varchar(150) NOT NULL,
  `to` varchar(150) NOT NULL,
  `icon` varchar(25) default NULL,
  `pa` smallint(2) NOT NULL default '0',
  `cout` int(12) NOT NULL default '0',
  `protection` enum('0','pass','clef') NOT NULL default '0',
  `pass` varchar(15) NOT NULL,
  `bloque` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `from` (`from`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1419 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_livre`
--

DROP TABLE IF EXISTS `cc_lieu_livre`;
CREATE TABLE IF NOT EXISTS `cc_lieu_livre` (
  `lieuId` mediumint(8) unsigned NOT NULL,
  `itemDbId` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`lieuId`,`itemDbId`),
  KEY `itemDbId` (`itemDbId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_menu`
--

DROP TABLE IF EXISTS `cc_lieu_menu`;
CREATE TABLE IF NOT EXISTS `cc_lieu_menu` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `lieutech` varchar(150) NOT NULL,
  `caption` varchar(30) NOT NULL,
  `url` varchar(150) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lieutech` (`lieutech`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_lieu_tenirporte`
--

DROP TABLE IF EXISTS `cc_lieu_tenirporte`;
CREATE TABLE IF NOT EXISTS `cc_lieu_tenirporte` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `de` varchar(150) NOT NULL,
  `vers` varchar(150) NOT NULL,
  `qui` int(5) unsigned NOT NULL,
  `expiration` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `vers` (`vers`,`qui`),
  KEY `qui` (`qui`),
  KEY `expiration` (`expiration`),
  KEY `de` (`de`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=132 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_log_conn`
--

DROP TABLE IF EXISTS `cc_log_conn`;
CREATE TABLE IF NOT EXISTS `cc_log_conn` (
  `id` int(12) NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `timestamp` int(10) NOT NULL,
  `user` varchar(25) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host` varchar(50) NOT NULL,
  `cookie` varchar(50) NOT NULL,
  `client` varchar(150) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_log_mp`
--

DROP TABLE IF EXISTS `cc_log_mp`;
CREATE TABLE IF NOT EXISTS `cc_log_mp` (
  `id` int(12) NOT NULL auto_increment,
  `userId` int(12) NOT NULL,
  `date` int(12) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `item` varchar(15) NOT NULL,
  `statusPP` tinytext NOT NULL,
  `statusCC` tinytext NOT NULL,
  `post` blob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `email` (`email`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_log_persomort`
--

DROP TABLE IF EXISTS `cc_log_persomort`;
CREATE TABLE IF NOT EXISTS `cc_log_persomort` (
  `id` int(12) NOT NULL auto_increment,
  `perso` varchar(25) NOT NULL,
  `persoId` int(12) NOT NULL,
  `timestamp` int(15) NOT NULL,
  `from` varchar(25) NOT NULL,
  `fromId` int(12) NOT NULL,
  `action` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_log_persosuppr`
--

DROP TABLE IF EXISTS `cc_log_persosuppr`;
CREATE TABLE IF NOT EXISTS `cc_log_persosuppr` (
  `id` int(11) NOT NULL auto_increment,
  `timestamp` bigint(30) NOT NULL,
  `perso` varchar(30) NOT NULL,
  `mj` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_log_telephone`
--

DROP TABLE IF EXISTS `cc_log_telephone`;
CREATE TABLE IF NOT EXISTS `cc_log_telephone` (
  `id_he_exp` int(12) NOT NULL default '0',
  `id_he_dest` int(11) NOT NULL default '0',
  `date` int(10) NOT NULL default '0',
  `from_tel` varchar(8) NOT NULL,
  `from_persoid` int(12) NOT NULL default '0',
  `to_tel` varchar(8) NOT NULL,
  `to_persoid` int(12) NOT NULL default '0',
  PRIMARY KEY  (`id_he_exp`),
  KEY `from_tel` (`from_tel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_mairie_question`
--

DROP TABLE IF EXISTS `cc_mairie_question`;
CREATE TABLE IF NOT EXISTS `cc_mairie_question` (
  `id` int(12) NOT NULL auto_increment,
  `section` smallint(2) NOT NULL,
  `question` text NOT NULL,
  `reponse_tech` varchar(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `section` (`section`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_mairie_question_reponse`
--

DROP TABLE IF EXISTS `cc_mairie_question_reponse`;
CREATE TABLE IF NOT EXISTS `cc_mairie_question_reponse` (
  `questionId` int(12) NOT NULL,
  `reponse_tech` varchar(1) NOT NULL,
  `reponse` text NOT NULL,
  PRIMARY KEY  (`questionId`,`reponse_tech`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_mj`
--

DROP TABLE IF EXISTS `cc_mj`;
CREATE TABLE IF NOT EXISTS `cc_mj` (
  `id` int(12) NOT NULL auto_increment,
  `userId` int(12) NOT NULL,
  `nom` varchar(25) NOT NULL,
  `poste` varchar(100) NOT NULL,
  `email_prefix` varchar(20) NOT NULL,
  `present` smallint(1) NOT NULL default '0',
  `ax_ppa` smallint(1) NOT NULL default '0',
  `ax_ej` smallint(1) NOT NULL default '0',
  `ax_hj` smallint(1) NOT NULL default '0',
  `ax_admin` smallint(1) NOT NULL default '0',
  `last_connection` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_mj_he`
--

DROP TABLE IF EXISTS `cc_mj_he`;
CREATE TABLE IF NOT EXISTS `cc_mj_he` (
  `id` int(11) NOT NULL auto_increment,
  `msg` text NOT NULL,
  `mjId` int(12) NOT NULL default '0',
  `concernant` varchar(25) NOT NULL COMMENT 'Nom du personnage ou du compte, selon',
  `concernant_type` enum('system','perso','perso','lieu','item','mj') NOT NULL,
  `date` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `mjId` (`mjId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=438 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso`
--

DROP TABLE IF EXISTS `cc_perso`;
CREATE TABLE IF NOT EXISTS `cc_perso` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `userId` mediumint(5) unsigned NOT NULL,
  `nom` varchar(25) NOT NULL,
  `sexe` enum('m','f') NOT NULL default 'm',
  `age` tinyint(2) unsigned NOT NULL default '0',
  `taille` varchar(4) NOT NULL,
  `yeux` tinytext NOT NULL,
  `ethnie` varchar(20) NOT NULL,
  `cheveux` tinytext NOT NULL,
  `poids` mediumint(3) unsigned NOT NULL default '0',
  `playertype` enum('humain','animal','robot','objet','training') NOT NULL default 'humain',
  `prmax` smallint(3) unsigned NOT NULL default '10',
  `pa` smallint(3) NOT NULL default '50',
  `pamax` smallint(3) unsigned NOT NULL default '99',
  `pv` smallint(3) NOT NULL default '99',
  `pvmax` smallint(3) unsigned NOT NULL default '99',
  `pn` smallint(2) NOT NULL default '99',
  `lng1` varchar(2) NOT NULL,
  `lng1_lvl` varchar(2) NOT NULL,
  `lng2` varchar(2) NOT NULL,
  `lng2_lvl` varchar(2) NOT NULL,
  `vies` smallint(1) NOT NULL default '1',
  `cash` mediumint(12) NOT NULL default '0',
  `lieu` varchar(150) NOT NULL,
  `current_action` text NOT NULL,
  `description` text NOT NULL,
  `background` text NOT NULL,
  `note_mj` text NOT NULL,
  `imgurl` varchar(200) NOT NULL,
  `esquive` enum('0','1') NOT NULL default '1',
  `reaction` enum('rien','riposte','fuir') NOT NULL default 'riposte',
  `soin` tinyint(1) unsigned NOT NULL default '0',
  `menotte` int(12) default NULL COMMENT 'Si menotte, inv_id des menottes',
  `bloque` enum('0','1') NOT NULL default '0',
  `inscription_valide` enum('0','1','mod') NOT NULL default '0' COMMENT '0=Non, 1=Oui (Jouable), mod = Une modification doit être faite.',
  `visa_perm` int(10) NOT NULL default '0' COMMENT '0=Non; 1=Oui; autre=dernier exam',
  `heQte` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=542 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso_caract`
--

DROP TABLE IF EXISTS `cc_perso_caract`;
CREATE TABLE IF NOT EXISTS `cc_perso_caract` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `persoid` int(5) unsigned NOT NULL,
  `caractid` smallint(2) unsigned NOT NULL,
  `desc` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `perso_caract` (`persoid`,`caractid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5860 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso_competence`
--

DROP TABLE IF EXISTS `cc_perso_competence`;
CREATE TABLE IF NOT EXISTS `cc_perso_competence` (
  `persoid` int(5) unsigned NOT NULL,
  `compid` smallint(2) unsigned NOT NULL,
  `xp` int(12) NOT NULL,
  PRIMARY KEY  (`persoid`,`compid`),
  KEY `compid` (`compid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso_connu`
--

DROP TABLE IF EXISTS `cc_perso_connu`;
CREATE TABLE IF NOT EXISTS `cc_perso_connu` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `persoid` int(5) unsigned NOT NULL default '0' COMMENT 'Id du perso qui connait une personne',
  `nomid` int(5) unsigned NOT NULL default '0' COMMENT 'id du personne connue du perso',
  `nom` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `persoid` (`persoid`),
  KEY `nomid` (`nomid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3192 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso_fouille`
--

DROP TABLE IF EXISTS `cc_perso_fouille`;
CREATE TABLE IF NOT EXISTS `cc_perso_fouille` (
  `fromid` int(5) unsigned NOT NULL COMMENT 'fouille par',
  `toid` int(5) unsigned NOT NULL COMMENT 'sera fouillé',
  `expiration` int(10) NOT NULL,
  `reponse` smallint(1) NOT NULL,
  PRIMARY KEY  (`fromid`,`toid`),
  KEY `expiration` (`expiration`),
  KEY `toid` (`toid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso_menotte`
--

DROP TABLE IF EXISTS `cc_perso_menotte`;
CREATE TABLE IF NOT EXISTS `cc_perso_menotte` (
  `inv_id` int(12) unsigned NOT NULL COMMENT 'menotté par',
  `to_id` int(5) unsigned NOT NULL COMMENT 'sera menotté',
  `expiration` int(10) NOT NULL,
  PRIMARY KEY  (`inv_id`,`to_id`),
  KEY `expiration` (`expiration`),
  KEY `to_id` (`to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_perso_stat`
--

DROP TABLE IF EXISTS `cc_perso_stat`;
CREATE TABLE IF NOT EXISTS `cc_perso_stat` (
  `persoid` int(5) unsigned NOT NULL,
  `statid` smallint(2) unsigned NOT NULL,
  `xp` int(12) NOT NULL,
  PRIMARY KEY  (`persoid`,`statid`),
  KEY `statid` (`statid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `cc_ppa`
--

DROP TABLE IF EXISTS `cc_ppa`;
CREATE TABLE IF NOT EXISTS `cc_ppa` (
  `id` int(12) NOT NULL auto_increment,
  `persoid` int(12) NOT NULL,
  `type` varchar(10) NOT NULL,
  `date` int(10) NOT NULL,
  `mjid` int(12) NOT NULL default '0' COMMENT 'Si attribution == mj ID, si général == 0',
  `titre` tinytext NOT NULL,
  `msg` text NOT NULL,
  `lieu` varchar(150) NOT NULL,
  `pa` smallint(3) NOT NULL,
  `paMax` smallint(3) NOT NULL,
  `pv` smallint(3) NOT NULL,
  `pvMax` smallint(3) NOT NULL,
  `notemj` text NOT NULL,
  `statut` enum('ouvert','ferme') NOT NULL default 'ouvert',
  PRIMARY KEY  (`id`),
  KEY `parentid` (`mjid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=298 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_ppa_reponses`
--

DROP TABLE IF EXISTS `cc_ppa_reponses`;
CREATE TABLE IF NOT EXISTS `cc_ppa_reponses` (
  `id` int(12) NOT NULL auto_increment,
  `sujetid` int(12) NOT NULL,
  `mjid` int(12) NOT NULL COMMENT 'Réponse du perso = id 0',
  `date` int(10) NOT NULL,
  `msg` text NOT NULL,
  `notemj` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sujetid` (`sujetid`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=883 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_producteur`
--

DROP TABLE IF EXISTS `cc_producteur`;
CREATE TABLE IF NOT EXISTS `cc_producteur` (
  `id` smallint(2) unsigned NOT NULL auto_increment,
  `lieuId` mediumint(8) unsigned NOT NULL,
  `cash` int(11) NOT NULL,
  `pa_cash_ratio` float NOT NULL COMMENT '1pa donne Xcash',
  `total_pa` int(12) NOT NULL,
  `pa_needed` int(12) NOT NULL COMMENT 'pt requis pour lancer une production',
  PRIMARY KEY  (`id`),
  KEY `lieuId` (`lieuId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_producteur_inv`
--

DROP TABLE IF EXISTS `cc_producteur_inv`;
CREATE TABLE IF NOT EXISTS `cc_producteur_inv` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `producteurId` smallint(2) unsigned NOT NULL,
  `itemDbId` mediumint(8) unsigned NOT NULL,
  `qte` int(12) NOT NULL,
  `pa_needed` int(12) NOT NULL COMMENT 'pa requis pour produire 1 item',
  `prix` int(12) NOT NULL,
  `pack` int(6) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `producteurId_2` (`producteurId`,`itemDbId`),
  KEY `itemDbId` (`itemDbId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=175 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_session`
--

DROP TABLE IF EXISTS `cc_session`;
CREATE TABLE IF NOT EXISTS `cc_session` (
  `userId` int(12) default NULL,
  `ip` varchar(15) NOT NULL,
  `idcookie` varchar(50) NOT NULL default '',
  `expiration` varchar(15) NOT NULL,
  PRIMARY KEY  (`idcookie`),
  KEY `userId` (`userId`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cc_sitesweb`
--

DROP TABLE IF EXISTS `cc_sitesweb`;
CREATE TABLE IF NOT EXISTS `cc_sitesweb` (
  `id` int(12) NOT NULL auto_increment,
  `url` varchar(250) NOT NULL,
  `titre` varchar(250) NOT NULL,
  `acces` enum('pub','priv') NOT NULL,
  `first_page` int(12) NOT NULL COMMENT 'Afficher directement une page en plus de l''index',
  PRIMARY KEY  (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_sitesweb_acces`
--

DROP TABLE IF EXISTS `cc_sitesweb_acces`;
CREATE TABLE IF NOT EXISTS `cc_sitesweb_acces` (
  `id` int(12) NOT NULL auto_increment,
  `site_id` int(12) NOT NULL,
  `user` varchar(20) NOT NULL,
  `pass` varchar(20) NOT NULL,
  `accede` enum('0','1') NOT NULL default '0',
  `poste` enum('0','1') NOT NULL default '0',
  `modifier` enum('0','1') NOT NULL default '0',
  `admin` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_sitesweb_pages`
--

DROP TABLE IF EXISTS `cc_sitesweb_pages`;
CREATE TABLE IF NOT EXISTS `cc_sitesweb_pages` (
  `id` int(12) NOT NULL auto_increment,
  `site_id` int(12) NOT NULL,
  `msg_parentid` int(12) NOT NULL default '0',
  `titre` varchar(250) NOT NULL,
  `content` longtext NOT NULL,
  `acces` enum('pub','priv') NOT NULL,
  `showIndex` enum('0','1') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `msg_parentid` (`msg_parentid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_sitesweb_pages_acces`
--

DROP TABLE IF EXISTS `cc_sitesweb_pages_acces`;
CREATE TABLE IF NOT EXISTS `cc_sitesweb_pages_acces` (
  `id` int(12) NOT NULL auto_increment,
  `page_id` int(12) NOT NULL,
  `user_id` int(12) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=81 ;

-- --------------------------------------------------------

--
-- Structure de la table `cc_stat`
--

DROP TABLE IF EXISTS `cc_stat`;
CREATE TABLE IF NOT EXISTS `cc_stat` (
  `id` smallint(2) unsigned NOT NULL auto_increment,
  `abbr` varchar(3) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `abbr` (`abbr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `cc_caract_incompatible`
--
ALTER TABLE `cc_caract_incompatible`
  ADD CONSTRAINT `cc_caract_incompatible_ibfk_1` FOREIGN KEY (`id1`) REFERENCES `cc_caract` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_caract_incompatible_ibfk_2` FOREIGN KEY (`id2`) REFERENCES `cc_caract` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_competence_stat`
--
ALTER TABLE `cc_competence_stat`
  ADD CONSTRAINT `cc_competence_stat_ibfk_1` FOREIGN KEY (`compid`) REFERENCES `cc_competence` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_competence_stat_ibfk_2` FOREIGN KEY (`statid`) REFERENCES `cc_stat` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_he_fromto`
--
ALTER TABLE `cc_he_fromto`
  ADD CONSTRAINT `cc_he_fromto_ibfk_1` FOREIGN KEY (`msgid`) REFERENCES `cc_he` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_item_inv`
--
ALTER TABLE `cc_item_inv`
  ADD CONSTRAINT `cc_item_inv_ibfk_1` FOREIGN KEY (`inv_dbid`) REFERENCES `cc_item_db` (`db_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_lieu_ban`
--
ALTER TABLE `cc_lieu_ban`
  ADD CONSTRAINT `cc_lieu_ban_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_lieu_casier`
--
ALTER TABLE `cc_lieu_casier`
  ADD CONSTRAINT `cc_lieu_casier_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_lieu_distributeur`
--
ALTER TABLE `cc_lieu_distributeur`
  ADD CONSTRAINT `cc_lieu_distributeur_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_lieu_distributeur_ibfk_2` FOREIGN KEY (`producteurId`) REFERENCES `cc_producteur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_lieu_etude`
--
ALTER TABLE `cc_lieu_etude`
  ADD CONSTRAINT `cc_lieu_etude_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_lieu_livre`
--
ALTER TABLE `cc_lieu_livre`
  ADD CONSTRAINT `cc_lieu_livre_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_lieu_livre_ibfk_2` FOREIGN KEY (`itemDbId`) REFERENCES `cc_item_db` (`db_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_lieu_tenirporte`
--
ALTER TABLE `cc_lieu_tenirporte`
  ADD CONSTRAINT `cc_lieu_tenirporte_ibfk_1` FOREIGN KEY (`qui`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso`
--
ALTER TABLE `cc_perso`
  ADD CONSTRAINT `cc_perso_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cc_account` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso_caract`
--
ALTER TABLE `cc_perso_caract`
  ADD CONSTRAINT `cc_perso_caract_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso_competence`
--
ALTER TABLE `cc_perso_competence`
  ADD CONSTRAINT `cc_perso_competence_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_competence_ibfk_2` FOREIGN KEY (`compid`) REFERENCES `cc_competence` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso_connu`
--
ALTER TABLE `cc_perso_connu`
  ADD CONSTRAINT `cc_perso_connu_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_connu_ibfk_2` FOREIGN KEY (`nomid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso_fouille`
--
ALTER TABLE `cc_perso_fouille`
  ADD CONSTRAINT `cc_perso_fouille_ibfk_1` FOREIGN KEY (`fromid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_fouille_ibfk_2` FOREIGN KEY (`toid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso_menotte`
--
ALTER TABLE `cc_perso_menotte`
  ADD CONSTRAINT `cc_perso_menotte_ibfk_1` FOREIGN KEY (`inv_id`) REFERENCES `cc_item_inv` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_menotte_ibfk_2` FOREIGN KEY (`to_id`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_perso_stat`
--
ALTER TABLE `cc_perso_stat`
  ADD CONSTRAINT `cc_perso_stat_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_stat_ibfk_2` FOREIGN KEY (`statid`) REFERENCES `cc_stat` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_producteur`
--
ALTER TABLE `cc_producteur`
  ADD CONSTRAINT `cc_producteur_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cc_producteur_inv`
--
ALTER TABLE `cc_producteur_inv`
  ADD CONSTRAINT `cc_producteur_inv_ibfk_1` FOREIGN KEY (`producteurId`) REFERENCES `cc_producteur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_producteur_inv_ibfk_2` FOREIGN KEY (`itemDbId`) REFERENCES `cc_item_db` (`db_id`) ON DELETE CASCADE;

  
  
  
  
  
---- MAJ

--
-- Structure table cc_boutiques_gerants + conversion
--

DROP TABLE IF EXISTS `cc_boutiques_gerants`;
CREATE TABLE IF NOT EXISTS `cc_boutiques_gerants` AS 
(SELECT `proprioid` as `persoid`, `id` as `boutiqueid` 
FROM `cc_lieu` WHERE `proprioid` != 'NULL');

ALTER TABLE `cc_boutiques_gerants` 
ADD PRIMARY KEY(`boutiqueid`, `persoid`);

--
-- Suppression du champ inutile dans cc_lieu
--

ALTER TABLE `cc_lieu`
DROP COLUMN `proprioid`;

--
-- Structure tables relatives aux médias
-- cc_media cc_lieu_medias
--

DROP TABLE IF EXISTS `cc_media`;
CREATE TABLE IF NOT EXISTS `cc_media` (
	`id` int(12) NOT NULL auto_increment,
	`mediaType` enum('radio','tele') NOT NULL,
	`canalId` int(12) NOT NULL,
	`date` int(10) unsigned NOT NULL,
	`titre` text NOT NULL,
	`message` longtext NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `cc_lieu_medias`;
CREATE TABLE IF NOT EXISTS `cc_lieu_medias` (
	`id` int(12) NOT NULL auto_increment,
	`lieuid` mediumint(8) unsigned NOT NULL,
	`nom` varchar(50) NOT NULL,
	`mediaType` enum('radio','tele', 'tous') NOT NULL,
	`canalId` int(12) NOT NULL,
	`interactionType` smallint(1) NOT NULL COMMENT 'reception = 0, emission = 1', 
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	
--
-- Modification table item
-- Ajout d'un type d'objet
--

ALTER TABLE `cc_item_db` CHANGE `db_type` `db_type` ENUM( 'arme', 'autre', 'badge', 'cartebanque', 'cartememoire', 'clef', 'defense', 'drogue', 'livre', 'munition', 'nourriture', 'ordinateur', 'sac', 'talkiewalkie', 'telephone', 'trousse', 'media' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'autre' ;

--
-- Structure tables relatives aux historiques des boutiques
-- cc_boutiques_historiques
--

DROP TABLE IF EXISTS `cc_boutiques_historiques` ;
CREATE TABLE IF NOT EXISTS `cc_boutiques_historiques` (
	`id` int(12) NOT NULL auto_increment,
	`boutiqueid` int(12) NOT NULL,
	`date` int(10) NOT NULL,
	`transactiontype` varchar(10) NOT NULL COMMENT 'ACH, VEN, DEP, RET <=> achat, vente, depot, retrait',
	`itemlist` text,
	`marchandage` int(1),
	`moyenpaiement` int(1) COMMENT 'CB = 1, comptant = 0',
	`prixtotal` int(12) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Modification table cc_mj
-- Ajout d'un accès développeur
--

ALTER TABLE `cc_mj`
ADD COLUMN `ax_dev` smallint(1) NOT NULL DEFAULT 0;

--
-- Modification table cc_banque_comptes
-- Ajout d'une authorisation de transactions automatiques
--

ALTER TABLE `cc_banque_comptes`
ADD COLUMN `compte_auth_auto_transaction` smallint(1) NOT NULL DEFAULT 0;

--
-- Structure table relative aux transactions automatiques
-- cc_banque_transactions
--

DROP TABLE IF EXISTS `cc_banque_transactions`;
CREATE TABLE IF NOT EXISTS `cc_banque_transactions` (
	`transaction_id` int(12) NOT NULL auto_increment,
	`transaction_compte_from` int(12) NOT NULL,
	`transaction_compte_to` int(12) NOT NULL,
	`transaction_valeur` int(12) NOT NULL,
	`transaction_description` varchar(50) NOT NULL,
	`transaction_date` int(10) unsigned NOT NULL,
	PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Structure table stockant les descriptions des perso associés à un message sans doublon
-- cc_he_description
--

DROP TABLE IF EXISTS `cc_he_description`;
CREATE TABLE IF NOT EXISTS `cc_he_description` (
	`id` int(12) NOT NULL auto_increment,
	`description` text NOT NULL,
	`msg_who_use` int(12) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Remplissage de la table des descriptions avec les descriptions déjà présentes dans la table he_fromto
--

INSERT INTO `cc_he_description` (`description`, `msg_who_use`)
SELECT `description`, COUNT(*) AS `nb_desc`
FROM `cc_he_fromto` 
GROUP BY `description`;

--
-- Ajout du champ id_description correspondant dans la table cc_he_fromto
--

ALTER TABLE `cc_he_fromto`
ADD COLUMN `id_description` int(12) NOT NULL DEFAULT 0;

--
-- Remplissage du nouveau champ id_description dans la table cc_he_fromto
--

UPDATE `cc_he_fromto` as ft LEFT JOIN `cc_he_description` as d ON (ft.description = d.description)
SET ft.id_description = d.id;

--
-- Suppression du champ description dans la table cc_he_fromto
--

ALTER TABLE `cc_he_fromto`
DROP COLUMN `description`;

--
-- Ajout d'une colonne name_complement pour stocker "System" ou autres trucs
--

ALTER TABLE `cc_he_fromto`
ADD COLUMN `name_complement` varchar(25) NOT NULL DEFAULT "";

--
-- Remplissage de la colonne name_completement
-- Modification de la colonne persoid en concordance
--

UPDATE `cc_he_fromto`
SET `name_complement` = `persoid`, `persoid` = 0
WHERE `persoid` = 0;

--
-- Modification du type de la colonne persoid pour le rendre uniquement int
--

ALTER TABLE `cc_he_fromto` 
CHANGE `persoid` 
`persoid` int(12) NOT NULL DEFAULT 0;
