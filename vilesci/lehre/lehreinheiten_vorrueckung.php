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
 *          Manfred Kindl 	< manfred.kindl@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/lehreinheitgruppe.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/bisverwendung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('lehre/vorrueckung', null, 'suid'))
	die($rechte->errormsg);

$stg_obj = new studiengang();
$stg_obj->loadArray($rechte->getStgKz('lehre/vorrueckung'),'typ, kurzbz');

$stg_arr = array();

foreach ($stg_obj->result as $stg)
{
	$stg_arr[$stg->studiengang_kz] = $stg->kuerzel;
}

//Default BA1Codes für echte Dienstverträge aus Config Laden
$arrEchterDV = [103, 110];
if (defined('DEFAULT_ECHTER_DIENSTVERTRAG') && DEFAULT_ECHTER_DIENSTVERTRAG != '')
{
	$arrEchterDV = DEFAULT_ECHTER_DIENSTVERTRAG;
}

$studiengang_kz = (isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'');
$semester = (isset($_GET['semester'])?$_GET['semester']:'');
$stsem_von = (isset($_GET['stsem_von'])?$_GET['stsem_von']:'');
$stsem_nach = (isset($_GET['stsem_nach'])?$_GET['stsem_nach']:'');
$text = '';
$anzahl_lehreinheiten = 0;
$anzahl_lehreinheitmitarbeiter = 0;
$anzahl_lehreinheitgruppe = 0;
$error_lehreinheit = 0;
$error_lehreinheitmitarbeiter = 0;
$error_lehreinheitgruppe = 0;

if ($stsem_von == '')
{
	$stsem_obj = new studiensemester();
	$stsem_von = $stsem_obj->getPrevious();
}

if ($stsem_nach == '')
{
	$stsem_obj = new studiensemester();
	$stsem_obj->getNextStudiensemester();
	$stsem_nach = $stsem_obj->studiensemester_kurzbz;
}

echo '<!DOCTYPE HTML>
<html>
<head>
	<title>Lehreinheit Vorrueckung</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body style="background-color:#eeeeee;">
<h2>Lehreinheiten Vorr&uuml;ckung</h2>
';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Studiengang: <SELECT name="studiengang_kz">';
echo '<OPTION value="">---Stg ausw&auml;hlen---</OPTION>';
$types = new studiengang();
$types->getAllTypes();
$typ = '';
foreach($stg_obj->result as $row)
{
	if ($typ != $row->typ || $typ == '')
	{
		if ($typ != '')
			echo '</optgroup>';
		echo '<optgroup label="'.($types->studiengang_typ_arr[$row->typ] != ''?$types->studiengang_typ_arr[$row->typ]:$row->typ).'">';
	}
	if($row->studiengang_kz == $studiengang_kz)
		$selected = 'selected';
	else
		$selected = '';
	echo '<option value="'.$row->studiengang_kz.'" '.$selected.'>'.$db->convert_html_chars($row->kuerzel.' - '.$row->bezeichnung).'</option>';
	$typ = $row->typ;
}
echo '</SELECT>';

echo ' Semester: <SELECT name="semester">';
echo '<OPTION value="">-- Alle --</OPTION>';
for ($i = 1;$i <= 10;$i++)
{
	if ($semester == $i)
		$selected = 'selected';
	else
		$selected = '';

	echo '<OPTION value="'.$i.'" '.$selected.'>'.$i.'</OPTION>';
}
echo '</SELECT>';

echo ' Von: <SELECT name="stsem_von">';
$stsem_obj = new studiensemester();
$stsem_obj->getAll();

foreach ($stsem_obj->studiensemester as $stsem)
{
	if ($stsem_von == $stsem->studiensemester_kurzbz)
		$selected = 'selected';
	else
		$selected = '';

	echo '<OPTION value="'.$stsem->studiensemester_kurzbz.'" '.$selected.'>'.$stsem->studiensemester_kurzbz.'</OPTION>';
}
echo '</SELECT>';

echo ' Nach: <SELECT name="stsem_nach">';

