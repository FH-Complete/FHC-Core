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
printVersion("Apache", apache_get_version());

// PHP version
printVersion("php", phpversion());

// PHP module
printVersion("php-xsl", phpversion('xsl'));
printVersion("php-gd", phpversion('gd'));
printVersion("php-pgsql", phpversion('pgsql'));
printVersion("php-ldap", phpversion('ldap'));
printVersion("php-mcrypt", phpversion('mcrypt'));
printVersion("php-mbstring", phpversion('mbstring'));
printVersion("php-soap", phpversion('soap'));
printVersion("php-curl", phpversion('curl'));

// Unoconv version
$returnArray = array();
exec('unoconv --version',$returnArray);
if(isset($returnArray[0]))
	$unoconvVersion = explode(' ',$returnArray[0])[1];
else
	$unoconvVersion = null;

printVersion("Unoconv", $unoconvVersion, "0.7");

// Codeigniter Environment Variable CI_ENV
$CI_ENV = getenv('CI_ENV');
printVersion("CI_ENV", $CI_ENV);

// ZIP
printVersion("zip", checkInstalled('zip'));

// Composer
printVersion("composer", checkInstalled('composer'));

// Composer / Vendor
$vendorFileExists = file_exists('../vendor/codeigniter/framework/index.php');
printVersion("Composer Status", ($vendorFileExists?'ok':'out of date'));

// Config Files
$ConfigExists = file_exists('../config/cis.config.inc.php');
if(!$ConfigExists)
	$ConfigExists = file_exists('../config/vilesci.config.inc.php');

printVersion("ConfigFile CIS/Vilesci", ($ConfigExists?'ok':'missing'));

$ConfigExists = file_exists('../config/global.config.inc.php');
printVersion("ConfigFile Global", ($ConfigExists?'ok':'missing'));

if($CI_ENV == '')
	$CI_ENV = 'production';
$ConfigExists = file_exists('../application/config/'.$CI_ENV.'/config.php');
printVersion("ConfigFile Codeigniter", ($ConfigExists?'ok':'missing'));

// Htaccess Files
$htaccessExists = file_exists('../cis/private/.htaccess');
printVersion("htaccess File CIS", ($htaccessExists?'ok':'missing'));
$htaccessExists = file_exists('../content/.htaccess');
printVersion("htaccess File Content", ($htaccessExists?'ok':'missing'));
$htaccessExists = file_exists('../vilesci/.htaccess');
printVersion("htaccess File Vilesci", ($htaccessExists?'ok':'missing'));
$htaccessExists = file_exists('../system/.htaccess');
printVersion("htaccess File System", ($htaccessExists?'ok':'missing'));
$htaccessExists = file_exists('../rdf/.htaccess');
printVersion("htaccess File RDF", ($htaccessExists?'ok':'missing'));

echo '
	</tbody>
	</table>
</body>
</html>';

function printVersion($module, $currentVersion, $expectedVersion = '')
{
	$failed = false;

	if ($currentVersion == null)
		$failed = true;

	if ($currentVersion == '')
		$currentVersion = 'missing';
	if ($expectedVersion != '' && $currentVersion != $expectedVersion)
		$failed = true;

	echo '
	<tr>
		<td>'.$module.'</td>
		 <td><span class="'.($failed?'fail':'ok').'">'.$currentVersion.'</span>';
	if($failed && $expectedVersion != '')
		echo ' (should be '.$expectedVersion.')';
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
