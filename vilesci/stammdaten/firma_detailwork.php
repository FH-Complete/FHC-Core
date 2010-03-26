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

require_once('../../include/funktion.class.php');

require_once('../../include/standort.class.php');
require_once('../../include/adresse.class.php');

require_once('../../include/kontakt.class.php');
require_once('../../include/person.class.php');

require_once('../../include/organisationseinheit.class.php');

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
$errorstr='';
$tabselect=0;

$standort_id = (isset($_REQUEST['standort_id'])?$_REQUEST['standort_id']:'');
$adresse_id = (isset($_REQUEST['adresse_id'])?$_REQUEST['adresse_id']:'');
$kontakt_id = (isset($_REQUEST['kontakt_id'])?$_REQUEST['kontakt_id']:'');	
$oe_kurzbz= (isset($_REQUEST['oe_kurzbz'])?$_REQUEST['oe_kurzbz']:'');	
$oe_parent_kurzbz= (isset($_REQUEST['oe_parent_kurzbz'])?$_REQUEST['oe_parent_kurzbz']:'');	
$work = (isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['save'])?$_REQUEST['save']:null));	
$showmenue = (isset($_REQUEST['showmenue'])?$_REQUEST['showmenue']:false);		
$personfunktionstandort_id = (isset($_REQUEST['personfunktionstandort_id'])?$_REQUEST['personfunktionstandort_id']:'');	
$firma_organisationseinheit_id = (isset($_REQUEST['firma_organisationseinheit_id'])?$_REQUEST['firma_organisationseinheit_id']:'');	
if(isset($_REQUEST['nation']) && $_REQUEST['nation']=="A" && isset($_REQUEST['gemeinde_combo']) && isset($_REQUEST['ort_combo']))
{
	$_REQUEST['gemeinde']=$_REQUEST['gemeinde_combo'];
	$_REQUEST['ort']=$_REQUEST['ort_combo'];
}


function getGemeindeDropDown($postleitzahl)
{
	global $db, $_REQUEST, $gemeinde;
	$return='';
	$found=false;
	$firstentry='';
	$gemeinde_x = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
	$qry = "SELECT distinct name FROM bis.tbl_gemeinde WHERE plz='".addslashes($postleitzahl)."'";
	$return.= '<SELECT id="gemeinde_combo" name="gemeinde_combo" onchange="loadOrtData()">';
	if(is_numeric($postleitzahl) && $postleitzahl<10000)
	{
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($firstentry=='')
					$firstentry=$row->name;
				if($gemeinde_x=='')
					$gemeinde_x=$row->name;
				
				if($row->name==$gemeinde_x)
				{
					$selected='selected';
					$found=true;
				}
				else
					$selected='';
				$return.= "<option value='$row->name' $selected>$row->name</option>";
			}
		}
	}
	
	$return.= '</SELECT>';
	if(!$found && (isset($importort) && $importort!=''))
	{
		$return.= $importort;
	}
	$gemeinde = $gemeinde_x;
	return $return;
}

if(isset($_GET['type']) && $_GET['type']=='getgemeindecontent' && isset($_GET['plz']))
{
	header('Content-Type: text/html; charset=UTF-8');

	echo getGemeindeDropDown($_GET['plz']);
	exit;
}

function getOrtDropDown($postleitzahl, $gemeindename)
{
	global $db, $_REQUEST;
	$return='';
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$qry = "SELECT distinct ortschaftsname FROM bis.tbl_gemeinde 
			WHERE plz='".addslashes($postleitzahl)."' AND name='".addslashes($gemeindename)."'";
	$return.='<SELECT id="ort_combo" name="ort_combo">';
	if(is_numeric($postleitzahl) && $postleitzahl<10000)
	{
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($row->ortschaftsname==$ort)
					$selected='selected';
				else 
					$selected='';
				$return.= "<option value='$row->ortschaftsname' $selected>$row->ortschaftsname</option>";
			}
		}
	}
	
	$return.= '</SELECT>';
	return $return;
}
if(isset($_GET['type']) && $_GET['type']=='getortcontent' && isset($_GET['plz']) && isset($_GET['gemeinde']))
{
	header('Content-Type: text/html; charset=UTF-8');
	
	echo getOrtDropDown($_GET['plz'], $_GET['gemeinde']);
	exit;
}

	// Defaultwerte 
	$adresstyp_arr = array('h'=>'Hauptwohnsitz','n'=>'Nebenwohnsitz','f'=>'Firma',''=>'');

