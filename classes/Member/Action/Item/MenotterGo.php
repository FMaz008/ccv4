<?php
/** Activation du menottage
*
* Note: Une menotte équipée signifie qu'elle est en utilisation. Le joueur menotté à le inv_id d'inscrit dans le champ menotte de la table perso.
* @package Member_Action
*/
class Member_Action_Item_MenotterGo
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!isset($_GET['choix']))
			return fctErrorMSG('Paramêtre requis manquant(1).');
			
		if(!isset($_GET['id']))
			return fctErrorMSG('Paramêtre requis manquant(2).');
		
		$menId = (int)$_GET['id'];
		
		
		//Vérifier que la demande de menottage est bien réelle
		$query = 'SELECT inv_id'
					. ' FROM ' . DB_PREFIX . 'perso_menotte'
					. ' WHERE inv_id=:itemId'
						. ' AND to_id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',			$menId,				PDO::PARAM_INT);
		$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr === false)
			return fctErrorMSG('Cette demande de menottage est expirée.');
			
		
		//Vérifier que personne d'autre n'est menotté avec cette paire.
		$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'perso'
					. ' WHERE menotte=:itemId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',			$menId,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr !== false)
			return fctErrorMSG('Ces menottes ou attaches sont déjà en cours d\'utilisation sur une autre personne(1).');
		
		
		
		//Trouver à qui appartiennent les menottes
		$query = 'SELECT inv_persoid, inv_equip'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' WHERE inv_id=:itemId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',			$menId,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrMen = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if ($arrMen === false)
			return fctErrorMSG('L\'item avec lequel la demande avait été faite n\'est plus en jeu.');

		if($arrMen['inv_equip']==1)
			return fctErrorMSG('Ces menottes ou attaches sont déjà en cours d\'utilisation sur une autre personne(2).');
		
		
		$from_id = (int)$arrMen['inv_persoid'];
		if($_GET['choix']==1)
		{
			//Accepter d'être menotté
			
			//Activer le menottage
			$query = 'UPDATE ' . DB_PREFIX . 'perso'
						. ' SET menotte=:itemId'
						. ' WHERE id=:persoId'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':itemId',			$menId,				PDO::PARAM_INT);
			$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		
			//Equiper l'item pour éviter un double menottage
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
						. ' SET inv_equip="1"'
						. ' WHERE inv_id=:itemId'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':itemId',			$menId,				PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$msg = 'La demande de menottage à été acceptée.';
			
		}
		else
		{
			//Refuser d'être menotté
			
			$msg = 'La demande de menottage à été refusée.';
			
		}
		
		//Supprimer la demande de menottage
		$query = 'DELETE FROM ' . DB_PREFIX . 'perso_menotte'
					. ' WHERE inv_id =:itemId'
						. ' AND to_id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':itemId',			$menId,				PDO::PARAM_INT);
		$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		Member_He::add($perso->getId(), $from_id, 'menotte', $msg);
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
