<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>
 *          Manfred Kindl <kindlm@technikum.wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/fachbereich.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/projekt.class.php');
require_once('../../../include/phrasen.class.php'); 
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/service.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/betriebsmittelperson.class.php');

$sprache = getSprache(); 
$p=new phrasen($sprache); 
	
if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));
	
$user = get_uid();
$datum = new datum();

$zeitaufzeichnung_id = (isset($_GET['zeitaufzeichnung_id'])?$_GET['zeitaufzeichnung_id']:'');
$projekt_kurzbz = (isset($_POST['projekt'])?$_POST['projekt']:'');
$oe_kurzbz_1 = (isset($_POST['oe_kurzbz_1'])?$_POST['oe_kurzbz_1']:'');
$oe_kurzbz_2 = (isset($_POST['oe_kurzbz_2'])?$_POST['oe_kurzbz_2']:'');
$aktivitaet_kurzbz = (isset($_POST['aktivitaet'])?$_POST['aktivitaet']:'');
$von = (isset($_POST['von'])?$_POST['von']:date('d.m.Y H:i'));
$bis = (isset($_POST['bis'])?$_POST['bis']:date('d.m.Y H:i', mktime(date('H'), date('i')+10, 0, date('m'),date('d'),date('Y'))));
$beschreibung = (isset($_POST['beschreibung'])?$_POST['beschreibung']:'');
$service_id = (isset($_POST['service_id'])?$_POST['service_id']:'');
$kunde_uid = (isset($_POST['kunde_uid'])?$_POST['kunde_uid']:'');
$kartennummer = (isset($_POST['kartennummer'])?$_POST['kartennummer']:'');
$filter = (isset($_GET['filter'])?$_GET['filter']:'foo');
$alle = (isset($_POST['alle'])?(isset($_POST['normal'])?false:true):false);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>'.$p->t("zeitaufzeichnung/zeitaufzeichnung").'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
		<link href="../../../skin/jquery.css" rel="stylesheet" type="text/css"/>
        <link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet"  type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
        <script src="../../../include/js/jquery1.9.min.js" type="text/javascript" ></script> 

        <script type="text/javascript">
		$(document).ready(function() 
		{ 
		    $("#t1").tablesorter(
			{
				sortList: [[4,1]],
				widgets: ["zebra"]
			}); 

            function formatItem(row) 
            {
                return row[0] + " " + row[1] + " " + row[2];
            }	
            
            $("#kunde_name").autocomplete({
			source: "zeitaufzeichnung_autocomplete.php?autocomplete=kunde",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#kunde_uid").val(ui.item.uid);
			}
			});

		}); 
		
		function setbisdatum()
		{
			var now = new Date();
			var ret = "";
			var monat = now.getMonth();
			monat++;
			ret = foo(now.getDate());
			ret = ret + "." + foo(monat);
			ret = ret + "." + now.getFullYear();
			ret = ret + " " + foo(now.getHours());
			ret = ret + ":" + foo(now.getMinutes());
			//ret = ret + ":" + foo(now.getSeconds());
				
			document.getElementById("bis").value=ret;
		}
		
		function setvondatum()
		{
			var now = new Date();
			var ret = "";
			var monat = now.getMonth();
			monat++;
			ret = foo(now.getDate());
			ret = ret + "." + foo(monat);
			ret = ret + "." + now.getFullYear();
			ret = ret + " " + foo(now.getHours());
			ret = ret + ":" + foo(now.getMinutes());
			//ret = ret + ":" + foo(now.getSeconds());
				
			document.getElementById("von").value=ret;
		}
		
		function foo(val)
		{
			if(val<10)
				return "0"+val;
			else
				return val;
		}
		
		function confdel()
		{
			return confirm("'.$p->t("global/warnungWirklichLoeschen").'");
		}
		
		function loaduebersicht()
		{
			projekt = document.getElementById("projekt").value;
			
			document.location.href="'.$_SERVER['PHP_SELF'].'?filter="+projekt;
		}
		
		function uebernehmen()
		{
			document.getElementById("bis").value=document.getElementById("von").value;
		}

		function checkdatum()
		{
			var Datum,Tag,Monat,Jahr,Stunde,Minute,vonDatum,bisDatum,diff;
		
			Datum=document.getElementById("von").value;
		    Tag=Datum.substring(0,2); 
		    Monat=Datum.substring(3,5);
		    Jahr=Datum.substring(6,10);
		    Stunde=Datum.substring(11,13);
		    Minute=Datum.substring(14,16);
		    vonDatum=Jahr+\'\'+Monat+\'\'+Tag+\'\'+Stunde+\'\'+Minute;
		    
		    Datum=document.getElementById("bis").value;
		    Tag=Datum.substring(0,2); 
		    Monat=Datum.substring(3,5);
		    Jahr=Datum.substring(6,10);
		    Stunde=Datum.substring(11,13);
		    Minute=Datum.substring(14,16);
		    bisDatum=Jahr+\'\'+Monat+\'\'+Tag+\'\'+Stunde+\'\'+Minute;
		    diff=bisDatum-vonDatum;
		    
			if (bisDatum>vonDatum)  
			{
				if (diff>9999)  
				{
					alert("'.$p->t("zeitaufzeichnung/zeitraumAuffallendHoch").'");
					document.getElementById("bis").focus();
				  	return true;
				}
			}
			else
			{
				alert("'.$p->t("zeitaufzeichnung/bisDatumKleinerAlsVonDatum").'");
				document.getElementById("bis").focus();
			  	return false;
			}
			return true;
		}
		</script>
	</head>
