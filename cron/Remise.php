<?php
/**
 * Routine effectuant les remises
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package CronTask
 */

require_once('_system.php');

Remise::doRemise($db, 100);
?>
