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
/*
 * Script zum Kopieren einer Kreuzerltool Uebung zu einer anderen Lehreinheit
 * (zB fuer die Uebernahme der Uebungen aus dem Vorjahr)
 */
	require_once('../../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');

// ---------------- Standart Include Dateien einbinden
    require_once('../../../../include/studiengang.class.php');
	require_once('../../../../include/studiensemester.class.php');
    require_once('../../../../include/lehrveranstaltung.class.php');
    require_once('../../../../include/lehreinheit.class.php');
    require_once('../../../../include/lehreinheitgruppe.class.php');
    require_once('../../../../include/lehreinheitmitarbeiter.class.php');


	require_once('../../../../include/functions.inc.php');
	require_once('../../../../include/benutzerberechtigung.class.php');
	require_once('../../../../include/uebung.class.php');
	require_once('../../../../include/beispiel.class.php');
	require_once('../../../../include/datum.class.php');

	
// ***********************************************************************************************
//      Datenbankverbindungen zu Classen
// ***********************************************************************************************

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
	
	
##echo "<hr> GET <br> ";	
#var_dump($_GET);	
#echo "<hr> POST <br> ";
#var_dump($_POST);
	
$errormsg=array();
$error=0;

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Rechte f&uuml;r diese Seite');

// @studiensemester_kurzbz Studiensemester WSxxxx SSxxxx
        $studiensemester_kurzbz=(isset($_GET['studiensemester_kurzbz'])?trim($_GET['studiensemester_kurzbz']):$stsem_aktuell);
// $lehrveranstaltung_id Lehrveranstaltung zur Lehreinheit
	    $lehrveranstaltung_id=(isset($_GET['lvid'])?trim($_GET['lvid']):(isset($_GET['lehrveranstaltung_id'])?trim($_GET['lehrveranstaltung_id']):''));
// @$studiengang_kz Studiengang
        $studiengang_kz=(isset($_GET['studiengang_kz'])?trim($_GET['studiengang_kz']):227);
// @$semester Semester des Studienganges
        $semester=(isset($_GET['semester'])?trim($_GET['semester']):1);
// @$lehreinheit_id Lehreinheit
        $lehreinheit_id=(isset($_GET['leid'])?trim($_GET['leid']):(isset($_GET['lehreinheit_id'])?trim($_GET['lehreinheit_id']):''));
		
		
        $uebung_id_source=(isset($_GET['uebung_id_source'])?$_GET['uebung_id_source']:(isset($_GET['uebung_id'])?trim($_GET['uebung_id']):''));

// ------------- Target
// @studiensemester_kurzbz Studiensemester WSxxxx SSxxxx
        $studiensemester_kurzbz_target=(isset($_GET['studiensemester_kurzbz_target'])?trim($_GET['studiensemester_kurzbz_target']):$stsem_aktuell);
// $lehrveranstaltung_id Lehrveranstaltung zur Lehreinheit
	    $lehrveranstaltung_id_target=(isset($_GET['lvid_target'])?trim($_GET['lvid_target']):(isset($_GET['lehrveranstaltung_id_target'])?trim($_GET['lehrveranstaltung_id_target']):''));
// @$studiengang_kz Studiengang
        $studiengang_kz_target=(isset($_GET['studiengang_kz_target'])?trim($_GET['studiengang_kz_target']):227);
// @$semester Semester des Studienganges
        $semester_target=(isset($_GET['semester_target'])?trim($_GET['semester_target']):$semester);
// @$lehreinheit_id_target Lehreinheit
        $lehreinheit_id_target=(isset($_GET['lehreinheit_id_target'])?trim($_GET['lehreinheit_id_target']):'');
// @$lehreinheit_id_sel Lehreinheit
       $lehreinheit_id_sel=(isset($_GET['lehreinheit_id_sel'])?trim($_GET['lehreinheit_id_sel']):'');
// @$lehreinheit_id_sel Lehreinheit
    $uebung_id_sel=(isset($_GET['uebung_id_sel'])?trim($_GET['uebung_id_sel']):'');

if ($uebung_id_sel!='' && !is_array($uebung_id_sel))
	$uebung_id_sel=array($uebung_id_sel);
		
