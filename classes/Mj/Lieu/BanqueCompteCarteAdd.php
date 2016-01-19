<?php
/** Gestion de l'interface d'ajout d'une banque
*
* @package Mj
*/
class Mj_Lieu_BanqueCompteCarteAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(isset($_POST['save']))
		{
			self::save();
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_BanqueCompteCarte&id=" . $_GET['id'] . "';</script>");
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Aucun id de compte spécifié.');
		
		
		//Trouver les # de banque+compte
		$query = 'SELECT compte_banque, compte_compte'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_id=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		$banque_no = $arr['compte_banque'];
		$compte_no = $arr['compte_compte'];
		
		$tpl->set('COMPTE', $banque_no . '-' . $compte_no);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['carte_id'] = '';
		$arr['carte_banque'] = $banque_no;
		$arr['carte_compte'] = $compte_no;
		$arr['carte_nom'] = '';
		$arr['carte_nip'] = '';
		$arr['carte_valid'] = 1;
		
		
		$tpl->set("CARTE",$arr);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompteCarteAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!is_numeric($_POST['carte_nip']))
			return fctErrorMSG('Le NIP doit être un entier.', '?mj=Lieu_BanqueCompteMod',null,false);
		
			
		//Valider si le # de banque+compte existe
		$query = 'SELECT compte_id'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banqueNo'
					. ' AND compte_compte=:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['carte_banque'],	PDO::PARAM_INT);
		$prep->bindValue(':compte',		$_POST['carte_compte'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Le # de compte (' . $_POST['carte_banque'] . '-' . $_POST['carte_compte'] . ') est innexistant.', '?mj=Lieu_BanqueCompteCarteAdd&id=' . $_GET['id'],null,false);
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'banque_cartes'
				. ' (carte_banque, carte_compte, carte_nom, carte_nip, carte_valid)'
				. ' VALUES'
				. ' (:banqueNo, :compte, :nom, :nip, :valid);';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['carte_banque'],	PDO::PARAM_INT);
		$prep->bindValue(':compte',		$_POST['carte_compte'],	PDO::PARAM_STR);
		$prep->bindValue(':nom',		$_POST['carte_nom'],	PDO::PARAM_STR);
		$prep->bindValue(':nip',		$_POST['carte_nip'],	PDO::PARAM_STR);
		$prep->bindValue(':valid',		$_POST['carte_valid'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
	}
}
