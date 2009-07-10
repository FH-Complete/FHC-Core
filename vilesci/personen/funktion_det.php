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
	Letzte Änderung: 	28.10.2004 Anpassung an neues DB-Schema (WM)
********************************************************************************/
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/fachbereich.class.php');

// Datenbankverbindung herstellen

$user=get_uid();
$type='';
if (isset($_POST['type']))
	$type=$_POST['type'];

if (isset($_GET['type']))
	$type=$_GET['type'];

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
// Daten für Studiengangauswahl
$sql_query="SELECT oe_kurzbz, oe_kurzbz as kurzbz, bezeichnung FROM public.tbl_organisationseinheit ORDER BY kurzbz";
$result_stg=$db->db_query($sql_query);
if(!$result_stg)
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

<html>
<head>
	<title>Funktion Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
	<H1>Funktion: <?php echo $funktion->beschreibung?></H1>
	<table class="liste">
	<tr class="liste">
	<?php

	// Liste der Personen darstellen
	if ($type!='edit')
	{
		// Personen holen
		$qry = "SELECT UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as studiengang_kurzbz, tbl_benutzer.uid as uid, * FROM public.tbl_benutzerfunktion, public.tbl_person, public.tbl_benutzer, public.tbl_studiengang 
				WHERE funktion_kurzbz='".addslashes($kurzbz)."' AND
				tbl_benutzerfunktion.uid=tbl_benutzer.uid AND
				tbl_benutzer.person_id=tbl_person.person_id AND
				tbl_benutzerfunktion.oe_kurzbz=tbl_studiengang.oe_kurzbz";

		if($result = $db->db_query($qry))
		{			
			echo "<tr class='liste'><th>Name</th><th>User-ID</th><th>Studiengang</th><th>Fachbereich</th><th colspan=\"2\">Aktion</th></tr>";
			$j=0;	
			while($row = $db->db_fetch_object($result))
			{				
				$j++;
				echo "<tr class='liste".($j%2)."'>";
				echo "<td>".$row->nachname.", ".$row->vorname."</td>";
				echo "<td>".$row->uid."</td>";
				echo "<td>".$row->studiengang_kurzbz."</td>";
				echo "<td>".$row->fachbereich_kurzbz."</td>";
				echo "<td><a href=\"funktion_det.php?type=edit&kurzbz=$kurzbz&uid=".$row->uid."&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&oe_kurzbz=$row->oe_kurzbz\">Edit</a></td>";
				echo "<td><a href=\"funktion_det.php?type=delete&kurzbz=$kurzbz&uid=".$row->uid."&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&oe_kurzbz=$row->oe_kurzbz\">Delete</a></td>";
		    	echo "</tr>\n";

			}
		} 
		else
		{
			echo "Fehler: ".	$db->db_last_error();
		}
	}
	else
	?>
	</table>
	<hr>
	<form action="funktion_det.php" method="post" name="persfunk_neu" id="persfunk_neu">
  	<p>
  	<?php
	if ($type=='edit')
	{
		echo '<INPUT type="hidden" name="type" value="editsave">';
		echo '<INPUT type="hidden" name="bn_funktion_id" value="'.$_GET['bn_funktion_id'].'">';
	}
	else
		echo '<INPUT type="hidden" name="type" value="new">';
	?>
    
    <INPUT type="hidden" name="kurzbz" value="<?php echo $kurzbz ?>">
    <table>
    <tr><td>Lektor: </td><td>
    <SELECT name="uid">
      <?php
		// Auswahl der Person
		$num_rows=$db->db_num_rows($result_person);
		while($row=$db->db_fetch_object ($result_person))
		{
			echo "<option value=\"$row->uid\" ";
			if ($type=='edit' && ($row->uid==$_GET['uid']))
				echo 'selected ';
			echo ">$row->nachname $row->vorname - $row->uid</option>";
		}
		?>
    </SELECT></td></tr>
	<tr><td>Organisationseinheit: </td><td>
    <SELECT name="oe_kurzbz">
      <option value="-1">- auswählen -</option>
      <?php
		// Auswahl des Studiengangs
		$num_rows=$db->db_num_rows($result_stg);
		while($row=$db->db_fetch_object ($result_stg))
		{
			echo "<option value=\"$row->oe_kurzbz\" ";
			if (($type=='edit') && ($row->oe_kurzbz==$_GET['oe_kurzbz']) && isset($_GET['oe_kurzbz']))
				echo 'selected ';
			echo ">$row->kurzbz</option>";
		}
		?>
    </SELECT></td></tr>
    <tr><td>Fachbereich:</td><td>
    <SELECT name="fb_kurzbz">
     <option value="-1">- auswählen -</option>
      <?php
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
      } else
      {
      	echo "Fehler: ".$fb->errormsg;
      }
      ?>
    </SELECT></td></tr></table>
    <input type="submit" name="Submit" value="<?php
	if ($type!='edit')
		echo 'Hinzufügen';
	else
		echo 'Speichern';
	?>">
  </p>
	</form>
</body>
</html>
