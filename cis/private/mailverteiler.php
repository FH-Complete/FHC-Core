<?php
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

   //Studentenvertreter duerfen die Verteiler genauso wie Lektoren verwenden
   /*doch nicht
   if(!$is_lector)
   {
   		$fkt = new benutzerfunktion($conn);
   		if($fkt->benutzerfunktion_exists($user, 'stdv'))
   			$is_lector=true;
   }
   */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="JavaScript">
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

<body>
<table  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td>
    	<table border="0" cellspacing="0" cellpadding="0">
	      <tr>
	        <td width ="690" class="ContentHeader"><font class="ContentHeader">&nbsp;Kommunikation - Mailverteiler</font></td>
	      </tr>
	      </table><br><br>
		   	<strong><font color="#ff0000">Hinweis: </font></strong>Diese Verteiler d&uuml;rfen nur f&uuml;r Fachhochschul-relevante Zwecke verwendet werden!
		   		<br>
            <strong><font color="#ff0000">Info: </font></strong>Infos bez&uuml;glich  <a href="../cisdocs/Mailverteiler.pdf" target="_blank">Berechtigungskonzept</a> Mailverteiler, <a href="../cisdocs/bedienung_mailverteiler.pdf" target="_blank">Bedienungsanleitung</a> Mailverteiler
            <br>
