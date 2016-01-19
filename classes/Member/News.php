<?php
/** Affichage de la page d'accueil des membres
 *
 * @package Member
 */
class Member_News
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if($account->isLogged())
		{
			//Déterminer la quantité de perso que ce compte à l'autorisation de créer
			$query = 'SELECT auth_creation_perso'
					. ' FROM ' . DB_PREFIX . 'account'
					. ' WHERE id=:userId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;

			
			//Passer le nombre d'autorisation au template
			$tpl->set('CREATE_PERSO', $arr[0]);
		
		
			//Vérifier si une inscription est en 'modification'
			$query = 'SELECT id, nom, sexe, inscription_valide'
					. ' FROM ' . DB_PREFIX . 'perso '
					. ' WHERE	inscription_valide!="1"'
						. ' AND userid=:userId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$arrInscr = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			
			if (count($arrInscr) != 0 )
			{
				$MODS = array();
				$WAIT = array();
				foreach($arrInscr as $arr)
				{
					if($arr['inscription_valide'] == 'mod')
						$MODS[] = $arr;
					if($arr['inscription_valide'] == '0')
						$WAIT[] = $arr;
				}
			
				if(count($MODS) > 0)
					$tpl->set('MODS', $MODS);
				
				if(count($WAIT) > 0)
					$tpl->set('WAIT', $WAIT);
			}
		
		
		
			$tpl->set('SEXE', $account->getSexe());
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/news.htm',__FILE__,__LINE__);
		
	}
}
