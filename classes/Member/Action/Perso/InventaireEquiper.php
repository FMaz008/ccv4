<?php
/** Gestion de l'action d'équiper un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Perso_InventaireEquiper
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
		
		if($perso->getMenotte())
			die($_POST['id'] . '|' . rawurlencode('Vous êtes menotté et cette action est trop complexe.'));
		
		
		//Trouver en inventaire l'item que l'on souhaite équiper
		$i=0; $item = null;
		while( $tmp = $perso->getInventaire($i++))
			if($tmp->getInvId() == $_POST['id'])
				$item = $tmp;
		
		if(empty($item))
			die($_POST['id'] . '|' . rawurlencode('Cet item (#' . $_POST['id'] . ') ne vous (id ' . $perso->getId() . ') appartiend pas. (cheat)'));
		
		
		//Tableau des positions possible. La clé=le nom de la position, La valeur=le nom de la classe de l'item qui va y être placé.
		$arrEquipPos = array(
						'dos'  => 'Member_ItemSac',
						'arme' => 'Member_ItemArme',
						'tete' => 'Member_ItemDefenseTete',
						'main' => 'Member_ItemDefenseMain',
						'torse'=> 'Member_ItemDefenseTorse',
						'jambe'=> 'Member_ItemDefenseJambe',
						'pied' => 'Member_ItemDefensePied',
						'bras' => 'Member_ItemDefenseBras'
						);
		$itemClass = false;
		foreach($arrEquipPos as $className)
			if($item instanceof $className)
				$itemClass=$className;
		if($itemClass===false)
			die($_POST['id'] . '|' . rawurlencode('Ce type d\'item ne peut pas être équipé.'));
		
		
		//Vérifier si un objet est déjà équipé à cette emplacement
		$i=0;
		while( $tmp = $perso->getInventaire($i++))
			if($tmp instanceof $itemClass && $tmp->isEquip())
				die($_POST['id'] . '|' . rawurlencode('Vous avez déjà un item équipé. Vous devez ranger l\'item équipé avant d\'en équiper un nouveau.'));
		
		//Vérifier si c'est une arme la résistance
		if($item instanceof Member_ItemArme && $item->getResistance() <= 0)
			die($_POST['id'] . '|' . rawurlencode('Vous ne pouvez pas vous équiper de cette arme, elle est trop endommagée pour être utilisée.'));
		
		//Équiper l'item
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_equip="1"'
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
		
		Member_He::add('System', $perso->getId(), 'equip', "Vous equipez votre [i]" . $item->getNom() . '[/i].');
		
		
		die($_POST['id'] . '|OK|' . $perso->getPa() . '|' . $perso->getPr()); //Tout est OK
	}
}
