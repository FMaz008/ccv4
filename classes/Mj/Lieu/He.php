<?php
/** Affichage de l'historique d'évènement d'un lieu
*
* @package Mj
*/

class Mj_Lieu_He
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Validations de base
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		
		
		
		//Définition des variables utiles
		$id = (int)$_GET['id'];
		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
		$nbr = 50; // 50 items par page
		$from = ($page-1) * $nbr;
		
		
		//Trouver tout les messages du lieu
		$query= 'SELECT DISTINCT he.msg, he.date, he.id AS hid, he.type, p.nom, ft.fromto, ft.persoid, ft.name_complement, d.description, p.sexe, p.imgurl, ft.`show`'
				. ' FROM	('
						. ' SELECT msgid, `show`, persoid'
						. ' FROM '.DB_PREFIX.'he_fromto'
						. ' WHERE lieuid = :lieuId'
						//	. ' AND fromto="from"'
						. ' ORDER BY `msgid` DESC'
						. ' LIMIT :from, :nbr'
						. ' ) as sq'
				. ' LEFT JOIN '.DB_PREFIX.'he AS he ON (he.id=sq.msgid)'
				. ' LEFT JOIN '.DB_PREFIX.'he_fromto AS ft ON ( ft.msgid = he.id )'
				. ' LEFT JOIN '.DB_PREFIX.'perso_connu AS pc ON ( pc.persoid = sq.persoid AND pc.nomid = ft.persoid )'
				. ' LEFT JOIN '.DB_PREFIX.'perso AS p ON ( p.id = ft.persoid )'
				. ' LEFT JOIN '.DB_PREFIX.'he_description AS d ON (d.id = ft.id_description)'
				. ' ORDER BY he.`date` DESC, hid ASC, ft.fromto ASC , pc.nom ASC;';
		
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$id,	PDO::PARAM_INT);
		$prep->bindValue(':from',	$from,	PDO::PARAM_INT);
		$prep->bindValue(':nbr',	$nbr,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		//Bâtir un tableau d'objet HeMessage en fonction de la requête
		$heMsg = array();
		$i=0;
		$lastMsgId = -1; //Id du dernier message
		$lastPersoId = -1; //Id du dernier perso From/To
		//Créer le message et la liste de perso de/a.
		foreach($arrAll as &$arr)
		{
			if ($arr['hid']!=$lastMsgId) //Il s'agit d'un nouveau message
			{
				$heMsg[$i++] = new Member_HeMessage($perso, $arr);
				$lastMsgId = $arr['hid'];
				$lastPersoId = $arr['persoid'];
			}
			else
			{
				if ($lastPersoId != $arr['persoid'])
				{
					$heMsg[$i-1]->addFromTo($arr['id'], $arr);
					$lastPersoId = $arr['persoid'];
				}
			}
		}
		
		
		//Lister les messages
		$itemsSrc = '';
		foreach($heMsg as $msg)
		{
			$tpl->set('MSG',$msg);
			$itemsSrc .= $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/HeItem.htm',__FILE__,__LINE__);
		}
		
		
		//Trouver le nom du lieu
		$query = 'SELECT nom_affiche'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE id=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$id,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$lieuNom = stripslashes($arr['nom_affiche']);
		
		//Définir les variables du template d'affichage du HE de lieu
		$tpl->set('LIEU_NOM', $lieuNom);
		$tpl->set('HE_ITEMS', $itemsSrc);
		$tpl->set('LIEU_ID', $id);
		$tpl->set('PAGE', $page);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/He.htm',__FILE__,__LINE__);
	}
}
