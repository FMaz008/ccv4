	<?php
/** Gestion de l'interface d'ajout pour les items de type drogue
*
* @package Mj
*/
class Mj_Item_DrogueAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{

		if(isset($_POST['save']))
		{
			self::save();
			die("<script>location.href='?mj=Item_Drogue';</script>");
			
		}
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		
		$arr['db_id'] = '';
		$arr['db_soustype'] = 'drogue_drogue';
		$arr['db_nom'] = '';
		$arr['db_desc'] = '';
		$arr['db_valeur'] = '5';
		$arr['db_img'] = 'none.gif';
		$arr['db_duree'] = '1';
		$arr['db_shock_pa'] = '0';
		$arr['db_shock_pv'] = '0';
		$arr['db_boost_pa'] = '0';
		$arr['db_boost_pv'] = '0';
		$arr['db_perc_stat_agi'] = '0';
		$arr['db_perc_stat_dex'] = '0';
		$arr['db_perc_stat_per'] = '0';
		$arr['db_perc_stat_for'] = '0';
		$arr['db_perc_stat_int'] = '0';
		$arr['db_resistance'] = '1';
		$arr['db_notemj'] = '';
						
		
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
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'item_db`'
					. ' ('
						. '`db_type`,'
						. ' `db_soustype`,'
						. ' `db_regrouper`,'
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
					. ')'
					. ' VALUES('
							.'"drogue",'
							. ' :db_soustype,'
							. ' "1",'
							. ' :db_nom,'
							. ' :db_desc,'
							. ' :db_valeur,'
							. ' :db_img,'
							. ' :db_duree,'
							. ' :db_shock_pa,'
							. ' :db_shock_pv,'
							. ' :db_boost_pa,'
							. ' :db_boost_pv,'
							. ' :db_perc_stat_agi,'
							. ' :db_perc_stat_dex,'
							. ' :db_perc_stat_per,'
							. ' :db_perc_stat_for,'
							. ' :db_perc_stat_int,'
							. ' :db_resistance,'
							. ' :db_notemj'
					. ');';
		$prep = $db->prepare($query);
		$prep->bindValue(':db_soustype', $_POST['db_soustype'], PDO::PARAM_STR);
		$prep->bindValue(':db_nom', $_POST['db_nom'], PDO::PARAM_STR);
		$prep->bindValue(':db_desc', $_POST['db_desc'], PDO::PARAM_STR);
		$prep->bindValue(':db_valeur', $_POST['db_valeur'], PDO::PARAM_INT);
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
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Trouver l'id de l'arme créé
		$query = "SELECT LAST_INSERT_ID()";
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$result = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		$db_id = $result[0];
		
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
										. ' :db_id,'
										. ' :actioncaption_add,'
										. ' :actionpage_add'
								. ' );';
					$prep = $db->prepare($query);
					$prep->bindValue(':db_id', $db_id, PDO::PARAM_INT);
					$prep->bindValue(':actioncaption_add', $_POST[$i . '_actioncaption_add'], PDO::PARAM_STR);
					$prep->bindValue(':actionpage_add', $_POST[$i . '_actionpage_add'], PDO::PARAM_STR);
					$prep->execute($db, __FILE__,__LINE__);
					$prep->closeCursor();
					$prep = NULL;
				}
			}
		}
	}
}

