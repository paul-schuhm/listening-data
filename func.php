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
    ) {}
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
 * Authenticates the user with Spotify and returns a valid access token.
 * If a refresh token is available locally, it is used to obtain a new
 * access token without user interaction. Otherwise, the OAuth
 * authorization flow is triggered and the resulting refresh token
 * is stored for future use.
 *
 * @return AccessToken
 */
function connect(): AccessToken
{

    $access_token = null;

    //if no available refresh token, ask first auth from Spotify user
    if (!file_exists('refresh_token')) {
        //Obtain authorization : requires web form validation
        $code = ask_for_auth();
        $access_token = request_access_token($code);
        //Store refresh token to store authorization and reuse it next time.
        save_refresh_token($access_token);
    } else {
        //Ask new access token from refresh token (skip auth.)
        $refresh_token = file_get_contents('refresh_token');
        $access_token = refresh_access_token($refresh_token);
    }

    if ($access_token === null) {
        throw new RuntimeException("Impossible de se connecter au compte utilisateur. Réessayer.");
    }

    return $access_token;
}


/**
 * Ask user for auth (OAuth 2 flow) to impersonate him
 *
 * @return string authorization code
 */
function ask_for_auth(): string
{

    /*@see https://developer.spotify.com/documentation/web-api/concepts/scopes*/
    $scopes = ['playlist-read-private', 'user-top-read', 'user-library-read'];

    $query_params = array(
        'client_id'     => CLIENT_ID,
        'redirect_uri'  => REDIRECT_URI,
        /*@see https://developer.spotify.com/documentation/web-api/tutorials/code-flow/ */
        'response_type' => 'code',
        'scope' => implode(' ', $scopes)
    );

    $auth_url = sprintf("%s?%s", AUTHORIZE_URL, http_build_query($query_params));

    //Redirection vers la page d'authentification user de Spotify (web form)
    //Remarque : je ne pense pas que cette instruction soit portable...
    exec("xdg-open '$auth_url' >/dev/null 2>&1");

    //Handle redirect URI from the browser by opening a socket
    $socket = stream_socket_server('tcp://' . REDIRECT_URI, $errno, $errstr);
    $connexion = stream_socket_accept($socket);

    $request = fread($connexion, 1024);

    //Extract 'code' from the URL(request arg URL ?code=XXXXxxxx)
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
 * Return the Spotify access token once the user has authenticated and granted authorization
 *
 * @param string $code Token obtained after Spotify user has granted authorization to this client app
 * @return AccessToken Necessary for any further requests
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

    return new AccessToken(
        $response['access_token'],
        $response['token_type'],
        $response['expires_in'],
        //If no refresh token, reuse the previous one
        //(@see https://developer.spotify.com/documentation/web-api/tutorials/refreshing-tokens, section Response )
        $response['refresh_token'] ?? $refresh_token,
    );
}


/**
 * Send an HTTP request
 *
 * @param string $ressource
 * @param AccessToken $access_token
 * @param string $method. Default : GET
 * @param string $format. Default : ARRAY (call json_decode)
 * @return mixed
 */
function request(string $ressource, AccessToken $access_token, string $method = 'GET', string $format = 'ARRAY'): mixed
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => BASE_URL . $ressource,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token->value
        ]
    ]);

    $response = curl_exec($ch);

    if ($response == false) {
        dump(curl_errno($ch), curl_error($ch));
        throw new RuntimeException("Une erreur est survenue.");
    }

    if ($format === 'ARRAY') {
        $response = json_decode($response, true);
    }

    return $response;
}

/**
 * Print the playlist information on a single line
 *
 * @param array $playlist
 * @return void
 */
function printf_playlist_data(array $playlist): void
{

    if (!is_array($playlist) || !isset($playlist['name'])) {
        return;
    }

    $width = 33;

    $pad = $width - mb_strwidth($playlist['name'], 'UTF-8');

    printf(
        "- playlist: %s%s | Number of tracks: %3d | Owner : %s\n",
        $playlist['name'],
        str_repeat(' ', max(0, $pad)),
        intval($playlist['tracks']['total']),
        $playlist['owner']['display_name']
    );
}


/**
 * Save all of the user's playlists locally as JSON.
 *
 * @param AccessToken $access_token
 * @param string $which_one Which playlists to save ? Default: ALL. Possible values : 'OWNED', 'ALL'
 * @return void
 */
