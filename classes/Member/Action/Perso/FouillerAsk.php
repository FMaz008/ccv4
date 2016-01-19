<?php
/**
 * Gestion de la demande de fouille corporelle (Demande lancée par Member_Action_Perso_Depouiller)
 *
 * @package Member_Action
 */
class Member_Action_Perso_FouillerAsk
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$errorUrl = '?popup=1&m=Action_Perso_Depouiller';
		
		
		
		if(!isset($_POST['persoid']))
			return fctErrorMSG('Vous devez sélectionner une personne à fouiller.', $errorUrl);
		
		
		if(!isset($_POST['msg']) || empty($_POST['msg']))
			return fctErrorMSG('Vous devez entrer un message expliquant l\'action. (ex.: veuillez lever les bras.)', $errorUrl);
		
		
		
		//Valider que le personnage à fouiller est bel et bien dans le lieu actuel
		$i=0;
		$found=false;
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
		{
			if($tmp->getId() == $_POST['persoid'])
			{
				$found=true;
				break;
			}
		}
		
		if(!$found)
			return fctErrorMSG('Le personnage que vous avez sélectionné ne se situe pas dans le même lieu que vous.', $errorUrl);
		
		
		
		//Tout est OK, créer et envoyer la demande de Fouille
		//Si une ancienne demande existait, la supprimer, de facon à la renouveller
		$query = 'DELETE FROM ' . DB_PREFIX . 'perso_fouille'
					. ' WHERE fromid = ' . (int)$perso->getId()
						. ' AND toid = ' . (int)$_POST['persoid']
					. ' LIMIT 1;';
		$db->query($query, __FILE__, __LINE__);
		
		//Créer la demande
		$query = 'INSERT INTO ' . DB_PREFIX . 'perso_fouille'
					. ' (`fromid`, `toid`, `expiration`, `reponse`)'
					. ' VALUES'
					. ' (:fromId, :toId, UNIX_TIMESTAMP(), 0);';
		$prep = $db->prepare($query);
		$prep->bindValue(':fromId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->bindValue(':toId',	$_POST['persoid'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
			
		//Soumettre le message confirmant la demande
		$msg = fctScriptProtect($_POST['msg']);
		Member_He::add($perso->getId(), (int)$_POST['persoid'], 'fouille', 
						$msg . 
						"\n[HJ: Acceptez-vous de vous laisser fouiller? [<a href=\"?m=Action_Perso_FouillerGo&amp;id=" . (int)$perso->getId() . "&amp;choix=1\">Oui</a>/<a href=\"?m=Action_Perso_FouillerGo&amp;id=" . (int)$perso->getId() . "&amp;choix=0\">Non</a>]]", 
						HE_AUCUN, HE_TOUS, false);
						
		Member_He::add($perso->getId(), (int)$_POST['persoid'], 'fouille', 
						$msg . 
						"\n[HJ: En attente de la réponse du joueur ...]", 
						HE_TOUS, HE_AUCUN, false);
		
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
