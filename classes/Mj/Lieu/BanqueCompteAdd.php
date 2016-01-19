<?php
/** Gestion de l'interface d'ajout d'une banque
*
* @package Mj
*/
class Mj_Lieu_BanqueCompteAdd{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
	
		if(isset($_POST['save']))
		{
			self::save();
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_BanqueCompte';</script>");
		}

		$tpl->set('BANK_ID', (int)$_GET['bankId']);
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['compte_idperso'] = '';
		$arr['compte_nom'] = '';
		$arr['compte_banque'] = '';
		$arr['compte_compte'] = '';
		$arr['compte_cash'] = 0;
		$arr['compte_nip'] = '';
		$arr['compte_auth_auto_transaction'] = 0;
		
		
		$tpl->set("COMPTE",$arr);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompteAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!is_numeric($_POST['compte_nip']))
			return fctErrorMSG('Le NIP doit être un entier.', '?mj=Lieu_BanqueCompteMod',null,false);
		
		if (!is_numeric($_POST['compte_cash']))
			return fctErrorMSG('Le solde doit être un entier.', '?mj=Lieu_BanqueCompteAdd',null,false);
			
		//Valider si le # de banque existe
		$query = 'SELECT compte_id'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_banque=:compte;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',	$_POST['compte_banque'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Le # de banque (' . $_POST['compte_banque'] . ') est innexistant.', '?mj=Lieu_BanqueCompteAdd',null,false);
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'banque_comptes'
				. ' (compte_idperso, compte_nom, compte_banque, compte_compte, compte_cash, compte_nip, compte_auth_auto_transaction)'
				. ' VALUES '
				. ' (:persoId, :nom, :banqueNo, :compte, :cash, :nip, :compte_auth_auto_transaction);';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',						$_POST['compte_idperso'],					PDO::PARAM_INT);
		$prep->bindValue(':nom',							$_POST['compte_nom'],						PDO::PARAM_STR);
		$prep->bindValue(':banqueNo',						$_POST['compte_banque'],					PDO::PARAM_INT);
		$prep->bindValue(':compte',							$_POST['compte_compte'],					PDO::PARAM_STR);
		$prep->bindValue(':cash',							$_POST['compte_cash'],						PDO::PARAM_INT);
		$prep->bindValue(':nip',							$_POST['compte_nip'],						PDO::PARAM_STR);
		$prep->bindValue(':compte_auth_auto_transaction', 	$_POST['compte_auth_auto_transaction'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}

