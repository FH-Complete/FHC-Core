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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class studiengang extends basis_db
{
	public $new;      			// boolean
	public $result = array();	// studiengang Objekt

	public $studiengang_kz;		// integer
	public $kurzbz;				// varchar(5)
	public $kurzbzlang;			// varchar(10)
	public $bezeichnung;		// varchar(128)
	public $english;			// varchar(128)
	public $typ;				// char(1)
	public $farbe;				// char(6)
	public $email;				// varchar(64)
	public $max_semester;		// smallint
	public $max_verband;		// char(1)
	public $max_gruppe;			// char(1)
	public $erhalter_kz;		// smallint
	public $bescheid;			// varchar(256)
	public $bescheidbgbl1;		// varchar(16)
	public $bescheidbgbl2;		// varchar(16)
	public $bescheidgz;			// varchar(16)
	public $bescheidvom;		// Date
	public $titelbescheidvom;	// Date
	public $ext_id;				// bigint
	public $orgform_kurzbz;
	public $zusatzinfo_html;
	public $sprache;
	public $testtool_sprachwahl;
	public $studienplaetze;
	public $oe_kurzbz;

	public $kuerzel;			// = typ + kurzbz (Bsp: BBE)

	public $studiengang_typ_arr = array();
	public $kuerzel_arr = array();
	public $moodle;				// boolean
	
	/**
	 * Konstruktor
	 * @param studiengang_kz Kennzahl des zu ladenden Studienganges
	 */
	public function __construct($studiengang_kz=null)
	{
		parent::__construct();
		
		if(!is_null($studiengang_kz))
			$this->load($studiengang_kz);
		
		$this->studiengang_typ_arr["b"] = "Bachelor";
		$this->studiengang_typ_arr["d"] = "Diplom";
		$this->studiengang_typ_arr["m"] = "Master";
		$this->studiengang_typ_arr["l"] = "LLL";
		$this->studiengang_typ_arr["e"] = "Erhalter";
	}

	/**
	 * Laedt einen Studiengang
	 * @param studiengang_kz KZ des Studienganges der zu Laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='".addslashes($studiengang_kz)."'";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz=$row->studiengang_kz;
				$this->kurzbz=$row->kurzbz;
				$this->kurzbzlang=$row->kurzbzlang;
				$this->bezeichnung=$row->bezeichnung;
				$this->english=$row->english;
				$this->typ=$row->typ;
				$this->farbe=$row->farbe;
				$this->email=$row->email;
				$this->max_semester=$row->max_semester;
				$this->max_verband=$row->max_verband;
				$this->max_gruppe=$row->max_gruppe;
				$this->erhalter_kz=$row->erhalter_kz;
				$this->bescheid=$row->bescheid;
				$this->bescheidbgbl1=$row->bescheidbgbl1;
				$this->bescheidbgbl2=$row->bescheidbgbl2;
				$this->bescheidgz=$row->bescheidgz;
				$this->bescheidvom=$row->bescheidvom;
				$this->ext_id=$row->ext_id;
				$this->kuerzel = mb_strtoupper($row->typ.$row->kurzbz);
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->zusatzinfo_html = $row->zusatzinfo_html;
				$this->sprache = $row->sprache;
				$this->testtool_sprachwahl = ($row->testtool_sprachwahl=='t'?true:false);
				$this->studienplaetze = $row->studienplaetze;
				$this->oe_kurzbz = $row->oe_kurzbz;

				$this->telefon=$row->telefon;
            	$this->titelbescheidvom=$row->titelbescheidvom;
            	$this->aktiv=($row->aktiv=='t'?true:false);
            	$this->moodle=($row->moodle=='t'?true:false);
				
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	/**
	 * Liefert alle Studiengaenge
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($order=null, $aktiv=true)
	{
		$qry = 'SELECT * FROM public.tbl_studiengang';
		if ($aktiv)
			$qry.=' WHERE aktiv';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		} 

		while($row = $this->db_fetch_object())
		{
			$stg_obj = new studiengang();

			$stg_obj->studiengang_kz=$row->studiengang_kz;
			$stg_obj->kurzbz=$row->kurzbz;
			$stg_obj->kurzbzlang=$row->kurzbzlang;
			$stg_obj->bezeichnung=$row->bezeichnung;
			$stg_obj->english=$row->english;
			$stg_obj->typ=$row->typ;
			$stg_obj->farbe=$row->farbe;
			$stg_obj->email=$row->email;
			$stg_obj->max_semester=$row->max_semester;
			$stg_obj->max_verband=$row->max_verband;
			$stg_obj->max_gruppe=$row->max_gruppe;
			$stg_obj->erhalter_kz=$row->erhalter_kz;
			$stg_obj->bescheid=$row->bescheid;
			$stg_obj->bescheidbgbl1=$row->bescheidbgbl1;
			$stg_obj->bescheidbgbl2=$row->bescheidbgbl2;
			$stg_obj->bescheidgz=$row->bescheidgz;
			$stg_obj->bescheidvom=$row->bescheidvom;
			$stg_obj->ext_id=$row->ext_id;
			$stg_obj->kuerzel = mb_strtoupper($row->typ.$row->kurzbz);
			$stg_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$stg_obj->zusatzinfo_html = $row->zusatzinfo_html;
			$stg_obj->sprache = $row->sprache;
			$stg_obj->testtool_sprachwahl = ($row->testtool_sprachwahl=='t'?true:false);
			$stg_obj->studienplaetze = $row->studienplaetze;
			$stg_obj->oe_kurzbz = $row->oe_kurzbz;

            $stg_obj->telefon=$row->telefon;
            $stg_obj->titelbescheidvom=$row->titelbescheidvom;
            $stg_obj->aktiv=($row->aktiv=='t'?true:false);
			$stg_obj->moodle=($row->moodle=='t'?true:false);
			
			$this->result[] = $stg_obj;
			$this->kuerzel_arr[$row->studiengang_kz]=$stg_obj->kuerzel;
		}

		return true;
	}
		
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{
		//Laenge Pruefen
		if(mb_strlen($this->bezeichnung)>128)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein.';
			return false;
		}
		if(mb_strlen($this->kurzbz)>5)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 5 Zeichen sein.';
			return false;
		}
		if(mb_strlen($this->kurzbzlang)>10)
		{
			$this->errormsg = 'Kurzbezlang darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->english)>128)
		{
			$this->errormsg = 'english darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'studiengang_kz ungueltig! ('.$this->studiengang_kz.'/'.$this->ext_id.')';
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
		{
			return false;
		}

		if($this->new)
		{
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_studiengang (studiengang_kz, kurzbz, kurzbzlang, bezeichnung, english,
				typ, farbe, email, telefon, max_verband, max_semester, max_gruppe, erhalter_kz, bescheid, bescheidbgbl1,
				bescheidbgbl2, bescheidgz, bescheidvom, titelbescheidvom, aktiv, ext_id, orgform_kurzbz, zusatzinfo_html, oe_kurzbz) VALUES ('.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->kurzbz).', '.
				$this->addslashes($this->kurzbzlang).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->english).', '.
				$this->addslashes($this->typ).', '.
				$this->addslashes($this->farbe).', '.
				$this->addslashes($this->email).', '.
				$this->addslashes($this->telefon).', '.
				$this->addslashes($this->max_verband).', '.
				$this->addslashes($this->max_semester).', '.
				$this->addslashes($this->max_gruppe).', '.
				$this->addslashes($this->erhalter_kz).', '.
				$this->addslashes($this->bescheid).', '.
				$this->addslashes($this->bescheidbgbl1).', '.
				$this->addslashes($this->bescheidbgbl2).', '.
				$this->addslashes($this->bescheidgz).', '.
				$this->addslashes($this->bescheidvom).', '.
				$this->addslashes($this->titelbescheidvom).', '.
				$this->addslashes($this->aktiv).', '.
				$this->addslashes($this->ext_id).', '.
				$this->addslashes($this->orgform_kurzbz).', '.
				$this->addslashes($this->zusatzinfo_html).', '.
				$this->addslashes($this->oe_kurzbz).';';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			$qry = 'UPDATE public.tbl_studiengang SET '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
				'kurzbz='.$this->addslashes($this->kurzbz).', '.
				'kurzbzlang='.$this->addslashes($this->kurzbzlang).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'english='.$this->addslashes($this->english).', '.
				'typ='.$this->addslashes($this->typ).', '.
				'farbe='.$this->addslashes($this->farbe).', '.
				'email='.$this->addslashes($this->email).', '.
				'max_verband='.$this->addslashes($this->max_verband).', '.
				'max_semester='.$this->addslashes($this->max_semester).', '.
				'max_gruppe='.$this->addslashes($this->max_gruppe).', '.
				'erhalter_kz='.$this->addslashes($this->erhalter_kz).', '.
				'bescheid='.$this->addslashes($this->bescheid).', '.
				'bescheidbgbl1='.$this->addslashes($this->bescheidbgbl1).', '.
				'bescheidbgbl2='.$this->addslashes($this->bescheidbgbl2).', '.
				'bescheidgz='.$this->addslashes($this->bescheidgz).', '.
				'bescheidvom='.$this->addslashes($this->bescheidvom).', '.
				'titelbescheidvom='.$this->addslashes($this->titelbescheidvom).', '.
				'ext_id='.$this->addslashes($this->ext_id).', '.
				'telefon='.$this->addslashes($this->telefon).', '.
				'orgform_kurzbz='.$this->addslashes($this->orgform_kurzbz).', '.
				'aktiv='.$this->addslashes($this->aktiv).', '.
				'oe_kurzbz='.$this->addslashes($this->oe_kurzbz).','.
				'zusatzinfo_html='.$this->addslashes($this->zusatzinfo_html).' '.
				'WHERE studiengang_kz='.$this->addslashes($this->studiengang_kz).';';
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
	 * Setzt Studiengaenge aktiv/inaktiv
	 * benoetigt studiengang_kz und 'on'/'off'
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function toggleAktiv($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "UPDATE public.tbl_studiengang SET aktiv = NOT aktiv WHERE studiengang_kz='".addslashes($studiengang_kz)."'";

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

}
?>