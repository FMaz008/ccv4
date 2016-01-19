<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueTransactionAuto
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Valider si le # du compte à été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Passer le # de compte au template
		$tpl->set('COMPTE', $_POST['compte']);
		
		try
		{
			$compte = Member_BanqueCompte::getCompteFromNoCompte($_POST['compte']);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}		
				
		//Vérifier si le compte appartiend bien au perso
		if($compte->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
			
		//Vérifier si le compte est autorisé à avoir des transactions automatiques
		if(!$compte->authAutoTransactions())
			return fctErrorMSG('Ce compte n\'est pas autorisé à faire des transactions automatiques.');

		//Charger les transactions du compte
		$transactions = $compte->getTransactionsAuto();
		
		if(!empty($transactions))
			$tpl->set('TRANSACTIONS', $transactions);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BanqueTransactionAuto.htm',__FILE__,__LINE__);
	}
}
