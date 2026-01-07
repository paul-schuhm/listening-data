<?php

define('DEBUG_MODE', true);

//Load data and env variables from a local config.ini file placed at the root of the project
$config = parse_ini_file(__DIR__ . '/config.ini', true)['spotify'];

if ($config == false) {
    throw new Exception("Impossible de charger les données pour se connecter. Merci de créer le fichier config.ini avec les clé/valeurs adéquates.");
}

define('CLIENT_ID', $config['client_id']);
define('CLIENT_SECRET', $config['client_secret']);
define('BASE_URL_SPOTIFY', $config['base_url_spotify']);
define('REDIRECT_URI', $config['redirect_uri']);
define('REDIRECT_URI_SECURE', $config['redirect_uri_secure']);
