<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class mqttRing extends eqLogic
{
  /* Handle MQTT */
  public static function handleMqttMessage($_message) {
    if (isset($_message[config::byKey('mqtt::topic', __CLASS__, 'ring')])) {
      $message = $_message[config::byKey('mqtt::topic', __CLASS__, 'ring')];
    } else {
      log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Le message reçu n\'est pas un message mqttRing', __FILE__));
      return;
    }
    // Parcours des messages
    foreach( $message as $_key => $_values) {
      if( $_key == 'config') {
        log::add(__CLASS__, 'debug', __('Configuration: ', __FILE__) . json_encode($_values));
        self::handleConfig($_values);
        continue;
      } else {
        // Formattage TOPIC => VALUE
        $datas = implode_recursive($_values, '/');
        // On parcours
        foreach( $datas as $_topic => $_value) {
          $temp = explode('/', $_topic);
          $_eqLogicalId = $_key.'/'.$temp[0].'/'.$temp[1];
          $_cmdLogicalId = implode('/', array_slice($temp, 2));
          // Equipement existant ?
          $eqLogic = self::byLogicalId($_eqLogicalId, __CLASS__);
          if (!is_object($eqLogic)) {
            log::add(__CLASS__, 'warning', __('Equipement Inconnu: ', __FILE__) . $_eqLogicalId);
            continue;
          }
          // Snapshot Camera ? cmdLogicalId se termine par snapshot/image
          if (substr($_cmdLogicalId, -14) == 'snapshot/image') {
            $_img_file = __DIR__ . '/../../data/' . $temp[1] . '.png';
            if ($_value == 'disabled') {
              $_im = imagecreatefrompng(__DIR__ . '/../../data/no-image.png');
            } else {
              $_im = imageCreateFromString(base64_decode($_value));
            }
            // Image OK
            if ($_im !== false) {
              $backcolor = imagecolorallocate($_im, 0, 0, 0);
              $textcolor = imagecolorallocate($_im, 255, 255, 255);
              imagefilledrectangle($_im, 0, 0, 105, 15, $backcolor);
              imagestring($_im, 2, 1, 1, date('j/m/y H:i:s'), $textcolor);
              if (!imagepng($_im, $_img_file, 0)) {
                log::add(__CLASS__, 'warning', __('Sauvegarde snapshot impossible.', __FILE__));
              }
              imagedestroy($_im);
            } else {
              log::add(__CLASS__, 'warning', __('Snapshot invalide reçu.', __FILE__));
            }
            continue;
          }
          // Batterie ? cmdLogicalId se termine par batteryLevel
          if (substr($_cmdLogicalId, -12) == 'batteryLevel') {
            $eqLogic->batteryStatus($_value);
          }
          // Commande Existante
          $cmd = $eqLogic->getCmd('info', $_cmdLogicalId);
          if (!is_object($cmd)) {
            log::add(__CLASS__, 'debug', __('Commande ', __FILE__) . $_cmdLogicalId . __(' inconnue dans l\'equipement ', __FILE__) . $_eqLogicalId);
            continue;
          }
          // Binaire "virtuel" Alarme activée
          // Binaire "virtuel" En Alarme
          if ($_cmdLogicalId == 'alarm/state') {
            if ($_value == 'disarmed') {
              $eqLogic->checkAndUpdateCmd('alarmactive', 0);
            } else {
              $eqLogic->checkAndUpdateCmd('alarmactive', 1);
            }
            if ($_value == 'triggered') {
              $eqLogic->checkAndUpdateCmd('enalarme', 0);
            } else {
              $eqLogic->checkAndUpdateCmd('enalarme', 1);
            }
          }
          // Traitement de la valeur
          $val_ok = array('online', 'on', 'ok', 'locked');
          if( $cmd->getSubType() == 'binary' ) {
            if( in_array(strtolower($_value), $val_ok )) {
              $_value = 1;
            } else {
              $_value = 0;
            }
          }
          // Mise à jour
          $eqLogic->checkAndUpdateCmd($_cmdLogicalId, $_value);
        }
      }
    }
  }

  /* Fonctions Propres au plugin */
  public static function handleConfig( $_values ) {
    // Parcours des configurations
    foreach( $_values as $uniqID => $sensors ) {
      log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Configuration pour ', __FILE__) . $uniqID);
      // alarm_control_panel virtuel
      if (substr($uniqID, -5) == '_mode') {
        $data = $sensors["alarm_control_panel"]["mode"];
      } else {
        $data = $sensors["sensor"]["info"];
      }
      $_start = (strlen(config::byKey('mqtt::topic', __CLASS__, 'ring')) + 1);
      $_eqLogicId = substr($data["availability_topic"], $_start, -7);
      // On recherche
      $eqLogic = self::byLogicalId($_eqLogicId, __CLASS__);
      // Création Equipement si besoin
      if (!is_object($eqLogic)) {
        $eqLogic = new mqttRing();
        $eqLogic->setEqType_name(__CLASS__);
        $eqLogic->setLogicalId($_eqLogicId);
        $eqLogic->setIsEnable(1);
        $eqLogic->setIsVisible(0);
      }
      $eqLogic->setName($data["device"]["name"]);
      $eqLogic->setConfiguration("ringMarque", $data["device"]["mf"]);
      $eqLogic->setConfiguration("ringModele", $data["device"]["mdl"]);
      $eqLogic->save();
      // Dispose d'un statut de connection
      if ($data["connection_topic"] != 'unavailable') {
        $cmd = $eqLogic->getCmd('info', 'connection');
        // Création si besoin
        if (!is_object($cmd)) {
          $cmd = new mqttRingCmd();
          $cmd->setLogicalId('connection');
          $cmd->setEqLogic_id($eqLogic->getId());
          $cmd->setName('online');
          $cmd->setType('info');
          $cmd->setSubType('binary');
          $cmd->setIsVisible(1);
          $cmd->setGeneric_type('GENERIC_INFO');
          $cmd->setTemplate('dashboard', 'core::alert');
          $cmd->setTemplate('mobile', 'core::alert');
          $cmd->setAlert('warningif', '#value#==0');
          $cmd->setAlert('warningduring', '5');
          $cmd->save();
        }
      }
      // Configuration Image & batteries
      if ($data["device"]["mf"] === 'Ring') {
        switch ($data["device"]["mdl"]) {
          case 'Alarm Base Station':
            $eqLogic->setConfiguration("ringImage", "basestation");
            $eqLogic->save();
            break;
          case 'Contact Sensor':
            $eqLogic->setConfiguration("battery_type", "2x3V CR2032");
            $eqLogic->setConfiguration("ringImage", "contact");
            $eqLogic->save();
            break;
          case 'Motion Sensor':
            $eqLogic->setConfiguration("battery_type", "2x1.5V AAA");
            $eqLogic->setConfiguration("ringImage", "motion");
            $eqLogic->save();
            break;
          case 'Glassbreak Sensor':
            $eqLogic->setConfiguration("battery_type", "3x1.5V AAA");
            $eqLogic->setConfiguration("ringImage", "glassbreak");
            $eqLogic->save();
            break;
          case 'Security Keypad':
            $eqLogic->setConfiguration("ringImage", "keypad");
            $eqLogic->save();
            break;
          case 'Z-Wave Range Extender':
            $eqLogic->setConfiguration("ringImage", "extender");
            $eqLogic->save();
            break;
          case 'Intercom':
            $eqLogic->setConfiguration("ringImage", "intercom");
            $eqLogic->save();
            break;
          case 'Chime':
          case 'Chime Pro':
          case 'Chime v2':
          case 'Chime Pro v2':
            $eqLogic->setConfiguration("ringImage", "chime");
            $eqLogic->save();
            break;
          case 'Doorbell':
          case 'Doorbell 2':
          case 'Door View Cam':
          case 'Doorbell 3':
          case 'Doorbell 3 Plus':
          case 'Doorbell Wired':
          case 'Doorbell Pro':
          case 'Doorbell Pro 2':
          case 'Doorbell Elite':
          case 'Doorbell Gen 2':
            $eqLogic->setConfiguration("ringImage", "doorbell");
            $eqLogic->save();
            break;
          case 'Spotlight Cam':
          case 'Spotlight Cam Pro':
            $eqLogic->setConfiguration("ringImage", "spotlight");
            $eqLogic->save();
            break;
          case 'Floodlight Cam':
          case 'Floodlight Pro':
          case 'Floodlight Cam Plus':
            $eqLogic->setConfiguration("ringImage", "floodlight");
            $eqLogic->save();
            break;
          case 'Stick Up Cam':
          case 'Indoor Cam':
            $eqLogic->setConfiguration("ringImage", "camera");
            $eqLogic->save();
            break;
        }
      }
      // Préparation des cmdLogicId
      $_subAdd = (strlen(config::byKey('mqtt::topic', __CLASS__, 'ring')) + 2);
      $_subtopicStart = (strlen($_eqLogicId) + $_subAdd);
      // Parcours des capteurs
      foreach( $sensors as $famille => $_sensors ) {
        switch( $famille ) {
          // Les Sensors spéciaux
          case "sensor":
            foreach( $_sensors as $type => $data ) {
              // Sensor Topic
              $cmdLogicId = substr($data["json_attributes_topic"], $_subtopicStart);
              // Sensor dans arbre JSON
              $_start = (strpos($data['value_template'], '["') + 2);
              $_nbcar = ((strpos($data['value_template'], '"]') - $_start));
              $_jsond = substr($data['value_template'], $_start, $_nbcar );
              $cmdLogicId .= '/' . $_jsond;
              $cmd = $eqLogic->getCmd('info', $cmdLogicId);
              // Création si besoin
              if (!is_object($cmd)) {
                $cmd = new mqttRingCmd();
                $cmd->setLogicalId($cmdLogicId);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setIsVisible(0);
                $cmd->setType('info');
                $cmd->setTemplate('dashboard', 'core::line');
                $cmd->setTemplate('mobile', 'core::line');
                switch( $type ) {
                  case "battery":
                    $cmd->setName($type);
                    $cmd->setSubType('numeric');
                    $cmd->setUnite($data['unit_of_measurement']);
                    $cmd->setGeneric_type('BATTERY');
                    $cmd->setConfiguration('minValue', '0');
                    $cmd->setConfiguration('maxValue', '100');
                    break;
                  case "wireless":
                    $cmd->setName($type);
                    $cmd->setSubType('numeric');
                    $cmd->setUnite($data['unit_of_measurement']);
                    $cmd->setConfiguration('minValue', '-100');
                    $cmd->setConfiguration('maxValue', '0');
                    break;
                  default:
                    // Type of info
                    switch( $_jsond ) {
                      case "acStatus":
                      case "commStatus":
                        $cmd->setName($_jsond);
                        $cmd->setSubType('binary');
                        $cmd->setIsVisible(1);
                        $cmd->setTemplate('dashboard', 'core::alert');
                        $cmd->setTemplate('mobile', 'core::alert');
                        $cmd->setGeneric_type('GENERIC_INFO');
                        break;
                      // lastUpdate, ....
                      default:
                        $cmd->setName($_jsond);
                        $cmd->setSubType('string');
                    }
                  // Fin default
                }
                $cmd->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . $uniqID . ':' . $type);
              }
            }
            break;
          // Parcours des Capteurs Binaires
          case "binary_sensor":
            foreach( $_sensors as $type => $data ) {
              // Sensor Topic
              $cmdLogicId = substr($data["state_topic"], $_subtopicStart);
              // Sensors dans arbre JSON ?
              if( array_key_exists('value_template', $data) ) {
                $_start = (strpos($data['value_template'], '["') + 2);
                $_nbcar = ((strpos($data['value_template'], '"]') - $_start));
                $_jsond = substr($data['value_template'], $_start, $_nbcar );
                if( strlen($_jsond) > 0 ) {
                  $cmdLogicId .= '/' . $_jsond;
                }
              }
              $cmd = $eqLogic->getCmd('info', $cmdLogicId);
              // Création si besoin
              if (!is_object($cmd)) {
                $cmd = new mqttRingCmd();
                $cmd->setLogicalId($cmdLogicId);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName($type);
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setIsVisible(1);
                $cmd->setTemplate('dashboard', 'core::alert');
                $cmd->setTemplate('mobile', 'core::alert');
                if( $type == 'contact') {
                  $cmd->setGeneric_type('OPENING');
                  $cmd->setTemplate('dashboard', 'core::door');
                  $cmd->setTemplate('mobile', 'core::door');
                  $cmd->setDisplay('invertBinary', '1');
                } else if( $type == 'motion' ) {
                  $cmd->setGeneric_type('PRESENCE');
                  $cmd->setTemplate('dashboard', 'core::presence');
                  $cmd->setTemplate('mobile', 'core::presence');
                  $cmd->setDisplay('invertBinary', '1');
                } else if( $type == 'flood' ) {
                  $cmd->setGeneric_type('FLOOD');
                  $cmd->setTemplate('dashboard', 'core::flood');
                  $cmd->setTemplate('mobile', 'core::flood');
                  $cmd->setDisplay('invertBinary', '1');
                } else if( $type == 'tamper' ) {
                  $cmd->setGeneric_type('SABOTAGE');
                } else if( $type == 'tilt' ) {
                  $cmd->setGeneric_type('SHOCK');
                  $cmd->setDisplay('invertBinary', '1');
                } else if( $type == 'smoke' ) {
                  $cmd->setGeneric_type('SMOKE');
                  $cmd->setDisplay('invertBinary', '1');
                } else {
                  $cmd->setGeneric_type('GENERIC_INFO');
                  $cmd->setDisplay('invertBinary', '1');
                }
                $cmd->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . $uniqID . ':' . $type);
              }
            }
            break;
          // Parcours des Intercoms
          case "lock":
            foreach( $_sensors as $type => $data ) {
              $cmdLogicId = substr($data["state_topic"], $_subtopicStart);
              $cmd = $eqLogic->getCmd('info', $cmdLogicId);
              // Création si besoin
              if (!is_object($cmd)) {
                $cmd = new mqttRingCmd();
                $cmd->setLogicalId($cmdLogicId);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName($type);
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setIsVisible(1);
                $cmd->setGeneric_type('LOCK_STATE');
                $cmd->setTemplate('dashboard', 'core::lock');
                $cmd->setTemplate('mobile', 'core::lock');
                $cmd->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . $uniqID . ':' . $type);
              }
              // Commande Action ?
              if( array_key_exists('command_topic', $data) ) {
                // Update commande INFO
                $cmd->setName($type.'_etat');
                $cmd->setIsVisible(0);
                $cmd->save();
                // Racine Commande Action
                $cmdaLogicId = substr($data["command_topic"], $_subtopicStart);
                // Action Fermeture
                $cmdaLogicId_on = $eqLogic->getCmd('action', $cmdaLogicId . '/on');
                if (!is_object($cmdaLogicId_on)) {
                  $cmda = new mqttRingCmd();
                  $cmda->setLogicalId($cmdaLogicId . '/on');
                  $cmda->setEqLogic_id($eqLogic->getId());
                  $cmda->setName($type.'_on');
                  $cmda->setType('action');
                  $cmda->setSubType('other');
                  $cmda->setIsVisible(1);
                  $cmda->setValue($cmd->getId());
                  $cmda->setConfiguration('value', 'intercom');
                  $cmda->setGeneric_type('LOCK_CLOSE');
                  $cmda->setTemplate('dashboard', 'core::lock');
                  $cmda->setTemplate('mobile', 'core::lock');
                  $cmda->setConfiguration('actionConfirm', '1');
                  $cmda->save();
                  log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Action ', __FILE__) . $uniqID . ':' . $type.'_on');
                }
                // Action Ouverture
                $cmdaLogicId_off = $eqLogic->getCmd('action', $cmdaLogicId . '/off');
                if (!is_object($cmdaLogicId_off)) {
                  $cmda = new mqttRingCmd();
                  $cmda->setLogicalId($cmdaLogicId . '/off');
                  $cmda->setEqLogic_id($eqLogic->getId());
                  $cmda->setName($type.'_off');
                  $cmda->setType('action');
                  $cmda->setSubType('other');
                  $cmda->setIsVisible(1);
                  $cmda->setValue($cmd->getId());
                  $cmda->setConfiguration('value', 'intercom');
                  $cmda->setGeneric_type('LOCK_OPEN');
                  $cmda->setTemplate('dashboard', 'core::lock');
                  $cmda->setTemplate('mobile', 'core::lock');
                  $cmda->setConfiguration('actionConfirm', '1');
                  $cmda->save();
                  log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Action ', __FILE__) . $uniqID . ':' . $type.'_off');
                }
              }
            }
            break;
          // Parcours des Numériques
          case "number":
            foreach( $_sensors as $type => $data ) {
              $cmdLogicId = substr($data["state_topic"], $_subtopicStart);
              $cmd = $eqLogic->getCmd('info', $cmdLogicId);
              // Création si besoin
              if (!is_object($cmd)) {
                $cmd = new mqttRingCmd();
                $cmd->setLogicalId($cmdLogicId);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName($type);
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->setIsVisible(0);
                $cmd->setTemplate('dashboard', 'core::line');
                $cmd->setTemplate('mobile', 'core::line');
                $cmd->setConfiguration('minValue', $data["min"]);
                $cmd->setConfiguration('maxValue', $data["max"]);
                if( $type == 'volume') {
                  $cmd->setGeneric_type('VOLUME');
                } else if( $type == 'snapshot_interval') {
                  $cmd->setGeneric_type('TIMER');
                  $cmd->setUnite('s');
                } else {
                  $cmd->setGeneric_type('GENERIC_INFO');
                }
                $cmd->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . $uniqID . ':' . $type);
              }
              // Commande Action ?
              if( array_key_exists('command_topic', $data) ) {
                // Update commande INFO
                $cmd->setName($type.'_etat');
                $cmd->save();
                // Commande Action
                $cmdaLogicId = substr($data["command_topic"], $_subtopicStart);
                $cmda = $eqLogic->getCmd('action', $cmdaLogicId);
                // Création ACTION si besoin
                if (!is_object($cmda)) {
                  $cmda = new mqttRingCmd();
                  $cmda->setLogicalId($cmdaLogicId);
                  $cmda->setEqLogic_id($eqLogic->getId());
                  $cmda->setName($type);
                  $cmda->setType('action');
                  $cmda->setSubType('slider');
                  $cmda->setValue($cmd->getId());
                  $cmda->setConfiguration('minValue', $data["min"]);
                  $cmda->setConfiguration('maxValue', $data["max"]);
                  if( $type == 'volume') {
                    $cmda->setGeneric_type('SET_VOLUME');
                  } else if( $type == 'snapshot_interval') {
                    $cmda->setGeneric_type('SET_TIMER');
                  }
                  $cmda->setTemplate('dashboard', 'core::value');
                  $cmda->setTemplate('mobile', 'core::value');
                  $cmda->save();
                  log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Action ', __FILE__) . $uniqID . ':' . $type);
                }
              }
            }
            break;
          // Parcours des Lampes & interrupteurs
          case "light":
          case "switch" :
            foreach( $_sensors as $type => $data ) {
              if( $type == 'stream' || $type == 'event_stream' ) {
                continue;
              }
              $cmdLogicId = substr($data["state_topic"], $_subtopicStart);
              $cmd = $eqLogic->getCmd('info', $cmdLogicId);
              // Création INFO si besoin
              if (!is_object($cmd)) {
                $cmd = new mqttRingCmd();
                $cmd->setLogicalId($cmdLogicId);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setName($type);
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setIsVisible(0);
                if( $type == 'light') {
                  $cmd->setGeneric_type('LIGHT_STATE_BOOL');
                  $cmd->setTemplate('dashboard', 'core::light');
                  $cmd->setTemplate('mobile', 'core::light');
                } else if( $type == 'siren' || $type == 'fire' || $type == 'police' ) {
                  $cmd->setGeneric_type('SIREN_STATE');
                  $cmd->setTemplate('dashboard', 'default');
                  $cmd->setTemplate('mobile', 'default');
                } else {
                  $cmd->setGeneric_type('GENERIC_INFO');
                  $cmd->setTemplate('dashboard', 'default');
                  $cmd->setTemplate('mobile', 'default');
                }
                $cmd->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . $uniqID . ':' . $type);
              }
              // Commande Action
              $cmdaLogicId = substr($data["command_topic"], $_subtopicStart);
              $cmda = $eqLogic->getCmd('action', $cmdaLogicId);
              // Création ACTION Toggle si besoin
              if (!is_object($cmda)) {
                $cmda = new mqttRingCmd();
                $cmda->setLogicalId($cmdaLogicId);
                $cmda->setEqLogic_id($eqLogic->getId());
                $cmda->setName($type.'_toggle');
                $cmda->setType('action');
                $cmda->setSubType('other');
                $cmda->setValue($cmd->getId());
                $cmda->setConfiguration('value', 'toggle');
                if( $type == 'light') {
                  $cmda->setGeneric_type('LIGHT_TOGGLE');
                  $cmda->setTemplate('dashboard', 'core::light');
                  $cmda->setTemplate('mobile', 'core::light');
                }else if( $type == 'siren' || $type == 'fire' || $type == 'police' ) {
                  $cmda->setGeneric_type('LIGHT_TOGGLE');
                  $cmda->setTemplate('dashboard', 'core::alert');
                  $cmda->setTemplate('mobile', 'core::alert');
                } else {
                  $cmda->setGeneric_type('GENERIC_ACTION');
                  $cmda->setTemplate('dashboard', 'core::toggle');
                  $cmda->setTemplate('mobile', 'core::toggle');
                }
                $cmda->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Action ', __FILE__) . $uniqID . ':' . $type);
              }
            }
            break;
          // Panneau de contôle alarme
          case "alarm_control_panel" :
            if (isset($_sensors["mode"])) {
              $data = $_sensors["mode"]; // Panneau virtuel site Base Station
            } else {
              $data = $_sensors["alarm"]; // Panneau site avec Base Station
            }
            $cmdLogicId = substr($data["state_topic"], $_subtopicStart);
            // Commande alarm/state
            $cmd = $eqLogic->getCmd('info', $cmdLogicId);
            // Création si besoin
            if (!is_object($cmd)) {
              $cmd = new mqttRingCmd();
              $cmd->setLogicalId($cmdLogicId);
              $cmd->setEqLogic_id($eqLogic->getId());
              $cmd->setName('Mode Alarme');
              $cmd->setType('info');
              $cmd->setSubType('string');
              $cmd->setIsVisible(1);
              $cmd->setGeneric_type('ALARM_MODE');
              $cmd->setTemplate('dashboard', 'core::tile');
              $cmd->setTemplate('mobile', 'core::tile');
              $cmd->save();
              log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . $uniqID . ': Alarme');
            }
            // Commande info virtuelle : Etat Alarme Binaire
            $cmd = $eqLogic->getCmd('info', 'alarmactive');
            // Création si besoin
            if (!is_object($cmd)) {
              $cmd = new mqttRingCmd();
              $cmd->setLogicalId('alarmactive');
              $cmd->setEqLogic_id($eqLogic->getId());
              $cmd->setName('Alarme Active');
              $cmd->setType('info');
              $cmd->setSubType('binary');
              $cmd->setIsVisible(1);
              $cmd->setGeneric_type('ALARM_ENABLE_STATE');
              $cmd->setTemplate('dashboard', 'core::lock');
              $cmd->setTemplate('mobile', 'core::lock');
              $cmd->save();
              log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . ' alarmactive : Alarme');
            }
            // Commande info virtuelle : En Alarme
            $cmd = $eqLogic->getCmd('info', 'enalarme');
            // Création si besoin
            if (!is_object($cmd)) {
              $cmd = new mqttRingCmd();
              $cmd->setLogicalId('enalarme');
              $cmd->setEqLogic_id($eqLogic->getId());
              $cmd->setName('En Alarme');
              $cmd->setType('info');
              $cmd->setSubType('binary');
              $cmd->setIsVisible(1);
              $cmd->setGeneric_type('ALARM_STATE');
              $cmd->setTemplate('dashboard', 'core::alert');
              $cmd->setTemplate('mobile', 'core::alert');
              $cmd->save();
              log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Info ', __FILE__) . ' enalarme : Alarme');
            }
            // Commandes actions
            $_modes = array('arm_away','arm_home','disarm');
            // ID de la commande info 'alarme/state'
            $cmdvalue = $eqLogic->getCmd('info', 'alarme/state');
            foreach( $_modes as $mode ) {
              $cmdaLogicId = substr($data["command_topic"], $_subtopicStart) . '%' . $mode;
              $cmda = $eqLogic->getCmd('action', $cmdaLogicId);
              // Création ACTION Toggle si besoin
              if (!is_object($cmda)) {
                $cmda = new mqttRingCmd();
                $cmda->setLogicalId($cmdaLogicId);
                $cmda->setEqLogic_id($eqLogic->getId());
                $cmda->setName($mode);
                $cmda->setType('action');
                $cmda->setSubType('other');
                $cmda->setValue($cmdvalue);
                $cmda->setGeneric_type('ALARM_SET_MODE');
                $cmda->setTemplate('dashboard', 'default');
                $cmda->setTemplate('mobile', 'default');
                $cmda->setConfiguration('value', $mode);
                if( $mode == 'arm_away') {
                  $cmda->setDisplay('icon', '<i class="icon jeedomapp-out icon_red"></i>');
                } else if( $mode == 'arm_home' ) {
                  $cmda->setDisplay('icon', '<i class="icon jeedomapp-in icon_orange"></i>');
                } else {
                  $cmda->setDisplay('icon', '<i class="icon jeedomapp-alarme icon_green"></i>');
                }
                $cmda->save();
                log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ajout commande Action ', __FILE__) . $uniqID . ':' . $mode);
              }
            }
            break;
          // Default
          default:
            log::add(__CLASS__, 'warning', '[' . $uniqID . '] ' . __('Type non géré ', __FILE__) . $famille);
        }
      }
    }
  }

  /* Icones */
  public function getImage() {
    if (file_exists(__DIR__.'/../config/devices/'.  $this->getConfiguration('ringImage').'.png')){
      return 'plugins/mqttRing/core/config/devices/'.  $this->getConfiguration('ringImage').'.png';
    }
    return false;
  }

  /* Dependencies */
  public static function dependancy_info() {
    $return = array();
    $return['log'] = log::getPathToLog(__CLASS__ . '_update');
    $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependence';
    $return['state'] = 'ok';

    if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependence')) {
      $return['state'] = 'in_progress';
    } else {
      if (config::byKey('lastDependancyInstallTime', __CLASS__) == '') {
        $return['state'] = 'nok';
      } else if (!file_exists('/usr/local/bin/go2rtc')) {
        $return['state'] = 'nok';
      } else if (filesize('/usr/local/bin/go2rtc') == 0) {
        $return['state'] = 'nok';
      } else if (!file_exists(__DIR__ . '/../../resources/ring-mqtt/ring-mqtt.js')) {
        $return['state'] = 'nok';
      } else if (!is_dir(realpath(dirname(__FILE__) . '/../../resources/ring-mqtt/node_modules'))) {
        $return['state'] = 'nok';
      } else if (config::byKey('ringmqttRequire', __CLASS__) != config::byKey('ringmqttVersion', __CLASS__)) {
        $return['state'] = 'nok';
      }
    }
    return $return;
  }

  public static function dependancy_end() {
    config::save('ringmqttVersion', config::byKey('ringmqttRequire', __CLASS__), __CLASS__);
  }

  /* Deamon */
  public static function deamon_start() {
    self::deamon_stop();
    $deamon_info = self::deamon_info();
    if ($deamon_info['launchable'] != 'ok') {
      throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
    }

    mqtt2::addPluginTopic(__CLASS__, config::byKey('mqtt::topic', __CLASS__, 'ring'));
    $mqttInfos = mqtt2::getFormatedInfos();
    log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Informations reçues de MQTT Manager', __FILE__) . ' : ' . json_encode($mqttInfos));
    $mqtt_url = ($mqttInfos['port'] === 1883) ? 'mqtts://' : 'mqtt://';
    $mqtt_url .= ($mqttInfos['password'] === null) ? '' : $mqttInfos['user'].':'.$mqttInfos['password'].'@';
    $mqtt_url .= $mqttInfos['ip'].':'.$mqttInfos['port'];

    $appjs_path = realpath(dirname(__FILE__) . '/../../resources/ring-mqtt');
    chdir($appjs_path);

    $appjs_debug = '';
    if (log::convertLogLevel(log::getLogLevel(__CLASS__)) == 'debug') {
      $appjs_debug = 'DEBUG=ring-mqtt,ring-rtsp ';
    }
    $cmd = $appjs_debug . '/usr/bin/node ' . $appjs_path . '/ring-mqtt.js';

    $config = [
      'mqtt_url' => $mqtt_url,
      'mqtt_options' => '',
      'livestream_user' => '',
      'livestream_pass' => '',
      'enable_cameras'=> false,
      'enable_modes' => false,
      'enable_panic' => false,
      'hass_topic' => config::byKey('mqtt::topic', __CLASS__, 'ring') . '/jeedom',
      'ring_topic' => config::byKey('mqtt::topic', __CLASS__, 'ring'),
      'location_ids' => ['']
    ];
    // Activation caméras
    if ( config::byKey('ring::cameras', __CLASS__, 'non') !== 'non') {
      $config['enable_cameras'] = true;
    }
    // Activation des modes virtuels (site sans base station)
    if ( config::byKey('ring::modes', __CLASS__, 'non') !== 'non') {
      $config['enable_modes'] = true;
    }
    // Activation des boutons panique
    if ( config::byKey('ring::panic', __CLASS__, 'non') !== 'non') {
      $config['enable_panic'] = true;
    }
    file_put_contents('config.json', json_encode($config));

    log::add(__CLASS__, 'info', __('Démarrage du démon mqttRing', __FILE__) . ' : ' . $cmd);
    exec(system::getCmdSudo() . $cmd . ' >> ' . log::getPathToLog('mqttRingd') . ' 2>&1 &');
    $i = 0;
    while ($i < 30) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] == 'ok') {
        break;
      }
      sleep(1);
      $i++;
    }
    if ($i >= 30) {
      mqtt2::removePluginTopic(config::byKey('mqtt::topic', __CLASS__, 'ring'));
      log::add(__CLASS__, 'error', __('Impossible de démarrer le démon mqttRing, consultez les logs', __FILE__), 'unableStartDeamon');
      return false;
    }
    message::removeAll(__CLASS__, 'unableStartDeamon');
    return true;
  }

  public static function deamon_stop() {
    log::add(__CLASS__, 'info', __('Arrêt du démon mqttRing', __FILE__));
    $find = 'ring-mqtt/ring-mqtt.js';
    $cmd = "(ps ax || ps w) | grep -ie '" . $find . "' | grep -v grep | awk '{print $1}' | xargs " . system::getCmdSudo() . "kill -15 > /dev/null 2>&1";
    exec($cmd);
    $i = 0;
    while ($i < 5) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] == 'nok') {
        break;
      }
      sleep(1);
      $i++;
    }
    if ($i >= 5) {
      system::kill($find, true);
      $i = 0;
      while ($i < 5) {
        $deamon_info = self::deamon_info();
        if ($deamon_info['state'] == 'nok') {
          break;
        }
        sleep(1);
        $i++;
      }
    }
    mqtt2::removePluginTopic(config::byKey('mqtt::topic', __CLASS__, 'ring'));
  }

  public static function deamon_info() {
    $return = array();
    $return['log'] = __CLASS__;
    $return['launchable'] = 'ok';
    $return['state'] = 'nok';
    if (self::isRunning()) {
      $return['state'] = 'ok';
    }
    if (!class_exists('mqtt2')) {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Le plugin MQTT Manager n\'est pas installé', __FILE__);
    } else {
      if (mqtt2::deamon_info()['state'] != 'ok') {
        $return['launchable'] = 'nok';
        $return['launchable_message'] = __('Le démon MQTT Manager n\'est pas démarré', __FILE__);
      }
    }
    // Dépendances
    if (self::dependancy_info()['state'] == 'nok') {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Dépendances non installées.', __FILE__);
    }
    return $return;
  }

  public static function isRunning() {
    if (!empty(system::ps('ring-mqtt/ring-mqtt.js'))) {
      return true;
    }
    return false;
  }
}

