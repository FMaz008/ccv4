<?php
/** Gestion des caractéristiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionCaractMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une caractéristique.');
		
		
		
		if (isset($_POST['save']))
		{
			
			//Supprimer les caractéristiques cochées
			$itemId = (int)$_POST['id'];
			if(isset($_POST['del']))
			{
				$query = 'DELETE'
						. ' FROM ' . DB_PREFIX . 'caract_incompatible'
						. ' WHERE  (id1=:delId1 AND id2=:caractId1)'
							. ' OR (id2=:delId2 AND id1=:caractId2)'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				foreach($_POST['del'] as $del)
				{
					
					$prep->bindValue(':delId1', 	$del,		PDO::PARAM_INT);
					$prep->bindValue(':delId2', 	$del,		PDO::PARAM_INT);
					$prep->bindValue(':caractId1',	$itemId,	PDO::PARAM_INT);
					$prep->bindValue(':caractId2',	$itemId,	PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
				}
				$prep->closeCursor();
				$prep = NULL;
			}
			
			//Insertion de nouvelles incompatibilités associées au lieu
			if ($_POST['total_incompatible_add']>0)
			{
				$query = 'INSERT INTO ' . DB_PREFIX . 'caract_incompatible'
						. ' (`id1`,`id2`)'
						. ' VALUES'
						. ' (:id1, :id2);';
				$prep = $db->prepare($query);
				for($i=1;$i<=$_POST['total_incompatible_add'];$i++)
				{
					if (!empty($_POST[$i . '_incompatible_add']))
					{
						$prep->bindValue(':id1',	$itemId,							PDO::PARAM_INT);
						$prep->bindValue(':id2',	$_POST[$i . '_incompatible_add'],	PDO::PARAM_INT);
						$prep->execute($db, __FILE__, __LINE__);
					}
				}
				$prep->closeCursor();
				$prep = NULL;
			}
			
			//modification
			$query = 'UPDATE ' . DB_PREFIX . 'caract'
					. ' SET'
						. ' `catid`=:catId,'
						. ' `type`=:type,'
						. ' `nom`=:nom,'
						. ' `desc`=:desc'
					. ' WHERE id=:id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':catId',	$_POST['catId'],	PDO::PARAM_INT);
			$prep->bindValue(':type',	$_POST['type'],		PDO::PARAM_STR);
			$prep->bindValue(':nom',	$_POST['nom'],		PDO::PARAM_STR);
			$prep->bindValue(':desc',	$_POST['desc'],		PDO::PARAM_STR);
			$prep->bindValue(':id',		$_POST['id'],		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
		
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
		
		
		
		
		//Fetcher les informations des caractéristiques particulières
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'caract'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		$arr['nom'] = stripslashes($arr['nom']);
		$arr['desc']= stripslashes($arr['desc']);

		$tpl->set('CARACT', $arr);
		
		
		
		//Fetcher les caractéristiques incompatibles
		$e=0;
		$arrIncompatible = array();
		$query = 'SELECT id2 as id,nom'
				. ' FROM ' . DB_PREFIX . 'caract_incompatible'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract ON (id=id2)'
				. ' WHERE id1=:id1'
				. ' UNION'
				. ' SELECT id1 as id,nom'
				. ' FROM ' . DB_PREFIX . 'caract_incompatible'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract ON (id=id1)'
				. ' WHERE id2=:id2;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id1', $_POST['id'],	PDO::PARAM_INT);
		$prep->bindValue(':id2', $_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrIncompatible = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('CARACT_INCOMPATIBLE', $arrIncompatible);
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionCaractMod.htm',__FILE__,__LINE__);
	}
}

