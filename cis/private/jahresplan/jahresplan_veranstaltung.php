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
*	Veranstaltungsdaten - Pflege
*
*		Aktionen: Anzeige, Anlage, Aenderung und Loeschen
*		Ansicht : Voll oder Popup (window.opener)	
*
*		Zusatz : Reservierungsinformationen 
*				koennen im Veranstaltungszeitraum dazu gefuegt werden 
*		
*
*/
 
 
// ---------------- CIS Include Dateien einbinden
	require_once('../../config.inc.php');
	// Datenbankverbindung - ohne erfolg kann hier bereits beendet werden
	if (!$conn=pg_pconnect(CONN_STRING))
	{
		die('Jahresplan<br>Keine Veranstaltungen zurzeit Online.<br>Bitte etwas Geduld.<br>Danke'); 
	}
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');

// ---------------- Datenbank-Verbindung 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	
// ---------------- Jahresplan Classe und Allg.Funktionen		
	include_once('../../../include/jahresplan.class.php');
 	include_once('jahresplan_funktionen.inc.php');
	
// ------------------------------------------------------------------------------------------
//	Request Parameter 
// ------------------------------------------------------------------------------------------
	if (!$userUID=get_uid())
	{
		die('Es wurde keine Benutzer UID gefunden ?');
	}
	// Parameter Veranstaltungskategorie
  	$veranstaltungskategorie_kurzbz=trim((isset($_REQUEST['veranstaltungskategorie_kurzbz']) ? $_REQUEST['veranstaltungskategorie_kurzbz']:''));
	// Parameter Veranstaltung
   	$veranstaltung_id=trim((isset($_REQUEST['veranstaltung_id']) ? $_REQUEST['veranstaltung_id']:''));
   	$Jahr=trim((isset($_REQUEST['Jahr']) ? $_REQUEST['Jahr']:date("Y", mktime(0,0,0,date("m"),date("d"),date("y")))));
   	$Monat=trim((isset($_REQUEST['Monat']) ? $_REQUEST['Monat']:date("m", mktime(0,0,0,date("m"),date("d"),date("y")))));
	$Suchtext=trim((isset($_REQUEST['Suchtext']) ? $_REQUEST['Suchtext']:''));

	$work=trim((isset($_REQUEST['work']) ? $_REQUEST['work']:''));
	
// ------------------------------------------------------------------------------------------
//	Personen Classe 
//		Anwernderdaten ermitteln
// ------------------------------------------------------------------------------------------
	$userNAME=$userUID;
	$unicode=null; // Standart Encoding der Datenbank
	$pers = new benutzer($conn,$userUID,$unicode); // Lesen Person - Benutzerdaten
	if (isset($pers->nachname))
	{
		$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
		$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
		$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
		$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
	}
// ------------------------------------------------------------------------------------------
//	Benutzer Classe 
//		Berechtigungen ermitteln
// ------------------------------------------------------------------------------------------
	$is_lector=false;
	$is_wartungsberechtigt=false;
	if (isset($pers->nachname))
	{
		$benutzerberechtigung = new benutzerberechtigung($conn,$userUID);
		$benutzerberechtigung->getBerechtigungen($userUID,true);
		// Nur Lektoren oder Mitarbeiter duerfen alle Termine sehen , Studenten nur Freigegebene Kategorien
		if($benutzerberechtigung->fix || $benutzerberechtigung->lektor)
			$is_lector=true;
		else
			$is_lector=false;
		// Kennzeichen setzen fuer Berechtigungspruefung
		$berechtigung='veranstaltung';
		$studiengang_kz=null;
		$art='suid';
		$fachbereich_kurzbz=null;
		// Berechtigungen abfragen
		$is_wartungsberechtigt=$benutzerberechtigung->isBerechtigt($berechtigung,$studiengang_kz,$art, $fachbereich_kurzbz);
		if (!$is_wartungsberechtigt)
		{
			$is_wartungsberechtigt=false;
		}	
		unset($benutzerberechtigung); // Klasse Berechtigungen entfernen 
	}	

	if (!$is_wartungsberechtigt)
	{
		exit('Sie sind nicht berechtigt f&uuml;r diese Seite !');
	}
