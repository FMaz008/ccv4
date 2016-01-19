<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Lieu_Coupdoeil
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$lieuId = (int)$_GET['id'];

		
		
		//trouvé le nom normal à partir du technique
		$query = 'SELECT nom_affiche'
				. ' FROM `' . DB_PREFIX . 'lieu`'
				. ' WHERE id=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$lieuId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($arr === false)
			return fctErrorMSG('Lieu innexistant.');
			
		
		$tpl->set('SHOW_LIEU',$arr['nom_affiche']);
		
		
		//rechercher la description
		$query  =  'SELECT description, image'
					. ' FROM ' . DB_PREFIX . 'lieu'
					. ' WHERE id = :lieuId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$lieuId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		
		if (!empty($arr['image']))
		{
			$tpl->set("SHOW_LIEU_IMG",$arr['image']);
		}
			
		$tpl->set("SHOW_LIEU_DESC",stripslashes($arr['description']));
		
		
		
		// Description PERSO
		$query = 'SELECT nom_technique'
				. ' FROM `' . DB_PREFIX . 'lieu`'
				. ' WHERE id=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$lieuId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$lieuTech = $arr['nom_technique'];
		$tpl->set('SHOW_LIEU', $lieuTech);
		
		
		//finalement je met SELECT *, car il y a pas mal de chose à charger.
		$query = 'SELECT *'
						. ' FROM ' . DB_PREFIX . 'perso'
						. ' WHERE lieu = :lieuTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',	$lieuTech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$PERSO_LIST = array();
		foreach($arrAll as &$arr)
		{
			$arr['description'] = stripslashes($arr['description']);
			$PERSO_LIST[]= $arr; 
		}
		
		//arme + état
		if (isset($PERSO_LIST))
		{
			$query = 'SELECT db_nom'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id=inv_dbid)'
					. ' WHERE inv_equip="1"' 
						. ' AND db_type="arme"'
						. ' AND inv_persoid=:persoId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			
			foreach($PERSO_LIST as &$p)
			{
				//A optimiser
				$prep->bindValue(':persoId',	$p['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$arr = $prep->fetch();
				
				
				$p['arme'] = stripslashes($arr['db_nom']);
				// $p["etat"] = fct_etatperso($p["id"],"text");
			}
			$prep->closeCursor();
			$prep = NULL;
		} 
		$tpl->set('PERSO_LIST',$PERSO_LIST);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Coupdoeil.htm');
	}
}
