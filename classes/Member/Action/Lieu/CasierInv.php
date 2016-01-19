<?php
/** Gestion d'une boutique par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_CasierInv
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_CasiersListe';
		
		
		//Valider si un casier à été sélectionné
		if(!isset($_POST['id_casier']))
			return fctErrorMSG('Aucun casier sélectionné.', $errorUrl);
		
		
		$tpl->set('ID_CASIER', $_POST['id_casier']);
		
		
		//LISTER TOUT LES CASIERS DU LIEU
		$i=0;
		$found=false;
		while( $casier = $perso->getLieu()->getCasiers($i++))
		{
			if($casier->getId()==$_POST['id_casier'])
			{
				$found = true;
				break;
			}
		}
		
		//Valider si le casier se trouve dans le lieu actuel
		if(!$found)
			return fctErrorMSG('Le casier #' . $_POST['id_casier'] . ' est introuvable.', $errorUrl);
		
		
		//PROTECTION: vérifier la protection du casier
		if($casier->getProtection() != NULL)
		{
		
			//Protection par digipass
			if($casier->getProtection() == 'pass')
			{
				if(isset($_POST['pass']) && $_POST['pass'] != $casier->getPass())
					$tpl->set('WRONGPASS', true);
				
				if(!isset($_POST['pass']) || $_POST['pass'] != $casier->getPass())
					return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Casier_digipass.htm',__FILE__,__LINE__);
				
				$tpl->set('PASS', $_POST['pass']);
				
			//Protection par clef
			}
			elseif($casier->getProtection() == 'clef')
			{
				$accesOk = false;
				
				//Trouver la clé
				$i=0; $e=0;
				$arrClefs = array();
				while( $item = $perso->getInventaire($i++))
				{
					if($item instanceof Member_ItemClef)
					{
						$arrClefs[$e++] = $item;
						if(isset($_POST['clef']) && $item->getInvId() == $_POST['clef'])
						{
							if($item->getCode() == $casier->getPass())
							{
								$accesOk = true;
								$tpl->set('CLEF', $_POST['clef']);
								break;
							}
						}
					}
				}
					
				if(!$accesOk)
				{
					if(isset($_POST['clef']))
						$tpl->set('WRONGPASS', true);
				
					if(count($arrClefs)>0)
						$tpl->set('CLEFS', $arrClefs);
					return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Casier_clef.htm',__FILE__,__LINE__);
				}
				
			
			}else{
				 fctBugReport('Une protection d\'un casier n\'est pas prise en charge par le système.', array('CasierId:' . $casier->getId(), 'Protection:' . $casier->getProtection()), __FILE__, __LINE__);
			}
		}
		
		
		
		//LISTER L'INVENTAIRE DU CASIER
		$i=0; $e=0;
		$arr = array();
		while($item = $casier->getInventaire($i++))
			$arr[$e++] = $item;
		
		//Passer l'invantaire du casier au template, sauf s'il est vide (ne rien envoyer au template)
		if(count($arr)>0)
			$tpl->set('ITEMS', $arr);
		
		
		
		
		//LISTER L'INVENTAIRE DU PERSO
		$i=0; $items=array();
		while( $item = $perso->getInventaire($i++))
			if(!($item instanceof Member_ItemDrogueDrogue)) //Pourquoi exclu t-on les drogues ?
				$items[$i] = $item;
		
		if(count($items)>0)
			$tpl->set('INV_PERSO', $items);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/CasierInv.htm',__FILE__,__LINE__);
	}
}
