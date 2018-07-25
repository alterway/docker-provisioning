#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-binding-loop.sh user password host port vhost data
# data is a list of :
# exchange|routingKey|queue|arguments ; exchange|routingKey|queue|arguments ; exchange|routingKey|queue|arguments

# example:
#./rabbitmq-create-binding-loop.sh dildevelop dildevelop localhost 15772 demoapi "api.dil.demoapi.q.actor.eventStore|actor.*.bbb.#|api.dil.demoapi.e.topic.commands|{\"aaaa\":\"aaa\",\"zzz\":\"zzz\",\"bbb\":\"bbb\"};api.dil.demoapi.q.movie.eventStore|movie.*.bbb.#|api.dil.demoapi.e.topic.commands|{\"aaaa\":\"aaa\",\"zzz\":\"zzz\",\"bbb\":\"bbb\"}"

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
    exchange=$(echo $i | awk -F  "|" '{print $1}')
    routingKey=$(echo $i | awk -F  "|" '{print $2}')
    queue=$(echo $i | awk -F  "|" '{print $3}')
    args=$(trim $(echo $i | awk -F  "|" '{print $4}'))
    if [ -z $args ]; then
        args='{}'
    fi
    echo -e "\n Binding exchange '${exchange}' to queue '${queue}' with routing key '${routingKey}' in vhost '${vhost}' with args '$args'"
    curl -u ${user}:${pw} -H "content-type:application/json" \
     -d '{"routing_key":"'${routingKey}'","arguments":'${args}'}' \
     -X POST http://${host}:${port}/api/bindings/${vhost}/e/${exchange}/q/${queue}
done

echo

# show all exchanges for the given vhost
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/bindings/${vhost}

echo


