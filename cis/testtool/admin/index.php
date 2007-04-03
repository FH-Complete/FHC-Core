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
// Oberflaeche zur Aenderung von Beispielen und Upload von Bildern
require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/frage.class.php');
require_once('../../../include/vorschlag.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

//wandelt einen String in HEX-Werte um
function strhex($string)
{
    $hex="";
    for ($i=0;$i<strlen($string);$i++)
        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
    return $hex;
}

//Connection Herstellen
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

//$user=get_uid();
//$rechte = new benutzerberechtigung($conn);
//$rechte->getBerechtigungen($user);
//if(!$rechte->isBerechtigt('admin'))
//	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_GET['gebiet_id']))
	$gebiet_id = $_GET['gebiet_id'];
else 
	$gebiet_id = '';

if(isset($_GET['nummer']))
	$nummer = $_GET['nummer'];
else 
	$nummer = '';
	
if(isset($_GET['gruppe_id']))
	$gruppe_id = $_GET['gruppe_id'];
else 
	$gruppe_id = '';

if(isset($_GET['frage_id']))
	$frage_id = $_GET['frage_id'];
else 
	$frage_id = '';
	
if(isset($_GET['vorschlag_id']))
	$vorschlag_id = $_GET['vorschlag_id'];
else 
	$vorschlag_id = '';
	
$save_vorschlag_error=false;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="Javascript">
//Vorschau anzeigen
function preview()
{
	document.getElementById('vorschau').innerHTML = document.getElementById('text').value;
}
</script>
</head>

<body>
<h1>Admin - Fragen bearbeiten</h1>
<?php
//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		//Wenn File ein Bild ist
		if (($_FILES['bild']['type']=="image/gif") || ($_FILES['bild']['type']=="image/jpeg") || ($_FILES['bild']['type']=="image/png")) 
		{
			$filename = $_FILES['bild']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$content = strhex($content);
			
			$frage = new frage($conn);
			if($frage->load($_GET['frage_id']))
			{
				//HEX Wert in die Datenbank speichern
				$frage->bild = $content;
				$frage->new = false;
				if($frage->save())
					echo "<b>Bild gespeichert</b><br>";
				else 
					echo '<b>'.$frage->errormsg.'</b><br>';
			}
			else 
				echo '<b>'.$frage->errormsg.'</b><br>';
		}
		else 
			echo "<b>File ist kein gueltiges Bild</b><br>";
	}		
}

//Speichern der Frage-Daten
if(isset($_POST['submitdata']))
{
	$frage = new frage($conn);
	if($frage->load($_GET['frage_id']))
	{
		$frage->text = $_POST['text'];
		$frage->demo = (isset($_POST['demo'])?true:false);
		$frage->loesung = $_POST['loesung'];
		$frage->new = false;
		if($frage->save())
		{
			echo "<b>Daten gespeichert</b><br>";
		}
		else 
			echo '<b>'.$frage->errormsg.'</b><br>';
	}
	else 
		echo '<b>'.$frage->errormsg.'</b><br>';
}

