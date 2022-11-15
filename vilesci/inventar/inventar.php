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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/ort.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/person.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/betriebsmitteltyp.class.php');
require_once('../../include/betriebsmittelstatus.class.php');
require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/wawi_rechnung.class.php');

if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
// Initialisierung
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$default_status_vorhanden='vorhanden'; // Defaultwert fuer Selectfeld - Status

	//------------ Berechtigungen
	$berechtigung_kurzbz='wawi/inventar:begrenzt';
	$recht=false;
	$delete_recht=false;
	$schreib_recht=false;
	$schreib_recht_administration=2; // Admin wert fuer set schreib_recht
	$datum_obj = new datum();

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
  	$inventarnummer=trim((isset($_REQUEST['inventarnummer']) ? $_REQUEST['inventarnummer']:''));
  	$seriennummer=trim((isset($_REQUEST['seriennummer']) ? $_REQUEST['seriennummer']:''));
  	$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
  	$oe_kurzbz=trim((isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz']:''));
  	$beschreibung=trim((isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung']:''));
  	$betriebsmittel_id=trim((isset($_REQUEST['betriebsmittel_id']) ? $_REQUEST['betriebsmittel_id']:''));
  	$betriebsmitteltyp=trim((isset($_REQUEST['betriebsmitteltyp']) ? $_REQUEST['betriebsmitteltyp']:''));
  	$betriebsmittelstatus_kurzbz=trim((isset($_REQUEST['betriebsmittelstatus_kurzbz']) ? $_REQUEST['betriebsmittelstatus_kurzbz']:''));
	$firma_id=trim(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:'');
	$bestellnr=trim(isset($_REQUEST['bestellnr'])?$_REQUEST['bestellnr']:'');
	$bestellung_id=trim(isset($_REQUEST['bestellung_id'])?$_REQUEST['bestellung_id']:'');
	$bestelldetail_id=trim(isset($_REQUEST['bestelldetail_id'])?$_REQUEST['bestelldetail_id']:'');
  	$hersteller=trim((isset($_REQUEST['hersteller']) ? $_REQUEST['hersteller']:''));
	$jahr_monat=trim(isset($_REQUEST['jahr_monat']) ? $_REQUEST['jahr_monat']:'');
  	$afa=trim(isset($_REQUEST['afa']) ? $_REQUEST['afa']:'');
  	$inventur_jahr=trim(isset($_REQUEST['inventur_jahr']) ? $_REQUEST['inventur_jahr']:'');
	$anlage_jahr_monat=trim(isset($_REQUEST['anlage_jahr_monat']) ? $_REQUEST['anlage_jahr_monat']:'');

  	$person_id=trim(isset($_REQUEST['person_id']) ? $_REQUEST['person_id']:'');
	if (!empty($person_id) && !is_numeric($person_id))
	{
		if ($oBenutzer = new benutzer($person_id))
			$person_id=$oBenutzer->person_id;
	}

  	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);
	// Erweiterte Suche On-Off
  	$extend_search=trim(isset($_REQUEST['extend_search']) ?$_REQUEST['extend_search']:'false');
	$check=$betriebsmitteltyp.$bestellung_id.$bestelldetail_id.$bestellnr.$hersteller.$firma_id.$beschreibung.$oe_kurzbz.$person_id;
	$extend_search=($check?'true':$extend_search);

// ------------------------------------------------------------------------------------------
// Berechtigung
// ------------------------------------------------------------------------------------------
	$oBenutzerberechtigung = new benutzerberechtigung();
	$oBenutzerberechtigung->errormsg='';
	$oBenutzerberechtigung->berechtigungen=array();
	if (!$oBenutzerberechtigung->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$recht=false;
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,($oe_kurzbz),'s')
	|| $oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'s'))
		$recht=true;
	if (!$recht)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	// Pruefen ob Schreibrechte (Anzeigen der Aenderungsmoeglichkeit)
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'su'))
		$schreib_recht=true;

	// Pruefen ob Schreibrechte (Anzeigen der Aenderungsmoeglichkeit)
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'suid'))
		$delete_recht=true;

	if($oBenutzerberechtigung->isBerechtigt('wawi/inventar', null, 'suid') )
		$schreib_recht=$schreib_recht_administration;

// ------------------------------------------------------------------------------------------
// Verarbeitung - Ajax oder Work
// ------------------------------------------------------------------------------------------
 	$ajax=trim(isset($_REQUEST['ajax']) ?$_REQUEST['ajax']:false);
  	$work=trim(isset($_REQUEST['work']) ?$_REQUEST['work']:false);

	// Statusaenderung
	if (($ajax && strtolower($ajax)=='set_status')
	||  ($work && strtolower($work)=='set_status') )
	{
		if($schreib_recht)
		{
			$oBetriebsmittel = new betriebsmittel($betriebsmittel_id);
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->debug=$debug;
			$oBetriebsmittel->errormsg='';
			$oBetriebsmittel->updatevon=$uid;
			$oBetriebsmittel->updateamum=date('Y-m-d H:i:s');
			if ($oBetriebsmittel->save())
			{
				$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
				$oBetriebsmittel_betriebsmittelstatus->result=array();
				$oBetriebsmittel_betriebsmittelstatus->errormsg='';
				$oBetriebsmittel_betriebsmittelstatus->debug=$debug;

				$oBetriebsmittel_betriebsmittelstatus->new=true;

				$oBetriebsmittel_betriebsmittelstatus->betriebsmittelbetriebsmittelstatus_id=trim(isset($_REQUEST['betriebsmittelbetriebsmittelstatus_id']) ? $_REQUEST['betriebsmittelbetriebsmittelstatus_id']:'');
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittel_id=$oBetriebsmittel->betriebsmittel_id;
				$oBetriebsmittel_betriebsmittelstatus->datum=date('Ymd');
				$oBetriebsmittel_betriebsmittelstatus->updatevon=$uid;
				$oBetriebsmittel_betriebsmittelstatus->updateamum=date('Y-m-d H:i:s');
				$oBetriebsmittel_betriebsmittelstatus->insertvon=$uid;
				$oBetriebsmittel_betriebsmittelstatus->insertamum=date('Y-m-d H:i:s');
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz=trim((isset($_REQUEST['betriebsmittelstatus_kurzbz']) ? $_REQUEST['betriebsmittelstatus_kurzbz']:''));
				if ($oBetriebsmittel_betriebsmittelstatus->save())
					$errormsg[]='<span title="die Neue Status ID ist '.$oBetriebsmittel_betriebsmittelstatus->betriebsmittelbetriebsmittelstatus_id.'"><img src="../../skin/images/tick.png" alt="ok" /></span>';
				else
					$errormsg[]=$oBetriebsmittel_betriebsmittelstatus->errormsg;
			}
			else
				$errormsg[]=$oBetriebsmittel->errormsg;
		} // Recht
		else
				$errormsg[]='Sie haben keine Berechtigung fuer die Datenbearbeitung';

		// Fehlerausgabe bzw. Informationen ueber den Status der Verarbeitung
	}
	// Bestellposition aendern
	if (($ajax && strtolower($ajax)=='set_position')
	||  ($work && strtolower($work)=='set_position') )
	{
		if($schreib_recht)
		{
			$oBetriebsmittel = new betriebsmittel($betriebsmittel_id);
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->debug=$debug;
			$oBetriebsmittel->errormsg='';
			if (is_numeric($bestellung_id))
			{
				$oBetriebsmittel->bestellung_id=$bestellung_id;
				$oBetriebsmittel->bestelldetail_id=$bestelldetail_id;
				$oBetriebsmittel->updatevon=$uid;
				$oBetriebsmittel->updateamum=date('Y-m-d H:i:s');
				if (!$oBetriebsmittel->save(false))
					$errormsg[]=$oBetriebsmittel->errormsg;
			}
			else
				$errormsg[]='Bestellung ID '.$bestellung_id.' falsch';
		}
		else
			$errormsg[]='Sie haben keine Berechtigung fuer die Datenbearbeitung';

		// Fehlerausgabe bzw. Informationen ueber den Status der Verarbeitung
	}

	// Inventur setzen
	if (($ajax && strtolower($ajax)=='set_inventur')
	||  ($work && strtolower($work)=='set_inventur') )
	{
		if($schreib_recht)
		{
			$oBetriebsmittel = new betriebsmittel();
			if($oBetriebsmittel->load($betriebsmittel_id))
			{
				$oBetriebsmittel->updatevon = $uid;
				$oBetriebsmittel->updateamum = date('Y-m-d H:i:s');
				$oBetriebsmittel->inventuramum = date('Y-m-d H:i:s');
				$oBetriebsmittel->inventurvon = $uid;
				if (!$oBetriebsmittel->save())
					$errormsg[]=$oBetriebsmittel->errormsg;
			}
			else
				$errormsg[]='BetriebsmittelID ist falsch';
		}
		else
			$errormsg[]='Sie haben keine Berechtigung fuer die Datenbearbeitung';

		// Fehlerausgabe bzw. Informationen ueber den Status der Verarbeitung
	}

	// Betriebsmittel Baum entfernen - Personen,Status,Inventar
	if (($ajax && strtolower($ajax)=='set_delete')
	||  ($work && strtolower($work)=='set_delete') )
	{
		if($schreib_recht==$schreib_recht_administration)
		{
			$oBetriebsmittel = new betriebsmittel($betriebsmittel_id);
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->debug=$debug;
			$oBetriebsmittel->errormsg='';
			if (is_numeric($betriebsmittel_id))
			{
				$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
				$oBetriebsmittel_betriebsmittelstatus->result=array();
				$oBetriebsmittel_betriebsmittelstatus->debug=$debug;
				$oBetriebsmittel_betriebsmittelstatus->errormsg='';
				if ($oBetriebsmittel_betriebsmittelstatus->delete_betriebsmittel($betriebsmittel_id))
				{
					$oBetriebsmittelperson = new betriebsmittelperson();
					$oBetriebsmittelperson->result=array();
					$oBetriebsmittelperson->debug=$debug;
					$oBetriebsmittelperson->errormsg='';
					$person_id=null;
					if ($oBetriebsmittelperson->delete_betriebsmittel($betriebsmittel_id))
					{
						$oBetriebsmittel->errormsg='';
						if ($oBetriebsmittel->delete($betriebsmittel_id))
						{
							$errormsg[]='Betriebsmittel '.($inventarnummer?$inventarnummer.'/ ID '.$betriebsmittel_id:$betriebsmittel_id).' wurde entfernt';
						}
						else
							$errormsg[]=$oBetriebsmittel->errormsg;
					}
					else
						$errormsg[]=$oBetriebsmittelperson->errormsg;
				}
				else
					$errormsg[]=$oBetriebsmittel_betriebsmittelstatus->errormsg;
			}
			else
				$errormsg[]='Betriebsmittel ID '.$betriebsmittel_id.' falsch';
		}
		else
			$errormsg[]='Sie haben keine Berechtigung zum Entfernen der Daten';
	}
	// Bei einem Ajax Call nun das Script mit der Meldungsausgabe beenden
	if (!empty($ajax))
	{
		if (is_array($errormsg) && count($errormsg)>0)
			exit(implode(", ",$errormsg));
		elseif (!is_array($errormsg))
			exit($errormsg);
		else
			exit('<img src="../../skin/images/tick.png" alt="ok '.$ajax.'" />');
	}
	// Wurde die Betriebsmittel ID uebergeben - die inventarnummer dazu ermitteln
	if ($betriebsmittel_id && empty($inventarnummer))
	{
		if ($oBetriebsmittel = new betriebsmittel($betriebsmittel_id))
			$inventarnummer=$oBetriebsmittel->inventarnummer;
	}

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------

	$oOrt = new ort();
	$oOrt->result=array();

	$oOrganisationseinheit = new organisationseinheit();
	$oOrganisationseinheit->result=array();

	$oBetriebsmittel = new betriebsmittel();
	$oBetriebsmittel->result=array();
	$oBetriebsmittel->debug=$debug;

	$oBetriebsmitteltyp = new betriebsmitteltyp();
	$oBetriebsmitteltyp->result=array();

	$oBetriebsmittelstatus = new betriebsmittelstatus();
	$oBetriebsmittelstatus->result=array();

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
	// Organisation - Inventarverwalter
	$oes=new organisationseinheit();
	if (!$oOrganisationseinheit->loadArray($oBenutzerberechtigung->getOEkurzbz($berechtigung_kurzbz),'organisationseinheittyp_kurzbz,bezeichnung'))
		$errormsg[]=$oOrganisationseinheit->errormsg;
	$extend_search=true;

	$resultOrganisationseinheit=$oOrganisationseinheit->result;

	// Typtable
	if (!$oBetriebsmitteltyp->getAll('typ_code, beschreibung'))
		$errormsg[]=$oBetriebsmitteltyp->errormsg;
	$resultBetriebsmitteltyp=$oBetriebsmitteltyp->result;

	// Statustable
	if (!$rows=$oBetriebsmittelstatus->getAll())
		$errormsg[]=$oBetriebsmittelstatus->errormsg;
	$resultBetriebsmittelstatus=$oBetriebsmittelstatus->result;

