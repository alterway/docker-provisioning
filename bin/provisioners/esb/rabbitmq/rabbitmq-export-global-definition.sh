#!/usr/bin/env bash
# see http://localhost:15772/api/
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-export-global-definition.sh user password host port [filePath]
# example:
#./rabbitmq-export-global-definition.sh guest guest localhost 15772 [/tmp/def.json]

user=$1
pw=$2
host=$3
port=$4
if [ ! -z $5 ]; then
    filePath=$5
    curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/definitions \
 > $filePath
else
    curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/definitions
fi
