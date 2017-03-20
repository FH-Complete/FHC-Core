<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*******************************************************************************************************
 *		Autocomplete
 * 		projektabgabe ermöglicht den Download aller Abgaben eines Stg.
 * 			fuer Diplom- und Bachelorarbeiten
 *******************************************************************************************************/
	header( 'Expires:  -1' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Pragma: no-cache' );
	header('Content-Type: text/html;charset=UTF-8');

// ---------------- Vilesci Include Dateien einbinden
	require_once('../../config/vilesci.config.inc.php');
  	require_once('../../include/functions.inc.php');
	include_once('../../include/basis_db.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------

	if (!$db = new basis_db())
		die('Datenbank kann nicht geoeffnet werden.  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	if (!$uid = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$rechte =  new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

// ------------------------------------------------------------------------------------------
// Initialisierung
// ------------------------------------------------------------------------------------------
	$errormsg=array();

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
  	$oe_kurzbz=trim((isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz']:''));
  	$funktion_kurzbz=trim((isset($_REQUEST['funktion_kurzbz']) ? $_REQUEST['funktion_kurzbz']:''));
  	$nation=trim((isset($_REQUEST['nation']) ? $_REQUEST['nation']:''));
  	$plz=trim((isset($_REQUEST['plz']) ? $_REQUEST['plz']:''));

  	$work=trim(isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['ajax'])?$_REQUEST['ajax']:false));
	$work=strtolower($work);

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
/* jQuery autocomplete
lineSeparator = (default value: "\n")
	The character that separates lines in the results from the backend.
cellSeparator (default value: "|")
	The character that separates cells in the results from the backend.
*/
	switch ($work)
	{
		case 'gemeinde':
		 	$gemeinde=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
		 	$nation=trim((isset($_REQUEST['nation']) ? $_REQUEST['nation']:''));
		 	if ($nation!='A')
				exit();
			if (is_null($gemeinde) || $gemeinde=='')
				exit();

			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($gemeinde))));
			$pWhere=" upper(gemeinde) like '%".addslashes($matchcode)."%' ";
			$pSql="SELECT distinct gemeinde
					FROM bis.tbl_gemeinde
					where ". $pWhere ."
					ORDER BY nation,gemeinde ";

				$pArt='';
				$pDistinct=true;
				$pFields='';
				$pTable='';
				$matchcode='';
				$pWhere='';
				$pOrder='';
				$pLimit='';

			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
			{
				exit(' |'.$db->errormsg."\n");
			}
			for ($i=0;$i<count($oRresult);$i++)
				echo html_entity_decode($oRresult[$i]->gemeinde).'|'.html_entity_decode($nation)."\n";
			break;

		case 'plz':
		 	$plz=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
		 	$nation=trim((isset($_REQUEST['nation']) ? $_REQUEST['nation']:''));
		 	if ($nation!='A')
				exit();
			if (is_null($plz) || $plz=='')
				exit();

			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($plz))));
			$pWhere=" to_char(plz,'999999') like '%".addslashes($matchcode)."%' ";
			$pSql="SELECT distinct plz
					FROM bis.tbl_gemeinde
					where ". $pWhere ."
					ORDER BY plz ";

				$pArt='';
				$pDistinct=true;
				$pFields='';
				$pTable='';
				$matchcode='';
				$pWhere='';
				$pOrder='';
				$pLimit='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
			{
					exit(' |'.$db->errormsg."\n");
			}
			for ($i=0;$i<count($oRresult);$i++)
				echo html_entity_decode($oRresult[$i]->plz)."\n";
			break;

		case 'ort':
		 	$ort=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
		 	$nation=trim((isset($_REQUEST['nation']) ? $_REQUEST['nation']:''));
		 	if ($nation!='A')
				exit();
			if (is_null($ort) || $ort=='')
				exit();

			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($ort))));
			$pWhere=" upper(ort) like '%".addslashes($matchcode)."%' ".($nation?" and nation='".addslashes($nation)."'":'');
			$pSql="SELECT distinct plz,ort
					FROM public.tbl_adresse
					where ". $pWhere ."
					ORDER BY plz,ort ";

				$pArt='';
				$pDistinct=true;
				$pFields='';
				$pTable='';
				$matchcode='';
				$pWhere='';
				$pOrder='';
				$pLimit='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
			{
					exit(' |'.$db->errormsg."\n");
			}
			for ($i=0;$i<count($oRresult);$i++)
				echo html_entity_decode($oRresult[$i]->ort).'|'.html_entity_decode($oRresult[$i]->plz)."\n";
			break;



		case 'position':
		 	$position=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));

			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($position))));
			$pWhere=" upper(position) like '%".addslashes($matchcode)."%' ".($funktion_kurzbz?" and funktion_kurzbz='".addslashes($funktion_kurzbz)."'":'');
			$pSql="SELECT distinct funktion_kurzbz,position
					FROM public.tbl_personfunktionstandort
					where ". $pWhere ."
					ORDER BY funktion_kurzbz,position ";

				$pArt='';
				$pDistinct=true;
				$pFields='';
				$pTable='';
				$matchcode='';
				$pWhere='';
				$pOrder='';
				$pLimit='';

			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
			{
				if (empty($funktion_kurzbz))
					exit;

				$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($position))));
				$pWhere=" upper(position) like '%".addslashes($matchcode)."%'";
				$pSql="SELECT distinct funktion_kurzbz,position
						FROM public.tbl_personfunktionstandort
						where ". $pWhere ."
						ORDER BY funktion_kurzbz,position ";

				if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
					exit(' |'.$db->errormsg."\n");
			}
			for ($i=0;$i<count($oRresult);$i++)
				echo html_entity_decode($oRresult[$i]->position).'|'.html_entity_decode($oRresult[$i]->funktion_kurzbz)	."\n";
			break;

		case 'work_firmen_search':
			$json=array();
			$berechtigung_kurzbz = 'basis/firma:begrenzt';
			if(!$rechte->isBerechtigt($berechtigung_kurzbz))
				exit(json_encode(array_push($json, array ('oFirma_id' => '','oName' =>'keine Berechtigung'))));

			$filter = (isset($_REQUEST['filter'])?$_REQUEST['filter']:'');
			$firmentyp_kurzbz = (isset($_REQUEST['firmentyp_kurzbz'])?$_REQUEST['firmentyp_kurzbz']:'');

			$qry =" select distinct tbl_firma.firma_id,tbl_firma.name  ";
			$qry.=" FROM public.tbl_firma,  public.tbl_standort  ";
			$qry.=" left outer join public.tbl_adresse  on ( tbl_adresse.adresse_id=tbl_standort.adresse_id ) ";
			$qry.=" WHERE tbl_standort.firma_id=tbl_firma.firma_id ";

			if($filter!='')
				$qry.= " and ( lower(tbl_firma.name) like lower('%$filter%')
						OR lower(kurzbz) like lower('%$filter%')
						OR lower(tbl_adresse.strasse) like lower('%$filter%')
						OR lower(bezeichnung) like lower('%$filter%')
						OR lower(tbl_firma.anmerkung) like lower('%$filter%')
						".(is_numeric($filter)?" OR tbl_firma.firma_id='$filter'":'')."
						 ) ";


			if($firmentyp_kurzbz!='')
				$qry.=" and firmentyp_kurzbz='".addslashes($firmentyp_kurzbz)."'";
			$qry.=" ORDER BY tbl_firma.name ";
			// Datenbremse fallse keine Kriterien gesetzt sind
			if($filter=='' && $firmentyp_kurzbz=='')
				$qry.=" limit 350 ";

			$pArt='';
			$pDistinct=false;
			$pFields='';
			$pTable='';
			$matchcode='';
			$pWhere='';
			$pOrder='';
			$pLimit='';
			$pSql=$qry;
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
			{
				array_push($json, array ('oFirma_id' => '','oName' => $db->errormsg ));
			}
			else if ($oRresult)
			{
				for ($i=0;$i<count($oRresult);$i++)
				{
					array_push($json, array ('oFirma_id' => $oRresult[$i]->firma_id,'oName' => $oRresult[$i]->name ));
				}
			}
			else
			{
				array_push($json, array ('oFirma_id' => '','oName' => 'keine Daten gefunden!' ));
			}
			echo json_encode($json);
			break;

		case 'tags':
			$tag=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));

		 	$pWhere=" upper(tag) like upper('%".addslashes($tag)."%')";

			$pArt='select';
			$pDistinct=false;
			$pFields='tag';
			$pTable='public.tbl_tag';
			$matchcode='';
			$pOrder='tag';
			$pLimit='';
			$pSql='';
			if (!$result=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");

			if(is_array($result))
			{
                            $json=array();
                            for ($i=0;$i<count($result);$i++)
                            {
                                $item['tag']=$result[$i]->tag;
                                $json[]=$item;
                                //echo html_entity_decode($result[$i]->tag)."\n";
                            }
                            echo json_encode($json);
			}
			break;

		// Person - FH Technikum suche
		case 'person':
		 	$person_id=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($person_id) || $person_id=='')
				exit('person_id wurde nicht übergeben!');

			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
			$pWhere=" aktiv ";
			if ($person_id)
			{
				$pWhere.="	and (UPPER(trim(uid)) like '%".$matchcode."%'  ";
				$pWhere.="	or UPPER(trim(to_char(person_id,'999999999'))) like '%".$matchcode."%' ";
				$pWhere.="	or	UPPER(trim(nachname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(vorname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(nachname || ' ' || vorname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(vorname || ' ' || nachname)) like '%".addslashes($matchcode)."%' ) ";
			}
			if (!empty($oe_kurzbz))
			{
				$pSql="SELECT vw_benutzer.uid,vw_benutzer.person_id,vw_benutzer.aktiv,uid,person_id,titelpre,anrede,vorname,nachname,vornamen,titelpost,funktion_kurzbz
					FROM public.tbl_benutzerfunktion JOIN campus.vw_benutzer USING(uid)
					where ". $pWhere ."
					and (funktion_kurzbz='oezuordnung')
					and	oe_kurzbz IN(
						WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
						(
							SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
							WHERE upper(trim(oe_kurzbz))=upper(trim('".addslashes($oe_kurzbz)."'))
							UNION ALL
							SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
							WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
						)
						SELECT oe_kurzbz
						FROM oes
						GROUP BY oe_kurzbz  limit 1)
					ORDER BY nachname, vorname, funktion_kurzbz ";

				$pArt='';
				$pDistinct=true;
				$pFields='';
				$pTable='';
				$matchcode='';
				$pWhere='';
				$pOrder='';
				$pLimit='';

			}
			else
			{
				$pArt='select';
				$pDistinct=true;
				$pFields='uid,person_id,titelpre,anrede,vorname,nachname,vornamen,aktiv,\'\' as funktion_kurzbz';
				$pTable=' campus.vw_benutzer ';
				$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
				$pOrder='nachname';
				$pLimit='100';
				$pSql='';
			}
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");

			$json=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
                            $item['anrede']=trim($oRresult[$i]->anrede);
                            $item['titelpre']=$oRresult[$i]->titelpre?html_entity_decode($oRresult[$i]->titelpre).' ':'';
                            $item['vorname']=html_entity_decode($oRresult[$i]->vorname);
                            $item['nachname']=html_entity_decode($oRresult[$i]->nachname);
                            $item['funktion_kurzbz']=$oRresult[$i]->funktion_kurzbz?html_entity_decode($oRresult[$i]->funktion_kurzbz).' ':'';
                            $item['aktiv']=$oRresult[$i]->aktiv==true || $oRresult[$i]->aktiv=='t'?true:false;
                            $item['uid']=$oRresult[$i]->uid;
                            $item['person_id']=$oRresult[$i]->person_id;
                            $json[]=$item;
