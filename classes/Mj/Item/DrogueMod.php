<?php
/** Gestion de l'interface pour les items
*
* @package Mj
*/
class Mj_Item_DrogueMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		if(!isset($_POST['db_id']))
			return fctErrorMSG('Vous devez sélectionner un objet.');
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(isset($_POST['save']))
		{
			self::save();
			die("<script>location.href='?mj=Item_Drogue';</script>");
		}
		
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		
		$query = 'SELECT `db_id`,'
					. ' `db_soustype`,'
					. ' `db_nom`,'
					. ' `db_desc`,'
					. ' `db_valeur`,'
					. ' `db_img`,'
					. ' `db_duree`,'
					. ' `db_shock_pa`,'
					. ' `db_shock_pv`,'
					. ' `db_boost_pa`,'
					. ' `db_boost_pv`,'
					. ' `db_perc_stat_agi`,'
					. ' `db_perc_stat_dex`,'
					. ' `db_perc_stat_per`,'
					. ' `db_perc_stat_for`,'
					. ' `db_perc_stat_int`,'
					. ' `db_resistance`,'
					. ' `db_notemj`'
					. ' FROM  `' . DB_PREFIX . 'item_db`'
					. ' WHERE `db_id` = :db_id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':db_id', $_POST['db_id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('ITEM',$arr);
		
		//lister le dossier d'image
		$dir2 = dir($account->getSkinRemotePhysicalPath() . "../_common/items/");
		$counter=0;
		$arrurl = array();
		$arr=array();
		while ($url = $dir2->read())
			$arrurl[$counter++]=$url;
		
		natcasesort($arrurl);
		$arrurl = array_values($arrurl);
		for ($i=0;$i<count($arrurl);$i++)
			if ($arrurl[$i]!='' && substr($arrurl[$i],0,1)!='.')
				$arr[$i] = $arrurl[$i];
		$tpl->set('IMGS',$arr);
		
		//Générer la liste des actions associable à un lieu
		$query = 'SELECT `id`, `url`, `caption`'
					. ' FROM `' . DB_PREFIX . 'item_menu`'
					. ' WHERE `item_dbid` = :db_id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':db_id', $_POST['db_id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$result = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($result) > 0)
		{
			$ACTIONS= array();
			foreach($result as $arr)
				$ACTIONS[] = $arr;
			$tpl->set('ACTIONS',$ACTIONS);
		}
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Item/Drogue_Addmod.htm');
	}
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if (empty($_POST['db_valeur'])){ $_POST['db_valeur'] = 0; }
		if (empty($_POST['db_duree'])){ $_POST['db_duree'] = 0; }
		if (empty($_POST['db_shock_pa'])){ $_POST['db_shock_pa'] = 0; }
		if (empty($_POST['db_shock_pv'])){ $_POST['db_shock_pv'] = 0; }
		if (empty($_POST['db_boost_pa'])){ $_POST['db_boost_pa'] = 0; }
		if (empty($_POST['db_boost_pv'])){ $_POST['db_boost_pv'] = 0; }
		if (empty($_POST['db_perc_stat_agi'])){ $_POST['db_perc_stat_agi'] = 0; }
		if (empty($_POST['db_perc_stat_dex'])){ $_POST['db_perc_stat_dex'] = 0; }
		if (empty($_POST['db_perc_stat_per'])){ $_POST['db_perc_stat_per'] = 0; }
		if (empty($_POST['db_perc_stat_for'])){ $_POST['db_perc_stat_for'] = 0; }
		if (empty($_POST['db_perc_stat_int'])){ $_POST['db_perc_stat_int'] = 0; }
		if (empty($_POST['db_resistance'])){ $_POST['db_resistance'] = 0; }
		
		$query = 'UPDATE `' . DB_PREFIX . 'item_db`'
					. ' SET'
						. ' `db_nom` = :db_nom,'
						. ' `db_soustype` = :db_soustype,'
						. ' `db_desc` = :db_desc,'
						. ' `db_valeur` = :db_valeur,'
						. ' `db_img` = :db_img,'
						. ' `db_duree` = :db_duree,'
						. ' `db_shock_pa` = :db_shock_pa,'
						. ' `db_shock_pv` = :db_shock_pv,'
						. ' `db_boost_pa` = :db_boost_pa,'
						. ' `db_boost_pv` = :db_boost_pv,'
						. ' `db_perc_stat_agi` = :db_perc_stat_agi,'
						. ' `db_perc_stat_dex` = :db_perc_stat_dex,'
						. ' `db_perc_stat_per` = :db_perc_stat_per,'
						. ' `db_perc_stat_for` = :db_perc_stat_for,'
						. ' `db_perc_stat_int` = :db_perc_stat_int,'
						. ' `db_resistance` = :db_resistance,'
						. ' `db_notemj` = :db_notemj'
					. ' WHERE `db_id` = :db_id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':db_nom', $_POST['db_nom'], PDO::PARAM_STR);
		$prep->bindValue(':db_soustype', $_POST['db_soustype'], PDO::PARAM_STR);
		$prep->bindValue(':db_desc', $_POST['db_desc'], PDO::PARAM_STR);
		$prep->bindValue(':db_valeur', str_replace(',','.',$_POST['db_valeur']), PDO::PARAM_STR);
		$prep->bindValue(':db_img', $_POST['db_img'], PDO::PARAM_STR);
		$prep->bindValue(':db_duree', $_POST['db_duree'], PDO::PARAM_INT);
		$prep->bindValue(':db_shock_pa', $_POST['db_shock_pa'], PDO::PARAM_INT);
		$prep->bindValue(':db_shock_pv', $_POST['db_shock_pv'], PDO::PARAM_INT);
		$prep->bindValue(':db_boost_pa', $_POST['db_boost_pa'], PDO::PARAM_INT);
		$prep->bindValue(':db_boost_pv', $_POST['db_boost_pv'], PDO::PARAM_INT);
		$prep->bindValue(':db_perc_stat_agi', $_POST['db_perc_stat_agi'], PDO::PARAM_INT);
		$prep->bindValue(':db_perc_stat_dex', $_POST['db_perc_stat_dex'], PDO::PARAM_INT);
		$prep->bindValue(':db_perc_stat_per', $_POST['db_perc_stat_per'], PDO::PARAM_INT);
		$prep->bindValue(':db_perc_stat_for', $_POST['db_perc_stat_for'], PDO::PARAM_INT);
		$prep->bindValue(':db_perc_stat_int', $_POST['db_perc_stat_int'], PDO::PARAM_INT);
		$prep->bindValue(':db_resistance', $_POST['db_resistance'], PDO::PARAM_INT);
		$prep->bindValue(':db_notemj', $_POST['db_notemj'], PDO::PARAM_STR);
		$prep->bindValue(':db_id', $_POST['db_id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Mise à jour des actions actuelles
		$query = 'SELECT `id`'
					. ' FROM `' . DB_PREFIX . 'item_menu`'
					. ' WHERE `item_dbid` = :db_id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':db_id', $_POST['db_id'], PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$result = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		foreach($result as $row)
		{
			if (isset($_POST['delAction_' . $row[0]]))
			{
				$query = 'DELETE'
							. ' FROM `' . DB_PREFIX . 'item_menu`'
							. ' WHERE `id` = :id'
							. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id', $row[0], PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
				$prep->closeCursor();
				$prep = NULL;
			}
			else
			{
				$query = 'UPDATE `' . DB_PREFIX . 'item_menu`'
							. ' SET `caption` = :caption'
							. ' WHERE `id` = :id;';
				$prep = $db->prepare($query);
				$prep->bindValue(':caption', $_POST[$row[0] . '_actioncaption'], PDO::PARAM_STR);
				$prep->bindValue(':id', $row[0], PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
				$prep->closeCursor();
				$prep = NULL;
			}
		}
		
		//Insertion de nouvelles actions associer au lieu
		if ($_POST['total_action_add']>0)
		{
			for($i=1;$i<=$_POST['total_action_add'];$i++)
			{
				if (!empty($_POST[$i . '_actionpage_add']))
				{
					$query = 'INSERT INTO `' . DB_PREFIX . 'item_menu`'
								. ' (`item_dbid`,`caption`,`url`)'
								. ' VALUES ('
									. ':db_id,'
									. ' :caption,'
									. ' :url'
								. ' );';
					$prep = $db->prepare($query);
					$prep->bindValue(':db_id', $_POST['db_id'], PDO::PARAM_INT);
					$prep->bindValue(':caption', $_POST[$i . '_actioncaption_add'], PDO::PARAM_STR);
					$prep->bindValue(':url', $_POST[$i . '_actionpage_add'], PDO::PARAM_STR);
					$prep->execute($db, __FILE__,__LINE__);
					$prep->closeCursor();
					$prep = NULL;
				}
			}
		}
	}
}