// ------------------------------------------------------------------------------------------
// HTML Output
// ------------------------------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Inventar - Betriebsmittel - Suche</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">

		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<!--		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script> -->
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
		<script type="text/javascript" language="JavaScript1.2">
		<!--
			function formatItem(row)
			{
			    return row[0] + " <br>" + row[1];
			}

			var ajxFile = "<?php echo $_SERVER["PHP_SELF"];  ?>";

			function set_status(output_id,betriebsmittelbetriebsmittelstatus_id,betriebsmittel_id,inventarnummer,bestellung_id,bestelldetail_id,betriebsmittelstatus_kurzbz)
			{
				document.getElementById(output_id).innerHTML = '<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >';
				$.ajax
				(
					{
						type: "POST",
						url: ajxFile,
						data: "ajax=set_status" + "&debug=<?php echo $debug;?>" + "&betriebsmittelbetriebsmittelstatus_id=" + betriebsmittelbetriebsmittelstatus_id  + "&betriebsmittel_id=" + betriebsmittel_id + "&inventarnummer=" + inventarnummer + "&bestellung_id=" + bestellung_id + "&bestelldetail_id=" + bestelldetail_id + "&betriebsmittelstatus_kurzbz=" + betriebsmittelstatus_kurzbz,
						success: function(phpData)
						{
							document.getElementById(output_id).innerHTML = phpData;
							return;
						}
					}
				);
				document.getElementById(output_id).innerHTML = '';
			}

			function set_position(output_id,betriebsmittel_id,inventarnummer,bestellung_id,bestelldetail_id)
			{
				document.getElementById(output_id).innerHTML = '<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >';
				if(bestelldetail_id!='')
				{
					$.ajax
					(
						{
							type: "POST",
							url: ajxFile,
							data: "ajax=set_position" + "&debug=<?php echo $debug;?>"  + "&betriebsmittel_id=" + betriebsmittel_id + "&inventarnummer=" + inventarnummer + "&bestellung_id=" + bestellung_id + "&bestelldetail_id=" + bestelldetail_id ,
							success: function(phpData)
							{
								document.getElementById(output_id).innerHTML = phpData;
								return;
							}
						}
					);
				}
				document.getElementById(output_id).innerHTML = '';
			}
		-->
		</script>
	</head>

	<body>

		<h1 title="Anwender:<?php echo $uid ?>">&nbsp;Inventar - Suche&nbsp;</h1>
	    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
		<div>
			<table class="navbar">
			<tr>
				<td><label for="inventarnummer">Inv.nr.</label>&nbsp;
<!--					<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1500);}" id="inventarnummer" name="inventarnummer" type="text" size="10" maxlength="30" value="<?php //echo $inventarnummer;?>">&nbsp; -->
					<input id="inventarnummer" name="inventarnummer" type="text" size="10" maxlength="30" value="<?php echo $inventarnummer;?>">
					<script type="text/javascript">
						$(document).ready(function()
						{
							$('#inventarnummer').autocomplete({
								source: "inventar_autocomplete.php?work=inventarnummer",
								minLength:2,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].inventarnummer;
										ui.content[i].label=ui.content[i].inventarnummer+" "+ui.content[i].beschreibung;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.inventarnummer;
									setTimeout('document.sendform.submit()',1500);
								}
							});
/*							  $('#inventarnummer').autocomplete('inventar_autocomplete.php',
							  {
								minChars:2,
								matchSubset:1,matchContains:1,
								width:500,
								formatItem:formatItem,
								extraParams:{'work':'inventarnummer'}
							  }); */
					  });
					</script>
					</td>

					<td><label for="seriennummer">Seriennr.</label>&nbsp;
					<input id="seriennummer"  name="seriennummer" type="text" size="10" maxlength="60" value="<?php echo $seriennummer;?>">&nbsp;
					</td>

					<td><label for="ort_kurzbz">Ort</label>&nbsp;
						<input id="ort_kurzbz" name="ort_kurzbz" size="16" maxlength="40" value="<?php echo $ort_kurzbz;?>">&nbsp;
						<script type="text/javascript">
						$(document).ready(function()
						{
							$('#ort_kurzbz').autocomplete({
								source: "inventar_autocomplete.php?work=inventar_ort",
								minLength:3,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].ort_kurzbz;
										ui.content[i].label=ui.content[i].ort_kurzbz+" "+ui.content[i].bezeichnung;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.ort_kurzbz;
									setTimeout('document.sendform.submit()',1500);
								}
							});
