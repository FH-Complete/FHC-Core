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
require_once('../../include/nation.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/mobilitaetsprogramm.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

// ******* INIT ********
$user = get_uid();
//Zugriffsrechte pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('basis/firma:begrenzt'))
	die('Sie haben keine Berechtigung für diese Seite');

// Parameter einlesen
$tabselect = (isset($_GET['tabselect'])?$_GET['tabselect']:0);
$firma_id = (isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:'');
$adresse_id = (isset($_REQUEST['adresse_id'])?$_REQUEST['adresse_id']:'');
$standort_id = (isset($_REQUEST['standort_id'])?$_REQUEST['standort_id']:'');
$oe_kurzbz = (isset($_REQUEST['oe_kurzbz'])?$_REQUEST['oe_kurzbz']:'');
$firma_organisationseinheit_id = (isset($_REQUEST['firma_organisationseinheit_id'])?$_REQUEST['firma_organisationseinheit_id']:'');
$tag = (isset($_REQUEST['tag'])?$_REQUEST['tag']:'');
$mobilitaetsprogramm_code = (isset($_REQUEST['mobilitaetsprogramm_code'])?$_REQUEST['mobilitaetsprogramm_code']:'');
$save = (isset($_REQUEST['save'])?$_REQUEST['save']:null);
$work = (isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['save'])?$_REQUEST['save']:null));
$ajax = (isset($_REQUEST['ajax'])?$_REQUEST['ajax']:null);

$neu = (isset($_REQUEST['neu'])?$_REQUEST['neu']:null);

// Defaultwerte
$adresstyp_arr = array('h'=>'Hauptwohnsitz','n'=>'Nebenwohnsitz','f'=>'Firma',''=>'');
$errorstr='';

