# Changelog mqttRing

>**IMPORTANT**
>
>The library used by the plugin requires nodeJS 18 for optimal performance.
>It is therefore recommended that you update Jeedom to version 4.3.19 to avoid abnormal operation.

>**IMPORTANT**
>
>If there is no information about the update, it means that it is only for documentation, translation or text updates.

# 04/12/2023
- Fixed the problem of not deleting the old topic when changing it.

# 12/11/2023
- Unknown commands are no longer displayed in the log. Reactivation possible in configuration.
- On / Off action created for lights.

# 02/11/2023
- Alarm status translated instead of raw data.
- Update ring library to version 5.6.3

# 10/10/2023
- Image of equipment added for greater legibility.
- Added battery information for equipment with batteries.
- Update ring library to version 5.5.2
- Removal of the Status command, which has no use.
- Addition of an "online" command for wifi equipment

# 18/06/2023
- Addition of binary information: Alarm activated. Generic type ALARM_ENABLE_STATE
- Binary information added: Alarm triggered. Generic type ALARM_STATE

# 07/06/2023
- Update ring library to version 5.3.0
- Adding RING INTERCOM state and actions
- Restoring the RTSP stream

>**IMPORTANT**
>
>If you are not receiving notifications after upgrading (Ding,...),
>you must delete all previously authenticated ring-mqtt instances from the Ring Control Center.
>
>![RingControlCenter](../images/retrait_appareils.png)
>
>You will then need to reconnect the plugin as you did when you installed it.

# 14/05/2023
- Update ring library to version 5.2.2.
- Library version displayed in the configuration.
- Alarm message after 1 minute of unavailability instead of immediately.

# 22/12/2022
- First stable release

# 14/12/2022
- First public beta
