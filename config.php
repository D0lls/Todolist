<?php
//Configuration pour la base
define('DB_HOST','localhost'); 
define('DB_NAME','todo-list'); 
define('DB_USER','root'); 
define('DB_PASS',''); 

define('NB_DAY', 2); //Nombre de jours avant de rendre un todo 'urgent'
error_reporting(E_ALL ^ E_NOTICE);
session_start();

header('Content-Type: text/html; charset=ISO-8859-1'); 
?>