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

	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/basis_db.class.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/phrasen.class.php');

	$sprache = getSprache();
	$p = new phrasen($sprache);

	if (!$db = new basis_db())
		die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

	if (!$uid = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$rechte =  new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	$berechtigung_kurzbz = 'lehre/abgabetool:download';
	if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt($berechtigung_kurzbz))
		die($p->t('global/fehlerBeimErmittelnDerUID'));

// ------------------------------------------------------------------------------------------
// Initialisierung
// ------------------------------------------------------------------------------------------
	$errormsg=array();

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------

  	$stg_kz=trim(isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'');
  	$abgabetyp=trim(isset($_REQUEST['abgabetyp'])?$_REQUEST['abgabetyp']:'');

  	$work=trim(isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['ajax'])?$_REQUEST['ajax']:false));
	$work=strtolower($work);

# Direktaufruf Test	$work='work_termin_select';

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
		case 'work_termin_select':

			$qry="	SELECT distinct campus.tbl_paabgabe.datum as termin , to_char(campus.tbl_paabgabe.datum, 'DD.MM.YYYY') as termin_anzeige
					FROM lehre.tbl_projektarbeit
							JOIN campus.tbl_paabgabe USING(projektarbeit_id)
							LEFT JOIN public.tbl_benutzer ON(uid=student_uid)
							LEFT JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
							LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
							LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
							LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
							WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
						";
			if ($stg_kz!='')
				$qry.=" AND public.tbl_studiengang.studiengang_kz=".$db->db_add_param($stg_kz);
			if ($abgabetyp!='')
				$qry.=" AND campus.tbl_paabgabe.paabgabetyp_kurzbz=".$db->db_add_param($abgabetyp);
			$qry.=" ORDER BY termin desc";

			$pArt='';
			$pDistinct=false;
			$pFields='';
			$pTable='';
			$matchcode='';
			$pWhere='';
			$pOrder='';
			$pLimit='';
			$pSql=$qry;
			$json=array();
			array_push($json, array ('oTermin' => '','oTerminAnzeige' => '-'.$p->t('global/alle').'-' ));
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
			{
				array_push($json, array ('oTermin' => '','oTerminAnzeige' => $db->errormsg ));
			}
			else if ($oRresult)
			{
				for ($i=0;$i<count($oRresult);$i++)
				{
					array_push($json, array ('oTermin' => $oRresult[$i]->termin,'oTerminAnzeige' => $oRresult[$i]->termin_anzeige ));
				}
			}
			else
			{
				array_push($json, array ('oTermin' => '','oTerminAnzeige' => 'Fehler' ));
			}
			echo json_encode($json);
			break;

	    default:
   	   		echo " Funktion $work fehlt! ";
			break;
	}
	exit();
?>
