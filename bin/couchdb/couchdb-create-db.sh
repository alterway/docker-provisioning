#!/usr/bin/env bash

dbName=$1
dbUser=$2
dbPw=$3
dbHost=$4
dbPort=$5

# create database
echo "curl -X PUT http://${dbUser}:${dbPw}@${dbHost}:${dbPort}/${dbName}"
curl -X PUT http://${dbUser}:${dbPw}@${dbHost}:${dbPort}/${dbName}

