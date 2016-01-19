<?php
/** Gestion des caractéristiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionCaractDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une caractéristique.');
		
		
		//Rechercher si la caract est une famille ou une simple caract
		$query = 'SELECT id, catid'
				. ' FROM ' . DB_PREFIX . 'caract'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$cat = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($cat['catid']==0)
		{
			
			//Sélectionner les ID de toutes les compétences comprisent dans la catégorie
			$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'caract'
					. ' WHERE catid=:id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id', $cat['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			//Supprimer la caractéristique comprise dans la catégorie
			foreach($arrAll as &$caract)
				self::deleteCaract($caract['id']);
			
		}
		
		//Supprimer la caractéristique sélectionnée
		self::deleteCaract($_POST['id']);
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'Perso_GestionCaract');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);
	}
	
	
	private static function deleteCaract($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Supprimer les incompatibilités de la caractéristique.
		$query = 'DELETE FROM ' . DB_PREFIX . 'caract_incompatible'
				. ' WHERE	id1=:id'
					. ' OR  id2=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $id,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Supprimer la caractéristique
		$query = 'DELETE FROM ' . DB_PREFIX . 'caract'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $id,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//La retirer aux perso
		$query = 'DELETE FROM ' . DB_PREFIX . 'perso_caract'
				. ' WHERE caractid=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $id,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}

