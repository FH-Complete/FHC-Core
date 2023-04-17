<?php
/* Copyright (C) 2012 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 *
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/preoutgoing.class.php');
require_once('../../../include/firma.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/mobilitaetsprogramm.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/akte.class.php');

$method = (isset($_GET['method'])?$_GET['method']:'');
$message = '';
$uid=get_uid();
$sprache = getSprache();
$p=new phrasen($sprache);
$outgoing = new preoutgoing();
$outgoing->loadUid($uid);

// speichert outgoing
if(isset($_REQUEST['submitOutgoing']))
{
    $ansprechpersonUid = (isset($_REQUEST['ansprechperson_uid']))?$_REQUEST['ansprechperson_uid']:'';

    $datum=new datum();
    $zeitraum_von = $datum->formatDatum($_REQUEST['zeitraum_von'], 'Y-m-d');
    $zeitraum_bis = $datum->formatDatum($_REQUEST['zeitraum_bis'], 'Y-m-d');

    $preoutgoing = new preoutgoing();
    $preoutgoing->loadUid($outgoing->uid);

    // löschen der Ansprechperson
    if($_POST['ansprechperson']==' ' || $_POST['ansprechperson']=='' || $_POST['ansprechperson_uid'] == '')
        $ansprechpersonUid  = '';

    $preoutgoing->new = false;
    $preoutgoing->ansprechperson = $ansprechpersonUid;
    $preoutgoing->dauer_von = $zeitraum_von;
    $preoutgoing->dauer_bis = $zeitraum_bis;
    $preoutgoing->anmerkung_student = $_POST['anmerkung'];
    $preoutgoing->updatevon = $uid;

    if($preoutgoing->save())
        $message='<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
    else
        $message='<span class="error">'.$p->t('global/fehlerBeimSpeichernDerDaten').'</span>';
}

// Updated die Daten des Preoutgoing
if(isset($_REQUEST['zDaten']))
{
    $preoutgoingZDaten = new preoutgoing();
    $preoutgoingZDaten->load($outgoing->preoutgoing_id);

    $datum=new datum();
    // wenn sprachkurs gesetzt -> erasmus programm
    if(isset($_REQUEST['sprachkurs']))
    {
        $preoutgoingZDaten->sprachkurs_von = $datum->formatDatum($_REQUEST['sprachkurs_von'], 'Y-m-d');
        $preoutgoingZDaten->sprachkurs_bis = $datum->formatDatum($_REQUEST['sprachkurs_bis'], 'Y-m-d');
        if($_REQUEST['sprachkurs'] == 'vorbereitend')
        {
            $preoutgoingZDaten->sprachkurs = true;
            $preoutgoingZDaten->intensivsprachkurs = false;
        }
        else if($_REQUEST['sprachkurs']=='intensiv')
        {
            $preoutgoingZDaten->sprachkurs = false;
            $preoutgoingZDaten->intensivsprachkurs = true;
        }
        else
        {
            $preoutgoingZDaten->sprachkurs = false;
            $preoutgoingZDaten->intensivsprachkurs = false;
        }
    }

    $preoutgoingZDaten->praktikum_von = $datum->formatDatum($_REQUEST['praktikum_von'], 'Y-m-d');
    $preoutgoingZDaten->praktikum_bis = $datum->formatDatum($_REQUEST['praktikum_bis'], 'Y-m-d');
    $preoutgoingZDaten->praktikum = isset($_REQUEST['praktikum'])?true:false;
    $betreuer = isset($_POST['betreuer_uid'])?$_POST['betreuer_uid']:'';

    if($_POST['betreuer']==' ' || $_POST['betreuer']=='' || $_POST['betreuer_uid'] == '')
        $betreuer = '';
    $preoutgoingZDaten->bachelorarbeit = isset($_REQUEST['bachelorarbeit'])?true:false;
    $preoutgoingZDaten->masterarbeit = isset($_REQUEST['masterarbeit'])?true:false;
    $preoutgoingZDaten->projektarbeittitel = $_REQUEST['projektarbeittitel'];
    $preoutgoingZDaten->behinderungszuschuss = isset($_REQUEST['behinderungszuschuss'])?true:false;
    $preoutgoingZDaten->studienbeihilfe = isset($_REQUEST['studienbeihilfe'])?true:false;
    $preoutgoingZDaten->betreuer = $betreuer;
    $preoutgoingZDaten->studienrichtung_gastuniversitaet = isset($_REQUEST['studienrichtungGastuni'])?$_REQUEST['studienrichtungGastuni']:'';
    $preoutgoingZDaten->new = false;
    if(!$preoutgoingZDaten->save())
        $message='<span class="error">'.$p->t('global/fehlerBeimSpeichernDerDaten').'</span>';
    else
        $message='<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
}

// neuen Datensatz anlegen
if($method=='new')
{
    $preoutgoing = new preoutgoing();
    $preoutgoing->uid = $uid;
    $preoutgoing->new = true;
    $preoutgoing->bachelorarbeit = false;
    $preoutgoing->masterarbeit = false;
    $preoutgoing->sprachkurs = false;
    $preoutgoing->intensivsprachkurs = false;
    $preoutgoing->praktikum = false;
    $preoutgoing->behinderungszuschuss = false;
    $preoutgoing->studienbeihilfe = false;
    $preoutgoing->insertvon = $uid;
    if($preoutgoing->save())
    {
        // Email an Auslandsabteilung schicken
        sendMailInternational();
        $message='<span class="ok">'.$p->t('global/erfolgreichAngelegt').'</span>';
    }
    else
        die($preoutgoing->errormsg);
}

// speichert die eingegebene Lehrveranstaltung
if(isset($_POST['saveLv']) == 'saveLv')
{
    $bezeichnung = $_POST['lv_bezeichnung'];
    $ects = $_POST['lv_ects'];
    $wochenstunden = $_POST['lv_wochenstunden'];
    $unitcode = $_POST['lv_unitcode'];

    $preoutgoingLv = new preoutgoing();
    $preoutgoingLv->preoutgoing_id = $outgoing->preoutgoing_id;
    $preoutgoingLv->bezeichnung = $bezeichnung;
    $preoutgoingLv->ects = $ects;
    $preoutgoingLv->wochenstunden = $wochenstunden;
    $preoutgoingLv->unitcode = $unitcode;
    $preoutgoingLv->new = true;
    $preoutgoingLv->insertvon = $uid;
    if(!$preoutgoingLv->saveLv())
        $message='<span class="error">'.$p->t('global/fehlerBeimSpeichernDerDaten').'</span>';
    else
        $message='<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
}

// löscht die übergebene Lehrveranstaltung
if($method== 'deleteLv')
{
    $lv_id = $_GET['lv_id'];
    $preoutgoingLv = new preoutgoing();

    // Wenn die Lv zum preoutgoing gehört wird sie gelöscht

    if($preoutgoingLv->checkLv($lv_id, $outgoing->preoutgoing_id))
    {
        if(!$preoutgoingLv->deleteLv($lv_id))
            $message ='<span class="error">'.$p->t('incoming/fehlerBeimLoeschenDerLv').'</span>';
        else
            $message ='<span class="ok">'.$p->t('global/erfolgreichgelöscht').'</span>';
    }
}

// speichert die ausgewählte Firma
if($method=='saveFirma')
{
    $firmaOutgoing = new preoutgoing();

    // Check ob schon 3 Firmen Eingetragen sind
    if(($firmaOutgoing->getAnzahlFirma($outgoing->preoutgoing_id)) < 3)
    {
        if(!isset($_GET['firma_id']))
        {
            // Freemover wird ausgewählt
            $firma_id = '';
            $name = $_GET['name'];
            $firmaOutgoing->mobilitaetsprogramm_code = 202;
        }
        else
        {
            // Programm ausgewählt
            $firma_id = $_GET['firma_id'];
            $name = '';
            $firmaOutgoing->mobilitaetsprogramm_code = $_GET['programm'];
        }

            $firmaOutgoing->preoutgoing_id = $outgoing->preoutgoing_id;
            $firmaOutgoing->firma_id = $firma_id;
            $firmaOutgoing->name = $name;
            $firmaOutgoing->auswahl = false;
            $firmaOutgoing->new = true;
            if(!$firmaOutgoing->saveFirma())
            {
                die($firmaOutgoing->errormsg);
            }
            $message='<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
    }
    else
        $message = '<span class="error">'.$p->t('incoming/nichtMehrAlsDreiUniversitaeten').'</span>';
}

// Löscht die Akte mit übergebener Id
if($method == 'files')
{
	$akte = new akte();

	if(isset($_GET['id']))
	{
		if($_GET['mode']=="delete")
		{
			if($akte->delete($_GET['id']))
				$message ='<span class="ok">'.$p->t('global/erfolgreichgelöscht').'</span>';
			else
				$message ='<span class="error">'.$p->t('global/fehleraufgetreten').'</span>';
		}
	}
}

// löscht die ausgewählte Firma
if($method =="deleteFirma")
{
    if(isset($_GET['outgoingFirma_id']))
    {
        $outgoingFirma_id = $_GET['outgoingFirma_id'];
        $firmaOutgoing = new preoutgoing();
        if(!$firmaOutgoing->deleteFirma($outgoingFirma_id))
            $message = '<span class="error">'.$p->t('global/fehlerBeimLoeschenDesEintrags').'</span>';
        $message ='<span class="ok">'.$p->t('global/erfolgreichgelöscht').'</span>';
    }
    else
        $message = '<span class="error">'.$p->t('incoming/ungueltigeIdUebergeben').'</span>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
		<title><?php echo $p->t('incoming/outgoingRegistration'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
        <link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
        <link href="../../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
        <link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
        <script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>


        <script type="text/javascript">
        $(document).ready(function()
        {
            $( "#datepicker_zeitraumvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_zeitraumbis" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_sprachkursvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_sprachkursbis" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_praktikumvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_praktikumbis" ).datepicker($.datepicker.regional['de']);

            $("#myTable").tablesorter(
            {
                widgets: ["zebra"]
            });
            $("#myTableFiles").tablesorter(
            {
                sortList: [[0,0]],
                widgets: ["zebra"]
            });


            function formatItem(row)
            {
                return row[0] + " " + row[1] + " " + row[2];
            }


            $("#ansprechperson").autocomplete({
			source: "outgoing_autocomplete.php?autocomplete=mitarbeiter",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#ansprechperson_uid").val(ui.item.uid);
			}
			});

            $("#betreuer").autocomplete({
			source: "outgoing_autocomplete.php?autocomplete=mitarbeiter",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#betreuer_uid").val(ui.item.uid);
			}
			});
        });



        </script>
	</head>
	<body>
<?php

$benutzer = new benutzer();
$benutzer->load($uid);
$outgoing = new preoutgoing();
$outgoing->loadUid($uid);
$datum = new datum();
$zeitraum_von = $datum->formatDatum($outgoing->dauer_von, 'd.m.Y');
$zeitraum_bis = $datum->formatDatum($outgoing->dauer_bis, 'd.m.Y');
$ansprechperson = new benutzer();
$ansprechperson->load($outgoing->ansprechperson);

$name = '';
if($benutzer->titelpre !='')
    $name.=$benutzer->titelpre.' ';
$name.= $benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost;
//
if(isset($_GET['ansicht']) == 'auswahl')
{

?>
    <table border ="0" width="100%">
        <tr>
            <td align="left" colspan="4"><b><h1><div style="display:block; text-align:left; float:left;"><?php echo $p->t('incoming/outgoingRegistration'); ?></div><div style="display:block; text-align:right; margin-right:6px; "><?php echo((check_lektor($outgoing->uid)!='0')?"Mitarbeiter: ":"Student: ").$name; ?></div></h1></b></td>
        </tr>
        <tr><td><?php echo $message; ?></td></tr>
        <tr><td><h3><?php echo $p->t('incoming/programmAuswahl');?>:</h3></td><td><div style="display:block; text-align:right; margin-right:6px; "><a href="<?php echo $_SERVER['PHP_SELF']; ?>?method=new&ansicht=auswahl" align ="left"><?php echo $p->t('incoming/neuenOutgoingAnlegen'); ?></a></div></td></tr>
    </table>

    <table border="0"  width="100%">
        <tr>
            <td width="50%">
       <!-- Linke Seite -> Auswahl der Universitäten -->
                <table width="90%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px">
                    <tr>
                        <td><b>ERASMUS</b>: Finanzielle Unterstützung für Studierendenmobilität bei Partnerinstitutionen in den EU-Mitgliedsstaaten, Island, Kroatien, Liechtenstein, Norwegen, der Schweiz und der Türkei.</td>
                    </tr>
                    <tr>
                        <td><SELECT name="auswahl_erasmus" style="width: 90%" onchange="saveFirma(this.value, '7')">
                        <option value="erasmus_auswahl">-- select --</option>
                    <?php
                    $firmaErasmus = new firma();
                    $firmaErasmus->getFirmenMobilitaetsprogramm('7');
                    foreach($firmaErasmus->result as $fi)
                        echo'<option value="'.$fi->firma_id.'">'.$fi->name.'</option>';
                    ?>
                        </td>
                    </tr>
                </table>
                <table width="90%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px">
                    <tr>
                        <td><b>CEEPUS</b>: Finanzielle Unterstützung für Studierendenmobilität bei Partnerinstitutionen im Rahmen unseres Netzwerkes in Albanien, Bosnien-Herzegowina, Bulgarien, Kosovo (Universität Prishtina), Kroatien, Mazedonien, Moldawien, Montenegro, Österreich, Polen, Rumänien, Serbien, der Slowakischen Republik, Slowenien, der Tschechischen Republik und Ungarn.</td>
                    </tr>
                    <tr>
                        <td><SELECT name="auswahl_ceepus" style="width: 90%" onchange="saveFirma(this.value, '6')" >
                        <option value="ceepus_auswahl">-- select --</option>
                    <?php
                    $firmaCeepus = new firma();
                    $firmaCeepus->getFirmenMobilitaetsprogramm('6');
                    foreach($firmaCeepus->result as $fi)
                        echo'<option value="'.$fi->firma_id.'">'.$fi->name.'</option>';
                    ?>
                        </td>
                    </tr>
                </table>
                <table width="90%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px;">
                    <tr>
                        <td><b>Sonstige</b>: Bilaterale Abkommen zwischen der FH Technikum Wien und Hochschulen außerhalb Europas zum gegenseitigen geförderten Studierendenaustausch. Eine Liste der Partnerinstitutionen befindet sich auf unserer Homepage. </td>
                    </tr>
                    <tr>
                        <td><SELECT name="auswahl_sonstige" style="width: 90%" onchange="saveFirma(this.value, '30')">
                        <option value="sonstige_auswahl">-- select --</option>
                    <?php
                    $firmaSonstige = new firma();
                    $firmaSonstige->getFirmenMobilitaetsprogramm('30');
                    foreach($firmaSonstige->result as $fi)
                        echo'<option value="'.$fi->firma_id.'">'.$fi->name.'</option>';
                    ?>
                        </td>
                    </tr>
                </table>
                <table width="90%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px;">
                    <tr>
                        <td><b>Freemover</b>: Bewerbung bei einer Hochschule, die keine Partnerinstitution ist. Studierende planen und organisieren ihren Studienaufenthalt selbst. Meistens muss die Studiengebühr der Gasthochschule bezahlt werden. </td>
                    </tr>
                    <tr>
                        <td><input type="text" size="45" maxlength="40" name="freemover" id="freemover" /></td>
                        <td><input type="button" value="add" onclick="saveFreemover()" /></td>
                    </tr>
                </table>
                <br>
            </td>
                <!-- Rechte Seite -> Ausgewählte Universitäten -->
                <td valign="top">

                    <table width="100%" style="border: thin solid black; border-spacing:5px; background-color: lightgray; margin-top:5px;" >
                        <tr><td><b> <?php echo $p->t('incoming/auswahlUniversitaeten'); ?>: </b></td></tr>

                        <?php
                        $outgoingFirma = new preoutgoing();
                        $outgoingFirma->loadAuswahlFirmen($outgoing->preoutgoing_id);
                        $disabledSpeichern = ($outgoing->checkStatus($outgoing->preoutgoing_id, 'freigabe'))?'disabled':'';

                        $i = 1;

                        foreach($outgoingFirma->firmen as $fi)
                        {
                            $firmaAuswahl = new firma();
                            $firmaAuswahl->load($fi->firma_id);
                            $style = '';
                            $link = '';

                            if($fi->auswahl == true)
                                $style = 'style="color:red"';

                            $mobilitätsprogramm = new mobilitaetsprogramm();
                            $mobilitätsprogramm->load($fi->mobilitaetsprogramm_code);
                            if($mobilitätsprogramm->kurzbz == '')
                                $mobprogramm = 'SUMMERSCHOOL';
                            else
                                $mobprogramm = $mobilitätsprogramm->kurzbz;

                            if($fi->name == '')
                            {
                                if(!$outgoing->checkStatus($outgoing->preoutgoing_id, 'freigabe'))
                                    $link = "<a href='".$_SERVER['PHP_SELF']."?method=deleteFirma&outgoingFirma_id=".$fi->preoutgoing_firma_id."&ansicht=auswahl'>delete</a>";

                                echo " <tr><td ".$style.">".$i.": ".$firmaAuswahl->name." [".$mobprogramm."] $link </td></tr>";
                            }
                            else // freemover
                            {
                                if(!$outgoing->checkStatus($outgoing->preoutgoing_id, 'freigabe'))
                                    $link = "<a href='".$_SERVER['PHP_SELF']."?method=deleteFirma&outgoingFirma_id=".$fi->preoutgoing_firma_id."&ansicht=auswahl'>delete</a>";
                                echo " <tr><td ".$style.">".$i.": ".$fi->name." [Freemover] $link </td></tr>";
                            }
                            $i++;
                        }
                        ?>

                    </table>
                    <form action="<?php echo $_SERVER['PHP_SELF']."?ansicht=auswahl"; ?>" method ="POST">
                        <table width="100%" style="border: thin solid black; border-spacing:5px; background-color: lightgray; margin-top:5px; margin-bottom:5px;" >
                                <tr><td><?php echo $p->t('incoming/zeitraumVon');?>:</td><td><input type="text" size="25" maxlength="40" name="zeitraum_von" id="datepicker_zeitraumvon" value="<?php echo($zeitraum_von); ?>"/></td></tr>
                                <tr><td><?php echo $p->t('incoming/zeitraumBis');?>:</td><td><input type="text" size="25" maxlength="40" name="zeitraum_bis" id="datepicker_zeitraumbis" value="<?php echo($zeitraum_bis); ?>"/></td></tr>
                                <tr><td><?php echo $p->t('incoming/ansprechpersonHeimatuniversitaet');?>:</td><td><input type="text" size="25" maxlength="40" name="ansprechperson" id="ansprechperson" value="<?php echo($ansprechperson->vorname.' '.$ansprechperson->nachname);?>"/><input type="hidden" value="<?php echo ($ansprechperson->uid); ?>" id="ansprechperson_uid" name="ansprechperson_uid" /></td></tr>
                                <tr><td><?php echo $p->t('incoming/anmerkungen');?>:</td><td><textarea name="anmerkung" cols="20" rows="5"><?php echo($outgoing->anmerkung_student); ?></textarea></td></tr>
                        </table>
                        <table border="0" width ="100%">
                            <tr>
                                <td width="50%"><input type="submit" name="submitOutgoing" value="<?php echo $p->t('global/speichern')?>" <?php echo $disabledSpeichern; ?>></td>
                                <?php
                                // Weiter Button bei Freigabe anzeigen
                                if($outgoing->checkStatus($outgoing->preoutgoing_id, 'freigabe'))
                                {
                                    echo '<td  align="right"><input type="button" name="absenden" value="'.$p->t("incoming/weiter").'" onclick="clickWeiter()"></td>';
                                }
                                ?>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td><?php
                                    if(!$outgoing->checkStatus($outgoing->preoutgoing_id, 'freigabe'))
                                        echo '<span class="error">'.$p->t('incoming/warteAufFreigabe').'</span>';

                                    ?>
                                </td>
                            </tr>
                        </table>

                    </form>
                </td>
            </tr>

    </table>
    <table><!--Summerschool Anmeldung -->
        <tr><td><h3><?php echo $p->t('incoming/summerschool');?>:</h3></td></tr>
        <tr><td>
                 <table width="46%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px">
                    <tr>
                        <td><b>Summerschool</b>: Meist zwei- bis dreiwöchige wissenschaftliche Fachkurse, die in den Sommerferien von Partnerhochschulen organisiert werden. Ankündigungen von Summer Schools erfolgen auf der CIS-Seite. </td>
                    </tr>
                    <tr>
                        <td><SELECT name="auswahl_summerschool" onchange="saveFirma(this.value, '')" style="width: 90%">
                        <option value="summerschool_auswahl">-- select --</option>
                    <?php
                    $firmaSummerschool = new firma();
                    $firmaSummerschool->getFirmen('Partneruniversität');
                    foreach($firmaSummerschool->result as $fi)
                        echo'<option value="'.$fi->firma_id.'">'.$fi->name.'</option>';
                    ?>
                            </SELECT>
                        </td>
                    </tr>
                </table>
            </td></tr>
    </table>
        <?php
}
else
{
        // Wenn schon Freigegeben -> dann zusätzliche Felder anzeigen
    if($outgoing->checkStatus($outgoing->preoutgoing_id, 'freigabe'))
    {
        $outgoing_id = $outgoing->preoutgoing_id;

        $outgoingAuswahlFirma = new preoutgoing();
        $outgoingAuswahlFirma->loadAuswahl($outgoing_id);
        $bscChecked = $outgoing->bachelorarbeit?'checked':'';
        $mscChecked = $outgoing->masterarbeit?'checked':'';
        $praktikumChecked = $outgoing->praktikum?'checked':'';
        $behindChecked = $outgoing->behinderungszuschuss?'checked':'';
        $beihilfeChecked = $outgoing->studienbeihilfe?'checked':'';
        $sprachkursSelect = $outgoing->sprachkurs?'selected':'';
        $intensivSprachkursSelect = $outgoing->intensivsprachkurs?'selected':'';
        $betreuer = new benutzer();
        $betreuer->load($outgoing->betreuer);

        ?><table border ="0" width="100%">
    <tr>
        <td align="left" colspan="4"><b><h1><div style="display:block; text-align:left; float:left;"><?php echo $p->t('incoming/outgoingRegistration'); ?></div><div style="display:block; text-align:right; margin-right:6px; "><?php echo((check_lektor($outgoing->uid)!='0')?"Mitarbeiter: ":"Student: ").$name; ?></div></h1></b></td>
    </tr>
    <tr><td><?php echo $message; ?></td></tr>
    <tr><td><h3><?php echo $p->t('incoming/zusaetzlicheDaten');?>:</h3></td><td></td></tr>
    </table><?php

        echo '<form name="zusaetzlicheDaten" method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        echo '<table width="90%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px; margin-bottom:5px;">';
        echo '<tr><td><table>';

        echo '<tr><td>'.$p->t('incoming/praktikum').': </td><td><input type="checkbox" name="praktikum" value="Praktikum" '.$praktikumChecked.'></td>
                <td>'.$p->t('incoming/bachelorthesis').': <input type="checkbox" name="bachelorarbeit" '.$bscChecked.'></td>';
        echo '<td>'.$p->t('incoming/masterthesis').': <input type="checkbox" name="masterarbeit" '.$mscChecked.'></td></tr>';
        echo '<tr><td>'.$p->t('incoming/praktikumVon').': </td><td><input type="text" name="praktikum_von" id="datepicker_praktikumvon" value="'.$datum->formatDatum($outgoing->praktikum_von, 'd.m.Y').'"></td><td colspan="3">'.$p->t('incoming/betreuerMasterBachelor').': </td><td><input type="text" name="betreuer" id="betreuer" value="'.$betreuer->vorname.' '.$betreuer->nachname.'"><input type="hidden" name="betreuer_uid" id="betreuer_uid" value="'.$outgoing->betreuer.'"> </td></tr>';
        echo '<tr><td>'.$p->t('incoming/praktikumBis').': </td><td><input type="text" name="praktikum_bis" id="datepicker_praktikumbis" value="'.$datum->formatDatum($outgoing->praktikum_bis, 'd.m.Y').'"></td><td colspan="3">'.$p->t('incoming/projektarbeitstitel').': </td><td><input type="text" name="projektarbeittitel" id="projektarbeittitel" value="'.$outgoing->projektarbeittitel.'"></td></tr>';
        echo '<tr><td>&nbsp; </td></tr>';
        // zusätzliche Felder bei Erasmus
        if($outgoingAuswahlFirma->mobilitaetsprogramm_code == '7')
        {
            echo '<tr><td>'.$p->t('incoming/sprachkurs').': </td><td><select name="sprachkurs">
                <option value="kein">'.$p->t('incoming/keiner').'</option>
                <option value="vorbereitend" '.$sprachkursSelect.'>'.$p->t('incoming/vorbereitenderSprachkurs').'</option>
                <option value="intensiv" '.$intensivSprachkursSelect.'>'.$p->t('incoming/erasmusIntensivsprachkurs').'</option>
                </select></td></tr>';
            echo '<tr><td>'.$p->t('incoming/sprachkursVon').':</td><td> <input type="text" name="sprachkurs_von" id="datepicker_sprachkursvon" value="'.$datum->formatDatum($outgoing->sprachkurs_von, 'd.m.Y').'"></td><td colspan="4">'.$p->t('incoming/studienrichtungGastuniversitaet').': <input type="text" name="studienrichtungGastuni" value="'.$outgoing->studienrichtung_gastuniversitaet.'"></td></tr>';
            echo '<tr><td>'.$p->t('incoming/sprachkursBis').': </td><td><input type="text" name="sprachkurs_bis" id="datepicker_sprachkursbis" value="'.$datum->formatDatum($outgoing->sprachkurs_bis, 'd.m.Y').'"></td></tr>';
        }

        echo '<tr><td colspan="6">'.$p->t('incoming/aufgrundEinerBehinderung').': <input type="checkbox" name="behinderungszuschuss" '.$behindChecked.'>';
        echo '<tr><td colspan="6">'.$p->t('incoming/währendDesAuslandsaufenthaltes').': <input type="checkbox" name="studienbeihilfe" '.$beihilfeChecked.'>';
        echo '</table>';
        echo '</td></tr></table>';
        echo '<table width="90%">';
        echo '<tr><td><input type="button" value="'.$p->t('global/zurueck').'" onclick="clickZurueck()"></td><td align="right"><input type="submit" value="'.$p->t('global/speichern').'" name="zDaten"></td>';
        echo '</table>';
        echo '</form>';

        // Bei Mitarbeiter Lehrveranstaltung ausblenden
        if(check_lektor($outgoing->uid)=='0')
        {
            echo '<hr>';
            echo '<p width="100%" align="center"><h3>'.$p->t('incoming/auswahlDerLv').'</h2></p>';
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
            echo '<table width="90%" style="border: thin solid black; border-spacing:10px; background-color: lightgray; margin-top:5px; margin-bottom:5px;">';
            echo '<tr><td>'.$p->t('global/bezeichnung').': <input type="text" name="lv_bezeichnung" size="50" id="lv_bezeichnung"></td><td>Wochenstunden: <input type="text" name="lv_wochenstunden" id="lv_wochenstunden" size="4"></td><td>ECTS: <input type="text" name="lv_ects" size="4" id="lv_ects"></td><td>Unit Code: <input tpye="text" size="4" name="lv_unitcode" id="lv_unitcode"></td><td><input type="submit" value="add" name="saveLv"></tr>';
            echo '</table>';
            echo '</form>';

            $preoutgoingLv = new preoutgoing();
            $preoutgoingLv->loadLvs($outgoing_id);
            echo '<h3>'.$p->t('incoming/uebersichtLv').'</h3>';
            echo'<table id="myTable" class="tablesorter">
            <thead>
                <tr>
                <th>'.$p->t('global/bezeichnung').'</th>
                <th>'.$p->t('incoming/wochenstunden').'</th>
                <th>'.$p->t('incoming/ects').'</th>
                <th>'.$p->t('incoming/unitcode').'</th>
                <th></th>
                </tr>
            </thead>
            <tbody>';
            foreach($preoutgoingLv->lehrveranstaltungen as $lv)
            {
                echo '<tr><td>'.$lv->bezeichnung.'</td><td>'.$lv->ects.'</td><td>'.$lv->wochenstunden.'</td><td>'.$lv->unitcode.'</td><td><a href="'.$_SERVER['PHP_SELF'].'?method=deleteLv&lv_id='.$lv->preoutgoing_lehrveranstaltung_id.'">'.$p->t('incoming/loeschen').'</a></td></tr>';

            }
            echo '</table>';
            echo '<table>';
            echo '<tr><td><input type="button" value="'.$p->t('incoming/learningAgreement').'" onClick="getLearningAgreement();"><input type="button" value="'.$p->t('incoming/geaendertesLA').'" onClick="getLearningAgreementChange();"></td></tr>';
            echo '</table>';
        }

        echo '<hr>';
        echo '<h3>'.$p->t('incoming/verwaltungVonDateien').'</h3>';
        echo '<table>
            <tr><td><a href="'.APP_ROOT.'cis/private/outgoing/akteupload.php?person_id='.$benutzer->person_id.'" onclick="FensterOeffnen(this.href); return false;">',$p->t('incoming/fileupload'),'</a></td></td></tr>';
        $akte = new akte();
        $akte->getAktenOutgoing($benutzer->person_id);


        if(count($akte->result)>0)
        {
        echo'<table id="myTableFiles" class="tablesorter">
            <thead>
                <tr>
                <th>'.$p->t('incoming/dateiname').'</th>
                <th></th>
                </tr>
            </thead>
        <tbody>';
            foreach ($akte->result as $ak)
            {
                echo '<tr>
                        <td><a href="'.APP_ROOT.'cis/private/outgoing/akte.php?id='.$ak->akte_id.'">'.$ak->titel.'</a></td>
                        <td><a href="'.$_SERVER['PHP_SELF'].'?method=files&mode=delete&id='.$ak->akte_id.'" title="delete">'.$p->t('incoming/loeschen').'</a></td>
                    </tr>';
            }
            echo '</table>';
        }
    }
}

        // Lehrveranstaltungen eingragen


        ?>
    <script type="text/javascript">
        function saveFirma(firma_id, programm)
        {
            window.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?method=saveFirma&ansicht=auswahl&firma_id="+firma_id+"&programm="+programm;
        }
        function saveFreemover()
        {
            window.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?method=saveFirma&ansicht=auswahl&name="+document.getElementById("freemover").value;
        }
        function saveLv()
        {
            window.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?method=saveLv&ects="+document.getElementById("lv_ects").value+"&bezeichnung="+document.getElementById("lv_bezeichnung").value;
        }
        function clickWeiter()
        {
            window.location.href="<?php echo $_SERVER['PHP_SELF'] ?>";
        }
        function clickZurueck()
        {
            window.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?ansicht=auswahl";
        }
        function FensterOeffnen (adresse)
		{
			MeinFenster = window.open(adresse, "Info", "width=600,height=200");
	  		MeinFenster.focus();
		}
        function getLearningAgreement()
        {
            var url = "<?php echo APP_ROOT ?>content/pdfExport.php?xsl=OutgoingLearning&xml=learningagreement_outgoing.rdf.php&preoutgoing_id=<?php echo $outgoing->preoutgoing_id;?>&output=pdf";
            window.location.href= url;
        }
        function getLearningAgreementChange()
        {
            var url = "<?php echo APP_ROOT ?>content/pdfExport.php?xsl=OutgoingChangeL&xml=learningagreement_outgoing.rdf.php&preoutgoing_id=<?php echo $outgoing->preoutgoing_id;?>&output=pdf";
            window.location.href= url;
        }
        function test()
        {
            alert('test')
        }
    </script>

    </body>
</html>

<?php
function sendMailInternational()
{
    $emailtext= "Dies ist eine automatisch generierte E-Mail.<br><br>";
    $emailtext.= "Es hat sich ein neuer Outgoing am System registriert.</b>";
    $mail = new mail(MAIL_INTERNATIONAL_OUTGOING, 'no-reply', 'New Outgoing', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
    $mail->setHTMLContent($emailtext);
    $mail->send();
}
?>
