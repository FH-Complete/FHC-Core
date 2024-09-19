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
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */
/**
 * @brief bietet die Moeglichkeit zur Anzeige und
 * Aenderung der Zeitwuensche
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/zeitwunsch.class.php');
require_once('../../../include/zeitwunsch_gueltigkeit.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/zeitaufzeichnung_gd.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/lehrstunde.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$uid = get_uid();

if(!check_lektor($uid))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$datum_obj = new datum();

// Nächstes Studiensemester
$next_ss = new Studiensemester();
$next_ss->getNextStudiensemester();

// Aktuelles Studiensemester
$akt_ss = new Studiensemester();
$akt_ss->load($akt_ss->getAkt());

// Dropdown: Aktuelles/naechstes Studiensemester zum Bearbeiten
$selected_ss = (isset($_GET['stsem']) && is_string($_GET['stsem'])) ? $_GET['stsem'] : $next_ss->studiensemester_kurzbz; // Default: Nächstes Studiensemester

// Dropdown: Vergangene Studiensemester zum Kopieren
$selected_past_ss = (isset($_GET['pastStsem']) && is_string($_GET['pastStsem'])) ? $_GET['pastStsem'] : null; // Default: null

//Stundentabelleholen
if(! $result_stunde=$db->db_query('SELECT * FROM lehre.tbl_stunde ORDER BY stunde'))
	die($db->db_last_error());
$num_rows_stunde=$db->db_num_rows($result_stunde);

// Personendaten
$person = new benutzer();
if(!$person->load($uid))
	die($person->errormsg);

$ma = new mitarbeiter($uid);
$fixangestellt = $ma->fixangestellt;

// Check, ob Bearbeitung des ZW gesperrt ist.
// Gesperrt, wenn Lektor bereits zugewiesene LVs hat.
// Sperre in jeden Fall aufgehoben, wenn ein ZW kopiert wird. (denn ist eine eventuelle Sperre bereits manuell aufgehoben worden)
$isGesperrt = checkIsVerplant($uid, $selected_ss) && is_null($selected_past_ss); // boolean

// Erklärung zu Pausen bei geteilten Arbeitszeiten speichern
if (isset($_GET['selbstverwaltete-pause-akt']) && !empty($_GET['submit-akt']))
{
    $selbstverwaltete_pause = ($_GET['selbstverwaltete-pause-akt'] == 'yes') ? true : false;

    $zeitaufzeichnung_gd = new Zeitaufzeichnung_gd();
    $zeitaufzeichnung_gd->uid = $uid;
    $zeitaufzeichnung_gd->studiensemester_kurzbz = $akt_ss->studiensemester_kurzbz;
    $zeitaufzeichnung_gd->selbstverwaltete_pause = $selbstverwaltete_pause;
	$za_gd = new Zeitaufzeichnung_gd();
	$za_gd->load($uid, $akt_ss->studiensemester_kurzbz);
	if ($za_gd->uid)
	{
		echo 'Bereits eingetragen';
	}
    else if (!$zeitaufzeichnung_gd->save())
    {
        echo $zeitaufzeichnung_gd->errormsg;
    }

}
if (isset($_GET['selbstverwaltete-pause']) && !empty($_GET['submit']))
{
    $selbstverwaltete_pause = ($_GET['selbstverwaltete-pause'] == 'yes') ? true : false;

    $zeitaufzeichnung_gd = new Zeitaufzeichnung_gd();
    $zeitaufzeichnung_gd->uid = $uid;
    $zeitaufzeichnung_gd->studiensemester_kurzbz = $next_ss->studiensemester_kurzbz;
    $zeitaufzeichnung_gd->selbstverwaltete_pause = $selbstverwaltete_pause;
	$za_gd = new Zeitaufzeichnung_gd();
	$za_gd->load($uid, $next_ss->studiensemester_kurzbz);
	if ($za_gd->uid)
	{
		echo 'Bereits eingetragen';
	}
    else if (!$zeitaufzeichnung_gd->save())
    {
        echo $zeitaufzeichnung_gd->errormsg;
    }

}

// Zeitwuensche speichern
if (isset($_GET['type']) && $_GET['type'] == 'save')
{
    // Letzte Zeitwunschgueltigkeit (ZWG) holen
    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->getByUID($uid, 1);
    $lastZwg = !empty($zwg->result) ? $zwg->result[0] : null;

    // Check, ob letzte ZWG im nächsten Studiensemester startet. D.h. es existiert ein neuer Zeitwunsch in der Zukunft
    $lastZwgStartsNextSemester = (!is_null($lastZwg) && $lastZwg->von >= $next_ss->start) ? true : false;
    $zw_zwg_id = null;  // ZWG ID, die zum Speichern / Updaten des Zeitwunsches uebergeben wird

    //  Wenn allererster Zeitwunsch, also noch keine ZWG vorhanden
    if (is_null($lastZwg))
    {
        // Wenn ZW fuer naechstes Studiensemester ist
        if ($selected_ss == $next_ss->studiensemester_kurzbz)
        {
            // Neue ZWG setzen: von = Start nächstes Studiensemester, bis offen lassen
            $zw_zwg_id = insertZWG($uid, $next_ss->start, null);
        }

        // Wenn Zeitwunsch fuer aktuelles Studiensemester ist
        if ($selected_ss == $akt_ss->studiensemester_kurzbz)
        {
            // Neue ZWG setzen: von = now(), bis offen lassen
            $zw_zwg_id = insertZWG($uid, (new DateTime())->format('Y-m-d H:i:s'), null);
        }
    }

    // Wenn mindestens eine ZWG vorhanden
    if (!is_null($lastZwg))
    {
        // Wenn Zeitwunsch fuer naechstes Studiensemester ist
        if ($selected_ss == $next_ss->studiensemester_kurzbz)
        {
            // Wenn naechstes Studiensemester schon eine eigene ZWG hat
            if ($lastZwgStartsNextSemester)
            {
                // Nur Zeitwunsch dieser ZWG updaten
                $zw_zwg_id = $lastZwg->zeitwunsch_gueltigkeit_id;
            }

            // Wenn naechstes Studiensemester keine eigene ZWG hat
            if (!$lastZwgStartsNextSemester)
            {
                // Fuer bisher letzte ZWG ein Endedatum setzen: bis = Ende aktuelles Studiensemester
                updateZWG($uid, $lastZwg->zeitwunsch_gueltigkeit_id, $akt_ss->ende);

                // Neue ZWG setzen: von = Start nächstes Studiensemester, bis offen lassen
                $zw_zwg_id = insertZWG($uid, $next_ss->start, null);
            }
        }

        // Wenn Zeitwunsch fuer aktuelles Studiensemester ist
        if ($selected_ss == $akt_ss->studiensemester_kurzbz)
        {
            /**
             * Check, ob aktuelles Studiensemester eine ZWG hat.
             * Wenn die allererste ZWG fuer das naechste Studiensemester erstellt wurde, dann hat das
             * aktuelle Studiensemester noch keine ZWG.
             * */
            $zwg = new Zeitwunsch_gueltigkeit();
            $zwg->getByStudiensemester($uid, $akt_ss->studiensemester_kurzbz);
            $akt_ss_zwg = !empty($zwg->result) ? $zwg->result[0] : null;

            // Keine ZWG fuer aktuelles Studiensemester vorhanden.
            // Da eine ZWG ID aber schon vorhanden: USER HAT ERSTMALIG MIT NAECHSTEM STUDIENSEMESTER EINTRAG BEGONNEN
            if (is_null($akt_ss_zwg))
            {
                // Neue ZWG setzen: von = now(), ende = Ende aktuelles Studiensemester
                $zw_zwg_id = insertZWG($uid, (new DateTime())->format('Y-m-d H:i:s'), $akt_ss->ende);
            }

            // ZWG für aktuelles Studiensemester ist vorhanden --> SPLIT AKTUELLE STUDIENSEMESTER
            if ((!is_null($akt_ss_zwg)))
            {
                // Wenn am selben Tag schon neue ZWG gespeichert wurde, keine neue ZWG anlegen, sondern diese nur updaten
                // Verhindert mehrfache Eintraege, wenn oefters zwischengespeichert wird.
                if ((new DateTime($akt_ss_zwg->von))->format('Y-m-d') == (new Datetime())->format('Y-m-d'))
                {
                    updateZWG($uid, $akt_ss_zwg->zeitwunsch_gueltigkeit_id, $akt_ss_zwg->bis);

                    $zw_zwg_id = $akt_ss_zwg->zeitwunsch_gueltigkeit_id;
                }
                else
                {
                    // Neue ZWG setzen: von = now(), bis = Bis von ZWG des aktuellen Studiensemesters uebernehmen:
                    // -> bis ist entweder Ende aktuelles Studiensemester (wenn ZWG für nächstes Studiensemester vorhanden ist)
                    // -> sonst ist bis null
                    $zw_zwg_id = insertZWG($uid, (new DateTime())->format('Y-m-d H:i:s'), $akt_ss_zwg->bis);

                    // Fuer bisher letzte ZWG das Endedatum auf gestern setzen: bis = gestern
                    // NOTE: MUSS nach dem insert sein
                    updateZWG($uid, $akt_ss_zwg->zeitwunsch_gueltigkeit_id, (new DateTime('yesterday'))->format('Y-m-d H:i:s'));
                }
            }
        }
    }

    // Insert Zeitwunsch mit Zeitwunsch ZWG ID
    if (is_numeric($zw_zwg_id))
    {
        $zw = new zeitwunsch();

        for ($t=1;$t<7;$t++)
        {
            for ($i=0;$i<$num_rows_stunde;$i++)
            {
                $var='wunsch'.$t.'_'.$i;
                if(!isset($_POST[$var]))
                    continue;
                $gewicht=$_POST[$var];
                $stunde=$i+1;

                $zw->mitarbeiter_uid = $uid;
                $zw->stunde = $stunde;
                $zw->tag = $t;
                $zw->gewicht = $gewicht;
                $zw->updateamum = date('Y-m-d H:i:s');
                $zw->updatevon = $uid;
                $zw->zeitwunsch_gueltigkeit_id = $zw_zwg_id;

                if (!$zw->exists($uid, $zw_zwg_id, $stunde, $t))
                {
                    $zw->new = true;
                    $zw->insertamum = date('Y-m-d H:i:s');
                    $zw->insertvon = $uid;
                }
                else
                {
                    $zw->new = false;
                }

                if(!$zw->save())
                    echo $zw->errormsg;
            }
        }
    }

    // Wenn speichern möglich ist, dann hat der Lektor entweder keine LVs zugeteilt oder hat aktiv die Bearbeitungssperre
    // deaktiviert. Bearbeitungssperre wird gesetzt, wenn isGesperrt true ist. Deshalb hier mit false überschreiben.
    $isGesperrt = false;
}

