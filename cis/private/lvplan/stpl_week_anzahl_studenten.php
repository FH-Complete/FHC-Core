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
 *          Gerald Simane-Sequens <gerald.simane@technikum-wien.at>.
 */
	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/student.class.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	require_once('../../../include/studiensemester.class.php');
	
	if (!$db = new basis_db())
		die('Fehler beim Oeffnen der Datenbankverbindung');
	$uid=isset($_GET['uid'])?$_GET['uid']:(isset($_POST['uid'])?$_POST['uid']:get_uid());
	$uid=trim($uid);
	
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	
	if(!$rechte->isBerechtigt('lehre/reservierung:begrenzt', null, 's') && !$rechte->isBerechtigt('admin'))
		die($rechte->errormsg);
	unset($rechte);

   	header('Content-Type: text/html;charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Anzahl Studenten Lehrveranstaltungsplan FH Technikum-Wien</title>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<script src="../../../vendor/components/jqueryui/jquery-ui.min.js" type="text/javascript"></script>
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
	
		div.info {top:5%;position: absolute;display:none;padding: 5px 5px 5px 5px;border: 1px solid Black;empty-cells : hide;text-align:center;vertical-align: top;z-index: 99;background-color: white; position:absolute;}
		div.infoclose {border: 7px outset #008381;padding: 0px 10px 0px 10px;}
		div.infodetail {font-size:medium;text-align:left;background-color: #F5F5F5;padding: 15px 15px 15px 15px;}

	-->
	</style>

		<script type="text/javascript" language="JavaScript1.2">
		<!-- 
		$(function() 
		{
			$("#info").resizable();
			$("#ui-resizable").draggable();
		});
		-->
		</script>
</head>
<body id="inhalt">
		<div id="ui-resizable" class="ui-resizable">
		   <div style="-moz-user-select: none;"  class="ui-resizable-handle ui-resizable-e"></div>
		   <div style="-moz-user-select: none;"  class="ui-resizable-handle ui-resizable-s"></div>
		   <div  style="z-index: 1001; -moz-user-select: none;" class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div>
			<div id="info" class="info">
				<div style="border: 7px outset #393939;padding: 10px 10px 10px 10px;">
				<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr style="color:#FFF;" class="ContentHeader">
					<td id="info_print" align="left" style="color:#FFF;cursor: default;" class="ContentHeader">
					<div>drucken <img border="0" src="../../../skin/images/printer.png" title="drucken" ><br /></div>
						<script type="text/javascript">
						   $(document).ready(function()
						   { 
								$("td#info_print").click(function()
								{
									el='div#infodetail';
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
						
										var iframe=document.createElement('IFRAME');
										document.body.appendChild(iframe);
										doc=iframe.contentWindow.document;
									}
									
									var links=window.document.getElementsByTagName("link");
									for(var i=0;i<links.length;i++)
									{
										if(links[i].rel.toLowerCase()=="stylesheet")
										{
											doc.write('<link type="text/css" rel="stylesheet" href="'+links[i].href+'"></link>');
										}
									}		
									doc.write('<div class="'+$(el).attr("class")+'">'+$(el).html()+'</div>');
									doc.close();
									(tab ? tab : iframe.contentWindow).focus();
							        setTimeout( function() { ( tab ? tab : iframe.contentWindow).print(); if (tab) { tab.close(); } }, 1000);

								});								
							});								
						</script>		
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
					</td>
					</tr></table>							
					<div id="infodetail" style="font-size:medium;text-align:left;background-color: #F5F5F5;padding: 15px 15px 15px 15px;"></div>
					<br>
				</div>
			</div>
		</div>
<?php



	// Variablen uebernehmen
	$datum=(isset($_GET['datum'])?$_GET['datum']:(isset($_POST['datum'])?$_POST['datum']:time()));
	$stpl_table=(isset($_GET['stpl_table'])?$_GET['stpl_table']:'stundenplan');
	$montag=montag($datum);
	$letzterTag=mktime(0,0,0,date('m',$montag),date('d',$montag) + TAGE_PRO_WOCHE,date('Y',$montag));
	$letzterTagAnzeige=mktime(0,0,0,date('m',$montag),date('d',$montag) + ( TAGE_PRO_WOCHE - 1),date('Y',$montag));
	
	// Vorbelegen der Wochennavigation	
	$kwRet=mktime(0,0,0,date('m',$montag),date('d',$montag) -7 ,date('Y',$montag));
	$kwVor=mktime(0,0,0,date('m',$montag),date('d',$montag) +7 ,date('Y',$montag));
	$kw=strftime('%W',mktime(0,0,0,date('m',$montag),date('d',$montag),date('Y',$montag)));
	
	$adresse_id=(isset($_GET['adresse_id'])?$_GET['adresse_id']:(isset($_POST['adresse_id'])?$_POST['adresse_id']:1));

	// Datum Anzeige Header
	$tag=strftime('%a %d',mktime(0,0,0,date('m',$montag),date('d',$montag) ,date('Y',$montag)));
	$tag_monat=strftime('%a %d %b',mktime(0,0,0,date('m',$montag),date('d',$montag) ,date('Y',$montag)));
	$tag_monat_jahr=strftime('%a %d %b %Y',mktime(0,0,0,date('m',$montag),date('d',$montag),date('Y',$montag)));			
	$letzter_tag_monat_jahr=strftime('%a %d %b %Y',mktime(0,0,0,date('m',$letzterTagAnzeige),date('d',$letzterTagAnzeige),date('Y',$letzterTagAnzeige)));			
	
	// Beginn Ende setzen
	$objSS=new studiensemester();
	$ss=$objSS->getaktorNext();
	$objSS->load($ss);
	$datum_obj = new datum();
	$ss_begin=$datum_obj->mktime_fromdate($objSS->start);
	$ss_ende=$datum_obj->mktime_fromdate($objSS->ende);

	
	$sql_query=' select tbl_adresse.plz,tbl_adresse.name, sum(tbl_ort.max_person) as summe  ';
	$sql_query.=' from  public.tbl_ort,public.tbl_standort, public.tbl_adresse ';
	$sql_query.=" where tbl_standort.standort_id=tbl_ort.standort_id ";	
	$sql_query.=" and tbl_adresse.adresse_id=tbl_standort.adresse_id ";	
	$sql_query.=" and tbl_adresse.adresse_id=".$db->db_add_param($adresse_id, FHC_INTEGER)." ";	
	$sql_query.=" and tbl_ort.aktiv and tbl_ort.lehre ";
	$sql_query.=" group by tbl_adresse.plz,tbl_adresse.name  ";
	// Gibt es fuer das Datum und Stunde einen Stundenplaneintrag
	if(!$results_anzahl=$db->db_query($sql_query))
		die($db->db_last_error());
	$raum_max_anz=0;
	$fh_name='FH lese fehler';
	if ($num_rows_anzahl=$db->db_num_rows($results_anzahl))
	{
		  $fh_name = $db->db_result($results_anzahl,0,"name").', '.$db->db_result($results_anzahl,0,"plz");
		  $raum_max_anz = $db->db_result($results_anzahl,0,"summe");
	}	  
	
	$stg=array();
	echo '<H2><table class="tabcontent"><tr><td>
				&nbsp;Lehrveranstaltungsplan  &gt;&gt; <a class="Item" href="index.php">Wochenplan</a> - Anzahl Studenten 
				&nbsp;&nbsp;&nbsp;<a class="Item" href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;datum='.$kwRet.'&amp;stpl_table='.$stpl_table.'">&lt;&lt;</a> 
				Wochenplan  &nbsp;<a class="Item" href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;datum='.$datum.'&amp;stpl_table='.$stpl_table.'">Kw '.$kw.'</a>
				&nbsp;<a class="Item" href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;datum='.$kwVor.'&amp;stpl_table='.$stpl_table.'">&gt;&gt;</a>
				&nbsp;&nbsp;&nbsp;<a class="Item" href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;datum='.time().'&amp;stpl_table='.$stpl_table.'">Heute</a>
		</td></tr></table></H2>';

		// Stundentafel abfragen
	$sql_query="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
	if(!$results=$db->db_query($sql_query))
			die($db->db_last_error());


			echo '<table class="tabcontent" style="	z-index: 1;">';
				echo '<tr><td style="text-align:center;color:#FFF;" class="ContentHeader" colspan="'. ( TAGE_PRO_WOCHE + 1 ) .'">'. $fh_name .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. (date('Ym',$montag)==date('Ym',$letzterTagAnzeige)?$tag:(date('Y',$montag)==date('Y',$letzterTagAnzeige)?$tag_monat:$tag_monat_jahr)) .' - '. $letzter_tag_monat_jahr.'</td></tr>';
				echo '<tr>';
					echo '<td  style="text-align:center;color:#FFF;" class="ContentHeader">Stunde</td>';
					for ($i=0; $i<TAGE_PRO_WOCHE; $i++)
					{
						echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">'.strftime('%a',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))).'&nbsp;'.date('d M',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))).'</td>';
					}
				echo '</tr>';		

			$max_person_array=array();
			$num_rows_stunde=$db->db_num_rows($results);
			echo '<tr>';
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$row = $db->db_fetch_object($results, $k);
				$row->show_beginn=substr($row->beginn,0,5);
				$row->show_ende=substr($row->ende,0,5);
				$row->check_beginn=str_replace(':','',substr($row->beginn,0,5));
				$row->check_ende=str_replace(':','',substr($row->ende,0,5));
				
				echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">'.$row->show_beginn.'<br>'.$row->show_ende.'</td>';
				$lehreinheiten=array();			

				for ($i=0; $i<TAGE_PRO_WOCHE; $i++)
				{			
					// Init je Tag und Std.
					$fehler=false;	
					$aktiv=false;
					$max_person=0;

					$day= date('Ymd',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag)));
					if ($day== date('Ymd') && date('Hi')  >= $row->check_beginn &&  date('Hi')<=$row->check_ende  )
						$aktiv=true;
						
					echo '<td style="border-bottom: 1px solid Black;'.($aktiv?'background-color:#009e84;color:#FFF;':'').'" valign="top"  '.($k % 2==0?'':' class="MarkLine" ').' >';
					
					$sql_query=' select distinct  vw_'.$stpl_table.'.stg_bezeichnung as bezeichnung,vw_'.$stpl_table.'.stg_kurzbzlang as kurzbzlang,vw_'.$stpl_table.'.stg_kurzbz as kurzbz, vw_'.$stpl_table.'.'.$stpl_table.'_id,vw_'.$stpl_table.'.lehrform, vw_'.$stpl_table.'.gruppe, vw_'.$stpl_table.'.gruppe_kurzbz, vw_'.$stpl_table.'.unr,vw_'.$stpl_table.'.verband,vw_'.$stpl_table.'.ort_kurzbz,vw_'.$stpl_table.'.lehreinheit_id,vw_'.$stpl_table.'.studiengang_kz,vw_'.$stpl_table.'.semester,tbl_ort.max_person,tbl_standort.adresse_id,tbl_adresse.plz,tbl_adresse.name  ';
					$sql_query.=' from lehre.vw_'.$stpl_table.', public.tbl_ort,public.tbl_standort, public.tbl_adresse ';
					$sql_query.=" where vw_".$stpl_table.".datum=".$db->db_add_param(date('Y-m-d',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))))." ";
					$sql_query.=" and vw_".$stpl_table.".stunde=".$db->db_add_param($row->stunde, FHC_INTEGER)." ";
					$sql_query.=" and tbl_ort.ort_kurzbz=vw_".$stpl_table.".ort_kurzbz ";
					$sql_query.=" and tbl_standort.standort_id=tbl_ort.standort_id ";	
					$sql_query.=" and tbl_adresse.adresse_id=tbl_standort.adresse_id ";	
					$sql_query.=" and tbl_adresse.adresse_id=".$db->db_add_param($adresse_id, FHC_INTEGER)." ";
					$sql_query.=" order by tbl_adresse.plz,vw_".$stpl_table.".ort_kurzbz ";

					// Gibt es fuer das Datum und Stunde einen Stundenplaneintrag
					if(!$results_anzahl=$db->db_query($sql_query))
						die($db->db_last_error());
					$num_rows_anzahl=$db->db_num_rows($results_anzahl);

					$gefunden_anz=0;
					$tooltip='';
					for ($k_anz=0; $k_anz<$num_rows_anzahl; $k_anz++)
					{
						$row_anz = $db->db_fetch_object($results_anzahl, $k_anz);
						// Lehreinheit wird aufgeteilt in zwei Raeume - nicht verarbeiten , das sind die selben Personen
						if (isset($lehreinheiten[trim($row_anz->lehreinheit_id).trim($row_anz->gruppe_kurzbz)]))
							continue;						
						$lehreinheiten[$row_anz->lehreinheit_id]=trim($row_anz->lehreinheit_id).trim($row_anz->gruppe_kurzbz);
							
						$max_person=$row_anz->max_person+$max_person;	
						$row_anz->verband=trim($row_anz->verband);
						$row_anz->gruppe=trim($row_anz->gruppe);
						$row_anz->gruppe_kurzbz=trim($row_anz->gruppe_kurzbz);						

						$stsem=$ss;
						
						$gruppe=($row_anz->gruppe_kurzbz?$row_anz->gruppe_kurzbz:null);						
						$student=new student();
					
						$row_anz->anz=0;
						if ($result=$student->getStudents($row_anz->studiengang_kz,$row_anz->semester,$row_anz->verband,$row_anz->gruppe,$gruppe, $stsem))
							$row_anz->anz=count($result);
								

						if (empty($row_anz->anz))														
							$fehler=true;

						$lvb=$row_anz->kurzbzlang.'-'.$row_anz->semester;
						if (!is_null($row_anz->verband) && !empty($row_anz->verband))
						{
							$lvb.=$row_anz->verband;
							if (!is_null($row_anz->gruppe) && !empty($row_anz->gruppe) )
								$lvb.=$row_anz->gruppe;
						}
						if (!empty($k_anz))
							$tooltip.='</tr><tr>';
						else
							$tooltip.='<tr><th colspan=\'4\'>'.  date('d M Y',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))).'  '.$row->show_beginn.' - '.$row->show_ende.'</th><th>Anzahl</th></tr>'; 				
						$tooltip.='<td title=\'Stundenplan ID '.($stpl_table=='stundenplan'?$row_anz->stundenplan_id:$row_anz->stundenplandev_id).'\'><b>'.trim($row_anz->ort_kurzbz).'</b>&nbsp;</td><td><a href=\'stpl_detail.php?type=ort&datum='.date('Y-m-d',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))).'&stunde='.$row->stunde.'&pers_uid='.$uid.'&stg_kz=&sem=&ver=&grp=&ort_kurzbz='.trim($row_anz->ort_kurzbz).'\' target=\'_blank\' titel=\'Studiengang Kz '.$row_anz->studiengang_kz.'\'>'.$lvb.'</a>&nbsp;</td><td>'.$row_anz->gruppe_kurzbz.'&nbsp;</td><td>'.(!$row_anz->anz?'<font color=\'Maroon\'>':'').$row_anz->bezeichnung.(!$row_anz->anz?'</font>':'').'&nbsp;</td><td>'.$row_anz->anz.'</td>'; 				
						$gefunden_anz+=$row_anz->anz;
					}
					
					if (!empty($gefunden_anz))
					{
						$tooltip.='<tr><td colspan=\'4\' align=\'right\'>max.Personen:'.$max_person.' Belegung:'. number_format($gefunden_anz / $max_person,2)*100 .'% <b>Ges.:</b></td><td><b>'.$gefunden_anz.'</b></td></tr>'; 				

						echo '<br><img  id="img_'.$i.'_'.$k.'"  src="../../../skin/images/sticky.png" title="Detailanzeige"> <b'.($fehler?' style="color:red;" ':'').'> Gesamt: </b>'.$gefunden_anz;
						echo '<script type="text/javascript">
							   $(document).ready(function()
							   {
								   $("img#img_'.$i.'_'.$k.'").click(function(event)
								   {
								   		$("div#infodetail").html("<table border=\"0\"><tr>'.$tooltip.'</tr></table>");
						    	    	$("div#info").show("slow"); // div# langsam oeffnen
		   							});
								});
						</script>';
					}	
					echo '</td>';


					if (!isset($max_person_array[$i]['tag']))
						$max_person_array[$i]['tag']=0;
					$max_person_array[$i]['tag']=$max_person_array[$i]['tag']+$gefunden_anz;
					if (!isset($max_person_array[$i]['tag_max']))
						$max_person_array[$i]['tag_max']=0;
					$max_person_array[$i]['tag_max']=$max_person_array[$i]['tag_max']+$max_person;
			
					if (!isset($max_person_array[$k]['stunde']))
						$max_person_array[$k]['stunde']=0;				
					$max_person_array[$k]['stunde']=$max_person_array[$k]['stunde']+$gefunden_anz;
					if (!isset($max_person_array[$k]['stunde_max']))
						$max_person_array[$k]['stunde_max']=0;				
					$max_person_array[$k]['stunde_max']=$max_person_array[$k]['stunde_max']+$max_person;

					if (!isset($max_person_array[$i][$k]['tag_stunde']))
						$max_person_array[$i][$k]['tag_stunde']=0;				
					$max_person_array[$i][$k]['tag_stunde']=$max_person_array[$i][$k]['tag_stunde']+$gefunden_anz;
					if (!isset($max_person_array[$i][$k]['tag_stunde_max']))
						$max_person_array[$i][$k]['tag_stunde_max']=0;				
					$max_person_array[$i][$k]['tag_stunde_max']=$max_person_array[$i][$k]['tag_stunde_max']+$max_person;

				}
				echo '</tr>';		

			}		
		echo '</table>';			

		
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
	{
		echo '<table class="tabcontent" style="	z-index: 1;">';
				echo '<tr><td style="text-align:center;color:#FFF;" class="ContentHeader" colspan="'. ( TAGE_PRO_WOCHE + 2 ) .'">'. $fh_name .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. (date('Ym',$montag)==date('Ym',$letzterTagAnzeige)?$tag:(date('Y',$montag)==date('Y',$letzterTagAnzeige)?$tag_monat:$tag_monat_jahr)) .' - '. $letzter_tag_monat_jahr.'</td></tr>';

			echo '<tr>';
				echo '<td colspan="2" style="text-align:center;color:#FFF;" class="ContentHeader">Zeit / Datum &ndash;&nbsp;</td>';									
				for ($i=0; $i<TAGE_PRO_WOCHE; $i++)
				{
					echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">'.strftime('%a',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))).'&nbsp;'.  date('d M Y',mktime(0,0,0,date('m',$montag),date('d',$montag) + $i,date('Y',$montag))).'</td>';	
				}
			echo '</tr>';	
			$stunde_proz=0;	
			$stunde=0;
			$stunde_max=0;
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$row = $db->db_fetch_object($results, $k);
				$row->show_beginn=substr($row->beginn,0,5);
				$row->show_ende=substr($row->ende,0,5);
				$row->check_beginn=str_replace(':','',substr($row->beginn,0,5));
				$row->check_ende=str_replace(':','',substr($row->ende,0,5));
				echo '<tr>';
					echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">'.$row->show_beginn.'<br>'.$row->show_ende.'</td>';
					echo '<td style="border-bottom: 1px solid Black;'.($aktiv?'background-color:#009e84;color:#FFF;':'').'" valign="top"  '.($k % 2==0?'':' class="MarkLine" ').' >';
						echo 'anz.:'.$max_person_array[$k]['stunde'];
						echo '<br>';	
						echo  'FH&nbsp;&nbsp;&nbsp;'.($raum_max_anz*TAGE_PRO_WOCHE);
						echo '<br>';					
						echo ' &Oslash;&nbsp;'.($max_person_array[$k]['stunde']?number_format(($max_person_array[$k]['stunde'])/TAGE_PRO_WOCHE / ($raum_max_anz),2)*100:0).'%';						
						echo '<br>';					
						echo 'Raum&nbsp;'.$max_person_array[$k]['stunde_max'];
						echo '<br>';					
						echo ' &Oslash;&nbsp;'.($max_person_array[$k]['stunde']?number_format(($max_person_array[$k]['stunde']/TAGE_PRO_WOCHE) / ($max_person_array[$k]['stunde_max']/TAGE_PRO_WOCHE),2)*100:0).'%';						
					echo '</td>';		

				$stunde=$stunde+$max_person_array[$k]['stunde'];
				$stunde_max=$stunde_max+$max_person_array[$k]['stunde_max'];
				
				for ($i=0; $i<TAGE_PRO_WOCHE; $i++)
				{
					echo '<td style="border-bottom: 1px solid Black;'.($aktiv?'background-color:#009e84;color:#FFF;':'').'" valign="top"  '.($k % 2==0?'':' class="MarkLine" ').' >';				
							echo 'anz.:'.$max_person_array[$i][$k]['tag_stunde'];						
							echo '<br>';					
							echo 'FH&nbsp;&nbsp;&nbsp;max.:'. $raum_max_anz;
							echo '<br>';							
							echo ' '.($max_person_array[$i][$k]['tag_stunde']?number_format($max_person_array[$i][$k]['tag_stunde'] / $raum_max_anz,2)*100:0).'%';	
							echo '<br>';
							echo 'Raum max.:'. $max_person_array[$i][$k]['tag_stunde_max'];
							echo '<br>';							
							echo ' '.($max_person_array[$i][$k]['tag_stunde']?number_format($max_person_array[$i][$k]['tag_stunde'] / $max_person_array[$i][$k]['tag_stunde_max'],2)*100:0).'%';						
					echo '</td>';		
				}
				echo '</tr>';		
			}		


			echo '<tr>';
				echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">&Oslash;</td>';		

				echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">';
				echo  'FH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&Oslash;&nbsp;'.($stunde?number_format(($stunde)/$num_rows_stunde/TAGE_PRO_WOCHE / ($raum_max_anz),2)*100:0).'%';						
				echo '<br>';					
				echo 'Raum&nbsp;&Oslash;&nbsp;'.($stunde_max?number_format(($stunde/$num_rows_stunde/TAGE_PRO_WOCHE) / ($stunde_max/$num_rows_stunde/TAGE_PRO_WOCHE),2)*100:0).'%';						
				echo '</td>';		
			
			
			for ($i=0; $i<TAGE_PRO_WOCHE; $i++)
			{

				echo '<td style="text-align:center;color:#FFF;" class="ContentHeader">';
					echo 'FH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&Oslash;&nbsp;'.($max_person_array[$i]['tag']?number_format($max_person_array[$i]['tag'] / ($raum_max_anz *$num_rows_stunde),2)*100:0).'%';	
					echo '<br>';
					echo 'Raum&nbsp;&Oslash;&nbsp;'.($max_person_array[$i]['tag']?number_format($max_person_array[$i]['tag'] / $max_person_array[$i]['tag_max'],2)*100:0).'%';						
				echo '</td>';			
			}

			echo '</tr>';		
	

		echo '</table>';	
	}	
?>
</BODY>
</HTML>
