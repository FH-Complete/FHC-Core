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
 
 #-------------------------------------------------------------------------------------------	
/* 
*	Reservierung zur Veranstaltung - Pflege
*
*		Aktionen: Anzeige, Anlage, Aenderung und Loeschen
*		Ansicht : Voll oder Popup (window.opener)	
*
*
*/
 
// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/functions.inc.php');
	
// ---------------- Datenbank-Verbindung 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	
// ---------------- Jahresplan Classe und Allg.Funktionen		
	include_once('../../../include/jahresplan.class.php');
 	include_once('jahresplan_funktionen.inc.php');
	if (!$is_wartungsberechtigt)
		die($p->t("global/keineBerechtigungFuerDieseSeite")).('<a href="javascript:history.back()">'.$p->t("global/zurueck").'</a>');

// ------------------------------------------------------------------------------------------
//	Init
// ------------------------------------------------------------------------------------------
	$error='';	
	
// ------------------------------------------------------------------------------------------
//	Request Parameter 
// ------------------------------------------------------------------------------------------
	// Parameter Veranstaltung
   	$veranstaltung_id=trim((isset($_REQUEST['veranstaltung_id']) ? $_REQUEST['veranstaltung_id']:''));
	if (empty($veranstaltung_id))
		exit($p->t("eventkalender/keineVeranstaltungsIdUebergeben").'!');

  	$openfirst=trim((isset($_REQUEST['openfirst']) ? $_REQUEST['openfirst']:''));
  	$startDatum=trim((isset($_REQUEST['startDatum']) ? $_REQUEST['startDatum']:mktime(12,0,0,date("m"),date("d"),date("y")) ));
   	$endeDatum=trim((isset($_REQUEST['endeDatum']) ? $_REQUEST['endeDatum']:mktime(13,0,0,date("m"),date("d"),date("y")) ));
	
	// Verarbeiten einer Reservierung
   	$work=trim((isset($_REQUEST['work']) ? $_REQUEST['work']:''));
   	$veranstaltung_id_zuordnen=trim((isset($_REQUEST['veranstaltung_id_zuordnen']) ? $_REQUEST['veranstaltung_id_zuordnen']:''));
   	$reservierung_id=trim((isset($_REQUEST['reservierung_id']) ? $_REQUEST['reservierung_id']:''));
   	$reservierung_key=trim((isset($_REQUEST['reservierung_key']) ? $_REQUEST['reservierung_key']:''));


// ------------------------------------------------------------------------------------------
//	Jahresplan Classe 
//		Reservierungen
// ------------------------------------------------------------------------------------------
	if ($work=='save' || $work=='del')
	{
		$Jahresplan->InitReservierung();	
		// Der Reservierung die Veranstaltungsnummer eintragen bzw. leer wenn die Zuordnung aufgehoben wird
		if (!empty($reservierung_id))
		{
			$reservierung_id=explode('|',$reservierung_id);
			if (is_array($reservierung_id))
			{
				for ($updRes=0;$updRes<count($reservierung_id);$updRes++)
				{
					$Jahresplan->veranstaltung_id=$veranstaltung_id_zuordnen;			
					$Jahresplan->reservierung_id=$reservierung_id[$updRes];
					if (!$Jahresplan->saveReservierung())
					{
						$error.=($error?'<br>':'')."Fehler Reserv.ID ".$Jahresplan->reservierung_id." ".$Jahresplan->errormsg;
					}
				}
			}
			else
			{	
				$Jahresplan->veranstaltung_id=$veranstaltung_id_zuordnen;			
				$Jahresplan->reservierung_id=$reservierung_id;				
				if (!$Jahresplan->saveReservierung())
				{
					$error.=($error?'<br>':'')."Fehler Reserv.ID ".$Jahresplan->reservierung_id." ".$Jahresplan->errormsg;
				}
			}	
		}
	}	
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $p->t("eventkalender/reservierungenZurVeranstaltungsID");?> <?php echo $veranstaltung_id.' - '.$userNAME;?> </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script language="JavaScript" type="text/javascript">
	<!--
		var openfirst='<?php echo $openfirst; ?>';
