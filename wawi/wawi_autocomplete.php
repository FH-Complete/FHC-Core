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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

	header( 'Expires:  -1' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Pragma: no-cache' );
	header('Content-Type: text/html;charset=UTF-8');

	require_once('../config/vilesci.config.inc.php');
	require_once('auth.php');
  	require_once('../include/functions.inc.php');
	require_once('../include/benutzerberechtigung.class.php');
	require_once('../include/benutzer.class.php');
	require_once('../include/person.class.php');
	require_once('../include/mitarbeiter.class.php');
	require_once('../include/ort.class.php');
	require_once('../include/studiengang.class.php');
  	require_once('../include/organisationseinheit.class.php');
  	require_once('../include/wawi.class.php');
  	require_once('../include/betriebsmittel.class.php');
  	require_once('../include/betriebsmittelperson.class.php');
  	require_once('../include/betriebsmitteltyp.class.php');
  	require_once('../include/betriebsmittelstatus.class.php');
  	require_once('../include/betriebsmittel_betriebsmittelstatus.class.php');
  	require_once ('../include/firma.class.php');
  	require_once ('../include/tags.class.php');

  	if (!$uid = get_uid())
		die('Keine UID gefunden:'.$uid.' !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

  	if (!$db = new basis_db())
		die('Datenbank kann nicht geoeffnet werden.  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$rechte = new benutzerberechtigung();
	if(!$rechte->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung fuer diese Seite');
		
	if(!$rechte->isBerechtigt('wawi/inventar:begrenzt', null, 's'))
		die('Sie haben keine Berechtigung fuer diese Seite');

// ------------------------------------------------------------------------------------------
// Initialisierung
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$default_status_vorhanden='vorhanden';

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
  	$nummer=trim((isset($_REQUEST['nummer']) ? $_REQUEST['nummer']:''));
  	$seriennummer=trim((isset($_REQUEST['seriennummer']) ? $_REQUEST['seriennummer']:''));
  	$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
  	$oe_kurzbz=trim((isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz']:''));
	$person_id=trim((isset($_REQUEST['person_id']) ? $_REQUEST['person_id']:''));
  	$beschreibung=trim((isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung']:''));
  	$betriebsmitteltyp=trim((isset($_REQUEST['betriebsmitteltyp']) ? $_REQUEST['betriebsmitteltyp']:''));
  	$betriebsmittelstatus_kurzbz=trim((isset($_REQUEST['betriebsmittelstatus_kurzbz']) ? $_REQUEST['betriebsmittelstatus_kurzbz']:''));
	$firma_id=trim(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:'');
	$bestellnr=trim(isset($_REQUEST['bestellnr'])?$_REQUEST['bestellnr']:'');
	$bestellung_id=trim(isset($_REQUEST['bestellung_id'])?$_REQUEST['bestellung_id']:'');
	$bestelldetail_id=trim(isset($_REQUEST['bestelldetail_id'])?$_REQUEST['bestelldetail_id']:'');
	$kostenstelle_id=trim(isset($_REQUEST['kostenstelle_id'])?$_REQUEST['kostenstelle_id']:'');
  	$hersteller=trim((isset($_REQUEST['hersteller']) ? $_REQUEST['hersteller']:''));
	$jahr_monat=trim(isset($_REQUEST['jahr_monat']) ? $_REQUEST['jahr_monat']:'');
  	$afa=trim(isset($_REQUEST['afa']) ? $_REQUEST['afa']:'');
  	$inventur_jahr=trim(isset($_REQUEST['inventur_jahr']) ? $_REQUEST['inventur_jahr']:'');
  	$aktiv=trim(isset($_REQUEST['aktiv']) ? $_REQUEST['aktiv']:false);

  	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);

  	$work=trim(isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['ajax'])?$_REQUEST['ajax']:false));
	$work=strtolower($work);

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------
	// Class - Datenbank	
	$oBetriebsmittel = new betriebsmittel();
	$oBetriebsmittel->result=array();
	$oBetriebsmittel->debug=$debug;	

	$oWawi = new wawi();
	$oWawi->result=array();
	$oWawi->debug=$debug;
	$oWawi->errormsg='';

	$oPerson = new person();
	$oPerson->result=array();
	$oPerson->errormsg='';

	$oOrganisationseinheit = new organisationseinheit();
	$oOrganisationseinheit->result=array();

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
// Hersteller
		case 'hersteller':
		 	$hersteller=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($hersteller) || $hersteller=='')
				exit();
			$pArt='select';
			$pDistinct=true; 
			$pFields='hersteller';
			$pTable='wawi.tbl_betriebsmittel';
			$matchcode=addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($hersteller)));
			$pWhere=" upper(trim(hersteller)) like upper(trim('".$matchcode."%'))";
			$pOrder='hersteller';
			$pLimit='100';
			$pSql='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");
			for ($i=0;$i<count($oRresult);$i++)
				echo html_entity_decode($oRresult[$i]->hersteller).'|'. ''."\n";
			break;


// Person - FH Technikum suche
		case 'person':
		 	$person_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($person_id) || $person_id=='')
				exit();
			
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
			//$pWhere=" aktiv ";
			$pWhere=' 1=1 ';
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

				
			for ($i=0;$i<count($oRresult);$i++)
			{
				echo html_entity_decode($oRresult[$i]->person_id).'|'
									.trim($oRresult[$i]->anrede).'&nbsp;'.($oRresult[$i]->titelpre?html_entity_decode($oRresult[$i]->titelpre).'&nbsp;':'')
									.html_entity_decode($oRresult[$i]->vorname).' '.html_entity_decode($oRresult[$i]->nachname).($oRresult[$i]->funktion_kurzbz?html_entity_decode($oRresult[$i]->funktion_kurzbz).'&nbsp;':'') 
									.($oRresult[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;

		// Organisation -  suche
		case 'organisationseinheit':
		 	$oe_kurzbz=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($oe_kurzbz) || $oe_kurzbz=='')
				exit();

			$oOrganisationseinheit->result=array();
			if (!$oOrganisationseinheit->getAll())
				exit($oOrganisationseinheit->errormsg."\n");
			for ($i=0;$i<count($oOrganisationseinheit->result);$i++)
			{
				if ($aktiv && ($oOrganisationseinheit->result[$i]->aktiv==false || $oOrganisationseinheit->result[$i]->aktiv=='f'))
					break;

				if (!$oe_kurzbz
				|| stristr($oOrganisationseinheit->result[$i]->oe_kurzbz,$oe_kurzbz)
				|| stristr($oOrganisationseinheit->result[$i]->bezeichnung,$oe_kurzbz)
				|| stristr($oOrganisationseinheit->result[$i]->oe_parent_kurzbz,$oe_kurzbz)
				|| stristr($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz,$oe_kurzbz) )
					echo html_entity_decode($oOrganisationseinheit->result[$i]->oe_kurzbz).'|'
					.'&nbsp;'
					.(is_null($oOrganisationseinheit->result[$i]->bezeichnung) || empty($oOrganisationseinheit->result[$i]->bezeichnung) || $oOrganisationseinheit->result[$i]->bezeichnung=='NULL' || $oOrganisationseinheit->result[$i]->bezeichnung=='null'?'':html_entity_decode($oOrganisationseinheit->result[$i]->bezeichnung) )
					.'&nbsp;'
					.(is_null($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz) || empty($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz) || $oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz=='NULL' || $oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz=='null'?'':html_entity_decode($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz) )

					.($oOrganisationseinheit->result[$i]->aktiv==true || $oOrganisationseinheit->result[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;

		// Bestellung
		case 'wawi_bestellnr':
		 	$bestellnr=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($bestellnr) || $bestellnr=='')
				exit();
			if (!$oWawi->bestellung(null,$jahr_monat,null,"$bestellnr"))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
				echo html_entity_decode($oWawi->result[$i]->bestellnr).'|'.', '.html_entity_decode($oWawi->result[$i]->jahr_monat_tag).',  '.html_entity_decode($oWawi->result[$i]->bestellung_id).', '.html_entity_decode($oWawi->result[$i]->firmenname).', '.html_entity_decode($oWawi->result[$i]->titel).' '.html_entity_decode($oWawi->result[$i]->bemerkungen)."\n";
			if ($oWawi->errormsg)
				exit($oWawi->errormsg."\n");

			echo "| *** Bestell ID *** \n";
			$oWawi->result=array();
			if ($oWawi->bestellung(null,$jahr_monat,"$bestellnr*"))
			{
				for ($i=0;$i<count($oWawi->result);$i++)
					echo html_entity_decode($oWawi->result[$i]->bestellnr).'|'.', '.html_entity_decode($oWawi->result[$i]->jahr_monat_tag).',  '.html_entity_decode($oWawi->result[$i]->bestellung_id).', '.html_entity_decode($oWawi->result[$i]->firmenname).', '.html_entity_decode($oWawi->result[$i]->titel).' '.html_entity_decode($oWawi->result[$i]->bemerkungen)."\n";
			}

			break;

		// Bestellung ID
		case 'wawi_bestellung_id':
		 	$bestellung_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($bestellung_id) || $bestellung_id=='')
				exit();
			if ($oWawi->bestellung(null,$jahr_monat,"$bestellung_id*"))
			{
				for ($i=0;$i<count($oWawi->result);$i++)
					echo html_entity_decode($oWawi->result[$i]->bestellung_id).'|'.', '.html_entity_decode($oWawi->result[$i]->jahr_monat_tag).',  '.html_entity_decode($oWawi->result[$i]->bestellnr).', '.html_entity_decode($oWawi->result[$i]->firmenname).', '.html_entity_decode($oWawi->result[$i]->titel).' '.html_entity_decode($oWawi->result[$i]->bemerkungen)."\n";
			}
			if ($oWawi->errormsg)
				exit($oWawi->errormsg."\n");
			$oWawi->result=array();
			if (!$oWawi->bestellung(null,$jahr_monat,null,"$bestellung_id%"))
				exit($oWawi->errormsg."\n");
			echo "| *** Bestellnr *** \n";
			for ($i=0;$i<count($oWawi->result);$i++)
				echo html_entity_decode($oWawi->result[$i]->bestellung_id).'|'.', '.html_entity_decode($oWawi->result[$i]->jahr_monat_tag).',  '.html_entity_decode($oWawi->result[$i]->bestellnr).', '.html_entity_decode($oWawi->result[$i]->firmenname).', '.html_entity_decode($oWawi->result[$i]->titel).' '.html_entity_decode($oWawi->result[$i]->bemerkungen)."\n";
			break;


		// Bestelldetail ID
		case 'wawi_bestelldetail_id':
		 	$bestelldetail_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($bestellung_id) || $bestellung_id=='' || is_null($bestelldetail_id) || $bestelldetail_id=='')
				exit();
			if ($oWawi->bestellpositionen($bestellung_id,null,"%$bestelldetail_id"))
			{
				for ($i=0;$i<count($oWawi->result);$i++)
					echo html_entity_decode($oWawi->result[$i]->bestelldetail_id).'|'.', '.html_entity_decode($oWawi->result[$i]->beschreibung).',  '.html_entity_decode($oWawi->result[$i]->artikelnr).' Preis VE '.html_entity_decode(number_format($oWawi->result[$i]->preisve,2)).', Menge '.html_entity_decode($oWawi->result[$i]->menge).', Pos.summe '.html_entity_decode(number_format($oWawi->result[$i]->summe,2))."\n";
			}
			if ($oWawi->errormsg)
				exit($oWawi->errormsg."\n");

			if (!$oWawi->bestellpositionen($bestellung_id,null,null,null))
				exit($oWawi->errormsg."\n");

			echo "| *** alle Positionen *** \n";
			for ($i=0;$i<count($oWawi->result);$i++)
					echo html_entity_decode($oWawi->result[$i]->bestelldetail_id).'|'.', '.html_entity_decode($oWawi->result[$i]->beschreibung).',  '.html_entity_decode($oWawi->result[$i]->artikelnr).' Preis VE '.html_entity_decode(number_format($oWawi->result[$i]->preisve,2)).', Menge '.html_entity_decode($oWawi->result[$i]->menge).', Pos.summe '.html_entity_decode(number_format($oWawi->result[$i]->summe,2))."\n";
			break;
			
		// Firmen ID
		case 'wawi_firma_id':
		 	$firma_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($firma_id) ||$firma_id=='')
				exit();
			$oWawi = new wawi();
			$oWawi->result=array();
			$oWawi->debug=$debug;
			if (!$oWawi->firma("$firma_id%",null))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
				echo html_entity_decode($oWawi->result[$i]->firma_id).'|'.', '.html_entity_decode($oWawi->result[$i]->firmenname).', '.html_entity_decode($oWawi->result[$i]->strasse).' '.html_entity_decode($oWawi->result[$i]->plz).' '.html_entity_decode($oWawi->result[$i]->ort)."\n";
			break;

		// Firmen Search
		case 'wawi_firma_search':
		 	$firma_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($firma_search) ||$firma_search=='')
				exit();	
			$sFirma = new firma();
			if (!$sFirma->getAll($firma_search))
				exit($sFirma->errormsg."\n");
			for ($i=0;$i<count($sFirma->result);$i++)
				echo html_entity_decode($sFirma->result[$i]->name).'|'.html_entity_decode($sFirma->result[$i]->firma_id)."\n";
			break;
			
		case 'tags':
			$bestell_id = $_REQUEST['bestell_id'];
			
			$tag_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			
			if (is_null($bestell_id) || $tag_search=='')
				exit();	
				
			$tags = new tags(); 
			
			if (!$tags->getAll())
				exit($tags->errormsg."\n");
				
			for ($i=0;$i<count($tags->result);$i++)
				echo html_entity_decode($tags->result[$i]->tag)."\n";
			break;
			
		case 'detail_tags':
			$detail = $_REQUEST['detail_id'];
			
			$tag_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			
			if (is_null($detail) || $tag_search=='')
				exit();	
				
			$tags = new tags(); 
			
			if (!$tags->getAll())
				exit($tags->errormsg."\n");
				
			for ($i=0;$i<count($tags->result);$i++)
				echo html_entity_decode($tags->result[$i]->tag)."\n";
			break;
			
			// Mitarbeiter Search
		case 'wawi_mitarbeiter_search':
		 	$mitarbeiter_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($mitarbeiter_search) ||$mitarbeiter_search=='')
				exit();	
			
			$sMitarbeiter = new mitarbeiter();
			$mitarbeiter_all = array(); 
			$mitarbeiter_all = $sMitarbeiter->getMitarbeiter(null,null,null,$mitarbeiter_search);

			for ($i=0;$i<count($mitarbeiter_all);$i++)
				echo html_entity_decode($mitarbeiter_all[$i]->vorname).' '.html_entity_decode($mitarbeiter_all[$i]->nachname).'|'.html_entity_decode($mitarbeiter_all[$i]->uid)."\n";
			break;
			

		// Kostenstelle ID
		case 'wawi_kostenstelle_id':
		 	$kostenstelle_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($kostenstelle_id) || $kostenstelle_id=='')
				exit();
			if (!$oWawi->kostenstelle("$kostenstelle_id%",null))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->kostenstelle_id).'|'.', Nr :'.html_entity_decode($oWawi->result[$i]->kostenstelle_nr).', '.html_entity_decode($oWawi->result[$i]->bezeichnung).', Stg.:'.html_entity_decode($oWawi->result[$i]->stg_kurzzeichen).' '.html_entity_decode($oWawi->result[$i]->stg_bez).' '
					.' '.($oWawi->result[$i]->stg_aktiv==true || $oWawi->result[$i]->stg_aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;

		// Kostenstelle Nr
		case 'wawi_kostenstelle_nr':
		 	$kostenstelle_nr=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($kostenstelle_nr) || $kostenstelle_nr=='')
				exit();
			if (!$oWawi->kostenstelle(null,null,null,null,"$kostenstelle_nr%"))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->kostenstelle_nr).'|'.', ID :'.html_entity_decode($oWawi->result[$i]->kostenstelle_id).', '.html_entity_decode($oWawi->result[$i]->bezeichnung).', Stg.:'.html_entity_decode($oWawi->result[$i]->stg_kurzzeichen).' '.html_entity_decode($oWawi->result[$i]->stg_bez).' '
					.' '.($oWawi->result[$i]->stg_aktiv==true || $oWawi->result[$i]->stg_aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;

		// Kostenstelle Serch
		case 'wawi_kostenstelle_search':
		 	$kostenstelle_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($kostenstelle_search) || $kostenstelle_search=='')
				exit();
			if (!$oWawi->kostenstelle(null,$kostenstelle_search))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->kostenstelle_nr).'|' .', '. html_entity_decode($oWawi->result[$i]->bezeichnung).' , ID :'.html_entity_decode($oWawi->result[$i]->kostenstelle_id).', Nr.:'.html_entity_decode($oWawi->result[$i]->kostenstelle_nr).', Stg.:'.html_entity_decode($oWawi->result[$i]->stg_kurzzeichen).' '.html_entity_decode($oWawi->result[$i]->stg_bez).' '
					.' '.($oWawi->result[$i]->stg_aktiv==true || $oWawi->result[$i]->stg_aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;

		// Konto ID
		case 'wawi_konto_id':
		 	$konto_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($konto_id) || $konto_id=='')
				exit();
			if (!$oWawi->konto("$konto_id"))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->konto).'|'.', '.html_entity_decode($oWawi->result[$i]->beschreibung)
				."\n";
			}
			break;

		// Konto ID
		case 'wawi_konto_search':
		 	$konto_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($konto_search) || $konto_search=='')
				exit();
			if (!$oWawi->konto(null,null,"$konto_search"))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->konto).'|'.', '.html_entity_decode($oWawi->result[$i]->beschreibung)
				."\n";
			}
			break;

		// Studiengang ID
		case 'wawi_studiengang_id':
		 	$studiengang_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($studiengang_id) || $studiengang_id=='')
				exit();
			if ($kostenstelle_id && !$oWawi->studiengang_kostenstelle("$studiengang_id%",null,null,$kostenstelle_id))
				exit($oWawi->errormsg."\n");
			elseif (!$kostenstelle_id && !$oWawi->studiengang("$studiengang_id%",null,null))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->studiengang_id).'|'.', '.html_entity_decode($oWawi->result[$i]->kurzzeichen) .' '.html_entity_decode($oWawi->result[$i]->bezeichnung)
					.' '.($oWawi->result[$i]->aktiv==true || $oWawi->result[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;


			
		// Studiengang Suche
		case 'wawi_studiengang_search':
		 	$studiengang_search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($studiengang_search) || $studiengang_search=='')
				exit();
			if ($kostenstelle_id && !$oWawi->studiengang_kostenstelle(null,null,"$studiengang_id%",$kostenstelle_id))
				exit($oWawi->errormsg."\n");
			else if (!$oWawi->studiengang(null,null,$studiengang_search))
				exit($oWawi->errormsg."\n");
			for ($i=0;$i<count($oWawi->result);$i++)
			{
				echo html_entity_decode($oWawi->result[$i]->studiengang_id).'|'.', '.html_entity_decode($oWawi->result[$i]->kurzzeichen) .' '.html_entity_decode($oWawi->result[$i]->bezeichnung) 
					.' '.($oWawi->result[$i]->aktiv==true || $oWawi->result[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n";
			}
			break;
	    default:
   	   		echo " Funktion $work fehlt! ";
			break;
	}
	exit();
?>
