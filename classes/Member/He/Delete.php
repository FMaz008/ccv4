<?php
/**
 * Gestion des actions d'effacements du HE
 * 
 * Exemple d'utilisation:
 * <code>
 * $he = new Member_He();
 * </code>
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Member
 */



class Member_He_Delete
{
	function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		if(isset($_POST['del']) && $_POST['del'] != null)
		{
			//Effacer la sélection
			if (isset($_POST['delsel']))
				Member_He::deleteMessages($perso->getId(), $_POST['del']);
			
		}
		
		//Effacer les déplacements
		if (isset($_POST['deldep']))
			Member_He::deleteType($perso->getId(), 'move');

		//Effacer tout le HE et forcer la restauration du compteur heQte à 0
		if(isset($_GET['delall']))
			Member_He::deleteAll($perso->getId());

		
		//Retourner le template complété/rempli
		$tpl->set('PAGE','index');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/redirect.htm',__FILE__,__LINE__);
	}
}

