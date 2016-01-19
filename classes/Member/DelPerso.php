<?php
/** Affichage de la page de confirmation de la suppression.
 *
 * @package Member
 * @subpackage Delete
 */
class Member_DelPerso
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if (!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes. (cheat)');
		
		
		
			
		//Trouver les informations sur le perso
		$query = 'SELECT id, nom'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE	id=:persoId '
					. ' AND userid=:userId'
					. ' AND inscription_valide="mod"'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],		PDO::PARAM_INT);
		$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Ce personnage n\'existe pas, ne vous appartiend pas, ou n\'est pas en phase de refus. (cheat)');
		
		$tpl->set('PERSO', $arr);
		
		
		

		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/delPerso.htm',__FILE__,__LINE__);
		
	}
}

