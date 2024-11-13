# Changelog mqttRing - BETA

>**IMPORTANT**
>
>The library used by the plugin requires nodeJS 20 for optimal performance.
>It is therefore recommended that you update Jeedom to version 4.4.17 under debian 11 to avoid abnormal operation.

>**IMPORTANT**
>
>If there is no information about the update, it means that it is only for documentation, translation or text updates.

# 13/11/2024
- Update ring library to version 5.7.2

# 17/09/2024
- Debian 11 minimum
- NodeJS 20 minimum
- Update ring library to version 5.7.1

# 30/08/2024
- Update ring library to version 5.6.7

# 15/01/2023
- Add picture for Outdoor Siren

# 04/12/2023
- Fixed the problem of not deleting the old topic when changing it.

# 12/11/2023
- Unknown commands are no longer displayed in the log. Reactivation possible in configuration.
- On / Off action created for lights.

# 02/11/2023
- Alarm status translated instead of raw data.

# 31/10/2023
- Update ring library to version 5.6.3

# 03/10/2023
- Image of equipment added for greater legibility.
- Added battery information for equipment with batteries.

# 02/10/2023
- Update ring library to version 5.5.2
- Removal of the Status command, which has no use.
- Addition of an "online" command for wifi equipment

# 18/06/2023
- Addition of binary information: Alarm activated. Generic type ALARM_ENABLE_STATE
- Binary information added: Alarm triggered. Generic type ALARM_STATE

# 05/06/2023
- Update ring library to version 5.3.0

# 26/05/2023
- Adding RING INTERCOM state and actions.
- Restoring the RTSP stream

# 13/05/2023
- Update ring library to version 5.2.2.
- Library version displayed in the configuration.
- Alarm message after 1 minute of unavailability instead of immediately.

# 14/12/2022
- First public beta
