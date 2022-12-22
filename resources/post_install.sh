#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Post installation de l'installation/mise à jour des dépendances mqttRing"

PROGRESS_FILE=/tmp/jeedom_install_in_progress_mqttRing
echo 20 > ${PROGRESS_FILE}

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
RING_BRANCH="v5.0.5_Jeedom"
RSS_VERSION="0.20.4"
FIND_ARCH=`sudo dpkg --print-architecture`

case ${FIND_ARCH} in
  amd64)
    RSS_ARCH="amd64";;
  arm64)
    RSS_ARCH="arm64v8";;
  *)
    RSS_ARCH="armv7";;
esac

curl -L -s "https://github.com/aler9/rtsp-simple-server/releases/download/v${RSS_VERSION}/rtsp-simple-server_v${RSS_VERSION}_linux_${RSS_ARCH}.tar.gz" | tar zxf - -C /usr/local/bin rtsp-simple-server
chown www-data:www-data -R /usr/local/bin/rtsp-simple-server

echo 50 > ${PROGRESS_FILE}
git clone -b ${RING_BRANCH} --depth 1 https://github.com/WoCha-FR/ring-mqtt.git ${BASEDIR}/ring-mqtt

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
