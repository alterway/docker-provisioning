#!/usr/bin/env bash
# see http://localhost:15772/api/
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-import-global-definition.sh user password host port filePath
# example:
#./rabbitmq-import-global-definition.sh guest guest localhost 15772 /tmp/def.json

user=$1
pw=$2
host=$3
port=$4
filePath=$5

echo "importing definition from $filePath"

curl -u ${user}:${pw} -H "content-type:application/json" \
 -X POST http://${host}:${port}/api/definitions \
 -d @$filePath

echo