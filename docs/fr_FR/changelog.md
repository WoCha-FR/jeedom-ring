# Changelog mqttRing

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

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
