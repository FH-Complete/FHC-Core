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
require_once(dirname(__FILE__).'/person.class.php');

class prestudent extends person
{
	//Tabellenspalten
	public $prestudent_id;	// varchar(16)
	public $aufmerksamdurch_kurzbz;
	public $studiengang_kz;
	public $berufstaetigkeit_code;
	public $ausbildungcode;
	public $zgv_code;
	public $zgvort;
	public $zgvdatum;
	public $zgvmas_code;
	public $zgvmaort;
	public $zgvmadatum;
	public $ausstellungsstaat;
	public $aufnahmeschluessel;
	public $facheinschlberuf;
	public $anmeldungreihungstest;
	public $reihungstestangetreten;
	public $reihungstest_id;
	public $punkte; //rt_gesamtpunkte
	public $rt_punkte1;
	public $rt_punkte2;
	public $rt_punkte3=0;
	public $bismelden=true;
	public $anmerkung;
	public $ext_id_prestudent;
	public $dual=false;
    public $zgvdoktor_code; 
    public $zgvdoktorort; 
    public $zgvdoktordatum; 
    
	public $status_kurzbz;
	public $studiensemester_kurzbz;
	public $ausbildungssemester;
	public $datum;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	public $orgform_kurzbz;
	public $studienplan_id;
	public $studienplan_bezeichnung;
	
	public $studiensemester_old='';
	public $ausbildungssemester_old='';
	
	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
		
	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional einen Prestudent
	 * @param $prestudent_id Prestudent der geladen werden soll (default=null)
	 */
	public function __construct($prestudent_id=null)
	{
		parent::__construct();

		if($prestudent_id != null)
			$this->load($prestudent_id);
	}
	
