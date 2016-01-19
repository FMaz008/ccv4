<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Mj_Lieu_BanqueTransactionAutoMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		//Vérifier le # du compte a été recu
		if(!isset($_POST['compteId']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');

		if(!isset($_POST['banqueId']))
			return fctErrorMSG('Identifiant banque manquant.');
		
		//Valider si une transaction à été sélectionnée
		if(!isset($_POST['tid']))
			return fctErrorMSG('Aucune transaction n\'a été sélectionnée.');
				
		//Instancier le compte
		try
		{
			$compte = Member_BanqueCompte::getCompteFromId($_POST['compteId']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage());
		}	

		//Passer le compte au template
		$tpl->set('COMPTE', $compte);
		//Passer l'id de la banque au template
		$tpl->set('BANQUE_ID', $_POST['banqueId']);

		//Rechercher la transaction
		$transactions = $compte->getTransactionsAuto();
		foreach($transactions as $transaction)
			if($transaction->getId() == $_POST['tid'])
				$transactionFound = $transaction;
		
		//Vérifier si la transaction existe
		if(!isset($transactionFound))
			return fctErrorMSG('Cette transaction n\'existe pas.');
		
		//Passer les informations sur la carte au template
		$tpl->set('TRANSACTION',$transactionFound);
		
		//Afficher la page
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueTransactionAutoMod.htm',__FILE__,__LINE__);
	}
}
