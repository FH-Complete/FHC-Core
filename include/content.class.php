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
					tbl_content.content_id='".addslashes($content_id)."'
					AND tbl_contentsprache.sprache='".addslashes($sprache)."'";
		if($sichtbar)
			$qry.=" AND sichtbar=true";
		if($version!='')
			$qry.=" AND tbl_contentsprache.version='".addslashes(intval($version))."'";
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
				$this->sichtbar = ($row->sichtbar=='t'?true:false);
				$this->content = $row->content;
				$this->reviewvon = $row->reviewvon;
				$this->reviewamum = $row->reviewamum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->menu_open = ($row->menu_open=='t'?true:false);
				$this->aktiv = ($row->aktiv=='t'?true:false);
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
		$qry = "SELECT oe_kurzbz FROM campus.tbl_content WHERE content_id='".addslashes($content_id)."'";
		
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
			$qry = "BEGIN;INSERT INTO campus.tbl_content(template_kurzbz, oe_kurzbz, updatevon, updateamum, insertvon, insertamum, aktiv, menu_open, beschreibung) VALUES(".
					$this->addslashes($this->template_kurzbz).','.
					$this->addslashes($this->oe_kurzbz).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->insertamum).','.
					($this->aktiv?'true':'false').','.
					($this->menu_open?'true':'false').','.
					$this->addslashes($this->beschreibung).');';
		}
		else
		{
			$qry = "UPDATE campus.tbl_content SET ".
					" updatevon=".$this->addslashes($this->updatevon).','.
					" updateamum=".$this->addslashes($this->updateamum).','.
					" template_kurzbz=".$this->addslashes($this->template_kurzbz).','.
					" oe_kurzbz=".$this->addslashes($this->oe_kurzbz).','.
					" aktiv=".($this->aktiv?'true':'false').','.
					" menu_open=".($this->menu_open?'true':'false').','.
					" beschreibung=".$this->addslashes($this->beschreibung).
					" WHERE content_id='".addslashes($this->content_id)."';";
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
		$qry = "SELECT count(*) as anzahl FROM campus.tbl_contentchild WHERE content_id='".addslashes($content_id)."'";
		
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
					tbl_contentchild.content_id='".addslashes($content_id)."'
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
					tbl_contentchild.content_id='".addslashes($content_id)."'
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
					*, (SELECT titel FROM campus.tbl_contentsprache WHERE sprache='".addslashes($sprache)."' AND content_id=tbl_content.content_id ORDER BY version LIMIT 1) as titel
				FROM 
					campus.tbl_content
				WHERE 
					content_id NOT IN(
						WITH RECURSIVE parents(content_id, child_content_id) as 
						(
							SELECT content_id, child_content_id FROM campus.tbl_contentchild 
							WHERE child_content_id='".addslashes($content_id)."'
							UNION ALL
							SELECT cc.content_id, cc.child_content_id FROM campus.tbl_contentchild cc, parents 
							WHERE cc.child_content_id=parents.content_id
						)
						SELECT content_id
						FROM parents
						GROUP BY content_id)
					AND content_id<>'".addslashes($content_id)."'
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
		$qry = "DELETE FROM campus.tbl_contentchild WHERE contentchild_id='".addslashes($contentchild_id)."'";
		
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
		$qry = 'INSERT INTO campus.tbl_contentchild (content_id, child_content_id, insertamum, insertvon, sort) VALUES('.
				$this->addslashes($this->content_id).','.
				$this->addslashes($this->child_content_id).','.
				$this->addslashes($this->insertamum).','.
				$this->addslashes($this->insertvon).','.
				$this->addslashes($this->sort).');';
				
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
	 * Holt die hochste Sortierung eines Contentteilbaums
	 * 
	 * @param $content_id
	 */
	public function getMaxSort($content_id)
	{
		$qry="SELECT max(sort) as max FROM campus.tbl_contentchild WHERE content_id='".addslashes($content_id)."'";
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
				$obj->aktiv = ($row->aktiv=='t'?true:false);
				$obj->menu_open = ($row->menu_open=='t'?true:false);
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
	 * Laedt alle Content Eintraege die keine Childs von anderen Contenteintraegen sind
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
				$obj->aktiv = ($row->aktiv=='t'?true:false);
				$obj->menu_open = ($row->menu_open=='t'?true:false);
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
								WHERE contentchild_id='".addslashes($contentchild_id)."')
					AND sort<(SELECT sort FROM campus.tbl_contentchild 
								WHERE contentchild_id='".addslashes($contentchild_id)."')
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
						 WHERE contentchild_id='".addslashes($contentchild_id)."')
				WHERE contentchild_id='".addslashes($nachbar_id)."';
				UPDATE campus.tbl_contentchild SET sort='".addslashes($nachbar_sort)."' 
				WHERE contentchild_id='".addslashes($contentchild_id)."';";
		
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
								WHERE contentchild_id='".addslashes($contentchild_id)."')
					AND sort>(SELECT sort FROM campus.tbl_contentchild 
								WHERE contentchild_id='".addslashes($contentchild_id)."')
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
						 WHERE contentchild_id='".addslashes($contentchild_id)."')
				WHERE contentchild_id='".addslashes($nachbar_id)."';
				UPDATE campus.tbl_contentchild SET sort='".addslashes($nachbar_sort)."' 
				WHERE contentchild_id='".addslashes($contentchild_id)."';";
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
					$this->addslashes($this->sprache).','.
					$this->addslashes($this->content_id).','.
					$this->addslashes($this->version).','.
					($this->sichtbar?'true':'false').','.
					$this->addslashes($this->content).','.
					$this->addslashes($this->reviewvon).','.
					$this->addslashes($this->reviewamum).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->titel).','.
					$this->addslashes($this->gesperrt_uid).');';					
		}
		else
		{
			$qry = "UPDATE campus.tbl_contentsprache SET ".
					" sprache=".$this->addslashes($this->sprache).','.
					" content_id=".$this->addslashes($this->content_id).','.
					" version=".$this->addslashes($this->version).','.
					" sichtbar=".($this->sichtbar?'true':'false').','.
					" content=".$this->addslashes($this->content).','.
					" reviewvon=".$this->addslashes($this->reviewvon).','.
					" reviewamum=".$this->addslashes($this->reviewamum).','.
					" updatevon=".$this->addslashes($this->updatevon).','.
					" updateamum=".$this->addslashes($this->updateamum).','.
					" titel=".$this->addslashes($this->titel).','.
					" gesperrt_uid=".$this->addslashes($this->gesperrt_uid).
					" WHERE contentsprache_id='".addslashes($this->contentsprache_id)."';";
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
		$qry = "SELECT max(version) maxversion FROM campus.tbl_contentsprache WHERE content_id='".addslashes($content_id)."' AND sprache='".addslashes($sprache)."'";
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
				WHERE content_id='".addslashes($content_id)."' AND sprache='".addslashes($sprache)."'
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
				$obj->sichtbar = ($row->sichtbar=='t'?true:false);
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
	 * @param $version
	 */
	public function getLanguages($content_id)
	{
		$qry = "SELECT distinct sprache FROM campus.tbl_contentsprache WHERE content_id='".addslashes($content_id)."'";
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
	 * @return boolean
	 */
	public function contentSpracheExists($content_id, $sprache, $version=null, $sichtbar=null)
	{
		$qry = "SELECT 1 FROM campus.tbl_contentsprache 
				WHERE 
					content_id='".addslashes($content_id)."' 
					AND sprache='".addslashes($sprache)."'
				";
		if(!is_null($version) && $version!='')
			$qry.=" AND version='".addslashes(intval($version))."'";
		if($sichtbar)
			$qry.=" AND sichtbar=".($sichtbar?'true':'false');
		
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
		$qry = "SELECT * FROM campus.tbl_contentsprache WHERE contentsprache_id='".addslashes($contentsprache_id)."'";
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->contentsprache_id = $row->contentsprache_id;
				$this->sprache = $row->sprache;
				$this->content_id = $row->content_id;
				$this->version = $row->version;
				$this->sichtbar = $row->sichtbar;
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
		$qry = "SELECT * FROM campus.tbl_contentlog WHERE contentsprache_id='".addslashes($contentsprache_id)."' AND ende is null LIMIT 1;";
		
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
				$this->addslashes($user).','.
				$this->addslashes($contentsprache_id).',now());
				UPDATE campus.tbl_contentsprache SET gesperrt_uid='.$this->addslashes($user).
				' WHERE contentsprache_id='.$this->addslashes($contentsprache_id);
				
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Sperren';
			return false;
		}
	}
	
	/**
	 * Gibt einen Eintrag nach dem Bearbeiten wieder frei
	 * 
	 * @param $contentsprache_id
	 * @param $user
	 */
	public function freigeben($contentsprache_id, $user)
	{
		$qry = 'UPDATE campus.tbl_contentlog SET ende=now() WHERE'.
				' uid='.$this->addslashes($user).
				' AND contentsprache_id='.$this->addslashes($contentsprache_id).
				' AND ende is null;'.
				'UPDATE campus.tbl_contentsprache SET gesperrt_uid=null;';
				
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Sperren';
			return false;
		}
	}
	
}
?>