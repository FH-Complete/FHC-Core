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

   require_once('../config.inc.php');
   require_once('../../include/functions.inc.php');
   require_once('../../include/studiengang.class.php');
   require_once('../../include/gruppe.class.php');
   require_once('../../include/person.class.php');
   require_once('../../include/benutzer.class.php');
   require_once('../../include/student.class.php');
   require_once('../../include/lehrverband.class.php');
   require_once('../../include/benutzerfunktion.class.php');

   if(!$conn=pg_pconnect(CONN_STRING))
      die('Fehler beim Herstellen der DB Connection');

   $user=get_uid();

   $is_lector=check_lektor($user,$conn);
   $is_stdv=false;
   
   //Studentenvertreter duerfen den Verteiler tw_std oeffnen
   if(!$is_lector)
   {
   		$fkt = new benutzerfunktion($conn);
   		if($fkt->benutzerfunktion_exists($user, 'stdv'))
   			$is_stdv=true;
   }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
<title>Mailverteiler</title>
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

//-->
</script>

</head>

<body id="inhalt">
<table class="tabcontent">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    	<table class="tabcontent">
	      <tr>
	        <td width ="690" class="ContentHeader"><font class="ContentHeader">Kommunikation - Mailverteiler</font></td>
	      </tr>
	      </table>
		  <br><br>
		   	<strong><font class="error">Hinweis: </font></strong>Diese Verteiler d&uuml;rfen nur f&uuml;r Fachhochschul-relevante Zwecke verwendet werden!
		   		<br>
		   	<?php
		   	if(MAILVERTEILER_SPERRE)
		   		echo '<strong><font class="error">Info: </font></strong>Infos bez&uuml;glich  <a class="Item" href="../cisdocs/Mailverteiler.pdf" target="_blank">Berechtigungskonzept</a> Mailverteiler, <a class="Item" href="../cisdocs/bedienung_mailverteiler.pdf" target="_blank">Bedienungsanleitung</a> Mailverteiler';
		   	?>
            <br>
<?php
		$stg_obj = new studiengang($conn);
