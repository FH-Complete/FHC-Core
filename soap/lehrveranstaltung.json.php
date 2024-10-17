<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 * 			Stefan Puraner	<puraner@technikum-wien.at>
 */
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/webservicerecht.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/studienordnung'))
	die('Sie haben keine Berechtigung für diese Seite');

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'loadLehrveranstaltungStudienplan':
		$studienplan_id=$_REQUEST['studienplan_id'];
		$lehrveranstaltung = new lehrveranstaltung();
		if($lehrveranstaltung->loadLehrveranstaltungStudienplan($studienplan_id))
		{
			$data['result']=$lehrveranstaltung->getLehrveranstaltungTree();
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$lehrveranstaltung->errormsg;
		}
		break;
	case 'getSemesterFromStudiengang':
		$studiengang_kz = $_REQUEST['studiengang_kz'];
		$lehrveranstaltung = new lehrveranstaltung();
		if(($result = $lehrveranstaltung->getSemesterFromStudiengang($studiengang_kz)))
		{
			$data["result"]=$result;
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$lehrveranstaltung->errormsg;
		}
		break;
	case 'saveKompatibleLehrveranstaltung':
//		$wsrecht = new webservicerecht();
//		if(!$wsrecht->isUserAuthorized($uid, $method))
//			die('Sie haben keine Berechtigung fuer diesen Vorgang');
		
		$lv_id = $_REQUEST['lehrveranstaltung_id'];
		$lv_id_kompatibel = $_REQUEST['lehrveranstaltung_id_kompatibel'];
		$lehrveranstaltung = new lehrveranstaltung();
		if(($result = $lehrveranstaltung->saveKompatibleLehrveranstaltung($lv_id, $lv_id_kompatibel)))
		{
			$data["result"]=$result;
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$lehrveranstaltung->errormsg;
		}
		break;
	case 'deleteKompatibleLehrveranstaltung':
//		$wsrecht = new webservicerecht();
//		if(!$wsrecht->isUserAuthorized($uid, $method))
//			die('Sie haben keine Berechtigung fuer diesen Vorgang');
		
		$lv_id = $_REQUEST['lehrveranstaltung_id'];
		$lv_id_kompatibel = $_REQUEST['lehrveranstaltung_id_kompatibel'];
		$lehrveranstaltung = new lehrveranstaltung();
		if(($result = $lehrveranstaltung->deleteKompatibleLehrveranstaltung($lv_id, $lv_id_kompatibel)))
		{
			$data["result"]=$result;
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$lehrveranstaltung->errormsg;
		}
		break;
	default:
		break;
}

echo json_encode($data);

?>
