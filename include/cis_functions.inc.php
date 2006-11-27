<?php 
/**
 * @author: maximilian schremser
 * @date: 2004-10-24
 * globale funktionen für verschiedene anwendungen auf der cis.technikum-wien.at
 */ 

	function get_active_status($auth) {
		$course_id = $auth->stg_id;
		$term_id   = $auth->semester;

		$sql = "SELECT distinct verband, gruppe FROM tbl_student WHERE (studiengang_kz = $course_id) AND (semester = $term_id) ORDER BY verband,gruppe";
		$rs = new pgRS($sql);
		$count_verband = $rs->num;
		$verbands = $rs->arr;
		for ($rt = $count_verband-1; $rt >= 0; $rt--) {
			$verband = $verbands[$rt]["verband"] . $verbands[$rt]["gruppe"];
			if ($verbands[$rt]["verband"] == $auth->verband &&
			     $verbands[$rt]["gruppe"] == $auth->gruppe)
				return pow(2,$rt);
		}
		return 0;
	} // eof function get_active_status

	//get_lf_bezeichnung holt die Studiengang Bezeichnung und die Lehrfach
	// bezeichnung für das aktuelle Lehrfach.
	function get_lf_bezeichnung($course_id, $term_id, $lesson)
	{
		$sql = "SELECT lf.bezeichnung, kurzbzlang FROM lehre.tbl_lehrfachzuteilung lf
			JOIN tbl_studiengang USING(studiengang_kz) WHERE lf.studiengang_kz = $course_id 
			AND lf.lehrfachzuteilung_kurzbz = '$lesson' AND  lf.semester = $term_id LIMIT 1";
		$rs = new pgRS($sql);
		$ret[0] = $rs->arr[0]["kurzbzlang"];
		$ret[1] = $rs->arr[0]["bezeichnung"];
		
		return $ret;
	}

	// Die Freigabe für die Verbände für den Studienbrief berechnen
	// Um die Freigeschaltenen Studienbriefe zu berechnen.
	function sum($points = array()) {
		$p = 0;
		for ($i = 0; $i <  count($points); $i++) 
		{
			$p+= $points[$i];
		}
		return $p;
	} /* eof sum() */

	// erstellen des aktuellen studienjahres
	function get_studienjahr($i = -1) {
	  if ($i != -1) {
		$stj  = date("Y")+$i;
		$stj .= "/";
		$stj .=  sprintf("%02d", date("y")+$i+1);
	  } else if ( date("n") >= 7)
	    $stj = date("Y") .   "/" . date("y", mktime(0,0,0,1,1,date("Y")+1));
	  else 
	    $stj = date("Y")-1 . "/" . date("y");
	  return $stj;
	}

	function get_needed_term_id($term_id = 0, $stj = "") {
	  if ($stj == "") 
		  $stj = get_studienjahr();
	  $needed_term_id = $term_id + ((date("Y") - substr($stj,0,4))*2);

	  if ((date("n") >= 8 || date("n") < 3))
		if ($term_id%2==0)
			$needed_term_id--;
		else
			$needed_term_id++;

	  if ((date("n") < 8 || date("n") >= 3))
		if ($term_id%2!= 0)
		$needed_term_id--;
	  return $needed_term_id;
	}


	// berechnet die notwendige Semester_ID zum Anzeigen der 
	// Studenten im Kreuzerltool
	function get_needed_term_id2($term_id = 0, $stj = "") {
	  // im WS sollen noch Studenten vom letzten SS sichtbar sein
	  if ((date("n") >= 8 || date("n") < 2)) {
		if ($term_id%2==0)
			$term_id++;
	  }
	  // im SS sollen noch die Studenten vom WS sichtbar sein
	  else {
		if ($term_id%2==1) {
			$term_id++;
		}
	  }
	  return $term_id;
	}


	// zurückrechnen auf die studenten im jetzigen semester
	// studienpunkte sind nur für die eigene person sichtbar
	function get_old_studienjahr() {
		if (date("n") >= 7 && $term_id%2 == 1) {
			$studienjahr = date("Y") - intval(($auth->semester - $term_id)/2);
		}
		else if (date("n") >= 7 && $term_id%2 == 0) {
			$studienjahr = date("Y") - intval(($auth->semester - $term_id)/2)-1;
		}
		else if (date("n") < 7 && $term_id%2 == 1) {
			$studienjahr = date("Y") - intval(($auth->semester - $term_id)/2);
		}
		else {
			$studienjahr = date("Y") - intval(($auth->semester - $term_id)/2);
		}

		$studienjahr .= "/" . date("y", mktime(0,0,0,1,1,$studienjahr+1));
	}
	// write out the menu for kreuzerltool (campus/lehre/effort
	function write_menu($menu, $course_id, $lesson, $term_id, $module = "", $stj = 0) {
		if ($stj == 0)
			$stj = get_studienjahr();
		print "
<form method=\"post\" action=\"#page\">
<table border=0 cellpadding=1 cellspacing=0 width=600>
<tr>
<td>
<table border=0 width=100% cellpadding=0 cellspacing=0 width=600>
  <tr> 
     <td width=50%>
    <li type=square><a href=\"result_effort.php?course_id=$course_id&short=$lesson&term_id=$term_id&stj=$stj&module=$module#page\">Kreuzerlliste Statistik</a></li>
    </td>
     <td>
   <li type=square><a href=\"show_list.php?course_id=$course_id&short=$lesson&term_id=$term_id&stj=$stj&module=$module#page\">Anwesenheits- 
        und <br>&nbsp;&nbsp;&nbsp;&Uuml;bersichtstabelle</a></li></td></tr>
    <tr>
    <td>
   <li type=square><a href=\"create_stb.php?course_id=$course_id&short=$lesson&term_id=$term_id&stj=$stj&module=$module#page\">Kreuzerllisten anlegen <br>&nbsp;&nbsp;&nbsp;und verwalten</a></li></td>
   <td>
    <li type=square><a href=\"edit_list.php?course_id=$course_id&short=$lesson&term_id=$term_id&stj=$stj&module=$module#page\">Studentenpunkte <br>&nbsp;&nbsp;&nbsp;&nbsp;verwalten</a></li></td></tr>
   <tr>
   <td colspan=2>&nbsp;</td>
   </tr>
  <tr> 
    <td width=200 class=light valign=middle height=19>&nbsp;
    $menu</td>
    <td align=right class=light>Studienjahr: <select name=stj size=1 onChange=document.location.href='$PHP_SELF?course_id=$course_id&short=$lesson&term_id=$term_id&module=$module&stj='+document.forms[0].stj.options[document.forms[0].stj.options.selectedIndex].value;>";
    	$k = 0;
	// einfügen eines neuen Studienjahres ab 1.Juli bis 31.Dezember
	if (date("n") >= 7) {
		$d = date("Y") . "/" . date("y", mktime(0,0,0,1,1,date("Y")+1));
	
		echo "<option value='$d'";
		if ($d == $stj)
			echo "selected";
		echo ">$d</option>";
	}

	// vergangene Studienjahre seit 2003/04
	for ($i = date("y")+0, $j = date("Y")-1; $i >= 3, $j >= 2002; $i--,$j--)
	{
		$nextYear = $i;
		$year = $j;
		// add a spacing 0
		if ($nextYear < 10)
			$nextYear = "0$nextYear";
		echo "<option value=\"$year/$nextYear\"";
		echo ($stj == ($year."/".$nextYear))?"selected":"";
		echo ">$year/$nextYear</option>";
	}
	print <<<EOF
    </select></td>
  </tr>
  <tr>
   <td colspan=2>&nbsp;</td>
   </tr>
  </table></td></tr>	
</table></td>
</tr></table>
</form>

EOF;
	}



	/**
	  * @params inputString - the String to be formatted 
	  * @return the formatted String
	  */
	function format($inputString) {
		$inputString = stripslashes($inputString);
		return str_replace("\n", "<br>",$inputString);
	}


	function get_lectors($course_id, $term_id, $short) {
		if ($course_id && $term_id && $short) {
		$sql = "SELECT DISTINCT on (bezeichnung,nachname) bezeichnung, studiengang_kz, semester, vornamen, nachname, email, uid FROM lehre.tbl_lehrfachzuteilung JOIN  tbl_person ON uid = lektor_uid WHERE studiengang_kz='$course_id' AND semester='$term_id' AND lehrfachzuteilung_kurzbz = '$short' AND tbl_person.aktiv=TRUE  ORDER BY bezeichnung, nachname";
			//$sql_query = "SELECT DISTINCT ON(uid), vornamen, nachname, 
			//	titel FROM lehre.tbl_lehrfachzuteilung, tbl_person, 
			//tbl_mitarbeiter WHERE tbl_person.uid = 
			//lehre.tbl_lehrfachzuteilung.lektor_uid AND
			//lehre.tbl_lehrfachzuteilung.studiengang_kz='$course_id' 
			//AND lehre.tbl_lehrfachzuteilung.semester='$term_id' 
			//AND LOWER(lehre.tbl_lehrfachzuteilung.lehrfachzuteilung_kurzbz)='$short' 
			//AND lehre.tbl_lehrfachzuteilung.aktiv = true
			//ORDER BY nachname";

			$result_lectors = new pgRS($sql);
		}
		$num_rows_lectors = $result_lectors->num;
		$row_email = array();
		$row_vornamen = array();
		$row_nachname = array();
		$row_titel    = array();
		for($i = 0; $i < $num_rows_lectors; $i++)
		{
			$row_email[] = $result_lectors->arr[$i]["emailtw"];
			$row_vornamen[] = $result_lectors->arr[$i]["vornamen"];
			$row_nachname[] = $result_lectors->arr[$i]["nachname"];
			$row_titel[] = $result_lectors->arr[$i]["titel"];
			
		}
		$ret = "";
      		for ($i = 0; $i < count($row_email); $i++)
      		{
			$ret .= "$row_titel[$i] $row_vornamen[$i] $row_nachname[$i]";
			if ($i < count($row_email)-1)
				$ret .= ", ";
		}
		return $ret;
	}	

	function get_person($bezeichnung, $course_id) {
		$sql = "SELECT titel, vornamen, nachname, bezeichnung FROM tbl_funktion, public.tbl_person JOIN tbl_personfunktion ON (tbl_personfunktion.uid = tbl_person.uid) WHERE tbl_personfunktion.funktion_kurzbz = tbl_funktion.funktion_kurzbz AND bezeichnung='$bezeichnung' AND tbl_personfunktion.studiengang_kz='$course_id'";
	      $rs = new pgRS($sql);
	      $arr = $rs->arr[0];
	      $ret = $arr["titel"]." " . $arr["vornamen"]." " . $arr["nachname"];
	      return $ret;
	}
	function create_version($user, $info_data) {
			$id = 0;
			$info_id = 0;
			$version_id = 0;
			
			$ects_points = $info_data["ects_credits"];
			$learning_language = $info_data["sprache"];
			$wochenstunden  = $info_data["hours"];
			$course_id =  $info_data["course_id"];
			$term_id = $info_data["term_id"];
			$short = $info_data["short"];
			$studienjahr = $info_data["studienjahr"];
			$fachbereich = $info_data["fachbereich"];
			$fachbereichsleiter = $info_data["fachbereichsleiter"];
			$fachbereichskoordinator = $info_data["fachbereichskoordinator"];
			$lehrender = $info_data["lehrender"];
			
			$sql = "INSERT INTO lv_infos (ects_credits,
			sprache, hours, studienjahr, fachbereich, 
			fachbereichsleiter, fachbereichskoordinator, lehrender) VALUES 
				($ects_points, $learning_language, 
				 $wochenstunden, '$studienjahr',
				 '$fachbereich', '$fachbereichsleiter',
				 '$fachbereichskoordinator', '$lehrender')";
			$rs = new myRS($sql);
			$info_id  = $rs->iid;
			if ($info_id) {
				$sql = "SELECT max(version) as version FROM lv_versions 
					WHERE studiengang_kz = $course_id AND
					short = '$short' AND term_id = $term_id";
				$rs = new myRS($sql);
				$max_version = $rs->arr[0]["version"];
				$sql ="INSERT INTO lv_versions (studiengang_kz, term_id, 
					short, lector_uid, version, 
					status, info_id, date_created, date_modified) 
					VALUES ($course_id, $term_id, '$short', 
					'$user', " . ($max_version +1).", 0,$info_id," .time() .", " . time().")";
				$rs  = new myRS($sql);
				$version_id = $rs->iid;
			}
			return array($version_id, $info_id);
		} // eof create_version

		function get_version($version_id) {
			$sql = "SELECT * FROM lv_versions v, lv_infos i 
				WHERE v.id = $version_id AND v.info_id = i.id";
			$rs = new myRS($sql);
		return array( 
			      $rs->arr[0]["ects_credits"]/10,
			      $rs->arr[0]["sprache"],
			      $rs->arr[0]["hours"]/10,
			      $rs->arr[0]["info_id"],
			      $rs->arr[0]["content_de_id"],
			      $rs->arr[0]["content_en_id"],
			      $rs->arr[0]["date_created"],
			      $rs->arr[0]["date_modified"],
			      $rs->arr[0]["studienjahr"],
			      $rs->arr[0]["fachbereich"],
			      $rs->arr[0]["fachbereichsleiter"],
			      $rs->arr[0]["fachbereichskoordinator"],
			      $rs->arr[0]["lehrender"],
			      $rs->arr[0]["studiengang_kz"],
			      $rs->arr[0]["term_id"],
			      $rs->arr[0]["short"]
			    );

	} // eof get_version


	// updates the record with info_id in lv_infos
	function update_version($user, $info_data) {
		$id = 0;
		$version_id = 0;
		$ects_points = $info_data["ects_credits"];
		$learning_language = $info_data["sprache"];
		$wochenstunden  = $info_data["hours"];
		$info_id =  $info_data["info_id"];
		$studienjahr =  $info_data["studienjahr"];
		$fachbereich =  $info_data["fachbereich"];
		$fachbereichsleiter =  $info_data["fachbereichsleiter"];
		$fachbereichskoordinator =  $info_data["fachbereichskoordinator"];
		$lehrender =  $info_data["lehrender"];
		$version_id =  $info_data["version_id"];
		
		$sql = "UPDATE lv_infos SET ects_credits = $ects_points, 
			sprache = $learning_language, 
			hours = $wochenstunden, 
			studienjahr = '$studienjahr',
			fachbereich = '$fachbereich',
			fachbereichsleiter = '$fachbereichsleiter',
			fachbereichskoordinator = '$fachbereichskoordinator',
			lehrender = '$lehrender'
			WHERE id = $info_id";
		$rs = new myRS($sql);
		$sql = "UPDATE lv_versions SET date_modified = " .time() .
			" WHERE id = $version_id";
		$rs = new myRS($sql);
	} // eof update_version


	// saves a version to be displayed in LV infos freigeben
	function save_version($version_id) {
		$sql = "UPDATE lv_versions SET status = 1 WHERE id = $version_id";
		$rs = new myRS($sql);
	}

	// inserts a record into lv_content_$lang and
	// update lv_versions.content_$lang_id
	function create_content($lang, $version_id, $info_data) {
		$arr_values = array_values($info_data);
		$keys = join(", ", array_keys($info_data));
		
		for ($i = 0; $i < count($arr_values)-1;$i++) {
			$values .= "'" . $arr_values[$i] . "', ";
		}
		$values .= "'" . $arr_values[count($arr_values)-1] ."'";
		$sql = "INSERT INTO lv_content_$lang ($keys) VALUES ($values)";
		$rs = new myRS($sql);
		$content_id = $rs->iid;
		
		$sql = "UPDATE lv_versions SET content_" . $lang . "_id = 
			$content_id WHERE id = $version_id";
		$rs = new myRS($sql);
		
		return $content_id;
	} // eof create_content

	// update_content updates the table lv_content_$lang
	function update_content($lang, $id, $info_data) {
		$sql = "UPDATE lv_content_$lang SET ";
		
		$keys = array_keys($info_data);
		$values = array_values($info_data);
		
		for ($i = 0; $i < count($values); $i++) {
			$sql .= $keys[$i] ." = '" . $values[$i] . "'";
			if ($i < count($values)-1) $sql .= ", ";
		}
		$sql .= " WHERE id = $id";
		$rs = new myRS($sql);
	} // eof update_content
	function get_content($lang, $content_id, $fields) {
		$sql = "SELECT " . join (", ", $fields) . " FROM 
			lv_content_$lang WHERE id = $content_id";
		$rs = new myRS($sql);
		return $rs->arr[0];
	} // eof get_content

	function write_log($lektor_uid, $action) {
		$sql = "INSERT INTO lv_log (lektor_uid, action, datum)" .
			" VALUES ('$lektor_uid', '$action', " . time() .")";
		$rs = new myRS($sql);
		return 1;
	}


