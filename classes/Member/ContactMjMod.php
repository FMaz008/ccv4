<?php
/** Gestion de l'interface d'une boutique
 *
 * @package Member
 * @subpackage Contact
 */
class Member_ContactMjMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if(!isset($_GET['id']) || !is_numeric($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		
		
		
		
		
		//Ajouter un message au PPA
		if(isset($_POST['form_action']) && ($_POST['form_action'] == 'reply' || ($_POST['form_action'] == 'del' && !empty($_POST['msg']))))
		{
			$query = 'INSERT INTO ' . DB_PREFIX . 'ppa_reponses'
					. ' (`sujetid`, `mjid`, `date`, `msg`)'
					. ' VALUES'
					. ' (:sujetId,  0,  UNIX_TIMESTAMP(), :msg);';
			$prep = $db->prepare($query);
			$prep->bindValue(':sujetId',	$_GET['id'],						PDO::PARAM_INT);
			$prep->bindValue(':msg',		fctScriptProtect($_POST['msg']),	PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			
			
			//Copier le message dans les HE
			Member_He::add($perso->getId(), 'MJ', 'ppa', $_POST['msg']);
		}

		//Fermer le PPA
		if(isset($_POST['form_action']) && $_POST['form_action'] == 'del')
		{
			$query = 'UPDATE ' . DB_PREFIX . 'ppa'
					. ' SET statut="ferme"'
					. ' WHERE	id=:sujetId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':sujetId',	$_GET['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			
			$tpl->set('PAGE', '?popup=1&m=ContactMj');
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
		}
		
		
		
		
		//Trouver les PPA demandé
		$query = 'SELECT m.id, m.msg, m.`date`, m.statut, mj.nom as mjnom'
				. ' FROM ' . DB_PREFIX . 'ppa as m'
				. ' LEFT JOIN ' . DB_PREFIX . 'mj as mj ON (mj.id = m.mjid) '
				. ' WHERE	m.id=:sujetId'
					. ' AND persoid=:persoId'
				. ' LIMIT 1';
		$prep = $db->prepare($query);
		$prep->bindValue(':sujetId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$perso->getId(),PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if($arr === false)
			return fctErrorMSG('Ppa innexistant.');
		
		
		$PPA = array();
		$i=0;
		
		//Afficher le message de base (Le PPA)
		$PPA[$i] = $arr;
		$PPA[$i]['msg'] = BBCodes(stripslashes($PPA[$i]['msg']));
		$PPA[$i]['date'] = fctToGameTime($arr['date']);
		$PPA[$i]['de'] = $perso->getNom();
		$i++;
		
		
		//Afficher les réponses
		$query = 'SELECT p.*, mj.nom as mjnom'
				. ' FROM ' . DB_PREFIX . 'ppa_reponses as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'mj as mj ON (mj.id = p.mjid)'
				. ' WHERE	p.sujetid=:sujetId'
				. ' ORDER BY p.`date` ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':sujetId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrM = $prep->fetchAll();
		
		if(count($arrM) != 0)
		{
			foreach($arrM as $arr)
			{
				$PPA[$i] = $arr;
				$PPA[$i]['msg'] = BBCodes(stripslashes($PPA[$i]['msg']));
				$PPA[$i]['date'] = fctToGameTime($arr['date']);
				
				if(empty($arr['mjnom']))
					$PPA[$i]['de'] = $perso->getNom();
				else
					$PPA[$i]['de'] = stripslashes($arr['mjnom']);
				
				$i++;
			}
		}
		
		$tpl->set('PPA', $PPA);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/contactMjMod.htm',__FILE__,__LINE__);
	}
}

