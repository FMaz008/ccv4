<?php
/** Gestion des transactions automatiques associées à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueTransactionAutoMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Vérifier le # du compte a été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Valider si une carte à été sélectionnée
		if(!isset($_POST['tid']))
			return fctErrorMSG('Aucune transaction n\'a été sélectionnée.');
		
		//Passer le # du compte au template
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
		
		//Vérifier si la carte existe
		if(!isset($transactionFound))
			return fctErrorMSG('Cette transaction n\'existe pas.');
		
		//Passer les informations sur la carte au template
		$tpl->set('TRANSACTION',$transactionFound);
		
		//Afficher la page
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BanqueTransactionAutoMod.htm',__FILE__,__LINE__);
	}
}