//Speichern eines Vorschlages
if(isset($_POST['submitvorschlag']))
{
	$content='';
	if(isset($_FILES['bild']['tmp_name']) && is_uploaded_file($_FILES['bild']['tmp_name']))
	{
		//Wenn File ein Bild ist
		if (($_FILES['bild']['type']=="image/gif") || ($_FILES['bild']['type']=="image/jpeg") || ($_FILES['bild']['type']=="image/png")) 
		{
			$filename = $_FILES['bild']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$content = strhex($content);
		}
		else 
			echo "<b>Datei ist kein Bild!</b><br>";
	}
	
	$vorschlag = new vorschlag($conn);
	$error=false;

	if($_POST['vorschlag_id']!='')
	{
		if($vorschlag->load($_POST['vorschlag_id']))
		{		
			$vorschlag->new = false;
			$vorschlag->vorschlag_id = $_POST['vorschlag_id'];
		}
		else 
		{
			echo '<b>Fehler beim Laden des Datensatzes</b><br>';
			$error = true;
		}
	}
	else
		$vorschlag->new = true;
	if($_POST['nummer']=='' || !is_numeric($_POST['nummer']))
	{
		$error = true;
		echo '<b>Nummer ist ungueltig</b><br>';
	}
		
	if(!$error)
	{		
		$vorschlag->bild = $content;
		$vorschlag->frage_id = $_GET['frage_id'];
		$vorschlag->nummer = $_POST['nummer'];
		$vorschlag->antwort = strtoupper($_POST['antwort']);
		$vorschlag->text = $_POST['text'];
				
		if($vorschlag->save())
			echo "<b>Vorschlag gespeichert</b><br>";
		else 
		{
			$save_vorschlag_error=true;
			echo '<b>'.$vorschlag->errormsg.'</b><br>';
		}
	}
	else 
		$save_vorschlag_error=true;
}
//Vorschlag loeschen
if(isset($_GET['type']) && $_GET['type']=='delete' && isset($_GET['vorschlag_id']))
{
	$vs = new vorschlag($conn);
	if(!$vs->delete($_GET['vorschlag_id']))
		echo '<b>'.$vs->errormsg.'</b><br>';
	$vorschlag_id='';
}
//Liste der Gebiete
$qry  = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";
$result = pg_query($conn, $qry);
while($row = pg_fetch_object($result))
{
	if($gebiet_id=='')	
		$gebiet_id = $row->gebiet_id;
	if($gebiet_id==$row->gebiet_id)
		echo "<u><a href='$PHP_SELF?gebiet_id=$row->gebiet_id' class='Item'>$row->bezeichnung</a></u> -";
	else
		echo "<a href='$PHP_SELF?gebiet_id=$row->gebiet_id' class='Item'>$row->bezeichnung</a> -";
}
echo '<br><br>';

// Liste der Fragen
$qry = "SELECT distinct nummer FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' ORDER BY nummer";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($nummer=='')
			$nummer = $row->nummer;
			
		if($nummer==$row->nummer)
			echo "<u><a href='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$row->nummer' class='Item'>$row->nummer</a></u> -";
		else
			echo "<a href='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$row->nummer' class='Item'>$row->nummer</a> -";
	}
}

echo '<br><br>';
//Liste der Gruppen
$qry = "SELECT gruppe_id, gruppe_kurzbz FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND nummer='".addslashes($nummer)."' ORDER BY gruppe_kurzbz";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($gruppe_id=='')
			$gruppe_id = $row->gruppe_id;
		if($gruppe_id==$row->gruppe_id)
			echo "<u><a href='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$row->gruppe_id' class='Item'>$row->gruppe_kurzbz</a></u> -";
		else
			echo "<a href='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$row->gruppe_id' class='Item'>$row->gruppe_kurzbz</a> -";
	}
}

