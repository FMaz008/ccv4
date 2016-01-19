<?php
/** Gestion de l'interface d'ajout d'une banque
*
* @package Mj
*/
class Mj_Lieu_BanqueAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
		
		if(isset($_POST['save']))
		{
			$ret = self::save();
			if($ret!==true)
				return $ret;
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Banque';</script>");
			
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['banque_lieu'] = '';
		$arr['banque_no'] = '';
		$arr['banque_nom'] = '';
		$arr['banque_retrait'] = '0';
		$arr['banque_frais_ouverture'] = 0;
		$arr['banque_telephone'] = '0';
		
		
		$tpl->set("BANQUE",$arr);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Validation des champs
		if (!is_numeric($_POST['banque_no']) || strlen($_POST['banque_no'])!=4)
			return fctErrorMSG('Le numéro de banque doit être numérique de composé de 4 caractères (entre 1000 et 9999).', '?mj=Lieu_BanqueAdd',null,false);
			
		if (!is_numeric($_POST['banque_frais_ouverture']))
			return fctErrorMSG('Le frais d\'ouverture doit être un entier.', '?mj=Lieu_BanqueAdd',null,false);
			
		//Valider si le # de banque est déjà pris
		$query = 'SELECT banque_id'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_no=:banqueNo'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['banque_no'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr !== false)
			return fctErrorMSG('Le # ' . $_POST['banque_no'] . ' est déjà utilisé.', '?mj=Lieu_BanqueAdd',null,false);
		
		//Valider si une banque existe déjà dans ce lieu
		$query = 'SELECT banque_id'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_lieu=:lieuTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',	$_POST['banque_lieu'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr !== false)
			return fctErrorMSG('Une banque existe déjà dans ce lieu.', '?mj=Lieu_BanqueAdd',null,false);
		
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'banque'
				. ' (banque_lieu, banque_no, banque_nom, banque_retrait, banque_frais_ouverture, banque_telephone)'
				. ' VALUES'
				. ' (:lieuTech, :banqueNo, :nom, :retrait, :frais, :tel);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',	$_POST['banque_lieu'],		PDO::PARAM_STR);
		$prep->bindValue(':banqueNo',	$_POST['banque_no'],		PDO::PARAM_INT);
		$prep->bindValue(':nom',		$_POST['banque_nom'],		PDO::PARAM_STR);
		$prep->bindValue(':retrait',	$_POST['banque_retrait'],	PDO::PARAM_STR);
		$prep->bindValue(':frais',		$_POST['banque_frais_ouverture'],	PDO::PARAM_INT);
		$prep->bindValue(':tel',		$_POST['banque_telephone'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		return true;
	}
}