/**
 * Zeitwunschgueltigkeit fuer Tabelle holen.
 * Der Zeitwunsch wird anhand der Zeitwunschgueltigkeit (ZWG) des gewaehlten Studiensemesters ermittelt.
 * Das Studiensemester wird, je nach Vorhandensein, in dieser Reihenfolge herangezogen:
 * 1. Wenn in Dropdown ausgewaehlt: Vergangenes Studiensemester (zum Kopieren von Zeitwunsch)
 * 2. Wenn in Dropdown ausgewaehlt: Aktuelles Studiensemester
 * 3: Default: Nächstes Studiensemesters
 */
$zwg = new zeitwunsch_gueltigkeit();
$tmp_ss = is_null($selected_past_ss) ? $selected_ss : $selected_past_ss;
$zwg->getByStudiensemester($uid, $tmp_ss);
$zwg_id = !empty($zwg->result[0]) ? $zwg->result[0]->zeitwunsch_gueltigkeit_id : null;  //null, wenn noch kein ZW

/**
 * Zeitwunsch fuer Tabelle holen
 * Wenn noch kein Zeitwunsch vorhanden, bleibt die Zeitwunsch Instanz leer
 * */
$zw = new zeitwunsch();
if (!$zw->loadByZWG($uid, $zwg_id))
{
    die($zw->errormsg);
}
$wunsch = $zw->zeitwunsch;

