<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */

// -------------------------------------------------------------------------------
// Include Daten
// -------------------------------------------------------------------------------
	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/organisationseinheit.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	require_once('../../../include/benutzerfunktion.class.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/fachbereich.class.php');
// -------------------------------------------------------------------------------
// Parameterdaten 
// -------------------------------------------------------------------------------
	$oe_kurzbz = (isset($_GET['oe_kurzbz'])?$_GET['oe_kurzbz']:null);
	$debug = (isset($_GET['debug'])?$_GET['debug']:(isset($_POST['debug'])?$_POST['debug']:false));
	$debug = true;
	#$oe_kurzbz='bif';		
	
// -------------------------------------------------------------------------------		
// Html Header output
// -------------------------------------------------------------------------------
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script src="../../../include/js/jquery.js" type="text/javascript"></script>
	<script src="../../../include/js/jquery-ui.js" type="text/javascript"></script>

	<style type="text/css">
	<!--
		li {	list-style : outside url("../../../skin/images/right.gif");}
		/* ----------------------------------
		Resizable
		---------------------------------- */
		.ui-resizable { position: relative;}
		.ui-resizable-handle { position: absolute;font-size: 0.1px;z-index: 99999; display: block;}
		.ui-resizable-disabled .ui-resizable-handle, .ui-resizable-autohide .ui-resizable-handle { display: none; }
		.ui-resizable-n { cursor: n-resize; height: 7px; width: 100%; top: -5px; left: 0; }
		.ui-resizable-s { cursor: s-resize; height: 7px; width: 100%; bottom: -5px; left: 0; }
		.ui-resizable-e { cursor: e-resize; width: 7px; right: -5px; top: 0; height: 100%; }
		.ui-resizable-w { cursor: w-resize; width: 7px; left: -5px; top: 0; height: 100%; }
		.ui-resizable-se { cursor: se-resize; width: 12px; height: 12px; right: 1px; bottom: 1px; }
		.ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: -5px; bottom: -5px; }
		.ui-resizable-nw { cursor: nw-resize; width: 9px; height: 9px; left: -5px; top: -5px; }
		.ui-resizable-ne { cursor: ne-resize; width: 9px; height: 9px; right: -5px; top: -5px;}
	
		div.info {width:40%;display:none;padding: 5px 5px 5px 5px;border: 1px solid Black;empty-cells : hide;text-align:center;vertical-align: top;z-index: 99;background-color: white; position:absolute;}
		div.infoclose {border: 7px outset #008381;padding: 0px 10px 0px 10px;}
		div.infodetail {font-size:medium;text-align:left;background-color: #F5F5F5;padding: 15px 15px 15px 15px;}
		
		table {border:0; padding:0;margin:0;}
		tr {border:0; padding:0;margin:0;}
		td {border:0; padding:0;margin:0;}
		
		table.orglevel0 {border:0; padding:0;margin:0; }
		table.orglevel0 tr {background-color: #626b71;}
		table.orglevel0 th {text-align:center;color:#FFF;}
		table.orglevel0 td {text-align:left;color:#FFF;}
		table.orglevel0 a {color:#FFF;}
		table.orglevel0 a:hover {color:silver;}		
				
		table.orglevel1 {width:100%;border:0; padding:0;margin:0; }
		table.orglevel1 tr {background-color: #d6da96;}
		table.orglevel1 th {text-align:center;color:#000;}
		table.orglevel1 td {text-align:left;color:#000;}
		table.orglevel1 a {color:#000;}		
		table.orglevel1 a:hover {color:navy;}		
	
		table.orglevel2 {width:100%;border:0; padding:0;margin:0; }
		table.orglevel2 tr {background-color: #147caa;}
		table.orglevel2 th {text-align:center;color:#FFF;}
		table.orglevel2 td {text-align:left;color:#FFF;}
		table.orglevel2 a {color:#FFF;}			
		table.orglevel2 a:hover {color:#000;}		
		

	-->
	</style>
';

echo '
		<script type="text/javascript" language="JavaScript1.2">
		<!-- 
			//http://api.jquery.com/jQuery.ajax/
			var ajxFile = "'.$_SERVER["PHP_SELF"].'";
			var InfoWin;
	
			function load_oe_kurzbz(oe_kurzbz,obj)
			{
			   		$("div#infodetail").html("<img src=\'../../../skin/images/spinner.gif\' alt=\'warten\' title=\'warten\' >");
				    $("div#info").show("slow"); // div# langsam oeffnen
					$.ajax
					(
						{
							type: "GET",
							timeout: 1500,
							dataType: "html",
							url: ajxFile,
							data: "oe_kurzbz=" + oe_kurzbz,
							error: function()
							{
					   			$("div#infodetail").html("error ");
								return;								
							},		
							success: function(phpData)
							{
						   		$("div#infodetail").html(phpData);
								return;								
							}
						}
					);
		}
	
		function load_user(uid)
		{

		   		$("div#infodetail").html("<img src=\'../../../skin/images/spinner.gif\' alt=\'warten\' title=\'warten\' >");
			    $("div#info").show("slow"); // div# langsam oeffnen
				$.ajax
				(
						{
							type: "GET",
							timeout: 1500,
							dataType: "html",
							url: "../profile/index.php",
							data: "uid=" + uid,
							error: function()
							{
					   			$("div#infodetail").html("error ");
								return;
							},		
							success: function(phpData2)
							{
						   		$("div#infodetail").html(phpData2);
								return;
							}
						}
				);
		}
		$(function() 
		{
			$("#info").resizable();
			$("#ui-resizable").draggable();
		});

				
		-->
		</script>
		
</head>
<body>
';

// -------------------------------------------------------------------------------
// Html Daten output
// -------------------------------------------------------------------------------

		
		// -------------------------------------------------------------------------------
		// Detailanzeige Organisation - Ajax Container
		echo '
		<div id="ui-resizable" class="ui-resizable">
		   <div style="-moz-user-select: none;"  class="ui-resizable-handle ui-resizable-e"></div>
		   <div style="-moz-user-select: none;"  class="ui-resizable-handle ui-resizable-s"></div>
		   <div  style="z-index: 1001; -moz-user-select: none;" class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div>
			<div id="info" class="info" >
				<div id="infodaten" class="infodaten">
				
				<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr style="color:#FFF;" class="ContentHeader">
					<td id="info_print" align="left" style="color:#FFF;cursor: default;" class="ContentHeader">
					<div>drucken <img border="0" src="../../../skin/images/printer.png" title="drucken" ><br /></div>
						<script type="text/javascript">
						   $(document).ready(function()
						   { 
								$("td#info_print").click(function()
								{
									el=\'div#infodetail\';
									var doc=null;
									var tab=false;;
									var iframe=false;
									if ($.browser.opera || $.browser.mozilla) 
									{
							            var tab = window.open("","jqPrint-preview");
							            tab.document.open();
							            var doc = tab.document;
									}
									else
									{
										var iframe=document.createElement(\'IFRAME\');
										$(iframe).attr(\'style\',\'position:absolute;width:0px;height:0px;left:-500px;top:-500px;\');
										document.body.appendChild(iframe);
										doc=iframe.contentWindow.document;
									}
									
									var links=window.document.getElementsByTagName("link");
									for(var i=0;i<links.length;i++)
									{
										if(links[i].rel.toLowerCase()=="stylesheet")
										{
											doc.write(\'<link type="text/css" rel="stylesheet" href="\'+links[i].href+\'"></link>\');
										}
									}		
									doc.write(\'<div class="\'+$(el).attr("class")+\'">\'+$(el).html()+\'</div>\');
									doc.close();
//        alert(iframe.contentWindow.document);
									(tab ? tab : iframe.contentWindow).focus();
							        setTimeout( function() 
										{ 
											( tab ? tab : iframe.contentWindow).print(); if (tab) { tab.close(); } }, 1000);
										});								
							});								
						</script>	
					</div>	
					</td>
					<td id="info_close" align="right" style="color:#FFF;cursor: default;" class="ContentHeader">
					<div>schliessen  <img border="0" src="../../../skin/images/cross.png" title="schliessen">&nbsp;</div>
						<script type="text/javascript">
						   $(document).ready(function()
						   {
							   $("td#info_close").click(function(event)
							   {
						    	       $("div#info").hide("slow");       			// div# langsam oeffnen
					   			});
							});
						</script>	
					</div>
					</td>
					</tr></table>			
					<br>		
					<div id="infodetail" class="infodetail">&nbsp;</div>
					<br>
				</div>
			</div>
		</div>
		';
		echo '
			<table id="inhalt" class="tabcontent">
			  <tr>
		    	<td class="tdwidth10">&nbsp;</td>
			    <td>
					<table class="tabcontent">
			    	  <tr>
			        	<td class="ContentHeader"><font class="ContentHeader">&nbsp;Organisation der '.CAMPUS_NAME.'&nbsp;</font></td>
				      </tr>';
					  echo '<tr><td>';
					// -----------  Anzeige Organisation
					if (!$outarray=getOrganisationen($oe_kurzbz,$debug))
						echo '<font class="error">Fehler beim Daten lesen</font>';
					else if (!displayOrganisationen($oe_kurzbz,$outarray))
						echo '<font class="error">Fehler bei der Ausgabe der Daten</font>';
					echo '</td></tr>';	
			echo '</table>
			</td>
			</tr>
		</table>';
echo '</body></html>';

// ---------------------------------------------------------------------------------------------------------------
// Zeigt das Array in einer Verschachtelten Tabelle an
// ---------------------------------------------------------------------------------------------------------------
function displayOrganisationen($oe_kurzbz=null,$outarray=array())
{
/*	--- ALLE ARRA Varianten ---
	$outarray['ersteebene']=array();
	$outarray['child']=array();
	$outarray['studienzentren']=array();
	$outarray['fachhochschule']=array();
	$outarray['uebergreifende']=array();
	$outarray['nochnichtzugeordnet']=array();
*/

// ---------------------------------------------------------------------------------------------------------------
// wird nur eine Organisatzionsdatensatz gefunden die Personen anzeigen
	if(!is_null($oe_kurzbz))
	{
		$qry = "SELECT distinct titelpre, vorname, nachname, titelpost, funktion_kurzbz, uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE
				aktiv and                       (funktion_kurzbz='oezuordnung' OR funktion_kurzbz='Leitung') AND
				oe_kurzbz IN(
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as 
					(
						SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit 
						WHERE oe_kurzbz='".addslashes($oe_kurzbz)."'
						UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes 
						WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
					)
					SELECT oe_kurzbz
					FROM oes
					GROUP BY oe_kurzbz)
				ORDER BY funktion_kurzbz, nachname, vorname ";
		$db = new basis_db();
		if(!$result=$db->db_query($qry))
			echo '<p class="error">'.$db->errormsg.'</p>';
		else
		{
			if($anz=$db->db_num_rows($result))
			{
				$oe_obj = new organisationseinheit($oe_kurzbz);
				$oe_obj->oe_parent_kurzbz=$oe_kurzbz;
				echo '<tr><td><table class="rahmen">';
				echo '<tr><td><h1>&nbsp;'.$oe_obj->organisationseinheittyp_kurzbz.' - '.$oe_obj->bezeichnung.'&nbsp;</h1></td></tr>';
				echo '<tr><td><fieldset><legend>&nbsp;Mitarbeiter&nbsp;</legend>';
				if ($anz>1)
					echo '<h3>&nbsp;Anzahl: '.$anz.'&nbsp;</h3>';
				echo '<ul>';
				while($row = $db->db_fetch_object($result))
				{
					$url="void(open('../profile/index.php?uid='+escape(".$row->uid."),'','resizable,location,menubar,toolbar,scrollbars,status'));"; 
//					echo '<li class="personenliste"><a target="_blank" href="javascript:'.$url.'" class="Item">'.$row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost.($row->funktion_kurzbz=='Leitung'?' (Leitung)':'').'</a></li>';
//					echo '<li class="personenliste"><a target="_blank" href="../profile/index.php?uid='.$row->uid.'" class="Item">'.$row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost.($row->funktion_kurzbz=='Leitung'?' (Leitung)':'').'</a></li>';
					echo '<li class="personenliste"><a href="javascript:load_user(\''.$row->uid.'\');" class="Item">'.$row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost.($row->funktion_kurzbz=='Leitung'?' (Leitung)':'').'</a></li>';
/*
      $(function(){
	      $('a.new-window').click(function(){
      window.open(this.href);
      return false;
      });
      });
*/	  
				}
				echo '</ul>';
				echo '</fieldset></td></tr>';
				echo '</table></td></tr>';
			}	
		}
		return true;
		
	}// Ende wenn nur eine Organisation gefunden wurde

	echo '<table style="padding:0;border:0;margin:0;width:100%;">';
		echo '<tr>';
		if (is_array($outarray['fachhochschule']) && count($outarray['fachhochschule'])>0 )
		{
			echo '<td><table class="orglevel0"><tr><td><b>FACHHOCHSCHULE</b><br><ul>';	 
			foreach ($outarray['fachhochschule'] as $key=>$val) 
			{
				echo '<li><a href="javascript:load_oe_kurzbz(\''.$val->oe_kurzbz.'\',this);" class="Item">';
					echo $val->bezeichnung;
				echo '</a></li>';
			}
			echo '</ul></td></tr></table></td>'; 
		}	
		$i=count($outarray['studienzentren']);
		
		foreach ($outarray['ersteebene'] as $key=>$val) 
		{
			echo '<td style="width:100%;vertical-align: bottom;"><table style="padding:0;border:0px;margin:0;width:100%;" class="orglevel0"><tr><th><a  href="javascript:load_oe_kurzbz(\''.$val->oe_kurzbz.'\',this);" class="Item">';
				echo $val->bezeichnung.'<br>'.$val->organisationseinheittyp_kurzbz;
			echo '</a></th></tr><tr><td style="background-color: #FFF;color:#626b71;text-align: center;"><span style="color:#626b71;width:5px;"><img src=\'../../../skin/images/bullet_arrow_down.png\' alt=\'info\' title=\'info\' ></span></td></tr></table></td>';
		}
		echo '</tr>';
//--- STUDIENZENTRUM 
		echo '<tr><td style="vertical-align: top;" colspan="3"><table style="padding:0;border:0;margin:0;width:100%;height:100%;"><tr>';
		reset($outarray['studienzentren']);		
		
		foreach ($outarray['studienzentren'] as $key=>$val) 
		{
			if (!isset($val) || !isset($val['dat']) || !is_object($val['dat']) || !isset($val['dat']->organisationseinheittyp_kurzbz))
				continue;	
		
			echo '<td style="width:'. (100 / $i) .'%;vertical-align: bottom;">
				<table class="orglevel1" style="padding:0;border:0px;margin:0;width:100%;">';

			// Pfeil nach Unten
			echo '<tr><th style="vertical-align:top;border-top : 1px solid #626b71;background-color: #FFF;"><img src=\'../../../skin/images/bullet_arrow_down.png\' alt=\'info\' title=\'info\' ></th></tr>';
			
			echo '<tr><td style="vertical-align:top;border: 1px inset Black;"><table class="orglevel1" style="padding:0;border:0px;margin:0;">';

// ---- STUDIENGANG
				echo '<tr><th style="height:70px;vertical-align:top;"><a  href="javascript:load_oe_kurzbz(\''.$key.'\',this);" class="Item">';
					echo '<b>'.$val['dat']->organisationseinheittyp_kurzbz.'</b>'.'<br>'.$val['dat']->bezeichnung;
				echo '</a></th></tr>';
				$first_organisationseinheittyp_kurzbz=null;
				$last_organisationseinheittyp_kurzbz=null;
				echo '<tr><td style="height:220px;vertical-align:top;"><ul>';
				reset($val['child']);			
				foreach ($val['child'] as $keys=>$vals) 
				{
					if (is_null($first_organisationseinheittyp_kurzbz))
						$first_organisationseinheittyp_kurzbz=$vals['dat']->organisationseinheittyp_kurzbz;							
					if (!is_null($last_organisationseinheittyp_kurzbz) && $vals['dat']->organisationseinheittyp_kurzbz!=$last_organisationseinheittyp_kurzbz)
					{
						$last_organisationseinheittyp_kurzbz=$vals['dat']->organisationseinheittyp_kurzbz;
						break;
					}	
					$last_organisationseinheittyp_kurzbz=$vals['dat']->organisationseinheittyp_kurzbz;
										
					echo '<li   title="'.$vals['dat']->organisationseinheittyp_kurzbz.' '.$vals['dat']->oe_kurzbz.' Parent '.$vals['dat']->oe_parent_kurzbz.'"><a  href="javascript:load_oe_kurzbz(\''.$keys.'\',this);" class="Item">';
						echo $vals['dat']->bezeichnung;
					echo '</a></li>';
				}
				echo '</ul></td></tr>';
// ---- INSTITUTE
			if ($last_organisationseinheittyp_kurzbz!=$first_organisationseinheittyp_kurzbz)
			{
					echo '<tr><td><table style="padding:0;border:0px;margin:0;" class="orglevel2">';
					echo '<tr><td style="height:140px;vertical-align:top;"><b>'.$last_organisationseinheittyp_kurzbz.'</b><ul>';
					reset($val['child']);
					foreach ($val['child'] as $keys=>$vals) 
					{
						if ($vals['dat']->organisationseinheittyp_kurzbz!=$last_organisationseinheittyp_kurzbz)
							continue;
						$last_organisationseinheittyp_kurzbz=$vals['dat']->organisationseinheittyp_kurzbz;
						echo '<li title="'.$vals['dat']->organisationseinheittyp_kurzbz.' Parent '.$vals['dat']->oe_parent_kurzbz.'"><a  href="javascript:load_oe_kurzbz(\''.$keys.'\',this);" class="Item">';
							echo $vals['dat']->bezeichnung;
						echo '</a></li>';
					}
					echo '</ul></td></tr></table></td></tr>';
			}		
			else
			{
				echo '<tr><td><table style="padding:0;border:0px;margin:0;" class="orglevel1">';
				echo '<tr><td style="height:140px;vertical-align:top;">&nbsp;</td></tr></table></td></tr>';
			}
			echo '</table></td></tr></table></td>';
		}	
		echo '</tr></table></td></tr>';	
// --- UEBERGREIFEND
		if (count($outarray['uebergreifende'])>0)
		{
			echo '<tr><td style="text-align:center;" colspan="3">
				<table class="orglevel2" style="padding:0;border:0;margin:0;width:100%;">
				<tr><td style="padding:0 0 0 40%;text-align: left;"><b>&Uuml;bergreifende Institute</b><ul>';
			foreach ($outarray['uebergreifende'] as $key=>$val) 
			{
						echo '<li><a  href="javascript:load_oe_kurzbz(\''.$key.'\',this);" class="Item">';
							echo $val->organisationseinheittyp_kurzbz.'-'.$val->bezeichnung;
						echo '</a></li>';
			}			
			echo '</ul></td></tr></table></td></tr>';
		}	
		
	echo '</table>';

#	var_dump($outarray['studienzentren']);
	return true;	
}

// ---------------------------------------------------------------------------------------------------------------
// Zeigt das Array in einer Verschachtelten Tabelle an
// ---------------------------------------------------------------------------------------------------------------
function getOrganisationen($oe_kurzbz=null,$debug=false)
{
// -------------------------------------------------------------------------------
// Organisationen
			
	//Alle obersten Organisationseinheiten holen
	$oe = new organisationseinheit();
	if(!is_null($oe_kurzbz) && !empty($oe_kurzbz))
	{
		//wenn eine Organisationseinheit uebergeben wurde, dann laden
		$oe->load($oe_kurzbz);
		$oe_obj = new organisationseinheit();
		$oe_obj->oe_parent_kurzbz=$oe->oe_kurzbz;
		
		#var_dump( $oe);
		
		$oe->result[] = $oe;
		$breadcrumbs='';
		do 
		{
			$oe_obj->load($oe_obj->oe_parent_kurzbz);
			$breadcrumbs = '<a  href="javascript:load_oe_kurzbz(\''.$oe_obj->oe_kurzbz.'\',this);" class="Item">'.$oe_obj->organisationseinheittyp_kurzbz.' - '.$oe_obj->bezeichnung.'</a> &gt; '.$breadcrumbs;
		} while($oe_obj->oe_parent_kurzbz!='');
		$breadcrumbs='<a href="organisationseinheiten.php" class="Item">Organisation</a> &gt; '.$breadcrumbs;
		echo '<tr><td><h3>'.$breadcrumbs.'</h3></td></tr>';
	}
	else 
	{
		//Wenn keine Organisationseinheite uebergeben wurde, die obersten laden
		$oe->getHeads();
	}

	// Initialisierung
	$outarray['ersteebene']=array();
	$outarray['child']=array();
	$outarray['studienzentren']=array();
	$outarray['fachhochschule']=array();
	$outarray['uebergreifende']=array();
	$outarray['nochnichtzugeordnet']=array();
	
	// Spezielle Zuordnungen zu den Anzeige-Array
	$check_fachhochschule=array('abteilung','institut');
	$check_uebergreifende=array('studiengang');
	$check_noch_nicht_zugeordnet=array();
	
	// Erste Ebene lesen (wenn parameter oe_kurzbz uebergeben wird ist das die erste Ebene)
	$ersteebene = array();
	foreach ($oe->result as $result)
		$outarray['ersteebene'][$result->oe_kurzbz]=$result;
	if (!is_array($outarray['ersteebene']) || count($outarray['ersteebene'])<1)
		return false;
	
	// Alle Eintraege zur Hauptebene suchen 	
	reset($outarray['ersteebene']);	
	foreach ($outarray['ersteebene'] as $key=>$val) 
		$outarray['child'] = getChilds($key);

	// Aufteilen auf die verschidenen Teile 
	if (is_array($outarray['child']))
		reset($outarray['child']);	
	else
		return $outarray;
	foreach ($outarray['child'] as $key=>$val) 
	{

		if (!isset($val) 
		|| !is_array($val) 
		|| count($val)<1)
			continue;
	
		if ($key=='Dummy')
			continue;
		if (!isset($val['child']) || is_null($val['child']) || !is_array($val['child']) || count($val['child'])<1)
		{
			$check=strtolower($val['dat']->organisationseinheittyp_kurzbz);
			if (in_array($check,$check_fachhochschule))
				$outarray['fachhochschule'][$key]=$val['dat'];			 
			else if (in_array($check,$check_uebergreifende))
				 $outarray['uebergreifende'][$key]=$val['dat'];
			else
				$outarray['nochnichtzugeordnet'][$key]=$val['dat'];
			continue;
		}
		$outarray['studienzentren'][$key]=$val;
	}  
	#var_dump($outarray['studienzentren']);
	return $outarray;
}

// ---------------------------------------------------------------------------------------------------------------
//Liefert die Kindelemente einer Organisationseinheit in 
//einem verschachteltem Array zurueck
// ---------------------------------------------------------------------------------------------------------------
function getChilds($foo)
{
	$obj = new organisationseinheit();
	$arr = array();
	$arr1 = $obj->getDirectChilds($foo);
	foreach ($arr1 as $value)
	{
		if ($daten=getDaten($value))
			$arr[$value]['dat']=$daten;
	}	
	if ((!is_array($arr) && !is_object($arr)) || count($arr)<1)	
		return null;
		
	reset($arr);
	foreach ($arr as $key =>$val) 
		$arr[$key]['child'] = getChilds($key);
		
	return $arr;
}
// ---------------------------------------------------------------------------------------------------------------
//Liefert zur oe_kurzbz die Kpl. Daten zureuck 
// -- Nur Aktive Datensaetze 
// ---------------------------------------------------------------------------------------------------------------
function getDaten($oe_kurzbz='')
{
	if (is_null($oe_kurzbz) || empty($oe_kurzbz))
		return array();
	$obj = new organisationseinheit($oe_kurzbz);
	$obj->load($oe_kurzbz);
	if(!isset($obj->aktiv) || empty($obj->aktiv))
		return null;	

	if ($stg = new studiengang())
	{
		$qry = "SELECT * FROM public.tbl_studiengang WHERE aktiv and upper(trim(kurzbzlang))=upper(trim('".$oe_kurzbz."'))";
		if ($result = $stg->db_query($qry))
		{
			while($row = $stg->db_fetch_object($result))
			{
				foreach ($row as $key=>$val) 
					$obj->$key=str_replace('/',' / ',$val);
			}
		} 	
	}

	return $obj;	
}
?>
