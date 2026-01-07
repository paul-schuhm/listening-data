<?php

/**
 * Module containing all functions.
 */

require_once 'config.php';

/**
 * Var_dump $data only in DEBUG_MODE
 *
 * @param [type] ...$data
 * @return void
 */
function dump(mixed ...$data): void
{
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        return;
    }

    foreach ($data as $value) {
        var_dump($value);
    }
}


/**
 * Request and return Spotify access token, after authentication
 *
 * @param string $code Token obtained after Spotify user has granted authorization to this client app.
 * @return string
 */
function request_access_token(string $code): string
{

    /*@see https://developer.spotify.com/documentation/web-api/tutorials/code-flow (section Request an access token)*/
    $data = [
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => REDIRECT_URI,
    ];

    $token = base64_encode(sprintf("%s:%s", CLIENT_ID, CLIENT_SECRET));
    
    dump($token);

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => ACCESS_TOKEN_URL_SPOTIFY,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            'Content-type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . $token
        ]
    ]);

    $result = curl_exec($ch);

    if ($result == false) {
        dump(curl_errno($ch), curl_error($ch));
        throw new RuntimeException("Impossible d'obtenir l'access token. Vérifier les credentials et réessayer.");
    }

    return $result;
}
