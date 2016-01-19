<?php
/** Gestion de l'interface de l'inventaire du personnage
*
* @package Mj
*/


class Mj_Perso_Inventaire
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un personnage.');
			
		if(!is_numeric($_GET['id']))
			return fctErrorMSG('ID non numérique');
		
		
		//Trouver des informations sur le perso
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Ce personnage n\'existe pas.');
		
		$perso = new Member_Perso($arr);
		$tpl->set('PERSO', $perso);
		
		//Effectuer le rendu tableau de l'inventaire
		try
		{
			$arrItem = Member_ItemFactory::createFromPersoId($perso->getId(), true);
			
			$tableHTML = Mj_Item_Inventaire::generateInventaireTable($account, $arrItem, 'Perso_Inventaire', $_GET['id'], '800px');
			if(!empty($tableHTML))
				$tpl->set('ITEMS_TABLE',$tableHTML);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/Inventaire.htm',__FILE__,__LINE__);
	}
}
