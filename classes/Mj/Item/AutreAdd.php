<?php
/** Gestion de l'interface pour l'ajout d'item de type autre
*
* @package Mj
*/
class Mj_Item_AutreAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
	

		if(isset($_POST['save']))
		{
			self::save();
			die('<script>location.href="?mj=Item_Autre";</script>');
		}
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		$arr['db_id'] = '';
		$arr['db_nom'] = '';
		$arr['db_desc'] = '';
		$arr['db_valeur'] = '0';
		$arr['db_img'] = 'none.gif';
		$arr['db_pr'] = '1';
		$arr['db_resistance'] = '1';
		$arr['db_notemj'] = '';
						
		
		$tpl->set('ITEM',$arr);
		

		//lister le dossier d'image
		$dir2 = dir($account->getSkinRemotePhysicalPath() . "../_common/items/");
		$counter=0;
		$arrurl = array();
		$arr=array();
		while ($url = $dir2->read()) {
			$arrurl[$counter]=$url;
			$counter++;
		}
		natcasesort($arrurl);
		$arrurl = array_values($arrurl);
		for ($i=0;$i<count($arrurl);$i++)
			if ($arrurl[$i]!='' && substr($arrurl[$i],0,1)!='.')
				$arr[$i] = $arrurl[$i];
		
		
		$tpl->set('IMGS',$arr);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Item/Autre_Addmod.htm');
	}
	
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if (empty($_POST['db_valeur'])){ $_POST['db_valeur'] = 0; }
		if (empty($_POST['db_pr'])){ $_POST['db_pr'] = 0; }
		if (empty($_POST['db_resistance'])){ $_POST['db_resistance'] = 0; }
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'item_db`'
					. ' ('
						. '`db_type`,'
						. ' `db_nom`,'
						. ' `db_desc`,'
						. ' `db_valeur`,'
						. ' `db_img`,'
						. ' `db_pr`,'
						. ' `db_resistance`,'
						. ' `db_notemj`'
					. ')'
					. ' VALUES('
							.'"autre",'
							. ' :db_nom,'
							. ' :db_desc,'
							. ' :db_valeur,'
							. ' :db_img,'
							. ' :db_pr,'
							. ' :db_resistance,'
							. ' :db_notemj'
					. ');';
		$prep = $db->prepare($query);
		$prep->bindValue(':db_nom', $_POST['db_nom'], PDO::PARAM_STR);
		$prep->bindValue(':db_desc', $_POST['db_desc'], PDO::PARAM_STR);
		$prep->bindValue(':db_valeur', $_POST['db_valeur'], PDO::PARAM_INT);
		$prep->bindValue(':db_img', $_POST['db_img'], PDO::PARAM_STR);
		$prep->bindValue(':db_pr', $_POST['db_pr'], PDO::PARAM_INT);
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

