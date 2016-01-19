<?php
/** Gestion de l'interface de l'inventaire d'un lieu ou d'un casier
*
* @package Mj
*/

function compare($a, $b)//Fonction de comparaison servant au tri du tableau pour afficher les items en inventaire par groupe de "type"
{
   return strcmp($a->getType(), $b->getType());
}

class Mj_Lieu_Inventaire
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
			return fctErrorMSG($e->getMessage());
		}
		
		
		
		//Lister tout les items que le lieu possède (section de gauche)
		$i=0; $e=0; $f=0;
		$arrItem = array();
		
		if (isset($_GET['cid']))
		{
			//Listing d'un casier
			$found=false;
			while( $casier = $lieu->getCasiers($i++))
			{
				if($casier->getId()==$_GET['cid'])
				{
					$found=true;
					break;
				}
			}
			
			if (!$found)
				return fctErrorMSG('Le casier ID #' . $_GET['cid'] . ' est introuvable.');
			
			while($item = $casier->getInventaire($f++))
				$arrItem[$e++] = $item;
					
		}
		else
		{
			//Listing d'un lieu
			while( $item = $lieu->getItems($i++))
				$arrItem[$e++] = $item;
			
		}
		usort($arrItem, 'compare');
		
		$tpl->set('LIEU_TECH', $lieu->getNomTech());
		$tpl->set('ITEMS',$arrItem);
		$tpl->set('CASIER', ((isset($_GET['cid'])) ? $_GET['cid'] : false ));
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Inventaire.htm',__FILE__,__LINE__);
	}
}