if ($uebung_id_source!='' && !is_array($uebung_id_source))
	$uebung_id_source=array($uebung_id_source);

#var_dump($uebung_id_source);
	
if (!empty($lehreinheit_id_sel))
{


		if ($le_obj->load($lehreinheit_id_sel))
		{
			$studiensemester_kurzbz=$le_obj->studiensemester_kurzbz;
			$lehrveranstaltung_id=$le_obj->lehrveranstaltung_id;
			$lehreinheit_id=$lehreinheit_id_sel;
			if ($lv_obj->load($lehrveranstaltung_id))
			{			
				$studiengang_kz=$lv_obj->studiengang_kz;
				$semester=$lv_obj->semester;
			}
			else
			{
            	$errormsg[]='Lehrveranstaltung '.$lehrveranstaltung_id.' wurden nicht gefunden! '.$lv_obj->errormsg;
			}	
		}
		else
		{
            $errormsg[]='Lehreinheit '.$lehreinheit_id_sel.' wurden nicht gefunden! '.$le_obj->errormsg;
		}	
}	
else if(!isset($_GET['kopieren']) && $uebung_id_source=='' && $uebung_id_sel!='' )
{
#var_dump($uebung_id_sel);
	$uebung_id_source=$uebung_id_sel;
}
	
#var_dump($uebung_id_source);	
	
// Es wurde nur eine Uebungsid uebergeben, und noch keine Aktion gedrueckt		
if(!isset($_GET['kopieren']) && $uebung_id_source!='')
{
		if (!is_array($uebung_id_source) && !empty($uebung_id_source))
			$uebung_id_source=array($uebung_id_source);
			
		$ueb_0 = new uebung($uebung_id_source[0]);
#var_dump($ueb_0);		
        if ($lehreinheit_id=$ueb_0->lehreinheit_id)
        {
                if ($le_obj->load($lehreinheit_id))
                {
                        $lehrveranstaltung_id = $le_obj->lehrveranstaltung_id;
                        $studiensemester_kurzbz = $le_obj->studiensemester_kurzbz;
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
                }
                else
                {
                        $bAnzeige=false;
                        $errormsg[]='Lehreinheit wurde nicht gefunden '.addslashes($lehreinheit_id).' '.$le_obj->errormsg;
                }
        }
		else
		{
             $errormsg[]='&Uuml;bung wurde nicht gefunden '.addslashes($uebung_id_source).' '.$ueb_0->errormsg;
		}				
}

