<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Lieu_BanqueCompteMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']) && !isset($_GET['id'])){
			return fctErrorMSG('Vous devez sélectionner une banque.', '?mj=Lieu_Banque',null,false);
		}
		
		if (isset($_GET['id']))
			$_POST['id'] = $_GET['id'];
		
		
		
		
		if(isset($_POST['save']))
			self::save();
		
		
		
		//Fetcher toutes les informations concernant le compte
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' LEFT JOIN ' . DB_PREFIX . 'banque'
					. ' ON(banque_no=compte_banque)'
				. ' WHERE compte_id=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_POST['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		//Déprotéger certains champs pour qu'ils soient affichés correctement
		$tpl->set('BANK_ID', $arr['banque_id']);
		$tpl->set('COMPTE',$arr);
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompteAddmod.htm');
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!is_numeric($_POST['compte_nip']))
			return fctErrorMSG('Le NIP doit être un entier.', '?mj=Lieu_BanqueCompteMod',null,false);
			
		if (!is_numeric($_POST['compte_cash']))
			return fctErrorMSG('Le solde doit être un entier.', '?mj=Lieu_BanqueCompteMod',null,false);
			
		//Valider si le # de banque existe
		$query = 'SELECT compte_id'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_banque=:banqueNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['compte_banque'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Le # de banque (' . $_POST['compte_banque'] . ') est innexistant.', '?mj=Lieu_BanqueCompteAdd',null,false);
		

		
		$query = 'UPDATE ' . DB_PREFIX . 'banque_comptes'
				. ' SET'
					. ' compte_idperso=					:persoId,'
					. ' compte_nom=						:nom,'
					. ' compte_banque=					:banqueNo,'
					. ' compte_compte=					:compte,'
					. ' compte_cash=					:cash,'
					. ' compte_nip=						:nip,'
					. ' compte_auth_auto_transaction=	:compte_auth_auto_transaction'
				. ' WHERE compte_id=	:compteId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',						$_POST['compte_idperso'],				PDO::PARAM_INT);
		$prep->bindValue(':nom',							$_POST['compte_nom'],					PDO::PARAM_STR);
		$prep->bindValue(':banqueNo',						$_POST['compte_banque'],				PDO::PARAM_INT);
		$prep->bindValue(':compte',							$_POST['compte_compte'],				PDO::PARAM_STR);
		$prep->bindValue(':cash',							$_POST['compte_cash'],					PDO::PARAM_INT);
		$prep->bindValue(':nip',							$_POST['compte_nip'],					PDO::PARAM_STR);
		$prep->bindValue(':compteId',						$_POST['id'],							PDO::PARAM_INT);
		$prep->bindValue(':compte_auth_auto_transaction',	$_POST['compte_auth_auto_transaction'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}

