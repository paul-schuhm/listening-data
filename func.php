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

    $data = [
        'grant_type' => '',
        'code' => $code,
        'redirect_uri' => REDIRECT_URI
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_exec($ch);
}
