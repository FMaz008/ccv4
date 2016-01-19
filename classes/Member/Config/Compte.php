<?php
/** Gestion des options du compte
*
* @package Member
*/
class Member_Config_Compte
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso=null)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',				$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();

		
		$remise = array();
		$currem = $arr['remise'];
		for ($i=1;$i<24;$i++)
		{
			$remise[$i-1]['brut'] = $i;
			$remise[$i-1]['formated'] = date('Y/m/d H:i' , ($currem+($i*60*60)));
		}
		$tpl->set('REMISE_ADV',$remise);
		$tpl->set('EMAIL', $arr['email']);
		$tpl->set('NEXT_REMISE',date('H:i' , $currem));

		
		
		//Lister les skins disponibles
		$skins = array();
		$dir2 = dir(SITE_PHYSICAL_PATH . 'tpl/');
		$counter=0;
		while ($url = $dir2->read())
		{
			if (is_dir(SITE_PHYSICAL_PATH . 'tpl/' . $url) && !preg_match('/^(_.*|\\.|\\.\\.)$/', $url) && substr($url,0,1)!='.')
			{
				
				$skins[$counter]['title']= ucwords(str_replace('_', ' ',$url));
				$skins[$counter]['name'] = $url;
				
				// Si le skin listé est celui actuellement utilisé
				if ($url == $arr['skin']) 
					$skins[$counter]['set'] = true;
					
				$counter++;
			}
		}
		$tpl->set('SKINS',$skins);
		
		//Créer le tableau de la liste des possibilités pour le HE
		$arr_values = array(5,10,15,20,25,30,40,50,75,100,125,150,175,200);
		$heitems = array();
		for ($i=0;$i<count($arr_values);$i++)
		{
			$heitems[$i]['value'] = $arr_values[$i];
			if($arr['heitems']==$arr_values[$i])
			{
				$heitems[$i]['set'] = true;
			}
		}
		$tpl->set('HEITEMS',$heitems);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Config/compte.htm',__FILE__,__LINE__);
	}
}

