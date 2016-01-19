<?php
/** Gestion des cartes associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCarteAdd2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	
		$cout_carte = 10;
	
		//Vérifier les paramêtres requis
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
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
		
		//Valider si le nip a été entré
		if (!is_numeric($_POST['nip']))
			return fctErrorMSG('Vous devez entrez un NIP composé uniquement de chiffres de 0 à 9.');
			
		//Valider si le nom a été entré
		if (empty($_POST['nom']))
			return fctErrorMSG('Vous devez entrez un nom pour la carte.');
		
		//Valider si le compte possède assez d'argent pour acheter une carte
		if($compte->getCash() < $cout_carte && $compte->getCash() != -1)
			return fctErrorMSG('Vous n\'avez pas assez de fond dans votre compte pour créer une carte.');
		
		//Retirer le cout de la carte du compte
		$compte->changeCash('-', $cout_carte);
		$compte->setCash();
		
		//Créer l'access
		$carteId = $compte->createAccesCard($_POST['nom'], $_POST['nip'], $_POST['valid']);
		
		//Créer la carte
		$compte->createCarteGuichet($_POST['nom'], $carteId, $perso->getId());
		
		//Ajouter la transaction dans l'historique du compte et le HE
		$compte->add_bq_hist('', 'CGCH', $cout_carte, 0);
		Member_He::add(NULL, $perso->getId(), 'banque', 'Vous obtenez une carte pour le compte ' . $compte->getNoBanque() . '-' . $compte->getNoCompte() . '.');
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_carte_redirect.htm',__FILE__,__LINE__);
	}
}
