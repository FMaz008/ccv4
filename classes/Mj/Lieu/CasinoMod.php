<?php
/** Gestion de l'interface de modification des casino
*
* @package Mj
*/
class Mj_Lieu_CasinoMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']) && !isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un casino.', '?mj=Lieu_Casino',null,false);
		
		
		if (isset($_GET['id']))
			$_POST['id'] = $_GET['id'];
		
		
		
		if(isset($_POST['save']))
		{
			$ret = self::save();
			if($ret!==true)
				return $ret;
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Casino';</script>");
		}
		
		
		//Fetcher toutes les informations concernant la banque
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'casino'
				. ' WHERE casino_id=:casinoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':casinoId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$casino = new Member_Casino($arr);
		
		
		
		$tpl->set('CASINO',$casino);
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/CasinoAddmod.htm');
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Valider si une banque existe déjà dans ce lieu
		$query = 'SELECT casino_id'
				. ' FROM ' . DB_PREFIX . 'casino'
				. ' WHERE casino_lieu=:casinoLieu'
					. ' AND casino_id!=:casinoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':casinoLieu',	$_POST['casino_lieu'],	PDO::PARAM_STR);
		$prep->bindValue(':casinoId',	$_POST['id'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr !== false)
			return fctErrorMSG('Un casino existe déjà dans ce lieu.', '?mj=Lieu_CasinoMod',array('id'=>$_POST['id']),false);
		
		
		
		$query = 'UPDATE ' . DB_PREFIX . 'casino'
				. ' SET'
					. ' casino_lieu	= :lieu,'
					. ' casino_nom	= :nom,'
					. ' casino_cash	= :cash'
				. ' WHERE casino_id=:casinoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieu',	$_POST['casino_lieu'],	PDO::PARAM_STR);
		$prep->bindValue(':nom',	$_POST['casino_nom'],	PDO::PARAM_STR);
		$prep->bindValue(':cash',	$_POST['casino_cash'],	PDO::PARAM_INT);
		$prep->bindValue(':casinoId',	$_POST['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		return true;
	}
}


