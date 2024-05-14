<?php
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
	header( 'Expires:  -1' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Pragma: no-cache' );
	header('Content-Type: text/html;charset=UTF-8');

	require_once('../../config/vilesci.config.inc.php');
  	require_once('../../include/functions.inc.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/mitarbeiter.class.php');
	require_once('../../include/ort.class.php');
	require_once('../../include/studiengang.class.php');
  	require_once('../../include/organisationseinheit.class.php');
  	require_once('../../include/betriebsmittel.class.php');
  	require_once('../../include/betriebsmittelperson.class.php');
  	require_once('../../include/betriebsmitteltyp.class.php');
  	require_once('../../include/betriebsmittelstatus.class.php');
  	require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
  	require_once('../../include/wawi_bestellung.class.php');
  	require_once('../../include/firma.class.php');

  	if (!$uid = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

  	if (!$db = new basis_db())
		die('Datenbank kann nicht geoeffnet werden.  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$rechte = new benutzerberechtigung();
	if(!$rechte->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung fuer diese Seite');
		
	if(!$rechte->isBerechtigt('wawi/inventar:begrenzt', null, 's'))
		die('Sie haben keine Berechtigung fuer diese Seite');

	$errormsg=array();
	$default_status_vorhanden='vorhanden';

	// Parameter Aufruf uebernehmen
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

	// Class - Datenbank	
	$oBetriebsmittel = new betriebsmittel();
	$oBetriebsmittel->result=array();
	$oBetriebsmittel->debug=$debug;	

	$oPerson = new person();
	$oPerson->result=array();
	$oPerson->errormsg='';

	$oOrganisationseinheit = new organisationseinheit();
	$oOrganisationseinheit->result=array();

	/* jQuery autocomplete
	lineSeparator = (default value: "\n")
		The character that separates lines in the results from the backend.
	cellSeparator (default value: "|")
		The character that separates cells in the results from the backend.
	*/
	switch ($work)
	{
		// SerienNummer - Inventarnummern suche
		case 'seriennummer':
		 	$seriennummer=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($seriennummer) || $seriennummer=='')
				exit();
			$pArt='select';
			$pDistinct=true; 
			$pFields='seriennummer,beschreibung';
			$pTable='wawi.tbl_betriebsmittel';
			$matchcode=addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($seriennummer)));
			$pWhere=" upper(trim(seriennummer)) like upper(trim('".$matchcode."%'))";
			$pOrder='seriennummer';
			$pLimit='100';
			$pSql='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");
			
			$result=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
				$item['seriennummer']=html_entity_decode($oRresult[$i]->seriennummer);
				$item['beschreibung']=is_null($oRresult[$i]->beschreibung) || empty($oRresult[$i]->beschreibung) || $oRresult[$i]->beschreibung=='NULL' || $oRresult[$i]->beschreibung=='null'?'':html_entity_decode($oRresult[$i]->beschreibung);
				$result[]=$item;
//				echo html_entity_decode($oRresult[$i]->seriennummer).'|'. (is_null($oRresult[$i]->beschreibung) || empty($oRresult[$i]->beschreibung) || $oRresult[$i]->beschreibung=='NULL' || $oRresult[$i]->beschreibung=='null'?'':html_entity_decode($oRresult[$i]->beschreibung))."\n";
			}
			echo json_encode($result);
			break;

		// Hersteller
		case 'hersteller':
		 	$hersteller=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
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

			$result=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
				$item['hersteller']=html_entity_decode($oRresult[$i]->hersteller);
				$result[]=$item;
//				echo html_entity_decode($oRresult[$i]->hersteller).'|'. ''."\n";
			}
			echo json_encode($result);
			break;

		// Bestellung
		case 'bestellung_id':
		 	$bestellung_id=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($bestellung_id) || $bestellung_id=='')
				exit();
			$pArt='select';
			$pDistinct=true; 
			$pFields='nummer,beschreibung';
			$pTable='wawi.tbl_betriebsmittel';
			$matchcode=addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($nummer)));
			$pWhere=" upper(trim(bestellung_id)) like upper(trim('".$matchcode."%'))";
			$pOrder='bestellung_id';
			$pLimit='100';
			$pSql='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");
			for ($i=0;$i<count($oRresult);$i++)
				echo html_entity_decode($oRresult[$i]->seriennummer).'|'. (is_null($oRresult[$i]->beschreibung) || empty($oRresult[$i]->beschreibung) || $oRresult[$i]->beschreibung=='NULL' || $oRresult[$i]->beschreibung=='null'?'':html_entity_decode($oRresult[$i]->beschreibung))."\n";
			break;

		// Betriebsmittel Inventarnummer
		case 'inventarnummer':
		 	$inventarnummer=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($inventarnummer) || $inventarnummer=='')
				exit();
			$pArt='select';
			$pDistinct=true; 
			$pFields='inventarnummer,beschreibung';
			$pTable='wawi.tbl_betriebsmittel';
			$matchcode=addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($inventarnummer)));
			$pWhere=" upper(trim(inventarnummer)) like upper(trim('".$matchcode."%'))";
			$pOrder='inventarnummer';
			$pLimit='100';
			$pSql='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");

			$result=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
				$item['inventarnummer']=html_entity_decode($oRresult[$i]->inventarnummer);
				$item['beschreibung']=is_null($oRresult[$i]->beschreibung) || empty($oRresult[$i]->beschreibung) || $oRresult[$i]->beschreibung=='NULL' || $oRresult[$i]->beschreibung=='null'?'':html_entity_decode(mb_str_replace("\n","",$oRresult[$i]->beschreibung));
				$result[]=$item;
