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

$error_log = '';
$fehler = 0;

$text = '';
$anzahl_quelle = 0;
$anzahl_eingefuegt = 0;
$anzahl_update = 0;
$anzahl_fehler = 0;
$ausgabe = '';
$error_log_fas = '';
$update = false;
$bismeldedatum_start = date("Y-m-d",  mktime(0, 0, 0, 1, 1, date("Y")-1));
$bismeldedatum_ende = date("Y-m-d",  mktime(0, 0, 0, 12, 31, date("Y")-1));

$ba1_arr = array();
$qry = "SELECT * FROM bis.tbl_beschaeftigungsart1";
if($result = $db->db_query($qry))
	while($row = $db->db_fetch_object($result))
		$ba1_arr[$row->ba1code]=$row->ba1kurzbz;

$ba2_arr = array();
$qry = "SELECT * FROM bis.tbl_beschaeftigungsart2";
if($result = $db->db_query($qry))
	while($row = $db->db_fetch_object($result))
		$ba2_arr[$row->ba2code]=$row->ba2bez;

$verwendung_arr = array();
$qry = "SELECT * FROM bis.tbl_verwendung";
if($result = $db->db_query($qry))
	while($row = $db->db_fetch_object($result))
		$verwendung_arr[$row->verwendung_code]=$row->verwendungbez;

$ausmass_arr = array();
$qry = "SELECT * FROM bis.tbl_beschaeftigungsausmass";
if($result = $db->db_query($qry))
	while($row = $db->db_fetch_object($result))
		$ausmass_arr[$row->beschausmasscode]=$row->beschausmassbez;

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>BIS-Meldung - &Uuml;berpr&uuml;fung von Verwendungen</title>
		<meta charset="UTF-8">
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

//**** aktive mitarbeiter und bismelden mit keiner verwendung
$qryall='
	SELECT
		uid, nachname, vorname, count(bisverwendung_id)
	FROM
		campus.vw_mitarbeiter
		LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE
		aktiv
		AND bismelden
		AND (ende>now() OR ende IS NULL)
	GROUP BY
		uid, nachname, vorname
	HAVING count(bisverwendung_id)=0
	ORDER by nachname, vorname;';
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<H2>Bei $num_rows_all aktiven Mitarbeitern sind die keine aktuellen Verwendungen vorhanden</H2>";
	echo "Es ist keine aktiven Verwendungen vorhanden. (unabhängig vom Meldezeitraum)<br>";

	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
				JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
			WHERE
				tbl_benutzer.aktiv=TRUE
				AND bismelden=TRUE
				AND (ende>now() OR ende IS NULL)
				AND mitarbeiter_uid=".$db->db_add_param($rowall->uid)."
			ORDER BY beginn";

		if ($result = $db->db_query($qry))
		{
			echo "<br><u>Aktive(r) Mitarbeiter(in): <b>".$rowall->nachname." ".$rowall->vorname."</b>
			 hat keine aktuelle Verwendung</u><br>";
		}
	}
}

//**** mehrere aktive Verwendungen im Meldezeitraum
$qryall="
	SELECT
		a.mitarbeiter_uid as uid
	FROM
		bis.tbl_bisverwendung a,
		bis.tbl_bisverwendung b
	WHERE
		a.mitarbeiter_uid = b.mitarbeiter_uid
		AND (a.ende>='$bismeldedatum_start' or a.ende is null)
		AND (a.beginn<'$bismeldedatum_ende' or a.beginn is null)
		AND a.bisverwendung_id <> b.bisverwendung_id
		AND (b.ende>='$bismeldedatum_start' or b.ende is null)
		AND (b.beginn<'$bismeldedatum_ende' or b.beginn is null)
		AND a.ende >= b.beginn
		AND a.beginn < b.beginn
	ORDER BY a.mitarbeiter_uid
