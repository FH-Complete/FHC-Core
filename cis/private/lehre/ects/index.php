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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/* @author Andres Oesterreicher
   @date 20.10.2005
   @brief Formular zum eintragen der ECTS Information auf Deutsch und Englisch
          Die Informationen werden in der Tabelle tbl_lvinfo gespeichert.

   @edit	08-11-2006 Versionierung entfernt: Studiensemester=WS2007
   			02-01-2007 Umstellung auf die neue DB
*/
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/basis_db.class.php');	
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/lvinfo.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/safehtml/safehtml.class.php');

$sprache1 = getSprache(); 
$p=new phrasen($sprache1);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
   
   $output = '';
   $errormsg = '';
   $okmsg='';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title><?php echo $p->t('courseInformation/ectsInformation')?></title>

<script language="JavaScript" type="text/javascript">
<!--
   function save()
   {
   		window.document.editFrm.status.value="save";
   		window.document.editFrm.action="<?php echo $_SERVER['PHP_SELF']; ?>";
   		window.document.editFrm.target="_self";
   		window.document.editFrm.submit();
   }
-->
</script>
</head>
<body style="padding: 10px">
<?php
	function Cut($string)
	{
		if(strlen($string)>50)
			return substr($string,0,47)."...";
		else
			return $string;
	}

	$user = get_uid();
    //Berechtigung ueberpruefen
    if(!check_lektor($user))
    	die("<br><center>".$p->t('global/keineBerechtigungFuerDieseSeite')."</center>");
	
    if(isset($_GET['lvid']))
    	$lv=$_GET['lvid'];

	//Variablenuebernahme
	if(isset($_POST['lv']))  //LehrveranstaltungsID
		$lv = $_POST['lv'];

	if(isset($_GET['lvid']))
	{
		$lv_obj = new lehrveranstaltung();
		$lv_obj->load($lv);

		if(!isset($stg))
			$stg = $lv_obj->studiengang_kz;
		if(!isset($sem))
			$sem = $lv_obj->semester;
	}

	if(!isset($stg) && isset($_POST['stg']))
		$stg = $_POST['stg'];
	if(!isset($sem) && isset($_POST['sem']))
		$sem = $_POST['sem'];	
		
	
	if(isset($_POST['changed'])) //Gibt an welches der Auswahlfelder geaendert wurde
		$changed = $_POST['changed'];

	if(isset($_POST['status']))
		$status = $_POST['status'];

