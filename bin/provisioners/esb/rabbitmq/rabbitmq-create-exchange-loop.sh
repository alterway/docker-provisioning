#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-exchange-loop.sh user password host port vhost data
# data is a list of :
# exchange|exchangeType|autoDelete|durable|args ; exchange|exchangeType|autoDelete|durable|args ; exchange|exchangeType|autoDelete|durable|args

# example:
#./rabbitmq-create-exchange-loop.sh dildevelop dildevelop localhost 15772 demoapi "myExchange|direct|false|true|{\"aaaa\":\"aaa\",\"zzz\":\"zzz\",\"bbb\":\"bbb\"}"

#exchangeType in [fanout, direct, topic, headers]

#autoDelete:
# If set, the exchange is deleted when all queues have finished using it.

#durable:
# If set when creating a new exchange, the exchange will be marked as durable. Durable exchanges remain active when a server restarts. Non-durable exchanges (transient exchanges) are purged if/when a server restarts.

#args:
#alternate-exchange: If messages to this exchange cannot otherwise be routed, send them to the alternate exchange named here.

trim() {
    local var="$*"
    var="${var#"${var%%[![:space:]]*}"}"   # remove leading whitespace characters
    var="${var%"${var##*[![:space:]]}"}"   # remove trailing whitespace characters
    echo -n "$var"
}
user=$1
pw=$2
host=$3
port=$4
vhost=$5

IFS=';' read -ra ADDR <<< "$6"
for i in "${ADDR[@]}"; do
    exchangeName=$(echo $i | awk -F  "|" '{print $1}')
    exchangeType=$(echo $i | awk -F  "|" '{print $2}')
    autoDelete=$(echo $i | awk -F  "|" '{print $3}')
    durable=$(echo $i | awk -F  "|" '{print $4}')
    args=$(trim $(echo $i | awk -F  "|" '{print $5}'))
    if [ -z $args ]; then
        args='{}'
    fi
    # create exchange for the given vhost
    echo -e "\n Creating '${exchangeType}' exchange '${exchangeName}' for vhost '${vhost}', autoDelete=${autoDelete}, durable=${durable}, args=${args}"
    curl -u ${user}:${pw} -H "content-type:application/json" \
     -d '{"type":"'${exchangeType}'","auto_delete":'${autoDelete}',"durable":'${durable}',"arguments":'${args}'}' \
     -X PUT http://${host}:${port}/api/exchanges/${vhost}/${exchangeName}
done

echo

# show all exchanges for the given vhost
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/exchanges/${vhost}

echo