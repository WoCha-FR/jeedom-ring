# Changelog mqttRing

>**IMPORTANT**
>
>La librairie utilisée par le plugin nécessite nodeJS 18 pour un fonctionnement optimal.
>Il est donc recommandé de mettre a jour Jeedom à la version 4.3.19 pour éviter des fonctionnements anormaux.

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 15/01/2023
- Ajout image Outdoor Siren

# 04/12/2023
- Correction erreur de non suppression de l'ancien topic lors d'un changement de ce dernier.

# 12/11/2023
- Les commandes inconnues ne sont plus affichés dans le log. Réactivation possible dans la configuration.
- Création action On / Off pour les lumières.

# 02/11/2023
- Etat de l'alarme traduit au lieu de la données brute.
- Mise à jour librairie ring à la version 5.6.3

# 10/10/2023
- Ajout image des équipements pour plus de lisibilité.
- Ajout information des piles pour les équipements qui en dispose.
- Mise à jour librairie ring à la version 5.5.2
- Retrait commande Status qui n'à pas d'utilité.
- Ajout d'une commande "online" pour les équipements wifi

# 18/06/2023
- Ajout d'une information binaire : Alarme activée. Type générique ALARM_ENABLE_STATE
- Ajout d'une information binaire : Alarme déclenchée. Type générique ALARM_STATE

# 07/06/2023
- Mise à jour librairie ring à la version 5.3.0
- Ajout des infos & actions de RING INTERCOM.
- Restauration du flux RTSP

>**IMPORTANT**
>
>Si vous ne recevez pas de notifications après la mise à niveau (Sonnette,...),
>vous devez supprimer toutes les instances ring-mqtt précédemment authentifiées du Ring Control Center.
>
>![RingControlCenter](../images/retrait_appareils.png)
>
>Il vous faudras ensuite reconnecter le plugin comme lors de son installation.

# 14/05/2023
- Mise à jour librairie ring à la version 5.2.2.
- Affichage version librairie dans la configuration.
- Message d'alarme après 1 minute d'indisponibilité au lieu d'immédiatement.

# 22/12/2022
- Première version stable

# 14/12/2022
- Première beta publique
