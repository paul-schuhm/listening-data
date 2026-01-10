# Listening Data (Spotify)

Récupérer des données et exporter les métadonnées d'écoute auprès de Spotify, notamment pour faire un *backup* du compte (en particulier des *playlists*).

- [Listening Data (Spotify)](#listening-data-spotify)
  - [Installer](#installer)
  - [Utiliser](#utiliser)
    - [Réaliser un backup des playlists](#réaliser-un-backup-des-playlists)
    - [Afficher activité d'écoute (top tracks, artistes) sur différentes périodes](#afficher-activité-découte-top-tracks-artistes-sur-différentes-périodes)
  - [Références utiles](#références-utiles)

## Installer

1. **Créez** le fichier de configuration `config.ini` :

    ~~~bash
    cp config.ini.dist config.ini
    chmod +x backup-playlists show-activity
    ~~~

2. **Renseignez-y** les credentials et données de votre application cliente Spotify.

## Utiliser

### Réaliser un backup des playlists

~~~bash
./backup-playlists
~~~

### Afficher activité d'écoute (top tracks, artistes) sur différentes périodes

~~~bash
./show-activity
~~~

## Références utiles

- [Spotify for Developers : Web API](https://developer.spotify.com/documentation/web-api), la doc de l'API de Spotify ;
- [Spotify for Developers : Authorization Code Flow](https://developer.spotify.com/documentation/web-api/tutorials/code-flow), le workflow (protocole OAuth 2) pour obtenir un access token lié à un utilisateur authentifié ;
- [Spotify for Developers : Redirect URIs](https://developer.spotify.com/documentation/web-api/concepts/redirect_uri), sur la politique des URI de redirection définie par Spotify ;
