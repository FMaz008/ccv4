<?php
/** Gestion de l'interface d'ajout d'un casino
*
* @package Mj
*/
class Mj_Lieu_CasinoAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
		
		if(isset($_POST['save']))
		{
			$ret = self::save();
			if($ret!==true)
				return $ret;
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Casino';</script>");
			
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['casino_id'] = 0;
		$arr['casino_lieu'] = '';
		$arr['casino_nom'] = '';
		$arr['casino_cash'] = 0;
		$casino = new Member_Casino($arr);
		
		$tpl->set("CASINO",$casino);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/CasinoAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Valider si un casino existe déjà dans ce lieu
		$query = 'SELECT casino_id'
				. ' FROM ' . DB_PREFIX . 'casino'
				. ' WHERE casino_lieu=:casinoLieu'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':casinoLieu',	$_POST['casino_lieu'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr !== false)
			return fctErrorMSG('Un casino existe déjà dans ce lieu.', '?mj=Lieu_CasinoAdd',null,false);
		
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'casino'
				. ' (casino_lieu, casino_nom, casino_cash)'
				. ' VALUES'
				. ' (:lieu, :nom, :cash);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieu',	$_POST['casino_lieu'],	PDO::PARAM_STR);
		$prep->bindValue(':nom',	$_POST['casino_nom'],	PDO::PARAM_STR);
		$prep->bindValue(':cash',	$_POST['casino_cash'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		return true;
	}
}



