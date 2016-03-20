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
sudo -u postgres createdb -O fhcomplete fhctest

echo "Installing FH-Complete..."
# cp index.ci.php index.php		# Maybe somtimes
cp config/global.config-default.inc.php config/global.config.inc.php
cp config/cis.config-default.inc.php config/cis.config.inc.php
cp config/vilesci.config-default.inc.php config/vilesci.config.inc.php
cp config/system.config-default.inc.php config/system.config.inc.php
# mkdir documents
#chown www-data data/cache
#./composer.phar update
php index.php Migrate
echo "Done!"
echo "Now make #composer update"
echo "and #php bin/fhcomplete update"

exit 0
