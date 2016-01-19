<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Lieu_BanqueMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']) && !isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner une banque.', '?mj=Lieu_Banque',null,false);
		
		
		if (isset($_GET['id']))
			$_POST['id'] = $_GET['id'];
		
		
		
		if(isset($_POST['save']))
		{
			$ret = self::save();
			if($ret!==true)
				return $ret;
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Banque';</script>");
		}
		
		
		//Fetcher toutes les informations concernant la banque
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_id=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Déprotéger certains champs pour qu'ils soient affichés correctement
		$arr['banque_nom'] = stripslashes($arr['banque_nom']);
		$arr['banque_lieu'] = stripslashes($arr['banque_lieu']);
		
		$tpl->set('BANQUE',$arr);
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueAddmod.htm');
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Validation des champs
		if (!is_numeric($_POST['banque_no']) || strlen($_POST['banque_no'])!=4)
			return fctErrorMSG('Le numéro de banque doit être numérique de composé de 4 caractères (entre 1000 et 9999).', '?mj=Lieu_BanqueMod',array('id'=>$_POST['id']),false);
			
		if (!is_numeric($_POST['banque_frais_ouverture']))
			return fctErrorMSG('Le frais d\'ouverture doit être un entier.', '?mj=Lieu_BanqueMod',array('id'=>$_POST['id']),false);
			
		//Valider si le # de banque est déjà pris
		$query = 'SELECT banque_id'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE	banque_no=:banqueNo'
					. ' AND banque_id!=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$_POST['banque_no'],	PDO::PARAM_STR);
		$prep->bindValue(':banqueId',	$_POST['id'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr !== false)
			return fctErrorMSG('Le # ' . $_POST['banque_no'] . ' est déjà utilisé.', '?mj=Lieu_BanqueMod',array('id'=>$_POST['id']),false);
		
		
		//Valider si une banque existe déjà dans ce lieu
		$query = 'SELECT banque_id'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_lieu=:lieuTech'
					. ' AND banque_id!=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',	$_POST['banque_lieu'],	PDO::PARAM_STR);
		$prep->bindValue(':banqueId',	$_POST['id'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if($arr !== false)
			return fctErrorMSG('Une banque existe déjà dans ce lieu.', '?mj=Lieu_BanqueMod',array('id'=>$_POST['id']),false);
		
		
		
		
		//Trouver le # de la banque
		$query = 'SELECT banque_no'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_id=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueId',	$_POST['id'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if($arr === false)
			return fctErrorMSG('Cette banque est introuvable. (' . (int)$_POST['id'] . ')', '?mj=Lieu_BanqueMod',array('id'=>$_POST['id']),false);
		
		$banqueNo = $arr['banque_no'];
		
		//Si le # de banque à changer, modifier tous les comptes
		if($banqueNo!=$_POST['banque_no'])
		{
			$query = 'UPDATE ' . DB_PREFIX . 'banque_comptes'
					. ' SET	`compte_banque`=:newBanqueNo'
					. ' WHERE `compte_banque`=:oldBanqueNo;';
			$prep = $db->prepare($query);
			$prep->bindValue(':newBanqueNo',	$_POST['banque_no'],	PDO::PARAM_INT);
			$prep->bindValue(':oldBanqueNo',	$banqueNo,				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$query = 'UPDATE ' . DB_PREFIX . 'banque_cartes'
					. ' SET	`carte_banque`=:newBanqueNo'
					. ' WHERE `carte_banque`=:oldBanqueNo;';
			$prep = $db->prepare($query);
			$prep->bindValue(':newBanqueNo',	$_POST['banque_no'],	PDO::PARAM_INT);
			$prep->bindValue(':oldBanqueNo',	$banqueNo,				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$query = 'UPDATE ' . DB_PREFIX . 'banque_historique'
					. ' SET `compte`=CONCAT(:newBanqueNo, SUBSTRING(`compte`,5))'
					. ' WHERE `compte` LIKE :oldBanqueNo;';
			$prep = $db->prepare($query);
			$prep->bindValue(':newBanqueNo',	$_POST['banque_no'],	PDO::PARAM_INT);
			$prep->bindValue(':oldBanqueNo',	$banqueNo . '-%',		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		$query = 'UPDATE ' . DB_PREFIX . 'banque'
				. ' SET'
					. ' banque_lieu				= :lieuTech,'
					. ' banque_no				= :banqueNo,'
					. ' banque_nom				= :banqueNom,'
					. ' banque_retrait			= :retrait,'
					. ' banque_frais_ouverture	= :fraisOuverture,'
					. ' banque_telephone		= :telephone'
				. ' WHERE banque_id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',		$_POST['banque_lieu'],	PDO::PARAM_STR);
		$prep->bindValue(':banqueNo',		$_POST['banque_no'],	PDO::PARAM_INT);
		$prep->bindValue(':banqueNom',		$_POST['banque_nom'],	PDO::PARAM_STR);
		$prep->bindValue(':retrait',		$_POST['banque_retrait'],	PDO::PARAM_STR);
		$prep->bindValue(':fraisOuverture',	$_POST['banque_frais_ouverture'],	PDO::PARAM_INT);
		$prep->bindValue(':telephone',		$_POST['banque_telephone'],	PDO::PARAM_STR);
		$prep->bindValue(':id',				$_POST['id'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
			
		return true;
	}
}