<body>
';

echo '<h1>'.$p->t("zeitaufzeichnung/zeitaufzeichnung").'</h1>';

// Wenn Kartennummer übergeben wurde dann hole uid von Karteninhaber
if($kartennummer != '')
{
    $betriebsmittel = new betriebsmittelperson(); 
    if(!$betriebsmittel->getKartenzuordnung($kartennummer))
        die($betriebsmittel->errormsg); 
    
    $kunde_uid = $betriebsmittel->uid; 
}
//Speichern der Daten
if(isset($_POST['save']) || isset($_POST['edit']))
{
	$zeit = new zeitaufzeichnung();
	
	if(isset($_POST['edit']))
	{
		if(!$zeit->load($zeitaufzeichnung_id))
			die($p->t("global/fehlerBeimLadenDesDatensatzes"));
		
		$zeit->new = false;
	}
	else 
	{
		$zeit->new = true;
		$zeit->insertamum = date('Y-m-d H:i:s');
		$zeit->insertvon = $user;
	}
	
	$zeit->uid = $user;
	$zeit->aktivitaet_kurzbz = $aktivitaet_kurzbz;
	$zeit->start = $datum->formatDatum($von, $format='Y-m-d H:i:s');
	$zeit->ende = $datum->formatDatum($bis, $format='Y-m-d H:i:s');
	$zeit->beschreibung = $beschreibung;
	$zeit->oe_kurzbz_1 = $oe_kurzbz_1;
	$zeit->oe_kurzbz_2 = $oe_kurzbz_2;
	$zeit->updateamum = date('Y-m-d H:i:s');
	$zeit->updatevon = $user;
	$zeit->projekt_kurzbz = $projekt_kurzbz;
	$zeit->service_id = $service_id;
	$zeit->kunde_uid = $kunde_uid;
	
	if(!$zeit->save())
	{
		echo '<b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': '.$zeit->errormsg.'</b><br>';
	}
	else 
	{
		echo '<b>'.$p->t("global/datenWurdenGespeichert").'</b><br>';
		$zeitaufzeichnung_id = $zeit->zeitaufzeichnung_id;
	}
}

//Datensatz loeschen
if(isset($_GET['type']) && $_GET['type']=='delete')
{
	$zeit = new zeitaufzeichnung();
	
	if($zeit->load($zeitaufzeichnung_id))
	{
		if($zeit->uid==$user)
		{
			if($zeit->delete($zeitaufzeichnung_id))
				echo '<b>'.$p->t("global/eintragWurdeGeloescht").'</b><br>';
			else 
				echo '<b>'.$p->t("global/fehlerBeimLoeschenDesEintrags").'</b><br>';
		}
		else 
			echo '<b>'.$p->t("global/keineBerechtigung").'!</b><br>';
	}
	else 
		echo '<b>'.$p->t("global/datensatzWurdeNichtGefunden").'</b><br>';
}