// Aktion wurde gedruckt	
if(isset($_GET['kopieren']))
{
		if (!is_array($uebung_id_source) && !empty($uebung_id_source) )
			$uebung_id_source=array($uebung_id_source);
		if (!is_array($uebung_id_source) || count($uebung_id_source)<1 )
		{
			$error=true;
			$errormsg[]="<span class='error'>&Uuml;bung muss ausgew&auml;hlt sein!</span>";
		}
		
		if (!is_numeric($lehreinheit_id_target))
		{
			$error=true;
			$errormsg[]="<span class='error'>Lehreinheit muss ausgew&auml;hlt sein!</span>";
		}	
		
		if (!$error)
		{
			$db->db_query('BEGIN;');	

			reset($uebung_id_source);
			foreach ($uebung_id_source as $ueb)
			{
				$copy_insert = 0;
				$copy_update = 0;	
				$copy_insert_bsp = 0;
				$copy_update_bsp = 0;
				$error=false;
				
				
				$ueb_1 = new uebung($ueb);
				$lehreinheit_id_unterord=$ueb_1->lehreinheit_id;
				$nummer_source = $ueb_1->nummer;
				$qry = "SELECT * from campus.tbl_uebung where nummer = ".myaddslashes($nummer_source)." and lehreinheit_id = ".myaddslashes($lehreinheit_id_target).";";
				//echo $qry;
				if($result1 = $db->db_query($qry))	
				{
					if ($db->db_num_rows($result1) >0)
					{
						$row1 = $db->db_fetch_object($result1);		
						$ueb_1_target =new uebung($row1->uebung_id);
						$ueb_1_target->new = false;
						$new = null;
						$ueb_1_target->insertamum = null;
						$ueb_1_target->insertvon = null;
						$ueb_1_target->updateamum = date('Y-m-d H:i:s');
						$ueb_1_target->updatevon = $user;
						$copy_update++;
					}
					else
					{
						$ueb_1_target =new uebung();
						$ueb_1_target->new = true;
						$new = true;
						$ueb_1_target->insertamum = date('Y-m-d H:i:s');
						$ueb_1_target->insertvon = $user;
						$ueb_1_target->updateamum = null;
						$ueb_1_target->updatevon = null;
						$copy_insert++;
					}
					$ueb_1_target->gewicht = $ueb_1->gewicht;
					$ueb_1_target->punkte = null;
					$ueb_1_target->angabedatei=null;
					$ueb_1_target->freigabevon = null;
					$ueb_1_target->freigabebis = null;
					$ueb_1_target->abgabe = false;
					$ueb_1_target->beispiele = false;
					$ueb_1_target->statistik = false;
					$ueb_1_target->maxstd = null;
					$ueb_1_target->maxbsp=null;
					$ueb_1_target->liste_id=null;
					$ueb_1_target->bezeichnung = $ueb_1->bezeichnung;
					$ueb_1_target->positiv = $ueb_1->positiv;
					$ueb_1_target->defaultbemerkung = $ueb_1->defaultbemerkung;
					$ueb_1_target->lehreinheit_id = $lehreinheit_id_target;
					$ueb_1_target->nummer = $nummer_source;
							
					if (!$ueb_1_target->save($new))
					{
						$error = 1;
						$errormsg[]="<span class='error'>Haupt&uuml;bung konnte nicht kopiert werden!</span>";
					}
						
					else
					{
						// SubÃ¼bungen durchlaufen			
						$ueb_2 = new uebung();
						$ueb_2->load_uebung($lehreinheit_id_unterord,2,$ueb);
						
						$ueb_2anzahl = count($ueb_2->uebungen);
						if ($ueb_2anzahl >0)			
						{
							foreach ($ueb_2->uebungen as $subrow)
							{
																	
								$nummer_source2 = $subrow->nummer;
								$qry2 = "SELECT * from campus.tbl_uebung where nummer = '".$nummer_source2."' and lehreinheit_id = '".$lehreinheit_id_target."'";
								$result2 = $db->db_query($qry2);
								if ($db->db_num_rows($result2) >0)
								{
									$row2 = $db->db_fetch_object($result2);		
									$ueb_2_target =new uebung($row2->uebung_id);
									$ueb_2_target->new = false;
									$new = null;
									$ueb_2_target->insertamum = null;
									$ueb_2_target->insertvon = null;
									$ueb_2_target->updateamum = date('Y-m-d H:i:s');
									$ueb_2_target->updatevon = $user;
									$copy_update++;
								}
								else
								{
									$ueb_2_target =new uebung();
									$ueb_2_target->new = true;
									$new = true;
									$ueb_2_target->insertamum = date('Y-m-d H:i:s');
									$ueb_2_target->insertvon = $user;
									$ueb_2_target->updateamum = null;
									$ueb_2_target->updatevon = null;
									$copy_insert++;
								}
								$ueb_2_target->gewicht = $subrow->gewicht;
								$ueb_2_target->punkte = $subrow->punkte;
								$ueb_2_target->angabedatei=null;
								$ueb_2_target->freigabevon = $subrow->freigabevon;
								$ueb_2_target->freigabebis = $subrow->freigabebis;
								$ueb_2_target->abgabe = $subrow->abgabe;
								$ueb_2_target->beispiele = $subrow->beispiele;
								$ueb_2_target->statistik = $subrow->statistik;
								$ueb_2_target->maxstd = $subrow->maxstd;
								$ueb_2_target->maxbsp=$subrow->maxbsp;
								$ueb_2_target->liste_id=$ueb_1_target->uebung_id;
								$ueb_2_target->bezeichnung = $subrow->bezeichnung;
								$ueb_2_target->positiv = $subrow->positiv;
								$ueb_2_target->defaultbemerkung = $subrow->defaultbemerkung;
								$ueb_2_target->lehreinheit_id = $lehreinheit_id_target;
								$ueb_2_target->nummer = $nummer_source2;
										
								if (!$ueb_2_target->save($new))
								{
									$error = 1;
									$errormsg[]="<span class='error'>&Uuml;bung konnte nicht kopiert werden!</span>";
								}
								
								//angabedatei syncen
								if (($error == 0) and $subrow->angabedatei != "")
								{	
									$angabedatei_source = $subrow->angabedatei;
									$angabedatei_target = makeUploadName($db,'angabe', $lehreinheit_id_target, $ueb_2_target->uebung_id, $stsem);
									$angabedatei_target .= ".".mb_substr($angabedatei_source, mb_strrpos($angabedatei_source, '.',0) + 1);
									echo $angabedatei_source."->".$angabedatei_target."<br>";
									exec("cp ".BENOTUNGSTOOL_PATH."angabe/".$angabedatei_source." ".BENOTUNGSTOOL_PATH."angabe/".$angabedatei_target);
									$angabeupdate = "update campus.tbl_uebung set angabedatei = '".$angabedatei_target."' where uebung_id = '".$ueb_2_target->uebung_id."'";
									$db->db_query($angabeupdate);
								}
												
								if (($error == 0) and $ueb_2_target->beispiele)
								{
									// beispiele synchronisieren
									$bsp_obj = new beispiel();
									$bsp_obj->load_beispiel($subrow->uebung_id);
									foreach ($bsp_obj->beispiele as $bsp)
									{
										$nummer_source_bsp = $bsp->nummer;
										$qrybsp = "SELECT * from campus.tbl_beispiel where nummer = '".$nummer_source_bsp."' and uebung_id = '".$ueb_2_target->uebung_id."'";
										$resultbsp = $db->db_query($qrybsp);
					
										if ($db->db_num_rows($resultbsp) >0)
										{
											$rowbsp = $db->db_fetch_object($resultbsp);		
											$bsp_target =new beispiel($rowbsp->beispiel_id);
											$bsp_target->new = false;
											$new = null;
											$bsp_target->insertamum = null;
											$bsp_target->insertvon = null;
											$bsp_target->updateamum = date('Y-m-d H:i:s');
											$bsp_target->updatevon = $user;
											$copy_update_bsp++;
										}
										else
										{
											$bsp_target =new beispiel();
											$bsp_target->new = true;
											$new = true;
											$bsp_target->insertamum = date('Y-m-d H:i:s');
											$bsp_target->insertvon = $user;
											$bsp_target->updateamum = null;
											$bsp_target->updatevon = null;
											$copy_insert_bsp++;
										}
										$bsp_target->uebung_id = $ueb_2_target->uebung_id;
										$bsp_target->nummer = $nummer_source_bsp;
										$bsp_target->bezeichnung = $bsp->bezeichnung;
										$bsp_target->punkte = $bsp->punkte;
										
										if (!$bsp_target->save($new))
										{
											$error = 1;
											$errormsg[]="<span class='error'>Beispiele konnten nicht angelegt werden</span>";							
										}
										
										//NotenschlÃ¼ssel synchronisieren
										$clear = "delete from campus.tbl_notenschluesseluebung where uebung_id = '".$ueb_1_target->uebung_id."'";
										$db->db_query($clear);
										
										$qry_ns_source = "SELECT * from campus.tbl_notenschluesseluebung where uebung_id = '".$ueb."'";
										$result_ns_source = $db->db_query($qry_ns_source);
										while($row_ns = $db->db_fetch_object($result_ns_source))
										{
											$ns_insert = "INSERT INTO campus.tbl_notenschluesseluebung values ('".$ueb_1_target->uebung_id."','".$row_ns->note."', '".$row_ns->punkte."')";
											$db->db_query($ns_insert);					
										}					
													
									}									
								}
									
							}
						}
					}
					
				}
				else
				{
					$errormsg[]="<span class='error'>Fehler beim Datenbankzugriff!</span>";
				}				
	
				if ($error == 0)
				{
					$errormsg[]="&Uuml;bung ".$ueb." erfolgreich kopiert! (&Uuml;: ".$copy_insert."/".$copy_update."; B: ".$copy_insert_bsp."/".$copy_update_bsp.")";
				}
				else
				{
					$errormsg[]="&Uuml;bung ".$ueb." wurde nicht kopiert! (&Uuml;: ".$copy_insert."/".$copy_update."; B: ".$copy_insert_bsp."/".$copy_update_bsp.")";
					break;
				}
			}	
		}	
		// Nun alle An Felder - Lehreinheit zum Von kopieren (neue Anzeige)
		if ($error == 0)
		{
			$db->db_query('COMMIT');	
		}
		else
		{
			$db->db_query('ROLLBACK');			
		}
		$errormsg[]='<br><br><a href="'.$_SERVER['PHP_SELF'].'" class="Item">noch eine &Uuml;bung kopieren</a>';
	}


	// ***********************************************************************************************
	//      Datenbankfeld - Variable 
	// ***********************************************************************************************
	function myaddslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title>Kreuzerltool - Copy </title>