";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<H2>Bei $num_rows_all aktiven Mitarbeitern sind überlappende Verwendungen im Meldezeitraum vorhanden</H2>";

	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
				JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
			WHERE
				tbl_mitarbeiter.bismelden=TRUE
				AND (tbl_bisverwendung.ende>='$bismeldedatum_start' or tbl_bisverwendung.ende is null)
				AND (tbl_bisverwendung.beginn<'$bismeldedatum_ende' or tbl_bisverwendung.beginn is null)
				AND mitarbeiter_uid=".$db->db_add_param($rowall->uid)."
			ORDER BY beginn";

		if ($result = $db->db_query($qry))
		{
			$num_rows = $db->db_num_rows($result);
			if ($num_rows > 1)
			{
				while ($row = $db->db_fetch_object($result))
				{
					if ($i == 0)
					{
						echo "
							<br>
								<u>Aktive(r) Mitarbeiter(in) <b>".$row->nachname." ".$row->vorname."</b>".
								" hat überlappende Verwendungen im Meldezeitraum:
								</u>
							<br>";
						$i++;
					}
					echo $verwendung_arr[$row->verwendung_code].", ".
						$ba1_arr[$row->ba1code].", ".
						$ba2_arr[$row->ba2code].", ".
						$ausmass_arr[$row->beschausmasscode].", ".
						$row->beginn." - ".$row->ende."<br>";
				}
			}
			elseif ($num_rows == 0)
				echo "<br><u>ELSE ZWeig: ".$rowall->uid."</u><br>";
		}
	}
}

//**** aktive fixe mitarbeiter mit keiner aktuellen verwendung
$qryall = '
	SELECT
		uid, nachname, vorname, count(bisverwendung_id)
	FROM
		campus.vw_mitarbeiter
		LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE
		aktiv
		AND fixangestellt
		AND NOT ende>now() AND NOT ende IS NULL
		AND uid NOT IN (
			SELECT uid FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
			WHERE aktiv AND fixangestellt AND (ende>now() OR ende IS NULL)
			)
		GROUP BY uid, nachname, vorname
		ORDER by nachname, vorname;';
