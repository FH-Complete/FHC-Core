<?php
/*******************************************************************************
	File: 	funktion_det.php
	Descr: 	Hier werden Personen aufgelistet, die zur in funktion.php ausgewählten
			Gruppe gehören. Es können Datensätze hinzugefügt und gelöscht werden.
			Dazu wird dieses File rekursiv aufgerufen.
	Erstellt am: 25.05.2003 von Christian Paminger, Werner Masik
	Letzte Änderung: 	28.10.2004 Anpassung an neues DB-Schema (WM)
********************************************************************************/
include('../config.inc.php');
include_once('../../include/person.class.php');
include_once('../../include/funktion.class.php');
include_once('../../include/fachbereich.class.php');

// Datenbankverbindung herstellen
$conn=pg_connect(CONN_STRING);

// Neue Funktionszuweisung speichern
if ($_POST['type']=='new')
{
	//Einfügen in die Datenbank
	//print_r($_POST);
	$funktion=new funktion($conn);
	$uid=$_POST['pers_id'];
	$funktion->kurzbz=$_POST['kurzbz'];
	if (isset($_POST['stg_id']) && $_POST['stg_id']!=-1)
	{
		$studiengang_kz=$_POST['stg_id'];
	} else
	{
		$studiengang_kz=null;
	}
	if (isset($_POST['fb_id']) && $_POST['fb_id']!=-1)
	{
		$fachbereich_id=$_POST['fb_id'];
	} else
	{
		$fachbereich_id=null;
	}
	if (!$funktion->addPerson($uid,$studiengang_kz,$fachbereich_id))
	{
		echo "Fehler: ".$funktion->errormsg;
	}

}

// Funktionszuweisung updaten
if ($_POST['type']=='editsave')
{
	//print_r($_POST);
	//Einfügen in die Datenbank
	$funktion=new funktion($conn);
	$personfunktion_id=$_POST['personfunktion_id'];
	$uid=$_POST['pers_id'];
	if (isset($_POST['stg_id']) && $_POST['stg_id']!=-1)
	{
		$studiengang_kz=$_POST['stg_id'];
	} else
	{
		$studiengang_kz=null;
	}
	if (isset($_POST['fb_id']) && $_POST['fb_id']!=-1)
	{
		$fachbereich_id=$_POST['fb_id'];
	} else
	{
		$fachbereich_id=null;
	}

	if (!$funktion->updatePerson($personfunktion_id,$uid,$studiengang_kz,
		$fachbereich_id))
	{
		echo "Fehler: ".$funktion->errormsg;
	}
	/*
	$sql_query="UPDATE personfunktion SET person_id=$pers_id, funktion_id=$id, studiengang_id=$stg_id";
	$sql_query.=" WHERE id=$funkpers_id";
	$result=pg_exec($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
	*/
}

// Eine Funktionszuweisung loeschen
if ($_GET['type']=="delete")
{

	$funktion=new funktion($conn);
	$personfunktion_id=$_GET['funkpers_id'];
	if (!is_numeric($personfunktion_id))
	{
		echo "personfunktion_id ist keine Zahl";
	}
	if (!$funktion->removePerson($personfunktion_id) )
	{
		echo "Fehler: ".$funktion->errormsg;
	}

}

// Daten für Personenauswahl
$sql_query="SELECT nachname, vornamen, uid FROM tbl_person ORDER BY upper(nachname), vornamen, uid";
$result_person=pg_exec($conn, $sql_query);
if(!$result_person)
	die (pg_errormessage($conn));
// Daten für Studiengangauswahl
$sql_query="SELECT studiengang_kz, kurzbz, bezeichnung FROM tbl_studiengang ORDER BY kurzbz";
$result_stg=pg_exec($conn, $sql_query);
if(!$result_stg)
	die (pg_errormessage($conn));

// Instanz von Funktion-Klasse erzeugen
$funktion=new funktion($conn);
//print_r($_GET);
if (!$funktion->load($_POST['kurzbz']))
{
	if (!$funktion->load($_GET['kurzbz']))
	{
		echo "Fehler: ".$funktion->errormsg;
		exit();
	}
}

?>

