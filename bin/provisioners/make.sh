#!/bin/bash

if [[ $# -eq 0 ]] ; then
    exit 0
fi

# Install the PHP Code Sniffer in the global ~/.composer vendor.
chmod +x config/phing/bin/provisioners/install-phpcs.sh
./config/phing/bin/provisioners/install-phpcs.sh

. ~/.profile
make "$@"
