<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class zgv extends basis_db
{

	public $zgv_code;
	public $zgv_bez;
	public $zgv_kurzbz;

	public $zgvmas_code;
	public $zgvmas_bez;
	public $zgvmas_kurzbz;

	public $zgvdoktor_code;
	public $zgvdoktor_bez;
	public $zgvdoktor_kurzbz;

	public $bezeichnung;

	public $result = array();

	public function __construct($zgv_code=null)
	{
		parent::__construct();

		if($zgv_code!=null)
			$this->load($zgv_code);
	}

	/**
	 * Lädt eine zgv von tbl_zgv
	 * @param  $zgv_code
	 * @return boolean
	 */
	public function load($zgv_code)
	{
		$sprache = new sprache();
		$qry = 'SELECT *,'.$sprache->getSprachQuery('bezeichnung').' FROM bis.tbl_zgv WHERE zgv_code = '.$this->db_add_param($zgv_code, FHC_INTEGER).';';

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->zgv_code = $row->zgv_code;
				$this->zgv_bez = $row->zgv_bez;
				$this->zgv_kurzbz = $row->zgv_kurzbz;
				$this->bezeichnung=$sprache->parseSprachResult('bezeichnung',$row);
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Lädt eine Master-ZGV von tbl_zgvmaster
	 * @param  $zgvmas_code
	 * @return boolean
	 */
	public function load_mas($zgvmas_code)
	{
		$sprache = new sprache();
		$qry = 'SELECT *,'.$sprache->getSprachQuery('bezeichnung').' FROM bis.tbl_zgvmaster WHERE zgvmas_code = '.$this->db_add_param($zgvmas_code, FHC_INTEGER).';';

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->zgvmas_code = $row->zgvmas_code;
				$this->zgvmas_bez = $row->zgvmas_bez;
				$this->zgvmas_kurzbz = $row->zgvmas_kurzbz;
				$this->bezeichnung=$sprache->parseSprachResult('bezeichnung',$row);
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * lädt alle ZGVs von tbl_zgv
	 * @return boolean
	 */
	public function getAll($onlyAktiv = false)
	{
		$sprache = new sprache();
		$qry ='SELECT *,'.$sprache->getSprachQuery('bezeichnung').' FROM bis.tbl_zgv';

		if ($onlyAktiv === true)
		{
			$qry .= " WHERE aktiv";
		}

		$qry .= " ORDER BY zgv_bez";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$zgv = new zgv();
				$zgv->zgv_code = $row->zgv_code;
				$zgv->zgv_bez = $row->zgv_bez;
				$zgv->zgv_kurzbz = $row->zgv_kurzbz;
				$zgv->bezeichnung=$sprache->parseSprachResult('bezeichnung',$row);

				$this->result[] = $zgv;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Abfrage aufgetreten';
			return false;
		}
	}

	/**
	 * Lädt alle Master ZGVs von tbl_zgvmaster
	 * @return boolean
	 */
	public function getAllMaster($onlyAktiv = false)
	{
		$sprache = new sprache();
		$qry = 'SELECT *,'.$sprache->getSprachQuery('bezeichnung').' FROM bis.tbl_zgvmaster';

		if ($onlyAktiv === true)
		{
			$qry .= " WHERE aktiv";
		}

		$qry .= " ORDER BY zgvmas_bez";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$zgv_master = new zgv();

				$zgv_master->zgvmas_code = $row->zgvmas_code;
				$zgv_master->zgvmas_bez = $row->zgvmas_bez;
				$zgv_master->zgvmas_kurzbz = $row->zgvmas_kurzbz;
				$zgv_master->bezeichnung=$sprache->parseSprachResult('bezeichnung',$row);

				$this->result[] = $zgv_master;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Abfrage aufgetreten';
			return false;
		}
	}

	/**
	 * Lädt alle Doktor ZGVs von tbl_zgvdoktor
	 * @return boolean
	 */
	public function getAllDoktor($onlyAktiv = false)
	{
		$sprache = new sprache();
		$qry='SELECT *,'.$sprache->getSprachQuery('bezeichnung').' FROM bis.tbl_zgvdoktor;';
		
		if ($onlyAktiv === true)
		{
			$qry .= " WHERE aktiv";
		}
		
		$qry .= " ORDER BY zgvdoktor_bez";
		
		
		if($result = $this->db_query($qry))
		{
			while($row= $this->db_fetch_object($result))
			{
				$zgv_doktor = new zgv();

				$zgv_doktor->zgvdoktor_code = $row->zgvdoktor_code;
				$zgv_doktor->zgvdoktor_bez = $row->zgvdoktor_bez;
				$zgv_doktor->zgvdoktor_kurzbz = $row->zgvdoktor_kurzbz;
				$zgv_doktor->bezeichnung=$sprache->parseSprachResult('bezeichnung',$row);

				$this->result[] = $zgv_doktor;
			}
		}
		else
		{
			$this->erromsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

}
?>
