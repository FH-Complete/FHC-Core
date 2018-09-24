<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>
 */
/**
 * Klasse Note
 * @create 2007-06-06
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class note extends basis_db
{
	public $new;       		// boolean
	public $result=array();

	//Tabellenspalten
	public $note;						// smallint
	public $bezeichnung;				// varchar(32)
	public $anmerkung;					// varchar(256)
	public $farbe;						// varchar(6)
	public $positiv=true;				// boolean
	public $notenwert;					// boolean
	public $aktiv;						// boolean
	public $lehre;						// boolean
	public $offiziell;					// boolean
	public $lkt_ueberschreibbar;		// boolean
	public $bezeichnung_mehrsprachig; 	// varchar (64)[]

	/**
	 * Konstruktor
	 * @param $note
	 */
	public function __construct($note = null)
	{
		parent::__construct();

		if($note != null)
			$this->load($note);
	}

	/**
	 * Laedt eine Note
	 * @param  $note
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($note)
	{
		if(!is_numeric($note))
		{
			$this->errormsg = 'Note ist ungueltig';
			return false;
		}
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT 	note, 
						bezeichnung, 
						anmerkung, 
						farbe, 
						positiv, 
						notenwert, 
						aktiv, 
						lehre, 
						offiziell,
						lkt_ueberschreibbar,
						$bezeichnung_mehrsprachig 
				FROM 
					lehre.tbl_note 
				WHERE 
					note=".$this->db_add_param($note);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->note = $row->note;
				$this->bezeichnung = $row->bezeichnung;
				$this->anmerkung = $row->anmerkung;
				$this->farbe = $row->farbe;
				$this->notenwert = $row->notenwert;
				$this->positiv = $this->db_parse_bool($row->positiv);
				$this->lehre = $this->db_parse_bool($row->lehre);
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->offiziell = $this->db_parse_bool($row->offiziell);
				$this->lkt_ueberschreibbar = $this->db_parse_bool($row->lkt_ueberschreibbar);
				$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);
				return true;
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft die Daten vor dem Speichern
	 * auf Gueltigkeit
	 */
	public function validate()
	{
		if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note ist ungueltig';
			return false;
		}
		return true;
	}	

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new=$this->new;

		if(!$this->validate())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO lehre.tbl_note (note, bezeichnung, anmerkung, positiv, notenwert, aktiv, lehre, ';
			foreach($this->bezeichnung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry .= " bezeichnung_mehrsprachig[$idx],";
			}
			$qry .= ' offiziell, lkt_ueberschreibbar) VALUES('.
				$this->db_add_param($this->note).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->anmerkung).', '.
				$this->db_add_param($this->positiv, FHC_BOOLEAN).','.
				$this->db_add_param($this->notenwert).','.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
				$this->db_add_param($this->lehre, FHC_BOOLEAN).',';

				foreach($this->bezeichnung_mehrsprachig as $key=>$value)
					$qry .= $this->db_add_param($value).',';
				
			$qry .= $this->db_add_param($this->offiziell, FHC_BOOLEAN);
			$qry .= $this->db_add_param($this->lkt_ueberschreibbar, FHC_BOOLEAN).');';

				 
		}
		else
		{
			$qry='UPDATE lehre.tbl_note SET '.
				'note='.$this->db_add_param($this->note).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
				'positiv='.$this->db_add_param($this->positiv, FHC_BOOLEAN).', '.
				'notenwert='.$this->db_add_param($this->notenwert).', '.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				'lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN).', ';
				
				foreach($this->bezeichnung_mehrsprachig as $key=>$value)
				{
					$idx = sprache::$index_arr[$key];
					$qry .= " bezeichnung_mehrsprachig[$idx]=".$this->db_add_param($value).", ";
				}
				
				$qry .= ' offiziell='.$this->db_add_param($this->offiziell, FHC_BOOLEAN).', '.
				$qry .= ' lkt_ueberschreibbar='.$this->db_add_param($this->lkt_ueberschreibbar, FHC_BOOLEAN).' '.
				'WHERE note='.$this->db_add_param($this->note).';';
			
			
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Speichern des Datensatzes";
			return false;
		}
	}

	/**
	 * Laedt alle Noten, inklusive inaktiven Noten
	 * @param null $offiziell wenn true, werden nur Noten, die auf offiziellen Dokumenten gedruckt weden können, geladen
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAll($offiziell = null)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT 	note, 
						bezeichnung, 
						anmerkung, 
						farbe, 
						positiv, 
						notenwert, 
						aktiv, 
						lehre, 
						offiziell,
						lkt_ueberschreibbar,
						$bezeichnung_mehrsprachig 
				FROM 
					lehre.tbl_note ";

		if(is_bool($offiziell))
			$qry .= " WHERE offiziell = ".$this->db_add_param($offiziell, FHC_BOOLEAN);

		$qry .= " ORDER BY note";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$n = new note();

				$n->note = $row->note;
				$n->bezeichnung = $row->bezeichnung;
				$n->anmerkung = $row->anmerkung;
				$n->farbe = $row->farbe;
				$n->positiv = $this->db_parse_bool($row->positiv);
				$n->notenwert = $row->notenwert;
				$n->aktiv = $this->db_parse_bool($row->aktiv);
				$n->lehre = $this->db_parse_bool($row->lehre);
				$n->offiziell = $this->db_parse_bool($row->offiziell);
				$n->lkt_ueberschreibbar = $this->db_parse_bool($row->lkt_ueberschreibbar);
				$n->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);

				$this->result[] = $n;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle aktive Noten
	 * @param null $offiziell wenn true, werden nur Noten, die auf offiziellen Dokumenten gedruckt weden können, geladen
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getActive($offiziell = null)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT 	note, 
						bezeichnung, 
						anmerkung, 
						farbe, 
						positiv, 
						notenwert, 
						aktiv, 
						lehre, 
						offiziell, 
						lkt_ueberschreibbar,
						$bezeichnung_mehrsprachig 
				FROM 
					lehre.tbl_note 
				WHERE 
					aktiv = TRUE";

		if(is_bool($offiziell))
			$qry .= " AND offiziell = ".$this->db_add_param($offiziell, FHC_BOOLEAN);

		$qry .= " ORDER BY note";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$n = new note();

				$n->note = $row->note;
				$n->bezeichnung = $row->bezeichnung;
				$n->anmerkung = $row->anmerkung;
				$n->farbe = $row->farbe;
				$n->positiv = $this->db_parse_bool($row->positiv);
				$n->notenwert = $row->notenwert;
				$n->aktiv = $this->db_parse_bool($row->aktiv);
				$n->lehre = $this->db_parse_bool($row->lehre);
				$n->offiziell = $this->db_parse_bool($row->offiziell);
				$n->lkt_ueberschreibbar = $this->db_parse_bool($row->lkt_ueberschreibbar);
				$n->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);

				$this->result[] = $n;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
