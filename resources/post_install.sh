#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Post installation de l'installation/mise à jour des dépendances mqttRing"

PROGRESS_FILE=/tmp/jeedom_install_in_progress_mqttRing
echo 20 > ${PROGRESS_FILE}

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
GORTC_VER="1.5.0"
FIND_ARCH=`sudo dpkg --print-architecture`

case ${FIND_ARCH} in
  amd64)
    GORTC_ARCH="amd64";;
  arm64)
    GORTC_ARCH="arm64";;
  *)
    GORTC_ARCH="arm";;
esac

curl "https://github.com/AlexxIT/go2rtc/releases/download/v${GORTC_VER}/go2rtc_linux_${GORTC_ARCH}" > /usr/local/bin/go2rtc
chown www-data:www-data -R /usr/local/bin/go2rtc
chmod +x /usr/local/bin/go2rtc

cd $BASEDIR
source ../core/config/mqttRing.config.ini &> /dev/null
echo "Wanted Version: ${ringmqttRequire}"

echo 50 > ${PROGRESS_FILE}
git clone -b v${ringmqttRequire}_Jeedom --depth 1 https://github.com/WoCha-FR/ring-mqtt.git ${BASEDIR}/ring-mqtt

echo 55 > ${PROGRESS_FILE}
cd $BASEDIR/ring-mqtt
npm ci

echo 90 > ${PROGRESS_FILE}
if [ -f "${BASEDIR}/ring-state.json" ]; then
  echo "Restore configuration"
  mv ${BASEDIR}/ring-state.json ${BASEDIR}/ring-mqtt/ring-state.json
fi
chown www-data:www-data -R ${BASEDIR}/ring-mqtt

echo "Everything is successfully installed!"
echo "Post install finished"
