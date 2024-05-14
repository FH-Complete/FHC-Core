<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/firma.class.php');
require_once('../../include/standort.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/person.class.php');	
require_once('../../include/nation.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
// Benutzerinformation
$user=get_uid();

// Zugriffsrechte pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('basis/firma',null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

// Parameter
$firma_id_geloescht = (isset($_REQUEST['firma_id_geloescht'])?$_REQUEST['firma_id_geloescht']:'');
if (empty($firma_id_geloescht))
	exit('Es fehlt welche Firma gel&ouml;scht werden soll !');
$firma_id_bleibt = (isset($_REQUEST['firma_id_bleibt'])?$_REQUEST['firma_id_bleibt']:'');
if (empty($firma_id_bleibt))
	exit('Es fehlt welche Firma bleibt !');
if ($firma_id_geloescht==$firma_id_bleibt)
	exit('Zusammenlegen nicht m&ouml;glich ! Firmendaten sind gleich ');
$work = (isset($_REQUEST['work'])?$_REQUEST['work']:'');

//----------------------------------------------------------------------------------------
// wenn Work ok (Voranzeige) PopUp Anzeige , und Ende / Funktion am Ende
//----------------------------------------------------------------------------------------
if (empty($work) )
{
	if (!$standort=getFirmaUndStandorte($firma_id_geloescht,$firma_id_bleibt))
		exit("Fehler beim Ermitteln der Daten! Firma $firma_id_geloescht die geloescht werden soll, und Firma $firma_id_bleibt die bleibt.");
	$geloescht=$standort['geloescht'];
	$bleibt=$standort['bleibt'];		

}
	
?>	
<html>
<head>
	<title>Firmen zusammenlegen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script src="../../vendor/components/jqueryui/jquery-ui.min.js" type="text/javascript"></script>

	<script type="text/javascript" language="JavaScript1.2">
	function show_firma_bleibt(work)
	{
	
		
		var wohin='detailInfoAnzeigeZusammenlegen';
	    $("div#detailInfoZusammenlegen").show("slow"); // div# langsam oeffnen
		
		$("div#"+wohin).html('<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >');
		var formdata = $('form#firmaform').serialize(); 
	
	
		$.ajax
		(
			{
				type: "POST", timeout: 3500,	
				dataType: 'html',
				url: 'firma_zusammen_details.php',
				data: formdata+'&work='+work,
				error: function()
				{
		   			$("div#"+wohin).html("error ");
					return;								
				},		
				success: function(phpData)
				{
			   		$("div#"+wohin).html(phpData);
				}
			}
		);
			
		return;
	}

	-->
	</script>	
	<style type="text/css">
	<!--
		div.detailInfoZusammenlegen {width:90%;display:none;padding: 5px 5px 5px 5px;border: 1px solid Black;empty-cells : hide;text-align:center;vertical-align: top;z-index: 99;background-color: white; position:absolute;}
		div.detailInfoCloseZusammenlegen {border: 7px outset #008381;padding: 0px 10px 0px 10px;}
		div.detailInfoAnzeigeZusammenlegen {font-size:medium;text-align:left;background-color: #F5F5F5;padding: 15px 15px 15px 15px;}
	-->
	</style>
</head>
<body>	
<?php 
	if (!empty($work) && $work=='ok' )
	{
		if (voransicht($_REQUEST))
			echo "<br> Erfolgreicher Test";
		exit;
	}	

	if (!empty($work) && $work=='save' )
	{
		if (zusammenlegen($_REQUEST))
			echo "<br> Erfolgreich Zusammengelegt";
		exit;
	}	
?>

	<h2>Firmen zusammenlegen</h2>
	<input type="button" name="Voranzeigen" value="voranzeigen zusammenlegen" onclick="show_firma_bleibt('ok');">	
	<input type="button" name="Zusammenlegen" value="start zusammenlegen" onclick="show_firma_bleibt('save');">	
	<div id="detailInfoZusammenlegen" class="detailInfoZusammenlegen">
		<div style="text-align:right;color:#000; cursor: pointer; cursor: hand;" onclick="document.getElementById('detailInfoZusammenlegen').style.display = 'none';">
			<b id="detailInfoCloseZusammenlegen">schliessen  <img border="0" src="../../skin/images/cross.png" title="schliessen">&nbsp;</b></div>
			<br>		
			<div id="detailInfoAnzeigeZusammenlegen" class="detailInfoAnzeigeZusammenlegen">&nbsp;</div>
			<br>
		</div>
	</div>
	
	
<form id="firmaform">

	<input style="display:none;" type="Text" name="firma_id_geloescht" value="<?php echo $firma_id_geloescht;?>">
	<input style="display:none;" type="Text" name="firma_id_bleibt" value="<?php echo $firma_id_bleibt;?>">

	<div id="firma_container">
	<table width="100%">
		<tr>

	<!-- TEIL LINKS das wird geloescht -->			
			<td width="50%" valign="top">
			  <fieldset style="background-color:#ff978f;">
			    <legend style="background-color:#ff978f;"><?php echo $geloescht->firma_id.' '.$geloescht->firmentyp_kurzbz.' '.$geloescht->name ;?> wird gel&ouml;scht</legend>
				  <fieldset style="background-color:#ffeac9;">
				    <legend style="background-color:#ffeac9;">Standorte</legend>
					<table>					
					<?php
						if ($geloescht->standorte)
						{
							foreach ($geloescht->standorte as $standort)
							{
							
								echo '<tr><td colspan="2"><fieldset style="background-color:#ffdab9;"><legend style="background-color:#ffdab9;">'.$standort->standort_id.' '.($standort->bezeichnung==NULL?$standort->kurzbz:$standort->bezeichnung).'</legend><table>';
								echo '<tr><td><input checked class="checkbox" value="'.$standort->standort_id.'" id="standort" name="standort[]" type="checkbox" ></td><td>'.(($standort->bezeichnung==NULL||$standort->bezeichnung=="")?$standort->kurzbz:$standort->bezeichnung).'</td></tr>';	
								echo '<tr><td colspan="2"><fieldset style="background-color:#ffe4e1;"><legend style="background-color:#ffe4e1;">Adressen zu '.$standort->kurzbz.'</legend><table>';
								if ($standort->adresse)
								{			
									echo '<tr><td valign="top">'.$standort->adresse->strasse.'<br>'.$standort->adresse->plz.' '.$standort->adresse->ort.'</td></tr>';
								}	
								echo '</table></fieldset></td></tr>';

								echo '<tr><td colspan="2"><fieldset style="background-color:#ffe4e1;"><legend style="background-color:#ffe4e1;">Direktekontakte zu '.$standort->kurzbz.'</legend><table>';
								//if ($standort->kontakt->result)
								if ($standort->kontakt)
								{			
									foreach ($standort->kontakt->result as $kontakt)
									{								
										echo '<tr><td valign="top"><input checked title="'.$kontakt->kontakt_id.'" value="'.$kontakt->kontakt_id.'" id="kontakt_'.$kontakt->kontakt_id.'" name="kontakt['.$standort->standort_id.'][]" type="Checkbox" ></td><td valign="top">'.$kontakt->kontakttyp.' '.$kontakt->kontakt.'<br>'.$kontakt->anmerkung.'</td></tr>';
									}
								}	
								echo '</table></fieldset></td></tr>';
								
								echo '<tr><td colspan="2"><fieldset style="background-color:#ffe4e1;"><legend style="background-color:#ffe4e1;">Personenkontakte zu '.$standort->kurzbz.'</legend><table>';
								if ($standort->personfunktion->result)
								{			
									foreach ($standort->personfunktion->result as $personfunktion)
									{								
										echo '<tr><td valign="top"><input checked value="'.$personfunktion->personfunktionstandort_id.'" id="personfunktionstandort_'.$personfunktion->personfunktionstandort_id.'" name="personfunktionstandort['.$standort->standort_id.'][]"  type="Checkbox" ></td><td valign="top">'.$personfunktion->funktion_kurzbz.'<br>'.$personfunktion->position.'<br>'.$personfunktion->anrede.'</td></tr>';
									}
								}	
								echo '</table></fieldset></td></tr>';
									
									
							echo '</table></fieldset></td></tr>';

							}
						}
					 ?>	
						<tr><td>&nbsp;</td></tr>
					</table>
				 </fieldset>					
			 </fieldset>

			 <fieldset style="background-color:#ff978f;">
			    <legend style="background-color:#ff978f;">Organisation</legend>
					<table>					
					<?php
					
						if ($geloescht->firmaorganisationseinheit)
						{
							foreach ($geloescht->firmaorganisationseinheit as $firmaorganisationseinheit)
							{
								//var_dump($firmaorganisationseinheit);
								echo '<tr><td><input checked="checked" value="'.$firmaorganisationseinheit->firma_organisationseinheit_id.'" id="firmaorganisationseinheit" name="firmaorganisationseinheit[]" type="Checkbox" ></td><td>'.$firmaorganisationseinheit->bezeichnung.'</td></tr>';
								echo '<tr><td></td><td>'.$firmaorganisationseinheit->fobezeichnung.'</td></tr>';
								echo '<tr><td></td><td>KNr.: '.$firmaorganisationseinheit->kundennummer.'</td></tr>';
							}
						}
					 ?>
						<tr><td>&nbsp;</td></tr>
					</table>
			 </fieldset>	
		 
			</td>
	<!-- TEIL RECHTS das bleibt -->			
			<td width="50%" valign="top">
			  <fieldset style="background-color:#B6ffAf;">
			    <legend style="background-color:#B6ffAf;"><?php echo $bleibt->firma_id.' '.$bleibt->firmentyp_kurzbz.' '.$bleibt->name ;?> bleibt</legend>
				 
				  <fieldset style="background-color:#c9ffd0;">
				    <legend style="background-color:#c9ffd0;">Standorte</legend>
					<table>					
					<?php
						if ($bleibt->standorte)
						{
							foreach ($bleibt->standorte as $standort)
							{
								echo '<tr><td colspan="2"><fieldset style="background-color:#c3ffb9;"><legend style="background-color:#c3ffb9;">'.$standort->standort_id.' '.$standort->bezeichnung.'</legend><table>';
									echo '<tr><td><input checked="checked" value="'.$standort->standort_id.'" id="standort" name="standort[]" type="Checkbox" ></td><td>'.$standort->bezeichnung.'</td></tr>';	
									echo '<tr><td colspan="2"><fieldset style="background-color:#e3ffe1;"><legend style="background-color:#e3ffe1;">Adressen zu '.$standort->kurzbz.'</legend><table>';
									if ($standort->adresse)
									{			
										echo '<tr><td valign="top">'.$standort->adresse->strasse.'<br>'.$standort->adresse->plz.' '.$standort->adresse->ort.'</td></tr>';
									}	
									echo '</table></fieldset></td></tr>';
	
	
									echo '<tr><td colspan="2"><fieldset style="background-color:#e3ffe1;"><legend style="background-color:#e3ffe1;">Direktekontakte zu '.$standort->kurzbz.'</legend><table>';
									//if ($standort->kontakt->result)
									if ($standort->kontakt)
									{			
										foreach ($standort->kontakt->result as $kontakt)
										{								
											$person=($kontakt->anrede?$kontakt->anrede.' ':'').($kontakt->titelpost?$kontakt->titelpost.' ':'').($kontakt->titelpre?$kontakt->titelpre.' ':'').($kontakt->nachname?$kontakt->nachname.' ':'').($kontakt->vorname?$kontakt->vorname.' ':'').($kontakt->vornamen?$kontakt->vornamen.' ':'');
											echo '<tr><td valign="top"><input checked="checked" title="'.$kontakt->kontakt_id.'" value="'.$kontakt->kontakt_id.'" id="kontakt_'.$kontakt->kontakt_id.'" name="kontakt['.$standort->standort_id.'][]" type="Checkbox" ></td><td valign="top">'.$kontakt->kontakttyp.' '.$kontakt->kontakt.'<br>'
												.($person?$person.'<br>':'').$kontakt->anmerkung.'</td></tr>';
										}
									}	
									echo '</table></fieldset></td></tr>';


									echo '<tr><td colspan="2"><fieldset style="background-color:#e3ffe1;"><legend style="background-color:#e3ffe1;">Personenkontakte zu '.$standort->kurzbz.'</legend><table>';
									if ($standort->personfunktion->result)
									{		
										//var_dump($standort->personfunktion);	
										foreach ($standort->personfunktion->result as $personfunktion)
										{								
											echo '<tr><td valign="top"><input  checked="checked" value="'.$personfunktion->personfunktionstandort_id.'" id="personfunktionstandort_'.$personfunktion->personfunktionstandort_id.'" name="personfunktionstandort['.$standort->standort_id.'][]" type="Checkbox" ></td><td valign="top">'.$personfunktion->funktion_kurzbz.'<br>'.$personfunktion->position.'<br>Anrede: '.$personfunktion->anrede.'<br>'.trim($personfunktion->titelpre.' '.$personfunktion->vorname.' '.$personfunktion->nachname.' '.$personfunktion->titelpost).'</td></tr>';
										}
									}	
									echo '</table></fieldset></td></tr>';


							echo '</table></fieldset></td></tr>';								
							}
						}
					 ?>	
						<tr><td>&nbsp;</td></tr>
					</table>
				 </fieldset>	
			 </fieldset>	

			 <fieldset style="background-color:#B6ffAf;">
				    <legend style="background-color:#B6ffAf;">Organisation</legend>
					<table>					
					<?php
						if ($bleibt->firmaorganisationseinheit)
						{
							foreach ($bleibt->firmaorganisationseinheit as $firmaorganisationseinheit)
							{
								echo '<tr><td><input checked="checked" value="'.$firmaorganisationseinheit->firma_organisationseinheit_id.'" id="firmaorganisationseinheit" name="firmaorganisationseinheit[]" type="Checkbox" ></td><td>'.$firmaorganisationseinheit->bezeichnung.'</td></tr>';	
								echo '<tr><td></td><td>'.$firmaorganisationseinheit->fobezeichnung.'</td></tr>';
								echo '<tr><td></td><td>KNr.: '.$firmaorganisationseinheit->kundennummer.'</td></tr>';
							}
						}
					 ?>	
						<tr><td>&nbsp;</td></tr>
					</table>
			 </fieldset>	
			 
			</td>
		</tr>
	</table>
</div>
</form>


</body>
</html>
<?php 
/**
 * Voransicht der Zusammenlegung
 *
 * @param $firmendaten
 */
function voransicht($firmendaten)
{
	$firma_id_bleibt = (isset($firmendaten['firma_id_bleibt'])?$firmendaten['firma_id_bleibt']:'');
	$standort = (isset($firmendaten['standort'])?$firmendaten['standort']:array());
	$kontakt = (isset($firmendaten['kontakt'])?$firmendaten['kontakt']:array());
	$personfunktionstandort = (isset($firmendaten['personfunktionstandort'])?$firmendaten['personfunktionstandort']:array());
	$firmaorganisationseinheit = (isset($firmendaten['firmaorganisationseinheit'])?$firmendaten['firmaorganisationseinheit']:array());

	//Überprüfung auf doppelte Organisationseinheiten
	$firmaorganisationseinheit_check=array();
	for ($i=0;$i<count($firmaorganisationseinheit);$i++)
	{
		$firmaorganisationseinheit_obj->result[$i] = new firma();
		if($firmaorganisationseinheit_obj->result[$i]->load_firmaorganisationseinheit($firmaorganisationseinheit[$i]))
		{
			if (isset($firmaorganisationseinheit_obj->result[$i]))
			{
				if(array_key_exists($firmaorganisationseinheit_obj->result[$i]->oe_kurzbz,$firmaorganisationseinheit_check))
				{
					exit("<b style='color:red'>Es wurden Zuordnungen von Organisationseiheiten mehrfach ausgewählt!<br>Bitte Auswahl korrigieren.</b>");
				}
				$firmaorganisationseinheit_check[$firmaorganisationseinheit_obj->result[$i]->oe_kurzbz]=$firmaorganisationseinheit[$i];
			}
		}
	}		
	
	if (is_array($standort) && count($standort))
	{
		// Array mit Standort als Key fuer Kontrolle der Adressen ob der Standort noch gueltig ist oder neu zugeordnet wird
		$standort_check=array();
		for ($i=0;$i<count($standort);$i++)
			$standort_check[$standort[$i]]=$standort[$i];
			
		// Pruefen ob Kontakte noch einen alten Standort zugewiessen ist	
	}
	
	// Pruefen ob Kontakte noch einen alten Standort zugewiessen ist	
	$kontakt_ok=array();
	if (is_array($kontakt) && count($kontakt))
	{
		foreach ($kontakt as $key => $val)
		{
			$standort_id=$key;
			// Kontakt wird dem ersten Standort von Firma bleibt zugeordnet
			if (!isset($standort_check[$standort_id]) && isset($standort[0]) )
				$standort_id=$standort[0];
			elseif (!isset($standort_check[$standort_id]))
				continue;	
			for ($ii=0;$ii<count($val);$ii++)
			{
				$kontakt_ok[$standort_id][]=$val[$ii];	
				if ($standort_id==$key)
					continue; // Keine Aenderung nechsten Datensatz pruefen
			}
		}
		$kontakt=$kontakt_ok;
		$kontakt_ok=null;
	}				

		
	// Pruefen ob personfunktionstandorte noch einen alten Standort zugewiessen ist	
	$personfunktionstandort_ok=array();
	if (is_array($personfunktionstandort) && count($personfunktionstandort))
	{
		foreach ($personfunktionstandort as $key => $val)
		{
			$standort_id=$key;
			if (!isset($standort_check[$standort_id]) && isset($standort[0]) )
				$standort_id=$standort[0];
			elseif (!isset($standort_check[$standort_id]))
				continue;	
			for ($ii=0;$ii<count($val);$ii++)
			{
				$personfunktionstandort_ok[$standort_id][]=$val[$ii];	
				if ($standort_id==$key)
					continue; // Keine Aenderung nechsten Datensatz pruefen
			}
		}				
		$personfunktionstandort=$personfunktionstandort_ok;
		$personfunktionstandort_ok=null;
	}

	if (is_array($firmaorganisationseinheit) && count($firmaorganisationseinheit))
	{
		// Array mit Standort als Key fuer Kontrolle der Adressen ob der Standort noch gueltig ist oder neu zugeordnet wird
		$firmaorganisationseinheit_check=array();
		$firmaorganisationseinheit_ok=array();
		for ($i=0;$i<count($firmaorganisationseinheit);$i++)
		{
		
			$firmaorganisationseinheit_obj->result[$i] = new firma();
				
				
			if($firmaorganisationseinheit_obj->result[$i]->load_firmaorganisationseinheit($firmaorganisationseinheit[$i]))
			{
				if (isset($firmaorganisationseinheit_obj->result[$i]))
				{
						$firmaorganisationseinheit_check[$firmaorganisationseinheit_obj->result[$i]->oe_kurzbz]=$firmaorganisationseinheit[$i];
						//echo $firmaorganisationseinheit_obj->result[$i]->oe_kurzbz."  ".$firmaorganisationseinheit_check[$firmaorganisationseinheit_obj->result[$i]->oe_kurzbz]."<br>";
				}
			}	
			else 
				echo "<br>".$firmaorganisationseinheit_obj->errormsg;	
		}	
		//var_dump($firmaorganisationseinheit_check);
		foreach ($firmaorganisationseinheit_check as $key => $val)
		{
			$firmaorganisationseinheit_ok[]=$val;
		}

		$firmaorganisationseinheit=$firmaorganisationseinheit_ok;				
		$firmaorganisationseinheit_ok=null;
		$firmaorganisationseinheit_check=null;
	}
	
	$firma = new firma();
	if(!$firma->load($firma_id_bleibt))
		exit('Welche Firma bleibt Fehler :'.$firma->errormsg);

	echo '	
	  <fieldset style="background-color:#B6ffAf;">
		<legend style="background-color:#B6ffAf;">Informationen nach der Zusammenlegung </legend>
		  <fieldset style="background-color:#c3ffb9;">
	    	<legend style="background-color:#c3ffb9;">Firma '.$firma->firma_id.'</legend>';
			
	echo '<table>';
	echo '<tr><td>'.$firma->firmentyp_kurzbz.' '.$firma->name
	 .($firma->anmerkung?'<br>'.$firma->anmerkung:'')
	 .'<br>Steuernummer:&nbsp;'.$firma->steuernummer .'&nbsp;Finanzamt:&nbsp;';
	// Finanzamt anzeige und suche
	if ($firma->finanzamt)
	{
		$firma_finanzamt = new firma();
		if ($firma_finanzamt->load($firma->finanzamt))	
			echo $firma_finanzamt->name;
	}
	echo '</td></tr>';
	
	echo "<tr><td>"
		."Aktiv:<input disabled ".($firma->aktiv?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')." type='checkbox' name='aktiv' ".($firma->aktiv?'checked':'').">"
		."&nbsp;Gesperrt:<input disabled ".($firma->gesperrt?' style="background-color: #FFF4F4;" ':' style="background-color: #E3FDEE;" ')." type='checkbox' name='gesperrt' ".($firma->gesperrt?'checked':'').">"
		."&nbsp;Schule:<input disabled ".($firma->schule?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')."  type='checkbox' name='schule' ".($firma->schule?'checked':'').">"
		."</td></tr>";
	echo '</table>';

	if (!is_array($standort) || !count($standort))
		echo '<font color="red">Achtung! Keinen Standort gefunden ! - Verarbeiten wird nicht m&ouml;glich sein</font><br>';
	else
	{
		foreach ($kontakt as $key => $val)
		{
			echo '<fieldset style="background-color:#e3ffe1;">';
		    echo '<legend style="background-color:#e3ffe1;">Standort  '.$key.' der Firma '. $firma->name.'</legend>';
				
			$standort_obj = new standort();
			$standort_obj->result=array();
			if ($standort_obj->load($key))
				echo '<h3>'.$standort_obj->kurzbz.', '.$standort_obj->bezeichnung.'</h3>';
			else
				echo $standort_obj->errormsg.'<br>';
					
			// Kontakt zum Standort
			if (!isset($kontakt[$key]) || !is_array($kontakt[$key]) || !count($kontakt[$key]))
			{
				echo '<font color="red">Keine Kontakte zum Standort !</font><br>';
			}	
			else
			{
				foreach ($kontakt[$key] as $keys => $vals)
				{
				// Kontakte zum Standort
				$kontakt_obj = new kontakt();
				if($kontakt_obj->load($vals))
					echo '<b>Kontakt</b> '
					."&nbsp;Zustellung:<input disabled ".($kontakt_obj->zustellung?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')."  type='checkbox' name='schule' ".($kontakt_obj->zustellung?'checked':'').">&nbsp;"									
					.  $vals.' '.$kontakt_obj->kontakttyp.' '.$kontakt_obj->kontakt 
					.'&nbsp;'.$kontakt_obj->beschreibung
					.'<br>';
				else
					echo $kontakt_obj->errormsg.'<br>';
				 }
			}	 

			// Personfunktionstandort zum Standort
			if (!isset($personfunktionstandort[$key]) || !is_array($personfunktionstandort[$key]) || !count($personfunktionstandort[$key]))
			{
				echo '<font color="red">Keine Personen mit Funktionen zum Standort !</font><br>';
			}	
			else
			{
				foreach ($personfunktionstandort[$key] as $keys => $vals)
				{
					// Personfunktion zum Standort
					$personfunktion_obj = new person(); 
					if($personfunktion_obj->load_personfunktion('','','','',$vals))
					{
						//var_dump($personfunktion_obj);
						echo '<b>Personen und Funktion</b> '
						.  $vals.' '.$personfunktion_obj->result[0]->funktion_kurzbz.' '.$personfunktion_obj->result[0]->position.' '.$personfunktion_obj->result[0]->anrede.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($personfunktion_obj->result[0]->titelpre.' '.$personfunktion_obj->result[0]->vorname.' '.$personfunktion_obj->result[0]->nachname.' '.$personfunktion_obj->result[0]->titelpost)
						.'<br>';
					}
					else
					{
						echo $personfunktion_obj->errormsg.'<br>';
					}
				 }
			}	 
			echo '</fieldset>';
		}			 
	} 

	echo '<fieldset style="background-color:#e3ffe1;">
			<legend style="background-color:#e3ffe1;">Organisationseinheit zur Firma  '.$firma->name.'</legend>';
					
					 
	if (isset($firmaorganisationseinheit) && is_array($firmaorganisationseinheit) && count($firmaorganisationseinheit) ) 
	{
		$i=0;
		foreach ($firmaorganisationseinheit as $key => $val)
		{
			$firmaorganisationseinheit_obj->result[$i] = new firma();
			$bleibt->firmaorganisationseinheit=array();
			if(!$firmaorganisationseinheit_obj->result[$i]->load_firmaorganisationseinheit($val))
			{
				echo $firmaorganisationseinheit_obj->errormsg.'<br>';
			}	
			else if ($firmaorganisationseinheit_obj->result[$i])
			{
				echo $firmaorganisationseinheit_obj->result[$i]->firma_organisationseinheit_id.' <b>'.$firmaorganisationseinheit_obj->result[$i]->oe_kurzbz.'</b><br>'.$firmaorganisationseinheit_obj->result[$i]->bezeichnung.', KNr.: '.$firmaorganisationseinheit_obj->result[$i]->kundennummer.'<br>';
			}
			$i++;	
		}	
	}	 
	else
	{
		echo '<font color="red">Keine Organisationseinheit zur Firma !</font><br>';
	} 

	echo '</fieldset>
	  </fieldset>
	</fieldset>';

	return true;
}

/**
 * Zusammenlegen der Firmen
 *
 * @param unknown_type $firmendaten
 * @return unknown
 */
function zusammenlegen($firmendaten)
{
	global $db, $user;
			
	$error=false;
	$firma_id_geloescht = (isset($firmendaten['firma_id_geloescht'])?$firmendaten['firma_id_geloescht']:'');
	$firma_id_bleibt = (isset($firmendaten['firma_id_bleibt'])?$firmendaten['firma_id_bleibt']:'');
	$standort = (isset($firmendaten['standort'])?$firmendaten['standort']:array());
	$kontakt = (isset($firmendaten['kontakt'])?$firmendaten['kontakt']:array());
	$personfunktionstandort = (isset($firmendaten['personfunktionstandort'])?$firmendaten['personfunktionstandort']:array());
	$firmaorganisationseinheit = (isset($firmendaten['firmaorganisationseinheit'])?$firmendaten['firmaorganisationseinheit']:array());

	//Überprüfung auf doppelte Organisationseinheiten
	$firmaorganisationseinheit_check=array();
	for ($i=0;$i<count($firmaorganisationseinheit);$i++)
	{
		$firmaorganisationseinheit_obj->result[$i] = new firma();
		if($firmaorganisationseinheit_obj->result[$i]->load_firmaorganisationseinheit($firmaorganisationseinheit[$i]))
		{
			if (isset($firmaorganisationseinheit_obj->result[$i]))
			{
				if(array_key_exists($firmaorganisationseinheit_obj->result[$i]->oe_kurzbz,$firmaorganisationseinheit_check))
				{
					exit("<b style='color:red'>Es wurden Zuordnungen von Organisationseiheiten mehrfach ausgewählt!<br>Bitte Auswahl korrigieren.</b>");
				}
				$firmaorganisationseinheit_check[$firmaorganisationseinheit_obj->result[$i]->oe_kurzbz]=$firmaorganisationseinheit[$i];
			}
		}
	}
	
	if(!$db->db_query('BEGIN;'))
		return 'Fehler beim Starten der Transaktion';
	
	// Ermitteln der Standorte zu den Firmen - geloescht und bleibt. Wichtiger Teil zum ermitteln welche Standorte entfernt werden sollen
	$standorte_vorhanden=array();
	$standort_obj = new standort();
	$standort_obj->result=array();
	if ($standort_obj->load_firma($firma_id_geloescht))
	{
		foreach ($standort_obj->result as $key => $val)
			$standorte_vorhanden[$val->standort_id]=$val->standort_id;
	}
	$standort_obj = new standort();
	$standort_obj->result=array();
	if ($standort_obj->load_firma($firma_id_bleibt))
	{
		foreach ($standort_obj->result as $key => $val)
			$standorte_vorhanden[$val->standort_id]=$val->standort_id;
	}
	
	$standort_check=array();
	if (is_array($standort) && count($standort))
	{
		// Array mit Standort als Key fuer Kontrolle der Adressen ob der Standort noch gueltig ist oder neu zugeordnet wird
		$standort_check=array();
		for ($i=0;$i<count($standort);$i++)
		{
			$standort_check[$standort[$i]]=$standort[$i];
			$standorte_vorhanden[$standort[$i]]='X';
			
			$standort_obj = new standort();
			$standort_obj->result=array();
			if($standort_obj->load($standort[$i]))
			{
				// Standortwechsel zu anderer Firma
				if ($standort_obj->firma_id!=$firma_id_bleibt)
				{
					echo '<h5>Wechsel Standort '.$standort[$i].' von Firma ID '.$standort_obj->firma_id.' auf => Firma ID '.$firma_id_bleibt.'</h5>';
					$standort_obj->new=false;
					$standort_obj->firma_id=$firma_id_bleibt;
					if (!$standort_obj->save())
					{
						$error=true;
						echo 'Standort: '.$standort_obj->errormsg.'<br>';
					}
				}
			}
			else 
			{
				$error=true;
				echo 'Standort: '.$standort_obj->errormsg;
			}
		}
	} // Ende Standort 		
	
	if($error)
	{
		$db->db_query('ROLLBACK;');
		return false;
	}
	
	// Pruefen ob Kontakte noch einen alten Standort zugewiessen ist	
	$kontakt_check=array();
	$kontakt_ok=array();
	if (is_array($kontakt) && count($kontakt))
	{
		foreach ($kontakt as $key => $val)
		{
			$standort_id=$key;
			// Kontakt wird dem ersten Standort von Firma bleibt zugeordnet
			if (!isset($standort_check[$standort_id]) && isset($standort[0]) )
				$standort_id=$standort[0];
			elseif (!isset($standort_check[$standort_id]))
				continue;	
				
			for ($ii=0;$ii<count($val);$ii++)
			{
				$kontakt_check[$val[$ii]]=$val[$ii];
				$kontakt_ok[$standort_id][]=$val[$ii];	
				if ($standort_id!=$key)
					echo '<h5>Wechsel Kontakt '.$val[$ii] .' von Standort ID '.$key.' auf => Standort ID '.$standort_id.'</h5>';
				else
					continue; // Keine Aenderung nechsten Datensatz pruefen

				$qry = "UPDATE public.tbl_kontakt set updateamum= now(),updatevon='".addslashes($user)."',standort_id='".$standort_id."' WHERE kontakt_id='".$val[$ii]."'";
				$db->errormsg='';
				if(!$db->db_query($qry))
				{
					$error=true;
					echo 'Fehler beim Aender der Kontaktdaten';
				}
			}
		}
		$kontakt=$kontakt_ok;
		$kontakt_ok=null;
	}				
	
	if($error)
	{
		$db->db_query('ROLLBACK;');
		return false;
	}
	
	// Pruefen ob personfunktionstandorte noch einen alten Standort zugewiessen ist	
	$personfunktionstandort_check=array();
	$personfunktionstandort_ok=array();
	if (is_array($personfunktionstandort) && count($personfunktionstandort))
	{
		foreach ($personfunktionstandort as $key => $val)
		{
			
			$standort_id=$key;
			if (!isset($standort_check[$standort_id]) && isset($standort[0]) )
				$standort_id=$standort[0];
			elseif (!isset($standort_check[$standort_id]))
				continue;	
			
			
			for ($ii=0;$ii<count($val);$ii++)
			{
				$personfunktionstandort_check[$val[$ii]]=$val[$ii];
				$personfunktionstandort_ok[$standort_id][]=$val[$ii];	

				if ($standort_id!=$key)
					echo '<h5>Wechsel Personfunktionstandort '.$val[$ii] .' von Standort ID '.$key.' auf => Standort ID '.$standort_id.'</h5>';
				else
					continue; // Keine Aenderung nechsten Datensatz pruefen

				$qry = "UPDATE public.tbl_personfunktionstandort SET standort_id='".$standort_id."' WHERE personfunktionstandort_id='".$val[$ii]."'";
				$db->errormsg='';
				if(!$db->db_query($qry))
				{
					echo 'Fehler beim Aendern der Personenzuordnung';
					$error = true;
				}
			}
		}
		$personfunktionstandort=$personfunktionstandort_ok;
		$personfunktionstandort_ok=null;
	}
	
	if($error)
	{
		$db->db_query('ROLLBACK;');
		return false;
	}
	
	// Welche Kontakte werden entfernt
	if (is_array($standorte_vorhanden) && count($standorte_vorhanden))
	{
		reset($standorte_vorhanden);
		// Array mit Standort als Key fuer Kontrolle der Adressen ob der Standort noch gueltig ist oder neu zugeordnet wird
		foreach ($standorte_vorhanden as $key => $val)
		{
			if (!is_numeric($val)) // Kennzeichen ob bereits verarbeitet
				continue;
				
			$qry = "DELETE FROM public.tbl_kontakt WHERE standort_id='".$val."'";
			$db->errormsg='';
			if(!$db->db_query($qry))
			{
				$error=true;
				echo 'Fehler beim Aendern der Kontakte';
			}

			$qry = "DELETE FROM public.tbl_personfunktionstandort WHERE standort_id='".$val."'";
			$db->errormsg='';
			if(!$db->db_query($qry))
			{
				$error=true;
				echo 'Fehler beim Aendern der Personenzuordnung';
			}

			$standort_obj = new standort();
			$standort_obj->result=array();
			if (!$standort_obj->load($val))
			{
				$error=true;
				echo  'Fehler beim lesen Adresse zum Standort '.$val.' '.$standort_obj->errormsg.'<br>';
			}


			if ($standort_obj->result)
			{	
				foreach ($standort_obj->result as $keys => $vals)
				{
					$qry = "DELETE FROM public.tbl_standort WHERE standort_id='".$val."'";
					if(!$db->db_query($qry))
					{
						$error=true;
						echo 'Fehler beim Aendern des Standorts';
					}

					if($vals->adresse_id!='')
					{
						$qry = "DELETE FROM public.tbl_adresse WHERE adresse_id='".$vals->adresse_id."'";
						if(!$db->db_query($qry))
						{
							$error=true;
							echo 'Fehler beim Aendern der Adresse';
						}
					}
				}
			}
		}
	}
	
	if($error)
	{
		$db->db_query('ROLLBACK;');
		return false;
	}	
	
	if (isset($firmaorganisationseinheit) && is_array($firmaorganisationseinheit) && count($firmaorganisationseinheit) ) 
	{
		$i=0;
		foreach ($firmaorganisationseinheit as $key => $firma_organisationseinheit_id)
		{
			$firmaorganisationseinheit_obj->result[$i] = new firma();
			if(!$firmaorganisationseinheit_obj->result[$i]->load_firmaorganisationseinheit($firma_organisationseinheit_id))
			{
				$error=true;
				echo 'Firma - Organisationseinheit: '.$firmaorganisationseinheit_obj->errormsg.' ('.$firma_organisationseinheit_id.')<br>';
			}	
			else if ($firmaorganisationseinheit_obj)
			{
				//var_dump($firmaorganisationseinheit_obj);
				foreach ($firmaorganisationseinheit_obj->result as $keys => $vals)
				{
					// Organisation gehoert bereits zu dieser Firma
					if ($vals->firma_id==$firma_id_bleibt)
						continue;
					// gibt es die Zuornung der Oe-Einheit zur Firma schon?
					$qry_check="SELECT * FROM public.tbl_firma_organisationseinheit WHERE firma_id='".$firma_id_bleibt."' AND oe_kurzbz='".$vals->oe_kurzbz."';";
					if($db->db_num_rows($db->db_query($qry_check))==0)
					{
						//nein
						$qry='UPDATE public.tbl_firma_organisationseinheit SET '.
							'firma_id='.addslashes($firma_id_bleibt).', '.
							'updateamum= now(), '.
					     	'updatevon=\''.addslashes($user).'\' '.
							" WHERE firma_organisationseinheit_id='".addslashes($vals->firma_organisationseinheit_id)."';";
					}
					else 
					{
						//ja
						$qry='UPDATE public.tbl_firma_organisationseinheit SET '.
							'bezeichnung=\''.addslashes($vals->bezeichnung).'\', '.
							'kundennummer=\''.addslashes($vals->kundennummer).'\', '.
							'updateamum= now(), '.
					     	'updatevon=\''.addslashes($user).'\' '.
							" WHERE firma_id='".addslashes($firma_id_bleibt)."' AND oe_kurzbz='".$vals->oe_kurzbz."';";
					}
					$db->errormsg='';
					
					if($result=$db->db_query($qry))					
					{
						echo 'Organisation '.$vals->firma_organisationseinheit_id.' '.$vals->name.', '. $vals->organisationseinheittyp_kurzbz.' '.$vals->bezeichnung.' zu Firma '.$firma_id_bleibt.' zugeordnet '.'<br>';
					}
					else 
					{
						$error=true;
						echo "<br>OE: ".$qry."<br>";
						echo 'Fehler bein Zuordnen von Organisation '.$vals->firma_organisationseinheit_id.' '.$vals->name.', '. $vals->organisationseinheittyp_kurzbz.' '.$vals->bezeichnung.' zu Firma '.$firma_id_bleibt.'<br>';
						echo $db->errormsg."<br>";
					}
				}	
			}	
			$i++;
		}	
	}	 

		
	// Alle Organisationseinheiten die noch gebunden sind an "wird geloescht Firma" nach dem Zuordnen zu "bleibt Firma" loeschen
	$qry = "DELETE FROM public.tbl_firma_organisationseinheit WHERE firma_id='".$firma_id_geloescht."'";
	$db->errormsg='';
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Loeschen der Organisationseinheiten';
		$error=true;
	}

	//Alle Tags uebernehemen die der neuen Firma noch nicht zugeordnet sind
	$qry = "UPDATE public.tbl_firmatag SET firma_id='$firma_id_bleibt' WHERE firma_id='$firma_id_geloescht' AND tag NOT IN(SELECT tag FROM public.tbl_firmatag WHERE firma_id='$firma_id_bleibt');";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Uebernehmen der Tags';
		$error=true;
	}

	//Die Restlichen Tags loeschen
	$qry = "DELETE FROM public.tbl_firmatag WHERE firma_id='$firma_id_geloescht'";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Entfernen der Tags';
		$error=true;
	}
	
	//Projektarbeiten Zuordnungen umhaengen
	$qry = "UPDATE lehre.tbl_projektarbeit SET firma_id='$firma_id_bleibt' WHERE firma_id='$firma_id_geloescht'";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Aendern der Projektarbeitszuordnung';
		$error=true;
	}
	
	//Projektarbeiten Zuordnungen umhaengen
	$qry = "UPDATE public.tbl_adresse SET firma_id='$firma_id_bleibt' WHERE firma_id='$firma_id_geloescht'";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Aendern der Adresszuordnung';
		$error=true;
	}
	
	//Preinteressenten umhaengen
	$qry = "UPDATE public.tbl_preinteressent SET firma_id='$firma_id_bleibt' WHERE firma_id='$firma_id_geloescht'";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Aendern der Preinteressentzuordnung';
		$error=true;
	}
	
	//Projektarbeiten Zuordnungen umhaengen
	$qry = "UPDATE public.tbl_firma SET finanzamt='$firma_id_bleibt' WHERE finanzamt='$firma_id_geloescht'";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Aendern der Finanzamtzuordnung';
		$error=true;
	}

	//WaWi Bestellungen umhaengen
	$qry = "UPDATE wawi.tbl_bestellung SET firma_id='$firma_id_bleibt' WHERE firma_id='$firma_id_geloescht'";
	if(!$db->db_query($qry))
	{
		echo 'Fehler beim Aendern der Wawi Bestellung';
		$error=true;
	}
	// Firma loeschen
	$firma = new firma();
	if(!$firma->delete($firma_id_geloescht))
	{
		$error = true;
		echo 'Firma loeschen:'.$firma->errormsg;
	}
	
	if($error)
	{
		$db->db_query('ROLLBACK;');
		return false;
	}
	else 
	{
		$db->db_query('COMMIT;');
		return true;
	}
}

