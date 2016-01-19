<?php
/** Affichage de la page d'instruction pour la création d'un personnage
 *
 * @package Member
 * @subpackage Inscription
 */
class Member_CreerPerso
{
	public static function generatePage(&$tpl, &$session, &$account)
	{	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Aller chercher les instructions de création d'un perso
		$query = 'SELECT db_param'
				. ' FROM ' . DB_PREFIX . 'item_db'
				. ' WHERE db_id=:livreId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':livreId',	7,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr===false)
			return fctErrorMSG('Les instruction de création d\'un personnage sont introuvables.');
		
		$tpl->set('INSTRUCTIONS', BBCodes(stripslashes($arr['db_param'])));
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/creerPerso.htm',__FILE__,__LINE__);
		
	}
}

