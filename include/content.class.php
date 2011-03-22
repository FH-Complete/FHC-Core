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
require_once('basis_db.class.php');

class content extends basis_db
{
	public $new;      // boolean
	public $result = array(); // studiensemester Objekt

	//Tabellenspalten
	public $content_id;
	public $template_kurzbz;
	public $titel;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $oe_kurzbz;
		
	public $contentsprache_id;
	public $sprache;
	public $version;
	public $sichtbar;
	public $content;
	public $reviewvon;
	public $reviewamum;
		
	/**
	 * Konstruktor 
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function getContent($content_id, $sprache='German', $version=null, $sichtbar=true)
	{
		if(!is_numeric($content_id))
		{
			$this->errormsg='ContentID ist ungueltig';
			return false;
		}
		$qry = "SELECT 
					*,
					tbl_contentsprache.insertamum, tbl_contentsprache.insertvon,
					tbl_contentsprache.updateamum, tbl_contentsprache.updatevon 
				FROM 
					campus.tbl_content
					JOIN campus.tbl_contentsprache USING(content_id)
				WHERE
					tbl_content.content_id='".addslashes($content_id)."'
					AND tbl_contentsprache.sprache='".addslashes($sprache)."'";
		if($sichtbar)
			$qry.=" AND sichtbar=true";
		if(!is_null($version))
			$qry.=" AND tbl_contentsprache.version='".addslashes($version)."'";
		$qry.=" ORDER BY version LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->content_id = $row->content_id;
				$this->titel = $row->titel;
				$this->template_kurzbz = $row->template_kurzbz;
				$this->sprache = $row->sprache;
				$this->contentsprache_id = $row->contentsprache_id;
				$this->version = $row->version;
				$this->sichtbar = ($row->sichtbar=='t'?true:false);
				$this->content = $row->content;
				$this->reviewvon = $row->reviewvon;
				$this->reviewamum = $row->reviewamum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
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
			$this->errormsg = 'Fehler beim Laden des Contents';
			return false;
		}
	}
			
	/**
	 * Prueft ob der Zugriff auf den Content eingeschraenkt ist auf
	 * eine bestimmte Benutzergruppe
	 * 
	 * @param $content_id
	 * @return true wenn eingeschraenkt sonst false
	 */
	public function islocked($content_id)
	{
		if(!is_numeric($content_id))
		{
			$this->errormsg = 'ContentID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT count(*) as anzahl FROM campus.tbl_contentgruppe WHERE content_id='".addslashes($content_id)."'";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				if($row->anzahl>0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Prueft ob ein User die Berechtigung fuer das Anzeigen des Contents besitzt
	 * 
	 * @param $content_id ID des Contents
	 * @param $uid User der versucht auf den Content zuzugreifen
	 */
	public function berechtigt($content_id, $uid)
	{
		if(!is_numeric($content_id))
		{
			$this->errormsg = 'ContentID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT 
					1
				FROM 
					campus.tbl_contentgruppe 
					JOIN public.vw_gruppen USING(gruppe_kurzbz) 
				WHERE
					tbl_contentgruppe.content_id='".addslashes($content_id)."'
					AND vw_gruppen.uid='".addslashes($uid)."'";
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		} 
	}
	
	public function getMenueArray($content_id)
	{
		$arr = array();
		if(!is_numeric($content_id))
		{
			$this->errormsg='ContentID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT 
					tbl_contentchild.content_id,
					tbl_contentchild.child_content_id,
					tbl_content.titel
				FROM
					campus.tbl_contentchild
					JOIN campus.tbl_content ON(tbl_contentchild.child_content_id=tbl_content.content_id)
				WHERE
					tbl_contentchild.content_id='".addslashes($content_id)."'";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$arr[$row->titel]=array('name'=>$row->titel, 'link'=>APP_ROOT.'content.php?content_id='.$row->child_content_id, 'target'=>'main');
				$arr[$row->titel]=array_merge($arr[$row->titel],$this->getMenueArray($row->child_content_id));
			}
		}
		return $arr; 
	}
}
?>