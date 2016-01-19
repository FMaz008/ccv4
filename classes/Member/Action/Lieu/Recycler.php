<?php
/** Recyclage des Items, but: générer un template qui va afficher la liste des objets à recycler
*
* @package Member_Action
*/
class Member_Action_Lieu_Recycler
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier que le lieu dans lequel est le perso permet bien de recycler les items
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'lieu_menu`'
				. ' WHERE `lieutech`=:lieuTech'
					. ' AND `url`="Recycler"'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',		$perso->getLieu()->getNomTech(),			PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		if($arr === false)
			return fctErrorMSG('Vous n\'êtes pas dans un lieu permettant ce type d\'action.');	

		
	
	
		$i=0; $e=0;
		$objets=array();
		while( $item = $perso->getInventaire($i++))
				$objets[$e++] = $item;
			
		$tpl->set('OBJETS', $objets);

		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Recycler.htm',__FILE__,__LINE__);	
	}
}

