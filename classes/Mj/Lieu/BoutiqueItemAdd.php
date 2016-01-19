<?php
/** Gestion de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Lieu_BoutiqueItemAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!isset($_POST['itemId']))
			return fctErrorMSG('Vous devez sélectionner un item.', '?mj=Lieu_Boutique&id=' . $_GET['id'],null,false);
		
		
		
		
		
		//Instancier le lieu
		try
		{
			$lieu = Member_LieuFactory::createFromId((int)$_GET['id']);
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		
		$arrItemId = array();
		if(is_array($_POST['itemId']))
			$arrItemId = $_POST['itemId'];
		else
			$arrItemId[] = $_POST['itemId'];
		
		
		$queryAddon = array();
		foreach($arrItemId as $id)
			$queryAddon[] = '?';
		$queryAddon = implode(',', $queryAddon);
		
		
		$query = 'SELECT *
					FROM ' . DB_PREFIX . 'item_db
					WHERE db_id IN (' . $queryAddon . ');';
		$prep = $db->prepare($query);
		for($i=1; $i<=count($arrItemId);$i++)
			$prep->bindValue($i, $arrItemId[$i-1],	PDO::PARAM_INT);
		
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'item_inv'
				. ' (`inv_id`, `inv_dbid`, `inv_boutiquelieutech`, `inv_qte`, `inv_munition`, `inv_resistance`,`inv_remiseleft`,`inv_pn`, `inv_boutiquePrixVente`,`inv_boutiquePrixAchat`)'
				. ' VALUES'
				. ' (NULL,		:dbId,		:lieuTech,				:qte,		:munition,		:resistance,	NULL,			:pn,	-1,							-1);';
		$prep = $db->prepare($query);
		
		foreach($arrAll as &$arr)
		{
			//Vérifier si la boutique actuelle possède déjà cet item
			$i=0; $item = false;
			while( $item = $lieu->getBoutiqueInventaire($i++))
				if(self::itemIdentique($arr, $item)!==false)
					break;
			
			if($item!==false)
			{
				//L'item à été trouvé, l'incrémenter.
				$item_qte = $item->getQte() + $_POST['item' . $item->getDbId()];
				$item->setQte($item_qte);
			}
			else
			{
				//L'item n'existe pas, le créer
				$prep->bindValue(':dbId',		$arr['db_id'],					PDO::PARAM_INT);
				$prep->bindValue(':lieuTech',	$lieu->getNomTech(),			PDO::PARAM_STR);
				$prep->bindValue(':qte',		$_POST['item' . $arr['db_id']],	PDO::PARAM_INT);
				
				if($arr['db_soustype'] == 'arme_feu')
					$prep->bindValue(':munition',	0,							PDO::PARAM_INT);
				else
					$prep->bindValue(':munition',	NULL,						PDO::PARAM_NULL);
				
				if(isset($arr['db_resistance']))
					$prep->bindValue(':resistance',	$arr['db_resistance'],		PDO::PARAM_INT);
				else
					$prep->bindValue(':resistance',	NULL,						PDO::PARAM_NULL);
				
				if($arr['db_type'] == 'nourriture')
					$prep->bindValue(':pn',			$arr['db_pn'],				PDO::PARAM_INT);
				else
					$prep->bindValue(':pn',			NULL,						PDO::PARAM_NULL);
				
				$prep->execute($db, __FILE__, __LINE__);
				
			}
			
		}
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Lieu_Boutique&id=' . $_GET['id'] );
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}

	private static function itemIdentique(&$baseItem, &$item)
	{
		//Les ID doivent être identique
		if($item->getDbId() != $baseItem['db_id'])
			return false;

		/*
		if($item instanceof Member_ItemDrogueDrogue)
			if(self::itemsAreSameDrogue($item, $ITEM))
				return $item;
		*/
		
		if($item instanceof Member_ItemAutre)
			return true;
		
		if($item instanceof Member_ItemNourriture)
			if($item->getPn()==$baseItem['db_pr'])
				return true;

		if($item instanceof Member_ItemDefense)
			if($item->getResistance()==$baseItem['db_resistance'])
				return true;

		if($item instanceof Member_ItemArme)
			if($item->getResistance()==$baseItem['db_resistance'])
				return true;
				
		if($item instanceof Member_ItemClef)
			if($item->getCode()=='')
				return true;

		if($item instanceof Member_Badge)
			if($item->getNom()==$baseItem['db_nom'])
				if($item->getTitre()=='')
					return true;
					
		if($item instanceof Member_ItemTrousse)
			if($item->getResistance()==$baseItem['db_resistance'])
				return true;
		
		if($item instanceof Member_ItemNourriture)
			if($item->getPn()==$baseItem['db_pn'])
				return true;
		
		
		return false;
	}
}
