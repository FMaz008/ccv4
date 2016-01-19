<?php
function __autoload($class)
{
	require 'classes/' . str_replace('_', '/', $class) . '.php';
}
