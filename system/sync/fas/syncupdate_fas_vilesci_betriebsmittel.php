<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Berufspraktikumsdatensaetze von FAS DB in PORTAL DB
//* 
//*

require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$ausgabe='';
$ausgabe1='';
$ausgabe2='';
$all=0;
$ngef1=0;
$ngef2=0;
$upd=0;

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

//*************************

?>

<html>
<head>
<title>Zutrittskarten Datenergänzung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
$qry_bm="SELECT betriebsmittel_id,nummer FROM public.tbl_betriebsmittel WHERE nummer IS NOT NULL AND nummer<>'';";
if($result_bm = pg_query($conn, $qry_bm))
{
	$all=pg_num_rows($result_bm);
	while($row_bm = pg_fetch_object($result_bm))
	{
		$qry="SELECT * FROM person_schluessel WHERE nummer='".$row_bm->nummer."'";
		if($result = pg_query($conn_fas, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$qry_sel="SELECT * FROM public.tbl_betriebsmittelperson WHERE betriebsmittel_id=".myaddslashes($row_bm->betriebsmittel_id);
				if($result_sel = pg_query($conn, $qry_sel))
				{
					if(pg_num_rows($result_sel)>0)
					{
						$qry_upd="UPDATE public.tbl_betriebsmittelperson SET ".
							"ausgegebenam=".myaddslashes($row->verliehenam).
							"kaution=".myaddslashes($row->betrag).
							"WHERE betriebsmittel_id=".myaddslashes($row_bm->betriebsmittel_id).
							";";
						$ausgabe.="Karte: '".$row_bm->nummer."', Ausgabe: '".$row->verliehenam."', Kaution: ".$row->betrag."'\n";
						pg_query($conn, $qry_sel);
						$upd++;
					}
					else 
					{
						$ausgabe2.="Karte: '".$row_bm->nummer."' in tbl_betriebsmittelperson nicht gefunden\n";
						$ngef2++;
					}
				}
			}
			else 
			{
				$ausgabe1.="Karte: '".$row_bm->nummer."' in person_schluessel nicht gefunden\n";
				$ngef1++;
			}
		}		
	}
}
echo nl2br("Anzahl Betriebsmittel: ".$all.", nicht gefunden(vilesci/fas): ".$ngef1."/".$ngef2.", geändert: ".$upd."\n\n".$ausgabe1."\n".$ausgabe2."\n".$ausgabe);
mail($adress, 'SYNC-Update Betriebsmittel von '.$_SERVER['HTTP_HOST'], 
"Anzahl Betriebsmittel: ".$all.", nicht gefunden(fas/vilesci): ".$ngef1."/".$ngef2.", geändert: ".$upd."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");
?>
</body>
</html>