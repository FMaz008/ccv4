<?php
/** Gestion des caractéristiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionStatAdd
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
				return fctErrorMSG('L\'abbréviation doit faire 3 caractères alpha.', '?mj=Perso_GestionStatAdd',$_POST,false);
			}
			$query = 'SELECT nom'
					. ' FROM ' . DB_PREFIX . 'stat'
					. ' WHERE abbr=:abbr'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':abbr',	strtolower($_POST['abbr']),	PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			$result = $db->query($query, __FILE__, __LINE__);
			if($arr !== false)
			{
				unset($_POST['save']);
				return fctErrorMSG('Cet abbréviation existe déjà pour: ' . mysql_result($result,0) . '.', '?mj=Perso_GestionStatAdd',$_POST,false);
			}
			
			$query = 'INSERT INTO ' . DB_PREFIX . 'stat'
					. ' (`nom`, `abbr`, `description`)'
					. ' VALUES'
					. ' (:nom, :abbr, :description);';
			$prep = $db->prepare($query);
			$prep->bindValue(':nom',	$_POST['nom'],				PDO::PARAM_STR);
			$prep->bindValue(':abbr',	strtolower($_POST['abbr']),	PDO::PARAM_STR);
			$prep->bindValue(':description',	$_POST['description'],		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Retourner le template complété/rempli
			$tpl->set('PAGE', 'Perso_GestionStat');
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);

		}
		
		if(isset($_POST))
			fctStripMagicQuote($_POST);
		
		$stat = array();
		$stat['nom'] = isset($_POST['nom']) ? $_POST['nom'] : '';
		$stat['abbr'] = isset($_POST['abbr']) ? $_POST['abbr'] : '';
		$stat['description'] = isset($_POST['description']) ? $_POST['description'] : '';
		
		$tpl->set('STAT', $stat);
		$tpl->set('ADDMOD', 'Add');
		$tpl->set('ADDMOD_TXT', 'Ajouter');
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionStatAddMod.htm',__FILE__,__LINE__);


	}
}

