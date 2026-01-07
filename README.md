# Listening Data (Spotify)

Récupérer des données et exporter les métadonnées d'écoute auprès de Spotify, notamment pour faire un *backup* du compte (en particulier des *playlists*).

- [Listening Data (Spotify)](#listening-data-spotify)
  - [Installation](#installation)
  - [Lancer le programme](#lancer-le-programme)
    - [Serveur local HTTPS avec `stunnel`, comment cela fonctionne ?](#serveur-local-https-avec-stunnel-comment-cela-fonctionne-)
    - [Précautions](#précautions)
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

### Serveur local HTTPS avec `stunnel`, comment cela fonctionne ?

1. Demander `https://localhost` dans le navigateur initie une connexion TCP vers `127.0.0.1:443` ;
2. Négociation TLS a lieu (certificat fourni par `stunnel`, crée avec `mkcert`), `stunnel` fait le TLS *handshake* avec le navigateur ;
3. `stunnel` crée redirige vers `127.0.0.1:5005`, la connexion TCP sortante ;
4. Transmission *byte-for-byte* du flux HTTP déchiffré vers le dameon PHP (serveur web intégré PHP) ;
5. PHP s'exécute, écrit sur la socket TCP acceptée par le serveur (d'ailleurs <http://localhost:5005> fonctionne toujours comme avant !), sur la sortie standard du point de vue de PHP. Pour être tout à fait précis : PHP écrit la réponse HTTP sur `STDOUT`, que le SAPI `cli-server` envoie sur la socket TCP associée au client ;
6. La réponse arrive sur la socket TCP ouverte par stunnel vers `:5005`. `stunnel` :
   1. lit le flux HTTP en clair,
   2. chiffre les octets via la session TLS existante,
   3. les renvoie au navigateur sur la socket `:443` ;
7. Le navigateur :
   1. déchiffre le flux,
   2. traite la réponse comme une réponse HTTPS normale.

Le trafic est *bidirectionnel* et *symétrique*.

> C'est parfait pour tester le protocole OAuth ou les cookies Secure en local !

### Précautions

Ne pas **rendre publique la clé du CA local** (`localhost-key.pem`) et donc le bundle crée (`localhost-bundle.pem`).

Si un attaquant récupère clé privée, il peut fabriquer des **certificats signés par mon CA local**, donc **immédiatement trustés par mon navigateur** (car j'ai ajouté au trust store navigateur/OS avec `mkcert -install`). Si l'attaquant lance un processus *daemon* sur le port 443, lorsque je vais sur <https://localhost> je dialogue avec *son* processus et non mon service (par exemple, mon serveur web intégré PHP) ! Et mon navigateur n'y verra *que du feu* car le CA est valide. Alors les cookies, tokens et credentials se retrouvent exposés et accessibles à l'attaquant. Ainsi, le navigateur parle bien à *ma machine* mais il ne parle pas forcément à *mon service* ! Donc, attention.

> C'est pourquoi on a mis les fichiers crées par mkcert avec perms `600` (acces propriétaire seulement)

## Références utiles

- [Spotify for Developers : Web API](https://developer.spotify.com/documentation/web-api), la doc de l'API de Spotify ;
- [Spotify for Developers : Authorization Code Flow](https://developer.spotify.com/documentation/web-api/tutorials/code-flow), le workflow (protocole OAuth 2) pour obtenir un access token lié à un utilisateur authentifié ;
- [Spotify for Developers : Redirect URIs](https://developer.spotify.com/documentation/web-api/concepts/redirect_uri), sur la politique des URI de redirection définie par Spotify ;
- [mkcert](https://github.com/FiloSottile/mkcert), mkcert is a simple tool for making **locally-trusted development certificates**. It requires no configuration. Pour nous permettre d'enregistrer une URL de redirection (cf protocole OAuth) locale en HTTPS auprès de Spotify ;
- [How to use HTTPS on localhost with PHP built in web server (and run WordPress)](https://www.youtube.com/watch?v=sDAX1uQzM8Y), vidéo YouTube qui explique comment utiliser [mkcert (CA local)](https://github.com/FiloSottile/mkcert) et [stunnel](https://www.stunnel.org/) pour utiliser le serveur local de PHP avec un certificat TLS (pas nativement supporté, d'où l'usage de `stunnel`).
