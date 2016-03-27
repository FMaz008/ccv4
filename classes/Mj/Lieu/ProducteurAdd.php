<?php
/** Gestion de l'interface d'ajout d'une producteurs
*
* @package Mj
*/
class Mj_Lieu_ProducteurAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
		
		if(isset($_POST['save']))
		{
			$ret = self::save();
			if($ret!==true)
				return $ret;
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Producteur';</script>");
			
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['lieuId'] = '';
		$arr['lieuNom'] = '';
		$arr['nom_technique'] = '';
		$arr['total_pa'] = 0;
		$arr['pa_needed'] = 0;
		$arr['cash'] = 0;
		$arr['pa_cash_ratio'] = 0.5;
		
		$tpl->set("PROD",$arr);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/ProducteurAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Validation des champs
		if (!is_numeric($_POST['total_pa']))
			return fctErrorMSG('Le total des PA doit être un entier.', '?mj=Lieu_ProducteurAdd',null,false);
		
		if (!is_numeric($_POST['pa_needed']))
			return fctErrorMSG('Les PA requis à la production doivent être un entier.', '?mj=Lieu_ProducteurAdd',null,false);
		
		if (!is_numeric($_POST['lieuId']) || $_POST['lieuId']==0)
			return fctErrorMSG('Vous devez sélectionner un lieu.', '?mj=Lieu_ProducteurAdd',null,false);
		
		if (!is_numeric($_POST['pa_cash_ratio']) && !is_float($_POST['pa_cash_ratio']))
			return fctErrorMSG('Le ratio PA/Cash doit être un nombre décimal.', '?mj=Lieu_ProducteurAdd',null,false);
		
		if (!is_numeric($_POST['cash']))
			return fctErrorMSG('Le cash doit être un nombre entier.', '?mj=Lieu_ProducteurAdd',null,false);
		
		
		//Valider si un producteur existe déjà dans ce lieu
		$query = 'SELECT COUNT(id) as nbr'
				. ' FROM ' . DB_PREFIX . 'producteur'
				. ' WHERE lieuId=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['lieuId'],	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr= $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;


		if($arr['nbr'] > 0)
			return fctErrorMSG('Un producteur existe déjà dans ce lieu.', '?mj=Lieu_ProducteurAdd',null,false);
		
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'producteur'
				. ' (lieuId, cash, pa_cash_ratio, total_pa, pa_needed)'
				. ' VALUES'
				. ' (:lieuId, :cash, :ratio, :totalPa, :pa);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['lieuId'],			PDO::PARAM_INT);
		$prep->bindValue(':cash',	$_POST['cash'],				PDO::PARAM_INT);
		$prep->bindValue(':ratio',	$_POST['pa_cash_ratio'],	PDO::PARAM_INT);
		$prep->bindValue(':totalPa',$_POST['total_pa'],			PDO::PARAM_INT);
		$prep->bindValue(':pa',		$_POST['pa_needed'],		PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		return true;
	}
}


