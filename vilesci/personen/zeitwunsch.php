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
 *	kopiert von stdplan/profile/zeitwuensche.php mit dem Unterschied,
 *  dass der User hier parametrisiert ist + Speichern läuft hier über
 *  POST statt GET - ist aber Geschmacksache
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/zeitwunsch.class.php');
require_once('../../include/zeitwunsch_gueltigkeit.class.php');
require_once('../../include/studiensemester.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (isset($_GET['uid']))
{
	$uid=$_GET['uid'];
}
else if (isset($_POST['uid']))
{
	$uid=$_POST['uid'];
}
if (!isset($uid))
{
	die( "uid nicht gesetzt");
}

$uid_benutzer = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid_benutzer);
if(!$rechte->isBerechtigt('mitarbeiter', null, 's'))
	die($rechte->errormsg);

$datum_obj = new datum();
$updatevon = 0;

// Nächstes Studiensemester
$next_ss = new Studiensemester();
$next_ss->getNextStudiensemester();

// Aktuelles Studiensemester
$akt_ss = new Studiensemester();
$akt_ss->load($akt_ss->getAkt());

// Zeitwunschgueltigkeiten nach Semester selektierbar
$selected_ss = (isset($_GET['stsem']) && !empty($_GET['stsem'])) ? $_GET['stsem'] : $next_ss->studiensemester_kurzbz; // Default: Nächstes Studiensemester

// Default: Letzte Zeitwunschgueltigkeit (ZWG) holen
$zwg = new Zeitwunsch_gueltigkeit();
$zwg->getByUID($uid, 1);
$selected_zwg = !empty($zwg->result) ? $zwg->result[0] : null; // NULL, wenn Lektor noch gar keinen ZW hinterlegt hat

// Zeitwunschgueltigkeit ueber Dropdown ZWG gewaehlt
if (isset($_GET['zwg_id']))
{
    $selected_zwg = !empty($_GET['zwg_id']) ? new Zeitwunsch_gueltigkeit($_GET['zwg_id']) : null;
}