<html>
<head>
	<title>Funktion Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
	<H1>Funktion: <?php echo $funktion->bezeichnung ?></H1>
	<table class="liste">
	<tr class="liste">
	<?php

	// Liste der Personen darstellen
	if ($_GET['type']!='edit')
	{
		// Personen holen
		$personen=$funktion->getPersonen();
		if (is_array($personen))
		{
			echo "<tr class='liste'><th>Name</th><th>User-ID</th><th>Studiengang</th><th>Fachbereich</th><th colspan=\"2\">Aktion</th></tr>";
			$j=0;
			while(list($k,$v)=each($personen))
			{
				$bgcolor = $cfgBgcolorOne;
				$j++;
				echo "<tr class='liste".($j%2)."'>";
				echo "<td>".$v['person']->nachname.", ".$v['person']->titel." ".$v['person']->vornamen."</td>";
				echo "<td>".$v['person']->uid."</td>";
				echo "<td>".(isset($v['studiengang_kurzbz'])?$v['studiengang_kurzbz']:'&nbsp;')."</td>";
				echo "<td>".(isset($v['fachbereich_kurzbz'])?$v['fachbereich_kurzbz']:'&nbsp;')."</td>";
				echo "<td><a href=\"funktion_det.php?funkpers_id=$k&type=edit".(isset($v['fachbereich_id'])?"&fb_kurzbz=".$v['fachbereich_kurzbz']."&fb_id=".$v['fachbereich_id']:"")."&kurzbz=$kurzbz&stg_id=".$v['studiengang_kz']."&pers_id=".$v['person']->uid."\">Edit</a></td>";
				echo "<td><a href=\"funktion_det.php?funkpers_id=$k&type=delete".(isset($v['fachbereich_id'])?"&fb_kurzbz=".$v['fachbereich_kurzbz']."&fb_id=".$v['fachbereich_id']:"")."&kurzbz=$kurzbz&stg_id=".$v['studiengang_kz']."&pers_id=".$v['person']->uid."\">Delete</a></td>";
		    	echo "</tr>\n";

			}
		} else
		{
			echo "Fehler: ".$funktion->errormsg;
		}
	}
	else if ($_GET['type']!='edit')
		echo "Kein Eintrag gefunden!";
	?>
	</table>
	<hr>
	<form action="funktion_det.php" method="post" name="persfunk_neu" id="persfunk_neu">
  	<p>
  	<?php
	if ($_GET['type']=='edit')
	{
		echo '<INPUT type="hidden" name="type" value="editsave">';
		echo '<INPUT type="hidden" name="funkpers_id" value="'.$_GET['funkpers_id'].'">';
	}
	else
		echo '<INPUT type="hidden" name="type" value="new">';

	if (isset($_POST['kurzbz']))
	{
		$kurzbz=$_POST['kurzbz'];

	} else if (isset($_GET['kurzbz']))
	{
		 $kurzbz=$_GET['kurzbz'];
	}
	?>

    <INPUT type="hidden" name="personfunktion_id" value="<?php echo $_GET['funkpers_id'];?>">
    <INPUT type="hidden" name="kurzbz" value="<?php echo $kurzbz ?>">
    <table>
    <tr><td>Lektor: </td><td>
    <SELECT name="pers_id">
      <?php
		// Auswahl der Person
		$num_rows=pg_numrows($result_person);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_person, $i);
			echo "<option value=\"$row->uid\" ";
			if (($_GET['type']=='edit') && ($row->uid==$_GET['pers_id']))
				echo 'selected ';
			echo ">$row->nachname $row->vornamen - $row->uid</option>";
		}
		?>
    </SELECT></td></tr>
	<tr><td>Studiengang: </td><td>
    <SELECT name="stg_id">
      <option value="-1">- auswählen -</option>
      <?php
		// Auswahl des Studiengangs
		$num_rows=pg_numrows($result_stg);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_stg, $i);
			echo "<option value=\"$row->studiengang_kz\" ";
			if (($type=='edit') && ($row->studiengang_kz==$_GET['stg_id']) && isset($_GET['stg_id']))
				echo 'selected ';
			echo ">$row->kurzbz</option>";
		}
		?>
    </SELECT></td></tr>
    <tr><td>Fachbereich:</td><td>
    <SELECT name="fb_id">
     <option value="-1">- auswählen -</option>
      <?php
      // Auswahl Fachbereich
      $fachbereich=new fachbereich($conn);
      if ($fb_alle=$fachbereich->getAll()) {
      	foreach($fb_alle as $fb)
      	{
       		echo "<option value=\"$fb->id\" ";
       		if (($type=='edit') && ($fb->id==$_GET['fb_id']) && isset($_GET['fb_id']))
				echo 'selected ';
			echo ">$fb->kurzbz</option>";
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
