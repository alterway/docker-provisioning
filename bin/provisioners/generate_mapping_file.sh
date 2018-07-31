#!/bin/bash

if [[ ! -d $1 ]]; then
    echo 'Repository does not exist !'
fi

CONTEXT=${2:-MyContext}

for FILE in `ls $1*`
do
   CLASS=`echo $FILE | sed -r "s/Base(.+)\.class.+/\1/g"`
   TABLE=`echo $FILE | sed -r 's/([a-z0-9])([A-Z])/\1_\L\2/g' | sed -r 's/Base_//g' | sed -r "s/\.class.+//"`
   echo "$CONTEXT,$CLASS,$TABLE.php"
done
