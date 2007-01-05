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

//*
//* Synchronisiert schluesseltypdatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/schluesseltyp.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function validate($row)
{
}
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Schlüsseltyp</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS-PORTAL - Synchronisation
 */


$qry = "SELECT * FROM schluessel;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Schlüsseltyp Sync\n---------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();	
			
		$error=false;
		$schluesseltyp				=new schluesseltyp($conn);
		$schluesseltyp->beschreibung		=$row->name;
		$schluesseltyp->anzahl			=$row->anzahl==''?'0':$row->anzahl;
		$schluesseltyp->kaution			=$row->betrag==''?'0':$row->betrag;

		if($row->name=='Gaderobenschlüssel')
		{
			$schluesseltyp->schluesseltyp='Gaderobe';
		}
		else
		{
			$schluesseltyp->schluesseltyp=$row->name;
		}

		
		$schluesseltyp->new=true;
		if(!$schluesseltyp->save())
		{
			$error_log.=$schluesseltyp->errormsg."\n";
			$anzahl_fehler++;
		}
		else 
		{
			
			//überprüfen, ob sync-eintrag schon vorhanden
			$qryz="SELECT * FROM tbl_syncschluesseltyp WHERE fas_typ='$row->schluesseltyp_pk' AND portal_typ='$schluesseltyp->schluesseltyp'";
			if($resultz = pg_query($conn, $qryz))
			{
				if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
				{
					$qry='INSERT INTO tbl_syncschluesseltyp (fas_typ, portal_typ)'.
						'VALUES ('.$row->schluesseltyp_pk.', '.$person->schluesseltyp.');';
					$resulti = pg_query($conn, $qry);
				}
					}
			
			$anzahl_eingefuegt++;
		}		
	}
}	


//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>