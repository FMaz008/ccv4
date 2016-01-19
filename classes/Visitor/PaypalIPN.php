<?php
/**
 * Réception de la validation d'un paiement fait par paypal.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
 
class Visitor_PaypalIPN
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		

		if(empty($_POST))
			die();
		
		//$paypal_url="https://www.sandbox.paypal.com/cgi-bin/webscr";
		$paypal_url="https://www.paypal.com/cgi-bin/webscr";
		$result=self::fsockPost($paypal_url,$_POST); 
		
		$userId = substr($_POST['item_number'],5);
		$code = substr($_POST['item_number'],0,5);
		
		if(empty($userId))
			$userId = 0;
		
		if(!eregi("VERIFIED",$result)) // TRANSACTION INVALIDÉE PAR PAYPAL
		{
			$statusPP = 'invalide';
		}
		else
		{
			
			$statusPP = $_POST['payment_status'];
			$cc = self::validationCC($userId);
			$statusCC = ($cc === true) ? 'valide' : $cc;
			
			if (strtolower($statusPP) == 'completed' && strtolower($statusCC) == 'valide')
			{
				$ret = self::activationCC($userId, $code);
				$_POST['DEBUG'] = array(
									'activation' => $ret,
									'code' => $code
								);
			}
			
		}
		
		//Enregistrer la transaction
		$query = 'INSERT INTO ' . DB_PREFIX . 'log_mp'
					. ' (`email`,`item`,`userId`,`statusPP`,`statusCC`,`post`,`ip`,`date`)'
					. ' VALUES'
					. ' (:email, :itemNo, :userId, :statusPP, :statusCC, :post, :ip, :time);';
		$prep = $db->prepare($query);
		$prep->bindValue(':email',		$_POST['payer_email'],	PDO::PARAM_STR);
		$prep->bindValue(':itemNo',		$_POST['item_number'],	PDO::PARAM_STR);
		$prep->bindValue(':userId',		$userId,				PDO::PARAM_STR);
		$prep->bindValue(':statusPP',	$statusPP,				PDO::PARAM_STR);
		$prep->bindValue(':statusCC',	$statusCC,				PDO::PARAM_STR);
		$prep->bindValue(':post',		serialize($_POST),		PDO::PARAM_STR);
		$prep->bindValue(':ip',			$_SERVER['REMOTE_ADDR'],PDO::PARAM_STR);
		$prep->bindValue(':time',		time(),					PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		die();
		
	}
	





	private static function validationCC($userId)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//VALIDER SI LE MONTANT CORRESPOND A L'ITEM
		$query = 'SELECT mp, mp_expiration'
					. ' FROM ' . DB_PREFIX . 'account'
					. ' WHERE id=:id'
					. ' LIMIT 1;';
		$prep = $db->query($query);
		$prep->bindValue(':id',		$userId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		
		if($arr === false)
			return 'User #' . $userId . ' invalide.';
		
		$joursRestant = floor(($arr['mp_expiration'] - time())/(60*60*24));
		
		switch($code)
		{
			case "abo11": case "ext11":	$prix =  2.00; break;
			case "abo12": case "ext12":	$prix =  3.00; break;
			case "abo21": case "ext21":	$prix =  8.00; break;
			case "abo22": case "ext22":	$prix =  12.00; break;
			case "abo31": case "ext31":	$prix =  15.00; break;
			case "abo32": case "ext32":	$prix = 24.00; break;
			case "upg12":
				if($arr['mp']!=1)
					return 'Upgrade MP1->MP2, mais le user est MP' . $arr['mp'];
				
				$prix =  round(($joursRestant/365) * (12.00 - 3.00) * 1.10,2);
				break;
			
			case "upg13":
				if($arr['mp']!=1)
					return 'Upgrade MP1->MP3, mais le user est MP' . $arr['mp'];
				
				$prix =  round(($joursRestant/365) * (24.00 - 3.00) * 1.10,2);
				break;
			
			case "upg23":
				if($arr['mp']!=2)
					return 'Upgrade MP2->MP3, mais le user est MP' . $arr['mp'];
				
				$prix =  round(($joursRestant/365) * (24.00 - 12.00) * 1.10,2);
				break;
			
		}
		
		if($_POST['mc_gross'] < $prix)
			return 'Prix attendu pour un ' . $code . ': ' . $prix . 'e et le paiement reçu est de ' . $_POST['mc_gross'] . 'e';
		
		
		
		
		//LA TRANSACTION EST VALIDÉE PAR PAYPAL ET PAR CYBERCITY, ACTIVER L'ACHAT
		return true;
	}


	private static function activationCC($userId, $itemCode)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Détortiquer le code:
		$code = substr($itemCode,0,3);
		$lvl = substr($itemCode,3,1);
		$duree = substr($itemCode,4,1);
		$jours = ($duree=='1') ? 183 : 365;
		$duree = mktime(date("H"), date("i"), date("s"), date("m"), date("d")+$jours, date("Y"));
		
		//Activer selon le type d'abonnement
		switch($code)
		{
			case 'upg':
				$query = 'UPDATE ' . DB_PREFIX . 'account'
							. ' SET mp=:mp'
							. ' WHERE id=:id'
							. ' LIMIT 1;';
				$prep = $db->query($query);
				$prep->bindValue(':mp',		$lvl,		PDO::PARAM_STR);
				$prep->bindValue(':id',		$userId,	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				break;
			case 'abo':
				$query = 'UPDATE ' . DB_PREFIX . 'account'
						. ' SET mp=:mp,'
							. ' mp_expiration=:expiration'
						. ' WHERE id=:id'
						. ' LIMIT 1;';
				$prep = $db->query($query);
				$prep->bindValue(':mp',			$lvl,		PDO::PARAM_STR);
				$prep->bindValue(':expiration',	$duree,		PDO::PARAM_INT);
				$prep->bindValue(':id',			$userId,	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				break;
			case 'ext':
				$query = 'UPDATE ' . DB_PREFIX . 'account'
						. ' SET mp_expiration=mp_expiration+:extention'
						. ' WHERE id=:id'
						. ' LIMIT 1;';
				$prep = $db->query($query);
				$prep->bindValue(':extention',	$jours*24*60*60,	PDO::PARAM_INT);
				$prep->bindValue(':id',			$userId,			PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				break;
			
			default:
				return 'Non-reconnu: ' . $code . ', ' . $lvl . ',' . $duree;
				break;
		}
		return 'Ok: ' . $code . ', ' . $lvl . ',' . $duree;
	}

	//posts transaction data using fsockopen. 
	private static function fsockPost($url,$data)
	{ 
	
		//Parse url
		$web=parse_url($url); 
	
		$postdata=NULL;
		//build post string 
		foreach($data as $i=>$v)
		{ 
			$postdata.= $i . "=" . urlencode($v) . "&"; 
		}
	
		$postdata.="cmd=_notify-validate";
	
		//Set the port number
		if($web['scheme'] == "https")
		{
			$web['port']="443";  $ssl="ssl://";
		}
		else
		{
			$web['port']="80";
		}  
	
		//Create paypal connection
		$fp=@fsockopen($ssl . $web['host'],$web['port'],$errnum,$errstr,30); 
	
		//Error checking
		if(!$fp)
		{
			echo "$errnum: $errstr";
		}
		else //Post Data
		{
	
			fputs($fp, "POST " . $web['path'] . " HTTP/1.1\r\n"); 
			fputs($fp, "Host: " . $web['host'] . "\r\n"); 
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
			fputs($fp, "Content-length: ".strlen($postdata)."\r\n"); 
			fputs($fp, "Connection: close\r\n\r\n"); 
			fputs($fp, $postdata . "\r\n\r\n"); 
	
			//loop through the response from the server 
			while(!feof($fp))
			{
				$info[]=@fgets($fp, 1024);
			} 
	
			//close fp - we are done with it 
			fclose($fp); 
	
			//break up results into a string
			$info=implode(",",$info); 
	
		}
	
		return $info; 
	
	}
}

