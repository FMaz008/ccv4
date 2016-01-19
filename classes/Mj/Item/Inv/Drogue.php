<?php
/** Gestion de l'interface de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Item_Inv_Drogue
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Sauvegarder les informations
		$query = 'UPDATE `' . DB_PREFIX . 'item_inv`'
					. ' SET `inv_persoid` 			= :inv_persoid,'
						. ' `inv_lieutech` 			= :inv_lieutech,'
						. ' `inv_boutiquelieutech` 	= :inv_boutiquelieutech,'
						. ' `inv_idcasier` 			= :inv_idcasier,'
						. ' `inv_itemid` 			= :inv_itemid,'
						. ' `inv_equip`			 	= :inv_equip,'
						. ' `inv_duree`				= :inv_duree,'
						. ' `inv_shock_pa`			= :inv_shock_pa,'
						. ' `inv_shock_pv`			= :inv_shock_pv,'
						. ' `inv_boost_pa`			= :inv_boost_pa,'
						. ' `inv_boost_pv`			= :inv_boost_pv,'
						. ' `inv_perc_stat_agi`		= :inv_perc_stat_agi,'
						. ' `inv_perc_stat_dex`		= :inv_perc_stat_dex,'
						. ' `inv_perc_stat_per`		= :inv_perc_stat_per,'
						. ' `inv_perc_stat_for`		= :inv_perc_stat_for,'
						. ' `inv_perc_stat_int`		= :inv_perc_stat_int,'
						. ' `inv_remiseleft`		= :inv_remiseleft,'
						. ' `inv_resistance` 		= :inv_resistance,'
						. ' `inv_extradesc` 		= :inv_extradesc,'
						. ' `inv_notemj` 			= :inv_notemj'
					. ' WHERE `inv_id` = :inv_id;';
		$prep = $db->prepare($query);

		//Déterminer qui a ou non l'item en sa possession (qui = lieu, casier, perso, boutique, etc.)
		switch($_POST['attacheA'])
		{
			case 'perso':
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
		
		$prep->bindValue(':inv_equip', (isset($_POST['inv_equip']) ? '1' : '0'), PDO::PARAM_INT);
		$prep->bindValue(':inv_duree', (int)$_POST['inv_duree'], PDO::PARAM_INT);
		$prep->bindValue(':inv_shock_pa', (int)$_POST['inv_shock_pa'], PDO::PARAM_INT);
		$prep->bindValue(':inv_shock_pv', (int)$_POST['inv_shock_pv'], PDO::PARAM_INT);
		$prep->bindValue(':inv_boost_pa', (int)$_POST['inv_boost_pa'], PDO::PARAM_INT);
		$prep->bindValue(':inv_boost_pv', (int)$_POST['inv_boost_pv'], PDO::PARAM_INT);
		$prep->bindValue(':inv_perc_stat_agi', (int)$_POST['inv_perc_stat_agi'], PDO::PARAM_INT);
		$prep->bindValue(':inv_perc_stat_dex', (int)$_POST['inv_perc_stat_dex'], PDO::PARAM_INT);
		$prep->bindValue(':inv_perc_stat_per', (int)$_POST['inv_perc_stat_per'], PDO::PARAM_INT);
		$prep->bindValue(':inv_perc_stat_for', (int)$_POST['inv_perc_stat_for'], PDO::PARAM_INT);
		$prep->bindValue(':inv_perc_stat_int', (int)$_POST['inv_perc_stat_int'], PDO::PARAM_INT);
		$prep->bindValue(':inv_remiseleft', (int)$_POST['inv_remiseleft'], PDO::PARAM_INT);
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