if ($resultall = $db->db_query($qryall))
{
	$num_rows_all = $db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all aktiven Fixangestellten Mitarbeitern sind keine aktuellen Verwendungen eingetragen</H2>";
	while ($rowall = $db->db_fetch_object($resultall))
	{
		$i = 0;
		$qry = "
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
				JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)
				JOIN public.tbl_person USING(person_id)
			WHERE
				tbl_benutzer.aktiv=TRUE
				AND mitarbeiter_uid=".$db->db_add_param($rowall->uid)."
			ORDER by beginn";

		if ($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while ($row=$db->db_fetch_object($result))
			{
				if ($i == 0)
				{
					echo "<br>
							<u>
							Aktive(r) Mitarbeiter(in) <b>".$rowall->nachname." ".$rowall->vorname."</b>
							hat keine aktuellen Verwendungen:
							</u>
						<br>";
					$i++;
				}
				echo $verwendung_arr[$row->verwendung_code].", ".
					$ba1_arr[$row->ba1code].", ".
					$ba2_arr[$row->ba2code].", ".
					$ausmass_arr[$row->beschausmasscode].", ".
					$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}

//**** nicht aktive mitarbeiter mit aktueller verwendung
$qryall = '
	SELECT
		uid, nachname, vorname
	FROM
		campus.vw_mitarbeiter
		JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE
		aktiv=false
		and bismelden=true
		AND (ende>now() OR ende IS NULL)
	GROUP BY uid, nachname, vorname
	ORDER by nachname, vorname;';

if ($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all nicht aktiven Mitarbeitern sind die aktuellen Verwendungen nicht plausibel (inaktiv aber aktuelle Verwendung)</H2>";
	echo "Diese Personen werden trotzdem gemeldet!";

	while ($rowall = $db->db_fetch_object($resultall))
	{
		$i = 0;
		$qry = "
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
			WHERE
				(ende>now() OR ende IS NULL)
				AND mitarbeiter_uid=".$db->db_add_param($rowall->uid)."
			ORDER BY beginn";

		if ($result = $db->db_query($qry))
		{
			$num_rows = $db->db_num_rows($result);
			while ($row = $db->db_fetch_object($result))
			{
				if ($i == 0)
				{
					echo "<br>
						<u>
							Nicht aktive(r) Mitarbeiter(in) <b>".$rowall->nachname." ".$rowall->vorname."</b>
							hat ".$num_rows." aktuelle Verwendungen:
						</u>
						<br>";
					$i++;
				}
				echo $verwendung_arr[$row->verwendung_code].", ".
					$ba1_arr[$row->ba1code].", ".
					$ba2_arr[$row->ba2code].", ".
					$ausmass_arr[$row->beschausmasscode].", ".
					$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//**** - wenn hauptberufcode gesetzt ist, muss die Verwendung 1 sein
$qryall = "
	SELECT
		uid, nachname, vorname
	FROM
		campus.vw_mitarbeiter
		JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE
		verwendung_code NOT IN ('1')
		AND hauptberuflich=true
		AND (tbl_bisverwendung.ende>".$db->db_add_param($bismeldedatum_start)." or tbl_bisverwendung.ende is null)
	GROUP BY uid, nachname, vorname
	ORDER by nachname, vorname, uid;";

if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Mitarbeitern sind die Eintragungen 'hauptberuflich' nicht plausibel</H2>";
 	echo "hauptberuflich=ja, aber Verwendung nicht ".$verwendung_arr[1];

	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
			WHERE
				verwendung_code NOT IN ('1')
				AND hauptberuflich=true
				AND mitarbeiter_uid=".$db->db_add_param($rowall->uid)."
				AND (tbl_bisverwendung.ende>".$db->db_add_param($bismeldedatum_start)." or tbl_bisverwendung.ende is null)
			ORDER BY beginn";

		if($result = $db->db_query($qry))
		{
			$num_rows=$db->db_num_rows($result);
			while($row=$db->db_fetch_object($result))
			{
				if($i==0)
				{
					echo "<br><u>Mitarbeiter(in) <b>".$rowall->nachname." ".$rowall->vorname."</b>:</u><br>";
					$i++;
				}
				echo $verwendung_arr[$row->verwendung_code].",
					hauptberuflich ".($row->hauptberuflich=='t'?'ja':'nein').", ".
					$ba1_arr[$row->ba1code].", ".
					$ba2_arr[$row->ba2code].", ".
					$ausmass_arr[$row->beschausmasscode].", ".
					$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}

//**** aktive, freie lektoren auf verwendung 1 oder 2 prüfen
$qryall="
	SELECT
		uid, nachname, vorname
	FROM
		campus.vw_mitarbeiter
		JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE
		aktiv
		AND lektor
		AND fixangestellt=false
		AND verwendung_code NOT IN ('1','2')
		AND (ende>now() OR ende IS NULL)
	GROUP BY uid, nachname, vorname
	ORDER by nachname, vorname, uid;";

if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all aktiven, freien Lektoren ist die Verwendung nicht plausibel</H2>";
	echo "Verwendung darf nur '".$verwendung_arr[1]."' oder '".$verwendung_arr[2]."' sein.<br><br>";

	while($rowall=$db->db_fetch_object($resultall))
	{
		$i=0;
		$qry="
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
			WHERE
				verwendung_code NOT IN ('1','2')
				AND mitarbeiter_uid=".$db->db_add_param($rowall->uid)."
			ORDER BY beginn";

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
				echo $verwendung_arr[$row->verwendung_code].", ".
					$ba1_arr[$row->ba1code].", ".
					$ba2_arr[$row->ba2code].", ".
					$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}

//**** Echter Dienstvertrag ohne Vertragsstunden
$arrayEchterDV= array(103, 110);
if (defined('DEFAULT_ECHTER_DIENSTVERTRAG') && DEFAULT_ECHTER_DIENSTVERTRAG != '')
{
	$arrayEchterDV = DEFAULT_ECHTER_DIENSTVERTRAG;
}
$str_ba1codein = trim(array_reduce($arrayEchterDV, function($c, $i) { global $db; return $c .= $db->db_add_param($i, FHC_INTEGER) . ','; }), ',');

$qryall="
	SELECT
		distinct mitarbeiter_uid as uid, vorname, nachname
	FROM
		bis.tbl_bisverwendung
		JOIN public.tbl_mitarbeiter using(mitarbeiter_uid)
		JOIN public.tbl_benutzer on(uid=mitarbeiter_uid)
		JOIN public.tbl_person using(person_id)
		WHERE
		(beginn is null or beginn<".$db->db_add_param($bismeldedatum_ende).")
		and (ende is null or ende>=".$db->db_add_param($bismeldedatum_start).")
		and ba1code IN (" . $str_ba1codein . ")
		and vertragsstunden is null
		and beschausmasscode!=5
	ORDER by nachname, vorname, mitarbeiter_uid;";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Mitarbeitern mit echtem Dienstvertrag fehlen die Vertragsstunden</H2>";
	echo "Bei der BIS-Verwendung müssen bei echtem Dienstvertrag Vertragsstunden eingetragen sein
	damit die JVZÄ korrekt berechnet werden.<br><br>";

	while($rowall=$db->db_fetch_object($resultall))
	{
		echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
	}
}

//**** - Lehrauftrag aber keine aktuelle Verwendung
$i=0;
$qryall="
	SELECT
		DISTINCT lehre.tbl_lehreinheitmitarbeiter.mitarbeiter_uid, nachname, vorname
	FROM
		lehre.tbl_lehreinheitmitarbeiter
		JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
		JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
		JOIN campus.vw_mitarbeiter ON (tbl_lehreinheitmitarbeiter.mitarbeiter_uid=uid)
	WHERE
		(
			lehre.tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($lastss)."
			OR lehre.tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($lastws)."
		)
		AND tbl_lehreinheitmitarbeiter.stundensatz!=0
		AND tbl_lehreinheitmitarbeiter.semesterstunden!=0
		AND NOT EXISTS (
			SELECT * FROM bis.tbl_bisverwendung
			WHERE
				(
					beginn<".$db->db_add_param($ende[$lastss])."
					AND (ende>".$db->db_add_param($beginn[$lastws])." OR ende is null)
				)
				AND mitarbeiter_uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid
			)
		ORDER BY nachname,vorname;";

if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Lektoren <u>mit Lehrauftrag</u> sind die Verwendungen nicht plausibel (Lehrauftrag aber keine aktuelle Verwendung)</H2>";
	while($rowall=$db->db_fetch_object($resultall))
	{
		$i++;
		echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
		$qry="
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
			WHERE
				mitarbeiter_uid=".$db->db_add_param($rowall->mitarbeiter_uid)."
			ORDER BY beginn;";
		if($result = $db->db_query($qry))
		{
			while($row=$db->db_fetch_object($result))
			{
				echo $verwendung_arr[$row->verwendung_code].", ".
					$ba1_arr[$row->ba1code].", ".
					$ba2_arr[$row->ba2code].", ".
					$row->beginn." - ".$row->ende."<br>";
			}
		}
	}
}
//**** - Verwendung Habil. und Entwicklungsteam Habil.=1
$i=0;
$qryall="
	SELECT
		DISTINCT mitarbeiter_uid, nachname, vorname
	FROM
		bis.tbl_entwicklungsteam
		JOIN bis.tbl_bisverwendung USING (mitarbeiter_uid)
		JOIN campus.vw_mitarbeiter ON (tbl_entwicklungsteam.mitarbeiter_uid=uid)
	WHERE
		((besqualcode!=1 AND habilitation) OR (besqualcode=1 AND habilitation=false))
	ORDER BY mitarbeiter_uid;";
if($resultall = $db->db_query($qryall))
{
	$num_rows_all=$db->db_num_rows($resultall);
	echo "<br><br><H2>Bei $num_rows_all Lektoren sind die Angaben über Habilitationen nicht plausibel</H2>";
	echo "Wenn Besondere Qualifikation beim Entwicklungsteam gleich Habilitation ist dann muss bei der Verwendung auch
	eine Habilitation eingetragen sein.";

	while($rowall=$db->db_fetch_object($resultall))
	{
		$i++;
		echo "<br><u>Mitarbeiter(in) ".$rowall->nachname." ".$rowall->vorname.":</u><br>";
		$qry="
			SELECT
				mitarbeiter_uid, nachname, vorname, besqualbez,
				habilitation, studiengang_kz, verwendung_code,
				tbl_bisverwendung.beginn as anfang, tbl_bisverwendung.ende as zuende
			FROM
				bis.tbl_entwicklungsteam join bis.tbl_bisverwendung USING (mitarbeiter_uid)
				JOIN campus.vw_mitarbeiter ON (tbl_entwicklungsteam.mitarbeiter_uid=uid)
				JOIN bis.tbl_besqual USING(besqualcode)
			WHERE
				((besqualcode!=1 AND habilitation) OR (besqualcode=1 AND habilitation=false))
				AND mitarbeiter_uid=".$db->db_add_param($rowall->mitarbeiter_uid)."
			ORDER BY tbl_bisverwendung.beginn";
		if($result = $db->db_query($qry))
		{
			while($row=$db->db_fetch_object($result))
			{
				echo $verwendung_arr[$row->verwendung_code].", ".
					$row->anfang." - ".$row->zuende.", Habilitation ".($row->habilitation=='t'?'ja':'nein').
					" <-> Entwicklungsteam-bes.Qualifikation:(Stg. ".$row->studiengang_kz.") '".$row->besqualbez."'.<br>";
			}
		}
	}
}

//**** inaktive mitarbeiter und bismelden ohne verwendung
$qryall='
	SELECT
		uid, nachname, vorname, count(bisverwendung_id)
	FROM
		campus.vw_mitarbeiter
		LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid)
	WHERE bismelden
	GROUP BY uid, nachname, vorname
	HAVING count(bisverwendung_id)=0
	ORDER by nachname, vorname;';
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
