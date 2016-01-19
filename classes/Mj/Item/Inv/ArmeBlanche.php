<?php
/** Gestion de l'interface de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Item_Inv_ArmeBlanche
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Sauvegarder les informations
		$query = 'UPDATE `' . DB_PREFIX . 'item_inv`'
					. ' SET `inv_persoid` 			= :inv_persoid,'
						. ' `inv_equip`				= :inv_equip,'
						. ' `inv_lieutech` 			= :inv_lieutech,'
						. ' `inv_boutiquelieutech` 	= :inv_boutiquelieutech,'
						. ' `inv_idcasier` 			= :inv_idcasier,'
						. ' `inv_itemid` 			= :inv_itemid,'
						. ' `inv_resistance` 		= :inv_resistance,'
						. ' `inv_extradesc` 		= :inv_extradesc,'
						. ' `inv_notemj` 			= :inv_notemj'
					. ' WHERE `inv_id` = :inv_id;';
		$prep = $db->prepare($query);

		$inv_equip = '0';
		
		//Déterminer qui a ou non l'item en sa possession (qui = lieu, casier, perso, boutique, etc.)
		switch($_POST['attacheA'])
		{
			case 'perso':
				if (isset($_POST['inv_equip']))
				{
					//Déséquiper les armes
					$query = 'UPDATE `' . DB_PREFIX . 'item_inv`'
								. ' LEFT JOIN `' . DB_PREFIX . 'item_db` ON (db_id = inv_dbid)'
								. ' SET `inv_equip` = "0"'
								. ' WHERE `inv_persoid` = :inv_persoid'
										//. ' AND `db_soustype` = :db_soustype;';
										. ' AND `db_type` = "arme";';
					$prep2 = $db->prepare($query);
					$prep2->bindValue(':inv_persoid', $_POST['inv_persoid'], PDO::PARAM_INT);
					//$prep2->bindValue(':db_soustype', $_POST['db_soustype'], PDO::PARAM_INT);
					$prep2->execute($db, __FILE__,__LINE__);
					$prep2->closeCursor();
					$prep2 = NULL;
					$inv_equip = '1';
				}
				
				$prep->bindValue(':inv_persoid', 			$_POST['inv_persoid'], PDO::PARAM_INT);
				$prep->bindValue(':inv_lieutech', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_boutiquelieutech',	NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_idcasier',			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_itemid',				NULL, PDO::PARAM_NULL);
				break;
			case 'lieu':
				$prep->bindValue(':inv_persoid', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_lieutech',			$_POST['inv_lieutech'], PDO::PARAM_STR);
				$prep->bindValue(':inv_boutiquelieutech', 	NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_idcasier',			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_itemid',				NULL, PDO::PARAM_NULL);
				break;
			case 'casier':
				$prep->bindValue(':inv_persoid', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_lieutech', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_boutiquelieutech',	$_POST['inv_casierid'], PDO::PARAM_STR);
				$prep->bindValue(':inv_idcasier', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_itemid', 			NULL, PDO::PARAM_NULL);
				break;
			case 'item':
				$prep->bindValue(':inv_persoid', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_lieutech', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_boutiquelieutech', 	NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_idcasier',			$_POST['inv_itemid'], PDO::PARAM_INT);
				$prep->bindValue(':inv_itemid', 			NULL, PDO::PARAM_NULL);
				break;
			case 'boutique':
				$prep->bindValue(':inv_persoid', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_lieutech', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_boutiquelieutech', 	NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_idcasier', 			NULL, PDO::PARAM_NULL);
				$prep->bindValue(':inv_itemid',				$_POST['inv_boutiquelieutech'], PDO::PARAM_INT);
				break;
		}

		$prep->bindValue(':inv_equip', $inv_equip, PDO::PARAM_INT);
		$prep->bindValue(':inv_resistance', $_POST['inv_resistance'], PDO::PARAM_INT);
		$prep->bindValue(':inv_extradesc', $_POST['inv_extradesc'], PDO::PARAM_STR);
		$prep->bindValue(':inv_notemj', $_POST['inv_notemj'], PDO::PARAM_STR);
		$prep->bindValue(':inv_id', $_GET['id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', $_GET['rpage'] . '&id=' . $_GET['rid']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}
