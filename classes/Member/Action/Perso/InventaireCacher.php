<?php
/** Gestion de l'action de cacher un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Perso_InventaireCacher
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$actionPa = 10;
		
		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
			die('00|' . rawurlencode('Vous devez sélectionner un item pour effectuer cette action.'));
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			die($_POST['id'] . '|' . rawurlencode('Votre n\'&ecirc;tes pas en état d\'effectuer cette action.'));
		
		if($perso->getPa() <= $actionPa)
			die($_POST['id'] . '|' . rawurlencode('Vous n\'avez pas assez de PA pour effectuer cette action.'));
		
		
		
		//Trouver en inventaire l'item que l'on souhaite cacher
		$i=0; $item = null;
		while( $tmp = $perso->getInventaire($i++))
			if($tmp->getInvId() == $_POST['id'])
				$item = $tmp;
		
		if(empty($item))
			die($_POST['id'] . '|' . rawurlencode('Cet item ne vous appartiend pas. (cheat)'));
		
		
		if($item->canRegroupe())
		{
			if (!isset($_POST['askQte']))
			{
				//Demander la quantité à cacher
				$msg = '<div style="text-align:center;width:100%;">';
				$msg .= 'Cacher: <input type="text" size="3" class="text" id="ask_qte" value="1" style="text-align:right;" /> / ' . $item->getQte() . '<br />';
				$msg .= "<input type=\"button\" class=\"button\" onclick=\"submitJeterForm('?m=Action_Perso_InventaireCacher'," . $_POST['id'] . ", \$F('ask_qte'));\" value=\"Cacher\" />";
				$msg .= '</div>';
				die($_POST['id'] . '|' . $msg);
				
			}
		}
		else
		{
			$_POST['askQte'] = 1;
		}
		
		// Test si on arrive à cacher l'item
		
		$itemPr = $_POST['askQte'] * $item->getPr();//Calcul des pr totaux à cacher
		//On major les pr à 9
		if($itemPr > 9)
			$itemPr=9;
		$tauxReussite = $perso->getChancesReussite("PCKP");
		$de = rand(1,100) + $itemPr; //Le nombre de pr influe sur les chances de réussite : plus l'objet est gros, plus il est dur à cacher
		
		/* --DEBUG MODE--
		if(DEBUG_MODE)
			echo("Test : [Dé=" . $de . ", TauxRéussite=" . $tauxReussite . "]\n");
		*/	
		
		if($de < $tauxReussite) //Le test a réussi
		{
			//Déséquiper + cacher l'item
		
			$cache_no = rand(1, 9999);
			$item->cacherVersLieu($perso->getLieu(), $_POST['askQte'], $tauxReussite-$de, $cache_no);
		
			$messageHe = 'Vous cachez votre [i]' . $item->getNom() . '[/i] dans ' . $perso->getLieu()->getNom() .".\n[HJ: numéro de cachette : " . $cache_no . '].';
			
			//Gain d'xp
			$messageHe .= "\n\n" . $perso->setStat(array(	'AGI' => '+00',
															'FOR' => '-02',
															'DEX' => '+02',
															'PER' => '+02',
															'INT' => '-02'));
			$messageHe .= "\n" . $perso->setComp(array(	'PCKP' => (rand(1, 3)*2)));
			
		}
		else //Le test a échoué
		{
			$messageHe = 'Vous essayez de trouver une cachette pour votre [i]' . $item->getNom() . '[/i] dans ' . $perso->getLieu()->getNom() .' mais c\'est un échec';
			
			//Gain d'xp
			$messageHe .= "\n\n" . $perso->setStat(array(	'AGI' => '+00',
															'FOR' => '-01',
															'DEX' => '+01',
															'PER' => '+01',
															'INT' => '-01'));
			$messageHe .= "\n" . $perso->setComp(array(	'PCKP' => rand(1, 3)));
		}
		
		
		//Affichage dans le HE
		Member_He::add('System', $perso->getId(), 'cacher', $messageHe);
		
		$perso->changePa('-', $actionPa);
		$perso->setPa();
		
		$perso->refreshInventaire(); //Recalculer l'inventaire (les PR)
		
			
		die($_POST['id'] . '|OK|' . $perso->getPa() . '|' . $perso->getPr()); //Tout est OK
	}
}

