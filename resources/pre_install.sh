#!/bin/bash
set -x  # make sure each command is printed in the terminal
echo "Pre installation de l'installation/mise à jour des dépendances mqttRing"

PROGRESS_FILE=/tmp/jeedom_install_in_progress_mqttRing
echo 5 > ${PROGRESS_FILE}

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd $BASEDIR

if [ -d "${BASEDIR}/ring-mqtt" ]; then
  if [ -f "${BASEDIR}/ring-mqtt/ring-state.json" ]; then
    echo "Backup configuration"
    cp ${BASEDIR}/ring-mqtt/ring-state.json ${BASEDIR}/ring-state.json
  fi
  rm -R ${BASEDIR}/ring-mqtt
fi

echo 7 > ${PROGRESS_FILE}

echo "Pre install finished"
