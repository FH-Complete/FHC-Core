<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/dms.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/akte.class.php');
require_once('../include/dokument.class.php');

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();

if(!isset($_GET['id']))
	die('ID muss uebergeben werden');

if(isset($_SESSION['bewerbung/personId']))
	$person_id = $_SESSION['bewerbung/personId'];
else
	$person_id ='';

if(isset($_GET['akte_id']))
	$akte_id = $_GET['akte_id'];
else
	$akte_id ='';

//if(!isset($_GET['version']))
//	die('Version muss uebergeben werden');

$id = $_GET['id'];
$version = isset($_GET['version'])?$_GET['version']:null;

if(!is_numeric($id))
	die('ID ist ungueltig');

if($version!='' && !is_numeric($version))
	die('Version ist ungueltig');

$doc = new dms();
if(!$doc->load($id,$version))
	die('Dieses Dokument existiert nicht mehr');

if($doc->isLocked($id))
{
	//Wenn person_id aus Session und akte_id uebergeben wurde
	//und person_id Besitzer des Dokuments ist (person_id aus tbl_akte)
	//und das Dokument in der Onlinebewerbung hochgeladen werden kann
	//darf das Dokument heruntergeladen werden
	if($person_id != '' && $akte_id != '')
	{
		$akte = new akte();
		$akte->load($akte_id);
		$akte_person = $akte->person_id;
		$akte_dokument_kurzbz = $akte->dokument_kurzbz;

		$dokumente_person = new dokument();
		$dokumente_person->getAllDokumenteForPerson($person_id, true);

		$dokumente_arr = array();
		foreach ($dokumente_person->result AS $row)
			$dokumente_arr[] .= $row->dokument_kurzbz;

		// An der FHTW wird das vorläufige ZGV Dokument verlangt und kann somit auch heruntergeladen werden
		// Auch der Invitation Letter und die Zeitbestätigung können von BewerberInnen heruntergeladen werden
		if (CAMPUS_NAME == 'FH Technikum Wien')
		{
			$dokumente_arr[] .= 'ZgvBaPre';
			$dokumente_arr[] .= 'ZgvMaPre';
			$dokumente_arr[] .= 'InvitLet';
			$dokumente_arr[] .= 'ZeitBest';
		}
		if ($person_id!=$akte_person || !in_array($akte_dokument_kurzbz, $dokumente_arr))
			die('Sie haben keinen Zugriff auf dieses Dokument');
	}
	else
	{
		//Dokument erfordert Authentifizierung
		$user = get_uid();

		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user);

		if(!$doc->isBerechtigt($id, $user))
		{
			// Wenn eine Berechtigung an der Kategorie haengt
			// dann darf nur mit diesem Recht darauf zugegriffen werden
			$kategorie = new dms();
			$kategorie->loadKategorie($doc->kategorie_kurzbz);
			if($kategorie->berechtigung_kurzbz != '')
			{
				if(!$rechte->isBerechtigt($kategorie->berechtigung_kurzbz))
					die($rechte->errormsg);
			}

			//Globales DMS recht pruefen
			if(!$rechte->isBerechtigt('basis/dms'))
				die($rechte->errormsg);
		}
	}
}

if(!isset($_GET['notimeupdate']))
	$doc->touch($doc->dms_id, $doc->version);

$filename = DMS_PATH.$doc->filename;
if(file_exists($filename))
{
	if($handle = fopen($filename,"r"))
	{
		if($doc->mimetype=='')
			$doc->mimetype='application/octetstream';

		header('Content-type: '.$doc->mimetype);
		header('Content-Disposition: inline; filename="'.$doc->name.'"');
		header('Content-Length: ' .filesize($filename));

		while (!feof($handle))
		{
			echo fread($handle, 8192);
		}
		fclose($handle);
	}
	else
		echo 'Fehler: Datei konnte nicht geoeffnet werden';
}
else
	echo 'Die Datei existiert nicht';
?>
