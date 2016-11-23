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
/**
 * Überprüfung der Verwendungsdatensaetze im FASonline
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/studiensemester.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('mitarbeiter/stammdaten', null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$error_log='';
$fehler=0;

$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$ausgabe='';
$error_log_fas='';
$update=false;
$bismeldedatum=date("Y-m-d",  mktime(0, 0, 0, 9, 1, date("Y")));
$bismeldedatumvorjahr=date("Y-m-d",  mktime(0, 0, 0, 9, 1, date("Y")-1));

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>BIS-Meldung - &Uuml;berpr&uuml;fung von Verwendungen</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	</head>
<body>
	<H1>BIS-Verwendungen werden &uuml;berpr&uuml;ft</H1>
	<br />
<?php
$qry="SELECT * FROM public.tbl_studiensemester";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$beginn[$row->studiensemester_kurzbz]=$row->start;
		$ende[$row->studiensemester_kurzbz]=$row->ende;
	}
}
$stsem_obj = new studiensemester();
$lastss = $stsem_obj->getPrevious();
$lastws = $stsem_obj->getBeforePrevious();

//1 - aktive mitarbeiter und bismelden mit keiner verwendung oder mehr als einer aktuellen verwendung
$qryall='SELECT uid,nachname,vorname, count(bisverwendung_id)
	FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE aktiv AND bismelden AND (ende>now() OR ende IS NULL)
	GROUP BY uid,nachname,vorname HAVING count(bisverwendung_id)!=1 ORDER by nachname,vorname;';
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<H2>Bei $num_rows_all aktiven Mitarbeitern sind die aktuellen Verwendungen nicht plausibel</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung
			JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)
			JOIN public.tbl_person USING(person_id)
			JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
			WHERE tbl_benutzer.aktiv=TRUE AND bismelden=TRUE
			AND (ende>now() OR ende IS NULL) AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			if($num_rows>1)
			{
				while($row=$db->db_fetch_object($result))
				{
					if($i==0)
					{
						echo "<br><u>Aktive(r) Mitarbeiter(in) ".$row->nachname." ".$row->vorname." hat ".$num_rows." aktuelle Verwendungen (m&ouml;glicherweise korrekt):</u><br>";
						$i++;
					}
					echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
				}
			}
			elseif($num_rows==0)
				echo "<br><u>Aktive(r) Mitarbeiter(in): ".$rowall->nachname." ".$rowall->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
		}
	}
}
//2 - aktive mitarbeiter mit keiner aktuellen verwendung
$qryall='SELECT uid,nachname,vorname, count(bisverwendung_id)
	FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE aktiv AND NOT ende>now() AND NOT ende IS NULL
	AND uid NOT IN (SELECT uid FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE aktiv AND (ende>now() OR ende IS NULL)) GROUP BY uid,nachname,vorname ORDER by nachname,vorname;';
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all aktiven Mitarbeitern sind keine aktuellen Verwendungen eingetragen</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) JOIN public.tbl_person USING(person_id)
			WHERE tbl_benutzer.aktiv=TRUE AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while($row=$db->db_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Aktive(r) Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname." hat keine aktuellen Verwendungen:</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}

//3 - nicht aktive mitarbeiter mitarbeiter mit aktueller verwendung
$qryall='SELECT uid,nachname,vorname FROM campus.vw_mitarbeiter
	JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE aktiv=false AND (ende>now() OR ende IS NULL)
	GROUP BY uid,nachname,vorname
	ORDER by nachname,vorname;';

if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all nicht aktiven Mitarbeitern sind die aktuellen Verwendungen nicht plausibel</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung
			WHERE (ende>now() OR ende IS NULL) AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while($row=$db->db_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Nicht aktive(r) Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//4 - wenn hauptberuf=j dann sollte verwendung=1,5,6 sein - check
$qryall="SELECT uid,nachname,vorname FROM campus.vw_mitarbeiter
	JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE verwendung_code NOT IN ('1','5','6') AND hauptberuflich=true
	GROUP BY uid,nachname,vorname
	ORDER by nachname,vorname,uid;";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Mitarbeitern sind die Eintragungen 'hauptberuflich' nicht plausibel (hauptberuflich ja, aber Verwendung nicht 1,5,6)</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung
			WHERE verwendung_code NOT IN ('1','5','6') AND hauptberuflich=true AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while($row=$db->db_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", hauptberuflich ".($row->hauptberuflich=='t'?'ja':'nein').", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//5 - stimmt beschausmasscode mit vertragsstunden überein?
$qryall="SELECT uid,nachname,vorname FROM campus.vw_mitarbeiter
	JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE (beschausmasscode='1' AND vertragsstunden<='35')
	OR (beschausmasscode='2' AND vertragsstunden>'15')
	OR (beschausmasscode='3' AND vertragsstunden<'16')
	OR (beschausmasscode='3' AND vertragsstunden>'25')
	OR (beschausmasscode='4' AND vertragsstunden<'26')
	OR (beschausmasscode='4' AND vertragsstunden>'35')
	OR (beschausmasscode='5' AND vertragsstunden>'0')
	GROUP BY uid,nachname,vorname
	ORDER by nachname,vorname,uid;";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Mitarbeitern ist das Beschäftigungsausmaß nicht plausibel</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung
			WHERE ((beschausmasscode='1' AND vertragsstunden<'38.5')
			OR (beschausmasscode='2' AND vertragsstunden>'15')
			OR (beschausmasscode='3' AND vertragsstunden<'16')
			OR (beschausmasscode='3' AND vertragsstunden>'25')
			OR (beschausmasscode='4' AND vertragsstunden<'26')
			OR (beschausmasscode='4' AND vertragsstunden>'35')
			OR (beschausmasscode='5' AND vertragsstunden>'0'))
			AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while($row=$db->db_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
					$i++;
				}
				echo "Beschäftigungsausmaß ".$row->beschausmasscode.", Vertragsstunden ".$row->vertragsstunden.", Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//6 - aktive, freie lektoren auf verwendung 1 oder 2 prüfen
$qryall="SELECT uid,nachname,vorname FROM campus.vw_mitarbeiter
	JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE aktiv 	AND lektor AND fixangestellt=false
	AND verwendung_code NOT IN ('1','2') AND (ende>now() OR ende IS NULL)
	GROUP BY uid,nachname,vorname
	ORDER by nachname,vorname,uid;";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all aktiven, freien Lektoren ist die Verwendung nicht plausibel</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung
			WHERE verwendung_code NOT IN ('1','2')
			AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while($row=$db->db_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
					$i++;
				}
				echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//7 - Lehrauftrag aber keine aktuelle Verwendung
$i=0;
$qryall="SELECT DISTINCT lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid, nachname, vorname
	FROM lehre.tbl_lehreinheitmitarbeiter join lehre.tbl_lehreinheit USING (lehreinheit_id)
	JOIN 
		lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
       	JOIN campus.vw_mitarbeiter ON (tbl_lehreinheitmitarbeiter.mitarbeiter_uid=uid)
	WHERE 
		(lehre.tbl_lehreinheit.studiensemester_kurzbz='$lastss' OR lehre.tbl_lehreinheit.studiensemester_kurzbz='$lastws')
		AND tbl_lehreinheitmitarbeiter.stundensatz!=0 AND tbl_lehreinheitmitarbeiter.semesterstunden!=0
       	AND NOT EXISTS (SELECT * FROM bis.tbl_bisverwendung
       			WHERE ((beginn<'".$ende[$lastss]."') AND (ende>'".$beginn[$lastws]."') OR ende is null) AND mitarbeiter_uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid)
        		ORDER BY nachname,vorname;";

if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Lektoren <u>mit Lehrauftrag</u> sind die Verwendungen nicht plausibel</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i++;
		echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
		$qry="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$rowall->mitarbeiter_uid."';";
		if($result = $db->db_query($qry))
		{
			while($row=$db->db_fetch_object($result))
			{
				echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", ".$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//8 - Verwendung Habil. und Entwicklungsteam Habil.=1
$i=0;
$qryall="SELECT DISTINCT mitarbeiter_uid, nachname, vorname
	FROM bis.tbl_entwicklungsteam join bis.tbl_bisverwendung USING (mitarbeiter_uid)
	JOIN campus.vw_mitarbeiter ON (tbl_entwicklungsteam.mitarbeiter_uid=uid)
	WHERE ((besqualcode!=1 AND habilitation) OR (besqualcode=1 AND habilitation=false))
        	ORDER BY mitarbeiter_uid;";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Lektoren sind die Angaben über Habilitationen nicht plausibel</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i++;
		echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
		$qry="SELECT mitarbeiter_uid, nachname, vorname, besqualbez, habilitation, studiengang_kz, verwendung_code, tbl_bisverwendung.beginn as anfang, tbl_bisverwendung.ende as zuende
			FROM bis.tbl_entwicklungsteam join bis.tbl_bisverwendung USING (mitarbeiter_uid)
		        	JOIN campus.vw_mitarbeiter ON (tbl_entwicklungsteam.mitarbeiter_uid=uid)
		        	JOIN bis.tbl_besqual USING(besqualcode)
			WHERE ((besqualcode!=1 AND habilitation) OR (besqualcode=1 AND habilitation=false))
			AND mitarbeiter_uid='".$rowall->mitarbeiter_uid."';";
		if($result = $db->db_query($qry))
		{
			while($row=$db->db_fetch_object($result))
			{
				echo "Verwendung Code ".$row->verwendung_code.", ".$row->anfang." - ".$row->zuende.", Habilitation ".($row->habilitation=='t'?'ja':'nein')." <-> Entwicklungsteam-bes.Qualifikation:(Stg. ".$row->studiengang_kz.") '".$row->besqualbez."'.<br>";
			}
		}
	}
}

//9 - 2 gleiche aktive Verwendungen
$qryall="
		SELECT distinct vorname, nachname, personalnummer, ba1code, ba2code FROM campus.vw_mitarbeiter JOIN (
		SELECT a.mitarbeiter_uid, a.ba1code, a.ba2code FROM bis.tbl_bisverwendung a, bis.tbl_bisverwendung b WHERE
		a.bisverwendung_id<>b.bisverwendung_id AND
		a.ba1code=b.ba1code AND
		a.ba2code=b.ba2code AND
		a.mitarbeiter_uid=b.mitarbeiter_uid AND
		(a.ende is null OR a.ende>'$bismeldedatumvorjahr') AND
		(b.ende is null OR b.ende>'$bismeldedatumvorjahr') AND
		a.beschausmasscode=b.beschausmasscode AND
		a.verwendung_code=b.verwendung_code
		) c ON(mitarbeiter_uid=uid)";

if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Lektoren sind mehrere gleiche aktive Verwendungen vorhanden</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i++;
		echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
		echo "(ba1code: $rowall->ba1code, ba2code: $rowall->ba2code)";		
	}
}
echo '<br>';
//9 - inaktive mitarbeiter und bismelden ohne verwendung
$qryall='SELECT uid,nachname,vorname, count(bisverwendung_id)
	FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE bismelden
	GROUP BY uid,nachname,vorname HAVING count(bisverwendung_id)=0 ORDER by nachname,vorname;';
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<H2>Bei $num_rows_all Mitarbeitern sind keine Verwendungen vorhanden - diese werden nicht BIS gemeldet</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		echo '<br>'.$rowall->nachname.' '.$rowall->vorname."($rowall->uid)";
	}
}
?>
</body>
</html>
