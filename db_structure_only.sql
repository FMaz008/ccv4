-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2017 at 07:58 PM
-- Server version: 5.7.10
-- PHP Version: 5.6.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET names 'utf8';

--
-- Database: `ccv4`
--

-- --------------------------------------------------------


SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `cc_account`;
DROP TABLE IF EXISTS `cc_banque`;
DROP TABLE IF EXISTS `cc_banque_cartes`;
DROP TABLE IF EXISTS `cc_banque_comptes`;
DROP TABLE IF EXISTS `cc_banque_historique`;
DROP TABLE IF EXISTS `cc_banque_transactions`;
DROP TABLE IF EXISTS `cc_boutiques_gerants`;
DROP TABLE IF EXISTS `cc_boutiques_historiques`;
DROP TABLE IF EXISTS `cc_buglog`;
DROP TABLE IF EXISTS `cc_caract`;
DROP TABLE IF EXISTS `cc_caract_incompatible`;
DROP TABLE IF EXISTS `cc_casino`;
DROP TABLE IF EXISTS `cc_competence`;
DROP TABLE IF EXISTS `cc_competence_stat`;
DROP TABLE IF EXISTS `cc_he`;
DROP TABLE IF EXISTS `cc_he_description`;
DROP TABLE IF EXISTS `cc_he_fromto`;
DROP TABLE IF EXISTS `cc_item_db`;
DROP TABLE IF EXISTS `cc_item_db_armemunition`;
DROP TABLE IF EXISTS `cc_item_inv`;
DROP TABLE IF EXISTS `cc_item_menu`;
DROP TABLE IF EXISTS `cc_lieu`;
DROP TABLE IF EXISTS `cc_lieu_ban`;
DROP TABLE IF EXISTS `cc_lieu_casier`;
DROP TABLE IF EXISTS `cc_lieu_distributeur`;
DROP TABLE IF EXISTS `cc_lieu_etude`;
DROP TABLE IF EXISTS `cc_lieu_lien`;
DROP TABLE IF EXISTS `cc_lieu_livre`;
DROP TABLE IF EXISTS `cc_lieu_medias`;
DROP TABLE IF EXISTS `cc_lieu_menu`;
DROP TABLE IF EXISTS `cc_lieu_tenirporte`;
DROP TABLE IF EXISTS `cc_log_conn`;
DROP TABLE IF EXISTS `cc_log_mp`;
DROP TABLE IF EXISTS `cc_log_persomort`;
DROP TABLE IF EXISTS `cc_log_persosuppr`;
DROP TABLE IF EXISTS `cc_log_telephone`;
DROP TABLE IF EXISTS `cc_mairie_question`;
DROP TABLE IF EXISTS `cc_mairie_question_reponse`;
DROP TABLE IF EXISTS `cc_media`;
DROP TABLE IF EXISTS `cc_mj`;
DROP TABLE IF EXISTS `cc_mj_he`;
DROP TABLE IF EXISTS `cc_perso`;
DROP TABLE IF EXISTS `cc_perso_caract`;
DROP TABLE IF EXISTS `cc_perso_competence`;
DROP TABLE IF EXISTS `cc_perso_connu`;
DROP TABLE IF EXISTS `cc_perso_fouille`;
DROP TABLE IF EXISTS `cc_perso_menotte`;
DROP TABLE IF EXISTS `cc_perso_stat`;
DROP TABLE IF EXISTS `cc_ppa`;
DROP TABLE IF EXISTS `cc_ppa_reponses`;
DROP TABLE IF EXISTS `cc_producteur`;
DROP TABLE IF EXISTS `cc_producteur_inv`;
DROP TABLE IF EXISTS `cc_session`;
DROP TABLE IF EXISTS `cc_sitesweb`;
DROP TABLE IF EXISTS `cc_sitesweb_acces`;
DROP TABLE IF EXISTS `cc_sitesweb_pages`;
DROP TABLE IF EXISTS `cc_sitesweb_pages_acces`;
DROP TABLE IF EXISTS `cc_stat`;

SET FOREIGN_KEY_CHECKS=1;

-- La BD devrait maintenant être vide, on peut la recréer.


--
-- Table structure for table `cc_account`
--

