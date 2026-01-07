<?php

/**
 * Module containing all functions.
 */


/**
 * Request and return access token
 *
 * @param string $code Token obtained after user grand client app autorization
 * @return string
 */
function request_access_token(string $code): string
{

    $data = [
        'grant_type' => '',
        'code' => $code,
        'redirect_uri' => ''
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_exec($ch);
}
