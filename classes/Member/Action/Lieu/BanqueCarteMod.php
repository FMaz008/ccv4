<?php
/** Gestion des cartes associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCarteMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Vérifier le # du compte a été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Valider si une carte à été sélectionnée
		if(!isset($_POST['cid']))
			return fctErrorMSG('Aucune carte n\'a été sélectionnée.');
		
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
		
		//Rechercher la carte 
		$cartes = $compte->getCartes();
		foreach($cartes as $carte)
			if($carte->getNo() == $_POST['cid'])
				$carteFound = $carte;
		
		//Vérifier si la carte existe
		if(!isset($carteFound))
			return fctErrorMSG('Cette carte n\'existe pas. (' . $_POST['cid'] . ')');
		
		//Passer les informations sur la carte au template
		$tpl->set('CARTE',$carteFound);
		
		//Afficher la page
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_carte_mod.htm',__FILE__,__LINE__);
	}
}
