<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class statistik extends basis_db
{
	public $new;
	public $statistik_obj=array();
	public $result=array();

	public $statistik_kurzbz;
	public $content_id;
	public $bezeichnung;
	public $url;
	public $sql;
	public $gruppe;
	public $publish;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $udpatevon;
	public $berechtigung_kurzbz;
	public $preferences;

	public $studiengang_kz;		// integer
	public $prestudent_id;		// integer
	public $geschlecht;			// char(1)
	public $studiensemester_kurzbz;// varchar(16)
	public $ausbildungssemester;// smallint

	public $anzahl; //Hilfsvariable fuer Group BY Abfragen

	// Daten der Statistik
	public $data; // DB ressource
	public $html;
	public $csv;
	public $json;

	/**
	 * Konstruktor
	 */
	public function __construct($statistik_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($statistik_kurzbz))
			$this->load($statistik_kurzbz);
		else
			$this->new=true;
	}

	/**
	 * Laedt eine Statistik
	 * @param $statistik_kurzbz
	 */
	public function load($statistik_kurzbz)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_statistik
				WHERE
					statistik_kurzbz = " . $this->db_add_param($statistik_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->statistik_kurzbz = $row->statistik_kurzbz;
				$this->content_id = $row->content_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->url = $row->url;
				$this->sql = $row->sql;
				$this->gruppe = $row->gruppe;
				$this->publish = $this->db_parse_bool($row->publish);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->udpatevon = $row->updatevon;
				$this->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$this->preferences = $row->preferences;
				$this->new = false;

				return true;
			}
			else
			{
				$this->errormsg = 'Dieser Eintrag wurde nicht gefunden: ' . $statistik_kurzbz;
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
	 * Laedt alle Statistiken
	 * @return true wenn ok, sonst false
	 */
	public function getAll($order = FALSE)
	{
		$qry = 'SELECT * FROM public.tbl_statistik';

		if($order)
			$qry .= ' ORDER BY ' . $order;

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new statistik();

				$obj->statistik_kurzbz = $row->statistik_kurzbz;
				$obj->content_id = $row->content_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->url = $row->url;
				$obj->sql = $row->sql;
				$obj->gruppe = $row->gruppe;
				$obj->publish = $this->db_parse_bool($row->publish);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->preferences = $row->preferences;

				$this->result[] = $obj;
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
	 * Laedt alle Statistiken einer Gruppe, Parameter publish zum Filtern.
	 * @return true wenn ok, sonst false
	 */
	public function getGruppe($gruppe,$publish=null)
	{
		$qry = "SELECT * FROM public.tbl_statistik WHERE gruppe=".$this->db_add_param($gruppe);
		if ($publish===true)
			$qry.=' AND publish ';
		elseif ($publish===false)
			$qry.=' AND NOT publish ';
		$qry.=' ORDER BY bezeichnung;';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new statistik();

				$obj->statistik_kurzbz = $row->statistik_kurzbz;
				$obj->content_id = $row->content_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->url = $row->url;
				$obj->sql = $row->sql;
				$obj->gruppe = $row->gruppe;
				$obj->publish = $this->db_parse_bool($row->publish);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->udpatevon = $row->updatevon;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->preferences = $row->preferences;

				$this->result[] = $obj;
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
	 * Laedt alle Statistik Gruppen, Parameter publish zum Filtern.
	 * @return true wenn ok, sonst false
	 */
	public function getAnzahlGruppe($publish = null)
	{
		$qry = 'SELECT gruppe, count(*) AS anzahl FROM public.tbl_statistik ';

		if($publish === true)
		{
			$qry .= 'WHERE publish ';
		}
		elseif($publish === false)
		{
			$qry .= 'WHERE NOT publish ';
		}

		$qry .= ' GROUP BY gruppe ORDER BY gruppe;';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new statistik();

				$obj->gruppe = $row->gruppe;
				$obj->anzahl = $row->anzahl;

				$this->result[] = $obj;
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
	 * Speichert einen Statistik Datensatz
	 * @param $new boolean
	 * @return boolean true wenn ok false im Fehlerfalls
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		/* Da derzeit die statistik_kurzbz der primary key in der DB ist,
		 * darf er vorerst nur [a-zA-Z0-9_] (\w) enthalten. (bis auf autoincrement
		 * integer umgestellt ist)
		 */
		$this->statistik_kurzbz = preg_replace('/\W/', '', $this->statistik_kurzbz);

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_statistik(statistik_kurzbz, content_id, bezeichnung, url, sql,
					gruppe, publish, insertamum, insertvon, updateamum, updatevon, preferences, berechtigung_kurzbz) VALUES('.
					$this->db_add_param($this->statistik_kurzbz).','.
					$this->db_add_param($this->content_id,FHC_INTEGER).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->url).','.
					$this->db_add_param($this->sql).','.
					$this->db_add_param($this->gruppe).','.
					$this->db_add_param($this->publish, FHC_BOOLEAN).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->preferences).','.
					$this->db_add_param($this->berechtigung_kurzbz).');';
		}
		else
		{
			if($this->statistik_kurzbz_orig=='')
				$this->statistik_kurzbz_orig=$this->statistik_kurzbz;
			$qry = 'UPDATE public.tbl_statistik SET
				content_id='.$this->db_add_param($this->content_id,FHC_INTEGER).','.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
				' statistik_kurzbz='.$this->db_add_param($this->statistik_kurzbz).','.
				' url='.$this->db_add_param($this->url).','.
				' sql='.$this->db_add_param($this->sql).','.
				' gruppe='.$this->db_add_param($this->gruppe).','.
				' publish='.$this->db_add_param($this->publish, FHC_BOOLEAN).','.
				' insertamum='.$this->db_add_param($this->insertamum).','.
				' insertvon='.$this->db_add_param($this->insertvon).','.
				' updateamum='.$this->db_add_param($this->updateamum).','.
				' updatevon='.$this->db_add_param($this->updatevon).','.
				' preferences='.$this->db_add_param($this->preferences).','.
				' berechtigung_kurzbz='.$this->db_add_param($this->berechtigung_kurzbz).
				' WHERE statistik_kurzbz='.$this->db_add_param($this->statistik_kurzbz_orig,FHC_STRING,false);
		}
		//echo $qry;
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Liefert ein Array mit den Menueeintraegen der Statistiken
	 * Mit dem Returnwert dieser Funktion wird die entsprechende Stelle im
	 * Menue ueberschrieben
	 * @return Array fuer Menue
	 */
	public function getMenueArray()
	{
		$arr = array();

		$qry = "SELECT
					*
				FROM
					public.tbl_statistik
				ORDER BY gruppe, bezeichnung, statistik_kurzbz";

		if($result = $this->db_query($qry))
		{
			$lastgruppe='';
			while($row = $this->db_fetch_object($result))
			{
				if($row->gruppe!='' && $row->gruppe!=$lastgruppe)
				{
					$arr[$row->gruppe]=array('name'=>$row->gruppe);
					$lastgruppe=$row->gruppe;
				}
				if($row->gruppe!='')
				{
					$arr[$row->gruppe][$row->statistik_kurzbz]=array('name'=>$row->bezeichnung, 'link'=>APP_ROOT.'vilesci/statistik/statistik_frameset.php?statistik_kurzbz='.$row->statistik_kurzbz, 'target'=>'main');
					if($row->berechtigung_kurzbz!='')
						$arr[$row->gruppe][$row->statistik_kurzbz]['permissions']=array($row->berechtigung_kurzbz);
				}
				else
				{
					$arr[$row->statistik_kurzbz]=array('name'=>$row->bezeichnung, 'link'=>APP_ROOT.'vilesci/statistik/statistik_frameset.php?statistik_kurzbz='.$row->statistik_kurzbz, 'target'=>'main');
					if($row->berechtigung_kurzbz!='')
						$arr[$row->statistik_kurzbz]['permissions']=array($row->berechtigung_kurzbz);
				}
			}
		}
		return $arr;
	}

	/**
	 * Loescht einen Eintrag
	 *
	 * @param $statistik_kurzbz
	 * @return true wenn ok, sonst false
	 */
	public function delete($statistik_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_statistik WHERE statistik_kurzbz=".$this->db_add_param($statistik_kurzbz).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Löschen des Eintrages';
			return false;
		}
	}



	/**
	 * Laedt bestimmte PreStudenten
	 * @param studiengang_kz KZ des Studienganges der zu Laden ist
	 * @param studiensemester_kurzbz Studiensemester
	 * @param ausbildungssemester KZ Ausbildungssemester
	 * @param datum_stichtag Stichtag im ISO-Format, Ergebniss filtert auf <= (kleiner,gleich)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function get_prestudenten($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester=null, $datum_stichtag=null)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		if($ausbildungssemester!='' && !is_numeric($ausbildungssemester))
		{
			$this->errormsg = 'Ausbildungssemester muss eine gueltige Zahl sein';
			return false;
		}

		// Neue Studenten ermitteln
		$qry="
			SELECT
				DISTINCT prestudent_id, geschlecht, studiengang_kz, ausbildungssemester, studiensemester_kurzbz
			FROM
				public.tbl_prestudent
				JOIN public.tbl_prestudentstatus status USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
			WHERE
				status_kurzbz='Student'
				AND NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE status_kurzbz='Student' AND datum<status.datum AND prestudent_id=status.prestudent_id)
				AND studiengang_kz=".$this->db_add_param($studiengang_kz);
		if($ausbildungssemester!='')
			$qry.="	AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		$qry.="	AND ((studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if (!is_null($datum_stichtag))
			$qry.="	AND datum <=".$this->db_add_param($datum_stichtag);
		$qry.=') ';
		$qry.=" OR (studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if (!is_null($datum_stichtag))
			$qry.="	AND datum <=".$this->db_add_param($datum_stichtag);
		$qry.="))";
		$qry.=" ORDER BY prestudent_id;";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stat_obj = new statistik();
				$stat_obj->studiengang_kz=$row->studiengang_kz;
				$stat_obj->ausbildungssemester=$row->ausbildungssemester;
				$stat_obj->prestudent_id=$row->prestudent_id;
				$stat_obj->geschlecht=$row->geschlecht;
				$stat_obj->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$this->statistik_obj[]=$stat_obj;
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
	 *
	 * Liefert die DropOut Rate
	 * @param unknown_type $studiengang_kz
	 * @param unknown_type $studiensemester_kurzbz
	 * @param unknown_type $ausbildungssemester
	 * @param unknown_type $datum_stichtag
	 */
	public function get_DropOut($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester=null, $datum_stichtag=null)
	{
		$this->statistik_obj=array();

		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		if($ausbildungssemester!='' && !is_numeric($ausbildungssemester))
		{
			$this->errormsg = 'Ausbildungssemester muss eine gueltige Zahl sein';
			return false;
		}

		// Neue Studenten ermitteln
		$qry="SELECT DISTINCT prestudent_id, geschlecht, studiengang_kz, ausbildungssemester, studiensemester_kurzbz
			FROM tbl_prestudent JOIN tbl_prestudentstatus USING (prestudent_id) JOIN tbl_person USING (person_id)
			WHERE (status_kurzbz='Abbrecher')
			AND studiengang_kz=".$this->db_add_param($studiengang_kz);
		if($ausbildungssemester!='')
			$qry.="	AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		$qry.="	AND (studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if (!is_null($datum_stichtag))
			$qry.="	AND datum <=".$this->db_add_param($datum_stichtag);
		$qry.=') ';
		$qry.=" ORDER BY prestudent_id;";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stat_obj = new statistik();
				$stat_obj->studiengang_kz=$row->studiengang_kz;
				$stat_obj->ausbildungssemester=$row->ausbildungssemester;
				$stat_obj->prestudent_id=$row->prestudent_id;
				$stat_obj->geschlecht=$row->geschlecht;
				$stat_obj->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$this->statistik_obj[]=$stat_obj;
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
	 * Laedt die Daten einer Statistik (derzeit nur SQL)
	 * @param $statistik_kurzbz
	 */
	public function loadData()
	{
		$this->html='';
		$this->csv='';
		$this->json=array();
		set_time_limit(120);

		if($this->sql!='')
		{
			$sql = $this->sql;

			// Wenn im SQL ein $user vorkommt wird das durch den eingeloggten User ersetzt
			if(strpos($sql, '$user')!==false)
			{
				$uid = get_uid();
				$sql = str_replace('$user',$this->db_add_param($uid),$sql);
			}
			foreach($_REQUEST as $name=>$value)
			{
				// Inputs, die in eckigen Klammern stehen, werden als Array interpretiert
				if (substr($value, 0, 1) == '[' && substr($value, -1) == ']')
				{
					//Eckige Klammern entfernen und String aufsplitten
					$value = substr($value, 1);
					$value = substr($value, 0, -1);
					$value = explode(',', $value);
				}
				if (is_array($value))
				{
					$in = $this->db_implode4SQL($value);
					$sql = str_replace('$'.$name,$in,$sql);
				}
				else
					$sql = str_replace('$'.$name,$this->db_add_param($value),$sql);
			}
			if($this->data = $this->db_query($sql))
			{
				$this->html.= '<thead><tr>';
				$anzahl_spalten = $this->db_num_fields($this->data);
				for($spalte=0;$spalte<$anzahl_spalten;$spalte++)
				{
					$this->html.= '<th>'.$this->convert_html_chars($this->db_field_name($this->data,$spalte)).'</th>';
					$this->csv.='"'.$this->db_field_name($this->data,$spalte).'",';
				}
				$this->html.= '</tr></thead><tbody>';
				$this->csv=substr($this->csv,0,-1)."\n";
				while($row = $this->db_fetch_object($this->data))
				{
					$this->html.= '<tr>';
					$anzahl_spalten = $this->db_num_fields($this->data);

					for($spalte=0;$spalte<$anzahl_spalten;$spalte++)
					{
						$name = $this->db_field_name($this->data,$spalte);
						$this->html.= '<td>'.$this->convert_html_chars($row->$name).'</td>';
						// Umwandeln von Punkt in Komma bei Float-Werten
						if (is_numeric($row->$name))
						{
							if (strpos($row->$name,'.') != false)
								$row->$name = number_format($row->$name,2,",","");
						}
						$this->csv.= '"'.$row->$name.'",';
					}

					$this->json[] = $row;
					$this->html.= '</tr>';
					$this->csv=substr($this->csv,0,-1)."\n";
				}
				$this->html.= '</tbody>';
			}
			return true;
		}
		else
		{
			$this->errormsg= 'Zu dieser Statistik gibt es keine SQL Abfrage';
			return false;
		}
	}

	function getHtmlTable($id, $class='')
	{

		return '<table class="'.$class.'" id="'.$id.'">'.$this->html.'</table>';
	}

	function getCSV()
	{
		return $this->csv;
	}

	function writeCSV($filename, $delimiter=',', $enclosure='"')
	{
		$fh=fopen($filename,'w');

		$fieldnames=array();
		for ($i=0; $i < $this->db_num_fields($this->data); $i++)
			$fieldnames[]=$this->db_field_name($this->data,$i);
		fputcsv($fh, $fieldnames, $delimiter, $enclosure);
		$this->db_result_seek($this->data,0);
		while ($row = $this->db_fetch_row($this->data))
			fputcsv($fh, $row, $delimiter, $enclosure);
		fclose($fh);
		return true;
	}

	function getJSON()
	{
		return json_encode($this->json);
	}

	function getArray()
	{
		return $this->json;
	}

	/**
	 *
	 * Parst Variablen aus einem String und liefert diese als Array zurueck
	 * @param $value String mit Variablen
	 * z.B.: "Select * from tbl_person where person_id<'$person_id'"
	 * oder "../content/statistik/bewerberstatistik.php?stsem=$StSem&stg_kz=$stg_kz"
	 *
	 * @return Array mit den Variablennamen
	 */
	function parseVars($value)
	{
		$result = array();

		$check = '/\$[0-9A-z]+/';
		preg_match_all($check, $value, $result);
		$result = $result[0];
		$vars = array();
		for($i=0;$i<count($result);$i++)
		{
			// $user wird automatisch ersetzt und daher auch nicht geliefert
			if($result[$i]!='$user')
			{
				$vars[$i] = mb_str_replace('$','',$result[$i]);
			}
		}
		return array_unique($vars);
	}
}
