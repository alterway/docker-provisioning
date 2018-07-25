#!/bin/bash

build_orm() {
  echo "We create database after database is launched."
  output=1
  timeout=20
  while [ $output -ge 1 ]; do
    echo -n "$timeout"
    timeout=$(( $timeout - 1 ))
    if [[ $timeout -eq 0 ]]; then
      echo
      echo "Could not build the database processes. Aborting...stderr"
      cat /tmp/.persistence.stderr.log
      echo
      echo "Could not build the database processes. Aborting...stdout"
      cat /tmp/.persistence.stdout.log
      return 1
    fi
    phing -f build.xml prepare:persistence 2> /tmp/.persistence.stderr.log 1> /tmp/.persistence.stdout.log
    output=$(cat /tmp/.persistence.stderr.log |grep -Pce '.*Connection refused.*')
    echo -n "."
    sleep 1
  done

  if [[ $timeout -ge 1 ]]; then
    cat /tmp/.persistence.stdout.log
  fi
}

build_odm() {
    # Unknown treatment to wait for the database.
    exit 0
}

build_couchdb() {
    # Nothing to wait here.
    exit 0
}

case ${1} in
    orm)
        build_orm
        ;;
    odm)
        build_odm
        ;;
    couchdb)
        build_couchdb
        ;;
    *)
        #nothing
        ;;
esac
