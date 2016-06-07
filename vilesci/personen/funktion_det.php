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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */


/*******************************************************************************
	File: 	funktion_det.php
	Descr: 	Hier werden Personen aufgelistet, die zur in funktion.php ausgewählten
			Gruppe gehören. Es können Datensätze hinzugefügt und gelöscht werden.
			Dazu wird dieses File rekursiv aufgerufen.
	Erstellt am: 25.05.2003 von Christian Paminger, Werner Masik
********************************************************************************/
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

// Datenbankverbindung herstellen

$user=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('mitarbeiter',null,'suid'))
    die($rechte->errormsg);

$type='';
if (isset($_POST['type']))
	$type=$_POST['type'];

if (isset($_GET['type']))
	$type=$_GET['type'];

if(isset($_GET['kurzbz']))
	$funktion_kurzbz=$_GET['kurzbz'];

if(isset($_GET['datumvon']))
	$datumvon=$_GET['datumvon'];
else
	$datumvon='';

if(isset($_GET['datumbis']))
	$datumbis=$_GET['datumbis'];
else
	$datumbis='';

// Neue Funktionszuweisung speichern
if ($type=='new' || $type=='editsave')
{
	//Einfügen in die Datenbank

	$funktion=new benutzerfunktion();
	$funktion->uid=$_POST['uid'];
	$funktion->funktion_kurzbz=$_POST['kurzbz'];
	if (isset($_POST['oe_kurzbz']) && $_POST['oe_kurzbz']!=-1)
	{
		$funktion->oe_kurzbz=$_POST['oe_kurzbz'];

		if (isset($_POST['fb_kurzbz']) && $_POST['fb_kurzbz']!=-1)
		{
			$funktion->fachbereich_kurzbz=$_POST['fb_kurzbz'];
		}
		else
		{
			$funktion->fachbereich_kurzbz=null;
		}

		$funktion->semester = (isset($_POST['semester'])?$_POST['semester']:'');
		$funktion->datum_von = $_POST['datumvon'];
		$funktion->datum_bis = $_POST['datumbis'];

		if($type=='editsave')
		{
			$funktion->new=false;
			$funktion->benutzerfunktion_id = $_POST['bn_funktion_id'];
			$funktion->updateamum=date('Y-m-d H:i:s');
			$funktion->updatevon=$user;
		}
		else
		{
			$funktion->new=true;
			$funktion->updateamum=date('Y-m-d H:i:s');
			$funktion->updatevon=$user;
			$funktion->insertamum=date('Y-m-d H:i:s');
			$funktion->insertvon=$user;
		}

		if (!$funktion->save())
		{
			echo "Fehler: ".$funktion->errormsg;
		}
	}
	else
		echo "Studiengang muss angegeben werden";

}

// Eine Funktionszuweisung loeschen
if ($type=='delete')
{
	$funktion=new benutzerfunktion();
	$bn_funktion_id=$_GET['bn_funktion_id'];
	if (!is_numeric($bn_funktion_id))
	{
		echo "Benutzer_funktion_id ist keine Zahl";
	}
	else
	{
		if (!$funktion->delete($bn_funktion_id))
		{
			echo "Fehler: ".$funktion->errormsg;
		}
	}
}

// Daten für Personenauswahl
$sql_query="SELECT nachname, vorname, uid FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) ORDER BY upper(nachname), vorname, uid";
$result_person=$db->db_query($sql_query);
if(!$result_person)
	die ($db->db_last_error());
// Daten für Organisationseinheiten
$sql_query="SELECT oe_kurzbz, organisationseinheittyp_kurzbz as kurzbz, bezeichnung FROM public.tbl_organisationseinheit ORDER BY kurzbz, bezeichnung";
$result_oe=$db->db_query($sql_query);
if(!$result_oe)
	die ($db->db_last_error());

