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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
    <label class="col-md-4 control-label">{{Version Librairie RING}}</label>
    <div class="col-md-3">
    <?php
      $file = dirname(__FILE__) . '/../resources/ring-mqtt/package.json';
      $package = array();
      if (file_exists($file)) {
        $package = json_decode(file_get_contents($file), true);
      }
      if (isset($package['version'])){
        config::save('ringmqttVersion', $package['version'], 'mqttRing');
      }
      $localVersion = config::byKey('ringmqttVersion', 'mqttRing', 'N/A');
      $wantedVersion = config::byKey('ringmqttRequire', 'mqttRing', '');
      if ($localVersion != $wantedVersion) {
        echo '<span class="label label-warning">' . $localVersion . '</span><br>';
        echo "<div class='alert alert-danger text-center'>{{Le plugin nécessite la version}} <code>" . $wantedVersion . '</code><br />';
        echo "{{Veuillez relancer les dépendances pour mettre à jour la librairie. Relancez ensuite le démon pour voir la nouvelle version.}}</div>";
      } else {
        echo '<span class="label label-success">' . $localVersion . '</span><br>';
      }
    ?>
    </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Topic racine}}</label>
      <div class="col-md-3">
        <input class="configKey form-control" data-l1key="mqtt::topic" />
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Activation des caméras}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Récupere les caméras, sonnette & chimes}}"></i></sup>
      </label>
      <div class="col-md-4">
        <select class="configKey form-control" data-l1key="ring::cameras">
          <option value="non">{{Non}}</option>
          <option value="oui">{{Oui}}</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Activation des modes}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Modes d'alarmes pour les sites avec caméras uniquement}}"></i></sup>
      </label>
      <div class="col-md-4">
        <select class="configKey form-control" data-l1key="ring::modes">
          <option value="non">{{Non}}</option>
          <option value="oui">{{Oui}}</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Activation des boutons Panique}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Ajouter les boutons Police, Incendie, ...}}"></i></sup>
      </label>
      <div class="col-md-4">
        <select class="configKey form-control" data-l1key="ring::panic">
          <option value="non">{{Non}}</option>
          <option value="oui">{{Oui}}</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Identification RING}}</label>
      <div class="col-md-3">
        <a class="btn btn-default" id="bt_ringAuthPage"><i class="fa fa-paper-plane" aria-hidden="true"></i> {{Ouvrir}}</a>
      </div>
    </div>
  </fieldset>
</form>

<script>
  $('#bt_ringAuthPage').off('clic').on('click', function() {
    PopUpCentre("http://<?php print config::byKey('internalAddr') ?>:55123", 480, 700);
  })

  function PopUpCentre(url, width, height) {
    var leftPosition, topPosition;
    //Allow for borders.
    leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
    //Allow for title and status bars.
    topPosition = (window.screen.height / 2) - ((height / 2) + 50);
    //Open the window.
    nouvellefenetre = window.open(url, "Window2",
      "status=no,height=" + height + ",width=" + width + ",resizable=yes,left=" +
      leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" +
      topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no");

    if (nouvellefenetre) { //securité pour fermer la fenetre si le focus est perdu
      window.onfocus = function () {
        nouvellefenetre.window.close();
      }
    }
  }
  $('body').off('mqttRing::dependancy_end').on('mqttRing::dependancy_end', function(_event, _options) {
    window.location.reload()
  })
</script>