//				echo html_entity_decode($oRresult[$i]->inventarnummer).'|'. (is_null($oRresult[$i]->beschreibung) || empty($oRresult[$i]->beschreibung) || $oRresult[$i]->beschreibung=='NULL' || $oRresult[$i]->beschreibung=='null'?'':html_entity_decode(mb_str_replace("\n","",$oRresult[$i]->beschreibung)))."\n";
			}
			echo json_encode($result);
			break;

		// Ort - Inventarorte suche
		case 'inventar_ort':
		 	$ort_kurzbz=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($ort_kurzbz) || $ort_kurzbz=='')
				exit();
			$pArt='select';
			$pDistinct=true; 
			$pFields='tbl_betriebsmittel.ort_kurzbz,tbl_ort.bezeichnung,tbl_ort.aktiv';
			$pTable='wawi.tbl_betriebsmittel left outer join public.tbl_ort on (tbl_ort.ort_kurzbz=tbl_betriebsmittel.ort_kurzbz) ';
			$matchcode=addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($ort_kurzbz)));
			$pWhere=" upper(trim(tbl_betriebsmittel.ort_kurzbz)) like upper(trim('%".$matchcode."%')) or upper(trim(tbl_ort.bezeichnung)) like upper(trim('%".$matchcode."%'))";
			$pOrder='tbl_betriebsmittel.ort_kurzbz';
			$pLimit='100';
			$pSql='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");

			$result=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
				$item['ort_kurzbz']=html_entity_decode($oRresult[$i]->ort_kurzbz);
				$item['bezeichnung']=is_null($oRresult[$i]->bezeichnung) || empty($oRresult[$i]->bezeichnung) || $oRresult[$i]->bezeichnung=='NULL' || $oRresult[$i]->bezeichnung=='null'?'':html_entity_decode($oRresult[$i]->bezeichnung);
				$item['aktiv']=$oRresult[$i]->aktiv==true || $oRresult[$i]->aktiv=='t'?true:false;
				$result[]=$item;
/*				echo html_entity_decode($oRresult[$i]->ort_kurzbz).'|'
								.(is_null($oRresult[$i]->bezeichnung) || empty($oRresult[$i]->bezeichnung) || $oRresult[$i]->bezeichnung=='NULL' || $oRresult[$i]->bezeichnung=='null'?'':html_entity_decode($oRresult[$i]->bezeichnung))
								.($oRresult[$i]->aktiv==true || $oRresult[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
								."\n"; */
			}
			echo json_encode($result);
			break;

		// Ort - FH Technikum suche
		case 'ort':
		 	$ort_kurzbz=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($ort_kurzbz) || $ort_kurzbz=='')
				exit();
			$pArt='select';
			$pDistinct=true; 
			$pFields='tbl_ort.ort_kurzbz,tbl_ort.bezeichnung,tbl_ort.aktiv';
			$pTable=' public.tbl_ort ';
			$matchcode=addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($ort_kurzbz)));
			$pWhere=" tbl_ort.aktiv and ( upper(trim(tbl_ort.ort_kurzbz)) like upper(trim('%".$matchcode."%')) or upper(trim(tbl_ort.bezeichnung)) like upper(trim('%".$matchcode."%')) )";
			$pOrder='tbl_ort.ort_kurzbz';
			$pLimit='100';
			$pSql='';
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");

			$result=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
				$item['ort_kurzbz']=html_entity_decode($oRresult[$i]->ort_kurzbz);
				$item['bezeichnung']=is_null($oRresult[$i]->bezeichnung) || empty($oRresult[$i]->bezeichnung) || $oRresult[$i]->bezeichnung=='NULL' || $oRresult[$i]->bezeichnung=='null'?'':html_entity_decode($oRresult[$i]->bezeichnung);
				$item['aktiv']=$oRresult[$i]->aktiv==true || $oRresult[$i]->aktiv=='t'?true:false;
				$result[]=$item;

