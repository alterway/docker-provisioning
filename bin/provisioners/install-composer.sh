#!/bin/bash

mkdir -p ~/.composer

# http://www.frandieguez.com/blog/2014/10/easy-way-to-install-php-qa-tools/

#Add lines that will complete the PATH when the ~/.profile is reloaded.
if ! grep -q "~/.composer/vendor/bin" ~/.profile; then
    echo '' >> ~/.profile
    echo 'PATH="$PATH:~/.composer/vendor/bin"' >> ~/.profile
fi

#Reload bash's .profile without logging out and back in again
. ~/.profile

# Install composer based tools
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
/usr/local/bin/composer global install

# Change rights of all files in ~/.composer
username=`whoami`
groupname=`id -g -n ${username}`
chown -R "${username}":"${groupname}" ~/.composer 2>/dev/null 1>/dev/null

# Check that, at least, the ~/.composer folder itself has good rights.
userAndGroupChecks=`stat -c "%U %G" ~/.composer`

if [ "${userAndGroupChecks}" == "${username} ${groupname}" ]; then
    return 0
else
    return 1
fi