// Instanz von Funktion-Klasse erzeugen
$funktion=new funktion();
//print_r($_GET);
$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:$_GET['kurzbz']);
if (!$funktion->load($kurzbz))
{
	echo "Fehler: ".$funktion->errormsg;
	exit();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Funktion Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body>
	<H2>Funktion: <?php echo $funktion->beschreibung?></H2><br />
	<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
	<?php

	// Liste der Personen darstellen
	if ($type!='edit')
	{
		// Personen holen
		$qry = "SELECT
					tbl_organisationseinheit.bezeichnung as oebezeichnung,
					tbl_organisationseinheit.organisationseinheittyp_kurzbz as oetyp,
					tbl_benutzer.uid as uid, *
				FROM
					public.tbl_benutzerfunktion,
					public.tbl_person,
					public.tbl_benutzer,
					public.tbl_organisationseinheit
				WHERE
					funktion_kurzbz=".$db->db_add_param($kurzbz)." AND
					tbl_benutzerfunktion.uid=tbl_benutzer.uid AND
					tbl_benutzer.person_id=tbl_person.person_id AND
					tbl_benutzerfunktion.oe_kurzbz=tbl_organisationseinheit.oe_kurzbz";

		if($result = $db->db_query($qry))
		{
			echo "<thead>
					<tr class='liste'>
						<th class='table-sortable:default'>Name</th>
						<th class='table-sortable:default'>UID</th>
						<th class='table-sortable:default'>Organisationseinheit</th>
						<th class='table-sortable:default'>Institut</th>
						<th class='table-sortable:default'>Semester</th>
						<th class='table-sortable:default'>DatumVon</th>
						<th class='table-sortable:default'>DatumBis</th>
						<th colspan=\"2\">Aktion</th>
					</tr>
				  </thead>";
			$j=0;
			echo '<tbody>';
			while($row = $db->db_fetch_object($result))
			{
				$j++;
				echo "<tr>";
				echo "<td>".$row->nachname.", ".$row->vorname."</td>";
				echo "<td>".$row->uid."</td>";
				echo "<td>".$row->oetyp.' '.$row->oebezeichnung."</td>";
				echo "<td>".$row->fachbereich_kurzbz."</td>";
				echo "<td>".$row->semester."</td>";
				echo "<td>".$row->datum_von."</td>";
				echo "<td>".$row->datum_bis."</td>";
				echo "<td><a href=\"funktion_det.php?type=edit&kurzbz=$kurzbz&uid=".$row->uid."&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&oe_kurzbz=$row->oe_kurzbz&semester=$row->semester&datumvon=$row->datum_von&datumbis=$row->datum_bis\">Edit</a></td>";
				echo "<td><a href=\"funktion_det.php?type=delete&kurzbz=$kurzbz&uid=".$row->uid."&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&oe_kurzbz=$row->oe_kurzbz&semester=$row->semester&datumvon=$row->datum_von&datumbis=$row->datum_bis\">Delete</a></td>";
		    	echo "</tr>\n";

			}
			echo '</tbody>';
		}
		else
		{
			echo "Fehler: ".	$db->db_last_error();
		}
	}

	echo '

	</table>
	<hr>
	<form action="funktion_det.php" method="post" name="persfunk_neu" id="persfunk_neu">
  	<p>
  	';

	if ($type=='edit')
	{
		echo '<INPUT type="hidden" name="type" value="editsave">';
		echo '<INPUT type="hidden" name="bn_funktion_id" value="'.$_GET['bn_funktion_id'].'">';
	}
	else
		echo '<INPUT type="hidden" name="type" value="new">';

	echo '
    <INPUT type="hidden" name="kurzbz" value="'.$kurzbz.'">
    <table>
    <tr>
    	<td>Lektor: </td>
    	<td>
    		<SELECT name="uid">';

	// Auswahl der Person
	$num_rows=$db->db_num_rows($result_person);
	while($row=$db->db_fetch_object ($result_person))
	{
		echo "<option value=\"$row->uid\" ";
		if ($type=='edit' && ($row->uid==$_GET['uid']))
			echo 'selected ';
		echo ">$row->nachname $row->vorname - $row->uid</option>";
	}

	echo '</SELECT></td></tr>';

	echo '<tr>
			<td>Organisationseinheit: </td>
			<td>
		    	<SELECT name="oe_kurzbz">
		      	<option value="-1">- auswählen -</option>';

	// Auswahl der Organisationseinheit
	$num_rows=$db->db_num_rows($result_oe);
	while($row=$db->db_fetch_object($result_oe))
	{
		echo "<option value=\"$row->oe_kurzbz\" ";
		if (($type=='edit') && ($row->oe_kurzbz==$_GET['oe_kurzbz']) && isset($_GET['oe_kurzbz']))
			echo 'selected ';
		echo ">$row->kurzbz $row->bezeichnung</option>";
	}
	echo '</SELECT></td></tr>';


	$funktion = new funktion();
	if (isset($funktion_kurzbz)) // Prevents notice "Undefined variable: funktion_kurzbz"
	{
		$funktion->load($funktion_kurzbz);
	}

	if($funktion->fachbereich)
	{
		echo '
	    <tr>
	    	<td>Fachbereich:</td>
	    	<td>
			    <SELECT name="fb_kurzbz">
			     <option value="-1">- auswählen -</option>';

		// Auswahl Fachbereich
		$fachbereich=new fachbereich();
	    if ($fachbereich->getAll())
	    {
			foreach($fachbereich->result as $fb)
	      	{
	       		echo "<option value=\"$fb->fachbereich_kurzbz\" ";
	       		if (($type=='edit') && ($fb->fachbereich_kurzbz==$_GET['fb_kurzbz']) && isset($_GET['fb_kurzbz']))
					echo 'selected ';
				echo ">$fb->fachbereich_kurzbz</option>";
	      	}
		}
		else
		{
			echo "Fehler: ".$fb->errormsg;
		}

		echo '</SELECT></td></tr>';
	}

	if($funktion->semester)
	{
		echo '
	    <tr>
	    	<td>Semester:</td>
	    	<td>
			    <SELECT name="semester">
			     <option value="">- auswählen -</option>';

		for($i=1;$i<=8;$i++)
		{
			echo "<option value=\"$i\" ";
	       		if ($type=='edit' && isset($_GET['semester']) && ($i==$_GET['semester']))
					echo 'selected ';
			echo ">$i</option>";
		}

		echo '</SELECT></td></tr>';
	}

	echo '<tr><td>Datum Von:</td><td><input type="text" name="datumvon" value="'.$datumvon.'"></td></tr>';
	echo '<tr><td>Datum Bis:</td><td><input type="text" name="datumbis" value="'.$datumbis.'"></td></tr>';
	echo '</table>';

	echo '<input type="submit" name="Submit" value="'.($type!='edit'?'Hinzufügen':'Speichern').'">';

	echo '</p></form>';
?>
</body>
</html>
