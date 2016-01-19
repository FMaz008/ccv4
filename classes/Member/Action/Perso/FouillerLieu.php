<?php
/** Regarder tous les items presents dans le lieu
*
* @package Member_Action
*/
class Member_Action_Perso_FouillerLieu
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_FouillerLieu';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//LISTER TOUT LES ITEMS PRESENTS DANS LE LIEU NON CACHES
		$i=0; $e=0; $items=array();
		while( $item = $perso->getLieu()->getItems($i++))
			if(!$item->iscache()) //On affiche seulement les objets qui ne sont pas caches
				$items[$e++] = $item;
		$tpl->set('INV_LIEU', $items);
		
		//CAS OU ON CHERCHE LES OBJETS CACHES
		if(isset($_POST['search']))
		{
			
			//Détermine le nombre de Pa nécessaire
			$actionPa = 10;
		
			$tailleLieu = array(	'TC' => 1, 
									'C' => 2,
									'M' => 3,
									'L' => 4,
									'TL' => 5);
			$influence_tailleLieu = $tailleLieu[$perso->getLieu()->getDimension()]; //influence en fonction de la taille du lieu
			
			$actionPa *= $influence_tailleLieu;
			
			//Verification de l'état du personnage
			if(!$perso->isConscient())
				return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
			
			if($perso->getPa() < $actionPa)
				return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
			
			//Test de compétence
			$tauxReussite = $perso->getChancesReussite("PCKP");
			$de = rand(1,100);
			
			if(DEBUG_MODE)
				echo("Test : [Dé=" . $de . ", TauxRéussite=" . $tauxReussite . "]\n");
			
			$messageHe = 'Vous faites une recherche approfondi dans ' . $perso->getLieu()->getNom() . '. ';
			$items = array();
			//Le test a réussi
			if($de < $tauxReussite){
				
				//Affichage des objets cachés
				$i=0; $e=0; $degReussite = $tauxReussite - $de;
				while( $item = $perso->getLieu()->getItems($i++))
					if($item->iscache() && $degReussite > $item->getTauxCache()) //On affiche seulement les objets caché qui ont été trouvés
						$items[$e++] = $item;
				
				//Gain Xp
				$xpInfluTailleLieu = array(	'TC' => '1',
											'C' => '2',
											'M' => '3',
											'L' => '4',
											'TL' => '5'); // Le gain d'xp depend de la dimension du lieu
				if(empty($items))
					$messageHe .= 'Vous ne trouvez rien.' . "\n";
				else
					$messageHe .= 'Vous trouvez quelques choses.' . "\n";
				$messageHe .= "\n\n" . $perso->setStat(array(	'AGI' => '+00',
																'FOR' => '-0' . $xpInfluTailleLieu[$perso->getLieu()->getDimension()],
																'DEX' => '+0' . $xpInfluTailleLieu[$perso->getLieu()->getDimension()],
																'PER' => '+0' . $xpInfluTailleLieu[$perso->getLieu()->getDimension()],
																'INT' => '-0' . $xpInfluTailleLieu[$perso->getLieu()->getDimension()]));
				$messageHe .= "\n" . $perso->setComp(array(	'PCKP' => (rand(1, 3) * $tailleLieu[$perso->getLieu()->getDimension()])));
				
			}
			else
			{
				$messageHe .= 'Vous ne trouvez rien.' . "\n";
				
				//Gain Xp
				$messageHe .= "\n\n" . $perso->setStat(array(	'AGI' => '+00',
																'FOR' => '-01',
																'DEX' => '+01',
																'PER' => '+01',
																'INT' => '-01'));
				$messageHe .= "\n" . $perso->setComp(array(	'PCKP' => rand(1, 3)));
			}
			
			
			//Affichage dans le HE
			Member_He::add('System', $perso->getId(), 'chercher', $messageHe);
		
			//Mise à jour PA
			$perso->changePa('-', $actionPa);
			$perso->setPa();
			
			$tpl->set('INV_LIEU_CACHE', $items);
		}
		
		//CAS OU ON CHERCHE DANS UNE CACHETTE PRECISE
		if(isset($_POST['search_no'])){
		
			$actionPa = 10;
			
			//Verification de l'état du personnage
			if(!$perso->isConscient())
				return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
			
			if($perso->getPa() < $actionPa)
				return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
			
			//Liste des objets
			$i=0; $e=0; $items=array();
			while( $item = $perso->getLieu()->getItems($i++))
				if($item->getCacheNo() == $_POST['cache_no']) //On affiche seulement l'objet caché à la cachette
					$items[$e++] = $item;
			$tpl->set('INV_LIEU_CACHE', $items);
			
			if(empty($items))
				$messageHe = 'Vous cherchez dans un endroit précis [HJ: cachette '. $_POST['cache_no'] . '] dans ' . $perso->getLieu()->getNom() . ', mais il n\'y a rien.' . "\n";
			else
				$messageHe = 'Vous cherchez dans un endroit précis [HJ: cachette '. $_POST['cache_no'] . '] dans ' . $perso->getLieu()->getNom() . '. Vous trouvez quelque chose.' . "\n";
			
			//Affichage HE
			Member_He::add('System', $perso->getId(), 'chercher', $messageHe);
			
			//Mise à jour PA
			$perso->changePa('-', $actionPa);
			$perso->setPa();
		}
		
		$tpl->set('PERSO', $perso);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/FouillerLieu.htm',__FILE__,__LINE__);
	}
}