/**
 * Erimtteln der Firmen.- Standortdaten
 *
 * @param $firma_id_geloescht
 * @param $firma_id_bleibt
 */
function getFirmaUndStandorte($firma_id_geloescht,$firma_id_bleibt)	
{
	//----------------------------------------------------------------------------------------
	//  zwei Teileanzeigen a) wird geloescht b) bleibt 
	//----------------------------------------------------------------------------------------

	// -------------------------------------------------------------------------
	// Firmenstammdaten holen
	// -------------------------------------------------------------------------
	$firma = new firma();
	if(!$firma->load($firma_id_geloescht))
		exit('Firma wird gel&ouml;scht Fehler :'.$firma->errormsg);
	$geloescht=$firma;
			
	$firma = new firma();
	if(!$firma->load($firma_id_bleibt))
		exit('Welche Firma bleibt Fehler :'.$firma->errormsg);
	$bleibt=$firma;

	// -------------------------------------------------------------------------
	// Standorte je Firmenstammdaten holen
	// -------------------------------------------------------------------------
	// - wird geloescht
	$standort_obj = new standort();
	$standort_obj->result=array();
	$standort_obj->load_firma($geloescht->firma_id);
	$geloescht->standorte=array();
	if ($standort_obj->result)
	{
		$geloescht->standorte=$standort_obj->result;
		for ($i=0;$i<count($geloescht->standorte);$i++)
		{
			// Adresse zum Standort
			$adresse_obj = new adresse();
			$geloescht->standorte[$i]->adresse=array();
			if($geloescht->standorte[$i]->adresse_id && $adresse_obj->load($geloescht->standorte[$i]->adresse_id))
			{
				$geloescht->standorte[$i]->adresse=$adresse_obj;
			}
			// Kontakte zum Standort
			$kontakt_obj = new kontakt();
			$geloescht->standorte[$i]->kontakt=array();
			if($geloescht->standorte[$i]->standort_id && $kontakt_obj->load_standort($geloescht->standorte[$i]->standort_id))
			{
				$geloescht->standorte[$i]->kontakt=$kontakt_obj;
			}

			// Personen zum Standort
			$personfunktion_obj = new person();
			if(!isset($geloescht->personen[$i]))
				$geloescht->personen[$i]=new stdclass();
			$geloescht->personen[$i]->personfunktion=array();
			if($geloescht->standorte[$i]->standort_id && $personfunktion_obj->load_personfunktion($geloescht->standorte[$i]->standort_id,'',$geloescht->firma_id))
			{
				$geloescht->standorte[$i]->personfunktion=$personfunktion_obj;
			}
		}		
	}
	
	$firmaorganisationseinheit_obj = new firma();
	$geloescht->firmaorganisationseinheit=array();
	if(!$firmaorganisationseinheit_obj->get_firmaorganisationseinheit($geloescht->firma_id))
	{
		$geloescht->firmaorganisationseinheit=array();
	}
	if ($firmaorganisationseinheit_obj->result)
	{
		$geloescht->firmaorganisationseinheit=$firmaorganisationseinheit_obj->result;
	}	
		
	// - bleibt
	$standort_obj = new standort();
	$standort_obj->result=array();
	$standort_obj->load_firma($bleibt->firma_id);
	$bleibt->standorte=array();
	if ($standort_obj->result)
	{
		$bleibt->standorte=$standort_obj->result;
		for ($i=0;$i<count($bleibt->standorte);$i++)
		{
			// Adresse zum Standort
			$adresse_obj = new adresse();
			$bleibt->standorte[$i]->adresse=array();
			if($bleibt->standorte[$i]->adresse_id && $adresse_obj->load($bleibt->standorte[$i]->adresse_id))
			{
				$bleibt->standorte[$i]->adresse=$adresse_obj;
			}
			// Kontakte zum Standort
			$kontakt_obj = new kontakt();
			$bleibt->standorte[$i]->kontakt=array();
			if($bleibt->standorte[$i]->standort_id && $kontakt_obj->load_standort($bleibt->standorte[$i]->standort_id))
			{
				if(!isset($bleibt->standorte[$i]))
					$bleibt->standorte[$i] = new stdClass();
				$bleibt->standorte[$i]->kontakt=$kontakt_obj;
			}
			
			// Personen zum Standort
			$personfunktion_obj = new person();
			if(!isset($bleibt->personen[$i]))
				$bleibt->personen[$i]=new stdClass();
			$bleibt->personen[$i]->personfunktion=array();
			if($bleibt->standorte[$i]->standort_id && $personfunktion_obj->load_personfunktion($bleibt->standorte[$i]->standort_id,'',$bleibt->firma_id))
			{
				$bleibt->standorte[$i]->personfunktion=$personfunktion_obj;
			}
		}		
	}
	
	$firmaorganisationseinheit_obj = new firma();
	$bleibt->firmaorganisationseinheit=array();
	if(!$firmaorganisationseinheit_obj->get_firmaorganisationseinheit($bleibt->firma_id))
		$bleibt->firmaorganisationseinheit=array();
	if ($firmaorganisationseinheit_obj->result)
	{
		$bleibt->firmaorganisationseinheit=$firmaorganisationseinheit_obj->result;
	}	

	return $standort=array("geloescht"=>$geloescht,"bleibt"=>$bleibt);
}		
?>		