// ------------------------------------------------------------------------------------------
// Datenlesen fuer Anzeige
//	a) verarbeiten wenn Request Parameter 'work' - save(update) oder del
//	b) alle Veranstaltung lesen
// ------------------------------------------------------------------------------------------
	$Jahresplan = new jahresplan($conn);

	$error='';
	$work=(isset($_REQUEST['work']) ? $_REQUEST['work'] :'');
	if (!empty($work) && isset($_REQUEST['veranstaltung_id']) )
	{
		$Jahresplan->InitVeranstaltung();
		// Nur Berechtigte duerfen alle Informationen sehen (Mitarbeiter)	
		$Jahresplan->setVeranstaltungskategorieMitarbeiter($is_lector);
		// Nur Berechtigte duerfen auch noch nicht freigegebene Sehen	
		if (!$is_wartungsberechtigt)	
			$Jahresplan->setFreigabe(true);
		else
			$Jahresplan->setFreigabe(false);

		if ($work=='save')
		{
			$_REQUEST['veranstaltung_id']=(isset($_REQUEST['veranstaltung_id']) && !empty($_REQUEST['veranstaltung_id'])?trim($_REQUEST['veranstaltung_id']):'');
			if(!$veranstaltung=$Jahresplan->saveVeranstaltung($_REQUEST))
			{	
				$error='Fehler beim anlegen ! '.$Jahresplan->getError()." ".$Jahresplan->getVeranstaltung_id();
				$error.= "<br>" . $Jahresplan->getStringSQL() ."<br>";
				
			}
			else
			{
				$veranstaltung_id=$Jahresplan->getVeranstaltung_id();
				$error=(!empty($_REQUEST['veranstaltung_id_old'])?' &Auml;nderung ':' anlegen ').' ID '.$veranstaltung_id .' erfolgreich ! '.$Jahresplan->getError();
				$error.='	<script language="JavaScript1.2" type="text/javascript">
						<!--
							if (window.opener && !window.opener.closed) {
								if (confirm("Soll die Hauptseite neu aufgebaut werden?")) {
									window.opener.location.reload();
								}	
							}
						-->
						</script>				
					';
			}
		}
		
		if ($work=='del')
		{
			if(!$veranstaltung=$Jahresplan->deleteVeranstaltung($_REQUEST))
			{	
				$error='Fehler beim l&ouml;schen ! '.$Jahresplan->getError();
			}
			else
			{
				$error='Veranstaltung "'.$_REQUEST['veranstaltung_id'].'" gel&ouml;scht.';
				$veranstaltung_id='';
				$_REQUEST['veranstaltung_id']='';
				$_REQUEST['veranstaltung_id_old']='';
				$error.='	<script language="JavaScript1.2" type="text/javascript">
						<!--
							if (window.opener && !window.opener.closed) {
								if (confirm("Soll die Hauptseite neu aufgebaut werden?")) {
									window.opener.location.reload();
								}	
								this.close();
							}
						-->
						</script>				
					';				
			}
		}
	}

// ------------------------------------------------------------------------------------------
// Kategorie - Daten lesen fuer Kategorieselect 
//			Veranstaltungskategorien ohne Selektionsbedingung
// ------------------------------------------------------------------------------------------
	$Jahresplan->InitVeranstaltungskategorie();
	if ($Jahresplan->loadVeranstaltungskategorie())
	{
		$veranstaltungskategorie=$Jahresplan->getVeranstaltungskategorie();
	}	
	else // Es gibt keine Kategorie oder Fehler beim Lesen - keine weitere Anzeige mehr moeglich
	{
		die($Jahresplan->getError());
	}
	
