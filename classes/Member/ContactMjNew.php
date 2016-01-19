<?php
/** Gestion de l'interface d'une boutique
 *
 * @package Member
 * @subpackage Contact
 */
class Member_ContactMjNew
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$errorUrl = '?popup=1&m=ContactMj';
		
		
		if(!isset($_POST['type']) || !isset($_POST['titre']) || !isset($_POST['msg']))
			return fctErrorMSG('Données requises manquantes.', $errorUrl);
		
		if(empty($_POST['titre']))
			return fctErrorMSG('Vous devez remplir tout les champs (1).', $errorUrl);
			
		if(empty($_POST['msg']))
			return fctErrorMSG('Vous devez remplir tout les champs (2).', $errorUrl);
		
		
		$query = 'SELECT COUNT(*)'
				. ' FROM ' . DB_PREFIX . 'ppa'
				. ' WHERE	persoid=:persoId'
					. ' AND statut="ouvert";';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if($arr[0]>=PPA_MAX)
			return fctErrorMSG('Vous avez atteind votre maximum de ticket ouvert.', $errorUrl);
		
		
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'ppa'
				. ' (`persoid`, `type`, `date`, `mjid`, `titre`, `msg`, `lieu`, `pa`, `paMax`, `pv`, `pvMax`)'
				. ' VALUES'
				. ' (:persoId, :type, UNIX_TIMESTAMP(), 0, :titre, :msg, :lieu, :pa, :paMax, :pv, :pvMax);';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),					PDO::PARAM_INT);
		$prep->bindValue(':type',		$_POST['type'],						PDO::PARAM_STR);
		$prep->bindValue(':titre',		fctScriptProtect($_POST['titre']),	PDO::PARAM_STR);
		$prep->bindValue(':msg',		fctScriptProtect($_POST['msg']),	PDO::PARAM_STR);
		$prep->bindValue(':lieu',		$perso->getLieu()->getNomTech(),	PDO::PARAM_STR);
		$prep->bindValue(':pa',			$perso->getPa(),					PDO::PARAM_STR);
		$prep->bindValue(':paMax',		$perso->getPaMax(),					PDO::PARAM_STR);
		$prep->bindValue(':pv',			$perso->getPv(),					PDO::PARAM_STR);
		$prep->bindValue(':pvMax',		$perso->getPvMax(),					PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		
		
		//Copier le message dans les HE
		Member_He::add($perso->getId(), 'MJ', 'ppa', $_POST['msg']);
			
		//Retourner le template complété/rempli
		$tpl->set('PAGE', '?popup=1&m=ContactMj');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
	}
}