/*					echo html_entity_decode($oRresult[$i]->ort_kurzbz).'|'
								.(is_null($oRresult[$i]->bezeichnung) || empty($oRresult[$i]->bezeichnung) || $oRresult[$i]->bezeichnung=='NULL' || $oRresult[$i]->bezeichnung=='null'?'':html_entity_decode($oRresult[$i]->bezeichnung) )
								.($oRresult[$i]->aktiv==true || $oRresult[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
								."\n"; */
			}
			echo json_encode($result);
			break;
			
		// Person - FH Technikum suche
		case 'person':
		 	$person_id=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
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
				$pSql="SELECT distinct vw_benutzer.person_id,vw_benutzer.aktiv,uid,person_id,titelpre,anrede,vorname,nachname,vornamen,titelpost,funktion_kurzbz 
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
				$pFields='person_id,titelpre,anrede,vorname,nachname,vornamen,aktiv,\'\' as funktion_kurzbz';
				$pTable=' campus.vw_benutzer ';
				$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
				$pOrder='nachname';
				$pLimit='100';
				$pSql='';
			}
			if (!$oRresult=$db->SQL($pArt,$pDistinct,$pFields,$pTable,$pWhere,$pOrder,$pLimit,$pSql))
				exit(' |'.$db->errormsg."\n");

			$result=array();
			for ($i=0;$i<count($oRresult);$i++)
			{
				$item['person_id']=html_entity_decode($oRresult[$i]->person_id);
				$item['anrede']=trim($oRresult[$i]->anrede);
				$item['titelpre']=html_entity_decode($oRresult[$i]->titelpre);
				$item['vorname']=html_entity_decode($oRresult[$i]->vorname);
				$item['nachname']=html_entity_decode($oRresult[$i]->nachname);
				$item['funktion']=html_entity_decode($oRresult[$i]->funktion_kurzbz);
				$item['aktiv']=$oRresult[$i]->aktiv;
				$result[]=$item;
/*				echo html_entity_decode($oRresult[$i]->person_id).'|'
									.trim($oRresult[$i]->anrede).'&nbsp;'.($oRresult[$i]->titelpre?html_entity_decode($oRresult[$i]->titelpre).'&nbsp;':'')
									.html_entity_decode($oRresult[$i]->vorname).' '.html_entity_decode($oRresult[$i]->nachname).($oRresult[$i]->funktion_kurzbz?html_entity_decode($oRresult[$i]->funktion_kurzbz).'&nbsp;':'') 
									.($oRresult[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n"; */
			}
			echo json_encode($result);
			break;

		// Organisation -  suche
		case 'organisationseinheit':
		 	$oe_kurzbz=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($oe_kurzbz) || $oe_kurzbz=='')
				exit();

			$oOrganisationseinheit->result=array();
			if (!$oOrganisationseinheit->getAll())
				exit($oOrganisationseinheit->errormsg."\n");
			
			$result=array();
			for ($i=0;$i<count($oOrganisationseinheit->result);$i++)
			{
				if ($aktiv && ($oOrganisationseinheit->result[$i]->aktiv==false || $oOrganisationseinheit->result[$i]->aktiv=='f'))
					break;

				if (!$oe_kurzbz
				|| stristr($oOrganisationseinheit->result[$i]->oe_kurzbz,$oe_kurzbz)
				|| stristr($oOrganisationseinheit->result[$i]->bezeichnung,$oe_kurzbz)
				|| stristr($oOrganisationseinheit->result[$i]->oe_parent_kurzbz,$oe_kurzbz)
				|| stristr($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz,$oe_kurzbz) )
				{
					$item['oe_kurzbz']=html_entity_decode($oOrganisationseinheit->result[$i]->oe_kurzbz);
					$item['bezeichnung']=html_entity_decode($oOrganisationseinheit->result[$i]->bezeichnung);
					$item['organisationseinheittyp']=html_entity_decode($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz);
					$result[]=$item;
/*					echo html_entity_decode($oOrganisationseinheit->result[$i]->oe_kurzbz).'|'
					.'&nbsp;'
					.(is_null($oOrganisationseinheit->result[$i]->bezeichnung) || empty($oOrganisationseinheit->result[$i]->bezeichnung) || $oOrganisationseinheit->result[$i]->bezeichnung=='NULL' || $oOrganisationseinheit->result[$i]->bezeichnung=='null'?'':html_entity_decode($oOrganisationseinheit->result[$i]->bezeichnung) )
					.'&nbsp;'
					.(is_null($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz) || empty($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz) || $oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz=='NULL' || $oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz=='null'?'':html_entity_decode($oOrganisationseinheit->result[$i]->organisationseinheittyp_kurzbz) )

					.($oOrganisationseinheit->result[$i]->aktiv==true || $oOrganisationseinheit->result[$i]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />')
					."\n"; */
				}
			}
			echo json_encode($result);
			break;

		// Bestellung
		case 'wawi_bestellnr':
		 	$filter=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($filter) || $filter=='')
				exit();
			$bestellung = new wawi_bestellung();

			if ($bestellung->getAllSearch($filter, '', '', '', '', '', '', '', '', '', '', ''))
			{
				$result=array();
				foreach($bestellung->result as $row)
				{
					$item['bestell_nr']=html_entity_decode($row->bestell_nr);
					$item['insertamum']=html_entity_decode($bestellung->insertamum);
					$item['titel']=html_entity_decode($row->titel);
					$item['bemerkung']=html_entity_decode($row->bemerkung);
					$result[]=$item;
//					echo html_entity_decode($row->bestell_nr).'|'.html_entity_decode($bestellung->insertamum).',  '.html_entity_decode($row->bestell_nr).', '.html_entity_decode($row->titel).' '.html_entity_decode($row->bemerkung)."\n";
				}
				echo json_encode($result);
			}
			else
				exit($bestellung->errormsg."\n");

			break;

		// Bestellung ID
		case 'wawi_bestellung_id':
		 	$filter=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($filter) || $filter=='')
				exit();
			$bestellung = new wawi_bestellung();

			if ($bestellung->getBestellung($filter))
			{
				$result=array();
				foreach($bestellung->result as $row)
				{
					$item['bestellung_id']=html_entity_decode($row->bestellung_id);
					$item['insertamum']=html_entity_decode($bestellung->insertamum);
					$item['bestell_nr']=html_entity_decode($row->bestell_nr);
					$item['titel']=html_entity_decode($row->titel);
					$item['bemerkung']=html_entity_decode($row->bemerkung);
					$result[]=$item;
//					echo html_entity_decode($row->bestellung_id).'|'.html_entity_decode($bestellung->insertamum).',  '.html_entity_decode($row->bestell_nr).', '.html_entity_decode($row->titel).' '.html_entity_decode($row->bemerkung)."\n";
				}
				echo json_encode($result);
			}
			else
				exit($bestellung->errormsg."\n");

			break;


		// Bestelldetail ID
		case 'wawi_bestelldetail_id':
		 	$filter=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($bestellung_id) || $bestellung_id=='' || is_null($filter) || $filter=='')
			{
				echo "bestellung_id oder filter ist leer";
				exit();
			}
			
			$bestelldetail = new wawi_bestelldetail();
			$bestelldetail->getAllDetailsFromBestellung($bestellung_id, $filter);
			
			$result=array();
			foreach($bestelldetail->result as $row)
			{
				$item['bestelldetail_id']=html_entity_decode($row->bestelldetail_id);
				$item['beschreibung']=html_entity_decode($row->beschreibung);
				$item['artikelnummer']=html_entity_decode($row->artikelnummer);
				$item['preisprove']=html_entity_decode(number_format($row->preisprove,2));
				$item['menge']=html_entity_decode($row->menge);
				$result[]=$item;
//				echo html_entity_decode($row->bestelldetail_id).'|'.', '.html_entity_decode($row->beschreibung).',  '.html_entity_decode($row->artikelnummer).' Preis VE '.html_entity_decode(number_format($row->preisprove,2)).', Menge '.html_entity_decode($row->menge)."\n";
			}
			echo json_encode($result);
			break;
			
		
		// Firmen Search
		case 'wawi_firma_search':
		 	$firma_search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($firma_search) ||$firma_search=='')
				exit();
			$firma = new firma();
			$firma->searchFirma($firma_search);
			
			$result=array();
			foreach($firma->result as $row)
			{
				$item['firma_id']=html_entity_decode($row->firma_id);
				$item['name']=html_entity_decode($row->name);
				$result[]=$item;
//				echo html_entity_decode($row->firma_id).'|'.', '.html_entity_decode($row->name)."\n";
			}
			echo json_encode($result);
			break;

	    default:
   	   		echo " Funktion $work fehlt! ";
			break;
	}
	exit();
?>