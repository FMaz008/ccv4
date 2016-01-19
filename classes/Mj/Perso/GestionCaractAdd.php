<?php
/** Gestion des caractéristiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionCaractAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		



		if (isset($_POST['save']))
		{
			if(!isset($_POST['catId']))
				return fctErrorMSG('Vous devez sélectionner une catégorie.', '?mj=Perso_GestionCaract',null,false);
			
			$query = 'INSERT INTO ' . DB_PREFIX . 'caract'
					. ' (`catid`, `type`, `nom`, `desc` )'
					. ' VALUES'
					. ' (:catId, :type, :nom, :desc);';
			$prep = $db->prepare($query);
			$prep->bindValue(':catId',		$_POST['catId'],	PDO::PARAM_INT);
			$prep->bindValue(':type',		$_POST['type'],		PDO::PARAM_STR);
			$prep->bindValue(':nom',		$_POST['nom'],		PDO::PARAM_STR);
			$prep->bindValue(':desc',		$_POST['desc'],		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;

			$caractId = $db->lastInsertId();
			
			//Insertion de nouvelles incompatibilités associées au lieu
			if ($_POST['total_incompatible_add']>0)
			{
				
				$query = 'INSERT INTO ' . DB_PREFIX . 'caract_incompatible'
						. ' (`id1`,`id2`)'
						. ' VALUES'
						. ' (:caractId, :incompatId);';
				$prep = $db->prepare($query);
				
				for($i=1;$i<=$_POST['total_incompatible_add'];$i++)
				{
					if (!empty($_POST[$i . '_incompatible_add']))
					{
						$prep->bindValue(':caractId',	$caractId,							PDO::PARAM_INT);
						$prep->bindValue(':incompatId',	$_POST[$i . '_incompatible_add'],	PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
					}
				}
				$prep->closeCursor();
				$prep = NULL;
			}
			
			//Retourner le template complété/rempli
			$tpl->set('PAGE', 'Perso_GestionCaract');
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/redirect.htm',__FILE__,__LINE__);

		}


		//Établir la liste de signes disponibles.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'caract'
				. ' WHERE type="system"'
					. ' AND catid=0'
				. ' ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$cat = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		foreach($cat as &$elem)
		{
			$elem['nom'] = stripslashes($elem['nom']);
			$elem['desc'] = stripslashes($elem['desc']);
		}
		$tpl->set('CAT', $cat);

		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionCaractAdd.htm',__FILE__,__LINE__);


	}
}

