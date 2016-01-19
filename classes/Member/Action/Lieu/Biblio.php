<?php
/** Gestion du visionnement des livres en bibliothèque
*
* @package Member_Action
*/
class Member_Action_Lieu_Biblio
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		//Valider l'état du perso
		if (!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Afficher la liste des livres disponibles dans ce lieu
		$query = 'SELECT l.* '
				. ' FROM ' . DB_PREFIX . 'lieu_livre as b'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db as l ON (l.db_id = b.itemDbId)'
				. ' WHERE lieuId=:lieuId'
				. ' ORDER BY l.db_nom ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$perso->getLieu()->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrLivre = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$LIVRES = array();
		if (count($arrLivre) != 0 )
		{
			foreach($arrLivre as &$arr)
				$LIVRES[] = new Member_ItemLivre($arr);
			
			
			$tpl->set('LIVRES',$LIVRES);
		}
		
		
		//Retourner le template bâti.
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BiblioListe.htm',__FILE__,__LINE__);
	}
}
