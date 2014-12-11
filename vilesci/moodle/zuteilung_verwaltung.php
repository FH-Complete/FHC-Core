<?php
//@version $Id: kurs_verwaltung.php 2799 2009-07-16 11:56:39Z simane $
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger  < christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher       < andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl                < rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens       < gerald.simane-sequens@technikum-wien.at >
 */
/*
*   Dieses Programm listet nach Selektinskreterien alle Moodelkurse zu einem Studiengang oder Lehreinheit auf.
*   Jede MoodleID kann invididuell zu einem Studiengang oder Lehreinheit zugeteilt werden.
*/
header('Content-Type: text/html;charset=UTF-8');
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/lehreinheitgruppe.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/moodle19_course.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/moodle'))
	die('Sie haben keine Berechtigung für diese Seite');

// ***********************************************************************************************
//      Datenbankverbindungen zu Moodle und Vilesci und Classen
// ***********************************************************************************************

        if (!$objMoodle = new moodle19_course())
	         die('Fehler beim Oeffnen der Moodleverbindung');

        if (!$le_obj = new lehreinheit())
	         die('Fehler beim Oeffnen der Lehreinheit');

        if (!$legrp_obj = new lehreinheitgruppe())
	         die('Fehler beim Oeffnen der Lehreinheitgruppe');

        if (!$lv_obj = new lehrveranstaltung())
	         die('Fehler beim Oeffnen der Lehrveranstaltung');

        if (!$stg_obj = new studiengang())
	         die('Fehler beim Oeffnen der Studieng&auml;nge');

        if (!$stsem = new studiensemester())
	         die('Fehler beim Oeffnen der Studiensemester');
        // @$studiensemester_kurzbz Studiensemester xxJJJJ - xx fuer SS Sommer  oder WW Winter
        if (!$stsem_aktuell = $stsem->getakt())
                $stsem_aktuell = $stsem->getaktorNext();

// ***********************************************************************************************

// Variable Initialisieren

// ***********************************************************************************************

        // AusgabeStream

        $content='';
        $errormsg=array();

// @bDebug Anzeige der xml-rfc Daten moegliche Stufen sind 0,1,2,3
        $bDebug= (isset($_REQUEST['debug']) && !empty($_REQUEST['debug'])?1:0);
// @$lehrveranstaltung_id Lehrveranstaltung
        $studiensemester_kurzbz=(isset($_REQUEST['studiensemester_kurzbz'])?trim($_REQUEST['studiensemester_kurzbz']):$stsem_aktuell);
// @$lehreinheit_id Lehreinheit
        $lehreinheit_id=(isset($_REQUEST['leid'])?trim($_REQUEST['leid']):(isset($_REQUEST['lehreinheit_id'])?trim($_REQUEST['lehreinheit_id']):''));
        $lehrveranstaltung_id=(isset($_REQUEST['lvid'])?trim($_REQUEST['lvid']):(isset($_REQUEST['lehrveranstaltung_id'])?trim($_REQUEST['lehrveranstaltung_id']):''));
// @$studiengang_kz Studiengang
        $studiengang_kz=(isset($_REQUEST['studiengang_kz'])?trim($_REQUEST['studiengang_kz']):227);
// @$semester Semester des Studienganges
        $semester=(isset($_REQUEST['semester'])?trim($_REQUEST['semester']):1);
// @$moodle_id Moodle SubKurs (Unterkat.) ID zu Moodle Kurs ID (mdl_course_id)
        $moodle_id=(isset($_REQUEST['moodle_id'])?$_REQUEST['moodle_id']:'');
// @$mdl_course_id Moodle - ID suche
        $mdl_course_id=(isset($_REQUEST['mdl_course_id'])?trim($_REQUEST['mdl_course_id']):'');
// @bAnzeige der xml-rfc Daten moegliche Stufen sind 0,1,2,3
         $bAnzeige=(isset($_REQUEST['anzeige'])?trim($_REQUEST['anzeige']):false);

// @bAnzeige der xml-rfc Daten moegliche Stufen sind 0,1,2,3
         $lehre=(isset($_REQUEST['lehre'])?true:(!$bAnzeige?true:false));
         $aktiv=(isset($_REQUEST['aktiv'])?true:(!$bAnzeige?true:false));

// ***********************************************************************************************
// Datenbankabfragen
// ***********************************************************************************************