//    if(isset($_POST["freigeben"])) //Wird auf 'ja' gesetzt wenn gleich freigegebenwerden soll nach dem Speichern
//       $freigeben = $_POST["freigeben"];

	if(isset($_POST['sprache'])) //Sprache fuer dieses Lehrfach
		$sprache = $_POST['sprache'];

	//Variablen fuer das Formular
	$lehrziele_de = (isset($_POST['lehrziele_de'])?$_POST['lehrziele_de']:'');
	$lehrinhalte_de = (isset($_POST['lehrinhalte_de'])?$_POST['lehrinhalte_de']:'');
	$voraussetzungen_de = (isset($_POST['voraussetzungen_de'])?$_POST['voraussetzungen_de']:'');
	$unterlagen_de = (isset($_POST['unterlagen_de'])?$_POST['unterlagen_de']:'');
	$pruefungsordnung_de = (isset($_POST['pruefungsordnung_de'])?$_POST['pruefungsordnung_de']:'');
	$anmerkungen_de = (isset($_POST['anmerkungen_de'])?$_POST['anmerkungen_de']:'');
	$kurzbeschreibung_de = (isset($_POST['kurzbeschreibung_de'])?$_POST['kurzbeschreibung_de']:'');
	$anwesenheit_de = (isset($_POST['anwesenheit_de'])?$_POST['anwesenheit_de']:'');
	$freig_de = (isset($_POST['freig_de'])?($_POST['freig_de']=='on'?true:false):'');
	$methodik_de = (isset($_POST['methodik_de'])?$_POST['methodik_de']:'');
	//$titel_de = (isset($_POST['titel_de'])?$_POST['titel_de']:'');

	$parser = new SafeHTML();
 	$lehrziele_de = $parser->parse($lehrziele_de);
 	$parser = new SafeHTML();
 	$lehrinhalte_de = $parser->parse($lehrinhalte_de);
 	$parser = new SafeHTML();
	$voraussetzungen_de = $parser->parse($voraussetzungen_de);
	$parser = new SafeHTML();
	$unterlagen_de = $parser->parse($unterlagen_de);
	$parser = new SafeHTML();
	$pruefungsordnung_de = $parser->parse($pruefungsordnung_de);
	$parser = new SafeHTML();
	$anmerkungen_de = $parser->parse($anmerkungen_de);
	$parser = new SafeHTML();
	$kurzbeschreibung_de = $parser->parse($kurzbeschreibung_de);
	$parser = new SafeHTML();
	$anwesenheit_de = $parser->parse($anwesenheit_de);
	$parser = new SafeHTML();
	$freig_de = $parser->parse($freig_de);
	$parser = new SafeHTML();
	$methodik_de = $parser->parse($methodik_de);
 	
	$lehrziele_en = (isset($_POST['lehrziele_en'])?$_POST['lehrziele_en']:'');
	$lehrinhalte_en = (isset($_POST['lehrinhalte_en'])?$_POST['lehrinhalte_en']:'');
	$voraussetzungen_en = (isset($_POST['voraussetzungen_en'])?$_POST['voraussetzungen_en']:'');
	$unterlagen_en = (isset($_POST['unterlagen_en'])?$_POST['unterlagen_en']:'');
	$pruefungsordnung_en = (isset($_POST['pruefungsordnung_en'])?$_POST['pruefungsordnung_en']:'');
	$anmerkungen_en = (isset($_POST['anmerkungen_en'])?$_POST['anmerkungen_en']:'');
	$kurzbeschreibung_en = (isset($_POST['kurzbeschreibung_en'])?$_POST['kurzbeschreibung_en']:'');
	$anwesenheit_en = (isset($_POST['anwesenheit_en'])?$_POST['anwesenheit_en']:'');
	$freig_en = (isset($_POST['freig_en'])?($_POST['freig_en']=='on'?true:false):'');
	$methodik_en = (isset($_POST['methodik_en'])?$_POST['methodik_en']:'');
	//$titel_en = (isset($_POST['titel_en'])?$_POST['titel_en']:'');

	$parser = new SafeHTML();
	$lehrziele_en = $parser->parse($lehrziele_en);
	$parser = new SafeHTML();
 	$lehrinhalte_en = $parser->parse($lehrinhalte_en);
 	$parser = new SafeHTML();
	$voraussetzungen_en = $parser->parse($voraussetzungen_en);
	$parser = new SafeHTML();
	$unterlagen_en = $parser->parse($unterlagen_en);
	$parser = new SafeHTML();
	$pruefungsordnung_en = $parser->parse($pruefungsordnung_en);
	$parser = new SafeHTML();
	$anmerkungen_en = $parser->parse($anmerkungen_en);
	$parser = new SafeHTML();
	$kurzbeschreibung_en = $parser->parse($kurzbeschreibung_en);
	$parser = new SafeHTML();
	$anwesenheit_en = $parser->parse($anwesenheit_en);
	$parser = new SafeHTML();
	$freig_en = $parser->parse($freig_en);
	$parser = new SafeHTML();
	$methodik_en = $parser->parse($methodik_en);
	
	/* WriteLog($qry,$uid)
	* @brief Schreib die Querys im format: uid - datum - qry ins LogFile
	* @param $qry Query anweisung
	*        $uid Username
	* @return true wenn ok false wenn fehler beim oeffnen
	*/
	function WriteLog($qry,$uid)
	{

		if($fp=fopen(LOG_PATH.'lvinfo.log',"a"))
		{
			fwrite($fp,"\n");
			fwrite($fp,$uid." ". date("d.m.Y - H:i:s") . " ". $qry);
			fclose($fp);
			return true;
		}
		else
			return false;
	}

	if(isset($status))
	{

		if($status=='save') // Beim druecken auf "Speichern"
		{
			//Speichert die aenderungen in der Datenbank (de und en)
			$lv_obj_sav= new lvinfo();
			$save_error=false;
			$save_log_error=false;
			//Deutsch
			$lv_obj_sav->lehrziele=mb_eregi_replace("\r\n", "<br>", $lehrziele_de);
			$lv_obj_sav->lehrinhalte=mb_eregi_replace("\r\n", "<br>", $lehrinhalte_de);
			$lv_obj_sav->voraussetzungen=mb_eregi_replace("\r\n", "<br>", $voraussetzungen_de);
			$lv_obj_sav->unterlagen=mb_eregi_replace("\r\n", "<br>", $unterlagen_de);
			$lv_obj_sav->pruefungsordnung=mb_eregi_replace("\r\n", "<br>", $pruefungsordnung_de);
			$lv_obj_sav->anmerkungen=mb_eregi_replace("\r\n", "<br>", $anmerkungen_de);
			$lv_obj_sav->kurzbeschreibung=mb_eregi_replace("\r\n", "<br>", $kurzbeschreibung_de);
			$lv_obj_sav->anwesenheit=mb_eregi_replace("\r\n", "<br>", $anwesenheit_de);
			
			$lv_obj_sav->genehmigt = ($freig_de?true:false);
			$lv_obj_sav->updateamum=date('Y-m-d H:i:s');
			$lv_obj_sav->updatevon=$user;
			$lv_obj_sav->aktiv=true;
			$lv_obj_sav->sprache=ATTR_SPRACHE_DE;
			$lv_obj_sav->lehrveranstaltung_id=$lv;
			$lv_obj_sav->methodik = mb_eregi_replace("\r\n", "<br>", $methodik_de);
			//$lv_obj_sav->titel = mb_eregi_replace("\r\n", "<br>", $titel_de);

			$lv_obj1 = new lvinfo();
			$vorhanden=$lv_obj1->exists($lv, ATTR_SPRACHE_DE);

			if(!$vorhanden)
   	   	   		$lv_obj_sav->new=true;
			else
				$lv_obj_sav->new=false;

			if(!$lv_obj_sav->save())
				$save_error=true;
			else
				if(!WriteLog($lv_obj_sav->lastqry,$user))
					$save_log_error=true;

			//Englisch
			$lv_obj_sav->lehrziele=mb_eregi_replace("\r\n", "<br>", $lehrziele_en);
			$lv_obj_sav->lehrinhalte=mb_eregi_replace("\r\n", "<br>", $lehrinhalte_en);
			$lv_obj_sav->voraussetzungen=mb_eregi_replace("\r\n", "<br>", $voraussetzungen_en);
			$lv_obj_sav->unterlagen=mb_eregi_replace("\r\n", "<br>", $unterlagen_en);
			$lv_obj_sav->pruefungsordnung=mb_eregi_replace("\r\n", "<br>", $pruefungsordnung_en);
			$lv_obj_sav->anmerkungen=mb_eregi_replace("\r\n", "<br>", $anmerkungen_en);
			$lv_obj_sav->kurzbeschreibung=mb_eregi_replace("\r\n", "<br>", $kurzbeschreibung_en);
			$lv_obj_sav->anwesenheit=mb_eregi_replace("\r\n", "<br>", $anwesenheit_en);
			$lv_obj_sav->genehmigt = ($freig_en?true:false);
			$lv_obj_sav->aktiv=true;
			$lv_obj_sav->updateamum=date('Y-m-d H:i:s');
			$lv_obj_sav->updatevon=$user;
			$lv_obj_sav->sprache=ATTR_SPRACHE_EN;
			$lv_obj_sav->lehrveranstaltung_id=$lv;
			$lv_obj_sav->methodik = mb_eregi_replace("\r\n", "<br>", $methodik_en);
			//$lv_obj_sav->titel = mb_eregi_replace("\r\n", "<br>", $titel_en);

			$lv_obj1 = new lvinfo();
			$vorhanden = $lv_obj1->exists($lv, ATTR_SPRACHE_EN);

			if(!$vorhanden)
				$lv_obj_sav->new=true;
			else
				$lv_obj_sav->new=false;

			if(!$lv_obj_sav->save())
				$save_error=true;
			else
				if(!WriteLog($lv_obj_sav->lastqry,$user))
					$save_log_error=true;

			if($save_error)
				$errormsg.= $p->t('courseInformation/achtungFehlerBeimSpeichern');
			else
				$okmsg.= $p->t('global/erfolgreichgespeichert');
				
			if($save_log_error)
				$errormsg.= $p->t('courseInformation/fehlerLogFile');
		}
	}

	$output .= "\n";
	$output .= "<table class='tabcontent'><tr>";
	$output .= "<td width='85%'>";
	$output .= "<form action='".$_SERVER['PHP_SELF']."' name='auswahlFrm' method='POST'>";
	$stg_obj = new studiengang();

	//Anzeigen des DropDown Menues mit Stg
	if($stg_obj->getAll('typ, kurzbz'))
	{
		$output .= $p->t('global/studiengang')." <SELECT name='stg' onChange='javascript:window.document.auswahlFrm.changed.value=\"stg\";window.document.auswahlFrm.submit();'>";

		$stgselected=false;
		unset($firststg);
		//DropDown Menue mit den Stg fuellen
		foreach($stg_obj->result as $elem)
		{
			$lv_help_obj = new lehrveranstaltung();
			$lv_help_obj->load_lva($elem->studiengang_kz, null,null,true);

			if(count($lv_help_obj->lehrveranstaltungen)>0)
			{
				if(!isset($firststg))
					$firststg = $elem->studiengang_kz;

				if(!isset($stg))
					$stg=$elem->studiengang_kz;

				if($elem->studiengang_kz == $stg)
				{
					$output .= "<option value='$elem->studiengang_kz' selected>$elem->kuerzel</option>";
					$stgselected=true;
				}
				else
					$output .= "<option value='$elem->studiengang_kz'>$elem->kuerzel</option>";
			}
		}
		$output .= "</SELECT>";
		if(!$stgselected)
			$stg=$firststg;
	}
	else
	{
		$errormsg .= "$stg_obj->errormsg";
	}

	//Anzeigen des DropDown Menues mit Semester
	if(isset($changed) && $changed=='stg')
	{
		unset($sem);
		unset($lvid);
	}

	if($stg_obj->load($stg))
	{
		$output .= $p->t('global/semester')." <SELECT name='sem' onChange='javascript:window.document.auswahlFrm.changed.value=\"sem\";window.document.auswahlFrm.submit();'>";

		unset($firstsem);
		$semselected=false;

		for($i=1;$i<=$stg_obj->max_semester;$i++)
		{
			$lv_help_obj = new lehrveranstaltung();
			$lv_help_obj->load_lva($stg, $i, null,true);

			if(count($lv_help_obj->lehrveranstaltungen)>0)
			{

				if(!isset($firstsem))
					$firstsem=$i;

				if(!isset($sem) || (isset($sem) && $sem>$stg_obj->max_semester))
				{
					$sem = $i;
				}

				if($i == $sem)
				{
					$output .= "<option value='$i' selected>$i</option>";
					$semselected=true;
				}
				else
					$output .= "<option value='$i'>$i</option>";
			}
		}
		$output .= "</SELECT>";

		if(!$semselected)
			$sem=$firstsem;
	}
	else
		$errormsg .= "$stg_obj->errormsg";

	//Anzeigen des DropDown Menues mit Lehrveranstaltungen
	$lv_obj = new lehrveranstaltung();
	if($lv_obj->load_lva($stg,$sem,null,true))
	{
       $output .= $p->t('global/lehrveranstaltung')." <SELECT name='lv' onChange='javascript:window.document.auswahlFrm.changed.value=\"lv\";window.document.auswahlFrm.submit();'>";
       $vorhanden=false;
       unset($firstlv);

   	   foreach($lv_obj->lehrveranstaltungen as $erg)
   	   {
   	   	  if(!isset($lv) || (isset($changed) && $changed=='sem') || (isset($changed) && $changed=='stg'))
   	   	  {
   	   	     $lv = $erg->lehrveranstaltung_id;
   	   	     $changed='';
   	   	  }
   	   	  if(!isset($firstlv))
   	   	     $firstlv=$erg->lehrveranstaltung_id;

   	   	  if($lv == $erg->lehrveranstaltung_id)
   	   	  {
   	   	     $output .= "<option value='$erg->lehrveranstaltung_id' selected>".Cut($erg->bezeichnung)."</option>";
   	   	     $vorhanden=true;
   	   	  }
   	   	  else
   	   	     $output .= "<option value='$erg->lehrveranstaltung_id'>".Cut($erg->bezeichnung)."</option>";
   	   }
   	   $output .= "</SELECT>";
   	   if(!$vorhanden)
   	       $lv=$firstlv;
	}
	else
	{
		$errormsg .= "$lv_obj->errormsg";
	}

	$output .= "<input type='hidden' name='changed' value=''>";
	$output .= "<input type='Submit' value='".$p->t('global/anzeigen')."'>";
	$output .= "</form>";
	$output .= "</td>";

	$output .= "<td>";
	//Menue ausgeben
	$output .= "\n";
	$output .= "<ul>";
	$output .= "<li>&nbsp;<a class='Item' href='index.php?stg=$stg&sem=$sem&lv=$lv'><font size='3'>".$p->t('global/bearbeiten')."</font></a></li>";
	$output .= "<li>&nbsp;<a class='Item' href='freigabe.php?stg=$stg&sem=$sem&lv=$lv'><font size='3'>".$p->t('courseInformation/freigabe')."</font></a></li>";
	$output .= "<li>&nbsp;<a class='Item' href='beispiele.php'><font size='3'>".$p->t('global/beispiele')."</font></a></li>";
	$output .= "<li>&nbsp;<a class='Item' href='terminologie.php'><font size='3'>".$p->t('courseInformation/terminologie')."</font></a></li>";
	$output .= "</ul>";
	$output .= "</td></tr></table>";

	$stg_obj->load($stg);

	//Kopfzeile hinausschreiben und $output ausgeben
	echo "<h1>&nbsp;".$p->t('courseInformation/lvInfoSemester',array($stg_obj->kuerzel, $sem))."</h1>";
	echo $output;

	if(isset($lv) && isset($stg) && isset($sem)) // Wenn oben alles Ausgewaehlt wurde
	{
		//Anzeige des Formulares
		$stg_obj1 = new studiengang();
		$stg_obj1->load($stg);

		if(isset($errormsg) && $okmsg!='')
			echo '<span class="error">'.$errormsg.'</span><br>';
		if(isset($okmsg) && $okmsg!='')
			echo '<span class="ok">'.$okmsg.'</span><br>';

		$lv_obj_en = new lvinfo();
		$lv_obj_de = new lvinfo();

		if($lv_obj_en->load($lv, ATTR_SPRACHE_EN))
			$lv_en=$lv_obj_en;

		if($lv_obj_de->load($lv, ATTR_SPRACHE_DE))
			$lv_de=$lv_obj_de;

		if(!isset($_POST['lehrziele_de']) && isset($lv_de))
		{
			$lehrziele_de = $lv_de->lehrziele;
			$lehrinhalte_de = $lv_de->lehrinhalte;
			$voraussetzungen_de = $lv_de->voraussetzungen;
			$unterlagen_de = $lv_de->unterlagen;
			$pruefungsordnung_de = $lv_de->pruefungsordnung;
			$anmerkungen_de = $lv_de->anmerkungen;
			$kurzbeschreibung_de = $lv_de->kurzbeschreibung;
			$anwesenheit_de = $lv_de->anwesenheit;
			$freig_de = $lv_de->genehmigt;
			$titel_de = $lv_de->titel;
			$methodik_de = $lv_de->methodik;
		}

		if(!isset($_POST['lehrziele_en']) && isset($lv_en))
		{
			$lehrziele_en = $lv_en->lehrziele;
			$lehrinhalte_en = $lv_en->lehrinhalte;
			$voraussetzungen_en = $lv_en->voraussetzungen;
			$unterlagen_en = $lv_en->unterlagen;
			$pruefungsordnung_en = $lv_en->pruefungsordnung;
			$anmerkungen_en = $lv_en->anmerkungen;
			$kurzbeschreibung_en = $lv_en->kurzbeschreibung;
			$anwesenheit_en = $lv_en->anwesenheit;
			$freig_en = $lv_en->genehmigt;
			$titel_en = $lv_en->titel;
			$methodik_en = $lv_en->methodik;
		}

		$lv_obj = new lehrveranstaltung();
		$lv_obj->load($lv);
		echo "<Form name='editFrm' action='".$_SERVER['PHP_SELF']."' method='POST'>";

		echo "<table class='tabcontent'>";
		echo "<tr><td><b>".$p->t('courseInformation/ectsCredits')."</b></td><td>&nbsp;</td><td width='400'>".($lv_obj->ects!=''?number_format($lv_obj->ects,1,'.',''):'')."</td><td width='100%'></td></tr>";

		$stsem_obj = new studiensemester();
		$stsem = $stsem_obj->getaktorNext();
		//Namen der Lehrenden Auslesen
		$qry = "SELECT 
					* 
				FROM 
					campus.vw_mitarbeiter, lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit 
				WHERE 
					lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." 
					AND tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id 
					AND studiensemester_kurzbz=(SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) WHERE lehrveranstaltung_id=".$db->db_add_param($lv)." ORDER BY ende DESC LIMIT 1) 
					AND mitarbeiter_uid=uid";

		echo "<tr><td class='tdvertical' nowrap><b>".$p->t('courseInformation/lehrendeLautLehrauftrag')."</b></td><td>&nbsp;</td><td nowrap>";
		$helparray = array();
		if($result=$db->db_query($qry))
		{
			while($row=$db->db_fetch_object($result))
			{
				if(!in_array("$row->titelpre $row->vorname $row->nachname $row->titelpost",$helparray))//damit ein Name nicht doppelt vorkommt
					$helparray[] = "$row->titelpre $row->vorname $row->nachname $row->titelpost";
			}
		}

		foreach($helparray as $elem)
		  echo $elem."<br>";
		echo "</td></tr>";

	   //FB Leiter auslesen
	   $qry = "	SELECT 
	   				distinct titelpre, titelpost, vorname, nachname 
	   			FROM 
	   				public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) 
	   			WHERE 
	   				funktion_kurzbz='Leitung' AND 
	   				(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
					(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) AND
	   				oe_kurzbz in (SELECT distinct lehrfach.oe_kurzbz 
									FROM 
										lehre.tbl_lehreinheit 
										JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
									WHERE 
										tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." AND 
										studiensemester_kurzbz=(SELECT studiensemester_kurzbz 
																FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) 
																WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)."
																ORDER BY ende DESC LIMIT 1
																)	   											
								  )";
	   
	   echo "<tr><td class='tdvertical'><b>".$p->t('courseInformation/institutsleiter')."</b></td><td>&nbsp;</td><td>";
	   if($result=$db->db_query($qry))
	   {
	   	   while($row=$db->db_fetch_object($result))
	   	   {
	   	   	   echo "$row->titelpre $row->vorname $row->nachname $row->titelpost<br>";
	   	   }
	   }

	   echo "</td></tr>";

	   //FB Koordinator auslesen
		//$qry = "SELECT distinct vorname, nachname FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE funktion_kurzbz='fbk' AND studiengang_kz='$stg' AND fachbereich_kurzbz in (SELECT fachbereich_kurzbz FROM lehre.tbl_lehrfach, lehre.tbl_lehreinheit WHERE lehrveranstaltung_id='$lv' AND tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND tbl_lehreinheit.studiensemester_kurzbz=(SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) WHERE tbl_lehreinheit.lehrveranstaltung_id='$lv' ORDER BY ende DESC LIMIT 1))";
	   $qry = "SELECT 
				distinct titelpre, titelpost, vorname, nachname, tbl_fachbereich.fachbereich_kurzbz
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_benutzerfunktion, campus.vw_mitarbeiter, public.tbl_fachbereich
			WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." AND
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND
				tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz AND
				tbl_fachbereich.fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz AND
				tbl_benutzerfunktion.funktion_kurzbz='fbk' AND 
				(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
				(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) AND
				vw_mitarbeiter.uid=COALESCE(tbl_lehrveranstaltung.koordinator, tbl_benutzerfunktion.uid) AND
				tbl_lehrveranstaltung.studiengang_kz=(SELECT studiengang_kz FROM public.tbl_studiengang WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1)";
	   
		echo "<tr><td class='tdvertical'><b>".$p->t('courseInformation/institutskoordinator')."</b></td><td>&nbsp;</td><td>";
	   if($result=$db->db_query($qry))
	   {
	   	   while($row=$db->db_fetch_object($result))
	   	   {
	   	   	   echo "$row->titelpre $row->vorname $row->nachname $row->titelpost<br>";
	   	   }
	   }

	   echo "</td></tr>";

	   //echo "</table>";
	   echo "<tr><td>";


	   echo "<input type='hidden' name='stg' value='$stg'>";
	   echo "<input type='hidden' name='sem' value='$sem'>";
	   echo "<input type='hidden' name='lv' value='$lv'>";
	   echo "<input type='hidden' name='status' value=''>";

	   echo "</td></tr>";
	   //Sprache ausgeben
	   echo "<tr><td><b>".$p->t('courseInformation/unterrichtssprache')."</b></td><td>&nbsp;</td><td>$lv_obj->sprache";
	   echo "</td></tr>";
	   
	   //Anz. Incoming ausgeben
	   	   
	   if ($lv_obj->incoming > -1)
		{
			echo "<tr><td valign='top'><b>".$p->t('courseInformation/incomingplaetze')."</b></td><td>&nbsp;</td><td valign='top'>$lv_obj->incoming";
		}
		else echo "<tr><td valign='top'><b>".$p->t('courseInformation/incomingplaetze')."</b></td><td>0";
			echo "</td></tr><tr><td colspan='4'><font style='font-size:smaller'>".$p->t('courseInformation/beiFehlernInDenFixfeldern',array($stg_obj1->email))."</font></td></tr>";
			echo "<tr><td align='left' colspan='4'><br/><br/><font style='color:black'>".$p->t('courseInformation/pflichtfelderWerdenAufDerExternenSeiteAngezeigt',array($stg_obj1->email))."</font>.</td></tr>";
			//echo "<tr><td align='left' colspan='4'><font style='color:black'>".$p->t('courseInformation/fallsSieAufzaehlungslistenVerwenden',array($stg_obj1->email))."</font></td></tr>"; --> Es sollten keine HTML-Tags gespeichert werden koennen. Hier muss eine andere Loesung gefunde werden.
			echo "</table><br><br>";

	   //Eingabefelder anzeigen
	   echo "<table width='100%'  border='0' cellspacing='0' cellpadding='0'>";


	   echo '<tr>
         <td colspan="2"><b><u>DEUTSCH</u></b></td>
	     <td rowspan="12" width="20">&nbsp;</td>
         <td colspan="2"><b><u>ENGLISH</u></b></td>
         </tr>

       ';
	 
       echo '
       <tr class="liste0">
         <td><i>'.$p->t('lvinfo/kurzbeschreibung').' <font style="color:black">(Pflichtfeld)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="kurzbeschreibung_de">'. (isset($kurzbeschreibung_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$kurzbeschreibung_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/kurzbeschreibungEN').' <font style="color:black">(Required)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="kurzbeschreibung_en">'. (isset($kurzbeschreibung_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$kurzbeschreibung_en)):'').'</textarea></td>
       </tr>
       <tr class="liste1">
         <td><i>'.$p->t('lvinfo/methodik').' <font style="color:black">(Pflichtfeld)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="methodik_de">'. (isset($methodik_de)?stripslashes(mb_eregi_replace("<br>","\r\n", $methodik_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/methodikEN').' <font style="color:black">(Required)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="methodik_en">'. (isset($methodik_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$methodik_en)):'').'</textarea></td>
       </tr>';
       echo '<tr class="liste0">
         <td><i>'.$p->t('lvinfo/lernergebnisse').' <font style="color:black">(Pflichtfeld)</font></i></td>
         <td align="right"><textarea rows="5" cols="40" name="lehrziele_de">'. (isset($lehrziele_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$lehrziele_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/lernergebnisseEN').' <font style="color:black">(Required)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="lehrziele_en">'. (isset($lehrziele_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$lehrziele_en)):'').'</textarea></td>
       </tr>
       <tr class="liste1">
         <td><i>'.$p->t('lvinfo/lehrinhalte').' <font style="color:black">(Pflichtfeld)</font></i></td>
         <td align="right"><textarea rows="5" cols="40" name="lehrinhalte_de">'. (isset($lehrinhalte_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$lehrinhalte_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/lehrinhalteEN').' <font style="color:black">(Required)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="lehrinhalte_en">'. (isset($lehrinhalte_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$lehrinhalte_en)):'').'</textarea></td>
       </tr>
       <tr class="liste0">
         <td><i>'.$p->t('lvinfo/vorkenntnisse').' <font style="color:black">(Pflichtfeld)</font></i> </td>
         <td align="right"><textarea rows="5" cols="40" name="voraussetzungen_de">'. (isset($voraussetzungen_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$voraussetzungen_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/vorkenntnisseEN').' <font style="color:black">(Required)</font></i></td>
         <td align="right"><textarea rows="5" cols="40" name="voraussetzungen_en">'. (isset($voraussetzungen_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$voraussetzungen_en)):'').'</textarea></td>
       </tr>';
       echo '<tr class="liste1">
         <td><i>'.$p->t('lvinfo/literatur').'</i> </td>
         <td align="right"><textarea rows="5" cols="40" name="unterlagen_de">'. (isset($unterlagen_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$unterlagen_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/literaturEN').'</i></td>
         <td align="right"><textarea rows="5" cols="40" name="unterlagen_en">'. (isset($unterlagen_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$unterlagen_en)):'').'</textarea></td>
       </tr>
       <tr class="liste0">
         <td><i>'.$p->t('lvinfo/leistungsbeurteilung').'</i></td>
         <td align="right"><textarea rows="5" cols="40" name="pruefungsordnung_de">'. (isset($pruefungsordnung_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$pruefungsordnung_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/leistungsbeurteilungEN').'</i> </td>
         <td align="right"><textarea rows="5" cols="40" name="pruefungsordnung_en">'. (isset($pruefungsordnung_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$pruefungsordnung_en)):'').'</textarea></td>
       </tr>
        <tr class="liste1">
         <td><i>'.$p->t('lvinfo/anwesenheit').'</i></td>
         <td align="right"><textarea rows="5" cols="40" name="anwesenheit_de">'. (isset($anwesenheit_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$anwesenheit_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/anwesenheitEN').'</i></td>
         <td align="right"><textarea rows="5" cols="40" name="anwesenheit_en">'. (isset($anwesenheit_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$anwesenheit_en)):'').'</textarea></td>
       </tr>
       <tr class="liste0">
         <td><i>'.$p->t('lvinfo/anmerkungen').'</i></td>
         <td align="right"><textarea rows="5" cols="40" name="anmerkungen_de">'. (isset($anmerkungen_de)?stripslashes(mb_eregi_replace("<br>","\r\n",$anmerkungen_de)):'').'</textarea></td>
         <td><i>'.$p->t('lvinfo/anmerkungenEN').'</i></td>
         <td align="right"><textarea rows="5" cols="40" name="anmerkungen_en">'. (isset($anmerkungen_en)?stripslashes(mb_eregi_replace("<br>","\r\n",$anmerkungen_en)):'').'</textarea></td>
       </tr>
       <tr class="liste0">
         <td align=center colspan=2><br><input type="checkbox" name="freig_de" '. (isset($freig_de) && ($freig_de==true || $freig_de=='1')?'checked':'').'/><i>'.$p->t('courseInformation/freigeben').'</i><br><br></td>
         <td align=center colspan=2><input type="checkbox" name="freig_en" '. (isset($freig_en) && ($freig_en==true || $freig_en=='1')?'checked':'').'/><i>'.$p->t('courseInformation/freigeben').'</i> </td>
         <td ></td>
       </tr>';
	   echo "</table><br>";
	   echo "<div align='right'>";
	   echo "<input type='button' value='".$p->t('global/speichern')."' onClick='save();'>";
	   echo "<input type='button' value='".$p->t('courseInformation/voransicht')."' onClick='javascript:window.document.editFrm.action=\"preview.php\";window.document.editFrm.target=\"_blank\";window.document.editFrm.submit();'>";
	   echo "</div>";
	   if(isset($error) && $error!='')
	   	   	echo $error;
   }
?>
<td></tr></table>
</body>
</html>
