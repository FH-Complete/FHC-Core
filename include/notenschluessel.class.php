<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class notenschluessel extends basis_db 
{
	public $new;       		// boolean
	public $result=array();					
		
	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Liefert die Note zu Punkten einer Lehrveranstaltung
	 * @param $punkte
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @return Note
	 */
	public function getNote($punkte, $lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$notenschluessel_kurzbz = $this->getNotenschluessel($lehrveranstaltung_id, $studiensemester_kurzbz);

		$qry = "SELECT 
				note 
			FROM 
				lehre.tbl_notenschluesselaufteilung 
			WHERE 
				notenschluessel_kurzbz=".$this->db_add_param($notenschluessel_kurzbz)."
				AND punkte<=".$this->db_add_param($punkte)." 
			ORDER BY punkte desc LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->note;
			}
			else
			{
				$this->errormsg = 'Es wurde kein passender eintrag gefunden';
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
	 * Liefert den passenden Notenschluessel zu einer Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param $studiensemester_kurzbz Studiensemester
	 * @return Kurzbz des Notenschluessels 
	 */
	public function getNotenschluessel($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		// Notenschluessel der direkt an der LV haengt
		$qry = "SELECT 
					notenschluessel_kurzbz 
				FROM 
					lehre.tbl_notenschluesselzuordnung 
				WHERE 
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND (studiensemester_kurzbz is null OR studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->notenschluessel_kurzbz;
			}
		}

		// Notenschluessel am Studienplan
		// TODO

		// Notenschluessel an der OE

		$qry = "
		WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz, tiefe) as 
		(
			SELECT oe_kurzbz, oe_parent_kurzbz, 1 FROM public.tbl_organisationseinheit 
			WHERE oe_kurzbz=(SELECT 
					oe_kurzbz 
				FROM 
					lehre.tbl_lehrveranstaltung 
				WHERE 
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).")
			UNION ALL
			SELECT o.oe_kurzbz, o.oe_parent_kurzbz, oes.tiefe+1 as tiefe FROM public.tbl_organisationseinheit o, oes 
			WHERE o.oe_kurzbz=oes.oe_parent_kurzbz and aktiv = true
		)
		SELECT notenschluessel_kurzbz
		FROM oes JOIN lehre.tbl_notenschluesselzuordnung USING(oe_kurzbz)
		WHERE (studiensemester_kurzbz is null OR studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")
		ORDER BY tiefe asc LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->notenschluessel_kurzbz;
			}
		}

		return false;			
	}

	/**
	 * Laedt die Aufteilung eines Notenschluessels
	 */
	public function loadAufteilung($notenschluessel_kurzbz)
	{
		$qry = 'SELECT
					tbl_notenschluesselaufteilung.*, tbl_notenschluessel.bezeichnung,
					tbl_note.bezeichnung as notenbezeichnung
				FROM 
					lehre.tbl_notenschluesselaufteilung
					JOIN lehre.tbl_notenschluessel USING(notenschluessel_kurzbz)
					JOIN lehre.tbl_note USING(note)
				WHERE 
					notenschluessel_kurzbz='.$this->db_add_param($notenschluessel_kurzbz).'
				ORDER BY punkte desc';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new notenschluessel();
				$obj->notenschluessel_kurzbz = $row->notenschluessel_kurzbz;
				$obj->punkte = $row->punkte;
				$obj->note = $row->note;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->notenbezeichnung = $row->notenbezeichnung;
				$this->result[] = $obj;
			}
			return true;
		}
	}
}
?>
