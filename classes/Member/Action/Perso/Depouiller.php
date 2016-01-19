<?php
/** 
 * Gestion de l'action dépouiller un personnage (Afficher les personnage qui peuvent être dépouillés)
 *
 * @package Member_Action
 */
class Member_Action_Perso_Depouiller
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état de pouvoir effectuer cette action.');
		
		
		
		//Récupérer la liste des personnage ayant autorisé une fouille
		$query = 'SELECT toid'
				 . ' FROM ' . DB_PREFIX . 'perso_fouille'
				 . ' WHERE fromid=:fromId'
				. ' AND `reponse`=1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':fromId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$persoAuth = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		//Liste des personnages dans le lieu
		$i=0;
		$arrPerso = array();
		$arrPersoOk = array();
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() !== $perso->getId())
				if(!$tmp->isAutonome() || $tmp->getPa()==0)
					$arrPerso[] = $tmp;
				else
				{
					$auth = false;
					foreach($persoAuth as $persoA)
					{
						$auth = $auth || in_array($tmp->getId(), $persoA);
					}
					$arrPersoOk[] = array(
										'perso' => $tmp, 
										'auth' => $auth
										);
				}
				
		if(!empty($arrPerso))
			$tpl->set('LIST_PERSO', $arrPerso);
		
		if(!empty($arrPersoOk))
			$tpl->set('LIST_PERSO_OK', $arrPersoOk);
			
			
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/Depouiller.htm',__FILE__,__LINE__);
	}
}