//---------------------------------------------------------------------------
//      Check Moodle
        $mdl_course_stat='';
        if (!empty($mdl_course_id))
        {
					$bAnzeige=true;
				  	if(!$objMoodle->getAllMoodleVariant($mdl_course_id,'','','','','',false))
				    {
							$bAnzeige=false;
					   	 	$errormsg[]='Problem beim Lehre Moodle-Kurs '.addslashes($mdl_course_id).' lesen '.$objMoodle->errormsg;
					}
					// Lehre Moodle-Kurs gefunden
					if(isset($objMoodle->result) && isset($objMoodle->result[0]))
				    {
				      	  $mdl_course_stat='*';
				          $moodle_id=$objMoodle->result[0]->moodle_id;
				          $lehrveranstaltung_id=$objMoodle->result[0]->moodle_lehrveranstaltung_id;
				          $lehreinheit_id=$objMoodle->result[0]->moodle_lehreinheit_id;
				          $studiensemester_kurzbz=$objMoodle->result[0]->studiensemester_kurzbz;
					}
					// suchen Kurs in Moodle direkt - neue Vilesci - Lehre anlage notwendig
				    else
				    {
				    	$bAnzeige=false;
					   	// Wenn kein Eintrag in der Lehre vorhanden ist pruefen ob ein Moodlekurs vorhanden ist
                	     if ($objMoodle->load($mdl_course_id))
                    	 {
                        	   $mdl_course_stat='+';
						 }	   
	                      else
    	                  {
        		               $errormsg[]='Moodle-Kurs wurde nicht gefunden '.addslashes($mdl_course_id).' '.$objMoodle->errormsg;
                	      }
	               	}
      	}

//---------------------------------------------------------------------------
// @$lehreinheit_id Lehreinheit
        if ($lehreinheit_id)
        {
                if ($le_obj->load($lehreinheit_id))
                {
                        $lehrveranstaltung_id = $le_obj->lehrveranstaltung_id;
                        $studiensemester_kurzbz = $le_obj->studiensemester_kurzbz;

                }
                else
                {
                        $bAnzeige=false;
                        $errormsg[]='Lehreinheit wurde nicht gefunden '.addslashes($lehreinheit_id).' '.$le_obj->errormsg;
                }
        }

//---------------------------------------------------------------------------
// @$lehrveranstaltung_id Lehrveranstaltung
        if ($lehrveranstaltung_id)
        {
                if ($lv_obj->load($lehrveranstaltung_id))
                {
                    $studiengang_kz = $lv_obj->studiengang_kz;
	             $semester = $lv_obj->semester;
                }
                else
                {
                    $bAnzeige=false;
                    $errormsg[]='Lehrveranstaltung wurde nicht gefunden '.addslashes($lehreinheit_id).' '.$lv_obj->errormsg;
                }
        }

// ***********************************************************************************************

//      HTML Auswahlfelder (Teil 1)

