<?php
/** Gestion des items INV (possédé par quelque chose, en jeu)
*
* @package Mj
*/
class Mj_Item_Inv_Mod
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM `' . DB_PREFIX . 'item_inv` as i'
					. ' LEFT JOIN `' . DB_PREFIX . 'item_db` ON (db_id = inv_dbid)'
					. ' WHERE `inv_id` = :inv_id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':inv_id', $_GET['id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($arr === false)
			return fctErrorMSG('L\'item demandé ne semble pas exister.');

		
		if ($arr['db_type']=='arme')
			$page = $arr['db_soustype'];
		else
			$page = $arr['db_type'];
		
		if(empty($arr['db_resistance']))
			$arr['db_resistance'] = 0;
		
		$arr['inv_extradesc'] = stripslashes($arr['inv_extradesc']);
		$arr['inv_notemj'] = stripslashes($arr['inv_notemj']);
		$arr['db_notemj'] = stripslashes($arr['db_notemj']);
		
		//Vérifier à quoi l'item est associé, puis trouver le nom de de cette chose (perso ou lieu)
		$arr['perso'] = '';
		$arr['lieu'] = '';
		$arr['boutique'] = '';
		$arr['casier'] = '';
		$arr['item'] = '';
		if(isset($arr['inv_persoid']))
		{
			$query = 'SELECT nom'
						. ' FROM `' . DB_PREFIX .'perso`'
						. ' WHERE `id` = :id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id', $arr['inv_persoid'], PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$result = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			$arr['perso'] = $result['nom'];
			$arr['att_type'] = 'perso';
		}
		
		if(isset($arr['inv_lieutech']))
		{
			$query = 'SELECT nom_affiche'
						. ' FROM `' . DB_PREFIX .'lieu`'
						. ' WHERE `nom_technique` = :inv_lieutech;';
			$prep = $db->prepare($query);
			$prep->bindValue(':inv_lieutech', $arr['inv_lieutech'], PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			$result = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			$arr['lieu'] = $result['nom_affiche'];
			$arr['att_type'] = 'lieu';
		}
		
		if(isset($arr['inv_boutiquelieutech']))
		{
			$query = 'SELECT nom_affiche'
						. ' FROM `' . DB_PREFIX .'lieu`'
						. ' WHERE `nom_technique` = :inv_boutiquelieutech;';
			$prep = $db->prepare($query);
			$prep->bindValue(':inv_boutiquelieutech', $arr['inv_boutiquelieutech'], PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			$result = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			$arr['boutique'] = $result['nom_affiche'];
			$arr['att_type'] = 'boutique';
		}
		
		if(isset($arr['inv_idcasier']))
		{
			$query = 'SELECT nom_casier'
						. ' FROM `' . DB_PREFIX .'lieu_casier`'
						. ' WHERE `id_casier` = :inv_idcasier;';
			$prep = $db->prepare($query);
			$prep->bindValue(':inv_idcasier', $arr['inv_idcasier'], PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$result = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			$arr['boutique'] = $result['nom_casier'];
			$arr['att_type'] = 'casier';
		}
		
		if(isset($arr['inv_itemid']))
		{
			$query = 'SELECT db_nom'
						. ' FROM `' . DB_PREFIX .'item_inv`'
						. ' LEFT JOIN `' . DB_PREFIX . 'item_db` ON (`db_id` = `inv_dbid`)'
						. ' WHERE `inv_id` = :inv_itemid;';
			$prep = $db->prepare($query);
			$prep->bindValue(':inv_itemid', $arr['inv_itemid'], PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$result = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			$arr['boutique'] = $result['db_nom'];
			$arr['att_type'] = 'item';
		}
		
		if($arr['inv_nocompte'] != NULL)
			$arr['inv_nocompte'] = $arr['inv_nobanque'] . '-' . $arr['inv_nocompte'];
		
		//Afficher la page
		$tpl->set('ITEM',$arr);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Item/Inv/' . $page . '.htm');
	}
}

