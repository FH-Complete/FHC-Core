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
 * Authors: Harald Bamberger <harald.bamberger@technikum-wien.at>
 */
require_once(dirname(__DIR__) . '/basis_db.class.php');
/**
 * Description of covidhelper
 *
 * @author bambi
 */
class CovidHelper extends basis_db
{
	const STATUS_OK			= 1;
	const STATUS_NOTOK		= 0;
	const STATUS_UNKNOWN	= -1;
	const STATUS_NOTSET		= -2;
	
	const TITLE_OK		= 'Nachweis gültig';
	const TITLE_NOTOK	= 'Nachweis ungültig';
	const TITLE_UNKNOWN = 'Nachweis unbekannt';
	
	const DB_SCHEMA		= 'public';
	const DB_TABLE		= 'tbl_person';
	const DB_UDFNAME	= 'udf_3gvalid';
	
	protected $isUdfDefined;
	
	protected $uids;
	protected $covidstatus;

	public function __construct()
	{
		parent::__construct();
		$this->uids = array();
		$this->covidstatus = array();
		$this->isUdfDefined = false;
		$this->checkIfUdfValuesAreDefined();
	}

	public function isUdfDefined() 
	{
		return $this->isUdfDefined;
	}
	
	public function fetchCovidStatus(array $uids) 
	{
		$this->uids = $uids;
		$this->covidstatus = array();
		$this->fetchCovidValidStatus();
	}
	
	public function getIconHtml($uid) 
	{
		$html = '';
		$status = isset($this->covidstatus[$uid]) ? $this->covidstatus[$uid] : self::STATUS_NOTSET;
		switch ($status)
		{
			case self::STATUS_OK:
				$html = '<i title="' . $this->getTitle($uid) . '" class="fa fa-check-circle" aria-hidden="true" style="color: green; margin-right: .5em;"></i>';
				break;
			case self::STATUS_NOTOK:
			case self::STATUS_UNKNOWN:
				$html = '<i title="' . $this->getTitle($uid) . '" class="fa fa-times-circle" aria-hidden="true" style="color: red; margin-right: .5em;"></i>';
				break;
/*
			case self::STATUS_UNKNOWN:
				$html = '<i title="' . $this->getTitle($uid) . '" class="fa fa-question-circle" aria-hidden="true" style="color: grey; margin-right: .5em;"></i>';
				break;
 */
			default:
				$html = '';
				break;
		}
		return $html;
	}
	
	public function getBootstrapClass($uid) 
	{
		$class = '';
		$status = isset($this->covidstatus[$uid]) ? $this->covidstatus[$uid] : self::STATUS_NOTSET;
		switch ($status)
		{
			case self::STATUS_OK:
				$class = 'success';
				break;
			case self::STATUS_NOTOK:
			case self::STATUS_UNKNOWN:
				$class = 'danger';
				break;
/*
			case self::STATUS_UNKNOWN:
				$class = 'warning';
				break;
 */
			default:
				$class = '';
				break;
		}
		return $class;
	}

	public function getTitle($uid) 
	{
		$title = '';
		$status = isset($this->covidstatus[$uid]) ? $this->covidstatus[$uid] : self::STATUS_NOTSET;
		switch ($status)
		{
			case self::STATUS_OK:
				$title = self::TITLE_OK;
				break;
			case self::STATUS_NOTOK:
			case self::STATUS_UNKNOWN:
				$title = self::TITLE_NOTOK;
				break;
/*
			case self::STATUS_UNKNOWN:
				$title = self::TITLE_UNKNOWN;
				break;
 */
			default:
				$title = '';
				break;
		}
		return $title;
	}

	public function getCovidStatus() 
	{
		return $this->covidstatus;
	}
	
	protected function fetchCovidValidStatus() 
	{
		if( !($this->isUdfDefined && is_array($this->uids) && (count($this->uids) > 0)) ) 
		{
			return;
		}
		$sql = <<<EOSQL
SELECT b.uid, CASE 
		WHEN (p."udf_values" -> 'udf_3gvalid')::text::date >= CURRENT_DATE::text::date THEN 1 
		WHEN (p."udf_values" -> 'udf_3gvalid')::text::date < CURRENT_DATE::text::date THEN 0 
		ELSE -1 
	END AS covidvalid 
	FROM tbl_person p 
	JOIN tbl_benutzer b ON b.person_id = p.person_id AND b.uid IN ({$this->implode4SQL($this->uids)})
EOSQL;
	
		$this->covidstatus = array();
		if( $this->db_query($sql) ) 
		{
			while( false !== ($row = $this->db_fetch_object()) )
			{
				$this->covidstatus[$row->uid] = $row->covidvalid; 
			}
		} else {
			$this->errormsg = "Fehler in der Abfrage des Covidstatus.";
		}	
	}

	public function checkIfUdfValuesAreDefined()
	{
		$sql = 'SELECT count(name) AS "udfdefined" '
			 . 'FROM "system"."tbl_udf", jsonb_to_recordset("jsons") AS items(name text) '
			 . 'WHERE "schema" = \'' . self::DB_SCHEMA . '\' '
			 . 'AND "table" = \'' . self::DB_TABLE . '\' '
			 . 'AND "name" = \'' . self::DB_UDFNAME . '\'';
		if ( $this->db_query($sql) )
		{
			if ($row = $this->db_fetch_object())
			{
				$this->isUdfDefined = ($row->udfdefined > 0) ? true : false;
			}
			else 
			{
				$this->errormsg = "Fehler in der Abfrage beim Pruefen der UDFs. Kein Datensatz gefunden.";
				$this->isUdfDefined = false;
			}
		}
		else 
		{
			$this->errormsg = "Fehler in der Abfrage beim Pruefen der UDFs.";
			$this->isUdfDefined = false;
		}
	}
}
