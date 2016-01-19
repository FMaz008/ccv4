<?php
/** Gestion de l'interface de réparation des défenses
*
* @package Member_Action
*/
class Member_Action_Lieu_ReparerDefense
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		// Liste des défenses endommagées en inventaire
		$i=0; $arrItems = array();
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemDefense)
				if ($item->getResistance() < $item->getResistanceMax())
					$arrItems[count($arrItems)]['item'] = $item;

		
		//Établir le cout de réparation en PA + Cash pour chaque item
		for($i=0; $i<count($arrItems); $i++)
		{
			
			//Calculer le % de réussite
			$chanceReussite =	(
									  $perso->getChancesReussite('ARMU') * 2
									+ $perso->getChancesReussite('FORG') * 1
									+ $perso->getChancesReussite('ARTI') * 1
									) /4;
			$chanceReussite = round(($chanceReussite + ($arrItems[$i]['item']->getPercDommage() + $arrItems[$i]['item']->getPercComplexite())/2) /2);
			
			
			//Calculer le cout $ de la réparation
			$coutCash 	= round(($arrItems[$i]['item']->getPercDommage() / 20) * ($arrItems[$i]['item']->getResistanceMax() - $arrItems[$i]['item']->getResistance()),2);
			$coutPa		= floor((100-$chanceReussite)/10 * $arrItems[$i]['item']->getPercDommage()/10);
			
			
			//Ajouter les nouvelles données au tableau
			$arrItems[$i]['coutCash']	= $coutCash;
			$arrItems[$i]['coutPa']		= $coutPa;
			$arrItems[$i]['complex']	= round(($arrItems[$i]['item']->getPercDommage() + $arrItems[$i]['item']->getPercComplexite())/20);
		}	
		
		$tpl->set('ITEMS', $arrItems);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/ReparerDefense.htm',__FILE__,__LINE__);
	}
}

