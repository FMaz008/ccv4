<?php
/** Gestion des caractéristiques incompatibles du jeu
*
* @package Mj
*/
class Mj_Perso_GestionCaractIncompatible
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Générer la liste des caractéristiques
		$query = 'SELECT id, nom
					FROM ' . DB_PREFIX . 'caract
					ORDER BY nom ASC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$CARACT = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('CARACT',$CARACT);
		$tpl->set('IDX',$_POST['divid']);
		
		$source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionCaractIncompatible.htm');
		die($source);
	}
}

