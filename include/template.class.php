<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Verwaltet die Templates fuer das CMS
 */
require_once('basis_db.class.php');

class template extends basis_db
{
	public $new;
	public $result = array();

	public $template_kurzbz;	// varchar(32)
	public $bezeichnung;		// varchar(256)
	public $xsd;				// xml
	public $xslt_xhtml;			// xml
	public $xslfo_pdf;			// xml
			
	/**
	 * Konstruktor 
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 
	 * Laedt das angegeben Template
	 * @param $template_kurzbz
	 * @return true wenn erfolgreich, sonst false
	 */
	public function load($template_kurzbz)
	{
		$qry = "SELECT 
					* 
				FROM 
					campus.tbl_template
				WHERE
					tbl_template.template_kurzbz=".$this->db_add_param($template_kurzbz).";";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->template_kurzbz = $row->template_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->xsd = $row->xsd;
				$this->xslt_xhtml = $row->xslt_xhtml;
				$this->xslfo_pdf = $row->xslfo_pdf;
				return true;
			}
			else
			{
				$this->errormsg='Dieser Eintrag wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Templates';
			return false;
		}
	}
	
	/**
	 * Laedt alle Templates
	 */
	public function getAll()
	{
		$qry = 'SELECT 
					* 
				FROM 
					campus.tbl_template
				ORDER BY bezeichnung';
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new template();
				
				$obj->template_kurzbz = $row->template_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->xsd = $row->xsd;
				$obj->xslt_xhtml = $row->xslt_xhtml;
				$obj->xslfo_pdf = $row->xslfo_pdf;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Templates';
			return false;
		}
	}
}
?>