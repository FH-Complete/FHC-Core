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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Vorschlag Klasse
 * Verwaltet die Antwortmoeglichkeiten zu den Testtool Fragen
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class vorschlag extends basis_db
{
	//Tabellenspalten
	public $vorschlag_id;
	public $frage_id;
	public $nummer;
	public $punkte;
	public $aktiv;
	
	public $text;
	public $bild;
	public $audio;
	
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	
	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $new;

	/**
	 * Konstruktor - Laedt optional einen vorschlag
	 * @param $frage_id       Frage die geladen werden soll (default=null)
	 */
	public function __construct($vorschlag_id=null)
	{
		parent::__construct();

		if($vorschlag_id != null)
			$this->load($vorschlag_id);
	}

	/**
	 * Laedt Vorschlag mit der uebergebenen ID
	 * @param $vorschlag_id ID des Vorschlages der geladen werden soll
	 */
	public function load($vorschlag_id, $sprache='German')
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag WHERE vorschlag_id=".$this->db_add_param($vorschlag_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->vorschlag_id = $row->vorschlag_id;
				$this->frage_id = $row->frage_id;
				$this->punkte = $row->punkte;
				$this->nummer = $row->nummer;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->loadVorschlagSprache($vorschlag_id, $sprache);
				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden des Vorschlags";
			return false;
		}
	}
	
	/**
	 * Laedt die Vorschlaege in einer Sprache
	 *
	 * @param $vorschlag_id
	 * @param $sprache
	 */
	public function loadVorschlagSprache($vorschlag_id, $sprache)
	{
		$qry = "SELECT 
					* 
				FROM 
					testtool.tbl_vorschlag_sprache 
				WHERE 
					vorschlag_id=".$this->db_add_param($vorschlag_id, FHC_INTEGER)."
					AND sprache=".$this->db_add_param($sprache).';';
		
		if($this->db_query($qry))
		{
			if($row_sprache = $this->db_fetch_object())
			{				
				$this->text = $row_sprache->text;
				$this->bild = $row_sprache->bild;
				$this->audio = $row_sprache->audio;
			}
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		return true;
	}

	/**
	 * Speichert die Benutzerdaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $uid upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO testtool.tbl_vorschlag (frage_id, nummer, punkte, insertamum, insertvon, updateamum, updatevon, aktiv) VALUES('.
			       $this->db_add_param($this->frage_id, FHC_INTEGER).','.
			       $this->db_add_param($this->nummer).','.
				   $this->db_add_param($this->punkte).','.
				   $this->db_add_param($this->insertamum).','.
				   $this->db_add_param($this->insertvon).','.
				   $this->db_add_param($this->updateamum).','.
				   $this->db_add_param($this->updatevon).','.
				   $this->db_add_param($this->aktiv, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_vorschlag SET'.
			       ' frage_id='.$this->db_add_param($this->frage_id, FHC_INTEGER).','.
			       ' nummer='.$this->db_add_param($this->nummer).','.
			       ' punkte='.$this->db_add_param($this->punkte).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).','.
			       ' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).
					" WHERE vorschlag_id=".$this->db_add_param($this->vorschlag_id, FHC_INTEGER, false).";";
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('testtool.tbl_vorschlag_vorschlag_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->vorschlag_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else 
			{
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Frage';
			return false;
		}
	}

	/**
	 * Pueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	protected function validate_vorschlagsprache()
	{
		return true;	
	}
	
	/**
	 * Speichert einen Eintrag in tbl_vorschlag_sprache
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	public function save_vorschlagsprache()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate_vorschlagsprache())
			return false;

		$qry = "SELECT * FROM testtool.tbl_vorschlag_sprache 
				WHERE vorschlag_id=".$this->db_add_param($this->vorschlag_id, FHC_INTEGER)." AND
				sprache=".$this->db_add_param($this->sprache).";";
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				$this->new=false;
			else 
				$this->new=true;
		}
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO testtool.tbl_vorschlag_sprache (vorschlag_id, sprache, text, bild, audio, 
					insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->db_add_param($this->vorschlag_id, FHC_INTEGER).','.
			       $this->db_add_param($this->sprache).','.
				   $this->db_add_param($this->text).','.
				   $this->db_add_param($this->bild).','.
				   $this->db_add_param($this->audio).','.
				   $this->db_add_param($this->insertamum).','.
				   $this->db_add_param($this->insertvon).','.
				   $this->db_add_param($this->updateamum).','.
				   $this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_vorschlag_sprache SET'.
			       ' text='.$this->db_add_param($this->text).',';
			if($this->bild!='')
				$qry.=' bild='.$this->db_add_param($this->bild).',';
			if($this->audio!='')
				$qry.=' audio='.$this->db_add_param($this->audio).',';
			
			$qry.= ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
					" WHERE vorschlag_id=".$this->db_add_param($this->vorschlag_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($this->sprache).";";
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Vorschlaege zu einer Frage
	 * 
	 * @param integer $frage_id
	 * @param string $sprache
	 * @param boolean $random Wenn true, dann werden die Vorschlaege in zufaelliger Reihenfolge geladen, sonst nach nummer sortiert
	 */
	public function getVorschlag($frage_id, $sprache, $random)
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER);
		if($random)
			$qry.=" ORDER BY random()";
		else 
			$qry.=" ORDER BY nummer";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$vs = new vorschlag();
				$vs->vorschlag_id = $row->vorschlag_id;
				$vs->frage_id = $row->frage_id;
				$vs->nummer = $row->nummer;
				$vs->punkte = $row->punkte;
				$vs->aktiv = $this->db_parse_bool($row->aktiv);
				$qry = "SELECT * FROM testtool.tbl_vorschlag_sprache 
						WHERE vorschlag_id=".$this->db_add_param($row->vorschlag_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($sprache).";";
				if($this->db_query($qry))
				{
					if($row_sprache = $this->db_fetch_object())
					{				
						$vs->text = $row_sprache->text;
						$vs->bild = $row_sprache->bild;
						$vs->audio = $row_sprache->audio;
					}
				}
				$this->result[] = $vs;

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
	 * Loescht einen Vorschlag
	 *
	 * @param $vorschlag_id
	 * @return boolean
	 */
	public function delete($vorschlag_id)
	{
		$qry = "SELECT count(*) as anzahl FROM testtool.tbl_antwort WHERE vorschlag_id=".$this->db_add_param($vorschlag_id, FHC_INTEGER).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg='Fehler: Sie muessen zuerst alle Antworten zu diesem Vorschlag entfernen!';
					return false;
				}
			}
		}
		
		$qry = "
			DELETE FROM testtool.tbl_vorschlag_sprache WHERE vorschlag_id=".$this->db_add_param($vorschlag_id, FHC_INTEGER).";
			DELETE FROM testtool.tbl_vorschlag WHERE vorschlag_id=".$this->db_add_param($vorschlag_id, FHC_INTEGER).';';
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen';
			return false;
		}
	}
}
?>
