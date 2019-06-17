<?php
/* Copyright (C) 2017 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */
 /**
  * Script to Test the System Environment
  * Tests if all PHP-Modules, Configfiles, CommandlineTools, etc are installed
  */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
	die($rechte->errormsg);

echo '<!doctype html>
<html>
	<head>
	<meta charset="utf-8" />
	<title>FH-Complete - Environment</title>';

include('../include/meta/jquery.php');
include('../include/meta/jquery-tablesorter.php');

echo '
	<style>
	.fail{
		color:red;
	}
	.ok {
		color:green;
	}
	</style>
	<script language="Javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"],
			headers: {1:{sorter: false}}
		});
	});
	</script>
	</head>
<body>
<h1>FH-Complete Environment</h1>
<table id="t1" class="tablesorter">
<thead>
	<tr>
		<th>Module</th>
		<th>Version/Status</th>
	</tr>
</thead>
<tbody>
';
// Apache
printValue("Apache", apache_get_version());

// PHP version
printValue("php", version_compare(phpversion(),'5.6','>='), 'minimum php 5.6 required');

// PHP module
printValue("php-xsl", extension_loaded('xsl'));
printValue("php-gd", extension_loaded('gd'));
printValue("php-pgsql", extension_loaded('pgsql'));
printValue("php-ldap", extension_loaded('ldap'));
printValue("php-mbstring", extension_loaded('mbstring'));
printValue("php-soap", extension_loaded('soap'));
printValue("php-curl", extension_loaded('curl'));

// Unoconv version
$returnArray = array();
exec('unoconv --version',$returnArray);
if(isset($returnArray[0]))
{
	$hlp = explode(' ',$returnArray[0]);
	$unoconvVersion = $hlp[1];
}
else
	$unoconvVersion = false;

printValue("Unoconv", $unoconvVersion, "0.7");

// Codeigniter Environment Variable CI_ENV
$CI_ENV = getenv('CI_ENV');
printValue("CI_ENV", ($CI_ENV!=''?$CI_ENV:false),'not set -> defaults to development');

// ZIP
printValue("zip", checkInstalled('zip'));

// Composer
printValue("composer", checkInstalled('composer'));

// Composer / Vendor
$vendorFileExists = file_exists('../vendor/codeigniter/framework/index.php');
printValue("Composer Status", $vendorFileExists, 'out of date');

// Config Files
$ConfigExists = file_exists('../config/cis.config.inc.php');
if(!$ConfigExists)
	$ConfigExists = file_exists('../config/vilesci.config.inc.php');

printValue("ConfigFile CIS/Vilesci", $ConfigExists);

$ConfigExists = file_exists('../config/global.config.inc.php');
printValue("ConfigFile Global", $ConfigExists);

if($CI_ENV == '')
	$CI_ENV = 'development';
$ConfigExists = file_exists('../application/config/'.$CI_ENV.'/config.php');
printValue("ConfigFile Codeigniter", $ConfigExists);

// Htaccess Files
printValue("htaccess File CIS", file_exists('../cis/private/.htaccess'), 'missing htaccess File');
printValue("htaccess File Content", file_exists('../content/.htaccess'), 'missing htaccess File');
printValue("htaccess File Vilesci", file_exists('../vilesci/.htaccess'), 'missing htaccess File');
printValue("htaccess File System", file_exists('../system/.htaccess'), 'missing htaccess File');
printValue("htaccess File RDF", file_exists('../rdf/.htaccess'), 'missing htaccess File');

echo '
	</tbody>
	</table>
</body>
</html>';

function printValue($module, $status_ok, $message='')
{
	if(!is_bool($status_ok))
	{
		$output = $status_ok;
		$status_ok = true;
	}
	else
	{
		$output = ($status_ok?'ok':'failed');
	}

	echo '
	<tr>
		<td>'.$module.'</td>
		 <td><span class="'.($status_ok?'ok':'fail').'">'.$output.'</span>';
	if(!$status_ok && $message!='')
		echo ' '.$message;
	echo '</td>';
	echo '</tr>';
}

function checkInstalled($tool)
{
	$returnArray = array();
	$returnValue = null;
	exec('which '.$tool, $returnArray, $returnValue);
	if($returnValue==0)
		return 'ok';
	else
		return 'missing';
}
