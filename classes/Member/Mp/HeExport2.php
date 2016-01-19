<?php
/**
 * Exportation du HE.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2010, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

class Member_Mp_HeExport2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{

		
		if($account->getMemberLevel()===0)
			return fctErrorMSG('Cette option est accessible aux Membres Plus seulement.');

		header("Content-Type: text/plain;");
		header("Content-Disposition: attachment; filename=he_export" . date("Y-m-d") . ".txt");


		for($i=0; $i<12000; $i+=500)
		{
			$arrAll = Member_He::listMessages($perso, $i, 499, false);
			
			//Grouper adéquatement les envoyeurs & destinataires
			$arrHe = array();
			foreach($arrAll as &$arrMsg)
			{
				$arrTmp = array();
				$arrTmp['msg'] = $arrMsg->getMsg();
				$arrTmp['date'] = $arrMsg->getDateTxt();
				
				//remplacer les tableaux From To par la version texte
				$arrFrom = array();
				$arrTo = array();
				foreach($arrMsg->getFrom() as $tmp)
					$arrFrom[] = $tmp['nom'];
					
				foreach($arrMsg->getTo() as $tmp)
					$arrTo[] = $tmp['nom'];
				
				$arrTmp['from'] = implode(', ', $arrFrom);
				$arrTmp['to'] = implode(', ', $arrTo);

				$arrHe[] = $arrTmp;
				unset($arrTmp);
			}
			unset($arrAll);
			$tpl->set('MESSAGES', $arrHe);
			unset($arrHe);
			//Retourner le template complété/rempli
			echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/heExport2.htm',__FILE__,__LINE__);
			flush();
		}
		die();
	}
}

