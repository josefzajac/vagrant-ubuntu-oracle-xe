#!/bin/bash

apt-get update && apt-get upgrade -y

# Nginx
# PHP
# Utils
apt-get -qy install language-pack-en language-pack-de language-pack-cs\
        build-essential libssl-dev \
        nginx git \
        php5-fpm php5-cli \
        php5-curl php5-gd php5-intl php-pear php5-imagick php5-imap php5-mcrypt \
        php5-ps php5-pspell php5-recode php5-sqlite php5-tidy php5-xmlrpc php5-xsl php5-xcache \
        debconf-utils

# Remove unused packages
apt-get autoremove -y

# NVM
curl https://raw.githubusercontent.com/creationix/nvm/v0.16.1/install.sh | sudo -i -u vagrant sh

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Configure locale
locale-gen en_US.UTF-8
locale-gen de_DE.UTF-8
locale-gen cs_CZ.UTF-8

# Configure nginx
rm -rf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
ln -nsf /home/vagrant/vagrant-ubuntu-oracle-xe/vagrant/nginx/sites-available/kuba /etc/nginx/sites-enabled/
service nginx restart

# Configure php
rm -rf /etc/php5/fpm/php.ini
ln -nsf /home/vagrant/vagrant-ubuntu-oracle-xe/vagrant/php/php.ini /etc/php5/fpm/
service php5-fpm restart


# Initialize application - run as vagrant
#sudo -i -u vagrant bash /home/vagrant/vagrant-ubuntu-oracle-xe/composer install