	/**
	 * Laedt Prestudent mit der uebergebenen ID
	 * @param $uid ID der Person die geladen werden soll
	 */
	public function load($prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_prestudent WHERE prestudent_id=".$this->db_add_param($prestudent_id);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prestudent_id = $row->prestudent_id;
				$this->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$this->ausbildungcode = $row->ausbildungcode;
				$this->zgv_code = $row->zgv_code;
				$this->zgvort = $row->zgvort;
				$this->zgvdatum = $row->zgvdatum;
				$this->zgvmas_code = $row->zgvmas_code;
				$this->zgvmaort = $row->zgvmaort;
				$this->zgvmadatum = $row->zgvmadatum;
				$this->aufnahmeschluessel = $row->aufnahmeschluessel;
				$this->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
				$this->anmeldungreihungstest = $row->anmeldungreihungstest;
				$this->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
				$this->reihungstest_id = $row->reihungstest_id;
				$this->punkte = $row->rt_gesamtpunkte;
				$this->rt_punkte1 = $row->rt_punkte1;
				$this->rt_punkte2 = $row->rt_punkte2;
				$this->rt_punkte3 = $row->rt_punkte3;
				$this->bismelden = $this->db_parse_bool($row->bismelden);
				$this->person_id = $row->person_id;
				$this->anmerkung = $row->anmerkung;
				$this->ext_id_prestudent = $row->ext_id;
				$this->dual = $this->db_parse_bool($row->dual);
				$this->ausstellungsstaat = $row->ausstellungsstaat;
                $this->zgvdoktor_code = $row->zgvdoktor_code; 
                $this->zgvdoktorort = $row->zgvdoktorort; 
                $this->zgvdoktordatum = $row->zgvdoktordatum; 
				
				if(!person::load($row->person_id))
					return false;
				else 
					return true;
			}
			else 
			{
				$this->errormsg = "Kein Prestudent Eintrag gefunden";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden des Prestudenten";
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
		if($this->punkte>9999.9999)
		{
			$this->errormsg = 'Reihungstestgesamtpunkte darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if($this->rt_punkte1>9999.9999)
		{
			$this->errormsg = 'Reihungstestpunkte1 darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if($this->rt_punkte2>9999.9999)
		{
			$this->errormsg = 'Reihungstestpunkte2 darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if($this->rt_punkte3>9999.9999)
		{
			$this->errormsg = 'Reihungstestpunkte3 darf nicht groesser als 9999.9999 sein';
			return false;
		}

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
		//Personen Datensatz speichern
		//if(!person::save())
		//	return false;
			
		//Variablen auf Gueltigkeit pruefen
		if(!prestudent::validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id, 
					studiengang_kz, berufstaetigkeit_code, ausbildungcode, zgv_code, zgvort, zgvdatum, 
					zgvmas_code, zgvmaort, zgvmadatum, aufnahmeschluessel, facheinschlberuf, 
					reihungstest_id, anmeldungreihungstest, reihungstestangetreten, rt_gesamtpunkte, 
					rt_punkte1, rt_punkte2, rt_punkte3, bismelden, insertamum, insertvon, 
					updateamum, updatevon, ext_id, anmerkung, dual, ausstellungsstaat) VALUES('.
			       $this->db_add_param($this->aufmerksamdurch_kurzbz).",".
			       $this->db_add_param($this->person_id).",".
			       $this->db_add_param($this->studiengang_kz).",".
			       $this->db_add_param($this->berufstaetigkeit_code).",".
			       $this->db_add_param($this->ausbildungcode).",".
			       $this->db_add_param($this->zgv_code).",".
			       $this->db_add_param($this->zgvort).",".
			       $this->db_add_param($this->zgvdatum).",".
			       $this->db_add_param($this->zgvmas_code).",".
			       $this->db_add_param($this->zgvmaort).",".
			       $this->db_add_param($this->zgvmadatum).",".
			       $this->db_add_param($this->aufnahmeschluessel).",".
			       $this->db_add_param($this->facheinschlberuf, FHC_BOOLEAN).",".
			       $this->db_add_param($this->reihungstest_id).",".
			       $this->db_add_param($this->anmeldungreihungstest).",".
			       $this->db_add_param($this->reihungstestangetreten, FHC_BOOLEAN).",".
			       $this->db_add_param($this->punkte).",".
			       $this->db_add_param($this->rt_punkte1).",".
			       $this->db_add_param($this->rt_punkte2).",".
			       $this->db_add_param($this->rt_punkte3).",".
			       $this->db_add_param($this->bismelden, FHC_BOOLEAN).",".
			       $this->db_add_param($this->insertamum).",".
			       $this->db_add_param($this->insertvon).",".
			       $this->db_add_param($this->updateamum).",".
			       $this->db_add_param($this->updatevon).",".
			       $this->db_add_param($this->ext_id_prestudent).",".
			       $this->db_add_param($this->anmerkung).",".
			       $this->db_add_param($this->dual, FHC_BOOLEAN).",".
			       $this->db_add_param($this->ausstellungsstaat).");";
		}
		else
		{
			$qry = 'UPDATE public.tbl_prestudent SET'.
			       ' aufmerksamdurch_kurzbz='.$this->db_add_param($this->aufmerksamdurch_kurzbz).",".
			       ' person_id='.$this->db_add_param($this->person_id).",".
			       ' studiengang_kz='.$this->db_add_param($this->studiengang_kz).",".
			       ' berufstaetigkeit_code='.$this->db_add_param($this->berufstaetigkeit_code).",".
			       ' ausbildungcode='.$this->db_add_param($this->ausbildungcode).",".
			       ' zgv_code='.$this->db_add_param($this->zgv_code).",".
			       ' zgvort='.$this->db_add_param($this->zgvort).",".
			       ' zgvdatum='.$this->db_add_param($this->zgvdatum).",".
			       ' zgvmas_code='.$this->db_add_param($this->zgvmas_code).",".
			       ' zgvmaort='.$this->db_add_param($this->zgvmaort).",".
			       ' zgvmadatum='.$this->db_add_param($this->zgvmadatum).",".
			       ' aufnahmeschluessel='.$this->db_add_param($this->aufnahmeschluessel).",".
			       ' facheinschlberuf='.$this->db_add_param($this->facheinschlberuf, FHC_BOOLEAN).",".
			       ' reihungstest_id='.$this->db_add_param($this->reihungstest_id).",".
			       ' anmeldungreihungstest='.$this->db_add_param($this->anmeldungreihungstest).",".
			       ' reihungstestangetreten='.$this->db_add_param($this->reihungstestangetreten, FHC_BOOLEAN).",".
			       ' rt_gesamtpunkte='.$this->db_add_param($this->punkte).",".
			       ' rt_punkte1='.$this->db_add_param($this->rt_punkte1).",".
			       ' rt_punkte2='.$this->db_add_param($this->rt_punkte2).",".
			       ' rt_punkte3='.$this->db_add_param($this->rt_punkte3).",".
			       ' bismelden='.$this->db_add_param($this->bismelden, FHC_BOOLEAN).",".
			       ' updateamum='.$this->db_add_param($this->updateamum).",".
			       ' updatevon='.$this->db_add_param($this->updatevon).",".
			       ' ext_id='.$this->db_add_param($this->ext_id_prestudent).",".
			       ' anmerkung='.$this->db_add_param($this->anmerkung).",".
			       ' dual='.$this->db_add_param($this->dual, FHC_BOOLEAN).",".
				   ' ausstellungsstaat='.$this->db_add_param($this->ausstellungsstaat).
			       " WHERE prestudent_id=".$this->db_add_param($this->prestudent_id).";";
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->prestudent_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}						
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern des Prestudent-Datensatzes';
			return false;
		}
	}

	/**
	 * Laden aller Prestudenten, die an $datum zum Reihungstest geladen sind.
	 * Wenn $equal auf true gesetzt ist wird genau dieses Datum verwendet,
	 * ansonsten werden auch alle mit späterem Datum geladen. ---> von kindlm am 30.03.2012 geändert 
	 * da zukünftige Teilnehmer nicht mehr angezeigt werden sollen. 
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function getPrestudentRT($datum, $equal=false)
	{
		$sql_query='SELECT DISTINCT * FROM public.vw_prestudent WHERE rt_datum';
		if ($equal)
			$sql_query.='=';
		else
			$sql_query.='=';
		$sql_query.="'$datum' ORDER BY nachname,vorname";
		
		if(!$this->db_query($sql_query))
		{	
			$this->errormsg = 'Fehler beim Speichern des Benutzer-Datensatzes:'.$sql_query;
			return false;
		}
		
		$this->num_rows=0;
		
		while($row = $this->db_fetch_object())
		{
			$ps=new prestudent();
			$ps->prestudent_id = $row->prestudent_id;
			$ps->person_id = $row->person_id;
			$ps->reihungstest_id = $row->reihungstest_id;
			$ps->staatsbuergerschaft = $row->staatsbuergerschaft;
			$ps->geburtsnation = $row->geburtsnation;
			$ps->sprache = $row->sprache;
			$ps->anrede = $row->anrede;
			$ps->titelpost = $row->titelpost;
			$ps->titelpre = $row->titelpre;
			$ps->nachname = $row->nachname;
			$ps->vorname = $row->vorname;
			$ps->vornamen = $row->vornamen;
			$ps->gebdatum = $row->gebdatum;
			$ps->gebort = $row->gebort;
			$ps->gebzeit = $row->gebzeit;
			// $ps->foto = $row->foto;
			$ps->anmerkungen = $row->anmerkungen;
			$ps->homepage = $row->homepage;
			$ps->svnr = $row->svnr;
			$ps->ersatzkennzeichen = $row->ersatzkennzeichen;
			$ps->familienstand = $row->familienstand;
			$ps->geschlecht = $row->geschlecht;
			$ps->anzahlkinder = $row->anzahlkinder;
			$ps->aktiv = $this->db_parse_bool($row->aktiv);
			$ps->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
			$ps->studiengang_kz = $row->studiengang_kz;
			$ps->berufstaetigkeit_code = $row->berufstaetigkeit_code;
			$ps->ausbildungcode = $row->ausbildungcode;
			$ps->zgv_code = $row->zgv_code;
			$ps->zgvort = $row->zgvort;
			$ps->zgvdatum = $row->zgvdatum;
			$ps->zgvmas_code = $row->zgvmas_code;
			$ps->zgvmaort = $row->zgvmaort;
			$ps->zgvmadatum = $row->zgvmadatum;
			$ps->aufnahmeschluessel = $row->aufnahmeschluessel;
			$ps->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
			$ps->anmeldungreihungstest = $row->anmeldungreihungstest;
			$ps->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
			$ps->punkte = $row->punkte;
			$ps->rt_punkte1 = $row->rt_punkte1;
			$ps->rt_punkte2 = $row->rt_punkte2;
			$ps->bismelden = $this->db_parse_bool($row->bismelden);
			$ps->rt_studiengang_kz = $row->rt_studiengang_kz;
			$ps->rt_ort = $row->rt_ort;
			$ps->rt_datum = $row->rt_datum;
			$ps->rt_uhrzeit = $row->rt_uhrzeit;
			$ps->updateamum = $row->updateamum;
			$ps->updatevon = $row->updatevon;
			$ps->insertamum = $row->insertamum;
			$ps->insertvon = $row->insertvon;
			//$ps->ext_id_prestudent = $row->ext_id_prestudent;
			$this->result[]=$ps;
			$this->num_rows++; 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	
		}
		return true;		
	}
	
	/**
	 * Laedt die Rolle(n) eines Prestudenten
	 */
	public function getPrestudentRolle($prestudent_id, $status_kurzbz=null, $studiensemester_kurzbz=null, $order="datum, insertamum", $ausbildungssemester=null)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT 
					tbl_prestudentstatus.*, tbl_studienplan.bezeichnung as studienplan_bezeichnung
				FROM public.tbl_prestudentstatus 
					LEFT JOIN lehre.tbl_studienplan USING(studienplan_id)
				WHERE 
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);	
		if($status_kurzbz!=null)
			$qry.= " AND status_kurzbz=".$this->db_add_param($status_kurzbz);
		if($studiensemester_kurzbz!=null)
			$qry.= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if($ausbildungssemester!=null)
			$qry.= " AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($order!='')
			$qry.=" ORDER BY ".$order;
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$rolle = new prestudent();
				
				$rolle->prestudent_id = $row->prestudent_id;
				$rolle->status_kurzbz = $row->status_kurzbz;
				$rolle->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$rolle->ausbildungssemester = $row->ausbildungssemester;
				$rolle->datum = $row->datum;
				$rolle->insertamum = $row->insertamum;
				$rolle->insertvon = $row->insertvon;
				$rolle->updateamum = $row->updateamum;
				$rolle->updatevon = $row->updatevon;
				$rolle->orgform_kurzbz = $row->orgform_kurzbz;
				$rolle->studienplan_id = $row->studienplan_id;
				$rolle->studienplan_bezeichnung = $row->studienplan_bezeichnung;
				$this->result[] = $rolle;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}
	
