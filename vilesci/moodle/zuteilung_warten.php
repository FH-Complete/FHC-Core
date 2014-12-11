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
   // Ohne einer Moodlekurs ID hier beenden
   $mdl_course_id=(isset($_REQUEST['mdl_course_id'])?trim($_REQUEST['mdl_course_id']):'');
   $entfernen=(isset($_REQUEST['entfernen'])?trim($_REQUEST['entfernen']):'');
   if (empty($mdl_course_id) && !$entfernen)
	exit();

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

	   $entfernen=(isset($_REQUEST['entfernen'])?trim($_REQUEST['entfernen']):'');
	   if (!empty($entfernen))
	   {
		$lehrveranstaltung_id=(isset($_REQUEST['lehrveranstaltung_id']) && !empty($_REQUEST['lehrveranstaltung_id'])?trim($_REQUEST['lehrveranstaltung_id']):null);
    		$lehreinheit_id=(isset($_REQUEST['lehreinheit_id']) && !empty($_REQUEST['lehreinheit_id'])?$_REQUEST['lehreinheit_id']:null);
	       if(!$objMoodle->delete_vilesci($mdl_course_id,$lehrveranstaltung_id,$lehreinheit_id))
		{
	             exit('Eintrag wurde in Lehre nicht gel&ouml;scht! '.$objMoodle->errormsg);
	       }
	       exit('Eintrag in Lehre gel&ouml;scht! '.$objMoodle->errormsg);
	   }
		  
		  
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
#var_dump($_REQUEST);

        // AusgabeStream

        $content='';
        $errormsg=array();

// ***********************************************************************************************
// POST oder GET Parameter einlesen
// ***********************************************************************************************
	// @$mdl_course_id Moodle - ID suche
       $mdl_course_id=(isset($_REQUEST['mdl_course_id'])?trim($_REQUEST['mdl_course_id']):'');
       $studSem = (isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:''); 

	//---------------------------------------------------------------------------
	// Pruefen vor dem Datenlesen ob die Verarbeitung bereits erfolgen muss
   	$bNeuAufbau=(isset($_REQUEST['aendern'])?trim($_REQUEST['aendern']):false);
	if ($mdl_course_id && $bNeuAufbau)
	{
	    	$bWartung=(isset($_REQUEST['aenderung'])?trim($_REQUEST['aenderung']):false);
	    	$bKopieren=(isset($_REQUEST['kopieren'])?trim($_REQUEST['kopieren']):false);
			if ($bWartung || $bKopieren)
				moodlekurswartung($mdl_course_id,$errormsg );

	}

	//---------------------------------------------------------------------------
	// Check Moodle
       $mdl_course_stat='';

       if(!$objMoodle->getAllMoodleVariant($mdl_course_id,'',$studSem,'','','',false,false,false))
		{
             die('Moodle-Kurs '.$objMoodle->mdl_course_id.' wurde in Lehre nicht gefunden! '.$objMoodle->errormsg);
       }
	// es wurden Vilescidaten gefunden
		if(isset($objMoodle->result) && isset($objMoodle->result[0]))
      	{
			$new=false;
			$mdl_course_stat='*';
			$moodle_id=$objMoodle->result[0]->moodle_id;
			$studiengang_kz=$objMoodle->result[0]->lehrveranstaltung_studiengang_kz;
			$studiensemester_kurzbz=$objMoodle->result[0]->studiensemester_kurzbz;
			$semester=$objMoodle->result[0]->lehrveranstaltung_semester;
#moodle_lehrveranstaltung_id

			$lehrveranstaltung_id=$objMoodle->result[0]->lehrveranstaltung_id;
			$lehreinheit_id=$objMoodle->result[0]->lehreinheit_id;
			$gruppen=$objMoodle->result[0]->gruppen;
			$bezeichnung=$objMoodle->result[0]->mdl_fullname;
			$kurzbezeichnung=$objMoodle->result[0]->mdl_shortname;
			$lehrveranstaltung_id_moodle=$objMoodle->result[0]->moodle_lehrveranstaltung_id;
        }
	// es wurden Moodledaten gefunden
        else if ($objMoodle->load($mdl_course_id))
        {
			$objMoodle->result=array();
			$objMoodle->result[0]= new stdClass(); 
			$objMoodle->result[0]->fullname=$objMoodle->mdl_fullname;
			$objMoodle->result[0]->shortname=$objMoodle->mdl_shortname;

			$new=true;
			$mdl_course_stat='+';
			$errormsg[]='Neuzuteilung zu Moodlekurs '.$objMoodle->mdl_course_id.' m&ouml;glich';
			$moodle_id='?';
			$studiengang_kz=227;
			$studiensemester_kurzbz=$stsem_aktuell;
			$semester=1;
			$lehrveranstaltung_id='';
			$lehreinheit_id='';
			$gruppen=false;
			$bezeichnung=$objMoodle->result[0]->fullname;
			$kurzbezeichnung=$objMoodle->result[0]->shortname;
			$lehrveranstaltung_id_moodle=false;
		}
       else
       {
			die('Moodle-Kurs '.$objMoodle->mdl_course_id.' wurde nicht gefunden! '.$objMoodle->errormsg);
       }


