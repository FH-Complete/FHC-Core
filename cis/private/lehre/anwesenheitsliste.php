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

/**
 * Zeigt alle Gruppen an in denen sich Studenten befinden und verlinkt
 * auf die Seiten zum erstellen der Anwesenheitslisten(pdf) und Notenlisten(xls)
 *
 * Aufruf:
 * anwesenheitsliste.php?stg_kz=222&sem=1&lvid=1234
 */
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');

    $error=0;
    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
    {
       $error=1;
    }

    if(isset($_GET['stg_kz']))
    	$stg_kz=$_GET['stg_kz'];
    else
    	$error=2;

    if(isset($_GET['sem']))
    	$sem = $_GET['sem'];
    else
    	$error=2;

    if(isset($_GET['lvid']))
    	$lvid=$_GET['lvid'];
    else
    	$error=2;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader" width="70%"><font class="ContentHeader">&nbsp;Anwesenheits- und Notenlisten</font></td>
      </tr>
	  <tr>
	  	<td>
	  	<br />

	  	<?php
	if($error==0)
	{
	  	$aw_content='';
	  	$nt_content='';

	  	//Content fuer Anwesenheitslisten erstellen
	  	$stg_obj = new studiengang($conn, $stg_kz);
	  	$kurzbzlang = $stg_obj->kuerzel;

	  	//"normale" Gruppen auslesen
	  	$qry = "SELECT verband, gruppe, count(*) FROM public.tbl_lehrverband JOIN public.tbl_student USING(studiengang_kz, semester, verband, gruppe) WHERE studiengang_kz='$stg_kz' AND semester='$sem' AND student_uid not like '%Dummy%' GROUP BY verband, gruppe;";
	  	if($result = pg_query($conn,$qry))
	  	{
	  		if(pg_num_rows($result)>0)
	  		{
	  			$aw_content .= "<tr><td>.<a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid'>$kurzbzlang $sem</a></td><td>$kurzbzlang Semester $sem</td></tr>";
	  			$nt_content .= "<tr><td>.<a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&lvid=$lvid'>$kurzbzlang $sem</a></td><td>$kurzbzlang Semester $sem</td></tr>";
	  		}

	  		$lastverband = '';

	  		while($row = pg_fetch_object($result))
	  		{
	  			if($lastverband!=$row->verband)
	  			{
	  				$lastverband=$row->verband;
	  				$aw_content .= "<tr><td><a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&verband=$row->verband&lvid=$lvid'>&nbsp;&nbsp;<img src='../../../skin/images/haken.gif'>$kurzbzlang $sem$row->verband</a></td><td>$kurzbzlang Semester $sem Verband $row->verband</td></tr>";
	  				$nt_content .= "<tr><td><a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&verband=$row->verband&lvid=$lvid'>&nbsp;&nbsp;<img src='../../../skin/images/haken.gif'>$kurzbzlang $sem$row->verband</a></td><td>$kurzbzlang Semester $sem Verband $row->verband</td></tr>";
	  			}
	  			$aw_content.= "<tr><td><a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&verband=$row->verband&gruppe=$row->gruppe&lvid=$lvid'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif'>$kurzbzlang $sem$row->verband$row->gruppe</a></td><td>$kurzbzlang Semester $sem Verband $row->verband Gruppe $row->gruppe</td></tr>";
	  			$nt_content.= "<tr><td><a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&verband=$row->verband&gruppe=$row->gruppe&lvid=$lvid'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif'>$kurzbzlang $sem$row->verband$row->gruppe</a></td><td>$kurzbzlang Semester $sem Verband $row->verband Gruppe $row->gruppe</td></tr>";
	  		}
	  	}
	  	else
	  		echo "Fehler beim Auslesen der Daten";

	  	echo "<br />";
	  	//Spezialgruppen Auslesen
	  	$qry = "SELECT distinct gruppe_kurzbz, bezeichnung FROM public.tbl_gruppe JOIN public.tbl_benutzergruppe USING(gruppe_kurzbz) WHERE studiengang_kz='$stg_kz' AND semester='$sem';";
	  	if($result = pg_query($conn,$qry))
	  	{
	  		while($row = pg_fetch_object($result))
	  		{
	  			$aw_content .= "<tr><td><a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&gruppe_kurzbz=$row->gruppe_kurzbz&lvid=$lvid'>&nbsp;&nbsp;<img src='../../../skin/images/haken.gif'>$row->gruppe_kurzbz</a></td><td>$row->bezeichnung</td></tr>";
	  			$nt_content .= "<tr><td><a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&gruppe_kurzbz=$row->gruppe_kurzbz&lvid=$lvid'>&nbsp;&nbsp;<img src='../../../skin/images/haken.gif'>$row->gruppe_kurzbz</a></td><td>$row->bezeichnung</td></tr>";
	  		}
	  	}
	  	else
	  		echo "Fehler beim auslesen der Daten";

	  	echo "</table>";



	  	if($nt_content=='' && $aw_content=='')
	  	{
	  		echo "Derzeit sind in diesem Studiengang / Semester keine Studenten vorhanden";
	  	}
	  	else
	  	{
		  	if($aw_content!='')
				$aw_content = "<table border='0'><tr class='liste'><td>Gruppe</td><td>Bezeichnung</td></tr>".$aw_content."</table>";
		  	else
		  		$aw_content = "Derzeit sind in diesem Studiengang / Semester keine Studenten vorhanden";

		  	if($nt_content!='')
				$nt_content = "<table border='0'><tr class='liste'><td>Gruppe</td><td>Bezeichnung</td></tr>".$nt_content."</table>";
		  	else
		  		$nt_content = "Derzeit sind in diesem Studiengang / Semester keine Studenten vorhanden";
		  	echo "Zum Erstellen der Liste klicken Sie bitte auf die gew&uuml;nschte Gruppe!";
		  	echo "<br /><br/>";
		  	echo "<table>
		  		<tr>
		  			<th>Anwesenheitslisten</th>
		  			<th>Notenlisten</th>
		  		</tr>
		  		<tr>
		  		   <td>$aw_content</td>
		  		   <td>$nt_content</td>
		  		</tr>
		  		</table>";
	  	}
	}
	else
	{
		if($error==1)
			echo 'Es konnte keine Verbindung zur Datenbank hergestellt werden';
		elseif($error=2)
			echo 'Fehlerhafte Parameteruebergabe. Bitte versuchen Sie es erneut';
		else
			echo 'Unbekannter Fehler';
	}
	  	?>
	  	</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>