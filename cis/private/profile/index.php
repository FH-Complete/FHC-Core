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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <	gerald.simane-sequens@technikum-wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/betriebsmittel.class.php');
require_once('../../../include/betriebsmittelperson.class.php');
require_once('../../../include/betriebsmitteltyp.class.php');  
require_once('../../../include/phrasen.class.php');
require_once('../../../include/betriebsmittel_betriebsmittelstatus.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/fotostatus.class.php');
require_once('../../../include/addon.class.php'); 
require_once('../../../include/gruppe.class.php');

$sprache = getSprache(); 
$p=new phrasen($sprache);
  
if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
      
$uid=get_uid();
	
$datum_obj = new datum();
	
$ansicht=false; //Wenn ein anderer User sich das Profil ansieht (Bei Personensuche)
if(isset($_GET['uid']))
{
	$uid=stripslashes($_GET['uid']);
	$ansicht=true;
}

if(!$ansicht && isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case 'foto_freigabe':
			$benutzer = new benutzer();
			if($benutzer->load($uid))
			{
				$person = new person();
				if($person->load($benutzer->person_id))
				{
					$person->foto_sperre=false;
					$person->new=false;
					$person->save();
				}
			}
			break;
		case 'foto_sperre':
			$benutzer = new benutzer();
			if($benutzer->load($uid))
			{
				$person = new person();
				if($person->load($benutzer->person_id))
				{
					$person->foto_sperre=true;
					$person->new=false;
					$person->save();
				}
			}
			break;
	}
}
		
$stg = '';

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($stg_obj->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kurzbzlang;
	
if(check_lektor($uid))
{
	$user = new mitarbeiter();
	$type = 'mitarbeiter';
}
else
{
	$user = new student();
	$type='student';
}

if(!$user->load($uid))
	die($p->t('profil/esWurdenKeineProfileGefunden'));

if ($type=='mitarbeiter')
{
	$vorwahl = '';
	$kontakt = new kontakt();
	$kontakt->loadFirmaKontakttyp($user->standort_id,'telefon');
	$vorwahl = $kontakt->kontakt;	
}

// Mail-Groups
if(!($erg_mg=$db->db_query("SELECT gruppe_kurzbz, beschreibung FROM campus.vw_persongruppe WHERE mailgrp AND uid='$uid'  ".(isset($semester)?" and semester=$semester ":'')."  ORDER BY gruppe_kurzbz")))
	die($db->db_last_error());
$nr_mg=$db->db_num_rows($erg_mg);
	
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>'.$p->t('profil/profil').'</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
	<script src="../../../include/js/flexcroll.js" type="text/javascript" ></script>
	<script type="text/javascript" src="../../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<script language="JavaScript" type="text/javascript">
	<!--
		function RefreshImage()
		{
			window.location.reload();
		}
		
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			}); 
			$("#t2").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			});
		});
	-->
	</script>
</head>

<body>
<div class="flexcroll" style="outline: none;">
	<h1>'.$p->t('profil/profil').'</h1>
';
		
if(!$user->bnaktiv)
{
	if(!$ansicht)
	{
		
		if ($type=='student')
			$message = $p->t('profil/inaktivStudent');
		elseif($type=='mitarbeiter')
			$message = $p->t('profil/inaktivMitarbeiter');
		else
			$message = $p->t('profil/inaktivSonstige');		
	}
	else
		$message = $p->t('profil/AccountInaktiv');
		
	echo "<span style='color: red;'>$message</span>"; 
}

echo '
<table class="cmstable" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="cmscontent" rowspan="3" valign="top">
	<table width="100%">
	<tr>
  		<td valign="top" width="10%" nowrap style="padding-right:10px;">';
  		
//Foto anzeigen
$benutzer = new benutzer();
$benutzer->load($uid);
$person = new person();
$person->load($benutzer->person_id);
if($person->foto!='')
{
	if(!($ansicht && $user->foto_sperre))
		echo '<img id="personimage" src="../../public/bild.php?src=person&person_id='.$user->person_id.'" alt="'.$user->person_id.'" height="100px" width="75px">';
	else
		echo '<img id="personimage" src="../../../skin/images/profilbild_dummy.jpg" alt="Dummy Picture" height="100px" width="75px">';
}
else 
	echo '<img id="personimage" src="../../../skin/images/profilbild_dummy.jpg" alt="Dummy Picture" height="100px" width="75px">';