// ***********************************************************************************************
// Restliche POST oder GET Parameter der Dateneingabe einlesen
// ***********************************************************************************************
	// @bDebug Anzeige der xml-rfc Daten moegliche Stufen sind 0,1,2,3
       	$bDebug= (isset($_REQUEST['debug'])?$_REQUEST['debug']:0);

	   	$aendern_studiensemester_kurzbz=(isset($_REQUEST['aendern_studiensemester_kurzbz'])?trim($_REQUEST['aendern_studiensemester_kurzbz']):$studiensemester_kurzbz);
       	$aendern_studiengang_kz=(isset($_REQUEST['aendern_studiengang_kz'])?trim($_REQUEST['aendern_studiengang_kz']):$studiengang_kz);
       	$aendern_semester=(isset($_REQUEST['aendern_semester'])?trim($_REQUEST['aendern_semester']):$semester);

		$sel_lehrveranstaltung_id=(isset($_REQUEST['sel_lehrveranstaltung_id'])?trim($_REQUEST['sel_lehrveranstaltung_id']):$lehrveranstaltung_id);
		$aendern_lehrveranstaltung_id=(isset($_REQUEST['aendern_lehrveranstaltung_id']) && !empty($_REQUEST['aendern_lehrveranstaltung_id'])?trim($_REQUEST['aendern_lehrveranstaltung_id']):$sel_lehrveranstaltung_id);
		
    		$aendern_lehreinheit_id=(isset($_REQUEST['aendern_lehreinheit_id'])?$_REQUEST['aendern_lehreinheit_id']:(isset($_REQUEST['aendern_studiensemester_kurzbz'])?'':$lehreinheit_id));


		$aendern_bezeichnung=(isset($_REQUEST['aendern_bezeichnung'])?trim($_REQUEST['aendern_bezeichnung']):$bezeichnung);
		$aendern_kurzbezeichnung=(isset($_REQUEST['aendern_kurzbezeichnung'])?trim($_REQUEST['aendern_kurzbezeichnung']):$kurzbezeichnung);
		$aendern_gruppen=(isset($_REQUEST['aendern_gruppen']) && !empty($_REQUEST['aendern_gruppen'])?true:(isset($_REQUEST['aendern_gruppen'])?1:$gruppen));

// ***********************************************************************************************
//      HTML Auswahlfelder (Teil 1)
// ***********************************************************************************************

	//---------------------------------------------------------------------------
	 // Fuer bestehende Vilescidaten wird eine Detailinformation angezeigt
        if (!$new )
        {
		// Header
   			$content.='<h2>Moodle Kurs '.$objMoodle->result[0]->mdl_course_id.($aendern_lehrveranstaltung_id?' zur Lehrveranstaltung ':' zu Lehreinheiten ').'&nbsp;-&nbsp;'.$objMoodle->result[0]->mdl_fullname .'&nbsp;-&nbsp;'.$objMoodle->result[0]->mdl_shortname.'</h2>';
        }
		else
		{
			$content.='<h2>Moodle Kurs Neuzuteilung '.$mdl_course_id.'&nbsp;-&nbsp;'.$objMoodle->result[0]->fullname.'&nbsp;-&nbsp;'.$objMoodle->result[0]->shortname.'</h2>';
		}
