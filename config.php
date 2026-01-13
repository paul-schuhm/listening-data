<?php

/*Activer/Désactiver le mode debug. Utilisée par la fonction dump().*/
define('DEBUG_MODE', true);

//Load data and env variables from a local config.ini file placed at the root of the project
$config = parse_ini_file(__DIR__ . '/config.ini', true)['spotify'];
if ($config == false) {
    throw new Exception("Impossible de charger les données pour se connecter. Créer le fichier 'config.ini' avec les clé/valeurs adéquates (voir le fichier config.ini.dist).");
}

if (
    !isset($config['client_id'])  ||
    !isset($config['client_secret'])  ||
    !isset($config['client_id'])  ||
    !isset($config['access_token_url'])
) {
    throw new Exception("Fournir les informations client_id et client_secret nécessaires au programme.");
}

define('CLIENT_ID', $config['client_id']);
define('CLIENT_SECRET', $config['client_secret']);
define('ACCESS_TOKEN_URL_SPOTIFY', $config['access_token_url']);
define('AUTHORIZE_URL', $config['authorize_url']);
define('BASE_URL', $config['base_url']);
define('REDIRECT_URI', $config['redirect_uri']);
define('REDIRECT_URI_SECURE', $config['redirect_uri_secure']);
/*WHERE TO STORE BACKUP DATA*/
define('BACKUP_DIR', __DIR__ . '/backup');