foreach ($stsem_obj->studiensemester as $stsem)
{
	if ($stsem_nach == $stsem->studiensemester_kurzbz)
		$selected = 'selected';
	else
		$selected = '';

	echo '<OPTION value="'.$stsem->studiensemester_kurzbz.'" '.$selected.'>'.$stsem->studiensemester_kurzbz.'</OPTION>';
}

echo '</SELECT>';

echo '&nbsp;&nbsp;<input type="submit" value="Vorrücken"><br>';

if (defined('VILESCI_STUNDENSATZ_VORRUECKUNG')
	&& VILESCI_STUNDENSATZ_VORRUECKUNG != ''
	&& is_numeric(VILESCI_STUNDENSATZ_VORRUECKUNG))
{
	echo '<br><span style="color: blue">
		Alle Lehraufträge werden mit dem Stundensatz "'.VILESCI_STUNDENSATZ_VORRUECKUNG.'" vorgerückt
	</span>';
}
elseif (defined('VILESCI_STUNDENSATZ_VORRUECKUNG')
	&& VILESCI_STUNDENSATZ_VORRUECKUNG != ''
	&& VILESCI_STUNDENSATZ_VORRUECKUNG == 'default')
{
	echo '<br><span style="color: blue">
		Alle Lehraufträge werden mit dem aktuell hinterlegten Standard-Stundensatz der/des Lehrenden vorgerückt
	</span>';
}
elseif (defined('VILESCI_STUNDENSATZ_VORRUECKUNG')
	&& VILESCI_STUNDENSATZ_VORRUECKUNG != ''
	&& VILESCI_STUNDENSATZ_VORRUECKUNG == 'nachbeschaeftigungsart')
{
	echo '<br><span style="color: blue">
		Stundensätze werden abhängig von der Beschaeftigungsart aktualisiert.
	</span>';
}
else
{
	echo '<br><span style="color: blue">
		Alle Lehraufträge werden mit dem Stundensatz des Vorjahres vorgerückt
	</span>';
}

echo '</form>';

