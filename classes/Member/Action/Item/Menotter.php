<?php
/** Gestion de l'interface de l'action Menotter: Afficher l'interface de l'action
*
* Note: Une menotte équipée signifie qu'elle est en utilisation. Le joueur menotté à le inv_id d'inscrit dans le champ menotte de la table perso.
* @package Member_Action
*/
class Member_Action_Item_Menotter
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		//Lister tout les items en inventaire
		$i=0;
		$arrInv=array();
		while( $item = $perso->getInventaire($i++))
			$arrInv[] = $item->getDbId();
			
		$queryIn = implode(',',$arrInv);
		
		
		//Trouver tout les items avec la capaciter de menottage
		$query = 'SELECT item_dbid'
				. ' FROM ' . DB_PREFIX . 'item_menu'
				. ' WHERE item_dbid IN (' . $queryIn . ')'
					. ' AND url="Menotter";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$arrItems = array();
		foreach($arrAll as &$arr)//Liste de tout les items avec la capacité de Menottage
		{ 
			$i=0;
			while( $item = $perso->getInventaire($i++) ) //Liste de tout les items du perso
				if( $item->getDbId() == $arr['item_dbid'] && !$item->isEquip() ) //S'il y a correspondance, ajouter l'item à la liste à afficher
					$arrItems[] = $item;
		}
		$tpl->set('ITEMS', $arrItems);
		
		
		//Générer la liste des perso présents dans le lieu actuel
		$i=0;
		$persoDansLeLieuActuel = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId())
				$persoDansLeLieuActuel[] = $tmp;
		
		if(count($persoDansLeLieuActuel) > 0)
			$tpl->set('LIST_PERSO', $persoDansLeLieuActuel);
		
		
		
		
		
		
		
		
		
		//Générer la liste des perso présents dans le lieu actuel et menotté
		$i=0;
		$persoDansLeLieuActuel = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getMenotte()!=false)
				$persoDansLeLieuActuel[] = $tmp;
		
		
		if(count($persoDansLeLieuActuel) > 0)
		{
			
			//Vérifier ceux que nous sommes en mesure de détacher
			$i=0;
			$persoDettachable = array();
			while( $item = $perso->getInventaire($i++) ) //Liste de tout les items du perso
				foreach($persoDansLeLieuActuel as $sujet) //Liste tout les perso menotté du lieu
					if($sujet->getMenotte() == $item->getInvId()) //Si les menottes sont en inventaire de mon perso, j'ai donc la "clé" (l'item)
						$persoDettachable[] = $sujet;
			
			
			
			$tpl->set('LIST_PERSO2', $persoDettachable);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Menotter.htm',__FILE__,__LINE__);
	}
}
