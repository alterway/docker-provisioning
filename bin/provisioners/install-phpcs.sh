#!/bin/bash

# If the ~/.composer/vendor/bin is not a folder, create it using generic script.
if [[ ! -d ~/.composer/vendor/bin ]]; then
    chmod +x $( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/install-composer.sh
    . $( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/install-composer.sh
fi

# Install the PHP Code Sniffer in the global ~/.composer vendor.
if [[ ! -f ~/.composer/vendor/bin/phpcs ]]; then
    composer require -d ~/.composer "squizlabs/php_codesniffer"
fi