	/**
	 * Laedt die Rolle
	 *
	 * @param $prestudent_id
	 * @param $status_kurzbz
	 * @param $studiensemester_kurzbz
	 * @param $ausbildungssemester
	 * @return boolean
	 */
	public function load_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		if(!is_numeric($prestudent_id) || $prestudent_id=='')
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$this->db_add_param($prestudent_id).
			   " AND status_kurzbz=".$this->db_add_param($status_kurzbz).
			   " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).
			   " AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{								
				$this->prestudent_id = $row->prestudent_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->ausbildungssemester = $row->ausbildungssemester;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id_prestudent = $row->ext_id;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->studienplan_id = $row->studienplan_id;

				return true;
			}
			else 
			{
				$this->errormsg = 'Rolle existiert nicht';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}
	
	/**
	 * Laedt die Interessenten und Bewerber fuer ein bestimmtes Studiensemester
	 * @param $studiensemester_kurzbz Studiensemester fuer das die Int. und Bewerber
	 *                                geladen werden sollen
	 */
	public function loadIntessentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester=null, $typ=null, $orgform=null)
	{
		$stsemqry='';
		if(!is_null($studiensemester_kurzbz) && $studiensemester_kurzbz!='')
			$stsemqry=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		
		$qry = "SELECT 
					*, a.anmerkung, tbl_person.anmerkung as anmerkungen 
				FROM 
					(
						SELECT 
							*, (SELECT status_kurzbz FROM tbl_prestudentstatus 
							    WHERE prestudent_id=prestudent.prestudent_id $stsemqry
							    ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) AS rolle 
						FROM tbl_prestudent prestudent ORDER BY prestudent_id
					) a, tbl_prestudentstatus, tbl_person
				WHERE a.rolle=tbl_prestudentstatus.status_kurzbz AND 
					a.person_id=tbl_person.person_id AND
					a.prestudent_id = tbl_prestudentstatus.prestudent_id AND
					a.studiengang_kz=".$this->db_add_param($studiengang_kz);
						
		if(!is_null($studiensemester_kurzbz) && $studiensemester_kurzbz!='')
			$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
			
		if($semester!=null)
			$qry.=" AND ausbildungssemester=".$this->db_add_param($semester);
		if($orgform!=null && $orgform!='')
			$qry.=" AND tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform);
		
		switch ($typ)
		{
			case "interessenten": 	
				$qry.=" AND a.rolle='Interessent'";
				break;
			case "zgv":	
				$stg_obj = new studiengang();
				$stg_obj->load($studiengang_kz);
				if($stg_obj->typ=='m')
					$qry.=" AND a.rolle='Interessent' AND a.zgvmas_code is not null";
				else
					$qry.=" AND a.rolle='Interessent' AND a.zgv_code is not null";
				break;
			case "reihungstestangemeldet":  
				$qry.=" AND a.rolle='Interessent' AND a.anmeldungreihungstest is not null";
				break;
			case "reihungstestnichtangemeldet":
				$qry.=" AND a.rolle='Interessent' AND a.anmeldungreihungstest is null";
				break;
			case "bewerber":
				$qry.=" AND a.rolle='Bewerber'";
				break;
			case "aufgenommen":
				$qry.=" AND a.rolle='Aufgenommener'";
				break;
			case "warteliste":
				$qry.=" AND a.rolle='Wartender'";
				break;
			case "absage":
				$qry.=" AND a.rolle='Abgewiesener'";
				break;
			case "prestudent":
				if($studiensemester_kurzbz=='' || is_null($studiensemester_kurzbz))
					$qry = "SELECT *, '' as status_kurzbz, '' as studiensemester_kurzbz, '' as ausbildungssemester, '' as datum, tbl_person.anmerkung as anmerkungen, '' as orgform_kurzbz FROM public.tbl_prestudent prestudent, public.tbl_person WHERE NOT EXISTS (select * from tbl_prestudentstatus WHERE prestudent_id=prestudent.prestudent_id) AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)." AND prestudent.person_id=tbl_person.person_id";
				else 
					$qry .= " AND a.rolle IN('Interessent', 'Bewerber', 'Aufgenommener', 'Wartender', 'Abgewiesener')";
				break;
			case "absolvent":
				$qry.=" AND a.rolle='Absolvent'";
				break;
			case "diplomand":
				$qry.=" AND a.rolle='Diplomand'";
				break;
			default: 
				break;		
		}

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$ps = new prestudent();
				
				$ps->person_id = $row->person_id;
				$ps->staatsbuergerschaft = $row->staatsbuergerschaft;
				$ps->gebnation = $row->geburtsnation;
				$ps->sprache = $row->sprache;
				$ps->anrede = $row->anrede;
				$ps->titelpost = $row->titelpost;
				$ps->titelpre = $row->titelpre;
				$ps->nachname = $row->nachname;
				$ps->vorname = $row->vorname;
				$ps->vornamen = $row->vornamen;
				$ps->gebdatum = $row->gebdatum;
				$ps->gebort = $row->gebort;
				$ps->gebzeit = $row->gebzeit;
				//$ps->foto = $row->foto;
				$ps->anmerkungen = $row->anmerkungen;
				$ps->homepage = $row->homepage;
				$ps->svnr = $row->svnr;
				$ps->ersatzkennzeichen = $row->ersatzkennzeichen;
				$ps->familienstand = $row->familienstand;
				$ps->geschlecht = $row->geschlecht;
				$ps->anzahlkinder = $row->anzahlkinder;
				$ps->aktiv = $this->db_parse_bool($row->aktiv);
				
				$ps->prestudent_id = $row->prestudent_id;
				$ps->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$ps->studiengang_kz = $row->studiengang_kz;
				$ps->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$ps->ausbildungcode = $row->ausbildungcode;
				$ps->zgv_code = $row->zgv_code;
				$ps->zgvort = $row->zgvort;
				$ps->zgvdatum = $row->zgvdatum;
				$ps->zgvmas_code = $row->zgvmas_code;
				$ps->zgvmaort = $row->zgvmaort;
				$ps->zgvmadatum = $row->zgvmadatum;
				$ps->ausstellungsstaat = $row->ausstellungsstaat;
				$ps->aufnahmeschluessel = $row->aufnahmeschluessel;
				$ps->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
				$ps->anmeldungreihungstest = $row->anmeldungreihungstest;
				$ps->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
				$ps->reihungstest_id = $row->reihungstest_id;
				$ps->punkte = $row->rt_gesamtpunkte;
				$ps->rt_punkte1 = $row->rt_punkte1;
				$ps->rt_punkte2 = $row->rt_punkte2;
				$ps->rt_punkte3 = $row->rt_punkte3;
				$ps->bismelden = $this->db_parse_bool($row->bismelden);
				$ps->anmerkung = $row->anmerkung;
				$ps->dual = $this->db_parse_bool($row->dual);
				
				$ps->status_kurzbz = $row->status_kurzbz;
				$ps->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$ps->ausbildungssemester = $row->ausbildungssemester;
				$ps->datum = $row->datum;
				$ps->orgform_kurzbz = $row->orgform_kurzbz;
				
				$this->result[] = $ps;
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
	 * Prueft ob eine Person bereits einen PreStudenteintrag
	 * fuer einen Studiengang besitzt
	 * @param person_id
	 *        studiengang_kz
	 * @return true wenn vorhanden
	 *		 false wenn nicht vorhanden
	 *		 false und errormsg wenn Fehler aufgetreten ist
	 */
	public function exists($person_id, $studiengang_kz)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudent 
				WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." 
				AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{	
				if($row->anzahl>0)
				{
					$this->errormsg = '';
					return true;
				}
				else 	
				{
					$this->errormsg = '';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
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
	 * Speichert den Prestudentstatus
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save_rolle()
	{
		if($this->new)
		{
			//pruefen ob die Rolle schon vorhanden ist
			if($this->load_rolle($this->prestudent_id, $this->status_kurzbz, $this->studiensemester_kurzbz, $this->ausbildungssemester))
			{
				$this->errormsg = 'Diese Rolle existiert bereits';
				return false;
			}

			$qry = 'INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz, 
					studiensemester_kurzbz, ausbildungssemester, datum, insertamum, insertvon, 
					updateamum, updatevon, ext_id, orgform_kurzbz, studienplan_id) VALUES('.
			       $this->db_add_param($this->prestudent_id).",".
			       $this->db_add_param($this->status_kurzbz).",".
			       $this->db_add_param($this->studiensemester_kurzbz).",".
			       $this->db_add_param($this->ausbildungssemester).",".
			       $this->db_add_param($this->datum).",".
			       $this->db_add_param($this->insertamum).",".
			       $this->db_add_param($this->insertvon).",".
			       $this->db_add_param($this->updateamum).",".
			       $this->db_add_param($this->updatevon).",".
			       $this->db_add_param($this->ext_id_prestudent).",".
			       $this->db_add_param($this->orgform_kurzbz).",".
				   $this->db_add_param($this->studienplan_id,FHC_INTEGER).");";
		}
		else
		{			
			if($this->studiensemester_old=='') 
				$this->studiensemester_old = $this->studiensemester_kurzbz;
			if($this->ausbildungssemester_old=='')
				$this->ausbildungssemester_old = $this->ausbildungssemester;
			
			//wenn der PrimaryKey geaendert wird, schauen ob schon ein Eintrag mit diesem Key vorhanden ist
			if($this->studiensemester_old!=$this->studiensemester_kurzbz || $this->ausbildungssemester_old!=$this->ausbildungssemester)
			{
				if($this->load_rolle($this->prestudent_id, $this->status_kurzbz, $this->studiensemester_kurzbz, $this->ausbildungssemester))
				{
					$this->errormsg = 'Diese Rolle existiert bereits';
					return false;
				}
			}
			$qry = 'UPDATE public.tbl_prestudentstatus SET'.
			       ' ausbildungssemester='.$this->db_add_param($this->ausbildungssemester).",".
			       ' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).",".
			       ' datum='.$this->db_add_param($this->datum).",".
			       ' updateamum='.$this->db_add_param($this->updateamum).",".
			       ' updatevon='.$this->db_add_param($this->updatevon).",".
				   ' studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).",".
			       ' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).
			       " WHERE 
						prestudent_id=".$this->db_add_param($this->prestudent_id, FHC_INTEGER, false)." 
						AND status_kurzbz=".$this->db_add_param($this->status_kurzbz, FHC_STRING, false)."
						AND studiensemester_kurzbz=".$this->db_add_param($this->studiensemester_old, FHC_STRING, false)."
						AND ausbildungssemester=".$this->db_add_param($this->ausbildungssemester_old, FHC_STRING, false).";";
		}
		
		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern des Prestudentstatus';
			return false;
		}
	}
	
	/**
	 * Loescht einen Prestudentstatus
	 * @param $prestudent_id
	 *        $status_kurzbz
	 *        $studiensemester_kurzbz
	 *		  $ausbildungssemester
	 * @return true wenn ok, false wenn Fehler
	 */
	public function delete_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_prestudentstatus 
				WHERE 
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)."
					AND status_kurzbz=".$this->db_add_param($status_kurzbz)."
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($this->load_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester))
		{
			$this->db_query('BEGIN;');
			
			$log = new log();
			
			$log->executetime = date('Y-m-d H:i:s');
			$log->beschreibung = 'Loeschen der Rolle '.$status_kurzbz.' bei '.$prestudent_id;
			$log->mitarbeiter_uid = get_uid();
			$log->sql = $qry;
			$log->sqlundo = 'INSERT INTO public.tbl_prestudentstatus(prestudent_id, status_kurzbz, studiensemester_kurzbz,'.
							' ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id, orgform_kurzbz, studienplan_id) VALUES('.
							$this->db_add_param($this->prestudent_id).','.
							$this->db_add_param($this->status_kurzbz).','.
							$this->db_add_param($this->studiensemester_kurzbz).','.
							$this->db_add_param($this->ausbildungssemester).','.
							$this->db_add_param($this->datum).','.
							$this->db_add_param($this->insertamum).','.
							$this->db_add_param($this->insertvon).','.
							$this->db_add_param($this->updateamum).','.
							$this->db_add_param($this->updatevon).','.
							$this->db_add_param($this->ext_id_prestudent).','.
							$this->db_add_param($this->orgform_kurzbz).','.
							$this->db_add_param($this->studienplan_id, FHC_INTEGER).');';
			if($log->save(true))
			{
						
				if($this->db_query($qry))
				{
					$this->db_query('COMMIT');
					return true;
				}
				else 
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Loeschen der Daten';
					return false;
				}
			}
			else 
			{
				$this->db_query('ROLLBACK');
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else 
		{
			return false;
		}			
	}
	
	/**
	 * Liefert den Letzten Status eines Prestudenten in einem Studiensemester
	 * Wenn kein Studiensemester angegeben wird, wird der letztgueltige Status ermittelt
	 * @param $prestudent_id
	 * @param $studiensemester_kurzbz
	 * @return boolean
	 */
	public function getLastStatus($prestudent_id, $studiensemester_kurzbz='', $status_kurzbz = '')
	{
		if($prestudent_id=='' || !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if($studiensemester_kurzbz!='')
			$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		
		if($status_kurzbz !='')
			$qry.= " AND status_kurzbz =".$this->db_add_param($status_kurzbz);
		
		$qry.=" ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{				
				$this->prestudent_id = $row->prestudent_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->ausbildungssemester = $row->ausbildungssemester;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->studienplan_id = $row->studienplan_id;
				return true;	
			}
			else 
			{
				$this->errormsg = 'Keine Rolle vorhanden';
				return false;
			}			
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}
	
	/**
	 * Liefert den Ersten Status eines Prestudenten mit der übergebenen Statuskurzbezeichnung
	 * @param $prestudent_id
	 * @param $studiensemester_kurzbz
	 * @return boolean
	 */
	public function getFirstStatus($prestudent_id, $status_kurzbz)
	{
		if($prestudent_id=='' || !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_prestudentstatus 
				WHERE 
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)." 
					AND status_kurzbz = ".$this->db_add_param($status_kurzbz)."
				ORDER BY datum ASC, insertamum ASC, ext_id ASC LIMIT 1";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{				
				$this->prestudent_id = $row->prestudent_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->ausbildungssemester = $row->ausbildungssemester;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->studienplan_id = $row->studienplan_id;
				return true;	
			}
			else 
			{
				$this->errormsg = 'Keine Rolle vorhanden';
				return false;
			}			
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}

	/**
	 * Laedt alle Prestudenten der Person
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPrestudenten($person_id)
	{
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_prestudent WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." ORDER BY prestudent_id";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new prestudent();
				
				$obj->prestudent_id = $row->prestudent_id;
				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$obj->ausbildungcode = $row->ausbildungcode;
				$obj->zgv_code = $row->zgv_code;
				$obj->zgvort = $row->zgvort;
				$obj->zgvdatum = $row->zgvdatum;
				$obj->zgvmas_code = $row->zgvmas_code;
				$obj->zgvmaort = $row->zgvmaort;
				$obj->zgvmadatum = $row->zgvmadatum;
				$obj->aufnahmeschluessel = $row->aufnahmeschluessel;
				$obj->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
				$obj->anmeldungreihungstest = $row->anmeldungreihungstest;
				$obj->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->punkte = $row->rt_gesamtpunkte;
				$obj->rt_punkte1 = $row->rt_punkte1;
				$obj->rt_punkte2 = $row->rt_punkte2;
				$obj->rt_punkte3 = $row->rt_punkte3;
				$obj->bismelden = $this->db_parse_bool($row->bismelden);
				$obj->person_id = $row->person_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->ext_id_prestudent = $row->ext_id;
				$obj->dual = $this->db_parse_bool($row->dual);
				$obj->ausstellungsstaat = $row->ausstellungsstaat;
                $obj->zgvdoktor_code = $row->zgvdoktor_code; 
                $obj->zgvdoktorort = $row->zgvdoktorort; 
                $obj->zgvdoktordatum = $row->zgvdoktordatum; 
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden";
			return false;
		}
	}
}
?>