</head>
<body>
	<?php
     $cFormName='copy_uebung';

	 $content='';
	 $content.='
		<h1>Kopieren von &Uuml;bungen in eine andere Lehreinheit</h1>
          <form title="Script zum Kopieren einer &Uuml;bung in eine beliebige Lehreinheit." accept-charset="UTF-8" name="'.$cFormName.'"  method="GET" target="_self" action="'.$_SERVER['PHP_SELF'].'" >
				Direkt Eingabe : LE ID:<input name="lehreinheit_id_sel" onchange="document.'.$cFormName.'.studiengang_kz.selectedIndex = -1;document.'.$cFormName.'.semester.selectedIndex = -1;document.'.$cFormName.'.lehrveranstaltung_id.selectedIndex = -1;document.'.$cFormName.'.lehreinheit_id.selectedIndex = -1;document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();">&nbsp;,&nbsp;
				&Uuml;bung ID:<input name="uebung_id_sel" onchange="document.'.$cFormName.'.studiengang_kz.selectedIndex = -1;document.'.$cFormName.'.semester.selectedIndex = -1;document.'.$cFormName.'.lehrveranstaltung_id.selectedIndex = -1;document.'.$cFormName.'.lehreinheit_id.selectedIndex = -1;document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();">&nbsp;&nbsp;oder<br><br>
				
				
			<table class="liste">
			<tr>
			   	<th>Studiensem</th>
			   	<th>StgKz</th>
			   	<th>Sem</th>
			   	<th>Lehrveranstaltung</th>
			   	<th>Lehreinheiten</th>
			   	<th>&Uuml;bungID die kopiert werden soll:</th>
			</tr>
			<tr>
			