if ($studiengang_kz != '' && $stsem_von != '' && $stsem_nach != '')
{
	$stg_obj = new studiengang();
	if (!$stg_obj->load($studiengang_kz))
		die('Studiengang kann nicht geladen werden');

	if (!$rechte->isBerechtigt('lehre/vorrueckung', $stg_obj->oe_kurzbz, 'suid'))
		die($rechte->errormsg);

	echo '<br>Starte Vorrückung '.$stg_arr[$studiengang_kz]." $semester von $stsem_von nach $stsem_nach ...";

	$qry = "SELECT tbl_lehreinheit.lehreinheit_id
			FROM
				lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz)." AND
				tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem_von);
	if ($semester != '')
		$qry .= " AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester);

	if ($result = $db->db_query($qry))
	{
		$anzahl_von = $db->db_num_rows($result);
	}
	//Pruefen, ob schon eine Vorrueckung stattgefunden hat
	$qry_nach = "SELECT tbl_lehreinheit.lehreinheit_id
			FROM
				lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz)." AND
				tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem_nach);
	if ($semester != '')
		$qry_nach .= " AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester);

	if ($result = $db->db_query($qry_nach))
	{
		$anzahl_nach = $db->db_num_rows($result);
		$baseurl = basename($_SERVER['REQUEST_URI']);
		if ($anzahl_nach >= $anzahl_von && !isset($_GET['continue']))
		{
			echo '<br><br><span style="color:red">Es sind schon Lehreinheiten fuer das
				'.$stsem_nach.' in '.$stg_arr[$studiengang_kz].' '.$semester.' vorhanden.
				Trotzdem fortsetzen?</span><br><br>
					<form action="'.$baseurl.'&continue" method="POST"><input type="submit" value="Fortsetzen"></form>';
			die ();
		}
	}

	$stsem_nach_obj = new studiensemester();
	$stsem_nach_obj->load($stsem_nach);

	if ($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$text .= "<br>Lehreinheit $row->lehreinheit_id wird vorgerueckt";
			$le_obj = new lehreinheit();
			//Lehreinheit Neu Anlegen
			if ($le_obj->load($row->lehreinheit_id))
			{
				$le_obj->new = true;
				$le_obj->studiensemester_kurzbz = $stsem_nach;
				$le_obj->insertamum = date('Y-m-d H:i:s');
				$le_obj->insertvon = 'Vorrueckung_'.$user;
				$le_obj->ext_id = '';
				$le_obj->unr = '';

				if ($le_obj->save())
				{
					$anzahl_lehreinheiten++;

					//LehreinheitMitarbeiter Eintrag neu Anlengen
					$qry_lem = "SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter
						WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id);

					if ($result_lem = $db->db_query($qry_lem))
					{
						while ($row_lem = $db->db_fetch_object($result_lem))
						{
							$lem_obj = new lehreinheitmitarbeiter();
							if ($lem_obj->load($row->lehreinheit_id, $row_lem->mitarbeiter_uid))
							{
								// Pruefen ob der Lektor ueber die im Config festgelegte Stundengrenze
								// kommt und Meldung ausgeben
								$qry_stundengrenze="
									SELECT
										mitarbeiter_uid,fixangestellt,SUM(semesterstunden) AS summe, aktiv
									FROM
										lehre.tbl_lehreinheitmitarbeiter
										JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
										JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid)
										JOIN public.tbl_benutzer ON (uid=mitarbeiter_uid)
									WHERE
										mitarbeiter_uid=".$db->db_add_param($row_lem->mitarbeiter_uid)."
										AND studiensemester_kurzbz=".$db->db_add_param($stsem_von)."
									GROUP BY
										mitarbeiter_uid, fixangestellt, aktiv";

								if ($result_stundengrenze = $db->db_query($qry_stundengrenze))
								{
									if ($row_stundengrenze = $db->db_fetch_object($result_stundengrenze))
									{
										if (!$db->db_parse_bool($row_stundengrenze->fixangestellt)
											&& $row_stundengrenze->summe > WARN_SEMESTERSTD_FREI)
										{
											$text .= " <span style='color:red'>
												Stundengrenze ".WARN_SEMESTERSTD_FREI." Stunden ueberschritten
												von $row_lem->mitarbeiter_uid</span>";
										}
										elseif ($db->db_parse_bool($row_stundengrenze->fixangestellt)
											&& $row_stundengrenze->summe > WARN_SEMESTERSTD_FIX)
										{
											$text .= " <span style='color:red'>
												Stundengrenze ".WARN_SEMESTERSTD_FIX." Stunden ueberschritten
												von $row_lem->mitarbeiter_uid</span>";
										}

										// Meldung, wenn Benutzer inaktiv
										if ($db->db_parse_bool($row_stundengrenze->aktiv) == false)
										{
											$text .= "; <span style='color:orange'>Achtung! MitarbeiterIn
												<b>$row_lem->mitarbeiter_uid</b> ist nicht aktiv</span>";
										}
									}
								}
								$lem_obj->lehreinheit_id = $le_obj->lehreinheit_id;
								$lem_obj->new = true;
								// Wenn VILESCI_STUNDENSATZ_VORRUECKUNG definiert ist,
								// wird nicht der Stundensatz des Vorjahres verwendet
								// Wenn VILESCI_STUNDENSATZ_VORRUECKUNG numerisch ist,
								// wird dieser Wert fuer den Stundensatz verwendet
								if (defined('VILESCI_STUNDENSATZ_VORRUECKUNG')
									&& VILESCI_STUNDENSATZ_VORRUECKUNG != ''
									&& is_numeric(VILESCI_STUNDENSATZ_VORRUECKUNG))
								{
									$lem_obj->stundensatz = VILESCI_STUNDENSATZ_VORRUECKUNG;
								}
								// Wenn VILESCI_STUNDENSATZ_VORRUECKUNG default ist,
								//wird der Standard-Stundensatz des jeweiligen Lektors geladen und gespeichert
								elseif (defined('VILESCI_STUNDENSATZ_VORRUECKUNG')
									&& VILESCI_STUNDENSATZ_VORRUECKUNG != ''
									&& VILESCI_STUNDENSATZ_VORRUECKUNG == 'default')
								{
									$stundensatz = new mitarbeiter($row_lem->mitarbeiter_uid);
									$lem_obj->stundensatz = $stundensatz->stundensatz;

									if(defined('DIENSTVERHAELTNIS_SUPPORT') && DIENSTVERHAELTNIS_SUPPORT)
									{
										$qry = "
										SELECT
											stundensatz
										FROM
											hr.tbl_stundensatz
										WHERE
											uid=".$db->db_add_param($row_lem->mitarbeiter_uid)."
											AND gueltig_von <= ".$db->db_add_param($stsem_nach_obj->ende)."
											AND COALESCE(gueltig_bis, '2999-12-31') >= ".$db->db_add_param($stsem_nach_obj->start)."
											AND stundensatztyp = 'lehre'
										ORDER BY gueltig_von desc
										LIMIT 1
										";

										if($result_stundensatz = $db->db_query($qry))
										{
											if($row_stundensatz = $db->db_fetch_object($result_stundensatz))
											{
												$lem_obj->stundensatz = $row_stundensatz->stundensatz;
											}
										}
									}
								}
								// Wenn VILESCI_STUNDENSATZ_VORRUECKUNG nachbeschaeftigungsart ist, wird
								// bei echten Dienstvertraegen mit voller inkludierter Lehre (-1) der Stundensatz auf null gesetzt
								// bei echten Dienstvertraegen mit teilweise oder nicht inkludierter Lehre der Default Stundensatz gesetzt
								// bei sonstigen Dienstvertraegen der Default Stundensatz gesetzt
								elseif (defined('VILESCI_STUNDENSATZ_VORRUECKUNG')
									&& VILESCI_STUNDENSATZ_VORRUECKUNG != ''
									&& VILESCI_STUNDENSATZ_VORRUECKUNG == 'nachbeschaeftigungsart')
								{
									if ($lem_obj->stundensatz != '0')
									{
										$stundensatz = new mitarbeiter($row_lem->mitarbeiter_uid);
										$lem_obj->stundensatz = $stundensatz->stundensatz;

										if(defined('DIENSTVERHAELTNIS_SUPPORT') && DIENSTVERHAELTNIS_SUPPORT)
										{
											$qry = "
											SELECT
												stundensatz
											FROM
												hr.tbl_stundensatz
											WHERE
												uid=".$db->db_add_param($row_lem->mitarbeiter_uid)."
												AND gueltig_von <= ".$db->db_add_param($stsem_nach_obj->ende)."
												AND COALESCE(gueltig_bis, '2999-12-31') >= ".$db->db_add_param($stsem_nach_obj->start)."
												AND stundensatztyp = 'lehre'
											ORDER BY gueltig_von desc
											LIMIT 1
											";

											if($result_stundensatz = $db->db_query($qry))
											{
												if($row_stundensatz = $db->db_fetch_object($result_stundensatz))
												{
													$lem_obj->stundensatz = $row_stundensatz->stundensatz;
												}
											}

											$qry = "
											SELECT
												1
											FROM
												hr.tbl_dienstverhaeltnis dv
											WHERE
												dv.mitarbeiter_uid=".$db->db_add_param($row_lem->mitarbeiter_uid)."
												AND dv.von <= ".$db->db_add_param($stsem_nach_obj->ende)."
												AND COALESCE(dv.bis, '2999-12-31') >= ".$db->db_add_param($stsem_nach_obj->start)."
												AND vertragsart_kurzbz='echterdv'
											";

											if($result_dienstverhaeltnis = $db->db_query($qry))
											{
												if($db->db_num_rows($result_dienstverhaeltnis)>0)
												{
													$lem_obj->stundensatz = '';
												}
											}
										}
										else
										{
											$bisverwendung = new bisverwendung();
											if(!$bisverwendung->getVerwendungRange($row_lem->mitarbeiter_uid, $stsem_nach_obj->start, $stsem_nach_obj->ende))
											{
												$bisverwendung->getLastAktVerwendung($row_lem->mitarbeiter_uid);
												$bisverwendung->result[] = $bisverwendung;
											}

											foreach($bisverwendung->result as $row_verwendung)
											{
												// Bei echten Dienstvertraegen mit voller inkludierter Lehre wird kein Stundensatz
												// geliefert da dies im Vertrag inkludiert ist.
												if ((in_array($row_verwendung->ba1code, $arrEchterDV)) && $row_verwendung->inkludierte_lehre == -1)
												{
													$lem_obj->stundensatz = '';
													break;
												}
											}
										}
									}
								}
								$lem_obj->insertamum = date('Y-m-d H:i:s');
								$lem_obj->insertvon = 'Vorrueckung_'.$user;
								$lem_obj->ext_id = '';
								$lem_obj->vertrag_id = '';

								if (!$lem_obj->save())
								{
									$error_lehreinheitmitarbeiter++;
									$text .= 'Fehler beim Anlegen des Lehreinheitmitarbeiter
										Eintrages: '.$lem_obj->errormsg;
								}
								else
									$anzahl_lehreinheitmitarbeiter++;
							}
							else
							{
								$text .= 'Fehler beim Laden der Mitarbeiter';
								$error_lehreinheitmitarbeiter++;
							}
						}
					}
					else
					{
						$text .= 'Fehler beim Laden der Mitarbeiter '.$db->db_last_error();
						$error_lehreinheitmitarbeiter++;
					}

					//LehreinheitGruppe Eintrag neu Anlegen
					$qry_leg = "
					SELECT
						lehreinheitgruppe_id
					FROM
						lehre.tbl_lehreinheitgruppe
					WHERE
						lehreinheit_id=".$db->db_add_param($row->lehreinheit_id)."
						AND NOT (tbl_lehreinheitgruppe.semester='0' AND tbl_lehreinheitgruppe.verband='I')";

					if ($result_leg = $db->db_query($qry_leg))
					{
						while($row_leg = $db->db_fetch_object($result_leg))
						{
							$leg_obj = new lehreinheitgruppe();
							if ($leg_obj->load($row_leg->lehreinheitgruppe_id))
							{
								// Direktinskriptionsgruppen werden nicht vorgerückt
								if ($leg_obj->gruppe_kurzbz != '')
								{
									$grp = new gruppe();
									$grp->load($leg_obj->gruppe_kurzbz);
									if ($grp->direktinskription)
										continue;
								}

								$leg_obj->lehreinheit_id = $le_obj->lehreinheit_id;
								$leg_obj->new = true;
								$leg_obj->insertamum = date('Y-m-d H:i:s');
								$leg_obj->insertvon = 'Vorrueckung_'.$user;
								$leg_obj->ext_id = '';

								if (!$leg_obj->save())
								{
									$error_lehreinheitgruppe++;
									$text .= 'Fehler beim Anlegen des Lehreinheitgruppe Eintrages: '.$leg_obj->errormsg;
								}
								else
									$anzahl_lehreinheitgruppe++;
							}
							else
							{
								$text .= 'Fehler beim Laden der Gruppe '.$leg_obj->errormsg.' '.$db->db_last_error();
								$error_lehreinheitgruppe++;
							}
						}
					}
					else
					{
						$text .= 'Fehler beim Auslesen der Gruppen';
						$error_lehreinheitgruppe++;
					}
				}
				else
				{
					$error_lehreinheit++;
					$text .= 'Fehler beim Speichern der Lehreinheit '.$le_obj->errormsg;
				}
			}
			else
			{
				$error_lehreinheit++;
				$text .= 'Fehler beim Laden der Lehreinheit '.$le_obj->errormsg;
			}
		}
	}
	else
	{
		$text .= 'Fehler beim Laden der Lehreinheiten '.$db->db_last_error();
		$error_lehreinheit++;
	}

	echo "<br><br>";
	echo "Vorgerueckte Lehreinheiten: $anzahl_lehreinheiten<br>";
	echo "Vorgerueckte LEMitarbeiter: $anzahl_lehreinheitmitarbeiter<br>";
	echo "Vorgerueckte LEGruppen: $anzahl_lehreinheitgruppe<br>";
	echo "Fehler bei Lehreinheiten: $error_lehreinheit<br>";
	echo "Fehler bei LEMitarbeiter: $error_lehreinheitmitarbeiter<br>";
	echo "Fehler bei LEGruppen: $error_lehreinheitmitarbeiter<br>";

	echo '<br><br><hr>';
	echo $text;
	echo '<br><br><br><br><br><br><br><br>';
}

?>
</body>
</html>
