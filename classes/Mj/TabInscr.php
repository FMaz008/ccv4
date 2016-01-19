<?php
/**
 * Génère le contenu de l'onglet Inscription
 * @package Mj
 * @subpackage Ajax
 */

class Mj_TabInscr
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		
		//Trouver les inscriptions en attentes de validation
		$query = 'SELECT p.*, a.email, a.user'
				. ' FROM ' . DB_PREFIX . 'perso as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=p.userId)'
				. ' WHERE p.inscription_valide="0"'
				. ' ORDER BY id;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$donnees = array();
		foreach($arrAll as &$arr)
		{
			$arr['email'] = str_replace('@','<br />@',$arr['email']);
			$arr['description'] = nl2br(stripslashes($arr['description']));
			$arr['background'] = nl2br(stripslashes($arr['background']));
			$donnees[] = $arr;
		}
		$tpl->set('INSCR',$donnees);
		
		
		
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/tabInscr.htm');
		die();
	}
}

