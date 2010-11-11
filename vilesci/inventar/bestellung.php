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
	require_once($path.'include/studiengang.class.php');
	require_once($path.'include/mitarbeiter.class.php');
 	require_once($path.'include/wawi.class.php');
  	require_once($path.'include/betriebsmittel.class.php');
  	
	if (!$uid=get_uid())
		die('Keine Useranmeldedaten - UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
// ------------------------------------------------------------------------------------------
// Variable Initialisieren
// ------------------------------------------------------------------------------------------
	$errormsg=array();

	//------------ Berechtigungen
	$berechtigung_kurzbz='wawi/inventar:begrenzt';
	$recht=false;
	$schreib_recht=false;
	$schreib_recht_administration=2; // Admin wert fuer set schreib_recht	

// ------------------------------------------------------------------------------------------
// Parameter uebernehmen
// ------------------------------------------------------------------------------------------
  	$jahr_monat=trim(isset($_REQUEST['jahr_monat']) ? $_REQUEST['jahr_monat']:'');
  	$jahr_monat=trim(isset($_REQUEST['jahr']) ? $_REQUEST['jahr']:$jahr_monat);
	$firma_id=trim(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:'');
	$bestellung_id=trim(isset($_REQUEST['bestellung_id'])?$_REQUEST['bestellung_id']:'');
	$bestellung_id=trim(isset($_REQUEST['purchid'])?$_REQUEST['purchid']:$bestellung_id);
	$bestelldetail_id=trim(isset($_REQUEST['bestelldetail_id'])?$_REQUEST['bestelldetail_id']:'');
	$bestellnr=trim(isset($_REQUEST['bestellnr']) ? $_REQUEST['bestellnr']:'');
	$bestellnr=trim(isset($_REQUEST['purchnr'])?$_REQUEST['purchnr']:$bestellnr);
	$titel=trim(isset($_REQUEST['titel'])?$_REQUEST['titel']:'');
	$besteller=trim(isset($_REQUEST['besteller']) ? $_REQUEST['besteller']:'');
  	$kostenstelle_id=trim(isset($_REQUEST['kostenstelle_id']) ? $_REQUEST['kostenstelle_id']:'');
  	$konto_id=trim(isset($_REQUEST['konto_id'])?$_REQUEST['konto_id']:(isset($_REQUEST['konto'])?$_REQUEST['konto']:''));
	$kontonr=trim(isset($_REQUEST['kontonr'])?$_REQUEST['kontonr']:'');
  	$studiengang_id=trim(isset($_REQUEST['studiengang_id']) ? $_REQUEST['studiengang_id']:'');
	$pos_wert=trim(isset($_REQUEST['pos_wert'])?$_REQUEST['pos_wert']:0);
	if (!is_numeric($pos_wert))
		$pos_wert=0;
  	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);

  	$extend_search=trim(isset($_REQUEST['extend_search']) ?$_REQUEST['extend_search']:'false');
	// wurde im Erweitertenbereich etwas eingegeben - diesen auf alle Faelle anzeigen
	$check=$firma_id.$besteller.$titel.$kostenstelle_id.$konto_id.$studiengang_id;
	$extend_search=($check?'true':$extend_search);

// ------------------------------------------------------------------------------------------
// Berechtigung
// ------------------------------------------------------------------------------------------
	$oBenutzerberechtigung = new benutzerberechtigung();
	// read Berechtigung
	if (!$oBenutzerberechtigung->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
	$recht=false;
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'s'))
		$recht=true;
	if (!$recht)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	// Pruefen ob Schreibrechte (Anzeigen der Aenderungsmoeglichkeit)
	$schreib_recht=false;	
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null, 'suid'))
		$schreib_recht=true;

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------
	if (!$oWAWI = new wawi())
	   	die('Fehler beim Verbinden mit der Datenbank '.($debug?$oWAWI->errormsg.' *** File:='.__FILE__.' Line:='.__LINE__:''));
	$oWAWI->debug=$debug;
	$oWAWI->result=array();
	$oWAWI->errormsg='';
		
