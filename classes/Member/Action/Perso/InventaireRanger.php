<?php
/** Gestion de l'action d'équiper un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Perso_InventaireRanger
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$actionPa = 3;
		
		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
			die('00|' . rawurlencode('Vous devez sélectionner un item pour effectuer cette action.'));
			
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			die($_POST['id'] . '|' . rawurlencode('Votre n\'êtes pas en état d\'effectuer cette action.'));
		
		if($perso->getPa() <= $actionPa)
			die($_POST['id'] . '|' . rawurlencode('Vous n\'avez pas assez de PA pour effectuer cette action.'));
		
		
		
		
		//Trouver en inventaire l'item que l'on souhaite équiper
		$i=0;
		$item = null;
		while( $tmp = $perso->getInventaire($i++))
			if($tmp->getInvId() == $_POST['id'])
				$item = $tmp;
		
		if(empty($item))
			die($_POST['id'] . '|' . rawurlencode('Cet item (#' . $_POST['id'] . ') ne vous (id ' . $perso->getId() . ') appartiend pas. (cheat)'));
		
		

		
		//Ranger l'item
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_equip="0"'
				. ' WHERE inv_persoid=:persoId'
					. ' AND inv_id=:itemId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
		$prep->bindValue(':itemId',			$_POST['id'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$perso->refreshInventaire(); //Recalculer l'inventaire (les PR)		
		$perso->changePa('-', $actionPa);
		$perso->setPa();
		
		Member_He::add('System', $perso->getId(), 'ranger', "Vous rangez votre [i]" . $item->getNom() . '[/i].');
		
		die($_POST['id'] . '|OK|' . $perso->getPa() . '|' . $perso->getPr()); //Tout est OK
	}
}

