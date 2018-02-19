<?php

//##########################################################################
// Définition des pools de connexion
//##########################################################################

$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['ABSTRACTION']    = 'PDO';
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['TYPE']           = 'mysql';
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['FORCE_ENCODING'] = 'utf8';
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['CRITICAL']       = TRUE;
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['ERROR']          = 'warning'; // exception | silent | warning | NULL ; 'silent' => logs PHP ; 'warning' => à l'écran
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['DEBUG']          = DEBUG_SQL ? 'firebug' : '' ;  // screen | firebug | empty

$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['HOST'] = SACOCHE_WEBMESTRE_BD_HOST;
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['PORT'] = SACOCHE_WEBMESTRE_BD_PORT;
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['USER'] = SACOCHE_WEBMESTRE_BD_USER;
$_CONST['POOL'][SACOCHE_WEBMESTRE_BD_NAME]['PASS'] = SACOCHE_WEBMESTRE_BD_PASS;

//##########################################################################
// Associations des noms de connexion aux pools et à la base de données
//##########################################################################

$_CONST['CONNECTION'][SACOCHE_WEBMESTRE_BD_NAME]['POOL']    = SACOCHE_WEBMESTRE_BD_NAME;
$_CONST['CONNECTION'][SACOCHE_WEBMESTRE_BD_NAME]['DB_NAME'] = SACOCHE_WEBMESTRE_BD_NAME;

?>