CREATE TABLE `cc_account` (
  `id` mediumint(5) UNSIGNED NOT NULL,
  `idcookie` varchar(50) NOT NULL DEFAULT '',
  `user` varchar(25) NOT NULL,
  `pass` varchar(25) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sexe` enum('m','f') NOT NULL,
  `date_inscr` int(10) NOT NULL DEFAULT '0',
  `pub` varchar(10) NOT NULL DEFAULT '',
  `pub_detail` varchar(50) NOT NULL DEFAULT '',
  `remise` int(10) NOT NULL DEFAULT '0',
  `remise_tag` varchar(25) DEFAULT NULL,
  `last_conn` int(10) NOT NULL DEFAULT '0',
  `skin` varchar(15) NOT NULL DEFAULT 'dark_blue',
  `skin_localpath` varchar(200) NOT NULL DEFAULT '',
  `heitems` int(3) NOT NULL DEFAULT '10',
  `bloque` enum('0','1') NOT NULL DEFAULT '0',
  `code_validation` varchar(15) DEFAULT NULL,
  `mp` int(1) NOT NULL DEFAULT '0',
  `mp_expiration` int(10) NOT NULL DEFAULT '0',
  `auth_doublons` enum('0','1') NOT NULL DEFAULT '0',
  `auth_creation_perso` tinyint(1) NOT NULL DEFAULT '1',
  `log_login` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_banque`
--

CREATE TABLE `cc_banque` (
  `banque_id` int(12) NOT NULL,
  `banque_lieu` varchar(100) NOT NULL,
  `banque_no` int(4) NOT NULL DEFAULT '0',
  `banque_nom` varchar(50) NOT NULL,
  `banque_retrait` smallint(1) NOT NULL DEFAULT '1',
  `banque_frais_ouverture` char(3) NOT NULL DEFAULT '50',
  `banque_telephone` smallint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_banque_cartes`
--


CREATE TABLE `cc_banque_cartes` (
  `carte_id` int(12) NOT NULL COMMENT 'nocarte',
  `carte_banque` varchar(4) NOT NULL,
  `carte_compte` varchar(14) NOT NULL,
  `carte_nom` varchar(25) NOT NULL,
  `carte_nip` int(5) NOT NULL DEFAULT '0',
  `carte_valid` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_banque_comptes`
--

CREATE TABLE `cc_banque_comptes` (
  `compte_id` int(12) NOT NULL,
  `compte_idperso` int(12) NOT NULL DEFAULT '0',
  `compte_nom` varchar(50) NOT NULL,
  `compte_banque` int(4) NOT NULL DEFAULT '0',
  `compte_compte` varchar(14) NOT NULL,
  `compte_cash` int(12) NOT NULL DEFAULT '0',
  `compte_nip` int(5) NOT NULL DEFAULT '0',
  `compte_auth_auto_transaction` smallint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_banque_historique`
--


CREATE TABLE `cc_banque_historique` (
  `id` int(11) NOT NULL,
  `compte` varchar(19) NOT NULL,
  `date` varchar(10) NOT NULL,
  `compte2` varchar(19) NOT NULL,
  `code` varchar(20) NOT NULL,
  `retrait` int(12) NOT NULL DEFAULT '0',
  `depot` int(12) NOT NULL DEFAULT '0',
  `solde` int(12) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_banque_transactions`
--


CREATE TABLE `cc_banque_transactions` (
  `transaction_id` int(12) NOT NULL,
  `transaction_compte_from` int(12) NOT NULL,
  `transaction_compte_to` int(12) NOT NULL,
  `transaction_valeur` int(12) NOT NULL,
  `transaction_description` varchar(50) NOT NULL,
  `transaction_date` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_boutiques_gerants`
--

CREATE TABLE `cc_boutiques_gerants` (
  `persoid` int(12) NOT NULL,
  `boutiqueid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_boutiques_historiques`
--


CREATE TABLE `cc_boutiques_historiques` (
  `id` int(12) NOT NULL,
  `boutiqueid` int(12) NOT NULL,
  `date` int(10) NOT NULL,
  `transactiontype` varchar(10) NOT NULL COMMENT 'ACH, VEN, DEP, RET <=> achat, vente, depot, retrait',
  `itemlist` text,
  `marchandage` int(1) DEFAULT NULL,
  `moyenpaiement` int(1) DEFAULT NULL COMMENT 'CB = 1, comptant = 0',
  `prixtotal` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_buglog`
--

CREATE TABLE `cc_buglog` (
  `id` int(11) NOT NULL,
  `date` int(10) NOT NULL DEFAULT '0',
  `ip` varchar(100) NOT NULL,
  `vardump` longtext NOT NULL,
  `msg` text NOT NULL,
  `file` text NOT NULL,
  `line` int(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_caract`
--


CREATE TABLE `cc_caract` (
  `id` smallint(2) UNSIGNED NOT NULL,
  `catid` int(12) NOT NULL COMMENT '0 = categorie seulement',
  `type` enum('system','custom') NOT NULL,
  `nom` tinytext NOT NULL,
  `desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_caract_incompatible`
--

CREATE TABLE `cc_caract_incompatible` (
  `id1` smallint(2) UNSIGNED NOT NULL,
  `id2` smallint(2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_casino`
--

CREATE TABLE `cc_casino` (
  `casino_id` int(12) NOT NULL,
  `casino_lieu` varchar(100) NOT NULL,
  `casino_nom` varchar(50) NOT NULL,
  `casino_cash` int(12) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_competence`
--

CREATE TABLE `cc_competence` (
  `id` smallint(2) UNSIGNED NOT NULL,
  `abbr` varchar(4) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `efface` enum('0','1') NOT NULL COMMENT 'Si la compÃ©tence peut-Ãªtre effacÃ©e par le paneau d''administration',
  `inscription` enum('0','1') NOT NULL COMMENT 'Si la compÃ©tence apparaitra lors de l''inscription'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_competence_stat`
--


CREATE TABLE `cc_competence_stat` (
  `compid` smallint(2) UNSIGNED NOT NULL,
  `statid` smallint(2) UNSIGNED NOT NULL,
  `stat_multi` tinyint(1) NOT NULL COMMENT 'multiplicateur, utile pour faire ARMB = 1xint+2xagi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `cc_he`
--

CREATE TABLE `cc_he` (
  `id` int(12) UNSIGNED NOT NULL,
  `date` int(10) UNSIGNED NOT NULL DEFAULT '1107366894',
  `type` varchar(20) NOT NULL,
  `msg` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_he_description`
--

CREATE TABLE `cc_he_description` (
  `id` int(12) NOT NULL,
  `description` text NOT NULL,
  `msg_who_use` int(12) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_he_fromto`
--


CREATE TABLE `cc_he_fromto` (
  `msgid` int(12) UNSIGNED NOT NULL DEFAULT '0',
  `fromto` enum('from','to') NOT NULL DEFAULT 'from',
  `persoid` int(12) NOT NULL DEFAULT '0',
  `lieuid` mediumint(5) UNSIGNED NOT NULL DEFAULT '0',
  `masque` tinyint(1) NOT NULL DEFAULT '0',
  `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=non, 1=oui, 2=uniquement si moi-mÃªme',
  `id_description` int(12) NOT NULL DEFAULT '0',
  `name_complement` varchar(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_item_db`
--

CREATE TABLE `cc_item_db` (
  `db_id` mediumint(8) UNSIGNED NOT NULL,
  `db_type` enum('arme','autre','badge','cartebanque','cartememoire','clef','defense','drogue','livre','munition','nourriture','ordinateur','sac','talkiewalkie','telephone','trousse','media') NOT NULL DEFAULT 'autre',
  `db_soustype` enum('aucun','arme_feu','arme_blanche','arme_lancee','arme_lourde','arme_paralysante','arme_explosif','def_tete','def_torse','def_bras','def_main','def_jambe','def_pied','drogue_drogue','drogue_substance','drogue_antirejet','drogue_autre') NOT NULL DEFAULT 'aucun',
  `db_regrouper` enum('0','1') NOT NULL DEFAULT '0',
  `db_nom` varchar(150) NOT NULL DEFAULT '' COMMENT 'Livre: titre;',
  `db_desc` text NOT NULL,
  `db_valeur` int(12) NOT NULL DEFAULT '0',
  `db_img` varchar(150) NOT NULL DEFAULT 'SYS_none.gif',
  `db_pr` smallint(3) NOT NULL DEFAULT '0',
  `db_pn` smallint(3) DEFAULT NULL,
  `db_force` smallint(3) DEFAULT NULL COMMENT 'PN',
  `db_portee` enum('TC','C','M','L','TL') DEFAULT NULL,
  `db_tir_par_tour` smallint(1) DEFAULT NULL,
  `db_fiabilite` smallint(3) DEFAULT NULL,
  `db_precision` smallint(3) DEFAULT NULL,
  `db_capacite` int(11) DEFAULT NULL,
  `db_pass` varchar(20) DEFAULT NULL COMMENT 'Livre: Auteur',
  `db_forumaccess` int(12) DEFAULT NULL,
  `db_masque` enum('0','1') DEFAULT NULL,
  `db_seuilresistance` smallint(2) DEFAULT NULL,
  `db_resistance` smallint(3) DEFAULT NULL,
  `db_duree` smallint(3) DEFAULT NULL,
  `db_shock_pa` smallint(3) DEFAULT NULL,
  `db_shock_pv` smallint(3) DEFAULT NULL,
  `db_boost_pa` smallint(3) DEFAULT NULL,
  `db_boost_pv` smallint(3) DEFAULT NULL,
  `db_perc_stat_agi` smallint(3) DEFAULT NULL,
  `db_perc_stat_dex` smallint(3) DEFAULT NULL,
  `db_perc_stat_per` smallint(3) DEFAULT NULL,
  `db_perc_stat_for` smallint(3) DEFAULT NULL,
  `db_perc_stat_int` smallint(3) DEFAULT NULL,
  `db_internet` enum('0','1') DEFAULT NULL,
  `db_mcread` enum('0','1') DEFAULT NULL,
  `db_mcwrite` enum('0','1') DEFAULT NULL COMMENT 'CM: 0 -> non editable',
  `db_memoire` mediumint(7) DEFAULT NULL,
  `db_afficheur` enum('0','1','2') DEFAULT NULL,
  `db_anonyme` enum('0','1') DEFAULT NULL COMMENT 'Cryptage radio',
  `db_param` longtext COMMENT 'Livre: Texte du livre ; CM: Message par defaut sur la carte et non modifiable',
  `db_notemj` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_item_db_armemunition`
--

CREATE TABLE `cc_item_db_armemunition` (
  `id` int(8) NOT NULL,
  `db_armeid` int(8) NOT NULL DEFAULT '0',
  `db_munitionid` int(8) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quelle munition utilise une arme?';

-- --------------------------------------------------------

--
-- Table structure for table `cc_item_inv`
--

CREATE TABLE `cc_item_inv` (
  `inv_id` int(12) UNSIGNED NOT NULL,
  `inv_dbid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `inv_persoid` int(12) DEFAULT NULL,
  `inv_equip` enum('0','1') DEFAULT NULL,
  `inv_lieutech` varchar(150) DEFAULT NULL,
  `inv_boutiquelieutech` varchar(150) DEFAULT NULL,
  `inv_itemid` int(12) DEFAULT NULL,
  `inv_idcasier` int(11) DEFAULT NULL,
  `inv_desc` text,
  `inv_img` varchar(150) DEFAULT NULL,
  `inv_qte` smallint(7) UNSIGNED NOT NULL DEFAULT '1',
  `inv_munition` mediumint(5) DEFAULT NULL,
  `inv_resistance` mediumint(3) DEFAULT NULL,
  `inv_duree` smallint(3) DEFAULT NULL,
  `inv_shock_pa` smallint(3) DEFAULT NULL,
  `inv_shock_pv` smallint(3) DEFAULT NULL,
  `inv_boost_pa` smallint(3) DEFAULT NULL,
  `inv_boost_pv` smallint(3) DEFAULT NULL,
  `inv_perc_stat_agi` smallint(3) DEFAULT NULL,
  `inv_perc_stat_dex` smallint(3) DEFAULT NULL,
  `inv_perc_stat_per` smallint(3) DEFAULT NULL,
  `inv_perc_stat_for` smallint(3) DEFAULT NULL,
  `inv_perc_stat_int` smallint(3) DEFAULT NULL,
  `inv_remiseleft` smallint(2) DEFAULT NULL,
  `inv_pn` smallint(3) DEFAULT NULL,
  `inv_notel` varchar(8) DEFAULT NULL COMMENT 'pour les radio: Frequence sur laquelle est réglé la radio',
  `inv_memoiretext` text COMMENT 'Clé: Nom',
  `inv_nobanque` varchar(4) DEFAULT NULL,
  `inv_nocompte` varchar(14) DEFAULT NULL,
  `inv_nocarte` mediumint(7) DEFAULT NULL,
  `inv_nip` int(5) DEFAULT NULL COMMENT 'pour les radio: clef de cryptage entrée',
  `inv_boutiquePrixVente` float DEFAULT NULL,
  `inv_boutiquePrixAchat` float DEFAULT NULL,
  `inv_param` text COMMENT 'Clé: Pass',
  `inv_extradesc` text NOT NULL,
  `inv_notemj` text NOT NULL,
  `inv_cacheno` int(11) DEFAULT NULL,
  `inv_cachetaux` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_item_menu`
--


CREATE TABLE `cc_item_menu` (
  `id` int(12) NOT NULL,
  `item_dbid` int(12) NOT NULL DEFAULT '0',
  `caption` varchar(30) NOT NULL,
  `url` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu`
--


CREATE TABLE `cc_lieu` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `nom_technique` varchar(150) NOT NULL,
  `nom_affiche` tinytext NOT NULL,
  `description` text NOT NULL,
  `dimension` enum('TC','C','M','L','TL') NOT NULL DEFAULT 'M',
  `image` varchar(30) NOT NULL,
  `boutique_cash` float DEFAULT NULL,
  `boutique_compte` varchar(19) DEFAULT NULL,
  `boutique_vol` smallint(1) NOT NULL,
  `coeff_soin` smallint(6) NOT NULL DEFAULT '0',
  `qteMateriel` int(11) NOT NULL DEFAULT '0',
  `notemj` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_ban`
--


CREATE TABLE `cc_lieu_ban` (
  `id` int(12) UNSIGNED NOT NULL,
  `persoid` int(5) UNSIGNED NOT NULL DEFAULT '0',
  `lieu` varchar(150) NOT NULL,
  `remiseleft` smallint(1) NOT NULL DEFAULT '9'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_casier`
--


CREATE TABLE `cc_lieu_casier` (
  `id_casier` int(12) UNSIGNED NOT NULL,
  `nom_casier` varchar(150) DEFAULT NULL,
  `lieuId` mediumint(8) UNSIGNED DEFAULT NULL,
  `capacite_casier` int(11) DEFAULT NULL,
  `protection_casier` enum('pass','clef') DEFAULT NULL,
  `resistance_casier` smallint(6) DEFAULT NULL,
  `pass_casier` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_distributeur`
--


CREATE TABLE `cc_lieu_distributeur` (
  `id` int(12) UNSIGNED NOT NULL,
  `lieuId` mediumint(8) UNSIGNED NOT NULL,
  `producteurId` smallint(2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_etude`
--

CREATE TABLE `cc_lieu_etude` (
  `lieuId` mediumint(8) UNSIGNED NOT NULL,
  `comp` enum('ACRO','ARMB','ARMC','ARMF','ARML','ARMU','ARTI','ATHL','CHIM','CHRG','CROC','CRYP','CUIS','CYBR','DRSG','ELEC','ENSG','ESQV','EXPL','FORG','FRTV','GENE','HCKG','HRDW','LNCR','MECA','MRCH','PCKP','PLTG','PROG','PSYC','SCRS','TOXI') NOT NULL,
  `cout_cash` int(8) NOT NULL DEFAULT '0' COMMENT 'Cout Cash / 1 tour',
  `cout_pa` int(8) NOT NULL DEFAULT '3' COMMENT 'Cout PA / 1 tour',
  `qualite_lieu` tinyint(3) NOT NULL DEFAULT '70' COMMENT 'Ambience du lieu propice à l''étude'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_lien`
--


CREATE TABLE `cc_lieu_lien` (
  `id` int(12) UNSIGNED NOT NULL,
  `from` varchar(150) NOT NULL,
  `to` varchar(150) NOT NULL,
  `icon` varchar(25) DEFAULT NULL,
  `pa` smallint(2) NOT NULL DEFAULT '0',
  `cout` int(12) NOT NULL DEFAULT '0',
  `protection` enum('0','pass','clef') NOT NULL DEFAULT '0',
  `pass` varchar(15) NOT NULL,
  `bloque` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_livre`
--


CREATE TABLE `cc_lieu_livre` (
  `lieuId` mediumint(8) UNSIGNED NOT NULL,
  `itemDbId` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_medias`
--


CREATE TABLE `cc_lieu_medias` (
  `id` int(12) NOT NULL,
  `lieuid` mediumint(8) UNSIGNED NOT NULL,
  `nom` varchar(50) NOT NULL,
  `mediaType` enum('radio','tele','tous') NOT NULL,
  `canalId` int(12) NOT NULL,
  `interactionType` smallint(1) NOT NULL COMMENT 'reception = 0, emission = 1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_menu`
--

CREATE TABLE `cc_lieu_menu` (
  `id` int(12) UNSIGNED NOT NULL,
  `lieutech` varchar(150) NOT NULL,
  `caption` varchar(30) NOT NULL,
  `url` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_lieu_tenirporte`
--

CREATE TABLE `cc_lieu_tenirporte` (
  `id` int(12) UNSIGNED NOT NULL,
  `de` varchar(150) NOT NULL,
  `vers` varchar(150) NOT NULL,
  `qui` int(5) UNSIGNED NOT NULL,
  `expiration` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_log_conn`
--

CREATE TABLE `cc_log_conn` (
  `id` int(12) NOT NULL,
  `date` datetime NOT NULL,
  `timestamp` int(10) NOT NULL,
  `user` varchar(25) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host` varchar(50) NOT NULL,
  `cookie` varchar(50) NOT NULL,
  `client` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_log_mp`
--


CREATE TABLE `cc_log_mp` (
  `id` int(12) NOT NULL,
  `userId` int(12) NOT NULL,
  `date` int(12) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `item` varchar(15) NOT NULL,
  `statusPP` tinytext NOT NULL,
  `statusCC` tinytext NOT NULL,
  `post` blob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_log_persomort`
--

CREATE TABLE `cc_log_persomort` (
  `id` int(12) NOT NULL,
  `perso` varchar(25) NOT NULL,
  `persoId` int(12) NOT NULL,
  `timestamp` int(15) NOT NULL,
  `from` varchar(25) NOT NULL,
  `fromId` int(12) NOT NULL,
  `action` varchar(25) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_log_persosuppr`
--

CREATE TABLE `cc_log_persosuppr` (
  `id` int(11) NOT NULL,
  `timestamp` bigint(30) NOT NULL,
  `perso` varchar(30) NOT NULL,
  `mj` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_log_telephone`
--

CREATE TABLE `cc_log_telephone` (
  `id_he_exp` int(12) NOT NULL DEFAULT '0',
  `id_he_dest` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  `from_tel` varchar(8) NOT NULL,
  `from_persoid` int(12) NOT NULL DEFAULT '0',
  `to_tel` varchar(8) NOT NULL,
  `to_persoid` int(12) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_mairie_question`
--

CREATE TABLE `cc_mairie_question` (
  `id` int(12) NOT NULL,
  `section` smallint(2) NOT NULL,
  `question` text NOT NULL,
  `reponse_tech` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_mairie_question_reponse`
--

CREATE TABLE `cc_mairie_question_reponse` (
  `questionId` int(12) NOT NULL,
  `reponse_tech` varchar(1) NOT NULL,
  `reponse` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_media`
--

CREATE TABLE `cc_media` (
  `id` int(12) NOT NULL,
  `mediaType` enum('radio','tele') NOT NULL,
  `canalId` int(12) NOT NULL,
  `date` int(10) UNSIGNED NOT NULL,
  `titre` text NOT NULL,
  `message` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_mj`
--

CREATE TABLE `cc_mj` (
  `id` int(12) NOT NULL,
  `userId` int(12) NOT NULL,
  `nom` varchar(25) NOT NULL,
  `poste` varchar(100) NOT NULL,
  `email_prefix` varchar(20) NOT NULL,
  `present` smallint(1) NOT NULL DEFAULT '0',
  `ax_ppa` smallint(1) NOT NULL DEFAULT '0',
  `ax_ej` smallint(1) NOT NULL DEFAULT '0',
  `ax_hj` smallint(1) NOT NULL DEFAULT '0',
  `ax_admin` smallint(1) NOT NULL DEFAULT '0',
  `last_connection` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ax_dev` smallint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_mj_he`
--

CREATE TABLE `cc_mj_he` (
  `id` int(11) NOT NULL,
  `msg` text NOT NULL,
  `mjId` int(12) NOT NULL DEFAULT '0',
  `concernant` varchar(25) NOT NULL COMMENT 'Nom du personnage ou du compte, selon',
  `concernant_type` enum('system','perso','perso','lieu','item','mj') NOT NULL,
  `date` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso`
--

CREATE TABLE `cc_perso` (
  `id` int(5) UNSIGNED NOT NULL,
  `userId` mediumint(5) UNSIGNED NOT NULL,
  `nom` varchar(25) NOT NULL,
  `sexe` enum('m','f') NOT NULL DEFAULT 'm',
  `age` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `taille` varchar(4) NOT NULL,
  `yeux` tinytext NOT NULL,
  `ethnie` varchar(20) NOT NULL,
  `cheveux` tinytext NOT NULL,
  `poids` mediumint(3) UNSIGNED NOT NULL DEFAULT '0',
  `playertype` enum('humain','animal','robot','objet','training') NOT NULL DEFAULT 'humain',
  `prmax` smallint(3) UNSIGNED NOT NULL DEFAULT '10',
  `pa` smallint(3) NOT NULL DEFAULT '50',
  `pamax` smallint(3) UNSIGNED NOT NULL DEFAULT '99',
  `pv` smallint(3) NOT NULL DEFAULT '99',
  `pvmax` smallint(3) UNSIGNED NOT NULL DEFAULT '99',
  `pn` smallint(2) NOT NULL DEFAULT '99',
  `lng1` varchar(2) NOT NULL,
  `lng1_lvl` varchar(2) NOT NULL,
  `lng2` varchar(2) NOT NULL,
  `lng2_lvl` varchar(2) NOT NULL,
  `vies` smallint(1) NOT NULL DEFAULT '1',
  `cash` mediumint(12) NOT NULL DEFAULT '0',
  `lieu` varchar(150) NOT NULL,
  `current_action` text NOT NULL,
  `description` text NOT NULL,
  `background` text NOT NULL,
  `note_mj` text NOT NULL,
  `imgurl` varchar(200) NOT NULL,
  `esquive` enum('0','1') NOT NULL DEFAULT '1',
  `reaction` enum('rien','riposte','fuir') NOT NULL DEFAULT 'riposte',
  `soin` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `menotte` int(12) DEFAULT NULL COMMENT 'Si menotte, inv_id des menottes',
  `bloque` enum('0','1') NOT NULL DEFAULT '0',
  `inscription_valide` enum('0','1','mod') NOT NULL DEFAULT '0' COMMENT '0=Non, 1=Oui (Jouable), mod = Une modification doit être faite.',
  `visa_perm` int(10) NOT NULL DEFAULT '0' COMMENT '0=Non; 1=Oui; autre=dernier exam',
  `heQte` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso_caract`
--

CREATE TABLE `cc_perso_caract` (
  `id` int(12) UNSIGNED NOT NULL,
  `persoid` int(5) UNSIGNED NOT NULL,
  `caractid` smallint(2) UNSIGNED NOT NULL,
  `desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso_competence`
--

CREATE TABLE `cc_perso_competence` (
  `persoid` int(5) UNSIGNED NOT NULL,
  `compid` smallint(2) UNSIGNED NOT NULL,
  `xp` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso_connu`
--

CREATE TABLE `cc_perso_connu` (
  `id` int(12) UNSIGNED NOT NULL,
  `persoid` int(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Id du perso qui connait une personne',
  `nomid` int(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'id du personne connue du perso',
  `nom` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso_fouille`
--

CREATE TABLE `cc_perso_fouille` (
  `fromid` int(5) UNSIGNED NOT NULL COMMENT 'fouille par',
  `toid` int(5) UNSIGNED NOT NULL COMMENT 'sera fouillé',
  `expiration` int(10) NOT NULL,
  `reponse` smallint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso_menotte`
--

CREATE TABLE `cc_perso_menotte` (
  `inv_id` int(12) UNSIGNED NOT NULL COMMENT 'menotté par',
  `to_id` int(5) UNSIGNED NOT NULL COMMENT 'sera menotté',
  `expiration` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_perso_stat`
--


CREATE TABLE `cc_perso_stat` (
  `persoid` int(5) UNSIGNED NOT NULL,
  `statid` smallint(2) UNSIGNED NOT NULL,
  `xp` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `cc_ppa`
--

CREATE TABLE `cc_ppa` (
  `id` int(12) NOT NULL,
  `persoid` int(12) NOT NULL,
  `type` varchar(10) NOT NULL,
  `date` int(10) NOT NULL,
  `mjid` int(12) NOT NULL DEFAULT '0' COMMENT 'Si attribution == mj ID, si général == 0',
  `titre` tinytext NOT NULL,
  `msg` text NOT NULL,
  `lieu` varchar(150) NOT NULL,
  `pa` smallint(3) NOT NULL,
  `paMax` smallint(3) NOT NULL,
  `pv` smallint(3) NOT NULL,
  `pvMax` smallint(3) NOT NULL,
  `notemj` text NOT NULL,
  `statut` enum('ouvert','ferme') NOT NULL DEFAULT 'ouvert'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_ppa_reponses`
--

CREATE TABLE `cc_ppa_reponses` (
  `id` int(12) NOT NULL,
  `sujetid` int(12) NOT NULL,
  `mjid` int(12) NOT NULL COMMENT 'Réponse du perso = id 0',
  `date` int(10) NOT NULL,
  `msg` text NOT NULL,
  `notemj` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_producteur`
--

CREATE TABLE `cc_producteur` (
  `id` smallint(2) UNSIGNED NOT NULL,
  `lieuId` mediumint(8) UNSIGNED NOT NULL,
  `cash` int(11) NOT NULL,
  `pa_cash_ratio` float NOT NULL COMMENT '1pa donne Xcash',
  `total_pa` int(12) NOT NULL,
  `pa_needed` int(12) NOT NULL COMMENT 'pt requis pour lancer une production'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_producteur_inv`
--

CREATE TABLE `cc_producteur_inv` (
  `id` int(12) UNSIGNED NOT NULL,
  `producteurId` smallint(2) UNSIGNED NOT NULL,
  `itemDbId` mediumint(8) UNSIGNED NOT NULL,
  `qte` int(12) NOT NULL,
  `pa_needed` int(12) NOT NULL COMMENT 'pa requis pour produire 1 item',
  `prix` int(12) NOT NULL,
  `pack` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_session`
--

CREATE TABLE `cc_session` (
  `userId` int(12) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `idcookie` varchar(50) NOT NULL DEFAULT '',
  `expiration` varchar(15) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_sitesweb`
--

CREATE TABLE `cc_sitesweb` (
  `id` int(12) NOT NULL,
  `url` varchar(250) NOT NULL,
  `titre` varchar(250) NOT NULL,
  `acces` enum('pub','priv') NOT NULL,
  `first_page` int(12) NOT NULL COMMENT 'Afficher directement une page en plus de l''index'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_sitesweb_acces`
--

CREATE TABLE `cc_sitesweb_acces` (
  `id` int(12) NOT NULL,
  `site_id` int(12) NOT NULL,
  `user` varchar(20) NOT NULL,
  `pass` varchar(20) NOT NULL,
  `accede` enum('0','1') NOT NULL DEFAULT '0',
  `poste` enum('0','1') NOT NULL DEFAULT '0',
  `modifier` enum('0','1') NOT NULL DEFAULT '0',
  `admin` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_sitesweb_pages`
--

CREATE TABLE `cc_sitesweb_pages` (
  `id` int(12) NOT NULL,
  `site_id` int(12) NOT NULL,
  `msg_parentid` int(12) NOT NULL DEFAULT '0',
  `titre` varchar(250) NOT NULL,
  `content` longtext NOT NULL,
  `acces` enum('pub','priv') NOT NULL,
  `showIndex` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_sitesweb_pages_acces`
--

CREATE TABLE `cc_sitesweb_pages_acces` (
  `id` int(12) NOT NULL,
  `page_id` int(12) NOT NULL,
  `user_id` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cc_stat`
--

CREATE TABLE `cc_stat` (
  `id` smallint(2) UNSIGNED NOT NULL,
  `abbr` varchar(3) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




--
-- Indexes for dumped tables
--

--
-- Indexes for table `cc_account`
--
ALTER TABLE `cc_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`,`email`),
  ADD KEY `remise_tag` (`remise_tag`);

--
-- Indexes for table `cc_banque`
--
ALTER TABLE `cc_banque`
  ADD PRIMARY KEY (`banque_id`),
  ADD KEY `no` (`banque_no`),
  ADD KEY `lieu` (`banque_lieu`);

--
-- Indexes for table `cc_banque_cartes`
--
ALTER TABLE `cc_banque_cartes`
  ADD PRIMARY KEY (`carte_id`);

--
-- Indexes for table `cc_banque_comptes`
--
ALTER TABLE `cc_banque_comptes`
  ADD PRIMARY KEY (`compte_id`),
  ADD KEY `compte_compte` (`compte_compte`);

--
-- Indexes for table `cc_banque_historique`
--
ALTER TABLE `cc_banque_historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account` (`compte`);

--
-- Indexes for table `cc_banque_transactions`
--
ALTER TABLE `cc_banque_transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `cc_boutiques_gerants`
--
ALTER TABLE `cc_boutiques_gerants`
  ADD PRIMARY KEY (`boutiqueid`,`persoid`);

--
-- Indexes for table `cc_boutiques_historiques`
--
ALTER TABLE `cc_boutiques_historiques`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_buglog`
--
ALTER TABLE `cc_buglog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `datetime` (`date`,`ip`);
ALTER TABLE `cc_buglog` ADD FULLTEXT KEY `msg` (`msg`);

--
-- Indexes for table `cc_caract`
--
ALTER TABLE `cc_caract`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catId` (`catid`);

--
-- Indexes for table `cc_caract_incompatible`
--
ALTER TABLE `cc_caract_incompatible`
  ADD PRIMARY KEY (`id1`,`id2`),
  ADD KEY `id2` (`id2`);

--
-- Indexes for table `cc_casino`
--
ALTER TABLE `cc_casino`
  ADD PRIMARY KEY (`casino_id`),
  ADD KEY `lieu` (`casino_lieu`);

--
-- Indexes for table `cc_competence`
--
ALTER TABLE `cc_competence`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `abbr` (`abbr`);

--
-- Indexes for table `cc_competence_stat`
--
ALTER TABLE `cc_competence_stat`
  ADD PRIMARY KEY (`compid`,`statid`),
  ADD KEY `statid` (`statid`);

--
-- Indexes for table `cc_he`
--
ALTER TABLE `cc_he`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `cc_he_description`
--
ALTER TABLE `cc_he_description`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_he_fromto`
--
ALTER TABLE `cc_he_fromto`
  ADD PRIMARY KEY (`persoid`,`show`,`msgid`,`fromto`),
  ADD KEY `msgid` (`msgid`);

--
-- Indexes for table `cc_item_db`
--
ALTER TABLE `cc_item_db`
  ADD PRIMARY KEY (`db_id`),
  ADD KEY `db_type` (`db_type`,`db_soustype`,`db_nom`);

--
-- Indexes for table `cc_item_db_armemunition`
--
ALTER TABLE `cc_item_db_armemunition`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_armeid` (`db_armeid`,`db_munitionid`);

--
-- Indexes for table `cc_item_inv`
--
ALTER TABLE `cc_item_inv`
  ADD PRIMARY KEY (`inv_id`),
  ADD KEY `inv_dbid` (`inv_dbid`),
  ADD KEY `inv_persoid` (`inv_persoid`),
  ADD KEY `inv_lieutech` (`inv_lieutech`),
  ADD KEY `inv_boutiquelieutech` (`inv_boutiquelieutech`),
  ADD KEY `inv_itemid` (`inv_itemid`),
  ADD KEY `inv_idcasier` (`inv_idcasier`);

--
-- Indexes for table `cc_item_menu`
--
ALTER TABLE `cc_item_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lieutech` (`item_dbid`);

--
-- Indexes for table `cc_lieu`
--
ALTER TABLE `cc_lieu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nom_technique` (`nom_technique`);

--
-- Indexes for table `cc_lieu_ban`
--
ALTER TABLE `cc_lieu_ban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `persoid` (`persoid`);

--
-- Indexes for table `cc_lieu_casier`
--
ALTER TABLE `cc_lieu_casier`
  ADD PRIMARY KEY (`id_casier`),
  ADD KEY `lieuId` (`lieuId`);

--
-- Indexes for table `cc_lieu_distributeur`
--
ALTER TABLE `cc_lieu_distributeur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lieuId` (`lieuId`,`producteurId`),
  ADD KEY `producteurId` (`producteurId`);

--
-- Indexes for table `cc_lieu_etude`
--
ALTER TABLE `cc_lieu_etude`
  ADD PRIMARY KEY (`lieuId`,`comp`);

--
-- Indexes for table `cc_lieu_lien`
--
ALTER TABLE `cc_lieu_lien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from` (`from`);

--
-- Indexes for table `cc_lieu_livre`
--
ALTER TABLE `cc_lieu_livre`
  ADD PRIMARY KEY (`lieuId`,`itemDbId`),
  ADD KEY `itemDbId` (`itemDbId`);

--
-- Indexes for table `cc_lieu_medias`
--
ALTER TABLE `cc_lieu_medias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_lieu_menu`
--
ALTER TABLE `cc_lieu_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lieutech` (`lieutech`);

--
-- Indexes for table `cc_lieu_tenirporte`
--
ALTER TABLE `cc_lieu_tenirporte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vers` (`vers`,`qui`),
  ADD KEY `qui` (`qui`),
  ADD KEY `expiration` (`expiration`),
  ADD KEY `de` (`de`);

--
-- Indexes for table `cc_log_conn`
--
ALTER TABLE `cc_log_conn`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_log_mp`
--
ALTER TABLE `cc_log_mp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `cc_log_persomort`
--
ALTER TABLE `cc_log_persomort`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `cc_log_persosuppr`
--
ALTER TABLE `cc_log_persosuppr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_log_telephone`
--
ALTER TABLE `cc_log_telephone`
  ADD PRIMARY KEY (`id_he_exp`),
  ADD KEY `from_tel` (`from_tel`);

--
-- Indexes for table `cc_mairie_question`
--
ALTER TABLE `cc_mairie_question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section` (`section`);

--
-- Indexes for table `cc_mairie_question_reponse`
--
ALTER TABLE `cc_mairie_question_reponse`
  ADD PRIMARY KEY (`questionId`,`reponse_tech`);

--
-- Indexes for table `cc_media`
--
ALTER TABLE `cc_media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_mj`
--
ALTER TABLE `cc_mj`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Indexes for table `cc_mj_he`
--
ALTER TABLE `cc_mj_he`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mjId` (`mjId`);

--
-- Indexes for table `cc_perso`
--
ALTER TABLE `cc_perso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `cc_perso_caract`
--
ALTER TABLE `cc_perso_caract`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perso_caract` (`persoid`,`caractid`);

--
-- Indexes for table `cc_perso_competence`
--
ALTER TABLE `cc_perso_competence`
  ADD PRIMARY KEY (`persoid`,`compid`),
  ADD KEY `compid` (`compid`);

--
-- Indexes for table `cc_perso_connu`
--
ALTER TABLE `cc_perso_connu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `persoid` (`persoid`),
  ADD KEY `nomid` (`nomid`);

--
-- Indexes for table `cc_perso_fouille`
--
ALTER TABLE `cc_perso_fouille`
  ADD PRIMARY KEY (`fromid`,`toid`),
  ADD KEY `expiration` (`expiration`),
  ADD KEY `toid` (`toid`);

--
-- Indexes for table `cc_perso_menotte`
--
ALTER TABLE `cc_perso_menotte`
  ADD PRIMARY KEY (`inv_id`,`to_id`),
  ADD KEY `expiration` (`expiration`),
  ADD KEY `to_id` (`to_id`);

--
-- Indexes for table `cc_perso_stat`
--
ALTER TABLE `cc_perso_stat`
  ADD PRIMARY KEY (`persoid`,`statid`),
  ADD KEY `statid` (`statid`);

--
-- Indexes for table `cc_ppa`
--
ALTER TABLE `cc_ppa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parentid` (`mjid`);

--
-- Indexes for table `cc_ppa_reponses`
--
ALTER TABLE `cc_ppa_reponses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sujetid` (`sujetid`,`date`);

--
-- Indexes for table `cc_producteur`
--
ALTER TABLE `cc_producteur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lieuId` (`lieuId`);

--
-- Indexes for table `cc_producteur_inv`
--
ALTER TABLE `cc_producteur_inv`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producteurId_2` (`producteurId`,`itemDbId`),
  ADD KEY `itemDbId` (`itemDbId`);

--
-- Indexes for table `cc_session`
--
ALTER TABLE `cc_session`
  ADD PRIMARY KEY (`idcookie`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `cc_sitesweb`
--
ALTER TABLE `cc_sitesweb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `url` (`url`);

--
-- Indexes for table `cc_sitesweb_acces`
--
ALTER TABLE `cc_sitesweb_acces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`);

--
-- Indexes for table `cc_sitesweb_pages`
--
ALTER TABLE `cc_sitesweb_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `msg_parentid` (`msg_parentid`);

--
-- Indexes for table `cc_sitesweb_pages_acces`
--
ALTER TABLE `cc_sitesweb_pages_acces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`);

--
-- Indexes for table `cc_stat`
--
ALTER TABLE `cc_stat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `abbr` (`abbr`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cc_account`
--
ALTER TABLE `cc_account`
  MODIFY `id` mediumint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_banque`
--
ALTER TABLE `cc_banque`
  MODIFY `banque_id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_banque_cartes`
--
ALTER TABLE `cc_banque_cartes`
  MODIFY `carte_id` int(12) NOT NULL AUTO_INCREMENT COMMENT 'nocarte';
--
-- AUTO_INCREMENT for table `cc_banque_comptes`
--
ALTER TABLE `cc_banque_comptes`
  MODIFY `compte_id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_banque_historique`
--
ALTER TABLE `cc_banque_historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_banque_transactions`
--
ALTER TABLE `cc_banque_transactions`
  MODIFY `transaction_id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_boutiques_historiques`
--
ALTER TABLE `cc_boutiques_historiques`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_buglog`
--
ALTER TABLE `cc_buglog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_caract`
--
ALTER TABLE `cc_caract`
  MODIFY `id` smallint(2) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_casino`
--
ALTER TABLE `cc_casino`
  MODIFY `casino_id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_competence`
--
ALTER TABLE `cc_competence`
  MODIFY `id` smallint(2) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_he`
--
ALTER TABLE `cc_he`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_he_description`
--
ALTER TABLE `cc_he_description`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_item_db`
--
ALTER TABLE `cc_item_db`
  MODIFY `db_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_item_db_armemunition`
--
ALTER TABLE `cc_item_db_armemunition`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_item_inv`
--
ALTER TABLE `cc_item_inv`
  MODIFY `inv_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_item_menu`
--
ALTER TABLE `cc_item_menu`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu`
--
ALTER TABLE `cc_lieu`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_ban`
--
ALTER TABLE `cc_lieu_ban`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_casier`
--
ALTER TABLE `cc_lieu_casier`
  MODIFY `id_casier` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_distributeur`
--
ALTER TABLE `cc_lieu_distributeur`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_lien`
--
ALTER TABLE `cc_lieu_lien`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_medias`
--
ALTER TABLE `cc_lieu_medias`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_menu`
--
ALTER TABLE `cc_lieu_menu`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_lieu_tenirporte`
--
ALTER TABLE `cc_lieu_tenirporte`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_log_conn`
--
ALTER TABLE `cc_log_conn`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_log_mp`
--
ALTER TABLE `cc_log_mp`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_log_persomort`
--
ALTER TABLE `cc_log_persomort`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_log_persosuppr`
--
ALTER TABLE `cc_log_persosuppr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_mairie_question`
--
ALTER TABLE `cc_mairie_question`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_media`
--
ALTER TABLE `cc_media`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_mj`
--
ALTER TABLE `cc_mj`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_mj_he`
--
ALTER TABLE `cc_mj_he`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_perso`
--
ALTER TABLE `cc_perso`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_perso_caract`
--
ALTER TABLE `cc_perso_caract`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_perso_connu`
--
ALTER TABLE `cc_perso_connu`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_ppa`
--
ALTER TABLE `cc_ppa`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_ppa_reponses`
--
ALTER TABLE `cc_ppa_reponses`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_producteur`
--
ALTER TABLE `cc_producteur`
  MODIFY `id` smallint(2) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_producteur_inv`
--
ALTER TABLE `cc_producteur_inv`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_sitesweb`
--
ALTER TABLE `cc_sitesweb`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_sitesweb_acces`
--
ALTER TABLE `cc_sitesweb_acces`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_sitesweb_pages`
--
ALTER TABLE `cc_sitesweb_pages`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_sitesweb_pages_acces`
--
ALTER TABLE `cc_sitesweb_pages_acces`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cc_stat`
--
ALTER TABLE `cc_stat`
  MODIFY `id` smallint(2) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `cc_caract_incompatible`
--
ALTER TABLE `cc_caract_incompatible`
  ADD CONSTRAINT `cc_caract_incompatible_ibfk_1` FOREIGN KEY (`id1`) REFERENCES `cc_caract` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_caract_incompatible_ibfk_2` FOREIGN KEY (`id2`) REFERENCES `cc_caract` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_competence_stat`
--
ALTER TABLE `cc_competence_stat`
  ADD CONSTRAINT `cc_competence_stat_ibfk_1` FOREIGN KEY (`compid`) REFERENCES `cc_competence` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_competence_stat_ibfk_2` FOREIGN KEY (`statid`) REFERENCES `cc_stat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_he_fromto`
--
ALTER TABLE `cc_he_fromto`
  ADD CONSTRAINT `cc_he_fromto_ibfk_1` FOREIGN KEY (`msgid`) REFERENCES `cc_he` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_item_inv`
--
ALTER TABLE `cc_item_inv`
  ADD CONSTRAINT `cc_item_inv_ibfk_1` FOREIGN KEY (`inv_dbid`) REFERENCES `cc_item_db` (`db_id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_lieu_ban`
--
ALTER TABLE `cc_lieu_ban`
  ADD CONSTRAINT `cc_lieu_ban_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_lieu_casier`
--
ALTER TABLE `cc_lieu_casier`
  ADD CONSTRAINT `cc_lieu_casier_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_lieu_distributeur`
--
ALTER TABLE `cc_lieu_distributeur`
  ADD CONSTRAINT `cc_lieu_distributeur_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_lieu_distributeur_ibfk_2` FOREIGN KEY (`producteurId`) REFERENCES `cc_producteur` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_lieu_etude`
--
ALTER TABLE `cc_lieu_etude`
  ADD CONSTRAINT `cc_lieu_etude_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_lieu_livre`
--
ALTER TABLE `cc_lieu_livre`
  ADD CONSTRAINT `cc_lieu_livre_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_lieu_livre_ibfk_2` FOREIGN KEY (`itemDbId`) REFERENCES `cc_item_db` (`db_id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_lieu_tenirporte`
--
ALTER TABLE `cc_lieu_tenirporte`
  ADD CONSTRAINT `cc_lieu_tenirporte_ibfk_1` FOREIGN KEY (`qui`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso`
--
ALTER TABLE `cc_perso`
  ADD CONSTRAINT `cc_perso_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cc_account` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso_caract`
--
ALTER TABLE `cc_perso_caract`
  ADD CONSTRAINT `cc_perso_caract_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso_competence`
--
ALTER TABLE `cc_perso_competence`
  ADD CONSTRAINT `cc_perso_competence_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_competence_ibfk_2` FOREIGN KEY (`compid`) REFERENCES `cc_competence` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso_connu`
--
ALTER TABLE `cc_perso_connu`
  ADD CONSTRAINT `cc_perso_connu_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_connu_ibfk_2` FOREIGN KEY (`nomid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso_fouille`
--
ALTER TABLE `cc_perso_fouille`
  ADD CONSTRAINT `cc_perso_fouille_ibfk_1` FOREIGN KEY (`fromid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_fouille_ibfk_2` FOREIGN KEY (`toid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso_menotte`
--
ALTER TABLE `cc_perso_menotte`
  ADD CONSTRAINT `cc_perso_menotte_ibfk_1` FOREIGN KEY (`inv_id`) REFERENCES `cc_item_inv` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_menotte_ibfk_2` FOREIGN KEY (`to_id`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_perso_stat`
--
ALTER TABLE `cc_perso_stat`
  ADD CONSTRAINT `cc_perso_stat_ibfk_1` FOREIGN KEY (`persoid`) REFERENCES `cc_perso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_perso_stat_ibfk_2` FOREIGN KEY (`statid`) REFERENCES `cc_stat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_producteur`
--
ALTER TABLE `cc_producteur`
  ADD CONSTRAINT `cc_producteur_ibfk_1` FOREIGN KEY (`lieuId`) REFERENCES `cc_lieu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_producteur_inv`
--
ALTER TABLE `cc_producteur_inv`
  ADD CONSTRAINT `cc_producteur_inv_ibfk_1` FOREIGN KEY (`producteurId`) REFERENCES `cc_producteur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cc_producteur_inv_ibfk_2` FOREIGN KEY (`itemDbId`) REFERENCES `cc_item_db` (`db_id`) ON DELETE CASCADE;
  

