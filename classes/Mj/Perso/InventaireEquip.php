<?php
/** Équiper/Ranger un item
*
* @package Mj
*/

class Mj_Perso_InventaireEquip
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['equip']) || ($_GET['equip']!='1' && $_GET['equip']!='0'))
			return fctErrorMSG('ERREUR MÉCHANTE #1.', '?mj=Perso_Inventaire&id=' . $_GET['id'],null,false);
			
		
		//Équiper/Ranger ?
		if ($_GET['equip']=='1')//Équiper
		{ 
			
			//Déséquiper les items du même type qui pourraient être équipé
			
			//Trouver le type de l'item à équiper
			$query = 'SELECT db_type, db_soustype, inv_persoid'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id = inv_dbid)'
					. ' WHERE inv_id=:invId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':invId',		$_GET['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			

			if($arr['db_type'] == 'arme')
			{
				//Trouver tout les items du même type qui sont équipés
				$query = 'SELECT inv_id'
						. ' FROM ' . DB_PREFIX . 'item_inv'
						. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id = inv_dbid)'
						. ' WHERE db_type=:type'
							. ' AND inv_equip="1"'
							. ' AND inv_persoid=:persoId;';
				$prep = $db->prepare($query);
				$prep->bindValue(':type',		$arr['db_type'],		PDO::PARAM_STR);
				$prep->bindValue(':persoId',	$arr['inv_persoid'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
			}
			else
			{
				//Trouver tout les items du même sous-type qui sont équipés
				$query = 'SELECT inv_id'
						. ' FROM ' . DB_PREFIX . 'item_inv'
						. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id = inv_dbid)'
						. ' WHERE db_type=:type'
							. ' AND db_soustype=:sousType'
							. ' AND inv_equip="1"'
							. ' AND inv_persoid=:persoId;';
				$prep = $db->prepare($query);
				$prep->bindValue(':type',		$arr['db_type'],		PDO::PARAM_STR);
				$prep->bindValue(':sousType',	$arr['db_soustype'],	PDO::PARAM_STR);
				$prep->bindValue(':persoId',	$arr['inv_persoid'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$arrAll = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;
			}
			
			//Déséquiper les items équipés trouvés
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_equip="0"'
					. ' WHERE inv_id=:invId;';
			$prep = $db->prepare($query);
			foreach($arrAll as &$arr)
			{
				$prep->bindValue(':invId',	$arr['inv_id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
			}
			$prep->closeCursor();
			$prep = NULL;
		}
		
		//Équiper/ranger l'item
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_equip=:equip'
					. ' WHERE inv_id=:invId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':equip',		$_GET['equip'],	PDO::PARAM_STR);
		$prep->bindValue(':invId',		$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
				
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_Inventaire&id=' . $_GET['rid']);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

