<?php
/** Gestion des transactions automatiques associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueTransactionAutoAdd2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	
		$coutTransaction = 0;
	
		//Vérifier les paramêtres requis
		if(!isset($_POST['compteNoTo']) || empty($_POST['compteNoTo']))
			return fctErrorMSG('Vous devez préciser le compte récepteur.');
		if(!isset($_POST['description']) || empty($_POST['description']))
			return fctErrorMSG('Vous devez préciser une description pour la transaction.');
		if(!isset($_POST['value']) || empty($_POST['value']))
			return fctErrorMSG('Vous devez entrer le montant de la transaction.');
		if(!is_numeric($_POST['value']))
			return fctErrorMSG('Le montant doit être numérique.');
		if(	!isset($_POST['date_day']) || empty($_POST['date_day'])
			|| !isset($_POST['date_month']) || empty($_POST['date_month'])
			|| !isset($_POST['date_year']) || empty($_POST['date_year']))
			return fctErrorMSG('La date est incomplète.');
		if(!is_numeric($_POST['date_day']) || !is_numeric($_POST['date_month']) || !is_numeric($_POST['date_year']))
			return fctErrorMSG('La date est erronée.');
		if(!checkdate($_POST['date_month'], $_POST['date_day'], ($_POST['date_year'] - GAMETIME_DECAL)))
			return fctErrorMSG('La date est erronée.');
		
		//Passer le # du compte au template
		$tpl->set('COMPTE', $_POST['compte']);
		
		//Instancier les comptes
		try
		{
			$compteEmetteur = Member_BanqueCompte::getCompteFromNoCompte($_POST['compte']);
			$compteRecepteur = Member_BanqueCompte::getCompteFromNoCompte($_POST['compteNoTo']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}		
		
		//Vérifier si le compte appartiend bien au perso
		if ($compteEmetteur->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
		
		//Valider si le compte possède assez d'argent pour créer la transaction
		if($compteEmetteur->getCash() < $coutTransaction && $compteEmetteur->getCash() != -1)
			return fctErrorMSG('Vous n\'avez pas assez de fond dans votre compte pour créer une transaction.');
		
		$date = mktime(0, 0, 0, $_POST['date_month'], $_POST['date_day'], ($_POST['date_year'] - GAMETIME_DECAL));
		//Créer la transaction
		try
		{
			Member_BanqueTransactionAuto::createNewTransactionAuto($compteEmetteur->getId(), $compteRecepteur->getId(), $_POST['value'], $_POST['description'], $date);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}
				
		//Retirer le cout de la transaction du compte
		$compteEmetteur->changeCash('-', $coutTransaction);
		$compteEmetteur->setCash();
		
		//Ajouter la transaction dans l'historique du compte et le HE
		$compteEmetteur->add_bq_hist('', 'CGCH', $coutTransaction, 0);
		Member_He::add(NULL, $perso->getId(), 'banque', 'Vous créez une transaction automatique pour le compte ' . $compteEmetteur->getNoBanque() . '-' . $compteEmetteur->getNoCompte() . '.');
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BanqueTransactionAutoRedirect.htm',__FILE__,__LINE__);
	}
}