//Loeschen einer Adresse
if(isset($_GET['deleteadresse']))
{
	if( !$rechte->isBerechtigt('basis/firma:begrenzt',null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	if(is_numeric($standort_id))
	{
		$standort_obj = new standort();
		if(!$standort_obj->delete($standort_id))
		{
			$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Firma/Standort:'.$standort_obj->errormsg;
		}
	}
	if(is_numeric($adresse_id))
	{
		$adresse_obj = new adresse();
		if(!$adresse_obj->delete($adresse_id))
		{
			$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen der Firma/Adresse:'.$adresse_obj->errormsg;
		}
	}
	echo '
	<script language="JavaScript1.2" type="text/javascript">
		parent.frames[0].location.reload();
	</script>';
}

//Loeschen einer Organisationseinheit
if(isset($_GET['deleteorganisationseinheit']))
{
	if(!empty($firma_organisationseinheit_id))
	{
		$firma = new firma();
		$firma->load_firmaorganisationseinheit($firma_organisationseinheit_id);
		$oe_kurzbz = $firma->oe_kurzbz;
		if(!$rechte->isBerechtigt('basis/firma:begrenzt',$oe_kurzbz, 'suid'))
			die($rechte->errormsg);
		$firma = new firma();
		if(!$firma->deleteorganisationseinheit($firma_organisationseinheit_id))
		{
			$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Firma/Organisation:'.$firma->errormsg;
		}
	}
	else
		$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Firma/Organisation : ID fehlt';
	$tabselect=1;
}

if(isset($_GET['deletemobilitaetsprogramm']))
{
	if(!$rechte->isBerechtigt('basis/firma:begrenzt',null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	if(!empty($mobilitaetsprogramm_code))
	{
		$firma = new firma();
		if(!$firma->deletemobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code))
		{
			$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Firma/Mobilitaetsprogramm:'.$firma->errormsg;
		}
	}
	else
		$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Firma/Mobilitaetsprogramm : Code fehlt';
	$tabselect=2;
}

//Loeschen eines Tags
if(isset($_GET['deletetag']))
{
	if(!$rechte->isBerechtigt('basis/firma:begrenzt',null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	if(!empty($tag))
	{
		$firma = new firma();
		if(!$firma->deletetag($firma_id, $tag))
		{
			$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen des Tags:'.$firma->errormsg;
		}
	}
	else
		$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen des Tags : Tag fehlt';
}

?><!DOCTYPE HTML>
<html>
<head>
	<title>Firma - Details</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/styles/jquery.css" type="text/css">

	<script src="../../include/js/mailcheck.js" type="text/javascript"></script>
	<script src="../../include/js/datecheck.js" type="text/javascript"></script>

	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

	<script type="text/javascript" language="JavaScript1.2">
		function confdel()
		{
			if(confirm("Diesen Datensatz wirklich loeschen?"))
				return true;
			return false;
		}
	</script>

</head>
<body style="background-color:#eeeeee;">
<?php

//Tabs
switch ($work)
{
	case 'standortliste':
		echo getStandortliste($firma_id,$adresstyp_arr,$user);
		break;

	case 'organisationliste':
		echo getOrganisationsliste($firma_id,$adresstyp_arr,$user);
		break;

	case 'mobilitaetsprogramm':
		echo getMobilitaetsprogrammliste($firma_id,$user);
		break;
	case 'saveMobilitaetsprogramm':
		saveMobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code);
		echo getFirmadetail($firma_id,$adresstyp_arr,$user,$neu);
		$tabselect=2;
		break;
	case 'anmerkungsfeld':
		echo getAnmerkungen($firma_id,$user);
		break;
	case 'saveAnmerkungen':
		$status = saveAnmerkungen($firma_id,$user, $rechte);
		echo getFirmadetail($firma_id,$adresstyp_arr,$user,$neu);
		echo $status;
		$tabselect=3;
		break;

	case 'saveFirma':
		$status=saveFirma($user,$rechte); // Postdaten werden in der Funktion verarbeitet
		if (is_numeric($status))
			$firma_id=$status;
		if (!$ajax)
			echo getFirmadetail($firma_id,$adresstyp_arr,$user,$neu);
		else if (is_numeric($status))
			echo "Daten erfolgreich gespeichert";
		if (!is_numeric($status))
			echo $status;
		break;
    default:
		echo getFirmadetail($firma_id,$adresstyp_arr,$user,$neu);
		break;
}

echo  ($errorstr?'<br>'.$errorstr:'');

?>
<script language="Javascript">
$(document).ready(function() {
		$("#tabs").tabs();
		$( "#tabs" ).tabs( "option", "selected", <?php echo $tabselect;?> );
	})
</script>

</body>
</html>
<?php
/**
 * Firmenliste - lt. Suchekriterien
 */
function getFirmadetail($firma_id, $adresstyp_arr, $user, $neu)
{
	global $rechte;
	if($firma_id!='' || $neu=='true')
	{
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		// Init
		$htmlstr='';
		// Datenlesen zur Firma
		$firma = new firma();
		if($firma_id!='' && is_numeric($firma_id))
		{
			if (!$firma->load($firma_id))
				return '<br>Firma mit der ID <b>'.$firma_id.'</b> existiert nicht';
		}
		else
		{
			//Bei neuen Firmen wird standardmaessig Partnerfirma ausgewaehlt
			$firma->firmentyp_kurzbz='Partnerfirma';
			$firma->aktiv=true;
			$firma->gesperrt=false;
			$firma->schule=false;
		}

		$htmlstr.="<form id='addFirma' name='addFirma' action='firma_details.php' method='POST'>\n";
		$htmlstr.="<input type='hidden' name='work' value='saveFirma'>\n";
		$htmlstr.="<input type='hidden' name='firma_id' value='".$firma->firma_id."'>\n";
		// Firma Detailanzeige
		$htmlstr.="<table class='detail' style='padding-top:10px;'>\n";
		$htmlstr.="<tr><td><table width='100%'><tr>\n";
		$htmlstr.="<td>Typ: </td>";
		$htmlstr.="<td><select name='typ'>\n";

		$qry = "SELECT firmentyp_kurzbz FROM public.tbl_firmentyp ORDER BY firmentyp_kurzbz";
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$htmlstr.="<option value='".$row->firmentyp_kurzbz."' ".($firma->firmentyp_kurzbz == $row->firmentyp_kurzbz?' selected ':'').">".$row->firmentyp_kurzbz."</option>";
			}
		}
		$htmlstr.="</select></td>";
		$htmlstr.="<td>&nbsp;</td>";
		$htmlstr.="<td>Name: </td>";
		$htmlstr.="<td><input type='text' name='name' value='".$firma->name."' size='80' maxlength='128' /></td>\n";

		$htmlstr.="<td align='center' width='20%'><input type='submit' name='save' value='speichern'></td>\n";
		$htmlstr.="</tr></table></td>";

		$htmlstr.="</tr>\n";
		$htmlstr.="<tr><td><table><tr>\n";
		$htmlstr.="<td>Steuernummer: </td>";
		$htmlstr.="<td><input size='32' maxlength='32' type='text' name='steuernummer' value='".$firma->steuernummer."'></td>\n";
		$htmlstr.="<td>&nbsp;</td>";
		$htmlstr.="<td>Finanzamt: </td>";
		// Finanzamt anzeige und suche
		$firma_finanzamt = new firma();
		$firmentyp_finanzamt='Finanzamt';
		$firma_finanzamt->searchFirma('',$firmentyp_finanzamt, true);

		$htmlstr.="<td><select name='finanzamt'>";
		$htmlstr.="<option value=''>-- keine Auswahl --</option>";
		foreach ($firma_finanzamt->result as $row_finanzamt)
		{
			if($firma->finanzamt==$row_finanzamt->standort_id)
				$selected='selected="true"';
			else
				$selected='';
			$htmlstr.="	<option value='".$row_finanzamt->standort_id ."' ".$selected.">".$row_finanzamt->name.' - '.$row_finanzamt->bezeichnung." </option>";
		}
		$htmlstr.="</select></td>\n";

		$htmlstr.="<td>Aktiv: </td>";
		$htmlstr.="<td><input ".($firma->aktiv?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')." type='checkbox' name='aktiv' ".($firma->aktiv?'checked':'')."></td>\n";
		$htmlstr.="<td>&nbsp;</td>\n";

		$htmlstr.="<td>Gesperrt: </td>";

		$disabled='disabled=true';
		//Gesperrt Hackerl darf nur gesetzt werden wenn die Berechtigung vorhanden ist
		if($rechte->isBerechtigt('basis/firma',null, 'suid'))
		{
			$disabled='';
		}

		$htmlstr.="<td><input type='checkbox' name='gesperrt' ".($firma->gesperrt?'checked':'')." $disabled></td>\n";

		$htmlstr.="<td>&nbsp;</td>\n";

		$htmlstr.="<td>Schule:</td>";
		$htmlstr.="<td><input ".($firma->schule?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')."  type='checkbox' name='schule' ".($firma->schule?'checked':'')."> </td>";
		$htmlstr.="<td>&nbsp;</td>";

		$htmlstr.="<td>Lieferant:</td>";
		$htmlstr.="<td><input ".($firma->lieferant?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')."  type='checkbox' name='lieferant' ".($firma->lieferant?'checked':'')."> </td>";
		$htmlstr.="<td>&nbsp;</td>";

		$htmlstr.="</tr>";
		$htmlstr.="<tr>";
		$htmlstr.="<td title='Trennung mehrerer Tags durch ;'>Tags:</td><td><input type='text' id='tags' name='tags' size='32'>";
		/* $htmlstr.="<script type='text/javascript' language='JavaScript1.2'>
					$('#tags').autocomplete('stammdaten_autocomplete.php',
					{
						minChars:1,
						matchSubset:1,matchContains:1,
						width:400,
						multiple: true,
						multipleSeparator: '; ',
						extraParams:{'work':'tags'}
					});
				</script>"; */
		$htmlstr.="<script type='text/javascript'>
                            $(document).ready(function()
                            {
                                $('#tags').autocomplete({
                                    source: 'stammdaten_autocomplete.php?work=tags',
                                    minLength:1,
                                    response: function(event, ui)
                                    {
                                        for(i in ui.content)
                                        {
                                            ui.content[i].value=ui.content[i].tag;
                                            ui.content[i].label=ui.content[i].tag;
                                        }
                                    },
                                    select: function(event, ui)
                                    {
                                        ui.item.value=ui.item.tag;
                                    }
				});
                            });
                           </script>";
		$htmlstr.="</td>";
		$htmlstr.='<td>&nbsp;</td>';
		$htmlstr.='<td>Partner Code:</td>';
		$htmlstr.='<td><input type="text" name="partner_code" value="'.$db->convert_html_chars($firma->partner_code).'" maxlength="10" size="6" /></td>';
		$htmlstr.="<td colspan='7'>";

		foreach($firma->tags as $tag)
		{
			$htmlstr.= '&nbsp;'.$tag.'<a href="firma_details.php?firma_id='.$firma->firma_id.'&deletetag=true&tag='.urlencode($tag).'" title="entfernen"> <img src="../../skin/images/DeleteIcon.png" /></a>';
		}
		$htmlstr.="</td></tr></table></td>";
		$htmlstr.="</tr>\n";
		$htmlstr.="	</table>\n";
		$htmlstr.="</form>\n";

		$htmlstr.='<div id="addFirmaInfo"></div>';

		$htmlstr.='
			<!-- Tabs -->
			<div id="tabs" style="font-size:80%;">
				<ul class="css-tabs">
				     <li><a href="#standort">Standorte</a></li>
					 <li><a href="#organisationseinheit">Organisationseinheit</a></li>
					 <li><a href="#mobilitaetsprogramm">Mobilitätsprogramm</a></li>
					 <li><a href="#anmerkung">Anmerkungen</a></li>
				</ul>
				<div id="standort">
				'.getStandortliste($firma_id,$adresstyp_arr,$user).'
				</div>
				<div id="organisationseinheit">
				'.getOrganisationsliste($firma_id, $adresstyp_arr, $user).'
				</div>
				<div id="mobilitaetsprogramm">
				'.getMobilitaetsprogrammliste($firma_id, $user).'
				</div>
				<div id="anmerkung">
				'.getAnmerkungen($firma_id, $user).'
				</div>
			</div>

			<div id="detailstandort">	</div>
			';

		return $htmlstr;
	}
}
// ----------------------------------------------------------------------------------------------------------------------------------
/**
 * FirmenDatenspeichern POST ( Ajax )
 * Param  $user  Objekt vom Aktivenbenutzer
 * Param  $recht Objekt der Rechte des Aktivenbenutzer
 * Return firma_id oder Fehlertext
 */
function saveFirma($user,$rechte)
{
	// Speichern der Firmendaten
	if(!$rechte->isBerechtigt('basis/firma:begrenzt',null, 'suid'))
		return 'Sie haben keine Berechtigung fuer diese Aktion';
	// Verarbeiten
	$firma_id = (isset($_POST['firma_id'])?$_POST['firma_id']:'');
	$firma = new firma();
	if($firma_id!='')
	{
		if(!$firma->load($firma_id))
			return 'Firma '.$firma_id.' wurde nicht gefunden';
		else
			$firma->new = false;
	}
	else
	{
		$firma->insertamum = date('Y-m-d H:i:s');
		$firma->insertvon = $user;
		$firma->new = true;
	}
	$firma->name = (isset($_POST['name'])?$_POST['name']:'');
	$firma->firmentyp_kurzbz = (isset($_POST['typ'])?$_POST['typ']:'');
	$firma->updateamum = date('Y-m-d H:i:s');
	$firma->updatevon = $user;
	$firma->schule = isset($_POST['schule']);
	$firma->steuernummer = (isset($_POST['steuernummer'])?$_POST['steuernummer']:'');
	$firma->gesperrt = (isset($_POST['gesperrt'])?true:false);
	$firma->aktiv = (isset($_POST['aktiv'])?true:false);
	$firma->finanzamt = (isset($_POST['finanzamt'])?$_POST['finanzamt']:'');
	$firma->partner_code = (isset($_POST['partner_code'])?$_POST['partner_code']:'');
	$firma->lieferant = (isset($_POST['lieferant'])?true:false);
	$tags = (isset($_POST['tags'])?$_POST['tags']:'');

	if($firma->save())
	{
		if ($firma->new)
			$firma_id=$firma->firma_id;

		if($tags!='')
		{
			$firma->tags = explode('; ',$tags);
			$firma->insertvon=$user;
			$firma->insertamum=date('Y-m-d H:i:s');
			$firma->savetags();
		}
	}
	else
	{
		return 'Datensatz konnte nicht gespeichert werden: '.$firma->errormsg;
	}

	return $firma_id;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/**
 * Standortliste
 */
function getStandortliste($firma_id,$adresstyp_arr,$user)
{
	// Init
	$htmlstr='';

	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';

	// Datenlesen
	$standort_obj = new standort();
	$standort_obj->result=array();
	if (!$standort_obj->load_firma($firma_id))
		return $standort_obj->errormsg;

	// Es gibt noch keinen Standort zur Firma - Neuanlage
	if ($firma_id && !$standort_obj->result)
	{
		$firma_obj = new firma();
		$firma_obj->load($firma_id);

		$standort_obj->new=true;
		$standort_obj->standort_id=null;
		$standort_obj->adresse_id=null;
		$standort_obj->kurzbz=mb_substr($firma_obj->name, 0, 16);
		$standort_obj->bezeichnung='';
		$standort_obj->updatevon=$user;
		$standort_obj->insertvon=$user;
		$standort_obj->ext_id=null;
		$standort_obj->firma_id=$firma_id;
		if (!$standort_obj->save())
			return 'Fehler Standort '.$standort_obj->errormsg;
		$standort_obj = new standort();
		$standort_obj->load_firma($firma_id);
	}

	$htmlstr.= '<table class="liste">
				<tr>
					<th>Kurzbz</th>
					<th>Nation</th>
					<th>Gemeinde</th>
					<th>Plz</th>
					<th>Ort</th>
					<th>Strasse</th>
					<th>Typ</th>
					<th><font size="0">Zustelladr.</font></th>

					<td align="center" valign="top" colspan="2"><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'"><input type="Button" value="Neuanlage" name="work"></a></td>
			</tr>';

	$i=1;
	foreach ($standort_obj->result as $row)
	{

		if ($firma_id  && $row->standort_id && !$row->adresse_id)
		{
			$adresse_obj = new adresse();
			$adresse_obj->new = true;
			$adresse_obj->insertamum = date('Y-m-d H:i:s');
			$adresse_obj->insertvon = $user;
			$adresse_obj->person_id=null;
			$adresse_obj->strasse = '';
			$adresse_obj->plz = '';
			$adresse_obj->ort = '';
			$adresse_obj->gemeinde = '';
			$adresse_obj->nation = '';
			$adresse_obj->typ = '';
			$adresse_obj->heimatadresse = false;
			$adresse_obj->zustelladresse = false;
			$adresse_obj->firma_id = null;
			$adresse_obj->updateamum = date('Y-m-d H:i:s');
			$adresse_obj->updatvon = $user;
			if(!$adresse_obj->save())
				return 'Fehler Adresse '.$adresse_obj->errormsg;

			$standort_obj = new standort($row->standort_id);
			$standort_obj->updatevon=$user;
			$standort_obj->adresse_id=$adresse_obj->adresse_id;
			if (!$standort_obj->save())
				return 'Fehler Standort - Adresse '.$standort_obj->errormsg;
			$row->adresse_id=$adresse_obj->adresse_id;
		}

		$htmlstr .= "<tr id='standort".$i."' class='liste". ($i%2) ."'>\n";
		$i++;

		$adresse_id=$row->adresse_id;
		$adresse_obj = new adresse();
		if ($adresse_obj->load($adresse_id))
		{
			$htmlstr.= '<td><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&adresse_id='.$adresse_obj->adresse_id.'">'.$row->kurzbz.'</a></td>';
			$htmlstr.= "<td title='Nation ".$adresse_obj->nation."'>".(isset($nation_arr[$adresse_obj->nation])?$nation_arr[$adresse_obj->nation]:$adresse_obj->nation)."</td>";
			$htmlstr.= '<td>'.$adresse_obj->gemeinde.'</td>';
			$htmlstr.= '<td>'.$adresse_obj->plz.'</td>';
			$htmlstr.= '<td>'.$adresse_obj->ort.'</td>';
			$htmlstr.= '<td>'.$adresse_obj->strasse.'</td>';
			$htmlstr.= '<td>'.$adresstyp_arr[$adresse_obj->typ].'</td>';
			$htmlstr.= '<td align="center">'.($adresse_obj->zustelladresse?'Ja':'Nein').'</td>';

			$htmlstr.= '<td align="center"><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&adresse_id='.$adresse_obj->adresse_id.'"><img src="../../skin/images/application_form_edit.png" alt="editieren" title="edit"/></a></td>';
			$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deleteadresse=true&standort_id=$row->standort_id&adresse_id=$adresse_obj->adresse_id&firma_id=$firma_id' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		}
		else
		{
			$htmlstr.= '<td><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&adresse_id='.$row->adresse_id.'">'.$row->kurzbz.'</a></td>';
			$htmlstr.= '<td colspan="10">'.$adresse_obj->errormsg.'</td>';
		}
		$htmlstr.= '</tr>';
	}
	$htmlstr.= '</table>';
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/**
 * Organisationsliste
 */
function getOrganisationsliste($firma_id,$adresstyp_arr,$user)
{
	// Init
	$htmlstr='';
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';

	$htmlstr.= '<table class="liste">
				<tr>
					<th width="30%">Organisationseinheit</th>
					<th width="25%">Bezeichnung</th>
					<th width="15%">Kundennummer</th>
					<td width="15%" align="center" valign="top" colspan="2"><a target="detail_workfirma"
						href="firma_detailwork.php?work=eingabeOrganisationseinheit&firma_organisationseinheit_id=&oe_kurzbz=&firma_id='.$firma_id.'">
						<input type="Button" value="Neuanlage" name="work"></a></td>
			</tr>
			';
	// Datenlesen zur Firma
	$firma = new firma();
	if (!$firma->get_firmaorganisationseinheit($firma_id))
		return $htmlstr.'</table>';
	$i=0;
	foreach ($firma->result as $row)
	{
		$htmlstr .= "<tr id='standort".$i."' class='liste". ($i%2) ."'>\n";
		$i++;
		$htmlstr.= '<td><a target="detail_workfirma" href="firma_detailwork.php?work=eingabeOrganisationseinheit&firma_organisationseinheit_id='.$row->firma_organisationseinheit_id.'&oe_kurzbz='.$row->oe_kurzbz.'&firma_id='.$firma_id.'">'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</a></td>';
		$htmlstr.= '<td>'.$row->fobezeichnung.'</td>';
		$htmlstr.= '<td align="center">'.$row->kundennummer.'</td>';
		$htmlstr.= '<td align="center"><a target="detail_workfirma" href="firma_detailwork.php?work=eingabeOrganisationseinheit&firma_organisationseinheit_id='.$row->firma_organisationseinheit_id.'&oe_kurzbz='.$row->oe_kurzbz.'&firma_id='.$firma_id.'"><img src="../../skin/images/application_form_edit.png" alt="editieren" title="edit"/></a></td>';
		$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deleteorganisationseinheit=true&firma_organisationseinheit_id=".$row->firma_organisationseinheit_id."&oe_kurzbz=".$row->oe_kurzbz."&firma_id=".$firma_id."' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		$htmlstr.= '</tr>';
	}

	$htmlstr.= '</table>';
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/**
 * Mobilitaetsprogrammliste
 */
function getMobilitaetsprogrammliste($firma_id,$user)
{
	// Init
	$htmlstr='';
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';

	$htmlstr.= '<table class="liste">
			<tr>
				<th width="45%">Mobilitätsprogramm</th>
				<th width="5%"></th>
				<td width="15%" align="center" valign="top" colspan="2">

				<form action="'.$_SERVER['PHP_SELF'].'?firma_id='.$firma_id.'&work=saveMobilitaetsprogramm" METHOD="POST">
					<SELECT name="mobilitaetsprogramm_code">';
	$mob = new mobilitaetsprogramm();
	$mob->getAll();

	foreach($mob->result as $row)
	{
		$htmlstr.= '<OPTION value="'.$mob->convert_html_chars($row->mobilitaetsprogramm_code).'">'.$mob->convert_html_chars($row->kurzbz).'</OPTION>';
	}
	$htmlstr.='
					</SELECT>
					<input type="submit" value="Hinzufügen">
				</form>
				</td>
			</tr>
			';

	// Datenlesen zur Firma
	$mob = new mobilitaetsprogramm();
	if (!$mob->getFirmaMobilitaetsprogramm($firma_id))
		return $htmlstr.'</table>';
	$i=0;
	foreach ($mob->result as $row)
	{
		$htmlstr .= "<tr class='liste". ($i%2) ."'>\n";
		$htmlstr.= '<td>'.$row->kurzbz.'</td>';
		$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deletemobilitaetsprogramm=true&firma_id=".$firma_id."&mobilitaetsprogramm_code=".$row->mobilitaetsprogramm_code."' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		$htmlstr.= '</tr>';
		$i++;
	}

	$htmlstr.= '</table>';
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
function saveMobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code)
{
	$firma = new firma();
	if($firma->addMobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code))
		return true;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/**
 * Anmerkungen
 */
function getAnmerkungen($firma_id,$user)
{
	// Init
	$htmlstr='';
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';

 	// Datenlesen zur Firma
	$firma = new firma();
	if($firma_id!='' && is_numeric($firma_id) )
	{
		if (!$firma->load($firma_id))
			return '<br>Firma mit der ID <b>'.$firma_id.'</b> existiert nicht';
	}
	else
	{
		return false;
	}
	$htmlstr.="<form id='addAnmerkungen' name='addAnmerkung' action='firma_details.php' method='POST'>\n";
	$htmlstr.="<input type='hidden' name='work' value='saveAnmerkungen'>\n";
	$htmlstr.="<input type='hidden' name='firma_id' value='".$firma->firma_id."'>\n";

	$htmlstr.= "<table class='liste'>";
	$htmlstr.= "<tr>";
	$htmlstr.= "<td>Anmerkungen:</td>";

	$htmlstr.="<td align='center' width='20%'><input type='submit' name='save' value='speichern'></td>\n";

	$htmlstr.= "</tr><tr><td colspan='2'><textarea cols='40' rows='6' style='width:100%' name='anmerkung'>".$firma->anmerkung."</textarea></td></tr>";
	$htmlstr.="</table></form>\n";
	return $htmlstr;
}
/**
 * Anmerkungen
 */
function saveAnmerkungen($firma_id,$user, $rechte)
{
	if(!$rechte->isBerechtigt('basis/firma:begrenzt',null, 'suid'))
		return 'Sie haben keine Berechtigung';

	$firma_obj = new firma();
	if(!$firma_obj->load($firma_id))
		return 'Firma konnte nicht geladen werden';

	if(!isset($_REQUEST['anmerkung']))
		return 'Anmerkung muss uebergeben werden';

	$firma_obj->anmerkung = $_REQUEST['anmerkung'];

	if(!$firma_obj->save())
		return 'Fehler beim Speichern:'.$firma_obj->errormsg;
	else
		return 'Anmerkung gespeichert!';

}
