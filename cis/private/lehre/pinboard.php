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
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
 
/*--------------------------------------------------------------------------------------------
 * Pinboard
 * Zeigt alle Pinboardeintraege an. Am rechten Rand werden
 * Studiengangsleiter, Studiengangsleiter Stellvertreter, Assistentin
 * und Studentenvertreter dieses Studienganges angezeigt.
 *
 * Aufruf pinboard.php?course_id=254&term_id=1[&showall]
 * course_id: Studiengang
 * term_id: Semester
 * showall: Zeigt alle Pinboardeintraege an
 --------------------------------------------------------------------------------------------*/
 
 
// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
		
// ---------------- Diverse Funktionen und UID des Benutzers ermitteln
	require_once('../../../include/functions.inc.php');
	if (!$user=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden !');

		
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/news.class.php');

		// Open der NEWs-Classe
	if (!$news = new news())
			die('News Fehler! '.$news->errormsg);
	
	// Init	
	$error='';
  $short='';
	$short_long ='';
	$stg_bezeichnung='';
	
  $datum_content='';
  $stsem_content='';
	$stsemarr = array();

	// Parameter einlesen		
	$news_id=trim((isset($_REQUEST['news_id']) ? $_REQUEST['news_id']:''));
	$datum=trim((isset($_REQUEST['datum']) ? $_REQUEST['datum']:''));
	$showall=(isset($_REQUEST['showall']) ? true:false);

	$fachbereich_kurzbz=trim((isset($_REQUEST['fachbereich_kurzbz']) ? $_REQUEST['fachbereich_kurzbz']:''));
	
	$studiengang_kz=(isset($_REQUEST['course_id'])?$_REQUEST['course_id']:(isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:''));
	$semester=(isset($_REQUEST['term_id'])?$_REQUEST['term_id']:(isset($_REQUEST['semester'])?$_REQUEST['semester']:0));
	$studiensemester_kurzbz=trim((isset($_REQUEST['studiensemester_kurzbz']) && is_numeric($_REQUEST['studiensemester_kurzbz']) ? $_REQUEST['studiensemester_kurzbz']:''));
	
#echo "<p>fachbereich_kurzbz:$fachbereich_kurzbz, studiengang_kz:$studiengang_kz, semester:$semester,$studiensemester_kurzbz: studiensemester_kurzbz </p>";	
	
	$senat=false;
	if (!empty($fachbereich_kurzbz) && mb_strtolower($fachbereich_kurzbz)==mb_strtolower('Senat'))
		$senat = true;	

	if (empty($studiensemester_kurzbz))
	{
   		if ($stsem_obj = new studiensemester())
		    	$studiensemester_kurzbz = $stsem_obj->getaktorNext();
 	}
	
	if (!is_null($studiengang_kz) && $studiengang_kz!='' && is_numeric($studiengang_kz))
	{
		if ($stg_obj = new studiengang($studiengang_kz))
		{		
			$short = $stg_obj->kuerzel;
			$short_long = $stg_obj->kurzbzlang;
			$stg_bezeichnung = $stg_obj->bezeichnung;
		}	
		else
		{
			$studiengang_kz= '';
			$semester = '';		
		}
	}

	
	
	function print_STGnews($studiengang_kz, $semester, $showall=false, $fachbereich_kurzbz)
	{
		$alter = ($showall?0:MAXNEWSALTER);
		$maxnews = ($showall?0:MAXNEWS);
		$news_obj = new news();
		if($news_obj->getnews($alter, $studiengang_kz, $semester, $showall, $fachbereich_kurzbz, $maxnews))
			$zaehler = print_news($news_obj);
		else
			echo '<p>'.$news_obj->errormsg.'</p>';
		if($zaehler==0)
		   echo '<p>Zur Zeit gibt es keine aktuellen News!</p>';
	}

	function print_FBnews($fachbereich_kurzbz, $datum)
	{

		$news_obj = new news();
		if($news_obj->getFBNews($fachbereich_kurzbz, $datum))
		{
			if(mb_strtolower($fachbereich_kurzbz)==mb_strtolower('Senat'))
				$open=false;
			else
				$open=true;
			$zaehler = print_news($news_obj, $open);
		}
		else
			echo '<p>'.$news_obj->errormsg.'</p>';
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
					<td width="60%" align="left">'.$row->betreff.'</td>
					<!--<td width="30%" align="center"></td>-->
					<td width="30%" align="right" style="display: '.($open?'none':'block').'" id="'.$zaehler.'Mehr" ><a href="#" class="Item" onclick="return show(\''.$zaehler.'\')">mehr &gt;&gt;</a></td>
					<td width="30%" align="right" style="display: '.($open?'block':'none').'" id="'.$zaehler.'Verfasser">'.$row->verfasser.' <span style="font-weight: normal">( '.$datum.' )</td>
				</tr>
			</table>
			</div>
			<div class="text" style="display: '.($open?'block':'none').';" id="'.$zaehler.'Text">
			'.mb_ereg_replace("../../skin","../../../skin","$row->text").'
			</div>
			';
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
		if(!check_lektor($user))
			die('<tr><td>Sie haben keine Berechtigung für diesen Bereich</td></tr>');

		echo '<tr><td>&nbsp;</td></tr>';
		
		echo '<tr><td>';
		//Datum aller Senatsbeschluesse holen
		$qry = "SELECT distinct datum FROM campus.tbl_news WHERE fachbereich_kurzbz='Senat'";
		if($result = $db->db_query($qry));
		{
			while($row = $db->db_fetch_object($result))
			{
				//Studiensemester des Datums ermitteln
				$stsem = getStudiensemesterFromDatum($row->datum);
				//Wenn dieses StSem noch nicht angezeigt wird, dann anzeigen
				if(!in_array($stsem, $stsemarr))
				{
					if($stsem_content!='')
						$stsem_content.=' - ';
					$stsem_content .="<a href='".$_SERVER['PHP_SELF']."?fachbereich_kurzbz=Senat&studiensemester_kurzbz=$stsem' class='Item'>";

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
					$datum_content.="<a href='".$_SERVER['PHP_SELF']."?fachbereich_kurzbz=Senat&studiensemester_kurzbz=$stsem&datum=$row->datum' class='Item'>";
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
		print_FBnews($fachbereich_kurzbz, $datum);
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
	  	<td class="tdvertical"><?php print_STGnews($studiengang_kz, (int)$semester, $showall, $fachbereich_kurzbz); ?><a href='<?php echo $_SERVER['REQUEST_URI']."&showall"; ?>' class='Item'>Archiv</a></td>

		<td>&nbsp;</td>
		<td class="tdvertical">
          <p>Studiengangsleiter:<br>
                <?php

                $stg_oe_obj = new studiengang($studiengang_kz);
                //Studiengangsleiter auslesen
				$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE campus.vw_mitarbeiter.aktiv and uid=(SELECT uid FROM public.tbl_benutzerfunktion WHERE oe_kurzbz='$stg_oe_obj->oe_kurzbz' AND funktion_kurzbz='stgl'  AND (datum_von<=now() OR datum_von is null) AND (datum_bis>=now() OR datum_bis is null) LIMIT 1)";
				if($result_course_leader = $db->db_query($qry))
				{
					$num_rows_course_leader = $db->db_num_rows($result_course_leader);
					if($num_rows_course_leader > 0)
					{
						$row_course_leader = $db->db_fetch_object($result_course_leader, 0);
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
						echo "<a href=\"mailto:$row_course_leader->uid@".DOMAIN."\" class=\"Item\">$row_course_leader->uid@".DOMAIN."</a>";
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
						$hauptnummer='';
						$qry_standort = "SELECT tbl_firma.telefon as nummer FROM public.tbl_standort, public.tbl_adresse, public.tbl_firma
								WHERE standort_kurzbz='".addslashes($row_course_leader->standort_kurzbz)."' AND
								tbl_adresse.adresse_id=tbl_standort.adresse_id AND
								tbl_adresse.firma_id=tbl_firma.firma_id";
						if($result_standort = $db->db_query($qry_standort))
						{
							if($row_standort = $db->db_fetch_object($result_standort))
							{
								$hauptnummer = $row_standort->nummer;
							}
						}
						
						echo $hauptnummer.' - '.$row_course_leader->telefonklappe;
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
				$sql_query = "SELECT * FROM campus.vw_mitarbeiter WHERE campus.vw_mitarbeiter.aktiv and uid=(SELECT uid FROM public.tbl_benutzerfunktion WHERE oe_kurzbz=(SELECT oe_kurzbz FROM public.tbl_studiengang WHERE studiengang_kz='$studiengang_kz' ) AND funktion_kurzbz='stglstv'  AND (datum_von<=now() OR datum_von is null) AND (datum_bis>=now() OR datum_bis is null) LIMIT 1)";

				if($result_course_leader_deputy = $db->db_query($sql_query))
				{
					$num_rows_course_leader_deputy = $db->db_num_rows($result_course_leader_deputy);

					if($num_rows_course_leader_deputy > 0)
					{
						$row_course_leader_deputy = $db->db_fetch_object($result_course_leader_deputy, 0);
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
						echo "<a href=\"mailto:$row_course_leader_deputy->uid@".DOMAIN."\" class=\"Item\">$row_course_leader_deputy->uid@".DOMAIN."</a>";
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
						$hauptnummer='';
						$qry_standort = "SELECT tbl_firma.telefon as nummer FROM public.tbl_standort, public.tbl_adresse, public.tbl_firma
								WHERE standort_kurzbz='".addslashes($row_course_leader_deputy->standort_kurzbz)."' AND
								tbl_adresse.adresse_id=tbl_standort.adresse_id AND
								tbl_adresse.firma_id=tbl_firma.firma_id";
						if($result_standort = $db->db_query($qry_standort))
						{
							if($row_standort = $db->db_fetch_object($result_standort))
							{
								$hauptnummer = $row_standort->nummer;
							}
						}

						echo $hauptnummer.' - '.$row_course_leader_deputy->telefonklappe;
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
				$stg_oe_obj = new studiengang($studiengang_kz);
				$sql_query = "SELECT distinct * FROM campus.vw_mitarbeiter WHERE campus.vw_mitarbeiter.aktiv and uid in (SELECT uid FROM public.tbl_benutzerfunktion WHERE oe_kurzbz='$stg_oe_obj->oe_kurzbz' AND funktion_kurzbz='ass' AND (datum_von<=now() OR datum_von is null) AND (datum_bis>=now() OR datum_bis is null))";
				
				if($result_course_secretary = $db->db_query($sql_query))
				{
					$num_rows_course_secretary = $db->db_num_rows($result_course_secretary);

					while($row_course_secretary = $db->db_fetch_object($result_course_secretary))
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
								echo "<a href=\"mailto:$row_course_secretary->uid@".DOMAIN."\" class=\"Item\">$row_course_secretary->uid@".DOMAIN."</a>";
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
								$hauptnummer='';
								$qry_standort = "SELECT tbl_firma.telefon as nummer FROM public.tbl_standort, public.tbl_adresse, public.tbl_firma
										WHERE standort_kurzbz='".addslashes($row_course_secretary->standort_kurzbz)."' AND
										tbl_adresse.adresse_id=tbl_standort.adresse_id AND
										tbl_adresse.firma_id=tbl_firma.firma_id";
								if($result_standort = $db->db_query($qry_standort))
								{
									if($row_standort = $db->db_fetch_object($result_standort))
									{
										$hauptnummer = $row_standort->nummer;
									}
								}

								echo $hauptnummer.' - '.$row_course_secretary->telefonklappe;
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
				
				$qry = "SELECT zusatzinfo_html FROM public.tbl_studiengang WHERE studiengang_kz='$studiengang_kz'";
				
				if($result = $db->db_query($qry))
				{
					if($row = $db->db_fetch_object($result))
					{
						echo $row->zusatzinfo_html;
					}
				}
				$stg_oe_obj = new studiengang($studiengang_kz);
				echo "<p>Studentenvertreter:</font><font face='Arial, Helvetica, sans-serif' size='2'><br>";
				$sql_query = "SELECT tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre, tbl_person.titelpost, tbl_benutzer.uid FROM public.tbl_person, public.tbl_benutzer,public.tbl_benutzerfunktion WHERE oe_kurzbz='$stg_oe_obj->oe_kurzbz' AND funktion_kurzbz='stdv' AND  tbl_person.aktiv and tbl_person.person_id=public.tbl_benutzer.person_id AND public.tbl_benutzer.aktiv AND tbl_benutzerfunktion.uid=tbl_benutzer.uid";
				if($result_course_stdv = $db->db_query($sql_query))
				{
					$num_rows_course_stdv = $db->db_num_rows($result_course_stdv);

					if($num_rows_course_stdv > 0)
					{
						while($row_stdv = $db->db_fetch_object($result_course_stdv))
						{
							echo "<a class='Item' href='mailto:".$row_stdv->uid."@".DOMAIN."'>$row_stdv->titelpre $row_stdv->vorname $row_stdv->nachname $row_stdv->titelpost</a><br>";
						}
					}
					else
					{
						echo "<b>Nicht vorhanden</b>";
					}
				}
				
				//Links nur Anzeigen wenn im Config angegeben
				if(CIS_EXT_MENU)
				{
?>            	<table border="0" width="100%" cellpadding="0" cellspacing="0">
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
					if(is_dir($path))
					{
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
					if(is_dir($path))
					{					
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
              <td class="tdwrap">
                <?php
					echo '<img src="../../../skin/images/seperator.gif">&nbsp;<a href="news://news.technikum-wien.at/'.strtolower($short_long).'" class="Item" target="_blank">Newsgroups</a>';

				?>
              </td>
            </tr>
          </table>
          <?php
				}
			?>
				
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