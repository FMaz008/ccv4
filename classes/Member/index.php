<?php
/** Affichage de la page principale. 
 * HE, tableau des statistiques, menu d'action, etc.
 *
 * @package Member
 */
class Member_index
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
	
	
		//Valider si le personnage a été validé
		if(!$perso->isInscriptionValide())
			return fctErrorMSG('Votre personnage ' . $perso->getNom() . ' n\'a pas encore été validé par un MJ.', '?m=News', null, false);
		
		
		//Valider si le personnage est non-bloqué
		if($perso->isBloque())
			return fctErrorMSG('Votre personnage ' . $perso->getNom() . ' a été bloqué.', '?m=News', null, false);
		
		
		
		//Préparer le tableau d'information
		try
		{
			$tpl->set('id',		$perso->getId()); //Utile pour éviter de s'auto-renommer
			$tpl->set('nom',	$perso->getNom());
			$tpl->set('pa',		$perso->getPa());
			$tpl->set('paMax',	$perso->getPaMax());
			$tpl->set('pn',		$perso->getPn());
			$tpl->set('lieu',	$perso->getLieu()->getNom()); //Peut lancer une exception
			$tpl->set('pv',		$perso->getPv());
			$tpl->set('pvMax',	$perso->getPvMax());
			$tpl->set('argent',	fctCreditFormat($perso->getCash()));
			$tpl->set('pr',		$perso->getPr());
			$tpl->set('prmax',	$perso->getPrMax());
			$tpl->set('membre',	$account->getMemberLevelTxt() . '<br />reste: ' . $account->getMemberRestant() . " jour(s)");
			$tpl->set('MP_LVL', $account->getMemberLevel());
			$tpl->set('actionImmediate', $perso->getCurrentAction());

			if($perso->isEnergetique())
				$tpl->set('PA_COLORCLASS', 'txtStyle_valeur');
			elseif($perso->isFaible())
				$tpl->set('PA_COLORCLASS', 'txtStyle_risque');
			else
				$tpl->set('PA_COLORCLASS', 'txtStyle_critique');

			if($perso->isAutonome())
				$tpl->set('PV_COLORCLASS', 'txtStyle_valeur');
			elseif($perso->isConscient())
				$tpl->set('PV_COLORCLASS', 'txtStyle_risque');
			else
				$tpl->set('PV_COLORCLASS', 'txtStyle_critique');

			if($perso->isRassasie())
				$tpl->set('PN_COLORCLASS', 'txtStyle_valeur');
			elseif($perso->isFaim())
				$tpl->set('PN_COLORCLASS', 'txtStyle_critique');
			else
				$tpl->set('PN_COLORCLASS', 'txtStyle_risque');


			if($perso->isLege())
				$tpl->set('PR_COLORCLASS', 'txtStyle_valeur');
			else
				$tpl->set('PR_COLORCLASS', 'txtStyle_risque');

			
			$tpl->set('PERSO',	$perso);
			
			if($account->getMemberLevel()!=3)
				$tpl->set('SHOW_PUB', true);
		}
		catch(Exception $e)
		{
			//throw $e;
			return fctErrorMSG('Votre personnage ' . $perso->getNom() . ' est dans un lieu qui semble ne pas exister (' . $e->getMessage() . '). Contactez un MJ via le forum.', '?m=News', null, false);
		}
		
		//Préparer le menu d'action
		$tpl->set('MENU_ITEMS',$perso->generateActionMenu());
		$code = $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Member/menu_actions.htm');
		$tpl->set('PAGE_MENU_ACTIONS',$code);
		
		
		//Préparer le he [Entête]
		//$he = new Member_He($account, $perso);
		$from = (isset($_GET['hepage'])) ? $_GET['hepage'] : 1;
		
		$tpl->set('HE_PAGE', $from);
		$tpl->set('HE_SIZE', $perso->getHeMsgCount());
		$tpl->set('HE_MAXSPACE', Member_He::spacePerMembership($account->getMemberLevel()));
		
		$tpl->set('MP', $account->getMemberLevel());
		$tpl->set('HE_MSGPERPAGE',$account->getMsgPerPage());
		
		$code = $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Member/he_header.htm');
		$tpl->set('PAGE_HE_HEADER',$code);
		
		
		//Préparer le he [Liste des messages]
		$mpp = $account->getMsgPerPage();
		$heMsg = Member_He::listMessages($perso, ($from-1)*$mpp, $mpp);
		
		$code='';
		$i=$account->getMsgPerPage()+1;
		foreach($heMsg as $msg)
		{
			$tpl->set('MSG',$msg);
			$tpl->set('ITEM_NO_ON_PAGE', --$i);
			$code .= $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Member/he_item.htm');
		}
		$tpl->set('PAGE_HE_MESSAGES',$code);
		
		//Changement de l'action immédiate
		if(isset($_POST['saveActionImmediate']))
		{
			$perso->setCurrentAction($_POST['actionImmediate']);
			$tpl->set('actionImmediate', $_POST['actionImmediate']);
		}
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/index.htm',__FILE__,__LINE__);
		
	}
}

