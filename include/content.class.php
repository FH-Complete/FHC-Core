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
require_once(dirname(__FILE__).'/basis_db.class.php');

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
	public $aktiv;
	public $menu_open;
	public $beschreibung;
		
	public $contentsprache_id;
	public $sprache;
	public $version;
	public $sichtbar;
	public $content;
	public $reviewvon;
	public $reviewamum;
	public $gesperrt_uid;
		
	/**
	 * Konstruktor 
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Laedt den Content in der angegebenen Sprache
	 * Sollte der Content in dieser Sprache nicht vorhanden sein, wird der Content in der Default Sprache geladen
	 * 
	 * @param $content_id
	 * @param $sprache optional
	 * @param $version optional
	 * @param $sichtbar optional
	 */
	public function getContent($content_id, $sprache=DEFAULT_LANGUAGE, $version=null, $sichtbar=null, $load_default_language=false)
	{
		if(!is_numeric($content_id))
		{
			$this->errormsg='ContentID ist ungueltig';
			return false;
		}
		
		if(!$this->contentSpracheExists($content_id, $sprache, $version, $sichtbar))
		{
			if($load_default_language)
			{
				$sprache = DEFAULT_LANGUAGE;
			}
			else
			{
				$this->errormsg = 'Der Content existiert in dieser Sprache nicht ';
				return false;
			}
		}
		
		$qry = "SELECT 
					*,
					tbl_contentsprache.insertamum, tbl_contentsprache.insertvon,
					tbl_contentsprache.updateamum, tbl_contentsprache.updatevon 
				FROM 
					campus.tbl_content
					JOIN campus.tbl_contentsprache USING(content_id)
				WHERE
					tbl_content.content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
					AND tbl_contentsprache.sprache=".$this->db_add_param($sprache);
		if($sichtbar===true)
			$qry.=" AND sichtbar=true";
		elseif($sichtbar===false)
			$qry.=" AND sichtbar=false";
		if($version!='')
			$qry.=" AND tbl_contentsprache.version=".$this->db_add_param(intval($version), FHC_INTEGER);
		$qry.=" ORDER BY version DESC LIMIT 1";
	
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
				$this->sichtbar = $this->db_parse_bool($row->sichtbar);
				$this->content = $row->content;
				$this->reviewvon = $row->reviewvon;
				$this->reviewamum = $row->reviewamum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->menu_open = $this->db_parse_bool($row->menu_open);
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->gesperrt_uid = $row->gesperrt_uid;
				$this->beschreibung = $row->beschreibung;
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
	 * Laedt die Organisationseinheit eines Contents
	 * 
	 * @param $content_id
	 * @return varchar oe_kurzbz des Contents
	 */
	public function getOrganisationseinheit($content_id)
	{
		$qry = "SELECT oe_kurzbz FROM campus.tbl_content WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->oe_kurzbz;
			}
			else
			{
				$this->errormsg.='Es wurde kein Eintrag mit dieser ID gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg.='Fehler beim Laden der Daten';
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
		
		$qry = "SELECT count(*) as anzahl FROM campus.tbl_contentgruppe WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER);
		
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
					content_id=".$this->db_add_param($content_id, FHC_INTEGER)." 
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
		$qry = "DELETE FROM campus.tbl_contentgruppe WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)." AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);
		
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
		$qry = "SELECT 1 FROM campus.tbl_contentgruppe WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)." AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz).';';
		
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
				$this->db_add_param($this->content_id, FHC_INTEGER).','.
				$this->db_add_param($this->gruppe_kurzbz).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).');';
				
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
					(tbl_contentgruppe.content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
						OR NOT EXISTS (SELECT 1 FROM campus.tbl_contentgruppe WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)."))
					AND vw_gruppen.uid=".$this->db_add_param($uid);
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
	
	
	
	/**
	 * Speichert den XML Content
	 * @param $contentsprache_id
	 * @param $content
	 */
	public function saveContent($contentsprache_id, $content)
	{
		$qry="UPDATE campus.tbl_contentsprache SET content=".$this->db_add_param($content)." WHERE contentsprache_id=".$this->dB_add_param($contentsprache_id, FHC_INTEGER).';';
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
			$qry = "BEGIN;INSERT INTO campus.tbl_content(template_kurzbz, oe_kurzbz, updatevon, updateamum, insertvon, insertamum, aktiv, menu_open, beschreibung) VALUES(".
					$this->db_add_param($this->template_kurzbz).','.
					$this->db_add_param($this->oe_kurzbz).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->menu_open, FHC_BOOLEAN).','.
					$this->db_add_param($this->beschreibung).');';
		}
		else
		{
			$qry = "UPDATE campus.tbl_content SET ".
					" updatevon=".$this->db_add_param($this->updatevon).','.
					" updateamum=".$this->db_add_param($this->updateamum).','.
					" template_kurzbz=".$this->db_add_param($this->template_kurzbz).','.
					" oe_kurzbz=".$this->db_add_param($this->oe_kurzbz).','.
					" aktiv=".$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					" menu_open=".$this->db_add_param($this->menu_open, FHC_BOOLEAN).','.
					" beschreibung=".$this->db_add_param($this->beschreibung).
					" WHERE content_id=".$this->db_add_param($this->content_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_content_content_id') as content_id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->content_id = $row->content_id;
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
	
	/************ Menue / Childnodes *****************/
	
	/**
	 * Prueft ob der Content Kindelemente hat
	 * 
	 * @param $content_id
	 */
	public function hasChilds($content_id)
	{
		if($content_id=='' || !is_numeric($content_id))
		{
			$this->errormsg = 'ContentID ungueltig';
			return false;
		}
		$qry = "SELECT count(*) as anzahl FROM campus.tbl_contentchild 
				WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER);
		
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
				$this->errormsg = 'Fehler beim Laden von Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden von Daten';
			return false;
		}			
	}
	
	/**
	 * Liefert die Alle Childcontents des uebergebenen Contents als Array zurueck.
	 * Dieses kann zB direkt in das Vilesci Menue integriert werden
	 * 
	 * @param $content_id
	 * @param $sprache
	 */
	public function getMenueArray($content_id, $sprache=DEFAULT_LANGUAGE, $sichtbar=null)
	{
		$arr = array();
		if(!is_numeric($content_id))
		{
			$this->errormsg='ContentID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT 
					tbl_contentchild.content_id,
					tbl_contentchild.child_content_id
				FROM
					campus.tbl_contentchild
					JOIN campus.tbl_content ON(tbl_contentchild.child_content_id=tbl_content.content_id)
				WHERE
					tbl_contentchild.content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
					AND aktiv=true
				ORDER BY sort
				";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$content = new content();
				$content->getContent($row->child_content_id, $sprache, null, $sichtbar, true);
				
				if($sichtbar && !$content->sichtbar)
					continue;
			
				$arr[$content->content_id]=array('name'=>$content->titel, 'link'=>APP_ROOT.'cms/content.php?content_id='.$row->child_content_id, 'target'=>'main', 'open'=>($content->menu_open?'true':'false'),'content_id'=>$content->content_id,'template'=>$content->template_kurzbz);
				$arr[$content->content_id]=array_merge($arr[$content->content_id],$this->getMenueArray($row->child_content_id, $sprache, $sichtbar));
			}
		}
		return $arr; 
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
					tbl_contentchild.content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
				ORDER BY sort";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				
				$obj->contentchild_id = $row->contentchild_id;
				$obj->content_id = $row->content_id;
				$obj->child_content_id = $row->child_content_id;
				$obj->sort = $row->sort;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				
				$this->result[] = $obj;
			}
		}
	}
	
	/**
	 * Laedt alle Content Eintraege die fuer den uebergeben Content als
	 * Childnodes infrage kommen.
	 * Eintraege bei denen es zu einer Rekursion im Tree kommen koennte werden
	 * nicht geliefert
	 */
	public function getpossibleChilds($content_id, $sprache=DEFAULT_LANGUAGE)
	{
		$qry = "SELECT 
					*, (SELECT titel FROM campus.tbl_contentsprache WHERE sprache=".$this->db_add_param($sprache)." AND content_id=tbl_content.content_id ORDER BY version LIMIT 1) as titel
				FROM 
					campus.tbl_content
				WHERE 
					content_id NOT IN(
						WITH RECURSIVE parents(content_id, child_content_id) as 
						(
							SELECT content_id, child_content_id FROM campus.tbl_contentchild 
							WHERE child_content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
							UNION ALL
							SELECT cc.content_id, cc.child_content_id FROM campus.tbl_contentchild cc, parents 
							WHERE cc.child_content_id=parents.content_id
						)
						SELECT content_id
						FROM parents
						GROUP BY content_id)
					AND content_id<>".$this->db_add_param($content_id, FHC_INTEGER)."
					AND template_kurzbz<>'news'
				ORDER BY titel";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				
				$obj->titel = $row->titel;
				$obj->content_id = $row->content_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->template_kurzbz = $row->template_kurzbz;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->beschreibung = $row->beschreibung;
				
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
	public function deleteChild($contentchild_id)
	{
		$qry = "DELETE FROM campus.tbl_contentchild WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER);
		
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
	 * Fuegt einem Content einen Childcontent hinzu
	 * @return boolean
	 */
	public function addChild()
	{
		$qry = 'INSERT INTO campus.tbl_contentchild (content_id, child_content_id, insertamum, insertvon, sort) VALUES('.
				$this->db_add_param($this->content_id, FHC_INTEGER).','.
				$this->db_add_param($this->child_content_id, FHC_INTEGER).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).','.
				$this->db_add_param($this->sort).');';
				
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Zuteilen des Eintrages';
			return false;
		}
	}
	
	/**
	 * Holt die hoechste Sortierung eines Contentteilbaums
	 * 
	 * @param $content_id
	 */
	public function getMaxSort($content_id)
	{
		$qry="SELECT max(sort) as max FROM campus.tbl_contentchild 
				WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->max;
			}
			else
				return '0';
		}
		else
		{
			$this->errormsg = 'Fehler bei Abfrage';
			return false;
		}
	}
	
	/**
	 * Laedt alle Content Eintraege die keine Childs von anderen Contenteintraegen sind
	 * @return boolean
	 */
	public function getRootContent()
	{
		$qry = "SELECT 
					*										
				FROM (
					SELECT 
						distinct on(content_id) *					 
					FROM 
						campus.tbl_content
						LEFT JOIN campus.tbl_contentchild USING(content_id)
					WHERE
						tbl_content.template_kurzbz<>'news' AND 
						content_id NOT IN (SELECT child_content_id FROM campus.tbl_contentchild WHERE child_content_id=tbl_content.content_id)
					) as a
				ORDER BY sort, content_id";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
								
				$obj->content_id = $row->content_id;
				$obj->template_kurzbz = $row->template_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->menu_open = $this->db_parse_bool($row->menu_open);
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle aktuellen News, die aelter als zwei Monate sind
	 * @return boolean
	 */
	public function getNews()
	{
		$qry = "SELECT 
					*										
				FROM
					campus.tbl_content
					JOIN campus.tbl_news USING(content_id)
				WHERE
					tbl_news.datum>=now()-'2 month'::interval
				ORDER BY datum DESC LIMIT 100";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
								
				$obj->content_id = $row->content_id;
				$obj->template_kurzbz = $row->template_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->menu_open = $this->db_parse_bool($row->menu_open);
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Sortiert einen Menueeintrag nach oben
	 * @param $contentchild_id
	 */
	public function SortUp($contentchild_id)
	{
		$qry = "SELECT 
					sort, contentchild_id
				FROM 
					campus.tbl_contentchild 
				WHERE 
					content_id=(SELECT content_id FROM campus.tbl_contentchild 
								WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).")
					AND sort<(SELECT sort FROM campus.tbl_contentchild 
								WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).")
				ORDER BY sort DESC LIMIT 1;";
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$nachbar_id = $row->contentchild_id;
				$nachbar_sort = $row->sort;
			}
			else
			{
				$this->errormsg='Dies ist bereits der oberste Eintrag';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei Abfrage';
			return false;
		}
		
		$qry = "UPDATE campus.tbl_contentchild 
				SET sort=(SELECT sort FROM campus.tbl_contentchild 
						 WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).")
				WHERE contentchild_id=".$this->db_add_param($nachbar_id, FHC_INTEGER).";
				UPDATE campus.tbl_contentchild SET sort=".$this->db_add_param($nachbar_sort, FHC_INTEGER)." 
				WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).';';
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errosmg='Fehler beim Setzen der Sortierung';
			return false;
		}
	}
	
	/**
	 * Sortiert einen Menueeintrag nach unten
	 * @param $contentchild_id
	 */
	public function SortDown($contentchild_id)
	{
		$qry = "SELECT 
					sort, contentchild_id
				FROM 
					campus.tbl_contentchild 
				WHERE 
					content_id=(SELECT content_id FROM campus.tbl_contentchild 
								WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).")
					AND sort>(SELECT sort FROM campus.tbl_contentchild 
								WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).")
				ORDER BY sort ASC LIMIT 1;";
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$nachbar_id = $row->contentchild_id;
				$nachbar_sort = $row->sort;
			}
			else
			{
				$this->errormsg='Dies ist bereits der unterste Eintrag';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei Abfrage';
			return false;
		}
		
		$qry = "UPDATE campus.tbl_contentchild 
				SET sort=(SELECT sort FROM campus.tbl_contentchild 
						 WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).")
				WHERE contentchild_id=".$this->db_add_param($nachbar_id, FHC_INTEGER).";
				UPDATE campus.tbl_contentchild SET sort=".$this->db_add_param($nachbar_sort, FHC_INTEGER)." 
				WHERE contentchild_id=".$this->db_add_param($contentchild_id, FHC_INTEGER).';';
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errosmg='Fehler beim Setzen der Sortierung';
			return false;
		}
	}
		
	/************ Contentsprache *****************/
	
	/**
	 * Speichert den Contentsprache Eintrag
	 * 
	 * @param boolean $new
	 */
	public function saveContentSprache($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = 'INSERT INTO campus.tbl_contentsprache(sprache, content_id, version, sichtbar, content, 
					reviewvon, reviewamum, updateamum, updatevon, insertamum, insertvon, titel, gesperrt_uid) VALUES('.
					$this->db_add_param($this->sprache).','.
					$this->db_add_param($this->content_id, FHC_INTEGER).','.
					$this->db_add_param($this->version, FHC_INTEGER).','.
					$this->db_add_param($this->sichtbar, FHC_BOOLEAN).','.
					$this->db_add_param($this->content).','.
					$this->db_add_param($this->reviewvon).','.
					$this->db_add_param($this->reviewamum).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->titel).','.
					$this->db_add_param($this->gesperrt_uid).');';					
		}
		else
		{
			$qry = "UPDATE campus.tbl_contentsprache SET ".
					" sprache=".$this->db_add_param($this->sprache).','.
					" content_id=".$this->db_add_param($this->content_id, FHC_INTEGER).','.
					" version=".$this->db_add_param($this->version, FHC_INTEGER).','.
					" sichtbar=".$this->db_add_param($this->sichtbar, FHC_BOOLEAN).','.
					" content=".$this->db_add_param($this->content).','.
					" reviewvon=".$this->db_add_param($this->reviewvon).','.
					" reviewamum=".$this->db_add_param($this->reviewamum).','.
					" updatevon=".$this->db_add_param($this->updatevon).','.
					" updateamum=".$this->db_add_param($this->updateamum).','.
					" titel=".$this->db_add_param($this->titel).','.
					" gesperrt_uid=".$this->db_add_param($this->gesperrt_uid).
					" WHERE contentsprache_id=".$this->db_add_param($this->contentsprache_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_contentsprache') as contentsprache_id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
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
	 * Liefert die hoechste Versionsnummer eines Contents/Sprache
	 * 
	 * @param $content_id
	 * @param $sprache
	 */
	public function getMaxVersion($content_id, $sprache)
	{
		$qry = "SELECT max(version) maxversion FROM campus.tbl_contentsprache WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($sprache);
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
				return $row->maxversion;
			else
				return 0;
		}
		else
		{
			$this->errormsg='Fehler beim Ermitteln der hoechsten Version';
			return false;
		}	
	}
	
	/**
	 * Liefert die Versionen des Contents
	 * 
	 * @param $content_id
	 */
	public function loadVersionen($content_id, $sprache)
	{
		$qry = "SELECT
					contentsprache_id, sprache, content_id, version, sichtbar, reviewamum, reviewvon,
					updateamum, updatevon, insertamum, insertvon, titel
				FROM campus.tbl_contentsprache 
				WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($sprache)."
				ORDER BY version DESC";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				
				$obj->contentsprache_id = $row->contentsprache_id;
				$obj->sprache = $row->sprache;
				$obj->titel = $row->titel;
				$obj->content_id = $row->content_id;
				$obj->version = $row->version;
				$obj->sichtbar = $this->db_parse_bool($row->sichtbar);
				$obj->reviewvon = $row->reviewvon;
				$obj->reviewamum = $row->reviewamum;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$this->result[] = $obj;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Liefert die Sprachen in denen der Content vorhanden ist
	 * 
	 * @param $content_id
	 */
	public function getLanguages($content_id)
	{
		$qry = "SELECT distinct sprache FROM campus.tbl_contentsprache 
				WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER);
		$sprachen = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$sprachen[]=$row->sprache;
			}
		}
		array_unique($sprachen);
		return $sprachen;
	}
	
	/**
	 * Prueft ob der Content in der angegeben Sprache vorhanden ist
	 * @param $content_id
	 * @param $sprache
	 * @param $version optional
	 * @param $sichtbar optional
	 * @return boolean
	 */
	public function contentSpracheExists($content_id, $sprache, $version=null, $sichtbar=null)
	{
		$qry = "SELECT 1 FROM campus.tbl_contentsprache 
				WHERE 
					content_id=".$this->db_add_param($content_id, FHC_INTEGER)." 
					AND sprache=".$this->db_add_param($sprache)."
				";
		if(!is_null($version) && $version!='')
			$qry.=" AND version=".$this->db_add_param(intval($version), FHC_INTEGER);
		if($sichtbar)
			$qry.=" AND sichtbar=".$this->db_add_param($sichtbar, FHC_BOOLEAN);
		
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehlerhafte SQL Abfrage';
			return false;
		}
	}
	
	/**
	 * Laedt einen Eintrag der Tabelle Contentsprache
	 * 
	 * @param $contentsprache_id
	 */
	public function loadContentSprache($contentsprache_id)
	{
		$qry = "SELECT * FROM campus.tbl_contentsprache 
				WHERE contentsprache_id=".$this->db_add_param($contentsprache_id, FHC_INTEGER);
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->contentsprache_id = $row->contentsprache_id;
				$this->sprache = $row->sprache;
				$this->content_id = $row->content_id;
				$this->version = $row->version;
				$this->sichtbar = $this->db_parse_bool($row->sichtbar);
				$this->content = $row->content;
				$this->reviewvon = $row->reviewvon;
				$this->reviewamum = $row->reviewamum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->titel = $row->titel;
				$this->gesperrt_uid = $row->gesperrt_uid;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Es ist kein Eintrag mit dieser ID vorhanden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/************ ContentLOG *****************/
	
	/**
	 * Liefert den Logeintrag der fuer die Sperre verantwortlich ist
	 * 
	 * @param $contentsprache_id
	 */
	public function getSperrLog($contentsprache_id)
	{
		$qry = "SELECT * FROM campus.tbl_contentlog 
				WHERE contentsprache_id=".$this->db_add_param($contentsprache_id, FHC_INTEGER)."
				AND ende is null LIMIT 1;";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->contentlog_id=$row->contentlog_id;
				$this->contentsprache_id=$row->contentsprache_id;
				$this->uid = $row->uid;
				$this->start = $row->start;
				$this->ende = $row->ende;
			}
		}
	}
	
	/**
	 * Sperrt einen Eintrag zum Bearbeiten
	 * 
	 * @param $contentsprache_id
	 * @param $user
	 */
	public function sperren($contentsprache_id, $user)
	{
		$qry = 'INSERT INTO campus.tbl_contentlog(uid, contentsprache_id, start) VALUES('.
				$this->db_add_param($user).','.
				$this->db_add_param($contentsprache_id).',now());
				UPDATE campus.tbl_contentsprache SET gesperrt_uid='.$this->db_add_param($user).
				' WHERE contentsprache_id='.$this->db_add_param($contentsprache_id, FHC_INTEGER);
				
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Sperren';
			return false;
		}
	}
	
	/**
	 * Gibt den gesperrten Content eines Users wieder frei
	 * 
	 * @param $user
	 */
	public function freigabeUser($user)
	{
		$qry = 'UPDATE campus.tbl_contentlog SET ende=now() WHERE uid='.$this->db_add_param($user).'
				 AND ende is null;
				UPDATE campus.tbl_contentsprache SET gesperrt_uid=null WHERE
				gesperrt_uid='.$this->db_add_param($user).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Freigeben des Contents';
			return false;
		}
	}
	
	/**
	 * Gibt einen gesperrten Content wieder frei
	 * 
	 * @param $contentsprache_id
	 */
	public function freigabeContent($contentsprache_id)
	{
		$qry = 'UPDATE campus.tbl_contentlog SET ende=now() 
				WHERE contentsprache_id='.$this->db_add_param($contentsprache_id).'
					AND ende is null;
				UPDATE campus.tbl_contentsprache SET gesperrt_uid=null WHERE 
				contentsprache_id='.$this->db_add_param($contentsprache_id).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Freigeben des Contents';
			return false;
		}
	}
	
	/**
	 * Durchsucht den CIS-Content nach Uebereinstimmung mit den Suchbegriffen. 
	 * Erst werden Uebereinstimmungen mit dem Titel geliefert (aus den templates contentmittitel, contentohnetitel und redirect)
	 * und danach solche mit dem Content selbst (aus den templates contentmittitel und contentohnetitel).
	 * Limit optional.
	 * 
	 * @param array $searchItems Array mit Suchbegriffen
	 * @param integer $limit (optional) Anzahl an Datensaetzen, die zurueckgegeben werden sollen
	 */
	public function search($searchItems, $limit=null)
	{
		$qry = "SELECT 
					distinct on(content_id,sprache,version) content_id, content::text, titel, sprache, version, template_kurzbz, 1 AS sort
				FROM 
					campus.tbl_contentsprache
					JOIN campus.tbl_content USING(content_id)
				WHERE 
					sichtbar=true
					AND aktiv=true
					AND version = (SELECT campus.get_highest_content_version (content_id))
					AND template_kurzbz IN('contentmittitel','contentohnetitel','redirect','contentmittitel_filterwidget')";
		foreach($searchItems as $value)
		{
			$qry .= " AND 
						(
							lower(titel::text) like lower('%".$this->db_escape($value)."%') 
							OR lower(titel::text) like lower('%".$this->db_escape(htmlentities($value,ENT_NOQUOTES,'UTF-8'))."%')
						)
					";
		}
		$qry .= " UNION SELECT
					distinct on(content_id,sprache,version) content_id, content::text, titel, sprache, version, template_kurzbz, 2 AS sort
				FROM
					campus.tbl_contentsprache
					JOIN campus.tbl_content USING(content_id)
				WHERE
					sichtbar=true
					AND aktiv=true
					AND version = (SELECT campus.get_highest_content_version (content_id))";
		foreach($searchItems as $value)
		{
			$qry .= " AND
						(template_kurzbz IN('contentmittitel','contentohnetitel','contentmittitel_filterwidget')
							AND
							(
								lower(content::text) like lower('%".$this->db_escape($value)."%')
								OR lower(content::text) like lower('%".$this->db_escape(htmlentities($value,ENT_NOQUOTES,'UTF-8'))."%')
							)
						)
					";
		}
		$qry .= " ORDER BY sort,sprache,content_id DESC";

		if(!is_null($limit) && is_numeric($limit))
			$qry.=" LIMIT ".$limit;
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				$obj->content_id = $row->content_id;
				$obj->content = $row->content;
				$obj->titel = $row->titel;
				$obj->sprache = $row->sprache;
				$obj->version = $row->version;
				$obj->template_kurzbz = $row->template_kurzbz;
				
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
	 * Laedt alle Content Eintraege unterhalb eines Contents
	 * (Ohne Newseintraege)
	 */
	public function getAllChilds($content_id)
	{
		$qry = "
			SELECT 
				content_id
			FROM 
				campus.tbl_content
			WHERE 
				content_id IN(
					WITH RECURSIVE childs(content_id, child_content_id) as 
					(
						SELECT content_id, child_content_id FROM campus.tbl_contentchild 
						WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
						UNION ALL
						SELECT cc.child_content_id, null FROM campus.tbl_contentchild cc, childs
						WHERE cc.content_id=childs.content_id
					)
					SELECT content_id
					FROM childs
					GROUP BY content_id)
				AND template_kurzbz<>'news'
			";
		$ids=array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$ids[] = $row->content_id;
			}
			return $ids;
		}
	}

	/**
	 * 
	 * Laedt Contenteintraege anhand einer ID Liste
	 * @param $ids Array mit Content IDs
	 * @param $sprache
	 * @param $sichtbar
	 */
	public function loadArray($ids, $sprache, $sichtbar=null)
	{
		if(count($ids)==0)
			return false;

		$qry='';
		foreach($ids as $id)
		{
			if($qry!='')	
				$qry.='UNION ALL';
			$qry.= " SELECT 
						content_id, titel, oe_kurzbz, template_kurzbz, sprache,
						contentsprache_id, version, sichtbar, content, reviewvon, reviewamum,
						tbl_contentsprache.updateamum, tbl_contentsprache.updatevon, 
						tbl_contentsprache.insertamum, tbl_contentsprache.insertvon, 
						menu_open, aktiv, gesperrt_uid, beschreibung, 
						(SELECT CASE WHEN count(*)>0 THEN true ELSE false END 
						 FROM campus.tbl_contentgruppe WHERE content_id=tbl_content.content_id) as locked
					FROM 
					campus.tbl_content
					JOIN campus.tbl_contentsprache USING(content_id)
				WHERE
					tbl_content.content_id=".$this->db_add_param($id, FHC_INTEGER)."
					AND (tbl_contentsprache.sprache=".$this->db_add_param($sprache)."
					OR (tbl_contentsprache.sprache=".$this->db_add_param(DEFAULT_LANGUAGE)." AND
					NOT EXISTS(SELECT * FROM campus.tbl_contentsprache 
							WHERE 
								content_id=".$this->db_add_param($id, FHC_INTEGER)." 
								AND sprache=".$this->db_add_param($sprache);
			$qry.=")))";
			if($sichtbar)
				$qry.=" AND sichtbar=true";
			//Hoechste (sichtbare) Version
			$qry.=" AND (version=(SELECT max(version) FROM campus.tbl_contentsprache 
								WHERE content_id=".$this->db_add_param($id, FHC_INTEGER);
			$qry.=" AND tbl_contentsprache.sprache=".$this->db_add_param($sprache);
			if($sichtbar)
				$qry.=" AND sichtbar=true";
			$qry.=")
			OR
			((SELECT max(version) FROM campus.tbl_contentsprache
			WHERE content_id=".$this->db_add_param($id, FHC_INTEGER);
			$qry.=" AND tbl_contentsprache.sprache=".$this->db_add_param($sprache);
			if($sichtbar)
				$qry.=" AND sichtbar=true";
			$qry.=") is null
			AND version = (SELECT max(version) FROM campus.tbl_contentsprache 
								WHERE content_id=".$this->db_add_param($id, FHC_INTEGER);
			$qry.=" AND tbl_contentsprache.sprache=".$this->db_add_param(DEFAULT_LANGUAGE);
			if($sichtbar)
				$qry.=" AND sichtbar=true";
			$qry.=")))";
		}	

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();

				$obj->content_id = $row->content_id;
				$obj->titel = $row->titel;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->template_kurzbz = $row->template_kurzbz;
				$obj->sprache = $row->sprache;
				$obj->contentsprache_id = $row->contentsprache_id;
				$obj->version = $row->version;
				$obj->sichtbar = $obj->db_parse_bool($row->sichtbar);
				$obj->content = $row->content;
				$obj->reviewvon = $row->reviewvon;
				$obj->reviewamum = $row->reviewamum;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->menu_open = $this->db_parse_bool($row->menu_open);
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->gesperrt_uid = $row->gesperrt_uid;
				$obj->beschreibung = $row->beschreibung;
				$obj->locked = $this->db_parse_bool($row->locked);

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
	 * 
	 * Laedt rekursiv alle Kindelemente eines Contents und liefert diese als Array zurueck
	 * @param $content_id
	 * @return Array mit IDs der Kindelemente
	 */
	public function getChildArray($content_id)
	{
		$qry = "
			WITH RECURSIVE childs(content_id, child_content_id, sort) as 
					(
						SELECT content_id, child_content_id, sort FROM campus.tbl_contentchild 
						WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)."
						UNION ALL
						SELECT cc.content_id, cc.child_content_id, cc.sort FROM campus.tbl_contentchild cc, childs
						WHERE cc.content_id=childs.child_content_id
					)
					SELECT content_id, child_content_id
					FROM childs ORDER BY content_id, sort
			";

		$childs=array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				if($row->child_content_id!='')
					$childs[$row->content_id][]=$row->child_content_id;
			}
			return $childs;
		}
	}
	
	/**
	 * Durchsucht das CMS nach Contents (Titel und Content_ID), in denen $searchItems vorkommt. Limit optional.
	 * 
	 * @param array $searchItems
	 * @param $limit (optional)
	 * @return content_id
	 */
	public function searchCMS($searchItems, $limit=null)
	{
		$qry = "SELECT 
					DISTINCT content_id 
				FROM 
					campus.tbl_content 
				WHERE
					content_id IN
					(
						SELECT 
							DISTINCT ON(content_id,sprache,version) tbl_content.content_id
						FROM 
							campus.tbl_contentsprache
							JOIN campus.tbl_content USING(content_id)
						WHERE 
							tbl_content.template_kurzbz<>'news' AND (";
		foreach($searchItems as $value)
			$qry.="	(
						content_id::text = lower('".$this->db_escape($value)."')
						OR lower(titel::text) like lower('%".$this->db_escape($value)."%') 
					) OR";
			
		$qry.=" 1<>1)) ORDER BY content_id";

		if(!is_null($limit) && is_numeric($limit))
			$qry.=" LIMIT ".$limit;
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new content();
				$obj->content_id = $row->content_id;
				
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
	 * Loescht wahlweise den geamten Content oder nur eine Version in der uebergebenen Sprache
	 * 
	 * @param integer $content_id ID des Contents, der geloescht werden soll
	 * @param string $sprache (optional) Sprache des Contents, die geloescht werden soll
	 * @param string $version (optional) Version des Contents, die geloescht werden soll (nur in Kombination mit Sprache)
	 * @return boolean
	 */
	
	public function deleteContent($content_id, $sprache = NULL, $version = NULL) 
	{
		if($content_id=='' || !is_numeric($content_id))
		{
			$this->errormsg = 'ContentID ungueltig';
			return false;
		}
		
		if (is_null($version) && is_null($sprache))
		{
			//gesamter content wird gelöscht
			$qry  = "DELETE FROM campus.tbl_contentchild WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER).";
					 DELETE FROM campus.tbl_contentchild WHERE child_content_id=".$this->db_add_param($content_id, FHC_INTEGER).";
					 DELETE FROM campus.tbl_contentlog WHERE contentsprache_id IN (SELECT contentsprache_id FROM campus.tbl_contentsprache WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER).");
				 	 DELETE FROM campus.tbl_contentchild WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER).";
					 DELETE FROM campus.tbl_contentsprache WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER).";
					 DELETE FROM campus.tbl_contentgruppe WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER).";
				 	 DELETE FROM campus.tbl_content WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER).";";
		}
		else 
		{
			//eine version wird gelöscht
			$qry = "DELETE FROM campus.tbl_contentlog WHERE contentsprache_id IN (SELECT contentsprache_id FROM campus.tbl_contentsprache WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($sprache)." AND version=".$this->db_add_param($version, FHC_INTEGER).");
					DELETE FROM campus.tbl_contentsprache WHERE content_id=".$this->db_add_param($content_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($sprache)." AND version=".$this->db_add_param($version, FHC_INTEGER).";";
		}
		$this->errormsg = $qry;
		
		if($this->db_query($qry))
			return true;
		else
			return false;
		
	}
	
	/**
	 * Gibt die Anzahl der verbliebenen Eintraege in tbl_contentsprache in der uebergebenen Sprache zurueck
	 *
	 * @param integer $content_id ID des Contents
	 * @param string $sprache Sprache des Contents
	 * @return anzahl
	 */
	
	public function getNumberOfVersions($content_id, $sprache) 
	{
		$qry = "SELECT COUNT(*) as anzahl FROM campus.tbl_contentsprache WHERE content_id = ".$this->db_add_param($content_id, FHC_INTEGER)." AND sprache = ".$this->db_add_param($sprache).";";
		
		if($result = $this->db_query($qry))
		{
			$row = $this->db_fetch_object($result);
			return $row->anzahl;
		}
		else
		{
			return false;
		}
	}
}
?>