if(!$ansicht)
{
	//Foto Upload nur möglich wenn das Bild noch nicht akzeptiert wurde
	$fs = new fotostatus();
	if(!$fs->akzeptiert($user->person_id))
		echo "<br><a href='#BildUpload' onclick='window.open(\"../bildupload.php?person_id=$user->person_id\",\"BildUpload\", \"height=500,width=500,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>".$p->t('profil/bildHochladen')."</a>";
}
if($user->foto_sperre)
	echo '<br><b>'.$p->t('profil/profilfotoGesperrt').'</b>';
	
if(!$ansicht)
{
	if($user->foto_sperre)
		echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=foto_freigabe" title="'.$p->t('profil/infotextSperre').'">'.$p->t('profil/fotofreigeben').'</a>';
	else
		echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=foto_sperre" title="'.$p->t('profil/infotextSperre').'">'.$p->t('profil/fotosperren').'</a>';
}
	
echo '</td><td width="30%">';

echo '
		<b>'.($type=="student"?$p->t("profil/student"):$p->t('profil/mitarbeiter')).'</b><br><br>
		'.$p->t('global/username').': '.$user->uid.'<br>
		'.$p->t('global/titel').': '.$user->titelpre.' <br>
  		'.$p->t('global/vorname').': '.$user->vorname.'  '.$user->vornamen.'<br>
   		'.$p->t('global/nachname').': '.$user->nachname.'<br>
  		'.$p->t('global/postnomen').': '.$user->titelpost.'<br>';
		
if(!$ansicht)
{
	echo '	'.$p->t('global/geburtsdatum').': '.$datum_obj->formatDatum($user->gebdatum,'d.m.Y')."<br>
	".$p->t('global/geburtsort').": $user->gebort<br>";
        		
}
$studiengang = new studiengang();
if ($type=='student')
{	
	$studiengang->load($user->studiengang_kz);
	
	echo "<br>
	".$p->t('global/studiengang').": $studiengang->bezeichnung<br>
	".$p->t('global/semester').": $user->semester <a href='#' onClick='javascript:window.open(\"../stud_in_grp.php?kz=$user->studiengang_kz&sem=$user->semester\",\"_blank\",\"width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\");return false;'>".$p->t('benotungstool/liste')."</a><br>
	".$p->t('global/verband').": $user->verband ".($user->verband!=' '?"<a href='#' onClick='javascript:window.open(\"../stud_in_grp.php?kz=$user->studiengang_kz&sem=$user->semester&verband=$user->verband\",\"_blank\",\"width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\");return false;'>".$p->t('benotungstool/liste')."</a>":"")."<br>
	".$p->t('global/gruppe').": $user->gruppe ".($user->gruppe!=' '?"<a href='#' onClick='javascript:window.open(\"../stud_in_grp.php?kz=$user->studiengang_kz&sem=$user->semester&verband=$user->verband&grp=$user->gruppe\",\"_blank\",\"width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\");return false;'>".$p->t('benotungstool/liste')."</a>":"")."<br>
	".$p->t('profil/martrikelnummer').": $user->matrikelnr<br />";
}
		
