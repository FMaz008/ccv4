<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Mj_Lieu_BanqueTransactionAuto
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Valider si le # du compte à été recu
		if(!isset($_GET['id']))
			return fctErrorMSG('Id du compte manquant.');
			
		//Valider si le # de la banque a été reçu
		if(!isset($_GET['bid']))
			return fctErrorMSG('Id de la banque manquant.');
		
		//Passer l'id de la banque au template (pour le retour)
		$tpl->set('BANKID', $_GET['bid']);
		
		try
		{
			$compte = Member_BanqueCompte::getCompteFromId($_GET['id']);
		}
		catch(Exception $e)
		{
			return fctErrorMSG($e->getMessage());
		}
		
		//Passer le compte au template
		$tpl->set('COMPTE', $compte);

		//Charger les transactions du compte
		$transactions = $compte->getTransactionsAuto();
		
		if(!empty($transactions))
			$tpl->set('TRANSACTIONS', $transactions);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueTransactionAuto.htm',__FILE__,__LINE__);
	}
}
