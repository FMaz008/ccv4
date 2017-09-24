<?php
/** Gestion de l'action de jeter un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Perso_InventaireJeter
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$actionPa = 3;
		
		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
			die('00|' . rawurlencode('Vous devez sélectionner un item pour effectuer cette action.'));
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			die($_POST['id'] . '|' . rawurlencode('Votre n\'&ecirc;tes pas en état d\'effectuer cette action.'));
		
		if($perso->getPa() <= $actionPa)
			die($_POST['id'] . '|' . rawurlencode('Vous n\'avez pas assez de PA pour effectuer cette action.'));
		
		
		
		//Trouver en inventaire l'item que l'on souhaite jeter
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
				//Demander la quantité à jeter
				$msg = '<div style="text-align:center;width:100%;">';
				$msg .= 'Jeter: <input type="text" size="3" class="text" id="ask_qte" value="1" style="text-align:right;" /> / ' . $item->getQte() . '<br />';
				$msg .= "<input type=\"button\" class=\"button\" onclick=\"submitJeterForm('?m=Action_Perso_InventaireJeter'," . $_POST['id'] . ", \$('#ask_qte').val());\" value=\"Jeter\" />";
				$msg .= '</div>';
				die($_POST['id'] . '|' . $msg);
				
			}
		}
		else
		{
			$_POST['askQte'] = 1;
		}
		
		//Déséquiper + jeter l'item(
		
		$item->transfererVersLieu($perso->getLieu(), $_POST['askQte']);
		$perso->refreshInventaire(); //Recalculer l'inventaire (les PR)
		$perso->changePa('-', $actionPa);
		$perso->setPa();
		
		
		
		if($_POST['askQte']>1)
		{
			Member_He::add('System', $perso->getId(), 'jeter', "Vous jetez " . $_POST['askQte'] . "x [i]" . $item->getNom() . '[/i] au sol.');
		}
		else
		{
			Member_He::add('System', $perso->getId(), 'jeter', "Vous jetez votre [i]" . $item->getNom() . '[/i] au sol.');
		}
		
		die($_POST['id'] . '|OK|' . $perso->getPa() . '|' . $perso->getPr()); //Tout est OK
		
	}
}
