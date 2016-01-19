<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Lieu_BanqueCompteCarteMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']) && !isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner une banque.', '?mj=Lieu_Banque');
		
		
		
		
		if(isset($_POST['save'])) // Enregistrer les modifications
		{
			self::save();
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_BanqueCompteCarte&id=" . $_GET['id'] . "';</script>");
		}
		
		
		//Trouver les # de banque+compte
		$query = 'SELECT compte_banque, compte_compte'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_id=:compteId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_GET['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$banque_no = $arr['compte_banque'];
		$compte_no = $arr['compte_compte'];
		
		
		//Fetcher toutes les informations concernant le compte
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE	carte_banque=:banqueNo'
					. ' AND carte_compte=:compte'
					. ' AND carte_id=:carteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$banque_no,		PDO::PARAM_INT);
		$prep->bindValue(':compte',		$compte_no,		PDO::PARAM_INT);
		$prep->bindValue(':carteId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if($arr === false)
			return fctErrorMSG('Carte non-trouvée.<br />' . $query);
		
		
		//Déprotéger certains champs pour qu'ils soient affichés correctement
		$arr['carte_nom'] = stripslashes($arr['carte_nom']);
		
		$tpl->set('CARTE',$arr);
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompteCarteAddmod.htm');
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if (!is_numeric($_POST['carte_nip']))
			return fctErrorMSG('Le NIP doit être un entier.', '?mj=Lieu_BanqueCompteCarteMod&id=' . $_GET['id'],null,false);
		
		
		//Valider si le # de banque+compte existe
		$query = 'SELECT compte_id'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banqueNo'
					. ' AND compte_compte=:compte;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['carte_banque'],		PDO::PARAM_INT);
		$prep->bindValue(':compte',		$_POST['carte_compte'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Le # de compte (' . $_POST['carte_banque'] . '-' . $_POST['carte_compte'] . ') est innexistant.', '?mj=Lieu_BanqueCompteCarteAdd&id=' . $_GET['id'],null,false);
		
		
		
		$query = 'UPDATE ' . DB_PREFIX . 'banque_cartes'
				. ' SET'
					. ' carte_banque=	:banqueNo,'
					. ' carte_compte=	:compte,'
					. ' carte_nom=		:nom,'
					. ' carte_nip=		:nip,'
					. ' carte_valid=	:valid'
				. ' WHERE carte_id=:carteId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['carte_banque'],		PDO::PARAM_INT);
		$prep->bindValue(':compte',		$_POST['carte_compte'],		PDO::PARAM_STR);
		$prep->bindValue(':nom',		$_POST['carte_nom'],		PDO::PARAM_STR);
		$prep->bindValue(':nip',		$_POST['carte_nip'],		PDO::PARAM_STR);
		$prep->bindValue(':valid',		$_POST['carte_valid'],		PDO::PARAM_STR);
		$prep->bindValue(':carteId',	$_POST['id'],				PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
}
