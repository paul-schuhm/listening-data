# Listening Data (Spotify)

Un client Spotify pour faire un backup des playlists, monitorer l'activité d'écoute (tops) et analyser le contenu des playlists.

- [Listening Data (Spotify)](#listening-data-spotify)
  - [Installer les programmes](#installer-les-programmes)
  - [Utiliser](#utiliser)
    - [Réaliser un backup de vos playlists](#réaliser-un-backup-de-vos-playlists)
    - [Afficher l'activité d'écoute (top tracks et artistes) sur différentes périodes](#afficher-lactivité-découte-top-tracks-et-artistes-sur-différentes-périodes)
    - [Analyser le contenu des playlists sauvegardées (artistes, etc.)](#analyser-le-contenu-des-playlists-sauvegardées-artistes-etc)
  - [Références utiles](#références-utiles)

## Installer les programmes

> Prérequis : [installer PHP 8+](https://www.php.net/downloads.php).

1. **Créez** le fichier de configuration `config.ini` :

    ~~~bash
    cp config.ini.dist config.ini
    ~~~

2. **Renseignez-y** les *credentials* et données de [votre application cliente Spotify](https://developer.spotify.com/documentation/web-api/concepts/apps).
3. Rendre les programmes **exécutables** :

    ~~~bash
    chmod +x backup-playlists show-activity analyze-playlists
    ~~~

## Utiliser

### Réaliser un backup de vos playlists

~~~bash
./backup-playlists
~~~

> Sauve vos playlists publiques, privées, collaboratives ou non ainsi que vos titres likés ('Your Music')

### Afficher l'activité d'écoute (top tracks et artistes) sur différentes périodes

~~~bash
./show-activity
~~~

### Analyser le contenu des playlists sauvegardées (artistes, etc.)

~~~bash
./analyze-playlists
~~~

## Références utiles

- [Spotify for Developers : Web API](https://developer.spotify.com/documentation/web-api), la doc de l'API de Spotify ;
- [Spotify for Developers : Authorization Code Flow](https://developer.spotify.com/documentation/web-api/tutorials/code-flow), le workflow (protocole OAuth 2) pour obtenir un access token lié à un utilisateur authentifié ;
- [Spotify for Developers : Redirect URIs](https://developer.spotify.com/documentation/web-api/concepts/redirect_uri), sur la politique des URI de redirection définie par Spotify ;
