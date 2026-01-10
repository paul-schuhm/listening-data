# Suivi du projet

- Se (re)former sur serveur local php intégré en HTTPS (stunnel, mkcert), gérer socket TCP en PHP, interface cURL php (optionnel : non retenu car possibilité de renseigner une URL de redirection http pour le test et appli entièrement CLI) [x]
- Écrire un programme **sans interface web** (ni serveur web local) [x]
- Obtenir l'access token [x]
- Ne pas devoir demander à l'user son autorisation à chaque fois. **Utiliser refresh_token** [x]
C'est [**le but du refresh token**](https://developer.spotify.com/documentation/web-api/tutorials/refreshing-tokens) :

1. Autorisation initiale : l’utilisateur doit auth : obtiens code + access token + **refresh token**
2. Quand access token expires : utiliser refresh token pour renouveler l'access token *directement*. Ce token porte l'auth donnée initialement.
3. Le refresh token doit être stocké côté client.

- Accéder aux playlists (public et privée) de l'utilisateur [x]
- Backup des playlists au format JSON avec filtre des métadonnées [x]
- Obtenir les stats d'écoute (*tops* seulement, Spotify ne donne pas beaucoup d'infos... *Business is business*...) [x]
- Écrire un bot (cron) qui ajoute dans une playlist dédiée des musiques recommandées à partir des infos du profil et de l'activité d'écoute []
- *Have fun !* [x]
