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
	require_once('../../../include/studiensemester.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim öffnen der Datenbankverbindung');

    $senat=false;
    $short='';
    $course_id = '';
    $term_id = '';
    $fachbereich_kurzbz='';
    $studiensemester_kurzbz = '';
    $datum_content='';
    $stsem_content='';
    $datum = '';
    $user = get_uid();
    $stsemarr = array();
    $PHP_SELF = $_SERVER['PHP_SELF'];

    if(isset($_GET['studiensemester_kurzbz']))
    	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
    else
    {
    	$stsem_obj = new studiensemester($sql_conn);
    	$studiensemester_kurzbz = $stsem_obj->getaktorNext();
    }

    if(isset($_GET['datum']))
    	$datum = $_GET['datum'];

	if(isset($_GET['course_id']) && is_numeric($_GET['course_id']))
	{
		$stg_obj = new studiengang($sql_conn, $_GET['course_id']);
		$short = $stg_obj->kuerzel;
		$short_long = $stg_obj->kurzbzlang;
		$stg_bezeichnung = $stg_obj->bezeichnung;
		$course_id = $_GET['course_id'];
		$term_id = $_GET['term_id'];
	}

	if(isset($_GET['fachbereich_kurzbz']))
	{
		$fachbereich_kurzbz = $_GET['fachbereich_kurzbz'];
		if($fachbereich_kurzbz=='Senat')
			$senat = true;
	}

	if(isset($_GET['showall']))
	{
		$showall=true;
	}
	else
	{
		$showall=false;
	}

	function print_STGnews($stg_id, $semester, $sql_conn, $showall=false, $fachbereich_kurzbz)
	{
		$alter = ($showall?0:MAXNEWSALTER);
		$news_obj = new news($sql_conn);

		if($news_obj->getnews($alter, $stg_id, $semester, $showall, $fachbereich_kurzbz))
		{
			$zaehler = print_news($news_obj);
		}
		else
			echo $news_obj->errormsg;
		if($zaehler==0)
		   echo '<p>Zur Zeit gibt es keine aktuellen News!</p>';
	}

	function print_FBnews($sql_conn, $fachbereich_kurzbz, $datum)
	{
		$news_obj = new news($sql_conn);

		if($news_obj->getFBNews($fachbereich_kurzbz, $datum))
		{
			if($fachbereich_kurzbz=='Senat')
				$open=false;
			else
				$open=true;
			$zaehler = print_news($news_obj, $open);
		}
		else
			echo $news_obj->errormsg;
		if($zaehler==0)
		   echo '<p>Zur Zeit gibt es keine aktuellen News!</p>';
	}

	function print_news($news_obj, $open=true)
	{
		$zaehler=0;
		echo '<br /><div id="news">';
		foreach ($news_obj->result as $row)
		{
			$zaehler++;
			if($row->datum!='')
				$datum = date('d.m.Y',strtotime(strftime($row->datum)));
			else
				$datum='';

			echo '<div class="news">';
			//if($row->semester == '')
			//{
			echo '
			<div class="titel">
			<table width="100%">
				<tr>
					<td width="30%" align="left">'.$row->betreff.'</td>
					<td width="30%" align="center">'.$datum.'</td>
					<td width="30%" align="right" style="display: '.($open?'none':'block').'" id="'.$zaehler.'Mehr" ><a href="#" class="Item" onclick="return show(\''.$zaehler.'\')">mehr &gt;&gt;</a></td>
					<td width="30%" align="right" style="display: '.($open?'block':'none').'" id="'.$zaehler.'Verfasser">'.$row->verfasser.'</td>
				</tr>
			</table>
			</div>
			<div class="text" style="display: '.($open?'block':'none').';" id="'.$zaehler.'Text">
			'.str_replace("../../skin","../../../skin","$row->text").'
			</div>
			';
				//echo '<div class="titel"><table style="width: 100%"><tr><td  width="30%" align="left">'.$row->betreff.' </td><td width="30%" align=center>'.$datum.'</td><td width="30%" align=right>'.$row->verfasser.'</td></tr></table></div>';
			//}
			//else
			//{
			//	echo '<div class="titel">'.$row->betreff.' [Semester '.$row->semester.'] '.$datum.' '.$row->verfasser.'</div>';
			//}
			//echo '<div class="text">'.$row->text.'</div>';
			echo "</div><br />";
		}
		echo '</div>';
		return $zaehler;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="Javascript">
function show(id)
{
	document.getElementById(id+'Text').style.display = 'block';
	document.getElementById(id+'Verfasser').style.display = 'block';
	document.getElementById(id+'Verfasser').style.width = '100%';
	document.getElementById(id+'Mehr').style.display = 'none';
	return false;
}
</script>
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>

<?php

	//Anzeigen der Senatsbeschluesse
	if($senat)
	{
		echo '<td class="ContentHeader" width="100%"><font class="ContentHeader">&nbsp;Senatsbeschl&uuml;sse ';
		echo '</font></td>';
		echo ' </tr>';

		//Senatsbeschluesse duerfen nur die Mitarbeiter sehen
		if(!check_lektor($user, $sql_conn))
			die('<tr><td>Sie haben keine Berechtigung für diesen Bereich</td></tr>');

		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr><td>';
		//Datum aller Senatsbeschluesse holen
		$qry = "SELECT distinct datum FROM campus.tbl_news WHERE fachbereich_kurzbz='Senat'";
		if($result = pg_query($sql_conn, $qry));
		{
			while($row = pg_fetch_object($result))
			{
				//Studiensemester des Datums ermitteln
				$stsem = getStudiensemesterFromDatum($sql_conn, $row->datum);
				//Wenn dieses StSem noch nicht angezeigt wird, dann anzeigen
				if(!in_array($stsem, $stsemarr))
				{
					if($stsem_content!='')
						$stsem_content.=' - ';
					$stsem_content .="<a href='$PHP_SELF?fachbereich_kurzbz=Senat&studiensemester_kurzbz=$stsem' class='Item'>";

					if(isset($studiensemester_kurzbz) && $studiensemester_kurzbz==$stsem)
						$stsem_content .="<u>$stsem</u>";
					else
						$stsem_content .=$stsem;

					$stsem_content .="</a>";
					$stsemarr[] = $stsem;
				}
				//Datum ausgeben
				if(isset($studiensemester_kurzbz) && $studiensemester_kurzbz==$stsem)
				{
					if($datum == '')
						$datum = $row->datum;
					if($datum_content!='')
						$datum_content.=' - ';
					$datum_content.="<a href='$PHP_SELF?fachbereich_kurzbz=Senat&studiensemester_kurzbz=$stsem&datum=$row->datum' class='Item'>";
					//Wenn datum=ausgewaehltes Datum dann das Datum unterstreichen
					if($datum == $row->datum)
						$datum_content.='<u>'.date('d.m.Y',strtotime(strftime($row->datum))).'</u>';
					else
						$datum_content.=date('d.m.Y',strtotime(strftime($row->datum)));
					$datum_content.="</a>";
				}
			}
			echo "$stsem_content<br><br>$datum_content";
		}
		echo '</td><td>&nbsp;</td></tr>';
		echo '<tr><td class="tdvertical">';
		//News ausgeben
		print_FBnews($sql_conn, $fachbereich_kurzbz, $datum);
		echo '</td>';

	}
	else
	{
		echo '<td class="ContentHeader" width="70%"><font class="ContentHeader">&nbsp;Pinboard ';

		if(isset($stg_bezeichnung))
			echo ' - '.$stg_bezeichnung;

		echo '</font></td>';

		if(!isset($stg_bezeichnung))
			exit;

		echo '
		<td>&nbsp;</td>
		<td class="ContentHeader3" width="25%"><font class="HyperItem">&nbsp;Studiengangsmanagement</font></td>';

		echo ' </tr>';

		echo '<tr><td>&nbsp;</td>';


	?>
	</tr>
	  <tr>
	  	<td class="tdvertical"><?php print_STGnews($course_id, (int)$term_id, $sql_conn, $showall, $fachbereich_kurzbz); ?><a href='<?php echo $_SERVER['REQUEST_URI']."&showall"; ?>' class='Item'>Archiv</a></td>

		<td>&nbsp;</td>
		<td class="tdvertical">
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
			  	echo "<p>Sekretariat:</font><font face='Arial, Helvetica, sans-serif' size='2'>";
                //Sekritariat auslesen

				$sql_query = "SELECT distinct * FROM campus.vw_mitarbeiter WHERE uid in (SELECT uid FROM public.tbl_benutzerfunktion WHERE studiengang_kz='$course_id' AND funktion_kurzbz='ass')";

				if($result_course_secretary = pg_query($sql_conn, $sql_query))
				{
					$num_rows_course_secretary = pg_numrows($result_course_secretary);

					while($row_course_secretary = pg_fetch_object($result_course_secretary))
					{
		                echo "<br><b>";
		
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
						echo "<br>";
					}
				}
				
				$qry = "SELECT zusatzinfo_html FROM public.tbl_studiengang WHERE studiengang_kz='$course_id'";
				
				if($result = pg_query($sql_conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						echo $row->zusatzinfo_html;
					}
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
							echo "<a class='Item' href='mailto:".$row_stdv->uid."@technikum-wien.at'>$row_stdv->titelpre $row_stdv->vorname $row_stdv->nachname $row_stdv->titelpost</a><br>";
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
				  <td class="tdwrap">
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
              <td class="tdwrap">
                <?php
                	$path = '../../../documents/'.strtolower($short).'/download';
					if(!$dest_dir = is_dir($path))
					{

						if(!is_dir($path))
						{
							if(!is_dir('../../../documents/'.strtolower($short)))
								exec('mkdir -m 775 "../../../documents/'.strtolower($short).'"');
							exec('mkdir -m 775 "../../../documents/'.strtolower($short).'/download"');
							exec('sudo chgrp teacher ../../../documents/'.strtolower($short).'/download');
						}

					}
					$dest_dir = @dir($path);
					while($entry = $dest_dir->read())
					{
						if($entry != "." && $entry != "..")
						{
							$dir_empty = false;
							break;
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
              <td class="tdwrap">
                <?php
					echo '<img src="../../../skin/images/seperator.gif">&nbsp;<a href="news://cis.technikum-wien.at/'.strtolower($short_long).'" class="Item" target="_blank">Newsgroups</a>';

				?>
              </td>
            </tr>
          </table>
          </td>
<?php
	}
	?>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>