';
    //---------------------------------------------------------------------------
	// Auswahlfelder
	     $content.='<tr>';

        // Studiensemester public.tbl_studiensemester_kurzbz
         $content.='<td valign="top"><select onchange="document.'.$cFormName.'.studiengang_kz.selectedIndex = -1;document.'.$cFormName.'.semester.selectedIndex = -1;document.'.$cFormName.'.lehrveranstaltung_id.selectedIndex = -1;document.'.$cFormName.'.lehreinheit_id.selectedIndex = -1;document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();" name="studiensemester_kurzbz">';
# 	     $content.='<option value="">&nbsp;Alle&nbsp;</option>';
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
        	$content.='<td valign="top"><select onchange="document.'.$cFormName.'.semester.selectedIndex = -1;document.'.$cFormName.'.lehrveranstaltung_id.selectedIndex = -1;document.'.$cFormName.'.lehreinheit_id.selectedIndex = -1;document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();" name="studiengang_kz">';
#			$content.='<option value="" '.(empty($studiengang_kz)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
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
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehrveranstaltung_id.selectedIndex = -1;document.'.$cFormName.'.lehreinheit_id.selectedIndex = -1;document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();" name="semester">';
#                $content.='<option value="" '.(empty($semester)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
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
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehreinheit_id.selectedIndex = -1;document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();" name="lehrveranstaltung_id">';
#                $content.='<option value="" '.(empty($lehrveranstaltung_id)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $lv_obj->lehrveranstaltungen=array();
		  		if (!empty($studiengang_kz))
		  		{	
	                if ($lv_obj->load_lva_le($studiengang_kz, $studiensemester_kurzbz, $semester,null,null,null,'bezeichnung'))
	                {
	                        foreach ($lv_obj->lehrveranstaltungen as $row)
	                        {
									if (empty($lehrveranstaltung_id))
										$lehrveranstaltung_id=$row->lehrveranstaltung_id;
	                                $content.='<option value="'.$row->lehrveranstaltung_id.'" '.(("$lehrveranstaltung_id"=="$row->lehrveranstaltung_id")?' selected="selected" ':'').'>&nbsp;'.CutString($row->bezeichnung, 30, '...').' '.$row->lehrform_kurzbz.'&nbsp;('.$row->lehrveranstaltung_id.')</option>';
	                        }
	                }
	                else
	                {
	                        $content.='<option value="" '.(empty($studiengang_kz)?' selected="selected" ':'').'>&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
	                        $errormsg[]='Lehrveranstaltungen wurden nicht gefunden! '.$lv_obj->errormsg;
	                }
		 	 	}	  
                $content.='</select>
		  </td>';
        //---------------------------------------------------------------------------
        // Lehreinheit
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'[7].selectedIndex = -1;document.'.$cFormName.'.submit();" name="lehreinheit_id">';
#                $content.='<option value="" '.(empty($lehreinheit_id)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $le_obj->lehreinheiten=array();
                if (!empty($lehrveranstaltung_id))
                {
                        $le_obj->load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz);
                        foreach ($le_obj->lehreinheiten as $row)
                        {
								if (empty($lehreinheit_id))
									$lehreinheit_id=$row->lehreinheit_id;
						
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
								$lektoren=CutString($lektoren, 30, '...');
                                $content.='<option value="'.$row->lehreinheit_id.'" '.($lehreinheit_id==$row->lehreinheit_id?' selected="selected" ':'').'>&nbsp;'.$row->lehrform_kurzbz.' '.$gruppen.'&nbsp;'.$lektoren.'&nbsp;('.$row->lehreinheit_id.')</option>';
                        }
                }
                $content.='</select></td>';			

