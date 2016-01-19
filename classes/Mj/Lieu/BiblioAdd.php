<?php
/** Gestion d'un casier (page de modification)
*
* @package Mj
*/

class Mj_Lieu_BiblioAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		
		//Si le lieu est spécifié, le passer au template pour créer un lien de retour
		if(!isset($_POST['LIEU_ID']))
			return fctErrorMSG('Aucun lieu spécifié.');
		
		$tpl->set('LIEU_ID', $_POST['LIEU_ID']);
		
		
		
		
		//## Sauvegarder les modifications ?
		if(isset($_POST['save']))
			return self::save();
		
		
		
		//## Afficher la liste des livres
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'item_db'
				. ' WHERE db_type="livre";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
	
		if(count($arrAll)>0)
		{
			$LIVRES = array();
			foreach($arrAll as &$arr)
			{
				$arr['inv_extradesc'] = '';
				$arr['inv_equip'] = NULL;
				$arr['inv_cacheno'] = NULL;
				$arr['inv_cachetaux'] = NULL;
				$arr['inv_resistance'] = NULL;
				$LIVRES[] = new Member_ItemLivre($arr);
			}
			$tpl->set('LIVRES', $LIVRES);
		}
		
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BiblioAdd.htm',__FILE__,__LINE__);
	}
	
	
	
	private static function save()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!isset($_POST['LIVRE_ID']))
			return fctErrorMSG('Aucun livre à ajouter selectionné.', '?mj=Lieu_BiblioAdd', array('LIEU_ID'=>$_POST['LIEU_ID']),false);
		
		//Vérifier si le livre est déjà existant
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu_livre'
				. ' WHERE	lieuId=:lieuId'
					. ' AND itemDbId=:dbId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['LIEU_ID'],	PDO::PARAM_INT);
		$prep->bindValue(':dbId',	$_POST['LIVRE_ID'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr != false)
			return fctErrorMSG('Ce livre est déjà présent dans la bibliothèque.', '?mj=Lieu_BiblioAdd', array('LIEU_ID'=>$_POST['LIEU_ID']),false);
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'lieu_livre'
				. ' (lieuId, itemDbId)'
				. ' VALUES'
				. ' (:lieuId, :dbId);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['LIEU_ID'],	PDO::PARAM_INT);
		$prep->bindValue(':dbId',	$_POST['LIVRE_ID'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(isset($_POST['LIEU_ID']))
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Biblio&id=" . $_POST['LIEU_ID'] . "';</script>");
		else
			die('Sauvegardé.');
	}
}