echo "\n\n<br>";
$frage = new frage($conn);
$frage->getFrage($gebiet_id, $nummer, $gruppe_id);
if($frage->frage_id!='')
{		
	echo "<table><tr><td>";
	//Fragen
	echo "<table>";
	echo "<tr>";
	//Upload Feld fuer Bild
	echo "<td valign='bottom'>
			<form method=POST ENCTYPE='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$gruppe_id&frage_id=$frage->frage_id'>
			Bild: <input type='file' name='bild'>
			<input type='submit' name='submitbild' value='Upload'>
			</form>
		</td></tr>";
	//Wenn ein Bild vorhanden ist, dann anzeigen
	if($frage->bild!='')
	{
		echo "\n<tr><td width=400 height=300><img src='bild.php?src=frage&frage_id=$frage->frage_id' width=400 height=300></td>";
	}
	else 
	{
		echo "\n<tr><td align=center width=400 height=300 style='background: #DDDDDD;'>Kein Bild vorhanden</td>\n";
	}
	//Zusaetzliche EingabeFelder anzeigen
	echo "<td>";
	echo "<form method=POST action='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$gruppe_id&frage_id=$frage->frage_id'>";
	echo "<table>";
	//Bei Aenderungen im Textfeld werden diese sofort in der Vorschau angezeigt
	echo "<tr><td colspan=2><textarea name='text' id='text' cols=30 rows=8 oninput='preview()'>$frage->text</textarea></td></tr>";
	echo "<tr><td>Demo <input type='checkbox' name='demo' ".($frage->demo?'checked':'').">
			Lösung <input type='text' name='loesung' value='$frage->loesung' size=1 /></td>
		 <td align='right'><input type='submit' value='Speichern' name='submitdata' /></td>";
	echo "</tr></table>";
	echo "</form>";
	echo "</td>";
	//Vorschau fuer das Text-Feld
	echo "<tr><td colspan=2>Vorschau:<br><div id='vorschau' style='border: 1px solid black'>$frage->text</div></td></tr>";
	echo "</table>";
	echo '</td><td style="border-left: 1px solid black" valign="top">';
	
	$vorschlag = new vorschlag($conn);
	
	if($vorschlag_id!='')
		if(!$vorschlag->load($vorschlag_id))
			die($vorschlag->errormsg);
	if($save_vorschlag_error)
	{
		$vorschlag->vorschlag_id = (isset($_POST['vorschlag_id'])?$_POST['vorschlag_id']:'');
		$vorschlag->frage_id = $_GET['frage_id'];
		$vorschlag->nummer = $_POST['nummer'];
		$vorschlag->antwort = $_POST['antwort'];
		$vorschlag->text = $_POST['text'];
		$vorschlag->bild = '';
	}
	//Vorschlag
	echo '<b>Vorschlag</b><br><br>';
	echo "<form method=POST ENCTYPE='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$gruppe_id&frage_id=$frage->frage_id'>";
	echo "<input type='hidden' name='vorschlag_id' value='$vorschlag->vorschlag_id'>";
	echo '<table>';
	echo '<tr>';
	echo "<td>Antwort</td><td><input type='text' size='1' name='antwort' value='$vorschlag->antwort'></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Text:</td><td><textarea name="text" id="text" rows=5 cols=30 oninput="preview()">'.$vorschlag->text.'</textarea></td>';
	echo '</tr><tr valign="top">';
	//Upload Feld fuer Bild
	echo "<td>Bild:</td><td><input type='file' name='bild'></td>";
	echo "</tr>";
	echo "<tr><td>Nummer:</td><td><input type='text' name='nummer' size=3 value='$vorschlag->nummer'></td></tr>";
	echo "<tr><td colspan='2' align='right'><input type='submit' name='submitvorschlag' value=Speichern	></td></tr>";
	echo "</table>";
	echo "</form>";
		
	echo '</td></tr></table>';
	
	$vorschlag = new vorschlag($conn);
	$vorschlag->getVorschlag($frage->frage_id);
	$i=0;
	if(count($vorschlag->result)>0)
	{
		echo '<table><tr class="liste"><th>Nummer</th><th>Antwort</th><th>Text</th><th>Bild</th><th></th><th></th></tr>';
		
		foreach ($vorschlag->result as $vs)
		{
			$i++;
			echo "<tr class='liste".($i%2)."'><td>$vs->nummer</td><td>$vs->antwort</td><td>$vs->text</td><td><img src='bild.php?src=vorschlag&vorschlag_id=$vs->vorschlag_id'></td><td><a href='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$gruppe_id&frage_id=$frage->frage_id&vorschlag_id=$vs->vorschlag_id'>edit</a></td><td><a href='$PHP_SELF?gebiet_id=$gebiet_id&nummer=$nummer&gruppe_id=$gruppe_id&frage_id=$frage->frage_id&vorschlag_id=$vs->vorschlag_id&type=delete' onclick=\"return confirm('Wollen Sie diesen Eintrag wirklich loeschen?')\">delete</a></td></tr>";
		}
		echo '</table>';
	}
}


?>
</body>
</html>
