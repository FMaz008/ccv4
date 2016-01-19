<?php
/**
 * Affichage de l'interface d'abonnement.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
class Member_Mp_Abo2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$tarifs = array(
					0 => 0,
					1 => array(
							6 => 2.00,
							12 => 3.00
						),
					2 => array(
							6 => 8.00,
							12 => 12.00
						),
					3 => array(
							6 => 16.00,
							12 => 24.00
						)
				);
		
		
		
		if(!isset($_POST['userId']))
			return fctErrorMSG("Aucun utilisateur spéficié.");
		$userId = (int)$_POST['userId'];
		
		
		
		//Trouver les informations concernant l'abonnement MP
		$query = 'SELECT id, user, mp, mp_expiration'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:userId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',		$userId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		

		$expir = $arr['mp_expiration'];
		$lvl = $arr['mp'];		
		
		$tpl->set('MP_LVL', $lvl);
		$tpl->set('MP_TXT', Account::convMemberLevelTxt($lvl));
		$tpl->set('ACCOUNT_ID', $arr['id']);
		$tpl->set('ACCOUNT_USER', $arr['user']);
		
		
		
		//Créer le tableaux des possibilités d'abonnements
		$abo = array();
		
		
		
		if($lvl == 0){
			$abo[count($abo)] = array(
									'cat' => "Niveau I",
									'txt' => "Nouvel abonnement MP-1, 6 mois",
									'code' => 'abo11',
									'prix' => fctCreditFormat(round($tarifs[1][6],2))
									);
			$abo[count($abo)] = array(
									'cat' => "Niveau I",
									'txt' => "Nouvel abonnement MP-1, 12 mois",
									'code' => 'abo12',
									'prix' => fctCreditFormat(round($tarifs[1][12],2))
									);
			$abo[count($abo)] = array(
									'cat' => "Niveau II",
									'txt' => "Nouvel abonnement MP-2, 6 mois",
									'code' => 'abo21',
									'prix' => fctCreditFormat(round($tarifs[2][6],2))
									);
			$abo[count($abo)] = array(
									'cat' => "Niveau II",
									'txt' => "Nouvel abonnement MP-2, 12 mois",
									'code' => 'abo22',
									'prix' => fctCreditFormat(round($tarifs[2][12],2))
									);
			$abo[count($abo)] = array(
									'cat' => "Niveau III",
									'txt' => "Nouvel abonnement MP-3, 6 mois",
									'code' => 'abo31',
									'prix' => fctCreditFormat(round($tarifs[3][6],2))
									);
			$abo[count($abo)] = array(
									'cat' => "Niveau III",
									'txt' => "Nouvel abonnement MP-3, 12 mois",
									'code' => 'abo32',
									'prix' => fctCreditFormat(round($tarifs[3][12],2))
									);
		}
		else
		{
			//Déterminer le temps restant à l'abonnement actuel
			$joursRestant = floor(($expir - time())/(60*60*24));
			
			
			
			//Calculer les mises à niveau
			if($lvl==1){
				$abo[count($abo)] = array(
									'cat' => "Mise à niveau II",
									'txt' => "Mise à niveau du temps restant ($joursRestant jours) vers MP-2",
									'code' => 'upg12',
									'prix' => fctCreditFormat(round(($joursRestant/365) * ($tarifs[2][12] - $tarifs[1][12]) * 1.10,2))
									);
				$abo[count($abo)] = array(
									'cat' => "Mise à niveau III",
									'txt' => "Mise à niveau du temps restant ($joursRestant jours) vers MP-3",
									'code' => 'upg13',
									'prix' => fctCreditFormat(round(($joursRestant/365) * ($tarifs[3][12] - $tarifs[1][12]) * 1.10,2))
									);
			}
			if($lvl==2){
				$abo[count($abo)] = array(
									'cat' => "Mise à niveau III",
									'txt' => "Mise à niveau du temps restant ($joursRestant jours) vers MP-3",
									'code' => 'upg23',
									'prix' => fctCreditFormat(round(($joursRestant/365) * ($tarifs[3][12] - $tarifs[2][12]) * 1.10,2))
									);
			}
			
			//Calculer les extentions d'abonnement
			if($lvl==1){
				$abo[count($abo)] = array(
									'cat' => "Extension I",
									'txt' => "Extension MP-1, 6 mois",
									'code' => 'ext11',
									'prix' => fctCreditFormat(round($tarifs[1][6],2))
									);
				$abo[count($abo)] = array(
									'cat' => "Extension I",
									'txt' => "Extension MP-1, 12 mois",
									'code' => 'ext12',
									'prix' => fctCreditFormat(round($tarifs[1][12],2))
									);
			}
			if($lvl==2){
				$abo[count($abo)] = array(
									'cat' => "Extension II",
									'txt' => "Extension MP-2, 6 mois",
									'code' => 'ext21',
									'prix' => fctCreditFormat(round($tarifs[2][6],2))
									);
				$abo[count($abo)] = array(
									'cat' => "Extension II",
									'txt' => "Extension MP-2, 12 mois",
									'code' => 'ext22',
									'prix' => fctCreditFormat(round($tarifs[2][12],2))
									);
			}
			if($lvl==3){
				$abo[count($abo)] = array(
									'cat' => "Extension III",
									'txt' => "Extension MP-3, 6 mois",
									'code' => 'ext31',
									'prix' => fctCreditFormat(round($tarifs[3][6],2))
									);
				$abo[count($abo)] = array(
									'cat' => "Extension III",
									'txt' => "Extension MP-3, 12 mois",
									'code' => 'ext32',
									'prix' => fctCreditFormat(round($tarifs[3][12],2))
									);
			}
		}
		
		$tpl->set('SITE_VIRTUAL_PATH', SITE_VIRTUAL_PATH);
		$tpl->set('ABO', $abo);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/abo2.htm',__FILE__,__LINE__);
	}
}

