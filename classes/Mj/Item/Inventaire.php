<?php
/** Ajax: Gestion de l'interface de l'inventaire d'un item
*
* @package Mj
* @subpackage Ajax
*/

function compare($a, $b)//Fonction de comparaison servant au tri du tableau pour afficher les items en inventaire par groupe de "type"
{
   return strcmp($a->getType(), $b->getType());
}

class Mj_Item_Inventaire
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un item.');
			
		if(!is_numeric($_GET['id']))
			return fctErrorMSG('ID non numérique');
		
		//Trouver des informations sur l'item
		$query = 'SELECT *'
					. ' FROM `' . DB_PREFIX . 'item_inv`'
					. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$result = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		if(count($result) == 0)
			return fctErrorMSG('Cet item n\'existe pas.');
		
		//Effectuer le rendu tableau de l'inventaire
		$query = 'SELECT *'
					. ' FROM `' . DB_PREFIX . 'item_inv`'
					. ' INNER JOIN `' . DB_PREFIX . 'item_db` ON (`db_id` = `inv_dbid`)'
					. ' WHERE `inv_itemid` = ' . mysql_real_escape_string($_GET['id']) . ';';
					
		$tableHTML = generateInventaireTable($tpl, $query, 'Item_Inventaire', $_GET['id'], '800px');
		if(!empty($tableHTML))
			$tpl->set('ITEMS_TABLE',$tableHTML);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Item/Inventaire.htm',__FILE__,__LINE__);
	}
	
	
	/**
	 * Génère un tableau de l'inventaire d'un item à partir d'une requête.
	 *
	 * @param Account &$account Account à utiliser (par défaut: $account)
	 * @param array $arrItem tableau des items à traiter
	 * @param string $returnPage Nom du module actuel (pour les pages de retour, ex: Member_Perso_Inventaire)
	 * @param string $tableWidth Largeur du tableau à créer
	 * @return string Retourne du code HTML ou false si aucun item
	 */
	public static function generateInventaireTable(&$account, &$arrItem, $returnPage, $returnId, $tableWidth, $deep=0)
	{
		$deep++;
		$tpl = new Template($account);
		
		
		if(count($arrItem)==0)
			return array();
		
		//Trier le tableau en ordre de type
		usort($arrItem, "compare");
		
		//Construire la liste des tableaux
		$tableHTML  = '';
		$currentCat = '';
		$i=0;
		foreach($arrItem as $item)
		{
			//Gérer les entête du tableau
			$showHeader = false;
			if($currentCat != $item->getTypeTech())
			{
				if(!empty($tableHTML)) //Ne pas commencer avec une fermeture de tableau
					$tableHTML .= '</table><br />';
				$currentCat = $item->getTypeTech();
				$showHeader = true;
			}
			
			//Générer le tableau
			$type = $item->getTypeTech();
			if($item instanceof Member_ItemDefense)
				$type = 'defense';
			
			if($item instanceof Member_ItemDrogue)
				$type = 'drogue';
			
			if($item instanceof Member_ItemSac && $deep<2)
			{
				$arrItem2 = Member_ItemFactory::createFromItemId($item->getInvId(), true);
				
				if(!empty($arrItem2))
					$sacHTML = self::generateInventaireTable($account, $arrItem2, $returnPage, $returnId, '750px', $deep);
				else
					$sacHTML = "Vide";
				$tpl->set('SAC_INVENTAIRE', $sacHTML);
			}
			
			$tpl->set('SHOW_HEADER', $showHeader);
			$tpl->set('TABLE_WIDTH', $tableWidth);
			$tpl->set('RETURN_PAGE', $returnPage);
			$tpl->set('RETURN_ID', $returnId);
			$tpl->set('ITEM', $item);
			$tableHTML .= $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Item/Inventaire_' . $type . '.htm',__FILE__,__LINE__);
		}
		return $tableHTML . '</table>';
	}
}

