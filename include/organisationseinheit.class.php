<?php
/* Copyright (C) 2009 fhcomplete.org
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
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 * 			Stefan Puraner	<puraner@technikum-wien.at>
 *			Cristina Hainberger <hainberg@technikum-wien.at>
 */
/**
 * Klasse Organisationseinheit
 *
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class organisationseinheit extends basis_db
{
	public static $oe_parents_array=array();
	public $new;	 			// @var boolean
	public $errormsg; 			// @var string
	public $result;

	//Tabellenspalten
	public $oe_kurzbz;
	public $oe_parent_kurzbz;
	public $bezeichnung;
	public $organisationseinheittyp_kurzbz;
	public $aktiv=true;
	public $lehre=true;
	public $mailverteiler=false;
	public $standort_id;

	public $oe_kurzbz_orig;
	public $beschreibung;
	public $oetyp_bezeichnung;


	/**
	 * Konstruktor
	 * @param $oe_kurzbz Kurzbz der Organisationseinheit
	 */
	public function __construct($oe_kurzbz=null)
	{
		parent::__construct();

		if($oe_kurzbz != null)
			$this->load($oe_kurzbz);
	}

	/**
	 * Liefert alle Organisationseinheiten
	 * @param $aktiv
	 * @param $lehre
	 * @param $order Sortierreihenfolge. Standard: organisationseinheittyp_kurzbz, oe_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($aktiv=null, $lehre=null, $order='organisationseinheittyp_kurzbz, oe_kurzbz')
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE 1=1";

		if(!is_null($aktiv))
			$qry.=" AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

		if(!is_null($lehre))
			$qry.=" AND lehre=".$this->db_add_param($lehre, FHC_BOOLEAN);

		$qry .=" ORDER BY ".$order;

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
			return false;
		}
	}

	/**
	 * Laedt eine Organisationseinheit
	 * @param $oe_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($oe_kurzbz)
	{
		if($oe_kurzbz == '')
		{
			$this->errormsg = 'kurzbz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=$this->db_fetch_object())
		{
			$this->oe_kurzbz = $row->oe_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->oe_parent_kurzbz = $row->oe_parent_kurzbz;
			$this->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->mailverteiler = $this->db_parse_bool($row->mailverteiler);
			$this->lehre = $this->db_parse_bool($row->lehre);
			$this->standort_id = $row->standort_id;
			$this->freigabegrenze = $row->freigabegrenze;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle Organisationseinheiten an oberster Stelle
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getHeads()
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz is null";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei Abfrage';
			return false;
		}
	}

	/**
	 * Liefert die ChildNodes einer Organisationseinheit. Optional kann ein Typ übergeben werden, welchem das Child entspricht
	 *
	 * @param string $oe_kurzbz
	 * @param string $organisationseinheittyp_kurzbz
	 * @return Array mit den Childs inkl dem Uebergebenen Element
	 */
	public function getChilds($oe_kurzbz, $organisationseinheittyp_kurzbz = null)
	{
		$childs[] = $oe_kurzbz;

		$dbversion = $this->db_version();
		if($dbversion['server']>=8.4)
		{
			//ab PostgreSQL Version 8.4 wird die Rekursion von der DB aufgeloest
			$qry = "
			WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
			(
				SELECT oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz FROM public.tbl_organisationseinheit
				WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz)."
				UNION ALL
				SELECT o.oe_kurzbz, o.oe_parent_kurzbz, o.organisationseinheittyp_kurzbz FROM public.tbl_organisationseinheit o, oes
				WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
			)
			SELECT oe_kurzbz, organisationseinheittyp_kurzbz
			FROM oes";

			if ($organisationseinheittyp_kurzbz != '')
			{
				$qry .= " WHERE organisationseinheittyp_kurzbz = ".$this->db_add_param($organisationseinheittyp_kurzbz);
			}
			$qry .= " GROUP BY oe_kurzbz, organisationseinheittyp_kurzbz;";
			if($myresult = $this->db_query($qry))
			{
				while($row = $this->db_fetch_object($myresult))
				{
					$childs[] = $row->oe_kurzbz;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Childs';
			}
			return $childs;
		}
		else
		{
			//vor 8.4 muss die Rekursion in PHP aufgeloest werden
			$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz = ".$this->db_add_param($oe_kurzbz);

			if($myresult = $this->db_query($qry))
			{
				while($row = $this->db_fetch_object($myresult))
				{
					$childs = array_merge($childs, $this->getChilds($row->oe_kurzbz));
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Childs';
			}
			return $childs;
		}
	}

	/**
	 * Liefert die Direkten KindElemente der Organisationseinheit
	 *
	 * @param $oe_kurzbz
	 * @return Array mit den Childs inkl derm Uebergebenen Element
	 */
	public function getDirectChilds($oe_kurzbz)
	{
		$childs = array();
		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz = ".$this->db_add_param($oe_kurzbz)." ORDER BY organisationseinheittyp_kurzbz DESC, bezeichnung";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$childs[] = $row->oe_kurzbz;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der Childs';
		}
		return $childs;
	}

	/**
	 * Speichert eine Organisationseinheit
	 *
	 * @param $new
	 * @return boolean
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		if($new)
		{
			//Neu anlegen
			$qry = 'INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung,
																organisationseinheittyp_kurzbz, aktiv, mailverteiler, lehre) VALUES('.
					$this->db_add_param($this->oe_kurzbz).','.
					$this->db_add_param($this->oe_parent_kurzbz).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->organisationseinheittyp_kurzbz).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->mailverteiler, FHC_BOOLEAN).','.
					$this->db_add_param($this->lehre, FHC_BOOLEAN).');';
		}
		else
		{
			if($this->oe_kurzbz=='')
			{
				$this->errormsg = 'Kurzbezeichnung darf nicht leer sein';
				return false;
			}

			if($this->oe_kurzbz_orig=='')
			{
				$this->oe_kurzbz_orig=$this->oe_kurzbz;
			}

			$qry = 'UPDATE public.tbl_organisationseinheit SET '.
					' oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).','.
					' oe_parent_kurzbz='.$this->db_add_param($this->oe_parent_kurzbz).','.
					' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
					' organisationseinheittyp_kurzbz='.$this->db_add_param($this->organisationseinheittyp_kurzbz).','.
					' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					' mailverteiler='.$this->db_add_param($this->mailverteiler, FHC_BOOLEAN).','.
					' lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN).
					" WHERE oe_kurzbz=".$this->db_add_param($this->oe_kurzbz_orig, FHC_STRING, false).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Organisationseinheit';
			return false;
		}
	}

	/**
	 * Laedt alle Organisationseinheittypen
	 *
	 * @return boolean
	 */
	public function getTypen()
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheittyp ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new organisationseinheit();

				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Typen';
			return false;
		}
	}

	/**
	 * Laedt die Organisationseinheiten die als Array uebergeben werden
	 * @param $kurzbzs Array mit den kurzbezeichnungen
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($kurzbzs, $order=null, $aktiv=true)
	{
		if(count($kurzbzs)==0)
			return true;

		$kurzbzs = $this->db_implode4SQL($kurzbzs);

		$qry = 'SELECT * FROM public.tbl_organisationseinheit WHERE oe_kurzbz in('.$kurzbzs.')';
		if ($aktiv)
			$qry.=' AND aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$obj = new organisationseinheit();

			$obj->oe_kurzbz = $row->oe_kurzbz;
			$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
			$obj->lehre = $this->db_parse_bool($row->lehre);

			$this->result[] = $obj;
		}

		return true;
	}

	/**
	 * Laedt die Organisationseinheiten in ein Array
	 * das Array enthaelt danach Key alle Organisationseinheiten und als Value dessen Parent OE
	 */
	public function loadParentsArray()
	{
		$qry = 'SELECT * FROM public.tbl_organisationseinheit';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				organisationseinheit::$oe_parents_array[$row->oe_kurzbz]=$row->oe_parent_kurzbz;
			}
		}
	}

	/**
	 * Liefert die OEs die im Tree ueberhalb der uebergebene OE liegen
	 *
	 * @param $oe_kurzbz
	 */
	public function getParents($oe_kurzbz)
	{
		$parents=array();

		$qry="WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
		(
			SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
			WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz)." and aktiv = true
			UNION ALL
			SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
			WHERE o.oe_kurzbz=oes.oe_parent_kurzbz and aktiv = true
		)
		SELECT oe_kurzbz
		FROM oes";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$parents[]=$row->oe_kurzbz;
			}
			return $parents;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Get names and types of ALL parent organisational units recursivly for all ascending
	 * org units of given organisational unit. (All parent organisational units)
	 * @param string $oe_kurzbz
	 * @return boolean True on success. If true, returns object-array with name
	 * and types of given organisational unit and of its parent organisational units.
	 */
	public function getParents_withOEType($oe_kurzbz)
	{
		$parents=array();

		$qry="
			WITH RECURSIVE
				oes (oe_kurzbz, oe_parent_kurzbz) AS
				(
					SELECT
						oe_kurzbz,
						oe_parent_kurzbz,
						bezeichnung AS oe_bezeichnung,
						organisationseinheittyp_kurzbz
					FROM
						public.tbl_organisationseinheit
					WHERE
						oe_kurzbz=".$this->db_add_param($oe_kurzbz)."
					AND
						aktiv = true

					UNION ALL

					SELECT
						o.oe_kurzbz,
						o.oe_parent_kurzbz,
						o.bezeichnung,
						o.organisationseinheittyp_kurzbz
					FROM
						public.tbl_organisationseinheit o, oes
					WHERE
						o.oe_kurzbz = oes.oe_parent_kurzbz
					AND
						aktiv = true
				)
			SELECT
				oe_kurzbz,
				oe_bezeichnung,
				tbl_organisationseinheittyp.bezeichnung AS oe_typ_bezeichnung
			FROM
				oes
			JOIN
				public.tbl_organisationseinheittyp
			USING (organisationseinheittyp_kurzbz)";



		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_bezeichnung = $row->oe_bezeichnung;
				$obj->oe_typ_bezeichnung = (!is_null($row->oe_typ_bezeichnung) ? $row->oe_typ_bezeichnung : '');

				$this->result[]= $obj;
			}
			return $this->result;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	/**
	 * Prueft ob $child eine Organisationseinheit unterhalb der OE $oe_kurzbz ist
	 *
	 * @param $oe_kurzbz parent organisationseinheit
	 * @param $child child organisationseinheit
	 * @return true wenn child, false wenn nicht
	 */
	public function isChild($oe_kurzbz, $child)
	{
		if(count(organisationseinheit::$oe_parents_array)<=0)
		{
			$this->loadParentsArray();
		}

		if(!isset(organisationseinheit::$oe_parents_array[$child]))
		{
			$this->errormsg = 'Organisationseinheit existiert nicht';
			return false;
		}

		$childs = array_keys(organisationseinheit::$oe_parents_array, $oe_kurzbz);

		foreach ($childs as $row)
		{
			if($row==$child)
			{
				return true;
			}
			else
			{
				if($this->isChild($row, $child))
					return true;
			}
		}

		return false;
	}

	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
	public function cleanResult()
	{
		$data = array();
		if(count($this->result)>0)
		{
			foreach($this->result as $oeEinheit)
			{
				$obj = new stdClass();
				$obj->oe_kurzbz = $oeEinheit->oe_kurzbz;
				$obj->oe_parent_kurzbz = $oeEinheit->oe_parent_kurzbz;
				$obj->bezeichnung = $oeEinheit->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $oeEinheit->organisationseinheittyp_kurzbz;
				$obj->aktiv = $oeEinheit->aktiv;
				$obj->mailverteiler = $oeEinheit->mailverteiler;
				$obj->lehre = $oeEinheit->lehre;
				$data[]=$obj;
			}
		}
		else
		{
			$obj = new stdClass();
			$obj->oe_kurzbz = $this->oe_kurzbz;
			$obj->oe_parent_kurzbz = $this->oe_parent_kurzbz;
			$obj->bezeichnung = $this->bezeichnung;
			$obj->organisationseinheittyp_kurzbz = $this->organisationseinheittyp_kurzbz;
			$obj->aktiv = $this->aktiv;
			$obj->mailverteiler = $this->mailverteiler;
			$obj->lehre = $this->lehre;
			$data[]=$obj;
		}
		return $data;
	}

	/**
	 * Lädt Organisationseinheiten nach ihrem Typ
	 * @param type $oetyp_kurzbz
	 * @return boolean true, wenn ok; false, im Fehlerfall
	 */
	public function getByTyp($oetyp_kurzbz)
	{
		$qry = 'SELECT * FROM public.tbl_organisationseinheit WHERE organisationseinheittyp_kurzbz='.$this->db_add_param($oetyp_kurzbz).';';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
			return false;
		}
	}

	/**
	 * Sucht nach einer Organisationseinheit
	 * @param type $searchItem
	 * @return boolean true, wenn ok; false, im Fehlerfall
	 */
	public function search($searchItem)
	{
		$qry = 'SELECT * FROM public.tbl_organisationseinheit WHERE
				(
					LOWER(bezeichnung) LIKE LOWER(\'%'.$this->db_escape((implode(' ',$searchItem))).'%\')
					OR
					LOWER(organisationseinheittyp_kurzbz) LIKE LOWER(\'%'.$this->db_escape((implode(' ',$searchItem))).'%\')
				)';
				foreach($searchItem as $value)
				{
					$qry.=' OR (LOWER(oe_kurzbz)=LOWER('.$this->db_add_param($value).'))
							OR (LOWER(bezeichnung) LIKE LOWER(\'%'.$this->db_escape($value).'%\'))';
				}
		$qry.=	' ORDER BY organisationseinheittyp_kurzbz, bezeichnung;';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
			return false;
		}
	}

	/**
	 * Laedt alle Organisationseinheiten, sortiert nach den am haeufigsten vom User in der Zeitaufzeichnung verwendeten
	 *
	 * <p>Optionaler Zeitraum (Tage in die Vergangenheit), in denen die OE verwendet wurde<br>
	 * Optionale Anzahl an Ereignissen im angegebenen Zeitraum, um die OE zu beruecksichtigen</p>
	 *
	 * @param string $user uid
	 * @param integer $zeitraum Anzahl Tage in die Vergangenheit, die fuer das Auftreten der OE beruecksichtigt werden sollen
	 * @param string $anzahl_ereignisse default: 3 Wie oft soll diese OE mindestens in $zeitraum vorkommen, um beruecksichtigt zu werden
	 * @param boolean $aktiv
	 * @param array $funktion_zuordnungen Einschränkung nach zugeordneten Funktionen (Gültigkeitszeitraum heute - 1 Monat 1 Tag nach Gültigkeitsende)
	 */
	public function getFrequent($user, $zeitraum=null, $anzahl_ereignisse='3', $aktiv=null, $funktion_zuordnungen=array())
	{
		if(!is_numeric($anzahl_ereignisse))
		{
			$this->errormsg = "anzahl_ereignisse muss eine gueltige Zahl sein";
			return false;
		}

		if (!is_null($zeitraum) && $zeitraum>0 && is_numeric($zeitraum))
			$zeit = "AND tbl_zeitaufzeichnung.start>=(now()::date-$zeitraum)";
		else
			$zeit = "";

		$qry = "SELECT * FROM (
					SELECT
					oe_kurzbz,
					oe_parent_kurzbz,
					bezeichnung,
					organisationseinheittyp_kurzbz,
					aktiv,
					lehre,
					count(tbl_zeitaufzeichnung.zeitaufzeichnung_id)
					FROM campus.tbl_zeitaufzeichnung
					JOIN public.tbl_organisationseinheit ON(oe_kurzbz IN (oe_kurzbz_1,oe_kurzbz_2))
					WHERE tbl_zeitaufzeichnung.uid=".$this->db_add_param($user)."
					$zeit
					GROUP BY tbl_organisationseinheit.oe_kurzbz HAVING COUNT(*) > $anzahl_ereignisse
	
					UNION
	
					SELECT
					oe_kurzbz,
					oe_parent_kurzbz,
					bezeichnung,
					organisationseinheittyp_kurzbz,
					aktiv,
					lehre,
					'0'
					FROM public.tbl_organisationseinheit";

		if(!is_null($aktiv))
			$qry.=" WHERE aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

		$qry .=") oes";

		if (isset($funktion_zuordnungen) && is_array($funktion_zuordnungen) && count($funktion_zuordnungen) > 0)
		{
			$qry .= " WHERE EXISTS (
    					SELECT 1 FROM public.tbl_benutzerfunktion
    					WHERE uid = ".$this->db_add_param($user)."
    					AND funktion_kurzbz IN (".$this->db_implode4SQL($funktion_zuordnungen).")
    					AND oe_kurzbz = oes.oe_kurzbz
    					AND (datum_von <= now() OR datum_von IS NULL)
    					AND (datum_bis + interval '1 month 1 day' >= now() OR datum_bis IS NULL)
					)";
		}

		$qry .= " ORDER BY count DESC,bezeichnung,oe_kurzbz";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->lehre = $this->db_parse_bool($row->lehre);
				$obj->anzahl = $row->count;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
			return false;
		}
	}

	/**
	 * Gibt alle Standorte zurück
	 * @param $aktiv
	 * @param $lehre
	 * @return boolean|array false im Fehlerfall, ansonsten ein Array
	 */
	public function getAllStandorte($aktiv=null, $lehre=null)
	{
		$result = array();
		$qry = "SELECT DISTINCT standort FROM public.tbl_organisationseinheit WHERE standort IS NOT NULL";

		if(!is_null($aktiv))
			$qry.=" AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

		if(!is_null($lehre))
			$qry.=" AND lehre=".$this->db_add_param($lehre, FHC_BOOLEAN);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$result[] = $row->standort;
			}

			return $result;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Standorte';
			return false;
		}
	}

	/**
	 * Ermittelt die Stundenobergrenze fuer Lektoren
	 * Dabei wird im OE Baum nach oben nach Stundengrenzen gesucht und die niedrigste Stundengrenze ermittelt
	 * @param $oe_kurzbz Organisationseinheit
	 * @param $fixangestellt boolean legt fest ob die Grenze
	 *        fuer Freie oder Fixangestellte Lektoren ermittelt werden soll
	 * @return array(oe_kurzbz, numeric Anzahl der Stunden)
	 */
	public function getStundengrenze($oe_kurzbz, $fixangestellt=true)
	{
		if($fixangestellt)
			$fixfrei='fix';
		else
			$fixfrei='frei';

		$qry = "
			WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
			(
				SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
				WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz)."
				UNION ALL
				SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
				WHERE o.oe_kurzbz=oes.oe_parent_kurzbz
			)
			SELECT oe_kurzbz, warn_semesterstunden_".$fixfrei." as stunden
			FROM oes JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
			ORDER BY warn_semesterstunden_".$fixfrei." asc limit 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return array($row->oe_kurzbz, $row->stunden);
			}
		}
	}

	/**
	 * Get full term of organisational unit type
	 * @param string $oetyp_kurzbz
	 * @return boolean True on success. If true, returns full term of given organisational unit type.
	 */
	public function getOETypBezeichnung($oetyp_kurzbz)
	{
		if (isset($oetyp_kurzbz) && !empty($oetyp_kurzbz))
		{
			$qry = '
				SELECT
					bezeichnung
				FROM
					public.tbl_organisationseinheittyp
				WHERE
					organisationseinheittyp_kurzbz = '. $this->db_add_param($oetyp_kurzbz). ';';

			if ($this->db_query($qry))
			{
				if ($row = $this->db_fetch_object())
				{
					$this->oetyp_bezeichnung = $row->bezeichnung;
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$this->errormsg = "Fehler in der Abfrage zum Einholen OE-Typ Bezeichnung.";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'OE Typ fehlt bzw. darf nicht leer sein.';
			return false;
		}
	}

	/**
	 * Liefert alle Organisationseinheiten die bei Lehrveranstaltungen als
	 * Lehrfach verwendet werden.
	 *
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getLehrfachOEs()
	{
		$qry = "
		SELECT distinct tbl_organisationseinheit.*
		FROM
			lehre.tbl_lehrveranstaltung
			JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
		WHERE
			tbl_organisationseinheit.aktiv
			AND EXISTS(
				SELECT 1 FROM lehre.tbl_lehreinheit
				WHERE lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id
			)
			AND tbl_organisationseinheit.lehre
		ORDER BY organisationseinheittyp_kurzbz, bezeichnung
		";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();

				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
			return false;
		}
	}
}
?>