function backup_playlists(AccessToken $access_token, string $current_user_id, string $which_one = 'ALL')
{
    $playlists = request('/me/playlists', $access_token);

    printf("Total number of playlists : %d\n", intval($playlists['total']));

    $playlist_to_save = [];

    foreach ($playlists['items'] as $playlist) {

        $SAVE = false;

        switch ($which_one) {
            case 'ALL':
                $playlist_to_save[] = $playlist;
                $SAVE = true;
                break;
            case 'OWNED_ONLY':
                if ($playlist['owner']['id'] === $current_user_id) {
                    $playlist_to_save[] = $playlist;
                    $SAVE = true;
                }
                break;
        }

        if ($SAVE) {
            printf_playlist_data($playlist);
        }
    }

    printf("Playlists to save (%s): %d\n", $which_one, count($playlist_to_save));

    $position = 0;

    foreach ($playlist_to_save as $playlist) {

        //Keep only track metadata i'm interested in
        //@see https://developer.spotify.com/documentation/web-api/reference/get-playlists-tracks
        $query_params_filter_track_data = http_build_query([
            'fields' => 'items(track(name,href,track_number,uri, popularity, duration_ms, external_urls,album(name,href), artists(name)))'
        ]);
        $ressource = sprintf("/playlists/%s/tracks?%s", $playlist['id'], $query_params_filter_track_data);
        $tracks = request($ressource, $access_token, method: 'GET', format: 'ROW_JSON');
        save_playlist_locally($playlist, $tracks, ++$position, count($playlist_to_save));
    }
}

/**
 * Save locally the special playlist 'Your music' (Liked tracks)
 *
 * @param AccessToken $access_token
 * @return void
 */
function backup_liked_tracks(AccessToken $access_token)
{
    //Liked tracks list ('Your music') is paginated, max 50 per page.
    //@see https://developer.spotify.com/documentation/web-api/reference/get-users-saved-tracks
    $tracks = [];
    $offset = 0;
    $limit = 50;

    printf("Collecting saved tracks data :\n");
    do {
        $query_params = http_build_query([
            'offset' => $offset,
            'limit' => $limit,
            'fields' => 'next, total, items(track(name,href,uri,external_urls,album(name,href),artists(name)))'
        ]);

        $resource = sprintf("/me/tracks?%s", $query_params);
        $response = request($resource, $access_token);
        if (isset($response['items'])) {
            $tracks = array_merge($tracks, $response['items']);
        }

        printf("%d/%d (%02.1f%%)\n", count($tracks), $response['total'], count($tracks) / $response['total'] * 100);

        $offset += $limit;
    } while (isset($response['next'])); //next paginated page

    $playlist = [
        'name' => 'saved_tracks'
    ];

    save_playlist_locally($playlist, json_encode($tracks));
}


/**
 * Sanitize the playlist name to produce a valid, safe filename
 *
 * @param string $name Le nom de la playlist
 * @return string
 */
function format_2_filename(string $name): string
{
    $name = trim($name);
    $name = str_replace(" ", "-", $name);
    $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    $name = strip_tags($name);
    $name = strtolower($name);
    return $name;
}


/**
 * Save a copy of the playlist tracks in JSON format to a text file named after the playlist
 *
 * @param array $playlist Playlist to save. Key 'name' required
 * @param string $tracks List of tracks (JSON format)
 * @param null|integer $position. Optional. Position of the playlist in the playlist queue
 * @param null|integer $total. Optional. Total number of playlists in the queue
 * @return integer|boolean
 */
function save_playlist_locally(array $playlist, string $tracks, ?int $position = null, ?int $total = null): int|bool
{
    if (!defined('BACKUP_DIR')) {
        throw new RuntimeException("La valeur BACKUP_DIR (PATH où sauver les playlists) n'est pas défini. Le définir est relancer le programme.");
    }
    $dir = BACKUP_DIR;

    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        throw new RuntimeException("Impossible de créer $dir. Revoir les permissions sur le path concerné et relancer le programme.");
    }

    //les playlists ont un historique de versions (snapshot), on enregistre donc les playlists dans leur dernier état.
    $file_playlist = sprintf("$dir/%s.json", format_2_filename($playlist['name']));
    $file = fopen($file_playlist, 'w');
    $res = fwrite($file, $tracks);
    fclose($file);

    if ($res != false) {
        if ($position == null || $total == null) {
            printf("Playlist %s saved\n", $playlist['name']);
        } else {
            printf(" - (%d/%d) Playlist %s saved\n", $position, $total, $playlist['name']);
        }
    }

    return $res;
}