//		if (window.opener && !window.opener.closed && openfirst!='1') 
//		{
//			if (confirm("Soll die Hauptseite neu aufgebaut werden?")) 
//			{
//				window.opener.location.reload();
//			}	
//		}	

		if (window.opener && !window.opener.closed && openfirst!='1') 
		{
			if (confirm("<?php echo $p->t("eventkalender/sollDieHauptseiteNeuAufgebautWerden");?>?")) 
			{
			window.opener.location.selVeranstaltung.submit();
			}	
		}	
		
	var InfoWin;  
	function callWindows(url,nameID)
	{
		 // width=(Pixel) - erzwungene Fensterbreite 
		 // height=(Pixel) - erzwungene Fensterh&ouml;he 
		 // resizable=yes/no - Gr&ouml;&szlig;e fest oder ver&auml;nderbar 
		 // scrollbars=yes/no - fenstereigene Scrollbalken 
		 // toolbar=yes/no - fenstereigene Buttonleiste 
		 // status=yes/no - fenstereigene Statuszeile 
		 // directories=yes/no - fenstereigene Directory-Buttons (Netscape) 
		 // menubar=yes/no - fenstereigene Men&uuml;leiste 
		 // location=yes/no - fenstereigenes Eingabe-/Auswahlfeld f&uuml;r URLs 
		 
		if (InfoWin) {
			InfoWin.close();
	 	}
	       InfoWin=window.open(url,nameID,"copyhistory=no,directories=no,location=no,dependent=no,toolbar=yes,menubar=no,status=no,resizable=yes,scrollbars=yes, width=500,height=600,left=60, top=15");  
		InfoWin.focus();
		InfoWin.setTimeout("window.close()",800000);
	}
	//-->
	</script>

	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<style type="text/css">
	<!-- 
		form {display:inline;}
		.cursor_hand {cursor:pointer;vertical-align: top;white-space : nowrap;}
		.ausblenden {display:none;}
		.footer_zeile {color: silver;}
		
	
	/* Listen */
		table.reservierungen_liste {border:0px;background-color:#E5E5E5;}

		tr.header_liste_titelzeile  {background-color: #F0F0F0;text-align:center;}
		tr.header_liste_row_0  {background:#FEFFEC;vertical-align: top;}
		tr.header_liste_row_1  {background:#F7F7F7;vertical-align: top;}
	
		td.zahlen {text-align:right;}
	-->
	</style>

</head>
<body onunload="reloadOpener();">
	<h1><?php echo $p->t("eventkalender/reservierungenZurVeranstaltungsID");?> <?php echo $veranstaltung_id;?></h1>
<?php
// ------------------------------------------------------------------------------------------
// Datenlesen fuer Anzeige der
//	 Veranstaltungen mit ID Selektionsbedingung lesen
// ------------------------------------------------------------------------------------------
	$showHTML='';
	
	$Jahresplan->InitReservierung();	
	if (Date("H",$endeDatum)>2) // Plausib das mit den 2h abzug nicht die 00:00 Grenze unterschritten wird
		$Jahresplan->startDatum=$startDatum-7200;
	else
		$Jahresplan->startDatum=@mktime(0, 1, 0, date("m",$startDatum),date("d",$startDatum),date("Y",$startDatum));

	if (Date("H",$endeDatum)<22) // Plausib das mit den 2h dazu nicht die 24:00 Grenze ueberschritten wird
		$Jahresplan->endeDatum=$endeDatum+7200;
	else
		$Jahresplan->endeDatum=@mktime(23, 59, 0, date("m",$endeDatum),date("d",$endeDatum),date("Y",$endeDatum));

	$reservierungierung=array();
	if ($reservierungierung=$Jahresplan->loadReservierung())
	{
		if ((is_array($reservierungierung) || is_object($reservierungierung)) && count($reservierungierung)>0)
		{
			for ($iTmpZehler=0;$iTmpZehler<count($reservierungierung);$iTmpZehler++)
			{				
				$key='';
				$key.=$reservierungierung[$iTmpZehler]->ort_kurzbz;
				$key.=$reservierungierung[$iTmpZehler]->titel;
				$key.=$reservierungierung[$iTmpZehler]->reservierung_id;
				$reservierungierung[$iTmpZehler]->key=$reservierungierung[$iTmpZehler]->ort_kurzbz.'|'.$reservierungierung[$iTmpZehler]->titel.'|'.$reservierungierung[$iTmpZehler]->datum_anzeige;
				$reservierungierung_sort[$key][]=$reservierungierung[$iTmpZehler];
			}	
			if (sort($reservierungierung_sort))
			{
				$reservierungierung=array();
				while (list( $tmp_key, $tmp_value ) = each($reservierungierung_sort) ) 
				{
					$reservierungierung[]=$tmp_value[0];			
				}
			}	
		}
	}
	else
	{
		$reservierungierung=array();
	}	

	$showHTML.=$Jahresplan->errormsg;		
	$showHTML.='<table class="reservierungen_liste" cellpadding="1" cellspacing="1">
			<tr>
				<td>'.$p->t("eventkalender/reservierungID").'</td>
				<td>'.$p->t("lvplan/ort").'</td>
				<td>'.$p->t("global/titel").'</td>
				<td colspan="2">'.$p->t("global/datum").'</td>
				<td colspan="2">'.$p->t("eventkalender/angelegtVon").'</td>				
				<td>'.$p->t("eventkalender/veranstaltung").'</td>				
			</tr>';

	$lastkey=null;		
	$alleReservierung_id=null;		
	for ($iTmpZehler=0;$iTmpZehler<count($reservierungierung);$iTmpZehler++)
	{			
			$userNAME=$reservierungierung[$iTmpZehler]->uid;
			$pers = new benutzer($userNAME); // Lesen Person - Benutzerdaten
			if (isset($pers->nachname))
			{
				$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
				$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
				$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
				$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');	
				$reservierungierung[$iTmpZehler]->bild='';	
				if ($pers->foto)
				{
					$cURL='jahresplan_bilder.php?time='.time().'&'.(strlen($pers->foto)<800?'heximg='.$pers->foto:'userUID='.$pers->uid);
					$reservierungierung[$iTmpZehler]->bild='<img width="16" border="0" title="'.$userNAME.'" alt="Reservierung von Benutzer" src="'.$cURL.'">';
				}
			}			

		if (!is_null($lastkey) && $lastkey !=$reservierungierung[$iTmpZehler]->key)
		{	
			$showHTML.='
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'>
			<form name="selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle" target="_self" action="'. $_SERVER['PHP_SELF'] .'"  method="post" enctype="multipart/form-data">
				<td align="center"><a href="javascript:window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.work.value=\'save\';window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.submit();">'.$p->t("global/alle").'</a></td>
				<td>'. implode("</td><td>",explode('|',$lastkey)).'</td>
					<td colspan="5">
					<input class="ausblenden" name="reservierung_id" value="'.$alleReservierung_id.'">
					<input class="ausblenden" name="startDatum" value="'.$startDatum.'">
					<input class="ausblenden" name="endeDatum" value="'.$endeDatum.'">
					<input class="ausblenden" name="veranstaltung_id" value="'.$veranstaltung_id.'">
					
					<input class="ausblenden" name="veranstaltung_id_zuordnen" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="reservierung_key" value="'.$reservierungierung[$iTmpZehler]->key.'">
					<input class="ausblenden" name="work" value="nix">
					</td>
				';	
				$showHTML.='</tr>';
			$showHTML.='
			</form>
			</tr>
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'><td colspan="9"><hr></td></tr>
			';
			$alleReservierung_id=null;
		}
		
		$showHTML.='
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'>
			<form name="selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'" target="_self" action="'. $_SERVER['PHP_SELF'] .'"  method="post" enctype="multipart/form-data">
				<td>'.$reservierungierung[$iTmpZehler]->reservierung_id.'
					<input class="ausblenden" name="reservierung_id" value="'.$reservierungierung[$iTmpZehler]->reservierung_id.'">
					<input class="ausblenden" name="startDatum" value="'.$startDatum.'">
					<input class="ausblenden" name="endeDatum" value="'.$endeDatum.'">
					<input class="ausblenden" name="veranstaltung_id" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="work" value="nix">
					</td>';
				
				if ($lastkey && $lastkey==$reservierungierung[$iTmpZehler]->key)
				{	
					$showHTML.='<td colspan="3"><hr></td>';
				}
				else
				{	
				$showHTML.='	
					<td>'.$reservierungierung[$iTmpZehler]->ort_kurzbz.'</td>
					<td>'.$reservierungierung[$iTmpZehler]->titel.'</td>
					<td>'.$reservierungierung[$iTmpZehler]->datum_anzeige.'</td>
					';	
				}	
		
				$showHTML.='<td>'.$reservierungierung[$iTmpZehler]->beginn_anzeige.'-'.$reservierungierung[$iTmpZehler]->ende_anzeige.'</td>';				
				$showHTML.='<td>'.(isset($userNAME)?$userNAME:$reservierungierung[$iTmpZehler]->uid).'</td><td>'.(isset($reservierungierung[$iTmpZehler]->bild)?$reservierungierung[$iTmpZehler]->bild:'').'</td>';
				
				$showHTML.='<td class="zahlen">'.($reservierungierung[$iTmpZehler]->veranstaltung_id!=$veranstaltung_id?'<span class="cursor_hand" onclick="callWindows(\'jahresplan_detail.php?work=update&amp;veranstaltung_id='.$reservierungierung[$iTmpZehler]->veranstaltung_id.'\',\'Veranstaltung_Detail\').focus();">'.$reservierungierung[$iTmpZehler]->veranstaltung_id.'</span>':'').'</td>';
				$cTmpResScript=' onclick="window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'.work.value=\'save\';window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'.submit();" ' ;	
	
				// Checkbox Reservierung zuteilen oder aufheben
				$showHTML.='<td nowrap>';
				$showHTML.='<input '.(empty($reservierungierung[$iTmpZehler]->veranstaltung_id)?'':' checked="checked " ').' '.$cTmpResScript.' type="checkbox" value="'.$veranstaltung_id.'" name="veranstaltung_id_zuordnen">';
				if (!empty($reservierungierung[$iTmpZehler]->veranstaltung_id) &&  $reservierungierung[$iTmpZehler]->veranstaltung_id!=$veranstaltung_id)
				{
					$showHTML.='&nbsp;zugeordnet zu <span class="cursor_hand" onclick="callWindows(\'jahresplan_detail.php?work=update&amp;veranstaltung_id='.$reservierungierung[$iTmpZehler]->veranstaltung_id.'\',\'Veranstaltung_Detail\').focus();">'.$reservierungierung[$iTmpZehler]->veranstaltung_id.'</span>';
				}	
				$showHTML.='</td>';
				$showHTML.='</tr>';
		$showHTML.='
		</form>
		</tr>
		';
			$lastkey=$reservierungierung[$iTmpZehler]->key;
			$alleReservierung_id.=(!is_null($alleReservierung_id) && $alleReservierung_id?'|':'').$reservierungierung[$iTmpZehler]->reservierung_id;		
		}
		
		if (!is_null($lastkey) && isset($reservierungierung[$iTmpZehler-1]) && isset($reservierungierung[$iTmpZehler-1]->key))
		{	
			$showHTML.='
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'>
			<form  accept-charset="UTF-8" name="selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle" target="_self" action="'. $_SERVER['PHP_SELF'] .'"  method="post" enctype="multipart/form-data">
				<td align="center"><a href="javascript:window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.work.value=\'save\';window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.submit();">'.$p->t("global/alle").'</a></td>
				<td>'. implode("</td><td>",explode('|',$lastkey)).'</td>
					<td colspan="5">
					<input class="ausblenden" name="reservierung_id" value="'.$alleReservierung_id.'">
					<input class="ausblenden" name="startDatum" value="'.$startDatum.'">
					<input class="ausblenden" name="endeDatum" value="'.$endeDatum.'">
					<input class="ausblenden" name="veranstaltung_id" value="'.$veranstaltung_id.'">
					
					<input class="ausblenden" name="veranstaltung_id_zuordnen" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="reservierung_key" value="'.$reservierungierung[$iTmpZehler-1]->key.'">
					<input class="ausblenden" name="work" value="nix">
				</td>
				';	
				$showHTML.='</tr>';
			$showHTML.='
			</form>
			</tr>
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'><td colspan="9"><hr></td></tr>
			';
			$alleReservierung_id=null;
		}		
		
		$showHTML.='		
		</table>';
	$showHTML.=$error;
	$showHTML.='<p><span class="footer_zeile">'.$p->t('eventkalender/beiFragenGebenSieImmerDieVeranstaltungsIdXYan',array($veranstaltung_id)).'.</span></p>';
	echo $showHTML;
?>
</body>
</html>