#var_dump($uebung_id_source);
		
				$submitOK=false;				
				if (!$ueb = new uebung())
				{
			         die('Fehler beim Oeffnen der &Uuml;bungen');
				}
				else
				{
					$ueb->uebungen=array();
					$ueb->errormsg ='';
					if (!$lehreinheit_ueb=$ueb->load_uebung($lehreinheit_id))
					{
						$errormsg[]=$ueb->errormsg;
					}	
				}

	            $content.='<td valign="top"><select multiple size="'.count($ueb->uebungen).'" name="uebung_id_source[]">';
				if (is_array($ueb->uebungen) && count($ueb->uebungen)>0)
				{
                        foreach ($ueb->uebungen as $row)
						{									
							if (!is_array($uebung_id_source) && !empty($uebung_id_source) )
								$uebung_id_source=array($uebung_id_source);
							$selected='';
							if (is_array($uebung_id_source) && count($uebung_id_source)>0 )
							{
								reset($uebung_id_source);
								foreach ($uebung_id_source as $ueb)
								{
									if ($row->uebung_id==$ueb)
										$selected=' selected="selected" ';									
								}
							}	
							$submitOK=true;
                            $content.='<option value="'.$row->uebung_id.'" '.$selected.'>&nbsp;'.$row->bezeichnung.'&nbsp;('.$row->uebung_id.')</option>';
                        }
				}					
				if (!$submitOK)
                      $content.='<option value="">&nbsp;<span class="error">keine &Uuml;bung zur LE</span></option>';
                $content.='</select></td>';			
			
		$content.='	
			</tr>
			<tr><td colspan="6"><hr></td></tr>
			';
						
			if ($submitOK)			
				$content.='<tr><th colspan="5">Lehreinheit ID in welche diese &Uuml;bung kopiert werden soll</th><td><input type="submit" value="Kopieren" name="kopieren"></td></tr>';
			else
				$content.='<tr><th colspan="6">Lehreinheit ID in welche diese &Uuml;bung kopiert werden soll</th></tr>';
				
		$content.='	
			<tr><td colspan="6">&nbsp;</td></tr>			
			';			

		$content.='	
			<tr>			
			';
			
         $content.='<td valign="top"><select onchange="document.'.$cFormName.'.semester_target.value=\'\';document.'.$cFormName.'.lehrveranstaltung_id_target.value=\'\';document.'.$cFormName.'.lehreinheit_id_target.value=\'\';document.'.$cFormName.'.submit();" name="studiensemester_kurzbz_target">';
