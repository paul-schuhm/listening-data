# Listening Data (Spotify)

Récupérer des données et exporter les métadonnées d'écoute auprès de Spotify, notamment pour faire un *backup* du compte (en particulier des *playlists*).

- [Listening Data (Spotify)](#listening-data-spotify)
  - [Installation](#installation)
  - [Lancer le programme](#lancer-le-programme)
  - [Références utiles](#références-utiles)

## Installation

1. **Créez** le fichier de configuration `config.ini` :

    ~~~bash
    cp config.ini.dist config.ini
    ~~~

2. **Renseignez-y** les credentials et données de votre application cliente Spotify.

## Lancer le programme

~~~bash
php index.php
~~~

## Références utiles

- [Spotify for Developers : Web API](https://developer.spotify.com/documentation/web-api), la doc de l'API de Spotify ;
- [Spotify for Developers : Authorization Code Flow](https://developer.spotify.com/documentation/web-api/tutorials/code-flow), le workflow (protocole OAuth 2) pour obtenir un access token lié à un utilisateur authentifié ;
- [Spotify for Developers : Redirect URIs](https://developer.spotify.com/documentation/web-api/concepts/redirect_uri), sur la politique des URI de redirection définie par Spotify ;