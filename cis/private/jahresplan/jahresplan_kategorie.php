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
*	Veranstaltungskategorie- Pflege
*
*		Aktionen: Anzeige, Anlage, Aenderung und Loeschen
*		Ansicht : Voll oder Popup (window.opener)	
*
*
*/

// ---------------- CIS Include Dateien einbinden
	require_once('../../config.inc.php');
	// Datenbankverbindung - ohne erfolg kann hier bereits beendet werden
	if (!$conn=pg_pconnect(CONN_STRING))
	{
		die('Jahresplan<br />Keine Veranstaltungen zurzeit Online.<br />Bitte etwas Geduld.<br />Danke'); 
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
		die('Sie sind nicht berechtigt f&uuml;r diese Seite');
	}	
	
// ------------------------------------------------------------------------------------------
// Datenlesen fuer Anzeige
//	a) verarbeiten wenn Request Parameter 'work' belegt ist
//	b) alle Kategorien lesen
// ------------------------------------------------------------------------------------------
	
	$Jahresplan = new jahresplan($conn);
	$Jahresplan->InitVeranstaltungskategorie();

	$work=(isset($_REQUEST['work']) ? $_REQUEST['work'] :'');
	$error='';

// ------------------------------------------------------------------------------------------
// Datenverarbeiten	
// ------------------------------------------------------------------------------------------
	if (!empty($work) && isset($_REQUEST['veranstaltungskategorie_kurzbz'])  && !empty($_REQUEST['veranstaltungskategorie_kurzbz']))
	{
		if ($work=='save')
		{
			// Bildverarbeitung 
			if(isset($_FILES['uploadBild']['tmp_name']))
			{
				$filename = $_FILES['uploadBild']['tmp_name'];
				//File oeffnen
				if ($fp = fopen($filename,'r'))
				{
					//auslesen
					$string = fread($fp, filesize($filename));
					fclose($fp);
					if (isset($fp)) unset($fp);
					//in HEX-Werte umrechnen
			    		$hex="";
					for ($i=0;$i<strlen($string);$i++)
					        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
					if (!empty($hex)) 
					{
						$_REQUEST["bild"]=$hex;
					}	
				}	
			}
			// Update oder Insert ( veranstaltungskategorie_kurzbz_old )
			if(!$veranstaltungskategorie=$Jahresplan->saveVeranstaltungskategorie($_REQUEST))
			{	
				$error='Fehler beim &auml;ndern ! '.$Jahresplan->getError();
			}
			else
			{
				$error='Veranstaltungskategorie "'.$_REQUEST['veranstaltungskategorie_kurzbz'].'" ge&auml;ndert.';
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
			if(!$veranstaltungskategorie=$Jahresplan->deleteVeranstaltungskategorie(trim($_REQUEST['veranstaltungskategorie_kurzbz'])))
			{	
				$error='Fehler beim l&ouml;schen ! '.$Jahresplan->getError();
			}
			else
			{
				$error='Veranstaltungskategorie "'.$_REQUEST['veranstaltungskategorie_kurzbz'].'" gel&ouml;scht.';
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
	}
	// Datenverarbeiten ende
	
// ------------------------------------------------------------------------------------------
// Aktuelle Datenlesen
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
	
#var_dump($veranstaltungskategorie);		
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Jahresplan</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<style type="text/css">
	<!-- 
	form {display:inline;}
	.cursor_hand { cursor: pointer;vertical-align: top;white-space : nowrap;}
	.ausblenden {display:none;}
	.footer_zeile {color: silver;}

	tr.header_liste_row_0  {background:#FEFFEC;vertical-align: top;white-space : nowrap;}
	tr.header_liste_row_1  {background:#F7F7F7;vertical-align: top;white-space : nowrap;}

	-->
	</style>

</head>
<body>
<?php 
 // Start Wartungsberechtigt - Anzeige des Speziellen Menues
	$cTmpScriptWartungVeranstaltung="javascript:callWindows('jahresplan_veranstaltung.php?work=show&amp;veranstaltung_id=','Veranstaltung_Aenderung');";
	$cTmpScriptWartungKategorie="javascript:callWindows('jahresplan_kategorie.php?work=show&amp;veranstaltungskategorie_kurzbz=','Kategorie_Aenderung');";
?>
		<script language="JavaScript1.2" type="text/javascript">
		<!--
			if (!window.opener || window.opener.closed) {
				document.write('<?php echo '[&nbsp;<a href="index.php">Veranstaltung</a>&nbsp;|&nbsp;<a href="jahresplan_veranstaltung.php">Veranstaltung bearbeiten</a>&nbsp;|&nbsp;<a href="jahresplan_kategorie.php">Kategorie</a>&nbsp;]&nbsp;'.$userNAME; ?>');
			} else {
				window.resizeTo(800,600);
			}

		-->
		</script>				


	<h1>&nbsp;Kategoriebearbeiten&nbsp;</h1>
	<table cellpadding="1" cellspacing="4">
		<tr>
			<th>Kurzbezeichnung</th>
			<th>Bezeichnung</th>
			<th>Farbe</th>
			<th>Bildladen</th>
			<th>Bild</th>
			<th colspan="2">Aktion</th>
		</tr>
			
		<?php  for ($iTmpZehler=-1;$iTmpZehler<count($veranstaltungskategorie);$iTmpZehler++) { 
				// Create IMG  
				if (isset($veranstaltungskategorie[$iTmpZehler]) && $veranstaltungskategorie[$iTmpZehler]["bild"])
				{
					$veranstaltungskategorie[$iTmpZehler]['veranstaltungskategorie_kurzbz']=trim($veranstaltungskategorie[$iTmpZehler]['veranstaltungskategorie_kurzbz']);
					$veranstaltungskategorie[$iTmpZehler]['bezeichnung']=trim($veranstaltungskategorie[$iTmpZehler]['bezeichnung']);					

					$cURL='jahresplan_bilder.php?time='.time().'&'.(strlen($veranstaltungskategorie[$iTmpZehler]["bild"])<700?'heximg='.$veranstaltungskategorie[$iTmpZehler]["bild"]:'veranstaltungskategorie_kurzbz='.$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]);
					$veranstaltungskategorie[$iTmpZehler]["bild_image"]='<img height="20" border="0" alt="Kategoriebild" titel="'.$veranstaltungskategorie[$iTmpZehler]["bezeichnung"].'" src="'.$cURL.'" />';
				}
		
		?>
			     
		<form name="selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
		<tr <?php echo ($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ');?> >
	
			<td>*
				<input type="text" name="veranstaltungskategorie_kurzbz" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]['veranstaltungskategorie_kurzbz'])?$veranstaltungskategorie[$iTmpZehler]['veranstaltungskategorie_kurzbz']:constEingabeFehlt);?>" size="17" maxlength="16" onblur="if (this.value=='') {this.value=this.defaultValue;}" onfocus="if (this.value=='<?php echo constEingabeFehlt; ?>') { this.value='';}" />
				<input class="ausblenden" name="veranstaltungskategorie_kurzbz_old" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]['veranstaltungskategorie_kurzbz'])?$veranstaltungskategorie[$iTmpZehler]['veranstaltungskategorie_kurzbz']:'');?>" />
			</td>
	
			<td>*<input name="bezeichnung" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]['bezeichnung'])?$veranstaltungskategorie[$iTmpZehler]['bezeichnung']:'');?>" size="20" maxlength="63" /></td>
			<td><input  <?php echo (isset($veranstaltungskategorie[$iTmpZehler]['farbe'])?' style="background-color:#'.$veranstaltungskategorie[$iTmpZehler]['farbe'].';"':'');?> name="farbe" onchange="if (this.value=='') {this.style.backgroundColor='transparent';} else {this.style.backgroundColor='#' + this.value;}" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]['farbe'])?$veranstaltungskategorie[$iTmpZehler]['farbe']:'');?>" size="7" maxlength="6" /></td>

			<td>
				 <input size="8" maxlength="140" type="file" id="uploadBild" name="uploadBild" alt="suche" title="suchen" style="font-size:xx-small;" />
				 <input class="ausblenden" name="bild" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]['bild'])?$veranstaltungskategorie[$iTmpZehler]['bild']:'');?>" />				
			</td>

			<td>
				<input class="ausblenden" size="10" name="work" value="?" />
				<?php echo (isset($veranstaltungskategorie[$iTmpZehler]["bild_image"])?$veranstaltungskategorie[$iTmpZehler]["bild_image"]:'');?>
			</td>
			
			<td class="cursor_hand" onclick="if (window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.veranstaltungskategorie_kurzbz.value=='<?php echo constEingabeFehlt; ?>')  {window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.veranstaltungskategorie_kurzbz.focus();return false;}; if (window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.bezeichnung.value.length<1) {window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.bezeichnung.focus();return false;}; window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='save';window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" >speichern <img height="14px" border="0" alt="sichern - save" src="../../../skin/images/date_edit.png" /></td>
			<td <?php echo ($iTmpZehler<0?' class="ausblenden" ':''); ?> class="cursor_hand" onclick="window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='del';window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" >l&ouml;schen <img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/date_delete.png" /></td>
		</tr>
		</form>
		<?php } ?>				
		<tr class="footer_zeile"><td colspan="7">Kurzbezeichnung mit einem * (Stern) an erster Stelle werden nur f&uuml;r Mitarbeiter und Lektoren angezeigt.</td></tr>
		<tr class="footer_zeile"><td colspan="7">Pflichtfelder sind mit * (Stern) gekennzeichnet.</td></tr>
	</table>
	<?php echo $error; ?>
</body>
</html>


