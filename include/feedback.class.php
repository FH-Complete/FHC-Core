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
require_once(dirname(__FILE__).'/basis_db.class.php');

class feedback extends basis_db
{
	public $new;				// boolean
	public $result = array(); 	// feedback Objekt

	//Tabellenspalten
	public $feedback_id;	// integer
	public $betreff;		// varchar(128)
	public $text;			// text
	public $datum;			// date
	public $uid;			// varchar(32)
	public $lehrveranstaltung_id; // integer

	/**
	 * Konstruktor - Laedt optional ein Feeedback
	 * @param $feedback_id
	 */
	public function __construct($feedback_id=null)
	{
		parent::__construct();
		
		if(!is_null($feedback_id))
			$this->load($feedback_id);
	}

	/**
	 * Laedt ein Feedback
	 * @param
	 */
	public function load($feedback_id)
	{
		if(!is_numeric($feedback_id))
		{
			$this->errormsg = 'feedback_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_feedback WHERE feedback_id=".$this->db_add_param($feedback_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->feedback_id=$row->feedback_id;
				$this->betreff=$row->betreff;
				$this->text=$row->text;
				$this->datum=$row->datum;
				$this->uid=$row->uid;
				$this->lehrveranstaltung_id=$row->lehrveranstaltung_id;
							
				return true;
			}
			else 
			{
				$this->errormsg  = 'Kein Feedback mit dieser ID vorhanden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Lehrveranstaltungen';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->betreff)>128)
		{
			$this->errormsg = 'Betreff darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		return true;
	}
	
	/**
	 * Laedt die Feedbacks einer Lehrveranstaltung
	 *
	 * @param $lehrveranstaltung_id
	 * @return true wenn ok, sonst false
	 */
	public function load_feedback($lehrveranstaltung_id)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_feedback WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fb_obj = new feedback();

				$fb_obj->feedback_id=$row->feedback_id;
				$fb_obj->betreff=$row->betreff;
				$fb_obj->text=$row->text;
				$fb_obj->datum=$row->datum;
				$fb_obj->uid=$row->uid;
				$fb_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;

				$this->result[] = $fb_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Lehrveranstaltungen';
			return false;
		}
	}

	/**
	 * Speichert Feedback in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_feedback (betreff, text, datum, uid, lehrveranstaltung_id)
			        VALUES('.$this->db_add_param($this->betreff).','.
					$this->db_add_param($this->text).','.
					$this->db_add_param($this->datum).','.
					$this->db_add_param($this->uid).','.
					$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_feedback SET'.
			       ' betreff='.$this->db_add_param($this->betreff).','.
			       ' text='.$this->db_add_param($this->text).','.
			       ' datum='.$this->db_add_param($this->datum).','.
			       ' uid='.$this->db_add_param($this->uid).
			       " WHERE feedback_id=".$this->db_add_param($this->feedback_id, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Feedbacks';
			return false;
		}
	}
}
?>