/**
 * Init ZWG Objekt zum Erstellen einer neuen ZWG
 */
function insertZWG($uid, $von, $bis)
{
    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->new = true;
    $zwg->mitarbeiter_uid = $uid;
    $zwg->von = $von;
    $zwg->bis = $bis;
    $zwg->insertvon = $uid;
    if ($zwg->save())
    {
        return $zwg->zeitwunsch_gueltigkeit_id;
    }
    else
    {
        die($zwg->errormsg);
    }
}

/**
 * Init ZWG Objekt zum Updaten einer bestehenden ZWG
 */
function updateZWG($uid, $zwg_id, $bis)
{
    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->new = false;
    $zwg->zeitwunsch_gueltigkeit_id = $zwg_id;
    $zwg->mitarbeiter_uid = $uid;
    $zwg->bis = $bis;
    $zwg->updatevon = $uid;

    if (!$zwg->save())
    {
        die($zwg->errormsg);
    }

    return;
}

/**
 * Check, ob Lektor bereits zugewiesene LVs hat
 * @param $uid
 * @param $studiensemester_kurzbz
 * @return bool|void
 */
function checkIsVerplant($uid, $studiensemester_kurzbz)
{
    $lstd = new Lehrstunde();
    if (!$lstd->getStundenplanData('stundenplandev', null, $studiensemester_kurzbz, null, $uid, null, true))
    {
        die($lstd->errormsg);
    }

    return empty($lstd->result) ? false : true;
}