# 	     $content.='<option value="">&nbsp;Alle&nbsp;</option>';
         $stsem->studiensemester=array();
		 if ($stsem->getAll())
         {
              foreach ($stsem->studiensemester as $row)
              {
                     $content.='<option value="'.$row->studiensemester_kurzbz.'" '.(("$studiensemester_kurzbz_target"=="$row->studiensemester_kurzbz")?' selected="selected" ':'').'>&nbsp;'.$row->studiensemester_kurzbz.'&nbsp;</option>';
              }
         }
         else
         {
               $errormsg[]='Studiensemester wurden nicht gefunden! '.$stsem->errormsg;
         }
         $content.='</select></td>';

        //---------------------------------------------------------------------------
        // Studiengang public.tbl_studiengang_kz
        	$content.='<td valign="top"><select onchange="document.'.$cFormName.'.semester_target.value=\'\';document.'.$cFormName.'.lehrveranstaltung_id_target.value=\'\';document.'.$cFormName.'.lehreinheit_id_target.value=\'\';document.'.$cFormName.'.submit();" name="studiengang_kz_target">';
#			$content.='<option value="" '.(empty($studiengang_kz)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
            $stsem->result=array();
            if ($stg_obj->getAll('typ, kurzbz',true))
            {
                        $max_semester=0;
                        $arrStudiengang='';
                        foreach ($stg_obj->result as $row)
                        {
                                if (empty($studiengang_kz_target) && !isset($_REQUEST['studiengang_kz_target']) )
                                {
                                        $studiengang_kz_target=$row->studiengang_kz;
                                        $semester_target=1;
                                }
                                if ($studiengang_kz_target==$row->studiengang_kz)
                                {
                                        $arrStudiengang=$row;
                                        $max_semester=$row->max_semester;
                                }
                                $content.='<option value="'.$row->studiengang_kz.'" '.(("$studiengang_kz_target"=="$row->studiengang_kz")?' selected="selected" ':'').'>&nbsp;'.$row->kuerzel.'&nbsp;('.$row->kurzbzlang.')&nbsp;</option>';
                        }
                }
                else
                {
                        $content.='<option value="" '.(empty($studiengang_kz_target)?' selected="selected" ':'').'>&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
                        $errormsg[]='Studieng&auml;nge wurden nicht gefunden! '.$stg_obj->errormsg;
                }
                $content.='</select></td>';


        //---------------------------------------------------------------------------
        // Semster public.tbl_studiengang_kz - max Semester des Selektierten Studiengangs
		// document.'.$cFormName.'.uebung_id_source_target.value=\'\';
        //        $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehrveranstaltung_id_target.value=\'\';document.'.$cFormName.'.lehreinheit_id_target.value=\'\';document.'.$cFormName.'.submit();" name="semester_target">';