// ------------------------------------------------------------------------------------------
// Daten lesen fuer Anzeige der
//	 Veranstaltungen mit Selektionsbedingung
// ------------------------------------------------------------------------------------------
	if (!empty($veranstaltung_id))
	{
		$Jahresplan = new jahresplan($conn);
		$Jahresplan->InitVeranstaltung();	
		// Nur Berechtigte duerfen alle Informationen sehen (Mitarbeiter)	
		$Jahresplan->setVeranstaltungskategorieMitarbeiter($is_lector);
		// Nur Berechtigte duerfen auch noch nicht freigegebene Sehen	
		if (!$is_wartungsberechtigt)	
			$Jahresplan->setFreigabe(true);
		else
			$Jahresplan->setFreigabe(false);
			
		$Jahresplan->setVeranstaltung_id($veranstaltung_id);
		$Jahresplan->setVeranstaltungskategorie_kurzbz($veranstaltungskategorie_kurzbz);
		
		$veranstaltung=array();
		if ($Jahresplan->loadVeranstaltung())
		{
			$veranstaltung=$Jahresplan->getVeranstaltung();
			$veranstaltung=jahresplan_funk_veranstaltung_extend($veranstaltung);
		}
		elseif (empty($work))  // Es gibt keine Veranstaltung oder Fehler beim Lesen - keine weitere Anzeige mehr moeglich
		{
			die($Jahresplan->getError());
		}
	// Plausib
		if (!is_array($veranstaltung) || count($veranstaltung)<1 || !isset($veranstaltung["veranstaltung_id"])) 
		{
			$work='new';
			$veranstaltung_id='';
		}	
	}
	else // Reload ohne Datenverarbeitung , die Aufrufparameter in die Datentabelle uebertragen fuer Value der Inputfelder
	{
		$veranstaltung=$_REQUEST;
	}
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
	<title>Jahresplan</title>
