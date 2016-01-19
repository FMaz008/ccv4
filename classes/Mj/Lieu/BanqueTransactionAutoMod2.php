<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Mj_Lieu_BanqueTransactionAutoMod2
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		//Valider si le # du compte à été recu
		if(!isset($_POST['compteId']) || !is_numeric($_POST['compteId']))
			return fctErrorMSG('Ce compte est invalide.');

		//Valider si le # de banque à été recu
		if(!isset($_POST['banqueId']) || !is_numeric($_POST['banqueId']))
			return fctErrorMSG('banque invalide.');
		
		//Valider si le # de transaction à été recu
		if(!isset($_POST['tid']) || !is_numeric($_POST['tid']))
			return fctErrorMSG('Transaction invalide.');
				
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromId($_POST['compteId']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}	
		
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
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_BanqueTransactionAuto&id=" . $_POST['compteId'] . "&bid=" . $_POST['banqueId'] ."';</script>");
	}
}

