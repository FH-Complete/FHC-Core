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
	require_once('../../../config/cis.config.inc.php');
		
// ---------------- CIS Funktionen
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
	$work=(isset($_REQUEST['work']) ? $_REQUEST['work'] :'');


// ------------------------------------------------------------------------------------------
// Aufgabe 
//	a) verarbeiten wenn Request Parameter 'work' belegt ist
//	b) alle Kategorien lesen fuer Anzeige
// ------------------------------------------------------------------------------------------
	$Jahresplan->InitVeranstaltungskategorie();

// ------------------------------------------------------------------------------------------
// Datenverarbeiten	
// ------------------------------------------------------------------------------------------
	if (!empty($work) && isset($_REQUEST['veranstaltungskategorie_kurzbz'])  && !empty($_REQUEST['veranstaltungskategorie_kurzbz']))
	{
		if ($work=='save')
		{
			
			// Bildverarbeitung 
			if(isset($_FILES['uploadBild']['tmp_name']) && !empty($_FILES['uploadBild']['tmp_name']) )
			{
				$filename = $_FILES['uploadBild']['tmp_name'];
				//File oeffnen
				if ($fp = fopen($filename,'r'))
				{
					//auslesen
					$string = fread($fp, filesize($filename));
					fclose($fp);
					if (isset($fp)) unset($fp);
					$_REQUEST["bild"]=base64_encode($string);
				}	
			}

			$Jahresplan->new=false;
			if ( (isset($_REQUEST["veranstaltungskategorie_kurzbz_old"]) && $_REQUEST["veranstaltungskategorie_kurzbz"] != $_REQUEST["veranstaltungskategorie_kurzbz_old"])
			||  (!isset($_REQUEST["veranstaltungskategorie_kurzbz_old"]) || empty($_REQUEST["veranstaltungskategorie_kurzbz_old"])) )
			{
				$Jahresplan->new=true;
				if (isset($_REQUEST["veranstaltungskategorie_kurzbz_old"]) && $_REQUEST["veranstaltungskategorie_kurzbz"] != $_REQUEST["veranstaltungskategorie_kurzbz_old"])
					$Jahresplan->deleteVeranstaltungskategorie($_REQUEST["veranstaltungskategorie_kurzbz_old"]);
			}	
			
			$Jahresplan->veranstaltungskategorie_kurzbz=$_REQUEST["veranstaltungskategorie_kurzbz"];
			$Jahresplan->bezeichnung=$_REQUEST["bezeichnung"];
			$Jahresplan->farbe=$_REQUEST["farbe"];
			$Jahresplan->bild=$_REQUEST["bild"];							

			if(!$Jahresplan->saveVeranstaltungskategorie())
			{	
				$error='Fehler bei der '.($Jahresplan->new?' Neuanlage ':' &Auml;nderung ').' '.$Jahresplan->errormsg;
			}
			else
			{
				$error=$p->t("eventkalender/veranstaltungskategorie").' "'.$_REQUEST['veranstaltungskategorie_kurzbz'].'" '.($Jahresplan->new? $p->t("eventkalender/angelegt") : $p->t("eventkalender/geaendert")).' '.$Jahresplan->errormsg;
				$error.=' <script language="JavaScript1.2" type="text/javascript">
						<!--
							if (window.opener && !window.opener.closed) {
								if (confirm('.$p->t("eventkalender/sollDieHauptseiteNeuAufgebautWerden").'?)) {
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
			if(!$Jahresplan->deleteVeranstaltungskategorie($_REQUEST['veranstaltungskategorie_kurzbz']))
			{	
				$error=$Jahresplan->errormsg;
			}
			else
			{
				$error=$p->t("eventkalender/veranstaltungskategorie").' "'.$_REQUEST['veranstaltungskategorie_kurzbz'].'" '.$p->t("eventkalender/geloescht").'.';
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
	} // Datenverarbeiten ende
	
// ------------------------------------------------------------------------------------------
// Aktuelle Datenlesen
// ------------------------------------------------------------------------------------------
	$Jahresplan->InitVeranstaltungskategorie();
	if (!$veranstaltungskategorie=$Jahresplan->loadVeranstaltungskategorie())
		die($p->t("eventkalender/fehlerBeimLesenDerVeranstaltungskategorie").$Jahresplan->errormsg);
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $p->t("eventkalender/jahresplan");?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<style type="text/css">
	<!-- 
	form {display:inline;}
	.cursor_hand { cursor: pointer;vertical-align: top;white-space : nowrap;}
	.ausblenden {display:none;}
	.footer_zeile {color: silver;}
	.pflichtfeld {style:background-color:none ;border : 1px solid Black ;border-style: dashed;}
	
	tr.header_liste_row_0  {background:#FEFFEC;vertical-align: top;white-space : nowrap;}
	tr.header_liste_row_1  {background:#F7F7F7;vertical-align: top;white-space : nowrap;}

	-->
	</style>

</head>
<body>
	<h1>&nbsp;<?php echo $p->t("eventkalender/kategorienBearbeiten");?>&nbsp;</h1>
	
	<?php 
	 // Start Wartungsberechtigt - Anzeige des Speziellen Menues
		$cTmpScriptWartungVeranstaltung="javascript:callWindows('jahresplan_veranstaltung.php?work=show&amp;veranstaltung_id=','Veranstaltung_Aenderung');";
		$cTmpScriptWartungKategorie="javascript:callWindows('jahresplan_kategorie.php?work=show&amp;veranstaltungskategorie_kurzbz=','Kategorie_Aenderung');";
	?>
		<script language="JavaScript1.2" type="text/javascript">
		<!--
			if (!window.opener || window.opener.closed) {
				document.write('<?php echo '[&nbsp;<a href="index.php">'.$p->t("eventkalender/veranstaltung").'</a>&nbsp;|&nbsp;<a href="jahresplan_veranstaltung.php">'.$p->t("eventkalender/veranstaltungBearbeiten").'</a>&nbsp;|&nbsp;<a href="jahresplan_kategorie.php">'.$p->t("eventkalender/kategorie").'</a>&nbsp;]&nbsp;'.$userNAME.'<br/><br/>'; ?>');
			} else {
				window.resizeTo(800,600);
			}

		-->
		</script>				



	<table cellpadding="1" cellspacing="4">
		<tr>
			<th><?php echo $p->t("eventkalender/kurzbezeichnung");?></th>
			<th><?php echo $p->t("global/bezeichnung");?></th>
			<th><?php echo $p->t("eventkalender/farbe");?></th>
			<th><?php echo $p->t("eventkalender/bildladen");?></th>
			<th><?php echo $p->t("eventkalender/bild");?></th>
			<th colspan="2"><?php echo $p->t("global/aktion");?></th>
		</tr>
			
		<?php 
			 // Zaehler = -1 fuer die Neuanlage  	
			 for ($iTmpZehler=-1;$iTmpZehler<count($veranstaltungskategorie);$iTmpZehler++) { 
				// Create IMG  
				if (isset($veranstaltungskategorie[$iTmpZehler]) && $veranstaltungskategorie[$iTmpZehler]->bild)
				{
					$veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz=trim($veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz);
					$veranstaltungskategorie[$iTmpZehler]->bezeichnung=trim($veranstaltungskategorie[$iTmpZehler]->bezeichnung);					

					$cURL='jahresplan_bilder.php?time='.time().'&'.(strlen($veranstaltungskategorie[$iTmpZehler]->bild)<700?'heximg='.$veranstaltungskategorie[$iTmpZehler]->bild:'veranstaltungskategorie_kurzbz='.$veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz);
					$veranstaltungskategorie[$iTmpZehler]->bild_image='<img height="20" border="0" alt="Kategoriebild" titel="'.$veranstaltungskategorie[$iTmpZehler]->bezeichnung.'" src="'.$cURL.'" />';
				}
		
		?>
			     
		<form accept-charset="UTF-8" name="selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
		<tr <?php echo ($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ');?> >
	
			<td>
				<input class="pflichtfeld" type="text" name="veranstaltungskategorie_kurzbz" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz)?$veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz:constEingabeFehlt);?>" size="17" maxlength="16" onblur="if (this.value=='') {this.value=this.defaultValue;}" onfocus="if (this.value=='<?php echo constEingabeFehlt; ?>') { this.value='';}" />
				<input class="ausblenden" name="veranstaltungskategorie_kurzbz_old" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz)?$veranstaltungskategorie[$iTmpZehler]->veranstaltungskategorie_kurzbz:'');?>" />
			</td>
	
			<td><input class="pflichtfeld" name="bezeichnung" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]->bezeichnung)?$veranstaltungskategorie[$iTmpZehler]->bezeichnung:'');?>" size="20" maxlength="63" /></td>

			<td><input  <?php echo (isset($veranstaltungskategorie[$iTmpZehler]->farbe)?' style="background-color:#'.$veranstaltungskategorie[$iTmpZehler]->farbe.';"':'');?> name="farbe" onchange="if (this.value=='') {this.style.backgroundColor='transparent';} else {this.style.backgroundColor='#' + this.value;}" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]->farbe)?$veranstaltungskategorie[$iTmpZehler]->farbe:'');?>" size="7" maxlength="6" /></td>

			<td>
				 <input size="8" maxlength="140" type="file" id="uploadBild" name="uploadBild" alt="suche" title="<?php echo $p->t("global/suchen");?>" style="font-size:xx-small;" />

				 <input class="ausblenden" name="bild" value="<?php echo (isset($veranstaltungskategorie[$iTmpZehler]->bild)?$veranstaltungskategorie[$iTmpZehler]->bild:'');?>" />				
			</td>

			<td>
				<input class="ausblenden" size="10" name="work" value="?" />
				<?php echo (isset($veranstaltungskategorie[$iTmpZehler]->bild_image)?$veranstaltungskategorie[$iTmpZehler]->bild_image:'');?>
			</td>
			
			<td class="cursor_hand" onclick="if (window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.veranstaltungskategorie_kurzbz.value=='<?php echo constEingabeFehlt; ?>')  {window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.veranstaltungskategorie_kurzbz.focus();return false;}; if (window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.bezeichnung.value.length<1) {window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.bezeichnung.focus();return false;}; window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='save';window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" ><?php echo $p->t("global/speichern");?> <img height="14px" border="0" alt="sichern - save" src="../../../skin/images/date_edit.png" /></td>
			<td <?php echo ($iTmpZehler<0?' class="ausblenden" ':''); ?> class="cursor_hand" onclick="window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='del';window.document.selJahresplanVeranstaltung<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" ><?php echo $p->t("global/löschen");?> <img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/date_delete.png" /></td>
		</tr>
		</form>
		<?php } ?>				
		<tr class="footer_zeile"><td colspan="7"><?php echo $p->t("eventkalender/kurzbezeichnungenMitEinemStern");?>.</td></tr>
	</table>
	<div><table><tr><td style="color:black;background-color:none ;border : 1px solid Black;border-style:dashed;">&nbsp;&nbsp;&nbsp;</td><td><?php echo $p->t("eventkalender/pflichtfeld");?></td></tr></table></div>

	<?php echo $error; ?>
</body>
</html>