/**
 * Get Studiengaenge of STG assigend to LVs, to which lector is alredy assigend in
 * the given Studiensemester.
 *
 * @param $uid
 * @param $studiensemester_kurzbz
 * @return array|void
 */
function getStgOfVerplant($uid, $studiensemester_kurzbz)
{
    $stg_arr = array(); // Mail data for Studiengang

    // Get Stundenplan entries of lector of Studiensemester concerned by Zeitwunschgueltigkeit
    $lstd = new Lehrstunde();
    if (!$lstd->getStundenplanData(
            'stundenplandev',
            null,
            $studiensemester_kurzbz,
            null,
            $uid,
            null,
            true)) //...but only from now on
    {
        die($lstd->errormsg);
    }

    // Loop through Stundenplan entries
    foreach ($lstd->result as $row)
    {
        // Get LE of Stundenplan entry
        $le = new Lehreinheit($row->lehreinheit_id);

        // GET LV by LE of Stundenplan entry
        $lv = new Lehrveranstaltung($le->lehrveranstaltung_id);

        // Build Studiengang array
        $stg_arr[] = $lv->studiengang_kz;
    }

    // Make Studiengang array unique
    return array_unique($stg_arr);
}

/**
 * Get Studiengang eMail Addresses.
 * @param $stgKz_arr    // Studiengang Kennzeichen Array
 * @return array
 */
