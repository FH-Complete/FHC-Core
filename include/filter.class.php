<?php
/*
 * filter.class.php
 *
 * Copyright 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Christian Paminger <pam@technikum-wien.at
 *			Robert Hofer <robert.hofer@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class filter extends basis_db
{
	private $new = true;			// boolean
	public $result = array();		// Objekte

	//Tabellenspalten
	protected $filter_id;				// integer (PK)
	protected $kurzbz;					// varchar(32) unique
	protected $bezeichnung;				// varchar(64) (label shown before filter dropdown)
	protected $sql;						// text
	protected $valuename;				// varchar(32)
	protected $showvalue;				// boolean  (should the value be showed in the input widget
	protected $type;					// varchar (32) type of input widget
	protected $htmlattr;				// text (HTML Attributes for the Input Widget
	protected $updateamum;				// timestamp
	protected $updatevon;				// varchar
	protected $insertamum;				// timestamp
	protected $insertvon;				// varchar

	protected $values=array();

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function __set($name,$value)
	{
		$this->$name=$value;
	}

	public function __get($name)
	{
		return $this->$name;
	}


	/**
	 * Laden eines Filters
	 * @param filter_id ID des Datensatzes, der geladen werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function load($filter_id)
	{
		if(!is_numeric($filter_id))
		{
			$this->errormsg = 'Filter_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_filter WHERE filter_id=".$this->db_add_param($filter_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->filter_id=$row->filter_id;
				$this->kurzbz=$row->kurzbz;
				$this->bezeichnung=$row->bezeichnung;
				$this->sql=$row->sql;
				$this->valuename=$row->valuename;
				$this->showvalue=$this->db_parse_bool($row->showvalue);
				$this->type=$row->type;
				$this->htmlattr=$row->htmlattr;
				$this->insertamum=$row->insertamum;
				$this->insertvon=$row->insertvon;
				$this->updateamum=$row->updateamum;
				$this->updatevon=$row->updatevon;
				$this->new       = false;

			}
		}
		else
		{
			$this->error_msg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	/**
	 * Laden eines Filters
	 * @param filter_id ID des Datensatzes, der geladen werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadAll()
	{

		$qry = "SELECT * FROM public.tbl_filter;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new filter();

				$obj->filter_id=$row->filter_id;
				$obj->kurzbz=$row->kurzbz;
				$obj->bezeichnung=$row->bezeichnung;
				$obj->sql=$row->sql;
				$obj->valuename=$row->valuename;
				$obj->showvalue = $this->db_parse_bool($row->showvalue);
				$obj->type=$row->type;
				$obj->htmlattr=$row->htmlattr;
				$obj->insertamum=$row->insertamum;
				$obj->insertvon=$row->insertvon;
				$obj->updateamum=$row->updateamum;
				$obj->updatevon=$row->updatevon;
				$obj->new       = false;

				$this->result[] = $obj;
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
	 * Suchen ob Filter vorhanden
	 * @param kurzbz des Datensatzes, der gefunden werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function isFilter($kurzbz)
	{
		foreach ($this->result as $filter)
		{
			if ($filter->kurzbz==$kurzbz)
				return true;
		}

		return false;
	}

	/**
	 * Filter Bezeichnung des Filters mit einer gegebenen kurzbz holen
	 * @param $kurzbz kurzbz des Datensatzes, der gefunden werden soll
	 * @return string|boolean Bezeichnung wenn kurzbz gefunden, false andernfalls
	 */
	public function getBezeichnungFromKurzbz($kurzbz)
	{
		foreach ($this->result as $filter)
		{
			if ($filter->kurzbz==$kurzbz)
				return $filter->__get('bezeichnung');
		}

		return false;
	}

	/**
	 * Ausgabe des HTML Widgets
	 * @param kurzbz des Datensatzes, der gefunden werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getHtmlWidget($kurzbz)
	{
		$html='';
		foreach ($this->result as $filter)
		{
			if ($filter->kurzbz==$kurzbz)
			{
				$html.="\n\t\t\t";
				switch ($filter->type)
				{
					case 'select':
						$html.='<select id="' . $filter->kurzbz . '" class="form-control" name="'.$filter->kurzbz.'[]" ';
						$html.=$filter->htmlattr;
						$html.=' >';
						$user = get_uid();
						$sql = str_replace('$user', $this->db_add_param($user), $filter->sql);
						$this->loadValues($sql, $filter->valuename, $filter->showvalue);
						foreach ($this->values as $value)
							$html.="\n\t\t\t\t".'<option value="'.$value->value.'">'.$value->text.'</option>';
						$html.="\n\t\t\t</select>";
						break;
					case 'datepicker':
						$html .= '<input type="text" id="' . $filter->kurzbz . '" class="form-control" name="' . $filter->kurzbz . '">';
						$html .= '<script>';
						$html .= '$("#' . $filter->kurzbz . '").datepicker({ dateFormat: \'yy-mm-dd\' });';
						$html .= '</script>';
						break;
				}
				return $html;
			}
		}

		return $this->errormsg;
	}

	/**
	 * Laden eines Filters
	 * @param filter_id ID des Datensatzes, der geladen werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadValues($sql, $valuename, $showvalue)
	{
		$this->values = array();

		// In case a decryption function is used then perform password substitution
		$sql = $this->replaceSQLDecryptionPassword($sql);

		if($this->db_query($sql))
		{
			while($row = $this->db_fetch_row())
			{
				$obj=new stdClass();
				$obj->text='';
				for ($i=0; $i<$this->db_num_fields(); $i++)
				{
					if ($this->db_field_name(null,$i)=='value')
					{
						$obj->value=$row[$i];
						if ($showvalue)
							$obj->text.=' ('.$row[$i].') ';
					}
					elseif ($this->db_field_name(null,$i)=='name')
					{
						if ($showvalue)
							$obj->text.=' - '.$row[$i];
						else
							$obj->text.=$row[$i];
					}
					else
						$obj->text.=' - '.$row[$i];
				}
				//$obj->text = mb_substr($obj->text,1);
				$this->values[] = $obj;
			}
			//var_dump($this);
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}



	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		/* version wird beim speichern automatisch gesetzt
		if(!is_numeric($this->version) && $this->version!=='')
		{
			$this->errormsg='version enthaelt ungueltige Zeichen';
			return false;
		}*/

		//Gesamtlaenge pruefen
		if(mb_strlen($this->kurzbz)>32)
		{
			$this->errormsg = 'Kurzbz darf nicht länger als 32 Zeichen sein';
			return false;
		}

		//Boleanfelder prüfen
		if(!is_bool($this->showvalue))
		{
			$this->errormsg='Showvalue ist ungueltig';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @param neueVersion boolean default false; wenn gesetzt, dann
	 * wird Versionsnummer auf aktuelles Maximum+1 gesetzt. (für 'Save As'
	 * Funktion bzw. zum Anlegen komplett neuer Daten)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($neueVersion=false)
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		$this->db_query('BEGIN'); //Starting Transaction

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO public.tbl_filter (kurzbz, bezeichnung, sql, valuename,
					showvalue, type, htmlattr, insertamum, insertvon) VALUES ('.
			      $this->db_add_param($this->kurzbz).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->sql).', '.
			      $this->db_add_param($this->valuename).', '.
			      $this->db_add_param($this->showvalue, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->type).', '.
			      $this->db_add_param($this->htmlattr).', '.
			      'now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob filter_id eine gueltige Zahl ist
			if(!is_numeric($this->filter_id))
			{
				$this->errormsg = 'filter_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE public.tbl_filter SET'.
				' kurzbz='.$this->db_add_param($this->kurzbz).', '.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' sql='.$this->db_add_param($this->sql).', '.
				' valuename='.$this->db_add_param($this->valuename).', '.
				' showvalue='.$this->db_add_param($this->showvalue, FHC_BOOLEAN).', '.
		      	' type='.$this->db_add_param($this->type).', '.
		      	' htmlattr='.$this->db_add_param($this->htmlattr).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	' WHERE filter_id='.$this->db_add_param($this->filter_id, FHC_INTEGER, false).';';
		}

        if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('public.seq_filter_filter_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->filter_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else
				$this->db_query('COMMIT');

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		//echo '<pre>'.var_dump($this).'</pre>';
		return $this->filter_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $filter_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($filter_id)
	{
		//Pruefen ob filter_id eine gueltige Zahl ist
		if(!is_numeric($filter_id) || $filter_id === '')
		{
			$this->errormsg = 'filter_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM public.tbl_filter WHERE filter_id=".$this->db_add_param($filter_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}

	/**
	 * Ermittelt alle POST/GET-Variablen
	 * @return Zeichenkette fuer eine GET-Methode, false im Fehlerfall
	 */
	public function getVars()
	{
		$vars='';
		foreach($_REQUEST as $name=>$value)
		{
			if (is_array($value))
			{
				foreach($value AS $val)
					$vars.='&'.$name.'='.$val;
			}
			else
				$vars.='&'.$name.'='.$value;
		}
		//$vars.='&statistik_kurzbz='.$_REQUEST['statistik_kurzbz'];
		return $vars;
	}

}
