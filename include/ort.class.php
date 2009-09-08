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
	public $bezeichnung;		// string
	public $planbezeichnung;	// string
	public $max_person;		// integer
	public $lehre;				// boolean
	public $reservieren;		// boolean
	public $aktiv;				// boolean
	public $lageplan;			// oid
	public $dislozierung;		// smallint
	public $kosten;			// numeric(8,2)
	public $ausstattung;
	public $stockwerk;			// integer
	public $standort_kurzbz; 	// varchar(16)
	public $telefonklappe;		// varchar(8)

	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $ort_kurzbz Kurzbz des zu ladenden Ortes
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
	public function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_ort ORDER BY ort_kurzbz;';

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
			$ort_obj->aktiv 			= ($row->aktiv=='t'?true:false);
			$ort_obj->lehre 			= ($row->lehre=='t'?true:false);
			$ort_obj->lageplan 			= $row->lageplan;
			$ort_obj->dislozierung 		= $row->dislozierung;
			$ort_obj->kosten 			= $row->kosten;
			$ort_obj->reservieren		= ($row->reservieren=='t'?true:false);
			$ort_obj->ausstattung		= $row->ausstattung;
			$ort_obj->stockwerk			= $row->stockwerk;
			$ort_obj->standort_kurzbz	= $row->standort_kurzbz;
			$ort_obj->telefonklappe		= $row->telefonklappe;

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
		if($ort_kurzbz == '')
		{
			$this->errormsg = 'kurzbz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_ort WHERE ort_kurzbz = '".addslashes($ort_kurzbz)."';";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->ort_kurzbz 		= $row->ort_kurzbz;
			$this->bezeichnung 		= $row->bezeichnung;
			$this->planbezeichnung 	= $row->planbezeichnung;
			$this->max_person 		= $row->max_person;
			$this->aktiv 			= ($row->aktiv=='t'?true:false);
			$this->lehre 			= ($row->lehre=='t'?true:false);
			$this->lageplan 		= $row->lageplan;
			$this->dislozierung 	= $row->dislozierung;
			$this->kosten 			= $row->kosten;
			$this->reservieren		= ($row->reservieren=='t'?true:false);
			$this->ausstattung		= $row->ausstattung;
			$this->stockwerk		= $row->stockwerk;
			$this->standort_kurzbz	= $row->standort_kurzbz;
			$this->telefonklappe	= $row->telefonklappe;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
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
		if(mb_strlen($this->bezeichnung)>30)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 30 Zeichen sein';
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
				dislozierung, kosten, stockwerk, standort_kurzbz, telefonklappe) VALUES ('.
				$this->addslashes($this->ort_kurzbz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->planbezeichnung).', '.
				$this->addslashes($this->max_person).', '.
				($this->aktiv?'true':'false').', '.
				($this->lehre?'true':'false').', '.
				($this->reservieren?'true':'false').', '.
				$this->addslashes($this->lageplan).', '.
				$this->addslashes($this->dislozierung).', '.
				$this->addslashes(str_replace(",",".",$this->kosten)).', '.
				$this->addslashes($this->stockwerk).','.
				$this->addslashes($this->standort_kurzbz).','.
				$this->addslashes($this->telefonklappe).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			$qry = 'UPDATE public.tbl_ort SET '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'planbezeichnung='.$this->addslashes($this->planbezeichnung).', '.
				'max_person='.$this->addslashes($this->max_person).', '.
				'aktiv='.($this->aktiv?'true':'false') .', '.
				'lehre='.($this->lehre?'true':'false') .', '.
				'reservieren='.($this->reservieren?'true':'false') .', '.
				'lageplan='.$this->addslashes($this->lageplan).', '.
				'dislozierung='.$this->addslashes($this->dislozierung).', '.
				'kosten='.$this->addslashes(str_replace(",",".",$this->kosten)).', '.
				'standort_kurzbz='.$this->addslashes($this->standort_kurzbz).', '.
				'telefonklappe='.$this->addslashes($this->telefonklappe).', '.
				'stockwerk='.$this->addslashes($this->stockwerk).' '.
				'WHERE ort_kurzbz = '.$this->addslashes($this->ort_kurzbz).';';
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
		
		//stundevon ermitteln
		$qry = "SELECT stunde FROM (
				SELECT stunde, extract(epoch from (beginn-('".addslashes($zeit_von)."'::time))) AS delta FROM lehre.tbl_stunde
				UNION
				SELECT stunde, extract(epoch from (ende-('".addslashes($zeit_von)."'::time))) AS delta FROM lehre.tbl_stunde
				) foo WHERE delta>=0 ORDER BY delta LIMIT 1;";
		
		if($this->db_query($qry))
			if($row = $this->db_fetch_object())
				$stundevon = $row->stunde;

		//stundebis ermitteln
		$qry = "SELECT stunde FROM (
				SELECT stunde, extract(epoch from (beginn-('".addslashes($zeit_bis)."'::time))) AS delta FROM lehre.tbl_stunde
				UNION
				SELECT stunde, extract(epoch from (ende-('".addslashes($zeit_bis)."'::time))) AS delta FROM lehre.tbl_stunde
				) foo WHERE delta>=0 ORDER BY delta LIMIT 1;";
		
		if($this->db_query($qry))
			if($row = $this->db_fetch_object())
				$stundebis = $row->stunde;
		
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
			$qry.=" AND raumtyp_kurzbz='".addslashes($raumtyp)."'";
		if($anzpersonen!=null)
			$qry.=" AND (max_person>='".addslashes($anzpersonen)."' OR max_person is null)";
		 
		$qry.="	AND ort_kurzbz NOT IN 
			(
				SELECT ort_kurzbz FROM lehre.tbl_$db_table WHERE datum='".addslashes($datum)."' AND stunde>='".addslashes($stundevon)."' AND stunde<='".addslashes($stundebis)."'
				UNION
				SELECT ort_kurzbz FROM campus.tbl_reservierung WHERE datum='".addslashes($datum)."' AND stunde>='".addslashes($stundevon)."' AND stunde<='".addslashes($stundebis)."'
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
				$ort_obj->aktiv 			= ($row->aktiv=='t'?true:false);
				$ort_obj->lehre 			= ($row->lehre=='t'?true:false);
				$ort_obj->lageplan 			= $row->lageplan;
				$ort_obj->dislozierung 		= $row->dislozierung;
				$ort_obj->kosten 			= $row->kosten;
				$ort_obj->reservieren		= ($row->reservieren=='t'?true:false);
				$ort_obj->ausstattung		= $row->ausstattung;
				$ort_obj->stockwerk			= $row->stockwerk;
				$ort_obj->standort_kurzbz	= $row->standort_kurzbz;
				$ort_obj->telefonklappe		= $row->telefonklappe;
	
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
}
?>