#                $content.='<option value="" '.(empty($semester)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehrveranstaltung_id_target.value=\'\';document.'.$cFormName.'.lehreinheit_id_target.value=\'\';document.'.$cFormName.'.submit();" name="semester_target">';
                if ($studiengang_kz_target!='')
                {
                        for($i=0;$i<=$max_semester;$i++)
                        {
                                $content.='<option value="'.($i).'" '.(("$semester_target"=="$i")?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
                        }
                }
                else
                {
                        for($i=0;$i<=9;$i++)
                        {
                                $content.='<option value="'.($i).'" '.(("$semester_target"=="$i")?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
                        }
                }
                $content.='</select></td>';


##echo "<br> $studiengang_kz_target, $studiensemester_kurzbz_target, $semester_target , $lehrveranstaltung_id_target <br>";

        //---------------------------------------------------------------------------
        // Lehrveranstaltungen
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.lehreinheit_id_target.value=\'\';document.'.$cFormName.'.submit();" name="lehrveranstaltung_id_target">';
#                $content.='<option value="" '.(empty($lehrveranstaltung_id)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $lv_obj->lehrveranstaltungen=array();
		  		if (!empty($studiengang_kz))
		  		{	
	                if ($lv_obj->load_lva_le($studiengang_kz_target, $studiensemester_kurzbz_target, $semester_target,null,null,null,'bezeichnung'))
	                {
	                        foreach ($lv_obj->lehrveranstaltungen as $row)
	                        {
									if (empty($lehrveranstaltung_id_target))
										$lehrveranstaltung_id_target=$row->lehrveranstaltung_id;
	                                $content.='<option value="'.$row->lehrveranstaltung_id.'" '.(("$lehrveranstaltung_id_target"=="$row->lehrveranstaltung_id")?' selected="selected" ':'').'>&nbsp;'.CutString($row->bezeichnung, 30, '...').' '.$row->lehrform_kurzbz.'&nbsp;('.$row->lehrveranstaltung_id.')</option>';
	                        }
	                }
	                else
	                {
	                        $content.='<option value="" '.(empty($studiengang_kz_target)?' selected="selected" ':'').'>&nbsp;'.$stg_obj->errormsg.'&nbsp;</option>';
	                        $errormsg[]='Lehrveranstaltungen wurden nicht gefunden! '.$lv_obj->errormsg;
	                }
		 	 	}	  
                $content.='</select>
		  </td>';

#echo "<br> $studiengang_kz_target, $studiensemester_kurzbz_target, $semester_target , $lehrveranstaltung_id_target <br>";

        //---------------------------------------------------------------------------
        // Lehreinheit
		
	
                $content.='<td valign="top"><select onchange="document.'.$cFormName.'.submit();" name="lehreinheit_id_target">';
#                $content.='<option value="" '.(empty($lehreinheit_id)?' selected="selected" ':'').'>&nbsp;Alle&nbsp;</option>';
                $le_obj->lehreinheiten=array();
                if (!empty($lehrveranstaltung_id_target))
                {
                        $le_obj->load_lehreinheiten($lehrveranstaltung_id_target, $studiensemester_kurzbz_target);
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
								$lektoren=CutString($lektoren, 30, '...');									
                                $content.='<option value="'.$row->lehreinheit_id.'" '.($lehreinheit_id_target==$row->lehreinheit_id?' selected="selected" ':'').'>&nbsp;'.$row->lehrform_kurzbz.' '.$gruppen.'&nbsp;'.$lektoren.'&nbsp;('.$row->lehreinheit_id.')</option>';
                        }
                }
                $content.='</select></td>';			

			$content.='<td valign="top" align="right">';
			
			
#echo "<br> $studiengang_kz_target, $studiensemester_kurzbz_target, $semester_target , $lehrveranstaltung_id_target <br>";
			
				if (!$ueb = new uebung())
				{
			         die('Fehler beim Oeffnen der &Uuml;bungen');
				}
				else if (!empty($lehreinheit_id_target))
				{
					$ueb->uebungen=array();
					$ueb->errormsg ='';
					if (!$lehreinheit_ueb=$ueb->load_uebung($lehreinheit_id_target))
					{
						$errormsg[]=$ueb->errormsg;
					}	
					else if (count($ueb->uebungen))
					{
			            $content.='<select disabled multiple size="'.count($ueb->uebungen) .'">';
                        foreach ($ueb->uebungen as $row)
                        {
							$submitOK=true;								
							if (!is_array($uebung_id_source) && !empty($uebung_id_source) )
								$uebung_id_source=array($uebung_id_source);
							$selected='';
                            $content.='<option value="'.$row->uebung_id.'" '.$selected.'>&nbsp;'.$row->bezeichnung.'&nbsp;('.$row->uebung_id.')</option>';
                        }
		                $content.='</select>';			
					}	
					else if (!empty($lehreinheit_id_target))
					{
						 $content.='<span class="error">keine &Uuml;bungen vorhanden</span>';
					}				
				}
		$content.='</td>';
		$content.='</tr>';
		$content.='</table>';
				
       $content.='</form><hr>'.implode('<br>',$errormsg);
	   
	print_r($content);	
		
	?>
</body>
</html>