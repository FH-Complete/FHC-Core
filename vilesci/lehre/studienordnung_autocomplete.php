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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/lehrveranstaltung.class.php');


if (!$uid = get_uid())
	die('Keine UID gefunden');

$rechte = new benutzerberechtigung();
if(!$rechte->getBerechtigungen($uid))
	die('Sie haben keine Berechtigung fuer diese Seite');
		
if(!$rechte->isBerechtigt('lehre/studienordnung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$work = (isset($_REQUEST['work'])?$_REQUEST['work']:'');

switch ($work)
{
	case 'searchlehrveranstaltung':
		 	$filter=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($filter) || $filter=='')
				exit();
			
			$result=array();

			$lehrveranstaltung = new lehrveranstaltung();
			if(!$lehrveranstaltung->search($filter))
				die($lehrveranstaltung->errormsg);

			foreach($lehrveranstaltung->lehrveranstaltungen as $row)
			{
				$item['lehrveranstaltung_id']=$row->lehrveranstaltung_id;
				$item['bezeichnung']=$row->bezeichnung;
				$item['semester']=$row->semester;
				$item['kurzbz']=$row->kurzbz;
				$item['aktiv']=$row->aktiv;
				$item['lehre']=$row->lehre;
				$item['studiengang_kurzbzlang']=$row->studiengang_kurzbzlang;
				$result[]=$item;
			}

			echo json_encode($result);
			break;
	default: 
		echo 'Unbekannter Vorgang';
		break;
}
?>