// ***********************************************************************************************
//      HTML Listenanzeige (Teil 2) Aenderungsdaten
// ***********************************************************************************************
        // FormName erzeugen
        $cFormName='workMoodleCurse'.$mdl_course_id;
        $content.='
                <form accept-charset="UTF-8" name="'.$cFormName.'"  method="POST" target="_self" action="'.$_SERVER['PHP_SELF'].'" >
                        <table class="liste">
						<tr><td>&nbsp;</td></tr>
                        <tr>
                                <td>&nbsp;Studiensemester&nbsp;</td>
                                <td>&nbsp;Studiengang&nbsp;</td>
                                <td>&nbsp;Semster&nbsp;</td>
				   				 <td>&nbsp;</td>
                        </tr>
                   <tr>';

        //---------------------------------------------------------------------------
        // Studiensemester public.tbl_studiensemester_kurzbz
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.aendern_bezeichnung.value=\'\';document.'.$cFormName.'.aendern_lehrveranstaltung_id.checked=false;uncheckLE();generateLEText();document.'.$cFormName.'.submit();" name="aendern_studiensemester_kurzbz">';
                if ($stsem->getAll())
                {
                        foreach ($stsem->studiensemester as $row)
                        {
                                $content.='<option value="'.$row->studiensemester_kurzbz.'" '.(("$aendern_studiensemester_kurzbz"=="$row->studiensemester_kurzbz")?' selected="selected" ':'').'>&nbsp;'.$row->studiensemester_kurzbz.'&nbsp;</option>';
                        }
                }
                else
                {
                        $errormsg[]='Studiensemester wurden nicht gefunden! '.$stsem->errormsg;
                }
                $content.='</select></td>';

        //---------------------------------------------------------------------------
        // Studiengang public.tbl_studiengang_kz
            $content.='<td valign="top"><select onchange="document.'.$cFormName.'.aendern_bezeichnung.value=\'\';document.'.$cFormName.'.aendern_lehrveranstaltung_id.checked=false;uncheckLE();generateLEText();document.'.$cFormName.'.submit();" name="aendern_studiengang_kz">';
            $aendern_studiengang_name='';
	         if ($stg_obj->getAll('typ, kurzbz',true))
                {
                        $max_semester=0;
                        $arrStudiengang='';
                        foreach ($stg_obj->result as $row)
                        {
	                            if (empty($aendern_studiengang_kz) )
                                {
                                        $aendern_studiengang_kz=$row->studiengang_kz;
                                        $aendern_semester=1;
                                }

                                if ($aendern_studiengang_kz==$row->studiengang_kz)
                                {
				    				$aendern_studiengang_name=$row->kuerzel;
                                    $arrStudiengang=$row;
                                    $max_semester=$row->max_semester;
                                }
                        $content.='<option title="'.$row->kuerzel.'" value="'.$row->studiengang_kz.'" '.(("$aendern_studiengang_kz"=="$row->studiengang_kz")?' selected="selected" ':'').'>&nbsp;'.$row->kuerzel.'&nbsp;</option>';
                        }
                }
                else
                {
                        $content.='<option value="" >&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
                        $errormsg[]='Studiengang wurde nicht gefunden! '.$stg_obj->errormsg;
                }
                $content.='</select></td>';

        //---------------------------------------------------------------------------
        // Semster public.tbl_studiengang_kz - max Semester des Selektierten Studiengangs
		  $content.='<td valign="top"><select onchange="document.'.$cFormName.'.aendern_bezeichnung.value=\'\';document.'.$cFormName.'.aendern_lehrveranstaltung_id.checked=false;uncheckLE();generateLEText();document.'.$cFormName.'.submit();" name="aendern_semester">';
                if ($aendern_studiengang_kz!='')
                {
                        for($i=0;$i<=$max_semester;$i++)
                        {
                                $content.='<option value="'.($i).'" '.("$aendern_semester"=="$i"?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
                        }
              }
              $content.='</select></td>';
		$content.='<td valign="top">';

		$content.='<table><tr>';

        //---------------------------------------------------------------------------
        // Lehrveranstaltungen
		$content.='<th valign="top">&nbsp;Lehrveranstaltung&nbsp;</th>';
        $content.='<td><select onchange="document.'.$cFormName.'.aendern_bezeichnung.value=\'\';document.'.$cFormName.'.aendern_lehrveranstaltung_id.value=this.value;document.'.$cFormName.'.aendern_lehrveranstaltung_id.checked=false;uncheckLE();generateLEText();document.'.$cFormName.'.submit();" name="sel_lehrveranstaltung_id">';
	$lv_bez='';
       $lv_kurz_bez='';
	$lv_obj->lehrveranstaltungen=array();
       if ($lv_obj->load_lva_le($aendern_studiengang_kz,$aendern_studiensemester_kurzbz, $aendern_semester,null,null,null,'bezeichnung'))
       {
                    foreach ($lv_obj->lehrveranstaltungen as $row)
                    {

						if (empty($sel_lehrveranstaltung_id))
							$sel_lehrveranstaltung_id=$row->lehrveranstaltung_id;

						if ("$sel_lehrveranstaltung_id"=="$row->lehrveranstaltung_id")
						{
							$lv_kurz_bez=trim($row->kurzbz);
							if (!$aendern_bezeichnung)
								$aendern_bezeichnung=$row->bezeichnung;
						}

	                    $content.='<option value="'.$row->lehrveranstaltung_id.'" '.("$sel_lehrveranstaltung_id"=="$row->lehrveranstaltung_id"?' selected="selected" ':'').'>&nbsp;'.CutString($row->bezeichnung, 35).'&nbsp;'.($row->kurzbz?CutString($row->kurzbz,7).', ':'').'&nbsp;'.$row->lehrveranstaltung_id.($row->lehrform_kurzbz?CutString(', '.$row->lehrform_kurzbz,5):'').'</option>';
                    }
                }
                else
                {
                        $content.='<option value="" >&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
                        $errormsg[]='Lehrveranstaltung wurde nicht gefunden! '.$lv_obj->errormsg;
                }
                $content.='</select></td>';

		$aendern_lehrveranstaltung_id=(isset($_REQUEST['aendern_lehrveranstaltung_id']) && !empty($_REQUEST['aendern_lehrveranstaltung_id'])?trim($_REQUEST['aendern_lehrveranstaltung_id']):$sel_lehrveranstaltung_id);
		
		$bChecked=((!isset($_REQUEST['aendern_lehrveranstaltung_id']) && isset($objMoodle->result[0]->moodle_lehrveranstaltung_id) && $objMoodle->result[0]->moodle_lehrveranstaltung_id) || (isset($_REQUEST['aendern_lehrveranstaltung_id']) && $aendern_lehrveranstaltung_id)?true:false);
		
		$content.='<th  '.($bChecked?' class="error" ':'').' valign="top" title="'.$lv_kurz_bez.'" valign="top"><input onchange="if(this.checked) {uncheckLE();};generateLEText();" name="aendern_lehrveranstaltung_id" value="'.$aendern_lehrveranstaltung_id.'" type="Checkbox" '.($bChecked?' checked="checked" ':'').'>&nbsp;Moodle Kurs f&uuml;r gesamte LV</th>';
	$content.='</tr>';



        //---------------------------------------------------------------------------
        // Lehreinheit
	$content.='<tr>';
				$content.='<th valign="top">&nbsp;Lehreinheiten&nbsp;</th>';
			 	$content.='<td valign="top"><table>';

	#			$content.='<tr><th>Moodlekurs zu LV '.$aendern_studiensemester_kurzbz.'/'.$aendern_lehrveranstaltung_id.' </th></tr>';

				$le_obj->lehreinheiten=array();
				$le_obj->load_lehreinheiten($sel_lehrveranstaltung_id, $aendern_studiensemester_kurzbz);

				if (!is_array($le_obj->lehreinheiten) || count($le_obj->lehreinheiten)<1)
				{
					$content.='<tr>';
						$content.='<td valign="top" class="error">Achtung ! Es gibt keine Lehreinheit f&uuml;r '.$aendern_studiensemester_kurzbz.'-'.$aendern_bezeichnung.' '.$lv_kurz_bez.'</td>';
					$content.='</tr>';
				}
				else
				{
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
										
									$le_gefunden=false;
#									$bChecked=((!isset($_REQUEST['aendern_lehrveranstaltung_id']) && isset($objMoodle->result[0]->moodle_lehrveranstaltung_id) && $objMoodle->result[0]->moodle_lehrveranstaltung_id) || (isset($_REQUEST['aendern_lehrveranstaltung_id']) && $aendern_lehrveranstaltung_id)?false:true);
									if (isset($_REQUEST['aendern_lehrveranstaltung_id']) && !$aendern_lehrveranstaltung_id && isset($aendern_lehreinheit_id) && is_array($aendern_lehreinheit_id))
									{
										reset($aendern_lehreinheit_id);
										for ($ii=0;$ii<count($aendern_lehreinheit_id);$ii++)
										{
											if (isset($aendern_lehreinheit_id[$ii]) && $aendern_lehreinheit_id[$ii]==$row->lehreinheit_id)
												$le_gefunden=true;
										}
									}
									else if ($new || (isset($_REQUEST['aendern_lehrveranstaltung_id']) && $aendern_lehrveranstaltung_id) )
									{
										$le_gefunden=false;
									}
									else
									{
										reset($objMoodle->result);
										for ($ii=0;$ii<count($objMoodle->result);$ii++)
										{
											if ($objMoodle->result[$ii]->moodle_lehreinheit_id==$row->lehreinheit_id)
												$le_gefunden=true;
										}
									}

									$content.='<tr '.($le_gefunden?' class="error" ':' ').' >';
		                            // LE Text
										$content.='<td>'.$row->lehrform_kurzbz.'&nbsp;</td><td>'.$gruppen.'&nbsp;</td><td>'.$row->lehreinheit_id.'&nbsp;</td>';
		                            // LE Checkbox
									$content.='<td><input '.($le_gefunden?' checked="checked" ':' ').'  onchange="if(this.checked) {document.'.$cFormName.'.aendern_lehrveranstaltung_id.checked=false;};generateLEText();"  id="aendern_lehreinheit_id[]" name="aendern_lehreinheit_id[]" value="'.$row->lehreinheit_id.'" type="Checkbox">&nbsp;'.$lektoren.'</td>';
								$content.='</tr>';
								}
						}	
			$bGefundenLehreinheit=(count($le_obj->lehreinheiten)?true:false);
						
			$content.='<tr><td>&nbsp;</td></tr></table></td>';
		$content.='</tr>';
		$content.='</table></td></tr>';
        //---------------------------------------------------------------------------

        //---------------------------------------------------------------------------
        // ---- Submitknopf
		$content.='<tr><td colspan="4"><table>
						<tr>

						<th align="left">Moodle :
						<br />Kursbez.:&nbsp;<input name="aendern_bezeichnung" maxlength="254" size="60" type="Text" value="'. $aendern_bezeichnung.'">
						<br />Kurzbez.:&nbsp;<input name="aendern_kurzbezeichnung" maxlength="254" size="60" type="Text" value="'. $aendern_kurzbezeichnung.'">
						</th>
						<td>&nbsp;</td>
						<th>Gruppen übernehmen: <input type="checkbox" value="1" name="aendern_gruppen" '.($aendern_gruppen?' checked="checked" ':'').' ><br /></th>
					    <th>
								<input style="display:none" type="text" name="mdl_course_id" value="'.$mdl_course_id.'">
              			      	<input style="display:none" type="text" name="aendern" value="aendern" />
	                          	<input style="display:none" type="text" name="debug" value="'.$bDebug.'" />


		  		              <input style="padding: 2px 20px 2px 20px;" name="aenderung" type="submit" value="neu zuteilen">
		  		              <input style="padding: 2px 20px 2px 20px;" name="kopieren" type="submit" value="hinzuf&uuml;gen">
					    </th>';
		    if (!$new )
        	{
		// Header
				$content.='<td><table border="0">';
					$content.='<tr id="aktuell_on" onclick="this.className=\'ausblenden\';document.getElementById(\'aktuell\').className=\'einblenden\';document.getElementById(\'aktuell_off\').className=\'einblenden\';" class="einblenden"><td><img height="15" src="../../skin/images/bullet_arrow_right.png" border="0" title="Detailansicht" alt="bullet_arrow_down.png" /></td><td><b>anzeigen</b> aktuelle '.($objMoodle->result[0]->moodle_lehrveranstaltung_id?' Lehrveranstaltung ':' Lehreinheiten').'</td></tr>';
					$content.='<tr id="aktuell_off" onclick="this.className=\'ausblenden\';document.getElementById(\'aktuell\').className=\'ausblenden\';document.getElementById(\'aktuell_on\').className=\'einblenden\';" class="ausblenden"><td><img height="15" src="../../skin/images/bullet_arrow_down.png" border="0" title="Detailansicht" alt="bullet_arrow_down.png" /></td><td><b>ausblenden</b> ktuelle '.($objMoodle->result[0]->moodle_lehrveranstaltung_id?' Lehrveranstaltung ':' Lehreinheiten').'</td></tr>';
				$content.='</table></td>';
			}							
						
		$content.='</tr></table></td>';
        $content.='</tr></table>
        </form>
        <hr>';
	//---------------------------------------------------------------------------
	 // Fuer bestehende Vilescidaten wird eine Detailinformation angezeigt
        if (!$new )
        {
			$content.='<table border="0" id="aktuell" class="ausblenden" >';
		//---------------------------------------------------------------------------
		// @studiengang_kz Studiengang
			if ($studiengang_kz)
			{
			            if ($stg_obj->load($studiengang_kz))
			            {
							$content.='<tr>';
									$content.='<th>Studiengang</th>
										<td rowspan="5">&nbsp;&nbsp;&nbsp;</td>
										<td>
										<table class="liste" summary="Studiengang">
											<tr>
												<td>'.(isset($objMoodle->result[0])  && isset($objMoodle->result[0]->studiensemester_kurzbz)?$objMoodle->result[0]->studiensemester_kurzbz:'').'&nbsp; </td>
												<td>'.$stg_obj->kuerzel.'&nbsp;</td>
												<td>'.$stg_obj->bezeichnung.'&nbsp;</td>
												<td>('.$stg_obj->kurzbzlang.')&nbsp;</td>
												<td>ID&nbsp;'.$studiengang_kz.'&nbsp;</td>
											</tr>
										</table>
									</td>';
							$content.='</tr>';
			            }
			            else
			           {
			               $errormsg[]='Studieng '.$studiengang_kz.' wurden nicht gefunden! '.$stg_obj->errormsg;
			           }
			}
			else
			{
				$content.='<tr><td>-</td></tr>';
			}
			$content.='<tr>';
				$content.='<th valign="top">Lehrveranstaltung</th>
						<td valign="top">'.(isset($objMoodle->result[0])  && isset($objMoodle->result[0]->lehrveranstaltung_bezeichnung)?$objMoodle->result[0]->lehrveranstaltung_bezeichnung.'&nbsp;&nbsp;Kurzbz:&nbsp;'.$objMoodle->result[0]->lehrveranstaltung_kurzbz.'&nbsp;,&nbsp;Lehrform Kurzbz:'.($objMoodle->result[0]->lehrveranstaltung_lehrform_kurzbz?$objMoodle->result[0]->lehrveranstaltung_lehrform_kurzbz:' - ').',&nbsp;ID&nbsp;'.$objMoodle->result[0]->lehrveranstaltung_id.'&nbsp;':' - ').'</td>
						<td valign="top" '.($objMoodle->result[0]->moodle_lehrveranstaltung_id?' class="error" ':'').'><input disabled name="lehrveranstaltung_id" value="'.$objMoodle->result[0]->lehrveranstaltung_id.'" type="Checkbox" '.($objMoodle->result[0]->moodle_lehrveranstaltung_id?' checked="checked" ':'').'>&nbsp;</td>
						';
			$content.='<th valign="top">Lehreinheiten</th>';
			$content.='<td><table>';

			$le_obj->lehreinheiten=array(); // Init
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
				//Lektoren laden
					$lektoren='';
					$lehreinheitmitarbeiter = new lehreinheitmitarbeiter();
					$lehreinheitmitarbeiter->getLehreinheitmitarbeiter($row->lehreinheit_id);
					foreach ($lehreinheitmitarbeiter->lehreinheitmitarbeiter as $ma)
					{
						$lektoren.= ($lektoren?',':'').'&nbsp;'.$ma->mitarbeiter_uid;
					}			
						$le_gefunden=false;
						reset($objMoodle->result);
						for ($ii=0;$ii<count($objMoodle->result);$ii++)
						{
								if ($objMoodle->result[$ii]->moodle_lehreinheit_id==$row->lehreinheit_id)
									$le_gefunden=true;
						}
					$content.='<tr '.($le_gefunden?' class="error" ':' ').'>';
                   		$content.='<td>'.$row->lehrform_kurzbz.'&nbsp;</td><td>'.$gruppen.'&nbsp;</td><td>ID&nbsp;'.$row->lehreinheit_id.'&nbsp;</td>';
						$content.='<td valign="top"><input '.($le_gefunden?' checked="checked" ':'').' id="lehreinheit_id" disabled name="lehreinheit_id[]" value="'.$row->lehreinheit_id.'" type="Checkbox" >&nbsp;'.$lektoren.'</td>';
				 $content.='</tr>';
			}
				$content.='</table></td>';
			$content.='</tr>';
			$content.='</table>';
        }
		
