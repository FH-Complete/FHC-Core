<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');

echo '
<html>
<head>
<title>Lektor Edit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body class="background_main">';

if(!$conn = pg_pconnect(CONN_STRING))
       die ('Fehler beim Herstellen der DB Connection');

	if (isset($_POST['Save']))
	{
		if(doSAVE($conn))
			echo "<script language='Javascript'>window.location.href='lektor_uebersicht.php';</script>";
	}
	else if (isset($_GET['new']))
	{
		doEDIT($conn,null,true);
	}
	else
	{
		if (!isset ($_GET['id']))
		{
			echo "benötige ID für Lektor";
		}
		doEDIT($conn,$_GET['id']);
	}

/**
 * Lektor speichern/anlegen
 */
function doSAVE($conn)
{
	$lektor = new mitarbeiter($conn);
	if ($_POST['new']==1)
	{
		$lektor->new=true;
	}
	else
	{
		$lektor->load($_POST['uid']);
		$lektor->new=false;
	}
	// person
	$lektor->uid=$_POST['uid'];
	$lektor->titel=$_POST['titel'];
	$lektor->vornamen=$_POST['vornamen'];
	$lektor->nachname=$_POST['nachname'];
	$lektor->gebdatum=$_POST['gebdatum'];
	$lektor->gebort=$_POST['gebort'];
	//$lektor->gebzeit=$_POST['gebzeit'];
	//$lektor->anmerkungen=$_POST['anmerkungen'];
	$lektor->aktiv=($_POST['aktiv']=='1'?true:false);
	//$lektor->email=$_POST['email'];
	$lektor->alias=$_POST['alias'];
	$lektor->kurzbz=$_POST['kurzbz'];
	$lektor->homepage=$_POST['homepage'];
	// mitarbeiter
	$lektor->personalnummer=$_POST['personalnummer'];
	$lektor->lektor=($_POST['lektor']=='1'?true:false);
	$lektor->fixangestellt=($_POST['fixangestellt']=='t'?true:false);
	$lektor->standort_kurzbz=$_POST['standort_kurzbz'];
	$lektor->telefonklappe=$_POST['telefonklappe'];
	$lektor->ort_kurzbz=$_POST['raumnr'];
	//print_r($_POST);


	if ($lektor->save())
	{
		echo "<p>Datensatz gespeichert.</p>";
		return true;
	}
	else
	{
		echo "<p>".$lektor->errormsg."</p>";
		return false;
	}

//	doEDIT($lektor->uid,false,$msg);
}



/**
 * MA bearbeiten/anlegen
 * @param string $id optional; wenn nicht angegeben -> neuer datensatz
 */
function doEDIT($conn,$id='',$new=false,$msg='')
{
	// Mitarbeiterdaten holen
	$lektor = new mitarbeiter($conn);
	$status_ok=false;
	if (!$new)
	{
		$status_ok=$lektor->load(addslashes($id));
	}

	if (!$status_ok && !$new)
	{
		// Laden fehlgeschlagen
		echo $lektor->errormsg;
	}
	else
	{

		echo '<h2>Lektor/Mitarbeiter '.($new?'Neu':'Edit').'</h1>';

		if (strlen($msg)>0) 
			echo $msg."<br/>";

		echo '
			<table><tr><td>
			 
			<form name="std_edit" action="'.$_SERVER['PHP_SELF'].'" method="post">
			
			<table border="0">
			<tr><td>UID</td><td><input type="text" name="uid" value="'.$lektor->uid.'"></td></tr>
			<tr><td>Personalnummer</td><td><input type="text" name="personalnummer" value="'.$lektor->personalnummer.'"></td></tr>
			<tr><td>Titel</td><td><input type="text" name="titel" value="'.$lektor->titelpre.'"></td></tr>
			<tr><td>Vornamen</td><td><input type="text" name="vornamen" value="'.$lektor->vorname.'"></td></tr>
			<tr><td>Nachname</td><td><input type="text" name="nachname" value="'.$lektor->nachname.'"></td></tr>
			<tr><td>Lektor</td><td><input type="checkbox" name="lektor" value="1" '.($lektor->lektor?'checked':'').'></td></tr>
			<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" '.($lektor->aktiv?'checked':'') .'></td></tr>
			<tr><td>Geburtsdatum</td><td><input type="text" name="gebdatum" value="'.$lektor->gebdatum.'"> (TT.MM.JJJJ)</td></tr>
			<tr><td>Geburtsort</td><td><input type="text" name="gebort" value="'.$lektor->gebort.'"></td></tr>
			<tr><td>eMail Alias</td><td><input type="text" name="alias" value="'.$lektor->alias.'"></td></tr>
			<tr><td>Homepage</td><td><input type="text" name="homepage" value="'.$lektor->homepage.'"></td></tr>
			<tr><td>Kurzbezeichnung</td><td><input type="text" name="kurzbz" value="'.$lektor->kurzbz.'"></td></tr>
			<tr><td>Standort</td><td>
			<SELECT name="standort_kurzbz">
			<OPTION value="" selected>--Kein Standort--</OPTION>';

		$qry = "SELECT standort_kurzbz FROM public.tbl_standort ORDER BY standort_kurzbz";
		if($result=pg_query($conn,$qry))
		{
			while($row=pg_fetch_object($result))
				echo "<OPTION value='$row->standort_kurzbz' ". ($lektor->standort_kurzbz==$row->standort_kurzbz?'selected':'').">$row->standort_kurzbz</OPTION>";
		}

		echo '
			</SELECT>
			</td></tr>
			<tr><td>Telefonklappe</td><td><input type="text" name="telefonklappe" value="'.$lektor->telefonklappe.'"></td></tr>
			<tr><td>Fix angestellt</td><td><SELECT name="fixangestellt">
				<OPTION value="t" '.($lektor->fixangestellt?'selected':'').'>Ja</OPTION>
    			<OPTION value="f" '.(!$lektor->fixangestellt?'selected':'').'>Nein</OPTION>
    		</SELECT></td></tr>
			<tr><td>Raum Nr:</td><td>
			<SELECT name="raumnr">
			<OPTION value="" selected>--Kein Raum--</OPTION>';

		$qry = "SELECT ort_kurzbz FROM public.tbl_ort WHERE aktiv=true ORDER BY ort_kurzbz";
		if($result=pg_query($conn,$qry))
		{
			while($row=pg_fetch_object($result))
				echo "<OPTION value='$row->ort_kurzbz' ". ($lektor->ort_kurzbz===$row->ort_kurzbz?'selected':'').">$row->ort_kurzbz</OPTION>";
		}
	
		echo '
			</SELECT>
			</td></tr>
			</table>
			
  			<input type="submit" name="Save" value="Speichern">
  			<input type="hidden" name="id" value="'.$lektor->uid.'">
  			<input type="hidden" name="new" value="'.($new?'1':'0').'">
			</form>
			</td>
			<td valign="top">';
		
		if($lektor->uid!='')
			echo '<a href="../../content/pdfExport.php?xsl=AccountInfo&xml=accountinfoblatt.xml.php&uid='.$lektor->uid.'" >AccountInfoBlatt erstellen</a>';

		echo '</td></tr></table>';
	}
}

?>
</body>
</html>