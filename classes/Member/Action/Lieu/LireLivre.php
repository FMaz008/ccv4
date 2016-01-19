<?php
/** Gestion du visionnement des livres en bibliothèque
*
* @package Member_Action
*/
class Member_Action_Lieu_LireLivre
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		//Valider l'état du personnage
		if (!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Valider si un livre à été demandé
		if(!isset($_POST['id']) || !is_numeric($_POST['id']))
			return fctErrorMSG('Aucun livre spécifi&eacute;.');
		
		
		
		
		
		//Valider si l'accès au livre est permis
		//L'accès peut être permi par: un lieu bibliothèque, un item en notre possession
		$livre = false;
		
		//Vérifier si l'accès au livre est permis par une bibliothèque
		
		$query = 'SELECT l.*'
				. ' FROM `' . DB_PREFIX . 'lieu_livre` as b'
				. ' LEFT JOIN `' . DB_PREFIX . 'item_db` as l ON (l.`db_id` = b.`itemDbId`)'
				. ' WHERE	b.`lieuId`  =:lieuId'
					. ' AND b.`itemDbId`=:itemDbId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',			$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':itemDbId',		$_POST['id'],					PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($arr !== false)
			$livre = new Member_ItemLivre($arr);
		
		
		
		
		//Vérifier si l'accès est permi grace à une possession en inventaire
		if(!$livre)
		{
			$i=0;
			while( $item = $perso->getInventaire($i++))
			{
				if($item instanceof Member_ItemLivre)
				{
					if($item->getInvId() == $_POST['id'])
					{
						$livre = $item;
						break;
					}
				}
			}
		}
		
		
		
		//Si une URL de retour a été fournie, la passer au template
		if(isset($_POST['URL_RETOUR']))
			$tpl->set('URL_RETOUR', $_POST['URL_RETOUR']);
		
		
		
		
		//Valider si l'accès au livre est permis
		if($livre===false)
			return fctErrorMSG('Vous n\'avez pas le droit d\'accéder à ce livre.');
		
		
		$tpl->set('LIVRE', $livre);
		
		//Retourner le template bâti.
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/LireLivre.htm',__FILE__,__LINE__);
		
	}
}
