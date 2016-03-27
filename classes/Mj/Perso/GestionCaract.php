<?php
/** Gestion des caractéristiques du jeu
*
* @package Mj
*/

class Mj_Perso_GestionCaract
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	//BUT: Démarrer un template propre à cette page
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		

		//Établir la liste de signes disponibles.
		$query = 'SELECT *
					FROM ' . DB_PREFIX . 'caract
					WHERE type="system"
						AND catid=0
					ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$cat = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		foreach($cat as &$elem)
		{
			$elem['nom'] = stripslashes($elem['nom']);
			$elem['desc'] = stripslashes($elem['desc']);
		}
		$tpl->set('CAT', $cat);


		
		//Lister les caracts
		$query = 'SELECT *
					FROM ' . DB_PREFIX . 'caract
					WHERE type="system"
						AND catid>0
					ORDER BY nom;';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$caract = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		foreach($caract as &$elem)
		{
			$elem['nom'] = stripslashes($elem['nom']);
			$elem['desc'] = stripslashes($elem['desc']);
		}
		
		$tpl->set('CARACT', $caract);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/GestionCaract.htm',__FILE__,__LINE__);
	}
}