//Stundentabelleholen
if(! $result_stunde=$db->db_query("SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
    die($db->db_last_error());
$num_rows_stunde=$db->db_num_rows($result_stunde);

// Zeitwuensche speichern
if (isset($_POST['save']))
{
    if(!$rechte->isBerechtigt('mitarbeiter/zeitwuensche', null, 'suid'))
        die($rechte->errormsg);

    $selected_ss = isset($_POST['stsem']) ? $_POST['stsem'] : die('Studiensemester fehlt');

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
            $zw_zwg_id = insertZWG($uid, $next_ss->start, null, $uid_benutzer);
        }

        // Wenn Zeitwunsch fuer aktuelles Studiensemester ist
        if ($selected_ss == $akt_ss->studiensemester_kurzbz)
        {
            // Neue ZWG setzen: von = now(), bis offen lassen
            $zw_zwg_id = insertZWG($uid, (new DateTime())->format('Y-m-d H:i:s'), null, $uid_benutzer);
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
                updateZWG($uid, $lastZwg->zeitwunsch_gueltigkeit_id, $akt_ss->ende, $uid_benutzer);

                // Neue ZWG setzen: von = Start nächstes Studiensemester, bis offen lassen
                $zw_zwg_id = insertZWG($uid, $next_ss->start, null, $uid_benutzer);
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
                $zw_zwg_id = insertZWG(
                        $uid,
                        (new DateTime())->format('Y-m-d H:i:s'),
                        $akt_ss->ende,
                        $uid_benutzer
                );
            }

            // ZWG für aktuelles Studiensemester ist vorhanden --> SPLIT AKTUELLE STUDIENSEMESTER
            if ((!is_null($akt_ss_zwg)))
            {
                // Wenn am selben Tag schon neue ZWG gespeichert wurde, keine neue ZWG anlegen, sondern diese nur updaten
                // Verhindert mehrfache Eintraege, wenn oefters zwischengespeichert wird.
                if ((new DateTime($akt_ss_zwg->insertamum))->format('Y-m-d') == (new Datetime())->format('Y-m-d'))
                {
                    updateZWG($uid, $akt_ss_zwg->zeitwunsch_gueltigkeit_id, $akt_ss_zwg->bis, $uid_benutzer);

                    $zw_zwg_id = $akt_ss_zwg->zeitwunsch_gueltigkeit_id;
                }
                else
                {
                    // Neue ZWG setzen: von = now(), bis = Bis von ZWG des aktuellen Studiensemesters uebernehmen:
                    // -> bis ist entweder Ende aktuelles Studiensemester (wenn ZWG für nächstes Studiensemester vorhanden ist)
                    // -> sonst ist bis null
                    $zw_zwg_id = insertZWG(
                            $uid,
                            (new DateTime())->format('Y-m-d H:i:s'),
                            $akt_ss_zwg->bis,
                            $uid_benutzer
                    );

                    // Fuer bisher letzte ZWG das Endedatum auf gestern setzen: bis = gestern
                    // NOTE: MUSS nach dem insert sein
                    updateZWG(
                            $uid,
                            $akt_ss_zwg->zeitwunsch_gueltigkeit_id,
                            (new DateTime('yesterday'))->format('Y-m-d H:i:s'),
                            $uid_benutzer
                    );
                }
            }
        }
    }

    // Insert Zeitwunsch mit Zeitwunsch ZWG ID
    if (is_numeric($zw_zwg_id))
    {
        for ($t = 1; $t < 7; $t++)
        {
            for ($i = 0; $i < $num_rows_stunde; $i++)
            {
                $var = 'wunsch' . $t . '_' . $i;
                //echo $$var;
                $gewicht = $_POST[$var];
                $stunde = $i + 1;
                $query = "SELECT * FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid=" . $db->db_add_param($uid) . " AND zeitwunsch_gueltigkeit_id =" . $db->db_add_param($zw_zwg_id) . " AND stunde=" . $db->db_add_param($stunde, FHC_INTEGER) . " AND tag=" . $db->db_add_param($t, FHC_INTEGER);
                if (!$erg_wunsch = $db->db_query($query))
                    die($db->db_last_error());
                $num_rows_wunsch = $db->db_num_rows($erg_wunsch);
                if ($num_rows_wunsch == 0) {
                    $query = "INSERT INTO campus.tbl_zeitwunsch (mitarbeiter_uid, stunde, tag, gewicht, updateamum, updatevon, zeitwunsch_gueltigkeit_id) VALUES (" . $db->db_add_param($uid) . ", " . $db->db_add_param($stunde) . ", " . $db->db_add_param($t) . ", " . $db->db_add_param($gewicht) . ", now(), " . $db->db_add_param($uid_benutzer) . ", " . $db->db_add_param($zw_zwg_id) . ")";
                    if (!($erg = $db->db_query($query)))
                        die($db->db_last_error());
                } elseif ($num_rows_wunsch == 1) {
                    $query = "UPDATE campus.tbl_zeitwunsch SET gewicht=" . $db->db_add_param($gewicht) . ", updateamum=now(), updatevon=" . $db->db_add_param($uid_benutzer) . " WHERE mitarbeiter_uid=" . $db->db_add_param($uid) . " AND zeitwunsch_gueltigkeit_id=" . $db->db_add_param($zw_zwg_id) . " AND stunde=" . $db->db_add_param($stunde) . " AND tag=" . $db->db_add_param($t);
                    if (!($erg = $db->db_query($query)))
                        die($db->db_last_error());
                }
                else
                    die("Zuviele Eintraege!");
            }
        }
        $selected_zwg = new Zeitwunsch_gueltigkeit($zw_zwg_id);
    }
}

/**
 * Init ZWG Objekt zum Erstellen einer neuen ZWG
 */
