<?php

/**
 * Ce programme interroge Spotify pour récupérer des stats d'écoute et exporter les métadonnées d'écoute pour faire un backup du profil : historique d'écoute, playlists et contenu. Ce backup permettra d'importer toutes ces données (notamment playlists) vers un autre compte ou service plus tard.
 *
 * @package PS\ListeningData
 * @author Paul Schuhmacher
 */

define('DEBUG_MODE', true);

if (!DEBUG_MODE) {
    set_exception_handler(function (Exception $e) {
        die($e->getMessage());
    });
}

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

/*
Choisir l'une des 2 REDIRECT_URI (secure ou non). URL enregistrées auprès de l'appli Spotify (https://developer.spotify.com/dashboard)
Si REDIRECT_URI_SECURE choisie, penser à lancer le proxy TLS avec stunnel (voir README)
*/
$params = array(
    'client_id'     => CLIENT_ID,
    'redirect_uri'  => REDIRECT_URI,
    /*@see https://developer.spotify.com/documentation/web-api/tutorials/code-flow*/
    'response_type' => 'code',
);

$auth_url = BASE_URL_SPOTIFY . '?' . http_build_query($params);
$auth_url = 'https://accounts.spotify.com/authorize?' . http_build_query($params);

//Redirection vers la page d'authentification user de Spotify (web form)
exec("xdg-open '$auth_url' >/dev/null 2>&1");

//Handle redirect URI from the browser by opening a socket
$socket = stream_socket_server('tcp://127.0.0.1:5005', $errno, $errstr);
$connexion = stream_socket_accept($socket);

$request = fread($connexion, 1024);

//Extract 'code' from the URL(request arg ?code=XXXX)
preg_match('#GET /\?([^ ]+)#', $request, $matches);
parse_str($matches[1] ?? '', $query_string);

$code = $query_string['code'] ?? null;

if ($code != null) {
    fwrite($connexion, "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\n\r\n <p>Autorization granted</p>");
} else {
    fwrite($connexion, "HTTP/1.1 400 OK\r\nContent-Type: text/plain\r\n\r\n <p>Autorization code missing. Please try again</p>");
    throw new RuntimeException('Authorization code missing');
}

fclose($connexion);
fclose($socket);
