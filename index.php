<?php

/**
 * This program queries Spotify to retrieve (scarce...) listening stats and export listening metadata to create a profile backup: listening history, playlists, and their contents.
 * This backup will allow importing all this data (notably playlists) into another account or service later.
 *
 * @package PS\ListeningData
 * @author Paul Schuhmacher
 */

require_once 'config.php';
require_once 'func.php';

if (!DEBUG_MODE) {
    set_exception_handler(function (Exception $e) {
        die($e->getMessage());
    });
}

//if no available refresh token, ask first auth from spotify user
if (!file_exists('refresh_token')) {
    //Obtain authorization : requires web form validation
    $code = ask_for_auth();
    $access_token = request_access_token($code);
    //Store refresh token to store authorization and reuse it next time.
    save_refresh_token($access_token);
} else {
    //Ask new access token from refresh token (skip auth.)
    $refresh_token = file_get_contents('refresh_token');
    dump($refresh_token);
    $access_token = refresh_access_token($refresh_token);
}

//Test : print user info
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => BASE_URL . '/me',
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token->value
    ]
]);
curl_exec($ch);
