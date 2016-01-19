<?php
/**
 * Page principale du panel MJ
 * Page principale du panel MJ
 * @package Mj
 */

class Mj_index
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_POST['StatutMJ']))
			$mj->setPresent(!$mj->isPresent());
		
		$tpl->set('STATUT_MJ', $mj->isPresent());
		
		
		//condition de si il y a eu un changement pour accepter une inscription
		if (isset($_POST['inscr_save']))
			self::doInscription($tpl, $account, $mj);
		

		//condition pour "supprimer" un message dans le HE d'un MJ spécifique
		if(isset($_POST['ppa_suppr_save']))
			self::doDeleteHeSelectedMsg($mj);

		/*
		//condition pour "transférer" un message dans le HE d'un MJ spécifique
			$query = 'SELECT *
						FROM ' . DB_PREFIX . 'mj_he
						WHERE attrib_mj="0"
						ORDER BY id;';
			$result = $db->query($query,__FILE__,__LINE__);
			while ($heMsg = mysql_fetch_assoc($result)){
				if (isset($_POST['ppa_' . $heMsg['id']])){
					if ($_POST['ppa_' . $heMsg['id']] == $heMsg['id']){
						$query = 'UPDATE ' . DB_PREFIX . 'mj_he
							SET attrib_mj=' . $mj->getId() . '
							WHERE id=' . $heMsg['id'] . ';';
						$db->query($query,__FILE__,__LINE__);
						}
					}
				}
		*/
		
		
		
		$tpl->set("PAGE_EJ",$mj->accessEj());
		$tpl->set("PAGE_HJ",$mj->accessHj());
		$tpl->set("PAGE_DEV", $mj->accessDev());
		
		
		
		
		//Trouver les inscriptions en attentes de validation
		$query = 'SELECT COUNT(id) as c'
					. ' FROM ' . DB_PREFIX . 'perso'
					. ' WHERE inscription_valide="0";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('COUNT_INSCR', $arr['c']);
		
		
		//S'il y a une inscription à traiter, l'onglet par défaut est INSCRIPTION, sinon, PPA
		$DEFAULT_TAB = 0;
		if(isset($_GET['tab']))
			$DEFAULT_TAB = (int)$_GET['tab'];
		
		if($DEFAULT_TAB===0)
			$DEFAULT_TAB = ($arr['c']==0) ? 1 : 3;
		
		$tpl->set('DEFAULT_TAB', $DEFAULT_TAB);
		
		
		
		
		
		
		
		
		
		//Trouver les PPA associé au MJ
		$query = 'SELECT COUNT(id) as c'
					. ' FROM ' . DB_PREFIX . 'ppa'
					. ' WHERE	mjid=' . (int)$mj->getId()
						. ' AND statut="ouvert";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		$tpl->set('COUNT_PPA_MJ', $arr['c']);
		
		
		//Trouver les PPA en traitement rapide || traité trop lentement par un MJ
		$query = 'SELECT COUNT(m.id) as c'
				. ' FROM ' . DB_PREFIX . 'ppa as m'
				. ' LEFT JOIN ('
					. '	SELECT rep.*'
					. '	FROM ('
						. ' SELECT *'
						. ' FROM cc_ppa_reponses'
						. ' ORDER BY `date` DESC'
					. ' ) AS rep'
					. ' GROUP BY rep.sujetid'
				. ' ) as r ON (r.sujetid=m.id)'
				. ' WHERE'	
				. ' ('
					. ' m.mjid=0'	// Non-Attribué
					. ' OR'
					. ' ('
						. ' r.`date` <= ' . (int)(time() - mktime(0,0,0,0,3)) // Expirée >3 jours
						. ' AND'
						. ' r.mjid = 0' // Dernière réponse de la part du perso
						. ' )'
					. ' )'
					. ' AND m.statut="ouvert"'
				. ' ORDER BY m.`date` DESC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		$tpl->set('COUNT_PPA', $arr['c']);
		
		
		
		//Passer les privilèges
		$tpl->set('ACCESS_ADMIN', $mj->accessAdmin());
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/main_menu.htm');
	}
	
	
	
	
	
	private static function doInscription(&$tpl, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'SELECT p.*, a.email, a.id as aid'
				. ' FROM ' . DB_PREFIX . 'perso as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=p.userId)'
				. ' WHERE p.inscription_valide="0";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		foreach($arrAll as &$arrInscr )
		{
		
			//Accepter l'inscription
			if ($_POST['inscr_' . $arrInscr['id']] == 'ok' )
			{
				
				//Activer le perso
				$query = 'UPDATE ' . DB_PREFIX . 'perso'
						. ' SET `inscription_valide`="1",'
							. ' `pa` = `pamax`,'
							. ' `lieu`=:lieu_depart'
						. ' WHERE id = :id'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue('id', $arrInscr['id']);
				$prep->bindValue('lieu_depart', LIEU_DEPART);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
		
				//Ajouter le message d'accueil dans le HE
				$query = 'SELECT db_param'
						. ' FROM ' . DB_PREFIX . 'item_db'
						. ' WHERE db_id=9'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->execute($db, __FILE__, __LINE__);
				$arr = $prep->fetch();
				$prep->closeCursor();
				$prep = NULL;
				
				Member_He::add('Douanier', $arrInscr['id'], 'msg', stripslashes($arr['db_param']));

				//Ajouter&equiper les items de bienvenu
				$query = 'SELECT db_id, db_resistance'
						. ' FROM `' . DB_PREFIX . 'item_db`'
						. ' WHERE `db_id` IN(309, 310, 311);';
				$prep = $db->prepare($query);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;


				$query = 'INSERT INTO `' . DB_PREFIX . 'item_inv`'
							. ' (`inv_id`,	`inv_dbid`,	`inv_persoid`,'
							. ' `inv_equip`,`inv_qte`,	`inv_resistance`)'
							. ' VALUES'
							. ' ('
								. ' NULL, 	:db_id, 	:persoId,'
								. ' "1", 	1,			:resistance'
							. ' );';
				$prep = $db->prepare($query);
				foreach($arrAll as &$arr)
				{
					
					$prep->bindValue('db_id',		$arr['db_id'],			PDO::PARAM_INT);
					$prep->bindValue('persoId',		$arrInscr['id'],		PDO::PARAM_INT);
					$prep->bindValue('resistance',	$arr['db_resistance'],	PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					
				}
				$prep->closeCursor();
				$prep = NULL;
				
				//Envoie du email :
				$tpl->set('COMMENTAIRE', $_POST['inscr_commentaire_' . $arrInscr['id']]);
				$tpl->set('MJ_EMAIL', $mj->getEmail());
				$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/emailPersoAccepte.htm',__FILE__,__LINE__);
				mail(
					$arrInscr['email'],
					"Cybercity 2034",
					$MSG,
					"From: robot@cybercity2034.com\n"
					. "MIME-Version: 1.0\n"
					. "Content-type: text/html; charset=iso-8859-1\n"
				);
			}
			
			//Refuser l'inscription
			if ($_POST['inscr_' . $arrInscr['id']] == 'mod')
			{
				
				//Rendre le perso modifiable
				$query = 'UPDATE ' . DB_PREFIX . 'perso'
						. ' SET inscription_valide="mod"'
						. ' WHERE id = :persoId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue('persoId',		$arrInscr['id'],		PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
				
				//Ajouter un message dans le HE (qui sera affiché en haut de la page de modification
				Member_He::add($mj->getNom(), $arrInscr['id'], 'inscription', $_POST['inscr_commentaire_' . $arrInscr['id']]);
				
				//Envoie du email :
				$tpl->set('COMMENTAIRE', $_POST['inscr_commentaire_' . $arrInscr['id']]);
				$tpl->set('MJ_EMAIL', $mj->getEmail());
				$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/emailPersoRefuse.htm',__FILE__,__LINE__);
				mail(
					$arrInscr['email'],
					"Cybercity 2034",
					$MSG,
					"From: robot@cybercity2034.com\n"
					. "MIME-Version: 1.0\n"
					. "Content-type: text/html; charset=iso-8859-1\n"
				);
			}
			
			
			//Supprimer l'inscription
			if ($_POST['inscr_' . $arrInscr['id']] == 'suppr')
			{
			
				//Supprimer le perso
				Mj_Perso_Del::delete($arrInscr['id'], $mj->getNom());
				
				//Redonner la chance d'effectuer une inscription
				$query = 'UPDATE ' . DB_PREFIX . 'account'
						. ' SET auth_creation_perso= auth_creation_perso+1'
						. ' WHERE id=:accountId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue('accountId',		$arrInscr['aid'],		PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$prep->closeCursor();
				$prep = NULL;
				
				//Envoie du email :
				$tpl->set('COMMENTAIRE', $_POST['inscr_commentaire_' . $arrInscr['id']]);
				$tpl->set('MJ_EMAIL', $mj->getEmail());
				$MSG = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/emailPersoSupprime.htm',__FILE__,__LINE__);
				mail(
					$arrInscr['email'],
					"Cybercity 2034",
					$MSG,
					"From: robot@cybercity2034.com\n"
					. "MIME-Version: 1.0\n"
					. "Content-type: text/html; charset=utf-8\n"
				);
			}
			
		}
	}
	
	private static function doDeleteHeSelectedMsg(&$mj)
	{

		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'mj_he'
				. ' WHERE attrib_mj=:mjId'
				. ' ORDER BY id;';
		$prep = $db->prepare($query);
		$prep->bindValue('mjId',		$mj->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$query = 'UPDATE ' . DB_PREFIX . 'mj_he'
				. ' SET `show`="0"'
				. ' WHERE id=:msgId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		
		foreach($arrAll as &$heMsg)
		{
			if (isset($_POST['ppa_suppr_' . $heMsg['id']]))
			{
				if ($_POST['ppa_suppr_' . $heMsg['id']] == $heMsg['id'])
				{
					$prep->bindValue('msgId',	$heMsg['id'],	PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					
				}		
			}
		}
		$prep->closeCursor();
		$prep = NULL;
	}
}

