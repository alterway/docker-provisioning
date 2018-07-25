#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-vhost.sh user password host port vhost
# example:
#./rabbitmq-create-vhost.sh guest guest localhost 15772 demoapi

user=$1
pw=$2
host=$3
port=$4
vhost=$5

# delete this vhost
echo " Deleting vhost '${vhost}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X DELETE http://${host}:${port}/api/vhosts/${vhost}

echo