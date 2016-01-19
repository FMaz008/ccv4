<?php
/** Gestion de l'interface de l'inventaire d'une boutique d'un lieu
*
* @package Mj
*/

function compare($a, $b)//Fonction de comparaison servant au tri du tableau pour afficher les items en inventaire par groupe de "type"
{
   return strcmp($a->getType(), $b->getType());
}

class Mj_Lieu_Boutique
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		

		//Instancier le lieu
		try
		{
			$lieu = Member_LieuFactory::createFromId((int)$_GET['id']);
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		
		$tpl->set('LIEU_TECH', $lieu->getNomTech());
		
		
		
		
		//Lister tout les items que le lieu possède (section de gauche)
		$i=0; $e=0; $f=0;
		$arrItem = array();

		//Listing d'un lieu
		while( $item = $lieu->getBoutiqueInventaire($i++))
			$arrItem[$e++] = $item;
		
		
		usort($arrItem, "compare");
		
		$tpl->set('ITEMS',$arrItem);
		$tpl->set('CASIER', ((isset($_GET['cid'])) ? $_GET['cid'] : false ));
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Boutique.htm',__FILE__,__LINE__);
	}
}