//Laden der Daten zum aendern
if(isset($_GET['type']) && $_GET['type']=='edit')
{
	$zeit = new zeitaufzeichnung();
	
	if($zeit->load($zeitaufzeichnung_id))
	{
		if($zeit->uid==$user)
		{
			$uid = $zeit->uid;
			$aktivitaet_kurzbz = $zeit->aktivitaet_kurzbz;
			$von = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->start));
			$bis = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->ende));
			$beschreibung = $zeit->beschreibung;
			$oe_kurzbz_1 = $zeit->oe_kurzbz_1;
			$oe_kurzbz_2 = $zeit->oe_kurzbz_2;
			$projekt_kurzbz = $zeit->projekt_kurzbz;
			$service_id = $zeit->service_id;
			$kunde_uid = $zeit->kunde_uid;
		}
		else 
		{
			echo "<b>".$p->t("global/keineBerechtigungZumAendernDesDatensatzes")."</b>";
			$zeitaufzeichnung_id='';
		}
	}
}

//Projekte holen fuer zu denen der Benutzer zugeteilt ist
$projekt = new projekt();


if($projekt->getProjekteMitarbeiter($user))
{
	if(count($projekt->result)>0)
	{
		$bn = new benutzer();
		if(!$bn->load($user))
			die($p->t("zeitaufzeichnung/benutzerWurdeNichtGefunden",array($user)));
		
		echo "<table width='100%'>
				<tr>
					<td>".$p->t("zeitaufzeichnung/zeitaufzeichnungVon")." 
						<b>".$db->convert_html_chars($bn->vorname)." ".$db->convert_html_chars($bn->nachname)."</b>
					</td>
				</tr>
				<tr>
		      		<td>
		      			<a href='".$_SERVER['PHP_SELF']."' class='Item'>".$p->t("zeitaufzeichnung/neu")."</a>
		      		</td>
		      	</tr>
		      </table>";
		
		//Formular
		echo '<br><br><form action="'.$_SERVER['PHP_SELF'].'?zeitaufzeichnung_id='.$zeitaufzeichnung_id.'" method="POST" onsubmit="return checkdatum()">';
		
		echo '<table>';
		//Projekt
		echo '<tr>
				<td>'.$p->t("zeitaufzeichnung/projekt").'</td>
				<td><SELECT name="projekt" id="projekt">
					<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
		
		sort($projekt->result);
		foreach($projekt->result as $row_projekt)
		{
			if($projekt_kurzbz == $row_projekt->projekt_kurzbz || $filter == $row_projekt->projekt_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo '<option value="'.$db->convert_html_chars($row_projekt->projekt_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row_projekt->titel).'</option>';
		}
		echo '</SELECT><input type="button" value="'.$p->t("zeitaufzeichnung/uebersicht").'" onclick="loaduebersicht();"></td>';
		echo '</tr><tr>';
		//OE_KURZBZ_1
		echo '<td>'.$p->t("zeitaufzeichnung/organisationseinheit1").'</td>
			<td colspan="3"><SELECT name="oe_kurzbz_1">';
		$oe = new organisationseinheit();
		$oe->getAll();
		
		echo '<option value="">-- '.$p->t("zeitaufzeichnung/keineAuswahl").' --</option>';
		
		foreach ($oe->result as $row)
		{
			if($row->oe_kurzbz == $oe_kurzbz_1)
				$selected = 'selected';
			else 
				$selected = '';
			if($row->aktiv)
				$class='';
			else
				$class='class="inaktiv"';
				
			echo '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.' '.$class.'>'.$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung).'</option>';
		}
		echo '</SELECT>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';	
	
		//OE_KURZBZ_2
		echo '<td>'.$p->t("zeitaufzeichnung/organisationseinheit2").'</td>
				<td colspan="3"><SELECT name="oe_kurzbz_2">';
		echo '<option value="">-- '.$p->t("zeitaufzeichnung/keineAuswahl").' --</option>';
		
		$oe = new organisationseinheit();
		$oe->getAll();
		
		foreach ($oe->result as $row) 
		{
			if($oe_kurzbz_2 == $row->oe_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			if($row->aktiv)
				$class='';
			else
				$class='class="inaktiv"';
				
			echo '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.' '.$class.'>'.$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung).'</option>';
		}
		echo '</SELECT></td></tr>';
		//Aktivitaet
		echo '<tr>';
		echo '<td>'.$p->t("zeitaufzeichnung/aktivitaet").'</td><td>';
		
		$qry = "SELECT * FROM fue.tbl_aktivitaet ORDER by beschreibung";
		if($result = $db->db_query($qry))
		{
			echo '<SELECT name="aktivitaet">
			<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
			
			while($row = $db->db_fetch_object($result))
			{
				if($aktivitaet_kurzbz == $row->aktivitaet_kurzbz)
					$selected = 'selected';
				else
					$selected = '';
				
				echo '<OPTION value="'.$db->convert_html_chars($row->aktivitaet_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row->beschreibung).'</option>';
			}
			echo '</SELECT>';
		}
		echo '</td></tr>';
		echo '<tr>
			<td>'.$p->t('zeitaufzeichnung/service').'</td>
			<td colspan="3"><SELECT name="service_id">
			<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
		$service = new service();
		$service->getAll();
		foreach($service->result as $row)
		{
			if($row->service_id==$service_id)
				$selected='selected';
			else
				$selected='';
				
			echo '<OPTION value="'.$db->convert_html_chars($row->service_id).'" '.$selected.'>'.$db->convert_html_chars($row->oe_kurzbz.' - '.$row->bezeichnung).'</OPTION>';
		}
		echo '</SELECT></td>
			</tr>';
	/*	echo '<tr>
			<td>'.$p->t('zeitaufzeichnung/kunde').'</td>
			<td><SELECT name="kunde_uid">
			<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
		$mitarbeiter = new mitarbeiter();
		$result = $mitarbeiter->getMitarbeiter(null, null, null);
		foreach($result as $row)
		{
			if($row->uid==$kunde_uid)
				$selected='selected';
			else
				$selected='';
				
			echo '<OPTION value="'.$db->convert_html_chars($row->uid).'" '.$selected.'>'.$db->convert_html_chars($row->nachname.' '.$row->vorname.' - '.$row->uid).'</OPTION>';
		}
		echo '</SELECT>
			</td>
		</tr>';*/
        
        // person für Kundenvoransicht laden
        $kunde_name = '';
        if($kunde_uid != '')
        {
            $user_kunde = new benutzer(); 
            
            if($user_kunde->load($kunde_uid))
                $kunde_name=$user_kunde->vorname.' '.$user_kunde->nachname; 
        }
        echo '
        <tr>
            <td>'.$p->t("zeitaufzeichnung/kunde").'</td>
            <td colspan="3"><input type="text" id="kunde_name" value="'.$kunde_name.'" placeholder="'.$p->t("zeitaufzeichnung/nameEingeben").'"><input type ="hidden" id="kunde_uid" name="kunde_uid" value="'.$kunde_uid.'"> '.$p->t("zeitaufzeichnung/oderKartennummerOptional").' 
            <input type="text" id="kartennummer" name="kartennummer" placeholder="'.$p->t("zeitaufzeichnung/kartennummer").'"></td>
        </tr>'; 
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		//Start/Ende
		echo '
		<tr>
			<td>'.$p->t("global/von").'</td><td><input type="text" id="von" name="von" value="'.$db->convert_html_chars($von).'">&nbsp;<img style="vertical-align:bottom" src="../../../skin/images/timetable.png" title="'.$p->t("zeitaufzeichnung/aktuelleZeitLaden").'" onclick="setvondatum()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="->" title="'.$p->t("zeitaufzeichnung/alsEndzeitUebernehmen").'" onclick="uebernehmen()"></td>
			<td>'.$p->t("global/bis").'</td><td><input type="text" id="bis" name="bis" value="'.$db->convert_html_chars($bis).'">&nbsp;<img style="vertical-align:bottom" src="../../../skin/images/timetable.png" title="'.$p->t("zeitaufzeichnung/aktuelleZeitLaden").'" onclick="setbisdatum()"></td>
		<tr>';
		//Beschreibung
		echo '<tr><td>'.$p->t("global/beschreibung").'</td><td colspan="3"><textarea name="beschreibung" cols="60">'.$db->convert_html_chars($beschreibung).'</textarea></td></tr>';
		echo '<tr><td></td><td></td><td></td><td align="right">';
		//SpeichernButton
		if($zeitaufzeichnung_id=='')
			echo '<input type="submit" value="'.$p->t("global/speichern").'" name="save"></td></tr>';
		else 
		{
			echo '<input type="hidden" value="" name="'.($alle===true?'alle':'').'">'; 
			echo '<input type="submit" value="'.$p->t("global/aendern").'" name="edit">&nbsp;&nbsp;';
			echo '<input type="submit" value="'.$p->t("zeitaufzeichnung/alsNeuenEintragSpeichern").'" name="save"></td></tr>';
		}
		echo '</table>'; 
		echo '<hr>';
		echo '<input type="submit" value="Alle anzeigen" name="'.($alle===true?'normal':'alle').'">';       
		echo '</form>';
		
		$za = new zeitaufzeichnung();
	    if(isset($_GET['filter']))
	    	$za->getListeProjekt($_GET['filter']);
	    else
	    {
	    	if ($alle==true)
	    		$za->getListeUser($user, '');
	    	else 
	    		$za->getListeUser($user);
	    }
	   
		$summe=0;
		
		if(count($za->result)>0)
		{
			//Uebersichtstabelle
			echo '
			<table id="t1" class="tablesorter">
				<thead>
					<tr>
						<th>'.$p->t("zeitaufzeichnung/id").'</th>
						<th>'.$p->t("zeitaufzeichnung/projekt").'</th>
						<th>'.$p->t("zeitaufzeichnung/aktivitaet").'</th>
						<th>'.$p->t("zeitaufzeichnung/user").'</th>
						<th>'.$p->t("zeitaufzeichnung/start").'</th>
						<th>'.$p->t("zeitaufzeichnung/ende").'</th>
						<th>'.$p->t("zeitaufzeichnung/dauer").'</th>
						<th>'.$p->t("global/beschreibung").'</th>
						<th>'.$p->t("global/organisationseinheit").'</th>
						<th>'.$p->t("global/organisationseinheit").'</th>
						<th colspan="2">'.$p->t("global/aktion").'</th>
		    		</tr>
		    	</thead>
		    <tbody>';
		    
		    
			foreach($za->result as $row)
			{		        
				$summe = $row->summe;
				echo '<tr>
					<td>'.$db->convert_html_chars($row->zeitaufzeichnung_id).'</td>
					<td>'.$db->convert_html_chars($row->projekt_kurzbz).'</td>
			        <td>'.$db->convert_html_chars($row->aktivitaet_kurzbz).'</td>
			        <td>'.$db->convert_html_chars($row->uid).'</td>
			        <td nowrap>'.date('d.m.Y H:i', $datum->mktime_fromtimestamp($row->start)).'</td>
			        <td nowrap>'.date('d.m.Y H:i', $datum->mktime_fromtimestamp($row->ende)).'</td>
			        <td align="right">'.$db->convert_html_chars($row->diff).'</td>
			        <td title="'.$db->convert_html_chars(mb_eregi_replace("\r\n",' ',$row->beschreibung)).'">'.$db->convert_html_chars($row->beschreibung).'</td>
			        <td>'.$db->convert_html_chars($row->oe_kurzbz_1).'</td>
			        <td>'.$db->convert_html_chars($row->oe_kurzbz_2).'</td>
			        <td>';
		        if(!isset($_GET['filter']) || $row->uid==$user)
		        	echo '<a href="'.$_SERVER['PHP_SELF'].'?type=edit&zeitaufzeichnung_id='.$row->zeitaufzeichnung_id.'" class="Item">'.$p->t("global/bearbeiten").'</a>';
		        echo "</td>\n";
		        echo "       <td>";
		        if(!isset($_GET['filter']) || $row->uid==$user)
		        	echo '<a href="'.$_SERVER['PHP_SELF'].'?type=delete&zeitaufzeichnung_id='.$row->zeitaufzeichnung_id.'" class="Item"  onclick="return confdel()">'.$p->t("global/loeschen").'</a>';
		        echo "</td>\n";
		        echo "   </tr>\n";
		    }
		    echo "</tbody></table>\n";
		
	    echo $p->t("zeitaufzeichnung/gesamtdauer").": ".$db->convert_html_chars($summe);
		}
	}
	else 
	{
		echo $p->t("zeitaufzeichnung/sieSindDerzeitKeinenProjektenZugeordnet");
	}
}
else 
{
	echo $p->t("zeitaufzeichnung/fehlerBeimErmittelnDerProjekte");
}

echo '</body>
</html>'; 
?>