class mqttRingCmd extends cmd
{
  public function execute($_options = array()) {
    if ($this->getType() != 'action') {
       return;
    }
    $eqLogic = $this->getEqLogic();
    $rooTopic = config::byKey('mqtt::topic', 'mqttRing', 'ring') . '/' . $eqLogic->getLogicalid();
    $subTopic = $this->getLogicalId();

    switch ($this->getSubType()) {
      case 'slider':
        $value = strval(floor($_options['slider']));
        break;
      case 'other' :
        // Change Mode Alarm
        if( strpos( $subTopic, '%') !== false ) {
          list( $subTopic, ) = explode('%', $subTopic);
          $value = $this->getConfiguration('value');
        // Toggle
        } else if( $this->getConfiguration('value') == 'toggle' ) {
          $valCmd = cmd::byId($this->getValue());
          if ($valCmd->execCmd() == '0') {
            $value = 'ON';
          } else {
            $value = 'OFF';
          }
        // Intercom
        } else if( $this->getConfiguration('value') == 'intercom' ) {
          if ($subTopic == 'lock/command/on') {
            $subTopic = 'lock/command';
            $value = 'LOCK';
          } else {
            $subTopic = 'lock/command';
            $value = 'UNLOCK';
          }
        // Commande Utilisateur
        } else {
          $value = $this->getConfiguration('message');
        }
        break;
      default:
        $value = $this->getConfiguration('message');
        break;
    }

    log::add('mqttRing', 'debug', 'ACTION: ' . json_encode($_options));
    log::add('mqttRing', 'info', 'ACTION: ' . $rooTopic . '/' . $subTopic . ' => ' . $value);
    mqtt2::publish($rooTopic . '/' . $subTopic, $value);
 }
}
