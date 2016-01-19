<?php
/** Suppression d'une transaction automatique associée à un compte
*
* @package Member_Action
*/
class Mj_Lieu_BanqueTransactionAutoDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		//Valider si le # de compte a été recu
		if(!isset($_POST['compteId']) || !is_numeric($_POST['compteId']))
			return fctErrorMSG('Id du compte invalide.');
			
		//Valider si le # de la banque a été recu
		if(!isset($_POST['banqueId']) || !is_numeric($_POST['banqueId']))
			return fctErrorMSG('Id de la banque invalide.');
			
		$errorUrl = '?mj=Lieu_BanqueTransactionAuto&id=' . $_POST['compteId'] . '&bid=' . $_POST['banqueId'];
			
		//Valider si une transaction est passée
		if (!isset($_POST['tid']))
			return fctErrorMSG('Vous devez sélectionner une transaction.', $errorUrl);
		
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromId($_POST['compteId']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}	
		
		//supprimer la transaction
		$compte->delTransactionAuto($_POST['tid']);
		
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_BanqueTransactionAuto&id=" . $_POST['compteId'] . "&bid=" . $_POST['banqueId'] . "';</script>");
	}
}

