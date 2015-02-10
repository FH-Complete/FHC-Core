<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 * 			Manfred Kindl 	<kindlm@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();
if (!isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user']=='')
{
    $_SESSION['request_uri']=$_SERVER['REQUEST_URI'];

    header('Location: registration.php?method=allgemein');
    exit;
}

//require_once('../../include/functions.inc.php');
require_once('../../include/konto.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/zgv.class.php');
require_once('../../include/dms.class.php');
require_once('../../include/dokument.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/reihungstest.class.php');

$person_id = $_SESSION['bewerbung/personId'];
$akte_id = isset($_GET['akte_id'])?$_GET['akte_id']:'';
$method=isset($_GET['method'])?$_GET['method']:'';
$datum = new datum();
$person = new person();
if(!$person->load($person_id))
    die('Konnte Person nicht laden');

$message = '&nbsp;';

$vollstaendig = '<span class="badge alert-success">vollständig <span class="glyphicon glyphicon-ok"></span></span>';
$unvollstaendig = '<span class="badge alert-danger">unvollständig <span class="glyphicon glyphicon-remove"></span></span>';

if($method=='delete')
{
    $akte= new akte();
    if(!$akte->load($akte_id))
    {
        $message = "Ungueltige akte_id übergeben";
    }
    else
    {
    	if($akte->person_id!=$person_id)
    		die('Ungueltiger Zugriff');

        $dms_id = $akte->dms_id;
        $dms = new dms();

        if($akte->delete($akte_id))
        {
            if(!$dms->deleteDms($dms_id))
                $message = "Konnte DMS Eintrag nicht löschen";
            else
                $message = "Erfolgreich gelöscht";
        }
        else
        {
            $message="Konnte Akte nicht Löschen";
        }
    }

}


if(isset($_GET['rt_id']))
{

	$rt_id = filter_input(INPUT_GET, 'rt_id', FILTER_VALIDATE_INT);
	$pre_id = filter_input(INPUT_GET, 'pre', FILTER_VALIDATE_INT);

	if(isset($_GET['delete']))
	{
		$prestudent = new prestudent();
		if(!$prestudent->getPrestudenten($person_id))
			die('Konnte Prestudenten nicht laden');

		foreach($prestudent->result as $row)
		{
			if($row->prestudent_id == $pre_id)
			{
				$prest = new prestudent();
				$prest->load($pre_id);
				$prest->reihungstest_id = '';
				$prest->anmeldungreihungstest = '';
				$prest->new = false;

				if(!$prest->save())
					echo "Fehler aufgetreten";
			}
		}
	}
	else
	{
		$reihungstest = new reihungstest;
		$reihungstest->load($rt_id);

		if($reihungstest->max_teilnehmer && $reihungstest->getTeilnehmerAnzahl($rt_id) >= $reihungstest->max_teilnehmer)
		{
			die("max. Teilnehmeranzahl erreicht.");
		}

		$timestamp = time();

		$prestudent = new prestudent();
		if(!$prestudent->getPrestudenten($person_id))
			die('Konnte Prestudenten nicht laden');

		foreach($prestudent->result as $row)
		{
			if($row->prestudent_id == $pre_id)
			{
				$prest = new prestudent();
				$prest->load($pre_id);
				$prest->reihungstest_id = $rt_id;
				$prest->anmeldungreihungstest = date("Y-m-d",$timestamp);
				$prest->new = false;

				if(!$prest->save())
					echo "Fehler aufgetreten";
			}
		}
	}
}

if(isset($_POST['btn_bewerbung_abschicken']))
{
   // Mail an zuständige Assistenz schicken
    $pr_id = isset($_POST['prestudent_id'])?$_POST['prestudent_id']:'';

    $studiensemester = new studiensemester();
    $std_semester = $studiensemester->getakt();

    if($pr_id != '')
    {
        // Status Bewerber anlegen
        $prestudent_status = new prestudent();
        $prestudent_status->load($pr_id);

        $alterstatus = new prestudent();
        $alterstatus->getLastStatus($pr_id);

        // check ob es status schon gibt
        if(!$prestudent_status->load_rolle($pr_id, 'Bewerber', $std_semester, '1'))
        {
            $prestudent_status->status_kurzbz = 'Bewerber';
            $prestudent_status->studiensemester_kurzbz = $std_semester;
            $prestudent_status->ausbildungssemester = '1';
            $prestudent_status->datum = date("Y-m-d H:i:s");
            $prestudent_status->insertamum = date("Y-m-d H:i:s");
            $prestudent_status->insertvon = '';
            $prestudent_status->updateamum = date("Y-m-d H:i:s");
            $prestudent_status->updatevon = '';
            $prestudent_status->studienplan_id = $alterstatus->studienplan_id;
            $prestudent_status->new = true;
            if(!$prestudent_status->save_rolle())
                die('Fehler beim anlegen der Rolle');
        }

        if(sendBewerbung($pr_id))
			echo "<script type='text/javascript'>alert('Sie haben sich erfolgreich beworben. Die zuständige Assistenz wird sich in den nächsten Tagen bei Ihnen melden.');</script>";
        else
			echo "<script type='text/javascript'>alert('Es ist ein Fehler beim versenden der Bewerbung aufgetreten. Bitte versuchen Sie es nocheinmal');</script>";

    }


}

if(isset($_POST['submit_nachgereicht']))
{
    $akte = new akte;

    // gibt es schon einen eintrag?
    if(isset($_POST['akte_id']))
    {
        // Update
    }
    else
    {
        // Insert
        $akte->dokument_kurzbz = $_POST['dok_kurzbz'];
        $akte->person_id = $person_id;
        $akte->erstelltam = date('Y-m-d H:i:s');
        $akte->gedruckt = false;
        $akte->titel = '';
        $akte->anmerkung = $_POST['txt_anmerkung'];
        $akte->updateamum = date('Y-m-d H:i:s');
        $akte->insertamum = date('Y-m-d H:i:s');
        $akte->uid = '';
        $akte->new = true;
        $akte->nachgereicht = (isset($_POST['check_nachgereicht']))?true:false;
        if(!$akte->save())
            echo"Fehler beim Speichern aufgetreten ".$akte->errormsg;
    }

}

// gibt an welcher Tab gerade aktiv ist
$active = filter_input(INPUT_GET, 'active');

if(!$active)
{
	$active = 'allgemein';
}

// Persönliche Daten speichern
if(isset($_POST['btn_person']))
{
    $person->titelpre = $_POST['titel_pre'];
    $person->vorname = $_POST ['vorname'];
    $person->nachname = $_POST['nachname'];
    $person->titelpost = $_POST['titel_post'];
    $person->gebdatum = $datum->formatDatum($_POST['geburtsdatum'], 'Y-m-d');
    $person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
    $person->geschlecht = $_POST['geschlecht'];
    $person->svnr = $_POST['svnr'];
	$person->gebort = $_POST['gebort'];
	$person->geburtsnation = $_POST['geburtsnation'];

    $person->new = false;
    if(!$person->save())
        $message=('Fehler beim Speichern der Person aufgetreten');

	if($person->checkSvnr($person->svnr))
		$message = "SVNR bereits vorhanden";
}

// Kontaktdaten speichern
if(isset($_POST['btn_kontakt']))
{
    $kontakt = new kontakt();
    $kontakt->load_persKontakttyp($person->person_id, 'email');
    // gibt es schon kontakte von user
    if(count($kontakt->result)>0)
    {
        // Es gibt bereits einen Emailkontakt
        $kontakt_id = $kontakt->result[0]->kontakt_id;

        if($_POST['email'] == '')
        {
            // löschen
            $kontakt->delete($kontakt_id);
        }
        else
        {
	        $kontakt->person_id = $person->person_id;
	        $kontakt->kontakt_id = $kontakt_id;
	        $kontakt->zustellung = true;
	        $kontakt->kontakttyp = 'email';
	        $kontakt->kontakt = $_POST['email'];
	        $kontakt->new = false;

	        $kontakt->save();
        }
    }
    else
    {
        // neuen Kontakt anlegen
        $kontakt->person_id = $person->person_id;
        $kontakt->zustellung = true;
        $kontakt->kontakttyp = 'email';
        $kontakt->kontakt = $_POST['email'];
        $kontakt->new = true;

        $kontakt->save();
    }

	$kontakt_t = new kontakt();
    $kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
    // gibt es schon kontakte von user
    if(count($kontakt_t->result)>0)
    {
        // Es gibt bereits einen Emailkontakt
        $kontakt_id = $kontakt_t->result[0]->kontakt_id;

        if($_POST['telefonnummer'] == '')
        {
            // löschen
            $kontakt_t->delete($kontakt_id);
        }
        else
        {
	        $kontakt_t->person_id = $person->person_id;
	        $kontakt_t->kontakt_id = $kontakt_id;
	        $kontakt_t->zustellung = true;
	        $kontakt_t->kontakttyp = 'telefon';
	        $kontakt_t->kontakt = $_POST['telefonnummer'];
	        $kontakt_t->new = false;

	        $kontakt_t->save();
        }
    }
    else
    {
        // neuen Kontakt anlegen
        $kontakt_t->person_id = $person->person_id;
        $kontakt_t->zustellung = true;
        $kontakt_t->kontakttyp = 'telefon';
        $kontakt_t->kontakt = $_POST['telefonnummer'];
        $kontakt_t->new = true;

        $kontakt_t->save();
    }

    // Adresse Speichern
    if($_POST['strasse']!='' && $_POST['plz']!='' && $_POST['ort']!='')
    {
        $adresse = new adresse();
        $adresse->load_pers($person->person_id);
        if(count($adresse->result)>0)
        {
            // gibt es schon eine adresse, wird die erste adresse genommen und upgedatet
            $adresse_help = new adresse();
            $adresse_help->load($adresse->result[0]->adresse_id);

            // gibt schon eine Adresse
            $adresse_help->strasse = $_POST['strasse'];
            $adresse_help->plz = $_POST['plz'];
            $adresse_help->ort = $_POST['ort'];
            $adresse_help->nation = $_POST['nation'];
            $adresse_help->updateamum = date('Y-m-d H:i:s');
            $adresse_help->new = false;
            if(!$adresse_help->save())
                die($adresse_help->errormsg);

        }
        else
        {
            // adresse neu anlegen
            $adresse->strasse = $_POST['strasse'];
            $adresse->plz = $_POST['plz'];
            $adresse->ort = $_POST['ort'];
            $adresse->nation = $_POST['nation'];
            $adresse->insertamum = date('Y-m-d H:i:s');
            $adresse->updateamum = date('Y-m-d H:i:s');
            $adresse->person_id = $person->person_id;
            $adresse->zustelladresse = true;
            $adresse->heimatadresse = true;
            $adresse->new = true;
            if(!$adresse->save())
                die('Fehler beim Anlegen der Adresse aufgetreten');

        }
    }
}

if(isset($_POST['btn_zgv']))
{
    // Zugangsvoraussetzungen speichern
    $prestudent = new prestudent();
    if(!$prestudent->load($_POST['prestudent']))
        die('Prestudent konnte nicht geladen werden');

    $prestudent->new = false;
    $prestudent->zgv_code = $_POST['zgv'];
    $prestudent->zgvort = $_POST['zgv_ort'];
    $prestudent->zgvdatum = $datum->formatDatum($_POST['zgv_datum'], 'Y-m-d');
    $prestudent->zgvmas_code = $_POST['zgv_master'];
    $prestudent->zgvmaort = $_POST['zgv_master_ort'];
    $prestudent->zgvmadatum = $datum->formatDatum($_POST['zgv_master_datum'], 'Y-m-d');
    $prestudent->updateamum = date('Y-m-d H:i:s');

    if(!$prestudent->save())
        die('Fehler beim Speichern des Prestudenten aufgetaucht.');

    // Studienplan Speichern
    $prestudent_status = new prestudent();

    if($prestudent_status->getLastStatus($_POST['prestudent']))
    {
    	$prestudent_status->new = false;
    	$prestudent_status->studienplan_id=$_POST['studienplan_id'];
    	$prestudent_status->save_rolle();
    }
}


// Abfrage ob ein Punkt schon vollständig ist
 if($person->vorname && $person->nachname && $person->gebdatum && $person->staatsbuergerschaft && $person->geschlecht)
 {
     $status_person = true;
     $status_person_text = $vollstaendig;
 }
 else
 {
     $status_person = false;
     $status_person_text = $unvollstaendig;
 }

$kontakt = new kontakt();
$kontakt->load_persKontakttyp($person->person_id, 'email');
$adresse = new adresse();
$adresse->load_pers($person->person_id);
if(count($kontakt->result) && count($adresse->result))
{
    $status_kontakt = true;
    $status_kontakt_text = $vollstaendig;
}
else
{
    $status_kontakt = false;
    $status_kontakt_text = $unvollstaendig;
}

$prestudent = new prestudent();
if(!$prestudent->getPrestudenten($person->person_id))
    die('Fehler beim laden des Prestudenten');

$zgv_auswahl = false;

// Überprüfe ZGV pro Prestudent
foreach($prestudent->result as $pre)
{
    if($pre->zgv_code != '' || $pre->zgvmas_code != '' || $pre->zgvdoktor_code != '')
        $zgv_auswahl = true;
}


if($zgv_auswahl)
{
	$status_zgv = true;
	$status_zgv_text = $vollstaendig;
}
else
{
	$status_zgv = false;
	$status_zgv_text = $unvollstaendig;
}

$dokument_help = new dokument();
$dokument_help->getAllDokumenteForPerson($person_id, true);
$akte_person= new akte();
$akte_person->getAkten($person_id);

$missing = false;
$help_array = array();

foreach($akte_person->result as $akte)
{
    $help_array[] = $akte->dokument_kurzbz;
}

foreach($dokument_help->result as $dok)
{
    if(!in_array($dok->dokument_kurzbz, $help_array))
    {
        $missing = true;
    }
}

if($missing)
{
    $status_dokumente = false;
    $status_dokumente_text = $unvollstaendig;
}
else
{
    $status_dokumente = true;
    $status_dokumente_text = $vollstaendig;
}

$konto = new konto();
if($konto->checkKontostand($person_id))
{
	$status_zahlungen = true;
	$status_zahlungen_text = $vollstaendig;
}
else
{
	if($konto->errormsg=='')
	{
		$status_zahlungen = false;
	    $status_zahlungen_text = $unvollstaendig;
	}
	else
	{
		$status_zahlungen = false;
		$status_zahlungen_text = $unvollstaendig . $konto->errormsg;
	}
}

$prestudent = new prestudent();
if(!$prestudent->getPrestudenten($person_id))
	die('Konnte Prestudenten nicht laden');

$status_aufnahmeverfahren = false;
$status_aufnahmeverfahren_text = $unvollstaendig;

foreach($prestudent->result as $row)
{
	if($row->reihungstest_id != '')
	{
		$status_aufnahmeverfahren = true;
		$status_aufnahmeverfahren_text = $vollstaendig;
	}

}

?><!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Bewerbung für einen Studiengang</title>
		<link href="../../include/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<script src="../../include/js/jquery.min.1.11.1.js"></script>
		<script src="../../include/js/bootstrap.min.js"></script>
		<script src="../../include/js/bewerbung.js"></script>
		<script type="text/javascript">
			var activeTab = '<?php echo $active ?>';
		</script>
	</head>
	<body class="bewerbung">
		<div class="container">
			<nav class="navbar navbar-default">

				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bewerber-navigation" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>

				<div class="collapse navbar-collapse" id="bewerber-navigation">
					<ul class="nav navbar-nav">
						<li><a href="#allgemein" aria-controls="allgemein" role="tab" data-toggle="tab">Allgemein <br> &nbsp; </a></li>
						<li><a href="#daten" aria-controls="daten" role="tab" data-toggle="tab">Persönliche Daten <br> <?php echo $status_person_text;?></a></li>
						<li><a href="#kontakt" aria-controls="kontakt" role="tab" data-toggle="tab">Kontaktinformationen <br> <?php echo $status_kontakt_text;?></a></li>
						<li><a href="#dokumente" aria-controls="dokumente" role="tab" data-toggle="tab">Dokumente <br> <?php echo $status_dokumente_text;?></a></li>
						<li><a href="#zahlungen" aria-controls="zahlungen" role="tab" data-toggle="tab">Zahlungen <br> <?php echo $status_zahlungen_text;?></a></li>
						<li><a href="#aufnahme" aria-controls="aufnahme" role="tab" data-toggle="tab">Aufnahmeverfahren <br> <?php echo $status_aufnahmeverfahren_text;?></a></li>
						<li><a href="#abschicken" aria-controls="abschicken" role="tab" data-toggle="tab">Bewerbung abschicken <br> &nbsp; </a></li>
					</ul>
				</div>

			</nav>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane" id="allgemein">
					<h2>Allgemein</h2>
					<p>Wir freuen uns dass Sie sich für einen oder mehrere unserer Studiengänge bewerben. <br><br>
					Bitte füllen Sie das Formular vollständig aus und schicken Sie es danach ab.<br><br>
					<b>Bewerbungsmodus:</b><br>
					<p style="text-align:justify;">Füllen Sie alle Punkte aus. Sind alle Werte vollständig eingetragen, können Sie unter "Bewerbung abschicken" Ihre Bewerbung and die zuständige Assistenz schicken.<br>
					Diese wird sich in den nächsten Tagen bei Ihnen melden.</p>
					<br><br>
					<p><b>Aktuelle Bewerbungen: </b></p>
					<?php

					 // Zeige Stati der aktuellen Bewerbungen an
						$prestudent = new prestudent();
						if(!$prestudent->getPrestudenten($person_id))
							die('Konnte Prestudenten nicht laden'); ?>


						<table class="table">
							<tr>
								<th>Studiengang</th>
								<th>Status</th>
								<th>Datum</th>
								<th>Aktion</th>
								<th>Bewerbungsstatus</th>
							</tr>
							<?php foreach($prestudent->result as $row):
								$stg = new studiengang();
								if(!$stg->load($row->studiengang_kz))
									die('Konnte Studiengang nicht laden');

								$prestudent_status = new prestudent();
								$prestatus_help= ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_kurzbz:'Noch kein Status vorhanden';
								$bewerberstatus =($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')?'bestätigt':'noch nicht bestätigt'; ?>
								<tr>
									<td><?php echo $stg->bezeichnung ?></td>
									<td><?php echo $prestatus_help ?></td>
									<td><?php echo $datum->formatDatum($prestudent_status->datum, 'd.m.Y') ?></td>
									<td></td>
									<td><?php echo $bewerberstatus ?></td>
								</tr>
							<?php endforeach; ?>
						</table>
					<br>
					<button class="btn_weiter btn btn-default" type='button' data-next-tab="daten">Weiter</button>
				</div>
				<div role="tabpanel" class="tab-pane" id="daten">
					<h2>Persönliche Daten</h2>
					<?php

					$nation = new nation();
					$nation->getAll($ohnesperre = true);
					$titelpre = ($person->titelpre != '')?$person->titelpre:'';
					$vorname = ($person->vorname != '')?$person->vorname:'';
					$nachname = ($person->nachname != '')?$person->nachname:'';
					$titelpost = ($person->titelpost != '')?$person->titelpost:'';
					$geburtstag = ($person->gebdatum != '')?$datum->formatDatum($person->gebdatum, 'd.m.Y'):'';
					$gebort =  ($person->gebort != '')?$person->gebort:'';

					$svnr = ($person->svnr != '')?$person->svnr:''; ?>

					<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=daten" class="form-horizontal">
						<div class="form-group">
							<label for="titel_pre" class="col-sm-3 control-label">Titel vorgestellt</label>
							<div class="col-sm-9">
								<input type="text" name="titel_pre" id="titel_pre" value="<?php echo $titelpre ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="vorname" class="col-sm-3 control-label">Vorname*</label>
							<div class="col-sm-9">
								<input type="text" name="vorname" id="vorname" value="<?php echo $vorname ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="nachname" class="col-sm-3 control-label">Nachname*</label>
							<div class="col-sm-9">
								<input type="text" name="nachname" id="nachname" value="<?php echo $nachname ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="titel_post" class="col-sm-3 control-label">Titel nachgestellt</label>
							<div class="col-sm-9">
								<input type="text" name="titel_post" id="titel_post" value="<?php echo $titelpost ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="gebdatum" class="col-sm-3 control-label">Geburtsdatum* (dd.mm.yyyy)</label>
							<div class="col-sm-9">
								<input type="text" name="geburtsdatum" id="gebdatum" value="<?php echo $geburtstag ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="gebort" class="col-sm-3 control-label">Geburtsort</label>
							<div class="col-sm-9">
								<input type="text" name="gebort" id="gebort" value="<?php echo $gebort ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="geburtsnation" class="col-sm-3 control-label">Geburtsnation</label>
							<div class="col-sm-9">
								<select name="geburtsnation" id="geburtsnation" class="form-control">
									<option value="">-- Bitte auswählen -- </option>
									<?php $selected = '';
									foreach($nation->nation as $nat):
										$selected = ($person->geburtsnation == $nat->code) ? 'selected' : ''; ?>
										<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
											<?php echo $nat->kurztext ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="staatsbuergerschaft" class="col-sm-3 control-label">Staatsbürgerschaft*</label>
							<div class="col-sm-9">
								<select name="staatsbuergerschaft" id="staatsbuergerschaft" class="form-control">
									<option value="">-- Bitte auswählen -- </option>";
									<?php $selected = '';
									foreach($nation->nation as $nat):
										$selected = ($person->staatsbuergerschaft == $nat->code) ? 'selected' : ''; ?>
										<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
											<?php echo $nat->kurztext ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="svnr" class="col-sm-3 control-label">Österr. Sozialversicherungsnr</label>
							<div class="col-sm-9">
								<input type="text" name="svnr" id="svnr" value="<?php echo $svnr ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="geschlecht" class="col-sm-3 control-label">Geschlecht</label>
							<div class="col-sm-9">
								<?php
								$geschl_m = ($person->geschlecht == 'm') ? 'checked' : '';
								$geschl_w = ($person->geschlecht == 'w') ? 'checked' : '';
								?>
								m: <input type="radio" name="geschlecht" value="m" <?php echo $geschl_m ?>>
								w: <input type="radio" name="geschlecht" value="w" <?php echo $geschl_w ?>>
							</div>
						</div>
						<input class="btn btn-default" type="submit" value="Speichern" name="btn_person" onclick="return checkPerson();">
						<button class="btn_weiter btn btn-default" type="button" data-next-tab="kontakt">Weiter</button>
					</form>
						<?php echo $message; ?>
				</div>
				<div role="tabpanel" class="tab-pane" id="kontakt">
					<h2>Kontaktinformationen</h2>
					<?php
					$nation = new nation();
					$nation->getAll($ohnesperre=true);

					$kontakt = new kontakt();
					$kontakt->load_persKontakttyp($person->person_id, 'email');
					$email = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';

					$kontakt_t = new kontakt();
					$kontakt_t->load_persKontakttyp($person->person_id, 'telefon');
					$telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';

					$adresse = new adresse();
					$adresse->load_pers($person->person_id);
					$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
					$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
					$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
					$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
					?>


					<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>?active=dokumente" class="form-horizontal">
						<fieldset>
							<legend>Kontakt</legend>
							<div class="form-group">
								<label for="email" class="col-sm-2 control-label">Email*</label>
								<div class="col-sm-10">
									<input type="text" name="email" id="email" value="<?php echo $email ?>" size="32" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="telefonnummer" class="col-sm-2 control-label">Telefonnummer*</label>
								<div class="col-sm-10">
									<input type="text" name="telefonnummer" id="telefonnummer" value="<?php echo $telefon ?>" size="32" class="form-control">
								</div>
							</div>
						</fieldset>

						<fieldset>
							<legend>Adresse</legend>
							<div class="form-group">
								<label for="strasse" class="col-sm-2 control-label">Straße*</label>
								<div class="col-sm-10">
									<input type="text" name="strasse" id="strasse" value="<?php echo $strasse ?>" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="plz" class="col-sm-2 control-label">Postleitzahl*</label>
								<div class="col-sm-10">
									<input type="text" name="plz" id="plz" value="<?php echo $plz ?>" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="ort" class="col-sm-2 control-label">Ort*</label>
								<div class="col-sm-10">
									<input type="text" name="ort" id="ort" value="<?php echo $ort ?>" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="nation" class="col-sm-2 control-label">Nation*</label>
								<div class="col-sm-10">
									<select name="nation" class="form-control">
										<option>--Bitte auswählen --</option>
										<?php
										foreach($nation->nation as $nat):
											$selected = ($adr_nation == $nat->code)?'selected':''; ?>
											<option value="<?php echo $nat->code ?>" <?php echo $selected ?>>
												<?php echo $nat->kurztext ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</fieldset>
						<input class="btn btn-default" type="submit" value="Speichern" name="btn_kontakt" onclick="return checkKontakt();"> &nbsp;
						<button class="btn_weiter btn btn-default" type="button" data-next-tab="dokumente">
							Weiter
						</button>
					</form>
				</div>
				<div role="tabpanel" class="tab-pane" id="dokumente">
					<h2>Dokumente</h2>
					<p>Bitte laden Sie alle vorhandenen Dokumente, die für Ihre Bewerbung relevant sind, über folgenden Link hoch:</p>
					<a href="<?php echo APP_ROOT ?>cis/public/dms_akteupload.php?person_id=<?php echo $person_id ?>"
					   onclick="FensterOeffnen(this.href); return false;">
						Dokumente Upload
					</a>
					<p>Dokumente zum Uploaden:</p>
					<?php
					$dokumente_person = new dokument();
					$dokumente_person->getAllDokumenteForPerson($person_id, true); ?>

					<table class="table table-striped">
						<thead>
							<tr>
								<th width="30%">Name</th>
								<th width="10%">Status</th>
								<th width="10%">Aktion</th>
								<th width="20%"></th>
								<th width="30%">Benötigt für</th>
							</tr>
						</thead>
						<tbody>
					<?php
					foreach($dokumente_person->result as $dok):
						$akte = new akte;
						$akte->getAkten($person_id, $dok->dokument_kurzbz);

						if(count($akte->result)>0)
						{
							$akte_id = isset($akte->result[0]->akte_id)?$akte->result[0]->akte_id:'';

							// check ob status "wird nachgereicht"
							if($akte->result[0]->nachgereicht == true)
							{
								// wird nachgereicht
								$status = '<img title="wird nachgereicht" src="'.APP_ROOT.'skin/images/hourglass.png" width="20px">';
								$nachgereicht_help = 'checked';
								$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:true;'>".$akte->result[0]->anmerkung."</span></form>";
								$aktion = '<a href="'.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=dokumente"><img title="löschen" src="'.APP_ROOT.'skin/images/delete.png" width="20px"></a>';
							}
							else
							{
								$dokument = new dokument();
								if($dokument->load($akte->result[0]->dokument_kurzbz,$prestudent->prestudent_id))
								{
									// Dokument wurde bereits überprüft
									$status = '<img title="abgegeben" src="'.APP_ROOT.'skin/images/true_green.png" width="20px">';
									$nachgereicht_help = '';
									$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help."><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'></form>";
									$aktion = '';
								}
								else
								{
									// Dokument hochgeladen ohne überprüfung der Assistenz
									$status = '<img title="abgegeben" src="'.APP_ROOT.'skin/images/check_black.png" width="20px">';
									$nachgereicht_help = '';
									$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help."><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'></form>";
									$aktion = '<a href="'.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=dokumente"><img title="löschen" src="'.APP_ROOT.'skin/images/delete.png" width="20px"></a>';

								}
							}
						}
						else
						{
							// Dokument fehlt noch
							$status = '<img title="offen" src="'.APP_ROOT.'skin/images/upload.png" width="20px">';
							$aktion = '<img src="'.APP_ROOT.'skin/images/delete.png" width="20px" title="löschen"> <a href="'.APP_ROOT.'cis/public/dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'" onclick="FensterOeffnen(this.href); return false;"><img src="'.APP_ROOT.'skin/images/upload.png" width="20px" title="upload"></a><a href="#" onclick="toggleDiv(\'nachgereicht_'.$dok->dokument_kurzbz.'\');"><img src="'.APP_ROOT.'skin/images/hourglass.png" width="20px" title="wird nachgereicht"></a>';
							$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=dokumente'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht'><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht' class='btn btn-default'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'></form>";

						}

						$ben_stg = new basis_db();
						$qry = "SELECT studiengang_kz FROM public.tbl_dokumentstudiengang
							JOIN public.tbl_prestudent using (studiengang_kz)
							JOIN public.tbl_dokument using (dokument_kurzbz)
							WHERE dokument_kurzbz = ".$ben_stg->db_add_param($dok->dokument_kurzbz)." and person_id =".$ben_stg->db_add_param($person_id, FHC_INTEGER);

						$ben = "";
						if($result = $ben_stg->db_query($qry))
						{
							while($row = $ben_stg->db_fetch_object($result))
							{
								if($ben!='')
									$ben.=', ';

								$stg = new studiengang();
								$stg->load($row->studiengang_kz);

								$ben .= $stg->bezeichnung;
							}
						} ?>

						<tr>
							<td><?php echo $dok->bezeichnung ?></td>
							<td><?php echo $status ?></td>
							<td><?php echo $aktion ?></td>
							<td><?php echo $div ?></td>
							<td><?php echo $ben ?></td>
						</tr>
					<?php endforeach; ?>
						</tbody>
					</table>
							<br>
					<h2>Status</h2>
					<table class="table">
						<tr>
							<td>
								<img title="offen" src="<?php echo APP_ROOT ?>skin/images/upload.png" width="20px">
							</td>
							<td>Dokument noch nicht abgegeben (offen)</td>
						</tr>
						<tr>
							<td>
								<img title="offen" src="<?php echo APP_ROOT ?>skin/images/check_black.png" width="20px">
							</td>
							<td>Dokument wurde abgegeben aber noch nicht überprüft</td>
						</tr>
						<tr>
							<td>
								<img title="offen" src="<?php echo APP_ROOT ?>skin/images/hourglass.png" width="20px">
							</td>
							<td>Dokument wird nachgereicht </td>
						</tr>
						<tr>
							<td>
								<img title="offen" src="<?php echo APP_ROOT ?>skin/images/true_green.png" width="20px">
							</td>
							<td>Dokument wurde bereits überprüft</td>
						</tr>
					</table>
					<br><?php echo $message ?>
				</div>

				<div role="tabpanel" class="tab-pane" id="zahlungen">
					<?php
				//	$sprache = getSprache();
					$sprache=DEFAULT_LANGUAGE;
					$p = new phrasen($sprache);
				//	$uid=get_uid();
					$datum_obj = new datum();
					$studiengang = new studiengang();
					$studiengang->getAll();

					$stg_arr = array();
					foreach ($studiengang->result as $row)
						$stg_arr[$row->studiengang_kz]=$row->kuerzel;

					//$benutzer = new benutzer();
					//if(!$benutzer->load($uid))
					//	die('Benutzer wurde nicht gefunden');

					echo '<h2>'.$p->t('tools/zahlungen').' - '.$person->vorname.' '.$person->nachname.'</h2>';

					$konto = new konto();
					$konto->getBuchungstyp();
					$buchungstyp = array();

					foreach ($konto->result as $row)
						$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;

					$konto = new konto();
					$konto->getBuchungen($person_id);
					if(count($konto->result)>0): ?>
						<br><br>
						<table class="table table-striped">
							<thead>
								<tr>
									<th><?php echo $p->t('global/datum') ?></th>
									<th><?php echo $p->t('tools/zahlungstyp') ?></th>
									<th><?php echo $p->t('lvplan/stg') ?></th>
									<th><?php echo $p->t('global/studiensemester') ?></th>
									<th><?php echo $p->t('tools/buchungstext') ?></th>
									<th><?php echo $p->t('tools/betrag') ?></th>
									<th><?php echo 'Zahlungsinformation' ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($konto->result as $row):
									$betrag = $row['parent']->betrag;

									if(isset($row['childs']))
									{
										foreach ($row['childs'] as $row_child)
										{
											$betrag += $row_child->betrag;
										}
									}

									if($betrag<0)
									{
										$class = 'danger';
									}
									elseif($betrag>0)
									{
										$class = 'success';
									}
									else
									{
										$class = '';
									}
									?>
									<tr class="<?php echo $class ?>">
										<td><?php echo date('d.m.Y',$datum_obj->mktime_fromdate($row['parent']->buchungsdatum)) ?></td>
										<td><?php echo $buchungstyp[$row['parent']->buchungstyp_kurzbz] ?></td>
										<td><?php echo $stg_arr[$row['parent']->studiengang_kz] ?></td>
										<td><?php echo $row['parent']->studiensemester_kurzbz ?></td>

										<td nowrap><?php echo $row['parent']->buchungstext ?></td>
										<td align="right" nowrap><?php echo ($betrag<0?'-':($betrag>0?'+':'')).sprintf('%.2f',abs($row['parent']->betrag)) ?> €</td>
										<td align="center">
										<?php if($betrag==0 && $row['parent']->betrag<0): ?>
											bezahlt
										<?php else: ?>
												<a onclick="window.open('zahlungen_details.php?buchungsnr=<?php echo $row['parent']->buchungsnr ?>',
															'Zahlungsdetails',
															'height=320,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');
													return false;" href="#">
														<?php echo $p->t('tools/offen') ?>
												</a>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else:
						echo $p->t('tools/keineZahlungenVorhanden');
					endif; ?>
				</div>

				<div role="tabpanel" class="tab-pane" id="aufnahme">
					<h2>Aufnahmeverfahren</h2>
					<br>
					<p>Sie können sich für folgende Aufnahmeverfahren anmelden: </p>
					<?php

					$prestudent = new prestudent();
					if(!$prestudent->getPrestudenten($person_id))
						die('Konnte Prestudenten nicht laden');

					foreach($prestudent->result as $row)
					{
						$reihungstest = new reihungstest();
						if(!$reihungstest->getStgZukuenftige($row->studiengang_kz))
							echo "Fehler aufgetreten";

						$stg = new studiengang();
						$stg->load($row->studiengang_kz); ?>
						<h3>Studiengang <?php echo $stg->bezeichnung ?></h3>
						<table class="reihungstest table">
						<tr>
							<th>angemeldet / Plätze</th>
							<th>Datum</th>
							<th>Uhrzeit</th>
							<th>Ort</th>
							<th title="<?php echo $row->studiengang_kz ?>">Studiengang</th>
							<th>&nbsp;</th>
						</tr>
						<?php
						foreach($reihungstest->result as $rt)
						{
							$teilnehmer_anzahl = $reihungstest->getTeilnehmerAnzahl($rt->reihungstest_id);
							$spalte1 = $rt->max_teilnehmer ? $teilnehmer_anzahl . '/' . $rt->max_teilnehmer : '';

							// bereits angenommen
							if($row->reihungstest_id == $rt->reihungstest_id)
							{
								$rt_help = true; ?>
								<tr style='background-color:lightgrey;'>
									<td><?php echo $spalte1 ?></td>
									<td><?php echo $rt->datum ?></td>
									<td><?php echo $rt->uhrzeit ?></td>
									<td><?php echo $rt->ort_kurzbz ?></td>
									<td><?php echo $stg->bezeichnung ?></td>
									<td>
										<input type='button' name='btn_stg'
											value='Stornieren'
											onclick='location.href="<?php echo $_SERVER['PHP_SELF'] ?>?active=aufnahme&rt_id=<?php echo $rt->reihungstest_id ?>&pre=<?php echo $row->prestudent_id ?>&delete"'>
									</td>
								</tr>
								<?php
							}
							else
							{
								?>
								<tr>
									<td><?php echo $spalte1 ?></td>
									<td><?php echo $rt->datum ?></td>
									<td><?php echo $rt->uhrzeit ?></td>
									<td><?php echo $rt->ort_kurzbz ?></td>
									<td><?php echo $stg->bezeichnung ?></td>
									<td>
										<input type='button' name='btn_stg'
											<?php echo isset($rt->max_teilnehmer) && $teilnehmer_anzahl >= $rt->max_teilnehmer ? 'disabled' : '' ?>
											value='Anmelden'
											onclick='location.href="<?php echo $_SERVER['PHP_SELF'] ?>?active=aufnahme&rt_id=<?php echo $rt->reihungstest_id ?>&pre=<?php echo $row->prestudent_id ?>"'>
									</td>
								</tr>
								<?php
							}
						}
						?>
						</table><br>
						<?php
					}

					?>
				</div>

				<div role="tabpanel" class="tab-pane" id="abschicken">
					<h2>Bewerbung abschicken</h2>
					<p>Haben Sie alle Daten korrekt ausgefüllt bzw. alle Dokumente auf das System hochgeladen, können Sie Ihre Bewerbung abschicken.<br>
						Die jeweilige Studiengangsassistenz wird sich in den folgenden Tagen, bezüglich der Bewerbung, bei Ihnen Melden.
						<br><br>
						Bitte überprüfen Sie nochmals Ihre Daten.<br>
						Um Ihre Bewerbung jetzt abzuschließen klicken auf folgenden Link:
					</p><br><br>
					<?php

					$disabled = 'disabled';
					if($status_person == true && $status_kontakt == true && $status_dokumente == true && $status_zahlungen == true && $status_aufnahmeverfahren == true)
						$disabled = '';

					$prestudent_help= new prestudent();
					$prestudent_help->getPrestudenten($person->person_id);
					$stg = new studiengang();


					foreach($prestudent_help->result as $prest):
						$stg->load($prest->studiengang_kz); ?>
						<br>Bewerbung abschicken für <?php echo $stg->bezeichnung ?><br>

						<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
							<input class="btn btn-default" type="submit" value="Bewerbung abschicken (<?php echo $stg->kurzbzlang ?>)" name="btn_bewerbung_abschicken" <?php echo $disabled ?>>
							<input type="hidden" name="prestudent_id" value="<?php echo $prest->prestudent_id ?>">
						</form>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	</body>
</html>

<?php

// sendet eine Email an die Assistenz dass die Bewerbung abgeschlossen ist
function sendBewerbung($prestudent_id)
{
    global $person_id;

    $person = new person();
    $person->load($person_id);

    $prestudent = new prestudent();
    if(!$prestudent->load($prestudent_id))
        die('Konnte Prestudent nicht laden');

    $studiengang = new studiengang();
    if(!$studiengang->load($prestudent->studiengang_kz))
        die('Konnte Studiengang nicht laden');

    $email = 'Es hat sich ein Student für Ihren Studiengang beworben. <br>';
    $email.= 'Name: '.$person->vorname.' '.$person->nachname.'<br>';
    $email.= 'Studiengang: '.$studiengang->bezeichnung.'<br><br>';
    $email.= 'Für mehr Details, verwenden Sie die Personenansicht im FAS.';

    $mail = new mail($studiengang->email, 'no-reply', 'Bewerbung '.$person->vorname.' '.$person->nachname, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if(!$mail->send())
		return false;
	else
		return true;

}
