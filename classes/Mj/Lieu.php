<?php
/** Gestion des lieux
*
* @package Mj
*/
class Mj_Lieu
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (isset($_GET['id']))
			$_POST['id'] = $_GET['id'];
		
		
		/*
		if(isset($_POST['del']) || isset($_GET['del'])){
			require ('l_del.php');
			
		}elseif(isset($_POST['mod']) || isset($_GET['mod'])){
			require ('l_mod.php');
			return $tpl->fetch(SITEPATH_TEMPLATE . SITE_SKIN . '/html/mj/l_add-mod.htm');
			
		}elseif(isset($_POST['add']) || isset($_GET['add'])){
			require ('l_add.php');
			return $tpl->fetch(SITEPATH_TEMPLATE . SITE_SKIN . '/html/mj/l_add-mod.htm');
			
		}else{
			*/
		if(isset($_GET['qte_item']))
		{
			//Afficher aussi le nombre d'item dans le lieu
			$query = 'SELECT id, nom_technique, nom_affiche, proprioid, COUNT(inv_id) as qte_item
						FROM '.DB_PREFIX . 'lieu
						LEFT JOIN ' . DB_PREFIX . 'item_inv ON(inv_lieutech=nom_technique)
						GROUP BY id
						ORDER BY nom_technique ASC;';
		}
		elseif(isset($_GET['qte_perso']))
		{
			//Afficher aussi le nombre de perso dans le lieu
			$query = 'SELECT l.id, l.nom_technique, l.nom_affiche, l.proprioid, COUNT(p.id) as qte_perso
						FROM ' . DB_PREFIX . 'lieu as l
						LEFT JOIN ' . DB_PREFIX . 'perso as p ON(p.lieu=l.nom_technique)
						GROUP BY l.id
						ORDER BY nom_technique ASC;';
		}
		else
		{
			//Afficher uniquement la liste des lieux
			$query = 'SELECT id, nom_technique, nom_affiche
						FROM '.DB_PREFIX.'lieu
						ORDER BY nom_technique ASC;';
			
		}
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		if (count($arrAll)>0)
		{
			$LIEUX = array();
			$i=0;
			foreach($arrAll as &$arr)
			{
				$arr['nom_affiché'] = stripslashes($arr['nom_affiche']);
				$LIEUX[$i] = $arr;
				$i++;
			}
			$tpl->set("LIEUX",$LIEUX);
		}
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu.htm');
	}
	
	
	
	/**
	 * Retourne un tableau des actions qui peuvent être liés à un lieu.
	 *
	 */
	public static function getAllLinkedActions()
	{
		$arr = array();
		
		$arr[] = array('url'=>'Banque',			'nom'=>'Accéder à la banque', 'config_url'=>'Lieu_Banque');
		$arr[] = array('url'=>'Biblio',			'nom'=>'Accéder à la bibliothèque', 'config_url'=>'Lieu_Biblio');
		$arr[] = array('url'=>'Boutique',		'nom'=>'Accéder à la boutique', 'config_url'=>'Lieu_Boutique');
		$arr[] = array('url'=>'CasiersListe',	'nom'=>'Accéder aux casiers', 'config_url'=>'Lieu_Casiers');
		$arr[] = array('url'=>'Casino',			'nom'=>'Accéder au casino', 'config_url'=>'Lieu_CasinoMod');
		$arr[] = array('url'=>'Distributeur',	'nom'=>'Distribuer la production en gros', 'config_url'=>'Lieu_Distributeur');
		$arr[] = array('url'=>'Etudier',		'nom'=>'Étudier', 'config_url'=>'Lieu_Etude');
		$arr[] = array('url'=>'Guichet',		'nom'=>'Guichet', 'config_url'=>null);
		$arr[] = array('url'=>'LaboDrogue',		'nom'=>'Laboratoire', 'config_url'=>null);
		$arr[] = array('url'=>'Mairie',			'nom'=>'Mairie', 'config_url'=>null);
		$arr[] = array('url'=>'MediaListe',		'nom'=>'Accéder aux médias', 'config_url'=>'Lieu_MediaAcces');
		$arr[] = array('url'=>'OrdinateurFixe',	'nom'=>'Poste fixe', 'config_url'=>null);
		$arr[] = array('url'=>'Producteur',		'nom'=>'Travailler à la production', 'config_url'=>'Lieu_Producteur');
		$arr[] = array('url'=>'Recycler',		'nom'=>'Recycler', 'config_url'=>null);
		$arr[] = array('url'=>'ReparerArme',	'nom'=>'Réparer une arme', 'config_url'=>null);
		$arr[] = array('url'=>'ReparerDefense',	'nom'=>'Réparer une défense', 'config_url'=>null);
		$arr[] = array('url'=>'TelephonerCabine',	'nom'=>'Cabine téléphonique', 'config_url'=>null);
		$arr[] = array('url'=>'Soigner',		'nom'=>'Soigner', 'config_url'=>null);
		
		return $arr;
	}
	
	/**
	 * Retourne le tableau d'une action selon son url.
	 *
	 */
	public static function getLinkedAction($url)
	{
		$arr = self::getAllLinkedActions();
		foreach($arr as $row)
			if($row['url']==$url)
				return $row;
				
		return null;
	}
	
	
}
