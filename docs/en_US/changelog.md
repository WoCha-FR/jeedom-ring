# Changelog mqttRing

>**IMPORTANT**
>
>The library used by the plugin requires nodeJS 20 for optimal performance.
>It is therefore recommended that you update Jeedom to version 4.4.17 under debian 11 to avoid abnormal operation.

>**IMPORTANT**
>
>If there is no information about the update, it means that it is only for documentation, translation or text updates.

# 2025-06-13
- Update ring library to version 5.8.1

# 2025-05-05
- Update ring library to version 5.7.3
- Only one location can be retrieved

# 2024-11-13
- Update ring library to version 5.7.2

# 2024-09-17
- Debian 11 minimum
- NodeJS 20 minimum
- Update ring library to version 5.7.1

# 2024-09-02
- Update ring library to version 5.6.7

# 2023-01-15
- Add picture for Outdoor Siren

# 2023-12-04
- Fixed the problem of not deleting the old topic when changing it.

# 2023-11-12
- Unknown commands are no longer displayed in the log. Reactivation possible in configuration.
- On / Off action created for lights.

# 2023-11-02
- Alarm status translated instead of raw data.
- Update ring library to version 5.6.3

# 2023-10-10
- Image of equipment added for greater legibility.
- Added battery information for equipment with batteries.
- Update ring library to version 5.5.2
- Removal of the Status command, which has no use.
- Addition of an "online" command for wifi equipment

# 2023-06-18
- Addition of binary information: Alarm activated. Generic type ALARM_ENABLE_STATE
- Binary information added: Alarm triggered. Generic type ALARM_STATE

# 2023-06-07
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

# 2023-05-14
- Update ring library to version 5.2.2.
- Library version displayed in the configuration.
- Alarm message after 1 minute of unavailability instead of immediately.

# 2022-12-22
- First stable release

# 2022-12-14
- First public beta
