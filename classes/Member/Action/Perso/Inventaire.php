<?php
/** Gestion de l'interface de l'inventaire du personnage
*
* @package Member_Action
*/

function compare($a, $b){	//Fonction de comparaison servant au tri du tableua pour afficher les items en inventaire par groupe de "type"
   return strcmp($a->getType(), $b->getType());
}

class Member_Action_Perso_Inventaire
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		if($perso->getMenotte())
			$tpl->set('SILOUHET_IMG', 'silouhet_bg_menotte');
		else
			$tpl->set('SILOUHET_IMG', 'silouhet_bg');
		
		
		//Lister tout les items que le perso possède (section de gauche)
		$i=0; $e=0;
		while( $item = $perso->getInventaire($i++))
			$arrItem[$e++] = $item;
		
		
		if (!empty($arrItem))
		{
			usort($arrItem, "compare");
			$tpl->set('INVENTAIRE',$arrItem);
		}
		
		
		
		//AFFICHER LES IMAGES APPROPRIÉES SUR LE MANEQUIN (section de droite)
		/*
		//Tableau des positions possible. La clé=le nom de la position, La valeur=le nom de la classe de l'item qui va y être placé.
		$arrEquipPos = array(
						'dos'  => 'Member_ItemSac',
						'arme' => 'Member_ItemArme', 
						'tete' => 'Member_ItemDefenseTete',
						'main' => 'Member_ItemDefenseMain',
						'torse'=> 'Member_ItemDefenseTorse',
						'jambe'=> 'Member_ItemDefenseJambe',
						'pied' => 'Member_ItemDefensePied',
						'bras' => 'Member_ItemDefenseBras'
						);
		
		
		//Innitialiser les valeurs par défaut
		$EQUIP = array();
		$arrEquipPosKeys = array_keys($arrEquipPos);
		foreach($arrEquipPosKeys as $key){
			$EQUIP[$key]['id'] = 0;
			$EQUIP[$key]['img'] ='blank.gif';
		}
		
		//Charger les items équipé à la bonne position dans la liste
		$i=0;
		while( $item = $perso->getInventaire($i++)){
			foreach($arrEquipPosKeys as $key){
				if(is_a($item, $arrEquipPos[$key]) && $item->isEquip()){
					$EQUIP[$key]['id'] = $item->getId();
					$EQUIP[$key]['img']= $item->getImage();
				}
			}
		}
		$tpl->set('EQUIP',$EQUIP);
		*/
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Inventaire.htm',__FILE__,__LINE__);
	}
}

