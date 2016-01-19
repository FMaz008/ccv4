<?php
/** Gestion de l'inventaire du personnage
*
* @package Mj
*/

class Mj_Perso_InventaireItemMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
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
		
		if ($arr === false)
			return fctErrorMSG('perso innexistant');
			
		$perso = new Member_Perso($arr);
			
			
		//Lister l'inventaire
		$i=0; $e=0;
		$arrItem = array();
		while( $item = $perso->getInventaire($i++, true))
		{
			if
			(
				isset($_POST['inv' . $item->getInvId()]) &&
				is_numeric($_POST['inv' . $item->getInvId()]) &&
				$_POST['inv' . $item->getInvId()] != $item->getQte()
			)
			{
				$item->setQte($_POST['inv' . $item->getInvId()]);
			}
		}
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_Inventaire&id=' . $_GET['id'] );
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
}
