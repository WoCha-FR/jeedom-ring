# Changelog mqttRing - BETA

>**IMPORTANT**
>
>La librairie utilisée par le plugin nécessite nodeJS 20 pour un fonctionnement optimal.
>Il est donc recommandé de mettre a jour Jeedom à la version 4.4.17 sous debian 11 pour éviter des fonctionnements anormaux.

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 27/04/2025
- Mise à jour librairie ring à la version 5.7.3
- Possibilité de ne récupérer qu'un seul site

# 13/11/2024
- Mise à jour librairie ring à la version 5.7.2

# 17/09/2024
- Debian 11 minimum
- NodeJS 20 minimum
- Mise à jour librairie ring à la version 5.7.1

# 30/08/2024
- Mise à jour librairie ring à la version 5.6.7

# 15/01/2023
- Ajout image Outdoor Siren

# 04/12/2023
- Correction erreur de non suppression de l'ancien topic lors d'un changement de ce dernier.

# 12/11/2023
- Les commandes inconnues ne sont plus affichés dans le log. Réactivation possible dans la configuration.
- Création action On / Off pour les lumières.

# 02/11/2023
- Etat de l'alarme traduit au lieu de la données brute.

# 31/10/2023
- Mise à jour librairie ring à la version 5.6.3

# 03/10/2023
- Ajout image des équipements pour plus de lisibilité.
- Ajout information des piles pour les équipements qui en dispose.

# 02/10/2023
- Mise à jour librairie ring à la version 5.5.2
- Retrait commande Status qui n'à pas d'utilité.
- Ajout d'une commande "online" pour les équipements wifi

# 18/06/2023
- Ajout d'une information binaire : Alarme activée. Type générique ALARM_ENABLE_STATE
- Ajout d'une information binaire : Alarme déclenchée. Type générique ALARM_STATE

# 05/06/2023
- Mise à jour librairie ring à la version 5.3.0

# 26/05/2023
- Ajout des infos & actions de RING INTERCOM.
- Restauration du flux RTSP

# 13/05/2023
- Mise à jour librairie ring à la version 5.2.2.
- Affichage version librairie dans la configuration.
- Message d'alarme après 1 minute d'indisponibilité au lieu d'immédiatement.

# 14/12/2022
- Première beta publique
