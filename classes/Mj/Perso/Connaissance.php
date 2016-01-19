<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Perso_Connaissance
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Trouver le nom du perso dont nous avons l'ID (requête pour pseudo de $_GET['id'])
		$query = 'SELECT * FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id= :id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$bdd = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		$tpl->set('pseudo',$bdd['nom']);
		$tpl->set('id_get',$_GET['id']);
		
		
		
		//modification ?
		if (isset($_POST['save']))
		{
			$query = 'SELECT id'
					. ' FROM ' . DB_PREFIX . 'perso_connu'
					. ' WHERE persoid=:id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',		$_GET['id'],			PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			//Parcourir toutes les connaissances déjà enregistrés, puis vérifier s'il y a eu des modifications
			$query = 'DELETE FROM ' . DB_PREFIX . 'perso_connu'
						. ' WHERE id=:id'
						. ' LIMIT 1;';
			$prepDel = $db->prepare($query);
			
			$query = 'UPDATE ' . DB_PREFIX . 'perso_connu'
						. ' SET nom=:nom'
						. ' WHERE id=:id'
						. ' LIMIT 1;';
			$prepUpd = $db->prepare($query);
			
			foreach($arrAll as &$infoConn)
			{
				if (isset($_POST[$infoConn['id']]))
				{
					$nom = $_POST[$infoConn['id']];
					
					if (empty($nom))
					{
						$prepDel->bindValue(':id',	$infoConn['id'],	PDO::PARAM_INT);
						$prepDel->execute($db, __FILE__, __LINE__);
					}
					else
					{
						$prepUpd->bindValue(':nom',	$nom,				PDO::PARAM_STR);
						$prepUpd->bindValue(':id',	$infoConn['id'],	PDO::PARAM_INT);
						$prepUpd->execute($db, __FILE__, __LINE__);
					}
		
		
				}
			}
			$prepDel->closeCursor();
			$prepDel = NULL;
			$prepUpd->closeCursor();
			$prepUpd = NULL;
		}
		

		
		
		//Trouver les informations sur toutes les connaissances actuelles
		$query = 'SELECT p.nom as vrai_nom, pc.nom, pc.nomid, pc.id'
				. ' FROM ' . DB_PREFIX . 'perso_connu as pc'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id=pc.nomid)'
				. ' WHERE persoid=:id'
				. ' ORDER BY nomid;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$donnee = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set("connu",$donnee);
		
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Perso/Connaissance.htm');
	}
}
