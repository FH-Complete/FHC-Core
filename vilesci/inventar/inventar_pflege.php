<?php
/* Copyright (C) 2008 Technikum-Wien
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
// ---------------- Vilesci Include Dateien einbinden
	$path='../../';

	include_once($path.'config/vilesci.config.inc.php');
  	require_once($path.'include/functions.inc.php');
	require_once($path.'include/benutzerberechtigung.class.php');
	require_once($path.'include/person.class.php');
	require_once($path.'include/mitarbeiter.class.php');
  	require_once($path.'include/ort.class.php');
	require_once($path.'include/studiengang.class.php');
  	require_once($path.'include/organisationseinheit.class.php');
  	require_once($path.'include/wawi.class.php');
  	require_once($path.'include/betriebsmittel.class.php');
  	require_once($path.'include/betriebsmitteltyp.class.php');
  	require_once($path.'include/betriebsmittelstatus.class.php');
  	require_once($path.'include/betriebsmittel_betriebsmittelstatus.class.php');
	require_once($path.'include/betriebsmittelperson.class.php');
	
	if (!$uid = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
// ------------------------------------------------------------------------------------------
// Variable Initialisieren
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$berechtigung_kurzbz='wawi/inventar';
	$recht=false;
	$schreib_recht=false;
	$default_status_vorhanden='vorhanden';
// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------

  	$betriebsmittel_id=trim((isset($_REQUEST['betriebsmittel_id']) ? $_REQUEST['betriebsmittel_id']:''));

  	$beschreibung=trim((isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung']:''));
  	$betriebsmitteltyp=trim((isset($_REQUEST['betriebsmitteltyp']) ? $_REQUEST['betriebsmitteltyp']:''));
  	$nummer=trim((isset($_REQUEST['nummer']) ? $_REQUEST['nummer']:''));
  	$reservieren=trim((isset($_REQUEST['reservieren']) ?$_REQUEST['reservieren']:false));
  	$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
	$oe_kurzbz=trim((isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz']:''));
	$person_id=trim((isset($_REQUEST['person_id']) ? $_REQUEST['person_id']:''));
	$besteller=trim((isset($_REQUEST['besteller']) ? $_REQUEST['besteller']:''));
  	$hersteller=trim((isset($_REQUEST['hersteller']) ? $_REQUEST['hersteller']:''));
  	$seriennummer=trim((isset($_REQUEST['seriennummer']) ? $_REQUEST['seriennummer']:''));
	$bestellung_id=trim(isset($_REQUEST['bestellung_id'])?$_REQUEST['bestellung_id']:'');
	$bestelldetail_id=trim(isset($_REQUEST['bestelldetail_id'])?$_REQUEST['bestelldetail_id']:'');
	$bestellung_id_old=trim(isset($_REQUEST['bestellung_id_old'])?$_REQUEST['bestellung_id_old']:null);
	$bestelldetail_id_old=trim(isset($_REQUEST['bestelldetail_id_old'])?$_REQUEST['bestelldetail_id_old']:null);
  	$verwendung=trim(isset($_REQUEST['verwendung']) ? $_REQUEST['verwendung']:'');
  	$anmerkung=trim(isset($_REQUEST['anmerkung']) ? $_REQUEST['anmerkung']:'');
  	$betriebsmittelstatus_kurzbz=trim((isset($_REQUEST['betriebsmittelstatus_kurzbz']) ? $_REQUEST['betriebsmittelstatus_kurzbz']:$default_status_vorhanden));
	$firma_id=trim(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:'');
	$bestellnr=trim(isset($_REQUEST['bestellnr'])?$_REQUEST['bestellnr']:'');

  	$afa=trim(isset($_REQUEST['afa']) ? $_REQUEST['afa']:5);
  	$leasing_bis=trim(isset($_REQUEST['leasing_bis']) ? $_REQUEST['leasing_bis']:'');

	$jahr_monat=trim(isset($_REQUEST['jahr_monat']) ? $_REQUEST['jahr_monat']:'');
  	$inventur_jahr=trim(isset($_REQUEST['inventur_jahr']) ? $_REQUEST['inventur_jahr']:'');

  	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);

  	$ajax=trim(isset($_REQUEST['ajax']) ?$_REQUEST['ajax']:false);
  	$work=trim(isset($_REQUEST['work']) ?$_REQUEST['work']:false);
  	$anzahl=trim(isset($_REQUEST['anzahl']) ?$_REQUEST['anzahl']:1);

  	$vorlage=trim(isset($_REQUEST['vorlage']) ?$_REQUEST['vorlage']:'true');
  	$vorlage=trim((isset($_REQUEST['betriebsmittel_id']) ?'false':$vorlage));

// ------------------------------------------------------------------------------------------
// Berechtigung
// ------------------------------------------------------------------------------------------
	$oBenutzerberechtigung = new benutzerberechtigung();
	$oBenutzerberechtigung->errormsg='';
	$oBenutzerberechtigung->berechtigungen=array();
	// read Berechtigung
	if (!$oBenutzerberechtigung->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
	// Pruefen ob Schreibrechte (Anzeigen der Aenderungsmoeglichkeit)
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'su'))
		$schreib_recht=true;


	if (!$schreib_recht)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------

	$oOrt = new ort();
	$oOrt->result=array();
	$oOrt->errormsg='';

	$oPerson = new person();
	$oPerson->result=array();
	$oPerson->errormsg='';

	$oStudiengang = new studiengang();
	$oStudiengang->result=array();
	$oStudiengang->errormsg='';

	$oOrganisationseinheit = new organisationseinheit();
	$oOrganisationseinheit->result=array();
	$oOrganisationseinheit->errormsg='';

	$oWawi = new wawi();
	$oWawi->result=array();
	$oWawi->debug=$debug;
	$oWawi->errormsg='';

	$oBetriebsmitteltyp = new betriebsmitteltyp();
	$oBetriebsmitteltyp->result=array();
	$oBetriebsmitteltyp->debug=$debug;
	$oBetriebsmitteltyp->errormsg='';

	$oBetriebsmittel = new betriebsmittel();
	$oBetriebsmittel->result=array();
	$oBetriebsmittel->debug=$debug;
	$oBetriebsmittel->errormsg='';

	$oBetriebsmittelstatus = new betriebsmittelstatus();
	$oBetriebsmittelstatus->result=array();
	$oBetriebsmittelstatus->debug=$debug;
	$oBetriebsmittelstatus->errormsg='';

	$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
	$oBetriebsmittel_betriebsmittelstatus->result=array();
	$oBetriebsmittel_betriebsmittelstatus->debug=$debug;
	$oBetriebsmittel_betriebsmittelstatus->errormsg='';

	$oBetriebsmittelperson = new betriebsmittelperson();
	$oBetriebsmittelperson->result=array();
	$oBetriebsmittelperson->debug=$debug;
	$oBetriebsmittelperson->errormsg='';

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------

	// Typtable
	if (!$oBetriebsmitteltyp->getAll())
		$errormsg[]=$oBetriebsmitteltyp->errormsg;
	$resultBetriebsmitteltyp=$oBetriebsmitteltyp->result;

	// Statustable
	if (!$rows=$oBetriebsmittelstatus->getAll())
		$errormsg[]=$oBetriebsmittelstatus->errormsg;
	$resultBetriebsmittelstatus=$oBetriebsmittelstatus->result;

	// Vorlagedaten lesen aus Betriebsmittel
	if ($nummer!='' && empty($work) )
	{
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->errormsg='';
			if ($oBetriebsmittel->load_nummer($nummer))
			{
				if (is_array($oBetriebsmittel->result) && isset($oBetriebsmittel->result[0]) && isset($oBetriebsmittel_betriebsmittelstatus->result[0]->betriebsmittelstatus_kurzbz))
					$oBetriebsmittel->result=$oBetriebsmittel->result[0];
				$betriebsmittel_id=$oBetriebsmittel->result->betriebsmittel_id;
			}
			else
				$errormsg[]=$oBetriebsmittel->errormsg;
	}

	// Vorlagedaten lesen aus Betriebsmittel
	if ($betriebsmittel_id!='' && empty($work) )
	{
		$oBetriebsmittel->result=array();
		$oBetriebsmittel->errormsg='';
		if ($oBetriebsmittel->load($betriebsmittel_id))
		{
		  	$anzahl=count($oBetriebsmittel->result);

			foreach ($oBetriebsmittel->result as $key => $value)
				$$key=$value;

			$bestellung_id_old=$bestellung_id;
			$bestelldetail_id_old=$bestelldetail_id;

			$oBetriebsmittel_betriebsmittelstatus->result=array();
			$oBetriebsmittel_betriebsmittelstatus->errormsg='';
			if ($oBetriebsmittel_betriebsmittelstatus->load_last_betriebsmittel_id($betriebsmittel_id))
			{
				if (isset($oBetriebsmittel_betriebsmittelstatus->result->betriebsmittelstatus_kurzbz))
					$betriebsmittelstatus_kurzbz=$oBetriebsmittel_betriebsmittelstatus->result->betriebsmittelstatus_kurzbz;
				else if (is_array($oBetriebsmittel_betriebsmittelstatus->result) && isset($oBetriebsmittel_betriebsmittelstatus->result[0]) && isset($oBetriebsmittel_betriebsmittelstatus->result[0]->betriebsmittelstatus_kurzbz))
					$betriebsmittelstatus_kurzbz=$oBetriebsmittel_betriebsmittelstatus->result[0]->betriebsmittelstatus_kurzbz;
				else
					$betriebsmittelstatus_kurzbz=$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz;
			}
			else
				$errormsg[]=$oBetriebsmittel_betriebsmittelstatus->errormsg;
			// suchen eine Person zum Betriebsmittel  - Entliehen an eine Person
			$oBetriebsmittelperson->result=array();
			$oBetriebsmittelperson->errormsg='';
			if ($oBetriebsmittelperson->load_betriebsmittelpersonen($betriebsmittel_id))
			{
				if (isset($oBetriebsmittelperson->result->retouram) )
					$person_id=($oBetriebsmittelperson->result->retouram?'':$oBetriebsmittelperson->result->person_id);
				else if (is_array($oBetriebsmittelperson->result) && isset($oBetriebsmittelperson->result[0]) && isset($oBetriebsmittelperson->result[0]->retouram) )
					$person_id=($oBetriebsmittelperson->result[0]->retouram?'':$oBetriebsmittelperson->result[0]->person_id);
				else if (empty($oBetriebsmittelperson->retouram))
					$person_id=$oBetriebsmittelperson->person_id;
			}
			else
				$errormsg[]=$oBetriebsmittelperson->errormsg;

		}
		else
			$errormsg[]=$oBetriebsmittel->errormsg;
	}

	// Vorlagedaten lesen
	if ($bestellung_id!='' && empty($work)
	&& ($bestellung_id!=$bestellung_id_old || $bestelldetail_id!=$bestelldetail_id_old )  )
	{
		$studiengang_kurzbzlang=array();
		$studiengang_kuerzel=array();
		$oStudiengang->result=array();
		$oStudiengang->errormsg='';
		if ($oStudiengang->getAll())
		{
			reset($oStudiengang->result);
			foreach($oStudiengang->result AS $key => $value)
			{
				$value->kurzbzlang=trim($value->kurzbzlang);
				$studiengang_kurzbzlang[$value->kurzbzlang]=$value;
			}

			reset($oStudiengang->result);
			foreach($oStudiengang->result AS $key => $value)
			{
				$value->kuerzel=trim($value->kuerzel);
				$studiengang_kuerzel[$value->kuerzel]=$value;
			}
		}
		else if ($oStudiengang->errormsg)
			$errormsg[]=$oStudiengang->errormsg;

		// Bestellposition
		if ($bestelldetail_id)
		{
			if (!$oWawi->bestellpositionen($bestellung_id,null,$bestelldetail_id))
				$errormsg[]=$oWawi->errormsg;
			if (!isset($oWawi->result[0]) && !$oWawi->bestellpositionen($bestellung_id,null,null,$bestelldetail_id))
				$errormsg[]=$oWawi->errormsg;
			if (isset($oWawi->result[0]->bestelldetail_id))
				$bestelldetail_id=$oWawi->result[0]->bestelldetail_id;
		}
		// Bestellung
		else
		{
			if (!$oWawi->bestellung(null,null,$bestellung_id))
				$errormsg[]=$oWawi->errormsg;
		}
		if (!isset($oWawi->result[0]))
			$errormsg[]='Bestelldaten sind falsch!';

		// Bestelldatenverarbeiten
		for ($i=0;$i<count($oWawi->result);$i++)
		{

			$beschreibung=trim($oWawi->result[$i]->titel);
			if (isset($oWawi->result[$i]->beschreibung))
				$beschreibung.=($beschreibung?"\n":'').trim($oWawi->result[$i]->beschreibung).' '.trim($oWawi->result[$i]->artikelnr);

		  	$verwendung=trim($oWawi->result[$i]->kostenstelle_bezeichnung);
			if (isset($oWawi->result[$i]->konto_beschreibung))
				$verwendung.=($verwendung?"\n":'').trim($oWawi->result[$i]->konto_beschreibung);


		  	$anmerkung=trim($oWawi->result[$i]->bemerkungen);
		  	$hersteller=trim($oWawi->result[$i]->firmenname);

		  	$anzahl=trim(isset($oWawi->result[$i]->menge)?$oWawi->result[$i]->menge:$anzahl);

			$wawi=$oWawi->result[$i];
			// StgKz leer - pruefen ob in den Kostenstellen das StgKZ belegt ist
			if ((!isset($wawi->studiengang_id) || !$wawi->studiengang_id)
			&& isset($wawi->studiengang_kostenstelle_studiengang_id))
			{
					$wawi->studiengang_id=$wawi->studiengang_kostenstelle_studiengang_id;
					$wawi->studiengang_bezeichnung=$wawi->studiengang_kostenstelle_bezeichnung;
					$wawi->studiengang_kurzzeichen=$wawi->studiengang_kostenstelle_kurzzeichen;
			}
			$wawi->studiengang_kurzzeichen=trim($wawi->studiengang_kurzzeichen);
			$wawi->studiengang_bezeichnung=trim($wawi->studiengang_bezeichnung);

			if (isset($wawi->besteller) )
			  	$besteller=$wawi->besteller;

			// In Studiengangarray suchen mit Key = Kurzzeichen
			if (isset($studiengang_kurzbzlang[$wawi->studiengang_kurzzeichen]) && isset($studiengang_kurzbzlang[$wawi->studiengang_kurzzeichen]->oe_kurzbz) )
			{
				$wawi->oe_kurzbz=trim($studiengang_kurzbzlang[$wawi->studiengang_kurzzeichen]->oe_kurzbz);
				if (empty($wawi->studiengang_bezeichnung))
					$wawi->studiengang_bezeichnung=$studiengang_kurzbzlang[$wawi->studiengang_kurzzeichen]->bezeichnung;
				if (empty($wawi->studiengang_kurzzeichen))
					$wawi->studiengang_kurzzeichen=$studiengang_kurzbzlang[$wawi->studiengang_kurzzeichen]->kurzzeichen;
				$anmerkung.=($anmerkung?"\n":'').'Studiengang: '.$wawi->studiengang_bezeichnung .' '.$wawi->oe_kurzbz;
			}
			elseif (isset($studiengang_kurzbzlang[$wawi->studiengang_bezeichnung]) && isset($studiengang_kurzbzlang[$wawi->studiengang_bezeichnung]->oe_kurzbz) )
			{
				$wawi->oe_kurzbz=trim($studiengang_kurzbzlang[$wawi->studiengang_bezeichnung]->oe_kurzbz);
				if (empty($wawi->studiengang_bezeichnung))
					$wawi->studiengang_bezeichnung=$studiengang_kurzbzlang[$wawi->studiengang_bezeichnung]->bezeichnung;
				if (empty($wawi->studiengang_kurzzeichen))
					$wawi->studiengang_kurzzeichen=$studiengang_kurzbzlang[$wawi->studiengang_bezeichnung]->kurzzeichen;
				$anmerkung.=($anmerkung?"\n":'').'Studiengang: '.$wawi->studiengang_bezeichnung .' '.$wawi->oe_kurzbz;
			}
			elseif (isset($studiengang_kuerzel[$wawi->studiengang_kurzzeichen]) && isset($studiengang_kuerzel[$wawi->studiengang_kurzzeichen]->oe_kurzbz) )
			{
				$wawi->oe_kurzbz=trim($studiengang_kuerzel[$wawi->studiengang_kurzzeichen]->oe_kurzbz);
				if (empty($wawi->studiengang_bezeichnung))
					$wawi->studiengang_bezeichnung=$studiengang_kuerzel[$wawi->studiengang_kurzzeichen]->bezeichnung;
				if (empty($wawi->studiengang_kurzzeichen))
					$wawi->studiengang_kurzzeichen=$studiengang_kuerzel[$wawi->studiengang_kurzzeichen]->kurzzeichen;
				$anmerkung.=($anmerkung?"\n":'').'Studiengang: '.$wawi->studiengang_bezeichnung .' '.$wawi->oe_kurzbz;
			}
			elseif (isset($studiengang_kuerzel[$wawi->studiengang_bezeichnung]) && isset($studiengang_kuerzel[$wawi->studiengang_bezeichnung]->oe_kurzbz) )
			{
				$wawi->oe_kurzbz=trim($studiengang_kuerzel[$wawi->studiengang_bezeichnung]->oe_kurzbz);
				if (empty($wawi->studiengang_bezeichnung))
					$wawi->studiengang_bezeichnung=$studiengang_kuerzel[$wawi->studiengang_bezeichnung]->bezeichnung;
				if (empty($wawi->studiengang_kurzzeichen))
					$wawi->studiengang_kurzzeichen=$studiengang_kuerzel[$wawi->studiengang_bezeichnung]->kurzzeichen;
				$anmerkung.=($anmerkung?"\n":'').'Studiengang: '.$wawi->studiengang_bezeichnung .' '.$wawi->oe_kurzbz;
			}
			elseif ($oOrganisationseinheit->load($wawi->studiengang_bezeichnung))
			{
				$wawi->oe_kurzbz=trim($wawi->studiengang_bezeichnung);
				$anmerkung.=($anmerkung?"\n":'').'Studiengang: '.$wawi->studiengang_bezeichnung;
			}
			else
				$anmerkung.=($anmerkung?"\n":'').'WAWI Stg.: '.$wawi->studiengang_id.' '.$wawi->studiengang_kurzzeichen.', '.$wawi->studiengang_bezeichnung;

			if (!$oe_kurzbz)
				$oe_kurzbz=(isset($wawi->oe_kurzbz) && $wawi->oe_kurzbz?$wawi->oe_kurzbz:'etw');
		}
	}

// ------------------------------------------------------------------------------------------
// HTML Output
// ------------------------------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Inventar - Neuanlage</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="<?php echo $path;?>skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/jquery.css" rel="stylesheet" type="text/css">
		
		<script src="<?php echo $path;?>include/js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery-ui.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.min.js" type="text/javascript">		
		<script src="<?php echo $path;?>include/js/jquery.barcode.0.3.js" type="text/javascript"></script>		

	</head>
	<body>
		<h1 title="Anwender:<?php echo $uid ?>">&nbsp;Inventar - Neuanlage&nbsp;</h1>
	    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">

		<fieldset>
			<legend>Vorlage&nbsp;&nbsp;&nbsp;Anzahl:
			<select  id="anzahl" name="anzahl" onchange="document.sendform.submit();">
				<?php
					for ($i=1;$i<100 ;$i++)
						echo '<option '.($anzahl==$i?' selected="selected" ':'').' value="'.$i.'">'.$i.'&nbsp;</option>';
				?>
			</select>
		</legend>

		<div id="container" style="display:<?php echo ($vorlage && $vorlage!='false'?'block':'none'); ?>;">
			<table class="navbar">
			<tr>
				<td>
					<table class="navbar">
						<tr>

							<td>&nbsp;<label for="bestellung_id">Bestellung ID</label>&nbsp;
								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="bestellung_id" name="bestellung_id" size="10" maxlength="41" value="<?php echo $bestellung_id;?>">
								<script type="text/javascript" language="JavaScript1.2">				
									function formatItem(row) 
									{
									    return row[0] + " <li>" + row[1] + "</li> ";
									}
									$(document).ready(function() 
									{
										  $('#bestellung_id').autocomplete('inventar_autocomplete.php', 
										  {
											minChars:5,
											matchSubset:1,matchContains:1,
											width:500,
											formatItem:formatItem,
											extraParams:{'work':'wawi_bestellung_id'
														,'oe_kurzbz':$("#oe_kurzbz").val()
														,'hersteller':$("#hersteller").val()}
										  });
								  });
								</script>
								<input style="display:none" id="bestellung_id_old" name="bestellung_id_old" value="<?php echo $bestellung_id;?>">
							</td>
							<td>&nbsp;<label for="bestelldetail_id">Bestelldetail ID</label>&nbsp;
								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="bestelldetail_id" name="bestelldetail_id" size="6" maxlength="41" value="<?php echo $bestelldetail_id;?>">
								<script type="text/javascript" language="JavaScript1.2">
									function formatItem(row) 
									{
									    return row[0] + " <li>" + row[1] + "</li> ";
									}
									$(document).ready(function() 
									{
										  $('#bestelldetail_id').autocomplete('inventar_autocomplete.php', 
										  {
											minChars:1,
											matchSubset:1,matchContains:1,
											width:500,
											formatItem:formatItem,
											extraParams:{'work':'wawi_bestelldetail_id'
														,'bestellung_id':$("#bestellung_id").val()
														}
										  });
								  });
								</script>
								<input style="display:none" id="bestelldetail_id_old" name="bestelldetail_id_old" value="<?php echo $bestelldetail_id;?>">
							</td>

							<td>&nbsp;<label for="hersteller">Hersteller</label>&nbsp;
							<input id="hersteller" name="hersteller" type="text" size="35" maxlength="120" value="<?php echo $hersteller;?>">
									<script type="text/javascript" language="JavaScript1.2">
									function formatItem(row) 
									{
									    return row[0] + " <li>" + row[1] + "</li> ";
									}
									$(document).ready(function() 
									{
										  $('#hersteller').autocomplete('inventar_autocomplete.php', 
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:400,
											formatItem:formatItem,
											extraParams:{'work':'hersteller' }
										  });
								  });
								</script>
							</td>
						</tr>
					</table>
					<table class="navbar">
						<tr>
							<td>&nbsp;<label for="betriebsmitteltyp">Inventartyp</label>&nbsp;
								<select id="betriebsmitteltyp" name="betriebsmitteltyp">
										<?php
										for ($i=0;$i<count($resultBetriebsmitteltyp) ;$i++)
										{
											if ($resultBetriebsmitteltyp[$i]->betriebsmitteltyp)
												echo '<option '.($betriebsmitteltyp==$resultBetriebsmitteltyp[$i]->betriebsmitteltyp?' selected="selected" ':'').' value="'.$resultBetriebsmitteltyp[$i]->betriebsmitteltyp.'">'.($resultBetriebsmitteltyp[$i]->beschreibung=='NULL' || empty($resultBetriebsmitteltyp[$i]->beschreibung)?$resultBetriebsmitteltyp[$i]->betriebsmitteltyp:$resultBetriebsmitteltyp[$i]->beschreibung).'&nbsp;</option>';
										}
										?>
								</select>
							</td>
							<td>&nbsp;<label for="betriebsmittelstatus_kurzbz">Status</label>&nbsp;
									<select id="betriebsmittelstatus_kurzbz" name="betriebsmittelstatus_kurzbz" >
										  <?php
											for ($i=0;$i<count($resultBetriebsmittelstatus) ;$i++)
											{
												if ($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)
													echo '<option '.($betriebsmittelstatus_kurzbz==$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
											}
											?>
									</select>
							</td>

							<td>&nbsp;<label for="ort_kurzbz">Ort</label>&nbsp;
								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="ort_kurzbz" name="ort_kurzbz" size="10" maxlength="20" value="<?php echo $ort_kurzbz;?>">
									<script type="text/javascript" language="JavaScript1.2">
									function formatItem(row) 
									{
									    return row[0] + " <li>" + row[1] + "</li> ";
									}
									$(document).ready(function() 
									{
										  $('#ort_kurzbz').autocomplete('inventar_autocomplete.php', 
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:300,
											formatItem:formatItem,
											extraParams:{'work':'ort' }
										  });
								  });
								</script>
							<?php
						// Ort
								$ort_kurzbz=trim($ort_kurzbz);
								$oOrt->errormsg='';
								$oOrt->result=array();
								if ($ort_kurzbz && !$oOrt->load($ort_kurzbz))
									$errormsg[]=$oOrt->errormsg;
								else if ($ort_kurzbz)
									echo trim(($oOrt->bezeichnung && $oOrt->bezeichnung!='NULL'?$oOrt->bezeichnung:'')).'&nbsp;'.($oOrt->aktiv==true || $oOrt->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" >':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv">');
								$oOrt->result=array();
							?>
							</td>
						</tr>
					</table>

					<table class="navbar">
						<tr>
							<td>&nbsp;<label for="oe_kurzbz">Organisation</label>&nbsp;
								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="oe_kurzbz" name="oe_kurzbz" size="13" maxlength="14" value="<?php echo $oe_kurzbz;?>">
								<script type="text/javascript" language="JavaScript1.2">
									function formatItem(row) 
									{
									    return row[0] + " <li>" + row[1] + "</li> ";
									}
									$(document).ready(function() 
									{
										  $('#oe_kurzbz').autocomplete('inventar_autocomplete.php', 
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:400,
											formatItem:formatItem,
											extraParams:{'work':'organisationseinheit' }
										  });
								  });
							</script>
							
						<?php
						// Organisation
								$oe_kurzbz=trim($oe_kurzbz);
								$oOrganisationseinheit->errormsg='';
								$oOrganisationseinheit->result=array();
								if ($oe_kurzbz && !$oOrganisationseinheit->load($oe_kurzbz))
									$errormsg[]=$oOrganisationseinheit->errormsg;
								else if ($oe_kurzbz)
									echo (isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung ?$oOrganisationseinheit->organisationseinheittyp_kurzbz.' '.$oOrganisationseinheit->bezeichnung:$resultBetriebsmittel[$pos]->oe_kurzbz);
								$oOrganisationseinheit->result=array();
							?>
						</td>
								<?php
									$personen_namen='';
									if ($person_id)
									{
											if (!$oPerson = new person($person_id))
											{
												$personen_namen=$oPerson->errormsg;
											}	
											else if ($oPerson->nachname)
												$personen_namen=$oPerson->anrede.($oPerson->titelpre?'&nbsp;'.$oPerson->titelpre:'').'&nbsp;'.$oPerson->vorname.'&nbsp;'.$oPerson->nachname.'&nbsp;'.($oPerson->aktiv==true || $oPerson->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv">':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv">');
											else
											{
												$oPerson->errormsg='';
												if (!$oPerson->getTab($person_id))
													$personen_namen=$oPerson->errormsg;
												else if (isset($oPerson->personen[0]->nachname))
												{
													$person_id=$oPerson->personen[0]->person_id;
													$personen_namen=$oPerson->personen[0]->anrede.($oPerson->personen[0]->titelpre?'&nbsp;'.$oPerson->personen[0]->titelpre:'').'&nbsp;'.$oPerson->personen[0]->vorname.'&nbsp;'.$oPerson->personen[0]->nachname.'&nbsp;'.($oPerson->personen[0]->aktiv==true || $oPerson->personen[0]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv">':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv">');
												}	
												else
													$personen_namen='Fehler ! '.$person_id;
											}	
									}
									else if ($besteller)
										$personen_namen='<a href="mailto:.'.$besteller.'">$besteller</a>';
								?>

						<td>&nbsp;<label for="person_id">Mitarbeiter</label>&nbsp;
								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="person_id" name="person_id" size="13" maxlength="14" value="<?php echo $person_id; ?>">
									<script type="text/javascript" language="JavaScript1.2">
									function formatItem(row) {
									    return row[0] + " <li>" + row[1] + "</li> ";
									}
									$(document).ready(function() 
									{
										  $('#person_id').autocomplete('inventar_autocomplete.php', 
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:400,
											formatItem:formatItem,
											extraParams:{'work':'person'
												,'oe_kurzbz':$("#oe_kurzbz").val()
												 }
										  });
								  });
								</script>
								<?php
									echo $personen_namen;
								?>
									<input style="display:none" id="besteller" name="besteller" value="<?php echo $besteller;?>">
							</td>
						</tr>
					</table>

					<table class="navbar">
						<tr>
							<td valign="top">&nbsp;<label for="beschreibung">Beschreibung</label>&nbsp;</td>
							<td><textarea id="beschreibung" name="beschreibung" cols="80" rows="5"><?php echo $beschreibung;?></textarea></td>
						</tr>
						<tr>
							<td valign="top">&nbsp;<label for="anmerkung">Anmerkung</label>&nbsp;</td>
							<td><textarea id="anmerkung" name="anmerkung" cols="80" rows="5"><?php echo $anmerkung;?></textarea></td>

						</tr>
						<tr>
							<td valign="top">&nbsp;<label for="verwendung">Verwendung</label>&nbsp;</td>
							<td><textarea id="verwendung" name="verwendung" cols="80" rows="5"><?php echo $verwendung;?></textarea></td>
						</tr>
					</table>

					<table class="navbar">
						<tr>

							<td>&nbsp;<label for="leasing_bis">Leasing bis</label>&nbsp;</td>
							<td>
								<input id="leasing_bis" name="leasing_bis" size="10" maxlength="11" value="<?php echo $leasing_bis;?>">
								<script type="text/javascript" language="JavaScript1.2">
								$(function() 
								{
									$("#leasing_bis").datepicker({
																arrows:true,
																clearText: 'l&ouml;schen', clearStatus: 'aktuelles Datum l&ouml;schen',
																closeText: 'schlie&szlig;en', closeStatus: 'ohne &Auml;nderungen schlie&szlig;en',
																prevText: 'zur&uuml;ck', prevStatus: 'letzten Monat zeigen',
																nextText: 'vor', nextStatus: 'n&auml;chsten Monat zeigen',
																currentText: 'heute', currentStatus: '',
																monthNames: ['Januar','Februar','M&auml;rz','April','Mai','Juni',
																'Juli','August','September','Oktober','November','Dezember'],
																monthNamesShort: ['Jan','Feb','M?r','Apr','Mai','Jun',
																'Jul','Aug','Sep','Okt','Nov','Dez'],
																monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
																weekHeader: 'Wo', weekStatus: 'Woche des Monats',
																dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
																dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
																dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
																dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'W&auml;hle D, M d',
																dateFormat: 'dd-mm-yy', firstDay: 1,
																initStatus: 'W&auml;hle ein Datum', isRTL: false
																});
									});
								</script>
							</td>

							<td>&nbsp;<label for="afa">AfA Jahre</label>&nbsp;</td>
							<td>
								<select id="afa" name="afa" >
								<?php
									for ($i=1;$i<20;$i++)
										echo '<option  '.($afa==$i?' selected="selected" ':'').'  value="'.$i.'">'.$i.' Jahre</option>';
								?>
								</select>
							</td>

						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<!-- erweiterte VORLAGE EIN -->

	<div>
			<div id="container_show">
				<div style="background-color: #FFF4D5;cursor: pointer;font-size:normal;">
					<img src="../../skin/images/right.png" alt="anzeigen - show">Vorlagedaten anzeigen / ausblenden
					<input style="display:none;" type="text" id="vorlage" name="vorlage" value="<?php echo ($vorlage && $vorlage!='false'?'true':'false'); ?>">
				</div>
			</div>
			<script type="text/javascript" language="JavaScript1.2">
			   $(document).ready(function()       // Prueft, ob das Dokument geladen ist
			   {      
			   		$("div#container_show").click(function(event)
					{  // Bei Klick auf div#
			      		if ($("#vorlage").val() == 'false') 
						{
				        	 $("div#container").show("slow");         // div# langsam ?ffnen
							 $("#vorlage").val('true');
					    }
						else
						{
					         $("div#container").hide("slow");         // div# langsam verbergen
							 $("#vorlage").val('false');
				    	}
				   });
				});
			</script>

			<div>
				<a href="javascript:document.sendform.submit();"><img src="../../skin/images/refresh.png" alt="aktualisieren" >&nbsp;aktualisieren</a>
			</div>
	</div>
	<?php
		if (is_array($errormsg) && count($errormsg)>0)
			echo '<font class="error">'. implode("<br>",$errormsg).'</font>';
		elseif (!is_array($errormsg))
		echo '<font class="error"><br>'.$errormsg.'</font>';
	?>
	</fieldset>
	<hr>
	<!-- VORLAGE EMDE -->

	<noscript>
		<p class="error">Bitte JavaScript einschalten!</p>
  	</noscript>

	<!-- DATEN ANFANG -->
	<?php
@flush();

$betriebsmittel_id_array=(isset($_REQUEST['betriebsmittel_id_array'])?$_REQUEST['betriebsmittel_id_array']:array());

$nummer_array=(isset($_REQUEST['nummer_array'])?$_REQUEST['nummer_array']:array());
$seriennummer_array=(isset($_REQUEST['seriennummer_array'])?$_REQUEST['seriennummer_array']:array());
$betriebsmitteltyp_array=(isset($_REQUEST['betriebsmitteltyp_array'])?$_REQUEST['betriebsmitteltyp_array']:array());
$betriebsmittelstatus_kurzbz_array=(isset($_REQUEST['betriebsmittelstatus_kurzbz_array'])?$_REQUEST['betriebsmittelstatus_kurzbz_array']:array());
$ort_kurzbz_array=(isset($_REQUEST['ort_kurzbz_array'])?$_REQUEST['ort_kurzbz_array']:array());
$oe_kurzbz_array=(isset($_REQUEST['oe_kurzbz_array'])?$_REQUEST['oe_kurzbz_array']:array());
$person_id_array=(isset($_REQUEST['person_id_array'])?$_REQUEST['person_id_array']:array());
$person_id_old_array=(isset($_REQUEST['person_id_old_array'])?$_REQUEST['person_id_old_array']:array());
$bestellung_id_array=(isset($_REQUEST['bestellung_id_array'])?$_REQUEST['bestellung_id_array']:array());
$bestelldetail_id_array=(isset($_REQUEST['bestelldetail_id_array'])?$_REQUEST['bestelldetail_id_array']:array());
$hersteller_array=(isset($_REQUEST['hersteller_array'])?$_REQUEST['hersteller_array']:array());
$beschreibung_array=(isset($_REQUEST['beschreibung_array'])?$_REQUEST['beschreibung_array']:array());
$anmerkung_array=(isset($_REQUEST['anmerkung_array'])?$_REQUEST['anmerkung_array']:array());
$verwendung_array=(isset($_REQUEST['verwendung_array'])?$_REQUEST['verwendung_array']:array());
$leasing_bis_array=(isset($_REQUEST['leasing_bis_array'])?$_REQUEST['leasing_bis_array']:array());
$afa_array=(isset($_REQUEST['afa_array'])?$_REQUEST['afa_array']:array());

for ($pos=0;$pos<$anzahl;$pos++) 
{
	$errormsg=array();

  	$vorlage=trim(isset($_REQUEST['vorlage'.$pos]) ?$_REQUEST['vorlage'.$pos]:'false');

	$betriebsmittel_id_array[$pos]=trim(isset($betriebsmittel_id_array[$pos])?trim($betriebsmittel_id_array[$pos]):$betriebsmittel_id);
	$nummer_array[$pos]=trim(isset($nummer_array[$pos])?trim($nummer_array[$pos]):$nummer);
	$seriennummer_array[$pos]=trim(isset($seriennummer_array[$pos])?trim($seriennummer_array[$pos]):$seriennummer);
	$betriebsmitteltyp_array[$pos]=trim(isset($betriebsmitteltyp_array[$pos]) && $work=='save' ?trim($betriebsmitteltyp_array[$pos]):$betriebsmitteltyp);
	$betriebsmittelstatus_kurzbz_array[$pos]=trim(isset($betriebsmittelstatus_kurzbz_array[$pos]) && $work=='save' ?trim($betriebsmittelstatus_kurzbz_array[$pos]):$betriebsmittelstatus_kurzbz);
	$ort_kurzbz_array[$pos]=trim(isset($ort_kurzbz_array[$pos]) && $work=='save' ?trim($ort_kurzbz_array[$pos]):$ort_kurzbz);
	$oe_kurzbz_array[$pos]=trim(isset($oe_kurzbz_array[$pos]) && $work=='save' ?trim($oe_kurzbz_array[$pos]):$oe_kurzbz);
	$person_id_array[$pos]=trim(isset($person_id_array[$pos]) && $work=='save' ?trim($person_id_array[$pos]):$person_id);
	$person_id_old_array[$pos]=trim(isset($person_id_old_array[$pos]) && $work=='save' ?trim($person_id_old_array[$pos]):'');
	$bestellung_id_array[$pos]=trim(isset($bestellung_id_array[$pos]) && $work=='save' ?trim($bestellung_id_array[$pos]):$bestellung_id);
	$bestelldetail_id_array[$pos]=trim(isset($bestelldetail_id_array[$pos]) && $work=='save' ?trim($bestelldetail_id_array[$pos]):$bestelldetail_id);
	$hersteller_array[$pos]=trim(isset($hersteller_array[$pos]) && $work=='save' ?trim($hersteller_array[$pos]):$hersteller);
	$beschreibung_array[$pos]=trim(isset($beschreibung_array[$pos]) && $work=='save' ?trim($beschreibung_array[$pos]):$beschreibung);
	$anmerkung_array[$pos]=trim(isset($anmerkung_array[$pos]) && $work=='save' ?trim($anmerkung_array[$pos]):$anmerkung);
	$verwendung_array[$pos]=trim(isset($verwendung_array[$pos]) && $work=='save' ?trim($verwendung_array[$pos]):$verwendung);
	$leasing_bis_array[$pos]=trim(isset($leasing_bis_array[$pos]) && $work=='save' ?trim($leasing_bis_array[$pos]):$leasing_bis);
	$afa_array[$pos]=trim(isset($afa_array[$pos]) && $work=='save' ?trim($afa_array[$pos]):$afa);

	if ($work=='save' && $nummer_array[$pos])
	{

		$oBetriebsmittel = new betriebsmittel();
		$oBetriebsmittel->result=array();
		$oBetriebsmittel->debug=$debug;
		$oBetriebsmittel->errormsg='';

		$oBetriebsmittel->new=false;
		if (!$oBetriebsmittel->load_nummer($nummer_array[$pos]))
		{
			$oBetriebsmittel->new=true;
			$oBetriebsmittel->betriebsmittel_id=null;
		}
		$betriebsmittel_id_array[$pos]=$oBetriebsmittel->betriebsmittel_id;

		$oBetriebsmittel->beschreibung=$beschreibung_array[$pos];
	    $oBetriebsmittel->betriebsmitteltyp=$betriebsmitteltyp_array[$pos];
	    $oBetriebsmittel->nummer=$nummer_array[$pos];
	    $oBetriebsmittel->nummerintern=0;
	    $oBetriebsmittel->reservieren=false;
	    $oBetriebsmittel->ort_kurzbz=$ort_kurzbz_array[$pos];
	    $oBetriebsmittel->ext_id=0;
	    $oBetriebsmittel->insertvon=$uid;
	    $oBetriebsmittel->updatevon=$uid;

	    $oBetriebsmittel->insertamum=null;
	    $oBetriebsmittel->updateamum=null;

		$oBetriebsmittel->oe_kurzbz=$oe_kurzbz_array[$pos];
		$oBetriebsmittel->hersteller=$hersteller_array[$pos];
		$oBetriebsmittel->seriennummer=$seriennummer_array[$pos];
		$oBetriebsmittel->bestellung_id=$bestellung_id_array[$pos];
		$oBetriebsmittel->bestelldetail_id=$bestelldetail_id_array[$pos];
		$oBetriebsmittel->afa=$afa_array[$pos];
		$oBetriebsmittel->verwendung=$verwendung_array[$pos];
		$oBetriebsmittel->anmerkung=$anmerkung_array[$pos];
		$oBetriebsmittel->leasing_bis=$leasing_bis_array[$pos];
		
		if ($oBetriebsmittel->save())
		{
			$errormsg[]='Inventar / Betriebsmittel '.($oBetriebsmittel->new?'gespeichert ':'ge&auml;ndert ');
			$betriebsmittel_id_array[$pos]=$oBetriebsmittel->betriebsmittel_id;

			$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
			$oBetriebsmittel_betriebsmittelstatus->result=array();
			$oBetriebsmittel_betriebsmittelstatus->debug=$debug;
			$oBetriebsmittel_betriebsmittelstatus->errormsg='';

			$oBetriebsmittel_betriebsmittelstatus->new=true;

			$oBetriebsmittel_betriebsmittelstatus->betriebsmittelbetriebsmittelstatus_id=null;
			if ($oBetriebsmittel_betriebsmittelstatus->load_last_betriebsmittel_id($betriebsmittel_id_array[$pos]))
			{
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz=trim($oBetriebsmittel_betriebsmittelstatus->result[0]->betriebsmittelstatus_kurzbz);
				if (strtoupper($oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz)==strtoupper($betriebsmittelstatus_kurzbz_array[$pos]) )
				{
					$oBetriebsmittel_betriebsmittelstatus->new=false;
					$oBetriebsmittel_betriebsmittelstatus->betriebsmittelbetriebsmittelstatus_id=$oBetriebsmittel_betriebsmittelstatus->result[0]->betriebsmittelbetriebsmittelstatus_id;
					$oBetriebsmittel_betriebsmittelstatus->datum=$oBetriebsmittel_betriebsmittelstatus->result[0]->datum;
				}
			}

			$oBetriebsmittel_betriebsmittelstatus->datum=trim($oBetriebsmittel_betriebsmittelstatus->datum?$oBetriebsmittel_betriebsmittelstatus->datum:date('Y-m-d'));
			$oBetriebsmittel_betriebsmittelstatus->betriebsmittel_id=$betriebsmittel_id_array[$pos];
			$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz=$betriebsmittelstatus_kurzbz_array[$pos];
		    $oBetriebsmittel_betriebsmittelstatus->insertvon=$uid;
		    $oBetriebsmittel_betriebsmittelstatus->updatevon=$uid;
			if (!$oBetriebsmittel_betriebsmittelstatus->save())
				$errormsg[]=$oBetriebsmittel_betriebsmittelstatus->errormsg;


			$oBetriebsmittelperson = new betriebsmittelperson();
			$oBetriebsmittelperson->result=array();
			$oBetriebsmittelperson->debug=$debug;
			$oBetriebsmittelperson->errormsg='';
			#$oBetriebsmittelperson->delete($betriebsmittel_id_array[$pos]);


			// Entliehen an eine Person
			if (!empty($person_id_array[$pos]) && !is_numeric($person_id_array[$pos]))
			{
				if ($oBenutzer = new benutzer($person_id_array[$pos]))
					$person_id_array[$pos]=$oBenutzer->person_id;
			}	
			if (!empty($person_id_old_array[$pos]) && !is_numeric($person_id_old_array[$pos]))
			{
				if ($oBenutzer = new benutzer($person_id_old_array[$pos]))
					$person_id_old_array[$pos]=$oBenutzer->person_id;
			}	
																		
			if ($person_id_old_array[$pos] && $person_id_array[$pos]
			&& $person_id_old_array[$pos]!=$person_id_array[$pos])
			{
				$oBetriebsmittelperson = new betriebsmittelperson();
				$oBetriebsmittelperson->result=array();
				$oBetriebsmittelperson->debug=$debug;
				$oBetriebsmittelperson->errormsg='';
				// Betriebsmittel lesen zur Person
				if ($oBetriebsmittelperson->load($betriebsmittel_id_array[$pos],$person_id_old_array[$pos]))
				{
					if (is_array($oBetriebsmittelperson->result) && isset($oBetriebsmittelperson->result[0]))
						$oBetriebsmittelperson->result=$oBetriebsmittelperson->result[0];
					$oBetriebsmittelperson->errormsg='';
				    $oBetriebsmittelperson->betriebsmittel_id=$betriebsmittel_id_array[$pos];
				    $oBetriebsmittelperson->person_id=$oBetriebsmittelperson->result->person_id;
				    $oBetriebsmittelperson->anmerkung=$oBetriebsmittelperson->result->anmerkung;
				    $oBetriebsmittelperson->kaution=($oBetriebsmittelperson->result->kaution?$oBetriebsmittelperson->result->kaution:0);
				    $oBetriebsmittelperson->betriebsmitteltyp=$oBetriebsmittelperson->result->betriebsmitteltyp;
				    $oBetriebsmittelperson->beschreibung=$oBetriebsmittelperson->result->beschreibung;
				    $oBetriebsmittelperson->ausgegebenam=$oBetriebsmittelperson->result->ausgegebenam;
				    $oBetriebsmittelperson->retouram=date('Y-m-d');
				    $oBetriebsmittelperson->ext_id=$oBetriebsmittelperson->result->ext_id;
				    $oBetriebsmittelperson->updatevon=$uid;

					$oBetriebsmittelperson->new=false;
	 				if (!$oBetriebsmittelperson->save())
						$errormsg[]=$oBetriebsmittelperson->errormsg;
				}
			}

			// Entliehen an eine Person
			if ($person_id_array[$pos])
			{
				$oBetriebsmittelperson = new betriebsmittelperson();
				$oBetriebsmittelperson->result=array();
				$oBetriebsmittelperson->debug=$debug;
				$oBetriebsmittelperson->errormsg='';
				if (!$oBetriebsmittelperson->load($betriebsmittel_id_array[$pos],$person_id_array[$pos]))
				{
					$oBetriebsmittelperson->new=true;

					$oBetriebsmittelperson->result=array();
					$oBetriebsmittelperson->debug=$debug;
					$oBetriebsmittelperson->errormsg='';

				    $oBetriebsmittelperson->betriebsmittel_id=$betriebsmittel_id_array[$pos];
				    $oBetriebsmittelperson->person_id=$person_id_array[$pos];
				    $oBetriebsmittelperson->anmerkung=($oBetriebsmittelperson->new?($anmerkung_array[$pos]?StringCut($anmerkung_array[$pos],43):$beschreibung_array[$pos]):$oBetriebsmittelperson->anmerkung);
				    $oBetriebsmittelperson->kaution=0;
				    $oBetriebsmittelperson->betriebsmitteltyp=$betriebsmitteltyp_array[$pos];
				    $oBetriebsmittelperson->beschreibung=StringCut($beschreibung_array[$pos],89);
				    $oBetriebsmittelperson->ausgegebenam=($oBetriebsmittelperson->new?date('Y-m-d'):$oBetriebsmittelperson->ausgegebenam);
				    $oBetriebsmittelperson->retouram=($oBetriebsmittelperson->new?null:$oBetriebsmittelperson->retouram);
				    $oBetriebsmittelperson->ext_id=($oBetriebsmittelperson->new?null:$oBetriebsmittelperson->ext_id);
				    $oBetriebsmittelperson->insertvon=$uid;
				    $oBetriebsmittelperson->updatevon=$uid;
	 				if (!$oBetriebsmittelperson->save())
						$errormsg[]=$oBetriebsmittelperson->errormsg;
				}
			}
		}
		else
			$errormsg=$oBetriebsmittel->errormsg;
	}
		?>

	<div id="container_array">

		<div id="container_box<?php echo $pos; ?>">
			<fieldset><legend> <?php echo (1 + $pos ); ?>) Inventar ID <a href="inventar.php?betriebsmittel_id=<?php echo $betriebsmittel_id_array[$pos]; ?>"><?php echo $betriebsmittel_id_array[$pos]; ?></a> </legend>
				<table class="navbar">
					<tr>
						<th><?php echo (1 + $pos ); ?><input style="display:none;" id="betriebsmittel_id_array<?php echo $pos; ?>" name="betriebsmittel_id_array[]" value="<?php echo $betriebsmittel_id_array[$pos]; ?>"></th>

						<td>&nbsp;<label for="nummer_array<?php echo $pos; ?>">Inventarnummer</label>&nbsp;</td>
						<td><input id="nummer_array<?php echo $pos; ?>" name="nummer_array[]" size="32" maxlength="33" value="<?php echo $nummer_array[$pos]; ?>"></td>

						<td>&nbsp;<label for="seriennummer_array<?php echo $pos; ?>">Seriennummer</label>&nbsp;</td>
						<td><input id="seriennummer_array<?php echo $pos; ?>" name="seriennummer_array[]" size="32" maxlength="33" value="<?php echo $seriennummer_array[$pos]; ?>"></td>
						<td id="bcTarget<?php echo $pos; ?>">
							<table>
								<tr>
									<td>druck&nbsp;<img border="0" src="<?php echo $path;?>skin/images/printer.png" title="drucken" > </td>
								</tr>
							</table>
						</td>	
							<script type="text/javascript" language="JavaScript1.2">			
							   $(document).ready(function()           // Prueft, ob das Dokument geladen ist
							   {  
							  	 $("td#bcTarget<?php echo $pos; ?>").click(function(event) 
								 { 
										var PrintWin=window.open('etiketten.php?nummer=<?php echo urlencode($nummer_array[$pos]); ?>','Etik','copyhistory=no,directories=no,location=no,dependent=yes,toolbar=no,status=no,menubar=no,resizable=yes,scrollbars=yes,width=400,height=300,left=20, top=20'); 
										if (PrintWin) 
										{
											PrintWin.focus();
										}	
							   });
							});

						</script>
					</tr>
				</table>

			<div id="container<?php echo $pos; ?>"  style="display:<?php echo ($vorlage && $vorlage!='false'?'block':'none'); ?>;" >

			<div id="container_shows<?php echo $pos; ?>">
				<div style="background-color: #FFF4D5;cursor: pointer;font-size:normal;">
					<img src="../../skin/images/right.png" alt="anzeigen - show">Inventardaten anzeigen / ausblenden
				</div>
			</div>
			<script type="text/javascript" language="JavaScript1.2">
			   $(document).ready(function()            // Prueft, ob das Dokument geladen ist
			   {
				   $("div#container_shows<?php echo $pos; ?>").click(function(event)  // Bei Klick auf div#
				   {
				      if ($("#vorlage<?php echo $pos; ?>").val() == 'false') 
					  {
				         $("div#container<?php echo $pos; ?>").show("slow");         // div# langsam ?ffnen
				         $("#vorlage<?php echo $pos; ?>").val('true');
			    	  }
					  else
					  {
			        	 $("div#container<?php echo $pos; ?>").hide("slow");         // div# langsam verbergen
				         $("#vorlage<?php echo $pos; ?>").val('false');
				      }
				   });
				});
			</script>

				<table class="navbar">
					<tr>
						<td>
								<table class="navbar">
										<tr>
											<td>&nbsp;<label for="bestellung_id_array<?php echo $pos; ?>">Bestellung ID</label>&nbsp;
												<input id="bestellung_id_array<?php echo $pos; ?>" name="bestellung_id_array[]" size="10" maxlength="41" value="<?php echo $bestellung_id_array[$pos]; ?>">
												<script type="text/javascript" language="JavaScript1.2">
													function formatItem(row) 
													{
													    return row[0] + " <li>" + row[1] + "</li> ";
													}
													$(document).ready(function() 
													{
														  $('#bestellung_id_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php', 
														  {
															minChars:2,
															matchSubset:1,matchContains:1,
															width:500,
															formatItem:formatItem,
															extraParams:{'work':'wawi_bestellung_id'
																		,'oe_kurzbz':$("#oe_kurzbz_array<?php echo $pos; ?>").val()
																		,'hersteller':$("#hersteller_array<?php echo $pos; ?>").val()}
														  });
												  });
												</script>
											</td>
											<td>&nbsp;<label for="bestelldetail_id_array<?php echo $pos; ?>">Bestelldetail ID</label>&nbsp;
												<input id="bestelldetail_id_array<?php echo $pos; ?>" name="bestelldetail_id_array[]" size="6" maxlength="41" value="<?php echo $bestelldetail_id_array[$pos]; ?>">
												<script type="text/javascript" language="JavaScript1.2">
													function formatItem(row) 
													{
													    return row[0] + " <li>" + row[1] + "</li> ";
													}
													$(document).ready(function() 
													{
														  $('#bestelldetail_id_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php', 
														  {
															minChars:1,
															matchSubset:1,matchContains:1,
															width:500,
															formatItem:formatItem,
															extraParams:{'work':'wawi_bestelldetail_id'
																		,'bestellung_id':$("#bestellung_id_array<?php echo $pos; ?>").val()
																		,'oe_kurzbz':$("#oe_kurzbz_array<?php echo $pos; ?>").val()
																		,'hersteller':$("#hersteller_array<?php echo $pos; ?>").val()}
														  });
												  });
												</script>

											</td>

											<td>&nbsp;<label for="hersteller_array<?php echo $pos; ?>">Hersteller</label>&nbsp;
											<input id="hersteller_array<?php echo $pos; ?>" name="hersteller_array[]" type="text" size="35" maxlength="120" value="<?php echo $hersteller_array[$pos]; ?>">
												<script type="text/javascript" language="JavaScript1.2">
													function formatItem(row) 
													{
													    return row[0] + " <li>" + row[1] + "</li> ";
													}
													$(document).ready(function() 
													{
														  $('#hersteller_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php', 
														  {
															minChars:2,
															matchSubset:1,matchContains:1,
															width:400,
															formatItem:formatItem,
															extraParams:{'work':'hersteller' }
														  });
												  });
												</script>
											</td>
										</tr>
									</table>


							<table class="navbar">
								<tr>
									<td>&nbsp;<label for="betriebsmitteltyp_array<?php echo $pos; ?>">Inventartyp</label>&nbsp;
										<select id="betriebsmitteltyp_array<?php echo $pos; ?>" name="betriebsmitteltyp_array[]">
										<?php
										for ($i=0;$i<count($resultBetriebsmitteltyp) ;$i++)
										{
											if ($resultBetriebsmitteltyp[$i]->betriebsmitteltyp)
												echo '<option '.($betriebsmitteltyp_array[$pos]==$resultBetriebsmitteltyp[$i]->betriebsmitteltyp?' selected="selected" ':'').' value="'.$resultBetriebsmitteltyp[$i]->betriebsmitteltyp.'">'.($resultBetriebsmitteltyp[$i]->beschreibung=='NULL' || empty($resultBetriebsmitteltyp[$i]->beschreibung)?$resultBetriebsmitteltyp[$i]->betriebsmitteltyp:$resultBetriebsmitteltyp[$i]->beschreibung).'&nbsp;</option>';
										}
										?>
										</select>
									</td>

									<td>&nbsp;<label for="betriebsmittelstatus_kurzbz_array<?php echo $pos; ?>">Status</label>&nbsp;
										<select id="betriebsmittelstatus_kurzbz_array<?php echo $pos; ?>" name="betriebsmittelstatus_kurzbz_array[]" >
										  <?php
											for ($i=0;$i<count($resultBetriebsmittelstatus) ;$i++)
											{
												if ($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)
													echo '<option '.($betriebsmittelstatus_kurzbz_array[$pos]==$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
											}
											?>
										</select>
									</td>

									<td>&nbsp;<label for="ort_kurzbz_array<?php echo $pos; ?>">Ort</label>&nbsp;
										<input id="ort_kurzbz_array<?php echo $pos; ?>" name="ort_kurzbz_array[]" size="10" maxlength="20" value="<?php echo $ort_kurzbz_array[$pos]; ?>">
											<script type="text/javascript" language="JavaScript1.2">
													function formatItem(row) 
													{
													    return row[0] + " <li>" + row[1] + "</li> ";
													}
													$(document).ready(function() 
													{
														  $('#ort_kurzbz_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php', 
														  {
															minChars:2,
															matchSubset:1,matchContains:1,
															width:300,
															formatItem:formatItem,
															extraParams:{'work':'ort' }
														  });
												  });
										</script>
									<?php
								// Ort
										$ort_kurzbz=trim($ort_kurzbz_array[$pos]);
										$oOrt->errormsg='';
										$oOrt->result=array();
										if ($ort_kurzbz && !$oOrt->load($ort_kurzbz))
											$errormsg[]=$oOrt->errormsg;
										else if ($ort_kurzbz)
											echo trim(($oOrt->bezeichnung && $oOrt->bezeichnung!='NULL'?$oOrt->bezeichnung:'')).'&nbsp;'.($oOrt->aktiv==true || $oOrt->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv">':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv">');
										$oOrt->result=array();
									?>
								</td>
							</tr>
						</table>

						<table class="navbar">
							<tr>
								<td>&nbsp;<label for="oe_kurzbz_array<?php echo $pos; ?>">Organisation</label>&nbsp;
									<input id="oe_kurzbz_array<?php echo $pos; ?>" name="oe_kurzbz_array[]" size="13" maxlength="14" value="<?php echo $oe_kurzbz_array[$pos]; ?>" >
									<script type="text/javascript" language="JavaScript1.2">
										function formatItem(row) 
										{
										    return row[0] + " <li>" + row[1] + "</li> ";
										}
										$(document).ready(function() 
										{
											  $('#oe_kurzbz_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php', 
											  {
												minChars:2,
												matchSubset:1,matchContains:1,
												width:400,
												formatItem:formatItem,
												extraParams:{'work':'organisationseinheit' }
											  });
									  });
									</script>
								<?php
								// Organisation
										$oe_kurzbz=trim($oe_kurzbz_array[$pos]);
										$oOrganisationseinheit->errormsg='';
										$oOrganisationseinheit->result=array();
										if ($oe_kurzbz && !$oOrganisationseinheit->load($oe_kurzbz))
											$errormsg[]=$oOrganisationseinheit->errormsg;
										else if ($oe_kurzbz)
											echo ($oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:$resultBetriebsmittel[$pos]->oe_kurzbz);
										$oOrganisationseinheit->result=array();
									?>
								</td>

								<td>&nbsp;<label for="person_id_array<?php echo $pos; ?>">Mitarbeiter</label>&nbsp;
									<input style="display:none;" id="person_id_old_array<?php echo $pos; ?>" name="person_id_old_array[]" value="<?php echo $person_id_array[$pos]; ?>" >
									<input id="person_id_array<?php echo $pos; ?>" name="person_id_array[]" size="13" maxlength="14" value="<?php echo $person_id_array[$pos]; ?>" >
									<script type="text/javascript" language="JavaScript1.2">
											function formatItem(row) 
											{
											    return row[0] + " <li>" + row[1] + "</li> ";
											}
											$(document).ready(function() 
											{
												  $('#person_id_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php', 
												  {
													minChars:2,
													matchSubset:1,matchContains:1,
													width:400,
													formatItem:formatItem,
													extraParams:{'work':'person' }
												  });
										  });
										</script>
								<?php
									if ($person_id_array[$pos])
									{
											if (!$oPerson = new person($person_id_array[$pos]))
												echo $oPerson->errormsg;
											else if ($oPerson->nachname)
												echo $oPerson->anrede.($oPerson->titelpre?'&nbsp;'.$oPerson->titelpre:'').'&nbsp;'.$oPerson->vorname.'&nbsp;'.$oPerson->nachname.'&nbsp;'.($oPerson->aktiv==true || $oPerson->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" >':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv">');
											else
												echo 'Fehler';
									}
									else if ($besteller)
										echo "<a href='mailto:$besteller'>$besteller</a>";
								?>
								</td>
							</tr>
						</table>



							<table class="navbar">
								<tr>
									<td valign="top">&nbsp;<label for="beschreibung_array<?php echo $pos; ?>">Beschreibung</label>&nbsp;</td>
									<td><textarea id="beschreibung_array<?php echo $pos; ?>" name="beschreibung_array[]" cols="80" rows="5"><?php echo $beschreibung_array[$pos]; ?></textarea></td>
								</tr>
								<tr>

									<td valign="top">&nbsp;<label for="anmerkung_array<?php echo $pos; ?>">Anmerkung</label>&nbsp;</td>
									<td><textarea id="anmerkung_array<?php echo $pos; ?>" name="anmerkung_array[]" cols="80" rows="5"><?php echo $anmerkung_array[$pos]; ?></textarea></td>
								</tr>
								<tr>
									<td valign="top">&nbsp;<label for="verwendung_array<?php echo $pos; ?>">Verwendung</label>&nbsp;</td>
									<td><textarea id="verwendung_array<?php echo $pos; ?>" name="verwendung_array[]" cols="80" rows="5"><?php echo $verwendung_array[$pos]; ?></textarea></td>
								</tr>
							</table>

							<table class="navbar">
								<tr>
									<td>&nbsp;<label for="leasing_bis_array<?php echo $pos; ?>">Leasing bis</label>&nbsp;</td>
									<td>
										<input id="leasing_bis_array<?php echo $pos; ?>" name="leasing_bis_array[]" size="10" maxlength="11" value="<?php echo $leasing_bis_array[$pos]; ?>">
										<script type="text/javascript" language="JavaScript1.2">
										$(function() 
										{
												$("#leasing_bis_array<?php echo $pos; ?>").datepicker({arrows:true,
																		clearText: 'l&ouml;schen', clearStatus: 'aktuelles Datum l&ouml;schen',
																		closeText: 'schlie&szlig;en', closeStatus: 'ohne &Auml;nderungen schlie&szlig;en',
																		prevText: '&#x3c;zur&uuml;ck', prevStatus: 'letzten Monat zeigen',
																		nextText: 'vor&#x3e;', nextStatus: 'n&auml;chsten Monat zeigen',
																		currentText: 'heute', currentStatus: '',
																		monthNames: ['Januar','Februar','M&auml;rz','April','Mai','Juni',
																		'Juli','August','September','Oktober','November','Dezember'],
																		monthNamesShort: ['Jan','Feb','M?r','Apr','Mai','Jun',
																		'Jul','Aug','Sep','Okt','Nov','Dez'],
																		monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
																		weekHeader: 'Wo', weekStatus: 'Woche des Monats',
																		dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
																		dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
																		dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
																		dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'W&auml;hle D, M d',
																		dateFormat: 'yy.mmm.dd', firstDay: 1,
																		initStatus: 'W&auml;hle ein Datum', isRTL: false
																		} );
											});
										</script>
									</td>
									<td>&nbsp;<label for="afa_array<?php echo $pos; ?>">AfA Jahre</label>&nbsp;</td>
									<td>
										<select id="afa_array<?php echo $pos; ?>" name="afa_array[]" >
											<?php
												for ($i=1;$i<20;$i++)
													echo '<option  '.($afa_array[$pos]==$i?' selected="selected" ':'').'  value="'.$i.'">'.$i.' Jahre</option>';
											?>
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>

			<div id="container_show<?php echo $pos; ?>">
				<div style="background-color: #FFF4D5;cursor: pointer;font-size:normal;">
					<img src="../../skin/images/right.png" alt="anzeigen - show">Inventardaten anzeigen / ausblenden
					<input style="display:none" type="text" id="vorlage<?php echo $pos; ?>" name="vorlage<?php echo $pos; ?>" value="<?php echo $vorlage; ?>">
				</div>
			</div>
			<script type="text/javascript" language="JavaScript1.2">
			   $(document).ready(function()            // Prueft, ob das Dokument geladen ist
				{
			   		$("div#container_show<?php echo $pos; ?>").click(function(event) // Bei Klick auf div#
					{  
					      if ($("#vorlage<?php echo $pos; ?>").val() == 'false') 
						  {
					         $("div#container<?php echo $pos; ?>").show("slow");         // div# langsam ?ffnen
			    		     $("#vorlage<?php echo $pos; ?>").val('true');
					      }
						  else 
						  {
					         $("div#container<?php echo $pos; ?>").hide("slow");         // div# langsam verbergen
			    		     $("#vorlage<?php echo $pos; ?>").val('false');
				      		}
			   });
			});
			</script>
			<p><a href="javascript:document.sendform.work.value='save';document.sendform.submit();"><img src="../../skin/images/application_form_edit.png" alt="speichern">&nbsp;speichern</a></p>

				<?php
				// Error - Meldungen ausgeben
				if (is_array($errormsg) && count($errormsg)>0)
					echo '<font class="error">'. implode("<br>",$errormsg).'</font>';
				elseif (!is_array($errormsg))
					echo '<font class="error"><br>'.$errormsg.'</font>';
				?>
			</fieldset>
			<hr>
			</div> <!-- ENDE Daten Container -->
<?php
@flush();
} // Ende Anzahl Schleife
?>
		</div>
	&nbsp;
	<input id="work" name="work" value="" style="display:none;">
	</form>
</body>
</html>