/*							  $('#ort_kurzbz').autocomplete('inventar_autocomplete.php',
							  {
								minChars:3,
								matchSubset:1,matchContains:1,
								width:300,
								formatItem:formatItem,
								extraParams:{'work':'inventar_ort'
											,'inventarnummer':$("#inventarnummer").val()
											,'seriennummer':$("#seriennummer").val()
											,'jahr_monat':$("#jahr_monat").val()
											}
							  }); */
					  });
						</script>
				</td>
			<!-- Anlage-Jahr/Monat -->
				<td><label for="jahr_monat">Datum</label>&nbsp;
					<select id="jahr_monat" name="jahr_monat">
							<?php
							$jahr_monat_select=trim((!isset($_REQUEST['jahr_monat'])? '-':$jahr_monat));
							$tmpJahr=(int)date("Y",mktime(0, 0, 0, 1, 1, date("Y")-12));
							for ($i=0;$i<12;$i++)
							{
								$tmpJahr=$tmpJahr + 1;
								$jjjjmm=$tmpJahr.'-00';
								echo '<option '.($jahr_monat_select==$tmpJahr?'  selected="selected" ':'').' value="'.$tmpJahr.'">&nbsp;--'.$tmpJahr.'--&nbsp;</option>';
								for ($ii=1;$ii<=12;$ii++)
								{
									$jjjjmm=$tmpJahr.'-'.($ii<10?'0'."$ii":$ii);
									echo '<option '.($jahr_monat_select==$jjjjmm?' selected="selected" ':'').' value="'.$jjjjmm.'">&nbsp;'.$jjjjmm.'&nbsp;</option>';
									if ($tmpJahr==date("Y") && $ii==date("m"))
										break;
								}
							}
							?>
							<option <?php echo ($jahr_monat_select=='-' || empty($jahr_monat_select)  ?'  selected="selected" ':''); ?>   value="">&nbsp;-&nbsp;</option>
						</select>&nbsp;
				</td>
				<td><label for="anlage_jahr_monat">Anlagedatum</label>&nbsp;
					<select id="anlage_jahr_monat" name="anlage_jahr_monat">
							<?php
							$anlage_jahr_monat_select=trim((!isset($_REQUEST['anlage_jahr_monat'])? '-':$anlage_jahr_monat));
							$tmpJahr=(int)date("Y",mktime(0, 0, 0, 1, 1, date("Y")-12));
							for ($i=0;$i<12;$i++)
							{
								$tmpJahr=$tmpJahr + 1;
								$jjjjmm=$tmpJahr.'-00';
								echo '<option '.($anlage_jahr_monat_select==$tmpJahr?'  selected="selected" ':'').' value="'.$tmpJahr.'">&nbsp;--'.$tmpJahr.'--&nbsp;</option>';
								for ($ii=1;$ii<=12;$ii++)
								{
									$jjjjmm=$tmpJahr.'-'.($ii<10?'0'."$ii":$ii);
									echo '<option '.($anlage_jahr_monat_select==$jjjjmm?' selected="selected" ':'').' value="'.$jjjjmm.'">&nbsp;'.$jjjjmm.'&nbsp;</option>';
									if ($tmpJahr==date("Y") && $ii==date("m"))
										break;
								}
							}
							?>
							<option <?php echo ($anlage_jahr_monat_select=='-' || empty($anlage_jahr_monat_select)  ?'  selected="selected" ':''); ?>   value="">&nbsp;-&nbsp;</option>
						</select>&nbsp;
				</td>
				<td style="background-color: #FFFFDD;">&nbsp;<a href="javascript:document.sendform.submit();"><img border="0" src="../../skin/images/application_go.png" alt="suchen">&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
			</tr>
		</table>
		</div>

		<div id="ext_search" style="display:<?php echo ($extend_search && $extend_search!='false'?'block':'none'); ?>;">

		<table class="navbar">
			<tr>
				<td><label for="oe_kurzbz">Organisation</label>&nbsp;
					<select id="oe_kurzbz" name="oe_kurzbz" >
						  <?php
								if($oBenutzerberechtigung->isBerechtigt('wawi/inventar', null, 's'))
									echo '<option '.(empty($oe_kurzbz)?' selected="selected" ':'').' value="">bitte ausw&auml;hlen&nbsp;</option>';

								for ($i=0;$i<count($resultOrganisationseinheit) ;$i++)
								{
									if ($resultOrganisationseinheit[$i]->oe_kurzbz)
										echo '<option '.($oe_kurzbz==$resultOrganisationseinheit[$i]->oe_kurzbz?' selected="selected" ':'').' value="'.$resultOrganisationseinheit[$i]->oe_kurzbz.'">'.$resultOrganisationseinheit[$i]->organisationseinheittyp_kurzbz.' '.($resultOrganisationseinheit[$i]->bezeichnung=='NULL' || empty($resultOrganisationseinheit[$i]->bezeichnung)?$resultOrganisationseinheit[$i]->oe_kurzbz:$resultOrganisationseinheit[$i]->bezeichnung).'&nbsp;</option>';
								}
							?>
					</select>
				</td>
				<?php
					// Mitarbeiter Informationen lesen
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
								if (!$oPerson->getTab($person_id)) // in person_id kann auch der Name stehen
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
				?>
				<td>&nbsp;<label for="person_id">Mitarbeiter</label>&nbsp;
					<input id="person_id" name="person_id" size="13" maxlength="14" value="<?php echo $person_id; ?>">
						<script type="text/javascript">
						$(document).ready(function()
						{
							$('#person_id').autocomplete({
								source: "inventar_autocomplete.php?work=person",
								minLength:4,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].person_id;
										ui.content[i].label=ui.content[i].person_id+' '+ui.content[i].anrede+' '+ui.content[i].titelpre+' '+ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].funktion;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.person_id;
									setTimeout('document.sendform.submit()',1500);
								}
							});
