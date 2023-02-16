#!/bin/bash
BASEDIR=$(dirname $0) # path to current directory
cd ${BASEDIR}/../composer # go to composer folder
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
export COMPOSER_HOME=${BASEDIR}/../composer/cachecomposer;
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install # Install composer
rm -r ${BASEDIR}/../composer/cachecomposer