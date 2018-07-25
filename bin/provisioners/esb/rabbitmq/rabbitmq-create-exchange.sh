#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-exchange.sh user password host port vhost exchangeName [exchangeType=direct] [autoDelete=false] [durable=true] [arguments=[]]
# example:
#./rabbitmq-create-exchange.sh dildevelop dildevelop localhost 15772 demoapi command_exchange direct

#exchangeType in [fanout, direct, topic, headers]

#autoDelete:
# If set, the exchange is deleted when all queues have finished using it.

#durable:
# If set when creating a new exchange, the exchange will be marked as durable. Durable exchanges remain active when a server restarts. Non-durable exchanges (transient exchanges) are purged if/when a server restarts.

#args:
#alternate-exchange: If messages to this exchange cannot otherwise be routed, send them to the alternate exchange named here.

user=$1
pw=$2
host=$3
port=$4
vhost=$5

exchangeName=$6
exchangeType=direct
if [ ! -z $7 ]; then
    exchangeType=$7
fi
autoDelete=false
if [ ! -z $8 ]; then
    autoDelete=$8
fi
durable=true
if [ ! -z $9 ]; then
    arguments=$9
fi
arguments='{}'
if [ ! -z ${10} ]; then
    arguments=${10}
fi

## delete exchange for the given vhost
#echo " Deleting exchange '${exchangeName}' for vhost '${vhost}'"
#curl -u ${user}:${pw} -H "content-type:application/json" \
# -X DELETE http://${host}:${port}/api/exchanges/${vhost}/${exchangeName}

# create exchange for the given vhost
echo -e "\n Creating '${exchangeType}' exchange '${exchangeName}' for vhost '${vhost}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -d '{"type":"'${exchangeType}'","auto_delete":'${autoDelete}',"durable":'${durable}',"arguments":'${arguments}'}' \
 -X PUT http://${host}:${port}/api/exchanges/${vhost}/${exchangeName}

# show all exchanges for the given vhost
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/exchanges/${vhost}

echo