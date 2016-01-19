<?php
/** Gestion des cartes associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCarteMod2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		//Valider si le # du compte à été recu
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		//Valider si le # de carte à été recu
		if(!isset($_POST['cid']))
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
		
		//Valider si le NIP est possible
		if (!is_numeric($_POST['nip']))
			return fctErrorMSG('Vous devez entrez un NIP composé uniquement de chiffres de 0 à 9.');
			
		//Valider si le nom a été entré
		if (empty($_POST['nom']))
			return fctErrorMSG('Vous devez entrez un nom pour la carte.');
		
		//Rechercher la carte 
		$cartes = $compte->getCartes();
		foreach($cartes as $carte)
			if($carte->getNo() == $_POST['cid'])
				$carteFound = $carte;
		
		//Vérifier si la carte existe
		if(!isset($carteFound))
			return fctErrorMSG('Cette carte n\'existe pas. (' . $_POST['cid'] . ')');
			
		//Mettre à jour les informations
		$carteFound->changeNom($_POST['nom']);
		$carteFound->changeNip($_POST['nip']);
		$carteFound->changeValid((($_POST['valid'] == 1)? true : false));
		$carteFound->saveData();
		
		//rediriger la page
		//TODO: utiliser le module de redirection
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_carte_redirect.htm',__FILE__,__LINE__);
	}
}

