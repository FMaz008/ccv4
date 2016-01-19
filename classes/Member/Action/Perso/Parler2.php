<?php
/** Gestion des interactions de l'action Parler: Effectuer l'action de parler (envoyer le message)
*
* @package Member_Action
*/
function array_remove($arr,$value)
{
   return array_values(array_diff($arr,array($value)));
}


class Member_Action_Perso_Parler2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Parler';
		
		
		//Valider si: Aucun message tappé et on essaie pas de montrer un Badge
		if ((!isset($_POST['msg']) || empty($_POST['msg'])) && (!isset($_POST['badge']) || $_POST['badge']=='0'))
			return fctErrorMSG('Aucun message à envoyer.', $errorUrl);
		
		$msg_type = 'parler';
		
		//Charger les informations sur le badge et cr.er l'entête du message si nÃ©cÃ©ssaire.
		if (isset($_POST['badge']) && $_POST['badge']!='0')//Afficher le BADGE
		{
			$badge_txt = '';
			$msg_type = 'parlerbadge';
			if ($_POST['badge']=='VISAVERT' && $perso->getVisaPerm()=='1')
			{
				$badge_txt = 'Une personne vous présente son Visa Vert: ' . $perso->getNom() . '[hr]';
			}
			else
			{
			
				//Trouver le badge sélectionné
				$i=0; $trouve = false;
				while( $item = $perso->getInventaire($i++))
				{
					if($item->getInvId() == $_POST['badge'])
					{
						$trouve = true;
						break;
					}
				}
				if ($trouve)
					$badge_txt = 'Une personne vous présente son ' . $item->getNom() . ': '
									. $item->getTitre()
									. ' (' . $item->getContenu() . ') [hr]';
				else
					return fctErrorMSG('Le badge que vous avez sélectionné n\'existe pas.', $errorUrl);
				
			}
			
			$_POST['msg'] = $badge_txt . $_POST['msg'];
		}
		
		
		if (!isset($_POST['to']))
		{ 
			$_POST['to']='Note perso.';
		}
		else
		{
			//Si des destinataires sont recu, les valider et leur faire parvenir le message
			for($i=0;$i<count($_POST['to']);$i++)
				if (!$perso->getLieu()->confirmPerso($perso, $_POST['to'][$i]))
					array_remove($_POST['to'], $i);
		}
		
		//Copier le message dans les HE
		Member_He::add($perso->getId(), $_POST['to'], $msg_type, $_POST['msg']);
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