<script language="JavaScript" type="text/javascript">
<!--
//-->
</script>
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<style type="text/css">
	<!-- 
	form {display:inline;}
	label {text-align:right;}
	iframe  {display:inline;width: 100%;border:0px;}
	
	.cursor_hand {cursor:pointer;vertical-align: top;white-space : nowrap;}
	.ausblenden {display:none;}
	.footer_zeile {color: silver;}
	-->
	</style>
	<script language="JavaScript1.2" type="text/javascript">
	<!--
	function PruefeDatum(Datum,Startjahr,Endjahr) 
	 { 
	      var Datum, Tag, Monat, Jahr, Laenge, tageMonat; 
	      Laenge=Datum.length; 
		  
	      var datum_int = new Date(); 
	  
	      if (!parseInt(Startjahr) || Startjahr<1000)
		  {	  	
	    	  Startjahr = datum_int.getFullYear();
		      Startjahr = Startjahr - 1;      
	      } 
	  
	
	      if (!parseInt(Endjahr) || Endjahr<1000)
		  {
		  	Endjahr = datum_int.getFullYear();
	      		Endjahr = Endjahr +1;
		  }
	
	      if (Laenge==10 && Datum.substring(2,3)=="." && Datum.substring(5,6)==".") 
	      { 
		      Tag=parseInt(Datum.substring(0,2),10); 
		      Monat=parseInt(Datum.substring(3,5),10); 
		      Jahr=parseInt(Datum.substring(6,10),10); 
	      } 
	      else 
	      { 
	         alert("Kein gueltiges Datum!\nBitte Datum "+ Datum +" in der Form: TT.MM.JJJJ eingeben!"); 
		  return false; 
	      } 
	       
	      if (Monat==4 || Monat==6 || Monat==9 || Monat==11) 
	      { 
		      tageMonat=30; 
	      } 
	      else if (Monat==1 || Monat==3 || Monat==5 || Monat==7 || Monat==8 
	        || Monat==10 || Monat==12) 
	      { 
	      tageMonat=31; 
	      } 
	      else if(Monat==2 && Jahr%4==0 && Jahr%100!=0 || Jahr%400==0) 
	      { 
	      tageMonat=29; 
	      } 
	      else if(Monat==2 && Jahr%4!=0 || Jahr%100==0 && Jahr%400!=0) 
	      { 
	      tageMonat=28; 
	      } 
	       
	      if (Tag>=1 && Tag<=tageMonat && Monat>=1 && Monat<=12 && Jahr>=Startjahr && Jahr<=Endjahr) 
	      { 
		      return true; 
	      } 
	      else 
	      { 
		  	if (Tag<1 || Tag>tageMonat)
		         alert("Kein gueltiges Datum - Tag ("+ Tag +" >1 und <"+ tageMonat+" ) Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
		  	else if (Monat<1 || Monat>12)
		         alert("Kein gueltiges Datum - Monat ("+ Monat +"> 1 und <12 ) Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
		  	else if (Jahr<Startjahr || Jahr>Endjahr )
		         alert("Kein gueltiges Datum - Jahr ("+ Jahr +"> "+Startjahr+" und <"+Endjahr+" ) Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
			else	
		         alert("Kein gueltiges Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
		     return false; 
	      } 
 	 }
	
	function TimestampDatumZeit(Datum,Zeit,Startjahr,Endjahr) 
	 { 
	      var Datum, Tag, Monat, Jahr, Laenge,Stunde,Minute; 
	      Laenge=Zeit.length; 
	      var datum = new Date(); 
	      var Endjahr = datum.getYear(); 
	      Endjahr = Endjahr +10;
	      var Startjahr = datum.getYear();
	      Startjahr = Startjahr - 10;      
		  if (!PruefeDatum(Datum,Startjahr,Endjahr))
		  	return false;
	
	      Tag=parseInt(Datum.substring(0,2),10); 
	      Monat=parseInt(Datum.substring(3,5),10); 
	      Jahr=parseInt(Datum.substring(6,10),10); 
			
			
	      if (Laenge==5 && Zeit.substring(2,3)==":") 
	      { 
		      Stunde=parseInt(Zeit.substring(0,2),10); 
		      Minute=parseInt(Zeit.substring(3,5),10); 
	      } 
	
	      else if (Laenge==4 && Zeit.substring(1,2)==":") 
	      { 
		      Stunde=parseInt(Zeit.substring(0,1),10); 
		      Minute=parseInt(Zeit.substring(2,4),10); 
	      } 
	      else 
	      { 
	         alert("Kein gueltige Zeit!\nBitte Zeit "+Zeit+" in der Form: HH:MM eingeben!"); 
		     return false; 
	      }
		Monat=Monat-1;
	    //if (Monat<1) Monat=1;
		var timestamp = (new Date(Jahr,Monat,Tag,Stunde,Minute).getTime()/1000); 
		return timestamp;
	
	}

	var InfoWin;  
	function callWindows(url,nameID)
	{
		 // width=(Pixel) - erzwungene Fensterbreite 
		 // height=(Pixel) - erzwungene Fensterhöhe 
		 // resizable=yes/no - Größe fest oder veränderbar 
		 // scrollbars=yes/no - fenstereigene Scrollbalken 
		 // toolbar=yes/no - fenstereigene Buttonleiste 
		 // status=yes/no - fenstereigene Statuszeile 
		 // directories=yes/no - fenstereigene Directory-Buttons (Netscape) 
		 // menubar=yes/no - fenstereigene Menüleiste 
		 // location=yes/no - fenstereigenes Eingabe-/Auswahlfeld für URLs 
		 
		if (InfoWin) {
			InfoWin.close();
	 	}
	       InfoWin=window.open(url,nameID,"copyhistory=no,directories=no,location=no,dependent=no,toolbar=yes,menubar=no,status=no,resizable=yes,scrollbars=yes, width=500,height=600,left=60, top=15");  
		InfoWin.focus();
		InfoWin.setTimeout("window.close()",800000);
	}

	
			if (!window.opener || window.opener.closed) {
				document.write('[&nbsp;<a href="index.php">Veranstaltungen</a>&nbsp;|&nbsp;<a href="jahresplan_veranstaltung.php">Veranstaltung bearbeiten</a>&nbsp;|&nbsp;<a href="jahresplan_kategorie.php">Kategorie</a>&nbsp;]&nbsp;<?php echo $userNAME; ?>');
			} else {
				window.resizeTo(800,800);
			}
	
	-->
	</script>				
	
</head>
<body>
<?php 

	// Defaultwerte
	$cTmpCheckHeute = date("d.m.Y", mktime(0,0,0,date("m"),date("d"),date("y")));

	$cTmpTimestampStart=mktime(8,0,0,date("m"),date("d"),date("y"));
	$cTmpTimestampEnde=mktime(18,0,0,date("m"),date("d"),date("y"));
	if (isset($_REQUEST['start_datum']))
	{
		$arr = explode(".", $_REQUEST['start_datum']);
		$cTmpTimestampStart=mktime(8,0,0,$arr[1],$arr[0],$arr[2]);
		$cTmpTimestampEnde=mktime(18,0,0,$arr[1],$arr[0],$arr[2]);
	}
	if (!isset($veranstaltung['start_timestamp']))
	{
		$veranstaltung['start_timestamp']=$cTmpTimestampStart;
	}
	if (!isset($veranstaltung['ende_timestamp']))
	{
		$veranstaltung['ende_timestamp']=$cTmpTimestampEnde;
	}
	
	// Wartungsmenue URL
	$cTmpScriptWartungVeranstaltung='javascript:callWindows("jahresplan_veranstaltung.php?work=show&amp;veranstaltung_id=","Veranstaltung_Aenderung");';
	$cTmpScriptWartungKategorie='javascript:callWindows("jahresplan_kategorie.php?work=show&amp;veranstaltungskategorie_kurzbz=","Kategorie_Aenderung");';

?>
  <h1>&nbsp;Veranstaltung bearbeiten&nbsp;</h1>
  <fieldset>
    <legend><?php echo (!empty($veranstaltung_id)?"Datenpflege ID $veranstaltung_id":' Neuanlage '); ?></legend>

		<form name="selVeranstaltung" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
			<table cellpadding="10" cellspacing="0">
	
				<tr>
					<td><label for="veranstaltung_id">ID</label></td>
					<td>
						<?php echo (isset($veranstaltung['veranstaltung_id'])?$veranstaltung['veranstaltung_id']:$veranstaltung_id); ?>
						<input class="ausblenden" id="veranstaltung_id" name="veranstaltung_id" type="text" size="4" maxlength="10" value="<?php echo (isset($veranstaltung['veranstaltung_id'])?$veranstaltung['veranstaltung_id']:$veranstaltung_id); ?>" >
						<input class="ausblenden" name="veranstaltung_id_old" type="text" value="<?php echo (isset($veranstaltung['veranstaltung_id'])?$veranstaltung['veranstaltung_id']:$veranstaltung_id);?>" >
					</td>

					<td title="Neuanlage <?php echo date("d.m.Y",$veranstaltung['start_timestamp']);?>"  class="cursor_hand" onclick="self.location.href='<?php echo $_SERVER['PHP_SELF'].'?start_timestamp='.(isset($veranstaltung['start_timestamp'])?$veranstaltung['start_timestamp']:$cTmpTimestampStart).'&amp;ende_timestamp='.(isset($veranstaltung['ende_timestamp'])?$veranstaltung['ende_timestamp']:$cTmpTimestampEnde) ;?>';" >Neuanlage&nbsp;<img border="0" alt="Neuanlage" src="../../../skin/images/date_add.png" ></td>
					
				</tr>				
				
				<tr>
					<td><label for="veranstaltung_id">Kategorie</label></td>
					<td><select name="veranstaltungskategorie_kurzbz">
					<?php
						// Verarbeitungskategorie - Auswahl.- Selektliste
					  	if  (is_array($veranstaltungskategorie) || count($veranstaltungskategorie)>0)
						{
							reset($veranstaltungskategorie);
						  	for ($iTmpZehler=0;$iTmpZehler<count($veranstaltungskategorie);$iTmpZehler++)
							{
								// Check Space
								$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]=trim($veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]);
								$veranstaltungskategorie[$iTmpZehler]["bezeichnung"]=trim($veranstaltungskategorie[$iTmpZehler]["bezeichnung"]);
		
								$cURL='jahresplan_bilder.php?time='.time().'&'.(strlen($veranstaltungskategorie[$iTmpZehler]["bild"])<800?'heximg='.$veranstaltungskategorie[$iTmpZehler]["bild"]:'veranstaltungskategorie_kurzbz='.$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]);
								$veranstaltungskategorie[$iTmpZehler]["bild_image"]='<img height="20" border="0" alt="Kategoriebild" titel="'.$veranstaltungskategorie[$iTmpZehler]["bezeichnung"].'" src="'.$cURL.'">';
		
								echo '<option  '.(!empty($veranstaltungskategorie[$iTmpZehler]["farbe"])?' style="background-color:#'.$veranstaltungskategorie[$iTmpZehler]["farbe"].'" ':'').'  '.(isset($veranstaltung['veranstaltungskategorie_kurzbz']) && $veranstaltung['veranstaltungskategorie_kurzbz']==$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]?' selected="selected" ':'').' value="'.$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"].'">'.$veranstaltungskategorie[$iTmpZehler]["bezeichnung"].'</option>';
							}	
						}
					?>
					</select></td>
				</tr>				

					

				<tr>
					<td><label for="Datum1">Datum von</label></td>
					<td>
						<input class="ausblenden" name="start" type="text" value="<?php echo $veranstaltung['start_timestamp']=trim((isset($veranstaltung['start_timestamp'])?$veranstaltung['start_timestamp']:mktime(8,0,0,date("m"),date("d"),date("y")))) ;?>" >
						<input id="Datum1" name="Datum1" type="text" size="11" maxlength="11" value="<?php echo $veranstaltung['start_datum']=trim(date("d.m.Y",$veranstaltung['start_timestamp']));?>"  onchange="window.document.selVeranstaltung.tmpGanztag.checked=false;var time_stamp=TimestampDatumZeit(window.document.selVeranstaltung.Datum1.value,window.document.selVeranstaltung.Zeit1.value);  if (!time_stamp) {this.focus();return false;} else {window.document.selVeranstaltung.start.value=time_stamp; }; if (window.document.selVeranstaltung.start.value > window.document.selVeranstaltung.ende.value) {alert('Datum von ist kleiner als bis');this.focus(); } ; " >
						&nbsp;
						<select  id="Zeit1" name="Zeit1"  onchange="window.document.selVeranstaltung.tmpGanztag.checked=false;var time_stamp=TimestampDatumZeit(window.document.selVeranstaltung.Datum1.value,window.document.selVeranstaltung.Zeit1.value);  if (!time_stamp) {this.focus();return false;} else {window.document.selVeranstaltung.start.value=time_stamp; }; if (window.document.selVeranstaltung.start.value > window.document.selVeranstaltung.ende.value) {alert('Datum von ist kleiner als bis');this.focus(); } ; ">
						<?php
						$veranstaltung['start_zeit']=date("H:i",$veranstaltung['start_timestamp']);
						$veranstaltung['start_zeit']=trim($veranstaltung['start_zeit']);
						for ($timeIND=0;$timeIND<24;$timeIND++)
						{
							$cTmpTime=$timeIND.':00';
							echo '<option '. ($veranstaltung['start_zeit']==$cTmpTime || $veranstaltung['start_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':15';
							echo '<option '. ($veranstaltung['start_zeit']==$cTmpTime || $veranstaltung['start_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':30';
							echo '<option '. ($veranstaltung['start_zeit']==$cTmpTime || $veranstaltung['start_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':45';
							echo '<option '. ($veranstaltung['start_zeit']==$cTmpTime || $veranstaltung['start_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
						}	
						?>	
						</select>
					</td>	
				</tr>				


				<tr>
					<td><label for="Datum2">Datum bis</label></td>
					<td>
						<input class="ausblenden" name="ende" type="text" value="<?php echo $veranstaltung['ende_timestamp']=trim((isset($veranstaltung['ende_timestamp'])?$veranstaltung['ende_timestamp']:mktime(18,0,0,date("m"),date("d"),date("y")))) ;?>" >
						<input id="Datum2" name="Datum2" type="text" size="11" maxlength="11" value="<?php echo $veranstaltung['ende_datum']=trim(date("d.m.Y",$veranstaltung['ende_timestamp']));?>"   onchange="window.document.selVeranstaltung.tmpGanztag.checked=false;var time_stamp=TimestampDatumZeit(window.document.selVeranstaltung.Datum2.value,window.document.selVeranstaltung.Zeit2.value);  if (!time_stamp) {this.focus();return false; } else {window.document.selVeranstaltung.ende.value=time_stamp; }; if (window.document.selVeranstaltung.start.value > window.document.selVeranstaltung.ende.value) {alert('Datum von ist kleiner als bis');this.focus(); } ;" >
						&nbsp;
						<select  id="Zeit2" name="Zeit2"  onchange="window.document.selVeranstaltung.tmpGanztag.checked=false;var time_stamp=TimestampDatumZeit(window.document.selVeranstaltung.Datum2.value,window.document.selVeranstaltung.Zeit2.value);  if (!time_stamp) {this.focus();return false; } else {window.document.selVeranstaltung.ende.value=time_stamp; }; if (window.document.selVeranstaltung.start.value > window.document.selVeranstaltung.ende.value) {alert('Datum von ist kleiner als bis');this.focus(); } ;">
						<?php
						$veranstaltung['ende_zeit']=date("H:i",$veranstaltung['ende_timestamp']);
						$veranstaltung['ende_zeit']=trim($veranstaltung['ende_zeit']);
						for ($timeIND=0;$timeIND<24;$timeIND++)
						{
							$cTmpTime=$timeIND.':00';
							echo '<option '. ($veranstaltung['ende_zeit']==$cTmpTime || $veranstaltung['ende_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':15';
							echo '<option '. ($veranstaltung['ende_zeit']==$cTmpTime || $veranstaltung['ende_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':30';
							echo '<option '. ($veranstaltung['ende_zeit']==$cTmpTime || $veranstaltung['ende_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':45';
							echo '<option '. ($veranstaltung['ende_zeit']==$cTmpTime || $veranstaltung['ende_zeit']=='0'.$cTmpTime?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
						}	
						?>	
						</select> 
					 &nbsp;Ganztägige Veranstaltung
					 &nbsp;<input  <?php echo ( ($veranstaltung['start_zeit']=='00:00' && $veranstaltung['ende_zeit']=='23:45')?' checked="checked" ':'' );   ?> type="checkbox"  value="1" onclick="if (this.checked!=false) {window.document.selVeranstaltung.Zeit1.options.selectedIndex=0;window.document.selVeranstaltung.Zeit2.options.selectedIndex=(window.document.selVeranstaltung.Zeit2.options.length - 1); }; var time_stamp=TimestampDatumZeit(window.document.selVeranstaltung.Datum1.value,window.document.selVeranstaltung.Zeit1.value);  if (time_stamp) {window.document.selVeranstaltung.start.value=time_stamp; }; time_stamp=TimestampDatumZeit(window.document.selVeranstaltung.Datum2.value,window.document.selVeranstaltung.Zeit2.value);  if (time_stamp) {window.document.selVeranstaltung.ende.value=time_stamp; };" name="tmpGanztag" >
					</td>	
				</tr>				

				<tr>
					<td><label for="beschreibung">Titel</label></td>
					<td><textarea rows="3" cols="80" id="beschreibung" name="beschreibung" onblur="if (this.value=='') {this.value=this.defaultValue;}" onfocus="if (this.value=='<?php echo constEingabeFehlt; ?>') { this.value='';}"><?php echo (isset($veranstaltung['beschreibung'])?$veranstaltung['beschreibung']:constEingabeFehlt);?></textarea></td>
				</tr>	
				
				<tr>
					<td><label for="inhalt">Beschreibung</label></td>
					<td><textarea rows="3" cols="80" id="inhalt" name="inhalt"><?php echo (isset($veranstaltung['inhalt'])?$veranstaltung['inhalt']:'');?></textarea></td>
				</tr>	
			

				<tr>
					<td>
						<table>
						<tr>
							<td><label for="inhalt">Freigabe</label></td>
							<td><input type="checkbox" <?php echo (!isset($veranstaltung['freigabeamum']) || empty($veranstaltung['freigabeamum'])?'':' checked="checked" ' ); ?>  value="1" onclick="if (this.checked!=false) {window.document.selVeranstaltung.freigabevon.value='<?php echo $userUID;?>';window.document.selVeranstaltung.freigabeamum.value='<?php echo time();?>';} else {window.document.selVeranstaltung.freigabeamum.value='';};" name="tmpFreigabe" ></td>

						</tr>
						</table>
					</td>

					<td>
						<table>
						<tr>
							<td>&nbsp;</td>
							<td class="cursor_hand" onclick="if (window.document.selVeranstaltung.start.value > window.document.selVeranstaltung.ende.value) {alert('Datum von ist kleiner als bis');window.document.selVeranstaltung.Datum1.focus();return false; } ; window.document.selVeranstaltung.work.value='save';window.document.selVeranstaltung.submit();" >speichern&nbsp;<img class="cursor_hand" height="14px" border="0" alt="sichern - save" src="../../../skin/images/date_edit.png" ></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td <?php echo (empty($veranstaltung_id)?' class="ausblenden" ':' class="cursor_hand" '); ?>  onclick="window.document.selVeranstaltung.work.value='del';window.document.selVeranstaltung.submit();" >l&ouml;schen&nbsp;<img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/date_delete.png" ></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td  class="cursor_hand"  onclick="callWindows('jahresplan_detail.php?work=update&amp;veranstaltung_id=<?php echo $veranstaltung_id; ?>','Veranstaltung_Detail').focus(); ">Voransicht&nbsp;<img  title="Voransicht" src="../../../skin/images/date_magnify.png" alt="Voransicht" ></td>
							<td>&nbsp;</td>
						</tr>
						</table>
					</td>
				</tr>	
				<tr class="ausblenden">
					<td colspan="2">
						<input type="Text" value="<?php echo (!isset($veranstaltung['insertvon']) || empty($veranstaltung['insertvon'])?$userUID:$veranstaltung['insertvon'] ); ?>" name="insertvon" >
						<input type="Text" value="<?php echo (!isset($veranstaltung['insertamum_timestamp']) || empty($veranstaltung['insertamum_timestamp'])?time():$veranstaltung['insertamum_timestamp'] ); ?>" name="insertamum" >
					</td>
				</tr>
				<tr class="ausblenden">
					<td colspan="2">
						<input type="Text" value="<?php echo $userUID; ?>" name="updatevon" >
						<input type="Text" value="<?php echo time(); ?>" name="updateamum" >
					</td>
				</tr>
				<tr class="ausblenden">
					<td colspan="2">
						<input type="Text" value="<?php echo (!isset($veranstaltung['freigabevon']) || empty($veranstaltung['freigabevon'])?'':$veranstaltung['freigabevon'] ); ?>" name="freigabevon" >
						<input type="Text" value="<?php echo (!isset($veranstaltung['freigabeamum_timestamp']) || empty($veranstaltung['freigabeamum_timestamp'])?'':$veranstaltung['freigabeamum_timestamp'] ); ?>" name="freigabeamum" >
					</td>
				</tr>

			</table>
			<input class="ausblenden" type="Text" value="<?php echo $work ;?>" name="work" >
		</form>
	  </fieldset>

	<?php
	echo '<br>'.$error;
	$veranstaltung_id=(isset($veranstaltung['veranstaltung_id'])?$veranstaltung['veranstaltung_id']:$veranstaltung_id);
	if (!empty($veranstaltung_id))
	{
		echo '<hr>'.jahresplan_veranstaltung_detail_user($conn,$veranstaltung,$is_wartungsberechtigt);
		echo '<iframe id="reservierung" src="jahresplan_reservierung.php?veranstaltung_id='.$veranstaltung_id.'&amp;start='.(isset($veranstaltung['start_timestamp'])?$veranstaltung['start_timestamp']:mktime(12,0,0,date("m"),date("d"),date("y"))).'&amp;ende='.(isset($veranstaltung['ende_timestamp'])?$veranstaltung['ende_timestamp']:mktime(13,0,0,date("m"),date("d"),date("y"))).'"></iframe>';
	}
	else
	{
		echo '<hr><span class="footer_zeile">Reservierungen k&ouml;nnen erst nach dem speichern der Veranstaltung zugeordnet werden.</span>';
	}
	?>	

	  
</body>
</html>	