/*****************************************************************************
* 	function write_ects_menu($menu = 0, $course_id, $term_id, $short, $lang) {
* 
* 	if ($menu == 1) $menu_name = "LV-Infos anzeigen";
* 
* 	print <<<EOF
* 		<form method="get" action="view_$lang.php?menu=$menu" name="foo">
<input type="hidden" name="course_id" value="$course_id" />
<input type="hidden" name="term_id" value="$term_id" />
<input type="hidden" name="menu" value="$menu" />
<table border="0" width="100%" cellpadding="0" cellspacing="0">
<tr> 
<td width=20>&nbsp;</td>
<td width="357"><li><font size="2" face="Arial, Helvetica, sans-serif"><a class=Item2 href="view_$lang.php?menu=1&course_id=$course_id&short=$short&term_id=$term_id">LV-Infos - anzeigen</a>

</font></td>
<td width="396"><li><font size="2" face="Arial, Helvetica, sans-serif"><a class=Item2 href="edit.php?menu=2&course_id=$course_id&short=$short&term_id=$term_id">LV-Infos - erstellen / bearbeiten</a>

</font></td>
<td width="280"><li><font size="2" face="Arial, Helvetica, sans-serif"><a class=Item2 href="publish.php?course_id=$course_id&short=$short&term_id=$term_id">LV-Infos - freigeben</a></td>
<td width="280"><li><font size="2" face="Arial, Helvetica, sans-serif"><a class=Item2 href="index.php?menu=5&course_id=$course_id&short=$short&term_id=$term_id">LV-Infos - Feedback</a></td>
</tr>
<tr> 
<td width=20>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
</table>
</td> 
</tr>
EOF;
	
		if ($menu_name) { 
			print <<<EOF
<tr> 
<td width="100%" class="inner"><a name="page"></a>

<table border=0 cellpadding=0 cellspacing=0 width="100%">
<tr>
<td width=400><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"> 
&nbsp;$menu_name
</font></td>
<td width="50%" align="center">
Lehrfach: <select name="short" size="1" onChange="document.foo.submit();" >
<option value="">Bitte Lehrfach auswählen</option>
EOF;
	if ($course_id && $term_id) {
		$sql = "SELECT DISTINCT bezeichnung, lehrfachzuteilung_kurzbz FROM lehre.tbl_lehrfachzuteilung WHERE studiengang_kz=$course_id AND semester=$term_id ORDER BY bezeichnung";
		$rs = new pgRS($sql);
		for ($i = 0; $i < $rs->num; $i++) { 
			echo "<option value='" . $rs->arr[$i]["lehrfachzuteilung_kurzbz"] .
			 "' ";
			if ($short==$rs->arr[$i]["lehrfachzuteilung_kurzbz"])
				echo " selected";
			echo ">";
			echo $rs->arr[$i]["bezeichnung"];
			echo "</option>";
		}

	}
	echo '</select></td>
<td align="right" class="inner" valign="middle" width=300>';
	if ($menu != 2) {
		if ($lang == "" || $lang!="en") {
* 		   echo "[Deutsch]";	
* 		} else { 
* 			echo "<a class=Item2 href='view_de.php?course_id=$course_id&short=$short&term_id=$term_id&menu=$menu&lang=de'>[Deutsch]</a>";
* 		} 
* 		if ($lang == "en") {
* 		   echo "[English]";
* 		} else { 
* 		   echo "<a class=Item2 href='view_en.php?course_id=$course_id&short=$short&term_id=$term_id&menu=$menu&lang=en'>[English]</a>";
*  		}
* 	} // eof menu=2
* 	echo '</td>
* </tr></table></td>
* </tr>';
* 	}
* 	echo '<tr> 
* <td width="100%" align="center">&nbsp;</td></tr></table></form>';
* 	}
***************************************************************************/


	// write a menu for a quicker navigation in the ects - course contents program 
	function write_ects_quick_menu($course_id, $term_id, $short, $p, $version_id = 0, $info_id = 0, $content_de_id = 0, $content_en_id = 0) {
	     if ($p > 0) {
		print '<table style="border:1px solid black"><tr><th>Schnellnavigation</th></tr><tr><td valign=top>
<!-- MENU FUER NAVIGATION //-->';
		  static $headlines = array("", "Allgemeine Infos", "Inhalte DE", "Prüfung DE", "Inhalte EN", "Prüfung EN", "Beenden");
		for ($i = 1; $i < count($headlines); $i++) {
			echo "
		$i <a href=\"edit_$i.php?menu=2&course_id=$course_id&term_id=$term_id&short=$short&version_id=$version_id&info_id=$info_id&content_de_id=$content_de_id&content_en_id=$content_en_id&quick=1\">";
			if ($i==$p) echo "<b>";
			echo $headlines[$i];
			if ($i==$p) echo "</b>";
			echo "</a><br>";
		}
	     }
	}



	// this function returns the dictionary entry for the keyword in the 
	// given language
	function dict($lang = "de", $key = "") {
		static $dictionary = array (
				"back" => array("de" => "Zurück", "en" => "Back"),
				"LVInfos" => array("de" => "LV - Informationen", "en" => "Course Description"),
				"allCourses" => array("de" => "Alle Studiengänge", "en" => "All Courses"),
				"availCourses" => array("de" => "Vorhandene Lehrfächer in ", "en" => "Available Courses  in "),
				"term" => array("de" => "Semester", "en" => "term"),
				"noinfo" => array("de" => "Keine Informationenen verfügbar", "en" => "No course information available")

		);

		return $dictionary[$key][$lang];
	}
?>