if ($type=='mitarbeiter')
{
	echo "<br>
	".$p->t('profil/kurzzeichen').": $user->kurzbz<BR>";
				
	if($user->telefonklappe!='')
	{
		echo $p->t('profil/telefonTw').": $vorwahl - $user->telefonklappe<BR>";
		echo $p->t('profil/faxTw').": $vorwahl - 99 $user->telefonklappe<BR>";
	}
	if ($user->ort_kurzbz!='')
		echo $p->t('profil/buero').': '.$user->ort_kurzbz.'<br>';
}  
echo '</td>';
echo '<td valign="top">';
if(!$ansicht)
{	
	echo '<b>'.$p->t('profil/fhausweisStatus').'</b><br>';
	$bm = new betriebsmittel();
	if($bm->zutrittskarteAusgegeben($user->uid))
	{
		echo '<br>'.$p->t('profil/fhausweisWurdeBereitsAusgegeben');
	}
	else
	{
		$fs = new fotostatus();
		if($fs->getLastFotoStatus($user->person_id))
		{
			echo '<br>'.$p->t('profil/Bild').' '.$fs->fotostatus_kurzbz.' am '.$datum_obj->formatDatum($fs->datum, 'd.m.Y');
			switch($fs->fotostatus_kurzbz)
			{
				case 'abgewiesen':
					echo '<br><div style="color:red;">'.$p->t('profil/ladenSieBitteEinGueltigesFotoHoch').'</div>';
					break;
				case 'hochgeladen':
					echo '<br>'.$p->t('profil/fotoWurdeNochNichtAkzeptiert');
					break;
				case 'akzeptiert':
					if($bm->zutrittskartePrinted($user->uid))
					{
						echo '<br>'.$p->t('profil/fhausweisGedrucktAm').' '.$datum_obj->formatDatum($bm->insertamum,'d.m.Y');
						$geliefertts = $datum_obj->mktime_fromtimestamp($bm->insertamum);
						$abholungsdatum = $datum_obj->jump_day($geliefertts, 1);	
						echo '<br>'.$p->t('profil/fhausweisAbholbereitAmEmpfangAb').' '.date('d.m.Y',$abholungsdatum);	
					}
					else
						echo '<br>'.$p->t('profil/fhausweisWurdeNochNichtGedruckt');
					break;
					
				default:
					echo '<br><div style="color:red;">'.$p->t('profil/ladenSieBitteEinGueltigesFotoHoch').'</div>';
					break;
			}
		}
		else
		{
			echo '<br>'.$p->t('profil/ihrFotoWurdeNochNichtGeprueft');
		}
	}
	echo '<br><br>';
}
echo '<b>'.$p->t('profil/email').'</b><br><br>
    '.$p->t('profil/intern').': <a href="mailto:'.$user->uid.'@'.DOMAIN.'">'.$user->uid.'@'.DOMAIN.'</a><br>';

if($user->alias!='' && (!isset($user->studiengang_kz) || !in_array($user->studiengang_kz,$noalias)))
{
	echo $p->t('profil/alias').": <a class='Item' href='mailto:$user->alias@".DOMAIN."'>$user->alias@".DOMAIN."</a>";
}

if($user->homepage!='')
	echo "<br><br><b>".$p->t('profil/homepage')."</b><br><br><a href='$user->homepage' target='_blank'>$user->homepage</a>";
echo '</tr></table><br>';

$mail = MAIL_ADMIN;
		if(!isset($user->studiengang_kz) || $user->studiengang_kz=='')
		{
			$user->studiengang_kz = 0;
		}
		
		//Wenn eine Assistentin fuer diesen Studiengang eingetragen ist,
		//dann werden die aenderungswuesche an diese Adresse gesendet
		if($studiengang->email!='')
			$mail = $studiengang->email;
		else
			$mail = MAIL_ADMIN;
		
		if($user->studiengang_kz=='0')
			$mail = MAIL_GST;
			
if(!$ansicht)
		{
			echo "
			".$p->t('profil/solltenDatenNichtStimmen')." <a class='Item' href=\"mailto:$mail?subject=Datenkorrektur&body=Die%20Profildaten%20fuer%20User%20'$user->uid'%20sind%20nicht%20korrekt.%0D
			Hier die richtigen Daten:%0A%0ANachname:%20$user->nachname%0AVorname:%20$user->vorname%0AGeburtsdatum:%20$user->gebdatum
			%0AGeburtsort:%20$user->gebort%0ATitelPre:%20$user->titelpre%0ATitelPost:%20$user->titelpost
			%0A%0A***%0DPlatz fuer weitere (nicht angefuehrte Daten)%0D***\">".$p->t('profil/zustaendigeAssistenz')."</a><br><br>";
		}
		
