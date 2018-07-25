#!/bin/bash

# https://docs.docker.com/engine/installation/linux/ubuntu/#install-using-the-repository

# remove docker-engine
apt-get purge -y docker-engine docker docker.io docker-ce
apt-get autoremove -y --purge docker-engine docker docker.io docker-ce
apt-get autoclean
(rm -rf /var/lib/docker) || true
(rm /etc/apparmor.d/docker) || true

# ubuntu install
apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    software-properties-common

# Add Docker’s official GPG key:
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | apt-key add -

# Verify that the key fingerprint is 9DC8 5822 9FC7 DD38 854A E2D8 8D81 803C 0EBF CD88.
apt-key fingerprint 0EBFCD88

# Use the following command to set up the stable repository.
add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable"

# Update the apt package index.
apt-get update

#  apt-get install docker-ce=<VERSION>
# to get the list of versions : apt-cache madison docker-ce
apt-get install -y docker-ce=17.09.1~ce-0~ubuntu

# Add the connected user "${USER}" to the docker group.
# Change the user name to match your preferred user.
# You may have to logout and log back in again for
# this to take effect.
USER=$(ps -o user= -p $$ | awk '{print $1}')
#gpasswd -a ${USER} docker
groupadd docker
usermod -aG docker $USER

# test
docker run hello-world
docker version
#exec su -l $USER

#
# https://github.com/moby/moby/issues/9889
#
#dockerd --userns-remap 1000:1000
#'DOCKER_OPT="--userns-remap 1000:1000"' > /etc/default/docker
# /usr/bin/dockerd -H fd://
## insert content  after a pattern
#sed 's/.*Service.*/&\nEnvironmentFile=-\/etc\/default\/docker\/' /lib/systemd/system/docker.service

#https://docs.docker.com/install/linux/linux-postinstall/#manage-docker-as-a-non-root-user
#chown "$USER":"$USER" /home/"$USER"/.docker -R
#chmod g+rwx "/home/$USER/.docker" -R

# BE CAREFULL !!!!!!!!!!!!!!!!!!!!!!! we have to reboot
# reboot