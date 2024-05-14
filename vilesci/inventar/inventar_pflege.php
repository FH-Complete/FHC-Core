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
/**
 * Diese Seite dient zur Inventarisierung der Betriebsmittel.
 * Es kann eine Vorlage erstellt werden, damit mehrere Betriebsmittel mit den
 * gleichen Daten angelegt werden können.
 *
 * Es koennen neue Betriebsmittel angelegt, bearbeitet und geloescht werden.
 */
	require_once('../../config/vilesci.config.inc.php');
  	require_once('../../include/functions.inc.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/mitarbeiter.class.php');
  	require_once('../../include/ort.class.php');
  	require_once('../../include/datum.class.php');
	require_once('../../include/studiengang.class.php');
  	require_once('../../include/organisationseinheit.class.php');
  	require_once('../../include/betriebsmittel.class.php');
  	require_once('../../include/betriebsmitteltyp.class.php');
  	require_once('../../include/betriebsmittelstatus.class.php');
  	require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
	require_once('../../include/betriebsmittelperson.class.php');
	require_once('../../include/wawi_bestelldetail.class.php');
	require_once('../../include/wawi_bestellung.class.php');
	require_once('../../include/wawi_kostenstelle.class.php');
	require_once('../../include/wawi_bestellstatus.class.php');

	if (!$uid = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
// Variable Initialisieren
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$berechtigung_kurzbz='wawi/inventar:begrenzt';
	$recht=false;
	$schreib_recht=false;
	$default_status_vorhanden='vorhanden';
	$datum_obj = new datum();
// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------

  	$betriebsmittel_id=trim((isset($_REQUEST['betriebsmittel_id']) ? $_REQUEST['betriebsmittel_id']:''));

  	$beschreibung=trim((isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung']:''));
  	$betriebsmitteltyp=trim((isset($_REQUEST['betriebsmitteltyp']) ? $_REQUEST['betriebsmitteltyp']:''));
  	$inventarnummer=trim((isset($_REQUEST['inventarnummer']) ? $_REQUEST['inventarnummer']:''));
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

  	$afa=trim(isset($_REQUEST['afa']) ? $_REQUEST['afa']:3);
  	$leasing_bis=trim(isset($_REQUEST['leasing_bis']) ? $_REQUEST['leasing_bis']:'');

  	$anschaffungswert=isset($_REQUEST['anschaffungswert']) ? $_REQUEST['anschaffungswert']:'';
  	$anschaffungsdatum=isset($_REQUEST['anschaffungsdatum']) ? $_REQUEST['anschaffungsdatum']:'';
  	$hoehe=isset($_REQUEST['hoehe'])?$_REQUEST['hoehe']:'';
	$breite=isset($_REQUEST['breite'])?$_REQUEST['breite']:'';
	$tiefe=isset($_REQUEST['tiefe'])?$_REQUEST['tiefe']:'';
	$verplanen=isset($_REQUEST['verplanen'])?$_REQUEST['verplanen']:false;

	$jahr_monat=trim(isset($_REQUEST['jahr_monat']) ? $_REQUEST['jahr_monat']:'');
  	$inventur_jahr=trim(isset($_REQUEST['inventur_jahr']) ? $_REQUEST['inventur_jahr']:'');

  	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);

  	$ajax=trim(isset($_REQUEST['ajax']) ?$_REQUEST['ajax']:false);
  	$work=trim(isset($_REQUEST['work']) ?$_REQUEST['work']:false);
  	$anzahl=trim(isset($_REQUEST['anzahl']) ?$_REQUEST['anzahl']:1);

  	$vorlage=(isset($_REQUEST['vorlage'])?$_REQUEST['vorlage']:'true');
  	$vorlage=(isset($_REQUEST['betriebsmittel_id']) ?'false':$vorlage);

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
	//$oBetriebsmittelstatus->debug=$debug;
	$oBetriebsmittelstatus->errormsg='';

	$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
	$oBetriebsmittel_betriebsmittelstatus->result=array();
	$oBetriebsmittel_betriebsmittelstatus->debug=$debug;
	$oBetriebsmittel_betriebsmittelstatus->errormsg='';

	$oBetriebsmittelperson = new betriebsmittelperson();
	$oBetriebsmittelperson->result=array();
	//$oBetriebsmittelperson->debug=$debug;
	$oBetriebsmittelperson->errormsg='';

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------

	// Typtable
	if (!$oBetriebsmitteltyp->getAll('typ_code, beschreibung'))
		$errormsg[]=$oBetriebsmitteltyp->errormsg;
	$resultBetriebsmitteltyp=$oBetriebsmitteltyp->result;

	// Statustable
	if (!$rows=$oBetriebsmittelstatus->getAll())
		$errormsg[]=$oBetriebsmittelstatus->errormsg;
	$resultBetriebsmittelstatus=$oBetriebsmittelstatus->result;

	// Vorlagedaten lesen aus Betriebsmittel
	if ($inventarnummer!='' && empty($work) )
	{
		$oBetriebsmittel->result=array();
		$oBetriebsmittel->errormsg='';
		if ($oBetriebsmittel->load_inventarnummer($inventarnummer))
		{
			$betriebsmittel_id=$oBetriebsmittel->betriebsmittel_id;
		}
		else
			$errormsg[]=$oBetriebsmittel->errormsg;
	}

	if(isset($_REQUEST['anzahl_lock']))
		$anzahl_lock=true;
	else
		$anzahl_lock=false;
	// Vorlagedaten lesen aus Betriebsmittel
	if ($betriebsmittel_id!='' && empty($work) )
	{

		$oBetriebsmittel->result=array();
		$oBetriebsmittel->errormsg='';
		if ($oBetriebsmittel->load($betriebsmittel_id))
		{
			$anzahl_lock=true;
		  	$anzahl=1;

			$betriebsmittel_id = $oBetriebsmittel->betriebsmittel_id;
			$beschreibung = $oBetriebsmittel->beschreibung;
			$betriebsmitteltyp = $oBetriebsmittel->betriebsmitteltyp;
			$inventarnummer = $oBetriebsmittel->inventarnummer;
			$reservieren = $oBetriebsmittel->reservieren;
			$ort_kurzbz = $oBetriebsmittel->ort_kurzbz;
			$updateamum = $oBetriebsmittel->updateamum;
			$updatevon = $oBetriebsmittel->updatevon;
			$insertvon = $oBetriebsmittel->insertvon;
			$insertamum = $oBetriebsmittel->insertamum;
			$ext_id = $oBetriebsmittel->ext_id;
			$beschreibung = $oBetriebsmittel->beschreibung;
			$oe_kurzbz = $oBetriebsmittel->oe_kurzbz;
			$hersteller = $oBetriebsmittel->hersteller;
			$seriennummer = $oBetriebsmittel->seriennummer;
			$bestellung_id = $oBetriebsmittel->bestellung_id;
			$bestelldetail_id = $oBetriebsmittel->bestelldetail_id;
			$afa = $oBetriebsmittel->afa;
			$verwendung = $oBetriebsmittel->verwendung;
			$anmerkung = $oBetriebsmittel->anmerkung;
			$leasing_bis = $oBetriebsmittel->leasing_bis;
			$anschaffungsdatum = $oBetriebsmittel->anschaffungsdatum;
			$anschaffungswert = $oBetriebsmittel->anschaffungswert;
			$hoehe = $oBetriebsmittel->hoehe;
			$breite = $oBetriebsmittel->breite;
			$tiefe = $oBetriebsmittel->tiefe;
			$verplanen = $oBetriebsmittel->verplanen;

			$bestellung_id_old=$bestellung_id;
			$bestelldetail_id_old=$bestelldetail_id;

			$oBetriebsmittel_betriebsmittelstatus->result=array();
			$oBetriebsmittel_betriebsmittelstatus->errormsg='';
			if ($oBetriebsmittel_betriebsmittelstatus->load_last_betriebsmittel_id($betriebsmittel_id))
			{
				$betriebsmittelstatus_kurzbz=$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz;
			}
			else
				$errormsg[]=$oBetriebsmittel_betriebsmittelstatus->errormsg;
			// suchen eine Person zum Betriebsmittel  - Entliehen an eine Person
			$oBetriebsmittelperson->result=array();
			$oBetriebsmittelperson->errormsg='';
			if ($oBetriebsmittelperson->load_betriebsmittelpersonen($betriebsmittel_id))
			{
				$person_id=($oBetriebsmittelperson->retouram?'':$oBetriebsmittelperson->person_id);
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

		$bestelldetail = new wawi_bestelldetail();
		$bestellung = new wawi_bestellung();

		// Bestellposition
		if ($bestelldetail_id)
		{
			if(!$bestelldetail->load($bestelldetail_id))
				$errormsg[]=$bestelldetail->errormsg;
			$bestelldetail->result[] = $bestelldetail;

			if($anschaffungswert=='')
			{
				$anschaffungswert = ($bestelldetail->preisprove/100*(100+$bestelldetail->mwst));
				$anschaffungswert = number_format(str_replace(',','.',$anschaffungswert),2,'.','');
			}
		}
		else
		{
			//if(!$bestelldetail->getAllDetailsFromBestellung($bestellung_id))
			//	$errormsg[]=$bestelldetail->errormsg;
		}

		//Bestellung
		if (!$bestellung->load($bestellung_id))
			$errormsg[]=$bestellung->errormsg;
		else
		{
			$verwendung=trim($bestellung->titel);
			$besteller=$bestellung->besteller_uid;

			$kostenstelle = new wawi_kostenstelle();
			$kostenstelle->load($bestellung->kostenstelle_id);
			$oe_kurzbz=$kostenstelle->oe_kurzbz;
			$anmerkung.=trim($bestellung->bemerkung);
			$bestellstatus = new wawi_bestellstatus();
			$bestellstatus->getStatiFromBestellung('Lieferung', $bestellung_id);
			$anschaffungsdatum = $bestellstatus->datum;

			foreach($bestelldetail->result as $row)
			{
				if (isset($row->beschreibung))
					$beschreibung.=($beschreibung?"\n":'').trim($row->beschreibung).' '.trim($row->artikelnummer);

				/*
			  	$verwendung=trim($row->kostenstelle_bezeichnung);
				if (isset($row->konto_beschreibung))
					$verwendung.=($verwendung?"\n":'').trim($row->konto_beschreibung);

			  	$hersteller=trim($row->firmenname);
				*/
				if(!$anzahl_lock)
			  	$anzahl=trim(isset($row->menge)?$row->menge:$anzahl);
			}
			$beschreibung = mb_substr($beschreibung, 0, 256);
		}
	}


// ------------------------------------------------------------------------------------------
// HTML Output
// ------------------------------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Inventar</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">

<!--		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script> -->
<!--		<script src="../../vendor/components/jqueryui/jquery-ui.min.js" type="text/javascript"></script> -->
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

		<style type="text/css">
		table.navbar td
		{
			text-align: left;
		}
		</style>
		<script type="text/javascript">
			//Formatiert den Output der Autocomplete Elemente
			function formatItem(row)
			{
				return row[0] + ' <br>' + row[1];
			}

			// Prueft die laenge einer textarea
			function checklength(item, maxlength)
			{
				if(item.value.length>maxlength-1)
				{
					item.value=item.value.substring(0,item.value.length-1);
				}
			}

			function SubmitOhneVorlage()
			{
				first = document.getElementById('bestellung_id_array0')
				document.getElementById('bestellung_id').value=first.value;
				document.sendform.submit();
			}

			function SubmitOhneVorlageDetail()
			{
				first = document.getElementById('bestelldetail_id_array0')
				document.getElementById('bestelldetail_id').value=first.value;
				document.sendform.submit();
			}
		</script>
	</head>
	<body>
		<h1>&nbsp;Inventar&nbsp;</h1>
		<form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
<?php
if($betriebsmittel_id!='' || $anzahl_lock)
	echo '<input type="hidden" name="anzahl_lock" value="1">';
?>
		<fieldset>
			<legend>Vorlage&nbsp;&nbsp;&nbsp;Anzahl:
			<select  id="anzahl" name="anzahl" onchange="document.sendform.submit();">
				<?php
					for ($i=1;$i<100 ;$i++)
						echo '<option '.($anzahl==$i?' selected="selected" ':'').' value="'.$i.'">'.$i.'</option>';
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
<!--								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="bestellung_id" name="bestellung_id" size="10" value="<?php //echo $bestellung_id;?>"> -->
								<input id="bestellung_id" name="bestellung_id" size="10" value="<?php echo $bestellung_id;?>">
								<script type="text/javascript" language="JavaScript1.2">
									$(document).ready(function()
									{
										$('#bestellung_id').autocomplete({
											source: "inventar_autocomplete.php?work=wawi_bestellung_id",
											minLength:5,
											response: function(event, ui)
											{
												//Value und Label fuer die Anzeige setzen
												for(i in ui.content)
												{
													ui.content[i].value=ui.content[i].bestellung_id;
													ui.content[i].label=ui.content[i].bestellung_id+', '+ui.content[i].insertamum+', '+ui.content[i].bestell_nr+', '+ui.content[i].titel+', '+ui.content[i].bemerkung;
												}
											},
											select: function(event, ui)
											{
												ui.item.value=ui.item.bestellung_id;
												setTimeout('document.sendform.submit()',300);
											}
										});
/*										  $('#bestellung_id').autocomplete('inventar_autocomplete.php',
										  {
											minChars:5,
											matchSubset:1,matchContains:1,
											width:500,
											formatItem:formatItem,
											extraParams:{'work':'wawi_bestellung_id'
														,'oe_kurzbz':$("#oe_kurzbz").val()
														,'hersteller':$("#hersteller").val()}
										  }); */
								  });
								</script>
								<input style="display:none" id="bestellung_id_old" name="bestellung_id_old" value="<?php echo $bestellung_id;?>">
							</td>
							<td>&nbsp;<label for="bestelldetail_id">Bestelldetail ID</label>&nbsp;
<!--								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="bestelldetail_id" name="bestelldetail_id" size="6" value="<?php //echo $bestelldetail_id;?>"> -->
								<input id="bestelldetail_id" name="bestelldetail_id" size="6" value="<?php echo $bestelldetail_id;?>">
								<script type="text/javascript" language="JavaScript1.2">
									$(document).ready(function()
									{
										$('#bestelldetail_id').autocomplete({
											source: function(request, response)
											{
												$.ajax({
													url: "inventar_autocomplete.php",
													datatype:"json",
													data: {
														term: request.term,
														work: 'wawi_bestelldetail_id',
														bestellung_id: $('#bestellung_id').val()
													},
													success: function(data)
													{
														data=eval(data);
														 response($.map(data, function(item)
														 {
															return {
																value:item.bestelldetail_id,
																label:item.bestelldetail_id+', '+item.beschreibung+' '+item.artikelnummer+' Preis VE '+item.preisprove+', Menge '+item.menge
															}
														}));
													}
												});
											},
											minLength:1,
											select: function(event, ui)
											{
												$('#bestelldetail_id').val(ui.item.value);
												setTimeout('document.sendform.submit()',300);
											}

										});
/*										  $('#bestelldetail_id').autocomplete('inventar_autocomplete.php',
										  {
											minChars:1,
											matchSubset:1,matchContains:1,
											width:500,
											formatItem:formatItem,
											extraParams:{'work':'wawi_bestelldetail_id'
														,'bestellung_id':$("#bestellung_id").val()
														}
										  }); */
								  });
								</script>
								<input style="display:none" id="bestelldetail_id_old" name="bestelldetail_id_old" value="<?php echo $bestelldetail_id;?>">
							</td>

							<td>&nbsp;<label for="hersteller">Hersteller</label>&nbsp;
							<input id="hersteller" name="hersteller" type="text" size="35" maxlength="120" value="<?php echo $hersteller;?>">
									<script type="text/javascript" language="JavaScript1.2">
									$(document).ready(function()
									{
										$('#hersteller').autocomplete({
											source: "inventar_autocomplete.php?work=hersteller",
											minLength:3,
											response: function(event, ui)
											{
												//Value und Label fuer die Anzeige setzen
												for(i in ui.content)
												{
													ui.content[i].value=ui.content[i].hersteller;
													ui.content[i].label=ui.content[i].hersteller;
												}
											},
											select: function(event, ui)
											{
												ui.item.value=ui.item.hersteller;
											}
										});
/*										  $('#hersteller').autocomplete('inventar_autocomplete.php',
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:400,
											formatItem:formatItem,
											extraParams:{'work':'hersteller' }
										  }); */
								  });
								</script>
							</td>
						</tr>
					</table>
					<table class="navbar">
						<tr>
							<td>&nbsp;<label for="betriebsmitteltyp">Betriebsmitteltyp</label>&nbsp;
								<select id="betriebsmitteltyp" name="betriebsmitteltyp">
										<?php
										for ($i=0;$i<count($resultBetriebsmitteltyp) ;$i++)
										{
											if ($resultBetriebsmitteltyp[$i]->betriebsmitteltyp)
												echo '<option '.($betriebsmitteltyp==$resultBetriebsmitteltyp[$i]->betriebsmitteltyp?' selected="selected" ':'').' value="'.$resultBetriebsmitteltyp[$i]->betriebsmitteltyp.'">'.($resultBetriebsmitteltyp[$i]->beschreibung=='NULL' || empty($resultBetriebsmitteltyp[$i]->beschreibung)?$resultBetriebsmitteltyp[$i]->betriebsmitteltyp:$resultBetriebsmitteltyp[$i]->beschreibung).'&nbsp;('.$resultBetriebsmitteltyp[$i]->typ_code.')</option>';
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
<!--								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="ort_kurzbz" name="ort_kurzbz" size="16" value="<?php //echo $ort_kurzbz;?>"> -->
								<input id="ort_kurzbz" name="ort_kurzbz" size="16" value="<?php echo $ort_kurzbz;?>">
									<script type="text/javascript" language="JavaScript1.2">
									$(document).ready(function()
									{
										$('#ort_kurzbz').autocomplete({
											source: "inventar_autocomplete.php?work=ort",
											minLength:2,
											response: function(event, ui)
											{
												//Value und Label fuer die Anzeige setzen
												for(i in ui.content)
												{
													ui.content[i].value=ui.content[i].ort_kurzbz;
													ui.content[i].label=ui.content[i].ort_kurzbz+' '+ui.content[i].bezeichnung;
												}
											},
											select: function(event, ui)
											{
												ui.item.value=ui.item.ort_kurzbz;
												setTimeout('document.sendform.submit()',300);
											}
										});

/*										$('#ort_kurzbz').autocomplete('inventar_autocomplete.php',
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:300,
											formatItem:formatItem,
											extraParams:{'work':'ort' }
										  }); */
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
<!--								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="oe_kurzbz" name="oe_kurzbz" size="13" value="<?php // echo $oe_kurzbz;?>"> -->
								<input id="oe_kurzbz" name="oe_kurzbz" size="13" value="<?php echo $oe_kurzbz;?>">
								<script type="text/javascript" language="JavaScript1.2">
									$(document).ready(function()
									{
										$('#oe_kurzbz').autocomplete({
											source: "inventar_autocomplete.php?work=organisationseinheit",
											minLength:2,
											response: function(event, ui)
											{
												//Value und Label fuer die Anzeige setzen
												for(i in ui.content)
												{
													ui.content[i].value=ui.content[i].oe_kurzbz;
													ui.content[i].label=ui.content[i].oe_kurzbz+' '+ui.content[i].bezeichnung+' '+ui.content[i].organisationseinheittyp;
												}
											},
											select: function(event, ui)
											{
												ui.item.value=ui.item.oe_kurzbz;
												setTimeout('document.sendform.submit()',300);
											}
										});

/*										  $('#oe_kurzbz').autocomplete('inventar_autocomplete.php',
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:400,
											formatItem:formatItem,
											extraParams:{'work':'organisationseinheit' }
										  }); */
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
										$personen_namen='<a href="mailto:.'.$besteller.'">'.$besteller.'</a>';
								?>

						<td>&nbsp;<label for="person_id">Mitarbeiter</label>&nbsp;
<!--								<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" id="person_id" name="person_id" size="13" value="<?php echo $person_id; ?>"> -->
								<input id="person_id" name="person_id" size="13" value="<?php echo $person_id; ?>">
									<script type="text/javascript" language="JavaScript1.2">
									$(document).ready(function()
									{
										$('#person_id').autocomplete({
											source: "inventar_autocomplete.php?work=person",
											minLength:2,
											response: function(event, ui)
											{
												//Value und Label fuer die Anzeige setzen
												for(i in ui.content)
												{
													ui.content[i].value=ui.content[i].person_id;
													ddlabel = ui.content[i].person_id+' '+ui.content[i].anrede+' '+ui.content[i].titelpre+' '+ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].funktion;
													if(ui.content[i].aktiv=='t')
													{
														ddlabel=ddlabel+'(Aktiv)';
													}
													else
													{
														ddlabel=ddlabel+'(Inaktiv)';
													}
													ui.content[i].label=ddlabel;
													//ui.content[i].label=ui.content[i].person_id+' '+ui.content[i].anrede+' '+ui.content[i].titelpre+' '+ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].funktion;
												}
											},
											select: function(event, ui)
											{
												ui.item.value=ui.item.person_id;
												setTimeout('document.sendform.submit()',300);
											}
										});
/*										  $('#person_id').autocomplete('inventar_autocomplete.php',
										  {
											minChars:2,
											matchSubset:1,matchContains:1,
											width:400,
											formatItem:formatItem,
											extraParams:{'work':'person'}
										  }); */
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
							<td><textarea id="beschreibung" name="beschreibung" cols="80" rows="3" onkeypress="checklength(this,256)"><?php echo $beschreibung;?></textarea></td>
						</tr>
						<tr>
							<td valign="top">&nbsp;<label for="anmerkung">Anmerkung</label>&nbsp;</td>
							<td><textarea id="anmerkung" name="anmerkung" cols="80" rows="5"><?php echo $anmerkung;?></textarea></td>

						</tr>
						<tr>
							<td valign="top">&nbsp;<label for="verwendung" >Verwendung</label>&nbsp;</td>
							<td><textarea id="verwendung" name="verwendung" cols="80" rows="3" onkeypress="checklength(this,256)"><?php echo $verwendung;?></textarea></td>
						</tr>
					</table>

					<table class="navbar">
						<tr>

							<td>&nbsp;<label for="leasing_bis">Leasing bis</label>&nbsp;</td>
							<td>
								<input id="leasing_bis" name="leasing_bis" size="10" maxlength="11" value="<?php echo $datum_obj->formatDatum($leasing_bis,'d.m.Y');?>">
								<script type="text/javascript" language="JavaScript1.2">
								$(document).ready(function()
								{
									$( "#leasing_bis" ).datepicker($.datepicker.regional['de']);
								});
								</script>
							</td>

							<td>&nbsp;<label for="afa">AfA Jahre</label>&nbsp;</td>
							<td>
								<select id="afa" name="afa" >
								<?php
									for ($i=0;$i<20;$i++)
										echo '<option  '.($afa==$i?' selected="selected" ':'').'  value="'.$i.'">'.$i.' Jahre</option>';
								?>
								</select>
							</td>
							<td>&nbsp;<label for="anschaffungsdatum">Anschaffungsdatum</label>&nbsp;</td>
							<td>
								<input id="anschaffungsdatum" name="anschaffungsdatum" size="10" maxlength="11" value="<?php echo $datum_obj->formatDatum($anschaffungsdatum,'d.m.Y');?>">
								<script type="text/javascript" language="JavaScript1.2">
								$(document).ready(function()
								{
									$( "#anschaffungsdatum" ).datepicker($.datepicker.regional['de']);
								});
								</script>
							</td>
							<td>&nbsp;<label for="anschaffungswert">Anschaffungswert (brutto)</label>&nbsp;</td>
							<td>
								<input id="anschaffungswert" name="anschaffungswert" size="10" maxlength="11" value="<?php echo $anschaffungswert;?>">
							</td>

						</tr>
						<tr>
							<td>&nbsp;<label for="hoehe">Höhe in Meter</label>&nbsp;</td>
							<td>
								<input id="hoehe" name="hoehe" size="4" maxlength="8" value="<?php echo $hoehe;?>">
							</td>
							<td>&nbsp;<label for="breite">Breite in Meter</label>&nbsp;</td>
							<td>
								<input id="breite" name="breite" size="4" maxlength="8" value="<?php echo $breite;?>">
							</td>
							<td>&nbsp;<label for="tiefe">Tiefe in Meter</label>&nbsp;</td>
							<td>
								<input id="tiefe" name="tiefe" size="4" maxlength="8" value="<?php echo $tiefe;?>">
							</td>
							<td>&nbsp;<label for="tiefe">Verplanen</label>&nbsp;</td>
							<td>
								<input type="checkbox" id="verplanen" name="verplanen" <?php echo ($verplanen?'checked=checked':'');?>>
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
				        	 $("div#container").show("slow");         // div# langsam oeffnen
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
	<!-- VORLAGE ENDE -->

<!-- DATEN ANFANG -->
<?php
@flush();

$betriebsmittel_id_array=(isset($_REQUEST['betriebsmittel_id_array'])?$_REQUEST['betriebsmittel_id_array']:array());

$inventarnummer_array=(isset($_REQUEST['inventarnummer_array'])?$_REQUEST['inventarnummer_array']:array());
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
$anschaffungsdatum_array=(isset($_REQUEST['anschaffungsdatum_array'])?$_REQUEST['anschaffungsdatum_array']:array());
$anschaffungswert_array=(isset($_REQUEST['anschaffungswert_array'])?$_REQUEST['anschaffungswert_array']:array());
$hoehe_array=(isset($_REQUEST['hoehe_array'])?$_REQUEST['hoehe_array']:array());
$breite_array=(isset($_REQUEST['breite_array'])?$_REQUEST['breite_array']:array());
$tiefe_array=(isset($_REQUEST['tiefe_array'])?$_REQUEST['tiefe_array']:array());
$verplanen_array=(isset($_REQUEST['verplanen_array'])?$_REQUEST['verplanen_array']:array());

for ($pos=0;$pos<$anzahl;$pos++)
{
	$errormsg=array();

  	//$vorlage=trim(isset($_REQUEST['vorlage'.$pos]) ?$_REQUEST['vorlage'.$pos]:'false');

	$betriebsmittel_id_array[$pos]=trim(isset($betriebsmittel_id_array[$pos])?trim($betriebsmittel_id_array[$pos]):$betriebsmittel_id);
	$inventarnummer_array[$pos]=trim(isset($inventarnummer_array[$pos])?trim($inventarnummer_array[$pos]):$inventarnummer);
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
	$anschaffungsdatum_array[$pos]=trim(isset($anschaffungsdatum_array[$pos]) && $work=='save' ?trim($anschaffungsdatum_array[$pos]):$anschaffungsdatum);
	$anschaffungswert_array[$pos]=trim(isset($anschaffungswert_array[$pos]) && $work=='save' ?trim($anschaffungswert_array[$pos]):$anschaffungswert);
	$hoehe_array[$pos]=isset($hoehe_array[$pos]) && $work=='save' ?trim($hoehe_array[$pos]):$hoehe;
	$breite_array[$pos]=isset($breite_array[$pos]) && $work=='save' ?trim($breite_array[$pos]):$breite;
	$tiefe_array[$pos]=isset($tiefe_array[$pos]) && $work=='save' ?trim($tiefe_array[$pos]):$tiefe;
	//$verplanen_array[$pos]=isset($verplanen_array[$pos]) && $work=='save' ?trim($verplanen_array[$pos]):$verplanen;

	if ($work=='save')
	{
		if($inventarnummer_array[$pos]!='')
		{
			$oBetriebsmittel = new betriebsmittel();
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->debug=$debug;
			$oBetriebsmittel->errormsg='';

			$oBetriebsmittel->new=false;
			if (!$oBetriebsmittel->load($betriebsmittel_id_array[$pos]))
			{
				$oBetriebsmittel->new=true;
				$oBetriebsmittel->betriebsmittel_id=null;
				$oBetriebsmittel->insertamum = date('Y-m-d H:i:s');
				$oBetriebsmittel->insertvon=$uid;
				$oBetriebsmittel->inventuramum = date('Y-m-d H:i:s');
				$oBetriebsmittel->inventurvon = $uid;
			}
			$betriebsmittel_id_array[$pos]=$oBetriebsmittel->betriebsmittel_id;

			$oBetriebsmittel->beschreibung=$beschreibung_array[$pos];
		    $oBetriebsmittel->betriebsmitteltyp=$betriebsmitteltyp_array[$pos];
		    $oBetriebsmittel->inventarnummer=$inventarnummer_array[$pos];
		    $oBetriebsmittel->reservieren=false;
		    $oBetriebsmittel->ort_kurzbz=$ort_kurzbz_array[$pos];

		    $oBetriebsmittel->updatevon=$uid;
		    $oBetriebsmittel->updateamum=date('Y-m-d H:i:s');

			$oBetriebsmittel->oe_kurzbz=$oe_kurzbz_array[$pos];
			$oBetriebsmittel->hersteller=$hersteller_array[$pos];
			$oBetriebsmittel->seriennummer=$seriennummer_array[$pos];
			$oBetriebsmittel->bestellung_id=$bestellung_id_array[$pos];
			$oBetriebsmittel->bestelldetail_id=$bestelldetail_id_array[$pos];
			$oBetriebsmittel->afa=$afa_array[$pos];
			$oBetriebsmittel->verwendung=$verwendung_array[$pos];
			$oBetriebsmittel->anmerkung=$anmerkung_array[$pos];
			$oBetriebsmittel->leasing_bis=$datum_obj->formatDatum($leasing_bis_array[$pos],'Y-m-d');

			//wenn kein Anschaffungsdatum eingetragen ist und eine Bestellung zugeordnet ist,
			//wird das lieferdatum der Bestellung uebernommen
			if($oBetriebsmittel->bestellung_id!='' && $anschaffungsdatum_array[$pos]=='')
			{
				$bestellung = new wawi_bestellstatus();
				$bestellung->getStatiFromBestellung('Lieferung', $oBetriebsmittel->bestellung_id);
				$anschaffungsdatum_array[$pos]=$bestellung->datum;
			}

			$oBetriebsmittel->anschaffungsdatum = $datum_obj->formatDatum($anschaffungsdatum_array[$pos],'Y-m-d');

			//Wenn kein Anschaffungswert eingetragen ist, und eine BestelldetailID angegeben ist,
			//wird der Anschaffungswert von der Bestellung uebernommen
			if($oBetriebsmittel->bestelldetail_id!='' && $anschaffungswert_array[$pos]=='')
			{
				$bestellung = new wawi_bestelldetail();
				$bestellung->load($oBetriebsmittel->bestelldetail_id);
				$anschaffungswert_array[$pos]=($bestellung->preisprove/100*(100+$bestellung->mwst));
				$anschaffungswert_array[$pos]=number_format(str_replace(',','.',$anschaffungswert_array[$pos]),2,'.','');
			}
			if($anschaffungswert_array[$pos]!='')
				$oBetriebsmittel->anschaffungswert = number_format(str_replace(',','.',$anschaffungswert_array[$pos]),2,'.','');
			else
				$oBetriebsmittel->anschaffungswert ='';
			if($hoehe_array[$pos]!='')
				$oBetriebsmittel->hoehe = number_format(str_replace(',','.',$hoehe_array[$pos]),2,'.','');
			else
				$oBetriebsmittel->hoehe = '';

			if($breite_array[$pos]!='')
				$oBetriebsmittel->breite = number_format(str_replace(',','.',$breite_array[$pos]),2,'.','');
			else
				$oBetriebsmittel->breite = '';

			if($tiefe_array[$pos]!='')
				$oBetriebsmittel->tiefe = number_format(str_replace(',','.',$tiefe_array[$pos]),2,'.','');
			else
				$oBetriebsmittel->tiefe = '';

			if(!isset($verplanen_array[$pos]))
				$oBetriebsmittel->verplanen = false;
			else
				$oBetriebsmittel->verplanen = true;

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
					if (strtoupper($oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz)==strtoupper($betriebsmittelstatus_kurzbz_array[$pos]) )
					{
						$oBetriebsmittel_betriebsmittelstatus->new=false;
					}
					else
					{
						$oBetriebsmittel_betriebsmittelstatus->datum=date('Y-m-d');
						$oBetriebsmittel_betriebsmittelstatus->insertvon=$uid;
						$oBetriebsmittel_betriebsmittelstatus->insertamum=date('Y-m-d H:i:s');
					}
				}
				else
				{
					$oBetriebsmittel_betriebsmittelstatus->insertvon=$uid;
					$oBetriebsmittel_betriebsmittelstatus->insertamum=date('Y-m-d H:i:s');
				}
				$oBetriebsmittel_betriebsmittelstatus->datum=trim($oBetriebsmittel_betriebsmittelstatus->datum?$oBetriebsmittel_betriebsmittelstatus->datum:date('Y-m-d'));
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittel_id=$betriebsmittel_id_array[$pos];
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz=$betriebsmittelstatus_kurzbz_array[$pos];

			    $oBetriebsmittel_betriebsmittelstatus->updatevon=$uid;
			    $oBetriebsmittel_betriebsmittelstatus->updateamum=date('Y-m-d H:i:s');

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

				//wenn sich die Personenzuordnung aendert, dann wird die alte Personenzuordnung beendet
				if ($person_id_old_array[$pos]
				&& $person_id_old_array[$pos]!=$person_id_array[$pos])
				{
					$oBetriebsmittelperson = new betriebsmittelperson();
					$oBetriebsmittelperson->result=array();
					$oBetriebsmittelperson->debug=$debug;
					$oBetriebsmittelperson->errormsg='';
					// Betriebsmittel lesen zur Person
					if ($oBetriebsmittelperson->load_betriebsmittelpersonen($betriebsmittel_id_array[$pos],$person_id_old_array[$pos]))
					{
						$oBetriebsmittelperson->errormsg='';
					    $oBetriebsmittelperson->betriebsmittel_id=$betriebsmittel_id_array[$pos];
					    $oBetriebsmittelperson->retouram=date('Y-m-d');
					    $oBetriebsmittelperson->updatevon=$uid;
					    $oBetriebsmittelperson->updateamum=date('Y-m-d H:i:s');

						$oBetriebsmittelperson->new=false;
		 				if (!$oBetriebsmittelperson->save())
							$errormsg[]=$oBetriebsmittelperson->errormsg;
					}
					else
					{
						$errormsg[] = $oBetriebsmittelperson->errormsg;
					}
				}

				// Entliehen an eine Person
				if ($person_id_array[$pos])
				{
					$oBetriebsmittelperson = new betriebsmittelperson();
					$oBetriebsmittelperson->result=array();
					$oBetriebsmittelperson->debug=$debug;
					$oBetriebsmittelperson->errormsg='';
					if (!$oBetriebsmittelperson->load_betriebsmittelpersonen($betriebsmittel_id_array[$pos],$person_id_array[$pos])
					|| $oBetriebsmittelperson->retouram!='')
					{
						//wenn das Betriebsmittel dieser Person noch nicht zugeordnet ist, oder
						//es in der Zwischenzeit schon retourniert hat, dann zuordnen
						$oBetriebsmittelperson->new=true;

						$oBetriebsmittelperson->result=array();
						$oBetriebsmittelperson->debug=$debug;
						$oBetriebsmittelperson->errormsg='';

					    $oBetriebsmittelperson->betriebsmittel_id=$betriebsmittel_id_array[$pos];
					    $oBetriebsmittelperson->person_id=$person_id_array[$pos];
					    //$oBetriebsmittelperson->anmerkung=$anmerkung_array[$pos];
					    $oBetriebsmittelperson->kaution=0;
					    $oBetriebsmittelperson->retouram=null;
					    $oBetriebsmittelperson->betriebsmitteltyp=$betriebsmitteltyp_array[$pos];
					    $oBetriebsmittelperson->ausgegebenam=date('Y-m-d');
					    $oBetriebsmittelperson->insertvon=$uid;
					    $oBetriebsmittelperson->updatevon=$uid;
		 				if (!$oBetriebsmittelperson->save())
							$errormsg[]=$oBetriebsmittelperson->errormsg;
					}
				}
			}
			else
			{
				$errormsg[]=$oBetriebsmittel->errormsg;
			}
		}
		else
		{
			$errormsg[]='Fehler: Es muss eine Inventarnummer eingetragen werden';
		}
	}
		?>

	<div id="container_array">

		<div id="container_box<?php echo $pos; ?>">
			<fieldset><legend> <?php echo (1 + $pos ); ?>) Inventar ID <a href="inventar.php?betriebsmittel_id=<?php echo $betriebsmittel_id_array[$pos]; ?>"><?php echo $betriebsmittel_id_array[$pos]; ?></a> </legend>
				<table class="navbar">
					<tr>
						<th><?php echo (1 + $pos ); ?><input style="display:none;" id="betriebsmittel_id_array<?php echo $pos; ?>" name="betriebsmittel_id_array[]" value="<?php echo $betriebsmittel_id_array[$pos]; ?>"></th>

						<td>&nbsp;<label for="inventarnummer_array<?php echo $pos; ?>">Inventarnummer</label>&nbsp;</td>
						<td><input id="inventarnummer_array<?php echo $pos; ?>" name="inventarnummer_array[]" size="32" value="<?php echo $inventarnummer_array[$pos]; ?>"></td>

						<td>&nbsp;<label for="seriennummer_array<?php echo $pos; ?>">Seriennummer</label>&nbsp;</td>
						<td><input id="seriennummer_array<?php echo $pos; ?>" name="seriennummer_array[]" size="32" value="<?php echo $seriennummer_array[$pos]; ?>"></td>
						<td id="bcTarget<?php echo $pos; ?>">
							<table>
								<tr>
									<td>druck&nbsp;<img border="0" src="../../skin/images/printer.png" title="drucken" > </td>
								</tr>
							</table>

							<script type="text/javascript" language="JavaScript1.2">
							   $(document).ready(function()           // Prueft, ob das Dokument geladen ist
							   {
							  	 $("td#bcTarget<?php echo $pos; ?>").click(function(event)
								 {
										var PrintWin=window.open('etiketten.php?inventarnummer=<?php echo urlencode($inventarnummer_array[$pos]); ?>','Etik','copyhistory=no,directories=no,location=no,dependent=yes,toolbar=no,status=no,menubar=no,resizable=yes,scrollbars=yes,width=400,height=300,left=20, top=20');
										if (PrintWin)
										{
											PrintWin.focus();
										}
							   });
							});

						</script>
						</td>
					</tr>
				</table>

			<div id="container<?php echo $pos; ?>"  style="display:<?php echo ($vorlage && $vorlage=='false'?'block':'none'); ?>;" >

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
				      if ($("#vorlage<?php echo $pos; ?>").val() == 'true')
					  {
				         $("div#container<?php echo $pos; ?>").show("slow");         // div# langsam oeffnen
				         $("#vorlage<?php echo $pos; ?>").val('false');
			    	  }
					  else
					  {
			        	 $("div#container<?php echo $pos; ?>").hide("slow");         // div# langsam verbergen
				         $("#vorlage<?php echo $pos; ?>").val('true');
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
<!--												<input id="bestellung_id_array<?php echo $pos; ?>" <?php echo ($vorlage=='false'?"onchange=\"if (this.value.length>0) {setTimeout('SubmitOhneVorlage()',1300);}\"":""); ?> name="bestellung_id_array[]" size="10" value="<?php echo $bestellung_id_array[$pos]; ?>"> -->
												<input id="bestellung_id_array<?php echo $pos; ?>" name="bestellung_id_array[]" size="10" value="<?php echo $bestellung_id_array[$pos]; ?>">
												<script type="text/javascript" language="JavaScript1.2">
													$(document).ready(function()
													{
														$('#bestellung_id_array<?php echo $pos; ?>').autocomplete({
															source: "inventar_autocomplete.php?work=wawi_bestellung_id",
															minLength:2,
															response: function(event, ui)
															{
																//Value und Label fuer die Anzeige setzen
																for(i in ui.content)
																{
																	ui.content[i].value=ui.content[i].bestellung_id;
																	ui.content[i].label=ui.content[i].bestellung_id+', '+ui.content[i].insertamum+', '+ui.content[i].bestell_nr+', '+ui.content[i].titel+', '+ui.content[i].bemerkung;
																}
															},
															select: function(event, ui)
															{
																ui.item.value=ui.item.bestellung_id;
															}
														});
														/*  $('#bestellung_id_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php',
														  {
															minChars:2,
															matchSubset:1,matchContains:1,
															width:500,
															formatItem:formatItem,
															extraParams:{'work':'wawi_bestellung_id'
																		,'oe_kurzbz':$("#oe_kurzbz_array<?php echo $pos; ?>").val()
																		,'hersteller':$("#hersteller_array<?php echo $pos; ?>").val()}
														  }); */
												  });
												</script>
											</td>
											<td>&nbsp;<label for="bestelldetail_id_array<?php echo $pos; ?>">Bestelldetail ID</label>&nbsp;
												<input id="bestelldetail_id_array<?php echo $pos; ?>" <?php echo ($vorlage=='false'?"onchange=\"if (this.value.length>0) {setTimeout('SubmitOhneVorlageDetail()',1300);}\"":""); ?> name="bestelldetail_id_array[]" size="6" value="<?php echo $bestelldetail_id_array[$pos]; ?>">
												<script type="text/javascript" language="JavaScript1.2">
													$(document).ready(function()
													{
														$('#bestelldetail_id_array<?php echo $pos; ?>').autocomplete({
															source: function(request, response)
															{
																$.ajax({
																	url: "inventar_autocomplete.php",
																	datatype:"json",
																	data: {
																		term: request.term,
																		work: 'wawi_bestelldetail_id',
																		bestellung_id: $('#bestellung_id_array<?php echo $pos; ?>').val()
																	},
																	success: function(data)
																	{
																		data=eval(data);
																		 response($.map(data, function(item)
																		 {
																			return {
																				value:item.bestelldetail_id,
																				label:item.bestelldetail_id+', '+item.beschreibung+' '+item.artikelnummer+' Preis VE '+item.preisprove+', Menge '+item.menge

																			}
																		}))
																	}
																});
															},
															minLength:1,
														});
/*														  $('#bestelldetail_id_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php',
														  {
															minChars:1,
															matchSubset:1,matchContains:1,
															width:500,
															formatItem:formatItem,
															extraParams:{'work':'wawi_bestelldetail_id'
																		,'bestellung_id':$("#bestellung_id_array<?php echo $pos; ?>").val()
																		,'oe_kurzbz':$("#oe_kurzbz_array<?php echo $pos; ?>").val()
																		,'hersteller':$("#hersteller_array<?php echo $pos; ?>").val()}
														  }); */
												  });
												</script>

											</td>

											<td>&nbsp;<label for="hersteller_array<?php echo $pos; ?>">Hersteller</label>&nbsp;
											<input id="hersteller_array<?php echo $pos; ?>" name="hersteller_array[]" type="text" size="35" maxlength="120" value="<?php echo $hersteller_array[$pos]; ?>">
												<script type="text/javascript" language="JavaScript1.2">
													$(document).ready(function()
													{
														$('#hersteller_array<?php echo $pos; ?>').autocomplete({
															source: "inventar_autocomplete.php?work=hersteller",
															minLength:2,
															response: function(event, ui)
															{
																//Value und Label fuer die Anzeige setzen
																for(i in ui.content)
																{
																	ui.content[i].value=ui.content[i].hersteller;
																	ui.content[i].label=ui.content[i].hersteller;
																}
															},
															select: function(event, ui)
															{
																ui.item.value=ui.item.hersteller;
															}
														});
														/*  $('#hersteller_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php',
														  {
															minChars:2,
															matchSubset:1,matchContains:1,
															width:400,
															formatItem:formatItem,
															extraParams:{'work':'hersteller' }
														  }); */
												  });
												</script>
											</td>
										</tr>
									</table>


							<table class="navbar">
								<tr>
									<td>&nbsp;<label for="betriebsmitteltyp_array<?php echo $pos; ?>">Betriebsmitteltyp</label>&nbsp;
										<select id="betriebsmitteltyp_array<?php echo $pos; ?>" name="betriebsmitteltyp_array[]">
										<?php
										for ($i=0;$i<count($resultBetriebsmitteltyp) ;$i++)
										{
											if ($resultBetriebsmitteltyp[$i]->betriebsmitteltyp)
												echo '<option '.($betriebsmitteltyp_array[$pos]==$resultBetriebsmitteltyp[$i]->betriebsmitteltyp?' selected="selected" ':'').' value="'.$resultBetriebsmitteltyp[$i]->betriebsmitteltyp.'">'.($resultBetriebsmitteltyp[$i]->beschreibung=='NULL' || empty($resultBetriebsmitteltyp[$i]->beschreibung)?$resultBetriebsmitteltyp[$i]->betriebsmitteltyp:$resultBetriebsmitteltyp[$i]->beschreibung).'&nbsp;('.$resultBetriebsmitteltyp[$i]->typ_code.')</option>';
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
										<input id="ort_kurzbz_array<?php echo $pos; ?>" name="ort_kurzbz_array[]" size="16" value="<?php echo $ort_kurzbz_array[$pos]; ?>">
											<script type="text/javascript" language="JavaScript1.2">
													$(document).ready(function()
													{
														$('#ort_kurzbz_array<?php echo $pos; ?>').autocomplete({
															source: "inventar_autocomplete.php?work=ort",
															minLength:2,
															response: function(event, ui)
															{
																//Value und Label fuer die Anzeige setzen
																for(i in ui.content)
																{
																	ui.content[i].value=ui.content[i].ort_kurzbz;
																	ui.content[i].label=ui.content[i].ort_kurzbz+' '+ui.content[i].bezeichnung;
																}
															},
															select: function(event, ui)
															{
																ui.item.value=ui.item.ort_kurzbz;
															}
														});
														/*  $('#ort_kurzbz_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php',
														  {
															minChars:2,
															matchSubset:1,matchContains:1,
															width:300,
															formatItem:formatItem,
															extraParams:{'work':'ort' }
														  }); */
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
									<input id="oe_kurzbz_array<?php echo $pos; ?>" name="oe_kurzbz_array[]" size="13" value="<?php echo $oe_kurzbz_array[$pos]; ?>" >
									<script type="text/javascript" language="JavaScript1.2">
										$(document).ready(function()
										{
											$('#oe_kurzbz_array<?php echo $pos; ?>').autocomplete({
												source: "inventar_autocomplete.php?work=organisationseinheit",
												minLength:2,
												response: function(event, ui)
												{
													//Value und Label fuer die Anzeige setzen
													for(i in ui.content)
													{
														ui.content[i].value=ui.content[i].oe_kurzbz;
														ui.content[i].label=ui.content[i].oe_kurzbz+' '+ui.content[i].bezeichnung+' '+ui.content[i].organisationseinheittyp;
													}
												},
												select: function(event, ui)
												{
													ui.item.value=ui.item.oe_kurzbz;
												}
											});
											/*  $('#oe_kurzbz_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php',
											  {
												minChars:2,
												matchSubset:1,matchContains:1,
												width:400,
												formatItem:formatItem,
												extraParams:{'work':'organisationseinheit' }
											  }); */
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
									<input id="person_id_array<?php echo $pos; ?>" name="person_id_array[]" size="13" value="<?php echo $person_id_array[$pos]; ?>" >
									<script type="text/javascript" language="JavaScript1.2">
											$(document).ready(function()
											{
												$('#person_id_array<?php echo $pos; ?>').autocomplete({
													source: "inventar_autocomplete.php?work=person",
													minLength:2,
													response: function(event, ui)
													{
														//Value und Label fuer die Anzeige setzen
														for(i in ui.content)
														{
															ui.content[i].value=ui.content[i].person_id;
															//ui.content[i].label=ui.content[i].person_id+' '+ui.content[i].anrede+' '+ui.content[i].titelpre+' '+ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].funktion;
															ddlabel = ui.content[i].person_id+' '+ui.content[i].anrede+' '+ui.content[i].titelpre+' '+ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].funktion;
															if(ui.content[i].aktiv=='t')
															{
																ddlabel=ddlabel+'(Aktiv)';
															}
															else
															{
																ddlabel=ddlabel+'(Inaktiv)';
															}
															ui.content[i].label=ddlabel;
														}
													},
													select: function(event, ui)
													{
														ui.item.value=ui.item.person_id;
													}
												});
												/*  $('#person_id_array<?php echo $pos; ?>').autocomplete('inventar_autocomplete.php',
												  {
													minChars:2,
													matchSubset:1,matchContains:1,
													width:400,
													formatItem:formatItem,
													extraParams:{'work':'person' }
												  }); */
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
									<td><textarea id="beschreibung_array<?php echo $pos; ?>" name="beschreibung_array[]" cols="80" rows="3" onkeypress="checklength(this,256)"><?php echo $beschreibung_array[$pos]; ?></textarea></td>
								</tr>
								<tr>

									<td valign="top">&nbsp;<label for="anmerkung_array<?php echo $pos; ?>">Anmerkung</label>&nbsp;</td>
									<td><textarea id="anmerkung_array<?php echo $pos; ?>" name="anmerkung_array[]" cols="80" rows="5"><?php echo $anmerkung_array[$pos]; ?></textarea></td>
								</tr>
								<tr>
									<td valign="top">&nbsp;<label for="verwendung_array<?php echo $pos; ?>">Verwendung</label>&nbsp;</td>
									<td><textarea id="verwendung_array<?php echo $pos; ?>" name="verwendung_array[]" cols="80" rows="3" onkeypress="checklength(this,256)"><?php echo $verwendung_array[$pos]; ?></textarea></td>
								</tr>
							</table>

							<table class="navbar">
								<tr>
									<td>&nbsp;<label for="leasing_bis_array<?php echo $pos; ?>">Leasing bis</label>&nbsp;</td>
									<td>
										<input id="leasing_bis_array<?php echo $pos; ?>" name="leasing_bis_array[]" size="10" maxlength="11" value="<?php echo $datum_obj->formatDatum($leasing_bis_array[$pos],'d.m.Y'); ?>">
										<script type="text/javascript" language="JavaScript1.2">
										$(document).ready(function()
										{
												$( "#leasing_bis_array<?php echo $pos; ?>" ).datepicker($.datepicker.regional['de']);
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
									<td>&nbsp;<label for="anschaffungsdatum_array<?php echo $pos; ?>">Anschaffungsdatum</label>&nbsp;</td>
									<td>
										<input id="anschaffungsdatum_array<?php echo $pos; ?>" name="anschaffungsdatum_array[]" size="10" maxlength="11" value="<?php echo $datum_obj->formatDatum($anschaffungsdatum_array[$pos],'d.m.Y'); ?>">
										<script type="text/javascript" language="JavaScript1.2">
										$(document).ready(function()
										{
												$( "#anschaffungsdatum_array<?php echo $pos; ?>" ).datepicker($.datepicker.regional['de']);
										});
										</script>
									</td>
									<td>&nbsp;<label for="anschaffungswert_array<?php echo $pos; ?>">Anschaffungswert (brutto)</label>&nbsp;</td>
									<td>
										<input id="anschaffungswert_array<?php echo $pos; ?>" name="anschaffungswert_array[]" size="10" maxlength="11" value="<?php echo $anschaffungswert_array[$pos]; ?>">
									</td>
								</tr>
								<tr>
									<td>&nbsp;<label for="hoehe_array<?php echo $pos; ?>">Höhe in Meter</label>&nbsp;</td>
									<td>
										<input id="hoehe_array<?php echo $pos; ?>" name="hoehe_array[]" size="4" maxlength="8" value="<?php echo $hoehe_array[$pos];?>">
									</td>
									<td>&nbsp;<label for="breite">Breite in Meter</label>&nbsp;</td>
									<td>
										<input id="breite_array<?php echo $pos; ?>" name="breite_array[]" size="4" maxlength="8" value="<?php echo $breite_array[$pos];?>">
									</td>
									<td>&nbsp;<label for="tiefe">Tiefe in Meter</label>&nbsp;</td>
									<td>
										<input id="tiefe_array<?php echo $pos; ?>" name="tiefe_array[]" size="4" maxlength="8" value="<?php echo $tiefe_array[$pos];?>">
									</td>
									<td>&nbsp;<label for="tiefe">Verplanbar</label>&nbsp;</td>
									<td>
										<input type="checkbox" id="verplanen_array<?php echo $pos; ?>" name="verplanen_array[]" <?php echo (isset($verplanen_array[$pos]) && $verplanen_array[$pos]?'checked=checked':'');?>>
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
					      if ($("#vorlage<?php echo $pos; ?>").val() == 'true')
						  {
					         $("div#container<?php echo $pos; ?>").show("slow");         // div# langsam oeffnen
			    		     $("#vorlage<?php echo $pos; ?>").val('false');
					      }
						  else
						  {
					         $("div#container<?php echo $pos; ?>").hide("slow");         // div# langsam verbergen
			    		     $("#vorlage<?php echo $pos; ?>").val('true');
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
	</div>
<?php
@flush();
} // Ende Anzahl Schleife
?>
	&nbsp;
	<input id="work" name="work" value="" style="display:none;">
	</form>
</body>
</html>