// ***********************************************************************************************
#echo "<p> $studiensemester_kurzbz.$studiengang_kz.$lehrveranstaltung_id.$lehreinheit_id.$semester </p>";

        // FormName erzeugen
        $cFormName='searchMoodleCurse'.$studiensemester_kurzbz.$studiengang_kz.$lehrveranstaltung_id.$lehreinheit_id.$semester;
        $content.='<h2>Moodle - Kursverwaltung</h2>
                <form accept-charset="UTF-8" name="'.$cFormName.'"  method="POST" target="_self" action="'.$_SERVER['PHP_SELF'].'" >
                        <table class="liste">
                        <tr>
                           	<th>Studiensem</th>
                           	<th>StgKz</th>
                           	<th>Sem</th>
                           	<th>Lehrveranstaltung</th>
                           	<th>Lehreinheiten</th>
                           	<th>Moodlekurs</th>
							<td>&nbsp;</td>
                        </tr>';
        //---------------------------------------------------------------------------
	// Auswahlfelder
	     $content.='<tr>';

        // Studiensemester public.tbl_studiensemester_kurzbz
         $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehrveranstaltung_id.value=\'\';document.'.$cFormName.'.lehreinheit_id.value=\'\';document.'.$cFormName.'.mdl_course_id.value=\'\';document.'.$cFormName.'.submit();" name="studiensemester_kurzbz">';
 	     $content.='<option value="">&nbsp;Alle&nbsp;</option>';
         $stsem->studiensemester=array();
		 if ($stsem->getAll())
         {
              foreach ($stsem->studiensemester as $row)
              {
                     $content.='<option value="'.$row->studiensemester_kurzbz.'" '.(("$studiensemester_kurzbz"=="$row->studiensemester_kurzbz")?' selected="selected" ':'').'>&nbsp;'.$row->studiensemester_kurzbz.'&nbsp;</option>';
              }
         }
         else
         {
               $errormsg[]='Studiensemester wurden nicht gefunden! '.$stsem->errormsg;
         }
         $content.='</select></td>';

        //---------------------------------------------------------------------------
        // Studiengang public.tbl_studiengang_kz
        	$content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehrveranstaltung_id.value=\'\';document.'.$cFormName.'.lehreinheit_id.value=\'\';document.'.$cFormName.'.mdl_course_id.value=\'\';document.'.$cFormName.'.submit();" name="studiengang_kz">';
			if ($studiengang_kz=='*')
			{
				$studiengang_kz='';
			}  
			$content.='<option value="" '.(empty($studiengang_kz)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';

            $stsem->result=array();
            if ($stg_obj->getAll('typ, kurzbz',true))
            {
                        $max_semester=0;
                        $arrStudiengang='';
                        foreach ($stg_obj->result as $row)
                        {
                                if (empty($studiengang_kz) && !isset($_REQUEST['studiengang_kz']) )
                                {
                                        $studiengang_kz=$row->studiengang_kz;
                                        $semester=1;
                                }
                                if ($studiengang_kz==$row->studiengang_kz)
                                {
                                        $arrStudiengang=$row;
                                        $max_semester=$row->max_semester;
                                }
                                $content.='<option value="'.$row->studiengang_kz.'" '.(("$studiengang_kz"=="$row->studiengang_kz")?' selected="selected" ':'').'>&nbsp;'.$row->kuerzel.'&nbsp;('.$row->kurzbzlang.')&nbsp;</option>';
                        }
                }
                else
                {
                        $content.='<option value="" '.(empty($studiengang_kz)?' selected="selected" ':'').'>&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
                        $errormsg[]='Studieng&auml;nge wurden nicht gefunden! '.$stg_obj->errormsg;
                }
                $content.='</select></td>';

        //---------------------------------------------------------------------------
        // Semster public.tbl_studiengang_kz - max Semester des Selektierten Studiengangs
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehrveranstaltung_id.value=\'\';document.'.$cFormName.'.lehreinheit_id.value=\'\';document.'.$cFormName.'.mdl_course_id.value=\'\';document.'.$cFormName.'.submit();" name="semester">';
                $content.='<option value="" '.(empty($semester)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                if ($studiengang_kz!='')
                {
                        for($i=0;$i<=$max_semester;$i++)
                        {
                                $content.='<option value="'.($i).'" '.(("$semester"=="$i")?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
                        }
                }
                else
                {
                        for($i=0;$i<=9;$i++)
                        {
                                $content.='<option value="'.($i).'" '.(("$semester"=="$i")?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
                        }
                }
                $content.='</select></td>';

        //---------------------------------------------------------------------------
        // Lehrveranstaltungen
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehreinheit_id.value=\'\';document.'.$cFormName.'.mdl_course_id.value=\'\';document.'.$cFormName.'.submit();" name="lehrveranstaltung_id">';
                $content.='<option value="" '.(empty($lehrveranstaltung_id)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $lv_obj->lehrveranstaltungen=array();
		  		if (!empty($studiengang_kz))
		  		{	
	                if ($lv_obj->load_lva_le($studiengang_kz, $studiensemester_kurzbz, $semester,null,null,null,'bezeichnung'))
	                {
	                        foreach ($lv_obj->lehrveranstaltungen as $row)
	                        {
	                                $content.='<option value="'.$row->lehrveranstaltung_id.'" '.(("$lehrveranstaltung_id"=="$row->lehrveranstaltung_id")?' selected="selected" ':'').'>&nbsp;'.CutString($row->bezeichnung, 21).' '.$row->lehrform_kurzbz.'&nbsp;'.$row->lehrveranstaltung_id.'</option>';
	                        }
	                }
	                else
	                {
	                        $content.='<option value="" '.(empty($studiengang_kz)?' selected="selected" ':'').'>&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
	                        $errormsg[]='Lehrveranstaltungen wurden nicht gefunden! '.$lv_obj->errormsg;
	                }
		 	 	}	  
                $content.='</select><br />
		  &nbsp;nur in Lehre&nbsp;<input title="nur mit Verplanter Lehreinheiten" type="Checkbox" value="1" name="lehre" '.($lehre?' checked="checked" ':'').' />
		  &nbsp;nur aktive&nbsp;<input type="Checkbox" value="1" name="aktiv" '.($aktiv?' checked="checked" ':'').' />		  
		  </td>';
        //---------------------------------------------------------------------------
        // Lehreinheit
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.mdl_course_id.value=\'\';document.'.$cFormName.'.submit();" name="lehreinheit_id">';
                $content.='<option value="" '.(empty($lehreinheit_id)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $le_obj->lehreinheiten=array();
                if (!empty($lehrveranstaltung_id))
                {
                        $le_obj->load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz);
                        foreach ($le_obj->lehreinheiten as $row)
                        {
                                //Gruppen laden
                                $gruppen = '';
				    if (!$legrp_obj = new lehreinheitgruppe())
				         die('Fehler beim Oeffnen der Lehreinheitgruppe');
                                if ($legrp_obj->getLehreinheitgruppe($row->lehreinheit_id))
                                {
                                        foreach ($legrp_obj->lehreinheitgruppe as $grp)
                                        {
                                                if($grp->gruppe_kurzbz=='')
                                                        $gruppen.=' '.$grp->semester.$grp->verband.$grp->gruppe;
                                                else
                                                        $gruppen.=' '.$grp->gruppe_kurzbz;
                                        }
                                }
                                $content.='<option value="'.$row->lehreinheit_id.'" '.($lehreinheit_id==$row->lehreinheit_id?' selected="selected" ':'').'>&nbsp;'.$row->lehrform_kurzbz.' '.$gruppen.($bDebug?'&nbsp;(Le ID '.$row->lehreinheit_id.')':'').'</option>';
                        }
                }
                $content.='</select></td>';
        //---------------------------------------------------------------------------
        // ---- Moodle mdl_course_id
                $content.='<td valign="top"><b>oder</b>&nbsp;KursID&nbsp;<input size="4" maxlength="8" name="mdl_course_id" value="'.$mdl_course_id.'">'.$mdl_course_stat;

        //---------------------------------------------------------------------------
        // ---- Submitknopf
                $content.='
                        <td valign="top">
                                <input style="padding: 2px 20px 2px 20px;" name="anzeigen" type="submit" value="anzeigen">
                                <input style="display:none" type="text" name="anzeige" value="anzeige" />
                                <input style="display:none" type="text" name="debug" value="'.$bDebug.'" />
			    </td>
        </tr></table>
        </form>';

       $content.='<hr>';

// ***********************************************************************************************
//      HTML Listenanzeige (Teil 2) Detailkursdaten
// ***********************************************************************************************
        if ($bAnzeige)
        {
		   // Moodle ID eingabe wurde bereits am Anfang gelesen
		   if (!$mdl_course_id)
		   {
	                if ($lehreinheit_id)
	                        $lehrveranstaltung_id='';
#echo "<hr> $mdl_course_id,$studiengang_kz,$lehreinheit_id,$lehrveranstaltung_id,$studiensemester_kurzbz,$semester <hr>";
	                if(!$objMoodle->getAllMoodleVariant($mdl_course_id,$lehrveranstaltung_id,$studiensemester_kurzbz,$lehreinheit_id,$studiengang_kz,$semester,false,$lehre,$aktiv))
	                        $errormsg[]=$objMoodle->errormsg;
		   }
		// Aufbau der Moodlekurs - Tabelle

		   if (is_array($objMoodle->result) && count($objMoodle->result)>0)
		   	$content.=writeMoodlekursHTML($objMoodle->result,$bDebug,$errormsg);
        }

// ***********************************************************************************************
//      HTML Header und Foot zum Content (Ausgabestring) hinzufuegen, und Anzeigen
// ***********************************************************************************************

        $content='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
        <head>
                <title>Moodle - Kurszuteilungverwalten</title>
                <base target="main">
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
        </head>
        <body>
	        <!-- MoodleKurs Content Start -->
       	         '.$content.'
       	 <!-- MoodleKurs Content Ende -->
		    <iframe style="height:300px;width: 100%;padding: 0px 0px 0px 0px;margin: 0px 0px 0px 0px;border: 0px;"  id="zuteilung_warten" src="zuteilung_warten.php'.($mdl_course_stat=='+'?'?mdl_course_id='.$mdl_course_id:'').'" name="zuteilung_warten" frameborder="0">
              	  No iFrames
       	 </iframe>
		<p class="error">'.implode('<br>',$errormsg).'</p>
        </body>
        </html>';
        exit($content);

// ***********************************************************************************************
//      erzeugen HTML Output der Moodlekursdaten
// ***********************************************************************************************
        function writeMoodlekursHTML($arrMoodlekurs,$bDebug,&$errormsg)
        {
	 
	           $content='';
             if (!is_array($arrMoodlekurs) || count($arrMoodlekurs)<1)
                        return $content;

             if (!$objMoodle = new moodle19_course())
	              die('Fehler beim Oeffnen der Moodleverbindung');

             if (!$le_obj = new lehreinheit())
	              die('Fehler beim Oeffnen der Lehreinheit');

             if (!$legrp_obj = new lehreinheitgruppe())
	              die('Fehler beim Oeffnen der Lehreinheitgruppe');

             if (!$lv_obj = new lehrveranstaltung())
	              die('Fehler beim Oeffnen der Lehrveranstaltung');

             if (!$stg_obj = new studiengang())
	              die('Fehler beim Oeffnen der Studieng&auml;nge');

	      if (!$stsem = new studiensemester())
	        	die('Fehler beim Oeffnen der Studiensemester');

#$content.=count($objMoodle->result).'<hr>';
               // Header Top mit Anzahl der gelisteten Kurse
		$content.='<div style="height:300px;overflow:auto;">';
				$content.='<table class="liste">';

               // Header Teil Information der Funktion
               // Headerinformation der Tabellenfelder
                        $content.='<tr class="liste" align="center">';
			   
                                $content.='<th colspan="2">&nbsp;StSem&nbsp;</th>';
                                $content.='<th colspan="2">&nbsp;Studiengang&nbsp;</th>';
                                $content.='<th>&nbsp;Sem&nbsp;</th>';
                                $content.='<th colspan="2">&nbsp;Lehrveranstaltung&nbsp;</th>';
                                $content.='<th colspan="2">&nbsp;Lehreinheit&nbsp;</th>';
                                $content.='<th colspan="2">&nbsp;Moodle Kurs&nbsp;</th>';
                                $content.='<td colspan="2">&nbsp;bearbeiten&nbsp;</td>';
                        $content.='</tr>';

                // Alle Moodlekurse in einer Schleife anzeigen.
                for($i=0;$i<count($arrMoodlekurs);$i++)
                {
					
		$cFormName='workMoodleCurseDetail'.$i;
   


                        // ZeilenCSS (gerade/ungerade) zur besseren Ansicht
                        if ($i%2)
                                $showCSS=' class="liste0"  style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 2px 1px 2px; background:#FEFFEC" ';
                        else
                                $showCSS=' class="liste1" style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 2px 1px 2px; background:#FCFCFC"  ';

                        // Listenzeile
                        $content.='<tr '.$showCSS.' align="center">';
					$content.='<td align="left">';
						$content.='<table><tr>';
		                               $content.= '<td align="left" id="detail'.$i.'_on" onclick="this.className=\'ausblenden\';document.getElementById(\'detail'.$i.'_off\').className=\'einblenden\';document.getElementById(\'detail'.$i.'\').className=\'einblenden\';"><img height="15" src="../../skin/images/bullet_arrow_right.png" border="0" title="Detailansicht" alt="bullet_arrow_down.png" />&nbsp;</td>';
              		                 $content.= '<td align="left" id="detail'.$i.'_off"  onclick="this.className=\'ausblenden\';document.getElementById(\'detail'.$i.'_on\').className=\'einblenden\';document.getElementById(\'detail'.$i.'\').className=\'ausblenden\';" class="ausblenden"><img height="15" src="../../skin/images/bullet_arrow_down.png" border="0" title="Detailansicht" alt="bullet_arrow_down.png" />&nbsp;</td>';
						$content.='</tr></table>';
					$content.='</td>';

				  $content.='<td '.$showCSS.'>'.$arrMoodlekurs[$i]->studiensemester_kurzbz.'</td>';
	 	                if (!$stg_obj->load($arrMoodlekurs[$i]->lehrveranstaltung_studiengang_kz))
	                       {
					  $stg_obj->kuerzel='';
					  $stg_obj->bezeichnung='Fehler Studiengang ';
					  $stg_obj->kurzbzlang=$stg_obj->errormsg;
                		         $stg_obj->studiengang_kz=$arrMoodlekurs[$i]->lehrveranstaltung_studiengang_kz;
                              }

				    $content.='<td '.$showCSS.'>'.$stg_obj->kurzbzlang.'&nbsp;</td>';
                                $content.='<td '.$showCSS.'>'.$stg_obj->bezeichnung.($bDebug?' '.$stg_obj->studiengang_kz:'').'&nbsp;</td>';
                                $content.='<td '.$showCSS.'>'.$arrMoodlekurs[$i]->lehrveranstaltung_semester.'&nbsp;</td>';

		      // Lehrveranstaltung
				if ($arrMoodlekurs[$i]->moodle_lehrveranstaltung_id)
	               	{
					$lvID=$arrMoodlekurs[$i]->moodle_lehrveranstaltung_id;
					$kurzbz='<b>'.$arrMoodlekurs[$i]->lehrveranstaltung_kurzbz.'</b>, '.$arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung.($arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz?', '.$arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz:'');
                    		}
				else
				{
				     	$lvID='*'.$arrMoodlekurs[$i]->lehrveranstaltung_id;
                       		$kurzbz='<b>zur Lehreinheit - '.$arrMoodlekurs[$i]->lehrveranstaltung_kurzbz.'</b>, '.$arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung.($arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz?', '.$arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz:'');;
				}
                		$content.='<td colspan="2" title="'.(isset($arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung)?$arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung.' Kurzbz:'.$arrMoodlekurs[$i]->lehrveranstaltung_kurzbz.' LV Kurzbz:'.$arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz.' ID:'.$arrMoodlekurs[$i]->lehrveranstaltung_id:'').'" '.$showCSS.'>';
				$content.=$kurzbz. ($bDebug?' '.$lvID:'').'&nbsp;</td>';

                                // Lehreinheit
			  $leID=$arrMoodlekurs[$i]->lehreinheit_id;
                  	  if ($arrMoodlekurs[$i]->moodle_lehreinheit_id)
                  	  {
         	           	if ( $le_obj->loadLE($arrMoodlekurs[$i]->moodle_lehreinheit_id))
  	                  	{
                             //Gruppen laden
                             $gruppen = $le_obj->lehrform_kurzbz.'&nbsp;';
				  if (!$legrp_obj = new lehreinheitgruppe())
					  die('Fehler beim Oeffnen der Lehreinheitgruppe');
#var_dump($le_obj);
	                             $legrp_obj->getLehreinheitgruppe($arrMoodlekurs[$i]->lehreinheit_id);
	                             foreach ($legrp_obj->lehreinheitgruppe as $grp)
	                             {
	                                     if($grp->gruppe_kurzbz=='')
	                                             $gruppen.=' '.$grp->semester.$grp->verband.$grp->gruppe;
	                                     else
	                                             $gruppen.=' '.$grp->gruppe_kurzbz;
					}
				}
				else
				{
                            	$gruppen='Fehler Lehreinheit '.$legrp_obj->errormsg;
				}
                         }
			    else
			     {
				$leID='-';
                     	$gruppen='zur kpl.LV';
			    }
                     	$content.='<td '.$showCSS.'>'.$gruppen.'&nbsp;</td>';
                     	$content.='<td '.$showCSS.'>'.($bDebug?$leID:'').'&nbsp;</td>';

                                // Moodle
                                if (empty($arrMoodlekurs[$i]->mdl_shortname))
                                        $arrMoodlekurs[$i]->mdl_shortname=$arrMoodlekurs[$i]->mdl_fullname;
                                $content.='<td  onclick="document.'.$cFormName.'.submit();" '.$showCSS.'>'.$arrMoodlekurs[$i]->mdl_shortname.'&nbsp;</td>';
                                $content.='<td onclick="document.'.$cFormName.'.submit();" '.$showCSS.'>'.$arrMoodlekurs[$i]->mdl_course_id.'&nbsp;</td>';
                        // Bearbeitung Submit

					$cFormNameDel=$cFormName.'del';					    

					if ($arrMoodlekurs[$i]->mdl_course_id)
					{
	                              $content.= '<td valign="top" title="&Auml;ndert den Kurs in der Lehre und auch den Moodle Kurs. Entfernt den Kurs aus der Lehre."  style="cursor: pointer;" onclick="document.'.$cFormName.'.submit();">';
	       	                         $content.='<form style="display: inline;border:0px;" name="'.$cFormName.'" method="POST" target="zuteilung_warten" action="zuteilung_warten.php">';
          		                       $content.= '<input style="display:none" type="text" name="lehrveranstaltung_id" value="'.$arrMoodlekurs[$i]->moodle_lehrveranstaltung_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="lehreinheit_id" value="'.$arrMoodlekurs[$i]->moodle_lehreinheit_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="mdl_course_id" value="'.$arrMoodlekurs[$i]->mdl_course_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="studiensemester_kurzbz" value="'.$arrMoodlekurs[$i]->studiensemester_kurzbz.'" />';
                                            $content.= '<input style="display:none" type="text" name="wartung" value="wartung" />';
                                            $content.= '<input style="display:none" type="text" name="debug" value="'.$bDebug.'" />';
                                            $content.= '<img height="15" src="../../skin/images/edit.png" border="0" title="MoodleKurs aendern" alt="edit.png" />';
                                            $content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$i.'" />';
                                            $content.= '&auml;ndern';
              	                   $content.='</form>';
                     	           $content.= '</td>';

	                              $content.= '<td valign="top" title="Entfernt den Kurs aus der Lehre. Der Moodle Kurs bleibt bestehen." style="cursor: pointer;" onclick="if (!window.confirm(\'L&ouml;schen Moodlekurs '.$arrMoodlekurs[$i]->mdl_course_id.', '.$arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung.' ? \')) {return false;}; document.'.$cFormNameDel.'.submit();">';
	       	                     $content.='<form style="display: inline;border:0px;" name="'.$cFormNameDel.'" method="POST" target="zuteilung_warten" action="zuteilung_warten.php">';
          		                       $content.= '<input style="display:none" type="text" name="mdl_course_id" value="'.$arrMoodlekurs[$i]->moodle_mdl_course_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="lehrveranstaltung_id" value="'.$arrMoodlekurs[$i]->moodle_lehrveranstaltung_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="lehreinheit_id" value="'.$arrMoodlekurs[$i]->moodle_lehreinheit_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="studiensemester_kurzbz" value="'.$arrMoodlekurs[$i]->studiensemester_kurzbz.'" />';
                                            $content.= '<input style="display:none" type="text" name="entfernen" value="entfernen" />';
                                            $content.= '<input style="display:none" type="text" name="debug" value="'.$bDebug.'" />';
                                            $content.= '<img height="15" src="../../skin/images/table_row_delete.png" border="0" title="MoodleKurs entfernen" alt="table_row_delete.png" />';
                                            $content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$i.'" />';
                                            $content.= 'entfernen<br /> aus Lehre';
              	                   $content.='</form>';
                     	           $content.= '</td>';
					    
					}
					else
					{
	                              $content.= '<td valign="top" title="Entfernt den Kurs aus der Lehre." style="cursor: pointer;" onclick="if (!window.confirm(\'L&ouml;schen Moodlekurs '.$arrMoodlekurs[$i]->mdl_course_id.', '.$arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung.' ? \')) {return false;}; document.'.$cFormNameDel.'.submit();">';
	       	                         $content.='<form style="display: inline;border:0px;" name="'.$cFormNameDel.'" method="POST" target="zuteilung_warten" action="zuteilung_warten.php">';
          		                       $content.= '<input style="display:none" type="text" name="mdl_course_id" value="'.$arrMoodlekurs[$i]->moodle_mdl_course_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="lehrveranstaltung_id" value="'.$arrMoodlekurs[$i]->moodle_lehrveranstaltung_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="lehreinheit_id" value="'.$arrMoodlekurs[$i]->moodle_lehreinheit_id.'" />';
          		                       $content.= '<input style="display:none" type="text" name="studiensemester_kurzbz" value="'.$arrMoodlekurs[$i]->studiensemester_kurzbz.'" />';
                                            $content.= '<input style="display:none" type="text" name="entfernen" value="entfernen" />';
                                            $content.= '<input style="display:none" type="text" name="debug" value="'.$bDebug.'" />';
                                            $content.= '<img height="15" src="../../skin/images/table_row_delete.png" border="0" title="MoodleKurs entfernen" alt="table_row_delete.png" />';
                                            $content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$i.'" />';
                                            $content.= 'entfernen';
              	                   $content.='</form>';
                     	           $content.= '</td>';
					}
                        $content.='</tr>';

			   $content.='<tr>
			   	<td '.$showCSS.' colspan="19">
					<table id="detail'.$i.'" class="ausblenden" >
						<tr><td>&nbsp;</td></tr>';

			   		$content.='<tr>
							<th colspan="3" class="topbar" colspan="2">Detailanzeige Moodelkurs '.$arrMoodlekurs[$i]->mdl_course_id.($arrMoodlekurs[$i]->moodle_lehrveranstaltung_id?' - zur Lehrveranstaltung':' - zur Lehreinheit'). '</th>
					</tr>';
                     // Moodle
					$content.='<tr>';
	    	                    $content.='<td></td><th>&nbsp;Moodle Kurs&nbsp;</th><td>'.$arrMoodlekurs[$i]->mdl_fullname.'<br />&nbsp;'.  $arrMoodlekurs[$i]->mdl_shortname.',&nbsp;</td>';
					$content.='</tr>';
					
					$content.='<tr>
							<td colspan="3"><hr /></td>
						</tr>';

					$content.='<tr>';
                    	       		$content.='<td>&nbsp;</td><th>&nbsp;Studiensemester&nbsp;</th>';
								$content.='<td>'.$arrMoodlekurs[$i]->studiensemester_kurzbz.'</td>';
					$content.='</tr>';

					$content.='<tr>';
                   	           	$content.='<td>&nbsp;</td><th>&nbsp;Studiengang&nbsp;</th>';
                               	$content.='<td>'.$stg_obj->kuerzel.'&nbsp;'.$stg_obj->bezeichnung.'&nbsp;('.$stg_obj->kurzbzlang.'),&nbsp;'.$stg_obj->studiengang_kz.'&nbsp;</td>';
					$content.='</tr>';

					$content.='<tr>';
	                            $content.='<td></td><th>&nbsp;Semester&nbsp;</th>';
	                            $content.='<td>'.$arrMoodlekurs[$i]->lehrveranstaltung_semester.'&nbsp;</td>';
					$content.='</tr>';

					$content.='<tr>';
	                            $content.='<td></td><th>&nbsp;Lehrveranstaltung&nbsp;</th>';
           	                	$content.='<td>'.(isset($arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung)?$arrMoodlekurs[$i]->lehrveranstaltung_bezeichnung.'&nbsp;&nbsp;Kurzbz:&nbsp;'.$arrMoodlekurs[$i]->lehrveranstaltung_kurzbz.'&nbsp;,&nbsp;Lehrform Kurzbz:'.($arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz?$arrMoodlekurs[$i]->lehrveranstaltung_lehrform_kurzbz:' - '):'').',&nbsp;ID&nbsp;'.$arrMoodlekurs[$i]->lehrveranstaltung_id.'&nbsp;</td>';
					$content.='</tr>';

					$content.='<tr>';
	                            $content.='<td></td><th valign="top">&nbsp;Lehreinheit&nbsp;</th>';

					if ($arrMoodlekurs[$i]->moodle_lehrveranstaltung_id)
					{
						$content.='<td valign="top">';
						$le_obj->lehreinheiten=array(); // Init
			                     $le_obj->load_lehreinheiten($arrMoodlekurs[$i]->lehrveranstaltung_id, $arrMoodlekurs[$i]->studiensemester_kurzbz);
			                     foreach ($le_obj->lehreinheiten as $row)
			                     {
				                          //Gruppen laden
									$gruppen = '';
							    	if (!$legrp_obj = new lehreinheitgruppe())
							     		    die('Fehler beim Oeffnen der Lehreinheitgruppe');
						            if ($legrp_obj->getLehreinheitgruppe($row->lehreinheit_id))
						            {
										foreach ($legrp_obj->lehreinheitgruppe as $grp)
					                    {
					                           if($grp->gruppe_kurzbz=='')
					                              $gruppen.=' '.$grp->semester.$grp->verband.$grp->gruppe;
					                           else
					                              $gruppen.=' '.$grp->gruppe_kurzbz;
					                             }
					                }
									
									//Lektoren laden
									$lektoren='';
									$lehreinheitmitarbeiter = new lehreinheitmitarbeiter();
									$lehreinheitmitarbeiter->getLehreinheitmitarbeiter($row->lehreinheit_id);
									foreach ($lehreinheitmitarbeiter->lehreinheitmitarbeiter as $ma)
									{
										$lektoren.= ($lektoren?',':'').'&nbsp;'.$ma->mitarbeiter_uid;
									}
									
			                        $content.=$row->lehrform_kurzbz.'&nbsp;'.$gruppen.'&nbsp;ID&nbsp;'.$row->lehreinheit_id.'&nbsp;'.$lektoren;
								$content.='<br />';
						}
						$content.='</td>';
					}
					else
					{
						$content.='<td>'.$gruppen.',&nbsp;'.($arrMoodlekurs[$i]->lehreinheit_id?$arrMoodlekurs[$i]->lehreinheit_id:'').'</td>';
					}

					$content.='</tr>';
					$content.='<tr><td>&nbsp;</td></tr>';
                        // Bearbeitung Submit
					$content.='<tr>';
					if ($arrMoodlekurs[$i]->mdl_course_id)
					{
					#    $cFormName='workMoodleCurseDetail'.$i;
             	$content.= '<th colspan="3" style="cursor: pointer;" onclick="document.'.$cFormName.'.submit();">';	
                $content.= '<img height="15" src="../../skin/images/edit.png" border="0" title="MoodleKurs entfernen" alt="edit.png" />';
    	          $content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$i.'" />';
           	    $content.= '&auml;ndern';
              $content.= '</th>';

				    }
				    else
				    {
				    	$content.= '<td>&nbsp;</td>';
				    }
	                    $content.= '</tr>';
				$content.='</table></td></tr>';

                } // Ende Moodlekurse in einer Schleife anzeigen.
                $content.= '</table>';
		$content.='</div>';
            return  $content;
        }


// ***********************************************************************************************

//      String auf Laenge abschneiden

// ***********************************************************************************************

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



?>
