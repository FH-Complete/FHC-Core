<?php
/*******************************************************************************
	File: 	funktion_det.php
	Descr: 	Hier werden Personen aufgelistet, die zur in funktion.php ausgewählten
			Gruppe gehören. Es können Datensätze hinzugefügt und gelöscht werden.
			Dazu wird dieses File rekursiv aufgerufen.
	Erstellt am: 25.05.2003 von Christian Paminger, Werner Masik
	Letzte Änderung: 	28.10.2004 Anpassung an neues DB-Schema (WM)
********************************************************************************/
require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/fachbereich.class.php');

// Datenbankverbindung herstellen
$conn=pg_connect(CONN_STRING);
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
	
	$funktion=new benutzerfunktion($conn);
	$funktion->uid=$_POST['uid'];
	$funktion->funktion_kurzbz=$_POST['kurzbz'];
	if (isset($_POST['stg_kz']) && $_POST['stg_kz']!=-1)
	{
		$funktion->studiengang_kz=$_POST['stg_kz'];
		
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
	$funktion=new benutzerfunktion($conn);
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
$result_person=pg_query($conn, $sql_query);
if(!$result_person)
	die (pg_errormessage($conn));
// Daten für Studiengangauswahl
$sql_query="SELECT studiengang_kz, UPPER(typ::varchar(1) || kurzbz) as kurzbz, bezeichnung FROM public.tbl_studiengang ORDER BY kurzbz";
$result_stg=pg_query($conn, $sql_query);
if(!$result_stg)
	die (pg_errormessage($conn));

// Instanz von Funktion-Klasse erzeugen
$funktion=new funktion($conn);
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
				tbl_benutzerfunktion.studiengang_kz=tbl_studiengang.studiengang_kz";
	
		if($result = pg_query($conn, $qry))
		{			
			echo "<tr class='liste'><th>Name</th><th>User-ID</th><th>Studiengang</th><th>Fachbereich</th><th colspan=\"2\">Aktion</th></tr>";
			$j=0;	
			while($row = pg_fetch_object($result))
			{				
				$j++;
				echo "<tr class='liste".($j%2)."'>";
				echo "<td>".$row->nachname.", ".$row->vorname."</td>";
				echo "<td>".$row->uid."</td>";
				echo "<td>".$row->studiengang_kurzbz."</td>";
				echo "<td>".$row->fachbereich_kurzbz."</td>";
				echo "<td><a href=\"funktion_det.php?type=edit&kurzbz=$kurzbz&uid=".$row->uid."&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&stg_kz=$row->studiengang_kz\">Edit</a></td>";
				echo "<td><a href=\"funktion_det.php?type=delete&kurzbz=$kurzbz&uid=".$row->uid."&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&stg_kz=$row->studiengang_kz\">Delete</a></td>";
		    	echo "</tr>\n";

			}
		} 
		else
		{
			echo "Fehler: ".pg_errormessage($conn);
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
		$num_rows=pg_num_rows($result_person);
		while($row=pg_fetch_object ($result_person))
		{
			echo "<option value=\"$row->uid\" ";
			if ($type=='edit' && ($row->uid==$_GET['uid']))
				echo 'selected ';
			echo ">$row->nachname $row->vorname - $row->uid</option>";
		}
		?>
    </SELECT></td></tr>
	<tr><td>Studiengang: </td><td>
    <SELECT name="stg_kz">
      <option value="-1">- auswählen -</option>
      <?php
		// Auswahl des Studiengangs
		$num_rows=pg_num_rows($result_stg);
		while($row=pg_fetch_object ($result_stg))
		{
			echo "<option value=\"$row->studiengang_kz\" ";
			if (($type=='edit') && ($row->studiengang_kz==$_GET['stg_kz']) && isset($_GET['stg_kz']))
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
      $fachbereich=new fachbereich($conn);
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
