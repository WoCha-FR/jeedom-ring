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
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function mqttRing_install() {
  // Install libs
  $pluginId = basename(realpath(__DIR__ . '/..'));
  message::add($pluginId, __('Installation des dépendances', __FILE__));
  $plugin = plugin::byId($pluginId);
  $plugin->dependancy_install();
}

function mqttRing_update() {
  // Mise a jour librairie
  $pluginId = 'mqttRing';
  $dependencyInfo = mqttRing::dependancy_info();
  if (!isset($dependencyInfo['state'])) {
    message::add($pluginId, __('Veuilez vérifier les dépendances', __FILE__));
  } elseif ($dependencyInfo['state'] == 'nok') {
    try {
      $plugin = plugin::byId($pluginId);
      $plugin->dependancy_install();
    } catch (\Throwable $th) {
      message::add($pluginId, __('Cette mise à jour nécessite de réinstaller les dépendances même si elles sont marquées comme OK', __FILE__));
    }
  }
  // Mise a jour Délai Warning
  foreach (eqLogic::byType('mqttRing') as $eqLogic) {
    $cmd = $eqLogic->getCmd('info', 'status');
    if (is_object($cmd)) {
      $cmd->remove();
    }
  }
}

function mqttRing_remove() {
}
