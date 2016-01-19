<?php
/** Gestion de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Perso_InventaireItemAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!isset($_POST['itemId']))
			return fctErrorMSG('Vous devez sélectionner un item.', '?mj=Perso_Inventaire&id=' . $_GET['id'],null,false);
		
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'item_db'
				. ' WHERE db_id=:dbId'
				. ' LIMIT 1;';
		$prepSel = $db->prepare($query);

		$query = 'SELECT inv_id, inv_qte'
				. ' FROM ' . DB_PREFIX . 'item_inv'
				. ' WHERE inv_dbid=:dbId'
					. ' AND inv_persoid=:persoId'
				. ' LIMIT 1;';
		$prepSelP = $db->prepare($query);

		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_qte=inv_qte+:qte'
				. ' WHERE inv_id=:invId'
				. ' LIMIT 1;';
		$prepUpdP = $db->prepare($query);

		$query = 'INSERT INTO ' . DB_PREFIX . 'item_inv'
					. ' (`inv_id`, `inv_dbid`, `inv_persoid`, `inv_equip`,'
						. ' `inv_qte`, `inv_munition`, `inv_duree`,'
						. ' `inv_shock_pa`, `inv_shock_pv`, `inv_boost_pa`,`inv_boost_pv`,'
						. ' `inv_perc_stat_agi`, `inv_perc_stat_dex`, `inv_perc_stat_per`,'
						. ' `inv_perc_stat_for`, `inv_perc_stat_int`, `inv_resistance`,'
						. ' `inv_remiseleft`, `inv_pn`'
					. ' )'
					. ' VALUES'
					. ' ('
						. ' NULL, :dbId, :persoId, :equip, :qte, :munition, :duree,'
						. ' :shockPa, :shockPv, :boostPa, :boostPv,'
						. ' :statAgi, :statDex, :statPer, :statFor, :statInt,'
						. ' :resistance, NULL, :pn'
					. ' );';
		$prepIns = $db->prepare($query);

		
		foreach($_POST['itemId'] as $addItemId)
		{
			
			$prepSel->bindValue(':dbId',		$addItemId,		PDO::PARAM_INT);
			$prepSel->execute($db, __FILE__, __LINE__);
			$arr = $prepSel->fetch();
			
			
			if ($arr['db_regrouper']=='1')
			{
				$item_qte = $_POST['item' . $addItemId];
				$query_qte = 1;
				
				//Vérifier si le perso actuel possède déjà cet item, si oui: augmenter la qte.
				$prepSelP->bindValue(':dbId',		$addItemId,		PDO::PARAM_INT);
				$prepSelP->bindValue(':persoId',		$_GET['id'],	PDO::PARAM_INT);
				$prepSelP->execute($db, __FILE__, __LINE__);
				$arr2 = $prepSelP->fetch();
				
				if ($arr2 !== false)
				{
					//Augmenter la Qte
					$prepUpdP->bindValue(':qte',		$item_qte,			PDO::PARAM_INT);
					$prepUpdP->bindValue(':invId',	$arr2['inv_id'],	PDO::PARAM_INT);
					$prepUpdP->execute($db, __FILE__, __LINE__);
					
					$query_qte = 0; //Ne pas ajouter d'item avec la requête INSERT ci-dessous
				}
				
			}
			else
			{
				$item_qte = 1;
				$query_qte = (int)$_POST['item' . $addItemId];	
			}
			

			//Insérer un nouvel item
			$prepIns->bindValue(':dbId',	$addItemId,			PDO::PARAM_INT);
			$prepIns->bindValue(':persoId',	$_GET['id'],		PDO::PARAM_INT);

			if($arr['db_type'] == 'arme' || $arr['db_type'] == 'defense')
				$prepIns->bindValue(':equip',	0,						PDO::PARAM_INT);
			else
				$prepIns->bindValue(':equip',	NULL,					PDO::PARAM_NULL);

			$prepIns->bindValue(':qte',		$item_qte,			PDO::PARAM_INT);

			if($arr['db_soustype'] == 'arme_feu')
				$prepIns->bindValue(':munition',	0,							PDO::PARAM_INT);
			else
				$prepIns->bindValue(':munition',	NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_duree']))
				$prepIns->bindValue(':duree',		$arr['db_duree'],			PDO::PARAM_INT);
			else
				$prepIns->bindValue(':duree',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_shock_pa']))
				$prepIns->bindValue(':shockPa',		$arr['db_shock_pa'],		PDO::PARAM_INT);
			else
				$prepIns->bindValue(':shockPa',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_shock_pv']))
				$prepIns->bindValue(':shockPv',		$arr['db_shock_pv'],		PDO::PARAM_INT);
			else
				$prepIns->bindValue(':shockPv',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_boost_pv']))
				$prepIns->bindValue(':boostPv',		$arr['db_boost_pv'],		PDO::PARAM_INT);
			else
				$prepIns->bindValue(':boostPv',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_boost_pa']))
				$prepIns->bindValue(':boostPa',		$arr['db_boost_pa'],		PDO::PARAM_INT);
			else
				$prepIns->bindValue(':boostPa',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_perc_stat_agi']))
				$prepIns->bindValue(':statAgi',		$arr['db_perc_stat_agi'],	PDO::PARAM_INT);
			else
				$prepIns->bindValue(':statAgi',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_perc_stat_dex']))
				$prepIns->bindValue(':statDex',		$arr['db_perc_stat_dex'],	PDO::PARAM_INT);
			else
				$prepIns->bindValue(':statDex',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_perc_stat_per']))
				$prepIns->bindValue(':statPer',		$arr['db_perc_stat_per'],	PDO::PARAM_INT);
			else
				$prepIns->bindValue(':statPer',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_perc_stat_for']))
				$prepIns->bindValue(':statFor',		$arr['db_perc_stat_for'],	PDO::PARAM_INT);
			else
				$prepIns->bindValue(':statFor',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_perc_stat_int']))
				$prepIns->bindValue(':statInt',		$arr['db_perc_stat_int'],	PDO::PARAM_INT);
			else
				$prepIns->bindValue(':statInt',		NULL,						PDO::PARAM_NULL);

			if(isset($arr['db_resistance']))
				$prepIns->bindValue(':resistance',	$arr['db_resistance'],		PDO::PARAM_INT);
			else
				$prepIns->bindValue(':resistance',	NULL,						PDO::PARAM_NULL);

			if($arr['db_type'] == 'nourriture')
				$prepIns->bindValue(':pn',		$arr['db_pn'],					PDO::PARAM_INT);
			else
				$prepIns->bindValue(':pn',		NULL,							PDO::PARAM_NULL);

			
			$q=1;
			while ($q<=$query_qte)
			{
				$prepIns->execute($db, __FILE__, __LINE__);

				$q++;
			}
			
		}
		$prepSel->closeCursor();
		$prepSel = NULL;
		$prepSelP->closeCursor();
		$prepSelP = NULL;
		$prepUpdP->closeCursor();
		$prepUpdP = NULL;
		$prepIns->closeCursor();
		$prepIns = NULL;
					
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_Inventaire&id=' . $_GET['id'] );
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}

