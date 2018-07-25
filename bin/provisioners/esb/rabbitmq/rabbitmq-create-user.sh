#!/usr/bin/env bash
# see http://hg.rabbitmq.com/rabbitmq-management/raw-file/rabbitmq_v3_4_4/priv/www/api/index.html
# see https://www.rabbitmq.com/amqp-0-9-1-reference.html
# usage:
#./rabbitmq-create-user.sh user password host port vhost newUser [newPassword=newUser] [tags=management]
# example:
#./rabbitmq-create-user.sh guest guest localhost 15772 demoapi dildevelop password administrator

#tags: Comma-separated list of tags to apply to the user. Currently supported by the management plugin:
#management: User can access the management plugin
#policymaker: User can access the management plugin and manage policies and parameters for the vhosts they have access to.
#monitoring: User can access the management plugin and see all connections and channels as well as node-related information.
#administrator: User can do everything monitoring can do, manage users, vhosts and permissions, close other user's connections, and manage policies and parameters for all vhosts.
#Note that you can set any tag here; the links for the above four tags are just for convenience.

user=$1
pw=$2
host=$3
port=$4
vhost=$5
newUser=$6
newPw=$6
if [ ! -z $7 ]; then
    newPw=$7
fi
tags=management
if [ ! -z $8 ]; then
    tags=$8
fi

# delete user
echo " Deleting user '${newUser}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X DELETE http://${host}:${port}/api/users/${newUser}

# create user
echo -e "\n Creating user '${newUser}' (${tags})"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -d '{"password":"'${newPw}'", "tags":"'${tags}'"}' \
 -X PUT http://${host}:${port}/api/users/${newUser}

# add permission for new user to vhost
echo -e "\n Adding permissions to '${newUser}' on vhost '${vhost}'"
curl -u ${user}:${pw} -H "content-type:application/json" \
 -d '{"scope":"client","configure":".*","write":".*","read":".*"}' \
 -X PUT http://${host}:${port}/api/permissions/${vhost}/${newUser}

# show all users
curl -u ${user}:${pw} -H "content-type:application/json" \
 -X GET http://${host}:${port}/api/users

echo