// ------------------------------------------------------------------------------------------
// HTML Output
// ------------------------------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>WAWI Bestellung - Suche</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="<?php echo $path;?>skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/jquery.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/tablesort/table.css" type="text/css">
		<script src="<?php echo $path;?>include/js/tablesort/table.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.min.js" type="text/javascript"></script>
	</head>
	<body>
	<h1 title="Anwender:<?php echo $uid ?>">&nbsp;WAWI Bestellung - Suche&nbsp;</h1>
	<form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
	<div>
		<table class="navbar">
			<tr>
			<!-- Bestellnr z.B. INFxxxx -->
			 <td><label for="bestellnr">Bestellnr.</label>&nbsp; <input id="bestellnr" name="bestellnr" onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1300);}" size="10" maxlength="30" type="Text" value="<?php echo $bestellnr; ?>" >&nbsp;
				<script type="text/javascript" language="JavaScript1.2">
					function formatItem(row) 
					{
					    return row[0] + " <i>" + row[1] + "</i> ";
					}
					$(document).ready(function() 
					{
						  $('#bestellnr').autocomplete('inventar_autocomplete.php', 
						  {
							minChars:4,
							scroll: true, 
					        scrollHeight: 300, 
							width:500,
							formatItem:formatItem,
							extraParams:{'work':'wawi_bestellnr'}
						  });
					  });
				</script>
		</td>
		<!-- Bestell ID Eindeutigenummer -->
		<td><label for="bestellung_id">ID</label>&nbsp; <input id="bestellung_id" name="bestellung_id" maxlength="30" type="Text" value="<?php echo $bestellung_id; ?>" >&nbsp;
		<script type="text/javascript" language="JavaScript1.2">
			function formatItem(row) 
			{
			    return row[0] + " <i>" + row[1] + "</i> ";
			}
			$(document).ready(function() 
			{
				  $('#bestellung_id').autocomplete('inventar_autocomplete.php', 
				  {
					minChars:4,
					scroll: true, 
			        scrollHeight: 300, 
					width:500,
					formatItem:formatItem,
					extraParams:{'work':'wawi_bestellung_id'}
				  });
		  });
		</script>
		</td>
		<!-- Lieferant - Vendor -->
		<td><label for="firma_id">Lieferant</label>&nbsp; <input id="firma_id" name="firma_id" size="10" maxlength="40" value="<?php echo $firma_id; ?>" >&nbsp;
		<script type="text/javascript" language="JavaScript1.2">
			function formatItem(row) 
			{
			    return row[0] + " <i>" + row[1] + "</i> ";
			}
			$(document).ready(function() 
			{
				  $('#firma_id').autocomplete('inventar_autocomplete.php', 
				  {
					minChars:4,
					scroll: true, 
			        scrollHeight: 300, 
					width:500,
					formatItem:formatItem,
					extraParams:{'work':'wawi_firma_search'}
				  });
		  });
		</script>
		</td>
		<!-- Bestell-Jahr/Monat -->
		<td><label for="jahr_monat">Datum</label>&nbsp; <select id="jahr_monat" name="jahr_monat">
					<?php
					$jahr_monat_select=(!isset($_REQUEST['jahr_monat'])?date('Y-m'):$jahr_monat);
					$tmpJahr=(int)date("Y",mktime(0, 0, 0, 1, 1, date("Y")-12));
					for ($i=0;$i<12;$i++)
					{
						$tmpJahr=$tmpJahr + 1;
						$jjjjmm=$tmpJahr.'-00';
						echo '<option '.($jahr_monat_select==$tmpJahr?'  selected="selected" ':'').' value="'.$tmpJahr.'" >--&nbsp;'.$tmpJahr.'&nbsp;--</option>';
						for ($ii=1;$ii<=12;$ii++)
						{
							$jjjjm=$tmpJahr.'-'.$ii;
							$jjjjmm=$tmpJahr.'-'.($ii<10?'0'."$ii":$ii);
							echo '<option '.($jahr_monat_select==$jjjjm || $jahr_monat_select==$jjjjmm?' selected="selected" ':'').' value="'.$jjjjmm.'">&nbsp;'.$jjjjmm.'&nbsp;</option>';
							if ($tmpJahr==date("Y") && $ii==date("m"))
								break;
						}
					}
					?>
				<option <?php echo ($jahr_monat_select=='-' || empty($jahr_monat_select)  ?'  selected="selected" ':''); ?>   value="">&nbsp;-&nbsp;</option></select>
		&nbsp;</td>
		<!-- Positionswert je VE Wert groesser -->
		<td><label for="pos_wert">Pos.Wert gr.</label>&nbsp;<input id="pos_wert" name="pos_wert" size="5" maxlength="10" type="Text" value="<?php echo $pos_wert; ?>" >&nbsp;</td>
		<!-- Buttom suche -->
		<td class="ac_submit">&nbsp;<a href="javascript:document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
		</tr>
		</table>
	</div>
	<div id="ext_search" style="display:<?php echo ($extend_search && $extend_search!='false'?'block':'none'); ?>;">
	<table class="navbar">
		<tr> 
		<td><label for="titel">Bezeichnung</label>&nbsp;<input id="titel" name="titel" size="26" maxlength="60" type="Text" value="<?php echo $titel; ?>" >&nbsp;</td>
		<td><label for="besteller">Besteller</label>&nbsp;<input id="besteller" name="besteller" size="10" maxlength="60" type="Text" value="<?php echo $besteller; ?>" >&nbsp;</td> <td><label for="kostenstelle_id">Kostenstelle ID</label>&nbsp;<input id="kostenstelle_id" name="kostenstelle_id" size="8" maxlength="10" type="Text" value="<?php echo $kostenstelle_id; ?>" >&nbsp;
		<script type="text/javascript" language="JavaScript1.2">
			function formatItem(row) 
			{
			    return row[0] + " <i>" + row[1] + "</i> ";
			}
			$(document).ready(function() 
			{
				  $('#kostenstelle_id').autocomplete('inventar_autocomplete.php', 
				  {
					minChars:4,
					scroll: true, 
			      	scrollHeight: 200, 
					width:500,
					formatItem:formatItem,
					extraParams:{'work':'wawi_kostenstelle_search'}
				  });
		  });
		</script>
		</td> 
			<td><label for="konto_id">Konto</label>&nbsp;<input id="konto_id" name="konto_id" size="4" maxlength="10" type="Text" value="<?php echo $konto_id; ?>" >&nbsp;
			<script type="text/javascript" language="JavaScript1.2">
				function formatItem(row) 
				{
				    return row[0] + " <i>" + row[1] + "</i> ";
				}
				$(document).ready(function() 
				{
					  $('#konto_id').autocomplete('inventar_autocomplete.php', 
					  {
						minChars:2,
						scroll: true, 
					    scrollHeight: 200, 
						width:500,
						formatItem:formatItem,
						extraParams:{'work':'wawi_konto_search'}
					  });
			  });
			</script>
			</td> 
			<td><label for="studiengang_id">Stg.Kz</label>&nbsp;<input id="studiengang_id" name="studiengang_id" size="5" maxlength="10" type="Text" value="<?php echo $studiengang_id; ?>" >&nbsp;
			<script type="text/javascript" language="JavaScript1.2">
				function formatItem(row) 
				{
				    return row[0] + " <i>" + row[1] + "</i> ";
				}
				$(document).ready(function() 
				{
					  $('#studiengang_id').autocomplete('inventar_autocomplete.php',
					  {
						minChars:1,
						scroll: true, 
					    scrollHeight: 200, 
						width:500,
						formatItem:formatItem,
						extraParams:{'work':'wawi_studiengang_search'
									,'kostenstelle_id':$("#kostenstelle_id").val() }
					  });
			  });
			</script>
		</td>
	</tr>
	</table>
	</div>
	<!-- erweiterte SUCHE EIN -->
	<div>
		<div id="extend_search_on"><div style="cursor: pointer;">
			<table class="navbar">
				<tr>
				 <td><img src="../../skin/images/right.png" alt="anzeigen - show" />Erweiterte Suche anzeigen / ausblenden <input style="display:none;" type="text" id="extend_search" name="extend_search" value="<?php echo $extend_search;?>"></td>
				</tr>
			</table>
		</div>
	</div>
	<script type="text/javascript" language="JavaScript1.2">
	   $(document).ready(function()
	   {            // Pr�ft, ob das Dokument geladen ist
	   	$("div#extend_search_on").click(function(event)
		{  // Bei Klick auf div#
	      if ( $("#extend_search").val() != 'true') 
		  {
	         $("div#ext_search").show("slow");         // div# langsam �ffnen
	         $("#extend_search").val('true')
	      }
		  else
		  {
	         $("div#ext_search").hide("slow");         // div# langsam verbergen
	         $("#extend_search").val('false')
	      }
	   });
	});
	</script>
	</div>
	</form>
	<hr>
	<?php
	
	// ------------------------------------------------------------------------------------------
	//	Datenlesen
	// ------------------------------------------------------------------------------------------
		// wenn eine kpl. Bestellnummer eingegeben wurde sind Selectwerte zu leeren sonst wird nichts gefunden
		if (strlen($bestellnr)>8 || strlen($bestellung_id)>4)
		{
			  $jahr_monat='';
			  $pos_wert='';
		}
		if ($pos_wert=='0' || !is_numeric($pos_wert))
			$pos_wert='';
		if ($jahr_monat=='0')
			 $jahr_monat='';
		$check=$firma_id.$jahr_monat.$bestellung_id.$bestellnr.$besteller.$titel.$kostenstelle_id.$konto_id.$studiengang_id.$pos_wert;
		if ($check!='' && !$oWAWI->bestellung($firma_id,$jahr_monat,$bestellung_id,$bestellnr,$besteller,$titel,$kostenstelle_id,$konto_id,$studiengang_id,$pos_wert))
			$errormsg[]=$oWAWI->errormsg;
		
		// Pruefen ob nur EINE Bestellung gefunden wurde : ja - keine Liste - die Positionen dazu anzeigen
		if (is_array($oWAWI->result) && count($oWAWI->result)==1)
		{
			if (empty($bestellung_id) && isset($oWAWI->result[0]->bestellung_id) )
				$bestellung_id=$oWAWI->result[0]->bestellung_id;
			if (empty($bestellnr) && isset($oWAWI->result[0]->bestellnr) )
				$bestellnr=$oWAWI->result[0]->bestellnr;
			$result_old = $oWAWI->result;
			$oWAWI->result=array();
			$oWAWI->errormsg='';

			if (!empty($check) && !$oWAWI->bestellpositionen($bestellung_id,$bestellnr))
				$errormsg[]=$oWAWI->errormsg;
			// Ausgabe Bestelldetailanzeige
			if(count($oWAWI->result)==0)
			{
				//wenn keine Bestellpositionen vorhanden sind, dann nur die Uebersichtsdaten anzeigen
				echo output_bestellposition($result_old,$schreib_recht,$debug);
			}
			else
				echo output_bestellposition($oWAWI->result,$schreib_recht,$debug);
		}
		else if (is_array($oWAWI->result) && count($oWAWI->result)>1)
		{
			// Ausgabe Bestellungen in Listenform
			echo output_bestellung($oWAWI->result,$schreib_recht,$debug);
		}
		else
		{
			if (!empty($check) )
				$errormsg[]='keine Daten gefunden';
			else
				$errormsg[]='Auswahl fehlt';
		}
		// Meldungen ausgeben
		if (is_array($errormsg) && count($errormsg)>0)
			  echo '<font class="error">'. implode("<br />",$errormsg).'</font>';
		else if (!is_array($errormsg))
			  echo '<font class="error"><br />'.$errormsg.'</font>';
	?>
	</body>
