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

/**
 * Load data and env variables from a local config.ini file placed at the root of the project
 * @throws Exception If config.ini is not found
 *
 * @return array
 */
function load_env_and_secret_data(): array
{
    $config = parse_ini_file(__DIR__ . '/../config.ini', true)['spotify'];

    if ($config == false) {
        throw new Exception("Impossible de charger les données pour se connecter. Merci de créer le fichier config.ini avec les clé/valeurs adéquates.");
    }

    return $config;
}

/**
 * Authenticate the user, ask for authorization via a web form
 *
 * @return void
 */
function authenticate(array $config): void
{
    define('CLIENT_ID', $config['client_id']);
    define('CLIENT_SECRET', $config['client_secret']);

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
}

$config = load_env_and_secret_data();

// Credentials de l'application cliente Spotify (protocole OAuth)
define('BASE_URL_SPOTIFY', $config['base_url_spotify']);
define('REDIRECT_URI', $config['redirect_uri']);
define('REDIRECT_URI_SECURE', $config['redirect_uri_secure']);


//URL Redirect (Spotify) in the same script, program continues here after autorisation has been asked to the user.
if (isset($_GET['code'])) {
    require './callback.php';
    die;
}

authenticate($config);
