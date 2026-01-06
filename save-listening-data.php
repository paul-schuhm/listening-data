<?php

/**
 * Ce programme interroge Spotify pour récupérer des stats d'écoute et exporter les métadonnées d'écoute pour faire un backup du profil : historique d'écoute, playlists et contenu. Ce backup permettra d'importer toutes ces données (notamment playlists) vers un autre compte ou service plus tard.
 *
 * @package PS\ListeningData
 * @author Paul Schuhmacher
 */

$config = parse_ini_file(__DIR__ . '/config.ini', true)['spotify'];

// Credentials de l'application cliente Spotify (protocole OAuth)
define('BASE_URL_SPOTIFY', $config['base_url_spotify']);
define('CLIENT_ID', $config['client_id']);
define('CLIENT_SECRET', $config['client_secret']);
define('REDIRECT_URI', $config['redirect_uri']);
define('REDIRECT_URI_SECURE', $config['redirect_uri_secure']);

/*Choisir l'une des 2 REDIRECT_URI (secure ou non). URL enregistrées auprès de l'appli Spotify (https://developer.spotify.com/dashboard)
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

//Redirection vers la page d'authentification user de Spotify pour obtenir son autorisation d'agir en son nom.
header('Location: ' . $auth_url);
exit;