</html>
<?php
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------
// Ausgabe der Bestellungen in Listenform
function output_bestellung($resultBestellung=null,$schreib_recht=false,$debug=false)
{
	// Initialisierung
	$htmlstring='';
	
	// Plausib - Pruefung
	if (is_null($resultBestellung) || !is_array($resultBestellung) || count($resultBestellung)<1)
		return $htmlstring;
	
	// Classen
	if (!$oWAWI = new wawi())
	   	die($oWAWI->errormsg . ($debug?' *** File:='.__FILE__.' Line:='.__LINE__:'') );
	$oWAWI->debug=$debug;
	$oWAWI->errormsg='';
	$oWAWI->result=array();
	
	if (!$oBetriebsmittel = new betriebsmittel())
	   	die($oBetriebsmittel->errormsg.($debug?' *** File:='.__FILE__.' Line:='.__LINE__:'') );
	$oBetriebsmittel->debug=$debug;
	$oBetriebsmittel->errormsg='';
	$oBetriebsmittel->result=array();
	
	// HTML - Outputstring
	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultBestellung) && count($resultBestellung)>1)
		$htmlstring.='<tr><th colspan="10">Bitte eine Bestellnummer aus den '.count($resultBestellung).' gefundenen ausw&auml;hlen</th></tr>';
	$htmlstring.='<tr class="liste">
				<th class="table-sortable:default">Bestellnr.</th>
				<th class="table-sortable:default">ID</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default">Lieferant</th>
				<th class="table-sortable:default">Nr.</th>
				<th class="table-sortable:default">Status</th>
				<th class="table-sortable:default">Inventar</th>
			</tr>
			</thead>
			';
	// Listenausgabe der Bestellungen
	for ($pos=0;$pos<count($resultBestellung);$pos++)
	{

		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';

		$status='';
		if (!empty($resultBestellung[$pos]->geliefert))
			$status='<img src="../../skin/images/bullet_green.png" alt="" title="Lieferung am '.$resultBestellung[$pos]->geliefert.'" >&nbsp;Lieferung&nbsp;'.$resultBestellung[$pos]->geliefert;
		else if (!empty($resultBestellung[$pos]->freigb_kst))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe KST am '.$resultBestellung[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;&nbsp;'.$resultBestellung[$pos]->freigb_kst;
		else if (!empty($resultBestellung[$pos]->freigb_stg))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe STG am '.$resultBestellung[$pos]->freigb_kst.'" &nbsp;Freigabe&nbsp;&nbsp;'.$resultBestellung[$pos]->freigb_stg;
		else if (!empty($resultBestellung[$pos]->freigb_gst))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe GST am '.$resultBestellung[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;'.$resultBestellung[$pos]->freigb_gst;
		else if (!empty($resultBestellung[$pos]->freigb_rek))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe REK am '.$resultBestellung[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;&nbsp;'.$resultBestellung[$pos]->freigb_rek;
		else if (!empty($resultBestellung[$pos]->freigabe_gmb))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe GMB am '.$resultBestellung[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;&nbsp;'.$resultBestellung[$pos]->freigabe_gmb;
		else if (!empty($resultBestellung[$pos]->bestellung))
			$status='<img src="../../skin/images/bullet_red.png" alt="" title="Bestellung am '.$resultBestellung[$pos]->bestellung.'" >&nbsp;Bestellung&nbsp;'.$resultBestellung[$pos]->bestellung;
		else
			$status='<img src="../../skin/images/bullet_black.png" alt="" title="Anlage am '.$resultBestellung[$pos]->erstellung.'" >&nbsp;Anlage&nbsp;&nbsp;&nbsp;'.$resultBestellung[$pos]->erstellung;

		$htmlstring.='<!-- Bestellungen Auflisten -->
	    	<tr class="'.$classe.'"  style="font-size:smaller;">
				<td><a title="Detail Bestellnummer '.$resultBestellung[$pos]->bestellnr.'" href="'.$_SERVER["PHP_SELF"].'?bestellung_id=&amp;bestellnr='.urlencode($resultBestellung[$pos]->bestellnr).'&amp;jahr_monat=">'.$resultBestellung[$pos]->bestellnr.'</a></td>
				<td align="right"><a title="Detail Bestell-ID '.$resultBestellung[$pos]->bestellung_id.'" href="'.$_SERVER["PHP_SELF"].'?bestellung_id='.$resultBestellung[$pos]->bestellung_id.'&amp;bestellnr=&amp;jahr_monat=">'.$resultBestellung[$pos]->bestellung_id.'</a></td>

				<td>'.StringCut($resultBestellung[$pos]->titel,25) .'</td>

				<!-- Firmen -->
				<td>'.StringCut($resultBestellung[$pos]->firmenname,25).'</td>
				<td align="right"><a href="firma_detail.php?firma_id='.$resultBestellung[$pos]->firma_id.'">'.$resultBestellung[$pos]->firma_id.'</a></td>

				<td>&nbsp;'.$status.'&nbsp;</td>';

			$oBetriebsmittel->result=array();
			if (!isset($resultBestellung[$pos]->geliefert) || empty($resultBestellung[$pos]->geliefert))
				$htmlstring.='<td align="right">&nbsp;<a title="Detail Bestell ID '.$resultBestellung[$pos]->bestellung_id.'" href="'.$_SERVER["PHP_SELF"].'?bestellung_id='.$resultBestellung[$pos]->bestellung_id.'&amp;bestelldetail_id=&amp;bestellnr=&amp;betriebsmittelstatus_kurzbz=">Status&nbsp;<img src="../../skin/images/information.png" alt="Status" ></a>&nbsp;</td>';
			elseif ($oBetriebsmittel->load_bestellung_id($resultBestellung[$pos]->bestellung_id,null))
				$htmlstring.='<td align="right">&nbsp;<a title="Inventar Anzeige Bestell-ID '.$resultBestellung[$pos]->bestellung_id.'" href="inventar.php?bestellung_id='.$resultBestellung[$pos]->bestellung_id.'&amp;bestelldetail_id=&amp;bestellnr=&amp;betriebsmittelstatus_kurzbz=">anzeigen&nbsp;<img src="../../skin/images/application_go.png" alt="Bestellung '.$resultBestellung[$pos]->bestellnr.'" ></a>&nbsp;</td>';
			elseif ($schreib_recht)
				$htmlstring.='<td align="right">&nbsp;<a title="Inventar Neuanlage Bestell-ID '.$resultBestellung[$pos]->bestellung_id.'" href="inventar_pflege.php?bestellung_id='.$resultBestellung[$pos]->bestellung_id.'&amp;bestelldetail_id=&amp;bestellnr=&amp;betriebsmittelstatus_kurzbz=">neuanlage&nbsp;<img src="../../skin/images/application_form_edit.png" alt="Bestellung '.$resultBestellung[$pos]->bestellnr.'" ></a>&nbsp;</td>';
			else
				$htmlstring.='<td align="right">&nbsp;<a title="Detail Bestell-ID '.$resultBestellung[$pos]->bestellung_id.'" href="'.$_SERVER["PHP_SELF"].'?bestellung_id='.$resultBestellung[$pos]->bestellung_id.'&amp;bestelldetail_id=&amp;bestellnr=&amp;betriebsmittelstatus_kurzbz=">Status&nbsp;<img src="../../skin/images/information.png" alt="Status" ></a>&nbsp;</td>';
		$htmlstring.='</tr>';
	}
	$htmlstring.='</table>';
	return $htmlstring;
}
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------
// Ausgabe der Bestellpositionen in Listenform bei einer Bestellung
function output_bestellposition($resultBestellungPos=null,$schreib_recht=false,$debug=false)
{
	// Initialisierung
	$htmlstring='';
	// Plausib - Pruefung
	if (is_null($resultBestellungPos) || !is_array($resultBestellungPos) || count($resultBestellungPos)<1)
		return $htmlstring;

	// Classen
	if (!$oWAWI = new wawi())
	   	die($oWAWI->errormsg . ($debug?' *** File:='.__FILE__.' Line:='.__LINE__:'') );
	$oWAWI->debug=$debug;
	$oWAWI->errormsg='';
	$oWAWI->result=array();

	if (!$oBetriebsmittel = new betriebsmittel())
	   	die($oBetriebsmittel->errormsg . ($debug?' *** File:='.__FILE__.' Line:='.__LINE__:'') );
	$oBetriebsmittel->debug=$debug;
	$oBetriebsmittel->errormsg='';
	$oBetriebsmittel->result=array();

	// Wawi Besteller - Namen ermitteln
	if ($oWAWI->benutzer(null,$resultBestellungPos[0]->besteller))
		$besteller=$oWAWI->result[0]->anrede.' '.$oWAWI->result[0]->vname.' '.$oWAWI->result[0]->nname;
	else
	    $besteller=$resultBestellungPos[0]->besteller;
	// Wawi Kontaktperson - Namen zusammen stellen
	$kontaktperson=$resultBestellungPos[0]->kontaktperson_anrede.'&nbsp;'.$resultBestellungPos[0]->kontaktperson_vname.'&nbsp;'.$resultBestellungPos[0]->kontaktperson_nname;
	// Bestellstatus ermitteln
	if ($resultBestellungPos[0]->freigb_kst != '')
		$freigabe='&nbsp;Kst '.$resultBestellungPos[0]->freigb_kst;
	elseif ($resultBestellungPos[0]->freigb_stg != '')
		$freigabe='&nbsp;Stg '.$resultBestellungPos[0]->freigb_stg;
	elseif ($resultBestellungPos[0]->freigb_gst != '')
		$freigabe='&nbsp;Gst '.$resultBestellungPos[0]->freigb_gst;
	elseif ($resultBestellungPos[0]->freigb_rek != '')
		$freigabe='&nbsp;Rek '.$resultBestellungPos[0]->freigb_rek;
	elseif ($resultBestellungPos[0]->freigabe_gmb != '')
		$freigabe='&nbsp;Gmb '.$resultBestellungPos[0]->freigabe_gmb;
	else
		$freigabe='';
	// Lieferstatus ermitteln
	$status='';
	if (!empty($resultBestellungPos[0]->geliefert))
		$status='<img src="../../skin/images/bullet_green.png" alt="" title="Geliefert am '.$resultBestellungPos[0]->geliefert.'" >&nbsp;Geliefert '.$resultBestellungPos[0]->geliefert;
	else if (!empty($resultBestellungPos[0]->freigb_kst))
		$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe KST am '.$resultBestellungPos[0]->freigb_kst.'" >&nbsp;Freigabe '.$resultBestellungPos[0]->freigb_kst;
	else if (!empty($resultBestellungPos[0]->freigb_stg))
		$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe STG am '.$resultBestellungPos[0]->freigb_kst.'" >&nbsp;Freigabe '.$resultBestellungPos[0]->freigb_stg;
	else if (!empty($resultBestellungPos[0]->freigb_gst))
		$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe GST am '.$resultBestellungPos[0]->freigb_kst.'" >&nbsp;Freigabe '.$resultBestellungPos[0]->freigb_gst;
	else if (!empty($resultBestellungPos[0]->freigb_rek))
		$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe REK am '.$resultBestellungPos[0]->freigb_kst.'" >&nbsp;Freigabe '.$resultBestellungPos[0]->freigb_rek;
	else if (!empty($resultBestellungPos[0]->freigabe_gmb))
		$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe GMB am '.$resultBestellungPos[0]->freigb_kst.'" >&nbsp;Freigabe '.$resultBestellungPos[0]->freigabe_gmb;
	else if (!empty($resultBestellungPos[0]->bestellung))
		$status='<img src="../../skin/images/bullet_red.png" alt="" title="Bestellt am '.$resultBestellungPos[0]->bestellung.'" >&nbsp;Bestellt '.$resultBestellungPos[0]->bestellung;
	else
		$status='<img src="../../skin/images/bullet_black.png" alt="" title="Erstellt am '.$resultBestellungPos[0]->erstellung.'" >&nbsp;Anlage '.$resultBestellungPos[0]->erstellung;

		
	// Wawi - Bestelldetail - Ausgabe START
	$htmlstring.='<fieldset><legend>Bestelldetail '.$resultBestellungPos[0]->bestellnr.'</legend>';
		$htmlstring.='<fieldset><legend>Besteller </legend>';
		$htmlstring.='<table class="liste">
					<tr class="liste1">
						<th align="right">Bestellnr.</th>
						<td width="90%">'.$resultBestellungPos[0]->bestellnr.'</td>
					</tr>
					<tr class="liste1">
						<th align="right">Bestell ID</th>
						<td>'.$resultBestellungPos[0]->bestellung_id.'</a></td>
					</tr>

					<tr class="liste1">
						<th align="right">Bestelltitel</th>
						<td width="90%">'.$resultBestellungPos[0]->titel.'</td>
					</tr>

					<tr class="liste1">
						<th align="right">Firmenname</th>
						<td><a href="firma_detail.php?firma_id='.$resultBestellungPos[0]->firma_id.'">'.$resultBestellungPos[0]->firma_id.'&nbsp;'.$resultBestellungPos[0]->firmenname.'</a></td>
					</tr>
					<tr class="liste1">
						<th align="right">Studiengangkz.</th>
						<td><a href="studiengang_detail.php?studiengang_id='.(!empty($resultBestellungPos[0]->studiengang_id)?$resultBestellungPos[0]->studiengang_id:(isset($resultBestellungPos[0]->studiengang_kostenstelle_studiengang_id)?$resultBestellungPos[0]->studiengang_kostenstelle_studiengang_id:'')).'">'.(!empty($resultBestellungPos[0]->studiengang_id)?$resultBestellungPos[0]->studiengang_id:(isset($resultBestellungPos[0]->studiengang_kostenstelle_studiengang_id)?$resultBestellungPos[0]->studiengang_kostenstelle_studiengang_id:'')).' '.(isset($resultBestellungPos[0]->studiengang_bezeichnung) && !empty($resultBestellungPos[0]->studiengang_bezeichnung)?$resultBestellungPos[0]->studiengang_bezeichnung:(isset($resultBestellungPos[0]->studiengang_kostenstelle_bezeichnung)?$resultBestellungPos[0]->studiengang_kostenstelle_bezeichnung:'')).'</a></td>
					</tr>
					<tr class="liste1">
						<th align="right">Kostenstelle</th>
						<td><a href="kostenstelle_detail.php?kostenstelle_id='.$resultBestellungPos[0]->kostenstelle_id.'">'.$resultBestellungPos[0]->kostenstelle_id.' '.(isset($resultBestellungPos[0]->kostenstelle_bezeichnung)?$resultBestellungPos[0]->kostenstelle_bezeichnung:'').'</a></td>
					</tr>
					<tr class="liste1">
						<th align="right">Konto</th>
						<td><a href="konto_detail.php?konto_id='.$resultBestellungPos[0]->konto_id.'">'.$resultBestellungPos[0]->konto_id.' '.(isset($resultBestellungPos[0]->konto_beschreibung)?$resultBestellungPos[0]->konto_beschreibung:'').'</a></td>
					</tr>
					<tr class="liste1">
						<th align="right">Kontaktperson</th>
						<td title="user '.$resultBestellungPos[0]->kontaktperson_username.'">'.($resultBestellungPos[0]->kontaktperson_email?'<a title="'.$resultBestellungPos[0]->kontaktperson_email.'" href="mailto:'.$resultBestellungPos[0]->kontaktperson_email.'&amp;subject=Bestellung '.urlencode($resultBestellungPos[0]->bestellnr).' '.$resultBestellungPos[0]->titel.' '.$resultBestellungPos[0]->beschreibung.'"><img src="../../skin/images/email.png" alt="email" >&nbsp;':'').$kontaktperson.'&nbsp;'.($resultBestellungPos[0]->kontaktperson_email?'</a>':'').'&nbsp;</td>
					</tr>
					<tr class="liste1">
						<th align="right">Besteller</th>
						<td>'.($resultBestellungPos[0]->besteller?'<a title="'.$resultBestellungPos[0]->besteller.'" href="mailto:'.$resultBestellungPos[0]->besteller.'&amp;subject=Bestellung '.urlencode($resultBestellungPos[0]->bestellnr).' - '.$resultBestellungPos[0]->titel.',  '.$resultBestellungPos[0]->beschreibung.'"><img src="../../skin/images/email.png" alt="email" >&nbsp;':'').$besteller.'&nbsp;'.($resultBestellungPos[0]->besteller?'</a>':'').'&nbsp;</td>
					</tr>
			</table>';
	$htmlstring.='</fieldset>';

	$htmlstring.='<fieldset><legend>Information</legend>';
			$htmlstring.='<table class="liste">
					<tr>
					<thead>
						<th>Erstellt am</th>
						<th>Gesendet am</th>
						<th>Freigabe am</th>
						<th>Liefertermin</th>
						<th>Erledigt am</th>
						<th>Status</th>
					</thead>
					</tr>
					<tr class="liste1">
						<td>'.$resultBestellungPos[0]->erstellung.'</td>
						<td>'.$resultBestellungPos[0]->bestellung.'</td>
						<td>&nbsp;'.$freigabe.'&nbsp;</td>
						<td>'.$resultBestellungPos[0]->liefertermin.'</td>
						<td>'.$resultBestellungPos[0]->geliefert.'</td>
						<td>&nbsp;'.$status.'&nbsp;</td>
					</tr>
			</table>';
	$htmlstring.='</fieldset>';

	$htmlstring.='<fieldset><legend>Positionen zu '.$resultBestellungPos[0]->titel.'</legend>';
	$htmlstring.='<table class="liste">
				<thead>
				<tr><th colspan="8">Positionen</th></tr>
				<tr>
					<th>Menge</th>
					<th>VE</th>
					<th>Beschreibung</th>
					<th>Artikel</th>

					<th>Preis/VE</th>
					<th>Ust</th>
					<th>Pos.wert</th>
					<th>Inventar</th>

				</tr>
				</thead>
			';
	$summe_netto=0;
	$summe_brutto=0;
	for ($pos=0;$pos<count($resultBestellungPos);$pos++)
	{
		if(!isset($resultBestellungPos[$pos]->summe))
			continue;
		$status='';
		if (!empty($resultBestellungPos[$pos]->geliefert))
			$status='<img src="../../skin/images/bullet_green.png" alt="" title="Geliefert am '.$resultBestellungPos[$pos]->geliefert.'" >&nbsp;Geliefert&nbsp;'.$resultBestellungPos[$pos]->geliefert;
		else if (!empty($resultBestellungPos[$pos]->freigb_kst))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe KST am '.$resultBestellungPos[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;'.$resultBestellungPos[$pos]->freigb_kst;
		else if (!empty($resultBestellungPos[$pos]->freigb_stg))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe STG am '.$resultBestellungPos[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;'.$resultBestellungPos[$pos]->freigb_stg;
		else if (!empty($resultBestellungPos[$pos]->freigb_gst))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe GST am '.$resultBestellungPos[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;'.$resultBestellungPos[$pos]->freigb_gst;
		else if (!empty($resultBestellungPos[$pos]->freigb_rek))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe REK am '.$resultBestellungPos[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;'.$resultBestellungPos[$pos]->freigb_rek;
		else if (!empty($resultBestellungPos[$pos]->freigabe_gmb))
			$status='<img src="../../skin/images/bullet_orange.png" alt="" title="Freigabe GMB am '.$resultBestellungPos[$pos]->freigb_kst.'" >&nbsp;Freigabe&nbsp;'.$resultBestellungPos[$pos]->freigabe_gmb;
		else if (!empty($resultBestellungPos[$pos]->bestellung))
			$status='<img src="../../skin/images/bullet_red.png" alt="" title="Bestellt am '.$resultBestellungPos[$pos]->bestellung.'" >&nbsp;Bestellt&nbsp;&nbsp;'.$resultBestellungPos[$pos]->bestellung;
		else
			$status='<img src="../../skin/images/bullet_black.png" alt="" title="Erstellt am '.$resultBestellungPos[$pos]->erstellung.'" >&nbsp;Anlage&nbsp;&nbsp;&nbsp;'.$resultBestellungPos[$pos]->erstellung;

		$summe_netto=$summe_netto+$resultBestellungPos[$pos]->summe;
		$brutto=$resultBestellungPos[$pos]->summe + (($resultBestellungPos[$pos]->summe/100)*$resultBestellungPos[$pos]->mwst);

		if (empty($brutto))
			$brutto=0;
		$summe_brutto=$summe_brutto+$brutto;

		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';
		$summe=0;

		// Wurde ein Position ausgewaehlt diese Markieren
		$bestelldetail_id=trim(isset($_REQUEST['bestelldetail_id'])?$_REQUEST['bestelldetail_id']:'');
		if ($resultBestellungPos[$pos]->bestelldetail_id==$bestelldetail_id)
			$htmlstring.='<tr class="'.$classe.'" style="background-color: #FAFAD2;">';
		else
			$htmlstring.='<tr class="'.$classe.'">';

			$htmlstring.='<td align="right">'.number_format($resultBestellungPos[$pos]->menge, 2).'</td>
						<td>'.$resultBestellungPos[$pos]->ve.'</td>
						<td>'.$resultBestellungPos[$pos]->beschreibung.'</td>
						<td>'.$resultBestellungPos[$pos]->artikelnr.'</td>

						<td align="right">'.number_format($resultBestellungPos[$pos]->preisve,2).'</td>

						<td align="right">'.number_format($resultBestellungPos[$pos]->mwst, 0).'%</td>
						<td align="right">'.number_format($brutto,2).'</td>
			';
			$oBetriebsmittel->result=array();
			if (!isset($resultBestellungPos[$pos]->geliefert) || empty($resultBestellungPos[$pos]->geliefert))
				$htmlstring.='<td>&nbsp;-&nbsp;</td>';
			elseif ($oBetriebsmittel->load_bestellung_id($resultBestellungPos[$pos]->bestellung_id,$resultBestellungPos[$pos]->bestelldetail_id))
				$htmlstring.='<td>&nbsp;<a title="Inventar Anzeige Bestell-ID '.$resultBestellungPos[$pos]->bestelldetail_id.'" href="inventar.php?bestellung_id='.$resultBestellungPos[$pos]->bestellung_id.'&amp;bestelldetail_id='.$resultBestellungPos[$pos]->bestelldetail_id.'&amp;bestellnr=&amp;betriebsmittelstatus_kurzbz=">anzeigen<img src="../../skin/images/right.gif" alt="Bestellung '.$resultBestellungPos[$pos]->bestellnr.'-'.$resultBestellungPos[$pos]->bestelldetail_id.'" ></a>&nbsp;</td>';
			elseif ($oBetriebsmittel->load_bestellung_id($resultBestellungPos[$pos]->bestellung_id,null))
				$htmlstring.='<td>&nbsp;<a title="Inventar Anzeige Bestell-ID '.$resultBestellungPos[$pos]->bestelldetail_id.'" href="inventar.php?bestellung_id='.$resultBestellungPos[$pos]->bestellung_id.'&amp;bestelldetail_id=&amp;bestellnr=&amp;betriebsmittelstatus_kurzbz=">anzeigen<img src="../../skin/images/right.gif" alt="Bestellung '.$resultBestellungPos[$pos]->bestellnr.'" ></a>&nbsp;</td>';
			elseif ($schreib_recht)
				$htmlstring.='<td>&nbsp;<a title="Inventar Neuanlage Bestell-ID '.$resultBestellungPos[$pos]->bestelldetail_id.'" href="inventar_pflege.php?bestellung_id='.$resultBestellungPos[$pos]->bestellung_id.'&amp;bestelldetail_id='.$resultBestellungPos[$pos]->bestelldetail_id.'&amp;betriebsmittelstatus_kurzbz=&amp;anzahl='.number_format($resultBestellungPos[$pos]->menge,0).'">neuanlage<img src="../../skin/images/right.gif" alt="Bestellung '.$resultBestellungPos[$pos]->bestellnr.'-'.$resultBestellungPos[$pos]->bestelldetail_id.'" ></a>&nbsp;</td>';
			else
				$htmlstring.='<td>&nbsp;-&nbsp;</td>';
		$htmlstring.='</tr>';
	}
	$htmlstring.='<tr>
					<td colspan="8"><hr /></td>
				</tr>';
	$htmlstring.='<tr>
					<td colspan="6" align="right" nowrap>&nbsp;Summe Netto:&nbsp;</td>
					<td align="right">'.number_format($summe_netto, 2).'</td>
				</tr>';
	$htmlstring.='<tr>
					<td colspan="6" align="right" nowrap>&nbsp;Summe Brutto:&nbsp;</td>
					<td align="right">'.number_format($summe_brutto, 2).'</td>
				</tr>';
	$htmlstring.='</table>';
	$htmlstring.='</fieldset>';
	$htmlstring.='</fieldset>';
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" />&nbsp;zur&uuml;ck</a></div />';

	return $htmlstring;
}
?>