function getStgMail($stgKz_arr)
{
    $stgMail_arr = array();
    foreach($stgKz_arr as $stgKz)
    {
        $stg = new Studiengang($stgKz);
        $stgMail_arr[]= $stg->email;
    }
    return $stgMail_arr;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $p->t('zeitwunsch/zeitwunsch');?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="../../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css" type="text/css">
        <script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
        <script type="text/javascript" src="../../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
		<script type="text/javascript">
		// Pruefen ob nur die erlaubten Werte verwendet wurden
		function checkvalues()
		{
			var elem = document.getElementsByTagName('input');
			var error=false;

			for (var i = 0;i<elem.length;i++)
			{
				if(elem[i].name.match("^wunsch"))
				{
					if(!elem[i].value.match("^\-?[1-2]\d{0,0}$"))
						error=true;
				}
			}

			if(error)
			{
				alert('<?php echo $p->t('zeitwunsch/falscheWerteEingetragen');?>');
				return false;
			}
			else
				return true;
		}

        $(function() {
            // Bearbeitung deaktivieren, wenn Lektor zugewiesene LV im Studiensemester hat
            const isGesperrt = $('input[name=isGesperrt]').val();
            if (isGesperrt == 'true')
            {
                $('input[name=radioZWG]').attr("disabled", true);
                $('input[name=submit]').attr("disabled", true);
            }

            // Bearbeitung aktivieren, wenn Lektor Aktivierungslink klickt
            $('#bearbeitung-aktivieren').click(function(){
                $('input[name=radioZWG]').attr("disabled", false);
                $('input[name=submit]').attr("disabled", false);

                $('#divChangeZWG').removeClass('hidden');
                $('#divisGesperrt').addClass('hidden');
            });

            // Bei Wechsel von Studiensemester die Seite mit GET params neu laden
           $('#stsem').change(function(){
               let studiensemester = $('option:selected', this).val();

               window.location = '?stsem=' + studiensemester;
           });

           // Bei Wechsel zwischen Zeitwunsch aendern / kopieren
            $("input[name='radioZWG']").change(function(){
                if ($(this).val() == 'copy')
                {
                    $('#divCopyZWG').removeClass('hidden');
                    $('#divChangeZWG').addClass('hidden');
                }
                else
                {
                    $('#divCopyZWG').addClass('hidden');
                    $('#divChangeZWG').removeClass('hidden');
                }
            });

            // Bei Wahl von vergangenem Studiensemester die Seite mit GET params neu laden
            $('#pastStsem').change(function(){
                let stsem = $('#stsem option:selected').val()
                let pastStsem = $('option:selected', this).val();

                window.location = '?stsem='+ stsem + '&pastStsem=' + pastStsem;
            });

            // Alle Werte ind Zeitwunschtabelle auf 1 setzen
            $('#empty-table').click(function(){
                $('#table-zeitwunsch tr td input').each(function() {
                    $(this)
                        .val(1)
                        .parent().css('background-color', '#CCFFCC');
                });
            })

            // Aenderungen in Zeitwunschtabelle zurücknehmen -> Seite neu laden
            $('#reload-table').click(function(){
                let studiensemester = $('option:selected', '#stsem').val();
                window.location = '?stsem=' + studiensemester;
            })
        });
		</script>
	</head>

	<body>

	<div class="flexcroll" style="outline: none;">
	<table class="table">
<?php if($fixangestellt && (defined('CIS_ZEITWUNSCH_GD') && CIS_ZEITWUNSCH_GD)): ?>
		<!--Erklärung zu Pausen bei geteilten Arbeitszeiten-->
		<tr>
			<td>
				<h3>Zustimmung zur Verplanung in geteilter Arbeitszeit</h3>

			<form action="">
				<p>
					<?php
					echo $p->t('zeitwunsch/geteilteArbeitszeit');
                    echo '<br><br>';
					$gd = new zeitaufzeichnung_gd();
					$gd->load($uid, $akt_ss->studiensemester_kurzbz);
					if ( ! $gd->uid )
					{
                        echo '<div class="row">';
                        echo '<div class="col-lg-3 col-md-4 col-xs-4">';
                            echo '<div class="radio">';
                            echo '<b>Zustimmung für '.$akt_ss->studiensemester_kurzbz.': ';
                            echo '<label class="radio-inline">';
                            echo '<input type="radio" name="selbstverwaltete-pause-akt" value="yes">ja';
                            echo '</label>';
                            echo '<label class="radio-inline">';
                            echo '<input type="radio" name="selbstverwaltete-pause-akt" value="no">nein';
                            echo '</label>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div class="col-lg-2 col-md-3 col-xs-4">';
						    echo '</b><input type="submit" class="btn btn-default btn-block" name="submit-akt" value="'.$p->t('global/speichern').'" style="float: right"><br>';
                        echo '</div>';

                        echo '<div class="col-lg-7 col-md-5 col-xs-4">';
                        echo '</div>';
                        echo '</div>';
					}
					else
					{
						$zustimmung = ($gd->selbstverwaltete_pause) ? ' erteilt' : 'abgelehnt';
						echo '<br><br><b>Zustimmung für '.$akt_ss->studiensemester_kurzbz.': '.$zustimmung.' am '.$datum_obj->formatDatum($gd->insertamum,'d.m.Y H:i:s').'</b>';
					}
					$gd = new zeitaufzeichnung_gd();
					$gd->load($uid, $next_ss->studiensemester_kurzbz);
					if ( ! $gd->uid )
					{
                        echo '<div class="row">';
                        echo '<div class="col-lg-3 col-md-4 col-xs-4">';
                            echo '<div class="radio">';
                            echo '<b>Zustimmung für '.$next_ss->studiensemester_kurzbz.': ';
                            echo '<label class="radio-inline">';
                            echo '<input type="radio" name="selbstverwaltete-pause" value="yes">ja';
                            echo '</label>';
                            echo '<label class="radio-inline">';
                            echo '<input type="radio" name="selbstverwaltete-pause" value="no">nein';
                            echo '</label>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div class="col-lg-2 col-md-3 col-xs-4">';
						    echo '</b><input type="submit" class="btn btn-default btn-block" name="submit" value="'.$p->t('global/speichern').'" style="float: right">';
                        echo '</div>';

                        echo '<div class="col-lg-7 col-md-5 col-xs-4">';
                        echo '</div>';
                        echo '</div>';
					}
					else
					{
						$zustimmung = ($gd->selbstverwaltete_pause) ? ' erteilt' : 'abgelehnt';
						echo '<br><b>Zustimmung für '.$next_ss->studiensemester_kurzbz.': '.$zustimmung.' am '.$datum_obj->formatDatum($gd->insertamum,'d.m.Y H:i:s').'</b>';
					}
					?>

				</p>
			</form>
		</td>
	</tr>
<?php endif; ?>
	  <tr>
	    <td>
		<?php

        // FORM Begin
        echo '<form name="zeitwunsch" method="post" action="zeitwunsch.php?stsem='. $selected_ss. '&type=save" onsubmit="return checkvalues()">';
        echo '<input type="hidden" name="uid" value="'. $uid. '">';
        echo '<input type="hidden" name="isGesperrt" value="'. json_encode($isGesperrt). '">';

        // Mein Zeitwunsch-Semesterplan Dropdown, Default = naechstes Studiensemester
        $next_ss_selected = $next_ss->studiensemester_kurzbz == $selected_ss ? 'selected' : '';
        $akt_ss_selected = $akt_ss->studiensemester_kurzbz == $selected_ss ? 'selected' : '';

        echo '<h3>'. $p->t('zeitwunsch/gueltigIm');
        echo '<SELECT name="stsem" id="stsem">';
        echo '<OPTION value="'.$next_ss->studiensemester_kurzbz.'"'. $next_ss_selected. '>'. $next_ss->studiensemester_kurzbz.'</OPTION>';
        echo '<OPTION value="'.$akt_ss->studiensemester_kurzbz.'"'. $akt_ss_selected. '>'. $akt_ss->studiensemester_kurzbz.'</OPTION>';
        echo '</SELECT>';
        echo '</h3><br>';

        // Tabelle Zeitwunsch-Semesterplan
        echo '<table id="table-zeitwunsch" class="table table-default table-condensed table-bordered">';
            // Tabelle Kopfzeile
            echo '<tr>';
                echo '<th>'.$p->t('global/stunde').'<br>'.$p->t('global/beginn').'<br>'.$p->t('global/ende').'</th>';
                for ($i=0;$i<$num_rows_stunde; $i++)
                {
                    $beginn=$db->db_result($result_stunde,$i,'"beginn"');
                    $beginn=substr($beginn,0,5);
                    $ende=$db->db_result($result_stunde,$i,'"ende"');
                    $ende=substr($ende,0,5);
                    $stunde=$db->db_result($result_stunde,$i,'"stunde"');
                    echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
                }
            echo '</tr>';
            // Tabelle Zellen
            for ($j=1; $j<7; $j++)
        {
            echo '<TR><TD>'.$tagbez[$lang->index][$j].'</TD>';
            for ($i=0;$i<$num_rows_stunde;$i++)
            {
                if (isset($wunsch[$j][$i+1]))
                    $index=$wunsch[$j][$i+1];
                else
                    $index=1; // Defaultwert, wenn kein Zeitwunsch vorhanden

                $bgcolor=$cfgStdBgcolor[$index+3];
                echo '<TD style="padding-left: 5px; padding-right:5px;" align="center"  bgcolor="'.$bgcolor.'"><INPUT align="right" type="text" name="wunsch'.$j.'_'.$i.'" size="1" maxlength="2" value="'.$index.'"></TD>';
            }
            echo '</TR>';
        }
        echo '</table>';

        // Zeitwunsch aendern / kopieren
        echo '<div class="row">';
            echo '<div class="col-xs-12">';
            echo '<span>'. $p->t('zeitwunsch/erklaerungstext'). '</span><br>';
            echo '<hr>';
            echo '</div>'; // end col-xs-12
        echo '</div>'; // end row

        echo '<div class="row">';

            echo '<div class="col-xs-9">';
                // Radiobuttons aendern / kopieren
                $radioChangeChecked = is_null($selected_past_ss) ? 'checked' : '';
                $radioCopyChecked = !is_null($selected_past_ss) ? 'checked' : '';

                echo '<div class="radio">';
                echo '<span class="text-uppercase"><b>'.$p->t('zeitwunsch/fuer', array($selected_ss)). '</b></span>';
                echo '<label class="radio-inline">';
                echo '<b><input type="radio" name="radioZWG" id="radioChangeZWG" value="change" '. $radioChangeChecked. '> '. $p->t('zeitwunsch/kopieren'). '</b>';
                echo '</label>';
                echo '<label class="radio-inline">';
                echo '<b><input type="radio" name="radioZWG" id="radioCopyZWG" value="copy" '. $radioCopyChecked. '> '. $p->t('zeitwunsch/aendern'). '</b>';
                echo '</label>';
                if ($isGesperrt)
                {
                    echo '<span class="label label-danger valign-top">'.$p->t('zeitwunsch/stundenBereitsVerplant', array($selected_ss)). '</span>';
                }
                echo '</div>';
            echo '</div>'; // end col-xs-9

            echo '<div class="col-xs-3"  class="pull-left">';
                echo '<span><small><a id="empty-table" style="cursor: pointer">'. $p->t('zeitwunsch/werteAuf1setzen'). '</a></small></span><br>';
                echo '<span><small><a id="reload-table" style="cursor: pointer">'. $p->t('global/aenderungenZuruecksetzen'). '</a></small></span><br><br>';
            echo '</div>'; // end col-xs-3

            $divChangeHidden = !is_null($selected_past_ss) || $isGesperrt ? 'hidden' : '';
            $divCopyHidden = is_null($selected_past_ss) || $isGesperrt ? 'hidden' : '';
            $divisGesperrtHidden = $isGesperrt ? '' : 'hidden';

            echo '<div id="divChangeZWG" class="'. $divChangeHidden . '">';
                echo '<div class="col-xs-8 col-lg-7">';
                    echo '<span>' . $p->t('zeitwunsch/tragenSieInDiesesNormwochenraster') .'</span>';
                echo '</div>'; // end col
                echo '<div class="col-xs-1 col-lg-2">';
                    // BLANK
                echo '</div>';  // end col
            echo '</div>'; // end divChangeZWG

            echo '<div id="divCopyZWG" class="'. $divCopyHidden . '">';
                echo '<div class="col-xs-7">';
                echo '<span>' . $p->t('zeitwunsch/kopierenText') .'</span>';
                echo '</div>'; // end col

                $studiensemester = new Studiensemester();
                $tmp_ss = $selected_ss == $akt_ss->studiensemester_kurzbz ? $studiensemester->getPrevious() : $akt_ss->studiensemester_kurzbz;
                $studiensemester->load($tmp_ss);

                $zwg = new Zeitwunsch_gueltigkeit();
                $zwg->getByUID($uid, 4, true, $studiensemester->ende);
                $past_zwg_arr = $zwg->result;
                echo '<div class="col-xs-2">';
                    echo '<select name="pastStsem" id="pastStsem" class="form form-control">';
                    echo '<OPTION value="">-- '. $p->t("global/bitteWaehlen").' --</OPTION>';
                    foreach($past_zwg_arr as $row)
                    {
                        $selected = $row->studiensemester_kurzbz == $selected_past_ss ? 'selected' : '';
                        echo '<option value="'. $row->studiensemester_kurzbz. '" '. $selected. '>'. $row->studiensemester_kurzbz. '</option>';
                    }
                    echo '</select>';
                echo '</div>';  // end col
            echo '</div>'; // end divCopyZWG

            echo '<div id="divisGesperrt" class="'. $divisGesperrtHidden . '">';

                // Mail Adressen der Studiengaenge, wo Lektor ueber eine LV bereits verplant ist
                $stgKzOfVerplant_arr = getStgOfVerplant($uid, $selected_ss);
                $stgMail_arr = getStgMail($stgKzOfVerplant_arr);

                echo '<div class="col-xs-9">';
                echo '<div class="panel panel-danger">';
                    echo '<div class="panel-body">';
                    echo '<span class="text-danger"><b>'. $p->t("zeitwunsch/bearbeitungDeaktiviert"). ': </b></span>';
                    echo $p->t("zeitwunsch/bearbeitungDeaktiviertText", array($selected_ss, implode($stgMail_arr, '; '))). '
                        <a id="bearbeitung-aktivieren" style="cursor:pointer;">'. $p->t("zeitwunsch/bearbeitungAktivieren"). '</a><br>
                        <span><b>'. $p->t("zeitwunsch/kompetenzfeldWirdInformiert") . '</b></span>
                        </span>';
                    echo '</div>'; // end panel heading
                echo '</div>'; // end panel
                echo '</div>'; // end col
            echo '</div>'; // end divisGesperrt

            // Speichern - Button
            echo '<div class="col-xs-3">';
            echo '<input class="btn btn-default" style="width: 200px;" type="submit" name="submit" value="'.$p->t('global/speichern').'">';
            echo '</div>';

        echo '</div>'; // end row
        echo '<hr>';
        ?>
        </form>

        <!-- Zeitwunsch Erklaerung -->
        <div class="row">
            <div class="col-xs-9">
                    <span><b><?php echo $p->t('zeitwunsch/folgendePunkteSindZuBeachten');?>:</b></span>
                    <UL class="unordered-list">
                        <LI><?php echo $p->t('zeitwunsch/verwendenSieDenWertNur');?></LI>
                        <LI><?php echo $p->t('zeitwunsch/sperrenSieNurTermine');?></LI>
                        <LI><?php echo $p->t('zeitwunsch/esSolltenFuerJedeStunde');?></LI>
                    </UL><br>
            </div>
            <div class="col-xs-3">
                <br>
                <TABLE class="table table-condensed table-default table-bordered" align=center>
                    <TR>
                        <TH><B><?php echo $p->t('zeitwunsch/wert');?></B></TH>
                        <TH>
                            <DIV align="center"><B><?php echo $p->t('zeitwunsch/bedeutung');?></B></DIV>
                        </TH>
                    </TR>
                    <TR>
                        <TD style="background-color: <?php echo $cfgStdBgcolor[5]; ?>">
                            <DIV align="right">2</DIV>
                        </TD>
                        <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/hierMoechteIchUnterrichten');?></TD>
                    </TR>
                    <TR>
                        <TD style="background-color: <?php echo $cfgStdBgcolor[6]; ?>">
                            <DIV align="right">1</DIV>
                        </TD>
                        <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/hierKannIchUnterrichten');?></TD>
                    </TR>
                    <!--<TR>
                      <TD>
                        <DIV align="right">0</DIV>
                      </TD>
                      <TD>keine Bedeutung</TD>
                    </TR>-->
                    <TR>
                        <TD style="background-color: <?php echo $cfgStdBgcolor[2]; ?>">
                            <DIV align="right">-1</DIV>
                        </TD>
                        <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/nurInNotfaellen');?></TD>
                    </TR>
                    <TR>
                        <TD style="background-color: <?php echo $cfgStdBgcolor[1]; ?>">
                            <DIV align="right">-2</DIV>
                        </TD>
                        <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/hierAufGarKeinenFall');?></TD>
                    </TR>
                </TABLE>
            </div>
        </div>

        <!-- Zeitsperre -->
        <div class="row">
            <div class="col-xs-12">
                <h4><?php echo $p->t('zeitsperre/zeitsperren');?>:</h4>
                <?php
                $href = "<a href='zeitsperre_resturlaub.php' class='Item'>";
                echo $p->t('zeitwunsch/formularZumEintragenDerZeitsperren', array($href));
                ?></a>
            </div>
        </div>
        </td>
    </tr>
</table>

    </div>
	</body>
</html>
