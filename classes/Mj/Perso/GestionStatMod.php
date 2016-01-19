<?php
/** Gestion des statistiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionStatMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		



		if (isset($_POST['save']))
		{
			
			preg_match('/^[A-Za-z]{3}$/', $_POST['abbr'], $matches);
			if (count($matches)==0)
			{
				unset($_POST['save']);
				return fctErrorMSG('L\'abbréviation doit faire 3 caractères alpha.', '?mj=Perso_GestionStatMod',$_POST,false);
			}
			$query = 'SELECT nom'
					. ' FROM ' . DB_PREFIX . 'stat'
					. ' WHERE abbr=:abbr'
						. ' AND id!=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':abbr',	strtolower($_POST['abbr']),	PDO::PARAM_STR);
			$prep->bindValue(':id',		$_POST['id'],				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;

			if($arr!==false)
			{
				unset($_POST['save']);
				return fctErrorMSG('Cet abbréviation existe déjà pour: ' . mysql_result($result,0) . '.', '?mj=Perso_GestionStatMod',$_POST,false);
			}
			
			$query = 'UPDATE ' . DB_PREFIX . 'stat'
					. ' SET `nom` = :nom'
						. ' `abbr` = :abbr'
						. ' `description` = :description'
					. ' WHERE id =:id'
					. ' LIMIT 1';
			$prep = $db->prepare($query);
			$prep->bindValue(':nom',			$_POST['nom'],				PDO::PARAM_STR);
			$prep->bindValue(':abbr',			strtolower($_POST['abbr']),	PDO::PARAM_STR);
			$prep->bindValue(':description',	$_POST['description'],		PDO::PARAM_STR);
			$prep->bindValue(':id',				$_POST['id'],				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Retourner le template complété/rempli
			$tpl->set('PAGE', 'Perso_GestionStat');
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);

		}
		
		if(isset($_POST))
			fctStripMagicQuote($_POST);
		
		if(!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une stat.', '?mj=Perso_GestionStat',null,false);
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'stat'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',				$_POST['id'],				PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Impossible de trouver cet stat.', '?mj=Perso_GestionStat',null,false);
		
		
		
		$stat = array();
		$stat['id'] = $arr['id'];
		$stat['nom'] = isset($_POST['nom']) ? $_POST['nom'] : stripslashes($arr['nom']);
		$stat['abbr'] = isset($_POST['abbr']) ? $_POST['abbr'] : $arr['abbr'];
		$stat['description'] = isset($_POST['description']) ? $_POST['description'] : stripslashes($arr['description']);
		
		$tpl->set('STAT', $stat);
		$tpl->set('ADDMOD', 'Mod');
		$tpl->set('ADDMOD_TXT', 'Modifier');
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionStatAddMod.htm',__FILE__,__LINE__);


	}
}

