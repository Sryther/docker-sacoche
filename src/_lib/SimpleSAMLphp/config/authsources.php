<?php

$config = array(

	// Authentification sur la base distante Gepi
	'distant-gepi-saml' => array(
		'saml:SP',
		'idp'         => 'gepi-idp',
		'entityID'    => 'sacoche-sp', // Ce paramètre doit correspondre avec l' entityID dans le fichier simplesaml/metadata/saml20-sp-remote.php du fournisseur d'identité (Gepi) ; en l'absence de ce paramètre, c'est l'url de départ (monserveurSACoche/_lib/SimpleSAMLphp/modules/saml/www/sp/saml2-acs.php) qui est utilisé comme entityID
		'name'        => array( 'fr' => 'Gepi' ),
		'description' => array( 'fr' => 'Authentification commune Gepi / SACoche' ),
		//on va travailler sur les attributs pour avoir des attributs au format SACoche
		'authproc'    => array(
			50 => array(
				'class'      => 'core:AttributeMap',
				'login_gepi' => 'USER_ID_GEPI',
				'statut'     => 'USER_PROFIL_TYPE',
				'nom'        => 'USER_NOM',
				'prenom'     => 'USER_PRENOM'
			)
		)
	)

);
