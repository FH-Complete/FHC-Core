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

class studentnote
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $beispiele = array(); // lehreinheit Objekt

	//Vars
	var $uebung_id;		// serial
	var $gewicht;		// smalint
	var $punkte;		// Real
	var $note;
	var $note_gesamt;
	var $negativ;
	var $fehlt;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Uebung
	// * @param $conn        	Datenbank-Connection
	// * 		$uebung_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function studentnote($conn, $lehreinheit_id=null, $ss=null, $student_uid=null, $uebung_id=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($this->conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}

		if($uebung_id)
		{
			$this->calc_note($uebung_id, $student_uid);
		}
		else
		{
			$studentgesamtnote = 0;
			$counter = 0;
			$gewichte = 0;
			$negativ = false;
			$note_x_gewicht = 0;
			$note_x_gewicht_l1 = 0;
			$gewichte_l1 = 0;
			$fehlt = false;
			
			$ueb1_obj = new uebung($conn);
			$ueb1_obj->load_uebung($lehreinheit_id,1);	
			foreach($ueb1_obj->uebungen as $ueb1)
			{
				$ueb_obj = new uebung($conn);	
				//if ($ueb_obj->load_uebung($lehreinheit_id, 2, $ueb1->uebung_id))
				$ueb_obj->load_uebung($lehreinheit_id, 2, $ueb1->uebung_id);
				if ($ueb_obj->uebungen)
				{
					$note_x_gewicht = 0;
					$gewichte = 0;
					foreach ($ueb_obj->uebungen as $ueb)
					{
						if ($this->calc_note($ueb->uebung_id,$student_uid))
						{
							if (is_numeric($this->note))
							{
								if ($ueb->positiv && $this->note == 5)
									$negativ = true;						
								$note_x_gewicht += ($this->note * $this->gewicht);
								$gewichte += $this->gewicht;
							}
							else
								$fehlt = true;
						}
					}
					if ($gewichte > 0)
					{					
						$note_x_gewicht_l1 += ($note_x_gewicht / $gewichte);
						$gewichte_l1 += $ueb1->gewicht;
					}
				}
				// keine kreuzerllisten/abgaben				
				else
				{
					$s = new uebung($conn);					
					$s->load_studentuebung($student_uid, $ueb1->uebung_id);
					if ($s->note && $ueb1->gewicht)
					{					
						if ($s->note == 5 && $ueb1->positiv)
							$negativ = true;						
						$note_x_gewicht_l1 += ($s->note * $ueb1->gewicht);
						$gewichte_l1 += $ueb1->gewicht;
					}
						else
							$fehlt = true;
				}
			}
			if ($gewichte_l1 > 0)
			{		
				$this->studentgesamtnote = ($note_x_gewicht_l1 / $gewichte_l1);
				$this->negativ = $negativ;
				$this->fehlt = $fehlt;
			}
			else
			{
				$this->studentgesamtnote = "n";
				$this->negativ = $negativ;
				$this->fehlt = $fehlt;
			}
		}
	}

	// *********************************************************
	// * berechnet die gesamtnote der übung
	// * @param uebung_id, student_uid
	// *********************************************************
	function calc_note($uebung_id, $student_uid)
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
			$ueb = new uebung($this->conn);
			$ueb->load($uebung_id);
			if ($ueb->beispiele)
			{
				//Eingetragen diese Kreuzerlliste
				$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) WHERE uebung_id='$uebung_id' AND student_uid='$student_uid' AND vorbereitet=true";
				$punkte_eingetragen=0;
				if($result=pg_query($this->conn, $qry))
					if($row = pg_fetch_object($result))
						$punkte_eingetragen = ($row->punkteeingetragen!=''?$row->punkteeingetragen:0);
				if($ueb->load_studentuebung($student_uid, $uebung_id))
	            	{
	            		$mitarbeit = $ueb->mitarbeitspunkte;
	            	}

				$punkte_gesamt = $punkte_eingetragen + $mitarbeit;

				
				$qry = "SELECT min(note) as note from campus.tbl_notenschluesseluebung where punkte <= '".$punkte_gesamt."' and uebung_id = '".$uebung_id."'";
				if($result=pg_query($this->conn, $qry))
                	if($row = pg_fetch_object($result))
	                	$note = $row->note;
					else
						$note = 5;
				$this->note = $note;
				$this->gewicht = $ueb->gewicht;
				return true;
			}
			else
			{
				if($ueb->load_studentuebung($student_uid, $uebung_id))
	            	{
	            		$this->note = $ueb->note;
					$this->gewicht = $ueb->gewicht;
					return true;
	            	}
			}
			
		}
	}
	
	
}
?>