/*							  $('#person_id').autocomplete('inventar_autocomplete.php',
							  {
								minChars:4,
								matchSubset:1,matchContains:1,
								width:400,
								formatItem:formatItem,
								extraParams:{'work':'person' }
							  }); */
					  });
					</script>
					<?php
						echo $personen_namen;
					?>
				</td>
			</tr>
		</table>

		<table class="navbar">
			<tr>
					<td><label for="bestellnr">Bestellnr.</label>&nbsp;	<input id="bestellnr" name="bestellnr" size="10" maxlength="30" type="Text" value="<?php echo $bestellnr; ?>" >&nbsp;
						<script type="text/javascript">
							$(document).ready(function()
							{
								$('#bestellnr').autocomplete({
									source: "inventar_autocomplete.php?work=wawi_bestellnr",
									minLength:5,
									response: function(event, ui)
									{
										//Value und Label fuer die Anzeige setzen
										for(i in ui.content)
										{
											ui.content[i].value=ui.content[i].bestell_nr;
											ui.content[i].label=ui.content[i].bestell_nr+' '+ui.content[i].insertamum+' '+ui.content[i].titel+' '+ui.content[i].bemerkung;
										}
									},
									select: function(event, ui)
									{
										ui.item.value=ui.item.bestell_nr;
										setTimeout('document.sendform.submit()',1500);
									}
								});
								 /* $('#bestellnr').autocomplete('inventar_autocomplete.php',
								  {
									minChars:5,
									matchSubset:1,matchContains:1,
									width:500,
									formatItem:formatItem,
									extraParams:{'work':'wawi_bestellnr'}
								  }); */
						  });
						</script>
				</td>
				<!-- Bestell ID Eindeutigenummer -->
				<td><label for="bestellung_id">Bestell ID</label>&nbsp;<input id="bestellung_id" name="bestellung_id" size="10" maxlength="30" type="Text" value="<?php echo $bestellung_id; ?>" >&nbsp;
						<script type="text/javascript">
							$(document).ready(function()
							{
								$('#bestellung_id').autocomplete({
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
										setTimeout('document.sendform.submit()',1500);
									}
								});
/*								  $('#bestellung_id').autocomplete('inventar_autocomplete.php',
								  {
									minChars:2,
									matchSubset:1,matchContains:1,
									width:500,
									formatItem:formatItem,
									extraParams:{'work':'wawi_bestellung_id'}
								  }); */
						  });
						</script>
				</td>

				<td>&nbsp;<label for="firma_id">Lieferant</label>&nbsp;
					<input id="firma_id" name="firma_id" size="10" maxlength="30" value="<?php echo $firma_id; ?>" >&nbsp;
					<script type="text/javascript">
							$(document).ready(function()
							{
								$('#firma_id').autocomplete({
									source: "inventar_autocomplete.php?work=wawi_firma_search",
									minLength:4,
									response: function(event, ui)
									{
										//Value und Label fuer die Anzeige setzen
										for(i in ui.content)
										{
											ui.content[i].value=ui.content[i].firma_id;
											ui.content[i].label=ui.content[i].firma_id+' '+ui.content[i].name;
										}
									},
									select: function(event, ui)
									{
										ui.item.value=ui.item.firma_id;
									}
								});
/*								  $('#firma_id').autocomplete('inventar_autocomplete.php',
								  {
									minChars:4,
									matchSubset:1,matchContains:1,
									width:500,
									formatItem:formatItem,
									extraParams:{'work':'wawi_firma_search'	}
							  }); */
						  });
						</script>
				</td>

				<td>&nbsp;<label for="hersteller">Hersteller</label>&nbsp;
					<input id="hersteller" name="hersteller" type="text" size="10" maxlength="30" value="<?php echo $hersteller;?>" >&nbsp;
					<script type="text/javascript">

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
/*							  $('#hersteller').autocomplete('inventar_autocomplete.php',
							  {
								minChars:3,
								matchSubset:1,matchContains:1,
								width:400,
								formatItem:formatItem,
								extraParams:{'work':'hersteller'}
						  }); */
					  });
					</script>
				</td>
			</tr>
		</table>

		<table class="navbar">
			<tr>
				<td><label for="betriebsmittelstatus_kurzbz">Status</label>&nbsp;
						<select id="betriebsmittelstatus_kurzbz" name="betriebsmittelstatus_kurzbz" >
							<option  <?php
									  	$betriebsmittelstatus_kurzbz_select=trim((!isset($_REQUEST['betriebsmittelstatus_kurzbz'])?'':$betriebsmittelstatus_kurzbz));
										echo (empty($betriebsmittelstatus_kurzbz_select)?' selected="selected" ':''); ?>  value="">bitte ausw&auml;hlen&nbsp;</option>
										<?php
										for ($i=0;$i<count($resultBetriebsmittelstatus) ;$i++)
										{
											if ($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)
												echo '<option '.($betriebsmittelstatus_kurzbz_select==$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
										}
										?>
						</select>
				</td>

				<td>&nbsp;<label for="betriebsmitteltyp">Betriebsmitteltyp</label>&nbsp;
					<select id="betriebsmitteltyp" name="betriebsmitteltyp">
						<option  <?php echo (empty($betriebsmitteltyp)?' selected="selected" ':''); ?>  value="">bitte ausw&auml;hlen&nbsp;</option>
									<?php

									for ($i=0;$i<count($resultBetriebsmitteltyp) ;$i++)
									{
										if ($resultBetriebsmitteltyp[$i]->betriebsmitteltyp)
											echo '<option '.($betriebsmitteltyp==$resultBetriebsmitteltyp[$i]->betriebsmitteltyp?' selected="selected" ':'').' value="'.$resultBetriebsmitteltyp[$i]->betriebsmitteltyp.'">'.($resultBetriebsmitteltyp[$i]->beschreibung=='NULL' || empty($resultBetriebsmitteltyp[$i]->beschreibung)?$resultBetriebsmitteltyp[$i]->betriebsmitteltyp:$resultBetriebsmitteltyp[$i]->beschreibung).'&nbsp;('.$resultBetriebsmitteltyp[$i]->typ_code.')</option>';
									}
									?>
					</select>&nbsp;
				</td>
				<td>&nbsp;<label for="beschreibung">Bezeichnung</label>&nbsp;
					<input id="beschreibung" name="beschreibung" type="text" size="35" maxlength="90" value="<?php echo $beschreibung;?>" >&nbsp;
				</td>
			</tr>
		</table>

		</div>
		<!-- erweiterte SUCHE EIN -->
		<div>
			<div id="extend_search_on">
				<div style="cursor: pointer;">
					<table class="navbar">
					<tr>
						<td>
							<img border="0" src="../../skin/images/right.png" alt="anzeigen - show" >Erweiterte Suche anzeigen / ausblenden
							<input style="display:none;"  type="text" id="extend_search" name="extend_search" value="<?php echo ($extend_search && $extend_search!='false'?'true':'false'); ?>">
						</td>
					</tr>
					</table>
				</div>
			</div>
			<script type="text/javascript">
			   $(document).ready(function()
			   {
			   		$("div#extend_search_on").click(function(event)
					{
				      	if ( $("#extend_search").val() != 'true')
						{
				        	 $("div#ext_search").show("slow");
						     $("#extend_search").val('true')
					    }
						else
						{
						     $("div#ext_search").hide("slow");
						     $("#extend_search").val('false')
				      	}
			   		});
				});
			</script>
		</div>
		</form>
		<hr>
<?php
// ----------------------------------------
// Inventardaten - lesen
// ----------------------------------------

	// pruefen ob eine Eingabe erfolgte
	if ($inventarnummer || $betriebsmittel_id || $bestellung_id || $bestellnr || $seriennummer || $person_id)
	{
		$afa='';
		$inventur_jahr='';
		$jahr_monat='';
		$ort_kurzbz='';
		$oe_kurzbz='';
		$betriebsmitteltyp='';
		$betriebsmittelstatus_kurzbz='';
	}
	if (empty($bestellung_id) && empty($bestellnr)  )
		$bestelldetail_id='';

 	$check=$inventarnummer.$ort_kurzbz.$betriebsmittelstatus_kurzbz.$betriebsmitteltyp.$bestellung_id.$bestelldetail_id.$bestellnr.$hersteller.$afa.$jahr_monat.$firma_id.$inventur_jahr.$beschreibung.$oe_kurzbz.$seriennummer.$person_id.$betriebsmittel_id.$anlage_jahr_monat;
	$order=null; // Sortierung

	$oBetriebsmittel->result=array();
	$oBetriebsmittel->errormsg='';
	if ($check!='' && !$oBetriebsmittel->betriebsmittel_inventar($order,$inventarnummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer,$person_id,$betriebsmittel_id, $anlage_jahr_monat))
		$errormsg[]=$oBetriebsmittel->errormsg;

	echo '<form action="inventarliste.php" method="POST" target="_blank">
		<input type="hidden" name="order" value="'.$order.'">
		<input type="hidden" name="inventarnummer" value="'.$inventarnummer.'">
		<input type="hidden" name="ort_kurzbz" value="'.$ort_kurzbz.'">
		<input type="hidden" name="betriebsmittelstatus_kurzbz" value="'.$betriebsmittelstatus_kurzbz.'">
		<input type="hidden" name="betriebsmitteltyp" value="'.$betriebsmitteltyp.'">
		<input type="hidden" name="bestellung_id" value="'.$bestellung_id.'">
		<input type="hidden" name="bestelldetail_id" value="'.$bestelldetail_id.'">
		<input type="hidden" name="bestellnr" value="'.$bestellnr.'">
		<input type="hidden" name="hersteller" value="'.$hersteller.'">
		<input type="hidden" name="afa" value="'.$afa.'">
		<input type="hidden" name="jahr_monat" value="'.$jahr_monat.'">
		<input type="hidden" name="firma_id" value="'.$firma_id.'">
		<input type="hidden" name="inventur_jahr" value="'.$inventur_jahr.'">
		<input type="hidden" name="beschreibung" value="'.$beschreibung.'">
		<input type="hidden" name="oe_kurzbz" value="'.$oe_kurzbz.'">
		<input type="hidden" name="seriennummer" value="'.$seriennummer.'">
		<input type="hidden" name="person_id" value="'.$person_id.'">
		<input type="hidden" name="betriebsmittel_id" value="'.$betriebsmittel_id.'">
		<input type="hidden" name="anlage_jahr_monat" value="'.$anlage_jahr_monat.'">
		<input type="submit" value="Excel Export" />
		</form>
		';
	// Inventardatenliste
	if ( is_array($oBetriebsmittel->result) && count($oBetriebsmittel->result)==1)
		echo output_inventarposition($debug,$oBetriebsmittel->result,$resultBetriebsmittelstatus,$schreib_recht,$delete_recht,$schreib_recht_administration);
	else if ( is_array($oBetriebsmittel->result) && count($oBetriebsmittel->result)>1)
		echo output_inventar($debug,$oBetriebsmittel->result,$resultBetriebsmittelstatus,$schreib_recht,$delete_recht,$schreib_recht_administration,$default_status_vorhanden);
	else
	{
		if (!empty($check) )
			$errormsg[]='keine Daten gefunden';
		else
			$errormsg[]='Auswahl fehlt';
	}

	// Error - Meldungen ausgeben
	if (is_array($errormsg) && count($errormsg)>0)
		echo '<font class="error">'. implode("<br>",$errormsg).'</font>';
	elseif (!is_array($errormsg))
		echo '<font class="error"><br>'.$errormsg.'</font>';
?>
</body>
</html>
<?php
// Ende ===========================================================================================

// Funktionen

// ===========================================================================================
// Ausgabe der Bestellungen in Listenform
function output_inventar($debug=false,$resultBetriebsmittel=null,$resultBetriebsmittelstatus=array(),$schreib_recht=false,$delete_recht=false,$schreib_recht_administration=2)
{
	global $datum_obj;

	$htmlstring='';
	if (is_null($resultBetriebsmittel) || !is_array($resultBetriebsmittel) || count($resultBetriebsmittel)<1)
		return $htmlstring;
	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultBetriebsmittel) && count($resultBetriebsmittel)>1)
		$htmlstring.='<tr><th colspan="12">Bitte ein Inventar aus den '.count($resultBetriebsmittel).' gefundenen ausw&auml;hlen</th></tr>';
	$htmlstring.='<tr>
				<th class="table-sortable:default" title="Inventarnummer">Inv.nr.</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default">Verwendung</th>
				<th class="table-sortable:default" title="Seriennummer">Ser.nr.</th>
				<th class="table-sortable:default">Ort</th>
				<th class="table-sortable:default" title="Bestellnummer">Bestellnr</th>
				<th class="table-sortable:default">Datum</th>
				<th class="table-sortable:default" title="Organisationseinheit">Org.</th>
				<th class="table-sortable:default" title="Inventur">Inv.</th>
				<th class="table-sortable:default" title="Entlehnt">Entl.</th>
				<th colspan="3" class="table-sortable:default">Status</th>
			</tr>
			</thead>
			<tbody>
		';

	for ($pos=0;$pos<count($resultBetriebsmittel);$pos++)
	{
		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';

		// Pruefen ob OE vorhanden ist - ansonst suchen ob ein Benutzer vorhanden ist
		$resultBetriebsmittel[$pos]->oe_kurzbz=trim($resultBetriebsmittel[$pos]->oe_kurzbz);
		if (empty($resultBetriebsmittel[$pos]->oe_kurzbz))
		{
			$resultBetriebsmittel[$pos]->oe_kurzbz='Fehlt';
			$oBetriebsmittelOrganisationseinheit = new betriebsmittel();
			if ($oBetriebsmittelOrganisationseinheit->load_betriebsmittel_oe($resultBetriebsmittel[$pos]->betriebsmittel_id))
				$resultBetriebsmittel[$pos]->oe_kurzbz=$oBetriebsmittelOrganisationseinheit->oe_kurzbz;
			else if ($oBetriebsmittelOrganisationseinheit->errormsg)
				$resultBetriebsmittel[$pos]->oe_kurzbz=$oBetriebsmittelOrganisationseinheit->errormsg;
		}

		if(!isset($oOrganisationseinheit))
			$oOrganisationseinheit=new stdClass();
		$oOrganisationseinheit->bezeichnung='';
		$oOrganisationseinheit = new organisationseinheit($resultBetriebsmittel[$pos]->oe_kurzbz);
		// String - Daten Leerzeichen am Ende entfernen
		$resultBetriebsmittel[$pos]->bestellnr=trim($resultBetriebsmittel[$pos]->bestellnr);

		$resultBetriebsmittel[$pos]->titel=trim($resultBetriebsmittel[$pos]->titel);
		$resultBetriebsmittel[$pos]->beschreibung=trim($resultBetriebsmittel[$pos]->beschreibung);

		$resultBetriebsmittel[$pos]->firma_id=trim($resultBetriebsmittel[$pos]->firma_id);
		$resultBetriebsmittel[$pos]->firmenname=trim($resultBetriebsmittel[$pos]->firmenname);

		$htmlstring.='<tr class="'.$classe.'">
			<td><a href="'.$_SERVER["PHP_SELF"].'?inventarnummer='.$resultBetriebsmittel[$pos]->inventarnummer.'&amp;betriebsmittel_id='.$resultBetriebsmittel[$pos]->betriebsmittel_id.'&amp;bestellung_id='.$resultBetriebsmittel[$pos]->bestellung_id.'&amp;bestelldetail_id='.$resultBetriebsmittel[$pos]->bestelldetail_id.'" target="_blank">'.($resultBetriebsmittel[$pos]->inventarnummer?$resultBetriebsmittel[$pos]->inventarnummer:$resultBetriebsmittel[$pos]->betriebsmittel_id).'</a>&nbsp;</td>
			<td>'.StringCut((!empty($resultBetriebsmittel[$pos]->beschreibung)?$resultBetriebsmittel[$pos]->beschreibung:$resultBetriebsmittel[$pos]->betriebsmitteltyp),20).'&nbsp;</td>
			<td>'.$resultBetriebsmittel[$pos]->verwendung.'&nbsp;</td>
			<td>'.$resultBetriebsmittel[$pos]->seriennummer.'&nbsp;</td>
			<td>'.$resultBetriebsmittel[$pos]->ort_kurzbz.'&nbsp;</td>
			';
		$bestellung_ivalid_style='';
		if ($resultBetriebsmittel[$pos]->bestellung_id && !$resultBetriebsmittel[$pos]->bestellnr)
			$bestellung_ivalid_style='style="color: red;"';


		//$htmlstring.='<td align="right"><a href="../../addons/wawi/vilesci/bestellung.php?bestellung_id='.$resultBetriebsmittel[$pos]->bestellung_id.'" target="_blank" '.$bestellung_ivalid_style.'>'.$resultBetriebsmittel[$pos]->bestellnr.'</a>&nbsp;</td>';
		$htmlstring.='<td align="right">';

		//Wenn Rechnungen vorhanden sind, einen Link dazu anzeigen
		$rechnung = new wawi_rechnung();
		if($resultBetriebsmittel[$pos]->bestellung_id!='' && $rechnung->count($resultBetriebsmittel[$pos]->bestellung_id)>0)
		{
			$htmlstring.='&nbsp;<a href="../../addons/wawi/vilesci/rechnung.php?method=suche&amp;submit=true&amp;bestellnummer='.$resultBetriebsmittel[$pos]->bestellnr.'" target="_blank" '.$bestellung_ivalid_style.'><img src="../../skin/images/Calculator.png"></a>';
		}

		$htmlstring.='<a href="../../addons/wawi/vilesci/bestellung.php?method=update&amp;id='.$resultBetriebsmittel[$pos]->bestellung_id.'" target="_blank" '.$bestellung_ivalid_style.'>'.$resultBetriebsmittel[$pos]->bestellnr.'</a>';

		echo '</td>';

		$htmlstring.='<td><span style="display: none;">'.$resultBetriebsmittel[$pos]->betriebsmittelstatus_datum.'</span>'.$datum_obj->formatDatum($resultBetriebsmittel[$pos]->betriebsmittelstatus_datum,'d.m.Y').'&nbsp;</td>';
		$htmlstring.='<td>'.StringCut(($oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:$resultBetriebsmittel[$pos]->oe_kurzbz),20).'&nbsp;</td>';
		$htmlstring.='<td align="right">'.$datum_obj->formatDatum($resultBetriebsmittel[$pos]->inventuramum,'d.m.Y').'&nbsp;</td>';
		$htmlstring.='<td align="right">'.($resultBetriebsmittel[$pos]->ausgegeben=='t'?'Ja':'Nein').'&nbsp;</td>';

		$htmlstring.='<td>';
			// mit Berechtigung ist der Status zum bearbeiten
            $betriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
            $status = $betriebsmittel_betriebsmittelstatus->load_last_status_by_betriebsmittel_id($resultBetriebsmittel[$pos]->betriebsmittel_id);
            $betriebsmittelstatus_kurzbz_select = trim($status);
            //$resultBetriebsmittel[$pos]->betriebsmittelstatus_kurzbz;
			if (!$schreib_recht)
				$htmlstring.=$betriebsmittelstatus_kurzbz_select;
			else
			{
				$htmlstring.='<select style="font-size:xx-small;" onchange="set_status(\'list'.$pos.'\',\''.$resultBetriebsmittel[$pos]->betriebsmittelbetriebsmittelstatus_id.'\',\''.$resultBetriebsmittel[$pos]->betriebsmittel_id.'\',\''.$resultBetriebsmittel[$pos]->inventarnummer.'\',\''.$resultBetriebsmittel[$pos]->bestellung_id.'\',\''.$resultBetriebsmittel[$pos]->bestelldetail_id.'\',this.value);" name="betriebsmittelstatus_kurzbz">';

				for ($i=0;$i<count($resultBetriebsmittelstatus) ;$i++)
				{
					if ($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)
						$htmlstring.='<option '.($betriebsmittelstatus_kurzbz_select==$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
				}
				$htmlstring.='<option '.(trim($betriebsmittelstatus_kurzbz_select)==''?' selected="selected" ':'').' value="">Status ?&nbsp;</option>';
				$htmlstring.='</select>';
			}
			$htmlstring.='&nbsp;</td>';
			$htmlstring.='
			<td id="bcTarget'.$pos.'"><img border="0"  src="../../skin/images/printer.png" alt="Etik"></td>
				<script type="text/javascript">
				   $(document).ready(function()
				   {
					   	$("td#bcTarget'.$pos.'").click(function(event)
						{
							var PrintWin=window.open("etiketten.php?inventarnummer='. urlencode($resultBetriebsmittel[$pos]->inventarnummer).'","Etik","copyhistory=no,directories=no,location=no,dependent=yes,toolbar=no,status=no,menubar=no,resizable=yes,scrollbars=yes,width=400,height=300,left=20, top=20");
							if (PrintWin)
							{
								PrintWin.focus();
							}
					   });
				});
				</script>';
		$htmlstring.='
			<td style="font-size:xx-small;" id="list'.$pos.'"></td>
		</tr>
		';
	}
	$htmlstring.='</tbody></table>';
	return 	$htmlstring;
}
// ===========================================================================================
// Ausgabe der Bestellung Detail
function output_inventarposition($debug=false,$resultBetriebsmittel=null,$resultBetriebsmittelstatus=array(),$schreib_recht=false,$delete_recht=false,$schreib_recht_administration=2)
{
	global $datum_obj;

	// Verarbeitungs Array ermitteln aus der Uebergabe
	if (isset($resultBetriebsmittel[0]))
		$resBetriebsmittel=$resultBetriebsmittel[0];
	else
		$resBetriebsmittel=$resultBetriebsmittel;

	$htmlstring='';
	if (is_null($resBetriebsmittel) || ( !is_object($resBetriebsmittel) && !is_array($resBetriebsmittel) ) || count($resBetriebsmittel)<1)
		return $htmlstring;

	// Pruefen ob OE vorhanden ist - ansonst suchen ob ein Benutzer vorhanden ist

	$resBetriebsmittel->oe_kurzbz=trim($resBetriebsmittel->oe_kurzbz);
	if (empty($resBetriebsmittel->oe_kurzbz))
	{
		$resBetriebsmittel->oe_kurzbz='Fehlt';
		$oBetriebsmittelOrganisationseinheit = new betriebsmittel();
		if ($oBetriebsmittelOrganisationseinheit->load_betriebsmittel_oe($resBetriebsmittel->betriebsmittel_id))
			$resBetriebsmittel->oe_kurzbz=$oBetriebsmittelOrganisationseinheit->oe_kurzbz;
		else
			$resBetriebsmittel->oe_kurzbz=$oBetriebsmittelOrganisationseinheit->errormsg;
	}

	// Organisation - Inventarverwalter
	$oOrganisationseinheit = new organisationseinheit($resBetriebsmittel->oe_kurzbz);
	$OrgBezeichnung=(isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:'*'.$resultBetriebsmittel[0]->oe_kurzbz);
	$OrgTitel=(isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung.' '.$oOrganisationseinheit->organisationseinheittyp_kurzbz:$resultBetriebsmittel[0]->oe_kurzbz.' Fehlt');

	// Ort - Inventarstandort
	$oOrt = new ort($resBetriebsmittel->ort_kurzbz);
	$OrtBezeichnung=(isset($oOrt->bezeichnung) && $oOrt->bezeichnung?$oOrt->ort_kurzbz:'*'.$resBetriebsmittel->ort_kurzbz);
	$OrtTitel=(isset($oOrt->bezeichnung) && $oOrt->bezeichnung?$oOrt->ort_kurzbz.' '.($oOrt->bezeichnung?$oOrt->bezeichnung:'').' '.$OrtBezeichnung.' '.($oOrt->telefonklappe?'Kl.'.$oOrt->telefonklappe:''):$resBetriebsmittel->ort_kurzbz.' Kontrolle');

	// String - Daten Leerzeichen am Ende entfernen
	$resBetriebsmittel->bestellnr=trim($resBetriebsmittel->bestellnr);
	$resBetriebsmittel->titel=trim($resBetriebsmittel->titel);
	$resBetriebsmittel->firma_id=trim($resBetriebsmittel->firma_id);
	$resBetriebsmittel->firmenname=trim($resBetriebsmittel->firmenname);

	$htmlstring.='<fieldset><legend title="Betriebsmittel ID '.$resBetriebsmittel->betriebsmittel_id.'">Inventar '.$resBetriebsmittel->inventarnummer.'</legend>';
		$htmlstring.='<fieldset><legend>Kopfdaten  '.$resBetriebsmittel->betriebsmittel_id.'</legend>';
			$htmlstring.='<table class="liste">';
			$htmlstring.='<tr>
						<th align="right">Betriebsmitteltyp&nbsp;:&nbsp;</th>
						<td>'.$resBetriebsmittel->betriebsmitteltyp.'</td>

						<th align="right">Ort&nbsp;:&nbsp;</th>
						<td>'.$OrtBezeichnung.'</td>

						<th align="right">Organisation&nbsp;:&nbsp;</th>
						<td>'.$OrgTitel.'</td>
					</tr>';

			$htmlstring.='<tr>';
			$htmlstring.='<th align="right">Bestellnr.&nbsp;:&nbsp;</th>
						<td><a href="../../addons/wawi/vilesci/bestellung.php?method=update&amp;id='.$resBetriebsmittel->bestellung_id.'">'.$resBetriebsmittel->bestellnr.'</a></td>';

			$htmlstring.='<th align="right" nowrap>Bestell ID.&nbsp;:&nbsp;</th>';

		if ( ($schreib_recht && !$resBetriebsmittel->bestellung_id)
		||	($schreib_recht && $resBetriebsmittel->bestellung_id && !$resBetriebsmittel->bestellnr))
			$htmlstring.='<form name="sendform0" action="'. $_SERVER["PHP_SELF"].'" method="post" enctype="application/x-www-form-urlencoded">
				<td>
					<input style="display:none" name="work" value="set_position" >
					<input style="display:none" name="inventarnummer" value="'.$resBetriebsmittel->inventarnummer.'" >
					<input style="display:none" name="betriebsmittel_id" value="'.$resBetriebsmittel->betriebsmittel_id.'" >
					<input style="display:none" name="bestelldetail_id" value="'.$resBetriebsmittel->bestelldetail_id.'" >
					<input id="bestellung_ids" name="bestellung_id" size="6" maxlength="41"  value="'.$resBetriebsmittel->bestellung_id.'" >
					<script type="text/javascript">
						$(document).ready(function()
						{
							  $("#bestellung_ids").autocomplete({
							    source:"inventar_autocomplete.php?work=wawi_bestellung_id",
								minLength:2,
								response: function(event, ui)
								{
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].bestellung_id;
										ui.content[i].label=ui.content[i].bestellung_id+\', \'+ui.content[i].insertamum+\', \'+ui.content[i].bestell_nr+\', \'+ui.content[i].titel+\', \'+ui.content[i].bemerkung;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.bestellung_id;
								}
							  });
					  });
					</script>
				</td>
			</form>';
		/*
			<input onchange="setTimeout(\'document.sendform0.submit()\',1500);" id="bestellung_ids" name="bestellung_id" size="6" maxlength="41"  value="'.$resBetriebsmittel->bestellung_id.'" >
			$(document).ready(function()
			{
				  $("#bestellung_ids").autocomplete("inventar_autocomplete.php",
				  {
					minChars:4,
					matchSubset:1,matchContains:1,
					width:500,
					formatItem:formatItem,
					extraParams:{"work":"wawi_bestellung_id"}
				  });
			});
		*/
		else
			$htmlstring.='<td><a href="../../addons/wawi/vilesci/bestellung.php?method=update&amp;id='.$resBetriebsmittel->bestellung_id.'">'.$resBetriebsmittel->bestellung_id.'</a></td>';

		$htmlstring.='<th align="right" nowrap>Bestellpos. ID.&nbsp;:&nbsp;</th>';
		if ($schreib_recht && $resBetriebsmittel->bestellung_id)
			$htmlstring.='<form name="sendform1" action="'. $_SERVER["PHP_SELF"].'" method="post" enctype="application/x-www-form-urlencoded">
				<td>
					<input style="display:none" name="work" value="set_position" >
					<input style="display:none" name="inventarnummer" value="'.$resBetriebsmittel->inventarnummer.'" >
					<input style="display:none" name="betriebsmittel_id" value="'.$resBetriebsmittel->betriebsmittel_id.'" >
					<input style="display:none" name="bestellung_id" value="'.$resBetriebsmittel->bestellung_id.'" >
					<input id="bestelldetail_ids"   name="bestelldetail_id" size="6" maxlength="41"  value="'.$resBetriebsmittel->bestelldetail_id.'" >
					<script type="text/javascript">
						$(document).ready(function()
						{
							$("#bestelldetail_ids").autocomplete({
								source: "inventar_autocomplete.php?work=wawi_bestelldetail_id&bestellung_id='.$resBetriebsmittel->bestellung_id.'",
								minLength:1,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].bestelldetail_id;
										ui.content[i].label=ui.content[i].bestelldetail_id+\', \'+ui.content[i].beschreibung+\' \'+ui.content[i].artikelnummer+\' Preis VE \'+ui.content[i].preisprove+\', Menge \'+ui.content[i].menge;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.bestelldetail_id;
								}
							});
					  });
					</script>
				</td>
			</form>';

			//<input onchange="setTimeout(\'document.sendform1.submit()\',1500);" id="bestelldetail_ids"   name="bestelldetail_id" size="6" maxlength="41"  value="'.$resBetriebsmittel->bestelldetail_id.'" >
				/*
						$(document).ready(function()
						{
							  $("#bestelldetail_ids").autocomplete("inventar_autocomplete.php",
							  {
								minChars:1,
								matchSubset:1,matchContains:1,
								width:500,
								formatItem:formatItem,
								extraParams:{
											"work":"wawi_bestelldetail_id"
											,"bestellung_id":"'.$resBetriebsmittel->bestellung_id.'"
											}
							  });
					  });
			 */
		else
			$htmlstring.='<td>'.$resBetriebsmittel->bestelldetail_id.'</td>';

		if ($resBetriebsmittel->bestellung_id && !$resBetriebsmittel->bestellnr)
			$htmlstring.='<tr class="error"  style="font-size:smaller;"><td colspan="12" >Achtung! Bestellung nicht mehr vorhanden!</td></tr>';

		$htmlstring.='</tr>';

			$htmlstring.='<tr>
						<th align="right">Beschreibung&nbsp;:&nbsp;</th>
						<td colspan="3">'.$resBetriebsmittel->beschreibung.'</td>
						<th align="right">Seriennummer&nbsp;:&nbsp;</th>
						<td>'.$resBetriebsmittel->seriennummer.'</td>
					</tr>';

			$htmlstring.='<tr>
						<th align="right">Lieferant&nbsp;:&nbsp;</th>
						<td colspan="3">'.$resBetriebsmittel->firmenname.' ('.$resBetriebsmittel->firma_id.')</td>
						<th align="right">Hersteller&nbsp;:&nbsp;</th>
						<td>'.$resBetriebsmittel->hersteller.'</td>
					</tr>';

			$htmlstring.='<tr>
						<th align="right" valign="top">Anmerkung&nbsp;:&nbsp;</th>
						<td colspan="3">'.$resBetriebsmittel->anmerkung.'</td>
						</tr>';

			$htmlstring.='<tr>
						<th align="right" valign="top">Verwendung&nbsp;:&nbsp;</th>
						<td colspan="3">'.$resBetriebsmittel->verwendung.'</td>
						<th align="right">Leasing bis&nbsp;:&nbsp;</th>
						<td>'.$datum_obj->formatDatum($resBetriebsmittel->leasing_bis,'d.m.Y').'</td>
					</tr>';

			$htmlstring.='<tr><td>&nbsp;</td></tr>';

			$htmlstring.='<tr>';

			$htmlstring.='
				<th align="right">Status&nbsp;:&nbsp;</th>
			    <form name="sendform2" action="'. $_SERVER["PHP_SELF"].'" method="post" enctype="application/x-www-form-urlencoded">
				<td>
					<input style="display:none" name="work" value="set_status" >
					<input style="display:none" name="betriebsmittelbetriebsmittelstatus_id" value="'.$resBetriebsmittel->betriebsmittelbetriebsmittelstatus_id.'" >
					<input style="display:none" name="inventarnummer" value="'.$resBetriebsmittel->inventarnummer.'" >
					<input style="display:none" name="betriebsmittel_id" value="'.$resBetriebsmittel->betriebsmittel_id.'" >
					<input style="display:none" name="bestellung_id" value="'.$resBetriebsmittel->bestellung_id.'" >
					<input style="display:none" id="bestelldetail_id" name="bestelldetail_id" value="'.$resBetriebsmittel->bestelldetail_id.'" >
					';
			// mit Berechtigung ist der Status zum bearbeiten
			  	$betriebsmittelstatus_kurzbz_select=trim($resBetriebsmittel->betriebsmittelstatus_kurzbz);
				if (!$schreib_recht)
					$htmlstring.=$betriebsmittelstatus_kurzbz_select;
				else
				{
					$htmlstring.='&nbsp;<select onchange="document.sendform2.submit();" name="betriebsmittelstatus_kurzbz">';
							for ($i=0;$i<count($resultBetriebsmittelstatus) ;$i++)
							{
								if ($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)
									$htmlstring.='<option '.(trim($betriebsmittelstatus_kurzbz_select)==trim($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
							}
							$htmlstring.='<option '.(trim($betriebsmittelstatus_kurzbz_select)==''?' selected="selected" ':'').' value="">Status ?&nbsp;</option>';
					$htmlstring.='</select>';
			}
			$htmlstring.='</td>
			</form>
			';
			$htmlstring.='<th align="right">AfA Ende&nbsp;:&nbsp;</th>
						<td>'.$datum_obj->formatDatum($resBetriebsmittel->betriebsmittelstatus_datum_afa,'d.m.Y').'</td>

						<th align="right">Anschaffungsdatum&nbsp;:&nbsp;</th>
						<td>'.$datum_obj->formatDatum($resBetriebsmittel->anschaffungsdatum,'d.m.Y').'</td>
					</tr>';
			$htmlstring.='
					<tr>
						<th align="right">Letzte Inventur&nbsp;:&nbsp;</th>
						<td>'.$datum_obj->formatDatum($resBetriebsmittel->inventuramum,'d.m.Y').' '.$resBetriebsmittel->inventurvon.'</td>
						<td>
						<form action="'.$_SERVER['PHP_SELF'].'" mehtod="post">
						<input type="hidden" name="betriebsmittel_id" value="'.$resBetriebsmittel->betriebsmittel_id.'" >
						<input type="hidden" name="work" value="set_inventur" />
						<input type="submit" value="Inventur" />
						</form>
						</td>
						<td></td>
						<th align="right">Anschaffungswert&nbsp;:&nbsp;</th>
						<td>'.$resBetriebsmittel->anschaffungswert.'</td>
					</tr>';
		$htmlstring.='<tr><td colspan="6">&nbsp;</td></tr>';

			// Inventardaten Benutzer - Anlage und Aenderung
			$htmlstring.='<tr><td colspan="6">
				<table><tr>';
					$oUpdateBenutzer = new benutzer($resBetriebsmittel->insertvon);
					$htmlstring.='
								<td valign="top" align="right">&nbsp;Anlage&nbsp;:</td>
								<td valign="top"><a href="mailto:'.$oUpdateBenutzer->uid.'@'.DOMAIN.'?subject=Betriebsmittel - Inventar '.$resBetriebsmittel->inventarnummer.'">'.(isset($oUpdateBenutzer->person_id)?(isset($oUpdateBenutzer->anrede) && !empty($oUpdateBenutzer->anrede)?$oUpdateBenutzer->anrede.' ':'').
									(isset($oUpdateBenutzer->titelpre) && !empty($oUpdateBenutzer->titelpre)?$oUpdateBenutzer->titelpre.' ':'').
									$oUpdateBenutzer->vorname.' '.$oUpdateBenutzer->nachname.'</a>':$resBetriebsmittel->insertvon).'<br>'.$datum_obj->formatDatum($resBetriebsmittel->insertamum,'d.m.Y H:i:s').'&nbsp;
								</td>
								';
					$resBetriebsmittel->updatevon=($resBetriebsmittel->updatevon?$resBetriebsmittel->updatevon:$resBetriebsmittel->insertvon);
					$oUpdateBenutzer = new benutzer($resBetriebsmittel->updatevon);
					$htmlstring.='
								<td valign="top" align="right">&nbsp;letzte &Auml;nderung&nbsp;:</td>
								<td valign="top"><a href="mailto:'.$oUpdateBenutzer->uid.'@'.DOMAIN.'?subject=Betriebsmittel - Inventar '.$resBetriebsmittel->inventarnummer.'">'.(isset($oUpdateBenutzer->person_id)?(isset($oUpdateBenutzer->anrede) && !empty($oUpdateBenutzer->anrede)?$oUpdateBenutzer->anrede.' ':'').
									(isset($oUpdateBenutzer->titelpre) && !empty($oUpdateBenutzer->titelpre)?$oUpdateBenutzer->titelpre.' ':'').
									$oUpdateBenutzer->vorname.' '.$oUpdateBenutzer->nachname.'</a>':$resBetriebsmittel->updatevon).'<br>'.$datum_obj->formatDatum($resBetriebsmittel->updateamum,'d.m.Y H:i:s').'&nbsp;
								</td>
								';