/*				echo html_entity_decode($oRresult[$i]->person_id).'|'
									.trim($oRresult[$i]->anrede).'&nbsp;'.($oRresult[$i]->titelpre?html_entity_decode($oRresult[$i]->titelpre).'&nbsp;':'')
									.html_entity_decode($oRresult[$i]->vorname).' '.html_entity_decode($oRresult[$i]->nachname).($oRresult[$i]->funktion_kurzbz?html_entity_decode($oRresult[$i]->funktion_kurzbz).'&nbsp;':'')
									.($oRresult[$i]->aktiv==true || $oRresult[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n"; */
			}
                        echo json_encode($json);
			break;

		// Lektor,Student - FHTW Suche im LV-Plan
		case 'lektor_student':
		 	$person_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($person_id) || $person_id=='')
				exit('person_id wurde nicht übergeben!');

			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
			$pWhere=" aktiv ";
			if ($person_id)
			{
				$pWhere.="	and (UPPER(trim(uid)) like '%".$matchcode."%'  ";
				$pWhere.="	or UPPER(trim(to_char(person_id,'999999999'))) like '%".$matchcode."%' ";
				$pWhere.="	or	UPPER(trim(nachname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(vorname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(nachname || ' ' || vorname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(vorname || ' ' || nachname)) like '%".addslashes($matchcode)."%' ) ";
			}
			/*if (!empty($oe_kurzbz))
			{
				$pSql="SELECT vw_benutzer.uid,vw_benutzer.person_id,vw_benutzer.aktiv,uid,person_id,titelpre,anrede,vorname,nachname,vornamen,titelpost,funktion_kurzbz
					FROM public.tbl_benutzerfunktion JOIN campus.vw_benutzer USING(uid)
					where ". $pWhere ."
					and (funktion_kurzbz='oezuordnung')
					and	oe_kurzbz IN(
						WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
						(
							SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
							WHERE upper(trim(oe_kurzbz))=upper(trim('".addslashes($oe_kurzbz)."'))
							UNION ALL
							SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
							WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
						)
						SELECT oe_kurzbz
						FROM oes
						GROUP BY oe_kurzbz  limit 1)
					ORDER BY nachname, vorname, funktion_kurzbz ";

				$pArt='';
				$pDistinct=true;
				$pFields='';
				$pTable='';
				$matchcode='';
				$pWhere='';
				$pOrder='';
				$pLimit='';

			}
			else
			{*/
				$pArt='select';
				$pDistinct=true;
				$pFields='uid,vorname,nachname,tbl_mitarbeiter.lektor,aktiv';
				$pTable=' campus.vw_benutzer LEFT JOIN public.tbl_mitarbeiter ON (uid=mitarbeiter_uid)';
				$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
				$pOrder='lektor,nachname';
				$pLimit='100';
				$pSql='';

			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");


			for ($i=0;$i<count($oRresult);$i++)
			{
				echo html_entity_decode($oRresult[$i]->uid).'|'
									.trim($oRresult[$i]->uid).'&nbsp;'
									.html_entity_decode($oRresult[$i]->vorname).' '.html_entity_decode($oRresult[$i]->nachname).' '
									.html_entity_decode($oRresult[$i]->lektor)
					."\n";
			}
			break;

	    default:
   	   		echo " Funktion $work fehlt! ";
			break;
	}
	exit();
?>
