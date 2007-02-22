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
/* 
 * Ermoeglicht das Anmelden zu Freifaechern
 */
require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');

if(!$conn=pg_connect(CONN_STRING))
	die('Die Datenbankverbindung konnte nicht hergestellt werden.');
	
$user = get_uid();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
	<title>Freifaecher Anmeldungsuebersicht</title>
	</head>
	
	<body>
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		<tr>
	    <td width="10">&nbsp;</td>
	    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
	    	<tr> 
	      	<td class="ContentHeader"><font class="ContentHeader">&nbsp;Freif&auml;cher Anmeldunguebersicht</font></td>
	    	</tr>
	    	<tr> 
	      	<td>&nbsp;</td>
	    	</tr>
	    	<tr>
		    	<td>
		    	Bitte w&auml;hlen Sie eines der Freif&auml;cher aus
		    	<br />
<?php
$lvid = isset($_POST['lvid'])?$_POST['lvid']:'';

//Aktuelles Studiensemester holen
$stsem_obj = new studiensemester($conn);
$stsem = $stsem_obj->getaktorNext();


$lv_obj = new lehrveranstaltung($conn);

if($lv_obj->load_lva('0',null,null,true))
{	
	echo "<FORM method='POST' name='frmauswahl'>";
	echo "<SELECT name='lvid' onchange='window.document.frmauswahl.submit();'>";
	if($lvid=='')
		echo "\n<OPTION value='0' selected>--Auswahl--</OPTION>";
	foreach($lv_obj->lehrveranstaltungen as $row)
	{
		if($lvid==$row->lehrveranstaltung_id)
			echo "\n<OPTION value='$row->lehrveranstaltung_id' selected>$row->bezeichnung</OPTION>";
		else 
			echo "\n<OPTION value='$row->lehrveranstaltung_id'>$row->bezeichnung</OPTION>";
	}
	echo "\n</SELECT>";
	echo "\n</FORM>";
}
else
{
	die("Fehler bei Auslesen der Freifaecher! Bitte versuchen Sie es erneut");
}

//Wenn das Formular abgeschickt wurde
if(isset($_POST['lvid']))
{
	
	$qry = "SELECT 
				vorname, 
				nachname, 
				uid, 
				tbl_student.semester as semester, 
				tbl_studiengang.kurzbzlang 
			FROM 
				campus.vw_benutzer
				LEFT JOIN 
				(public.tbl_student LEFT JOIN public.tbl_studiengang using (studiengang_kz)) ON (student_uid = uid) 
			WHERE
				uid IN (SELECT uid FROM campus.tbl_benutzerlvstudiensemester 
				        WHERE lehrveranstaltung_id='$lvid' AND studiensemester_kurzbz='$stsem')
			ORDER BY
				nachname, vorname";
	
if($result=pg_query($conn,$qry))
{
	$ff = array();	
	$content='';
	
	$mailto= "&nbsp;<a href='mailto:";
	$content .= "<table>\n  <tr class='liste'><th></th><th>Nachname</th><th>Vorname</th><th>Mail</th><th>Studiengang</th><th>Semester</th></tr>";
	$i=0;
	while($row=pg_fetch_object($result))
	{
		$i++;
		$content .= "\n<tr class='liste".($i%2)."'><td>$i</td><td>$row->nachname</td><td>$row->vorname</td><td><a href='mailto:$row->uid@technikum-wien.at'>$row->uid@technikum-wien.at</a></td><td align='center'>$row->kurzbzlang</td><td align='center'>$row->semester</td></tr>";
		if($i!=1)
			$mailto.=",";
		$mailto.=$row->uid."@technikum-wien.at";
	}
	$mailto.="'>Mail an alle in diesem Freifach senden</a>";
	$content .= "</table>";
	
	if($i==0)
	{
		echo "<b>Es gibt noch keine Anmeldungen für dieses Freifach</b>";
	}
	else 
	{
		//echo "Anzahl der Anmeldungen: ".$i;
		echo $content;
		echo "<br />";
		echo $mailto;
	}
}
else 
	echo "Fehler beim Auslesen der Zuteilunstabelle";	
}

?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</body>
</html>