<?php
/* Copyright (C) 2006 fhcomplete.org
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
/**
 * Klasse ort (FAS-Online)
 * @create 04-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class ort extends basis_db
{
	public $new;     			// boolean
	public $result = array(); 	// ort Objekt

	//Tabellenspalten
	public $ort_kurzbz;		// string
	public $bezeichnung;	// string
	public $planbezeichnung;// string
	public $max_person;		// integer
	public $lehre;			// boolean
	public $reservieren;	// boolean
	public $aktiv;			// boolean
	public $lageplan;		// text
	public $dislozierung;	// smallint
	public $kosten;			// numeric(8,2)
	public $ausstattung;	// text
	public $stockwerk;		// integer
	public $standort_id; 	// varchar(16)
	public $telefonklappe;	// varchar(8)
	public $updateamum;		// timestamp without timezone
	public $updatevon;		// varchar(32)
	public $insertamum;		// timestamp without timezone
	public $insertvon;		// varchar(32)
	public $content_id;		// integer
	public $oe_kurzbz;		// varchar(32)
	public $m2;				// numeric(8,2)
	public $gebteil;		// varchar(32)
	public $arbeitsplaetze;	// integer
	
	public $ort_kurzbz_old;	// string

	/**
	 * Konstruktor
	 * @param $ort_kurzbz Kurzbz des zu ladenden Ortes
	 */
	public function __construct($ort_kurzbz=null)
	{
		parent::__construct();
				
		if($ort_kurzbz != null)
			$this->load($ort_kurzbz);
	}

	/**
	 * Laedt alle verfuegbaren Orte
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($raumtyp_kurzbz = null)
	{
		$qry = 'SELECT * FROM public.tbl_ort ORDER BY ort_kurzbz;';

		if (!is_null($raumtyp_kurzbz) && $raumtyp_kurzbz != '')
		{
			$qry = '
				SELECT 
					tbl_ort.* 
				FROM 
					public.tbl_ort 
					JOIN public.tbl_ortraumtyp USING(ort_kurzbz) 
				WHERE raumtyp_kurzbz = ' . $this->db_add_param($raumtyp_kurzbz) .
				'ORDER BY ort_kurzbz;';
		}
		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$ort_obj = new ort();

			$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
			$ort_obj->bezeichnung 		= $row->bezeichnung;
			$ort_obj->planbezeichnung 	= $row->planbezeichnung;
			$ort_obj->max_person 		= $row->max_person;
			$ort_obj->aktiv 			= $this->db_parse_bool($row->aktiv);
			$ort_obj->lehre 			= $this->db_parse_bool($row->lehre);
			$ort_obj->lageplan 			= $row->lageplan;
			$ort_obj->dislozierung 		= $row->dislozierung;
			$ort_obj->kosten 			= $row->kosten;
			$ort_obj->reservieren		= $this->db_parse_bool($row->reservieren);
			$ort_obj->ausstattung		= $row->ausstattung;
			$ort_obj->stockwerk			= $row->stockwerk;
			$ort_obj->standort_id		= $row->standort_id;
			$ort_obj->telefonklappe		= $row->telefonklappe;
			$ort_obj->content_id		= $row->content_id;
			$ort_obj->m2				= $row->m2;
			$ort_obj->oe_kurzbz			= $row->oe_kurzbz;
			$ort_obj->gebteil			= $row->gebteil;
			$ort_obj->arbeitsplaetze	= $row->arbeitsplaetze;
			$this->result[] = $ort_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle Orte die den Parametern entsprechen
	 * @param boolean $lehre Optional.
	 * @param boolean $reservieren Optional.
	 * @param boolean $aktiv Optional.
	 * @param int $standort_id Optional.
	 * @param string $gebaeudeteil Optional.
	 * @param string $oe_kurzbz Optional.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getOrte($lehre = null, $reservieren = null, $aktiv = null, $standort_id = '', $gebaeudeteil = '', $oe_kurzbz = '')
	{
		$qry = 'SELECT * FROM public.tbl_ort WHERE 1=1 ';
		
		if (!is_null($lehre) && is_bool($lehre))
			$qry .= ' AND lehre = '.$this->db_add_param($lehre, FHC_BOOLEAN);
		
		if (!is_null($reservieren) && is_bool($reservieren))
			$qry .= ' AND reservieren = '.$this->db_add_param($reservieren, FHC_BOOLEAN);
		
		if (!is_null($aktiv) && is_bool($aktiv))
			$qry .= ' AND aktiv = '.$this->db_add_param($aktiv, FHC_BOOLEAN);
		
		if ($standort_id != '' && is_numeric($standort_id))
			$qry .= ' AND standort_id = '.$this->db_add_param($standort_id, FHC_INTEGER);
			
		if ($gebaeudeteil != '')
			$qry .= ' AND gebteil = '.$this->db_add_param($gebaeudeteil);
			
		if ($oe_kurzbz != '')
			$qry .= ' AND oe_kurzbz = '.$this->db_add_param($oe_kurzbz);
		
		$qry .= ' ORDER BY ort_kurzbz;'; 

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while ($row = $this->db_fetch_object())
		{
			$ort_obj = new ort();
			
			$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
			$ort_obj->bezeichnung 		= $row->bezeichnung;
			$ort_obj->planbezeichnung 	= $row->planbezeichnung;
			$ort_obj->max_person 		= $row->max_person;
			$ort_obj->aktiv 			= $this->db_parse_bool($row->aktiv);
			$ort_obj->lehre 			= $this->db_parse_bool($row->lehre);
			$ort_obj->lageplan 			= $row->lageplan;
			$ort_obj->dislozierung 		= $row->dislozierung;
			$ort_obj->kosten 			= $row->kosten;
			$ort_obj->reservieren		= $this->db_parse_bool($row->reservieren);
			$ort_obj->ausstattung		= $row->ausstattung;
			$ort_obj->stockwerk			= $row->stockwerk;
			$ort_obj->standort_id		= $row->standort_id;
			$ort_obj->telefonklappe		= $row->telefonklappe;
			$ort_obj->content_id		= $row->content_id;
			$ort_obj->m2				= $row->m2;
			$ort_obj->oe_kurzbz			= $row->oe_kurzbz;
			$ort_obj->gebteil			= $row->gebteil;
			$ort_obj->arbeitsplaetze	= $row->arbeitsplaetze;
			$this->result[] = $ort_obj;
		}
		return true;
	}

	/**
	 * Laedt einen Ort
	 * @param $fachb_id ID des zu ladenden Ortes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($ort_kurzbz)
	{
		if ($ort_kurzbz == '')
		{
			$this->errormsg = 'kurzbz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_ort WHERE trim(ort_kurzbz) = " . $this->db_add_param(trim($ort_kurzbz)) . ";";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if ($row = $this->db_fetch_object())
		{
			$this->ort_kurzbz 		= $row->ort_kurzbz;
			$this->bezeichnung 		= $row->bezeichnung;
			$this->planbezeichnung	= $row->planbezeichnung;
			$this->max_person 		= $row->max_person;
			$this->aktiv 			= $this->db_parse_bool($row->aktiv);
			$this->lehre 			= $this->db_parse_bool($row->lehre);
			$this->lageplan 		= $row->lageplan;
			$this->dislozierung 	= $row->dislozierung;
			$this->kosten 			= $row->kosten;
			$this->reservieren		= $this->db_parse_bool($row->reservieren);
			$this->ausstattung		= $row->ausstattung;
			$this->stockwerk		= $row->stockwerk;
			$this->standort_id		= $row->standort_id;
			$this->telefonklappe	= $row->telefonklappe;
			$this->content_id 		= $row->content_id;
			$this->gebteil			= $row->gebteil;
			$this->oe_kurzbz		= $row->oe_kurzbz;
			$this->m2				= $row->m2;
			$this->arbeitsplaetze	= $row->arbeitsplaetze;
		}
		else
		{
			$this->errormsg = 'Ort wurde nicht gefunden';
			return false;
		}

		return true;
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		//Laenge Pruefen
		if(mb_strlen($this->bezeichnung)>64)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->planbezeichnung)>30)
		{
			$this->errormsg = 'Planbezeichnung darf nicht laenger als 30 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->ort_kurzbz)>16)
		{
			$this->errormsg = 'Ort_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->ort_kurzbz == '')
		{
			$this->errormsg = 'ort_kurzbz darf nicht leer sein';
			return false;
		}
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_ort (ort_kurzbz, bezeichnung, planbezeichnung, max_person, aktiv, lehre, reservieren, lageplan,
				dislozierung, kosten, stockwerk, standort_id, telefonklappe, insertamum, insertvon, updateamum, updatevon, content_id,ausstattung,m2,gebteil,oe_kurzbz,arbeitsplaetze) VALUES ('.
				$this->db_add_param($this->ort_kurzbz).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->planbezeichnung).', '.
				$this->db_add_param($this->max_person).', '.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				$this->db_add_param($this->lehre, FHC_BOOLEAN).', '.
				$this->db_add_param($this->reservieren, FHC_BOOLEAN).', '.
				$this->db_add_param($this->lageplan).', '.
				$this->db_add_param($this->dislozierung).', '.
				$this->db_add_param(str_replace(",",".",$this->kosten)).', '.
				$this->db_add_param($this->stockwerk).','.
				$this->db_add_param($this->standort_id).','.
				$this->db_add_param($this->telefonklappe).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).','.
				$this->db_add_param($this->updateamum).','.
				$this->db_add_param($this->updatevon).','.
				$this->db_add_param($this->content_id).','.
				$this->db_add_param($this->ausstattung).','.
				$this->db_add_param($this->m2).','.
				$this->db_add_param($this->gebteil).','.
				$this->db_add_param($this->oe_kurzbz).','.
				$this->db_add_param($this->arbeitsplaetze).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			$qry = 'UPDATE public.tbl_ort SET '.
				'ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'planbezeichnung='.$this->db_add_param($this->planbezeichnung).', '.
				'max_person='.$this->db_add_param($this->max_person).', '.
				'ausstattung='.$this->db_add_param($this->ausstattung).','.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN) .', '.
				'lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN) .', '.
				'reservieren='.$this->db_add_param($this->reservieren, FHC_BOOLEAN) .', '.
				'lageplan='.$this->db_add_param($this->lageplan).', '.
				'dislozierung='.$this->db_add_param($this->dislozierung).', '.
				'kosten='.$this->db_add_param(str_replace(",",".",$this->kosten)).', '.
				'standort_id='.$this->db_add_param($this->standort_id).', '.
				'telefonklappe='.$this->db_add_param($this->telefonklappe).', '.
				'stockwerk='.$this->db_add_param($this->stockwerk).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
				'updatevon='.$this->db_add_param($this->updatevon).', '.
				'content_id='.$this->db_add_param($this->content_id).', '.
				'm2='.$this->db_add_param($this->m2).', '.
				'gebteil='.$this->db_add_param($this->gebteil).', '.
				'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).', '.
				'arbeitsplaetze='.$this->db_add_param($this->arbeitsplaetze).' '.
				'WHERE ort_kurzbz = '.$this->db_add_param(($this->ort_kurzbz_old!='')?$this->ort_kurzbz_old:$this->ort_kurzbz).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Sucht nach freien Raeumen
	 * @param datum    ... Datum fuer das der Raum gesucht wird
	 *        zeit_von ... Zeit ab wann soll der Raum frei sein
	 *        zeit_bis ... Zeit bis wann soll der Raum frei sein
	 *        raumtyp  ... Art des Raumes (optional)
	 *        anzpersonen ... Anzahl der Personen die mindestens Platz haben sollen (optional)
	 *        reservierung ... true wenn nur Raeume aufscheinen sollen die auch Reservierbar sind
	 *        db_table ... Stundenplantabelle die geprueft werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function search($datum, $zeit_von, $zeit_bis, $raumtyp=null, $anzpersonen=null, $reservierung=true, $db_table='stundenplandev')
	{
		$stundevon = 1;
		$stundebis = 1;
		if(!is_numeric($anzpersonen))
		{
			$this->errormsg='Anzahl der Personen muss eine gueltige Zahl sein';
			return false;
		}
		
		//stundevon ermitteln
		$qry = "SELECT min(stunde) as stunde FROM (
				SELECT stunde, extract(epoch from (beginn-(".$this->db_add_param($zeit_von)."::time))) AS delta FROM lehre.tbl_stunde
				UNION
				SELECT stunde, extract(epoch from (ende-(".$this->db_add_param($zeit_von)."::time))) AS delta FROM lehre.tbl_stunde
				) foo WHERE delta>0";
		
		if($this->db_query($qry))
			if($row = $this->db_fetch_object())
				$stundevon = $row->stunde;

		//stundebis ermitteln
		$qry = "SELECT min(stunde) as stunde FROM (
				SELECT stunde, extract(epoch from (beginn-(".$this->db_add_param($zeit_bis)."::time))) AS delta FROM lehre.tbl_stunde
				UNION
				SELECT stunde, extract(epoch from (ende-(".$this->db_add_param($zeit_bis)."::time))) AS delta FROM lehre.tbl_stunde
				) foo WHERE delta>=0";
		
		if($this->db_query($qry))
			if($row = $this->db_fetch_object())
				$stundebis = $row->stunde;

		if($stundevon=='')
			$stundevon=1;
		if($stundebis=='')
		{
			$stundebis=20;
			$qry = "SELECT max(stunde) as max FROM lehre.tbl_stunde";
			if($this->db_query($qry))
				if($row = $this->db_fetch_object())
					$stundebis=$row->max;
		}
		//echo $stundevon.'-'.$stundebis;
		
		//Freie Raeume suchen
		$qry = "SELECT 
			DISTINCT tbl_ort.* 
		FROM 
			public.tbl_ort JOIN public.tbl_ortraumtyp USING(ort_kurzbz) 
		WHERE 
			aktiv AND lehre AND ort_kurzbz NOT LIKE '\\\\_%'";
		//derzeit noch nicht in verwendung
		//if($reservierung)
		//	$qry.=" AND reservieren";
		if($raumtyp!=null)
			$qry.=" AND raumtyp_kurzbz=".$this->db_add_param($raumtyp);
		if($anzpersonen!=null)
			$qry.=" AND (max_person>=".$this->db_add_param($anzpersonen)." OR max_person is null)";
		 
		$qry.="	AND ort_kurzbz NOT IN 
			(
				SELECT ort_kurzbz FROM lehre.tbl_$db_table WHERE datum=".$this->db_add_param($datum)." AND stunde>=".$this->db_add_param($stundevon)." AND stunde<=".$this->db_add_param($stundebis)."
				UNION
				SELECT ort_kurzbz FROM campus.tbl_reservierung WHERE datum=".$this->db_add_param($datum)." AND stunde>=".$this->db_add_param($stundevon)." AND stunde<=".$this->db_add_param($stundebis)."
			)
		";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$ort_obj = new ort();
	
				$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
				$ort_obj->bezeichnung 		= $row->bezeichnung;
				$ort_obj->planbezeichnung 	= $row->planbezeichnung;
				$ort_obj->max_person 		= $row->max_person;
				$ort_obj->aktiv 			= $this->db_parse_bool($row->aktiv);
				$ort_obj->lehre 			= $this->db_parse_bool($row->lehre);
				$ort_obj->lageplan 			= $row->lageplan;
				$ort_obj->dislozierung 		= $row->dislozierung;
				$ort_obj->kosten 			= $row->kosten;
				$ort_obj->reservieren		= $this->db_parse_bool($row->reservieren);
				$ort_obj->ausstattung		= $row->ausstattung;
				$ort_obj->stockwerk			= $row->stockwerk;
				$ort_obj->standort_id		= $row->standort_id;
				$ort_obj->telefonklappe		= $row->telefonklappe;
				$ort_obj->content_id 		= $row->content_id;
				$ort_obj->m2 				= $row->m2;
				$ort_obj->gebteil 			= $row->gebteil;
				$ort_obj->oe_kurzbz 		= $row->oe_kurzbz;
				$ort_obj->arbeitsplaetze	= $row->arbeitsplaetze;
	
				$this->result[] = $ort_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln eines Raumes';
			return false;
		}
	}
	
	/**
	 * Sucht nach einem Ort. Wenn aktiv-parameter true, dann nur aktive. Wenn lehre-parameter true, dann nur lehre. Wenn $klappe-parameter true wird auch nach Telefonklappen gesucht.
	 * @return true wenn ok, false im Fehlerfall
	 * 
	 * @param boolean aktiv (optional) default:false
	 * @param boolean lehre (optional) default:false
	 * @param boolean klappe (optional) default:false
	 */
	public function filter($filter, $aktiv=false, $lehre=false, $klappe=false)
	{
		$qry = "
			SELECT 
				* 
			FROM
				public.tbl_ort 
			WHERE (
				lower(ort_kurzbz) like '%".$this->db_escape(mb_strtolower($filter))."%'
				OR lower(bezeichnung) like '%".$this->db_escape(mb_strtolower($filter))."%'";
			if($klappe==true)
				$qry.=" OR lower(telefonklappe) like '%".$this->db_escape(mb_strtolower($filter))."%'";
			$qry.=")";
			if ($aktiv==true)
				$qry.= " AND aktiv=true";
			if ($lehre==true)
				$qry.= " AND lehre=true";
			$qry.= " ORDER BY ort_kurzbz;";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$ort_obj = new ort();

			$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
			$ort_obj->bezeichnung 		= $row->bezeichnung;
			$ort_obj->planbezeichnung 	= $row->planbezeichnung;
			$ort_obj->max_person 		= $row->max_person;
			$ort_obj->aktiv 			= $this->db_parse_bool($row->aktiv);
			$ort_obj->lehre 			= $this->db_parse_bool($row->lehre);
			$ort_obj->lageplan 			= $row->lageplan;
			$ort_obj->dislozierung 		= $row->dislozierung;
			$ort_obj->kosten 			= $row->kosten;
			$ort_obj->reservieren		= $this->db_parse_bool($row->reservieren);
			$ort_obj->ausstattung		= $row->ausstattung;
			$ort_obj->stockwerk			= $row->stockwerk;
			$ort_obj->standort_id		= $row->standort_id;
			$ort_obj->telefonklappe		= $row->telefonklappe;
			$ort_obj->content_id		= $row->content_id;
			$ort_obj->m2 				= $row->m2;
			$ort_obj->gebteil 			= $row->gebteil;
			$ort_obj->oe_kurzbz 		= $row->oe_kurzbz;
			$ort_obj->arbeitsplaetze	= $row->arbeitsplaetze;

			$this->result[] = $ort_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle verfuegbaren Orte
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getActive($aktiv=true, $lehre=true, $raumtyp_kurzbz=null)
	{
		$qry = 'SELECT * FROM public.tbl_ort WHERE aktiv='.$this->db_add_param($aktiv, FHC_BOOLEAN);
		
		if(!is_null($raumtyp_kurzbz) && $raumtyp_kurzbz!='')
		{
		    $qry .= ' AND raumtyp_kurzbz='.$this->db_add_param($raumtyp_kurzbz, FHC_STRING);
		}
		if(!is_null($lehre))
		{
		    $qry .= ' AND lehre='.$this->db_add_param($lehre, FHC_BOOLEAN);
		}
		
		$qry .= ' ORDER BY ort_kurzbz;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$ort_obj = new ort();

			$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
			$ort_obj->bezeichnung 		= $row->bezeichnung;
			$ort_obj->planbezeichnung 	= $row->planbezeichnung;
			$ort_obj->max_person 		= $row->max_person;
			$ort_obj->aktiv 			= $this->db_parse_bool($row->aktiv);
			$ort_obj->lehre 			= $this->db_parse_bool($row->lehre);
			$ort_obj->lageplan 			= $row->lageplan;
			$ort_obj->dislozierung 		= $row->dislozierung;
			$ort_obj->kosten 			= $row->kosten;
			$ort_obj->reservieren		= $this->db_parse_bool($row->reservieren);
			$ort_obj->ausstattung		= $row->ausstattung;
			$ort_obj->stockwerk			= $row->stockwerk;
			$ort_obj->standort_id		= $row->standort_id;
			$ort_obj->telefonklappe		= $row->telefonklappe;
			$ort_obj->content_id		= $row->content_id;
			$ort_obj->m2				= $row->m2;
			$ort_obj->oe_kurzbz			= $row->oe_kurzbz;
			$ort_obj->gebteil			= $row->gebteil;
			$ort_obj->arbeitsplaetze	= $row->arbeitsplaetze;
			$this->result[] = $ort_obj;
		}
		return true;
	}
	
	/**
	 * Laedt einen Ort (Limit 1) anhand seiner Planbezeichnung
	 * @param boolean aktiv (optional) default:true
	 * @param boolean lehre (optional) default:true
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getOrtByPlanbezeichnung($planbezeichnung, $aktiv=true, $lehre=true)
	{
		$qry = 'SELECT * FROM public.tbl_ort WHERE planbezeichnung='.$this->db_add_param($planbezeichnung);
	
		if(!is_null($aktiv))
		{
			$qry .= ' AND aktiv='.$this->db_add_param($aktiv, FHC_BOOLEAN);
		}
		if(!is_null($lehre))
		{
			$qry .= ' AND lehre='.$this->db_add_param($lehre, FHC_BOOLEAN);
		}
	
		$qry .= ' ORDER BY ort_kurzbz LIMIT 1;';
	
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
	
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$ort_obj = new ort();
		
				$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
				$ort_obj->bezeichnung 		= $row->bezeichnung;
				$ort_obj->planbezeichnung 	= $row->planbezeichnung;
				$ort_obj->max_person 		= $row->max_person;
				$ort_obj->aktiv 			= $this->db_parse_bool($row->aktiv);
				$ort_obj->lehre 			= $this->db_parse_bool($row->lehre);
				$ort_obj->lageplan 			= $row->lageplan;
				$ort_obj->dislozierung 		= $row->dislozierung;
				$ort_obj->kosten 			= $row->kosten;
				$ort_obj->reservieren		= $this->db_parse_bool($row->reservieren);
				$ort_obj->ausstattung		= $row->ausstattung;
				$ort_obj->stockwerk			= $row->stockwerk;
				$ort_obj->standort_id		= $row->standort_id;
				$ort_obj->telefonklappe		= $row->telefonklappe;
				$ort_obj->content_id		= $row->content_id;
				$ort_obj->m2				= $row->m2;
				$ort_obj->oe_kurzbz			= $row->oe_kurzbz;
				$ort_obj->gebteil			= $row->gebteil;
				$ort_obj->arbeitsplaetze	= $row->arbeitsplaetze;
				$this->result[] = $ort_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
	}
}
?>