<?php
		$stg_obj = new studiengang($conn);
		if(!$stg_obj->getAll('ascii(bezeichnung), bezeichnung, typ', true))
			echo $stg_obj->errormsg;

		foreach($stg_obj->result as $row)
		{
		    // Kopfzeile hinausschreiben
		    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		    echo "<tr><td>&nbsp;</td></tr>";
		    echo "<tr>";
		  	echo "   <td width=\"390\" class=\"ContentHeader2\">";
		    echo "   $row->kuerzel - $row->bezeichnung<a name=\"$row->studiengang_kz\">&nbsp;</a></td>";
		    echo "   <td width=\"20\"class=\"ContentHeader2\">&nbsp;</td>";
		    echo "   <td width=\"200\"class=\"ContentHeader2\">&nbsp;</td>";
			echo "   <td width=\"100\"class=\"ContentHeader2\" align=\"right\"><a class=\"Item2\" href=\"mailverteiler.php#\">top&nbsp;</a></td>";
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
				if(!$row1->aktiv)
				{
					if($is_lector)
					{
						/* open a popup containing the final dispatcher address */
						echo '<a href="#" onClick="javascript:window.open(\'open_grp.php?grp='.strtolower($row1->gruppe_kurzbz).'&desc='.$row1->beschreibung.'\',\'_blank\',\'width=500,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes\');return false;" class="Item"><img src="../../skin/images/open.gif" title="Verteiler &ouml;ffnen"></a>';
				    	echo "</td>";
					 	echo " <td width='200'>";	
					 	echo "<a href='mailto:".$row1->gruppe_kurzbz."@technikum-wien.at' class='Item'>".strtolower($row1->gruppe_kurzbz)."@technikum-wien.at</a></td>";						
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
					echo "<a href='mailto:".strtolower($row1->gruppe_kurzbz)."@technikum-wien.at' class='Item'>".strtolower($row1->gruppe_kurzbz)."@technikum-wien.at</a></td>";
				}
					 				 
				if(strtolower($row1->gruppe_kurzbz)=='tw_std')
					echo '<td width=\"100\" align="right">&nbsp;</td>';
				else
					echo ' <td width=\"100\" align="right"><a href="#" onClick="javascript:window.open(\'pers_in_grp.php?grp='.$row1->gruppe_kurzbz.'\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes\');return false;" class="Item">Personen</a>';

				echo "</tr>\n";					 
	  		}


		  	//StudentenListe Rausschreiben
		  	if($row->studiengang_kz!=0) //0 ist für ganzes TW
		  	{
				// ffe, 20060508: Display the opening link for department dispatchers only for students of the particular department
				$std_obj = new student($conn, $user);
				
		  		$qry_stud = "SELECT count(*) as anzahl FROM public.tbl_student WHERE studiengang_kz='$row->studiengang_kz' AND student_uid NOT LIKE '_Dummy%'";

			  		if(!$row_stud=pg_fetch_object(pg_query($conn, $qry_stud)))
			  			echo 'Fehler beim laden der Studenten';

			  		if($row_stud->anzahl>0)
			  		{
			  			echo "<tr><td width=\"390\" >&#8226; Alle Studenten dieses Studiengangs</td>";

						// ffe, 20060508: Display the opening link for department dispatchers only for students of the particular department
						if($is_lector || $std_obj->studiengang_kz==$row->studiengang_kz)
						{
							echo " <td width=\"20\">";
							echo '<a href="#" onClick="javascript:window.open(\'open_grp.php?grp='.strtolower($row->kuerzel).'_std&desc=Alle Studenten von '.strtolower($row->kuerzel).'\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes\');return false;" class="Item"><img src="../../skin/images/open.gif" title="Verteiler &ouml;ffnen"></a></td>';
							/* open a popup containing the final dispatcher address */						    
						    echo " <td width=\"200\" ><a href=\"mailto:".strtolower($row->kuerzel)."_std@technikum-wien.at\" class=\"Item\">".strtolower($row->kuerzel)."_std@technikum-wien.at</a></td>";
						}
						else
						{
							echo " <td width=\"20\">&nbsp</td>";
				  			//echo " <td width=\"200\" ><a href=\"mailto:".strtolower($row->kurzbz)."_std@technikum-wien.at\" class=\"Item\">".strtolower($row->kurzbz)."_std@technikum-wien.at</a></td>";
				  			echo " <td width=\"200\" >gesperrt</td>";
						}

					    echo ' <td width=\"100\" align="right"><a href="#" onClick="javascript:window.open(\'stud_in_grp.php?kz='.$row->studiengang_kz.'&all=true\',\'_blank\',\'width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes\');return false;" class="Item">Personen</a>';
						echo "</tr>\n";
			  		}
		  			echo "\n";
		  			echo '<tr><td><a href="#" onClick="return(js_toggle_container(\''.$row->kuerzel.'\'));" class="Item">&#8226; Studentenverteiler</a>';
					echo '</td></tr></table>';
					echo '<table border="0" cellspacing="0" cellpadding="0" id="'.$row->kuerzel.'" style="display: none">';
					
			  		//$sql_query1 = "SELECT DISTINCT studiengang_kz, semester, verband, gruppe FROM public.tbl_student where studiengang_kz ='$row->studiengang_kz' AND student_uid NOT LIKE '_dummy%' ORDER BY semester";
					$lv_obj = new lehrverband($conn);
					$lv_obj->getlehrverband($row->studiengang_kz);
					
					$zeilenzaehler=0;
			  		echo "\n";
			  		foreach($lv_obj->result as $row1)
			  		{
			  			if((!is_null($row1->semester)) AND ($row1->semester != "") AND ($row1->semester<=$row->max_semester)) //($row1->semester<'10'))
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
				  					$param = "kz=".$row->studiengang_kz."&sem=".$row1->semester;
				  					$strhelp = strtolower($row->kuerzel.trim($row1->semester).trim($row1->verband).trim($row1->gruppe));
						  			echo "<tr>\n";
						  			echo "  <td width=\"390\">&nbsp;&nbsp;&nbsp;&#8226; Semester $row1->semester";
						  			if(trim($row1->verband)!='')
						  			{
						  				$param .="&verband=$row1->verband";
						  				echo " Verband $row1->verband";
						  			}
						  			if(trim($row1->gruppe)!='')
						  			{
							  			$param .="&grp=$row1->gruppe";
							  			echo " Gruppe $row1->gruppe";
							  		}
						  			
						  			echo "</td>";
						  			echo "  <td width='20'></td>";
						  			echo "  <td width=\"200\"><a href='mailto:$strhelp@technikum-wien.at' class=\"Item\">$strhelp@technikum-wien.at</a></td>";
						  			echo "  <td width=\"100\" align=\"right\"><a class=\"Item\" href=\"#\" onClick='javascript:window.open(\"stud_in_grp.php?".$param."\",\"_blank\",\"width=600,height=500,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes\");return false;'>Personen</a></td>";
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
	<td with="10">&nbsp;
	</td>
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