function insertZWG($uid, $von, $bis, $admin_uid)
{
    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->new = true;
    $zwg->mitarbeiter_uid = $uid;
    $zwg->von = $von;
    $zwg->bis = $bis;
    $zwg->insertvon = $admin_uid;
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
function updateZWG($uid, $zwg_id, $bis, $admin_uid)
{
    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->new = false;
    $zwg->zeitwunsch_gueltigkeit_id = $zwg_id;
    $zwg->mitarbeiter_uid = $uid;
    $zwg->bis = $bis;
    $zwg->updatevon = $admin_uid;

    if (!$zwg->save())
    {
        die($zwg->errormsg);
    }

    return;
}

// Tabellendaten
/**
 * Zeitwunschgueltigkeit
 * Wurde ueber Dropdown gewaehlt (kann auch null sein, wenn noch kein Zeitwunsch vorliegt)
 * ODER ueber Speichernbutton neu erstellt / upgedatet
 */
$selected_zwg_id = !is_null($selected_zwg) ? $selected_zwg->zeitwunsch_gueltigkeit_id : '';

	if(!($erg=$db->db_query("
        SELECT * 
        FROM campus.tbl_zeitwunsch 
        WHERE mitarbeiter_uid = ". $db->db_add_param($uid). "
        AND zeitwunsch_gueltigkeit_id = ". $db->db_add_param(($selected_zwg_id))
    )))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($erg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$tag=$db->db_result($erg,$i,"tag");
		$stunde=$db->db_result($erg,$i,"stunde");
		$gewicht=$db->db_result($erg,$i,"gewicht");
		$wunsch[$tag][$stunde]=$gewicht;
		$updateamum=$db->db_result($erg,$i,"updateamum");
		$updatevon=$db->db_result($erg,$i,"updatevon");
	}
	if(!isset($wunsch))
	{
		//6-16
		for ($i=1;$i<7;$i++)
		{
			for ($j=0;$j<17;$j++)
			{
				$wunsch[$i][$j]='1';
			}
		}
	}


	// Personendaten
	if(! $result=$db->db_query("SELECT * FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid=".$db->db_add_param($uid)))
		die($db->db_last_error());
	if ($db->db_num_rows($result)==1)
		$person=$db->db_fetch_object($result);

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript">
    $(function(){
        // Bei Wechsel von Zeitwunschgueltigkeit die Seite mit GET params neu laden
        $('#zwg').change(function(){
            var uid = $('input[name="uid"]').val();
            var zeitwunsch_gueltigkeit_id = $('option:selected', this).val();
            var studiensemester = $('option:selected', this).data('stsem');

            window.location = '?uid=' + uid + '&zwg_id=' + zeitwunsch_gueltigkeit_id + '&stsem=' + studiensemester;
        });
});
</script>
</head>


<body>
<h2>Zeitw&uuml;nsche von <?php echo $person->titelpre.' '.$person->vorname.' '.$person->nachname. ' '.$person->titelpost; ?></h2>
<span>Zeitwunschgueltigkeit:</span>
<?php
$zwg = new Zeitwunsch_gueltigkeit();
$zwg->getByUID($uid, null, false);
$zwg_arr = $zwg->result;

// Dropdown
echo '<select name="zwg" id="zwg" class="form form-control">';

// Wenn nächstes Studiensemester keine Zeitwunschgueltigkeit hat...
if (!empty($zwg_arr) && $zwg_arr[0]->von < $next_ss->start)
{
    // ...naechstes Studiensemester 'neu anlegen' als Option anzeigen
    echo '<OPTION value="" data-stsem="'. $next_ss->studiensemester_kurzbz. '">'
        . $next_ss->studiensemester_kurzbz.'&emsp;[&ensp;neu anlegen&ensp;]
        </OPTION>';
}

// Vorhandene Zeitwunschgueltigkeiten
foreach($zwg_arr as $row)
{
    $von = (new DateTime($row->von))->format('d.m.Y');
    $bis = !is_null($row->bis) ? (new DateTime($row->bis))->format('d.m.Y') : "offen";
    $selected = !empty($selected_zwg_id) && $row->zeitwunsch_gueltigkeit_id == $selected_zwg_id && $row->studiensemester_kurzbz == $selected_ss ? ' selected ' : '';

    echo '<option value="'. $row->zeitwunsch_gueltigkeit_id. '" data-stsem="'. $row->studiensemester_kurzbz. '"'. $selected. '>'.
        $row->studiensemester_kurzbz. '&emsp;[ '. $von. ' &ensp;-&ensp;' . $bis. ' ]
        </option>';
}
// Wenn aktuelles Studiensemester keine Zeitwunschgueltigkeit hat, das naechste aber schon
if (count($zwg_arr) == 1 && ($zwg_arr[0]->von >= $next_ss->start))
{
    // ...aktuelles Studiensemester 'neu anlegen' als Option anzeigen
    $selected = $selected_ss == $akt_ss->studiensemester_kurzbz ? "selected" : '';
    echo '<OPTION value="" data-stsem="'. $akt_ss->studiensemester_kurzbz. '" '. $selected. ' >'. $akt_ss->studiensemester_kurzbz.'&emsp;[&ensp;neu anlegen&ensp;]</OPTION>';
}

// Wenn es noch keine Zeitwuensche gibt
if (empty($zwg_arr))
{
    // Optionen zum Anlegen einer Zeitwunschgueltigkeit fuer das aktuelle / naechste Studiensemester
    $selected = $selected_ss == $akt_ss->studiensemester_kurzbz ? 'selected' : '';
    echo '<OPTION value="" data-stsem="'. $next_ss->studiensemester_kurzbz. '">'. $next_ss->studiensemester_kurzbz.'&emsp;[&ensp;neu anlegen&ensp;]</OPTION>';
    echo '<OPTION value="" data-stsem="'. $akt_ss->studiensemester_kurzbz. '" '. $selected. ' >'. $akt_ss->studiensemester_kurzbz.'&emsp;[&ensp;neu anlegen&ensp;]</OPTION>';
}
echo '</select>';
?>
<br><br>

<FORM name="zeitwunsch" method="post" action="zeitwunsch.php?type=save">
    <INPUT type="hidden" name="uid" value="<?php echo $uid; ?>">
    <INPUT type="hidden" name="zwg_id" value="<?php echo $selected_zwg_id; ?>">
    <INPUT type="hidden" name="stsem" value="<?php echo $selected_ss; ?>">
  <TABLE width="100%" border="1" cellspacing="0" cellpadding="0">
    <TR>
    	<?php
	  	echo '<th>Stunde<br>Beginn<br>Ende</th>';
		for ($i=0;$i<$num_rows_stunde; $i++)
		{
			$beginn=$db->db_result($result_stunde,$i,'"beginn"');
			$beginn=substr($beginn,0,5);
			$ende=$db->db_result($result_stunde,$i,'"ende"');
			$ende=substr($ende,0,5);
			$stunde=$db->db_result($result_stunde,$i,'"stunde"');
			echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
		}
		?>
    </TR>
	<?php

    $readonly = 'readonly';

    if ($rechte->isBerechtigt('mitarbeiter/zeitwuensche', null, 'suid'))
        $readonly = '';

    for ($j=1; $j<7; $j++)
	{
		echo '<TR><TD>'.$tagbez[1][$j].'</TD>';
	  	for ($i=0;$i<$num_rows_stunde;$i++)
		{
			$index=$wunsch[$j][$i+1];
			if ($index=="")
				$index=1;
			$bgcolor=$cfgStdBgcolor[$index+3];
			echo '<TD align="center" bgcolor="'.$bgcolor.'"><INPUT align="right" type="text"  name="wunsch'.$j.'_'.$i.'" size="2" maxlength="2"' . $readonly .' value="'.$index.'"></TD>';
		}
		echo '</TR>';
	}
	?>
  </TABLE>
  <br/>
  <?php
  if($updatevon!='')
  {
  	echo 'Zeitwunsch zuletzt aktualisiert von ';
  	echo $updatevon;
  	echo ' am ';
  	echo $datum_obj->formatDatum($updateamum,'d.m.Y H:i:s');
  }
  else
  {
  	echo 'Noch keine Zeitwünsche eingetragen';
  }
  ?>
  <br/>
  <br/>
    <?php
        if($rechte->isBerechtigt('mitarbeiter/zeitwuensche', null, 'suid'))
        {
            /**
            * Disablen des Speicherbuttons und Textanzeige, wenn die gewaehlte Zeitwunschgueltigkeit nicht
            *die letztgueltige fuer das aktuelle / naechste Studiensemester ist.
            **/
            $disabled = getDisabledString($uid, $selected_zwg, $akt_ss, $next_ss); // return 'disabled' oder ''

            // Speichern Button
            echo '<INPUT type="submit" name="save" value="Speichern" '. $disabled. '>';

            if (!empty($disabled))
            {
                echo '<span style="color: red"><small>&emsp;Es können nur Zeitwünsche im aktuellen oder im nächsten Studiensemester bearbeitet werden.<br>
                &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Falls mehrere Zeitwünsche im aktuellen Semester gespeichert sind, kann nur der letztgültige geändert werden.</small></span>';
            }
        }
    ?>
</FORM>
<br>
<hr>
<H3>Erkl&auml;rung:</H3>
<P>Bitte kontrollieren/&auml;ndern Sie Ihre Zeitw&uuml;nsche und klicken Sie anschlie&szlig;end
  auf &quot;Speichern&quot;!<BR>
  <BR>
</P>
<TABLE width="50%" border="1" cellspacing="0" cellpadding="0" name="Zeitwerte">
  <TR>
    <TD><B>Wert</B></TD>
    <TD>
      <DIV align="center"><B>Bedeutung</B></DIV>
    </TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">2</DIV>
    </TD>
    <TD>Hier m&ouml;chte ich Unterrichten</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">1</DIV>
    </TD>
    <TD>Hier kann ich Unterrichten</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-1</DIV>
    </TD>
    <TD>Hier m&ouml;chte ich eher nicht</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-2</DIV>
    </TD>
    <TD>Hier nur in extremen Notf&auml;llen</TD>
  </TR>
</TABLE>
<P>&nbsp;</P>
<H3>Folgende Punkte sind zu beachten:</H3>
<OL>
  <LI>Verwenden Sie den Wert -2 nur, wenn Sie zu dieser Stunde wirklich nicht k&ouml;nnen, um eine bessere Optimierung zu erm&ouml;glichen.</LI>
  <LI>Es sollten f&uuml;r jede Stunde die tats&auml;chlich unterrichtet wird, mindestens das 3-fache an positiven Zeitw&uuml;nschen angegeben werden.<BR>
    Beispiel: Sie unterrichten 4 Stunden/Woche, dann sollten Sie mindestens 12 Stunden im Raster mit positiven Werten ausf&uuml;llen.</LI>
</OL>
<P>&nbsp;</P>
</body>
<script type="text/javascript">
    $(function(){
        // Bei Wechsel von Zeitwunschgueltigkeit die Seite mit GET params neu laden
        //$('#zwg').change(function(){
        //    let uid = $('input[name="uid"]').val();
        //    let zeitwunsch_gueltigkeit_id = $('option:selected', this).val();
        //    let studiensemester = $('option:selected', this).data('stsem');
        //
        //    // window.location = '?uid=' + uid + '&zwg_id=' + zeitwunsch_gueltigkeit_id + '&stsem=' + studiensemester;
        //    window.open = <?php //echo $_SERVER['PHP_SELF']  ?>// + '?uid=' + uid + '&zwg_id=' + zeitwunsch_gueltigkeit_id + '&stsem=' + studiensemester;
        //});
    });
    function submitZwg(obj){
        // console.log(obj);
        // console.log(obj.options[obj. selectedIndex].getAttribute('data-stsem'));
        // var selectedValue = document.getElementById("zwg").value;
        // console.log(selectedValue);
        // console.log(document.getElementById("zwg"));
        // console.log(document.getElementById("zwg").getAttribute('data'));
        // console.log($(selectElem).val());
        // console.log($(selectElem).selected);
        // console.log($('option:selected', this));
        // console.log($('option:selected', this).val());
        //     let uid = $('input[name="uid"]').val();
        //     let zeitwunsch_gueltigkeit_id = $('option:selected', obj).val();
        //     let studiensemester = $('option:selected', obj).data('stsem');


        let uid = document.getElementById("uid").value;
        let zeitwunsch_gueltigkeit_id = document.getElementById("zwg").value;
        let studiensemester = obj.options[obj.selectedIndex].getAttribute('data-stsem');
        console.log(uid);
        console.log(zeitwunsch_gueltigkeit_id);
        console.log(studiensemester);
        // window.location = '?uid=' + uid + '&zwg_id=' + zeitwunsch_gueltigkeit_id + '&stsem=' + studiensemester;

        //window.open('<?php //echo APP_ROOT ?>//vilesci/personen/zeitwunsch.php?uid=' + uid + '&zwg_id=' + zeitwunsch_gueltigkeit_id + '&stsem=' + studiensemester,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
        window.open('<?php echo $_SERVER['PHP_SELF'] ?>?uid=' + uid + '&zwg_id=' + zeitwunsch_gueltigkeit_id + '&stsem=' + studiensemester);

    }

</script>
</html>

<?php

function getDisabledString($uid, $selected_zwg, $akt_ss, $next_ss){
    // Disablen des Speicherbuttons und Textanzeige, wenn die gewaehlte Zeitwunschgueltigkeit nicht
    // die letztgueltige fuer das aktuelle / naechste Studiensemester ist.
    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->getByStudiensemester($uid, $akt_ss->studiensemester_kurzbz);
    $lastZwg_id_aktStudsem = empty($zwg->result) ? '' : $zwg->result[0]->zeitwunsch_gueltigkeit_id;

    $zwg = new Zeitwunsch_gueltigkeit();
    $zwg->getByStudiensemester($uid, $next_ss->studiensemester_kurzbz);
    $lastZwg_id_nextStudsem = empty($zwg->result) ? '' : $zwg->result[0]->zeitwunsch_gueltigkeit_id;

    return (
        is_null($selected_zwg)
        || !is_null($selected_zwg)
        && (
            $selected_zwg->zeitwunsch_gueltigkeit_id == $lastZwg_id_aktStudsem
            || $selected_zwg->zeitwunsch_gueltigkeit_id == $lastZwg_id_nextStudsem
        )
    )
        ? ''
        : 'disabled';

}