##	var_dump($_REQUEST);	

	//Loeschen einer Adresse
	if(isset($_GET['deleteadresse']))
	{
		$showmenue=1;	
		if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
			
		if(is_numeric($standort_id))
		{
			$standort_obj = new standort();
			if(!$standort_obj->delete($standort_id))
			{
				$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Standort:'.$standort_obj->errormsg;
			}
			$standort_obj = new standort();
			if(!$standort_obj->deletepersonfunktionstandort('',$standort_id))
			{
				$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Person Funktion Standort:'.$standort_obj->errormsg;
			}

		}
		if(is_numeric($adresse_id))
		{
			$adresse_obj = new adresse();
			if(!$adresse_obj->delete($adresse_id))
			{
				$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen der Adresse:'.$adresse_obj->errormsg;
			}
		}
	}
	
	if(isset($_GET['deletekontakt']))
	{
		$showmenue=1;	
		if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
			
		if(is_numeric($kontakt_id))
		{
			$kontakt_obj = new kontakt();
			if(!$kontakt_obj->delete($kontakt_id))
			{
				$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Standort:'.$kontakt_obj->errormsg;
			}
		}
	}
	
	if(isset($_GET['deletepersonfunktionstandort']))
	{
		$showmenue=1;
		if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
			
		if(is_numeric($personfunktionstandort_id))
		{
			$standort_obj = new standort();
			if(!$standort_obj->deletepersonfunktionstandort($personfunktionstandort_id))
			{
				$errorstr=($errorstr?$errorstr.', ':'').'Fehler beim Loeschen Person Funktion Standort:'.$standort_obj->errormsg;
			}
		}
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Firma - Detailwork</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/jquery.css" rel="stylesheet" type="text/css">


<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>

<script src="../../include/js/jquery.js" type="text/javascript"></script>
<script src="../../include/js/jquery-ui.js" type="text/javascript"></script>


<script src="../../include/js/jquery.autocomplete.js" type="text/javascript"></script>
<script src="../../include/js/jquery.autocomplete.min.js" type="text/javascript"></script>

<script src="../../include/js/jquery.tools.min.js" type="text/javascript"></script>



<script type="text/javascript" language="JavaScript1.2">
	


// **************************************
// * XMLHttpRequest Objekt erzeugen
// **************************************
var anfrage = null;

function erzeugeAnfrage()
{
	try
	{
		anfrage = new XMLHttpRequest();
	}
	catch (versuchmicrosoft)
	{
		try
		{
			anfrage = new ActiveXObject("Msxml12.XMLHTTP");
		}
		catch (anderesmicrosoft)
		{
			try
			{
				anfrage = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (fehlschlag)
			{
				anfrage = null;
            }
        }
    }
	if (anfrage == null)
		alert("Fehler beim Erstellen des Anfrageobjekts!");
}

//Gemeinde DropDown holen wenn Nation Oesterreich
function loadGemeindeData()
{
	if(document.getElementById('nation').value=='A')
	{
		anfrage=null;
		erzeugeAnfrage(); 
	    var jetzt = new Date();
		var ts = jetzt.getTime();
		var plz = document.getElementById('plz').value;
	    var url= '<?php echo $_SERVER['PHP_SELF']."?type=getgemeindecontent"?>';
	    url += '&plz='+plz+"&"+ts;
	    anfrage.open("GET", url, true);
	    anfrage.onreadystatechange = setGemeindeData;
	    anfrage.send(null);
	    document.getElementById('gemeinde').style.display='none';
		document.getElementById('ort').style.display='none';
	}
	else
	{
		document.getElementById('gemeindediv').innerHTML='';
		document.getElementById('ortdiv').innerHTML='';
		document.getElementById('gemeinde').style.display='block';
		document.getElementById('ort').style.display='block';

	}
}

function setGemeindeData()
{
	if (anfrage.readyState == 4)
	{
		if (anfrage.status == 200) 
		{
			var resp = anfrage.responseText;
            var gemeindediv = document.getElementById('gemeindediv');
			gemeindediv.innerHTML = resp;
			gemeindediv.style.display = 'block';
			gemeindediv.style.border = 0;
			gemeindediv.style.padding = 0;
			gemeindediv.style.minHeight=0;
			loadOrtData();
        } 
        else alert("Request status:" + anfrage.status);
    }
}

function loadOrtData()
{
	if(document.getElementById('gemeinde'))
	{
		anfrage=null;
		//Request erzeugen und die Note speichern
		erzeugeAnfrage(); 
	    var jetzt = new Date();
		var ts = jetzt.getTime();
		var plz = document.getElementById('plz').value;
		var gemeinde = document.getElementById('gemeinde_combo').value;
	    var url= '<?php echo $_SERVER['PHP_SELF']."?type=getortcontent"?>';
	    url += '&plz='+plz+"&gemeinde="+encodeURIComponent(gemeinde)+"&"+ts;
	    anfrage.open("GET", url, true);
	    anfrage.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	    anfrage.onreadystatechange = setOrtData;
	    anfrage.send(null);
	}
}

function setOrtData()
{
	if (anfrage.readyState == 4)
	{
		if (anfrage.status == 200) 
		{
			var resp = anfrage.responseText;
            var ortdiv = document.getElementById('ortdiv');
			ortdiv.innerHTML = resp;
			ortdiv.style.display = 'block';
			ortdiv.style.border = 0;
			ortdiv.style.padding = 0;
			ortdiv.style.minHeight=0;
        } 
        else alert("Request status:" + anfrage.status);
    }
}	
	
	
	
	
	
	
	
		function confdel()
		{
			if(confirm("Diesen Datensatz wirklich loeschen?"))
				return true;
			return false;
		}
	
		function workDetailRecord(wohin,formname)
		{
		    $("div#"+wohin).show("slow"); // div# langsam oeffnen
			$("div#"+wohin).html('<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >');
			var formdata = $('form#'+formname).serialize(); 
			//alert(formdata);
			$.ajax
				(
					{
						type: "POST", timeout: 3500,dataType: 'html',url: 'firma_detailwork.php',data: formdata,
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
		function callUrl(wohin,urldata)
		{
		    $("div#"+wohin).show("slow"); // div# langsam oeffnen
			$("div#"+wohin).html('<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >');
			$.ajax
				(
					{
						type: "POST", timeout: 3500,dataType: 'html',url: 'firma_detailwork.php',data: urldata,
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
		$("ul.css-tabsDetail").tabs("div.css-panes > div", {effect: 'ajax'}).history();
	});
</script>



<style type="text/css">
<!--
/* root element for tabs  */
ul.css-tabsDetail {  
	margin:0 !important; 
	padding:0;
	height:30px;
	border-bottom:1px solid #666;	 	
}

/* single tab */
ul.css-tabsDetail li {  
	float:left;	 
	padding:0; 
	margin:0;  
	list-style-type:none;	
}

/* link inside the tab. uses a background image */
ul.css-tabsDetail a { 
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

ul.css-tabsDetail a:hover {
	background-color:#F7F7F7;
	color:#333;
}
	
/* selected tab */
ul.css-tabsDetail a.current {
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
div.css-panes div div{
	display:block;
	border:0px solid #666;
	min-height:0px;
	padding:0px 0px;
	background-color:#ddd;
}
-->
</style>


</head>
<body style="background-color:#eeeeee;">
<?php
	if (empty($firma_id))
		exit('');

#var_dump($_REQUEST);
##exit;
##echo "$work <br>";

		$htmlcode='';
		switch ($work)
		{
			case 'listKontakte':
				$htmlcode=listKontakte($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte);
				$tabselect=2;
				break;
			case 'eingabeKontakt':
				$htmlcode=eingabeKontakt($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte);
				$tabselect=2;
				break;
			case 'saveKontakt':
				$htmlcode=saveKontakt($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte,$rechte);
				$tabselect=2;
				break;

			case 'listPersonenfunktionen':
				$htmlcode=getlistPersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte);
				$tabselect=1;
				break;
			case 'eingabePersonenfunktionen':
				$htmlcode=eingabePersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte);
				$tabselect=1;
				break;
			case 'savePersonenfunktionen':
				$htmlcode=savePersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte);
				$tabselect=1;
				break;
			case 'saveStandort':
				$htmlcode=saveStandort($firma_id,$standort_id,$adresse_id,$adresstyp_arr,$user,$rechte);
				$tabselect=0;
				break;		
				
			case 'saveFirmaorganisationseinheit':
				$firma_organisationseinheit_id=saveFirmaorganisationseinheit($firma_id,$firma_organisationseinheit_id,$oe_kurzbz,$oe_parent_kurzbz,$adresstyp_arr,$user,$rechte);
				if (!is_numeric($firma_organisationseinheit_id))
				{
					$htmlcode=$firma_organisationseinheit_id;
					$firma_organisationseinheit_id='';
				}
				$htmlcode.=eingabeOrganisationseinheit($firma_id,$firma_organisationseinheit_id,$oe_kurzbz,$adresstyp_arr,$user,$rechte);
				$htmlcode.='<script language="JavaScript1.2" type="text/javascript">
						parent.frames[1].location.reload();
						</script>';
				break;		
			case 'eingabeOrganisationseinheit':
				$htmlcode=eingabeOrganisationseinheit($firma_id,$firma_organisationseinheit_id,$oe_kurzbz,$adresstyp_arr,$user,$rechte);
				break;		
			case 'saveOrganisationseinheit':
				$htmlcode=saveOrganisationseinheit($firma_id,$firma_organisationseinheit_id,$oe_kurzbz,$oe_parent_kurzbz,$adresstyp_arr,$user,$rechte);
				break;		


		    default:
				if (!$showmenue)
					$htmlcode=getStandort($firma_id,$standort_id,$adresse_id,$adresstyp_arr,$user,$rechte);
				break;
		}
		
		if ($showmenue)
			echo '<!-- Tabs --> 
			<ul class="css-tabsDetail">
			     <li '.($tabselect==0?"class=current":"").'><a href="firma_detailwork.php?work=standort&firma_id='.$firma_id.'&standort_id='.$standort_id.'&adresse_id='.$adresse_id.'">Standort</a></li>
				 <li '.($tabselect==1?"class=current":"").'><a href="firma_detailwork.php?work=listPersonenfunktionen&firma_id='.$firma_id.'&standort_id='.$standort_id.'&adresse_id='.$adresse_id.'">Ansprechpartner</a></li>
				 <li '.($tabselect==2?"class=current":"").'><a href="firma_detailwork.php?work=listKontakte&firma_id='.$firma_id.'&standort_id='.$standort_id.'&adresse_id='.$adresse_id.'">Kontakte</a></li>
			</ul>
			<div class="css-panes">
				<div style="display:block" id="detail">'.$htmlcode.'</div>
			</div>
			<br />	
			<div id="detailworkinfodiv"></div>
			';
		else	
			echo $htmlcode;	
	echo  ($errorstr?'<br>'.$errorstr:'');	
?>	
</body>
</html>
<?php	


#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Organisation zur Firma
*/
function saveFirmaorganisationseinheit($firma_id,$firma_organisationseinheit_id,$oe_kurzbz,$oe_parent_kurzbz,$adresstyp_arr,$user,$rechte)
{
	// Init
	$htmlstr='';
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
 
 	// Datenlesen zur Firma	
	$firma = new firma();
	
	if($firma_organisationseinheit_id!='' && is_numeric($firma_organisationseinheit_id) )
	{
		if($firma->load_firmaorganisationseinheit($firma_organisationseinheit_id))
		{
			$firma->new = false;
		}
		else
		{
			return 'Firmaorganisationseinheit wurde nicht gefunden: '.$firma_organisationseinheit_id;
		}
	}
	else
	{
		$firma->new = true;
		$firma->insertamum = date('Y-m-d H:i:s');
		$firma->insertvon = $user;
	}

    $firma->firma_id=$firma_id;

	$oe_kurzbz = (isset($_POST['oe_kurzbz'])?$_POST['oe_kurzbz']:null);
	
	$kundennummer = (isset($_POST['kundennummer'])?$_POST['kundennummer']:null);
	$bezeichnung = (isset($_POST['bezeichnung'])?$_POST['bezeichnung']:'');
	$ext_id = (isset($_POST['ext_id'])?$_POST['ext_id']:null);
	$oe_parent_kurzbz = (isset($_POST['oe_parent_kurzbz'])?$_POST['oe_parent_kurzbz']:'');
	$organisationseinheit_obj = new organisationseinheit();
	if ($oe_kurzbz)
	{
		if (!$organisationseinheit_obj->load($oe_kurzbz))
		{
			echo 'Organisation fehler '.$organisationseinheit_obj->errormsg;
			return false;
		}	
		//$bezeichnung=($bezeichnung?$bezeichnung:$organisationseinheit_obj->bezeichnung);
	}	
	else
	{
		echo 'Organisation fehlt';
		return false;
	}	
	
	$firma->updateamum = date('Y-m-d H:i:s');
	$firma->updatevon = $user;
		
    $firma->oe_kurzbz=$organisationseinheit_obj->oe_kurzbz;
	$firma->bezeichnung=$bezeichnung;
	$firma->kundennummer=$kundennummer; 
	$firma->ext_id=$ext_id; 	

	if($firma->get_firmaorganisationseinheit($firma->firma_id, $firma->oe_kurzbz) && $firma_organisationseinheit_id=='')
	{
		echo "Organisationseinheit ".$firma->oe_kurzbz." ist bereits zugeteilt!";
		return false;
	}
	if (!$firma->saveorganisationseinheit())
		echo $firma->errormsg;
	return $firma->firma_organisationseinheit_id;
}
#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Organisation zur Firma
*/
function eingabeOrganisationseinheit($firma_id,$firma_organisationseinheit_id,$oe_kurzbz,$adresstyp_arr,$user,$rechte)
{
	##var_dump($_REQUEST);

	// Init
	$htmlstr='';
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
 
 	// Datenlesen zur Firma	
	$firma = new firma();
	if ($firma_organisationseinheit_id && !$firma->get_firmaorganisationseinheit($firma_id,$oe_kurzbz))
		return '<br>Fehler Firma ID <b>'.$firma_id.'</b> '.$firma->errormsg;

	$htmlstr.= '<table class="liste">
			<tr>
				<th>Bezeichnung Organisationseinheit</th>';
				//<th>Kurzbezeichnung</th>
	$htmlstr.= '<th>Bezeichnung</th>
				<th>Kundennummer</th>
				<th>&nbsp;</th>
			</tr>
			';
	//Kontakttype laden
	$organisationseinheit_obj = new organisationseinheit();
	$organisationseinheit_obj->getAll();


	$i=0;
	foreach ($firma->result as $row)
	{
		$htmlstr.="<form id='addFirmaorganisationseinheit".$i."' name='addFirmaorganisationseinheit".$i."' action='firma_detailwork.php' method='POST'>\n";
			$htmlstr.="<tr class='liste". ($i%2) ."'>\n";
				$i++;
				$htmlstr.= "<td><SELECT name='oe_kurzbz'>";
				for ($ii=0;$ii<count($organisationseinheit_obj->result);$ii++)
				{
					$htmlstr.= "<OPTION value='".$organisationseinheit_obj->result[$ii]->oe_kurzbz."' ".($organisationseinheit_obj->result[$ii]->oe_kurzbz==$row->oe_kurzbz?' selected ':'')." >".$organisationseinheit_obj->result[$ii]->bezeichnung."</OPTION>";
				}
				$htmlstr.= "</SELECT>
					<input type='Hidden' name='firma_organisationseinheit_id' value='".$row->firma_organisationseinheit_id."'>
					<input type='Hidden' name='oe_parent_kurzbz' value='".$row->oe_parent_kurzbz."'>";
					//<input type='Hidden' name='kundennummer' value='".$row->kundennummer."'>
				$htmlstr.= "<input type='Hidden' name='firma_id' value='".$firma_id."'>
					<input type='Hidden' name='work' value='saveFirmaorganisationseinheit'>
					</td>
					";

					//$htmlstr.= '<td>'.$row->organisationseinheittyp_kurzbz.'</td>';
					//$htmlstr.= '<td>'.$row->oe_kurzbz.'</td>';
					$htmlstr.= "<td><input type='text' name='bezeichnung' value='".$row->fobezeichnung."' size='50' maxlength='256'></td>";
					$htmlstr.= "<td><input type='text' name='kundennummer' value='".$row->kundennummer."' size='20' maxlength='128'></td>";
					$htmlstr.= '<td><input type="Submit" value="speichern" ></td>';			
			$htmlstr.= '</tr>';
		$htmlstr.="</form>\n";
	}
	if (!$firma_organisationseinheit_id)	
	{	
		$i++;
		$htmlstr.="<form id='addFirmaorganisationseinheit' name='addFirmaorganisationseinheit".$i."' action='firma_detailwork.php' method='POST'>\n";
		$htmlstr.="<tr class='liste". ($i%2) ."'>\n";
		$i++;
		$htmlstr.= "<td><SELECT name='oe_kurzbz'>";
		for ($ii=0;$ii<count($organisationseinheit_obj->result);$ii++)
		{
			$htmlstr.= "<OPTION value='".$organisationseinheit_obj->result[$ii]->oe_kurzbz."' ".($organisationseinheit_obj->result[$ii]->oe_kurzbz==''?' selected ':'')." >".$organisationseinheit_obj->result[$ii]->bezeichnung."</OPTION>";
		}
		$htmlstr.= "</SELECT>
			<input type='Hidden' name='firma_organisationseinheit_id' value=''>";
			//<input type='Hidden' name='kundennummer' value=''>
		$htmlstr.= "<input type='Hidden' name='firma_id' value='".$firma_id."'>
			<input type='Hidden' name='work' value='saveFirmaorganisationseinheit'>
		</td>
		";
		//$htmlstr.= '<td></td>';
		$htmlstr.= "<td><input type='text' name='bezeichnung' size='50' maxlength='256'></td>";
		$htmlstr.= "<td><input type='text' name='kundennummer' size='20' maxlength='128'></td>";
		$htmlstr.= '<td><input type="Submit" value="speichern" ></td>';			
		$htmlstr.= '</tr>';
		$htmlstr.="</form>\n";
	}
	
	$htmlstr.= '</table>';
	return $htmlstr;
	
}
#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Kontakte zu Firmen,Standorte in Listenform
*/
function saveKontakt($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte)	
{	
	if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
		return 'Sie haben keine Berechtigung fuer diese Aktion - Kontakte';
		
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
	if (empty($standort_id) || !is_numeric($standort_id) )
		return 'Standort fehlt.';

	// Init
	$htmlstr='';

	// Datenlesen
	$person_id = (isset($_POST['person_id'])?$_POST['person_id']:null);
	$kontakttyp = (isset($_POST['kontakttyp'])?$_POST['kontakttyp']:'');
	$anmerkung = (isset($_POST['anmerkung'])?$_REQUEST['anmerkung']:'');
	$kontakt = (isset($_POST['kontakt'])?$_REQUEST['kontakt']:'');
	$zustellung = (isset($_POST['zustellung'])?true:false);		
	$ext_id = (isset($_POST['ext_id'])?$_POST['ext_id']:'');

	if (strstr($kontakttyp,'mail') && (!strstr($kontakt,'@') || !strstr($kontakt,'.')) ) 
		return 'Kontakt (Typ '.$kontakttyp .') bitte pr&uuml;fen';

	//----------------------------------------		
	//	ADRESSEN Neuanlage - Aenderung
	//----------------------------------------				
	$kontakt_obj = new kontakt();
	if($kontakt_id!='' && is_numeric($kontakt_id) )
	{
		if($kontakt_obj->load($kontakt_id))
		{
			$kontakt_obj->new = false;
		}
		else
		{
			return 'Kontakt wurde nicht gefunden:'.$kontakt_id;
		}
	}
	else
	{
		$kontakt_obj->new = true;
		$kontakt_obj->insertamum = date('Y-m-d H:i:s');
		$kontakt_obj->insertvon = $user;
	}
	$kontakt_obj->person_id=null;
	$kontakt_obj->firma_id=null;
	$kontakt_obj->standort_id=$standort_id;
	$kontakt_obj->kontakttyp = $kontakttyp;
	$kontakt_obj->anmerkung = $anmerkung;
	$kontakt_obj->kontakt = $kontakt;
	$kontakt_obj->zustellung = $zustellung;
	$kontakt_obj->updateamum = date('Y-m-d H:i:s');
	$kontakt_obj->updatvon = $user;
	$kontakt_obj->ext_id=($ext_id?$ext_id:null);
	if(!$kontakt_obj->save())
		return 'Fehler beim Speichern des Kontakt '.$kontakttyp .' '.$kontakt.' ID '. $kontakt_id.' :'.$kontakt_obj->errormsg;
	if ($kontakt_obj->new)
		$kontakt_id=$kontakt_obj->kontakt_id;
		
	//frame reloaden, um wieder zur liste zu kommen
	echo '<script language="JavaScript1.2" type="text/javascript">
	<!--
		parent.frames[2].location.reload();
	-->		
	</script>';
	return 'Kontakt: '.$kontakttyp .' '.$kontakt.' ID '. $kontakt_id.' gespeichert';
}

#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Kontakte zu Firmen, Standorte in Listenform
*/
function listKontakte($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte)	
{	
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
	if (empty($standort_id) || !is_numeric($standort_id) )
		return 'Standort fehlt.';

	// Init
	$htmlstr='';

	// Datenlesen
	$kontakt_obj = new kontakt();
	if($standort_id!='' && is_numeric($standort_id) )
	{
		$kontakt_obj->result=array();
		if (!$kontakt_obj->load_standort($standort_id))
		{
			if ($kontakt_obj->errormsg)
				return $kontakt_obj->errormsg;
			else
				return eingabeKontakt($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte);
		}		
	}
##var_dump($kontakt_obj);

	$htmlstr.= '<table class="liste">
				<tr>
					<th>ID</th>
					<th>Typ</th>
					<th>Kontakt</th>
					<th>Anmerkung</th>
					<th>Zustellung</th>
					<th>Ext.Id</th>
					<td align="center" valign="top" colspan="2"><a target="detail_workfirma" href="javascript:callUrl(\'detail\',\'work=eingabeKontakt&firma_id='.$firma_id.'&standort_id='.$standort_id.'&kontakt_id=\');"><input type="Button" value="Neuanlage" name="work"></a></td>
			</tr>';

	$i=0;
	foreach ($kontakt_obj->result as $row)
	{
		$htmlstr .= "<tr id='kontakt".$i."' class='liste". ($i%2) ."'>\n";
		$i++;
			$htmlstr.= '<td><a target="detail_workfirma" href="javascript:callUrl(\'detail\',\'work=eingabeKontakt&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&kontakt_id='.$row->kontakt_id.'\');">'.$row->kontakt_id.'</a></td>';
			$htmlstr.= '<td>'.$row->kontakttyp.'</td>';
			$htmlstr.= '<td>'.$row->kontakt.'</td>';
			$htmlstr.= '<td>'.$row->anmerkung.'</td>';
			$htmlstr.= '<td align="center">'.($row->zustellung?'Ja':'Nein').'</td>';
			//$htmlstr.= '<td>'.$row->person_id.'</td>';
			$htmlstr.= '<td align="center">'.$row->ext_id.'</td>';
			
			$htmlstr.= '<td align="center"><a target="detail_workfirma" href="javascript:callUrl(\'detail\',\'work=eingabeKontakt&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&kontakt_id='.$row->kontakt_id.'\');"><img src="../../skin/images/application_form_edit.png" alt="editieren" title="editieren"/></a></td>';
			$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deletekontakt=true&firma_id=$firma_id&standort_id=$row->standort_id&kontakt_id=$row->kontakt_id' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		$htmlstr.= '</tr>';
	}
	$htmlstr.= '</table>';
	return $htmlstr;
}


#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Kontakte zu Firmen,Standorte in Listenform
*/
function eingabeKontakt($firma_id,$standort_id,$adresse_id,$kontakt_id,$adresstyp_arr,$user,$rechte)	
{	
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma f&uuml;r Kontakt fehlt.';

	if (empty($standort_id) || !is_numeric($standort_id) )
		return 'Standort f&uuml;r Kontakt fehlt.';
	
	// Init
	$htmlstr='';

	// Datenlesen
	$kontakt_obj = new kontakt();
	if($kontakt_id!='' && is_numeric($kontakt_id) )
	{
		$kontakt_obj->result=array();
		if (!$kontakt_obj->load($kontakt_id))
			return $kontakt_obj->errormsg;
	}
	else 
	{
##		$kontakt_obj->firma_id=$firma_id;
		$kontakt_obj->standort_id=$standort_id;
	}
	$htmlstr.="<form id='addKontakt' name='addKontakt' action='firma_detailwork.php' method='POST'>\n";
		$htmlstr.="<input type='hidden' name='work' value='saveKontakt'>\n";	
		$htmlstr.="<input type='hidden' name='firma_id' value='".$firma_id."'>\n";
		$htmlstr.="<input type='hidden' name='adresse_id' value='".$adresse_id."'>\n";
		$htmlstr.="<input type='hidden' name='standort_id' value='".$standort_id."'>\n";
		$htmlstr.="<input type='hidden' name='person_id' value='".$kontakt_obj->person_id."'>\n";		
		$htmlstr.="<input type='hidden' name='kontakt_id' value='".$kontakt_obj->kontakt_id."'>\n";
		$htmlstr.="<input type='hidden' name='ext_id' value='".$kontakt_obj->ext_id."'>\n";
		
	$htmlstr.="<table class='detail' style='padding-top:10px;'>\n";
	$htmlstr.="<tr><td><table><tr>\n";

	//Kontakttype laden
	$kontakttyp_obj = new kontakt();
	$kontakttyp_obj->result=array();
	if (!$kontakttyp_obj->getKontakttyp())
	{
		if ($kontakttyp_obj->errormsg)
			return $kontakttyp_obj->errormsg;
		else
			$kontakttyp_obj->result=array();
	}	
#	var_dump($kontakttyp_obj);
		$htmlstr.="<td>Typ: </td>";		
		$htmlstr.= "<td><SELECT name='kontakttyp'>";
		for ($i=0;$i<count($kontakttyp_obj->result);$i++)
		{
			$htmlstr.= "<OPTION value='".$kontakttyp_obj->result[$i]->kontakttyp."' ".($kontakt_obj->kontakttyp==$kontakttyp_obj->result[$i]->kontakttyp?' selected ':'')." >".$kontakttyp_obj->result[$i]->beschreibung."</OPTION>";
		}
		$htmlstr.= "</SELECT></td>";

		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Kontakt: </td>";		
		$htmlstr.="<td><input type='text' name='kontakt' value='".$kontakt_obj->kontakt."' size='30' maxlength='128' /></td>\n";
		
		
		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Anmerkung: </td>";		
		$htmlstr.="<td><input type='text' name='anmerkung' value='".$kontakt_obj->anmerkung."' size='30' maxlength='64' /></td>\n";


		$htmlstr.="<td>&nbsp;&nbsp;</td>";	
		
		$htmlstr.="<td>Zustellung: </td>";
		$htmlstr.="<td><input ".($kontakt_obj->zustellung?' style="background-color: #FFF4F4;" ':' style="background-color: #E3FDEE;" ')." type='checkbox' name='zustellung' ".($kontakt_obj->zustellung?'checked':'')."></td>\n";
		$htmlstr.="<td>&nbsp;</td>\n";
	

		$htmlstr.="</tr></table></td>";		
	$htmlstr.="</tr>\n";
				
	// Submit-Knopf  Zeile
	$htmlstr.="<tr><td><table><tr>\n";
			$htmlstr.='<td><input onclick="callUrl(\'detail\',\'work=listKontakte&firma_id='.$firma_id.'&standort_id='.$standort_id.'&kontakt_id=\');" type="Button" value="zur&uuml;ck"></td>';
			$htmlstr.="<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";	
			$htmlstr.='<td><input onclick="workDetailRecord(\'detailworkinfodiv\',\'addKontakt\');" type="Button" value="speichern"></td>';
		$htmlstr.="</tr></table></td>";				
	$htmlstr.="</tr>\n";
	
	$htmlstr.="	</table>\n";
	$htmlstr.="</form>\n";
	return $htmlstr;
}

#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Kontakte zu Firmen,Standorte in Listenform
*/
function savePersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte)	
{	
	if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
		return 'Sie haben keine Berechtigung fuer diese Aktion - Kontakte';
		
	// Datenlesen 
	// personfunktionstandort_id funktion_kurzbz person_id position anrede standort_id 
	$funktion_kurzbz = (isset($_POST['funktion_kurzbz'])?$_POST['funktion_kurzbz']:'');
	$person_id = (isset($_POST['person_id'])?$_POST['person_id']:null);
	$position = (isset($_POST['position'])?$_REQUEST['position']:'');
	$anrede = (isset($_POST['anrede'])?$_REQUEST['anrede']:'');
	$funktion_kurzbz = (isset($_POST['funktion_kurzbz'])?$_POST['funktion_kurzbz']:'');

	// Plausib
	//if (empty($personfunktionstandort_id) || !is_numeric($personfunktionstandort_id) )
	//	return 'Personfunktionstandort_id fehlt.';
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
	if (empty($standort_id) || !is_numeric($standort_id) )
		return 'Standort fehlt.';
	//if (empty($person_id) || !is_numeric($person_id) )
	//	return 'Personen ID fehlt.';
	if (empty($funktion_kurzbz) )
		return 'Funktion fehlt.';
		
	// Init
	$htmlstr='';

	//----------------------------------------		
	//	personfunktionstandort Neuanlage - Aenderung
	//----------------------------------------				
	$standort_obj = new standort();
	if($personfunktionstandort_id!='' && is_numeric($personfunktionstandort_id) )
	{
		if($standort_obj->load_personfunktionstandort($personfunktionstandort_id))
		{
			$standort_obj->new = false;
		}
		else
		{
			return 'Person Funktion am Standort wurde nicht gefunden:'.$personfunktionstandort_id;
		}
	}
	else
	{
		$standort_obj->new = true;
		$standort_obj->insertamum = date('Y-m-d H:i:s');
		$standort_obj->insertvon = $user;
	}
	// personfunktionstandort_id funktion_kurzbz person_id position anrede standort_id 
	$standort_obj->personfunktionstandort_id=$personfunktionstandort_id;
	$standort_obj->standort_id=($standort_id?$standort_id:null);
	$standort_obj->funktion_kurzbz=($funktion_kurzbz?$funktion_kurzbz:null);
	$standort_obj->person_id=($person_id?$person_id:null);
	$standort_obj->position=$position;
	$standort_obj->anrede = $anrede;

	if(!$standort_obj->savepersonfunktionstandort())
		return 'Fehler beim Speichern der Person Funktion am Standort :'.$standort_obj->errormsg;
		
	if ($standort_obj->new)
		$personfunktionstandort_id=$standort_obj->personfunktionstandort_id;
	echo '<script language="JavaScript1.2" type="text/javascript">
	<!--
		parent.frames[2].location.reload();
	-->		
	</script>';
	return 'Funktion der Person am Standort : '.$funktion_kurzbz .' '.$position.' ID '. $personfunktionstandort_id.' gespeichert';
}
#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Firmen,Standorte Funktionen/Personen
*/
function getlistPersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte)	
{	
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
	if (empty($standort_id) || !is_numeric($standort_id) )
		return 'Standort f&uuml;r Personenfunktionen fehlt.';
	if (!empty($personfunktionstandort_id) && !is_numeric($personfunktionstandort_id) )
		return 'ID f&uuml;r Personenfunktionen falsch.';

	// Init
	$htmlstr='';
	// Datenlesen
	$standort_obj = new standort();
	if($standort_id!='' && is_numeric($standort_id) )
	{
		$standort_obj->result=array();
		if (!$standort_obj->load_personfunktionstandort('',$firma_id,$standort_id)) 
		{
			if ($standort_obj->errormsg)
				return $standort_obj->errormsg;
			else
				return eingabePersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte);
		}	
	}

	//Kontakttype laden
	$funktion_obj = new funktion();
	$funktion_obj->result=array();
	$funktionen=array();
	if ($funktion_obj->getAll())
	{
		for ($i=0;$i<count($funktion_obj->result);$i++)
			$funktionen[$funktion_obj->result[$i]->funktion_kurzbz]=$funktion_obj->result[$i]->beschreibung;
	}	
#var_dump($standort_obj);	
	$htmlstr.= '<table class="liste">
				<tr>
					<th>ID</th>
					<th>Funktion</th>
					<th>Position</th>
					<th>Anrede</th>
					<th>Person</th>
					<td align="center" valign="top" colspan="3"><a target="detail_workfirma" href="javascript:callUrl(\'detail\',\'work=eingabePersonenfunktionen&firma_id&firma_id='.$firma_id.'&standort_id='.$standort_id.'&personfunktionstandort_id=\');"><input type="Button" value="Neuanlage" name="work"></a></td>
			</tr>';
	$i=0;
	foreach ($standort_obj->result as $row)
	{
		
		$htmlstr .= "<tr id='standort".$i."' class='liste". ($i%2) ."'>\n";
		$i++;

		$htmlstr.= '<td><a href="javascript:callUrl(\'detail\',\'work=eingabePersonenfunktionen&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&personfunktionstandort_id='.$row->personfunktionstandort_id.'\');" >'. $row->personfunktionstandort_id.'</a></td>';
		
		$htmlstr.= '<td>'.(isset($funktionen[$row->funktion_kurzbz])?$funktionen[$row->funktion_kurzbz]:$row->funktion_kurzbz).'</td>';
		$htmlstr.= '<td>'.$row->position.'</td>';
		$htmlstr.= '<td>'.$row->anrede.'</td>';

		$person=($row->person_anrede?$row->person_anrede.' ':'').($row->titelpre?$row->titelpre.' ':'').($row->vorname?$row->vorname.' ':'').($row->nachname?$row->nachname.' ':'');
		$htmlstr.= '<td>'.$person.'</td>';
		
		$htmlstr.= '<td align="center"><a href="javascript:callUrl(\'detail\',\'work=eingabePersonenfunktionen&firma_id='.$firma_id.'&standort_id='.$row->standort_id.'&personfunktionstandort_id='.$row->personfunktionstandort_id.'\');"><img src="../../skin/images/application_form_edit.png" alt="Funktion editieren" title="Funktion editieren"/></a></td>';
		$htmlstr.= "<td align='center'><a href='../personen/kontaktdaten_edit.php?person_id=".$row->person_id."' target=\"_blank\"><img src='../../skin/images/edit.png' alt='Kontaktdaten editieren' title='Kontaktdaten editieren'/></a></td>";
		$htmlstr.= "<td align='center'><a href='".$_SERVER['PHP_SELF']."?deletepersonfunktionstandort=true&standort_id=".$row->standort_id."&personfunktionstandort_id=".$row->personfunktionstandort_id."&firma_id=".$firma_id."' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='Funktion loeschen' title='Funktion loeschen'/></a></td>";
		$htmlstr.= '</tr>';
	}
	$htmlstr.= '</table>';
	return $htmlstr;
}

#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Firmenliste - lt. Suchekriterien 
*/
function eingabePersonenfunktionen($firma_id,$standort_id,$personfunktionstandort_id,$adresstyp_arr,$user,$rechte)
{
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
	if (empty($standort_id) || !is_numeric($standort_id) )
		return 'Standort f&uuml;r Personenfunktionen fehlt.';
	// Init
	$htmlstr='';
	$standort_obj = new standort();
#echo $personfunktionstandort_id;	
	if($personfunktionstandort_id!='' && is_numeric($personfunktionstandort_id) )
	{
		$standort_obj->result=array();
		if (!$standort_obj->load_personfunktionstandort($personfunktionstandort_id,'',$standort_id)) 
			return $standort_obj->errormsg;
		else if (isset($standort_obj->result[0]) )
			$standort_obj=$standort_obj->result[0];
	}
	else
	{
		$standort_obj->standort_id=$standort_id;
	}
##	var_dump($standort_obj);
#	exit;
	$htmlstr.="<form id='addPersonenfunktionen' name='addPersonenfunktionen' action='firma_detailwork.php' method='POST'>\n";
		$htmlstr.="<input type='hidden' name='work' value='savePersonenfunktionen'>\n";	
		$htmlstr.="<input type='hidden' name='firma_id' value='".$firma_id."'>\n";
		$htmlstr.="<input type='hidden' name='standort_id' value='".$standort_id."'>\n";
		$htmlstr.="<input type='hidden' name='personfunktionstandort_id' value='".$personfunktionstandort_id."'>\n";
		
	$htmlstr.="<table class='detail' style='padding-top:10px;'>\n";
	$htmlstr.="<tr><td><table><tr>\n";

	//Kontakttypen laden
	$funktion_obj = new funktion();
	$funktion_obj->result=array();
	if (!$funktion_obj->getAll())
	{
		if ($funktion_obj->errormsg)
			return $funktion_obj->errormsg;
		else
			$funktion_obj->result=array();
	}	
#	var_dump($kontakttyp_obj);
		$htmlstr.="<td>Funktion: </td>";		
		
		$htmlstr.= "<td><SELECT id='funktion_kurzbz' name='funktion_kurzbz'>";
		for ($i=0;$i<count($funktion_obj->result);$i++)
		{
			if ($funktion_obj->result[$i]->aktiv || $standort_obj->funktion_kurzbz==$funktion_obj->result[$i]->funktion_kurzbz)
				$htmlstr.= "<OPTION value='".$funktion_obj->result[$i]->funktion_kurzbz."' ".($standort_obj->funktion_kurzbz==$funktion_obj->result[$i]->funktion_kurzbz?' selected ':'')." >".$funktion_obj->result[$i]->beschreibung."</OPTION>";
		}
		$htmlstr.= "</SELECT></td>";

		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Position: </td>";		
		$htmlstr.="<td><input type='text' id='position'  name='position'  value='".$standort_obj->position."' size='30' maxlength='256' /></td>\n";
		$htmlstr.="<script type='text/javascript' language='JavaScript1.2'>
						function formatItem(row) 
						{
						    return row[0] + ' <li>' + row[1] + '</li> ';
						}
						$('#position').autocomplete('stammdaten_autocomplete.php', 
						{
							minChars:1,
							matchSubset:1,matchContains:1,
							width:400,
							formatItem:formatItem,
							extraParams:{'work':'position'
								,'funktion_kurzbz':$('#funktion_kurzbz').val()
							}
						});
					</script>
		";
		
		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Anrede: </td>";		
		$htmlstr.="<td><input type='text' name='anrede' value='".$standort_obj->anrede."' size='50' maxlength='128' /></td>\n";
		$htmlstr.="<td>&nbsp;</td></tr>";
		$htmlstr.="<tr><td>Person: </td>";		
		$htmlstr.="<td><input type='text' id='person_id' name='person_id' value='".$standort_obj->person_id."' size='20' maxlength='20' />\n";
		$htmlstr.="<script type='text/javascript' language='JavaScript1.2'>
							function formatItem(row) 
							{
							    return row[0] + ' <li>' + row[1] + '</li> ';
							}
							$('#person_id').autocomplete('stammdaten_autocomplete.php', 
							{
								minChars:2,
								matchSubset:1,matchContains:1,
								width:400,
								formatItem:formatItem,
								extraParams:{'work':'person'}
							});

					</script>
		";
		//$htmlstr.'<div id="contentPad">';
		//$htmlstr.='<span class="formInfo"><a href="ansprechpartner_person_tt.htm?width=475" class="jTip" id="one" name="Personensuche">?</a></div></span></td>';
		$htmlstr.="<td>&nbsp;</td>";	
		$person=($standort_obj->person_anrede?$standort_obj->person_anrede.' ':'').($standort_obj->titelpre?$standort_obj->titelpre.' ':'').($standort_obj->vorname?$standort_obj->vorname.' ':'').($standort_obj->nachname?$standort_obj->nachname.' ':'');
		$htmlstr.=($person?'<td colspan="2"></td><td id="person" colspan="9" align="right">'.$person.'</td></tr>':'')."</table></td>";		
	$htmlstr.="</tr>\n";
				
	// Submit-Knopf  Zeile
	$tabselect=1;
	$htmlstr.="<tr><td><table><tr>\n";
			$htmlstr.='<td><input onclick="callUrl(\'detail\',\'work=listPersonenfunktionen&firma_id='.$firma_id.'&standort_id='.$standort_id.'&personfunktionstandort_id=\');" type="Button" value="zur&uuml;ck"></td>';
			$htmlstr.="<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";	
			$htmlstr.='<td><input onclick="workDetailRecord(\'detailworkinfodiv\',\'addPersonenfunktionen\');" type="Button" value="speichern"></td>';
			$htmlstr.="<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";	
			$htmlstr.="<td><input type='button' value='Person anlegen' onClick=\"window.open('../personen/personen_anlegen.php')\"></td>";
		$htmlstr.="</tr></table></td>";				
	$htmlstr.="</tr>\n";
	
	$htmlstr.="	</table>\n";
	$htmlstr.="</form>\n";
	return $htmlstr;
}



#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
/*
	Firmenliste - lt. Suchekriterien 
*/
function getStandort($firma_id,$standort_id,$adresse_id,$adresstyp_arr,$user,$rechte)	
{	
	// Plausib
	if (empty($firma_id) || !is_numeric($firma_id) )
		return 'Firma fehlt.';
	
	// Init
	$htmlstr='';

	// Datenlesen
	$standort_obj = new standort();
	if($standort_id!='' && is_numeric($standort_id) )
	{
		$standort_obj->result=array();
		if (!$standort_obj->load($standort_id))
			return $standort_obj->errormsg;
		$adresse_id=$standort_obj->adresse_id;
	}
	else 
	{
		$standort_obj->firma_id=$firma_id;
		$standort_obj->standort_id=$standort_id;
		$standort_obj->adresse_id=$adresse_id;
	}
#	var_dump($standort_obj);
#echo $adresse_id;

	$adresse_obj = new adresse();
	if ($adresse_id!='' && !$adresse_obj->load($adresse_id))
	{	
			return $adresse_obj->errormsg;
	}
	else 
	{
		$adresse_obj->adresse_id=$adresse_id;
		$adresse_obj->typ='f';
		//$adresse_obj->nation='A';
	}
	
	$htmlstr.="<form id='addStandort' name='addStandort' action='firma_detailwork.php' method='POST'>\n";
		$htmlstr.="<input type='hidden' name='work' value='saveStandort'>\n";	
		$htmlstr.="<input type='hidden' name='firma_id' value='".$standort_obj->firma_id."'>\n";
		$htmlstr.="<input type='hidden' name='adresse_id' value='".$standort_obj->adresse_id."'>\n";
		$htmlstr.="<input type='hidden' name='standort_id' value='".$standort_obj->standort_id."'>\n";
		$htmlstr.="<input type='hidden' name='ext_id' value='".$standort_obj->ext_id."'>\n";
		$htmlstr.="<input type='hidden' name='ext_id_adr' value='".$adresse_obj->ext_id."'>\n";
		
	$htmlstr.="<table class='detail' style='padding-top:10px;'>\n";
	
	$htmlstr.="<tr><td><table><tr>\n";
		$htmlstr.="<td>Typ: </td>";				
		$htmlstr.= "<td><SELECT name='adresstyp'>";
			foreach($adresstyp_arr as $code=>$kurzbz)
			{
				$htmlstr.= "<OPTION value='".$code."' ".($adresse_obj->typ == $code?' selected ':'').">$kurzbz</OPTION>";
			}
		$htmlstr.="<td>&nbsp;</td>";	

		$htmlstr.="<td>Kurzbz: </td>";		
		$htmlstr.="<td><input type='text' name='kurzbz' value='".$standort_obj->kurzbz."' size='20' maxlength='40' /></td>\n";
		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Bezeichnung: </td>";		
		$htmlstr.="<td><input type='text' name='bezeichnung' value='".$standort_obj->bezeichnung."' size='20' maxlength='80' /></td>\n";

		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>&nbsp;</td>";	
		
		$htmlstr.="<td>Heimatadresse: </td>";
		$htmlstr.="<td><input ".($adresse_obj->heimatadresse?' style="background-color: #FFF4F4;" ':' style="background-color: #E3FDEE;" ')." type='checkbox' name='heimatadresse' ".($adresse_obj->heimatadresse?'checked':'')."></td>\n";
		$htmlstr.="<td>&nbsp;</td>\n";
	
		$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="<td>Zustelladresse:</td>";
		$htmlstr.="<td><input ".($adresse_obj->zustelladresse?' style="background-color: #E3FDEE;" ':' style="background-color: #FFF4F4;" ')."  type='checkbox' name='zustelladresse' ".($adresse_obj->zustelladresse?'checked':'')."> </td>";
		$htmlstr.="<td>&nbsp;</td>";	

		$htmlstr.="</tr></table></td>";		
	$htmlstr.="</tr>\n";

		$htmlstr.="<tr><td><table><tr>\n";
		$htmlstr.="<td>Name: </td>";
		$htmlstr.="<td><input type='text' name='name' value='".$adresse_obj->name."' size='80' maxlength='128' /></td>\n";
		$htmlstr.="</tr></table></td>";
	$htmlstr.="</tr>\n";

	//Nationen laden
	$nation_arr = array();
	$nation = new nation();
	$nation->getAll();
	foreach($nation->nation as $row)
			$nation_arr[$row->code]=$row->kurztext;

	//Auswahl Nation
	$htmlstr.="<tr><td><table><tr>\n";
	$htmlstr.="<td>Nation: </td>";		
	$htmlstr.= "<td><SELECT id='nation' name='nation' onchange='loadGemeindeData();'>";
	$htmlstr.= "<option ".($nation==''?' selected ':'')." value=''> </option>";				
	foreach ($nation_arr as $code=>$kurzbz)
		{
		$htmlstr.= "<OPTION value='$code' ".($adresse_obj->nation==$code?' selected ':'')." >$kurzbz</OPTION>";
		}
	$htmlstr.= "</SELECT></td>";
	
	//Posleitzahl
	$htmlstr.="<td>Plz: </td>";		
	$htmlstr.="<td><input type='text' id='plz' name='plz' value='".$adresse_obj->plz."' size='10' maxlength='15'  onblur='loadGemeindeData()' /></td>\n";
		
	//Gemeinde
	$htmlstr.="<td>Gemeinde: </td>";	
		
	$htmlstr.="<td><div id='gemeindediv'>";
	if($adresse_obj->nation=='A' && $adresse_obj->plz!='')
	{
		$htmlstr.=getGemeindeDropDown($adresse_obj->plz);
		$style="style='display:none'";
	}
	else 
	{
		$style="";
	}
	$htmlstr.="</div>";
	$htmlstr.="<input type='text' id='gemeinde' name='gemeinde' ".$style." value='".$adresse_obj->gemeinde."' size='20' maxlength='40' /></td>\n";

	
	//Ort		
	$htmlstr.="<td>Ort: </td>";		
	$htmlstr.="<td><div id='ortdiv'>";
	if($adresse_obj->nation=='A' && $adresse_obj->plz!='')
	{
		$htmlstr.=getOrtDropDown($adresse_obj->plz, $adresse_obj->gemeinde);
		$style="style='display:none'";
	}
	else 
	{
		$style="";
	}
	$htmlstr.="</div>";
	$htmlstr.="<input type='text' name='ort' id='ort' ".$style." value='".$adresse_obj->ort."' size='30' maxlength='45' /></td>\n";
		
	
		$htmlstr.="</tr></table></td>";		
##var_dump($adresse_obj);
	$htmlstr.="<tr><td><table><tr>\n";
			$htmlstr.="<td>Strasse:</td>";
			$htmlstr.="<td><input type='text' name='strasse' value='".$adresse_obj->strasse."' size='120' maxlength='150' /></td>\n";
			$htmlstr.="<td>&nbsp;</td>";	
		$htmlstr.="</tr></table></td>";	
	$htmlstr.="</tr>\n";
		
		
				
		$htmlstr.="</tr></table></td>";		
	$htmlstr.="</tr>\n";

	$htmlstr.="<tr><td><table><tr>\n";
		$htmlstr.='<td><input onclick="workDetailRecord(\'detailworkinfodiv\',\'addStandort\');" type="Button" value="speichern"></td>';
	$htmlstr.="</tr></table></td>";				
	$htmlstr.="</tr>\n";
	$htmlstr.="	</table>\n";
	$htmlstr.="</form>\n";
	return $htmlstr;
}
// ----------------------------------------------------------------------------------------------------------------------------------
/*
	Standortwartung - Adressen,Personen,Kontakte 
*/
function saveStandort($firma_id,$standort_id,$adresse_id,$adresstyp_arr,$user,$rechte)
{

	if( !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('basis/firma',null, 'suid'))
		return 'Sie haben keine Berechtigung fuer diese Aktion';

	// Standort speichern
	$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');
	$bezeichnung = (isset($_POST['bezeichnung'])?$_REQUEST['bezeichnung']:'');
	// Adressen
	$adresstyp = (isset($_POST['adresstyp'])?$_POST['adresstyp']:'');
	$strasse = (isset($_POST['strasse'])?$_POST['strasse']:'');
	$plz = (isset($_POST['plz'])?$_POST['plz']:'');
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$name = (isset($_POST['name'])?$_POST['name']:'');	
	$gemeinde = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
	$nation = (isset($_POST['nation'])?$_POST['nation']:'');
	$heimatadresse = (isset($_POST['heimatadresse'])?true:false);
	$zustelladresse = (isset($_POST['zustelladresse'])?true:false);

	$ext_id = (isset($_POST['ext_id'])?$_POST['ext_id']:'');
	$ext_id_adr = (isset($_POST['ext_id_adr'])?$_POST['ext_id_adr']:'');

	
	
	//----------------------------------------		
	//	ADRESSEN Neuanlage - Aenderung
	//----------------------------------------				
	$adresse_obj = new adresse();
	if(is_numeric($adresse_id))
	{
		if($adresse_obj->load($adresse_id))
		{
			$adresse_obj->new = false;
		}
		else
		{
			return 'Adresse wurde nicht gefunden:'.$adresse_id;
		}
	}
	else
	{
		$adresse_obj->new = true;
		$adresse_obj->insertamum = date('Y-m-d H:i:s');
		$adresse_obj->insertvon = $user;
	}

	$adresse_obj->person_id=null;
	$adresse_obj->name=$name;
	$adresse_obj->strasse = $strasse;
	$adresse_obj->nation = $nation;
	$adresse_obj->plz = $plz;
	$adresse_obj->ort = $ort;
	$adresse_obj->gemeinde = $gemeinde;
	$adresse_obj->typ = $adresstyp;
	$adresse_obj->heimatadresse = $heimatadresse;
	$adresse_obj->zustelladresse = $zustelladresse;
	$adresse_obj->firma_id = null;
	$adresse_obj->updateamum = date('Y-m-d H:i:s');
	$adresse_obj->updatvon = $user;
	$adresse_obj->ext_id=($ext_id_adr?$ext_id_adr:null);

	if(!$adresse_obj->save())
		return 'Fehler beim Speichern der Adresse:'.$adresse_obj->errormsg;
	if ($adresse_obj->new)
		$adresse_id=$adresse_obj->adresse_id;

		
	$standort_obj = new standort();
	if(is_numeric($standort_id))
	{
		if($standort_obj->load($standort_id))
		{
			$standort_obj->new = false;
		}
		else
		{
			return 'Standort wurde nicht gefunden:'.$standort_id;
		}
	}
	else
	{
		$standort_obj->new = true;
		$standort_obj->insertamum = date('Y-m-d H:i:s');
		$standort_obj->insertvon = $user;
	}

	if(is_numeric($adresse_id))
		$standort_obj->adresse_id=$adresse_id;
	else
		$standort_obj->adresse_id=null;

		$standort_obj->kurzbz=$kurzbz;
		$standort_obj->bezeichnung=$bezeichnung;
		$standort_obj->ext_id=($ext_id?$ext_id:null);

		$standort_obj->firma_id=$firma_id;
		$standort_obj->updatevon=$user;
		$standort_obj->updateamum = date('Y-m-d H:i:s');
		//var_dump($standort_obj);
		if(!$standort_obj->save())
		{
			if ($standort_obj->new)
				$adresse_obj->delete($adresse_id);
			return 'Fehler beim Speichern des Standort:'.$standort_obj->errormsg;
		}

		if ($standort_obj->new)
			$standort_id=$standort_obj->standort_id;
		if (empty($standort_id))
			return 'Abbruch nach Standort verarbeitung! ID Fehlt ';
##var_dump($standort_obj);
#exit;
?>
	<script language="JavaScript1.2" type="text/javascript">
	<!--
		parent.frames[0].location.reload();
		parent.frames[1].location.reload();
	-->		
	</script>
<?php
		
		return 'Standort ID '.$standort_id.' '.$standort_obj->kurzbz.' wurden erfolgreich gespeichert ';
}
#-------------------------------------------------------------------------------------------------------------------------------------------------------------#
?>
