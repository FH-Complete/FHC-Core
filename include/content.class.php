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
	
	public function getContent($content_id, $sprache='German', $version=null, $sichtbar=null)
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
		if($version!='')
			$qry.=" AND tbl_contentsprache.version='".addslashes($version)."'";
		$qry.=" ORDER BY version LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->content_id = $row->content_id;
				$this->titel = $row->titel;
				$this->oe_kurzbz = $row->oe_kurzbz;
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
	 * Laedt die Gruppen, welchen diesen Content betrachten duerfen
	 *
	 * @param $content_id
	 */
	public function loadGruppen($content_id)
	{
		$qry = "SELECT 
					tbl_contentgruppe.gruppe_kurzbz,
					tbl_contentgruppe.insertamum,
					tbl_contentgruppe.insertvon,
					tbl_gruppe.bezeichnung
				FROM 
					campus.tbl_contentgruppe 
					JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE
					content_id='".addslashes($content_id)."' 
				ORDER BY gruppe_kurzbz";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				
				$obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->bezeichnung = $row->bezeichnung;
				
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
	 * Loescht eine Gruppenzuteilung
	 * 
	 * @param $content_id
	 * @param $gruppe_kurzbz
	 * @return boolean
	 */
	public function deleteGruppe($content_id, $gruppe_kurzbz)
	{
		$qry = "DELETE FROM campus.tbl_contentgruppe WHERE content_id='".addslashes($content_id)."' AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Zuteilung';
			return false;
		}
	}
	
	/**
	 * Prueft ob eine Gruppenzuteilung vorhanden ist
	 * 
	 * @param $content_id
	 * @param $gruppe_kurzbz
	 * @return boolean
	 */
	public function isGruppeZugeteilt($content_id, $gruppe_kurzbz)
	{
		$qry = "SELECT 1 FROM campus.tbl_contentgruppe WHERE content_id='".addslashes($content_id)."' AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."';";
		
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zuteilung';
			return false;
		}
	}
	
	/**
	 * Fuegt eine Gruppe zu einem Content hinzu
	 * @return boolean
	 */
	public function addGruppe()
	{
		if($this->isGruppeZugeteilt($this->content_id, $this->gruppe_kurzbz))
		{
			$this->errormsg = 'Diese Gruppe ist bereits zugeordnet';
			return false;
		}
		
		$qry = 'INSERT INTO campus.tbl_contentgruppe (content_id, gruppe_kurzbz, insertamum, insertvon) VALUES('.
				$this->addslashes($this->content_id).','.
				$this->addslashes($this->gruppe_kurzbz).','.
				$this->addslashes($this->insertamum).','.
				$this->addslashes($this->insertvon).');';
				
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Zuteilen der Gruppe';
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
	
	/**
	 * Speichert den XML Content
	 * @param $contentsprache_id
	 * @param $content
	 */
	public function saveContent($contentsprache_id, $content)
	{
		$qry="UPDATE campus.tbl_contentsprache SET content='".addslashes($content)."' WHERE contentsprache_id='".addslashes($contentsprache_id)."';";
		if($this->db_query($qry))
			return true;
		else
			return false;
	}
	
	/**
	 * Speichert zusaetzliche Informationen zum Content 
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = "BEGIN;INSERT INTO campus.tbl_content(template_kurzbz, oe_kurzbz, titel, updatevon, updateamum, insertvon, insertamum) VALUES(".
					$this->addslashes($this->template_kurzbz).','.
					$this->addslashes($this->oe_kurzbz).','.
					$this->addslashes($this->titel).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->insertamum).');'.
					'INSERT INTO campus.tbl_contentsprache(content, sprache, content_id, version, sichtbar, insertamum, insertvon) VALUES('.
					$this->addslashes($this->content).','.
					$this->addslashes($this->sprache).','.
					"currval('campus.seq_content_content_id'),".
					$this->addslashes($this->version).','.
					($this->sichtbar?'true':'false').','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';					
		}
		else
		{
			$qry = "UPDATE campus.tbl_content SET ".
					" titel=".$this->addslashes($this->titel).','.
					" updatevon=".$this->addslashes($this->updatevon).','.
					" updateamum=".$this->addslashes($this->updateamum).','.
					" template_kurzbz=".$this->addslashes($this->template_kurzbz).','.
					" oe_kurzbz=".$this->addslashes($this->oe_kurzbz).
					" WHERE content_id='".addslashes($this->content_id)."';".
					"UPDATE campus.tbl_contentsprache SET ".
					" sichtbar=".($this->sichtbar?'true':'false').
					" WHERE contentsprache_id='".addslashes($this->contentsprache_id)."';";
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_content_content_id') as content_id, currval('campus.seq_contentsprache') as contentsprache_id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->content_id = $row->content_id;
						$this->contentsprache_id = $row->contentsprache_id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg='Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg='Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg='Fehler beim Speichern der Daten';
			return false;
		}				
	}
	
	/**
	 * Laedt die Child-Contents eines Eintrages
	 * 
	 * @param $content_id
	 */
	public function getChilds($content_id)
	{
		$qry = "SELECT 
					*
				FROM 
					campus.tbl_contentchild 
					JOIN campus.tbl_content ON(tbl_content.content_id=tbl_contentchild.child_content_id)
				WHERE 
					tbl_contentchild.content_id='".addslashes($content_id)."' 
				ORDER BY titel";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				
				$obj->content_id = $row->content_id;
				$obj->child_content_id = $row->child_content_id;
				$obj->titel = $row->titel;
				
				$this->result[] = $obj;
			}
		}
	}
	

	/**
	 * Laedt alle Content Eintraege
	 * 
	 */
	public function getAll()
	{
		$qry = "SELECT 
					*
				FROM 
					campus.tbl_content
				ORDER BY titel";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				
				$obj->content_id = $row->content_id;
				$obj->titel = $row->titel;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->template_kurzbz = $row->template_kurzbz;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Contents';
			return false;
		}
	}
	
	/**
	 * Loescht eine Contentzuordnung
	 * 
	 * @param $content_id
	 * @param $child_content_id
	 * @return boolean
	 */
	public function deleteChild($content_id, $child_content_id)
	{
		$qry = "DELETE FROM campus.tbl_contentchild WHERE content_id='".addslashes($content_id)."' AND child_content_id='".addslashes($child_content_id)."'";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Zuteilung';
			return false;
		}
	}
			
	/**
	 * Fuegt eine Gruppe zu einem Content hinzu
	 * @return boolean
	 */
	public function addChild()
	{
		$qry = 'INSERT INTO campus.tbl_contentchild (content_id, child_content_id, insertamum, insertvon) VALUES('.
				$this->addslashes($this->content_id).','.
				$this->addslashes($this->child_content_id).','.
				$this->addslashes($this->insertamum).','.
				$this->addslashes($this->insertvon).');';
				
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Zuteilen der Gruppe';
			return false;
		}
	}
}
?>