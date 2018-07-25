#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-queue.sh user password host port vhost queue [autoDelete=false] [durable=true] [arguments=[]]
# example:
#./rabbitmq-create-queue.sh dildevelop dildevelop localhost 15772 demoapi command_queue

#autoDelete :
# If set, the queue is deleted when all consumers have finished using it. The last consumer can be cancelled
# either explicitly or because its channel is closed. If there was no consumer ever on the queue, it won't be deleted.
# Applications can explicitly delete auto-delete queues using the Delete method as normal.

#durable :
# If set when creating a new queue, the queue will be marked as durable. Durable queues remain active when a server
# restarts. Non-durable queues (transient queues) are purged if/when a server restarts. Note that durable queues do not
# necessarily hold persistent messages, although it does not make sense to send persistent messages to a transient queue.

#args:
#x-message-ttl: How long a message published to a queue can live before it is discarded (milliseconds).
#x-expires: How long a queue can be unused for before it is automatically deleted (milliseconds).
#x-max-length: How many (ready) messages a queue can contain before it starts to drop them from its head.
#x-max-length-bytes: Total body size for ready messages a queue can contain before it starts to drop them from its head.
#x-dead-letter-exchange: Optional name of an exchange to which messages will be republished if they are rejected or expire.
#x-dead-letter-routing-key: Optional replacement routing key to use when a message is dead-lettered. If this is not set, the message's original routing key will be used.
#x-max-priority: Maximum number of priority levels for the queue to support; if not set, the queue will not support message priorities.

user=$1
pw=$2
host=$3
port=$4
vhost=$5
queue=$6
autoDelete=false
if [ ! -z $7 ]; then
    autoDelete=$7
fi
durable=true
if [ ! -z $8 ]; then
    durable=$8
fi
arguments='{}'
if [ ! -z $9 ]; then
    arguments=$9
fi

# create queue
echo -e "\n Creating queue '${queue}' on vhost '${vhost}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -d '{"auto_delete":'${autoDelete}',"durable":'${durable}',"arguments":'${arguments}'}' \
 -X PUT http://${host}:${port}/api/queues/${vhost}/${queue}

# show all queues for the given vhost
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/queues/${vhost}

echo