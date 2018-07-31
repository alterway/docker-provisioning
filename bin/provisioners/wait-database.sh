#!/bin/bash

wait_orm() {
  echo "We wait until orm database is launched."
  output=0
  attemptsLeft=20
  while [ $output -eq 0 ]; do
    attemptsLeft=$(( $attemptsLeft - 1 ))
    echo "attemptsLeft: $attemptsLeft"
    if [[ $attemptsLeft -eq 0 ]]; then
      echo
      echo "Could not build the database processes. Aborting...stderr"
      cat /tmp/.prog.stderr.log
      echo
      echo "Could not build the database processes. Aborting...stdout"
      cat /tmp/.prog.stdout.log
      exit 1
    fi
    php app/console doctrine:schema:update 2> /tmp/.prog.stderr.log 1> /tmp/.prog.stdout.log
    output=$(cat /tmp/.prog.stdout.log |grep -Pce '.*Nothing to update.*')
    echo "output: $output"
    echo -n "."
    sleep 3
  done

  if [[ $timeout -ge 1 ]]; then
    cat /tmp/.prog.stdout.log
  fi
}

wait_odm() {
    # Unknown treatment to wait for the database.
    exit 0
}

wait_couchdb() {
    # Nothing to wait here.
    exit 0
}

case ${1} in
    orm)
        wait_orm
        ;;
    odm)
        wait_odm
        ;;
    couchdb)
        wait_couchdb
        ;;
    *)
        #nothing
        ;;
esac
