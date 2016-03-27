<?php
/** Gestion de l'interface de gestion des producteurs
*
* @package Mj
*/

class Mj_Lieu_Producteur
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$query = 'SELECT p.*, COUNT(i.id) as items, l.nom_technique as nom_technique'
				. ' FROM ' . DB_PREFIX . 'producteur as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'producteur_inv as i ON (i.producteurId = p.id)'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as l ON (l.id = p.lieuId)'
				. ' GROUP BY p.id;';
		$prep = $db->prepare($query);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
					
		if (count($arrAll)>0)
		{
			//Lister tous les producteurs du jeu
			$tpl->set('PRODUCTEURS',$arrAll);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Producteur.htm',__FILE__,__LINE__);
	}
}

