<?php
/** Gestion des timers.
* <br> (cette classe est instanciée pour toutes les pages du site)
*
* Exemple d'utilisation:
* <code>
* $timer = new Timer();
* </code>
*
* @package CyberCity_2034
*/
class Timer
{	//BUT: Calculer le délais entre deux chose (ex: le début de génération d'une page et la fin)
	private $starttime;
	
	/** Démarrer un un timer.
	*
	* Exemple d'utilisation:
	* <code>
	* $timer->start();
	* </code>
	*
	*/
	public function start(){
		$time = microtime();
		$time = explode( ' ', $time );
		$time = $time[0] + $time[1];
		$this->starttime = $time;
	}
	
	/** Arrêter un un timer.
	*
	* Exemple d'utilisation:
	* <code>
	* echo $timerTotal->finish() . "sec";
	* </code>
	*
	*/
	public function finish(){
		$time = microtime();
		$time = explode( ' ', $time );
		$time = $time[0] + $time[1];
		$finishtime = $time;
		$totaltime = round( ( $finishtime - $this->starttime ), 4 );
		return $totaltime;
	}
}
?>