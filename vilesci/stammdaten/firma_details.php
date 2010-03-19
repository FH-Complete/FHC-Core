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
	$firma_id = (isset($_REQUEST["firma_id"])?$_REQUEST['firma_id']:'');
		 
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/firma.class.php');
	require_once('../../include/standort.class.php');
	require_once('../../include/adresse.class.php');
	require_once('../../include/nation.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	// ******* INIT ********
	$user = get_uid();
	//Zugriffsrechte pruefen
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('basis/firma'))
		die('Sie haben keine Berechtigung für diese Seite');
	
	// Parameter einlesen
	$adresse_id = (isset($_REQUEST['adresse_id'])?$_REQUEST['adresse_id']:'');
	$standort_id = (isset($_REQUEST['standort_id'])?$_REQUEST['standort_id']:'');
	$oe_kurzbz = (isset($_REQUEST['oe_kurzbz'])?$_REQUEST['oe_kurzbz']:'');
	$firma_organisationseinheit_id = (isset($_REQUEST['firma_organisationseinheit_id'])?$_REQUEST['firma_organisationseinheit_id']:'');	
	
	$save = (isset($_REQUEST['save'])?$_REQUEST['save']:null);	
	$work = (isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['save'])?$_REQUEST['save']:null));	
	$ajax = (isset($_REQUEST['ajax'])?$_REQUEST['ajax']:null);	

	// Defaultwerte 
	$adresstyp_arr = array('h'=>'Hauptwohnsitz','n'=>'Nebenwohnsitz','f'=>'Firma',''=>'');
	$errorstr='';
	$tabselect=0;
	
	//Loeschen einer Adresse
	if(isset($_GET['deleteadresse']))
	{
		if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
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
?>
	<script language="JavaScript1.2" type="text/javascript">
		parent.frames[0].location.reload();
	</script>
<?php
	}
	//Loeschen einer Adresse
	if(isset($_GET['deleteorganisationseinheit']))
	{
		if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
		if(!empty($firma_organisationseinheit_id))
		{
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
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Firma - Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">

<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script src="../../include/js/jquery.js" type="text/javascript"></script>
<script src="../../include/js/jquery-ui.js" type="text/javascript"></script>
<script src="../../include/js/jquery.tools.min.js" type="text/javascript"></script>


<script src="../../include/js/jquery.autocomplete.js" type="text/javascript"></script>
<script src="../../include/js/jquery.autocomplete.min.js" type="text/javascript"></script>	

	<script type="text/javascript" language="JavaScript1.2">
		function confdel()
		{
			if(confirm("Diesen Datensatz wirklich loeschen?"))
				return true;
			return false;
		}

		function workFirmaDetail(wohin,welches)
		{
			$('.selector').tabs('option', 'selected', welches);
		    $("div##"+wohin).show("slow"); // div# langsam oeffnen
			$("div#"+wohin).html('<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >');
			var formdata = $('form#addFirma').serialize(); 
			//alert(formdata);
			$.ajax
				(
					{
						type: "POST", timeout: 3500,dataType: 'html',url: 'firma_details.php',data: formdata+'&ajax=1',
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

		$(function() 
		{
			$("ul.css-tabs").tabs("div.css-panes > div", {effect: 'ajax'}).history();
			$('.selector').tabs('option', 'selected', <?php echo $tabselect ?>)
		});

	</script>


<style type="text/css">
<!--

/* root element for tabs  */
ul.css-tabs {  
	margin:0 !important; 
	padding:0;
	height:30px;
	border-bottom:1px solid #666;	 	
}

/* single tab */
ul.css-tabs li {  
	float:left;	 
	padding:0; 
	margin:0;  
	list-style-type:none;	
}

/* link inside the tab. uses a background image */
ul.css-tabs a { 
	float:left;
	font-size:13px;
	display:block;
	padding:5px 30px;	
	text-decoration:none;
	border:1px solid #666;	
	border-bottom:0px;
	height:18px;
	background-color:#efefef;
	color:#777;
	margin-right:2px;
	-moz-border-radius-topleft: 4px;
	-moz-border-radius-topright:4px;
	position:relative;
	top:1px;	
}

ul.css-tabs a:hover {
	background-color:#F7F7F7;
	color:#333;
}
	
/* selected tab */
ul.css-tabs a.current {
	background-color:#ddd;
	border-bottom:2px solid #ddd;	
	color:#000;	
	cursor:default;
}
/* tab pane */
div.css-panes div {
	display:none;
	border:1px solid #666;
	border-width:0 1px 1px 1px;
	min-height:150px;
	padding:15px 20px;
	background-color:#ddd;	
}
-->
</style>

</head>
<body style="background-color:#eeeeee;">
<?php
	if (empty($firma_id))
		exit('');

##echo "$work <br>";

		//Tabs
		switch ($work)
		{
			case 'standortliste':
				echo getStandortliste($firma_id,$adresstyp_arr,$user);
				break;

			case 'organisationliste':
				echo getOrganisationsliste($firma_id,$adresstyp_arr,$user);
				break;
				
			case 'anmerkungsfeld':
				echo getAnmerkungen($firma_id,$user);
				break;

			case 'saveFirma':
				$status=saveFirma($user,$rechte); // Postdaten werden in der Funktion verarbeitet			
				if (is_numeric($status))
					$firma_id=$status;
				if (!$ajax)
					echo getFirmadetail($firma_id,$adresstyp_arr,$user);
				else if (is_numeric($status))
					echo "Daten erfolgreich gespeichert";
				if (!is_numeric($status))
					echo $status;
				break;
		    default:
				echo getFirmadetail($firma_id,$adresstyp_arr,$user);
				break;
		}
echo  ($errorstr?'<br>'.$errorstr:'');
echo '<script language="JavaScript1.2" type="text/javascript">
	<!--
		parent.frames[0].location.reload();
	-->		
	</script>';
?>	

</body>
</html>
<?php	
/*
	Firmenliste - lt. Suchekriterien 
*/
function getFirmadetail($firma_id,$adresstyp_arr,$user)	
{	
	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	// Init
	$htmlstr='';
	// Datenlesen zur Firma	
	$firma = new firma();
	if($firma_id!='' && is_numeric($firma_id) )
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
		//$htmlstr.="<td>&nbsp;</td>";	
		if($firma_id!='' && is_numeric($firma_id) )
			$htmlstr.="<td align='center' width='20%'><input type='Button' onclick=\"workFirmaDetail('addFirmaInfo', 0);\" name='save' value='speichern'></td>\n";
		else
			$htmlstr.="<td align='center' width='20%'><input type='submit' name='save' value='anlegen'></td>\n";
		$htmlstr.="</tr></table></td>";
		//$htmlstr.="<td rowspan='2'><table><tr>\n";	
		//$htmlstr.="<td valign='top'>Anmerkung: </td>";
		//$htmlstr.="<td><textarea cols='40' style='width:100%' name='anmerkung'/>".$firma->anmerkung."</textarea></td>\n";
		// Unterscheiden der Wartung - Neuanlage = Submit, Aendern = Ajax
		//if($firma_id!='' && is_numeric($firma_id) )
		//	$htmlstr.="<td>&nbsp;</td><td valign='bottom'><input type='Button' onclick=\"workFirmaDetail('addFirmaInfo');\" name='save' value='speichern'></td>\n";
		//else
		//	$htmlstr.="<td>&nbsp;</td><td valign='bottom'><input type='submit' name='save' value='anlegen'></td>\n";

	//$htmlstr.="</tr></table></td>";	
	$htmlstr.="</tr>\n";
	$htmlstr.="<tr><td><table><tr>\n";	
		$htmlstr.="<td>Steuernummer: </td>";
		$htmlstr.="<td><input size='32' maxlength='32' type='text' name='steuernummer' value=".$firma->steuernummer."></td>\n";
		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Finanzamt: </td>";
		// Finanzamt anzeige und suche
		$firma_finanzamt = new firma();
		$firmentyp_finanzamt='Finanzamt';
		$firma_finanzamt->searchFirma('',$firmentyp_finanzamt);	
		#var_dump($firma_finanzamt);
		$htmlstr.="<td><select name='finanzamt'>";
			$htmlstr.="<option value=''> </option>";
			foreach ($firma_finanzamt->result as $row_finazamt)
				$htmlstr.="	<option value='".$row_finazamt->standort_id ."'>".$row_finazamt->bezeichnung." </option>";
		$htmlstr.="</select></td>\n";

		$htmlstr.="<td>Aktiv: </td>";
		$htmlstr.="<td><input ".($firma->aktiv?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')." type='checkbox' name='aktiv' ".($firma->aktiv?'checked':'')."></td>\n";
		$htmlstr.="<td>&nbsp;</td>\n";
	
		$htmlstr.="<td>Gesperrt: </td>";
		
		$htmlstr.="<td><input ".($firma->gesperrt?' style="background-color: #FFF4F4;" ':' style="background-color: #E3FDEE;" ')." type='checkbox' name='gesperrt' ".($firma->gesperrt?'checked':'')."></td>\n";
		$htmlstr.="<td>&nbsp;</td>\n";
	
		$htmlstr.="<td>Schule:</td>";
		$htmlstr.="<td><input ".($firma->schule?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')."  type='checkbox' name='schule' ".($firma->schule?'checked':'')."> </td>";
		$htmlstr.="<td>&nbsp;</td>";	

	$htmlstr.="</tr></table></td>";	
	$htmlstr.="</tr>\n";
	$htmlstr.="	</table>\n";
	$htmlstr.="</form>\n";

	$htmlstr.='<div id="addFirmaInfo"></div>';

	$htmlstr.='
		<!-- Tabs --> 
		<ul class="css-tabs">
		     <li><a href="firma_details.php?work=standortliste&firma_id='.$firma_id.'">Standorte</a></li>
			 <li><a href="firma_details.php?work=organisationliste&firma_id='.$firma_id.'">Organisationseinheit</a></li>
			 <li><a href="firma_details.php?work=anmerkungsfeld&firma_id='.$firma_id.'">Anmerkungen</a></li>
		</ul>
		<div class="css-panes">
			<div style="display:block"></div>
		</div>	
		<div id="detailstandort">	</div>
		';
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/*
	FirmenDatenspeichern POST ( Ajax )
	Param  $user  Objekt vom Aktivenbenutzer
	Param  $recht Objekt der Rechte des Aktivenbenutzer
	Return firma_id oder Fehlertext
*/
function saveFirma($user,$rechte)
{
	// Speichern der Firmendaten
	if(!$rechte->isBerechtigt('basis/firma',null, 'suid'))
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
	$firma->anmerkung = (isset($_REQUEST['anmerkung'])?$_REQUEST['anmerkung']:'');
	$firma->firmentyp_kurzbz = (isset($_POST['typ'])?$_POST['typ']:'');
	$firma->updateamum = date('Y-m-d H:i:s');
	$firma->updatevon = $user;
	$firma->schule = isset($_POST['schule']);
		// Neu in Rel. 2.0 
	$firma->steuernummer = (isset($_POST['steuernummer'])?$_POST['steuernummer']:'');
	$firma->gesperrt = (isset($_POST['gesperrt'])?true:false);
	$firma->aktiv = (isset($_POST['aktiv'])?true:false);
	$firma->finanzamt = (isset($_POST['finanzamt'])?$_POST['finanzamt']:'');			
	if($firma->save())
	{
		if ($firma->new)
			$firma_id=$firma->firma_id;
	}
	else
	{
		return 'Datensatz konnte nicht gespeichert werden: '.$firma->errormsg;
	}
##	var_dump($firma);
	return $firma_id;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/*
	Standortliste
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
		$standort_obj->new=true;
		$standort_obj->standort_id=null;
		$standort_obj->adresse_id=null;
		$standort_obj->kurzbz='';
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
	
#var_dump($standort_obj);	
	$htmlstr.= '<table class="liste">
				<tr>
					<th>Kurzbez</th>
					<th>Nation</th>
					<th>Gemeinde</th>
					<th>Plz</th>
					<th>Ort</th>
					<th>Strasse</th>
					<th>Typ</th>
					<th><font size="0">Heimatadr.</font></th>
					<th><font size="0">Zustelladr.</font></th>
					<th>Ext.Id</th>
					<td align="center" valign="top" colspan="2"><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'"><input type="Button" value="Neuanlage" name="work"></a></td>
			</tr>';
#var_dump($standort_obj);
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
				$adresse_obj->heimatadresse = '';
				$adresse_obj->zustelladresse = '';
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
	//getDetailStandort(wohin,prameter)
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
				$htmlstr.= '<td align="center">'.($adresse_obj->heimatadresse?'Ja':'Nein').'</td>';
				$htmlstr.= '<td align="center">'.($adresse_obj->zustelladresse?'Ja':'Nein').'</td>';
				$htmlstr.= '<td align="center">'.$row->ext_id.'</td>';
				$htmlstr.= '<td align="center"><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&adresse_id='.$adresse_obj->adresse_id.'"><img src="../../skin/images/application_form_edit.png" alt="editieren" title="edit"/></a></td>';
				$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deleteadresse=true&standort_id=$row->standort_id&adresse_id=$adresse_obj->adresse_id&firma_id=$firma_id' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
			}
			else
				$htmlstr.= '<td><a target="detail_workfirma" href="firma_detailwork.php?showmenue=1&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&adresse_id='.$row->adresse_id.'">'.$row->kurzbz.'</a></td>';
				$htmlstr.= '<td colspan="10">'.$adresse_obj->errormsg.'</td>';					
		$htmlstr.= '</tr>';
	}
	$htmlstr.= '</table>';
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/*
	Organisationsliste
*/
function getOrganisationsliste($firma_id,$adresstyp_arr,$user)
{
	// Init
	$htmlstr='';
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
 
 	// Datenlesen zur Firma	
	$firma = new firma();
	if (!$firma->get_firmaorganisationseinheit($firma_id))
		return '<br>Firma ID <b>'.$firma_id.'</b> '.$firma->errormsg;;
		
##	var_dump($firma);
	$htmlstr.= '<table class="liste">
				<tr>
					<th width="30%">Kurzbezeichnung</th>
					<th width="15%">Typ</th>
					<th width="25%">Bezeichnung</th>
					<th width="15%">Kundennummer</th>
					<td width="15%" align="center" valign="top" colspan="2"><a target="detail_workfirma" 
						href="firma_detailwork.php?work=eingabeOrganisationseinheit&firma_organisationseinheit_id=&oe_kurzbz=&firma_id='.$firma_id.'">
						<input type="Button" value="Neuanlage" name="work"></a></td>
			</tr>
			';
	$i=0;
	foreach ($firma->result as $row)
	{
		$htmlstr .= "<tr id='standort".$i."' class='liste". ($i%2) ."'>\n";
		$i++;
			$htmlstr.= '<td><a target="detail_workfirma" href="firma_detailwork.php?work=eingabeOrganisationseinheit&firma_organisationseinheit_id='.$row->firma_organisationseinheit_id.'&oe_kurzbz='.$row->oe_kurzbz.'&firma_id='.$firma_id.'">'.$row->oe_kurzbz.'</a></td>';
			$htmlstr.= '<td>'.$row->organisationseinheittyp_kurzbz.'</td>';
			$htmlstr.= '<td>'.$row->bezeichnung.'</td>';

			$htmlstr.= '<td align="center">'.$row->kundennummer.'</td>';
			$htmlstr.= '<td align="center"><a target="detail_workfirma" href="firma_detailwork.php?work=eingabeOrganisationseinheit&firma_organisationseinheit_id='.$row->firma_organisationseinheit_id.'&oe_kurzbz='.$row->oe_kurzbz.'&firma_id='.$firma_id.'"><img src="../../skin/images/application_form_edit.png" alt="editieren" title="edit"/></a></td>';
			$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deleteorganisationseinheit=true&firma_organisationseinheit_id=".$row->firma_organisationseinheit_id."&oe_kurzbz=".$row->oe_kurzbz."&firma_id=".$firma_id."' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		$htmlstr.= '</tr>';
	}
	
	$htmlstr.= '</table>';
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/*
	Anmerkungen
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
	$htmlstr.="<form id='addFirma' name='addAnmerkung' action='firma_details.php' method='POST'>\n";
	$htmlstr.="<input type='hidden' name='work' value='saveFirma'>\n";	
	$htmlstr.="<input type='hidden' name='firma_id' value='".$firma->firma_id."'>\n";

	$htmlstr.= "<table class='liste'>";
	$htmlstr.= "<tr>";
	$htmlstr.= "<td>Anmerkungen:</td>";
	if($firma_id!='' && is_numeric($firma_id) )
		$htmlstr.="<td align='center' width='20%'><input type='Button' onclick=\"workFirmaDetail('addFirmaInfo', 2);\" name='save' value='speichern'></td>\n";
	else
		$htmlstr.="<td align='center' width='20%'><input type='submit' name='save' value='anlegen'></td>\n";
	$htmlstr.= "</tr><tr><td colspan='2'><textarea cols='40' rows='6' style='width:100%' name='anmerkung'>".$firma->anmerkung."</textarea></td></tr>";
	$htmlstr.="</form>\n";
	return $htmlstr;
}
?>
