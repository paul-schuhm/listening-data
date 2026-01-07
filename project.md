# Suivi du projet

- Se (re)former sur serveur local php intégré en HTTPS (stunnel, mkcert), gérer socket TCP en PHP, interface cURL php [x]
- Écrire un programme sans interface web (serveur local) [x]
- Obtenir l'access token [x]
- Ne pas devoir donner auth à chaque fois à l'app cliente, **utiliser refresh_token** [x]

C'est **le but du refresh token** :https://developer.spotify.com/documentation/web-api/tutorials/refreshing-tokens

1. Autorisation initiale : l’utilisateur doit auth : obtiens code + access token + **refresh token**
2. Quand access token expires : utiliser refresh token pour renouveler l'access token *directement*. Ce token porte l'auth donnée initialement.
3. Le refresh token doit être stocké côté client.

- Accéder aux playlists de l'utilisateur []
- Backup des playlists []
- Obtenir les stats d'écoute (top, Spotify ne donne pas beaucoup d'infos... Business is business...) []
- Have fun [x]
- Écrire un bot (cron) qui ajoute quelques musiques recommandées dans une playlist []