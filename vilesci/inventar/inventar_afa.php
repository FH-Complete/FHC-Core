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
 * Liefert eine Liste aller abgeschriebenen Betriebsmittel im ausgewähltem Monat/Jahr
 */
	include_once('../../config/vilesci.config.inc.php');
	include_once('../../include/basis_db.class.php');
 	require_once('../../include/functions.inc.php');
 	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/mitarbeiter.class.php');
	require_once('../../include/ort.class.php');
 	require_once('../../include/organisationseinheit.class.php');
 	require_once('../../include/betriebsmittel.class.php');
 	require_once('../../include/betriebsmittelperson.class.php');
 	require_once('../../include/betriebsmitteltyp.class.php');
 	require_once('../../include/betriebsmittelstatus.class.php');
 	require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
 	require_once('../../include/datum.class.php');

	if (!$uid = get_uid())
			die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$oBenutzerberechtigung = new benutzerberechtigung();
	if (!$oBenutzerberechtigung->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
// Initialisierung
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$default_status_vorhanden='vorhanden';
	$berechtigung_kurzbz='wawi/inventar:begrenzt';
	$schreib_recht=false;
	$datum_obj = new datum();

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
 	$inventarnummer=trim((isset($_REQUEST['inventarnummer']) ? $_REQUEST['inventarnummer']:''));
 	$seriennummer=trim((isset($_REQUEST['seriennummer']) ? $_REQUEST['seriennummer']:''));
 	$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
 	$oe_kurzbz=trim((isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz']:''));
 	$beschreibung=trim((isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung']:''));
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

 	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);

 	$extend_search=trim(isset($_REQUEST['extend_search']) ?$_REQUEST['extend_search']:'false');
	$check=$firma_id.$bestellung_id.$bestelldetail_id.$bestellnr.$hersteller.$betriebsmitteltyp.$beschreibung.$oe_kurzbz;
	$extend_search=($check?'true':$extend_search);

	// Pruefen ob Schreibrechte (Anzeigen der Aenderungsmoeglichkeit)
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'su'))
		$schreib_recht=true;
	if (!$schreib_recht)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
// Verarbeitung - Ajax oder Work
// ------------------------------------------------------------------------------------------
 	$ajax=trim(isset($_REQUEST['ajax']) ?$_REQUEST['ajax']:false);
  	$work=trim(isset($_REQUEST['work']) ?$_REQUEST['work']:false);

	if (($ajax && strtolower($ajax)=='set_status')
	||  ($work && strtolower($work)=='set_status') )
	{
		if ($schreib_recht)
		{
			$betriebsmittel_id=trim(isset($_REQUEST['betriebsmittel_id']) ? $_REQUEST['betriebsmittel_id']:'');
			$oBetriebsmittel = new betriebsmittel($betriebsmittel_id);
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->debug=$debug;
			$oBetriebsmittel->updatevon=$uid;
			$oBetriebsmittel->updateamum=null;
			if ($oBetriebsmittel->save())
			{
				$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
				$oBetriebsmittel_betriebsmittelstatus->result=array();
				$oBetriebsmittel_betriebsmittelstatus->errormsg='';
				$oBetriebsmittel_betriebsmittelstatus->debug=$debug;

				$oBetriebsmittel_betriebsmittelstatus->new=true;

				$oBetriebsmittel_betriebsmittelstatus->betriebsmittelbetriebsmittelstatus_id=trim(isset($_REQUEST['betriebsmittelbetriebsmittelstatus_id']) ? $_REQUEST['betriebsmittelbetriebsmittelstatus_id']:'');
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittel_id=$oBetriebsmittel->betriebsmittel_id;
				$oBetriebsmittel_betriebsmittelstatus->datum=date('Y-m-d');
				$oBetriebsmittel_betriebsmittelstatus->updatevon=$uid;
				$oBetriebsmittel_betriebsmittelstatus->updateamum='';
				$oBetriebsmittel_betriebsmittelstatus->insertvon=$uid;
				$oBetriebsmittel_betriebsmittelstatus->insertamum='';
				$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz=trim((isset($_REQUEST['betriebsmittelstatus_kurzbz']) ? $_REQUEST['betriebsmittelstatus_kurzbz']:''));
				if (!$oBetriebsmittel_betriebsmittelstatus->save())
					$errormsg[]='<span title="die Neue Status ID ist '.$oBetriebsmittel_betriebsmittelstatus->betriebsmittelbetriebsmittelstatus_id.'">Neuanlage '.$oBetriebsmittel_betriebsmittelstatus->betriebsmittelstatus_kurzbz.' erfolgreich</span>';
				else
					$errormsg[]=$oBetriebsmittel_betriebsmittelstatus->errormsg;
			}
			else
				$errormsg[]=$oBetriebsmittel->errormsg;
		}
		else
			$errormsg[]='Sie haben keine Berechtigung fuer die Datenbearbeitung';
		// Fehlerausgabe bzw. Informationen ueber den Status der Verarbeitung
	}

	if (($ajax && strtolower($ajax)=='set_position')
	||  ($work && strtolower($work)=='set_position') )
	{
		if ($schreib_recht)
		{
			$betriebsmittel_id=trim(isset($_REQUEST['betriebsmittel_id']) ? $_REQUEST['betriebsmittel_id']:'');
			$oBetriebsmittel = new betriebsmittel($betriebsmittel_id);
			$oBetriebsmittel->result=array();
			$oBetriebsmittel->debug=$debug;

			$oBetriebsmittel->bestelldetail_id=$bestelldetail_id;
			$oBetriebsmittel->updatevon=$uid;
			$oBetriebsmittel->updateamum=null;

			if (!$oBetriebsmittel->save())
				$errormsg[]=$oBetriebsmittel->errormsg;
			// Fehlerausgabe bzw. Informationen ueber den Status der Verarbeitung
		}
		else
			$errormsg[]='Sie haben keine Berechtigung fuer die Datenbearbeitung';
	}
	if (!empty($ajax))
	{
		if (is_array($errormsg) && count($errormsg)>0)
			exit(implode(", ",$errormsg));
		elseif (!is_array($errormsg))
			exit($errormsg);
		else
			exit('<img src="../../skin/images/tick.png" alt="ok '.$ajax.'" />');
	}

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------

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
	if($oBenutzerberechtigung->isBerechtigt('wawi/inventar', null, 's'))
	{
		if (!$oOrganisationseinheit->getAll())
			$errormsg[]=$oOrganisationseinheit->errormsg;
	}
	else
	{
		$oes=new organisationseinheit();
		if (!$oOrganisationseinheit->loadArray($oBenutzerberechtigung->getOEkurzbz($berechtigung_kurzbz),'oe_kurzbz'))
			$errormsg[]=$oOrganisationseinheit->errormsg;
	}
	$resultOrganisationseinheit=$oOrganisationseinheit->result;

	// Typtable
	if (!$oBetriebsmitteltyp->getAll())
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
		<title>Inventar - AfA</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<!--		<link rel="stylesheet" href="../../include/js/jquery.css" rel="stylesheet" type="text/css"> -->
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

<!--		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script> -->

		<script type="text/javascript">
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

			function toggledisplay (id){
			  if (document.getElementById) {
			    var mydiv = document.getElementById(id);
			    mydiv.style.display = (mydiv.style.display=='block'?'none':'block');
			  }
			}
			function formatItem(row)
			{
			    return row[0] + " <i>" + row[1] + "</i> ";
			}
		</script>
	</head>
	<body>

		<h1 title="Anwender:<?php echo $uid ?>">&nbsp;Inventar - AfA&nbsp;</h1>
	    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">

		<div>
		<table class="navbar">
			<tr>
				<td><label for="inventarnummer">Inv.nr.</label>&nbsp;
<!--					<input onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1500);}" id="inventarnummer" name="inventarnummer" type="text" size="10" maxlength="30" value="<?php echo $inventarnummer;?>" />&nbsp; -->
					<input id="inventarnummer" name="inventarnummer" type="text" size="10" maxlength="30" value="<?php echo $inventarnummer;?>" />&nbsp;
					<script type="text/javascript">
						function selectItem(li)
						{
						   return false;
						}
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
							/*  $('#inventarnummer').autocomplete('inventar_autocomplete.php',
							  {
								minChars:2,
								scroll: true,
						        scrollHeight: 200,
								width:350,
								onItemSelect:selectItem,
								formatItem:formatItem,
								extraParams:{'work':'inventarnummer'
											,'afa':$("#afa").val()
											,'betriebsmitteltyp':$("#betriebsmitteltyp").val()
											 }
							  }); */
					  });
					</script>
				</td>
				<td><label for="seriennummer">Seriennr.</label>&nbsp;
					<input id="seriennummer"  name="seriennummer" type="text" size="10" maxlength="60" value="<?php echo $seriennummer;?>" />&nbsp;
					<script type="text/javascript">
						function selectItem(li) {
						   return false;
						}
						$(document).ready(function() {
							$('#seriennummer').autocomplete({
								source: "inventar_autocomplete.php?work=seriennummer",
								minLength:4,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].seriennummer;
										ui.content[i].label=ui.content[i].seriennummer+' '+ui.content[i].beschreibung;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.seriennummer;
								}
							});
							/*  $('#seriennummer').autocomplete('inventar_autocomplete.php', {
								minChars:4,
								matchSubset:1,matchContains:1,
								width:900,
								cacheLength:0,
								onItemSelect:selectItem,
								formatItem:formatItem,
								extraParams:{'work':'seriennummer'
											,'afa':$("#afa").val()
											,'betriebsmitteltyp':$("#betriebsmitteltyp").val()
											 }
							  }); */
					  });
					</script>
				</td>

				<td><label for="ort_kurzbz">Ort</label>&nbsp;
						<input id="ort_kurzbz" name="ort_kurzbz" size="10" maxlength="40" value="<?php echo $ort_kurzbz;?>" />&nbsp;
						<script type="text/javascript">

						function selectItem(li) {
						   return false;
						}

						$(document).ready(function() {
							$('#ort_kurzbz').autocomplete({
								source: "inventar_autocomplete.php?work=inventar_ort",
								minLength:2,
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
								}
							});
							 /* $('#ort_kurzbz').autocomplete('inventar_autocomplete.php', {
								minChars:2,
								matchSubset:1,matchContains:1,
								width:300,
								cacheLength:0,
								onItemSelect:selectItem,
								formatItem:formatItem,
								extraParams:{'work':'inventar_ort'
											,'afa':$("#afa").val()
											,'betriebsmitteltyp':$("#betriebsmitteltyp").val()
											,'betriebsmittelstatus_kurzbz':$("#betriebsmittelstatus_kurzbz").val() }
							  }); */
					  });
						</script>
				</td>
				<td>Datum&nbsp;
					<select name="afa">
						<?php
						$afa_select=trim((!isset($_REQUEST['afa'])? date("Y-m"):$afa));
						$tmpJahr=(int)date("Y",mktime(0, 0, 0, 1, 1, date("Y")-12));
						for ($i=0;$i<12;$i++)
						{
							$tmpJahr=$tmpJahr + 1;
							$jjjjmm=$tmpJahr.'-00';
							echo '<option '.($afa_select==$tmpJahr?'  selected="selected" ':'').' value="'.$tmpJahr.'">&nbsp;--'.$tmpJahr.'--&nbsp;</option>';
							for ($ii=1;$ii<=12;$ii++)
							{
								$jjjjmm=$tmpJahr.'-'.($ii<10?'0'."$ii":$ii);
								echo '<option '.($afa_select==$jjjjmm?' selected="selected" ':'').' value="'.$jjjjmm.'">&nbsp;'.$jjjjmm.'&nbsp;</option>';
								if ($tmpJahr==date("Y") && $ii==date("m"))
									break;
							}
						}
						?>
						<option <?php echo ($afa_select=='-' || empty($afa_select)  ?'  selected="selected" ':''); ?>   value="-">&nbsp;-&nbsp;</option>
					</select>&nbsp;
				</td>
				<td class="ac_submit">&nbsp;<a href="javascript:document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
			</tr>
		</table>
		</div>
		<div id="ext_search" style="display:<?php echo ($extend_search && $extend_search!='false'?'block':'none'); ?>;">
			<table class="navbar">
			<tr>
				<td><label for="oe_kurzbz">Organisation</label>&nbsp;
					<select id="oe_kurzbz" name="oe_kurzbz" >
						<option  <?php echo (empty($oe_kurzbz)?' selected="selected" ':''); ?>  value="">bitte ausw&auml;hlen&nbsp;</option>
						<?php
						for ($i=0;$i<count($resultOrganisationseinheit) ;$i++)
						{
							if ($resultOrganisationseinheit[$i]->oe_kurzbz)
								echo '<option '.($oe_kurzbz==$resultOrganisationseinheit[$i]->oe_kurzbz?' selected="selected" ':'').' value="'.$resultOrganisationseinheit[$i]->oe_kurzbz.'">'.($resultOrganisationseinheit[$i]->bezeichnung=='NULL' || empty($resultOrganisationseinheit[$i]->bezeichnung)?$resultOrganisationseinheit[$i]->oe_kurzbz:$resultOrganisationseinheit[$i]->bezeichnung).'&nbsp;</option>';
						}
						?>
					</select>
				</td>
			</tr>
			</table>

			<table class="navbar">
			<tr>
				<td><label for="bestellnr">Bestellnr.</label>&nbsp;
					<input id="bestellnr" name="bestellnr" size="10" maxlength="30" type="Text" value="<?php echo $bestellnr; ?>" >&nbsp;
					<script type="text/javascript">
						function selectItem(li) {
						   return false;
						}

						$(document).ready(function() {
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
								}
							});
							/*  $('#bestellnr').autocomplete('inventar_autocomplete.php', {
								minChars:5,
								matchSubset:1,matchContains:1,
								width:500,
								cacheLength:0,
								onItemSelect:selectItem,
								formatItem:formatItem,
								extraParams:{'work':'wawi_bestellnr'}
							  }); */
					  });
					</script>
				</td>

				<!-- Bestell ID Eindeutigenummer -->
				<td><label for="bestellung_id">Bestell ID
					</label>&nbsp;<input id="bestellung_id" name="bestellung_id" size="10" maxlength="30" type="Text" value="<?php echo $bestellung_id; ?>" >&nbsp;
						<script type="text/javascript">
							function selectItem(li) {
							   return false;
							}

							$(document).ready(function() {
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
									}
								});
								/*  $('#bestellung_id').autocomplete('inventar_autocomplete.php', {
									minChars:3,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:0,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_bestellung_id'}
								  }); */
						  });
						</script>
				</td>

				<td>Lieferant&nbsp;<input id="firma_id" name="firma_id" size="10" maxlength="40" value="<?php echo $firma_id; ?>">&nbsp;
					<script type="text/javascript" language="JavaScript1.2">
							function selectItem(li) {
							   return false;
							}

							$(document).ready(function() {
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
								/*  $('#firma_id').autocomplete('inventar_autocomplete.php', {
									minChars:4,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:0,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_firma_search'}
								  }); */
						  });
						</script>
				</td>
				<td><label for="hersteller">Hersteller</label>&nbsp;<input id="hersteller" name="hersteller" type="text" size="10" maxlength="30" value="<?php echo $hersteller;?>">&nbsp;
					<script type="text/javascript">
						function selectItem(li) {
						   return false;
						}

						$(document).ready(function() {
							$('#hersteller').autocomplete({
								source: "inventar_autocomplete.php?work=hersteller",
								minLength:4,
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
							/*  $('#hersteller').autocomplete('inventar_autocomplete.php', {
								minChars:4,
								matchSubset:1,matchContains:1,
								width:400,
								cacheLength:0,
								onItemSelect:selectItem,
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
				<td><label for="betriebsmittelstatus_kurzbz">Status</label>&nbsp;
					<select id="betriebsmittelstatus_kurzbz" name="betriebsmittelstatus_kurzbz" >
						<option  <?php
								  	$betriebsmittelstatus_kurzbz_select=trim((!isset($_REQUEST['betriebsmittelstatus_kurzbz'])?$default_status_vorhanden:$betriebsmittelstatus_kurzbz));
									echo (empty($betriebsmittelstatus_kurzbz_select)?' selected="selected" ':''); ?>  value="">bitte ausw&auml;hlen&nbsp;</option>
									<?php
									for ($i=0;$i<count($resultBetriebsmittelstatus) ;$i++)
									{
										if ($resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz)
											echo '<option '.($betriebsmittelstatus_kurzbz_select==$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
									}
									?>
					</select>&nbsp;
				</td>
				<td>Betriebsmitteltyp&nbsp;
					<select name="betriebsmitteltyp"  onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1500);}">
						<option  <?php echo (empty($betriebsmitteltyp)?' selected="selected" ':''); ?>  value="">bitte ausw&auml;hlen&nbsp;</option>
									<?php

									for ($i=0;$i<count($resultBetriebsmitteltyp) ;$i++)
									{
										if ($resultBetriebsmitteltyp[$i]->betriebsmitteltyp)
											echo '<option '.($betriebsmitteltyp==$resultBetriebsmitteltyp[$i]->betriebsmitteltyp?' selected="selected" ':'').' value="'.$resultBetriebsmitteltyp[$i]->betriebsmitteltyp.'">'.($resultBetriebsmitteltyp[$i]->beschreibung=='NULL' || empty($resultBetriebsmitteltyp[$i]->beschreibung)?$resultBetriebsmitteltyp[$i]->betriebsmitteltyp:$resultBetriebsmitteltyp[$i]->beschreibung).'&nbsp;</option>';
									}
									?>
					</select>&nbsp;
				</td>
				<td>Bezeichnung&nbsp;<input name="beschreibung" type="text" size="40" maxlength="90" value="<?php echo $beschreibung;?>" />&nbsp;</td>
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
							<img src="../../skin/images/right.png" alt="anzeigen - show" />Erweiterte Suche anzeigen / ausblenden
							<input style="display:none;" type="text" id="extend_search" name="extend_search" value="<?php echo $extend_search;?>">
						</td>
					</tr>
					</table>
				</div>
			</div>
			<script type="text/javascript">
				var isShow = <?php echo ($extend_search && $extend_search!='false'?'true':'false'); ?>;
			   $(document).ready(function(){            // Pr�ft, ob das Dokument geladen ist
			   $("div#extend_search_on").click(function(event){  // Bei Klick auf div#
			      if (isShow == false) {
			         $("div#ext_search").show("slow");         // div# langsam �ffnen
			         isShow = true;
			      } else {
			         $("div#ext_search").hide("slow");         // div# langsam verbergen
			         isShow = false;
			      }
				  $("#extend_search").val(isShow);
			   });
			});
			</script>
		</div>
		</form>
		<hr />
<?php
// ----------------------------------------
// Inventardaten - lesen
// ----------------------------------------
	// pruefen ob eine Eingabe erfolgte
	if ($inventarnummer || $bestellung_id || $bestellnr || $seriennummer)
	{
		$afa='';
		$afa='';
		$jahr_monat='';
		$ort_kurzbz='';
		$oe_kurzbz='';
		$betriebsmitteltyp='';
		$betriebsmittelstatus_kurzbz='';
	}

 	$check=$inventarnummer.$ort_kurzbz.$betriebsmittelstatus_kurzbz.$betriebsmitteltyp.$bestellung_id.$bestelldetail_id.$bestellnr.$hersteller.$afa.$jahr_monat.$firma_id.$afa.$beschreibung.$oe_kurzbz.$seriennummer;
	$order=null; // Sortierung

	$oBetriebsmittel->result=array();
	$oBetriebsmittel->errormsg='';
	if ($check!='' && !$oBetriebsmittel->betriebsmittel_inventar($order,$inventarnummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$afa,$beschreibung,$oe_kurzbz,$seriennummer))
		$errormsg[]=$oBetriebsmittel->errormsg;

	// Ausgabe
	if (is_array($oBetriebsmittel->result) && count($oBetriebsmittel->result)==1)
	{
		echo output_inventarposition($debug,$oBetriebsmittel->result,$resultBetriebsmittelstatus,$schreib_recht);
	}
	else if (is_array($oBetriebsmittel->result) && count($oBetriebsmittel->result)>1)
	{
		echo output_inventar($debug,$oBetriebsmittel->result,$resultBetriebsmittelstatus,$schreib_recht);
	}
	else
	{
		if ($check!='' )
			$errormsg[]='keine Daten gefunden';
		else
			$errormsg[]='Auswahl fehlt';
	}

	// Error - Meldungen ausgeben
	if (is_array($errormsg) && count($errormsg)>0)
		echo '<font class="error">'. implode("<br />",$errormsg).'</font>';
	elseif (!is_array($errormsg))
		echo '<font class="error"><br />'.$errormsg.'</font>';
?>
</body>
</html>
<?php
// Ausgabe der Bestellungen in Listenform
function output_inventar($debug=false,$resultBetriebsmittel=null,$resultBetriebsmittelstatus=array(),$schreib_recht=false)
{
	$htmlstring='';
	if (is_null($resultBetriebsmittel) || !is_array($resultBetriebsmittel) || count($resultBetriebsmittel)<1)
		return $htmlstring;

	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultBetriebsmittel) && count($resultBetriebsmittel)>1)
		$htmlstring.='<tr><th colspan="12">Bitte ein Inventar aus den '.count($resultBetriebsmittel).' gefundenen ausw&auml;hlen</th></tr>';
	$htmlstring.='<tr>
				<th class="table-sortable:default">Inv.nr.</th>
				<th class="table-sortable:default">Standort</th>
				<th class="table-sortable:default">Datum</th>
				<th class="table-sortable:default">AfA</th>
				<th class="table-sortable:default">Org.</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default">Ser.nr.</th>
				<th class="table-sortable:default">Status</th>
			</tr>
			</thead>
		';

	for ($pos=0;$pos<count($resultBetriebsmittel);$pos++)
	{

		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';


		// Organisation - Inventarverwalter
		$oOrganisationseinheit = new organisationseinheit($resultBetriebsmittel[$pos]->oe_kurzbz);
		$OrgBezeichnung=(isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:'*'.$resultBetriebsmittel[$pos]->oe_kurzbz);
		$OrgTitel=(isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung.' '.$oOrganisationseinheit->organisationseinheittyp_kurzbz:$resultBetriebsmittel[$pos]->oe_kurzbz.' Kontrolle');

		// Ort - Inventarstandort
		$oOrt = new ort($resultBetriebsmittel[$pos]->ort_kurzbz);
		$OrtBezeichnung=(isset($oOrt->bezeichnung) && $oOrt->bezeichnung?$oOrt->ort_kurzbz:'*'.$resultBetriebsmittel[$pos]->ort_kurzbz);
		$OrtTitel=(isset($oOrt->bezeichnung) && $oOrt->bezeichnung?$oOrt->ort_kurzbz.' '.$oOrt->bezeichnung:$resultBetriebsmittel[$pos]->ort_kurzbz.' Kontrolle');

		$htmlstring.='<tr class="'.$classe.'"  style="font-size:smaller;">

		<td><a href="'.$_SERVER["PHP_SELF"].'?inventarnummer='.$resultBetriebsmittel[$pos]->inventarnummer.'&amp;bestellung_id='.$resultBetriebsmittel[$pos]->bestellung_id.'&amp;bestelldetail_id='.$resultBetriebsmittel[$pos]->bestelldetail_id.'">'.$resultBetriebsmittel[$pos]->inventarnummer.'</a>&nbsp;</td>

		<td title="'.$OrtTitel.'">'.$OrtBezeichnung.'&nbsp;</td>

		<td>'.$resultBetriebsmittel[$pos]->betriebsmittelstatus_datum.'&nbsp;</td>
		<td>'.$resultBetriebsmittel[$pos]->afa.'&nbsp;</td>

		<td title="'.$OrgTitel.'">'.$OrgBezeichnung.'&nbsp;</td>

		<td>'.StringCut($resultBetriebsmittel[$pos]->beschreibung,25).'&nbsp;</td>
		<td>'.$resultBetriebsmittel[$pos]->seriennummer.'&nbsp;</td>

		<td>';
			// mit Berechtigung ist der Status zum bearbeiten

	  	$betriebsmittelstatus_kurzbz_select=trim($resultBetriebsmittel[$pos]->betriebsmittelstatus_kurzbz);
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
			$htmlstring.='</select>';
		}
		$htmlstring.='&nbsp;</td>
		<td style="font-size:xx-small;" id="list'.$pos.'"></td>

		</tr>
		';
	}
	$htmlstring.='</table>';
	return 	$htmlstring;
}

// Ausgabe der Bestellungen in Listenform
function output_inventarposition($debug=false,$resultBetriebsmittel=null,$resultBetriebsmittelstatus=array(),$schreib_recht=false)
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

	// Organisation - Inventarverwalter
	$oOrganisationseinheit = new organisationseinheit($resBetriebsmittel->oe_kurzbz);
	$OrgBezeichnung=(isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:'*'.$resultBetriebsmittel[0]->oe_kurzbz);
	$OrgTitel=(isset($oOrganisationseinheit->bezeichnung) && $oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung.' '.$oOrganisationseinheit->organisationseinheittyp_kurzbz:$resultBetriebsmittel[0]->oe_kurzbz.' Kontrolle');

	// Ort - Inventarstandort
	$oOrt = new ort($resBetriebsmittel->ort_kurzbz);
	$OrtBezeichnung=(isset($oOrt->bezeichnung) && $oOrt->bezeichnung?$oOrt->ort_kurzbz:'*'.$resBetriebsmittel->ort_kurzbz);
	$OrtTitel=(isset($oOrt->bezeichnung) && $oOrt->bezeichnung?$oOrt->ort_kurzbz.' '.($oOrt->bezeichnung?$oOrt->bezeichnung:'').' '.$OrtBezeichnung.' '.($oOrt->telefonklappe?'Kl.'.$oOrt->telefonklappe:''):$resBetriebsmittel->ort_kurzbz.' Kontrolle');

	$htmlstring.='<fieldset><legend title="Betriebsmittel ID '.$resBetriebsmittel->betriebsmittel_id.'">Inventar '.$resBetriebsmittel->inventarnummer.'</legend>';
	$htmlstring.='<fieldset><legend>Kopfdaten</legend>';
	$htmlstring.='<table class="liste">';
	$htmlstring.='<tr>
				<th align="right">Betriebsmitteltyp&nbsp;:&nbsp;</th>
				<td>'.$resBetriebsmittel->betriebsmitteltyp.'</td>

				<th align="right">Ort&nbsp;:&nbsp;</th>
				<td>'.$OrtBezeichnung.'</td>

				<th align="right">Organisation&nbsp;:&nbsp;</th>
				<td>'.$OrgTitel.'</td>
			</tr>';

	$htmlstring.='<tr>
				<th align="right">Bestellnr.&nbsp;:&nbsp;</th>
				<td><a href="../../addons/wawi/vilesci/bestellung.php?method=update&amp;id='.$resBetriebsmittel->bestellung_id.'">'.$resBetriebsmittel->bestellnr.'</a></td>

				<th align="right" nowrap>Bestell ID.&nbsp;:&nbsp;</th>
				<td><a href="../../addons/wawi/vilesci/bestellung.php?method=update&amp;id='.$resBetriebsmittel->bestellung_id.'">'.$resBetriebsmittel->bestellung_id.'</a></td>

				<th align="right" nowrap>Bestellpos. ID.&nbsp;:&nbsp;</th>
				';
	if ($schreib_recht && $resBetriebsmittel->bestellung_id)
	{
		$htmlstring.='<form name="sendform1" action="'. $_SERVER["PHP_SELF"].'" method="post" enctype="application/x-www-form-urlencoded">
			<td>
				<input style="display:none" name="work" value="set_position" >
				<input style="display:none" name="inventarnummer" value="'.$resBetriebsmittel->inventarnummer.'" >
				<input style="display:none" name="betriebsmittel_id" value="'.$resBetriebsmittel->betriebsmittel_id.'" >
				<input style="display:none" name="bestellung_id" value="'.$resBetriebsmittel->bestellung_id.'" >
				<input id="bestelldetail_id"   name="bestelldetail_id" size="6" maxlength="41"  value="'.$resBetriebsmittel->bestelldetail_id.'" >
					<script type="text/javascript">
							function selectItem(li) {
							   return false;
							}

							$(document).ready(function() {
								$("#bestelldetail_id").autocomplete({
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
		</form>
		';
		/*	<input onchange="setTimeout(\'document.sendform1.submit()\',1500);" id="bestelldetail_id"   name="bestelldetail_id" size="6" maxlength="41"  value="'.$resBetriebsmittel->bestelldetail_id.'" >
			$(document).ready(function() {
				  $(\'#bestelldetail_id\').autocomplete(\'inventar_autocomplete.php\', {
					minChars:1,
					matchSubset:1,matchContains:1,
					width:500,
					cacheLength:0,
					onItemSelect:selectItem,
					formatItem:formatItem,
					extraParams:{\'work\':\'wawi_bestelldetail_id\'
								,\'bestellung_id\':\''.$resBetriebsmittel->bestellung_id.'\'
						}
				  });
		  });
		 */
	}
	else
		$htmlstring.='<td>'.$resBetriebsmittel->bestelldetail_id.'</td>';

	$htmlstring.='</tr>';

	$htmlstring.='<tr>
					<th align="right">Beschreibung&nbsp;:&nbsp;</th>
					<td colspan="3">'.$resBetriebsmittel->beschreibung.'</td>
					<th align="right">Seriennummer&nbsp;:&nbsp;</th>
					<td>'.$resBetriebsmittel->seriennummer.'</td>
				</tr>';

	$htmlstring.='<tr>
					<th align="right">Lieferant&nbsp;:&nbsp;</th>
					<td colspan="3">'.$resBetriebsmittel->firmenname.'</td>
					<th align="right">Hersteller&nbsp;:&nbsp;</th>
					<td>'.$resBetriebsmittel->hersteller.'</td>
				</tr>';

	if ($info=$resBetriebsmittel->verwendung.($resBetriebsmittel->verwendung?'<br>':'').$resBetriebsmittel->anmerkung)
	{
		$htmlstring.='<tr>
				<th align="right" valign="top">Verwendung&nbsp;:&nbsp;</th>
				<td colspan="5">'.$info.'</td>
			</tr>';
	}

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
						$htmlstring.='<option '.($betriebsmittelstatus_kurzbz_select==$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz?' selected="selected" ':'').' value="'.$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz.'">'.($resultBetriebsmittelstatus[$i]->beschreibung=='NULL' || empty($resultBetriebsmittelstatus[$i]->beschreibung)?$resultBetriebsmittelstatus[$i]->betriebsmittelstatus_kurzbz:$resultBetriebsmittelstatus[$i]->beschreibung).'&nbsp;</option>';
				}
		$htmlstring.='</select>';
	}
	$htmlstring.='</td>
	</form>';
	$htmlstring.='<th align="right">AfA Ende&nbsp;:&nbsp;</th>
				<td>'.$datum_obj->formatDatum($resBetriebsmittel->betriebsmittelstatus_datum_afa,'d.m.Y').'</td>

				<th align="right">Leasing bis&nbsp;:&nbsp;</th>
				<td>'.$datum_obj->formatDatum($resBetriebsmittel->leasing_bis,'d.m.Y').'</td>
			</tr>';

	$htmlstring.='<tr><td colspan="6" id="list">&nbsp;</td></tr>';

	// Inventardaten Benutzer - Anlage und Aenderung
	$htmlstring.='<tr><td colspan="6"><table><tr><td>&nbsp;</td><tr>';
	$oUpdateBenutzer = new benutzer($resBetriebsmittel->insertvon);
	$htmlstring.='
				<td align="right">Anlage&nbsp;:&nbsp;</td>
				<td><a href="mailto:'.$oUpdateBenutzer->uid.'@'.DOMAIN.'?subject=Betriebsmittel - Inventar '.$resBetriebsmittel->inventarnummer.'">'.(isset($oUpdateBenutzer->person_id)?(isset($oUpdateBenutzer->anrede) && !empty($oUpdateBenutzer->anrede)?$oUpdateBenutzer->anrede.' ':'').
					(isset($oUpdateBenutzer->titelpre) && !empty($oUpdateBenutzer->titelpre)?$oUpdateBenutzer->titelpre.' ':'').
					$oUpdateBenutzer->vorname.' '.$oUpdateBenutzer->nachname.'</a>':$resBetriebsmittel->insertvon).'&nbsp;'.$datum_obj->formatDatum($resBetriebsmittel->insertamum,'d.m.Y H:i:s').'&nbsp;
				</td>
				';
	$oUpdateBenutzer = new benutzer($resBetriebsmittel->updatevon);
	$htmlstring.='
				<td align="right">letzte &Auml;nderung&nbsp;:&nbsp;</td>
				<td><a href="mailto:'.$oUpdateBenutzer->uid.'@'.DOMAIN.'?subject=Betriebsmittel - Inventar '.$resBetriebsmittel->inventarnummer.'">'.(isset($oUpdateBenutzer->person_id)?(isset($oUpdateBenutzer->anrede) && !empty($oUpdateBenutzer->anrede)?$oUpdateBenutzer->anrede.' ':'').
					(isset($oUpdateBenutzer->titelpre) && !empty($oUpdateBenutzer->titelpre)?$oUpdateBenutzer->titelpre.' ':'').
					$oUpdateBenutzer->vorname.' '.$oUpdateBenutzer->nachname.'</a>':$resBetriebsmittel->updatevon).'&nbsp;'.$datum_obj->formatDatum($resBetriebsmittel->updateamum,'d.m.Y H:i:s').'&nbsp;
				</td>
				';
	$htmlstring.='</tr></table></td></tr>';

	$htmlstring.='<tr>';
	$htmlstring.='</table>';
	$htmlstring.='</fieldset>';

	$htmlstring.='<fieldset><legend>History</legend>';

	// Betriebsmittel STATUS - History
	$oBetriebsmittel_betriebsmittelstatus = new betriebsmittel_betriebsmittelstatus();
	$oBetriebsmittel_betriebsmittelstatus->result=array();
	$oBetriebsmittel_betriebsmittelstatus->debug=$debug;
	$oBetriebsmittel_betriebsmittelstatus->errormsg='';
	if (!$oBetriebsmittel_betriebsmittelstatus->load_betriebsmittel_id($resBetriebsmittel->betriebsmittel_id))
		$htmlstring.='<br />'.$oBetriebsmittel_betriebsmittelstatus->errormsg;

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
							<td>'.$row->betriebsmittelstatus_kurzbz.'</td>
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
		}
	}
	$htmlstring.='</table>';

	// Betriebsmittel Personen
	$oBetriebsmittelperson = new betriebsmittelperson();
	$oBetriebsmittelperson->result=array();
	$oBetriebsmittelperson->debug=$debug;
	$oBetriebsmittelperson->errormsg='';
	if (!$oBetriebsmittelperson->getbetriebsmittelpersonen($resBetriebsmittel->betriebsmittel_id))
		  $htmlstring.='<br />'.$oBetriebsmittelperson->errormsg;

	if (is_array($oBetriebsmittelperson->result) && count($oBetriebsmittelperson->result)>0)
	{
		$htmlstring.='<fieldset><legend>Ausgabehistorie</legend>';
		asort($oBetriebsmittelperson->result);
		$htmlstring.='<table>';
			$htmlstring.='<tr>
						<thead>
							<td>Person</td>
							<td>ab Datum</td>
							<td>Retour am</td>
							<td colspan="2">Anlage</td>
							<td colspan="2">&Auml;nderung</td>
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
		$htmlstring.='</fieldset>';
	}
	$htmlstring.='</fieldset>';
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" >&nbsp;zur&uuml;ck&nbsp;</a></div />';
	return 	$htmlstring;
}
?>
