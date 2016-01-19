<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueTransactionAutoMod2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Valider si le # du compte à été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Valider si le # de carte à été recu
		if(!isset($_POST['tid']))
			return fctErrorMSG('Cette carte est invalide (aucune carte).');
		
		//Passer le # de compte au template
		$tpl->set('COMPTE', $_POST['compte']);
		
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromNoCompte($_POST['compte']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}	
						
		//Vérifier si le compte appartiend bien au perso
		if ($compte->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
		
		//Rechercher la transaction
		$transactions = $compte->getTransactionsAuto();
		foreach($transactions as $transaction)
			if($transaction->getId() == $_POST['tid'])
				$transactionFound = $transaction;
		
		//Vérifier si la transaction existe
		if(!isset($transactionFound))
			return fctErrorMSG('Cette transaction n\'existe pas. (' . $_POST['tid'] . ')');
			
		//Vérifier les données
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
			
		//Mettre à jour les informations
		try
		{
			$newCompteTo = Member_BanqueCompte::getCompteFromNoCompte($_POST['compteTo']);
			$transactionFound->changeCompteIdTo($newCompteTo->getId());
			$transactionFound->changeDescription($_POST['description']);
			$transactionFound->changeValue($_POST['value']);
			$date = mktime(0, 0, 0, $_POST['date_month'], $_POST['date_day'], ($_POST['date_year'] - GAMETIME_DECAL));
			$transactionFound->changeDate($date);
			$transactionFound->setData();
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}	
		
		//rediriger la page
		//TODO: utiliser le module de redirection
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BanqueTransactionAutoRedirect.htm',__FILE__,__LINE__);
	}
}

