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

class studiengang
{
	var $conn;    				// resource DB-Handle
	var $new;      				// boolean
	var $errormsg;			// string
	var $result = array();			// studiengang Objekt

	var $studiengang_kz;		// integer
	var $kurzbz;				// varchar(5)
	var $kurzbzlang;			// varchar(10)
	var $bezeichnung;			// varchar(128)
	var $english;				// varchar(128)
	var $typ;				// char(1)
	var $farbe;				// char(6)
	var $email;				// varchar(64)
	var $max_semester;			// smallint
	var $max_verband;			// char(1)
	var $max_gruppe;			// char(1)
	var $erhalter_kz;			// smallint
	var $bescheid;			// varchar(256)
	var $bescheidbgbl1;			// varchar(16)
	var $bescheidbgbl2;			// varchar(16)
	var $bescheidgz;			// varchar(16)
	var $bescheidvom;			// Date
	var $titelbescheidvom;		// Date
	var $ext_id;				// bigint
	var $orgform_kurzbz;
	var $zusatzinfo_html;
	var $sprache;
	var $testtool_sprachwahl;

	var $kuerzel;			// = typ + kurzbz (Bsp: BBE)

	var $studiengang_typ_arr = array();
	var $kuerzel_arr = array();

	
	// **************************************************************
	// * Konstruktor
	// * @param conn Connection zur Datenbank
	// *        studiengang_kz Kennzahl des zu ladenden Studienganges
	// **************************************************************
	function studiengang($conn, $studiengang_kz=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}

		if(is_numeric($studiengang_kz))
			$this->load($studiengang_kz);
		
		$this->studiengang_typ_arr["b"] = "Bachelor";
		$this->studiengang_typ_arr["d"] = "Diplom";
		$this->studiengang_typ_arr["m"] = "Master";
		$this->studiengang_typ_arr["l"] = "LLL";
		$this->studiengang_typ_arr["e"] = "Erhalter";
	}

	// *****************************************************
	// * Laedt einen Studiengang
	// * @param stg_id ID des Studienganges der zu laden ist
	// * @return true wenn ok, false im Fehlerfall
	// *****************************************************
	function load($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_studiengang WHERE studiengang_kz=$studiengang_kz";

		if($res = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($res))
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
				$this->kuerzel = strtoupper($row->typ.$row->kurzbz);
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->zusatzinfo_html = $row->zusatzinfo_html;
				$this->sprache = $row->sprache;
				$this->testtool_sprachwahl = $row->testtool_sprachwahl;

				$this->telefon=$row->telefon;
            	$this->titelbescheidvom=$row->titelbescheidvom;
            	$this->aktiv=$row->aktiv;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	// *******************************************
	// * Liefert alle Studiengaenge
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function getAll($order=null, $aktiv=true)
	{
		$qry = 'SELECT * FROM public.tbl_studiengang';
		if ($aktiv)
			$qry.=' WHERE aktiv';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$stg_obj = new studiengang($this->conn);

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
			$stg_obj->kuerzel = strtoupper($row->typ.$row->kurzbz);
			$stg_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$stg_obj->zusatzinfo_html = $row->zusatzinfo_html;
			$stg_obj->sprache = $row->sprache;
			$stg_obj->testtool_sprachwahl = $row->testtool_sprachwahl;

            $stg_obj->telefon=$row->telefon;
            $stg_obj->titelbescheidvom=$row->titelbescheidvom;
            $stg_obj->aktiv=$row->aktiv;
			
			$this->result[] = $stg_obj;
			$this->kuerzel_arr[$row->studiengang_kz]=$stg_obj->kuerzel;
		}

		return true;
	}

	/**
	 * Loescht einen Studiengang
	 * @param $stg_id ID des zu loeschenden Studienganges
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($stg_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		$this->bezeichnung = str_replace("'",'´',$this->bezeichnung);
		$this->kurzbz = str_replace("'",'´',$this->kurzbz);
		$this->kurzbzlang = str_replace("'",'´',$this->kurzbzlang);
		$this->english = str_replace("'",'´',$this->english);

		//Laenge Pruefen
		if(strlen($this->bezeichnung)>128)
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 128 Zeichen sein bei <b>$this->ext_id</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->kurzbz)>5)
		{
			$this->errormsg = "Kurzbez darf nicht laenger als 5 Zeichen sein bei <b>$this->ext_id</b> - $this->kurzbz";
			return false;
		}
		if(strlen($this->kurzbzlang)>10)
		{
			$this->errormsg = "Kurzbezlang darf nicht laenger als 10 Zeichen sein bei <b>$this->ext_id</b> - $this->kurzbzlang";
			return false;
		}
		if(strlen($this->english)>128)
		{
			$this->errormsg = "english darf nicht laenger als 128 Zeichen sein bei <b>$this->ext_id</b> - $this->english";
			return false;
		}
		$this->errormsg = '';
		return true;
	}
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
		{
			return false;
		}

		if($this->new)
		{
			//Pruefen ob studiengang_kz gueltig ist
			if(!is_numeric($this->studiengang_kz))
			{
				$this->errormsg = 'studiengang_kz ungueltig! ('.$this->studiengang_kz.'/'.$this->ext_id.')';
				return false;
			}
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_studiengang (studiengang_kz, kurzbz, kurzbzlang, bezeichnung, english,
				typ, farbe, email, telefon, max_verband, max_semester, max_gruppe, erhalter_kz, bescheid, bescheidbgbl1,
				bescheidbgbl2, bescheidgz, bescheidvom, titelbescheidvom, aktiv, ext_id, orgform_kurzbz, zusatzinfo_html) VALUES ('.
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
				$this->addslashes($this->zusatzinfo_html).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob studiengang_kz gueltig ist
			if(!is_numeric($this->studiengang_kz))
			{
				$this->errormsg = 'studiengang_kz ungueltig.';
				return false;
			}

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
				'zusatzinfo_html='.$this->addslashes($this->zusatzinfo_html).' '.
				'WHERE studiengang_kz='.$this->addslashes($this->studiengang_kz).';';
		}
		//echo $qry;
		if(pg_query($this->conn, $qry))
		{
			/*//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}

			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}*/
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	// *******************************************
	// * Setzt Studiengaenge aktiv/inaktiv
	// * benoetigt studiengang_kz und 'on'/'off'
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************

	function toggleAktiv($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "UPDATE public.tbl_studiengang SET aktiv = NOT aktiv WHERE studiengang_kz='$studiengang_kz'";

		if(pg_query($this->conn, $qry))
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