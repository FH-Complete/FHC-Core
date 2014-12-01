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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			gerald Raab <gerald.raab@technikum-wien.at>.
 */
/**
 * Notenverwaltung fuer das Kreuzerltool
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class studentnote extends basis_db
{
	public $new;      	// boolean
	public $beispiele = array(); // lehreinheit Objekt

	public $uebung_id;	// serial
	public $gewicht;	// smalint
	public $punkte;		// Real
	public $note;
	public $note_gesamt;
	public $negativ;
	public $fehlt;

	/**
	 * Konstruktor - Laedt optional eine Uebung
	 * @param $uebung_id
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Berechnet die Gesamtnote aus den Uebungen einer Lehreinheit
	 *
	 * @param $lehreinheit_id
	 * @param $ss Studiensemester
	 * @param $student_uid
	 */
	public function calc_gesamtnote($lehreinheit_id, $ss, $student_uid)
	{
		$studentgesamtnote = 0;
		$counter = 0;
		$gewichte = 0;
		$negativ_all = false;
		$note_x_gewicht = 0;
		$note_x_gewicht_l1 = 0;
		$gewichte_l1 = 0;
		$fehlt_all = false;
		$beispiele = false;
		
		$ueb1_obj = new uebung();
		$ueb1_obj->load_uebung($lehreinheit_id,1);	
		foreach($ueb1_obj->uebungen as $ueb1)
		{
			$this->calc_l1_note($ueb1->uebung_id, $student_uid, $lehreinheit_id);
			$note_x_gewicht_l1 += ($this->l1_note * $this->l1_gewicht);
			$gewichte_l1 += $this->l1_gewicht;
			if ($this->negativ)				
				$negativ_all = $this->negativ;
			if ($this->fehlt)				
				$fehlt_all = $this->fehlt;
					
		}
		if ($gewichte_l1 > 0)
		{					
			$this->studentgesamtnote = ($note_x_gewicht_l1 / $gewichte_l1);
			$this->negativ = $negativ_all;
			$this->fehlt = $fehlt_all;
		}
		else
		{
			$this->studentgesamtnote = "n";
			$this->negativ = $negativ_all;
			$this->fehlt = $fehlt_all;
		}
	}

	/**
	 * ?? berechnet irgendwas...
	 *
	 * @param $uebung_id
	 * @param $student_uid
	 * @param lehreinheit_id
	 */
	public function calc_l1_note($uebung_id, $student_uid, $lehreinheit_id)
	{
		$studentgesamtnote = 0;
		$counter = 0;
		$gewichte = 0;
		$negativ = false;
		$note_x_gewicht = 0;
		$note_x_gewicht_l1 = 0;
		$gewichte_l1 = 0;
		$fehlt = false;
		$beispiele = false;
		$punkte_gesamt = 0;
		$punkte_mitarbeit = 0;
		$punkte_eingetragen = 0;
		$l1_gewicht = 0;
		$ueb1 = new uebung($uebung_id);
		
		$ueb_obj = new uebung();	
		$ueb_obj->load_uebung($lehreinheit_id, 2, $uebung_id);
		if ($ueb_obj->uebungen)
		{
			$note_x_gewicht = 0;
			$gewichte = 0;
			$punkte_gesamt = 0;
			
			foreach ($ueb_obj->uebungen as $ueb)
			{
				if ($ueb->abgabe && !$ueb->beispiele)
				{							
					if ($this->calc_note($ueb->uebung_id, $student_uid))
					{
						if (is_numeric($this->note))
						{
							if ($ueb->positiv && $this->note == 5)
								$negativ = true;	
							$note_x_gewicht += ($this->note * $this->gewicht);
							$gewichte += $this->gewicht;
						}
						else
						{
							$fehlt = true;
							if ($ueb->positiv)
								$negativ = true;
						}
					}
				}
				else
				{
					$this->calc_punkte($ueb->uebung_id, $student_uid);							
					$punkte_gesamt += $this->punkte_gesamt;
					$punkte_mitarbeit += $this->punkte_mitarbeit;
					$punkte_eingetragen += $this->punkte_eingetragen;
					$beispiele = true;					
				}
			}
			
			if ($gewichte > 0)
			{
				$l1_note = ($note_x_gewicht / $gewichte);
				$l1_gewicht = $ueb1->gewicht;
			}
			//if ($punkte_gesamt > 0)
			if ($beispiele)
			{
				
				if ($ueb1->prozent == 't')
				{					
					$qry = "SELECT sum(tbl_beispiel.punkte) as punktegesamt_alle FROM campus.tbl_beispiel, campus.tbl_uebung
							WHERE tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
							tbl_uebung.lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER)." and tbl_uebung.liste_id = ".$this->db_add_param($ueb1->uebung_id, FHC_INTEGER);
					$punkte_moeglich=1;
					if($this->db_query($qry))
						if($row = $this->db_fetch_object())
							$punkte_moeglich = $row->punktegesamt_alle;					
					if ($punkte_moeglich == 0)
						$punkte_moeglich = 1;						
					$punkte_ns = $punkte_gesamt/$punkte_moeglich*100;
				}
				else
					$punkte_ns = $punkte_gesamt;

				//Prozentpunkte
				$qry = "SELECT min(note) as note FROM campus.tbl_notenschluesseluebung WHERE punkte <= ".$this->db_add_param($punkte_ns)." AND uebung_id = ".$this->db_add_param($ueb1->uebung_id);
				
				if($this->db_query($qry))
				{
                	if($row = $this->db_fetch_object())
	                	$note = $row->note;
					else
						$note = 5;
				}
				if ($ueb1->positiv && ($note == 5))
					$negativ = true;					
				$l1_note = $note;					
				
				if ($note != null)					
					$l1_gewicht = $ueb1->gewicht;
				else
					$l1_gewicht = 0;				
			}
			if ($ueb1->positiv && $beispiele && ($punkte_gesamt == 0))
				$negativ = true;
		}
		else
		{
			$s = new uebung();					
			$s->load_studentuebung($student_uid, $ueb1->uebung_id);
			if ($s->note && $ueb1->gewicht)
			{					
				if ($s->note == 5 && $ueb1->positiv)
					$negativ = true;
				$l1_note= $s->note;
				$l1_gewicht = $ueb1->gewicht;
			}
			else
			{
				$fehlt = true;
				if ($ueb1->positiv)
					$negativ = true;
			}
		}
		
		if ($l1_gewicht > 0)
		{					
			$this->l1_note = $l1_note;
			$this->l1_gewicht = $l1_gewicht;
			$this->negativ = $negativ;
			$this->fehlt = $fehlt;
			$this->punkte_gesamt_l1 = $punkte_gesamt;
			$this->punkte_eingetragen_l1 = $punkte_eingetragen;
			$this->punkte_mitarbeit_l1 = $punkte_mitarbeit;

		}
		else
		{
			$this->l1_note = null;
			$this->l1_gewicht = 0;
			$this->negativ = $negativ;
			$this->fehlt = $fehlt;
			$this->punkte_gesamt_l1 = null;
			$this->punkte_eingetragen_l1 = null;
			$this->punkte_mitarbeit_l1 = null;
		}
	}

	/**
	 * berechnet die note der übung
	 * @param uebung_id, student_uid
	 * setzt this->note, this->gewicht
	 */
	public function calc_note($uebung_id, $student_uid)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg='Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		else
		{
			$note = null;
			$punkte_eingetragen = 0;
			$punkte_gesamt = 0;
			$mitarbeit = 0;
			$ueb = new uebung();
			$ueb->load($uebung_id);
			
			if($ueb->load_studentuebung($student_uid, $uebung_id))
        	{
	        	$this->note = $ueb->note;
				$this->gewicht = $ueb->gewicht;
				return true;
        	}
            else
            {
				$this->note = null;
				$this->gewicht = 0;	            
	            return true;
	        }	
		}
	}


	/**
	 * berechnet die punkte der übung (kreuzerlliste)
	 * @param uebung_id, student_uid
	 * setzt this->punkte_gesamt
	 */
	public function calc_punkte($uebung_id, $student_uid)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg='Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		else
		{
			$note = null;
			$punkte_eingetragen = 0;
			$punkte_gesamt = 0;
			$mitarbeit = 0;
			$ueb = new uebung();
			
			//Eingetragen diese Kreuzerlliste
			$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) 
				WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER)." AND student_uid=".$this->db_add_param($student_uid)." AND vorbereitet=true";
			$punkte_eingetragen=0;
			if($this->db_query($qry))
				if($row = $this->db_fetch_object())
					$punkte_eingetragen = ($row->punkteeingetragen!=''?$row->punkteeingetragen:0);
			
			if($ueb->load_studentuebung($student_uid, $uebung_id))
            {
            	$mitarbeit = $ueb->mitarbeitspunkte;
            }

			$punkte_gesamt = $punkte_eingetragen + $mitarbeit;

			$this->punkte_gesamt = $punkte_gesamt;
			$this->punkte_eingetragen = $punkte_eingetragen;
			$this->punkte_mitarbeit = $mitarbeit;
			return true;
		}		
	}

}
?>
