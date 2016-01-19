<?php
/** Gestion d'un sac a dos
*
* @package Member_Action
*/
class Member_Action_Item_Sac
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Lister les sacs à dos dans l'inventaire.
		$i=0; $e=0;
		$arrSac = array();
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemSac)
			{
				$arrSac[$e]['sac'] = $item;
				
				$j=0; $f=0;
				$arrSacInv = array();
				while($elem = $item->getInventaire($j++))
					$arrSacInv[$f++] = $elem;
				
				if(count($arrSacInv)>0)
					$arrSac[$e]['inv'] = $arrSacInv;
				
				$e++;
			}
		}
		
		if(count($arrSac)>0)
			$tpl->set('SACS', $arrSac);
		
		
		//Lister l'inventaire du perso actuel
		$i=0; $e=0;
		$invPerso = array();
		while( $item = $perso->getInventaire($i++))
		{
			$invPerso[$e]['item'] = $item;
			$invPerso[$e]['class'] = '';
			if($item->isEquip() || $item instanceof Member_ItemSac)
				$invPerso[$e]['class'] = 'txtStyle_grayed';
			$e++;
		}
		
		if(count($invPerso)>0)
			$tpl->set('INV_PERSO', $invPerso);
		
		
		$tpl->set('PERSO_PR_MAX', $perso->getPrMax());
		$tpl->set('PERSO_PR', $perso->getPr());
		$tpl->set('PERSO_PA', $perso->getPa());
		
		if(isset($_GET['sacId']))
			$tpl->set('SHOW_SAC_ID', (int)$_GET['sacId']);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Sac.htm',__FILE__,__LINE__);
	}
}

