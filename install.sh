#!/bin/bash
#
# This archive is part of the FH-Complete source code.
# 
# The source code of this software - FH-Complete - is under the terms of one of 
# two licenses: Apache v2 and GPL v2
# 
# ABOUT:
# =====
# This script will install some FH-Complete dependencies and configure default 
# files
# 
# WARNING:
# Of course that Apache and PostgreSQL should already be running 
# because it is not responsability of FH-Complete to install other software
# Anyway, the following steps are an example to help install a full system:
# 
# ------------------------------------------------------------------------------
# apt-get install sudo
# sudo apt-get update
# apt-get install phppgadmin postgresql apache
# apt-get install php5-pgsql php5-gd php5-curl
# a2enmod rewrite
# service apache2 restart
# sudo apt-get install -y git
# cd /var/www/html
# git clone https://github.com/FH-Complete/FH-Complete.git fhcomplete
# sudo chown www-data /var/www/html/fhcomplete
# -----------------------------------------------------------------------------
# CREATE DATABASE USERS fhcomplete, web, vilesci
# Running:
# =======
# ./install.sh
#
# =============================================================================
# Install script for FH-Complete
# =============================================================================

echo "==============================================================="
echo "Installing FH-Complete (install.sh)"
echo "==============================================================="
# Make sure only root can run our script
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi
echo "Starting..."


cwd=$(pwd)
echo "Database ..."
pkill -f fhctest &
sudo -u postgres psql template1 -c 'DROP DATABASE IF EXISTS fhctest;'
sudo -u postgres createdb -O fhcomplete fhctest

echo "Installing FH-Complete..."
# cp index.ci.php index.php		# Maybe somtimes
cp config/global.config-default.inc.php config/global.config.inc.php
cp config/cis.config-default.inc.php config/cis.config.inc.php
cp config/vilesci.config-default.inc.php config/vilesci.config.inc.php
cp config/system.config-default.inc.php config/system.config.inc.php
# mkdir documents
#chown www-data data/cache

echo "======= Install composer and run it ============="
echo "commented out for phpci"
# php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
# php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '41e71d86b40f28e771d4bb662b997f79625196afcca95a5abf44391188c695c6c1456e16154c75a211d238cc3bc5cb47') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
# php composer-setup.php
# php -r "unlink('composer-setup.php');"
# ./composer.phar update

echo "======= Database Migration ============="
php index.ci.php DBTools migrate

echo "Done!"
echo "Now run #php bin/fhcomplete update (Joking, its just a todo notice!)"

exit 0
