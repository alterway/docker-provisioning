===============================
RabbitMQ Naming Conventions Doc
===============================

Queue
-----

Queue names should be named after what the consumer attached to the queue will do. What is the intent of the operation of this queue. Say you want to send an email to the user when their account is created (when a message with routing key user.event.created is sent using Derick's answer above). You would create a queue name sendNewUserEmail (or something along those lines, in a style that you find appropriate). This means it's easy to review and know exactly what that queue does.
If the queue was named after the binding, it can cause confusion, especially if routing keys change. Keep queue names decoupled and self descriptive.

It must be prefixed by "api.dil.<service>.q". Ex: api.dil.demoapi.q.actor.eventStore

Exchange
--------

It must be prefixed by "api.dil.<service>.e.<exchangeType>".

A complete queue name would be like: <prefix>.<goal>

Ex: api.dil.demoapi.e.topic.eventStore

Message content
---------------

A message should contain the answer of thoses 4 questions : who? when? what? how?

- who: userId, login, role
- when: timestamp of the action
- what: tenantId, resource, id(s), action. Ex: 11111, actor, aaa-bbb-ccc-ddd, create
- how: api, version, dbType

The format is json.
Ex:
{
    "timestamp": 1482920566,
    "tenantId": 11111,
    "resource": "DemoApiContext\\Domain\\Entity\\Actor",
    "id": "8988c5d1-60b9-4248-a68e-db5cae00b949",
    "action": "create",
    "userId": "",
    "login": "",
    "role": "",
    "source": "api",
    "version": 1
    "dbType": "orm"
}

Routing key
-----------
(With Topic exchange) The routing key must respect this format :
<resource>.<dbType>.<action>

Ex: actor.orm.create

Binding Exchange to Queue
-------------------------

It is possible to bind several times an exchange to a queue.

Binding key for topic exchange:
* = 1 word
# = 0,n word

Ex:
actor.*.* will match with routing key "actor.couchdb.update"
actor.# will match with routing key "actor.orm.delete"

Improvement?
------------
add version ?