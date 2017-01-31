<?php
/** Gestion des templates.
* <br> (cette classe est instanciée pour toutes les pages du site)
*
* Exemple d'utilisation:
* <code>
* $tpl = new Template($account);
* </code>
*
* @package CyberCity_2034
*/
class Template {

	private $vars; /// Holds all the template variables
	private $file;
	private $account; //Référence à l'account du membre
	
	private $arrColor = array('0000FF', '00FF00', 'FF0000', '663300', '006633', '330066', 'FF3366', 'FF6600', '6600FF');
    private static $currentColorIdx = 0;
	
	/** Créer un nouveau template.
	* 
	* @param string $file Fichier à charger
	*/ 
	function __construct(&$account, $file = null)
	{
		$this->file = $file;
	   	$this->set('SITE_NAME',SITE_NAME);
		//$this->set('SITEPATH_ROOT',SITEPATH_ROOT);
		//$this->set('SITEPATH_REAL',SITEPATH_REAL);
		//$this->set('TPLPATH_ROOT',TPLPATH_ROOT);
		//$this->set('TPLPATH_REAL',TPLPATH_REAL);
		$this->set('SITE_VERSION',SITE_VERSION);
		$this->account = &$account;
	}

	/** Définir une nouvelle variable session.
	* <br > NOTE: Les variables sont dynamique, vous pouvez en créer de nouvelles dynamiquement sans problème.
	* 
	* Exemple d'utilisation:
	* <code>
	* $tpl->set('GAME_SESSION_LENGHT',(SESSION_TIMEOUT*60));
	* </code>
	*
	* @param string $name Nom de la variable
	* @param string $value Valeur de la variable
	* @param string $htmlentities Convertir les caractères spéciaux de la valeur en entités HTML.
	*/ 
	public function set($name, $value, $htmlentities = false)
	{
		$this->vars[$name] = is_object($value)
		   ? ( get_class($value)==get_class($this) ? $value->fetch() : $value )
		   : ( is_string($value) && $htmlentities ? htmlentities($value) : $value );
	}


	/** Ouvre, traite et retourne la source du template généré.
	* <br> NOTE: Les variables sont dynamique, vous pouvez en créer de nouvelles dynamiquement sans problème.
	* <br> La fonction retourne la source générée
	*
	* Exemple d'utilisation:
	* <code>
	* echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html' . DIRECTORY_SEPARATOR . 'index_full.htm',__FILE__,__LINE__);
	* </code>
	*
	* @param string $file Chemin physique vers le fichier de template
	* @param string $errFile En cas d'erreur, permet d'afficher le fichier qui procède à l'appel
	* @param string $errLine en cas d'erreur, permet d'afficher la ligne en cause du fichier d'appel
	*
	* @return string
	*/ 
	public function fetch($file = null, $errFile=null, $errLine=null)
	{
		if(!$file)
			$file = $this->file;
		
		
		//Si le fichier demandé n'existe pas, charger celui du template par défaut
		if(!file_exists($file))
		{
			//Décortiquer le fichier actuel
			$startPath = $this->account->getSkinRemotePhysicalPath();
			$endPath = substr($file,strlen($startPath)-1, strlen($file)-strlen($startPath)+1);
			$file = SITE_PHYSICAL_PATH . 'tpl' . DIRECTORY_SEPARATOR . SITE_BASE_SKIN . $endPath;
			
		}
		
		extract($this->vars);          // Extract the vars to local namespace
		ob_start();                    // Start output buffering
		
		if(!file_exists($file))
			echo '<strong>[' . $errFile . ', L.' . $errLine . '] <font color=red>TPL 404:</font></strong> ' . $file;
		else
			include($file);               // Include the file
			
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		
		//Petit outil de débuggage pour localiser les fichiers en visualisant une page.
		if(DEBUG_MODE && isset($_GET['tpl'])){
			$contents = $this->addDebug($contents, $file, '', $errFile, $errLine);
		}
		return $contents;              // Return the contents
	}

	private function addDebug($contents, $file, $extraStyle, $cFile, $cLine)
    {
        $color = $this->arrColor[self::$currentColorIdx];

        $contents = '<div class="debug_template clearfix" style="border:3px solid #' . $color . ';' . $extraStyle . '"><div class="debug_template" style="background-color:#' . $color . ';">' . $file . '<br /><span style="font-size:8pt;">Called from:' . $cFile . ' (L.' . $cLine . ')</span></div>' . $contents . '</div>';

        self::$currentColorIdx++;
        if(self::$currentColorIdx == count($this->arrColor))
            self::$currentColorIdx = 0;

        return $contents;
    }
}