#		if(!$stg_obj->getAll('ascii(bezeichnung), bezeichnung, typ', true))
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
		    // Kopfzeile hinausschreiben
		    echo "<table class='tabcontent2'>";
		    echo "<tr><td>&nbsp;</td></tr>";
		    echo "<tr>";
		  	echo "   <td width=\"390\" class=\"ContentHeader2\">";
		    echo "   $row->kuerzel - $row->bezeichnung<a name=\"$row->studiengang_kz\">&nbsp;</a></td>";
		    echo "   <td width=\"20\" class=\"ContentHeader2\">&nbsp;</td>";
		    echo "   <td width=\"200\" class=\"ContentHeader2\">&nbsp;</td>";
			echo "   <td width=\"100\" class=\"ContentHeader2\" align=\"right\"><a class=\"Item2\" href=\"mailverteiler.php#\">top&nbsp;</a></td>";
			echo "   </tr>";
		    echo "<tr><td>&nbsp;</td></tr>\n";

			// Verteiler Normal
			$grp_obj = new gruppe($conn);
			if(!$grp_obj->getgruppe($row->studiengang_kz, null, true, true))
				echo $grp_obj->errormsg;

			foreach($grp_obj->result as $row1)
			{
				echo "<tr>";
				echo " <td width=\"390\" >&#8226; $row1->beschreibung</td>";

				// LINK for opening a closed mail dispatcher
				// display the open-link only when its a closed dispatcher and if the user has status lector
				// if dispatcher has attribute aktiv=true no opening action is needed
				echo "<td width=\"20\">";
				if(!$row1->aktiv && MAILVERTEILER_SPERRE)
				{
					//Studentenvertreter duerfen den Verteiler fuer alle Studenten oeffnen
					if($is_lector || ($is_stdv && strtolower($row1->gruppe_kurzbz)=='tw_std'))
					{
						/* open a popup containing the final dispatcher address */
						if(MAILVERTEILER_SPERRE)
							echo '<a href="#" onClick="javascript:window.open(\'open_grp.php?grp='.strtolower($row1->gruppe_kurzbz).'&amp;desc='.$row1->beschreibung.'\',\'_blank\',\'width=500,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item"><img alt="Verteiler" src="../../skin/images/open.gif" title="Verteiler &ouml;ffnen"></a>';
				    	echo "</td>";
					 	echo " <td width='200'>";
					 	echo "<a href='mailto:".$row1->gruppe_kurzbz."@".DOMAIN."' class='Item'>".strtolower($row1->gruppe_kurzbz)."@".DOMAIN."</a></td>";
					}
					else
					{
						echo "</td>";
						echo " <td width='200'>";
						//echo "".$row1->mail."@technikum-wien.at</td>";
						echo "gesperrt</td>";
					}
				}
				else
				{
					echo "</td>";
					echo " <td width='200'>";
					echo "<a href='mailto:".strtolower($row1->gruppe_kurzbz)."@".DOMAIN."' class='Item'>".strtolower($row1->gruppe_kurzbz)."@".DOMAIN."</a></td>";
				}

				if(strtolower($row1->gruppe_kurzbz)=='tw_std')
					echo '<td width="100" align="right">&nbsp;</td>';
				else
					echo ' <td width="100" align="right"><a href="#" onClick="javascript:window.open(\'pers_in_grp.php?grp='.$row1->gruppe_kurzbz.'\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item">Personen</a>';

				echo "</tr>\n";
	  		}


		  	//StudentenListe Rausschreiben
		  	if($row->studiengang_kz!=0) //0 ist für ganzes TW
		  	{
				// ffe, 20060508: Display the opening link for department dispatchers only for students of the particular department
				$std_obj = new student($conn, $user);

		  		$qry_stud = "SELECT count(*) as anzahl FROM public.tbl_student WHERE studiengang_kz='$row->studiengang_kz' AND student_uid NOT LIKE '_Dummy%'";

			  		if(!$row_stud=pg_fetch_object(pg_query($conn, $qry_stud)))
			  			echo 'Fehler beim Laden der Studenten';

			  		if($row_stud->anzahl>0)
			  		{
			  			echo "<tr><td width=\"390\" >&#8226; Alle Studenten dieses Studiengangs</td>";

						// ffe, 20060508: Display the opening link for department dispatchers only for students of the particular department
						if($is_lector || $std_obj->studiengang_kz==$row->studiengang_kz || !MAILVERTEILER_SPERRE)
						{
							echo " <td width=\"20\">";
							if(MAILVERTEILER_SPERRE)
								echo '<a href="#" onClick="javascript:window.open(\'open_grp.php?grp='.strtolower($row->kuerzel).'_std&amp;desc=Alle Studenten von '.strtolower($row->kuerzel).'\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item"><img alt="Verteiler" src="../../skin/images/open.gif" title="Verteiler &ouml;ffnen"></a></td>';
							/* open a popup containing the final dispatcher address */
						    echo " <td width=\"200\" ><a href=\"mailto:".strtolower($row->kuerzel)."_std@".DOMAIN."\" class=\"Item\">".strtolower($row->kuerzel)."_std@".DOMAIN."</a></td>";
						}
						else
						{
							echo " <td width=\"20\">&nbsp</td>";
				  			//echo " <td width=\"200\" ><a href=\"mailto:".strtolower($row->kurzbz)."_std@technikum-wien.at\" class=\"Item\">".strtolower($row->kurzbz)."_std@technikum-wien.at</a></td>";
				  			echo " <td width=\"200\" >gesperrt</td>";
						}

					    echo ' <td width="100" align="right"><a href="#" onClick="javascript:window.open(\'stud_in_grp.php?kz='.$row->studiengang_kz.'&amp;all=true\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes, resizable=1\');return false;" class="Item">Personen</a>';
						echo "</tr>\n";
			  		}
		  			echo "\n";
		  			echo '<tr><td><a href="#" onClick="return(js_toggle_container(\''.$row->kuerzel.'\'));" class="Item">&#8226; Studentenverteiler</a>';
					echo '</td></tr></table>';
					echo '<table class="tabcontent2" id="'.$row->kuerzel.'" style="display: none">';

			  		//$sql_query1 = "SELECT DISTINCT studiengang_kz, semester, verband, gruppe FROM public.tbl_student where studiengang_kz ='$row->studiengang_kz' AND student_uid NOT LIKE '_dummy%' ORDER BY semester";
					$lv_obj = new lehrverband($conn);
					$lv_obj->getlehrverband($row->studiengang_kz);

					$zeilenzaehler=0;
			  		echo "\n";
			  		foreach($lv_obj->result as $row1)
			  		{
			  			if((!is_null($row1->semester)) && ($row1->semester != "") && ($row1->semester<=$row->max_semester) && ($row1->semester>'0')) //($row1->semester<'10'))
			  			{
			  				$qry_cnt = "SELECT count(*) as anzahl FROM public.tbl_student WHERE studiengang_kz='$row1->studiengang_kz' AND semester='$row1->semester' AND student_uid NOT LIKE '_Dummy%'";
			  				if(trim($row1->verband)!='')
			  				{
				  				$qry_cnt .= " AND verband='$row1->verband'";

				  				if(trim($row1->gruppe)!='')
				  					$qry_cnt .= " AND gruppe='$row1->gruppe'";
			  				}


				  			if($row_cnt = pg_fetch_object(pg_query($conn, $qry_cnt)))
				  			{
				  				if($row_cnt->anzahl>0)
				  				{
				  					$param = "kz=".$row->studiengang_kz."&amp;sem=".$row1->semester;
				  					$strhelp = strtolower($row->kuerzel.trim($row1->semester).trim($row1->verband).trim($row1->gruppe));
						  			echo "<tr>\n";
						  			echo "  <td width=\"390\">&nbsp;&nbsp;&nbsp;&#8226; Semester $row1->semester";
						  			if(trim($row1->verband)!='')
						  			{
						  				$param .="&amp;verband=$row1->verband";
						  				echo " Verband $row1->verband";
						  			}
						  			if(trim($row1->gruppe)!='')
						  			{
							  			$param .="&amp;grp=$row1->gruppe";
							  			echo " Gruppe $row1->gruppe";
							  		}

						  			echo "</td>";
						  			echo "  <td width='20'></td>";
						  			echo "  <td width=\"200\"><a href='mailto:$strhelp@".DOMAIN."' class=\"Item\">$strhelp@".DOMAIN."</a></td>";
						  			echo "  <td width=\"100\" align=\"right\"><a class=\"Item\" href=\"#\" onClick='javascript:window.open(\"stud_in_grp.php?".$param."\",\"_blank\",\"width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes,resizable=1\");return false;'>Personen</a></td>";
						  			echo "</tr>";
						  			$zeilenzaehler++;


			  					}
			  				}
			  			}
			  		}
			  		if($zeilenzaehler==0)
			  		{
			  			echo "<tr><td>Keine Verteiler vorhanden</td></tr>";
			  		}
			  		$zeilenzaehler=0;
			  		echo "</table>";
		  		}
		  		else
		  		{
		  			echo "</table>";
		  		}

		  }
		  echo "</table>";
?>
  	</td>
	<td class="tdwidth10">&nbsp;</td>
  </tr>
</table>

<?php
	//Menue oeffnen wenn kurzbz uebergeben wird
  	if(isset($_GET['kbzl']) AND $_GET['kbzl']!='')
  	{
  	   echo "<script language='javascript'>
  	              js_toggle_container('".$_GET['kbzl']."');
  	         </script>";
    }
    ?>
</body></html>