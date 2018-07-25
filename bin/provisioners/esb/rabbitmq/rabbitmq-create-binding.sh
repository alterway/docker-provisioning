#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-binding.sh user password host port vhost exchange routingKey queue  [arguments=[]]
# example:
#./rabbitmq-create-binding.sh dildevelop dildevelop localhost 15772 demoapi command_exchange actor_update command_queue

user=$1
pw=$2
host=$3
port=$4
vhost=$5
exchange=$6
routingKey=$7
queue=$8
arguments='{}'
if [ ! -z $9 ]; then
    arguments=$9
fi

# delete binding for the given vhost, queue and exchange
echo " Deleting bindings for exchange '${exchange}' to queue '${queue}' in vhost '${vhost}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X DELETE http://${host}:${port}/api/bindings/${vhost}/${queue}/${exchange}

# create binding for the given vhost, queue and exchange
echo -e "\n Binding exchange '${exchange}' to queue '${queue}' with routing key '${routingKey}' in vhost '${vhost}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -d '{"routing_key":"'${routingKey}'","arguments":'${arguments}'}' \
 -X POST http://${host}:${port}/api/bindings/${vhost}/e/${exchange}/q/${queue}

# show all exchanges for the given vhost
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/bindings/${vhost}

echo