// ***********************************************************************************************
//      HTML Header und Foot zum Content (Ausgabestring) hinzufuegen, und wartung
// ***********************************************************************************************
        $content='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
        <head>
                <title>Moodle - Kurszuteilungverwalten</title>
                <base target="main">
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">

		<script type="text/javascript" language="JavaScript">
			<!-- Begin
			function generateLEText()
			{
				document.'.$cFormName.'.aendern_kurzbezeichnung.value=document.'.$cFormName.'.aendern_studiensemester_kurzbz.value+"-'.(isset($aendern_studiengang_name)?$aendern_studiengang_name:'???').'-"+document.'.$cFormName.'.aendern_semester.value+"'.($lv_kurz_bez?'-'.$lv_kurz_bez:'').'";
				if ("'.($bGefundenLehreinheit?'X':'').'"=="" || document.'.$cFormName.'.aendern_lehrveranstaltung_id.checked==true)
				{
					return;
				}		

				var bez="";
				document.'.$cFormName.'.aendern_kurzbezeichnung.bez;

				var no;
				var m=document.'.$cFormName.';
				if (no=m.elements["aendern_lehreinheit_id[]"].length)
				{
					for(i=0;i<no;i++)
					{
						if (m.elements["aendern_lehreinheit_id[]"][i].checked==true)
						{
							if (bez!="")
							{
								bez=bez+"/";
							}
							bez=bez+m.elements["aendern_lehreinheit_id[]"][i].value;
						}
					}
				}
				if (bez!="")
				{
					document.'.$cFormName.'.aendern_kurzbezeichnung.value=document.'.$cFormName.'.aendern_studiensemester_kurzbz.value+"-'.(isset($aendern_studiengang_name)?$aendern_studiengang_name:'???').'-"+document.'.$cFormName.'.aendern_semester.value+"-"+bez;
				}

			}

			function uncheckLE()
			{
				'.($bGefundenLehreinheit?'':' return; '). '

				var no;
				var m=document.'.$cFormName.';
				if (no=m.elements["aendern_lehreinheit_id[]"].length)
				{
					for(i=0;i<no;i++)
					{
						m.elements["aendern_lehreinheit_id[]"][i].checked=false;
					}
				}
			}

			//-->
		</script>
        </head>
        <body class="background_main">
        <!-- MoodleKurs Content Start -->
                '.$content.'<p class="error">'.implode('<br>',$errormsg).'</p>
        <br />
        </body>
                </html>';
        echo $content;
        exit;

