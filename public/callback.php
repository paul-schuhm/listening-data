<?php

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
}


if (isset($_GET['error'])) {
    $msg = sprintf("Erreur : %s. Merci de réessayer.", $_GET['error']);
    die("Une erreur est survenue. Merci de réessayer plus tard.");
}

$code = $_GET['code'] ?? null;

if ($code == null) {
    die("Une erreur est survenue. Merci de réessayer plus tard.");
}


echo "Ask for access token...";
die;

$access_token = request_access_token($code);
