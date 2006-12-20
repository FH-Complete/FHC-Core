<?php
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
    require_once('../../../include/studiensemester.class.php');
    require_once('../../../include/studiengang.class.php');
    require_once('../../../include/lehrveranstaltung.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');
    
	$user = get_uid();
	
	$rechte=new benutzerberechtigung($sql_conn);
	$rechte->getBerechtigungen($user);
	
	if(check_lektor($user,$sql_conn))
       $is_lector=true;
    else 
       $is_lector=false;
	
	function CutString($strVal, $limit)
	{
		if(strlen($strVal) > $limit+3)
		{
			return substr($strVal, 0, $limit) . "...";
		}
		else
		{
			return $strVal;
		}
	}
	
	if(!isset($course_id) && !isset($term_id))
	{
		$course_id = 254;
		$term_id = 1;
		
		if(!$is_lector)
		{
			$sql_query = "SELECT studiengang_kz, semester FROM campus.vw_student WHERE uid='$user' LIMIT 1";
				
			$result_student = pg_exec($sql_conn, $sql_query);
			$num_rows_student = pg_numrows($result_student);
			
			if($num_rows_student > 0)
			{
				$row = pg_fetch_object($result_student, 0);
				
				$course_id = $row->studiengang_id;
				$term_id = $row->semester;
			}
			
			if($course_id==0)
				$course_id=254;
			if($term_id==0)
				$term_id=1;
		}
	}
	else
	{			
		if(!isset($course_id) || $course_id==0)
		{
			$course_id = 254;
		}
	
		if(!isset($term_id) || $term_id==0)
		{
			$term_id = 1;
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">

<script language="JavaScript">
<!--        
__js_page_array = new Array();
        
function js_toggle_container(conid) 
{
	if (document.getElementById) 
	{
		var block = "table-row";
  
		if (navigator.appName.indexOf('Microsoft') > -1) 
		{
			block = 'block';
		}

		var status = __js_page_array[conid];
		if (status == null) 
		{
			status = "none";
		}

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
   {
     return true;
   }
}
  //-->
</script>

<script language="JavaScript">
<!--        
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
	  
	  if(restore) 
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
  //-->
</script>

<?php
	echo '<script language="JavaScript">';
	echo '	parent.content.location.href="pinboard.php?course_id='.$course_id.'&term_id='.$term_id.'"';
	echo '</script>';
?>
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="159" valign="top" nowrap>
	  <table width="100%"  cellspacing="0" cellpadding="0" frame="rhs" style="border-color:#BCBCBC;">
	    <form method="post" action="menu.php">
		<tr>
          <td nowrap><a class="HyperItem" href="../../index.html" target="_top">&lt;&lt; Zum Campus wechseln </a></td>
  		</tr>
		<tr>
          <td nowrap>&nbsp;</td>
  		</tr>
		<tr>
		  <td>
		  	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
			  <tr>
			  	<td width="81" nowrap>Studiengang: </td>
			  	<td nowrap>
			  	<select name="course" onChange="MM_jumpMenu('self',this,0)" class="TextBox">
				<?php
					$stg_obj = new studiengang($sql_conn);
					$stg_obj->getAll('kurzbz, kurzbzlang');
					//$sql_query = "SELECT DISTINCT studiengang_kz AS id, kurzbzlang FROM public.tbl_studiengang WHERE NOT(studiengang_kz='0') ORDER BY kurzbzlang";
					
					//$result = pg_exec($sql_conn, $sql_query);
					//$num_rows_result = pg_num_rows($result);
					$sel_kurzbzlang='';
				
					foreach($stg_obj->result as $row)
					{												
						if($row->studiengang_kz!=0)
						{							
							if(isset($course_id) AND $course_id == $row->studiengang_kz)
							{
								echo '<option value="menu.php?course_id='.$row->studiengang_kz.'&term_id='.$term_id.'" selected>'.$row->kurzbz .'('.$row->kurbzlang.')</option>';
								$sel_kurzbzlang=$row->kurzbzlang;
							}
							else
							{
								echo '<option value="menu.php?course_id='.$row->studiengang_kz.'&term_id='.$term_id.'">'.$row->kurzbz .'('.$row->kurzbzlang.')</option>';
							}
						}
					}
				?>
			  	</select>&nbsp;&nbsp;&nbsp;&nbsp;
			  	</td>
			  </tr>
			  <tr>
			  	<td nowrap>&nbsp;</td>
			  </tr>
			  <tr>
			  	<td nowrap>Semester: </td>
			  	<td nowrap>
			  	<select name="term" onChange="MM_jumpMenu('self',this,0)" class="TextBox">
				<?php

					$stg_obj=new studiengang($sql_conn,$course_id);
					
				    $max = $stg_obj->max_semester;
				    
				    if($term_id>$max)
				       $term_id=1;
				    
					for($i=0;$i<$max;$i++)
					{
						if(($i+1)==$term_id)
						   echo '<option value="menu.php?course_id='.$course_id.'&term_id='.($i+1).'" selected>'.($i+1).'. Semester</option>';
						else
						   echo '<option value="menu.php?course_id='.$course_id.'&term_id='.($i+1).'">'.($i+1).'. Semester</option>';
						   
					}
					
				?>
			  	</select>&nbsp;
			  	</td>
			  </tr>
			</table>
		  </td>
		</tr>
		</form>
		<tr>
		  <td nowrap>&nbsp;</td>
		</tr>
		
		<?php
			$lv_obj = new lehrveranstaltung($sql_conn);
			
			$lv_obj->load_lva($course_id,$term_id, null, true);
						
			foreach($lv_obj->lehrveranstaltungen as $row)
			{
				echo '<tr>';
				echo '	<td nowrap><ul style="margin: 0px; padding: 0px; padding-left: 20px;">';
				echo "<li><a class=\"Item2\" title=\"".$row->bezeichnung."\" href=\"lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".CutString($row->bezeichnung, 21)."</a></li>";
				echo '	</ul></td>';
				echo '</tr>';
			}
			
			echo '<tr>';
			echo '	<td nowrap>&nbsp;</td>';
			echo '</tr>';
			
			//Eigenen LV des eingeloggten Lektors anzeigen
			if($is_lector || $rechte->isBerechtigt('admin'))
			{
		?>
		<tr>
          <td nowrap><a href="?Eigene" class="MenuItem" onClick="return(js_toggle_container('Eigene'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Meine LV</a></td>
  		</tr>
		<tr>
          <td nowrap>
		  	<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Eigene" style="display: none;">
			  <tr>
			  	<td width="10" nowrap>&nbsp;</td>
				<td nowrap>
				<ul style="margin-top: 0px; margin-bottom: 0px;">
				<?php
				$stsemobj = new studiensemester($sql_conn);
				$stsem = $stsemobj->getAktorNext();
				
				$stg_obj = new studiengang($sql_conn);
				if($stg_obj->getAll())
				{				
					$stg = array();
				
					foreach($stg_obj->result as $row)
							$stg[$row->studiengang_kz] = $row->kurzbzlang;
				}
				else 
					echo "Fehler beim Auslesen der Studiengaenge";		
				
				//$qry = "SELECT * FROM tbl_lehrfach WHERE lehrfach_nr IN (SELECT distinct lehrfach_nr FROM tbl_lehrveranstaltung WHERE lektor='$user' AND studiensemester_kurzbz='$stsem') AND studiengang_kz!=0";
				$qry = "SELECT bezeichnung, studiengang_kz, semester, lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id  FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter 
				        WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND 
				        tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND 
				        mitarbeiter_uid='$user' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem'";
				
				if($result = pg_query($sql_conn,$qry))
				{
						while($row = pg_fetch_object($result))
							echo "<li><a class=\"Item2\" title=\"".$row->bezeichnung."\" href=\"lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".$stg[$row->studiengang_kz].' '.$row->semester.' '.$row->lehreverzeichnis."</a></li>";
				}
				else 
					echo "Fehler beim Auslesen des Lehrfaches";
				
				?>	
				</ul>
				</td>
			  </tr>
			</table>
		  </td>
		</tr>
		<?php
			}
		?>
		<tr>
          <td nowrap><a class="MenuItem" href="pinboard.php?course_id=<?php echo $course_id; ?>&term_id=<?php echo $term_id; ?>" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Pinboard</a></td>
  		</tr>
		<tr>
          <td nowrap><a href="?Info &amp; Kommunikation" class="MenuItem" onClick="return(js_toggle_container('Info &amp; Kommunikation'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Info &amp; Kommunikation</a></td>
  		</tr>
		<tr>
          <td nowrap>
		  	<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Info &amp; Kommunikation" style="display: none;">
			  <tr>
			  	<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="Item" href="../lvplan/" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Stundenplan</a></td>
			  </tr>
	    	  <tr>
			  	<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webmail</a></td>
			  </tr>
			  <tr>
			  	<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="Item" href="../info/faq_upload.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;FAQ</a></td>
			  </tr>
			</table>
		  </td>
  		</tr>
		<?php
			if($is_lector || $rechte->isBerechtigt('admin'))
			{
				echo '<tr>';
				echo '  <td nowrap><a href="?Lektorenbereich" class="MenuItem" onClick="return(js_toggle_container(\'Lektorenbereich\'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lektorenbereich</a></td>';
				echo '</tr>';
				echo '<tr>';
				echo '  <td nowrap>';
				echo '  	<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lektorenbereich" style="display: none;">';
				

				echo '	  <tr>';
				echo '	  	<td width="10" nowrap>&nbsp;</td>';
				echo '		<td nowrap><a class="Item" href="ects/index.php?stg='.$course_id.'&sem='.$term_id.'" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV Info</a></td>';
				echo '	  </tr>';

				
				echo '	  <tr>';
				echo '	  	<td width="10" nowrap>&nbsp;</td>';
				echo '		<td nowrap><a class="Item" href="fernlehrunterlagen.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Fernlehrunterlagen</a></td>';
				echo '	  </tr>';
				echo '	  <tr>';
				echo '	  	<td width="10" nowrap>&nbsp;</td>';
				echo '		<td nowrap><a class="Item" href="dokumentenvorlagen.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Vorlagen f&uuml;r die<br>&nbsp;&nbsp;&nbsp;Dokumentenerstellung</a></td>';
				echo '	  </tr>';
				echo '	  <tr>';
				echo '	  	<td width="10" nowrap>&nbsp;</td>';
				echo '		<td nowrap><a class="Item" href="pinboardverwaltung.php?course_id='.$course_id.'&term_id='.$term_id.'" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Pinboardverwaltung</a></td>';
				echo '	  </tr>';
				echo '	  <tr>';
				echo '	  	<td width="10" nowrap>&nbsp;</td>';
				echo '		<td nowrap><a class="Item" href="upload.php" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webupload</a></td>';
				echo '	  </tr>';
				echo '	</table>';
				echo '  </td>';
				echo '</tr>';
			}
			writeCISlog('STOP');
		?>
		<tr>
          <td nowrap><a class="MenuItem" href="../mailverteiler.php?kbzl=<?php echo $sel_kurzbzlang.'#'.$course_id; ?>" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mailverteiler</a></td>
  		</tr>
	  </table>
	</td>
  </tr>
</table>
</body>
</html>
