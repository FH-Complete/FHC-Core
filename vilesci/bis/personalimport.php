<?php
/* Copyright (C) 2007 Technikum-Wien
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
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/bisverwendung.class.php');
require_once('../../include/studiensemester.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der DB Connection');

$user=get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
<script language="Javascript">
</script>
<html>
<body>
<h2>Personal - Funktionen - Import</h2>
<?php
if(!$rechte->isBerechtigt('mitarbeiter') && !$rechte->isBerechtigt('admin'))
	die('Sie haben nicht die erforderlichen Rechte zum Importieren der Personaldaten');
	
$anzahl_personen_gesamt=0;
$anzahl_verwendungen_gesamt=0;
$anzahl_funktionen_gesamt=0;
$anzahl_personen_failed=0;
$anzahl_verwendungen_failed=0;
$anzahl_funktionen_failed=0;
$anzahl_funktionen_insert=0;

function getValue($obj)
{
	foreach ($obj as $row) 
	{
		return $row->nodeValue;	
	}
}

if(isset($_POST['submitfile']))
{
	if(isset($_FILES['datei']['tmp_name']))
	{
		$filename = $_FILES['datei']['tmp_name'];
		//File oeffnen
		
		$doc = new DOMDocument;
		if(!$doc->load($filename))
			die('XML konnte nicht geladen werden');
			
		$personen = $doc->getElementsByTagName('Person');
		
		foreach ($personen as $person)
		{
			//Personalnummer ermitteln
			$persnr = $person->getElementsByTagName('PersonalNummer');
			$habilitation = $person->getElementsByTagName('Habilitation');
			$personalnummer = (int)getValue($persnr);
			
			$anzahl_personen_gesamt++;
			//Mitarbeiter mit dieser Personalnummer holen
			$qry = "SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE personalnummer='$personalnummer'";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$mitarbeiter_uid = $row->mitarbeiter_uid;
				}
				else 
				{
					echo "<br>Mitarbeiter mit der Personalnummer $personalnummer wurde nicht gefunden.";
					$anzahl_personen_failed++;
					continue;
				}
			}
			else
			{
				$anzahl_personen_failed++;
				echo "<br>Fehlerhafte qry:".$qry;
				continue;
			}
			
			//Verwendungen durchgehen
			$verwendungen = $person->getElementsByTagName('Verwendung');
			
			foreach ($verwendungen as $verwendung)
			{
				$beschart1 = getValue($verwendung->getElementsByTagName('BeschaeftigungsArt1'));
				$beschart2 = getValue($verwendung->getElementsByTagName('BeschaeftigungsArt2'));
				$ausmass = getValue($verwendung->getElementsByTagName('BeschaeftigungsAusmass'));
				$verwendungscode = getValue($verwendung->getElementsByTagName('VerwendungsCode'));
				$anzahl_verwendungen_gesamt++;
				
				//Verwendung in der Datenbank suchen
				$qry = "SELECT bisverwendung_id FROM bis.tbl_bisverwendung 
						WHERE 
							ba1code='$beschart1' AND 
							ba2code='$beschart2' AND 
							beschausmasscode='$ausmass' AND 
							verwendung_code='$verwendungscode' AND 
							mitarbeiter_uid='$mitarbeiter_uid'
						ORDER BY beginn DESC LIMIT 1";
				if($result = pg_query($conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$bisverwendung_id = $row->bisverwendung_id;
					}
					else 
					{
						//echo "<br>$mitarbeiter_uid: BisVerwendung (ba1code: $beschart1, ba2code: $beschart2, ausmass: $ausmass, verwendungscode: $verwendungscode) wurde nicht gefunden";
						//$anzahl_verwendungen_failed++;
						//Anlegen wenn Verwendung nicht gefunden wird
						$bisverwendung = new bisverwendung($conn);
						$bisverwendung->ba1code = $beschart1;
						$bisverwendung->ba2code = $beschart2;
						$bisverwendung->vertragsstunden = 0;
						$bisverwendung->beschausmasscode = $ausmass;
						$bisverwendung->verwendung_code = $verwendungscode;
						$bisverwendung->mitarbeiter_uid = $mitarbeiter_uid;
						$bisverwendung->hauptberufcode = '';
						$bisverwendung->hauptberuflich = true;
						$bisverwendung->habilitation = ($habilitation=='J' || $habilitation=='j'?true:false);
						$studiensemester = new studiensemester($conn);
						$stsem = $studiensemester->getPrevious();
						$studiensemester->load($stsem);
						$bisverwendung->beginn = $studiensemester->start;
						$bisverwendung->ende = '';
						$bisverwendung->updateamum = date('Y-m-d H:i:s');
						$bisverwendung->updatevon = 'bisimport';
						$bisverwendung->insertamum = date('Y-m-d H:i:s');
						$bisverwendung->insertvon = 'bisimport';
						
						if($bisverwendung->save(true))
						{
							echo "<br>$mitarbeiter_uid: BisVerwendung (ba1code: $beschart1, ba2code: $beschart2, ausmass: $ausmass, verwendungscode: $verwendungscode) wurde neu angelegt";	
							$bisverwendung_id = $bisverwendung->bisverwendung_id;
						}
						else 
						{
							echo "<br>$mitarbeiter_uid: BisVerwendung (ba1code: $beschart1, ba2code: $beschart2, ausmass: $ausmass, verwendungscode: $verwendungscode) konnte nicht angelegt werden: $bisverwendung->errormsg";
							$anzahl_verwendungen_failed++;
							continue;
						}						
					}
				}
				else 
				{
					$anzahl_verwendungen_failed++;
					echo "<br>Fehlerhafte qry:".$qry;
					continue;
				}
				
				//Funktionen
				$funktionen = $verwendung->getElementsByTagName('Funktion');
				
				foreach ($funktionen as $funktion)
				{
					$stgkz = (int)getValue($funktion->getElementsByTagName('StgKz'));
					$sws = getValue($funktion->getElementsByTagName('SWS'));
					$anzahl_funktionen_gesamt++;
					//echo "<br>$mitarbeiter_uid: $stgkz/$sws - $bisverwendung_id";
					//Funktion in der Datenbank suchen
					$qry = "SELECT sws FROM bis.tbl_bisfunktion WHERE bisverwendung_id='$bisverwendung_id' AND studiengang_kz='$stgkz'";
					if($result = pg_query($conn, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							if($row->sws!=$sws)
							{
								$qry = "UPDATE bis.tbl_bisfunktion 
										SET sws='$sws', updateamum=now(), updatevon='$user'										
										WHERE bisverwendung_id='$bisverwendung_id' AND studiengang_kz='$stgkz'";
								if(pg_query($conn, $qry))
								{
									echo "<br>$mitarbeiter_uid: SWS der  Funktion (id: $bisverwendung_id, stg: $stgkz) wurde von $row->sws auf $sws geaendert";
								}
								else 
								{
									$anzahl_funktionen_failed++;
									echo "<br>Fehler bei qry:".$qry;
								}
							}
						}
						else 
						{
							$qry = "INSERT INTO bis.tbl_bisfunktion(bisverwendung_id, studiengang_kz, sws, updateamum, updatevon, insertamum, insertvon)
									VALUES('$bisverwendung_id','$stgkz','$sws',null, null, now(),'$user');";
							if(pg_query($conn, $qry))
							{
								$anzahl_funktionen_insert++;
								echo "<br>$mitarbeiter_uid: Neue Funktion wurde angelegt (id: $bisverwendung_id, stg: $stgkz, sws: $sws)";
							}
							else 
							{
								echo "<br>$mitarbeiter_uid: Fehler beim Anlegen der Funktion: $qry";
								$anzahl_funktionen_failed++;
							}
						}
					}
					else 
					{
						$anzahl_funktionen_failed++;
						echo "<br>Fehlerhafte qry:".$qry;
					}
				}
			}
		}
		
		echo '<br><br> ------------------- ';
		echo "<br>Anzahl der Personen im XML-File: $anzahl_personen_gesamt";
		echo "<br>Anzahl der Verwendungen im XML-File: $anzahl_verwendungen_gesamt";
		echo "<br>Anzahl der Funktionen im XML-File: $anzahl_funktionen_gesamt";
		echo "<br>Anzahl der Personen die nicht gefunden wurden: $anzahl_personen_failed";
		echo "<br>Anzahl der Verwendungen die nicht gefunden wurden: $anzahl_verwendungen_failed";
		echo "<br>Anzahl der Fehler bei Funktionen: $anzahl_funktionen_failed";
		echo "<br>Anzahl der eingefuegten Funktionen: $anzahl_funktionen_insert";
		
	}
}
else 
{
	//Formular zum Hochladen der XML Datei
	echo "	<form method='POST' enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."'>
			BIS XML: <input type='file' name='datei' />
			<input type='submit' name='submitfile' value='Upload' />
			</form>
		</td></tr>";
}

?>
</body>
</html>