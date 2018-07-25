#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
host=localhost
port=15772
vhost=demoapi
user=guest
pw=guest
newUser=dildevelop
newPw=dildevelop
exchangeType=topic
exchange=api.dil.demoapi.e.${exchangeType}.commands
queuePrefix=api.dil.demoapi.q

# create vhost
./rabbitmq-create-vhost.sh ${user} ${pw} ${host} ${port} ${vhost}

# create user + permissions
./rabbitmq-create-user.sh ${user} ${pw} ${host} ${port} ${vhost} ${newUser} ${newPw} administrator

# create queue(s)
./rabbitmq-create-queue.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${queuePrefix}.actor.eventStore
./rabbitmq-create-queue.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${queuePrefix}.movie.eventStore
./rabbitmq-create-queue.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${queuePrefix}.price.eventStore
./rabbitmq-create-queue.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${queuePrefix}.movieHasActors.eventStore

# create exchange(s)
./rabbitmq-create-exchange.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${exchange} ${exchangeType}

# binding exchange(s) to queue(s)
./rabbitmq-create-binding.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${exchange} 'actor.#' ${queuePrefix}.actor.eventStore
./rabbitmq-create-binding.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${exchange} 'movie.#' ${queuePrefix}.movie.eventStore
./rabbitmq-create-binding.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${exchange} 'price.#' ${queuePrefix}.price.eventStore
./rabbitmq-create-binding.sh ${newUser} ${newPw} ${host} ${port} ${vhost} ${exchange} 'movieHasActors.#' ${queuePrefix}.movieHasActors.eventStore