//------------------------------------------------------------------------------------------------
// Inventardaten --- AENDERN  und ETIKETTENDruck
//------------------------------------------------------------------------------------------------
	// call Datenwartung - Pflege
		if($schreib_recht)
			$htmlstring.='<td>&nbsp;<a href="inventar_pflege.php?betriebsmittel_id='.$resBetriebsmittel->betriebsmittel_id.'">
					<img src="../../skin/images/application_form_edit.png" alt="anzeigen - pflegen">
					&auml;ndern</a>&nbsp;</td>';

	// nur Admin oder Support darf wirklich loeschen
			if(trim($schreib_recht)==trim($schreib_recht_administration) || !empty($delete_recht) )
			{
				$htmlstring.='
				<td  id="bcDelete">
					<a href="'.$_SERVER['PHP_SELF'].'?work=set_delete&betriebsmittel_id='.$resBetriebsmittel->betriebsmittel_id.'" onclick="return confdel()">
					<img border="0"  src="../../skin/images/application_form_delete.png" alt="Entfernen">
					l&ouml;schen
					</a>
					</td>
					<script type="text/javascript">
					   function confdel()
					   {
					   		return confirm("Wollen Sie dieses Betriebsmittel wirklich loeschen?");
					   }
					</script>';
				}
				// 	Etikettendruck
				/*
				$htmlstring.='
				<td id="bcTargets">&nbsp;<img border="0" src="../../skin/images/printer.png" alt="Etik"> druck</td>
					<script type="text/javascript">
					   $(document).ready(function()
					   {
					   	$("td#bcTargets").click(function(event)
						{
							var PrintWin=window.open("etiketten.php?inventarnummer='. urlencode($resBetriebsmittel->inventarnummer).'","Etik","copyhistory=no,directories=no,location=no,dependent=yes,toolbar=no,status=no,menubar=no,resizable=yes,scrollbars=yes,width=400,height=300,left=20, top=20");
							if (PrintWin)
							{
								PrintWin.focus();
							}
					   });
					});
					</script>';*/
				$htmlstring.='</tr>
			</table></td></tr>';
		$htmlstring.='<tr>';
		$htmlstring.='</table>';

	$htmlstring.='</fieldset>';
	$htmlstring.='<fieldset><legend>History</legend>';