// ***********************************************************************************************
//      Submit - Datenverarbeiten
// ***********************************************************************************************
	function moodlekurswartung($mdl_course_id,&$errormsg)
	{
		if (!$user=get_uid())
		{
			$errormsg[]='Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden !';
			return false;
		}		
		
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

	       	// alter Pfad des Moodle Kurses
	       	$oldPath = $objMoodle->getPath($mdl_course_id); 
	        	 
	    	$bWartung=(isset($_REQUEST['aenderung']) && !empty($_REQUEST['aenderung'])?true:false);
	    	$bKopieren=(isset($_REQUEST['kopieren']) && !empty($_REQUEST['kopieren'])?true:false);
			$aendern_studiensemester_kurzbz=(isset($_REQUEST['aendern_studiensemester_kurzbz'])?trim($_REQUEST['aendern_studiensemester_kurzbz']):'');
	    	$aendern_studiengang_kz=(isset($_REQUEST['aendern_studiengang_kz'])?trim($_REQUEST['aendern_studiengang_kz']):'');
	    	$aendern_semester=(isset($_REQUEST['aendern_semester'])?trim($_REQUEST['aendern_semester']):'');

			$sel_lehrveranstaltung_id=(isset($_REQUEST['sel_lehrveranstaltung_id'])?trim($_REQUEST['sel_lehrveranstaltung_id']):$lehrveranstaltung_id);
			$aendern_lehrveranstaltung_id=(isset($_REQUEST['aendern_lehrveranstaltung_id']) && !empty($_REQUEST['aendern_lehrveranstaltung_id'])?trim($_REQUEST['aendern_lehrveranstaltung_id']):$sel_lehrveranstaltung_id);

	    	$aendern_lehreinheit_id=(isset($_REQUEST['aendern_lehreinheit_id'])?$_REQUEST['aendern_lehreinheit_id']:(isset($_REQUEST['aendern_studiensemester_kurzbz'])?'':''));

			$aendern_bezeichnung=(isset($_REQUEST['aendern_bezeichnung'])?trim($_REQUEST['aendern_bezeichnung']):'');
			$aendern_kurzbezeichnung=(isset($_REQUEST['aendern_kurzbezeichnung'])?trim($_REQUEST['aendern_kurzbezeichnung']):'');
			$aendern_gruppen=(isset($_REQUEST['aendern_gruppen']) && !empty($_REQUEST['aendern_gruppen'])?true:(isset($_REQUEST['aendern_gruppen'])?1:0));


		//  Original Moodlekurs lesen
	   if(!$objMoodle->getAllMoodleVariant($mdl_course_id,'','','','','',false,false,false))
	   {
                    die('Moodle-Kurs '.$objMoodle->mdl_course_id.' wurde in Lehre nicht gefunden! '.$objMoodle->errormsg);
       }
		// Kurs wurde gefunden
		if(isset($objMoodle->result) && isset($objMoodle->result[0]))
	    {
			$new_lehre_moodle_kurs=false;
			$objMoodle->new=false;
	    }
		// Es gibt im Moodle den Kurs
	    else if ($objMoodle->load($mdl_course_id) && !$bKopieren)
	    {
			$new_lehre_moodle_kurs=true;
			$objMoodle->new=true; // Datensatz anlegen
		}
		else
		{
	           die('Moodle-Kurs '.$mdl_course_id.' wurde nicht gefunden! '.$objMoodle->errormsg);
		}
		
		
		if ($bKopieren)
		{
			if ($new_lehre_moodle_kurs)
			{
	             	  die('nur bestehende Moodle-Kurse k&ouml;nnen kopiert werden ');
			}
			$objMoodle->new=true; // Datensatz anlegen
		}

#echo $aendern_lehrveranstaltung_id;		
#var_dump($aendern_lehreinheit_id);

		// Lehreinheiten 
		if ((!is_array($aendern_lehreinheit_id) && !empty($aendern_lehreinheit_id))
		 || (is_array($aendern_lehreinheit_id) && count($aendern_lehreinheit_id)>0) )
		{
			$objMoodle->lehrveranstaltung_id=null;
			$objMoodle->lehreinheit_id=$aendern_lehreinheit_id;
		 }
		 // Lehrveranstaltung
		else if ($aendern_lehrveranstaltung_id)
		{
			$objMoodle->lehrveranstaltung_id=$aendern_lehrveranstaltung_id;
			$objMoodle->lehreinheit_id=null;
		}
		 else
		 {
	  	    $errormsg[]='LV oder LE wurde nicht ausgew&auml;hlt!';
			return false;
		 }

		$objMoodle->mdl_course_id=$mdl_course_id;

		$objMoodle->studiensemester_kurzbz=$aendern_studiensemester_kurzbz;

		// Kurztext des Moodlekurses neu ermitteln
		$objMoodle->mdl_fullname=$aendern_bezeichnung;
		$objMoodle->mdl_shortname=$aendern_kurzbezeichnung;
		$objMoodle->insertamum=(!$new_lehre_moodle_kurs && isset($objMoodle->result[0]->insertamum)?$objMoodle->result[0]->insertamum:date('Y-m-d H:i:s'));
		$objMoodle->insertvon=(!$new_lehre_moodle_kurs && isset($objMoodle->result[0]->insertvon)?$objMoodle->result[0]->insertvon:$user);
		$objMoodle->gruppen=($aendern_gruppen?1:0);


		if (!$objMoodle->update_vilesci())
		{
         	$errormsg[]='Fehler Vilesci Moodle-Kurs '.$mdl_course_id.' '.$objMoodle->result[0]->mdl_fullname.' zugeordnet '.$objMoodle->errormsg;
			return false;
		}

		$errormsg[]='Vilesci Moodle-Kurs '.$mdl_course_id.' '.$aendern_bezeichnung.' '.$aendern_kurzbezeichnung.($objMoodle->new?' angelegt ':' geaendert ').$objMoodle->errormsg;
		if ($bKopieren || $new_lehre_moodle_kurs)
			return true;

		// Moodle aenderungen nur bei Wechsel der LV

		
		
			if ( (($aendern_lehrveranstaltung_id && isset($objMoodle->result[0]->lehrveranstaltung_id) && $objMoodle->result[0]->lehrveranstaltung_id!=$aendern_lehrveranstaltung_id)
			|| ( $aendern_bezeichnung!=$objMoodle->result[0]->mdl_fullname || $aendern_kurzbezeichnung!=$objMoodle->result[0]->mdl_shortname )))
			{
				if (is_array($objMoodle->lehreinheit_id))
					$objMoodle->lehreinheit_id=$objMoodle->lehreinheit_id[0];

				if (!$objMoodle->update_moodle($oldPath))
				{
	       	     	$errormsg[]='Fehler Moodle-Kurs aendern '.$mdl_course_id.' '.$aendern_bezeichnung.' '.$aendern_kurzbezeichnung.' '.$objMoodle->errormsg;
					return false;
				}
	       		$errormsg[]='Moodle-Kurs '.$mdl_course_id.' geaendert auf '.$aendern_bezeichnung.' '.$aendern_kurzbezeichnung.' '.$objMoodle->errormsg;
			}
		return true;
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