echo '<table width="100%">
    <tr>
    <td valign="top">';

	//Funktionen
	$qry = "SELECT 
				*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
				 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
			FROM 
				public.tbl_benutzerfunktion 
				JOIN public.tbl_funktion USING(funktion_kurzbz) 
				JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
			WHERE 
				uid=".$db->db_add_param($uid)." AND 
				(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
				(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())";
		
	if($result_funktion = $db->db_query($qry))
	{
		if($db->db_num_rows($result_funktion)>0)
		{
			echo '<b>'.$p->t('profil/funktionen').'</b><table class="tablesorter" id="t1"><thead><tr><th>'.$p->t('global/bezeichnung').'</th><th>'.$p->t('global/organisationseinheit').'</th><th>'.$p->t('global/semester').'</th><th>'.$p->t('global/institut').'</th></tr></thead><tbody>';

			while($row_funktion = $db->db_fetch_object($result_funktion))
			{
				echo "<tr><td>".$row_funktion->beschreibung.($row_funktion->bf_bezeichnung!=$row_funktion->beschreibung && $row_funktion->bf_bezeichnung!=''?' - '.$row_funktion->bf_bezeichnung:'')."</td><td nowrap>".$row_funktion->organisationseinheittyp_kurzbz.' '.$row_funktion->oe_bezeichnung."</td><td>$row_funktion->semester</td><td>$row_funktion->fachbereich_kurzbz</td></tr>";
			}
			echo '</tbody></table><br>';
		}
	}
if(!$ansicht)
{
    // Betriebsmittel Personen
    $oBetriebsmittelperson = new betriebsmittelperson();
    $oBetriebsmittelperson->result=array();
    $oBetriebsmittelperson->errormsg='';
			
    if ($oBetriebsmittelperson->getBetriebsmittelPerson($user->person_id))
	{
		if (is_array($oBetriebsmittelperson->result) && count($oBetriebsmittelperson->result)>0)
	    {
	    	echo '<b>'.$p->t('profil/entlehnteBetriebsmittel').'</b>
	    			<table class="tablesorter" id="t2">
	    			<thead>
	    				<tr>
	    					<th>'.$p->t('profil/betriebsmittel').'</th>
	    					<th>'.$p->t('profil/nummer').'</th>
	    					<th>'.$p->t('profil/ausgegebenAm').'</th>
	    				</tr>
	    			</thead><tbody>';
	   
			for ($i=0;$i<count($oBetriebsmittelperson->result);$i++)
			{
				if (empty($oBetriebsmittelperson->result[$i]->retouram) )
		        {
		          	$bm = new betriebsmittel_betriebsmittelstatus();
		           	if($bm->load_last_betriebsmittel_id($oBetriebsmittelperson->result[$i]->betriebsmittel_id) 
		             	&& $bm->betriebsmittelstatus_kurzbz<>'vorhanden')
		           	{
		          		continue;
		           	}
		           	$mailtext_inventar = "	".$p->t('mail/profilBetriebsmittelKorrektur')."?subject=Korrektur%20des%20Inventars%20".$oBetriebsmittelperson->result[$i]->inventarnummer."
		           							&body=Folgende%20Aenderung%20hat%20sich%20ergeben:%0A%0A
		          							Inventar:%20".$oBetriebsmittelperson->result[$i]->inventarnummer."%20(".$oBetriebsmittelperson->result[$i]->beschreibung.")%0A%0A
		          							Status:%20ausgeschieden%20%2F%20falsche%20Zuordnung%20%2F%20falsche%20Angaben%0A
		          							Details:%20%0A\"";
					echo "<tr>
		      				<td>".$oBetriebsmittelperson->result[$i]->betriebsmitteltyp.' '.$oBetriebsmittelperson->result[$i]->beschreibung.(isset($oBetriebsmittelperson->result[$i]->verwendung)?' ('.$oBetriebsmittelperson->result[$i]->verwendung.')':'')."</td>
		      				<td>".$oBetriebsmittelperson->result[$i]->nummer.' <a href="mailto:'.$mailtext_inventar.'>'.$oBetriebsmittelperson->result[$i]->inventarnummer."</a></td>
		      				<td>".$datum_obj->formatDatum($oBetriebsmittelperson->result[$i]->ausgegebenam,'d.m.Y')."</td>
		      			</tr>";
		                	
	            }
	        }
	    	echo '</tbody></table>';
		}
    }

	// Zutrittsgruppen
	$gruppe = new gruppe();
	$gruppe->loadZutrittsgruppen($uid);
	if(count($gruppe->result)>0)
	{
		echo '<b>Zutrittsgruppen</b>
		<table id="tableZutritt" class="tablesorter">
		<thead>
			<tr>
				<th>
				Zutritt
				</th>
			</tr>
		</thead>
		<tbody>
		';
		
		foreach($gruppe->result as $row)
		{
			echo '<tr>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '</tr>';	
		}
		echo '</tbody>
		</thead>
		</table>';
	}
}
    
		
	echo '	
    	</td>
  	</tr>
  	</table>
  	</td>';
	echo '<td class="menubox">';
	if ($type=='student')
	{		
	    if(!$ansicht)
	    {
	    	echo "<p><A class='Item' href='../lehre/notenliste.php'>".$p->t('profil/leistungsbeurteilung')."</a></p>";
	    }
	    echo '<p><A href="../lvplan/stpl_week.php?pers_uid='.$user->uid.'&type=student">'.$p->t('profil/lvplanVon').' '.$user->nachname.'</A></p>';
	}
	if ($type=='mitarbeiter')
	{
		if(!$ansicht)
		{
			echo '<p><A href="zeitwunsch.php?uid='.$user->uid.'">'.$p->t('profil/zeitwuensche').'</A></p>
				<p><A  href="lva_liste.php?uid='.$user->uid.'">'.$p->t('lvaliste/lehrveranstaltungen').'</A></p>';
		}
		if(check_lektor(get_uid()))
		{
			echo '<p><A href="zeitsperre_days.php?days=30&lektor='.$user->uid.'">'.$p->t('profil/zeitsperrenVon').' '.$user->nachname.'</A></p>';
		}
		if($uid!=get_uid())
		{
			echo '<p><A href="../lvplan/stpl_week.php?pers_uid='.$user->uid.'&type=lektor">'.$p->t('profil/lvplanVon').' '.$user->nachname.'</A></p>';
		}
	}
        //Überprüfung ob Addon vorhanden ist
        $addon = new addon();         
        foreach($addon->aktive_addons as $ad)
        {
            // checken ob es file profil_array.php gibt
            if(file_exists(DOC_ROOT.'/addons/'.$ad.'/cis/profil_array.php'))
            {
                include(DOC_ROOT.'/addons/'.$ad.'/cis/profil_array.php');
                // Wenn Mitarbeiter count == 0
                if(count($menu >0))
                {
                    foreach($menu as $entry)
                    {
                        echo "<p><a href=".APP_ROOT."addons/".$ad."/cis/".$entry['link']." target=".$entry['target'].">".$entry['name']."</a></p>"; 
                    }
                }
            }
        }
	
    //Überprüfung ob Hilfe-Link vorhanden
    if ($p->t("dms_link/profilhilfe")!='')
        echo '<p><a href="../../../cms/content.php?content_id='.$p->t("dms_link/profilhilfe").'" target="_blank">'.$p->t('global/hilfe').'</a></p>';
        
	echo'</td></tr>
		<tr>
		<td class="teambox" style="width: 20%;">
		<B>'.$p->t('mailverteiler/mailverteiler').'</B><BR><BR>
    	';
	//Mailverteiler
	if(!$ansicht)
		echo "<SMALL>".$p->t('profil/sieSindMitgliedInFolgendenVerteilern').":</SMALL>";
	else
		echo "<SMALL>".$p->t('profil/derUserIstInFolgendenVerteilern',array($user->uid)).":</SMALL>";
       
	echo '<table>';
	  		
  	for($i=0;$i<$nr_mg;$i++)
	{
		$row=$db->db_fetch_object($erg_mg,$i);
		echo '<TR><TD><A href="mailto:'.strtolower(trim($row->gruppe_kurzbz)).'@'.DOMAIN.'">'.strtolower($row->gruppe_kurzbz).'&nbsp;</TD>';
    	echo "<TD>&nbsp;$row->beschreibung</TD></TR>";
	}
	
	if (isset($user->matrikelnr))
	{
		echo '<TR><TD><A href="mailto:'.strtolower(trim($studiengang->kuerzel)).'_std@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).'_std&nbsp;</TD>';
    	echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel</TD></TR>";
		echo '<TR><TD><A href="mailto:'.strtolower(trim($studiengang->kuerzel)).trim($user->semester).'@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).$user->semester.'&nbsp;</TD>';
   		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel $user->semester</TD></TR>";
		echo '<TR><TD><A href="mailto:'.strtolower(trim($studiengang->kuerzel)).trim($user->semester).strtolower(trim($user->verband)).'@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).$user->semester.strtolower($user->verband).'&nbsp;</TD>';
   		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel $user->semester$user->verband</TD></TR>";
   		if(trim($user->gruppe)!='')
   		{
			echo '<TR><TD><A href="mailto:'.strtolower(trim($studiengang->kuerzel)).trim($user->semester).strtolower(trim($user->verband)).trim($user->gruppe).'@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).$user->semester.strtolower($user->verband).$user->gruppe.'&nbsp;</TD>';
   			echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel $user->semester$user->verband$user->gruppe</TD><TD></TD></TR>";
   		}
	}
	echo '	</table>
			</td>
			</tr>
			</tbody>
			</table>';
?>
</div>
</body>
</html>