//------------------------------------------------------------------------------------------------
// Betriebsmittel STATUS - History
//------------------------------------------------------------------------------------------------
	$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
	$oBetriebsmittel_betriebsmittelstatus->result=array();
	$oBetriebsmittel_betriebsmittelstatus->debug=$debug;
	$oBetriebsmittel_betriebsmittelstatus->errormsg='';
	if (!$oBetriebsmittel_betriebsmittelstatus->load_betriebsmittel_id($resBetriebsmittel->betriebsmittel_id))
		$htmlstring.='<br>'.$oBetriebsmittel_betriebsmittelstatus->errormsg;

	if (is_array($oBetriebsmittel_betriebsmittelstatus->result) && count($oBetriebsmittel_betriebsmittelstatus->result)>0)
	{
		$htmlstring.='<table>';
		$htmlstring.='<tr>
						<thead>
							<th>Status</th>
							<th>ab Datum</th>
							<th colspan="2">Anlage</th>
							<th colspan="2">&Auml;nderung</th>
						</thead>
					</tr>';
		for ($pos=0;$pos<count($oBetriebsmittel_betriebsmittelstatus->result);$pos++)
		{
			$row=$oBetriebsmittel_betriebsmittelstatus->result[$pos];
			$oInsertBenutzer = new benutzer($row->insertvon);
			$oUpdateBenutzer = new benutzer($row->updatevon);
			if ($pos%2)
				$classe='liste1';
			else
				$classe='liste0';
			$htmlstring.='<tr class="'.$classe.'">
							<td '.$row->betriebsmittelstatus_kurzbz.'>'.$row->betriebsmittelstatus_kurzbz.'</td>
							<td>'.$datum_obj->formatDatum($row->datum,'d.m.Y').'</td>

							<td><a href="mailto:'.$oInsertBenutzer->uid.'@'.DOMAIN.'?subject=Betriebsmittel - Inventar '.$resBetriebsmittel->inventarnummer.'">'.(isset($oInsertBenutzer->person_id)?(isset($oInsertBenutzer->anrede) && !empty($oInsertBenutzer->anrede)?$oInsertBenutzer->anrede.' ':'').
								(isset($oInsertBenutzer->titelpre) && !empty($oInsertBenutzer->titelpre)?$oInsertBenutzer->titelpre.' ':'').
								$oInsertBenutzer->vorname.' '.$oInsertBenutzer->nachname.'</a>':$row->insertvon).'</td>

							<td>'.$datum_obj->formatDatum($row->insertamum,'d.m.Y H:i:s').'</td>

							<td><a href="mailto:'.$oUpdateBenutzer->uid.'@'.DOMAIN.'?subject=Betriebsmittel - Inventar '.$resBetriebsmittel->inventarnummer.'">'.(isset($oUpdateBenutzer->person_id)?(isset($oUpdateBenutzer->anrede) && !empty($oUpdateBenutzer->anrede)?$oUpdateBenutzer->anrede.' ':'').
								(isset($oUpdateBenutzer->titelpre) && !empty($oUpdateBenutzer->titelpre)?$oUpdateBenutzer->titelpre.' ':'').
								$oUpdateBenutzer->vorname.' '.$oUpdateBenutzer->nachname.'</a>':$row->updatevon).'</td>

							<td>'.$datum_obj->formatDatum($row->updateamum,'d.m.Y H:i:s').'</td>
						</tr>';

			if ($row->anmerkung)
			{
				if($schreib_recht==$schreib_recht_administration)
					$htmlstring.='<tr class="'.$classe.'">
							<td colspan="6"><textarea rows="1" cols="80"  wrap="soft" readonly="readonly">'.$row->anmerkung.'</textarea></td>
						</tr>';
				else
					$htmlstring.='<tr class="'.$classe.'">
							<td colspan="6">'.$row->anmerkung.'</td>
						</tr>';
			}
		}
	}
	$htmlstring.='</table>';
	$htmlstring.='</fieldset>';
	$htmlstring.='<fieldset><legend>Betriebsmittelperson(en)</legend>';

