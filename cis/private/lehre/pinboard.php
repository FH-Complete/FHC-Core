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
/**
 * Pinboard
 * Zeigt alle Pinboardeintraege an. Am rechten Rand werden
 * Studiengangsleiter, Studiengangsleiter Stellvertreter, Assistentin 
 * und Studentenvertreter dieses Studienganges angezeigt.
 *
 * Aufruf pinboard.php?course_id=254&term_id=1[&showall]
 * course_id: Studiengang
 * term_id: Semester
 * showall: Zeigt alle Pinboardeintraege an
 */
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/news.class.php');
    
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim öffnen der Datenbankverbindung');

    $short='';
	if(isset($_GET['course_id']) && is_numeric($_GET['course_id']))
	{
		$stg_obj = new studiengang($sql_conn, $course_id);
		$short = $stg_obj->kuerzel;
		$short_long = $stg_obj->kurzbzlang;
		$course_id = $_GET['course_id'];
	}
	else 
		die('Fehler bei der Parameter&uuml;bergabe');
	
	if(isset($_GET['showall']))
	{
		$showall=true;
	}
	else 
	{
		$showall=false;
	}
	
	function print_news($stg_id, $semester, $sql_conn, $showall=false)
	{		
		$alter = ($showall?0:MAXNEWSALTER);
		$news_obj = new news($sql_conn);
		$zaehler=0;
		if($news_obj->getnews($alter, $stg_id, $semester))
		{
			foreach ($news_obj->result as $row)
			{
				$zaehler++;
				if($row->datum!='')
					$datum = date('d.m.Y',strtotime(strftime($row->datum)));
				else 	
					$datum='';
				
				if($row->semester == '')
				{
					echo '<p><small>'.$datum.' - '.$row->verfasser.' - [Allgemein]</small><br><b>'.$row->betreff.'</b><br>';
				}
				else
				{
					echo '<p><small>'.$datum.' - '.$row->verfasser.' - [Semester '.$row->semester.']</small><br><b>'.$row->betreff.'</b><br>';
				}
				
				echo "$row->text</p>";
			}
		}
		if($zaehler==0)
		   echo '<p>Zur Zeit gibt es keine aktuellen News!</p>';
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader" width="70%"><font class="ContentHeader">&nbsp;Pinboard <?php if(isset($short)) echo $short; ?></font></td>
		<td>&nbsp;</td>
		<td class="ContentHeader3" width="25%"><font class="HyperItem">&nbsp;Studiengangsmanagement</font></td>
      </tr>
	  <?php
	  	if(!isset($short))
			exit;
	  ?>
	  <tr>
	  	<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	  </tr>
	  <tr>
	  	<td valign="top"><?php print_news($course_id, (int)$term_id, $sql_conn, $showall); ?><a href='<?php echo $_SERVER['REQUEST_URI']."&showall"; ?>' class='Item'>Archiv</a></td>
		<td>&nbsp;</td>
		<td valign="top">
          <p>Studiengangsleiter:<br>
                <?php

                //Studiengangsleiter auslesen
				$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid=(SELECT uid FROM public.tbl_benutzerfunktion WHERE studiengang_kz='$course_id' AND funktion_kurzbz='stgl' LIMIT 1)";
				if($result_course_leader = pg_query($sql_conn, $qry))
				{
					$num_rows_course_leader = pg_numrows($result_course_leader);					
					if($num_rows_course_leader > 0)
					{
						$row_course_leader = pg_fetch_object($result_course_leader, 0);
					}
				}

                echo "<b>";
                
                if(isset($row_course_leader) && $row_course_leader != "")
				{
					if(!($row_course_leader->vorname == "" && $row_course_leader->nachname == ""))
					{
						echo $row_course_leader->titelpre.' '.$row_course_leader->vorname.' '.$row_course_leader->nachname.' '.$row_course_leader->titelpost;
					}
					else
					{
						echo "Nicht definiert";
					}
				}
				else
				{
					echo "Nicht definiert";
				}
				
                echo "</b><br>";
                
				if(isset($row_course_leader) && $row_course_leader != "")
				{
					if($row_course_leader->uid != "")
					{
						echo "<a href=\"mailto:$row_course_leader->uid@technikum-wien.at\" class=\"Item\">$row_course_leader->uid@technikum-wien.at</a>";
					}
					else
					{
						echo "E-Mail nicht definiert";
					}
				}
				else
				{
					echo "E-Mail nicht definiert";
				}
				
                echo "<br>";
			  	echo "Tel.:";
			  	
			  	if(isset($row_course_leader) && $row_course_leader != "")
				{
					if($row_course_leader->telefonklappe != "")
					{	
						echo '01 333 40 77 - '.$row_course_leader->telefonklappe;
					}
					else
					{
						echo "Nicht vorhanden";
					}
				}
				else
				{
					echo "Nicht vorhanden";
				}
				
			  	echo "</p>";
			  	echo "<p></p>";
			  	echo "<p>Stellvertreter:<br>";
                
			  	//Studiengangsleiter Stellvertreter auselesen
				$sql_query = "SELECT * FROM campus.vw_mitarbeiter WHERE uid=(SELECT uid FROM public.tbl_benutzerfunktion WHERE studiengang_kz='$course_id' AND funktion_kurzbz='stglstv' LIMIT 1)";
				
				if($result_course_leader_deputy = pg_query($sql_conn, $sql_query))
				{
					$num_rows_course_leader_deputy = pg_numrows($result_course_leader_deputy);
						
					if($num_rows_course_leader_deputy > 0)
					{
						$row_course_leader_deputy = pg_fetch_object($result_course_leader_deputy, 0);
					}
				}
				
                echo "<b>";
                
                if(isset($row_course_leader_deputy) && $row_course_leader_deputy != "")
				{
					if(!($row_course_leader_deputy->vorname == "" && $row_course_leader_deputy->nachname == ""))
					{
						echo $row_course_leader_deputy->titelpre.' '.$row_course_leader_deputy->vorname.' '.$row_course_leader_deputy->nachname.' '.$row_course_leader_deputy->titelpost;
					}
					else
					{
						echo "Nicht definiert";
					}
				}
				else
				{
					echo "Nicht definiert";
				}
				
                echo "</b><br>";
                
				if(isset($row_course_leader_deputy) && $row_course_leader_deputy != "")
				{
					if($row_course_leader_deputy->uid != "")
					{
						echo "<a href=\"mailto:$row_course_leader_deputy->uid@technikum-wien.at\" class=\"Item\">$row_course_leader_deputy->uid@technikum-wien.at</a>";
					}
					else
					{
						echo "E-Mail nicht definiert";
					}
				}
				else
				{
					echo "E-Mail nicht definiert";
				}
				
                echo "<br>";
  				echo "Tel.:";
  				
  				if(isset($row_course_leader_deputy) && $row_course_leader_deputy != "")
				{
					if($row_course_leader_deputy->telefonklappe != "")
					{	
						echo '01 333 40 77 - '.$row_course_leader_deputy->telefonklappe;
					}
					else
					{
						echo "Nicht vorhanden";
					}
				}
				else
				{
					echo "Nicht vorhanden";
				}
				
			  	echo "</p>";
			  	echo "<p>Sekretariat:</font><font face='Arial, Helvetica, sans-serif' size='2'><br>";
                //Sektritariat auslesen
                
				$sql_query = "SELECT * FROM campus.vw_mitarbeiter WHERE uid=(SELECT uid FROM public.tbl_benutzerfunktion WHERE studiengang_kz='$course_id' AND funktion_kurzbz='ass' LIMIT 1)";
					
				if($result_course_secretary = pg_query($sql_conn, $sql_query))
				{
					$num_rows_course_secretary = pg_numrows($result_course_secretary);
					
					if($num_rows_course_secretary > 0)
					{
						$row_course_secretary = pg_fetch_object($result_course_secretary, 0);
					}
				}
				
                echo "<b>";
                
                if(isset($row_course_secretary) && $row_course_secretary != "")
				{
					if(!($row_course_secretary->vorname == "" && $row_course_secretary->nachname == ""))
					{
						echo $row_course_secretary->titelpre.' '.$row_course_secretary->vorname.' '.$row_course_secretary->nachname.' '.$row_course_secretary->titelpost;
					}
					else
					{
						echo "Nicht definiert";
					}
				}
				else
				{
					echo "Nicht definiert";
				}
				
                echo "</b><br>";
                
				if(isset($row_course_secretary) && $row_course_secretary != "")
				{
					if($row_course_secretary->uid != "")
					{
						echo "<a href=\"mailto:$row_course_secretary->uid@technikum-wien.at\" class=\"Item\">$row_course_secretary->uid@technikum-wien.at</a>";
					}
					else
					{
						echo "E-Mail nicht definiert";
					}
				}
				else
				{
					echo "E-Mail nicht definiert";
				}
				
                echo "<br>";
  				echo "Tel.:";
  				
  				if(isset($row_course_secretary) && $row_course_secretary != "")
				{
					if($row_course_secretary->telefonklappe != "")
					{	
						echo '01 333 40 77 - '.$row_course_secretary->telefonklappe;
					}
					else
					{
						echo "Nicht vorhanden";
					}
				}
				else
				{
					echo "Nicht vorhanden";
				}
				
				echo "<p>Studentenvertreter:</font><font face='Arial, Helvetica, sans-serif' size='2'><br>";
				
				$sql_query = "SELECT tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre, tbl_person.titelpost, tbl_benutzer.uid FROM public.tbl_person, public.tbl_benutzer,public.tbl_benutzerfunktion WHERE studiengang_kz='$course_id' AND funktion_kurzbz='stdv' AND tbl_person.person_id=public.tbl_benutzer.person_id AND tbl_benutzerfunktion.uid=tbl_benutzer.uid";
				
				if($result_course_stdv = pg_query($sql_conn, $sql_query))
				{
					$num_rows_course_stdv = pg_numrows($result_course_stdv);
					
					if($num_rows_course_stdv > 0)
					{
						while($row_stdv = pg_fetch_object($result_course_stdv))
						{						
							echo "<a href='mailto:".$row_stdv->uid."@technikum-wien.at'>$row_stdv->titelpre $row_stdv->vorname $row_stdv->nachname $row_stdv->titelpost</a><br>";
						}
					}
					else
					{
						echo "<b>Nicht vorhanden</b>";
					}
				}
?>
				
            	<table border="0" width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td nowrap>
				  <?php
				    $path = '../../../documents/'.strtolower($short).'/lehrziele';
					if(!$dest_dir = @dir($path))
					{						
						if(!is_dir($path))
						{
							if(!is_dir('../../../documents/'.strtolower($short)))
								exec('mkdir -m 775 "../../../documents/'.strtolower($short).'"');
							exec('mkdir -m 775 "../../../documents/'.strtolower($short).'/lehrziele"');
							exec('sudo chgrp teacher ../../../documents/'.strtolower($short).'/lehrziele');
						}
					
						$dir_empty = true;
						$dest_dir = @dir($path);
						while($entry = $dest_dir->read())
						{
							if($entry != "." && $entry != "..")
							{
								$dir_empty = false;
								
								break;
							}
						}
					}
											
					if(isset($dir_empty) && $dir_empty == false)
					{
						echo '<img src="../../../skin/images/seperator.gif">&nbsp;<a href="'.$dest_dir->path.'/" class="Item" target="_blank">Lehrziele</a>';
					}
					else
					{						
						echo '<img src="../../../skin/images/seperator.gif">&nbsp;Lehrziele';
					}
				  ?>
              </td>
            </tr>
            <tr>
              <td nowrap>
                <?php						
                	$path = '../../../documents/'.strtolower($short).'/download';
					if(!$dest_dir = @dir($path))
					{
					
						if(!is_dir($path))
						{
							if(!is_dir('../../../documents/'.strtolower($short)))
								exec('mkdir -m 775 "../../../documents/'.strtolower($short).'"');
							exec('mkdir -m 775 "../../../documents/'.strtolower($short).'/download"');
							exec('sudo chgrp teacher ../../../documents/'.strtolower($short).'/download');
						}
					
						$dir_empty = true;
						$dest_dir = @dir($path);
						while($entry = $dest_dir->read())
						{
							if($entry != "." && $entry != "..")
							{
								$dir_empty = false;
								
								break;
							}
						}
					}
										
					if(isset($dir_empty) && $dir_empty == false)
					{
						echo '<img src="../../../skin/images/seperator.gif">&nbsp;<a href="'.$dest_dir->path.'/" class="Item" target="_blank">Allgemeiner Download</a>';
					}
					else
					{						
						echo '<img src="../../../skin/images/seperator.gif">&nbsp;Allgemeiner Download';
					}

				?>
				
              </td>
            </tr>
            <tr>
              <td nowrap>
                <?php
					echo '<img src="../../../skin/images/seperator.gif">&nbsp;<a href="news://cis.technikum-wien.at/'.strtolower($short_long).'" class="Item" target="_blank">Newsgroups</a>';

				?>
              </td>
            </tr>
          </table>          
          </td>
	  </tr>
    </table></td>
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>