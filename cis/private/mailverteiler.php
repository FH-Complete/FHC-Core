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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/phrasen.class.php');
	
$sprache = getSprache(); 
$p=new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));

$user=get_uid();

$is_lector=check_lektor($user);
$is_stdv=false;
$std_obj = new student($user);   
//Studentenvertreter duerfen den Verteiler tw_std oeffnen

if(!$is_lector)
{
	$fkt = new benutzerfunktion();
	if($fkt->benutzerfunktion_exists($user, 'stdv', true)) // Studienvertretung
		$is_stdv=true;
	elseif($fkt->benutzerfunktion_exists($user, 'hsv', true)) // Hochschulvertretung
		$is_stdv=true;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<title><?php echo $p->t('mailverteiler/mailverteiler');?></title>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script language="JavaScript" type="text/javascript">
	<!--
		__js_page_array = new Array();
	
	    function js_toggle_container(conid)
	    {
	
			if (document.getElementById)
			{
	        	var block = "table-row";
				if (navigator.appName.indexOf('Microsoft') > -1)
					block = 'block';
	            var status = __js_page_array[conid];
	            if (status == null)
	            	status = "none";
	            if (status == "none")
	            {
	            	document.getElementById(conid).style.display = block;
	            	__js_page_array[conid] = "visible";
	            }
	            else
	            {
	            	document.getElementById(conid).style.display = 'none';
	            	__js_page_array[conid] = "none";
	            }
	            return false;
	     	}
	     	else
	     		return true;
	  	}

		function container_visible(conid)
		{
			if(__js_page_array[conid] == 'visible')
				return true;
			else
				return false;
		}

		function show_layer(x)
		{
	 		if (document.getElementById && document.getElementById(x)) 
			{
				document.getElementById(x).style.visibility = 'visible';
				document.getElementById(x).style.display = 'inline';
			} else if (document.all && document.all[x]) {      
			   	document.all[x].visibility = 'visible';
				document.all[x].style.display='inline';
		      	} else if (document.layers && document.layers[x]) {                          
		           	 document.layers[x].visibility = 'show';
				 document.layers[x].style.display='inline';
		          }
		}
	
		function hide_layer(x)
		{
			var conid=x.substring(4);
			if(container_visible(conid))
				js_toggle_container(conid);
				
			if (document.getElementById && document.getElementById(x)) 
			{
			   	document.getElementById(x).style.visibility = 'hidden';
				document.getElementById(x).style.display = 'none';
	       	} else if (document.all && document.all[x]) {                                
				document.all[x].visibility = 'hidden';
				document.all[x].style.display='none';
	       	} else if (document.layers && document.layers[x]) {                          
		           	 document.layers[x].visibility = 'hide';
				 document.layers[x].style.display='none';
		          }
		}
		//-->		
	</script>
	<script language="JavaScript" type="text/javascript">
	$(document).ready(function() 
			{ 
			    $("#table1").tablesorter(
				{
					sortList: [[1,0]],
					widgets: ['zebra'],
					headers: {0:{sorter:false}}
				}); 
			} 
		)
	</script>
</head>

<body id="inhalt">
<h1><?php echo $p->t("mailverteiler/titel");?></h1>
<table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="cmscontent" rowspan="3" valign="top">	
		   	<?php echo $p->t("mailverteiler/absatz1");?>
	   		<br>
		   	<?php //echo $p->t("mailverteiler/absatz3");?>
	   		<br>
<?php
		$stg_obj = new studiengang();

		if(!$stg_obj->getAll(null, true))
			echo $stg_obj->errormsg;

		// Sortieren nach Kuerzel	
		if (!is_object($stg_obj->result) &&  count($stg_obj->result)>0)
		{
			$tw_arr=array();
			$nicht_tw_arr=array();	
			foreach($stg_obj->result as $row)
			{
				if (trim($row->kuerzel)=='ETW')
				{
					$tw_arr['ETW']=$row;
				}
				else
				{
					$nicht_tw_arr[trim($row->kuerzel)]=$row;
				}	
			}
			if(ksort($nicht_tw_arr))
			{	
				if ($new_tw_arr=array_merge($tw_arr,$nicht_tw_arr))
				{
					$stg_obj->result=array();
					foreach ($new_tw_arr as $key => $val) 
					{
						$stg_obj->result[]=$val;
					}
				}	
			}			
			if (isset($tw_arr)) unset($tw_arr);
			if (isset($new_tw_arr)) unset($new_tw_arr);
			if (isset($nicht_tw_arr)) unset($nicht_tw_arr);
		}
		
		foreach($stg_obj->result as $row)
		{
		    // Kopfzeile hinausschreiben (hide-Tabelle -> nur Kopfzeile)
		    echo "<table class='tabcontent2' id='hide".$row->kuerzel."' >";
		    echo '<tr onClick="hide_layer(\'hide'.$row->kuerzel.'\');show_layer(\'show'.$row->kuerzel.'\');">';
		  	echo "   <td height=\"18\" width=\"420\" class=\"ContentHeader2\" style='vertical-align: bottom;'><img height='9px' src='../../skin/images/right_lvplan.png' title='".$p->t('mailverteiler/anzeigen')."' alt='".$p->t('mailverteiler/anzeigen')."' border='0'>&nbsp;";
		    echo "   $row->kuerzel - ".$row->bezeichnung_arr[$sprache]."<a name=\"$row->studiengang_kz\">&nbsp;</a></td>";
		    echo "   <td width=\"20\" class=\"ContentHeader2\">&nbsp;</td>";
		    echo "   <td width=\"300\" class=\"ContentHeader2\">&nbsp;</td>";
			//echo "   <td width=\"100\" class=\"ContentHeader2\" align=\"right\"><a href=\"mailverteiler.php#\">top&nbsp;</a></td>"; // top-link entfernt
			echo "   <td width=\"100\" class=\"ContentHeader2\" align=\"right\">&nbsp;</td>";
			echo "   </tr>";
			echo "</table>";

		    // Kopfzeile hinausschreiben (show-Tabelle -> Kopfzeile mit Inhalt)
		    echo "<table class='tabcontent2' style='display:none;' id='show".$row->kuerzel."'>";
		    echo '<tr  onClick="show_layer(\'hide'.$row->kuerzel.'\');hide_layer(\'show'.$row->kuerzel.'\');">';
		
		  	echo "   <td height=\"18\" width=\"420\" class=\"ContentHeader2\" style='vertical-align: bottom;'><img height='9px' src='../../skin/images/right_lvplan.png' title='".$p->t('mailverteiler/ausblenden')."' alt='".$p->t('mailverteiler/ausblenden')."' border='0'>&nbsp;";
		    echo "   $row->kuerzel - ".$row->bezeichnung_arr[$sprache]."<a name=\"$row->studiengang_kz\">&nbsp;</a></td>";
		    echo "   <td width=\"20\" class=\"ContentHeader2\">&nbsp;</td>";
		    echo "   <td width=\"300\" class=\"ContentHeader2\">&nbsp;</td>";
			//echo "   <td width=\"100\" class=\"ContentHeader2\" align=\"right\"><a href=\"mailverteiler.php#\">top&nbsp;</a></td>";  // top-link entfernt
			echo "   <td width=\"100\" class=\"ContentHeader2\" align=\"right\">&nbsp;</td>";
			echo "   </tr>";			

			// Verteiler Normal
			$grp_obj = new gruppe();
			if(!$grp_obj->getgruppe($row->studiengang_kz, null, true, true))
				echo $grp_obj->errormsg;

			$zeile=0;	
			


		  	//StudentenListe Rausschreiben
		  	if($row->studiengang_kz!=0) //0 ist für ganzes TW
		  	{				
		  		$qry_stud = "SELECT count(*) as anzahl FROM public.tbl_student WHERE studiengang_kz='$row->studiengang_kz' AND student_uid NOT LIKE '_Dummy%'";

				if(!$row_stud=$db->db_fetch_object($db->db_query($qry_stud)))
					echo $p->t('mailverteiler/fehlerBeimLadenDerStudenten');

		  		echo '<tr><td colspan="4" style="padding-left: 12px;"><a href="#" onClick="return(js_toggle_container(\''.$row->kuerzel.'\'));"><img height="9px" src="../../skin/images/right_lvplan.png" title="'.$p->t('mailverteiler/ausblenden').'" alt="'.$p->t('mailverteiler/ausblenden').'" border="0">&nbsp;&nbsp;'.$p->t('mailverteiler/studentenverteiler').'</a></td></tr>';
				echo '<tr><td width="100%">';
				echo '<table class="tabcontent2" id="'.$row->kuerzel.'" style="display: none">';

				$lv_obj = new lehrverband();
				$lv_obj->getlehrverband($row->studiengang_kz);

				$zeilenzaehler=0;
		  		echo "\n";
		  		foreach($lv_obj->result as $row1)
		  		{
		  			if((!is_null($row1->semester)) && !empty($row1->semester) && ($row1->semester != "") && ($row1->semester<=$row->max_semester) && ($row1->semester>'0')) //($row1->semester<'10'))
		  			{
		  				$qry_cnt = "SELECT count(*) as anzahl FROM public.tbl_student WHERE studiengang_kz='$row1->studiengang_kz' AND semester='$row1->semester' AND student_uid NOT LIKE '_Dummy%'";
		  				if(trim($row1->verband)!='')
		  				{
			  				$qry_cnt .= " AND verband='$row1->verband'";

			  				if(trim($row1->gruppe)!='')
				  				$qry_cnt .= " AND gruppe='$row1->gruppe'";
			  			}
							
				  		if($row_cnt = $db->db_fetch_object($db->db_query($qry_cnt)))
				  		{
				  			if($row_cnt->anzahl>0)
				  			{
								
								$zeile++;
								if ($zeile%2)
								{
									$class=' class="row-odd" ';				
								}
								else
								{
									$class=' class="row-even" ';	
								}
								
				  				$param = "kz=".$row->studiengang_kz."&amp;sem=".$row1->semester;
				  				$strhelp = mb_strtolower($row->kuerzel.trim($row1->semester).trim($row1->verband).trim($row1->gruppe));
								echo "<tr ".$class.">\n";
								echo "  <td width=\"420\" style=\"padding-left: 18px;\">".$p->t('global/semester')." $row1->semester";
						  		if(trim($row1->verband)!='')
						  		{
						  			$param .="&amp;verband=$row1->verband";
						  			echo ' '.$p->t('global/verband')." $row1->verband";
						  		}
						  		if(trim($row1->gruppe)!='')
						  		{
									$param .="&amp;grp=$row1->gruppe";
						  			echo ' '.$p->t('global/gruppe')." $row1->gruppe";
						  		}
				  				if(trim($row1->bezeichnung)!='')
						  		{
						  			echo ' ('.$row1->bezeichnung.')';
						  		}
					  			echo "</td>";
					  			echo "  <td width='20'></td>";
					  			echo "  <td width=\"300\"><a href='mailto:$strhelp@".DOMAIN."' class=\"Item\">$strhelp@".DOMAIN."</a></td>";
					  			echo "  <td width=\"100\" align=\"right\"><a href=\"#\" onClick='javascript:window.open(\"stud_in_grp.php?".$param."\",\"_blank\",\"width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes,resizable=1\");return false;'>".$p->t('mailverteiler/personen')."</a>&nbsp;</td>";
					  			echo "</tr>";
					  			$zeilenzaehler++;
		  					}
		  				}
		  			}
		  		}
		  		if($zeilenzaehler==0)
		  		{
		  			echo "<tr><td>".$p->t('mailverteiler/keineVerteilerVorhanden')."</td></tr>";
		  		}
		  		echo "</table></td></tr>";
			}
		  	if($row->studiengang_kz!=0 && $row_stud->anzahl>0)
		  		{
		  			echo "<tr><td width=\"420\" style=\"padding-left: 12px;\">".$p->t('mailverteiler/alleStudentenDiesesStudienganges')."</td>";

					// ffe, 20060508: Display the opening link for department dispatchers only for students of the particular department
					if($is_lector || $std_obj->studiengang_kz==$row->studiengang_kz || !MAILVERTEILER_SPERRE)
					{
						echo " <td width=\"20\">";
						if(MAILVERTEILER_SPERRE)
							echo '<a href="#" onClick="javascript:window.open(\'open_grp.php?grp='.strtolower($row->kuerzel).'_std&amp;desc='.$p->t('mailverteiler/alleStudentenVon').' '.strtolower($row->kuerzel).'\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item"><img valign="bottom" alt="'.$p->t('mailverteiler/verteilerOeffnen').'" src="../../skin/images/lock.png" title="'.$p->t('mailverteiler/verteilerOeffnen').'"></a></td>';
						/* open a popup containing the final dispatcher address */
					    echo " <td width=\"300\" ><a href=\"mailto:".strtolower($row->kuerzel)."_std@".DOMAIN."\" class=\"Item\">".strtolower($row->kuerzel)."_std@".DOMAIN."</a></td>";
					}
					else
					{
						echo " <td width=\"20\">&nbsp</td>";
			  			echo " <td width=\"300\" >gesperrt</td>";
					}

				    echo ' <td width="100" align="right"><a href="#" onClick="javascript:window.open(\'stud_in_grp.php?kz='.$row->studiengang_kz.'&amp;all=true\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item">'.$p->t('mailverteiler/personen').'</a>&nbsp;';
					echo "</tr>\n";
		  		}
	  			echo "\n";
		  	foreach($grp_obj->result as $row1)
			{
				if(!$row1->aktiv)
					continue;
				
				$zeile++;
				if ($zeile%2)
				{
					$class=' class="row-odd" ';				
				}
				else
				{
					$class=' class="row-even" ';	
				}
				echo "<tr ".$class.">";
				echo " <td width=\"420\" style=\"padding-left: 12px;\">$row1->beschreibung</td>";

				// LINK for opening a closed mail dispatcher
				// display the open-link only when its a closed dispatcher and if the user has status lector
				// if dispatcher has attribute aktiv=true no opening action is needed
				echo "<td width=\"20\">";
				if($row1->gesperrt && MAILVERTEILER_SPERRE)
				{
					//Studentenvertreter duerfen den Verteiler fuer alle Studenten oeffnen
					if($is_lector || ($is_stdv && mb_strtolower($row1->gruppe_kurzbz)=='tw_std'))
					{
						/* open a popup containing the final dispatcher address */
						echo '<a href="#" onClick="javascript:window.open(\'open_grp.php?grp='.strtolower($row1->gruppe_kurzbz).'&amp;desc='.$row1->beschreibung.'\',\'_blank\',\'width=500,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item"><img valign="bottom" alt="'.$p->t('mailverteiler/verteilerOeffnen').'" src="../../skin/images/lock.png" title="'.$p->t('mailverteiler/verteilerOeffnen').'"></a>';
				    	echo "</td>";
					
					 	echo " <td width='300'>";
					 	echo "<nobr><a href='mailto:".$row1->gruppe_kurzbz."@".DOMAIN."' class='Item'>".strtolower($row1->gruppe_kurzbz)."@".DOMAIN."</a></nobr>";
						echo "&nbsp;</td>";
					}
					else
					{
						echo "&nbsp;</td>";
						echo " <td width='300'>";
						echo $p->t('mailverteiler/gesperrt');
						echo "&nbsp;</td>";
					}
				}
				else
				{
					echo "&nbsp;</td>";
					echo " <td width='300'>";
					echo "<nobr><a href='mailto:".strtolower($row1->gruppe_kurzbz)."@".DOMAIN."' class='Item'>".strtolower($row1->gruppe_kurzbz)."@".DOMAIN."</a></nobr>";
					echo "&nbsp;</td>";
				}

				if(strtolower($row1->gruppe_kurzbz)=='tw_std')
					echo '<td width="100" align="right">';
				else
					echo ' <td width="100" align="right"><a href="#" onClick="javascript:window.open(\'pers_in_grp.php?grp='.$row1->gruppe_kurzbz.'\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;">'.$p->t('mailverteiler/personen').'</a>';
				echo "&nbsp;</td>";

				echo "</tr>\n";
	  		}
	  		echo '</td></tr></table>';

		}

	//Menue oeffnen wenn kurzbz uebergeben wird
  	if(isset($_GET['kbzl']) && $_GET['kbzl']!='')
  	{
  	   echo "<script language='javascript'>
	              show_layer('show".$db->convert_html_chars($_GET['kbzl'])."');
	              hide_layer('hide".$db->convert_html_chars($_GET['kbzl'])."');
  	         </script>";
//  	              js_toggle_container('".$db->convert_html_chars($_GET['kbzl'])."');
			 }
echo '</td>';
if ($p->t("dms_link/anleitungMailverteiler")!='')
{
        echo '	<td class="menubox">
        		<p><a href="../../../cms/content.php?content_id='.$p->t("dms_link/anleitungMailverteiler").'" target="content">'.$p->t('mailverteiler/bedienungsanleitung').'</a></p>
        		</td>';
}
else 
{
	echo '<td style="width: 20%;">&nbsp;</td>';
}
echo '</tr>
<tr>
<td class="teambox" style="width: 20%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 20%;" valign="top">&nbsp;</td>
</tr>
</tbody>
</table></body></html>';
?>