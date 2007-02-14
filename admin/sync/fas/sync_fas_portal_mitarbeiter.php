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
/**
 * Synchronisiert Mitarbeiterdatensaetze von FAS DB in PORTAL DB
 *
 */
require_once('../../../vilesci/config.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Mitarbeiter</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//Mitarbeiter
$qry = "SELECT * FROM person JOIN mitarbeiter ON person_fk=person_pk WHERE uid NOT LIKE '\_dummy%'";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("\n Sync Mitarbeiter\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$mitarbeiter = new mitarbeiter($conn);
		
		//if($row->personalnummer!='')
		//{
			$mitarbeiter->sprache='German';
			$mitarbeiter->anrede=$row->anrede;
			$mitarbeiter->titelpost=$row->postnomentitel;
			$mitarbeiter->titelpre=$row->titel;
			$mitarbeiter->nachname=$row->familienname;			
			$mitarbeiter->vorname=$row->vorname;
			$mitarbeiter->vornamen=$row->vornamen;
			$mitarbeiter->gebdatum=$row->gebdat;
			$mitarbeiter->gebort=$row->gebort;

			$mitarbeiter->geburtsnation=$row->gebnation;
			$mitarbeiter->foto='';
			$mitarbeiter->anmerkungen=$row->bemerkung;
			$mitarbeiter->svnr=$row->svnr;
			$mitarbeiter->geschlecht=strtolower($row->geschlecht);
			$mitarbeiter->ersatzkennzeichen=$row->ersatzkennzeichen;
			if ($row->familienstand=='0')
			{
				$mitarbeiter->familienstand=null;
			}
			if ($row->familienstand=='1')
			{
				$mitarbeiter->familienstand='l';
			}
			if ($row->familienstand=='2')
			{
				$mitarbeiter->familienstand='v';
			}
			if ($row->familienstand=='3')
			{
				$mitarbeiter->familienstand='g';
			}
			if ($row->familienstand=='4')
			{
				$mitarbeiter->familienstand='w';
			}
			$mitarbeiter->anzahlkinder=$row->anzahlderkinder;
			$mitarbeiter->aktiv=($row->aktiv=='t'?true:false);
			$mitarbeiter->insertvon='SYNC';
			$mitarbeiter->insertamum='';
			$mitarbeiter->updateamum='';
			$mitarbeiter->updatevon='SYNC';
			$mitarbeiter->ext_id=$row->person_pk;
			$mitarbeiter->ext_id_mitarbeiter=$row->mitarbeiter_pk;
			$mitarbeiter->kurzbz=$row->kurzbez;
			$mitarbeiter->uid=$row->uid;
			if($row->ausbildung>0)
			{
				$mitarbeiter->ausbildungcode=$row->ausbildung;
			}
			else 
			{
				$mitarbeiter->ausbildungscode=null;
			}
			$mitarbeiter->personalnummer=$row->persnr;
			
			$mitarbeiter->gebzeit='';
			$mitarbeiter->ort_kurzbz='';
			$mitarbeiter->homepage='';
			$mitarbeiter->alias='';
			$mitarbeiter->lektor=true;
			$mitarbeiter->fixangestellt=false;
			$mitarbeiter->telefonklappe='';
			
			$qry = "SELECT person_id FROM public.tbl_benutzer WHERE uid='$row->uid'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						$qry2="SELECT * FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON mitarbeiter_uid=public.tbl_benutzer.uid JOIN public.tbl_person USING (person_id) WHERE mitarbeiter_uid='$row->uid'";
						if($result2 = pg_query($conn, $qry2))
						{		
							if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($row2=pg_fetch_object($result2))
								{
									//Mitarbeiterdaten updaten
									$mitarbeiter->new=false;
									$mitarbeiter->person_id=$row1->person_id;
									
									$mitarbeiter->gebzeit=$row2->gebzeit;
									$mitarbeiter->ort_kurzbz=$row2->ort_kurzbz;
									$mitarbeiter->homepage=$row2->homepage;
									$mitarbeiter->alias=$row2->alias;
									$mitarbeiter->lektor=($row2->lektor=='t'?true:false);
									$mitarbeiter->fixangestellt=($row2->fixangestellt=='t'?true:false);
									$mitarbeiter->telefonklappe=$row2->telefonklappe;
								}
							}
							else 
							{
								//Mitarbeiter neu anlegen
								$mitarbeiter->new=true;
							}
						}
						else 
						{
							$error_log.="Mitarbeiter von $row->uid konnte nicht gefunden werden\n";
							$error=true;	
						}
					}
					else 
					{
						$error_log.="Person_id von $row->uid konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//Mitarbeiter neu anlegen
					$mitarbeiter->new=true;
				}
				
				if(!$error)
					if(!$mitarbeiter->save())
					{
						$error_log.=$mitarbeiter->errormsg."\n";
						$anzahl_fehler++;
					}
					else 
					{
						$anzahl_eingefuegt++;
						echo "- ";
						ob_flush();
						flush();
					}
				else 
					$anzahl_fehler++;
			}
			else 
				$error_log .= "Fehler beim ermitteln der UID\n";
		//}
		//else 
		//	$error_log .= "$row->nachname ($row->uid) hat keine Personalnummer\n";
	}
}
else
{
	$error_log .= 'Mitarbeiterdatensaetze konnten nicht geladen werden\n';
}



echo nl2br("\n".$error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
$error_log.="\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler";
mail($adress, 'SYNC Mitarbeiter', $error_log);

?>
</body>
</html>