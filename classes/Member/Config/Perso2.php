<?php
/** Gestion des options du compte
*
* @package Member
*/
class Member_Config_Perso2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?m=Config_Perso';
		
		
		$img_maxw = 150; //Px
		$img_maxh = 200; //Px
		$allowedext = array('jpg', 'jpeg', 'gif', 'png');
		$img_maxsize = 50; //Kb
                

		//Enregistrer les modification des textes
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
				. ' SET reaction=:reaction,'
					. ' esquive=:esquive,'
					. ' current_action=:current_action,'
					. ' description=:description,'
					. ' background=:background'
				. ' WHERE userId=:userId'
					. ' AND id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$esquive = ((isset($_POST['esquive'])) ? '1' : '0');
		$prep->bindValue(':reaction',		$_POST['reaction'],			PDO::PARAM_STR);
		$prep->bindValue(':esquive',		$esquive,					PDO::PARAM_STR);
		$prep->bindValue(':current_action',	$_POST['current_action'],	PDO::PARAM_STR);
		$prep->bindValue(':description',	$_POST['description'],		PDO::PARAM_STR);
		$prep->bindValue(':background',		$_POST['background'],		PDO::PARAM_STR);
		$prep->bindValue(':userId',			$account->getId(),			PDO::PARAM_INT);
		$prep->bindValue(':id',				$perso->getId(),			PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		
		
		//Enregistrer l'image du personnage
		if (!isset($_POST['imgtype']))
			return fctErrorMSG('Aucune sélection pour l\'image n\'à été faite.', $errorUrl);
		
		switch($_POST['imgtype'])
		{
			
			case 'none':
				$query = 'UPDATE ' . DB_PREFIX . 'perso'
						. ' SET imgurl=""'
						. ' WHERE id=:id'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',				$perso->getId(),			PDO::PARAM_INT);
				$prep->executePlus($db, __FILE__,__LINE__);
				break;
				
			case 'remote':
				if (!isset($_POST['imgurl']))
					return fctErrorMSG('Aucune image spécifié.', $errorUrl);
				
				if(strtolower(substr($_POST['imgurl'], 0,7))!='http://')
					$_POST['imgurl'] = 'http://' . $_POST['imgurl'];
				
				//Vérifier la taille x,y maximale
				//if(!$imgsize = @GetImageSize($_POST['imgurl'])) //[0] = width [1] = height
				//	return fctErrorMSG('Impossible d\'accéder à l\'image (vérifiez l\'adresse exacte).', $errorUrl);

				// technique sans getimagesize qui ne fonctionne pas sans 
				$ch = curl_init();
				$timeout = 5; // set to zero for no timeout
				curl_setopt ($ch, CURLOPT_URL, $_POST['imgurl']);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$file_contents = curl_exec($ch);
				curl_close($ch);

				$new_image = ImageCreateFromString($file_contents);
				imagejpeg($new_image, "temp.jpg",100);

				// Get new dimensions
				$myfilesize = getimagesize("temp.jpg");
				$imgsize = $myfilesize;

				if (($imgsize[0] > $img_maxw) || ($imgsize[1] > $img_maxh)) 
					return fctErrorMSG('Image trop grosse (max ' . $img_maxw . ' x ' . $img_maxh . 'px).', $errorUrl);
				
				
				//Vérifier le poid de l'image:
				$imgpoid = self::filesize_remote($_POST['imgurl']);
				if ($imgpoid>($img_maxsize*1024))
					return fctErrorMSG('Image trop grosse (max ' . $img_maxsize . 'Ko).', $errorUrl);
				
				$query = 'UPDATE ' . DB_PREFIX . 'perso'
						. ' SET imgurl=:imgurl'
						. ' WHERE id=:id'
						. ' LIMIT 1';
				$prep = $db->prepare($query);
				$prep->bindValue(':id',				$perso->getId(),			PDO::PARAM_INT);
				$prep->bindValue(':imgurl',			$_POST['imgurl'],			PDO::PARAM_STR);
				$prep->executePlus($db, __FILE__,__LINE__);
				break;
				
			case 'local':
				
				if(empty($_FILES['imgfile']['name']))
					break;
				
				if (empty($_FILES['imgfile'])) //Variable globale pour l'upload de fichier.
					return fctErrorMSG('Aucune image spécifié.', $errorUrl);
				
				$uploaddir = SITE_PHYSICAL_PATH . 'tpl/_common/perso/';
				
				
				//Vérifier l'extention du fichier
				$pext = self::getFileExtension($_FILES['imgfile']['name']);
				$pext = strtolower($pext);
				$imgtypeok = false;
				for ($i=0;$i<count($allowedext);$i++)
					if ($allowedext[$i] == $pext)
						$imgtypeok = true;
				
				if ($imgtypeok!=true)
					return fctErrorMSG('Type de fichier d\'image invalide.', $errorUrl);
				
				
				//Vérifier la taille x,y maximale
				$imgsize = GetImageSize($_FILES['imgfile']['tmp_name']); //[0] = width [1] = height
				if (($imgsize[0] > $img_maxw) || ($imgsize[1] > $img_maxh))
					return fctErrorMSG('Image trop grosse: ' . $imgsize[0] . 'x' . $imgsize[1] . ' (max ' . $img_maxw . ' x ' . $img_maxh . 'px.).', $errorUrl);
				
				
				//Vérifier le poid de l'image:
				if ($_FILES['imgfile']['size'] > ($img_maxsize*1024))
					return fctErrorMSG('Image trop grosse: ' . round($_FILES['imgfile']['size']/1024,2) . 'Ko (max ' . $img_maxsize . 'Ko).', $errorUrl);
				


				//Les validations sont OK, copier le fichier
				
				if (!is_uploaded_file($_FILES['imgfile']['tmp_name']))
					return fctErrorMSG('Erreur: ce fichier n\'est pas un fichier uploadé.', $errorUrl);
				
				if($_FILES['imgfile']['error'] != UPLOAD_ERR_OK)
					return fctErrorMSG('Erreur: ' . $_FILES['pictures']['error'] . '.', $errorUrl);


				$newfile = $uploaddir . $perso->getId() . '.' . $pext;
				if (!move_uploaded_file($_FILES['imgfile']['tmp_name'],$newfile))
					//En cas d'erreur d'écriture (Droits CHMOD à vérifier)
					return fctErrorMSG('Erreur de copie (CHMOD ' . $uploaddir . ' ?).', $errorUrl);
				
				
				$query = 'UPDATE ' . DB_PREFIX . 'perso'
							. ' SET imgurl=:imgurl'
							. ' WHERE id=:id'
							. ' LIMIT 1';
				$imgurl = SITE_VIRTUAL_PATH . 'tpl/_common/perso/' . (int)$perso->getId() . '.' . $pext;
				$prep = $db->prepare($query);
				$prep->bindValue(':id',				$perso->getId(),	PDO::PARAM_INT);
				$prep->bindValue(':imgurl',			$imgurl,			PDO::PARAM_STR);
				$prep->executePlus($db, __FILE__,__LINE__);
				break;
		}
		
		
		
		//ajouter un message dans le HE pour dire que ca a ete pris en compte
		Member_He::add('System', $perso->getId() , 'Système', 'Changements des informations du personnage pris en compte.');
		
		//Retourner à la page par défaut
		header('location:?m=News');
		return '';
	}
	
	
	private static function filesize_remote($url, $timeout=2)
	{
		
		$url = parse_url($url);
		
		if (!isset($url['query']))
			$url['query']='';
			
		if ($fp = @fsockopen($url['host'], ($url['port'] ? $url['port'] : 80), $errno, $errstr, $timeout)){
			$openwhat = "HEAD " . $url['path'] . $url['query'] . " HTTP/1.0\r\nHost: " . $url['host'] . "\r\n\r\n";
			fwrite($fp, $openwhat);
			stream_set_timeout($fp, $timeout);
			while (!feof($fp)){
				$size = fgets($fp, 4096);
				if (stristr($size, 'Content-Length') !== false){
					$size = trim(substr($size, 16));
					break;
				}
			}
			fclose ($fp);
		}
		return is_numeric($size) ? intval($size) : false;
	}
	
	private static function getFileExtension($str)
	{
		
		$i = strrpos($str, '.');
		
		if (!$i) 
			return '';
		
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
	}

}
