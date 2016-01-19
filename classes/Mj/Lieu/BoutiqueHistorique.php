<?php
/** Gestion de l'historique d'une boutique d'un lieu
*
* @package Mj
*/

class Mj_Lieu_BoutiqueHistorique
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
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
		//$tpl->set('ID', $_GET['id']);
		
		//suppression
		if(isset($_POST['del']))
		{
			$lieu->supprimerBoutiqueHistorique();
		}
		
		//Lister toutes les transactions
		$historique = $lieu->getBoutiqueHistorique();
		
		if($historique != null)
			$tpl->set('HISTORIQUE_LIST', $historique);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BoutiqueHistorique.htm',__FILE__,__LINE__);
	}
}

