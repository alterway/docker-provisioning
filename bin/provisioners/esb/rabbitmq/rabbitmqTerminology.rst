========================
RabbitMQ Terminology Doc
========================

Broker
------
RabbitMQ is a message broker. You can think about it as a post office.

Queue
-----
A queue is the name for a mailbox. It is where messages are stored.

Exchange
--------
An exchange is like a router. It delivers the message to the right queue according to some rule (binding key) and its type

Exchange Type
-------------
fanout, direct, topic, headers

Producer / Publisher
--------------------
The Producer or Publisher is the one which send the message.

Consummer
---------
Consuming has a similar meaning to receiving

Message
-------
The message is like the letter you send in post office.

Routing key
-----------
A routing key is like the address of the letter.

Binding key
-----------
direct : it is a single word to match with the routing key.
topic : it is a pattern to match with the routing key. The pattern is composed by words separated with "."

Binding Exchange to Queue
-------------------------
It is the action to link a queue to an exchange.
Queue <== Binding key ==> Exchange

Vhost
-----

Channel
-------

Connection
----------


unacknowledged messages
------------------------
* Messages in queus which are not been acknowledged by consumers.
Unacknowledged messages are those which have been delivered across the network to a consumer but have not yet been ack'ed or rejected
-- but that consumer hasn't yet closed the channel or connection over which it originally received them.
Therefore the broker can't figure out if the consumer is just taking a long time to process those messages or if it has forgotten about them.
So, it leaves them in an unacknowledged state until either the consumer dies or they get ack'ed or rejected.

Since those messages could still be validly processed in the future by the still-alive consumer that originally sent them,
you can't (to my knowledge) insert another consumer into the mix and try to make external decisions about them.
You need to fix your consumers to make decisions about each message as they get processed rather than leaving old messages unacknowledged.

* Un message non acquitté est renvoyé dans la queue d'origine et peut être de nouveau lu par d'autre consumers.
* Un message ni tagé acquitté ni taggé non-acquitté est tagé comme message lu et ne peut-être relue par un autre consumer.
Toutefois, si le consumer qui a lu ce dernier message fini sa session de connexion à la queue de RabbitMQ, ce même message est retourné
dans la queue pour être consommer à nouveau?.


Poison Message
--------------
unacknowledged messages which exist in queue since long time.

ACK
---

NACK
----

USER TAGS
_________
#tags: Comma-separated list of tags to apply to the user. Currently supported by the management plugin:
#management: User can access the management plugin
#policymaker: User can access the management plugin and manage policies and parameters for the vhosts they have access to.
#monitoring: User can access the management plugin and see all connections and channels as well as node-related information.
#administrator: User can do everything monitoring can do, manage users, vhosts and permissions, close other user's connections, and manage policies and parameters for all vhosts.
#Note that you can set any tag here; the links for the above four tags are just for convenience.

Specification AMPQ
------------------
Les spécifications AMQP sont parties de cas d’utilisation très concrets pour aboutir à des spécifications essayant d’englober un maximum de typologies.

*Store-and-forward:                  les messages sont persistés puis récupérés par un seul client qui décidera s’il faut les supprimer.
*Point-to-point:                     une communication dédiée entre un émetteur et un receveur, éventuellement bidirectionnelle.
*One-to-many (ou fanout):            un message est retransmis à toutes les queues d’une zone d’échange. Ceci permet de modéliser le multicast.
*Transaction (distribuée ou pas):    l’émetteur peut englober un paquet de messages dans une transaction, ces messages ne pourront être lus que lorsque l’émetteur les aura acquittés.
*Publish-subscribe (pub-sub):        plusieurs émetteurs postent des messages en fonction de mots clés (topics) auxquels s’abonnent plusieurs receveurs.
*Content-based routing:              le routage des messages est déterminé selon le contenu du message ou par une fonction externe.
*Queued file transfer:               on n’envoie plus de simples messages mais des fichiers, voire tout le contenu d’un répertoire.

Ces différentes architectures se combinent bien sûr entre elles, je pense particulièrement aux transactions.
Ces schémas de base un peu abstraits rejoignent des concepts ou des applications connus de tous. Un serveur de mail par exemple s’appuiera sur un « store-and-forward »,
un chat sur un « point-to-point » ou un streaming de fichier sur un « queued file transfer ».
Cette sémantique est un vrai plus pour la phase de conception d’un projet même si ensuite rien n’est figé dans la réalisation.
Derrière ces grands concepts se cachent des briques élémentaires très simples :

*Queue de message (Message Queue): zone de stockage des messages (en mémoire ou sur le disque). Elle aura les propriétés privée/partagée, durable/transitoire, permanente/temporaire.
*Zone d’échange (Exchange): l’entité qui accepte les messages et les route vers les queues de messages. Les critères de routage peuvent se faire de plusieurs façons (inspection du contenu, du header, clés de routage…). Les zones d’échange peuvent être créées dynamiquement par les applications clientes.
*Zone virtuelle (Virtual Zone): ce concept est copié de celui des serveurs HTTP d’Apache. Cette zone crée un espace contenant différentes zones d’échange et de queues de message complètement étanches aux autres zones virtuelles. Donc une connexion au serveur ne pourra être associée qu’à une zone virtuelle. C’est très utile lorsqu’on veut mutualiser les ressources.