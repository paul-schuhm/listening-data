<?php

/**
 * Model of Access Token delivered by Spotify API
 */
readonly class AccessToken
{
    public function __construct(
        public string $value,
        public string $type,
        public string $expires_in,
        public string $refresh_token,
    ) {
    }
}

/**
 * Module containing all functions.
 */

require_once 'config.php';

/**
 * Debugging function : var_dump $data only in DEBUG_MODE
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
 * Ask user for auth (OAuth 2 flow) to impersonate him
 *
 * @return string authorization code
 */
function ask_for_auth(): string
{
    $params = array(
        'client_id'     => CLIENT_ID,
        'redirect_uri'  => REDIRECT_URI,
        /*@see https://developer.spotify.com/documentation/web-api/tutorials/code-flow/ */
        'response_type' => 'code',
        'show_dialog' => false
    );

    $auth_url = AUTHORIZE_URL . '?' . http_build_query($params);

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

    return $code;
}

/**
 * Request and return Spotify access token, after authentication
 *
 * @param string $code Token obtained after Spotify user has granted authorization to this client app.
 * @return AccessToken
 */
function request_access_token(string $code): AccessToken
{

    /*@see https://developer.spotify.com/documentation/web-api/tutorials/code-flow (section Request an access token)*/
    $data = [
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => REDIRECT_URI,
    ];

    $token = base64_encode(sprintf("%s:%s", CLIENT_ID, CLIENT_SECRET));

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => ACCESS_TOKEN_URL_SPOTIFY,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data, '', '&', PHP_QUERY_RFC3986),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $token
        ]
    ]);

    $response = curl_exec($ch);

    if ($response == false) {
        dump(curl_errno($ch), curl_error($ch));
        throw new RuntimeException("Impossible d'obtenir l'access token. Vérifier les credentials et réessayer.");
    }

    $response = json_decode($response, true);

    dump($response);

    return new AccessToken(
        $response['access_token'],
        $response['token_type'],
        $response['expires_in'],
        $response['refresh_token'],
    );
}


/**
 * Save refresh token for later use
 *
 * @param AccessToken $token
 * @return void
 */
function save_refresh_token(AccessToken $token): void
{
    $file_refresh_token = fopen('refresh_token', 'w');
    fwrite($file_refresh_token, $token->refresh_token);
    fclose($file_refresh_token);
}


/**
 * Renew access token from previously stored refresh token
 *
 * @param string $refresh_token
 * @return AccessToken
 */
function refresh_access_token(string $refresh_token): AccessToken
{
    /*@see https://developer.spotify.com/documentation/web-api/tutorials/refreshing-tokens */
    $data = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token,
    ];

    $token = base64_encode(sprintf("%s:%s", CLIENT_ID, CLIENT_SECRET));

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => ACCESS_TOKEN_URL_SPOTIFY,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data, '', '&', PHP_QUERY_RFC3986),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $token
        ]
    ]);

    $response = curl_exec($ch);

    if ($response == false) {
        dump(curl_errno($ch), curl_error($ch));
        throw new RuntimeException("Impossible d'obtenir l'access token. Vérifier les credentials et réessayer.");
    }

    $response = json_decode($response, true);

    dump($response);

    return new AccessToken(
        $response['access_token'],
        $response['token_type'],
        $response['expires_in'],
        //If no refresh token, reuse the previous one
        //(@see https://developer.spotify.com/documentation/web-api/tutorials/refreshing-tokens, section Response )
        $response['refresh_token'] ?? $refresh_token,
    );
}
