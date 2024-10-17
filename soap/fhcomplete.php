<?php
/*
 * Copyright 2013 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Aufruf: fhcomplete.php?class=benutzer&method=search&typ=json&parameter_0=österreicher
 */
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

$class = $_REQUEST['class'];
$method = $_REQUEST['method'];
$typ = (isset($_REQUEST['typ'])?$_REQUEST['typ']:'');

// die einzelnen funktionsparameter werden durchnummeriert mit 0 beginnend:
// parameter_0=param0&parameter_1=param1&parameter_3[0]=param3arr0&parameter_3[1]=param3arr1
$parameter=array();
for($i=0;$i<100;$i++)
{
	if(isset($_REQUEST['parameter_'.$i]))
	{
		if($_REQUEST['parameter_'.$i]=="true")
				$parameter[]=true;
		elseif($_REQUEST['parameter_'.$i]=="false")
				$parameter[]=false;
		elseif($_REQUEST['parameter_'.$i]=="null")
				$parameter[]=null;
		else
			$parameter[]=$_REQUEST['parameter_'.$i];
	}
	else
		break;
}

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/'.$class.'.class.php');

// Berechtigung pruefen
$uid = get_uid();

$wsrecht = new webservicerecht();
if(!$wsrecht->isUserAuthorized($uid, $method, $class))
	die('Sie haben keine Berechtigung fuer diesen Vorgang:'.$class.'->'.$method);

// Funktion aufrufen
$obj = new $class();
$error=false;

// Bei Save Funktionen werden alle Parameter zugewiesen
if(mb_stristr($method,'save'))
{
	if(isset($_REQUEST['loaddata']))
		$loaddata=json_decode($_REQUEST['loaddata'], true);
	else
		$loaddata=null;
	$savedata=json_decode($_REQUEST['savedata'], true);

	if(isset($loaddata['method']))
	{
		if(!$wsrecht->isUserAuthorized($uid, $loaddata['method']))
			die('keine Berechtigung');

		// Bearbeiten
		$loadparameter=array();
		for($i=0;$i<20;$i++)
		{
			$name = 'parameter_'.$i;
			if(isset($loaddata[$name]))
				$loadparameter[]=$loaddata[$name];
			else
				break;
		}

		if(!call_user_func_array(array($obj, $loaddata['method']), $loadparameter))
		{
			$error=true;
		}
	}
	else
	{
		// Neu
		$obj->insertvon = $uid;
		$obj->insertamum = date('Y-m-d H:i:s');
	}

	if(!$error)
	{
		// Attribute zuweisen zum Speichern
		foreach($savedata as $key=>$value)
		{
			$obj->$key=$value;
		}
	}
}

$return = '';
if(!$error && ($return = call_user_func_array(array($obj, $method), $parameter)))
{
	$data['result']=$obj->cleanResult();
	$data['return']=$return;
	$data['error']='false';
	$data['errormsg']='';
}
else
{
	$data['result']='';
	$data['return']=$return;
	$data['error']='true';
	$data['errormsg']=$obj->errormsg;
}

// Daten ausgeben
if($typ=='json')
	echo json_encode($data);
elseif($typ=='xml')
	echo array_to_xml($data);
else
	var_dump($data);
?>
