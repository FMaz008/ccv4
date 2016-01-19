<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Lieu_ProducteurMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']) && !isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un producteur.', '?mj=Lieu_Producteur',null,false);
		
		
		if (isset($_GET['id']))
			$_POST['id'] = $_GET['id'];
		
		
		
		if(isset($_POST['save']))
		{
			$ret = self::save();
			if($ret!==true)
				return $ret;
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Producteur';</script>");
		}
		
		
		//Fetcher toutes les informations concernant le producteur
		$query = 'SELECT p.*, l.nom_technique'
				. ' FROM ' . DB_PREFIX . 'producteur as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as l ON (l.id = p.lieuId)'
				. ' WHERE p.id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$tpl->set('PROD',$arr);
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/ProducteurAddmod.htm');
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Validation des champs
		if (!is_numeric($_POST['total_pa']))
			return fctErrorMSG('Le total des PA doit être un entier.', '?mj=Lieu_ProducteurMod',null,false);
		
		if (!is_numeric($_POST['pa_needed']))
			return fctErrorMSG('Le frais d\'ouverture doit être un entier.', '?mj=Lieu_ProducteurMod',null,false);
		
		if (!is_numeric($_POST['lieuId']) || $_POST['lieuId']==0)
			return fctErrorMSG('Vous devez sélectionner un lieu.', '?mj=Lieu_ProducteurMod',null,false);
		
		if (!is_numeric($_POST['pa_cash_ratio']) && !is_float($_POST['pa_cash_ratio']))
			return fctErrorMSG('Le ratio PA/Cash doit être un nombre décimal.', '?mj=Lieu_ProducteurAdd',null,false);
		
		if (!is_numeric($_POST['cash']))
			return fctErrorMSG('Le cash doit être un nombre entier.', '?mj=Lieu_ProducteurAdd',null,false);
		
		
		//Valider si une banque existe déjà dans ce lieu
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'producteur'
				. ' WHERE	lieuId=:lieuId'
					. ' AND id!=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['lieuId'],	PDO::PARAM_INT);
		$prep->bindValue(':id',		$_POST['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr !== false)
			return fctErrorMSG('Un producteur existe déjà dans ce lieu.', '?mj=Lieu_ProducteurMod',array('id'=>$_POST['id']),false);
		
		
		
		
		$query = 'UPDATE ' . DB_PREFIX . 'producteur'
				. ' SET'
					. ' lieuId		= :lieuId,'
					. ' cash		= :cash,'
					. ' pa_cash_ratio = :ratio,'
					. ' total_pa	= :total_pa,'
					. ' pa_needed	= :pa_needed'
				. ' WHERE id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',		$_POST['lieuId'],			PDO::PARAM_INT);
		$prep->bindValue(':cash',		$_POST['cash'],				PDO::PARAM_INT);
		$prep->bindValue(':ratio',		$_POST['pa_cash_ratio'],	PDO::PARAM_INT);
		$prep->bindValue(':total_pa',	$_POST['total_pa'],			PDO::PARAM_INT);
		$prep->bindValue(':pa_needed',	$_POST['pa_needed'],		PDO::PARAM_INT);
		$prep->bindValue(':id',			$_POST['id'],				PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		return true;
	}
}


