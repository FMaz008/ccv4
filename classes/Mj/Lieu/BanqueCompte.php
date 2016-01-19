<?php
/** Gestion de l'interface de gestion des banques
*
* @package Mj
*/

class Mj_Lieu_BanqueCompte
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Compte non spécifié.');
		
		//Vérifier quel est le nom de la banque
		$query = 'SELECT banque_no, banque_nom'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_id=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if ($arr === false)
			return fctErrorMSG('Banque non-trouvée.');
		
		$tpl->set('BANK_NAME', $arr['banque_nom']);
		$tpl->set('BANK_ID', (int)$_GET['id']);
		
		
		//Charger la liste des comptes
		$query = 'SELECT c.*, p.nom as compte_perso'
				. ' FROM ' . DB_PREFIX . 'banque_comptes as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = c.compte_idperso)'
				. ' WHERE c.compte_banque=:banqueNo'
				. ' ORDER BY compte_cash DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$arr['banque_no'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($arrAll)>0)
		{
			//Lister toutes les banques du jeu
			$arrBanque = array();
			foreach($arrAll as &$arr)
			{
				if($arr['compte_cash'] != -1)
					$arr['compte_cash'] = fctCreditFormat($arr['compte_cash'], true);
				else
					$arr['compte_cash'] = "illimité";
				$arrBanque[] = $arr;
			}
			$tpl->set('COMPTES',$arrBanque);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompte.htm',__FILE__,__LINE__);
	}
}