//------------------------------------------------------------------------------------------------
// Betriebsmittel Personen
//------------------------------------------------------------------------------------------------
	$oBetriebsmittelperson = new betriebsmittelperson();
	$oBetriebsmittelperson->result=array();
	$oBetriebsmittelperson->debug=$debug;
	$oBetriebsmittelperson->errormsg='';
	if (!$oBetriebsmittelperson->getbetriebsmittelpersonen($resBetriebsmittel->betriebsmittel_id))
		$htmlstring.='<br>'.$oBetriebsmittelperson->errormsg;
	if (is_array($oBetriebsmittelperson->result) && count($oBetriebsmittelperson->result)>0)
	{
		$htmlstring.='<table class="liste">';
			$htmlstring.='<tr>
						<thead>
							<th></th>
							<th>Person</th>
							<th>Ausgabe</th>
							<th>Retour</th>
							<th colspan="2">Anlage</th>
							<th colspan="2">&Auml;nderung</th>
						</thead>
						</tr>';
		for ($pos=0;$pos<count($oBetriebsmittelperson->result);$pos++)
		{
			$row=$oBetriebsmittelperson->result[$pos];
			if ($pos%2)
				$classe='liste1';
			else
				$classe='liste0';
			$htmlstring.='<tr class="'.$classe.'">
							<td>
								<a href="../../content/pdfExport.php?xsl=Uebernahme&xml=betriebsmittelperson.rdf.php&id='.$row->betriebsmittelperson_id.'" title="Übernahmebestätigung">
								<img src="../../skin/images/pdfpic.gif">
								</a>
							</td>
							<td>';
										$oPerson = new person();
										if (!$oPerson->load($row->person_id))
											$htmlstring.=$oPerson->errormsg;
										else if ($oPerson->nachname)
											$htmlstring.=$oPerson->anrede.($oPerson->titelpre?'&nbsp;'.$oPerson->titelpre:'').'&nbsp;'.$oPerson->vorname.'&nbsp;'.$oPerson->nachname.'&nbsp;'.($oPerson->aktiv==true || $oPerson->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" >':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" >');
										else
											$htmlstring.=$row->person_id;
			$htmlstring.='	</td>
							<td>'.$datum_obj->formatDatum($row->ausgegebenam,'d.m.Y').'</td>
							<td>'.$datum_obj->formatDatum($row->retouram,'d.m.Y').'</td>
							<td>';
										$oBenutzer = new benutzer();
										if (!$oBenutzer->load($row->insertvon))
											$htmlstring.=$oBenutzer->errormsg;
										else if ($oBenutzer->nachname)
											$htmlstring.=$oBenutzer->anrede.($oBenutzer->titelpre?'&nbsp;'.$oBenutzer->titelpre:'').'&nbsp;'.$oBenutzer->vorname.'&nbsp;'.$oBenutzer->nachname.'&nbsp;'.($oBenutzer->aktiv==true || $oBenutzer->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" >':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" >');
										else
											$htmlstring.=$row->insertvon;
			$htmlstring.='	</td>
							<td>'.$datum_obj->formatDatum($row->insertamum,'d.m.Y H:i:s').'</td>
							<td>';
										$oBenutzer = new benutzer();
										if (!$oBenutzer = new benutzer($row->updatevon))
											$htmlstring.=$oBenutzer->errormsg;
										else if ($oBenutzer->nachname)
											$htmlstring.=$oBenutzer->anrede.($oBenutzer->titelpre?'&nbsp;'.$oBenutzer->titelpre:'').'&nbsp;'.$oBenutzer->vorname.'&nbsp;'.$oBenutzer->nachname.'&nbsp;'.($oBenutzer->aktiv==true || $oBenutzer->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" >':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" >');
										else
											$htmlstring.=$row->updatevon;
			$htmlstring.='	</td>
							<td>'.$datum_obj->formatDatum($row->updateamum,'d.m.Y H:i:s').'</td>
						</tr>';
		}
		$htmlstring.='</table>';
	}
	else
	{
		$htmlstring.='keine Person(en) zum Betriebsmittel';
	}
	$htmlstring.='</fieldset>';
	$htmlstring.='</fieldset>';
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" >&nbsp;zur&uuml;ck&nbsp;</a></div >';
	return 	$htmlstring;
}
?>
